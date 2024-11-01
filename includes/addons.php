<?php
/**
 * WPB Addons
 *
 * Manages loading of addons
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBAddons' ) ) :

class WpBAddons {

	/**
     * WP BASE Core + Front [+Admin] instance
     */
	protected $a = null;
	
	/**
     * Constructor
     */
	function __construct() {
		$this->a = BASE();
		$this->addon_dir = WPB_PLUGIN_DIR ."/includes/addons";
	}
	
	/**
     * Add actions and filters
     */
	function add_hooks() {
		add_action( 'app_submenu_before_tools', array( $this, 'add_submenu' ), 20 );				// Add submenu item
		add_action( 'app_save_settings_with_get', array( $this, 'save_settings' ), 16 );			// Save settings
		add_action( 'app_loaded', array( $this, 'load_addons' ), 20 ); 
		add_action( 'app_installed', array( $this, 'installed' ) ); 
		add_action( 'activate_plugin', array( $this, 'plugin_activated_deactivated' ), 10, 2 );
		add_action( 'deactivate_plugin', array( $this, 'plugin_activated_deactivated' ), 10, 2 );

		add_filter( 'appointments_tools_tabs', array( $this, 'add_tab' ), 30 );						// Add tab to Tools
		add_action( 'admin_init', array( $this, 'display_notice' ) );
		add_action( 'admin_init', array( $this, 'display_success' ) );
		add_action( 'admin_init', array( $this, 'handle_license_activation' ) );
		add_action( 'update_option', array( $this, 'sanitize_license' ), 10, 3 );		
	}
	
	/**
     * List all files in addons directory. Only file name with php extension
	 * @return array
     */
	function get_all_addons () {
		$all = glob( $this->addon_dir ."/*.php" );
		$all = $all ? $all : array();
		$ret = array();
		foreach ($all as $path) {
			$ret[] = pathinfo($path, PATHINFO_FILENAME);
		}
		return $ret;
	}
	
	/**
     * List all extensions (Addons + Plugins) who are registered themselves.
	 * @return array
     */
	function get_all_active_extensions(){
		return apply_filters( 'app_active_extensions', false );
	}
	
	/**
     * List info of the addon
	 * @return array
     */
	function get_addon_info( $addon ) {
		$addon = str_replace( '/', '_', $addon );
		$path = $this->addon_dir ."/" . "{$addon}.php";

		return get_file_data( $path, wpb_default_headers(), 'plugin' );
	}
	
	/**
     * Return name of the addon
	 * @return string
     */
	function get_addon_name( $addon ) {
		$info = $this->get_addon_info( $addon );
		return isset( $info['Name'] ) ? str_replace( 'WP BASE Addon: ','', $info['Name']) : '';
	}
	
	/**
     * Check if an addon is active
     */
	function is_active( $addon ) {
		if ( $this->is_disabled( $addon ) )
			return false;
		
		if ( !is_array( wpb_setting('deactivated_addons') ) || !in_array( $addon, wpb_setting('deactivated_addons') ) )
			return true;
	
		return false;
	}
	
	/**
     * Check if an addon is disabled by an external plugin
	 * External plugin is expected to return false for not to disable and
	 * A reason (string) to disable
     */
	function is_disabled( $addon ) {
		return apply_filters( 'app_disable_addon', false, $addon );
	}
	
	/**
     * Allow addons to be deactivated at installation
     */
	function installed(){
		if ( defined( 'WPB_ADDONS_DEACTIVATED_AT_INSTALL' ) && WPB_ADDONS_DEACTIVATED_AT_INSTALL ) {
			$options = wpb_setting();
			$deactivated_addons = empty( $options['deactivated_addons'] ) ? array() : $options['deactivated_addons'];
			foreach ( $this->get_all_addons( ) as $addon ) {
				$deactivated_addons[] = $addon;
			}
				
			$options['deactivated_addons'] = array_unique( $deactivated_addons );
			$this->a->update_options( $options );
		}			
	}
	
	/**
     * Load all files in addons directory
	 * This happens after "plugins loaded". Therefore a WP plugin with the same class name of an addon has priority
	 * Such a plugin prevents loading of an addon by wpb_disable_addon function
     */
	function load_addons( ) {
		
		$all_addons = apply_filters( 'app_addons_to_load', $this->get_all_addons() );

		foreach ( (array)$all_addons as $addon ){
			if ( $this->is_active( $addon ) ) {
				// Allow forced disabling of gcal in case of emergency
				if ( 'gcal' == $addon && defined( 'WPB_DISABLE_GCAL' ) )
					continue;
				
				include_once $this->addon_dir ."/" . $addon . ".php";
			}
		}

		do_action( 'app_addons_loaded' );
		
		// Check if any addon/plugin is registered itself, if yes add a page
		if ( $this->get_all_active_extensions() ) {
			add_action( 'app_tools_licenses_tab', array( $this, 'license_settings' ) );					// Create tab HTML
		}
	}

	/**
	 * Check if a gateway plugin is active
	 * @param $gateway: Name of the gateway to check, i.e. paypal-standard
	 * @Since 2.0 
	 */	
	function is_gateway_active( $gateway ) {
		$active_gateways = array();
		global $app_gateway_active_plugins;
		foreach ( (array)$app_gateway_active_plugins as $code=>$addon ) {
			if ( $gateway == $addon->plugin_name )
				return true;
		}
		return false;
	}

	/**
     * Add submenu
     */
	function add_submenu() {
		if ( !wpb_is_demo() && defined( 'WPB_DEV' ) && WPB_DEV )
			add_submenu_page( 'appointments', __('WPB Addons','wp-base'), __('Addons','wp-base'), WPB_ADMIN_CAP, "app_addons", array( $this,'settings'));
	}
	
	/**
     * Add submenu
     */
	function add_license_submenu() {
		if ( !wpb_is_demo() )
			add_submenu_page( 'appointments', __('WPB Licenses','wp-base'), __('Licenses','wp-base'), WPB_ADMIN_CAP, "app_licenses", array($this,'license_settings'));
	}

	/**
     * Check if Advanced Features Extension is activated or deactivated
     */
	function plugin_activated_deactivated( $wp_plugin, $network_wide ) {
		if ( strpos( $wp_plugin, 'advanced-features' ) === false )
			return;
		
		$this->rebuild_shortcodes();
	}
	
	/**
     * Recreate list of shortcodes
     */
	function rebuild_shortcodes() {
		include_once( WPB_PLUGIN_DIR . '/includes/constant-data.php' );
		update_option( 'wp_base_shortcodes', array_keys( WpBConstant::shortcode_desc() ) );
	}

	/**
	 * Save settings 
	 */
	function save_settings() {
		// If there is no save request, nothing to do
		if ( ( !isset( $_REQUEST["update_addons"] ) || !isset( $_REQUEST["addon"] ) ) && !isset( $_POST["optimize_addons"] ) ) {
			return;
		}
		
		if ( !wp_verify_nonce($_REQUEST['app_nonce'],'update_addons') ) {
			wpb_notice( 'unauthorised', 'error' );
			return;
		}
		
		$options = wpb_setting();
		$business_options = $this->a->get_business_options();
		$deactivated_addons = empty( $options['deactivated_addons'] ) ? array() : $options['deactivated_addons'];
		$changed_addon_name = '';
		
		// Optimize addons
		if ( isset( $_POST["optimize_addons"] ) ) {
			
			// Annual
			if( empty( $business_options['alt_schedule_pref'] ) )
				$deactivated_addons[] = 'seasonal-and-alternating-wh';
			
			// Front end management
			// Time zones
			$posts_pages = $this->a->get_app_pages();
			$management_used = $timezones_used = $modular_used = false;
			
			// Modular shortcodes
			$all_shortcodes = wpb_shortcodes();
			$compact_shortcodes = array( 'app_book', 'app_list', 'app_theme', 'app_is_mobile', 'app_is_not_mobile' );
			$modular_shortcodes = array_diff( $all_shortcodes, $compact_shortcodes );  
			
			if ( !empty( $posts_pages ) ) {
				foreach ( $posts_pages as $post ) {

					if ( !$management_used && strpos( $post->post_content, '[app_manage' ) !== false )
						$management_used = true;

					if ( !$modular_used && preg_match_all( '/'. get_shortcode_regex( $modular_shortcodes ) .'/s', $post->post_content, $matches ) )
						$modular_used = true;

					if ( !$timezones_used && strpos( $post->post_content, '[app_timezone' ) !== false )
						$timezones_used = true;

					if ( $management_used && $timezones_used && $modular_used )
						break;
				}
			}
			
			// Also check widget contents
			$app_widgets = get_option( 'widget_appointments_shortcode' );

			if ( is_array( $app_widgets ) ) {
				foreach ( $app_widgets as $widget ) {
					if ( !isset( $widget['content'] ) )
						continue;
					if ( !$management_used && strpos( $widget['content'], '[app_manage' ) !== false )
						$management_used = true; 
					if ( !$modular_used && preg_match_all( '/'. get_shortcode_regex( $modular_shortcodes ) .'/s', $post->post_content, $matches ) )
						$modular_used = true; 
					if ( !$timezones_used && strpos( $widget['content'], '[app_timezone' ) !== false )
						$timezones_used = true; 
				}
			}
			
			if ( !$management_used )
				$deactivated_addons[] = 'front-end-management';
			// if ( !$modular_used )
				// $deactivated_addons[] = 'modular-shortcodes';
			if ( !$timezones_used && 'yes' != wpb_setting('enable_timezones') )
				$deactivated_addons[] = 'timezones';

			// BuddyPress
			global $bp;
			if ( !is_object( $bp ) )
				$deactivated_addons[] = 'buddypress';
			
			// Coupons
			if( empty( $business_options['coupons'] ) )
				$deactivated_addons[] = 'coupons';
				
			// Custom pricing
			if ( !trim( wpb_setting('advanced_pricing_total_price') ) 
				&& !trim( wpb_setting('advanced_pricing_deposit') ) 
				&& !$this->a->get_business_options('ep_rules') )
				$deactivated_addons[] = 'custom-and-variable-pricing';

			// Export/import - If admin capability set to be different than admin, deactivate
			if ( defined( 'WPB_ADMIN_CAP' ) && WPB_ADMIN_CAP != 'manage_options' )
				$deactivated_addons[] = 'export-import';	

			// Extras
			if( empty( $business_options['extras'] ) )
				$deactivated_addons[] = 'extras';

			// Front end edit
			if( 'yes' != wpb_setting('allow_edit') && 'yes' != wpb_setting('allow_worker_edit') )
				$deactivated_addons[] = 'front-end-edit';

			// GCal
			if( 'yes' != wpb_setting('gcal_api_allow_worker')	&& 'none' == wpb_setting('gcal_api_mode') )
				$deactivated_addons[] = 'gcal';
				
			// Extended Service, Group Bookings and Quotas
			$esf_not_used = $quotas_not_used = $group_bookings_not_used = true;
			$services = $this->a->get_services();
			$fields = array( 'weekday_leadtime', 'weekend_leadtime', 'holiday_leadtime', 'weekday_edittime', 'weekend_edittime', 'holiday_edittime', 'weekday_canceltime', 'weekend_canceltime', 'holiday_canceltime' );

			foreach ( $services as $service ) {

				foreach ( $fields as $field ) {
					if ( $esf_not_used && wpb_get_service_meta( $service->ID, $field ) ) {
						$esf_not_used = false;
						break;
					}
				}
				
				if ( $esf_not_used && ($service->padding || $service->break_time || $service->internal || $service->deposit) )
					$esf_not_used = false;
					
				if ( $quotas_not_used && (wpb_get_service_meta( $service->ID, 'weekday_quota' ) || wpb_get_service_meta( $service->ID, 'weekend_quota' )
					|| wpb_get_service_meta( $service->ID, 'weekly_quota' ) || wpb_get_service_meta( $service->ID, 'any_quota' )) )
					$quotas_not_used = false;
					
				if ( $group_bookings_not_used && wpb_get_service_meta( $service->ID, 'group_bookings' ) )
					$group_bookings_not_used = false;
				
				if ( !$esf_not_used && !$quotas_not_used && !$group_bookings_not_used )
					break;
			}
						
			if ( $esf_not_used && 'yes' == wpb_setting('preselect_first_service') && 'no' == wpb_setting('time_slot_calculus_legacy') && !$this->a->get_categories() )
				$deactivated_addons[] = 'extended-service-settings';
			
			if ( $quotas_not_used )
				$deactivated_addons[] = 'quotas';
			
			if ( $group_bookings_not_used )
				$deactivated_addons[] = 'group-bookings';

			// Locations
			if ( !$this->a->get_nof_locations() )
				$deactivated_addons[] = 'locations';

			// Marketpress
			if ( 'yes' != wpb_setting('use_mp') )
				$deactivated_addons[] = 'marketpress';

			// WooCommerce
			if ( 'yes' != wpb_setting('wc_enabled') )
				$deactivated_addons[] = 'woocommerce';

			// Packages
			$pack_not_used = true;
			$services = $this->a->get_services();
			foreach ( (array)$services as $service ) {
				if ( wpb_get_service_meta( $service->ID, 'package' ) ) {
					$pack_not_used = false;
					break;
				}
			}
			if ( $pack_not_used )
				$deactivated_addons[] = 'packages';
			
			// Payment gateways
			$allowed = isset( $options['gateways']['allowed'] ) && is_array( $options['gateways']['allowed'] ) ? $options['gateways']['allowed'] : array();
			foreach ( $this->get_all_addons( ) as $addon ) {
				if( substr( $addon, 0, 16 ) != "payment-gateway-" )
					continue;
				if ( !in_array( str_replace( "payment-gateway-", '', $addon ), $allowed ) )
					$deactivated_addons[] = $addon;
			}
			
			// Reminder emails
			if( 'yes' != wpb_setting('send_reminder') 
				&& 'yes' != wpb_setting('send_reminder_worker') 
				&& 'yes' != wpb_setting('send_dp_reminder') )
				$deactivated_addons[] = 'reminder-emails';	

			// Service providers
			if ( !$this->a->get_nof_workers() )
				$deactivated_addons[] = 'service-providers';
			
			// Shopping Cart
			if ( 'yes' != wpb_setting('use_cart') )
				$deactivated_addons[] = 'shopping-cart';
				
			// SMS
			if ( !wpb_setting('sms_service') )
				$deactivated_addons[] = 'sms';
			
			// Test appointments
			if ( !$this->a->db->get_var( "SELECT COUNT(ID) FROM " . $this->a->app_table . " WHERE status='test'" ) )
				$deactivated_addons[] = 'test-appointments';
			
			// UDF
			if ( !get_option( 'wp_base_udfs' ) )
				$deactivated_addons[] = 'udf';
				
			// PDF
			if ( 'yes' != wpb_setting("confirmation_attach") && 'yes' != wpb_setting("reminder_attach") && 'yes' != wpb_setting("cancellation_attach") )
				$deactivated_addons[] = 'pdf';
			
			$deactivated_addons = array_unique( $deactivated_addons );
			
			// Pro Addon
			global $app_gateway_active_plugins;
			$total_count = $this->get_all_addons() ? count( $this->get_all_addons() ) : 0;
			$deactivated_count = $deactivated_addons ? count( $deactivated_addons ) : 0;
			$active_count = $app_gateway_active_plugins ? count( $app_gateway_active_plugins ) : 0;
			if ( !$modular_used && ($total_count - 1 == $deactivated_count || 1 == $active_count) )
				$deactivated_addons[] = 'pro-options';
			
			$options['deactivated_addons'] = array_unique( $deactivated_addons );
			
			foreach ( $deactivated_addons as $filename ) {
				if ( $this->is_active( $filename ) )
					$changed_addon_name .= $this->get_addon_name($filename) . ', ';	
			}
			$changed_addon_name = urlencode( rtrim( $changed_addon_name, ', ' ) );
				
			if( !empty($deactivated_addons) && $this->a->update_options( $options ) ) {
				wp_redirect( wpb_add_query_arg( array( 'deactivated'=>1, 'activated'=>false, 'action_app'=>false, 'app_nonce'=>false, 'changed_addons'=>$changed_addon_name, 'addon'=>false ) ) );
				exit;
			}
			else {
				wp_redirect( wpb_add_query_arg( array( 'deactivated'=>false, 'activated'=>false, 'action_app'=>false, 'app_nonce'=>false, 'addon'=>false ) ) );
				exit;
			}
		}

		if ( isset( $_POST["addon"] ) && is_array( $_POST["addon"] ) && $_POST['app_new_status'] ) {
			$new_status = $_POST["app_new_status"];
			foreach ( $_POST["addon"] as $filename ) {
				//deactivate
				if ( 'deactivate' == $new_status ) {
					if ( $this->is_active( $filename ) ) {
						$deactivated_addons[] = $filename;
						$changed_addon_name .= $this->get_addon_name($filename) . ', ';
						$deactivated = true;
					}
				}
				else if ( 'activate' == $new_status ) {
					if ( !$this->is_active( $filename ) ) {
						$key = array_search( $filename, $deactivated_addons );
						if( $key !== false ){
							unset( $deactivated_addons[$key] );
							$changed_addon_name .= $this->get_addon_name($filename) . ', ';
							$deactivated = false;
						}
					}
				}
			}
			$changed_addon_name = urlencode( rtrim( $changed_addon_name, ', ' ) );
		}
		else if ( isset( $_GET["addon"] ) ) {
			$filename = $_GET["addon"];
			
			if ( $this->is_active( $filename ) ) {
				$deactivated_addons[] = $filename;
				$deactivated = true;
				$changed_addon_name = urlencode( $this->get_addon_name($filename) );
			}
			else {
				$key = array_search( $filename, $deactivated_addons );
				if( $key !== false ){
					unset( $deactivated_addons[$key] );
					$deactivated = false; // Means activated
					$changed_addon_name = urlencode( $this->get_addon_name($filename) );
				}
			}
		}
		$options['deactivated_addons'] = array_unique( $deactivated_addons );
		
		if( $this->a->update_options( $options ) ) {
			
			wpb_flush_cache();
			
			$this->rebuild_shortcodes();
			
			if ( $deactivated )
				wp_redirect( wpb_add_query_arg( array( 'deactivated'=>1, 'activated'=>false, 'action_app'=>false, 'app_nonce'=>false, 'changed_addons'=>$changed_addon_name, 'addon'=>false ) ) );
			else
				wp_redirect( wpb_add_query_arg( array( 'activated'=>1, 'deactivated'=>false, 'action_app'=>false, 'app_nonce'=>false, 'changed_addons'=>$changed_addon_name, 'addon'=>false ) ) );
		}
		else
			wp_redirect( wpb_add_query_arg( array( 'deactivated'=>false, 'activated'=>false, 'action_app'=>false, 'app_nonce'=>false, 'changed_addons'=>false, 'addon'=>false ) ) );
			
	}
	
	/**
	 * Admin settings HTML code 
	 */
	function settings() {

		wpb_admin_access_check( 'manage_addons' );
		
		?>
		
		<div class='wrap app-page'>
			<h2 class="app-dashicons-before dashicons-admin-plugins"><?php echo __('Addons','wp-base'); ?></h2>
			<div id="poststuff" class="metabox-holder">

			<?php
			if ( isset( $_GET['changed_addons'] ) && $_GET['changed_addons']  )
				$text = urldecode( $_GET['changed_addons'] );
			else if ( isset( $_GET['addon'] ) )
				$text = $this->get_addon_name($_GET['addon']);
			else $text = false;
			
			$count = $text ? count( explode(',', $text ) ) : 0;
			
			if ( (isset( $_GET['changed_addons'] ) && $_GET['changed_addons']) || ( isset( $_GET['addon'] ) && $_GET['addon'] ) ) {
				if ( isset( $_GET['deactivated'] ) && $_GET['deactivated'] )
					echo '<div class="updated fade"><p><b>[WP BASE]</b> '. sprintf( __('%1$d addon(s) deactivated: %2$s.','wp-base'), $count, $text ).'</p></div>';
				else if ( isset( $_GET['activated'] ) && $_GET['activated'] )
					echo '<div class="updated fade"><p><b>[WP BASE]</b> '. sprintf( __('%1$d addon(s) activated: %2$s.','wp-base'), $count, $text ).'</p></div>';	
			}
			// Description
			$desc = WpBConstant::addon_desc();
			wpb_infobox( $desc[0] );
			
			?>
			<?php 
				$wp_nonce = wp_create_nonce( 'update_addons' ); 
			?>

		<form method="post" action="<?php echo wpb_add_query_arg( array( 'page'=>'app_addons', 'addon'=>false, 'app_nonce'=>$wp_nonce ) ); ?>" >
			<div class="alignright actions" style="margin:6px 0 4px;">
				<input type="hidden" value="app_addons" name="page" />
				<input type="hidden" value="<?php if ( isset( $post->ID ) ) echo $post->ID; else echo 0; ?>" name="page_id" />
				<input type="hidden" value="1" name="optimize_addons" />
				<input type="hidden" value="optimize_addons" name="action_app" />
				<input type="hidden" name="app_nonce" value="<?php echo $wp_nonce?>">
				<input type="submit" class="button" value="<?php _e('Deactivate Unused Addons','wp-base'); ?>" title="<?php _e('Checks your settings and deactivates any addon that has not set up. Use this only after you finished configuring your website.','wp-base'); ?>" />
			</div>
		</form>
		<form method="post" action="<?php echo wpb_add_query_arg( array( 'page'=>'app_addons', 'addon'=>false, 'app_nonce'=>$wp_nonce ) ); ?>" >
			<div class="alignleft actions" style="margin:6px 0 4px;">
				<input type="hidden" value="app_addons" name="page" />
				<input type="hidden" value="<?php if ( isset( $post->ID ) ) echo $post->ID; else echo 0; ?>" name="page_id" />
				<input type="hidden" value="1" name="update_addons" />
				<input type="hidden" value="update_addons" name="action_app" />
				<input type="hidden" name="app_nonce" value="<?php echo $wp_nonce?>">
				<select name="app_new_status" style='float:none;'>
					<option value=""><?php _e('Bulk actions','wp-base'); ?></option>
					<option value="activate" class="hide-if-no-js"><?php _e('Activate','wp-base'); ?></option>
					<option value="deactivate" class="hide-if-no-js"><?php _e('Deactivate','wp-base'); ?></option>
				</select>			
				<input type="submit" class="button" value="<?php _e('Apply','wp-base'); ?>" />
			</div>

		
			<table class="wp-list-table widefat plugins app_addons">
			<thead>
			<tr>
				<th style="width:3%; text-align:left;" class="column-delete"><input type="checkbox" /></th>
				<th style="width:22%; text-align:left;"><?php _e( 'Addon', 'wp-base') ?></th>
				<th style="width:75%; text-align:left;"><?php _e( 'Description', 'wp-base') ?></th>
			</tr>
			</thead>
			<?php
			foreach ( $this->get_all_addons() as $filename ) {
				$addon_data = $this->get_addon_info( $filename );
				if ( !$addon_data["Name"] ) 
					continue;
				
				$url = wpb_add_query_arg( array( 'action_app'=>1, 'app_nonce'=>$wp_nonce, 'page'=>'app_addons', 'update_addons'=>1, 'addon'=>$filename, 'changed_addons'=>false ) );
				?>
				<tr <?php if ( $this->is_active( $filename) ) echo "class='active'"; else echo "class='inactive'";?>>
					<td class="column-delete check-column app-check-column">
						<input type="checkbox" name="addon[]" value="<?php echo $filename;?>" />	
					</td>
					<td class="plugin-title">
						<?php 
						echo '<strong>'. str_replace( 'WP BASE Addon: ', '', $addon_data["Name"] ) . '</strong>';
						echo '<div class="row-actions-visible">';								
						
						if ( $this->is_active( $filename ) ) {
							echo '<a href="'.$url.'">'. __('Deactivate', 'wp-base'). '</a>';
							do_action( 'app_addon_settings_link', $filename );
						}
						else {
							// Check why not active. Maybe disabled externally?
							if ( $reason = $this->is_disabled( $filename ) )
								echo $reason;
							else
								echo '<a href="'.$url.'">'. __('Activate', 'wp-base'). '</a>';
						}
							
						do_action( 'app_addon_help_link', $filename );
						echo '</div>';
						?>
					</td>
					<td class="column-description desc">
						<?php  
							echo '<div class="plugin-description">'. $addon_data["Description"] . '</div>';
							echo '<div class="second plugin-version-author-uri">'. __('Version', 'wp-base'). ' ' . $addon_data["Version"];
							if ( $addon_data["Author"] )
								echo ' | By ' . $addon_data["Author"];
							echo '</div>';
						?>
					</td>
				</tr>
				<?php
				}
				?>
			</table>
		</form>
		
		</div>
				
		</div>
		
		<script type="text/javascript">
		jQuery(document).ready(function($){

			var th_sel = $("th.column-delete input:checkbox");
			var td_sel = $("td.column-delete input:checkbox");
			th_sel.change( function() {
				if ( $(this).is(':checked') ) {
					td_sel.attr("checked","checked");
					th_sel.not(this).attr("checked","checked");
				}
				else{
					td_sel.removeAttr('checked');
					th_sel.not(this).removeAttr('checked');
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Add tabs to Tools
	 * @uses appointments_tools_tabs filter
	 * @return string
	 */
	function add_tab( $tabs ) {
		$tabs['licenses']	= __('Licenses', 'wp-base');
		return $tabs;
	}
	
	/**
	 * License settings HTML code 
	 */
	function license_settings() {

		wpb_admin_access_check( 'manage_licenses' );
		
		$extensions = $this->get_all_active_extensions();
		wpb_infobox( __('To activate a license on this website, enter the provided license key to the related field below and click Activate button.', 'wp-base') );
		
		?>
		
		
		<div class="postbox">
			<div class="inside">
				<table class="app_license form-table striped">
				<thead>
					<tr>
					<th class="app_license_name" style="width:30%"><?php _e( 'Addon Name', 'wp-base' ) ?></th>
					<th class="app_license_key" style="width:30%"><?php _e( 'License Key', 'wp-base' ) ?></th>
					<th class="app_license_status" style="width:15%"><?php _e( 'Status', 'wp-base' ) ?></th>
					<th class="app_license_actions" style="width:25%"><?php _e( 'Action', 'wp-base' ) ?></th>
					</tr>
				</thead>
				<?php 
				foreach ( $extensions as $file => $identifier ) { 
				
					$data = get_file_data( $file, wpb_default_headers(), 'plugin' );
					
					if ( empty( $data['ID'] ) )
						continue;
					
					if ( !empty( $data['Category'] ) && strtolower( $data['Category'] ) == 'free' )
						continue;
					
					$name			= trim( str_replace( 'WP BASE Addon: ','', $data['Name'] ) );
					$option_name	= 'wpb_license_key_' . $data['ID'];
					$license		= get_option( 'wpb_license_key_' . $data['ID'] );
					$status_option  = get_option( 'wpb_license_status_' . $data['ID'] );
					$status			= 'valid' === $status_option;
					$status_text	= $status ? '<span class="dashicons dashicons-yes" title="'.__( 'Valid and active', 'wp-base' ).'" ></span>' : '<span class="dashicons dashicons-no" title="'.__( 'Invalid, expired or inactive', 'wp-base' ).'" ></span>' ;
				?>
				<form class="app_form app_license" method="post">
					<tr>
						<th class="app_license_name" scope="row" ><?php echo $name; ?></th>
						<td class="app_license_key">
							<input name="<?php echo 'wpb_license_key_' . $data['ID'] ?>" type="text" style="width:90%" value="<?php echo $license; ?>" />
						</td>
						
						<td class="app_license_status">
							<span><?php echo $status_text ?></span>
						</td>
						
						<td class="app_license_actions">
							<input type="hidden" name="wpb_activation_file_name" value="<?php esc_attr_e( $file ) ?>" />
					<?php if ( !$status ) { ?>
							<button class="activate" ><?php _e('Activate', 'wp-base') ?></button>
							<input type="hidden" name="activate" value="1" />
							<?php wp_nonce_field( 'wpb_license_activate_nonce', 'wpb_license_activate_nonce' ); ?>
					<?php } else { ?>	
							<button class="deactivate" ><?php _e('Deactivate', 'wp-base') ?></button>
							<input type="hidden" name="deactivate" value="1" />
							<?php wp_nonce_field( 'wpb_license_deactivate_nonce', 'wpb_license_deactivate_nonce' ); ?>
					<?php } ?>
						</td>
					</tr>
				</form>
				<?php } ?>
				</table>
					
			</div>
		</div>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			$("button.activate").button({
			  icons: { primary: "ui-icon-play" }
			});
			$("button.deactivate").button({
			  icons: { primary: "ui-icon-pause" }
			});
		});
		</script>		
		<?php
	}
	
	function sanitize_license( $option, $old_value, $value ) {
		if ( 'wpb_license_key_' === substr( $option, 0, 16 ) ) {
			
			if ( $old_value && $old_value != $value ) {
				$id = str_replace( 'wpb_license_key_', '', $option );
				delete_option( 'wpb_license_status_' . $id );
			}
		}
	}
	
	function display_notice( ) {
		if ( empty( $_GET['sl_message'] ) )
			return;
		
		wpb_notice( urldecode( $_GET['sl_message'] ), 'error' );
	}

	function display_success( ) {
		if ( empty( $_GET['sl_success'] ) )
			return;
		
		wpb_notice( urldecode( $_GET['sl_success'] ) );
	}

	function handle_license_activation() {
		
		if ( empty( $_POST['wpb_activation_file_name'] ) )
			return;

		$base_url		= admin_url( 'admin.php?page=app_tools&tab=licenses' );
		$file			= $_POST['wpb_activation_file_name'];
		$data			= get_file_data( $file, wpb_default_headers(), 'plugin' );
		$name			= trim( str_replace( 'WP BASE Addon: ','', $data['Name'] ) );
		$option_name	= 'wpb_license_key_' . $data['ID'];
		$license		= isset( $_POST[$option_name] ) && update_option( $option_name, trim($_POST[$option_name]) ) ? trim($_POST[$option_name]) : get_option( $option_name );
		
		if ( empty( $license ) ) {
			wp_redirect( wpb_add_query_arg( array( 'sl_activation' => 'false', 'sl_message' => urlencode( __('Empty license key','wp-base') ) ), $base_url ) );
			exit();
		}

		if( isset( $_POST['activate'] ) ) {
			
			if( ! check_admin_referer( 'wpb_license_activate_nonce', 'wpb_license_activate_nonce' ) ) {
				wpb_notice( 'unauthorised', 'error' );
				return;
			}

			$api_params = array(
				'edd_action'=> 'activate_license',
				'license'	=> $license,
				'item_id'	=> $data['ID'],
				'url'		=> home_url(),
			);

			$response = wp_remote_post( WPB_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again', 'wp-base' );
				}

			} else {

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				if ( false === $license_data->success ) {

					switch( $license_data->error ) {

						case 'expired' :

							$message = sprintf(
								__( 'Your license key expired on %s.', 'wp-base' ),
								$this->a->dt_format, strtotime( $license_data->expires, $this->a->_time )
							);
							break;

						case 'revoked' :

							$message = sprintf( __( 'This license key has been disabled. If you think this is a mistake, please contact us using our website %s', 'wp-base' ), WPB_URL );
							break;

						case 'missing' :

							$message = __( 'Invalid license', 'wp-base' );
							break;

						case 'invalid' :
						case 'site_inactive' :

							$message = __( 'Your license is not active for this URL', 'wp-base' );
							break;

						case 'item_name_mismatch' :

							$message = sprintf( __( 'This appears to be an invalid license key for %s', 'wp-base' ), $data['Name'] );
							break;

						case 'no_activations_left':

							$message = __( 'Your license key has reached its activation limit', 'wp-base' );
							break;

						default :

							$message = __( 'An error occurred, please try again', 'wp-base' );
							break;
					}
				}
			}

			// Check if anything passed on a message constituting a failure
			if ( ! empty( $message ) ) {
				wp_redirect( wpb_add_query_arg( array( 'sl_activation' => 'false', 'sl_message' => urlencode( $message ) ), $base_url ) );
				exit();
			}

			// $license_data->license will be either "valid" or "invalid"

			update_option( 'wpb_license_status_'.$data['ID'], $license_data->license );
			$msg = sprintf( __( '%s Addon license activated','wp-base' ), $name );
			wp_redirect( wpb_add_query_arg( array( 'sl_message' =>false, 'sl_success'=> urlencode( $msg ) ), $base_url ) );
			exit();
		}


		if( isset( $_POST['deactivate'] ) ) {

			if( ! check_admin_referer( 'wpb_license_deactivate_nonce', 'wpb_license_deactivate_nonce' ) ) {
				wpb_notice( 'unauthorised', 'error' );
				return;
			}

			$api_params = array(
				'edd_action'=> 'deactivate_license',
				'license'	=> $license,
				'item_id'	=> $data['ID'],
				'url'		=> home_url(),
			);

			$response = wp_remote_post( WPB_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again', 'wp-base' );
				}

				wp_redirect( wpb_add_query_arg( array( 'sl_activation' => 'false', 'sl_message' => urlencode( $message ) ), $base_url ) );
				exit();
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if( $license_data->license == 'deactivated' ) {
				delete_option( 'wpb_license_status_'.$data['ID'] );
			}

			$msg = sprintf( __( '%s Addon license deactivated','wp-base' ), $name );
			wp_redirect( wpb_add_query_arg( array( 'sl_message' =>false, 'sl_success'=> urlencode( $msg ) ), $base_url ) );
			exit();

		}
	
	}
}
	BASE('Addons')->add_hooks();
	
endif;