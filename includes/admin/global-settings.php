<?php
/**
 * WPB Admin Global Settings
 *
 * Displays and manages global settings on admin side
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBGlobalSettings' ) ) {
	
class WpBGlobalSettings{

	/**
     * WP BASE Core + Front [+Admin] instance
     */
	protected $a = null;

	/**
     * Constructor
     */
	function __construct(){
		$this->a = BASE();
	}

	/**
     * Add admin actions
     */
	function add_hooks() {
		add_action( 'app_submenu_before_tools', array( $this, 'add_submenu' ), 12 );
		add_action( 'app_save_settings', array( $this, 'save_settings' ), 12 );
	}	

	/**
     * Add submenu page to main admin menu
     */
	function add_submenu(){
		add_submenu_page('appointments', __('WPB Global Settings','wp-base'), __('Global Settings','wp-base'), WPB_ADMIN_CAP, "app_settings", array($this,'settings'));
	}
	
	/**
     * Save admin settings
     */
	function save_settings() {

		if ( isset( $_POST['app_nonce'] ) && !wp_verify_nonce($_POST['app_nonce'],'update_app_settings') ) {
			wpb_notice( 'unauthorised', 'error' );
			return;
		}

		$saved = $settings_changed = $flush_cache = false;
		
		$old_options = $options = wpb_setting();
	
		// Save general settings
		if ( 'save_general' == $_POST["action_app"] ) {
			$settings_changed = true;
			$flush_cache = true;
			
			if ( !empty( $_POST["create_make_page"] ) && isset( $_POST["app_page_type"] ) )
				$options["app_page_type"]			= $_POST["app_page_type"];
			
			$this->create_app_page();
			
			$options["refresh_url"]					= trim( $_POST["refresh_url"] );
			# Calculate optimum time base
			if ( 'auto' == $_POST["min_time"] )
				$options['calc_min_time'] 				= $this->a->find_optimum_time_base();
			
			$options["min_time"]					= $_POST["min_time"];
			$options["app_lower_limit"]				= preg_replace( "/[^0-9]/", "", $_POST["app_lower_limit"] );
			$options["app_limit"]					= preg_replace("/[^0-9\-]/", "", $_POST["app_limit"] );
			$options["app_limit_unit"]				= $_POST["app_limit_unit"];
			$options["clear_time"]					= preg_replace("/[^0-9]/", "", $_POST["clear_time"] );
			$options["clear_time_pending_payment"]	= preg_replace("/[^0-9]/", "", $_POST["clear_time_pending_payment"] );
			
			$options["auto_confirm"]				= $_POST["auto_confirm"];
			$options["allow_confirm"]				= $_POST["allow_confirm"];
			$options['allow_cancel'] 				= $_POST['allow_cancel'];
			$options['cancel_limit'] 				= preg_replace( "/[^0-9]/", "", $_POST['cancel_limit'] );
			$options['cancel_page'] 				= isset( $_POST['cancel_page'] ) ? $_POST['cancel_page'] : '';

			$options["debug_mode"]					= $_POST["debug_mode"];
			$options["cache"]						= $_POST["cache"];
			$options["preload_pages"]				= isset($_POST["preload_pages"]) ? implode( ',', $_POST["preload_pages"] ) :'';
			$options["lazy_load"]					= $_POST["lazy_load"];

			$options["preselect_first_service"]		= $_POST["preselect_first_service"];
			$options["lsw_priority"]				= $_POST["lsw_priority"];
			$options["log_settings"]				= $_POST["log_settings"];
			$options["strict_check"]				= $_POST["strict_check"];
			$options["admin_edit_collapse"]			= $_POST["admin_edit_collapse"];
			$options["admin_toolbar"]				= $_POST["admin_toolbar"];
			$options["records_per_page"]			= preg_replace( "/[^0-9]/", "",  $_POST["records_per_page"] );
			$options["records_per_page_business"]	= preg_replace( "/[^0-9]/", "",  $_POST["records_per_page_business"] );
		}
		else if ( 'save_email_settings' == $_POST["action_app"] ) {
			$settings_changed = true;
			// Allow multiple admin emails if comma is used
			$options["admin_email"]					= (strpos( $_POST["admin_email"], ',') || is_email( $_POST["admin_email"] )) ? $_POST["admin_email"] : get_option('admin_email');
			$options["from_name"]					= stripslashes( $_POST["from_name"] );
			$options["from_email"]					= is_email( trim($_POST["from_email"]) ) ?  trim($_POST["from_email"]) : '';
			$options["log_emails"]					= $_POST["log_emails"];
			$options["use_html"]					= $_POST["use_html"];
			
			$options["send_confirmation"]			= $_POST["send_confirmation"];
			$options["send_confirmation_bulk"]		= $_POST["send_confirmation_bulk"];
			$options["send_notification"]			= $_POST["send_notification"];
			$options["confirmation_subject"]		= stripslashes( $_POST["confirmation_subject"] );
			$options["confirmation_message"]		= stripslashes( $_POST["confirmation_message"] );
			
			$options["send_pending"]				= $_POST["send_pending"];
			$options["send_pending_bulk"]			= $_POST["send_pending_bulk"];
			$options["pending_subject"]				= stripslashes( $_POST["pending_subject"] );
			$options["pending_message"]				= stripslashes( $_POST["pending_message"] );

			$options["send_completed"]				= $_POST["send_completed"];
			$options["send_completed_bulk"]			= $_POST["send_completed_bulk"];
			$options["completed_subject"]			= stripslashes( $_POST["completed_subject"] );
			$options["completed_message"]			= stripslashes( $_POST["completed_message"] );

			$options["send_cancellation"]			= $_POST["send_cancellation"];
			$options["send_cancellation_bulk"]		= $_POST["send_cancellation_bulk"];
			$options["cancellation_subject"]		= stripslashes( $_POST["cancellation_subject"] );
			$options["cancellation_message"]		= stripslashes( $_POST["cancellation_message"] );
			
			do_action( 'app_admin_email_settings_maybe_updated' );
			
			if ( isset( $_POST["send_test_email"] ) && 1 == $_POST["send_test_email"] )
				$this->send_test_email( $options["admin_email"] ); // This is the email entered in the setting, NOT the one from get_admin_email()
		}

		
		if ( $settings_changed ) {

			if ( $flush_cache ) {
				wpb_flush_cache( );
				update_user_meta( get_current_user_id(), 'app_service_check_needed', true );
			}
			
			if ( $old_options['cache'] != $options['cache'] && 'preload' === $options['cache'] ) {
				if ( $options["preload_pages"] && false === wpb_maybe_preload_cache( $options["preload_pages"] ) ) {
					add_action( 'admin_notices', array( $this, 'remote_get_error' ) );
					# Revert to old setting
					$options['cache'] = $old_options['cache'];
				}
			}
			
			if ( $this->a->update_options( $options ) )
				wpb_notice( 'saved' );
			
		}
	}
	
	/**
	 * Send a test email
	 * @since 2.0
	 */		
	function send_test_email( $admin_email ) {

		$r = $this->a->test_app_record();
		
		if ( empty( $r->service ) )
			return;
		
		$body = $this->a->_replace( wpb_setting("confirmation_message"), $r, 'test_message' );
		
		// Find who will get this test email
		if ( $admin_email ) {
			$temp = explode( ',', $admin_email );
			foreach( $temp as $email ) {
				$e = trim( $email );
				if ( is_email( $e ) )
					$to[] = $e;
			}
		}
		else
			$to = BASE('User')->get_admin_email( );
		
		// Send the email
		$mail_result = wp_mail( 
			$to,
			$this->a->_replace( __('This is a test email of WP BASE', 'wp-base'), $r, 'test_subject' ),
			$body, 
			$this->a->message_headers( 'send_test_email', $r ),
			apply_filters( 'app_email_attachment', array(), $r, 'test' ) 
		);
		
		// Result		
		if ( $mail_result ) {
			add_action( 'admin_notices', array( $this, 'test_email_success' ) );
			do_action( 'app_email_sent', $body, $r, 0, 'test' );
			return true;
		}
		else {
			add_action( 'admin_notices', array( $this, 'test_email_fail' ) );
			do_action( 'app_email_failed', $body, $r, 0, 'test' );
			return false;
		}
	}
	
	/**
	 * Prints "Test email successful" message on top of Admin page 
	 * @since 2.0
	 */
	function test_email_success( ) {
		echo '<div class="updated app-dismiss"><p><b>[WP BASE]</b> '. __('Test email has been successfully sent. Please wait a few minutes and check first admin email account.','wp-base').
		'</p><a class="notice-dismiss" data-what="general" title="'.__('Dismiss this notice', 'wp-base').'" href="javascript:void(0)"></a></div>';
	}

	/**
	 * Prints "Test email failed" message on top of Admin page 
	 * @since 2.0
	 */
	function test_email_fail( ) {
		echo '<div class="error app-dismiss"><p><b>[WP BASE]</b> '. __('Sending the test email has failed. Please check if you can actually send any emails from this website. For example, logout and try to receive an email using "Lost your password?" link of WordPress admin login page.','wp-base').
		'</p><a class="notice-dismiss" data-what="general" title="'.__('Dismiss this notice', 'wp-base').'" href="javascript:void(0)"></a></div>';
	}

	/**
	 * wp_remote_get is not working message
	 * @since 2.0
	 */
	function remote_get_error( ) {
		echo '<div class="error app-dismiss"><p><b>[WP BASE]</b> '. __('This website cannot use wp_remote_get function to retrieve data from itself. Preload function cannot be enabled.','wp-base').
		'</p><a class="notice-dismiss" data-what="general" title="'.__('Dismiss this notice', 'wp-base').'" href="javascript:void(0)"></a></div>';
	}

	/**
	 *	When Time Base setting has been changed, reset/reschedule the scheduled event
	 *	When caching setting changed accordingly, make a wp_remote_get test
	 *	@since 3.0
	 */	
	function settings_changed( $old_options, $options ) {
		if ( $old_options['min_time'] != $options['min_time'] )
			wp_reschedule_event( strtotime( current_time( 'Y-m-d' ) ) - 24*3600, 'wpb_time_base_tick', 'app_time_base_tick' );
		

	}

	/**
	 * Create a Make an Appointment/Book a Service Page 
	 */
	function create_app_page( $ecommerce=false ) {
		// Add an appointment page			
		if ( !empty( $_POST["create_make_page"] ) && !empty( $_POST["app_page_type"] ) && isset($_POST["create_page_btn"]) ) {
			
			$monthly 		= '[app_book]';
			$weekly 		= '[app_book type="weekly"]';
			$table_10 		= '[app_book type="table" range="10"]';
			$table_2_days 	= '[app_book type="table" range="2 days"]';
			$flex_vertical	= '[app_book type="flex" range="2weeks" mode="1"]';
			$flex_hor		= '[app_book type="flex" range="10days" mode="5" from_week_start="0"]';
			
			$shortcode = trim( ${$_POST["app_page_type"]} );

			if ( $ecommerce ) {
				if ( 'woocommerce' == $ecommerce ) {
					$post_type = 'product';
					$post_excerpt = $shortcode;
					$post_content = '&nbsp;';
				}
				else if ( 'marketpress' == $ecommerce ) {
					$post_type = class_exists('MP_Product') ? MP_Product::get_post_type() : 'product';
					$post_excerpt = '';
					$post_content = $shortcode;
				}
			}
			else {
				$post_type = 'page';
				$post_excerpt = '';
				$post_content = $shortcode;
			}
			
			$page_title = $ecommerce ? __( 'Book a Service', 'wp-base' ) : __( 'Make an Appointment', 'wp-base' );
			

			if ( $this->a->created_page_id = 
					wp_insert_post( 
						array(
							'post_title'	=> $page_title,
							'post_status'	=> 'publish',
							'post_type'		=> $post_type,
							'post_content'	=> $post_content,
							'post_excerpt'	=> $post_excerpt,
						)
					)
			) {
				add_action( 'admin_notices', array ( $this->a, 'page_created' ) );
				if ( isset( $_POST["list_of_bookings"] ) ) {
					if( $this->a->created_list_page_id = 
						wp_insert_post( 
							array(
								'post_title'	=> 'List of Bookings',
								'post_status'	=> 'publish',
								'post_type'		=> 'page',
								'post_content'	=> '[app_list]'
							)
						)
						
					) {
						add_action( 'admin_notices', array ( $this->a, 'page_created_list' ) );
						if ( isset( $_POST["set_refresh_url"] ) )
							$options["refresh_url"] = get_permalink( $this->a->created_list_page_id );
					}
				}
				return $this->a->created_page_id;
			}
		}		
	}
	
	/**
	 * HTML for create a Make an Appointment/Book a Service Page 
	 */
	function create_app_page_html( $ecommerce=false ) {
		
		$page_name = $ecommerce ? __( 'Book a Service', 'wp-base' ) : __( 'Make an Appointment', 'wp-base' );
		$link =  $ecommerce ? '<a href="'.admin_url('admin.php?page=app_settings#refresh-url').'">'. __( 'here', 'wp-base' ).'</a>' : __( 'below', 'wp-base' );
		$ptype = wpb_setting("app_page_type"); 
		
		?>
		<select name="app_page_type" class="app_no_save_alert" >
			<option value="monthly" <?php selected( 'monthly', $ptype ) ?>><?php _e('Monthly calendar - 2 months', 'wp-base')?></option>
			<option value="weekly" <?php selected( 'weekly', $ptype ) ?>><?php _e('Weekly calendar - 2 weeks', 'wp-base')?></option>
			<option value="table_10" <?php selected( 'table_10', $ptype ) ?>><?php _e('Table view - 10 booking rows', 'wp-base')?></option>
			<option value="table_2_days" <?php selected( 'table_2_days', $ptype ) ?>><?php _e('Table view - 2 days', 'wp-base')?></option>
			<?php if ( class_exists( 'WpBPro' ) ) { ?>
				<option value="flex_vertical" <?php selected( 'flex_vertical', $ptype ) ?>><?php _e('Flex view - Vertical, 2 weeks', 'wp-base')?></option>
				<option value="flex_hor" <?php selected( 'flex_hor', $ptype ) ?>><?php _e('Flex view - Horizontal, 10 days', 'wp-base')?></option>
			<?php } ?>
		</select>
		<input type="submit" class="app_no_save_alert button-secondary" name="create_page_btn" value="<?php _e('Create Page Now','wp-base') ?>"/>
		
		<?php /* Value of the following field determines what page type we will create */ ?>
		<input type="hidden" class="app_no_save_alert" name="create_make_page" value="<?php echo ($ecommerce ? $ecommerce : 1) ?>"/>

		<div class="app_mtmb">
			<input type="checkbox" name="list_of_bookings" class="app_no_save_alert" <?php checked( wpb_setting('list_page'), false ) ?>/>
			<?php _e('Also create a List of Bookings Page', 'wp-base' ) ?>
			
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="set_refresh_url" class="app_no_save_alert" <?php checked( wpb_setting('refresh_url'), false ); if ( wpb_setting('refresh_url') ) echo " disabled='disabled'" ?> />
			<?php _e('Also redirect client to this List of Bookings Page', 'wp-base' ) ?>
		</div>
		
		<span class="description app_bottom">
		<?php printf( __('Immediately creates a front end booking page with title "%s" with the selected booking layout by inserting <code>[app_book]</code> shortcode with required parameters. You can edit, add parameters to the shortcode and customize this page later.', 'wp-base'), $page_name ); ?>
		<?php printf( __('For layout examples, please visit our %s.','wp-base'), '<a href="'.WPB_DEMO_WEBSITE.'" target="_blank">'. __('demo website', 'wp-base'). '</a>'); ?>
		<?php _e('Checking "Create a List of Bookings Page" checkbox will additionally create a page with title "List of Bookings" with <code>[app_list]</code> shortcode.', 'wp-base'); ?>
		<?php printf( __('Checking "Redirect client..." checkbox will automatically fill the Redirect Url field %s.', 'wp-base'), $link ); ?>
		</span>
		<?php
		$existing_page_id = $this->a->first_app_page_id( $page_name );
		if ( $existing_page_id ) { ?>
			<br />
			<span class="description app_bottom"><?php printf( __('<b>Note:</b> You already have a "%s" page. If you click Create page Now button, another page with the same title will be created. To edit existing page:' , 'wp-base'), $page_name ); ?>
			<a class="app_bottom" href="<?php echo admin_url('post.php?post='.$existing_page_id.'&amp;action=edit')?>" target="_blank"><?php _e('Click here', 'wp-base')?></a>
			&nbsp;
			<?php _e('To view the page:', 'wp-base') ?>
			<a class="app_existing_make_page app_bottom" href="<?php echo get_permalink( $existing_page_id)?>" target="_blank"><?php _e('Click here', 'wp-base')?></a>
			</span>
		<?php }
		?>
	   <script type="text/javascript">
		  jQuery(document).ready(function ($) {
			var page_existing = false;
			
			<?php if ( $existing_page_id ) { echo 'page_existing = true;'; } ?>
			$('input[name="create_page_btn"]').click(function() {
			
				if ( page_existing && !confirm('<?php echo esc_js( __('You already have a Make an Appointment page. If you confirm this box, an additional appointment page with the same title will be created. This will not cause any harm (you can delete that page later on), but are you sure to continue?','wp-base') ) ?>') ) {$(this).attr('checked',false);return false;}
				else{
					// $('input[name="create_make_page"]').val(1);
					$("#app_main_settings_form").submit();
				}
				
			});
			
			$("input[name='list_of_bookings']").change(function(){
				if ( !$(this).is(":checked") ) {
					$("input[name='set_refresh_url']").attr("checked",false).attr("disabled",true);
				}
				else {
					$("input[name='set_refresh_url']").attr("disabled",false);
				}
			});
		  });
		</script>
		<?php 
	}
	
	/**
	 * Admin Global Settings HTML code 
	 */
	function settings() {

		wpb_admin_access_check( 'manage_global_settings' );

	?>
		<div class="wrap app-page">
		<h2 class="app-dashicons-before dashicons-admin-settings"><?php echo __('Global Settings','wp-base'); ?></h2>
		<h3 class="nav-tab-wrapper">
			<?php
			$tab = ( !empty($_GET['tab']) ) ? $_GET['tab'] : 'main';
			
			$tabs = array(
				'email'			=> __('Email', 'wp-base'),
			);
			
			if ( !array_key_exists( 'advanced', $tabs ) && apply_filters( 'app_add_advanced_tab', false ) )
				$tabs = array_merge( array( 'advanced'		=> __('Advanced', 'wp-base') ), $tabs );
			
			$tabhtml = array();

			
			$tabs = apply_filters( 'appointments_tabs', $tabs );

			$class = ( 'main' == $tab ) ? ' nav-tab-active' : '';
			$tabhtml[] = '	<a href="' . admin_url( 'admin.php?page=app_settings' ) . '" class="nav-tab'.$class.'">' . __('General', 'wp-base') . '</a>';

			foreach ( $tabs as $stub => $title ) {
				$class = ( $stub == $tab ) ? ' nav-tab-active' : '';
				$tabhtml[] = '	<a href="' . admin_url( 'admin.php?page=app_settings&amp;tab=' . $stub ) . '" class="nav-tab'.$class.'" id="app_tab_'.$stub.'">'.$title.'</a>';
			}

			echo implode( "\n", $tabhtml );
			
			$wp_editor_settings = array( 'editor_height'=> WPB_EDITOR_HEIGHT, );
			
			?>
		</h3>
		<div class="clear"></div>
		<?php switch( $tab ) {
	/*******************************
	* Main tab
	********************************
	*/
		case 'main':	?>
		
		<div id="poststuff" class="metabox-holder meta-box-sortables">
		<?php wpb_infobox( sprintf( __('WP BASE plugin makes it possible for your clients to apply for appointments from the front end and for you to enter appointments from backend. On this page, you can set settings which will be valid throughout the website. Please note that some settings can be overridden per page basis by setting appropriate shortcode parameters. For example, user info fields can be set on the Confirmation shortcode overriding settings on this page. If you do not know how to proceed, consider following the %s.', 'wp-base'), $this->a->_a( $this->a->_t('tutorials'), 'admin.php?page=app_help#tutorials', $this->a->_t('Click to access Tutorials on the Help page') ) ) ); ?>
			<form id="app_main_settings_form" class="app_form" method="post" action="<?php echo wpb_add_query_arg( null, null )?>">
			
			<div id="wpb-quick-start" class="postbox <?php echo postbox_classes('wpb-quick-start', wpb_get_current_screen_id( true ) ) ?>">
				<button type="button" class="handlediv" aria-expanded="true">
					<span class="screen-reader-text"><?php  _e( 'Toggle panel: Quick Start' ) ?></span>
					<span class="toggle-indicator" aria-hidden="true"></span>
				</button>
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<h3 class="hndle"><span class="dashicons dashicons-controls-forward"></span><span><?php _e('Quick Start', 'wp-base') ?></span></h3>
				<div class="inside">
					<table class="form-table">
						<tr>
							<th scope="row" ><?php _e('Create a Booking Page', 'wp-base')?></th>
							<td>
								<?php $this->create_app_page_html(); ?>
							</td>
						</tr>
						
						<tr id="refresh-url">
							<th scope="row" ><?php WpBConstant::echo_setting_name('refresh_url') ?></th>
							<td>
							<input value="<?php echo wpb_setting('refresh_url'); ?>" size="60" name="refresh_url" type="text" />
							<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('refresh_url') ?></span>
							</td>
						</tr>
				
					</table>
				</div>
			</div>

		
		<div id="tabs" class="app-tabs">
			<ul></ul>
		
			<div class="postbox">
				<h3 class="hndle"><span class="dashicons dashicons-clock"></span><span><?php _e('Time Settings', 'wp-base') ?></span></h3>
				<div class="inside">
					<table class="form-table">
					
						<tr id="time-base">
							<th scope="row" ><?php WpBConstant::echo_setting_name('min_time') ?></th>
							<td>
							<select name="min_time">
							<?php
							if ( 'auto' == wpb_setting("min_time") ) {
								if ( wpb_setting("calc_min_time") ) {
									$ctext = wpb_readable_duration($this->a->get_min_time());
									$class = '';
								}
								else {
									$ctext = __('Not possible to calculate. 60 mins is in effect.','wp-base');
									$class = " class='error'";
								}
							}
							else
								$class = '';
							
							echo '<option '.$class.' value="auto">'. __('Auto','wp-base') . '</option>';
							foreach ( $this->a->time_base() as $min_time ) {
								if ( wpb_setting("min_time") == $min_time )
									$s = ' selected="selected"';
								else
									$s = '';
								echo '<option value="'.$min_time .'"'. $s . '>'. wpb_readable_duration($min_time) . '</option>';
							}
							?>
							</select>
							<?php
							if ( 'auto' == wpb_setting("min_time") ) {
								echo '<span class="description app_bottom app_b">'. __('Calculated value: ','wp-base') . $ctext .'</span>  ';
							}
							?>
							<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('min_time') ?></span>
							</td>
						</tr>
						
						<tr id="app-lower-limit">
							<th scope="row" ><?php WpBConstant::echo_setting_name('app_lower_limit') ?></th>
							<td><input type="text" class="app_input_with_select app_50" name="app_lower_limit" value="<?php echo wpb_setting("app_lower_limit") ?>" />
							<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('app_lower_limit') ?></span>
							</td>
						</tr>
							<?php
							$limit = wpb_setting("app_limit");
							$limit_unit = wpb_setting('app_limit_unit'); 
							?>
						
						<tr id="app-upper-limit">
							<th scope="row" ><?php WpBConstant::echo_setting_name('app_limit') ?></th>
							<td><input type="text" class="app_input_with_select app_50" name="app_limit" value="<?php echo $limit ?>" />
							<select name="app_limit_unit" class="app_bottom">
								<option value="day" <?php selected( $limit_unit, 'day' ); ?>><?php echo _n('day', 'days', $limit, 'wp-base')?></option>
								<option value="month" <?php selected( $limit_unit, 'month' ); ?>><?php echo _n('month', 'months', $limit, 'wp-base')?></option>
							</select>

							<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('app_limit') ?></span>
							</td>
						</tr>
								
						<tr id="clear-time">
							<th scope="row" ><?php WpBConstant::echo_setting_name('clear_time') ?></th>
							<td><input type="text" class="app_input_with_select app_50" name="clear_time" value="<?php echo wpb_setting("clear_time") ?>" />
							<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('clear_time') ?></span>
							</td>
						</tr>
					
						<tr id="clear-time-pending-payment">
							<th scope="row" ><?php WpBConstant::echo_setting_name('clear_time_pending_payment') ?></th>
							<td><input type="text" class="app_input_with_select app_50" name="clear_time_pending_payment" value="<?php echo wpb_setting("clear_time_pending_payment") ?>" />
							<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('clear_time_pending_payment') ?></span>
							</td>
						</tr>

					</table>
				</div>
			</div>

			<div class="postbox">
            <h3 class="hndle"><span class="dashicons dashicons-tickets-alt"></span><span><?php _e('Booking & Cancelling', 'wp-base') ?></span></h3>
				<div class="inside">
				
					<table class="form-table">
					
					<tr id="auto-confirm">
							<th scope="row" ><?php WpBConstant::echo_setting_name('auto_confirm') ?></th>
							<td>
							<select name="auto_confirm">
							<option value="no" <?php if ( wpb_setting('auto_confirm') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
							<option value="yes" <?php if ( wpb_setting('auto_confirm') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
							</select>
							<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('auto_confirm') 	. '&nbsp;'. _e( 'To display the table summarizing effect of different settings please click the "i" icon:', 'wp-base'); ?></span>
							&nbsp;
							<a href="javascript:void(0)" class="info-button" title="<?php _e('Click to toggle details', 'wp-base')?>">
							<img src="<?php echo $this->a->plugin_url . '/images/information.png'?>" alt="" />
							</a>
							<div class="app-instructions" style="display:none">
								<table class="widefat">
									<tr>
										<th><?php _e( 'Setting of Auto Confirm' ) ?></th>
										<th style="text-align:center" colspan="2"><?php _e( 'Resulting Status When...' ) ?></th>
									</tr>
									<tr>
										<th>&nbsp;</th>
										<th><?php _e( 'Payment Required = NO OR Price = 0' ) ?></th>
										<th><?php _e( 'Payment Required = YES AND Price > 0' ) ?></th>
									</tr>
									<tr>
										<td><?php _e( 'No' ) ?></td>
										<td><?php _e( 'Pending' ) ?></td>
										<td><?php _e( 'Pending&rarr;{Payment}&rarr;Paid' ) ?></td>
									</tr>
									<tr>
										<td><?php _e( 'Yes' ) ?></td>
										<td><?php _e( 'Confirmed' )?></td>
										<td><?php _e( 'Pending&rarr;{Payment}&rarr;Paid' ) ?></td>
									</tr>
								</table>
							</div>
							</td>
						</tr>
						
						<tr>
							<th scope="row" ><?php WpBConstant::echo_setting_name('allow_confirm') ?></th>
							<td>
							<select name="allow_confirm">
							<option value="no" <?php if ( wpb_setting('allow_confirm') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
							<option value="yes" <?php if ( wpb_setting('allow_confirm') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
							</select>
							<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('allow_confirm') ?></span>
							</td>
						</tr>

						<tr>
							<th scope="row" ><?php WpBConstant::echo_setting_name('allow_cancel') ?></th>
							<td>
							<select name="allow_cancel">
							<option value="no" <?php if ( wpb_setting('allow_cancel') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
							<option value="yes" <?php if ( wpb_setting('allow_cancel') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
							</select>
							<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('allow_cancel') ?></span>
							</td>
						</tr>

						<tr id="cancel-limit">
							<th scope="row" ><?php WpBConstant::echo_setting_name('cancel_limit') ?></th>
							<td>
							<input type="text" style="width:50px" name="cancel_limit" value="<?php echo wpb_setting("cancel_limit") ?>" />
							<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('cancel_limit') ?></span>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php WpBConstant::echo_setting_name('cancel_page') ?></th>
							<td>
							<?php 
								if ( $dropdown = wp_dropdown_pages( array( "echo"=>false,"show_option_none"=>__('Home page', 'wp-base'),"option_none_value "=>0,"name"=>"cancel_page", "selected"=>wpb_setting("cancel_page") ) ) )
									echo $dropdown;
								else
									echo '<span class="app_b app_bottom">' . __( 'There are no pages!', 'wp-base' ) .'</span>';
								?>
							<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('cancel_page') ?></span>
							</td>
						</tr>
						
					</table>
				</div>
			</div>
			
			<?php do_action( 'app_admin_settings_after_booking' ) ?> 
	
			<div class="postbox">
				<h3 class="hndle"><span class="dashicons dashicons-performance"></span><span><?php _e('Performance', 'wp-base') ?></span></h3>
					<div class="inside">
					
					<table class="form-table">

					<tr>
						<th scope="row" ><?php WpBConstant::echo_setting_name('debug_mode') ?></th>
						<td>
						<select name="debug_mode">
						<option value="no" <?php if ( wpb_setting('debug_mode') <> 'yes' ) echo "selected='selected'"?>><?php _e('Off', 'wp-base')?></option>
						<option value="yes" <?php if ( wpb_setting('debug_mode') == 'yes' ) echo "selected='selected'"?>><?php _e('On', 'wp-base')?></option>
						</select>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('debug_mode') ?></span>
						</td>
					</tr>

					<tr>
						<th scope="row" ><?php WpBConstant::echo_setting_name('cache') ?></th>
						<td>
						<select name="cache">
						<option value="" <?php if ( !wpb_setting('cache') ) echo "selected='selected'"?>><?php _e('Off', 'wp-base')?></option>
						<option value="on" <?php if ( wpb_setting('cache') == 'on' ) echo "selected='selected'"?>><?php _e('On', 'wp-base')?></option>
						<option value="preload" <?php if ( wpb_setting('cache') == 'preload' ) echo "selected='selected'"?>><?php _e('On + Preload', 'wp-base')?></option>
						</select>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('cache') ?></span>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><?php WpBConstant::echo_setting_name('preload_pages') ?></th>
						<td>
						<?php
							$selected_pages = explode( ',', wpb_setting("preload_pages") );
							$make_pages = BASE()->get_app_pages();
							
							if ( is_array( $make_pages ) ) {
								echo '<div style="float:left"><select class="preload_pages" multiple="multiple" name="preload_pages[]" >';
								foreach ( $make_pages as $page ) {
									$s = is_array( $selected_pages ) && in_array( $page->ID, $selected_pages ) ? ' selected="selected"' : '';
									echo '<option value="'.$page->ID.'"' . $s . '>'. stripslashes($page->post_title) . '</option>';
								}
								echo '</select></div>';
							}
							else
								echo '<input type="text" size="40" value="'. __('No Make an Appointment page found','wp-base').'" readonly="readonly" />';
						?>
						<div style="float:left;width:80%;margin-left:5px;">
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('preload_pages') ?></span>
						</div>
						<div style="clear:both"></div>
						</td>
					</tr>

					<tr>
						<th scope="row" ><?php WpBConstant::echo_setting_name('lazy_load') ?></th>
						<td>
						<select name="lazy_load">
						<option value="no" <?php if ( !wpb_setting('lazy_load') ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
						<option value="yes" <?php if ( wpb_setting('lazy_load') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
						</select>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('lazy_load') ?></span>
						</td>
					</tr>
				</table>
				</div>
			</div>					
					
					
			<div class="postbox">
				<h3 class="hndle"><span class="dashicons dashicons-forms"></span><span><?php _e('Preferences', 'wp-base') ?></span></h3>
					<div class="inside">
					
					<table class="form-table">
					
					<tr>
						<th scope="row" ><?php WpBConstant::echo_setting_name('preselect_first_service') ?></th>
						<td>
						<select name="preselect_first_service">
						<option value="no" <?php if ( wpb_setting('preselect_first_service') == 'no' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
						<option value="yes" <?php if ( wpb_setting('preselect_first_service') != 'no' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
						</select>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('preselect_first_service') ?></span>
						</td>
					</tr>				
					
					<tr>
						<th scope="row" ><?php WpBConstant::echo_setting_name('lsw_priority') ?></th>
						<td>
						<?php $pri = wpb_setting( 'lsw_priority', WPB_DEFAULT_LSW_PRIORITY); ?>
						<select name="lsw_priority">
						<option value="LSW" <?php selected( $pri, 'LSW' ) ?>><?php _e('Location &gt; Service &gt; Provider', 'wp-base')?></option>
						<option value="LWS" <?php selected( $pri, 'LWS' ) ?>><?php _e('Location &gt; Provider &gt; Service', 'wp-base')?></option>
						<option value="SLW" <?php selected( $pri, 'SLW' ) ?>><?php _e('Service &gt; Location &gt; Provider', 'wp-base')?></option>
						<option value="SWL" <?php selected( $pri, 'SWL' ) ?>><?php _e('Service &gt; Provider &gt; Location', 'wp-base')?></option>
						<option value="WLS" <?php selected( $pri, 'WLS' ) ?>><?php _e('Provider &gt; Location &gt; Service', 'wp-base')?></option>
						<option value="WSL" <?php selected( $pri, 'WSL' ) ?>><?php _e('Provider &gt; Service &gt; Location', 'wp-base')?></option>
						</select>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('lsw_priority') ?></span>
						</td>
					</tr>				

					<tr>
						<th scope="row" ><?php WpBConstant::echo_setting_name('log_settings') ?></th>
						<td>
						<select name="log_settings">
						<option value="no" <?php if ( wpb_setting('log_settings') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
						<option value="yes" <?php if ( wpb_setting('log_settings') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
						</select>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('log_settings') ?></span>
						</td>
					</tr>

					<tr id="strict_check">
						<th scope="row" ><?php WpBConstant::echo_setting_name('strict_check') ?></th>
						<td>
						<select name="strict_check">
						<option value="no" <?php if ( wpb_setting('strict_check') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
						<option value="yes" <?php if ( wpb_setting('strict_check') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
						</select>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('strict_check') ?></span>
						</td>
					</tr>

					<tr>
						<th scope="row" ><?php WpBConstant::echo_setting_name('admin_edit_collapse') ?></th>
						<td>
						<select name="admin_edit_collapse">
						<option value="no" <?php if ( wpb_setting('admin_edit_collapse') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
						<option value="yes" <?php if ( wpb_setting('admin_edit_collapse') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
						</select>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('admin_edit_collapse') ?></span>
						</td>
					</tr>

					<tr>
						<th scope="row" ><?php WpBConstant::echo_setting_name('admin_toolbar') ?></th>
						<td>
						<select name="admin_toolbar">
						<option value="no" <?php if ( wpb_setting('admin_toolbar') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
						<option value="yes" <?php if ( wpb_setting('admin_toolbar') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
						</select>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('admin_toolbar') ?></span>
						</td>
					</tr>

					<tr id="records-per-page">
						<th scope="row" ><?php WpBConstant::echo_setting_name('records_per_page') ?></th>
						<td>
						<input type="text" style="width:50px" name="records_per_page" value="<?php echo wpb_setting("records_per_page") ?>" />
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('records_per_page') ?></span>
						</td>
					</tr>

					<tr id="records-per-page-business">
						<th scope="row" ><?php WpBConstant::echo_setting_name('records_per_page_business') ?></th>
						<td>
						<input type="text" style="width:50px" name="records_per_page_business" value="<?php echo wpb_setting("records_per_page_business") ?>" />
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('records_per_page_business') ?></span>
						</td>
					</tr>

				</table>
				</div>
			</div>
			<script type="text/javascript">
			jQuery(document).ready(function($){
				var ms_ops3 = {
					noneSelectedText:'<?php echo esc_js( __('Select Pages', 'wp-base' )) ?>',
					checkAllText:'<?php echo esc_js( __('Check all', 'wp-base' )) ?>',
					uncheckAllText:'<?php echo esc_js( __('Uncheck all', 'wp-base' )) ?>',
					selectedText:'<?php echo esc_js( __('# selected', 'wp-base' )) ?>',
					selectedList:5,
					minWidth: 400,
					position: {
					  my: 'left bottom',
					  at: 'left top'
				   }
				};
				$(".preload_pages").multiselect(ms_ops3);
			});
			</script>			
			
	</div><!-- tabs -->
		<p class="submit">
			<input type="hidden" name="action_app" value="save_general" />
			<?php 
				$wp_nonce = wp_nonce_field( 'update_app_settings', 'app_nonce', true, false ); 
				echo $wp_nonce;
			?>
			<div style="float:left;">
			<input type="submit" class="button-primary" value="<?php _e('Save General Settings', 'wp-base') ?>" />
			</div>

			<div style="float:right;margin-right:10px;">
				<?php if ( BASE('EXIM') ) : ?>
					<a href="<?php echo admin_url('admin.php?page=app_tools&tab=impex'); ?>" title="<?php _e('Link for export/import of settings', 'wp-base') ?>" ><?php _e('Export/Import Settings', 'wp-base') ?></a>
				<?php endif; ?>
			</div>
				
			<div style="clear:both"></div>	
		</p>
			</form>

		</div>
		<?php break;
	/*******************************
	* Email tab
	********************************
	*/
		case 'email':	
		
		?>
		
		<div id="poststuff" class="metabox-holder meta-box-sortables">
		<?php
		wpb_infobox( __( 'WP BASE provides several email templates which can include booking specific details.', 'wp-base'),
						WpBConstant::email_desc() );
						
		do_action( 'app_admin_email_settings_after_info' );
		?>

       <script type="text/javascript">
      	  jQuery(document).ready(function ($) {
            $('input[name="send_test_email_btn"]').click(function() {
				$('input[name="send_test_email"]').val(1);
				$("#app_email_settings_form").submit();
			});
          });
      	</script>
 		<form id="app_email_settings_form" class="app_form" method="post" action="<?php echo wpb_add_query_arg( null, null )?>">
	
			<div id="wpb-general-email-settings" class="postbox <?php echo postbox_classes('wpb-general-email-settings', wpb_get_current_screen_id( true ) ) ?>">
 				<button type="button" class="handlediv" aria-expanded="true">
					<span class="screen-reader-text"><?php  _e( 'Toggle panel: General email Settings' ) ?></span>
					<span class="toggle-indicator" aria-hidden="true"></span>
				</button>
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<h3 class="hndle"><span class="notification_settings"><?php _e('General', 'wp-base') ?></span></h3>
            <div class="inside">
			
				<table class="form-table">
				
				<tr>
					<th scope="row"><?php WpBConstant::echo_setting_name('admin_email') ?></th>
					<td>
					<input value="<?php echo wpb_setting('admin_email'); ?>" size="90" style="width:90%" name="admin_email" type="text" />
					<br />
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('admin_email') ?></span>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><?php WpBConstant::echo_setting_name('from_name') ?></th>
					<td>
					<input value="<?php echo esc_attr( wpb_setting('from_name') ) ?>" size="45" style="width:45%" name="from_name" type="text" />
					<br />
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('from_name') ?></span>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php WpBConstant::echo_setting_name('from_email') ?></th>
					<td>
					<input value="<?php echo wpb_setting('from_email'); ?>" size="45" style="width:45%" name="from_email" type="text" />
					<br />
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('from_email') ?></span>
					</td>
				</tr>

				<tr id="log-emails">
					<th scope="row" ><?php WpBConstant::echo_setting_name('log_emails') ?></th>
					<td>
					<select name="log_emails">
					<option value="no" <?php if ( wpb_setting('log_emails') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
					<option value="yes" <?php if ( wpb_setting('log_emails') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
					</select>
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('log_emails') ?></span>
					</td>
   				</tr>
				
				<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('use_html') ?></th>
					<td>
					<select name="use_html">
					<option value="no" <?php if ( wpb_setting('use_html') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
					<option value="yes" <?php if ( wpb_setting('use_html') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
					</select>
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('use_html') ?></span>
					</td>
   				</tr>

				<tr id="send-test-email">
					<th scope="row" ><?php _e('Send a Test email', 'wp-base')?></th>
					<td>
					<input type="button" class="app_no_save_alert button-secondary" name="send_test_email_btn" value="<?php _e('Send Now', 'wp-base') ?>"/>
					<input type="hidden" class="app_no_save_alert" name="send_test_email" />
					<span class="description app_bottom"><?php _e('Clicking this button will IMMEDIATELY try to send a test email to the admin email(s) above using confirmation message template below. Most email problems are related to incorrect hosting email installations. In other words, email application may not be correctly configured for your website. This may help to figure out if you have such a problem in the first place.', 'wp-base') ?></span>
					</td>
				</tr>
				</table>
			</div>
			</div>
 		
		<div id="tabs" class="app-tabs">
			<ul></ul>

			<div class="postbox">
				<h3 class="hndle">
					<span class="notification_settings">
					<abbr title="<?php _e( 'Emails sent when status is confirmed.', 'wp-base' ) ?>">
						<?php _e('Confirmation', 'wp-base') ?>
					</abbr>
					</span>
				</h3>
				<div class="inside">
			
				<table class="form-table">

				<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('send_confirmation') ?></th>
					<td>
					<select name="send_confirmation">
					<option value="no" <?php if ( wpb_setting('send_confirmation') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
					<option value="yes" <?php if ( wpb_setting('send_confirmation') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
					</select>
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('send_confirmation') ?></span>
					</td>
				</tr>
 				
				<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('send_confirmation_bulk') ?></th>
					<td>
					<select name="send_confirmation_bulk">
					<option value="no" <?php if ( wpb_setting('send_confirmation_bulk') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
					<option value="yes" <?php if ( wpb_setting('send_confirmation_bulk') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
					</select>
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('send_confirmation_bulk') ?></span>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><?php WpBConstant::echo_setting_name('confirmation_subject') ?></th>
					<td>
					<input value="<?php echo esc_attr( wpb_setting('confirmation_subject')); ?>" size="90" style="width:90%" name="confirmation_subject" type="text" />
					<?php do_action( 'app_admin_email_settings_after_subject', 'confirmation' ) ?>
					</td>
				</tr>
				
				<tr id="confirmation-message">
					<th scope="row"><?php WpBConstant::echo_setting_name('confirmation_message') ?></th>
					<td>
					<?php 
					if ( wpb_setting('use_html') == 'yes' ) {
						wp_editor( wpb_setting('confirmation_message'), 'confirmation_message', $wp_editor_settings );
					}
					else { ?>
					<textarea cols="90" style="width:90%" name="confirmation_message"><?php echo esc_textarea( wpb_setting('confirmation_message')); ?></textarea>
					<?php } ?>
					<?php do_action( 'app_admin_email_settings_after_message', 'confirmation' ) ?>
					</td>
				</tr>
				
				<?php do_action( 'app_admin_after_email', 'confirmation' ) ?>

				</table>
			</div>
			</div>

			<div class="postbox">
				<h3 class="hndle">
					<span class="notification_settings">
					<abbr title="<?php _e( 'Settings for email messages when appointment status is Pending.', 'wp-base' ) ?>">
						<?php _e('Pending', 'wp-base') ?>
					</abbr>
					</span>
				</h3>
				<div class="inside">
			
				<table class="form-table">

				<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('send_pending') ?></th>
					<td>
					<select name="send_pending">
					<option value="no" <?php if ( wpb_setting('send_pending') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
					<option value="yes" <?php if ( wpb_setting('send_pending') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
					</select>
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('send_pending') ?></span>
					</td>
				</tr>
 				
				<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('send_pending_bulk') ?></th>
					<td>
					<select name="send_pending_bulk">
					<option value="no" <?php if ( wpb_setting('send_pending_bulk') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
					<option value="yes" <?php if ( wpb_setting('send_pending_bulk') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
					</select>
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('send_pending_bulk') ?></span>
					</td>
				</tr>
 
				<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('send_notification') ?></th>
					<td>
					<select name="send_notification">
					<option value="no" <?php if ( wpb_setting('send_notification') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
					<option value="yes" <?php if ( wpb_setting('send_notification') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
					</select>
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('send_notification') ?></span>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php WpBConstant::echo_setting_name('pending_subject') ?></th>
					<td>
					<input value="<?php echo esc_attr( wpb_setting('pending_subject')); ?>" size="90" style="width:90%" name="pending_subject" type="text" />
					<?php do_action( 'app_admin_email_settings_after_subject', 'pending' ) ?>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><?php WpBConstant::echo_setting_name('pending_message') ?></th>
					<td>
					<?php if ( wpb_setting('use_html') == 'yes' ) {
						wp_editor( wpb_setting('pending_message'), 'pending_message', $wp_editor_settings );
					}
					else { ?>
					<textarea cols="90" style="width:90%" name="pending_message"><?php echo esc_textarea( wpb_setting('pending_message')); ?></textarea>
					<?php } ?>
					<?php do_action( 'app_admin_email_settings_after_message', 'pending' ) ?>
					</td>
				</tr>
				
				<?php do_action( 'app_admin_after_email', 'pending' ) ?>

				</table>
				</div>
			</div>
			
			<?php do_action( 'app_admin_after_email_pending' ) ?>

			<div class="postbox">
				<h3 class="hndle">
					<span class="notification_settings">
					<abbr title="<?php _e( 'Settings for email messages when appointment has just completed or its status is Completed.', 'wp-base' ) ?>">
						<?php _e('Completed', 'wp-base') ?>
					</abbr>
					</span>
				</h3>
				<div class="inside">
			
				<table class="form-table">

				<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('send_completed') ?></th>
					<td>
					<select name="send_completed">
					<option value="no" <?php if ( wpb_setting('send_completed') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
					<option value="yes" <?php if ( wpb_setting('send_completed') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
					</select>
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('send_completed') ?></span>
					</td>
				</tr>
 				
				<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('send_completed_bulk') ?></th>
					<td>
					<select name="send_completed_bulk">
					<option value="no" <?php if ( wpb_setting('send_completed_bulk') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
					<option value="yes" <?php if ( wpb_setting('send_completed_bulk') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
					</select>
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('send_completed_bulk') ?></span>
					</td>
				</tr>
 
				<tr>
					<th scope="row"><?php WpBConstant::echo_setting_name('completed_subject') ?></th>
					<td>
					<input value="<?php echo esc_attr( wpb_setting('completed_subject')); ?>" size="90" style="width:90%" name="completed_subject" type="text" />
					<?php do_action( 'app_admin_email_settings_after_subject', 'completed' ) ?>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><?php WpBConstant::echo_setting_name('completed_message') ?></th>
					<td>
					<?php if ( wpb_setting('use_html') == 'yes' ) {
						wp_editor( wpb_setting('completed_message'), 'completed_message', $wp_editor_settings );
					}
					else { ?>
					<textarea cols="90" style="width:90%" name="completed_message"><?php echo esc_textarea( wpb_setting('completed_message')); ?></textarea>
					<?php } ?>
					<?php do_action( 'app_admin_email_settings_after_message', 'completed' ) ?>
					</td>
				</tr>
				
				<?php do_action( 'app_admin_after_email', 'completed' ) ?>

				</table>
				</div>
			</div>
			
			<?php do_action( 'app_admin_after_email_completed' ) ?>

			<div class="postbox">
				<h3 class="hndle">
					<span class="notification_settings">
					<abbr title="<?php _e( 'Settings for email messages when appointment is cancelled or its status is Removed.', 'wp-base' ) ?>">
						<?php _e('Cancellation', 'wp-base') ?>
					</abbr>
					</span>
				</h3>
				<div class="inside">
			
				<table class="form-table">

				<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('send_cancellation') ?></th>
					<td>
					<select name="send_cancellation">
					<option value="no" <?php if ( wpb_setting('send_cancellation') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
					<option value="yes" <?php if ( wpb_setting('send_cancellation') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
					</select>
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('send_cancellation') ?></span>
					</td>
   				</tr>
				
				<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('send_cancellation_bulk') ?></th>
					<td>
					<select name="send_cancellation_bulk">
					<option value="no" <?php if ( wpb_setting('send_cancellation_bulk') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
					<option value="yes" <?php if ( wpb_setting('send_cancellation_bulk') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
					</select>
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('send_cancellation_bulk') ?></span>
					</td>
   				</tr>

				<tr>
					<th scope="row"><?php WpBConstant::echo_setting_name('cancellation_subject') ?></th>
					<td>
					<input value="<?php echo esc_attr( wpb_setting('cancellation_subject')); ?>" size="90" style="width:90%" name="cancellation_subject" type="text" />
					<?php do_action( 'app_admin_email_settings_after_subject', 'cancellation' ) ?>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><?php WpBConstant::echo_setting_name('cancellation_message') ?></th>
					<td>
					<?php if ( wpb_setting('use_html') == 'yes' ) {
						wp_editor( wpb_setting('cancellation_message'), 'cancellation_message', $wp_editor_settings );
					}
					else { ?>
					<textarea cols="90" style="width:90%" name="cancellation_message"><?php echo esc_textarea( wpb_setting('cancellation_message')); ?></textarea>
					<?php } ?>
					<?php do_action( 'app_admin_email_settings_after_message', 'cancellation' ) ?>					
					</td>
				</tr>
				
				<?php do_action( 'app_admin_after_email', 'cancellation' ) ?>
				
				</table>
				</div>
			</div>
			
			<?php do_action( 'app_admin_after_email_cancellation' ) ?>
			
		</div><!-- Tabs -->
		
			<input type="hidden" name="action_app" value="save_email_settings" />
				<?php 
					$wp_nonce = wp_nonce_field( 'update_app_settings', 'app_nonce', true, false ); 
					echo $wp_nonce;
				?>
				<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Email Settings', 'wp-base') ?>" />
				</p>
			
			</form>
		</div>
		<?php break;
	/*******************************
	* Advanced tab
	********************************
	*/

		case 'advanced':
			echo $this->advanced_tab();		
		break;

	/*******************************
	* Additional tabs
	********************************
	*/

		// For custom tab additions
		case 'custom1':			do_action( 'app_additional_tab1' ); break;
		case 'custom2':			do_action( 'app_additional_tab2' ); break;
		case 'custom3':			do_action( 'app_additional_tab3' ); break;
		// Specialized tab - Do not use existing tab names
		case $tab:				do_action( 'app_'.$tab.'_tab' ); break;
		
		} // End of the big switch ?>
		</div><!-- Wrap -->
		
		<script type="text/javascript">
		jQuery(document).ready(function($){
			var tab = typeof $.urlParam === 'function' && $.urlParam('tab') ? '_'+$.urlParam('tab') : '';
			postboxes.add_postbox_toggles(pagenow+tab);
		});
		</script>

	<?php
	}
	
	/**
	 * Contents of advanced tab
	 */
	function advanced_tab(){
		$wp_editor_settings = array( 'editor_height'=> WPB_EDITOR_HEIGHT );

		ob_start();

		?>
		<div id="poststuff" class="metabox-holder metabox-holder-advanced">
		<?php wpb_infobox( __('Some business models may need advanced settings. Here you can adjust them. These settings are enabled by Addons.', 'wp-base') ); ?>

		<form class="app_form" method="post" action="<?php echo wpb_add_query_arg( null, null )?>">
 		
		<div id="tabs" class="app-tabs">
			<ul></ul>
			
			
			<?php do_action( 'app_advanced_settings' ); ?>
			
		</div><!--tabs -->
				
				<input type="hidden" name="action_app" value="save_advanced" />
				<?php wp_nonce_field( 'update_app_settings', 'app_nonce' ); ?>
				<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Advanced Settings', 'wp-base') ?>" />
				</p>
			
			</form>
		
		</div>
		<?php
			$c = ob_get_contents();
			ob_end_clean();

			return $c;
	}

}

	BASE('GlobalSettings')->add_hooks();
}
