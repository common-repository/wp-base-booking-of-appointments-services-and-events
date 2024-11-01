<?php
/**
 * WPB Installer
 *
 * Installs the plugin
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;
 
if ( ! class_exists( 'WpBInstall' ) ) {
	 
class WpBInstall{

	/**
	 * Check version and install DB for the first visit to this blog in network activate case
	 * @since 1.0.2
	 */	
	static function auto_install() {
		$db_version = get_option( 'wp_base_db_version' );
		
		// First install is automatic upon visiting blog
		if ( !$db_version ) {
			self::install();
		} 
		else if ( version_compare( $db_version, WPB_LATEST_DB_VERSION, '<' ) && is_multisite() ) { 
			// Update is only automatic for network activate
			if ( !function_exists('is_plugin_active_for_network') ) 
				require_once(ABSPATH . '/wp-admin/includes/plugin.php');
			if ( is_plugin_active_for_network( WPB_PLUGIN_BASENAME ) ) {
				self::install();
			}
		}
	}

	/**
     * Run initial checks and Install database tables
     */
	static function install() {
		ob_start();
		
		// Preserve WpB Salt, if there is one. If not, create one
		if ( !get_option( 'wp_base_salt' ) ) {
			if ( $maybe_salt = get_option( 'appointments_salt' ) )
				add_option( 'wp_base_salt', $maybe_salt );
			else
				wpb_get_salt();
		}
		
		global $wpdb;
		
		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$columns = '';
		foreach ( range(0,287) as $no ) {
			$columns .= "`c".$no."` text,". "\n";
		}
		$columns = rtrim( $columns, "\n" );
		
		$SQL0 = "
CREATE TABLE {$wpdb->prefix}base_bookings (
	ID bigint(20) unsigned NOT null auto_increment,
	parent_id bigint(20) unsigned NOT null default '0',
	created datetime,
	user bigint(20) NOT null default '0',
	location bigint(20) NOT null default '0',
	service bigint(20) NOT null default '0',
	worker bigint(20) NOT null default '0',
	status varchar(35) default null,
	start datetime default null,
	end datetime default null,
	seats bigint(20) NOT null default '1',
	gcal_ID varchar(50) default null,
	gcal_updated datetime,
	price varchar(50) default null,
	deposit varchar(50) default null,
	payment_method varchar(35) default null,
PRIMARY KEY  (ID),
UNIQUE KEY gcal_ID (gcal_ID)
) $collate;
CREATE TABLE {$wpdb->prefix}base_transactions (
	transaction_ID bigint(20) unsigned NOT null auto_increment,
	transaction_app_ID bigint(20) NOT null default '0',
	transaction_paypal_ID varchar(30) default null,
	transaction_stamp bigint(35) NOT null default '0',
	transaction_total_amount varchar(255) default null,
	transaction_currency varchar(35) default null,
	transaction_status varchar(35) default null,
	transaction_note text,
	transaction_gateway varchar(200) default null,
	PRIMARY KEY  (transaction_ID),
	KEY transaction_app_ID (transaction_app_ID)
) $collate;
CREATE TABLE {$wpdb->prefix}base_services (
	ID bigint(20) unsigned NOT null auto_increment,
	sort_order bigint(20) NOT null default '0',
	name varchar(255) default null,
	locations text,
	capacity bigint(20) NOT null default '0',
	duration bigint(20) NOT null default '0',
	padding bigint(20) NOT null default '0',
	break_time bigint(20) NOT null default '0',
	internal tinyint(1) NOT null default '0',
	price varchar(255) default null,
	deposit varchar(255) default null,
	page bigint(20) NOT null default '0',
	categories text,
	PRIMARY KEY  (ID)
)	$collate;
CREATE TABLE {$wpdb->prefix}base_workers (
	ID bigint(20) unsigned,
	sort_order bigint(20) NOT null default '0',
	name varchar(255) default null,
	dummy varchar(255) default null,
	price varchar(255) default null,
	services_provided text,
	page bigint(20) NOT null default '0',
	PRIMARY KEY  (ID)
)	$collate;
CREATE TABLE {$wpdb->prefix}base_locations (
	ID bigint(20) unsigned NOT null auto_increment,
	sort_order bigint(20) NOT null default '0',
	name varchar(255) default null,
	capacity bigint(20) NOT null default '0',
	price varchar(255) default null,
	page bigint(20) NOT null default '0',
	PRIMARY KEY  (ID)
)	$collate;
CREATE TABLE {$wpdb->prefix}base_meta (
	meta_id bigint(20) unsigned NOT null AUTO_INCREMENT,
	object_id bigint(20) unsigned NOT null DEFAULT '0',
	meta_type varchar(50) DEFAULT null,
	meta_key varchar(50) DEFAULT null,
	meta_value longtext ,
	PRIMARY KEY  (meta_id),
	KEY object_id (object_id),
	KEY meta_type (meta_type),
	KEY meta_key (meta_key)
)	$collate;
	";
	
	$SQL2 ="
CREATE TABLE {$wpdb->prefix}base_wh_w (
	ID bigint(20) unsigned NOT null,
	$columns
	PRIMARY KEY  (ID)
)	ENGINE = MYISAM
	$collate;
CREATE TABLE {$wpdb->prefix}base_wh_s (
	ID bigint(20) unsigned NOT null,
	$columns
	PRIMARY KEY  (ID)
) 	ENGINE = MYISAM
	$collate;
CREATE TABLE {$wpdb->prefix}base_wh_a (
	ID bigint(20) unsigned NOT null,
	$columns
	PRIMARY KEY  (ID)
)	ENGINE = MYISAM
	$collate;
	";

		dbDelta( $SQL0 );

		$page_id = $service_count = 0;
		// Create a service description page on first install (no table) or no services defined (table exists but empty)
		$service_table = $wpdb->query( "SHOW TABLES LIKE '{$wpdb->prefix}base_services'" );
		if ( $service_table )
			$service_count = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->prefix}base_services" );
		if ( !$service_count )
			$page_id = self::create_default_service_page();
		// Or, there may be a sample service
		if ( !$page_id && $service_table &&
			$wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->prefix}base_services WHERE name='Sample Service' AND page=0" ) )
				$page_id = self::create_default_service_page();

		$SQL1 = "INSERT INTO {$wpdb->prefix}base_services (ID, `name`, capacity, duration, page) 
		VALUES (1, 'Sample Service', 0, 60, ".$page_id.")
		";

		// Add sample service
		if ( !$service_count )
			dbDelta( $SQL1 );
		
		dbDelta( $SQL2 );
		
		$db_version = get_option( 'wp_base_db_version' );
		
		// Update for ApB and WP BASE beta earlier versions (3.0.0Beta4<)
		if ( version_compare( $db_version, '2.0', '>=' ) && version_compare( $db_version, '3011', '<' ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}base_wh_a CHANGE `ID` `ID` BIGINT(20) UNSIGNED NOT null;" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}base_wh_s CHANGE `ID` `ID` BIGINT(20) UNSIGNED NOT null;" );
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}base_wh_w CHANGE `ID` `ID` BIGINT(20) UNSIGNED NOT null;" );
		}
		
		// This time, it is a new installation
		if ( (!$db_version || version_compare( $db_version, WPB_LATEST_DB_VERSION, '<' )) &&
			$wpdb->query( "SHOW TABLES LIKE '{$wpdb->prefix}base_bookings'" ) ) {
			update_option( 'wp_base_db_version', WPB_LATEST_DB_VERSION );			
		}
		
		if ( strpos( get_option( 'wp_base_installed' ), '|' ) !== false ) {
			list( $previous_version, $older_version, $activated ) = explode( '|', get_option( 'wp_base_installed' ) );
		}
		
		$previous_version = isset( $previous_version ) ? $previous_version : '0.0';
		update_option( 'wp_base_installed', WPB_VERSION .'|'. $previous_version.'|1' );
		
		# Default roles and capabilities
		if ( !$db_version || version_compare( $db_version, '3030', '<' ) ) {
			
			remove_role( 'wpb_client' );
			add_role( 'wpb_client', __('WP BASE Client','wp-base'), explode(',', WPB_CLIENT_CAP) );
			remove_role( 'wpb_worker' );
			add_role( 'wpb_worker', __('WP BASE Provider','wp-base'), explode(',', WPB_WORKER_CAP) );
			
			$worker_role = get_role( 'wpb_worker' );
			$worker_role->add_cap( 'manage_own_bookings' );
			$worker_role->add_cap( 'manage_own_services' );
			$worker_role->add_cap( 'manage_own_work_hours' );
			
			include_once( WPB_PLUGIN_DIR . '/includes/constant-data.php' );
			$def_roles = WpBConstant::get_default_caps();
			$admin_role = get_role( 'administrator' );
			foreach( $def_roles as $role ) {
				$admin_role->add_cap( $role );
			}
		}
		
		wp_clear_scheduled_hook( 'app_hourly_event' );
		wp_clear_scheduled_hook( 'app_daily_event' );
		wp_schedule_event( time(), 'hourly', 'app_hourly_event' );
		wp_schedule_event( time(), 'daily', 'app_daily_event' );
	}
	
	/**
	 * Create a sample service page with featured images
	 * @return integer: ID of the created page
	 * @since 2.0
	 */	
	private static function create_default_service_page(){
		global $wpdb, $blog_id;
		
		if ( is_multisite() )
			switch_to_blog( $blog_id );
		
		include_once( WPB_PLUGIN_DIR . '/includes/constant-data.php' );
		$page_title = __('Sample Service Description', 'wp-base');
		$existing_page_id = $wpdb->get_var( "SELECT ID FROM ". $wpdb->posts. " WHERE post_title = '$page_title' AND post_type='page' ");
		if ( $existing_page_id )
			return $existing_page_id;	// Already exists
		
		$default_page = array(
		  'post_content'   => WpBConstant::$_dummy_content,
		  'post_title'     => $page_title,
		  'post_status'    => 'private',
		  'post_type'      => 'page',
		); 
		
		$page_id =  wp_insert_post( $default_page );
		
		if ( $page_id && is_numeric( $page_id ) ) {
			$uploads = wp_upload_dir( null );
			$orig_filename = WPB_PLUGIN_DIR . "/images/default-service.jpg";
			$filename = $uploads["basedir"] . "/default-service.jpg";
			copy( $orig_filename, $filename );
			if ( file_exists( $filename ) ) {
				$filetype = wp_check_filetype( basename( $filename ), null );
				$attachment = array(
					'guid'           => $uploads["basedir"] . '/' . basename( $filename ), 
					'post_mime_type' => $filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				);
				$attach_id   = wp_insert_attachment( $attachment, $filename, $page_id );
				if ( $attach_id ) { 
					$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
					wp_update_attachment_metadata( $attach_id,  $attach_data );
					set_post_thumbnail( $page_id, $attach_id );
				}
			}
		}
		else
			$page_id = 0;
		
		if ( is_multisite() )
			restore_current_blog();
		
		return $page_id;
	}
	

}	
}
else {
	add_action( 'admin_notices', '_wpb_plugin_conflict_own' );
}

