<?php
/**
 * WPB Pre-Load
 *
 * Function definitions before loading of WP_BASE
 *
 * Adapted from WP Core
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

/**
 * Find blogs and uninstall tables for each of them
 * @since 1.0.2
 */
if ( !function_exists( 'wpb_uninstall' ) ) {
	function wpb_uninstall() {
		global $wpdb;
		
		remove_role( 'wpb_client' );
		remove_role( 'wpb_worker' );

		if ( function_exists('is_multisite') && is_multisite() ) {
				$network = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : "";
				$activate = isset($_GET['action']) ? $_GET['action'] : "";
				$is_network = ($network=='/wp-admin/network/plugins.php') ? true:false;
				$is_activation = ($activate=='deactivate') ? false:true;

			if ($is_network && !$is_activation){
				$old_blog = $wpdb->blogid;
				// Get all blog ids
				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					wpb_uninstall_s($blog_id);
				}
				switch_to_blog($old_blog);
				return;
			}	
		}
		// If not multisite, just make an ordinary uninstall		
		wpb_uninstall_s( );		
	}
}

if ( !function_exists( 'wpb_uninstall_s' ) ) {
	function wpb_uninstall_s( $blog_id=null ) {
		global $wpdb;
		
		// Lets ensure that this is correct blog
		if ( $blog_id && $blog_id != get_current_blog_id() ) {
			add_action( 'admin_notices', function() use ($blog_id) {
				echo '<div class="error"><p><b>[WP BASE]</b> ' . sprintf( __('Unable to uninstall WP BASE in blog %d','wp-base'), $blog_id ). '</p></div>';
			});
			return false;
		}
		
		wp_clear_scheduled_hook('app_hourly_event');
		wp_clear_scheduled_hook('app_daily_event');

		/*
		 * Only remove ALL product and page data if 
		 * 1) WPB_REMOVE_ALL_DATA constant is set to true in user's
		 * wp-config.php. This is to prevent data loss when deleting the plugin from the backend
		 * and to ensure only the site owner can perform this action.
		 * OR
		 * 2) This is a solo installation and there are no bookings
		 */
		if ( ( defined( 'WPB_REMOVE_ALL_DATA' ) && true === WPB_REMOVE_ALL_DATA ) ||
			( !is_multisite() && !$wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->prefix . "base_bookings" ) ) ) {		
				
			$ops = array( 'wp_base_options', 'wp_base_business_options', 'wp_base_texts', 'wp_base_udfs', 'wp_base_last_update',
						'wp_base_db_version', 'wp_base_salt', 'wp_base_replace_texts', 'wp_base_installed', 'wp_base_shortcodes' );
						
			foreach ( $ops as $option ) {
				delete_option( $option );
			}
			
			$wpdb->query( "DROP TABLE " . $wpdb->prefix . "base_wh_s" );
			$wpdb->query( "DROP TABLE " . $wpdb->prefix . "base_wh_w" );
			$wpdb->query( "DROP TABLE " . $wpdb->prefix . "base_wh_a" );
			$wpdb->query( "DROP TABLE " . $wpdb->prefix . "base_locations" );
			$wpdb->query( "DROP TABLE " . $wpdb->prefix . "base_services" );
			$wpdb->query( "DROP TABLE " . $wpdb->prefix . "base_workers" );
			$wpdb->query( "DROP TABLE " . $wpdb->prefix . "base_transactions" );
			$wpdb->query( "DROP TABLE " . $wpdb->prefix . "base_bookings" );
			$wpdb->query( "DROP TABLE " . $wpdb->prefix . "base_meta" );
			
			// Delete user metas
			$wpdb->query( "DELETE FROM " . $wpdb->usermeta . " WHERE meta_key='app_api_mode' OR meta_key='app_service_account' 
				OR meta_key='app_key_file' OR meta_key='app_selected_calendar' OR meta_key='app_gcal_summary'
				OR meta_key='app_gmt_offset' OR meta_key='app_timezone_string' OR meta_key='app_billing_info'		
				OR meta_key='app_gcal_description' OR meta_key LIKE 'app_dismiss%' OR meta_key LIKE 'app_udf_%' OR meta_key LIKE 'app_export_unchecked_columns'" );
			
			// Delete post metas
			$wpdb->query( "DELETE FROM " . $wpdb->postmeta . " WHERE meta_key LIKE 'app_exclude%' OR meta_key LIKE 'app_include%' OR meta_key LIKE 'app_worker%' OR meta_key LIKE 'app_service%' OR meta_key LIKE 'app_location%'" );

			// Remove all related folders with their contents
			$uploads = wp_upload_dir( null, false );
			if ( isset( $uploads["basedir"] ) )
				$uploads_dir 	= $uploads["basedir"];
			else
				$uploads_dir 	= WP_CONTENT_DIR . "/uploads";
				
			_wpb_rmdir( $uploads_dir . '/__app/' );
		}
	}
	
	// Recursively remove a folder
	function _wpb_rmdir( $dir ) {
		foreach( glob($dir . '/*') as $file ) {
			if( is_dir( $file ) )
				@_wpb_rmdir( $file );
			else
				@unlink( $file );
		}
		@rmdir( $dir );
	}
}

/**
 * Admin notice for conflicts
 * @since 2.0
 */
 
if ( !function_exists( '_wpb_plugin_conflict_own' ) ) {
	function _wpb_plugin_conflict_own(){
		echo '<div class="error"><p><b>[WP BASE]</b> '. __('Another version of WP BASE is already activated. Two versions cannot be active at the same time. Deactivate the other version to continue. Do NOT delete the other version, or you will lose previous data.','wp-base').'</p></div>';
	}
}

/**
 * Admin notice for php version
 * @since 2.0
 */
if ( !function_exists( '_wpb_plugin_php_version' ) ) {
	function _wpb_plugin_php_version(){
		echo '<div class="error"><p><b>[WP BASE]</b> '. __('WP BASE requires at least PHP V5.4. Please contact your hosting company to upgrade to the latest version (We recommend PHP V7.2).','wp-base').'</p></div>';
	}
}

/**
 * Admin notice for critical DB update requirement
 * @since 2.0
 */
if ( !function_exists( '_wpb_reactivate_required' ) ) {
	function _wpb_reactivate_required(){
		echo '<div class="error"><p><b>[WP BASE]</b> '. __('WP BASE database tables need to be updated. Please deactivate and reactivate the plugin (DO NOT delete the plugin). You will not lose any data.','wp-base').'</p></div>';
	}
}


