<?php
/**
 * Addon Name: Cancel
 * Description: Handles cancel requests
 * Class Name: WpBCancel
 * Version: 3.0.0Beta31
 * 
 * @package WP BASE
 * @author Hakan Ozevin
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBCancel' ) ) {

class WpBCancel {
	
	/**
     * WP BASE Core + Front [+Admin] instance
     */
	protected $a = null;
	
	/**
     * Constructor
     */
	function __construct() {
		$this->a = BASE();
	}
	
	/**
     * Add hooks
     */
	function add_hooks(){
		add_filter( 'app_list_add_cancel_column', array( $this, 'add_column' ), 10, 2 );	// Add column
		add_filter( 'app_list_add_cancel_button', array( $this, 'add_button' ), 10, 3 );	// Add button in cell
		
		add_action( 'init', array( $this, 'cancel_from_email' ), 3 ); 						// Check cancellation of an appointment from email link
		add_action( 'wp_ajax_cancel_app', array( $this, 'cancel_from_list' ) ); 			// Cancel appointment from List shortcode
		add_action( 'wp_ajax_nopriv_cancel_app', array( $this, 'cancel_from_list' ) ); 		// Cancel appointment from List shortcode
	}
	
	/**
     * Check if a user (probably editor) can override cancel
	 * @return bool
     */
	function can_override( $args ) {
		# Not capable
		$cap = !empty( $args['cap'] ) ? $args['cap'] : '';
		if ( BASE('User')->is_capable( $cap ) )
			return false;
		
		$override = !empty( $args['override'] ) ? $args['override'] : '';
		
		# Can override because of shortcode setting
		if ( $override && 'inherit' != $override )
			return true;
		
		# Can override because of global setting
		if ( 'inherit' == $override && 'yes' == wpb_setting( 'allow_cancel' ) )
			return true;
		
		return false;
	}
	
	/**
     * Check if a worker can cancel
	 * @return bool
     */
	function can_worker_cancel( $args ) {

		if ( $this->a->is_worker( get_current_user_id() ) && 'yes' == wpb_setting('allow_worker_cancel') )
			return true;
		
		return false;
	}
	
	/**
     * Check if clients can cancel (owner check not done here)
	 * @return bool
     */
	function can_client_cancel( $args ) {
		
		$is_client = !empty( $args['what'] ) && 'worker' != $args['what'];

		if ( $is_client && 'yes' == wpb_setting('allow_cancel') )
			return true;
		
		return false;
	}

	/**
     * Whether add a column to Bookings List
	 * @return bool
     */
	function add_column( $maybe_false, $args ) {
		
		# User (editor) can override cancel
		if ( $this->can_override( $args ) )
			return true;
		
		# Worker is allowed to cancel
		if ( $this->can_worker_cancel( $args ) )
			return true;
		
		# User is allowed to cancel
		if ( $this->can_client_cancel( $args )  )
			return true;

		return false;
	}
	
	/**
     * Whether user is owner of this booking
	 * @return bool
     */
	function is_owner( $r ) {
		if ( $r->user == get_current_user_id() )
			return true;
		
		# Check from cookie
		if ( $apps = $this->a->get_apps_from_cookie() ) {
			if ( is_array( $apps ) && in_array( $app_id, $apps ) )
				return true;
		}
		
		return false;
	}
	
	/**
     * Check if status of a booking is allowed to cancel
	 * @param $r		object		Booking object
	 * @return bool
     */
	function in_allowed_status( $r, $args=array() ) {
		$stat = !empty( $r->status ) ? $r->status : '';
		
		if ( !$stat || 'removed' == $stat )
			return false;
		
		$statuses = apply_filters( 'app_cancel_allowed_status', array( 'pending','confirmed','paid' ), $r->ID, $args );
		
		return in_array( $stat, $statuses );
	}
	
	/**
     * Combine all conditions to see if cancel allowed
     */
	function is_allowed( $r, $args ) {
		# We don't want completed appointments to be cancelled
		# Also check if cancel time has been passed
		# Even admin cannot cancel already cancelled app
		$stat				= !empty( $r->status ) ? $r->status : '';
		$in_allowed_status	= $this->in_allowed_status( $r, $args );
		$allowed_as_client	= $this->is_owner( $r ) && $this->can_client_cancel( $args );
		$allowed_as_worker	= $r->worker == get_current_user_id() && $this->can_worker_cancel( $args );
		$override			= $this->can_override( $args );
		$is_late			= $this->is_too_late( $r );
		
		if ( ( 'removed' != $stat && $override ) || ( $in_allowed_status && !$is_late && ( $allowed_as_client || $allowed_as_worker ) ) )
			return true;
		
		return false;		
	}
	
	/**
     * Whether add a button to the cell in Bookings List
     */
	function add_button( $ret, $r, $args ) {
		
		if ( $this->is_allowed( $r, $args ) ) {
			$is_disabled = '';
			$title = '';
		}
		else {
			$is_disabled = ' app-disabled-button';
			if ( $this->is_too_late( $r ) )
				$title = 'title="'. $this->a->get_text('too_late').'"';
			else
				$title = 'title="'. $this->a->get_text('not_possible').'"';
		}
		
		$button_text = !empty( $args['cancel_button'] ) ? $args['cancel_button'] : $this->a->get_text('cancel_button');
		$ret .= '<button '.$title.' class="app-list-cancel ui-button ui-state-default '.$is_disabled.'" name="app_cancel['.$r->ID.']" >'.$button_text.'</button>';
		
		return $ret;
	}
	
	/**
     * Handle cancellation when client clicks link in email
     */
	function cancel_from_email(){
		# Display cancelled notice
		if ( !empty( $_GET['app_cancelled'] ) ) {
			wpb_notice( $this->a->get_text('cancelled') );
			return;
		}
		
		# Check if we received required parameters 
		if ( empty( $_GET['app_cancel'] ) || empty( $_GET['app_id'] ) || empty( $_GET['cancel_nonce'] ) )
			return;

		# Only clients can use this option - Check if cancellation is still open
		if ( 'yes' != wpb_setting('allow_cancel') ) {
			if ( isset( $_GET['app_id'] ) && isset( $_GET['cancel_nonce'] ) ) {
				if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
					die( json_encode( array('error'=>esc_js( $this->a->get_text('cancel_disabled') ) ) ) );
				else
					wpb_notice( 'cancel_disabled', 'error' );
			}

			return;
		}

		if ( !empty( $_GET['app_re_cancel_login'] ) && !is_user_logged_in() ) {
			wpb_notice( $this->a->get_text('login_for_cancel') );
			return;
		}

		$app_id = $_GET['app_id'];
		$app = $this->a->get_app( $app_id );
		
		if ( empty( $app->created ) || !$this->in_allowed_status( $app ) ) {
			wpb_notice( 'not_possible', 'error' );
			return; # Appt deleted completely or already cancelled
		}
		
		# Check provided hash
		if (  $_GET['cancel_nonce'] != $this->a->create_hash( $app, 'cancel' ) ) {
			wpb_notice( 'unauthorised', 'error' );
			return;
		}
		
		if ( $this->is_too_late( $app ) ) {
			wpb_notice( 'too_late', 'error' );
			return;
		}
		
		# Check owner of the app. If he is a WP user and not logged in, make him log in
		if ( !empty( $app->user ) ) {
			if ( is_user_logged_in() && $app->user != get_current_user_id() )
				return; // User is not owner
			
			// If extra safety is required, force re-login
			$reauth = defined( 'WPB_EXTRA_SAFETY' ) && WPB_EXTRA_SAFETY ? true : false;
			if ( !is_user_logged_in() || $reauth ) {
				$redirect = wpb_add_query_arg( 'app_re_cancel_login', $app_id, wp_login_url( esc_url_raw($_SERVER['REQUEST_URI']), $reauth ) );
				wp_redirect( $redirect );
				exit;
			}
		}
		
		if ( $this->a->change_status( 'removed', $app_id ) ) {
			$this->a->log( sprintf( __('Client %1$s cancelled appointment having ID: %2$s','wp-base'), BASE('User')->get_client_name( $app_id, $app, false ), $app_id ) );
			$this->a->maybe_send_message( $app_id, 'cancellation' );
			wpb_update_app_meta( $app_id, 'abandoned', 'cancelled' );
			
			# If there would be a header warning other plugins can do whatever they could do
			if ( !headers_sent() ) {
				$url = wpb_setting('cancel_page') ? get_permalink( wpb_setting('cancel_page') ) : home_url();
				wp_redirect( wpb_add_query_arg( 'app_cancelled', $app_id, $url ) );
				exit;
			}
		}
		else {
			# If failed for any unknown reason, gracefully go to home page or do something else here
			do_action( 'app_cancel_failed', $app_id );
		}
	}

	/**
     * Handle cancellation when client clicks button in Booking List 
     */
	function cancel_from_list(){
		if ( empty( $_POST['app_id'] ) || empty( $_POST['cancel_nonce'] ) )
			die( json_encode( array('error'=>esc_js( $this->a->get_text('error') ) ) ) );
			
		# If client has been logged off, this will fail
		if ( !check_ajax_referer( 'cancel-app', 'cancel_nonce', false ) )
			die( json_encode( array('error'=>esc_js( $this->a->get_text('unauthorised') ) ) ) );

		$app_id	= (int)$_POST['app_id'];
		$app	= $this->a->get_app( $app_id );
		$args	= !empty( $_POST['args'] ) ? json_decode( wp_unslash( $_POST['args'] ) ) : array();
		
		# If it is already cancelled, e.g. by admin
		if ( 'removed' === $app->status )
			die( json_encode( array('error'=>$this->a->get_text('not_possible') ) ) );
		
		$override_cancel = $this->can_override( $args );
	
		# Addons may want to do something here
		$is_owner = apply_filters( 'app_cancellation_owner', $this->is_owner( $app ), $app );
		
		$has_right = ( $app->worker == get_current_user_id() && $this->can_worker_cancel( $args ) ) || ( $is_owner && $this->can_client_cancel( $args ) );
					
		# Too late message and final checks
		$in_allowed_stat = $this->in_allowed_status( $app );
		$too_late = $this->is_too_late( $app ) ? true : false;

		if ( !($override_cancel || ( $in_allowed_stat && !$too_late && $has_right )) ) {
			if ( $too_late )
				die( json_encode( array('error'=>esc_js( $this->a->get_text('too_late') ) ) ) );
			else
				die( json_encode( array('error'=>esc_js( $this->a->get_text('unauthorised') ) ) ) );
		}

		// Now we can safely continue for cancel
		if ( $this->a->change_status( 'removed', $app_id ) ) {
			
			if ( $this->is_owner( $app ) ) {
				wpb_update_app_meta( $app_id, 'abandoned', 'cancelled' );
				$this->a->log( sprintf( __('Client %1$s cancelled own booking with ID: %2$s','wp-base'), BASE('User')->get_client_name( $app_id, null, false ), $app_id ) );
			}
			else if ( $app->worker == get_current_user_id() ) {
				wpb_update_app_meta( $app_id, 'abandoned', 'worker_cancelled' );
				$this->a->log( sprintf( __('Provider %1$s cancelled own booking with ID: %2$s','wp-base'), $this->a->get_worker_name( $app->worker ), $app_id ) );
			}	
			else {
				wpb_update_app_meta( $app_id, 'abandoned', 'editor_cancelled' );
				$this->a->log( sprintf( __(' %1$s cancelled appointment of %2$s having ID: %3$s','wp-base'), BASE('User')->get_name(), BASE('User')->get_client_name( $app_id, null, false ), $app_id ) );
			}
			
			$this->a->maybe_send_message( $app_id, 'cancellation' );
			die( json_encode( array('success'=>1)));
		}
		else
			die( json_encode( array('error'=>esc_js( $this->a->get_text('not_possible') ) ) ) );
	}
	
	/**
	 * Check if appointment is too late to be cancelled
	 * @param app_id: ID of the appointment
	 * @since 2.0
	 */	
	function is_too_late( $app ) {
		$cancel_limit = (int)apply_filters( 'app_cancel_limit', wpb_setting("cancel_limit"), $app->ID );
		
		if ( $this->a->_time > strtotime( $app->start, $this->a->_time ) - intval( $cancel_limit * 3600 ) )
			$result = true;
		else
			$result = false;
		
		return apply_filters( 'app_cancel_is_too_late', $result, $app );
	}
	
	
}
	BASE('Cancel')->add_hooks();
}