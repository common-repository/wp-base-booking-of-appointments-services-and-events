<?php
/**
 * WPB Multiple
 *
 * Common methods for Multiple Appointments Addons (Packages, Recurring, Shopping Cart, Marketpress, Woocommerce, Extras)
 *
 * Adapted from WP Core
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBMultiple' ) ) {

class WpBMultiple {
	
	var $virtual = array();
	
	/**
     * WP BASE Core + Front [+Admin] instance
     */
	protected $a = null;
	
	protected $session_id;
	
	/**
     * Constructor
     */
	function __construct() {
		$this->a = BASE();
	}
	
	public function add_hooks(){
		
		add_action( 'admin_init', array( $this, 'init' ) );											// Check if advanced tab will be added
		add_action( 'app_pre_confirmation_check', array( $this, 'check_pre' ), 10, 1 );				// Check submitted vars in pre confirmation
		add_action( 'app_post_confirmation_check', array( $this, 'check_post' ), 10, 1 );			// Check submitted vars in post confirmation
		add_filter( 'app_reserved_status_query', array( $this, 'status_query' ), 16, 2 );			// Modify reserved statuses query
		add_action( 'app_inline_edit_updated', array( $this, 'inline_edit_update' ), 10, 3 );		// Modify user data after inline edit update
		add_action( 'app_change_status_children', array( $this, 'change_status' ), 10, 2 );			// Change status of childs
		add_action( 'app_deleted', array( $this, 'delete_h' ), 10, 2 );								// Delete childs
		add_action( 'app_bulk_status_change', array( $this, 'bulk_status_change' ), 10, 2 );		// Bulk change status of childs
		add_filter( 'app_statuses', array( $this, 'get_statuses' ), 18 );
		add_filter( 'app_pre_confirmation_reply', array( $this, 'pre_confirmation' ), 200, 2 );
		
		# Email
		add_filter( 'app_email_replace_pre', array( $this, 'email_replace' ), 100, 3 );				// Change several values in case of MA

		# List table
		// TODO: Activate this later
		// add_filter( 'app_list_allowed_columns', array( $this, 'allowed_columns' ), 12, 2 );			// Add jobs to the allowed list
		add_filter( 'app_list_add_cell', array( $this, 'add_cell' ), 12, 4 );						// Add cell for List shortcode

		# Admin
		add_filter( 'app_id_email', array( $this, 'app_id_email' ) );								// Modify app id in admin emails
		add_filter( 'app_search', array( $this, 'app_search' ), 10, 2 );							// Modify search
		// add_filter( 'appointments_tabs', array( $this, 'add_tab' ), 18 ); 						// Add tab
		add_action( 'app_save_settings', array( $this, 'save_settings' ) );							// Save settings
		// TODO: Activate these later
		// add_filter( 'app_bookings_allowed_columns', array( $this, 'allowed_columns' ), 12, 2 );		// Add jobs to the allowed list
		// add_filter( 'app_bookings_default_columns', array( $this, 'default_columns' ), 12, 1 );		// Add to default columns when nothing has selected
		// add_filter( 'default_hidden_columns', array( $this, 'default_hidden_columns' ), 12, 2 );	// Hide columns when nothing has selected
		add_filter( 'app_bookings_add_cell', array( $this, 'add_cell' ), 12, 4 );					// Add cell for List shortcode
	}
	
	function init() {
		if ( ! $this->is_active() )
			return;
		
		add_filter( 'app_add_advanced_tab', '__return_true' );										// Add Advanced tab
		add_action( 'app_advanced_settings', array( $this, 'settings' ), 8 );						// Display HTML settings
	}
	
	/**
	 * Check if any multiple appt addon is active
	 * @since 2.0
	 * @return bool
	 */
	function is_active() {
		if ( class_exists( 'WpBShoppingCart' ) || class_exists( 'WpBPackages' ) || class_exists( 'WpBRecurring' ) || class_exists( 'WpBWooCommerce' ) || class_exists( 'WpBMarketPress' ) )
			return true;
		
		return false;
	}
	
	/**
     * Modify statuses array and add cart status inside, only for admin
	 * @return array
     */
	function get_statuses( $s ) {
		if ( !$this->is_active() || !wpb_is_admin() )
			return $s;
		
		if ( !isset( $s['cart'] ) ) {
			$s['cart'] = $this->a->get_text('cart');
		}
		if ( !isset( $s['hold'] ) ) {
			if ( !isset( $this->hold_count ) )
				$this->hold_count = $this->a->db->get_var( "SELECT COUNT(*) FROM " . $this->a->app_table . " WHERE status='hold' " );

			if ( $this->hold_count )
				$s['hold'] = $this->a->get_text('hold');
		}				
		return $s;
	}

	/**
     * Modify the query that defines reserved
	 * Do not auto remove temp appointments
	 * But, directly delete expired temp appointments
	 * @param scope: Which method calls the filter
     */
	function status_query( $q, $context ) {
		if ( !$this->is_active() )
			return $q;
		
		$q_add = " OR status='cart'";
		if ( strpos( $q, $q_add ) !== false )
			return $q;
		
		return $q. $q_add;
	}
	
	/**
	 * Return session ID to mark virtual appts
	 * @since 2.0
	 * @return array
	 */
	function get_session_id(){
		return $this->a->session()->get_id();
	}

	/**
	 * Get virtual appt values
	 * @since 2.0
	 * @return array
	 */
	function get_virtual() {
		$id = $this->get_session_id();
		return isset( $this->virtual[$id] ) ? $this->virtual[$id] : array();
	}
	
	/**
	 * Set virtual appt values
	 * @param $v: array of appt objects
	 * @since 2.0
	 * @return none
	 */
	function set_virtual( $v ) {
		$id = $this->get_session_id();
		$this->virtual[$id] = $v;
	}

	/**
	 * Reset virtual appt values
	 * @since 2.0
	 * @return none
	 */
	function reset_virtual() {
		$id = $this->get_session_id();
		$this->virtual[$id] = array();
	}
	
	/**
	 * Delete a set of virtuals
	 * @since 2.0
	 * @return none
	 */
	function delete_virtual( $ids ) {
		if ( empty( $ids ) || !is_array( $ids ) )
			return;
		
		$id = $this->get_session_id();
		foreach ( $ids as $vid ) {
			unset( $this->virtual[$id][$vid] );
		}
	}

	/**
     * Create a virtual app to see what could be happened if such an app existed
	 * @return object
     */
	function create_virtual( $ID, $slot ) {
		// Account for on behalf booking
		$user_id = empty( $_POST['app_user_id'] ) ? 0 : $_POST['app_user_id'];
		$user_id = !$user_id && get_current_user_id() ? get_current_user_id() : 0; 

		$v = new StdClass;
		$v->ID = $ID;
		$v->created	= date ("Y-m-d H:i:s", $this->a->_time );
		$v->user = $user_id;
		$v->location = $slot->get_location();
		$v->service = $slot->get_service();
		$v->worker = $slot->get_worker();
		$v->start = date( "Y-m-d H:i:s", $slot->get_start() );
		$v->end = date( "Y-m-d H:i:s", $slot->get_end() );
		
		// Calculate price: In case we add an order limit in the future
		// $v->price = $this->a->get_base_price( $slot_start, $slot_end, $v->location, $v->service, $v->worker );
		
		return $v;
	}

	/**
     * Save app data after inline edit update
	 * @param $stat: New status
	 * @param $data: Saved data in array form
     */
	function inline_edit_update( $app_id, $data, $app_old ) {
		if ( !( is_array( $data ) && $app_id ) )
			return;
		
		// $cart = new ApBCart();
		$childs = $this->get_children( $app_id );
		if ( !$childs )
			return;
		
		// If app was a parent and became a child now, assign children to new parent
		$new_parent_arr = $data['parent_id'] ? array( 'parent_id' => $data['parent_id'] ) : array();
		// If status changed, match children to the new status
		$status = $data['status'];
		if ( in_array( $status, array_keys( $this->a->get_statuses() ) ) )
			$new_parent_arr['status'] = $status;
		
		foreach ( $childs as $child ) {
			// If old child is new parent now, set accordingly
			if ( $data['parent_id'] == $child->ID )
				$new_parent_arr['parent_id'] = 0;
			
			$this->a->db->update( $this->a->app_table,
				array_merge( 
					$new_parent_arr,
					array(
						'user'			=> $data['user'],
					)
				), 
				array('ID' => $child->ID) 
			);
		}
	}
	
	/**
     * Match status of child to parent
	 * @param app_id: Parent ID
     */
	function change_status( $stat, $app_id ) {
		$app = $this->a->get_app( $app_id );
		$childs = $this->get_children( $app_id );
		$result = false;
		if ( $childs && array_key_exists( $app->status, $this->a->get_statuses() ) ) {
			foreach ( $childs as $child ) {
				if( $this->a->db->update( $this->a->app_table, array( 'status' => $app->status ), array( 'ID' => $child->ID ) ) ) {
					$result = true;
					do_action( 'app_child_status_changed', $app->status, $child->ID );
				}
			}
		}
		if ( $result )
			wpb_flush_cache();
	}
	
	/**
     * Bulk change status of childs
	 * @param $stat: New status
	 * @param $post_vars: $_POST variable in array form or an array of ids
     */
	function bulk_status_change( $stat, $post_vars ) {
		if ( is_array( $post_vars ) && array_key_exists( $stat, $this->a->get_statuses() ) ) {
			$result = false;
			foreach ( $post_vars as $app_id ) {
				$childs = $this->get_children( $app_id );
				if ( $childs ) {
					foreach ( $childs as $child ) {
						if ( $this->a->change_status( $stat, $child, false ) ) {
							$result = true;
							do_action( 'app_child_status_changed', $stat, $child->ID );
						}
					}
				}
			}
			if ( $result )
				wpb_flush_cache();
		}
	}
	
	/**
     * Delete children handler
	 * @param $post_vars: $_POST variable in array form (app IDs array which are deleted)
     */
	function delete_h( $context, $app_ids=null ) {
		if ( 'child' == $context || 'children' == $context )
			return;
		
		return $this->delete_children( $app_ids );
	}
	
	/**
     * Delete childs (In case of expiration)
	 * @param $post_vars: $_POST variable in array form (app IDs array which are deleted) or an array of ids of parents
     */
	function delete_children( $post_vars ) {
		$result = false;
		if ( is_array( $post_vars ) && !empty( $post_vars ) ) {
			do_action( 'app_delete_pre', 'children', $post_vars );	
			foreach ( $post_vars as $app_id ) {
				$childs = $this->get_children( $app_id );
				if ( $childs ) {
					foreach ( $childs as $child ) {
						do_action( 'app_delete_pre', 'child', $child->ID );
						if ( $this->a->db->query( $this->a->db->prepare("DELETE FROM " . $this->a->app_table . " WHERE ID=%d LIMIT 1", $child->ID) ) ) {
							$result = true;
							// Delete meta
							$this->a->db->query( $this->a->db->prepare("DELETE FROM " . $this->a->meta_table . " WHERE object_id=%d AND meta_type='app'", $child->ID) );
							do_action( 'app_deleted', 'child', $child->ID );
						}
					}
				}
			}
			if ( $result ) {
				wpb_flush_cache();
				do_action( 'app_deleted', 'children' );
			}
		}
		return $result;
	}
	
	/**
     * Get children given a parent appointment
	 * @param $app_id: Parent ID
	 * @param $apps: Optionally a list of app objects to get children from
	 * @return array of objects
     */
	public function get_children( $app_id, $apps=null ) {
		if ( !$app_id )
			return array();
		
		$identifier = wpb_cache_prefix() . 'child_apps_'. $app_id;
		$childs = wp_cache_get( $identifier );
		
		if ( false === $childs ) {
			if ( !empty( $apps ) )
				$childs = $this->get_children_from_apps( $app_id, $apps );
			else
				$childs = $this->a->db->get_results( "SELECT * FROM " . $this->a->app_table . " WHERE parent_id='".esc_sql($app_id)."' AND parent_id>0 ORDER BY start", OBJECT_K );
			
			$childs = $childs ? $childs : array();
			
			wp_cache_set( $identifier, $childs );
		}
		
		return $childs;	
	}
	
	/**
     * Get children from a known list of objects given a parent appointment
	 * Adapted from WP function get_page_children
	 * @param $app_id: Parent id
	 * @param $apps: A list of app objects to get children from
	 * @return array of objects
     */
	function get_children_from_apps( $app_id, $apps ) {
		// Build a hash of ID -> children.
		$children = array();
		foreach ( (array) $apps as $app ) {
			$children[ intval( $app->parent_id ) ][] = $app;
		}
	 
		$app_list = array();
	 
		// Start the search by looking at immediate children.
		if ( isset( $children[ $app_id ] ) ) {
			// Always start at the end of the stack in order to preserve original `$apps` order.
			$to_look = array_reverse( $children[ $app_id ] );
	 
			while ( $to_look ) {
				$p = array_pop( $to_look );
				$app_list[] = $p;
				if ( isset( $children[ $p->ID ] ) ) {
					foreach ( array_reverse( $children[ $p->ID ] ) as $child ) {
						// Append to the `$to_look` stack to descend the tree.
						$to_look[] = $child;
					}
				}
			}
		}
	 
		return $app_list;		
	}
	
	/**
     * Find other children of a child's parent
	 * @param app_id: ID of the Child
	 * @return array of objects
     */
	public function get_siblings( $app_id ) {
		if ( !$app_id )
			return false;
		
		$identifier = wpb_cache_prefix() . 'sibling_apps_'. $app_id;
		$siblings = wp_cache_get( $identifier );
		
		if ( false === $siblings ) {
			$child = $this->a->get_app( $app_id );
			if ( empty( $child->parent_id ) )
				$siblings = null;
			else
				$siblings = $this->a->db->get_results( $this->a->db->prepare( "SELECT * FROM " . $this->a->app_table . " WHERE parent_id=%d AND parent_id>0 AND ID<>%d", $child->parent_id, $app_id), OBJECT_K );
			
			wp_cache_set( $identifier, $siblings );
		}
		
		return $siblings;
	}
	
	/**
     * Read cart items from session superglobal
	 * @return array 
     */
	function get_cart_items( ) {
		return array_filter( array_map( 'intval', (array)wpb_get_session_val('app_cart', array()) ) );
	}

	/**
     * Get all appts in a cart in a certain status
	 * @return array of objects
     */
	public function get_apps( $status='hold' ) {
		if ( !$ids = $this->get_cart_items() )
			return false;
		
		return $this->a->db->get_results( "SELECT * FROM " . $this->a->app_table . " WHERE ID IN (".implode(',',$ids).") AND status='".$status."' ORDER BY start", OBJECT_K );
	}

	/**
     * Check if apps in the SESSION are still in the DB and in cart status
	 * If not, clear them
	 * @return none
     */
	function check_cart() {
		// When cart is not enabled, or emptied there must not be any appt in the DB related to the session
		if ( !wpb_get_session_val('app_cart') ) {
			$ids = $this->a->db->get_col( "SELECT * FROM " . $this->a->app_table. " WHERE (status='cart' OR status='hold') AND ID IN(
								SELECT object_id FROM ". $this->a->meta_table." 
								WHERE meta_type='app' AND meta_key='session_id' AND meta_value='".$this->get_session_id()."') 
								" );
							
			if ( !empty( $ids ) ) {					
				$this->a->db->query( sprintf( "DELETE FROM " . $this->a->app_table. " WHERE (status='cart' OR status='hold') AND ID IN(%s) ", implode(',',$ids) ) );
				$this->a->db->query( sprintf( "DELETE FROM " . $this->a->meta_table." WHERE meta_type='app' AND object_id IN(%s) ", implode(',',$ids) ) );
			}
		}
		
		$ids = $this->get_cart_items();
		if ( empty( $ids ) )
			return;
		
		if ( !$results = $this->get_apps( 'cart' ) ) {
			wpb_set_session_val('app_cart', null);
			return;
		}
		
		$exists = array();
		foreach( $results as $r ) {
			$exists[] = $r->ID;
		}
		foreach ( $ids as $key=>$id ) {
			if ( !in_array( $id, $exists ) )
				unset( $ids[$key] );
		}

		wpb_set_session_val('app_cart', $ids);
	}
	
	 /**
     * Create an "app_value" result using app ids in session variable
	 * @return array if success, false if failure
     */
	function values() {
		if ( !$results = $this->get_apps( 'cart' ) ) {
			wpb_set_session_val('app_cart', null);
			return false;
		}
		
		$out = array();
		foreach( $results as $r ) {
			$r = apply_filters( 'app_cart_values', $r, $results );
			if ( empty( $r->service ) )
				continue;
			
			$slot = new WpBSlot( new WpBCalendar( $r->location, $r->service, $r->worker ), strtotime( $r->start, $this->a->_time ), strtotime( $r->end, $this->a->_time ) );
			$slot->set_app_id( $r->ID );
			$out[] = $slot->pack( );
		}
		
		return $out;
	}

	 /**
     * Adds a booking to DB in cart status, saves app_id to the cart
	 * This is "lazy load", so not all the properties are complete
	 * @param $context:	package or recurring
	 * @return $app_id (integer) if success, false if failure
     */
	function add( $val, $main=0, $parent_id=0, $context = '' ) {
		
		$slot		= new WpBSlot( $val );
		$category	= $slot->get_category();
		$service	= $slot->get_service();
		$start		= $slot->get_start();
		$end		= $slot->get_end();
		$format 	= $this->a->is_daily( $service ) ? 'Y-m-d' : 'Y-m-d H:i:s';
		$data = array(
					'parent_id'	=>	$parent_id,
					'created'	=>	date("Y-m-d H:i:s", $this->a->_time ),
					'user'		=>	!empty( $_POST['app_user_id'] ) ? $_POST['app_user_id'] : get_current_user_id(),
					'location'	=>	$slot->get_location(),
					'service'	=>	$service,
					'worker'	=> 	$slot->get_worker(),
					'status'	=>	'cart',
					'start'		=>	date ( $format, $start ),
					'end'		=>	date ( $format, $end ),
					'seats'		=>	apply_filters( 'app_pax', 1, $service, $slot ),
				);
		
		$result = $this->a->db->insert( $this->a->app_table, $data );
		if ( $result ) {
			$app_id = $this->a->db->insert_id;
			wpb_update_app_meta( $app_id, 'session_id', $this->get_session_id() );
			
			if ( $category )
				wpb_update_app_meta( $app_id, 'category', $category );
			
			if ( $main && $context ) {
				# Mark booking as package or recurring
				wpb_update_app_meta( $app_id, $context, $main );
			}
			
			if ( !empty( $_POST['has_cart'] ) ) {
				$temp = $this->get_cart_items();
				$temp[] = $app_id;
				wpb_set_session_val( 'app_cart', array_filter( array_unique( $temp ) ) );
			}
			
			wpb_flush_cache();
			return $app_id;
		}
		
		return false;
	}
	
	/**
     * Take a booking to hold status
	 * @return db result
     */
	function hold( $app_id ) {
		if ( !$app_id )
			return false;
		
		if ( $this->a->db->update( $this->a->app_table, array( 'status'=>'hold' ), array( 'ID'=>$app_id ) ) ) {
			wpb_flush_cache();
			return true;
		}
		return false;
	}
	
	/**
     * Take a booking back to cart status
	 * Also parent and siblings returned back to cart
	 * @return db result
     */
	function unhold( $app_id ) {
		if ( !$app_id )
			return false;
		
		$result = false;
		$app = $this->a->get_app( $app_id );
		$parent = !empty( $app->parent_id ) ? array( $app->parent_id => $this->a->get_app( $app->parent_id ) ) : array();
		$me = array( $app_id => $app );
		$siblings = $this->get_siblings( $app_id );
		$children = $this->get_children( $app_id );
		$friends = $this->get_apps(); // Apps in cart + in hold
		$related = array_merge( (array)$siblings, (array)$children, (array)$friends, $parent, $me );
		
		foreach ( $related as $rel ) {
			if ( empty( $rel->ID ) )
				continue;
			if ( $this->a->db->update( $this->a->app_table, array( 'status'=>'cart'), array( 'ID'=>$rel->ID ) ) )
				$result = true;
		}
		
		return $result;
	}

	/**
     * Removes a cart item from cart 
	 * @return $val string if success, false if failure
     */
	function remove( $app_id ) {
		if ( !$ids = $this->get_cart_items() )
			return false;

		$key = array_search( $app_id, $temp );
		if ( false !== $key ) {
			unset( $temp[$key] );
			wpb_set_session_val('app_cart', $temp);
			return true;
		}

		return false;
	}
	
	/**
     * Deletes cart contents and related apps from DB
	 * @return none
     */
	function empty_cart() {
		if( !$ids = $this->get_cart_items() )
			return false;
		
		$result = false;
		foreach ( $ids as $app_id ) {
			if ( $this->delete_item( $app_id ) )
			$result = true;
		}
		
		do_action( 'app_cart_emptied', wpb_get_session_val('app_cart') );
		wpb_set_session_val('app_cart', null);
		if ( $result )
			$this->a->adjust_auto_increment();
		
		return $result;
	}
	
	/**
     * Removes cart item from cart and related app from DB
	 * @param $val: 
	 * @return bool
     */
	function remove_item( $val ) {
		$slot		= new WpBSlot( $val );
		$app_id		= $slot->get_app_id();

		if ( !$app_id )
			return false;
		// TODO: What if Delete a package parent?

		return $this->delete_item( $app_id );
	}

	/**
     * Removes cart item from cart and related app from DB
	 * @return $val string if success, false if failure
     */
	function delete_item( $app_id ) {
		if ( $this->a->db->query( $this->a->db->prepare("DELETE FROM " .$this->a->app_table. " WHERE status='cart' AND ID=%d LIMIT 1", $app_id ) ) ) {
			$this->a->db->query( $this->a->db->prepare("DELETE FROM " .$this->a->meta_table. " WHERE object_id=%d AND meta_type='app'", $app_id ) );
			if ( $temp = wpb_get_session_val('app_cart') ) {
				if ( is_array( $temp ) ) {
					$key = array_search( $app_id, $temp );
					if ( false !== $key ) {
						unset( $temp[$key] );
						wpb_set_session_val('app_cart', $temp);
					}
				}
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Minimum number of appointments that can be booked
	 * @param $value_arr	null|array		null or an array "packed" strings which can be directly used to instantiate WpBSlot class
	 * @return integer
	 */		
	function get_apt_count_min( $value_arr=null ) {
		return apply_filters( 'app_apt_count_min', wpb_setting( "apt_count_min", 1), $value_arr );
	}
	
	/**
	 * Maximum number of appointments that can be booked
	 * @param $value_arr	null|array		null or an array of "packed" strings which can be directly used to instantiate WpBSlot class
	 *										for an example usage see cart_contents_html method
	 * @since 3.0
	 * @return integer
	 */		
	function get_apt_count_max( $value_arr ) {
		return apply_filters( 'app_apt_count_max', wpb_setting( "apt_count_max", 0), $value_arr );
	}

	/**
     * Check max limit before continuing any further
	 * 
     */
	function check_pre( $value_arr ) {
		
		$app_count_max = $this->get_apt_count_max( $value_arr );
		
		if ( $app_count_max && apply_filters( 'app_multiple_app_count', count( $value_arr ), $value_arr ) > $app_count_max ) {				
			die( json_encode( array("error"=>apply_filters( 'app_limit_exceeded_message', sprintf( esc_js( $this->a->get_text('limit_exceeded') ), $app_count_max )),
									"remove_last" => 1,
					)
				));
		}
	}
	
	/**
     * Check min and max limit before save
     */
	function check_post( $value_arr ) {

		$this->check_pre( $value_arr );
		
		$app_count_min = $this->get_apt_count_min( $value_arr );
		
		if ( apply_filters( 'app_multiple_app_count', count( $value_arr ), $value_arr ) < $app_count_min ) {			
			die( json_encode( array("error"=>apply_filters( 'app_too_less_message', sprintf( esc_js( $this->a->get_text('too_less') ), $app_count_min )))));
		}
	}

	/**
     * Get remaining time (Time left to checkout a cart before it is deleted)
	 * @param $ids: An array of app_id's in the cart
	 * @return integer (time in seconds)
     */
	function remaining_time( $ids=null ) {
		$ids = null === $ids ? $this->get_cart_items() : $ids;
		if ( empty( $ids ) || !is_array( $ids ) )
			return 0;
		
		$cdown = wpb_setting("countdown_time");	
		if ( $cdown > 0 )
			$clear_secs = $cdown * 60;
		else
			return 0;

		$query = 
			"SELECT created
			FROM {$this->a->app_table}
			WHERE created>'" . date ("Y-m-d H:i:s", $this->a->_time - (int)$clear_secs ). "'
			AND ID IN (". implode( ',', $ids ) .")
			AND (status='cart' OR status='hold')		
			ORDER BY created ASC
			LIMIT 1
			";
			
		$created = $this->a->db->get_var( $query );
		
		if ( !$created )
			return 0; 
		else
			return strtotime( $created ) - current_time('timestamp') + $clear_secs;
	}
	
	/**
     * Add remaining time and cart contents to pre confirmation output array
	 * @since 2.0
	 * @return array
     */
	function pre_confirmation( $reply_array, $value_arr ) {
		if ( !isset( $reply_array['remaining_time'] ) ) {
			$reply_array['remaining_time'] = $this->remaining_time();
		}
		
		if ( !empty( $value_arr ) && count( $value_arr ) > 1 )
			$reply_array['cart_contents'] = $this->cart_contents_html( $value_arr );
		
		return $reply_array;
	}
	
	/**
     * Generate html for cart contents
	 * @since 3.0
	 * @return string
     */
	function cart_contents_html( $value_arr ) {
		$value_arr = apply_filters( 'app_cart_content_items', $value_arr );
		
		if ( wpb_is_hidden('details') )
			return '';
		
		$cart = array();
		foreach ( $value_arr as $val  ) {
			$cart[] = $val instanceof WpBSlot ? $val : new WpBSlot( $val );
		}
		
		$cart_keys = array_keys( $cart );	# For PHP7.2 compliance
		
		if ( count( $cart_keys ) > 1 ) {
			$starts = $services = array();
			foreach( $cart as $key=>$r ) {
				$starts[$key] = $r->get_start();
				$services[$key] = $r->get_service();
			}
			
			array_multisort( $services, $starts, $cart );
		}
	
		$is_next_day = false;
		$html = '<label><span class="app-conf-title">'.$this->a->get_text('details'). '</span>';
		$html .= '<dl>';
		foreach ( $cart as $key => $r ) {
			
			$pax = apply_filters( 'app_pax', 1, $r->get_service(), $r );
			$pax_text = $pax > 1 ? ' ('. $pax. ' '. $this->a->get_text('pax') . ')' : ''; 
			
			$start_end = $this->format_start_end( $r ) . $pax_text;
			
			if ( strpos( $start_end, '<sup' ) !== false )
				$is_next_day = true;
			
			if ( !isset( $services[$key-1] ) || $services[$key] != $services[$key-1] )
				$html .= '<dt>' . $this->a->get_service_name( $r->get_service() ) . '</dt>';

			$html .= '<dd  data-value="'.$r->pack().'">';
			
			# Make this false to disable remove from cart/details
			if ( apply_filters( 'app_cart_allow_remove', true, $r, $cart ) ) {
				$html .= '<a class="app-remove-cart-item" data-value="'.$r->pack().'" data-app_id="'.$r->get_app_id().'" href="javascript:void(0)" title="'.esc_attr($this->a->get_text('click_to_remove')).'">';
				$html .= '<em class="app-icon icon-trash"></em></a>';
			}
			else {
				$html .= '<em class="app-icon icon-clock"></em></a>';
			}
			$html .= $start_end;
			$html .= '</dd>';
		}
		if ( $is_next_day )
			$html .= '<dd class="app-next-day-note"><sup> *</sup>'.$this->a->get_text('next_day').'</dd>';
		
		$html .= '</dl>';
		$html .= '</label>';
		
		return $html;
	}

	/**
     * Show both start and end date/times in a space saving way
	 * @since 3.0
	 * @return string
     */
	function format_start_end( $r ) {
		$hours = intval( ceil( ($r->get_end() - $r->get_start())/9600 ) );
		
		if ( $this->a->is_daily( $r->get_service() ) || $hours >=24 ) {
			return $this->a->format_start_end( $r->get_start(), $r->get_end() - 1 );
		}
		else {
			$is_next_day = date( 'Y-m-d', $r->get_start() ) == date( 'Y-m-d', $r->get_end()-1 ) ? false : true; # Don't warn 00:00 as next day
			$end_hour = date( $this->a->time_format, $r->get_end() );
			if ( '00:00' === $end_hour && 'H:i' == $this->a->time_format )
				$end_hour = '24:00';
			$result = date_i18n( $this->a->dt_format, $r->get_start() ) .' - '. $end_hour;
			if ( $is_next_day )
				$result = $result . '<sup class="app-next-day"> *</sup>';
			return $result;
		}
	}
	
	/**
     * Find max end timestamp including children (latest appt)
	 * @since 3.0
	 * @return integer
     */
	function find_end_max( $r ) {
		$child_end_max = strtotime( $r->end );
		if ( !empty( $r->parent_id ) || !$childs = $this->get_children( $r->ID ) )
			return $child_end_max;
		
		foreach ( $childs as $child ) {
			$child_end = strtotime( $child->end );
			if ( $child_end > $child_end_max )
				$child_end_max = $child_end;
		}
		
		return $child_end_max;
	}
	
	/**
     * Replace email placeholders
	 * @return string
     */
	function email_replace( $text, $r, $context ) {
		
		if ( !($this->is_active() && $childs = $this->get_children( $r->ID )) )
			return $text;
	
		$timezone = null;
		$timezones_enabled = BASE('Timezones') && BASE('Timezones')->is_enabled() ? true : false;
		if ( $timezones_enabled && $maybe_timezone = wpb_get_app_meta( $r->ID, 'timezone' ) )
			$timezone = $maybe_timezone;
		
		$format = $this->a->is_daily( $r->service ) ? $this->a->date_format : $this->a->dt_format;
		
		$parent_start_ts = strtotime( $r->start );
		$parent_end_ts = strtotime( $r->end ); 
		$child_end_max = 0;
		$nof_apps = 1; // Number of appointments
		$duration = $parent_end_ts - $parent_start_ts;
		
		/* Format Created, Start and End */
		// Find childs, if any
		if ( !preg_match( '/(reminder|follow_up|subject)/', $context ) ) {
			$start = $server_start = '';
			$serv_ids = array( $r->service );
			$worker_names = array( $this->a->get_worker_name( $r->worker ) );
			
			foreach ( $childs as $child ) {
				
				$child_start = strtotime( $child->start );
				$child_end = strtotime( $child->end );
				if ( $child_end > $child_end_max )
					$child_end_max = $child_end;	// Find max end timestamp
				
				$duration += $child_end - $child_start;

				// Do not list internal services
				if ( $this->a->is_internal( $child->service ) )
					continue;
				
				$nof_apps++;
				$serv_ids[] = $child->service;
				$worker_names[] = $this->a->get_worker_name( $child->worker );

				$timezone_c = null;
				if ( $timezones_enabled && $maybe_timezone = wpb_get_app_meta( $child->ID, 'timezone' ) )
					$timezone_c = $maybe_timezone;

				$format_c = $this->a->is_daily( $child->service ) ? $this->a->date_format : $this->a->dt_format;
				$start .= date_i18n( $format_c, $this->a->client_time( strtotime($child->start), $timezone_c ) ) . ' / ';
				$server_start .= date_i18n( $format_c, strtotime($child->start) ) . ' / ';
			}
			
			$start .= date_i18n( $format, $this->a->client_time( strtotime($r->start), $timezone ) ) . ' / '; // Start time is that of parent, because it starts first
			$start = apply_filters( 'app_multi_start', rtrim( $start, ' / ' ), $r, $context );
			$server_start .= date_i18n( $format, strtotime($r->start) ) . ' / '; // Start time is that of parent, because it starts first
			$server_start = apply_filters( 'app_multi_start', rtrim( $server_start, ' / ' ), $r, $context, 'server' );
			
			$end = date_i18n( $format, $this->a->client_time( max( $parent_end_ts, $child_end_max ), $timezone ) );
			$server_end = date_i18n( $format, max( $parent_end_ts, $child_end_max ) );

			$serv_ids = apply_filters( 'app_multi_service_ids', array_unique( $serv_ids ), $r );

			$service_names = array_unique( array_map( array($this->a, 'get_service_name'), $serv_ids ) );
			sort( $service_names );
			$service_name = implode( ", ", $service_names );

			$worker_names = array_unique( $worker_names );
			sort( $worker_names );
			$worker_name = implode( ", ", $worker_names );
			
			$text = str_replace( array("WORKER", "SERVICE", "SERVER_END_DATE_TIME", "SERVER_DATE_TIME", "END_DATE_TIME", "DATE_TIME", "DURATION", "NOF_APPS"),
								array($worker_name, $service_name, $server_end, $server_start, $end, $start, wpb_format_duration( intval($duration/60) ), $nof_apps),
								$text
								);
		}

		// TODO: Re-enable rest of the code again
		return $text;
		
		$details = $this->get_app_job_details( $r );
		
		if ( empty( $details ) )
			return $text;
		
		foreach( array('next_jobs','completed_jobs','cancelled_jobs','all_jobs') as $what ) {
			$replace = '';
			if ( isset( $details[$what] ) && strpos( $text, strtoupper($what) ) !== false ) {
				$app_ids = $details[$what];
				foreach( $app_ids as $app_id ) {
					$app = $this->a->get_app( $app_id, true );
					if ( !empty( $app->start ) )
						$replace .= date_i18n( $this->a->dt_format, strtotime( 'Y-m-d H:i:s', $app->start ) ) . ' / ';
				}
				$text = str_replace( strtoupper($what), rtrim( $replace, ' / ' ), $text );
				unset( $details[$what] );
			}
		}
		
		$keys = array_map( 'strtoupper', array_keys( $details ) );
		$values = array_values( $details );
		
		$text = str_replace( $keys, $values, $text );
		
		return $text;
	}

/****************************************************
* Methods for admin
*****************************************************
*/	

	/**
	 *	Add "MA" tab
	 */
	function add_tab( $tabs ) {
		if ( !$this->is_active() )
			return $tabs;
		
		$temp['multiple'] = __('Multiple Appointments', 'wp-base');
		$tabs = array_merge( $temp, $tabs );
		return $tabs;
	}

	/**
     * Allow jobs_ to be added to columns in Manage Bookings & List Shortcode
     */
	function allowed_columns( $allowed, $what ) {
		if ( $this->is_active() && !isset( $allowed['nof_jobs_total'] ) ) {
			$allowed[] = 'nof_jobs_total';
			$allowed[] = 'nof_jobs_completed';
			$allowed[] = 'nof_jobs_cancelled';
			$allowed[] = 'nof_jobs_remaining';
		}
		return $allowed;
	}

	/**
     * Add extra to columns in Manage Bookings
     */
	function default_columns( $default ) {
		if ( $this->is_active() && strpos( $default, 'nof_jobs_total' ) === false )
			$default = rtrim( $default, ",") . ",nof_jobs_total,nof_jobs_completed,nof_jobs_cancelled,nof_jobs_remaining";
		return $default;
	}

	/**
     * Add extra column as hidden to Manage Bookings if there is no selection before
     */
	function default_hidden_columns( $hidden, $screen ) {
		if ( !($this->is_active() && isset( $screen->id ) && 'toplevel_page_appointments' == $screen->id) )
			return $hidden;
		
		$jobs_arr = array( 'nof_jobs_total','nof_jobs_completed','nof_jobs_cancelled','nof_jobs_remaining' );
		$hidden = is_array( $hidden ) ? array_merge( $hidden, $jobs_arr ) : $jobs_arr;
		
		return $hidden;
	}

	/**
     * Get number and start dates of jobs_total, etc for an app
	 * @return array
     */
	function get_app_job_details( $app ) {
		$app_id = empty( $app->ID ) ? 0 : $app->ID; 
		$identifier = wpb_cache_prefix() . 'app_job_details_' . $app_id;
		$details = wp_cache_get( $identifier );
		
		if ( $details === false ) {
			$details = array();
			# Main is only set for package parent - But what if package parent is a child of a multi?
			if ( $this->a->is_app_package( $app_id ) || $this->a->is_recurring( $app_id ) || wpb_get_app_meta( $app_id, 'marketpress') || wpb_get_app_meta( $app_id, 'woocommerce') ) {
				$completed = $cancelled = $remaining = array();
				$parent = !empty( $app->parent_id ) ? array( $app->parent_id => $this->a->get_app( $app->parent_id ) ) : array();
				
				$me = array( $app_id => $this->a->get_app( $app_id, true ) );
				
				if ( empty( $parent ) ) {
					# This is a parent itself
					$children = $this->get_children( $app_id );
					$related = array_merge( (array)$children, $me  );
				}
				else {
					# This is a child
					$siblings = $this->get_siblings( $app_id );
					$children = $this->get_children( $app_id );
					$related = array_merge( (array)$siblings, (array)$children, $parent, $me );
				}
				
				$related = array_filter( $related );
				
				if ( !empty( $related ) ) {
					foreach ( $related as $r ) {
						// TODO: What if this is a multiple appointment of packages?
						if ( 'completed' == $r->status )
							$completed[] = $r->ID;
						else if ( 'removed' == $r->status )
							$cancelled[] = $r->ID;
						else
							$remaining[] = $r->ID;
					}
				}
				
				$details = array(
						'nof_jobs_completed'=> count( $completed ),
						'nof_jobs_cancelled'=> count( $cancelled ),
						'nof_jobs_remaining'=> count( $remaining ),
						'nof_jobs_total'	=> count( $completed ) + count( $cancelled ) + count( $remaining ),
						'completed_jobs'	=> $completed,
						'cancelled_jobs'	=> $cancelled,
						'remaining_jobs'	=> $remaining,
						'all_jobs'			=> $related,
					);
			}
			
			wp_cache_set( $identifier, $details );
		}
			
		return $details;
	}

	/**
     * Add cell content to Manage Bookings & List Shortcode
     */
	function add_cell( $cell, $col, $app, $args ) {
		if ( substr( $col, 0, 9 ) != 'nof_jobs_' )
			return $cell;
		
		if ( $cell )
			return $cell;
		
		$details = $this->get_app_job_details( $app );
		
		$out = !empty( $details[$col] ) ? $details[$col] : ' - ';
		return $cell . $out;
	}

	/**
     * Modify $app_id in admin emails and add children, so that all of them are searched
     */
	function app_id_email( $app_id ) {
		$add = '';
		if ( $childs = $this->get_children( $app_id ) ) {
			foreach ( $childs as $child ) {
				$add .= '+'. $child->ID;
			}
		}
		
		return $app_id . $add;
	}

	/**
     * Modify $app_id in admin search to add parent, siblings and children
     */
	function app_search( $s, $stype ) {
		if ( 'app_id' != $stype )
			return $s;
		
		$app_id = $s;
		$app = $this->a->get_app( $app_id );
		if ( empty( $app->ID ) )
			return $s;
		
		$add_arr = array();
		if ( $childs = $this->get_children( $app_id ) ) {
			foreach ( $childs as $child ) {
				$add_arr[] = $child->ID;
			}
		}
		if ( $app->parent_id ) {
			$add_arr[] = $app->parent_id;
			// Find siblings
			if ( $childs = $this->get_children( $app->parent_id ) ) {
				foreach ( $childs as $child ) {
					$add_arr[] = $child->ID;
				}
			}
		}
		$add_arr = array_unique( array_filter( $add_arr ) );
		rsort( $add_arr );
		$add = implode( ' ', $add_arr );
		
		return $s . ' '. $add;
	}

	/**
     * Add settings html
     */
	function settings() {
		?>
			<div class="postbox postbox-advanced">
			<h3 class="hndle" id="multiple"><span class="dashicons dashicons-admin-generic"></span><span><?php _e('Common MA Settings', 'wp-base'); ?></span></h3>
			<div class="inside">
				<table class="form-table">

				<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('countdown_time') ?></th>
					<td><input type="text" style="width:50px" name="countdown_time" value="<?php if ( wpb_setting("countdown_time") ) echo wpb_setting("countdown_time") ?>" />
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('countdown_time') ?></span>
				</tr>

				<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('deposit_cumulative') ?></th>
					<td>
					<select name="deposit_cumulative">
					<option value="no" <?php if ( wpb_setting('deposit_cumulative') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
					<option value="yes" <?php if ( wpb_setting('deposit_cumulative') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
					</select>
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('deposit_cumulative') ?></span>
					</td>
				</tr>

				<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('apt_count_max') ?></th>
					<td><input type="text" style="width:50px" name="apt_count_max" value="<?php if ( wpb_setting("apt_count_max") ) echo wpb_setting("apt_count_max") ?>" />
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('apt_count_max') ?></span>
				</tr>
				
				<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('apt_count_min') ?></th>
					<td><input type="text" style="width:50px" name="apt_count_min" value="<?php if ( wpb_setting("apt_count_min") ) echo wpb_setting("apt_count_min") ?>" />
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('apt_count_min') ?></span>
				</tr>

				</table>
			</div>
			</div>


	<?php
	}
	
	/**
     * Save settings
     */
	function save_settings() {
		
		if ( !isset( $_POST["countdown_time"] ) ) {
			return;
		}
		
		$options = wpb_setting();
		$options["countdown_time"]			= $_POST["countdown_time"];
		$options["deposit_cumulative"]		= $_POST["deposit_cumulative"];
		$options["apt_count_max"]			= preg_replace("/[^0-9]/", "", $_POST["apt_count_max"] );
		$options["apt_count_min"]			= preg_replace("/[^0-9]/", "", $_POST["apt_count_min"] );
		
		if ( $this->a->update_options( $options ) ) {
			wpb_notice( 'saved' );
		}
	}
	
}

	BASE('Multiple')->add_hooks();
}