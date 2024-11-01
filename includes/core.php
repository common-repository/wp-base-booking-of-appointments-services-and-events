<?php
/**
 * WPB Core
 *
 * Includes core controls and methods
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBCore' ) ):

/**
 * Instantiate and return instance of a class
 *
 * Safely returns instance preventing another instance and without the need to use globals
 * Allows addons use the same system with a WpB prefix, e.g. addon with class name WpBExtras can be instantiated with $extras_instance = BASE('Extras');
 * Allows one liner access to methods in an easily readable way, e.g. BASE('User')->get_name(3);
 * For addons, a class check should be included before using its method:
 * if ( BASE('Coupons') ) { $coupons = BASE('Coupons')->get_coupons(); }
 *
 * @since 3.0
 *
 * @uses static		$BASE
 * @param string    $identifier		Part of name of the class (WpB + $identifier: Class name)
 * @return mixed	false|object	false if class does not exists, instance object if it exists
 */	
function BASE( $identifier = 'Core' ) {
	static $BASE;
	if ( 'Core' === $identifier ) {
		if ( class_exists( 'WpBAdmin' ) )
			$identifier = 'Admin';
		else
			$identifier = 'Front';
	}
	
	$class_name = 'WpB'. $identifier;
	if ( !class_exists( $class_name ) )
		return false;
	
	if ( empty( $BASE[$class_name] ) ) {
		$BASE[$class_name] = new $class_name;
	}
	
	return $BASE[$class_name];
}

/**
 * Core class
 *
 */	
class WpBCore {

	/**
     * WpB Constants
     */
	const version = WPB_VERSION;
	
	/**
     * File locations
	 */
	public	$plugin_dir,
			$plugin_url,
			$uploads_dir,
			$uploads_url,
			$log_file,
			$gcal_image;

	 /**
     * Time variables
	 */
	public	$_time,
			$local_time,
			$start_of_week,
			$time_format,
			$date_format,
			$dt_format,
			$datetime_format;
		
	/**
     * Database Tables
	 */
	public	$app_table,
			$meta_table,
			$transaction_table,
			$locations_table,
			$services_table,
			$workers_table,
			$wh_w_table,
			$wh_s_table,
			$wh_a_table;

	/**
     * Location/Service/Worker (lsw)
	 */
	private $location = 0;
	private $service = 0;
	private $worker = 0;

	 /**
     * Constructor
     */
	function __construct() {
	
		global $wpdb;
		
		include_once( WPB_PLUGIN_DIR . '/includes/defaults.php' );

		$this->version 				= self::version;
		$this->EDGE 				= WPB_DB_DOMINANT_EDGE;	
		$this->app_name 			= str_replace( " ", "-", strtolower( WPB_NAME ) ); # Name in screens, WP BASE Dev or WP BASE
		
		$this->plugin_dir 			= WPB_PLUGIN_DIR;
		$this->plugin_url 			= $this->get_plugin_url();

		# Time variables - Preserve them during script execution
		$this->_time				= $this->local_time = apply_filters( 'app_local_time', current_time('timestamp') );
		$this->start_of_week		= get_option('start_of_week') ? get_option('start_of_week') : 0;
		$this->time_format			= get_option('time_format') ? get_option('time_format') : "H:i"; 
		$this->date_format			= get_option('date_format') ? get_option('date_format') : "Y-m-d";
		$this->dt_format			= $this->datetime_format = $this->date_format . " " . $this->time_format;

		# Database variables
		$this->db 					= $wpdb;
		$prefix 					= $wpdb->prefix;
		$this->app_table 			= $prefix . "base_bookings";
		$this->meta_table 			= $prefix . "base_meta";
		$this->transaction_table 	= $prefix . "base_transactions";
		$this->locations_table 		= $prefix . "base_locations";
		$this->services_table 		= $prefix . "base_services";
		$this->workers_table 		= $prefix . "base_workers";
		$this->wh_w_table			= $prefix . "base_wh_w";				# Version 2.0: New optimized wh for workers
		$this->wh_s_table			= $prefix . "base_wh_s";				# Version 2.0: New optimized wh for services
		$this->wh_a_table			= $prefix . "base_wh_a";				# Version 2.0: New optimized wh for alternative schedules
		
		# Set log file location
		$uploads = wp_upload_dir( null, false );
		$this->uploads_dir			= isset( $uploads["basedir"] ) ? $uploads["basedir"] : WP_CONTENT_DIR . "/uploads";
		$this->log_file 			= $this->uploads_dir . "/appointments-log.txt";
		$this->tabletools_file		= $this->custom_folder( true ) . '/copy_csv_xls_pdf.swf';

		# Also define uploads_url
		$this->uploads_url 			= isset( $uploads["baseurl"] ) ? $uploads["baseurl"] : WP_CONTENT_URL . "/uploads";
		$this->gcal_image 			= apply_filters( 'app_gcal_button', '<img src="' . $this->plugin_url . '/images/gc_button1.gif" alt="GCal button" />' );
		
		# Default for Temp vars
		$this->script 				= $this->list_script = $this->bp_script = '';
		$this->nof_datatables 		= $this->nof_datatables_book = $this->nof_app = 0;
		$this->checkout_error 		= false;
	
	}

	/**
     * Add files and actions
	 * Called by Front or Admin
     */
	function add_hooks() {
		
		include_once( WPB_PLUGIN_DIR . '/includes/notices.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/meta.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/class.calendar.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/class.slot.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/class.booking.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/functions.general.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/functions.internal.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/functions.booking.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/multiple.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/custom-texts.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/gateways.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/addons.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/user.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/wh.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/holidays.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/debug.php' );									
		include_once( WPB_PLUGIN_DIR . '/includes/compat.php' );								
		include_once( WPB_PLUGIN_DIR . '/includes/schedules.php' );
		include_once( WPB_PLUGIN_DIR . "/includes/custom-functions.php" );

		if ( is_multisite() )
			include_once( WPB_PLUGIN_DIR . '/includes/mu.php' );

		add_action( 'init', array( $this, 'localization' ) );									// Localize the plugin
		add_action( 'init', array( $this, 'continue_tutorial' ), 0 );							// Continue tutorial for admin users
		add_action( 'init', array( $this, 'ajax_init' ), 1 );									// Call ajax functions if required
		add_action( 'init', array( $this, 'after_installed' ), 2 );								// Create key file folder, etc
		add_action( 'init', array( $this, 'select_theme' ), 3 ); 								// Initial stuff, earlier
		add_action( 'widgets_init', array( $this, 'widgets_init' ) );							// Load widgets
		add_action( 'wp_loaded', array( $this, 'do_tasks' ) ); 									// Do scheduled tasks
		add_action( 'wp_loaded', array( $this, 'custom_date_time' ), 2000 );					// Allow custom date/time (formats) possible
		add_filter( 'cron_schedules', array( $this, 'add_schedules' ) );						// Add weekly and time_base_tick schedules
		add_action( 'wp', array( $this, 'create_tb_tick_event' ) );								// Create time base tick event
		add_action( 'app_settings_changed', array( $this, 'reschedule_tb_tick_event' ), 10, 2 );
		
		do_action( 'app_loaded' );
	}
	
	
/*******************************
* Methods for inits, styles, js
********************************
*/
	
	/**
	 * Get *installed* DB version (Not latest DB version, not script version)
	 * @since 2.0
	 */	
	function get_db_version(){
		if ( !isset( $this->db_version ) )
 			$this->db_version = get_option( 'wp_base_db_version' );
		
		return $this->db_version;
	}
	
	/**
	 * Get full url of the plugin
	 * @since 2.0
	 */	
	function get_plugin_url() {
		return apply_filters( 'app_plugin_url', WPB_PLUGIN_URL );
	}

	/**
	 * Return if this is SSL (Forced to be SSL, not if page is https or not) 
	 * @since 2.0
	 */	
	function is_ssl() {
		return apply_filters( 'app_is_ssl', false );
	}
	
	/**
	 * Load admin if a tutorial is interrupted
	 */
	public function continue_tutorial() {
		
		if ( !current_user_can( WPB_TUTORIAL_CAP ) )
			return false;
		
		wpb_session_start();
		
		if ( !$session_val = wpb_get_session_val('app_tutorial_continue') ) 
			return false;
		
		if ( !empty( $session_val['app_tutorial1'] ) ) {
			include_once( WPB_PLUGIN_DIR . '/includes/admin/base-admin.php' );
			return true;
		}
	}
	
	/**
	 * Initiate Session class
	 * @since 3.0
	 * @return WPB_Session object
	 */
	public function session(){
		include_once( WPB_PLUGIN_DIR . '/includes/class.session.php' );
		return new WPB_Session();
	}

	/**
	 * Include ajax class
	 * @return none
	 */
	public static function ajax_init() {
		if ( empty( $_POST['wpb_ajax'] ) )
			return;
		
		if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
			@ini_set( 'display_errors', 0 );
		}
		$GLOBALS['wpdb']->hide_errors();
		
		if ( !defined( 'WPB_AJAX' ) )
			define( 'WPB_AJAX', true );
		
		include_once( WPB_PLUGIN_DIR . '/includes/front-ajax.php' );
	}

	/**
	 * Initialize widgets
	 * @return none
	 */	
	function widgets_init() {
		if ( !is_blog_installed() )
			return;

		include_once( WPB_PLUGIN_DIR . '/includes/widget-helper.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/widgets.php' );

		register_widget( 'WpB_Widget_Services' );
		register_widget( 'WpB_Widget_Service_Providers' );
		register_widget( 'WpB_Widget_Monthly_Calendar' );
		register_widget( 'WpB_Widget_Theme_Selector' );
	}
	
	/**
	 * Do something after new installation or update, once only
	 * @since 2.0
	 * @return none
	 */
	function after_installed() {
		$activated = false;
		$installed_version = $previous_version = '0.0';
		if ( strpos( get_option( 'wp_base_installed' ), '|' ) !== false )
			list( $installed_version, $previous_version, $activated ) = explode( '|', get_option( 'wp_base_installed' ) );
		
		# (Re)create a list of shortcodes
		include_once( WPB_PLUGIN_DIR . '/includes/constant-data.php' );
		update_option( 'wp_base_shortcodes', array_keys( WpBConstant::shortcode_desc() ) );

		if ( version_compare( $installed_version, $previous_version, '>' ) ) {
			# New installation
			if ( '0.0' == $previous_version ) {
				# Since wp-cron can be affected by other plugins, using this fail-safe method instead
				add_option( "wp_base_last_update", time() );
				
				add_option( 'wp_base_options', WpBConstant::defaults() );

				$this->create_custom_folder( );
				
			}
			
			# New Installation + Upgrade
			update_user_meta( get_current_user_id(), 'app_welcome', WPB_VERSION ); 
			update_option( 'wp_base_installed', WPB_VERSION .'|'. WPB_VERSION .'|0' );
			
			# Check for settings - Versions 2 has appointments_options which can be imported
			if ( !get_option( 'wp_base_options' ) ) {
				add_option( 'wp_base_options', WpBConstant::defaults() );
			}
			
			# Copy Tabletools to custom folder so that we don't reveal plugins folder url
			$tabletools_file = 'copy_csv_xls_pdf.swf';
			copy( WPB_PLUGIN_DIR . '/js/' . $tabletools_file, $this->custom_folder(). '/'. $tabletools_file );
			
			do_action( 'app_installed', $installed_version, $previous_version );
		}
		else if ( $activated ) {
			# No install or update, just activation
			update_option( 'wp_base_installed', $installed_version .'|'. $previous_version .'|0' );
			wpb_rebuild_menu();
			do_action( 'app_activated', $installed_version, $previous_version );
		}
	}

	/**
	 * Try to create a custom css folder
	 * @since 1.2.2
	 * @return none
	 */
	function create_custom_folder( ) {
		if ( !is_dir( $this->uploads_dir . '/__app/css/' ) )
			@mkdir( $this->uploads_dir . '/__app/css/', 0777, true );
	}
	
	/**
	 * Return Alternative css folder name
	 * @param url: Return url of folder instead of dir
	 * @since 2.0
	 * @return string
	 */
	function custom_folder( $url = false ) {
		if ( $url )
			return WP_CONTENT_URL . '/uploads/__app/';
		else
			return $this->uploads_dir . '/__app/';
	}

	/**
	 *	Save theme selection from front end to user session cookie
	 *	@since 2.0
	 */
	function select_theme() {
		if ( isset( $_GET['app_select_theme']) ) {
			# Save to session variable according to connected device
			if ( wpb_is_mobile() ) {
				if ( in_array( $_GET['app_select_theme'], $this->get_themes( true ) ) )
					$this->sel_mobile_theme = wpb_set_session_val('app_mobile_theme', $_GET['app_select_theme']);
			}
			else {
				if ( in_array( $_GET['app_select_theme'], $this->get_themes() ) )
					$this->sel_theme = wpb_set_session_val('app_theme', $_GET['app_select_theme']);
			}
		}
	}
	
	/**
	 * Find which theme is selected server wise or user wise. 
	 * Also checks if css file really exists. If not, chooses default theme
	 * @param admin: Selected for admin side
	 * @since 2.0
	 * @return string
	 */		
	function selected_theme( $admin = false ) {
		
		$admin = wpb_is_admin() ? true : false;
		$mobile = wpb_is_mobile() && !$admin ? true : false;

		if ( $mobile ) {
			$server_theme = in_array( wpb_setting('mobile_theme'), $this->get_themes(true) ) ? wpb_setting('mobile_theme') : 'jquery-mobile';
			$active_theme = isset( $this->sel_mobile_theme ) && in_array( $this->sel_mobile_theme, $this->get_themes(true) ) ? $this->sel_mobile_theme : false;
			if ( !$active_theme )
				$active_theme = in_array( wpb_get_session_val('app_mobile_theme'), $this->get_themes(true) ) ? wpb_get_session_val('app_mobile_theme') : $server_theme;
		}
		else {
			if ( $admin ) {
				$active_theme = in_array( wpb_setting('admin_theme'), $this->get_themes() ) ? wpb_setting('admin_theme') : 'smoothness';
			}
			else {
				$server_theme = in_array( wpb_setting('theme'), $this->get_themes() ) ? wpb_setting('theme') : 'start';
				$active_theme = isset( $this->sel_theme ) && in_array( $this->sel_theme, $this->get_themes() ) ? $this->sel_theme : false;
				if ( !$active_theme )
					$active_theme = in_array( wpb_get_session_val('app_theme'), $this->get_themes() ) ? wpb_get_session_val('app_theme') : $server_theme;
			}
		}
		
		return apply_filters( 'app_selected_theme',  $active_theme );
	}

	/**
	 * Get url of selected theme file
	 * @param admin: Selected for admin side
	 * @return string
	 * @since 2.0
	 */
	function get_theme_file( ) {
		# A custom theme can be used, e.g. different files for different pages
		if ( $maybe_file = apply_filters( 'app_theme_file', '' ) )
			return $maybe_file;

		$mobile = wpb_is_mobile() && !wpb_is_admin() ? true : false;
		$folder = $mobile ? 'css-mobile/' : 'css/';

		$theme = strtolower( $this->selected_theme( ) );
		$accepted_filenames = array( $theme.".css", "jquery-ui.theme.min.css", "style.css" );
		
		# First check custom theme file in /uploads/_app/ folder, if any
		foreach(  $accepted_filenames as $filename ) {
			if ( file_exists( $this->custom_folder() . $folder . $theme . "/". $filename ) )
				return $this->custom_folder( true ) . $folder . $theme . "/" . $filename ;
		}
		
		# Then load css file from /plugins/wp-base/css folder
		foreach(  $accepted_filenames as $filename ) {
			if ( file_exists( $this->plugin_dir . '/'. $folder . $theme . "/". $filename ) )
				return $this->plugin_url . '/'. $folder . $theme . "/" . $filename ;
		}
		
		if ( $mobile && class_exists( 'WpBPro' ) ) {
			$pro_folder = 'css-mobile-advanced/';
			# Then load css file from /plugins/wp-base/css-mobile-advanced/ folder
			foreach(  $accepted_filenames as $filename ) {
				if ( file_exists( $this->plugin_dir . '/'. $pro_folder. $theme . "/". $filename ) )
					return $this->plugin_url . '/'. $pro_folder. $theme . "/" . $filename ;
			}
		}

		# If we could not find any file, still try to load, so that browser gives a warning
		return $this->plugin_url . '/'. $folder . $theme . "/" . "style.css" ;
	}	

	/**
	 * Return all available themes under /css/ folder of 1) plugin_dir/ 2) uploads/__app/
	 * @param mobile: Determines which folder to check. if true css-mobile/, if false css/
	 * @return array
	 * @since 2.0
	 */		
	function get_themes( $mobile = false ) {
		$folder = $mobile ? 'css-mobile/' : 'css/';
		$pro_folder = 'css-mobile-advanced/';

		$dirs = glob( $this->plugin_dir . '/'. $folder . '*' , GLOB_ONLYDIR);
		# Also check folders under key file folder
		$dirs2 = glob( $this->custom_folder( ) . $folder . '*' , GLOB_ONLYDIR);
		$dirs = array_merge( (array)$dirs, (array)$dirs2 );
		if ( $mobile ) {
			$dirs3 = glob( $this->plugin_dir . '/'. $pro_folder. '*' , GLOB_ONLYDIR);
			$dirs = array_merge( (array)$dirs, (array)$dirs3 );
		}
		$out = array();
		$accepted_filenames = array( "jquery-ui.theme.min.css", "style.css");
		if ( is_array( $dirs ) ) {
			foreach( $dirs as $dir ) {
				foreach( $accepted_filenames as $filename ) {
					if ( file_exists( $dir . '/' . $filename ) ) {
						$out[] = str_replace( array($this->plugin_dir . '/'. $folder, $this->custom_folder( ) . $folder,  $this->plugin_dir . '/' . $pro_folder), '', $dir ); // Get rid of absolute address
						break; // One file per folder is enough
					}
				}
			}
		}
		sort( $out );
		return apply_filters( 'app_themes', $out, $mobile );
	}

	/**
	 * Get url of front.css file
	 * @return string
	 * @since 2.0
	 */
	function get_front_css_file() {
		# A custom css file can be used, e.g. different files for different pages
		if ( $maybe_file = apply_filters( 'app_front_css_file', '' ) )
			return $maybe_file;
		
		# If file exists in the custom folder, use it first. If not, use files inside the plugin css directory.
		if ( file_exists( $this->custom_folder() . "css/front.css" ) )
			return $this->custom_folder( true ) . "css/front.css" ;
		else
			return $this->plugin_url . "/css/front.css";
	}

	/**
	 * Get url of admin.css file
	 * @return string
	 * @since 2.0
	 */
	function get_admin_css_file() {
		# If file exists in key file folder, use it first. If not, use files inside the plugin css directory.
		if ( file_exists( $this->custom_folder() . "css/admin.css" ) )
			return $this->custom_folder( true ) . "css/admin.css" ;
		else
			return $this->plugin_url . "/css/admin.css";
	}
	
	/**
	 *	Allow overwriting of date/time formats
	 *	@since 3.0
	 */
	function custom_date_time() {
		$this->time_format			= apply_filters( 'app_time_format', $this->time_format ); 
		$this->date_format			= apply_filters( 'app_date_format', $this->date_format );
		$this->dt_format			= $this->datetime_format = $this->date_format . " " . $this->time_format;
	}

	/**
	 *	Do tasks like update appointments, send reminders
	 *	@since 2.0
	 */
	function do_tasks() {

		if ( !empty( $_GET['app_empty_cart'] ) ) {
			if ( check_ajax_referer( 'front', false, false ) )
				BASE('Multiple')->empty_cart();
			
			$this->update_appointments();
			
			wp_redirect( wpb_add_query_arg( array( 'app_empty_cart' => false, '_ajax_nonce' => false ) ) );
			exit;
		}

		// BASE('Multiple')->check_cart();
		
		# Do not execute scheduled tasks during ajax
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;
		
		// if ( defined( 'DOING_CRON' ) && DOING_CRON )
			// return;
			
		//  Run this code not before 1 min
		if ( ( $this->_time - get_option( "wp_base_last_update" ) ) < apply_filters( 'app_update_time', 60 ) ) 
			return;
		
		$this->update_appointments();
		do_action( 'app_cron' );
	}
	
	/**
	 *	Get the command to run cron - to be deprecated and replaced by background processing
	 *	@since 2.0
	 *	@return string
	 */
	function get_cron_command() {
		return WPB_PLUGIN_DIR .'/includes/lib/cron.php';
	}
	
	/**
	 * Register new cron schedules
	 *
	 * @since 3.0
	 *
	 * @param array $schedules
	 * @return array
	 */
	public function add_schedules( $schedules = array() ) {
		# Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'wp-base' )
		);

		$schedules['wpb_time_base_tick'] = array(
			'interval' => $this->get_min_time() * 60,
			'display'  => __( 'On WpB time base tick', 'wp-base' )
		);

		return $schedules;
	}

	/**
	 *	Create an event which fires at each Time Base tick
	 *	@since 3.0
	 */	
	function create_tb_tick_event() {
		if ( ! wp_next_scheduled( 'app_time_base_tick' ) ) {
			wp_schedule_event( strtotime( current_time( 'Y-m-d' ) ) - 24*3600, 'wpb_time_base_tick', 'app_time_base_tick' );
		}
	}
	
	/**
	 *	When Time Base setting has been changed, reset/reschedule the scheduled event
	 *	@since 3.0
	 */	
	function reschedule_tb_tick_event( $old_options, $options ) {
		if ( !empty( $options['min_time'] ) && !empty( $old_options['min_time'] ) && $old_options['min_time'] != $options['min_time'] )
			wp_reschedule_event( strtotime( current_time( 'Y-m-d' ) ) - 24*3600, 'wpb_time_base_tick', 'app_time_base_tick' );
	}

	/**
	 *	Allow addons modify client offset relative to server time
	 *	@param date_timestamp: Date or timestamp in server time
	 *  Client offset is dependant on a time reference because of daylight savings switchovers
	 *	@return integer (in secs)
	 *	@since 2.0
	 */	
	function get_client_offset( $date_timestamp=0, $timezone=false ) {
		if ( isset( $this->client_offset ) && false === $timezone )
			return $this->client_offset;
		
		$date_timestamp = $date_timestamp ? $date_timestamp : $this->_time;
		$client_offset = apply_filters( 'app_client_offset', null, $date_timestamp, $timezone );
		$this->client_offset = $client_offset = ($client_offset === null || $client_offset === false) ? 0 : $client_offset;
		
		return $client_offset;
	}
	
	/**
	 *	Return timestamp adjusted to client's location
	 *	@param time: timestamp in local server time
	 *	@timezone: Timezone string saved in DB, e.g. when sending a reminder message
	 *	@return integer
	 *	@since 2.0
	 */	
	function client_time( $time, $timezone=false ){
		return $time + $this->get_client_offset( $time, $timezone );
	}
	
/**********************************************************
* Localizations
*
***********************************************************
*/
	/**
	 * Localize the plugin
	 * @uses plugins_loaded action hook
     */
	function localization() {
		/* 
		* Load up the localization file if we're using WordPress in a different language
		* Place it in /wp-content/plugin/languages/ folder and name it "wp-base-[value in wp-config].mo"
		* For details see: https://codex.wordpress.org/Function_Reference/load_plugin_textdomain
		*/
		load_plugin_textdomain( 'wp-base', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}
	
	/**
     * Get locale string
	 * @uses wpml_current_language filter
	 * @uses Polylang pll_current_language function
	 * @since 2.0
     */
	function get_locale() {
		$locale = function_exists('pll_current_language') ? pll_current_language('locale') : get_locale();
		return apply_filters( 'wpml_current_language', preg_replace('/_/', '-', $locale ) );
	}

	/**
	 * Return a safe date format that datepicker can use
	 * @return string
	 * @since 1.0.4.2
	 */	
	function safe_date_format() {
		# Allowed characters
		$check = str_replace( array( '-', '/', ',', 'F', 'j', 'y', 'Y', 'd', 'M', 'm' ), '', $this->date_format );
		if ( '' == trim( $check ) )
			return $this->date_format;
			
		# If an unallowed character found, return a default safe format
		return 'F j Y';
	}
	
/**********************************************************
* General Methods
*
* $l: location ID
* $s: service ID
* $w: worker ID
* $stat: Status (open: working, closed: not working)
***********************************************************
*/

	/**
	 * Set location, service, worker
	 * @param $l	integer|false	Location ID
	 * @param $s	integer|false	Service ID
	 * @param $w	integer|false	Worker ID
	 * @since 3.0
	 */
	function set_lsw( $l, $s, $w ) {
		if ( false !== $l && '' !== $l )
			$this->location = $l;
		if ( false !== $s && '' !== $s )
			$this->service = $s;
		if ( false !== $w && '' !== $w )
			$this->worker = $w;
	}

	/**
	 * Allow only certain order_by clauses
	 * @since 1.2.8
	 * @return string
	 */
	function sanitize_order_by( $order_by ) {
		if ( !$order_by )
			return 'ID';
			
		$whitelist = apply_filters( 'app_order_by_whitelist', array( 'id', 'sort_order', 'name', 'start', 'end', 'duration', 'price', 
					'id desc', 'sort_order desc', 'name desc', 'start desc', 'end desc', 'duration desc', 'price desc', 'rand()' ) );
		
		$temp = explode( ',', wpb_sanitize_commas( str_replace( ' ', ',', $order_by ) ) );
		foreach ( $temp as $order ) {
			if ( !in_array( strtolower($order), $whitelist ) )
				return 'ID';
		}
		
		if ( stripos ( $order_by, 'price' ) !== false ) {
			$order_by = str_replace( 'price', 'cast(price as unsigned)', $order_by );
		}

		return $order_by;
	} 
	
	/**
	 * Get location ID
	 * @since 2.0
	 */
	function read_location_id() {
		if ( !empty( $_REQUEST["app_location_id"] ) ) {
			if ( is_numeric( $_REQUEST["app_location_id"] ) )
				return (int)$_REQUEST["app_location_id"];
			else
				return $this->find_location_id_from_name( urldecode($_REQUEST["app_location_id"]) ); 
		}
		else if ( !empty( $_REQUEST["app_location"] ) ) {
			return $this->find_location_id_from_name( urldecode($_REQUEST["app_location"]) ); 
		}
		else	
			return 0;
	}
	
	/**
	 * Get number of locations
	 * @since 2.0
	 */
	function get_nof_locations() {
		return apply_filters( 'app_get_nof_locations', 0 ); 
	}

	/**
	 * Get all locations
	 * @param order_by: ORDER BY clause for mysql
	 * @return array of objects
	 * @since 2.0
	 */	
	function get_locations( $order_by = "sort_order" ) {
		return apply_filters( 'app_get_locations', null, $order_by );
	}

	/**
	 * Get a single location with given ID
	 * @param ID: Id of the service to be retrieved
	 * @since 2.0
	 * @return object
	 */	
	function get_location( $ID ) {
		return apply_filters( 'app_get_location', null, $ID );
	}

	/**
	 * Return $location property
	 * @since 3.0
	 * @return mixed
	 */	
	function get_lid(){
		return BASE()->location;
	}
	
	/**
	 * Find location name given its ID
	 * @since 2.0
	 * @return string
	 */	
	function get_location_name( $ID=0 ) {
		return stripslashes( apply_filters( 'app_location_name', $this->get_text('not_defined'), $ID ) );
	}

	/**
	 * Find location ID given its name
	 * @since 3.0
	 * @return integer
	 */	
	function find_location_id_from_name( $name ) {
		return (int)apply_filters( 'app_find_location_id_from_name', 0, $name );
	}

	/**
	 * Check if a location exists
	 * @param ID: Id of the location to be checked
	 * @since 3.0
	 * @return bool
	 */	
	function location_exists( $ID ) {
		$test = $this->get_location( $ID );
		return isset( $test->ID );
	}

	/**
	 * Get locations given a specific service
	 * @param ID: Id of the service
	 * @param order_by: ORDER BY clause for mysql
	 * @return array of objects
	 * @since 3.0
	 */	
	function get_locations_by_service( $ID, $order_by = "sort_order" ) {
		$locations = has_filter( 'app_get_locations_by_service' ) ? null : $this->get_locations( $order_by );
		return apply_filters( 'app_get_locations_by_service', $locations, $ID, $order_by );
	}

	/**
	 * Get locations given a specific worker
	 * @param ID: Id of the worker
	 * @param order_by: ORDER BY clause for mysql
	 * @return array of objects
	 * @since 3.0
	 */	
	function get_locations_by_worker( $ID, $order_by = "sort_order" ) {
		$locations = has_filter( 'app_get_locations_by_worker' ) ? null : $this->get_locations( $order_by );
		return apply_filters( 'app_get_locations_by_worker', $locations, $ID, $order_by );
	}

	/**
	 * Get number of categories
	 * @since 2.0
	 */
	function get_nof_categories() {
		return apply_filters( 'app_get_nof_categories', 0 ); 
	}

	/**
	 * Get all categories
	 * @param order_by: ORDER BY clause
	 * @return array of objects
	 * @since 2.0
	 */	
	function get_categories( $order_by = "sort_order" ) {
		return apply_filters( 'app_get_categories', null, $order_by );
	}

	/**
	 * Find category name given its ID
	 * @since 3.0
	 * @return string
	 */	
	function get_category_name( $ID ) {
		return apply_filters( 'app_category_name', '', $ID );
	}
	
	/**
	 * Try to find category name given service ID
	 * We can only know exact category of a service if 
	 * 1) Service has only one category or 
	 * 2) Client picked a category on front end, e.g. on service pulldown when optgroup category is enabled
	 * For the other cases, the first category of service will be returned, if any
	 * @since 3.0
	 * @return string
	 */	
	function guess_category_name( $service_id ) {
		
		$cat_id = !empty( $_POST['app_category'] ) ? $_POST['app_category'] : 0;
		
		if ( !$cat_id ) {
			$service = $this->get_service( $service_id );
			if ( !empty( $service->categories ) ) {
				$cats = wpb_explode( $service->categories );
				if ( !empty( $cats ) ) {
					$cat_id = current( $cats );
				}
			}
		}
		
		return apply_filters( 'app_guess_category_name', $cat_id ? $this->get_category_name( $cat_id ) : '', $service_id );
	}

	/**
	 * Check if a service is in a certain category
	 * @param $category_id	integer			Category id to be checked
	 * @param $service 		integer|object	Service id or service object	
	 * @since 2.0
	 */
	function is_in_category( $category_id, $service ) {
		if ( is_numeric( $service ) )
			$service = $this->get_service( $service );
		$cats = isset( $service->categories ) ? wpb_explode( $service->categories ) : array();
		return in_array( $category_id, (array)$cats );
	}

	/**
	 * Get number of services
	 * @since 2.0
	 */
	function get_nof_services() {
		if ( isset( $this->nof_services ) )
			return $this->nof_services;
		else {
			$this->nof_services = $this->db->get_var( "SELECT COUNT(*) FROM " . $this->services_table . " " );
			return $this->nof_services;
		}
	}

	/**
	 * Get smallest service ID or Sorted as first
	 * @return integer
	 */	
	function get_first_service_id() {
		$identifier = wpb_cache_prefix() . 'min_service_id';
		$min = wp_cache_get( $identifier );
		if ( false === $min ) {
			$min = $this->db->get_var( "SELECT ID FROM " . $this->services_table . " WHERE capacity >= 0 AND internal=0 ORDER BY sort_order, ID LIMIT 1" );
			if ( !$min )
				$min = 0;

			wp_cache_set( $identifier, $min );
		}
		
		return apply_filters( 'app_get_first_service_id', $min );
	}

	/**
	 * Read service ID from front end
	 * @return integer
	 */
	function read_service_id() {
		$service_id = 0;
		
		if ( isset( $_REQUEST["app_service_id"] ) ) {
			if ( is_numeric( $_REQUEST["app_service_id"] ) )
				$service_id = (int)$_REQUEST["app_service_id"];
			else if ( 'all' === $_REQUEST["app_service_id"] )
				$service_id = 'all';
			else
				$service_id = $this->find_service_id_from_name( urldecode($_REQUEST["app_service_id"]) ); 
		}
		else if ( isset( $_REQUEST["app_service"] ) ) {
			$service_id = $this->find_service_id_from_name( urldecode($_REQUEST["app_service"]) ); 
		}
		// else if ( 'no' != wpb_setting( 'preselect_first_service' ) )
			// $service_id = $this->get_first_service_id();
		
		return apply_filters( 'app_get_service_id', $service_id );
	}

	/**
	 * Get service ID using its name
	 * @since 2.0
	 * @return integer
	 */	
	function find_service_id_from_name( $name ) {
		$name = esc_sql( strtolower( $name ) );
		$identifier = wpb_cache_prefix() . 'service_ID_' . str_replace( ' ', '_', $name );
		$ID = wp_cache_get( $identifier );
		
		if ( false === $ID ) {
			$ID = $this->db->get_var( "SELECT ID FROM " . $this->services_table . " WHERE LOWER(name)='". $name ."' " );
			$ID = $ID ? $ID : $this->get_first_service_id();
			wp_cache_set( $identifier, $ID );
		}
			
		return $ID;
	}

	/**
	 * Get all services
	 * @param order_by: ORDER BY clause for mysql
	 * @param $exclude_internal: If set true, do not return internal services
	 * @param $category	integer	Optional category of service
	 * @return array of objects
	 */	
	function get_services( $order_by = "sort_order", $exclude_internal = false, $category = false ) {
	
		$order_by	= $this->sanitize_order_by( $order_by );
		$ex_text	= $exclude_internal ? "1" : "0";
		$cat_text	= $category && is_numeric($category) ? $category : "0";
		$identifier	= wpb_cache_prefix() . 'all_services_' . str_replace( ' ', '_', $order_by ) .'_'. $ex_text. '_'. $cat_text;
		
		$services = wp_cache_get( $identifier );
		
		if ( false === $services ) {
			$q = $exclude_internal ? "internal=0 AND" : "1=1 AND";
			$q .= $category && is_numeric($category) ? " category LIKE '%:".$category .":%'": "";
			$q = rtrim( $q, "AND" );
			$services = $this->db->get_results("SELECT * FROM " . $this->services_table . " WHERE ". $q. " ORDER BY ". $order_by ." ", OBJECT_K );
			wp_cache_set( $identifier, $services );
		}
		
		return $services;
	}
	
	/**
	 * Return $service property
	 * @since 3.0
	 * @return mixed
	 */	
	function get_sid(){
		return BASE()->service;
	}
	
	/**
	 * Return a list of all service IDs
	 * @since 3.0
	 * @return array
	 */	
	function get_service_ids(){
		$services = $this->get_services();
		if ( empty( $services ) )
			return array();
		
		return array_keys( $services );
	}
	
	/**
	 * Check if a service exists
	 * @param ID: Id of the service to be checked
	 * @since 2.0
	 * @return bool
	 */	
	function service_exists( $ID ) {
		$test_service = $this->get_service( $ID );
		return isset( $test_service->ID );
	}

	/**
	 * Get a single service with given ID
	 * @param ID: Id of the service to be retrieved
	 * @return object
	 */	
	function get_service( $ID ) {
		$identifier = wpb_cache_prefix() . 'service_'. $ID;
		$service = wp_cache_get( $identifier );
		if ( false === $service ) {
			$services = $this->get_services();
			if ( isset( $services[$ID] ) )
				$service = $services[$ID];
			else
				$service = null;

			wp_cache_set( $identifier, $service );
		}
		
		return apply_filters( 'app_get_service', $service, $ID );
	}
	
	/**
	 * Return IDs of daily (all day) services
	 * @since 2.0
	 * @return array
	 */	
	function get_daily_service_ids( ) {
		$daily_services = array();
		if ( $this->get_nof_services() > $this->EDGE ) {
			$daily_services = $this->db->get_col( "SELECT * FROM " . $this->services_table . " WHERE duration=1440 " );
		}
		else {
			$services = $this->get_services( );
			foreach ( $services as $service ) {
				if ( $this->is_daily( $service->ID ) )
					$daily_services[] = $service->ID;
			}
		}
		
		return $daily_services;
	}

	/**
	 * Find if a service lasts *all day*
	 * is_daily is used instead of is_allday
	 * @since 2.0
	 * @return bool
	 */	
	function is_daily( $ID = 0 ) {
		$result = false;
		
		if ( !$ID )
			$ID = $this->get_sid();
		
		$service = $this->get_service( $ID );
		
		if ( !empty( $service->duration ) && $service->duration >= 1440 )
			$result = true;
			
		return apply_filters( 'app_is_daily', $result, $ID );
	}
	
	/**
	 * Find if a service is package
	 * @since 2.0
	 * @return bool
	 */	
	function is_package( $ID = 0 ) {
		if ( !$ID )
			$ID = $this->get_sid();
		
		if ( BASE('Packages') && BASE('Packages')->is_package( $ID ) )
			return true;
			
		return false;
	}

	/**
	 * Check if an appt is booked as a package
	 * @since 2.0
	 * @return mixed	false|integer	false if not package, service ID if booked as package
	 */	
	function is_app_package( $app_id ) {
		if ( !$app_id )
			return false;
		
		if ( BASE('Packages') && $main = BASE('Packages')->is_app_package( $app_id ) )
			return $main;
			
		return false;
	}
	
	/**
	 * Find if a service is recurring
	 * @since 2.0
	 * @return bool
	 */	
	function is_recurring( $ID = 0 ) {
		if ( !$ID )
			$ID = $this->get_sid();
		
		if ( BASE('Recurring') && BASE('Recurring')->is_recurring( $ID ) )
			return true;
			
		return false;
	}
	
	/**
	 * Check if an appt is booked as recurring
	 * @since 2.0
	 * @return bool
	 */	
	function is_app_recurring( $app_id ) {
		if ( !$app_id )
			return false;
		
		if ( BASE('Recurring') && BASE('Recurring')->is_app_recurring( $app_id ) )
			return true;
			
		return false;
	}
	
	/**
	 * Get services given a specific category
	 * @param ID: Id of the category
	 * @param order_by: ORDER BY clause for mysql
	 * @since 2.0
	 * @return array of objects
	 */	
	function get_services_by_category( $ID, $order_by = "sort_order", $exclude_internal = false ) {
		$services = has_filter( 'app_get_services_by_category' ) ? array() : $this->get_services( $order_by );
		return apply_filters( 'app_get_services_by_category', $services, $ID, $order_by, $exclude_internal );
	}

	/**
	 * Get services given a certain worker
	 * @param ID: ID of the worker
	 * @since 1.2.3
	 * @return array of objects, key being the service_id
	 */	
	function get_services_by_worker( $ID, $order_by = "sort_order" ) {
		$services = has_filter( 'app_get_services_by_worker' ) ? array() : $this->get_services();
		return apply_filters( 'app_get_services_by_worker', $services, $ID, $order_by );
	}

	/**
	 * Get service with min or max duration (i.e. shortest or longest) given a certain worker
	 * @param ID: ID of the worker
	 * @param max: If true, gives max. Otherwise min service
	 * @since 2.0
	 * @return object
	 */	
	function get_min_max_service_by_worker( $ID, $max = false ) {
		$identifier = wpb_cache_prefix() . ( $max ? 'max_service_by_worker_' . $ID : 'min_service_by_worker_' . $ID );
		$min_max_service_by_worker = wp_cache_get( $identifier );
		
		if ( false === $min_max_service_by_worker ) {
			$service_ids = '';
			$worker = $this->get_worker( $ID );
			if ( isset( $worker->services_provided ) ) {
				$service_ids = trim( str_replace( ":",",", $worker->services_provided ), "," );
				if ( $service_ids ) {
					$desc = $max ? " DESC " : "";
					$service_ids = "(" . rtrim( $service_ids, "," ) . ")";
					$min_max_service_by_worker = $this->db->get_row( "SELECT * FROM " . $this->services_table . " WHERE ID IN " . $service_ids . " AND capacity>=0 ORDER BY (duration+break_time+padding),duration ".$desc." LIMIT 1" );
				}
			}
			
			wp_cache_set( $identifier , $min_max_service_by_worker );
		}
		
		return $min_max_service_by_worker;
	}

	/**
	 * Get the min or max worker price of for a service (i.e. cheapest or most expensive)
	 * @param ID: ID of the service
	 * @param max: If true, gives max. Otherwise min price (can be zero)
	 * @since 2.0
	 * @return object
	 */	
	function get_min_max_worker_price( $ID, $max = false ) {
		$identifier = wpb_cache_prefix() . ( $max ? 'max_price_by_worker_' . $ID : 'min_price_by_worker_' . $ID );
		$min_max_worker_price = wp_cache_get( $identifier );
		
		if ( false === $min_max_worker_price ) {
			$min_max_worker_price = 0;
			$desc = $max ? " DESC " : "";
			$result = $this->db->get_row( "SELECT * FROM " . $this->workers_table . " WHERE services_provided LIKE'%:" . $ID . ":%' ORDER BY (price) ".$desc." LIMIT 1" );
			$min_max_worker_price = isset( $result->price ) && $result->price ? $result->price : 0; 
			
			wp_cache_set( $identifier, $min_max_worker_price );
		}
		
		return $min_max_worker_price;
	}

	/**
	 * Get services given a specific location
	 * @param ID: Id of the location
	 * @param order_by: ORDER BY clause for mysql
	 * @return array of objects
	 * @since 2.0
	 */	
	function get_services_by_location( $ID, $order_by = "sort_order", $exclude_internal = false ) {
		return apply_filters( 'app_get_services_by_location', $this->get_services( $order_by, $exclude_internal ), $ID, $order_by, $exclude_internal );
	}

	/**
	 * Check if a service is internal given its ID
	 * @return bool
	 * @since 2.0
	 */	
	function is_internal( $ID ) {
		return apply_filters( 'app_is_internal', false, $ID );
	}

	/**
	 * Find service name given its ID
	 * @return string
	 */	
	function get_service_name( $ID ) {
		
		$result = $this->get_service( $ID );
		$name = !empty( $result->name ) ? $result->name : $this->get_text( 'not_defined' ); // Safe text if we delete a service
		$name = apply_filters( 'app_get_service_name', $name, $ID );
	
		return stripslashes( $name );
	}
	
	/**
	 * Get the capacity of the current or selected service
	 * @return integer
	 */		
	function get_capacity( $ID ) {
		if ( !$ID || !is_numeric( $ID ) )
			$ID = $this->service;
		
		$identifier = wpb_cache_prefix() . 'capacity_'. $ID;	
		$capacity = wp_cache_get( $identifier );
		
		if ( false === $capacity ) {
			
			if ( $this->service_exists( $ID ) ) {
				$service = $this->get_service( $ID );
				if ( !empty( $service->capacity ) )
					$capacity = (int)$service->capacity;
				else {
					$worker_count = count( (array)$this->get_workers_by_service( $ID ) );
					$capacity = max( $worker_count, 1 );
				}	 
			}
			else $capacity = 1; // No service defined or there are no workers. Apply 1

			wp_cache_set( $identifier, $capacity );
		}
		
		return apply_filters( 'app_get_capacity', $capacity, $ID, 'core' );
	}

	/**
	 * Return "padding before" of a service given its ID
	 * @return integer
	 * @since 2.0
	 */	
	function get_padding( $ID ) {
		return apply_filters( 'app_get_padding', 0, $ID );
	}
	
	/**
	 * Return "padding after" of a service given its ID
	 * @return integer
	 * @since 2.0
	 */	
	function get_break( $ID ) {
		return apply_filters( 'app_get_break', 0, $ID );
	}
	
	/**
	 * Find location for which current or selected post is a description page
	 * @param $post		null|object|integer 	Optional post object or post ID
	 * @return 			null|integer			ID of the location if there is one
	 * @since 3.0
	 */	
	function find_location_for_page( $post = null ) {
		return $this->find_lsw_for_page( 'location', $post );
	}

	/**
	 * Find service for which current or selected post is a description page
	 * @param $post		null|object|integer 	Optional post object or post ID
	 * @return 			null|integer			ID of the service if there is one
	 * @since 3.0
	 */	
	function find_service_for_page( $post = null ) {
		return $this->find_lsw_for_page( 'service', $post );
	}
	
	/**
	 * Find worker for which current or selected post is a description page
	 * @param $post		null|object|integer 	Optional post object or post ID
	 * @return 			null|integer			ID of the worker if there is one
	 * @since 3.0
	 */	
	function find_worker_for_page( $post = null ) {
		return $this->find_lsw_for_page( 'worker', $post );
	}

	/**
	 * Find location/service/worker for which current or selected post is a description page
	 * @param $lsw		string 					'location', 'service' or 'worker'
	 * @param $post		null|object|integer 	Post object or post ID or WP_Post instance
	 * @return 			null|integer			ID of the location/service/worker if there is one
	 * @since 3.0
	 */	
	function find_lsw_for_page( $lsw, $post ) {
		$lsw_id = null;
		$post = get_post( $post );
		
		if ( !empty( $post->ID ) ) {
			$identifier = wpb_cache_prefix() . $lsw.'_for_page_'. $post->ID;
			$lsw_id = wp_cache_get( $identifier );
			
			if ( false === $lsw_id ) {
				$lsw_id = $this->a->db->get_var( $this->a->db->prepare( "SELECT ID FROM ". $this->{$lsw.'_table'} . " WHERE page=%d ", $post->ID ) );
				wp_cache_set( $identifier, $lsw_id );
			}
		}
		
		return $lsw_id;
	}
	
	/**
	 * Get worker ID from front end
	 * worker = provider
	 * @return integer
	 */
	function read_worker_id() {
		if ( isset( $_GET["app_worker_id"] ) )
			return $_GET["app_worker_id"];
		else if ( isset( $_POST["app_worker_id"] ) )
			return $_POST["app_worker_id"];
		else	
			return 0;
	}

	/**
	 * Get number of *defined* service providers
	 * @since 2.0
	 */
	function get_nof_workers() {
		return apply_filters( 'app_get_nof_workers', 0 );
	}

	/**
	 * Get all workers
	 * @param order_by: ORDER BY clause for mysql
	 * @return array of objects or null
	 */	
	function get_workers( $order_by = "sort_order" ) {
		$workers = has_filter( 'app_get_workers' ) ? null : array( $this->get_default_worker_id() => $this->get_default_worker() );
		return apply_filters( 'app_get_workers', $workers, $order_by );
	}

	/**
	 * Get a single worker with given ID
	 * @param ID		integer		ID of the worker to be retrieved
	 * @return object or null
	 */	
	function get_worker( $ID ) {
		$worker = has_filter( 'app_get_worker' ) ? null : ($ID == $this->get_default_worker_id() ? $this->get_default_worker() : null);
		return apply_filters( 'app_get_worker', $worker, $ID );
	}
	
	/**
	 * Return $worker property
	 * @since 3.0
	 * @return mixed
	 */	
	function get_wid(){
		return BASE()->worker;
	}
	
	/**
	 * Get workers given a specific service
	 * @param $ID			integer		ID of the service to be retrieved
	 * @param $order_by		string		ORDER BY clause for mysql
	 * @return array of objects or null
	 */	
	function get_workers_by_service( $ID, $order_by = "sort_order" ) {
		$workers = has_filter( 'app_get_workers_by_service' ) ? null : $this->get_workers( $order_by );
		return apply_filters( 'app_get_workers_by_service', $workers, $ID, $order_by );
	}
	
	/**
	 * Get workers given a specific location
	 * @param $location		integer|string	ID or name of the location to be retrieved
	 * @param $order_by		string			ORDER BY clause for mysql
	 * @return array of objects or null
	 */	
	function get_workers_by_location( $location, $order_by = "sort_order" ) {
		$workers = has_filter( 'app_get_workers_by_location' ) ? null : $this->get_workers( $order_by );
		return apply_filters( 'app_get_workers_by_location', $workers, $location, $order_by );
	}

	/**
	 * Get all worker IDs
	 * @since 2.0
	 * @return array or null
	 */	
	function get_worker_ids( ) {
		$ids = has_filter( 'app_get_worker_ids' ) ? null : array( $this->get_default_worker_id() );
		return apply_filters( 'app_get_worker_ids', $ids );
	}

	/**
	 * Get worker Ids given a specific service
	 * @param ID: Id of the service to be retrieved
	 * @since 2.0
	 * @return array or null
	 */	
	function get_worker_ids_by_service( $ID, $order_by = "sort_order" ) {
		$worker = has_filter( 'app_get_worker_ids_by_service' ) ? null : array( $this->get_default_worker_id() );
		return apply_filters( 'app_get_worker_ids_by_service', $worker, $ID, $order_by );
	}
	
	/**
	 * Check if there is only one worker giving the selected service
	 * @param service_id: Id of the service for which check will be done
 	 * @since 1.1.1
	 * @return integer (worker ID if there is one, otherwise 0)
	 */	
	function is_single_worker( $service_id ) {
		// TODO: Consider capacity increase
		$is = has_filter( 'app_is_single_worker' ) ? 0 : $this->get_default_worker_id();
		return apply_filters( 'app_is_single_worker', $is, $service_id );
	}

	/**
	 * Find if a worker exists, given his user ID
	 * @param user_id: Id of the user who will be checked.
	 * @return bool
	 */	
	function worker_exists( $user_id ) {
		return $this->is_worker( $user_id );
	}
	
	/**
	 * Find if a user is worker (or default worker)
	 * @param user_id: Id of the user who will be checked if he is worker. If not given, current user is checked
	 * @return bool
	 */	
	function is_worker( $user_id = 0 ) {
		if ( !$user_id )
			$user_id = get_current_user_id();
		
		if ( $this->get_worker( $user_id ) )
			return true;

		if ( $user_id && $this->get_default_worker_id() == $user_id )
			return true;
		
		return false;
	}
	
	/**
	 * Find if a user is dummy
	 * @param user_id: Id of the user who will be checked if he is dummy
	 * since 1.0.6
	 * @return bool
	 */	
	function is_dummy( $user_id = 0 ) {
		return apply_filters( 'app_is_dummy', false, $user_id );
	}

	/**
	 * Find the user who is assigned as default worker, aka business representative
	 * @return integer: ID of the user
	 * @since 2.0
	 */	
	function get_default_worker_id( ) {
		if ( isset( $this->default_worker_id ) )
			return $this->default_worker_id;
		
		$this->default_worker_id = 0;
		
		if ( get_user_by( 'id', wpb_setting("default_worker") ) )
			$this->default_worker_id = wpb_setting("default_worker");
		else {
			// Find the first correctly configured user 
			$users = get_users( array( 'order_by' => 'ID' ) );
			foreach ( $users as $user ) {
				// Check if user does have a role
				if ( isset( $user->roles ) && $user->roles ) {
					$this->default_worker_id = $user->ID;
					break;
				}
			}
		}
		
		return $this->default_worker_id;
	}
	
	/**
	 * Get or Create worker object for default worker
	 * @return object
	 * @since 2.0
	 */	
	function get_default_worker( ) {
		$id = $this->get_default_worker_id();
		
		# Check if default worker is configured as a worker
		if ( BASE('SP') && BASE('SP')->get_nof_workers( ) ) {
			$workers = BASE('SP')->get_workers_from_db('sort_order');
			if ( $workers && isset( $workers[$id] ) )
				return $workers[$id];
		}
	
		# Else, create an object for default worker
		$def					= new StdClass;
		$def->ID				= $id;
		$def->sort_order		= 0;
		$def->name				= BASE('User')->get_name( $id );
		$def->dummy				= null;
		$def->services_provided	= null;
		$def->price				= 0;
		$def->page				= 0;
		
		return $def;
	}

	/**
	 * Find worker (or default worker) name given his user ID
	 * @param $worker: integer		If 0, unassigned worker text is displayed
	 * @param $display_name: If true the display name, if false user login is returned
	 * @return string
	 */	
	function get_worker_name( $worker, $display_name = true ) {
		if ( !$worker ) {
			# Show different text to authorized people
			if ( is_admin() || current_user_can( WPB_ADMIN_CAP ) || $this->is_worker( get_current_user_id() ) )
				$user_name = $this->get_text('our_staff');
			else
				$user_name = $this->get_text('a_specialist');
		}
		else {
			$identifier = wpb_cache_prefix() . 'worker_name_'. $worker;
			$user_name = wp_cache_get( $identifier );
			
			if ( false === $user_name ) {
				$workers = $this->get_workers( );
				if ( isset($workers[$worker]) && !empty( $workers[$worker]->name ) )
					$user_name = $workers[$worker]->name;
				else
					$user_name = BASE('User')->get_name( $worker );

				if ( !$user_name )
					$user_name = $this->get_text('not_defined');						

				wp_cache_set( $identifier, $user_name );
			}
		}
		
		return stripslashes( apply_filters( 'app_get_worker_name', $user_name, $worker ) );
	}

	/**
	 * Find worker (or default worker) email given his user ID
	 * since 1.0.6
	 * @return string
	 */	
	function get_worker_email( $worker = 0 ) {
		// Real person
		if ( !$this->is_dummy( $worker ) ) {
			$worker_data = BASE('User')->_get_userdata( $worker );
			if ( $worker_data )
				$worker_email = $worker_data->user_email;
			else
				$worker_email = '';
			return apply_filters( 'app_worker_email', $worker_email, $worker );
		}
		// Dummy
		if ( wpb_setting('dummy_assigned_to') ) {
			$worker_data = BASE('User')->_get_userdata( wpb_setting('dummy_assigned_to') );
			if ( $worker_data )
				$worker_email = $worker_data->user_email;
			else
				$worker_email = '';
			return apply_filters( 'app_dummy_email', $worker_email, $worker );
		}

		// If not set anything, get first admin email
		return BASE('User')->get_admin_email( true );
	}
	
	/**
	 * Return an appointment given its ID
	 * @param app_id: ID of the appointment to be retrieved from database
	 * @param all: Also save all appointments into cache (experimental)
	 * @since 1.1.8
	 * @return object or false
	 */	
	function get_app( $app_id, $all = false ) {
		if ( !$app_id )
			return false;
		
		global $app;
		$prefix = wpb_cache_prefix();
		$identifier = $prefix . 'app_'. $app_id;
		$app = wp_cache_get( $identifier );
		
		if ( false === $app ) {
			$app_all = wp_cache_get( $prefix. 'app_all' );
			if ( isset( $app_all[$app_id] ) )
				$app = $app_all[$app_id];
			else {
				# Check if we saved this app previously 
				$some_apps = wp_cache_get( $prefix . 'apps_in_all_appointments' );
				$some_apps2 = wp_cache_get( $prefix .'apps_in_my_appointments' );
				if ( isset( $some_apps[$app_id] ) )
					$app = $some_apps[$app_id];
				else if ( isset( $some_apps2[$app_id] ) )
					$app = $some_apps2[$app_id];
				else {
					if ( $all ) {
						# Also save all appts in cache
						if ( false === $app_all ) {
							$app_all = $this->db->get_results( "SELECT * FROM ". $this->app_table. " ORDER BY ID DESC LIMIT 100", OBJECT_K );
							wp_cache_set( $prefix. 'app_all', $app_all );
						}
						$app = isset( $app_all[$app_id] ) ? $app_all[$app_id] : $this->db->get_row( $this->db->prepare( "SELECT * FROM ". $this->app_table. " WHERE id=%d", $app_id ) );
					}
					else
						$app = $this->db->get_row( $this->db->prepare( "SELECT * FROM ". $this->app_table. " WHERE id=%d", $app_id ) );
				}
			}
			wp_cache_set( $identifier, $app );
		}
		
		$GLOBALS['appointment'] = $app;
		return $app;
	}
	
	/**
	 * How do we define virtual/internal "reserved" using statuses? Return the part of mySQL query that defines status matches
	 * Not to be confused with "reserved by GCal" status. 
	 * @param $context: Function making the query (Not method!)
	 * @since 3.0
	 * @return string
	 */	
	function reserved_status( $context = '' ) {
		return apply_filters( 'app_reserved_status_query', "status='pending' OR status='paid' OR status='confirmed' OR status='reserved' OR status='running'", $context );
	}
	
	/**
	 * Return all or service and worker dependent reserve appointments (i.e. running, pending, paid, confirmed or reserved by GCal)
	 * @param week: Optionally appointments only in the number of week in ISO 8601 format (since 1.2.3). 
	 * @return array of objects
	 */	
	function get_reserve_apps( $week = 0 ) {
		$identifier = wpb_cache_prefix() . 'reserve_apps_'. $week;
		$apps = wp_cache_get( $identifier );
		
		if ( false === $apps ) {

			// Developers: Mind the object cache usage. Therefore the filter result should be not changing throughout the script
			$q = $this->reserved_status( __FUNCTION__ );
			
			if ( "0" === (string)$week ) {
				$_q = "SELECT * FROM " . $this->app_table . " 
					WHERE (".$q.") ORDER BY start";
			}
			else {
				$_q = "SELECT * FROM " . $this->app_table . " 
					WHERE (".$q.") AND WEEKOFYEAR(start)=".$week. " ORDER BY start";
			}

			$apps = $this->db->get_results( $_q, OBJECT_K );			

			wp_cache_set( $identifier, $apps );
		}
		
		return $apps;
	}
	
	/**
	 * Return weekly reserve appointments by worker ID, optionally for all workers ($w='all')
	 * Worker appts are location independent
	 * @param week: Optionally appointments only in the number of week in ISO 8601 format (since 1.2.3)
	 * @return array of objects
	 */	
	function get_reserve_apps_by_worker( $w, $week = 0 ) {
		$reserve_apps = has_filter( 'app_get_reserve_apps_by_worker' ) ? null : $this->get_reserve_apps( $week );
		return apply_filters( 'app_get_reserve_apps_by_worker', $reserve_apps, $w, $week );
	}
	
	/**
	 * Return reserve appointments by worker for a given day
	 * @param $w		integer		ID of worker
	 * @param $day		string		Day in Y-m-d format
	 * @since 2.0
	 * @return array of objects
	 */	
	function get_daily_reserve_apps_by_worker( $w, $day ) {
		return $this->get_daily_reserve_apps_by( $w, $day, 'worker' );
	}
	
	/**
	 * Return reserve appointments by worker or service ID for a given day
	 * @param $w_or_s	integer		ID of service or worker
	 * @param $day		string		Day in Y-m-d format
	 * @param $subject	string		worker or service
	 * @since 2.0
	 * @return array of objects
	 */	
	function get_daily_reserve_apps_by( $w_or_s, $day, $subject = 'worker' ) {
		if ( !$day )
			return null;
		
		$subject_text = 'worker' === $subject ? 'worker' : 'service';
		
		$identifier = wpb_cache_prefix() . 'daily_reserve_apps_by_' . $subject_text . '_'. $w_or_s . '_' . $day;
		$daily_apps = wp_cache_get( $identifier );
		
		if ( false === $daily_apps ) {
			$day_start = $day . " 00:00:00";
			$day_start_ts = strtotime( $day_start, $this->_time );
			$day_end_ts = $day_start_ts + 24 *3600;
			$week = date( "W", $day_start_ts );
			if ( 'service' === $subject )
				$apps = $this->get_reserve_apps_by_service( $w_or_s, $week );
			else
				$apps = $this->get_reserve_apps_by_worker( $w_or_s, $week );
			if ( $apps ) {
				foreach ( $apps as $app ) {
					// Also account for appts exceeding the next day
					if ( strtotime( $app->start ) >= $day_start_ts && strtotime( $app->end ) <= $day_end_ts + wpb_get_duration( $app->service )*60 )
						$result[] = $app;
					if ( strtotime( $app->start ) > $day_end_ts )
						break;
				}
			}
			
			$daily_apps = isset( $result ) ? $result : null;
			
			wp_cache_set( $identifier, $daily_apps );
		}
		
		return $daily_apps;
	}
	
	/**
	 * Return reserve appointments by service ID
	 * @param week: Optionally appointments only in the number of week in ISO 8601 format (since 1.2.3)
	 * @since 1.1.3
	 * @return array of objects
	 */	
	function get_reserve_apps_by_service( $l, $s, $week = 0 ) {
		if ( 'all' === $s )
			return $this->get_reserve_apps( $week );
		
		# Query for GCal events. 
		$lquery = WPB_GCAL_SERVICE_ID === $s ? " (location=0 OR location=".$l.") " : " location=".$l." ";
		
		$identifier = wpb_cache_prefix() . 'reserve_apps_by_service_'. $l . '_' . $s . '_' . $week;
		$apps = wp_cache_get( $identifier );
		
		if ( false === $apps ) {
			$optimize = apply_filters( 'app_get_reserve_apps_by_worker_is_optimize', false, $l, $s, $week );
			if ( !$optimize ) {
				$q = $this->reserved_status( __FUNCTION__ );
				if ( "0" === (string)$week )
					$apps = $this->db->get_results( "SELECT * FROM " . $this->app_table . " 
						WHERE ".$lquery." AND service=".$s."
						AND (".$q.") ORDER BY start", OBJECT_K );
				else
					$apps = $this->db->get_results( "SELECT * FROM " . $this->app_table . " 
						WHERE location=".$l." AND service=".$s."  
						AND (".$q.") AND WEEKOFYEAR(start)=".$week. " ORDER BY start", OBJECT_K );
			}
				
			wp_cache_set( $identifier, $apps );
		}
		
		return $apps;
	}
	
	/**
	 * Return reserve appointments by service for a given day
	 * @param $s		integer		ID of service
	 * @param $day		string		Day in Y-m-d format
	 * @since 2.0
	 * @return array of objects
	 */	
	function get_daily_reserve_apps_by_service( $s, $day ) {
		return $this->get_daily_reserve_apps_by( $s, $day, 'service' );
	}
	
	/**
	 * Return reserve unassigned (worker=0, NOT $w) appointments given a service ID or in any service $w worker can give
	 * @param week: Optionally appointments only in the number of week in ISO 8601 format (since 1.2.3)
	 * @since 3.0
	 * @return array of objects
	 */	
	function get_reserve_unassigned_apps( $l, $s, $w, $week = 0 ) {
		if ( 'all' === $s )
			return $this->get_reserve_apps( $week );
		
		$identifier = wpb_cache_prefix() . 'reserve_unassigned_apps_'. $l . '_' . $s . '_' . $w . '_' .$week;
		$apps = wp_cache_get( $identifier );
		
		if ( false === $apps ) {
			# This filter can be used for external caching and/or optimization
			$apps = apply_filters( 'app_reserve_unassigned_apps', false, $l, $s, $w, $week );
			if ( false === $apps ) {
				# Query for GCal events. 
				$lquery = WPB_GCAL_SERVICE_ID === $s ? " (location=0 OR location=".$l.") " : " location=".$l." ";

				$squery = " service=".$s." ";
				if ( $w ) {
					$ser_provided = array_keys( $this->get_services_by_worker( $w ) );
					if ( !empty( $ser_provided ) )
						$squery = " (service=".$s." OR service IN (".implode( ',', $ser_provided ).")) ";
				}
				
				$q = $this->reserved_status( __FUNCTION__ );
				
				if ( "0" === (string)$week )
					$query = "SELECT * FROM " . $this->app_table . " 
						WHERE ".$lquery." AND ".$squery." AND worker=0
						AND (".$q.") ORDER BY start";
				else
					$query = "SELECT * FROM " . $this->app_table . " 
						WHERE location=".$l." AND ".$squery." AND worker=0  
						AND (".$q.") AND WEEKOFYEAR(start)=".$week. " ORDER BY start";
						
				$apps = $this->db->get_results( $query, OBJECT_K );
				
			}
				
			wp_cache_set( $identifier, $apps );
		}
		
		return $apps;
	}
	
	/**
	 *	Get appointments from cookie by checking hash
	 *	To prevent tampering with cookie
	 *  @return array
	 *  @since 3.0
	 */	
	function get_apps_from_cookie() {
		if ( empty( $_COOKIE["wpb_bookings"] ) || empty( $_COOKIE["wpb_userdata"] ) )
			return array();
		
		$data = maybe_unserialize( wp_unslash( $_COOKIE["wpb_bookings"] ) );
		$userdata = maybe_unserialize( wp_unslash( $_COOKIE["wpb_userdata"] ) );
		if ( empty( $data['hash'] ) || empty( $data['bookings'] ) || !is_array( $data['bookings'] ) || $data['hash'] != $this->create_hash( $data['bookings'], 'bookings_cookie', BASE('User')->anon_user_identifier( $userdata ) ) )
			return array();
		
		return $data['bookings'];
	}
	
	/**
	 * Get all posts/pages/custom posts having WP BASE shortcodes
	 * @since 3.0
	 * @return array|null	Array of post objects or null if not found
	 */		
	function get_app_pages(){
		return $this->db->get_results( "SELECT * FROM ".$this->db->posts." WHERE post_content LIKE '%[app_%' AND post_status<>'trash' AND post_status<>'auto-draft' AND post_type<>'attachment' AND post_type<>'revision' AND post_type<>'custom_css' AND post_type<>'customize_changeset' ORDER BY post_title,ID ", OBJECT_K );
	}

	/**
	 *	Find first free time slot with given lsw
	 *	This is not necessarily the first free slot for ALL lsw's: Each lsw may lead to a different first slot
	 *	A hard limit can be set, which prevents long execution times
	 *  @return integer|null	Integer (timestamp of the free slot) or null if none found within hard limit
	 *  @since 2.0
	 */	
	function find_first_free_slot( $location = false, $service = false, $worker = false ) {
		$location 	= false === $location ? $this->location : $location;
		$service 	= false === $service ? $this->service : $service;
		$worker 	= false === $worker ? $this->worker : $worker;
		
		$identifier = wpb_cache_prefix() . 'app_first_free_slot_'. $location .'_'. $service .'_'. $worker;
		
		$first = wp_cache_get( $identifier );
		
		if ( false === $first ) {
			$first = null;
			
			if ( $service ) {
				
				# Apply 5.0 secs hard limit contrary to 10.0 secs value
				add_filter( 'app_hard_limit', array( $this, 'hard_limit' ) );
				
				$max_days = min( $this->get_upper_limit(), 365 );
				$today = strtotime( 'today', $this->_time );
				
				$calendar = new WpBCalendar( $location, $service, $worker );
				
				for ( $d = $today; $d < $today + $max_days *86400; $d = $d + 86400 ) {
					$out = $calendar->find_slots_in_day( $d, 1 );
					if ( count( $out ) ) {
						$first = key( $out );
						break;
					}
				}
				
				remove_filter( 'app_hard_limit', array( $this, 'hard_limit' ) );
			}
			
			wp_cache_set( $identifier, $first );
			
		}

		return $first;
	}
	
	/**
	 * Return a hard limit value for find first slot in secs (Default 5.0s)
	 * @return float
	 */	
	function hard_limit(){
		return defined('WPB_FIND_FIRST_TIME_SLOT_HARD_LIMIT') ? WPB_FIND_FIRST_TIME_SLOT_HARD_LIMIT : 5.0;
	}

	/**
	 * Find first page id with title "Make an Appointment" or "Book a Service"
	 * @param $page_name	Title of the page
	 * @since 3.0
	 * @return integer or null
	 */	
	function first_app_page_id( $page_name = "Make an Appointment" ) {
		$key = sanitize_key( $page_name );
		if ( isset( $this->first_app_page_id[$key] ) )
			return $this->first_app_page_id[$key];
		
		$this->first_app_page_id[$key] = $this->db->get_var( $this->db->prepare( "SELECT ID FROM ". $this->db->posts. " WHERE post_title = %s AND post_type='page' AND post_status='publish' ", $page_name ) );
		return $this->first_app_page_id[$key];
	}

	/**
	 * Return link of the appointment on admin bookings page
	 * @param app_id: Appointment ID
	 * @param override_cap	Override capability check and use link, e.g. when used in an email sent to admin
	 * @since 2.0
	 * @return string
	 */	
	function get_app_link( $app_id, $override_cap = false ) {
		if ( $override_cap || current_user_can(WPB_ADMIN_CAP) )
			return '<a data-app_id="'.$app_id.'" title="'.__('Click to access to the related booking','wp-base').'" href="'. admin_url('admin.php?page=appointments&type=all&app_or_fltr=1&stype=app_id&app_s='.$app_id ). '" >'. $app_id . '</a>';
		else
			return $app_id;
	}

	/**
	 * Get customized/localized text
	 * @return string
	 * @since 2.0
	 */	
	function get_text( $key ) {
		# Simple cache
		if ( !isset( $this->texts ) )
			$this->texts = apply_filters( 'app_get_text', get_option( 'wp_base_texts' ), $key );
		
		if ( isset( $this->texts[$key] ) && '' !== $this->texts[$key] )
			return $this->texts[$key];
		else {
			// First check additional custom text
			if ( BASE('CustomTexts') && isset( BASE('CustomTexts')->add_default_texts[$key] ) )
				return BASE('CustomTexts')->add_default_texts[$key];
			else if ( BASE('CustomTexts') )
				return BASE('CustomTexts')->get_default_texts($key);
			else
				return $key;
		}
	}

	/**
	 * Get and cache all transactions (Booking ID, User ID, Sum of transactions)
	 * Note: Transactions are multipled by 100 (1234 means $12.34)
	 * @since 2.0
	 * @return array of objects 
	 */	
	function get_all_transactions( ) {
		$identifier = wpb_cache_prefix() . 'all_transactions';
		$trs = wp_cache_get( $identifier );
		
		if ( false === $trs ) {
			$q = "SELECT app.ID, app.user, SUM(tr.transaction_total_amount) 
					FROM {$this->transaction_table} AS tr, {$this->app_table} AS app 
					WHERE tr.transaction_app_ID=app.ID 
					AND app.ID IN (SELECT ID FROM {$this->app_table})
					GROUP BY app.ID";
			$trs = $this->db->query( $q, OBJECT_K );
			
			wp_cache_set( $identifier, $trs );
		}
		
		return $trs;
	}
	
	/**
	 * Get and cache all transactions of a user
	 * Note: Transactions are multipled by 100 (1234 means $12.34)
	 * @param user_id: User ID
	 * @since 2.0
	 * @return array of objects 
	 */	
	function get_transactions_by_user( $user_id ) {
		if ( !$user_id )
			return false;
		
		$identifier = wpb_cache_prefix() . 'transactions_by_user_'. $user_id;
		$trs = wp_cache_get( $identifier );
		
		if ( false === $trs ) {
			$q = "SELECT app.ID, SUM(tr.transaction_total_amount) AS total_paid
					FROM {$this->transaction_table} AS tr, {$this->app_table} AS app 
					WHERE tr.transaction_app_ID=app.ID 
					AND app.ID IN (SELECT ID FROM {$this->app_table} WHERE user={$user_id})
					GROUP BY app.ID";

			$trs = $this->db->get_results( $q, OBJECT_K );
			
			wp_cache_set( $identifier, $trs );
		}
		
		return $trs;
	}

	/**
	 * Get total paid (sum of all transaction amounts) for an app
	 * Note: Paid numbers are multipled by 100 (1234 means $12.34)
	 * @param app_id: app ID
	 * @param all: Also save all appointments into cache (experimental)
	 * @since 2.0
	 * @return integer
	 */	
	function get_total_paid_by_app( $app_id, $all = true ) {
		// If we are interested in just one payment. e.g. to use in email, it is resource saver if we call it from DB
		if ( !$all ) {
			$identifier = wpb_cache_prefix() . 'total_paid_by_app_'. $app_id;
			$paid = wp_cache_get( $identifier );
			
			if ( false === $paid ) {
				$paid = $this->db->get_var( $this->db->prepare( "SELECT SUM(transaction_total_amount) FROM {$this->transaction_table} WHERE transaction_app_ID=%d", $app_id ) );
				wp_cache_set( $identifier, $paid );
			}
			return $paid;
		}
		
		$app = $this->get_app( $app_id, $all );
		// Logged in user
		// We are prefering the first method, because it caches user's all transactions
		if ( !empty( $app->user ) ) {
			$trs = $this->get_transactions_by_user( $app->user );
			if ( isset( $trs[$app_id] ) && isset( $trs[$app_id]->total_paid ) )
				return $trs[$app_id]->total_paid;
			else
				return 0;
		}
		else {
			$trs = $this->get_all_transactions( );
			if ( isset( $trs[$app_id]->total_paid ) )
				return $trs[$app_id]->total_paid;
			else
				return 0;
		}
	}
	
	/**
	 * Calculate down payment (formerly used as 'deposit') given price
	 * For backwards compatibility
	 * @param price: the full price
	 * @since 1.0.8 
	 * @return string
	 */	
	function get_deposit( $price ) {
		return $this->calc_downpayment( $price );
	}

	/**
	 * Calculate downpayment given price, aka "amount"
	 * @param price: the full price
	 * @since 2.0 (previously get_deposit)
	 * @return string
	 */	
	function calc_downpayment( $price ) {
		$dpayment = $price;
		
		// Members no pre-payment
		if ( $this->is_member() && wpb_setting("members_no_payment") )
			$dpayment=0;
		else {
			if ( wpb_setting("percent_downpayment") )
				$dpayment = wpb_round( $price * wpb_setting("percent_downpayment") / 100 );
			if ( wpb_setting("fixed_downpayment") )
				$dpayment = wpb_setting("fixed_downpayment");
		}
		
		return apply_filters( 'app_calc_downpayment', $dpayment, $price );
	}
	
	/**
	* Get Tax of a Service in %
	* @param $ID:	Service ID
	* @since 3.0
	* @return integer
	*/	
	function get_tax( $ID ) {
		return apply_filters( 'app_get_tax', wpb_setting('tax', 0), $ID );
	}

	/**
	* Find if user has sufficient level for discounts
	* @return bool
	*/	
	function is_member( ) {
		
		$result = false;
		
		if ( is_user_logged_in() && wpb_setting("members") ) {
			global $current_user;
			$user_roles = $current_user->roles;
			$meta		= maybe_unserialize( wpb_setting("members") );
			
			if( is_array( $meta ) && is_array( $user_roles ) ) {
				foreach ( $user_roles as $role ) {
					if ( in_array( $role, $meta["level"] ) ) {
						$result = true;
						break;
					}
				}
			}
		}
		
		return apply_filters( 'app_is_member', $result );
	}
	
	/*
	 * function get_setting
	 * @param string $key A setting key, or -> separated list of keys to go multiple levels into an array
	 * @param mixed $default Returns when setting is not set
	 * This is inherited from old version of MP
	 * an easy way to get to our settings array without undefined indexes
	 * If No -> is used, same functionality as get_options
	 * @since 2.0
	 */
	function get_setting( $key, $default = null ) {
		$settings = wpb_setting( );
		$keys = explode('->', $key);
		array_map('trim', $keys);
		if (count($keys) == 1)
			$setting = isset($settings[$keys[0]]) ? $settings[$keys[0]] : $default;
		else if (count($keys) == 2)
			$setting = isset($settings[$keys[0]][$keys[1]]) ? $settings[$keys[0]][$keys[1]] : $default;
		else if (count($keys) == 3)
			$setting = isset($settings[$keys[0]][$keys[1]][$keys[2]]) ? $settings[$keys[0]][$keys[1]][$keys[2]] : $default;
		else if (count($keys) == 4)
			$setting = isset($settings[$keys[0]][$keys[1]][$keys[2]][$keys[3]]) ? $settings[$keys[0]][$keys[1]][$keys[2]][$keys[3]] : $default;

		return apply_filters( "app_setting_".implode('', $keys), $setting, $default );
	}

	/**
     * Provide options
	 * @param $item: Pick the required key. Leave empty for all options.
	 * @param $fallback:	Return that value if option set, but not true
	 * @since 2.0
 	 * @return mixed
     */
	function get_options( $item = null, $fallback = null ) {
		return wpb_setting( $item, $fallback );
	}
	
	/**
     * Update options
	 * @param item: Pick the required option. Leave empty for all options.
	 * @since 2.0
 	 * @return bool
     */
	function update_options( $value, $item = null ) {
		global $current_user;
		
		$old_options = $options = wpb_setting();
	
		if ( $item !== null )
			$options[$item] = $value;
		else
			$options = $value;

		if ( ! update_option( apply_filters( 'app_update_options', 'wp_base_options' ), $options ) )
			return false;

		do_action( 'app_settings_changed', $old_options, $options );
		
		if ( 'yes' !== wpb_setting('log_settings' ) )
			return true;
		
		# Log changes
		unset( $old_options['service_check_needed'] );
		unset( $options['service_check_needed'] );
		
		$diff = wpb_array_diff_assoc_recursive( $old_options, $options );
		if ( empty( $diff ) )
			return true;
		
		$count = count($diff);
		$text = '';
		foreach ( $diff as $key=>$value ) {
			// Key can be an array, e.g. for items on admin menu. We dont want these options to be tracked
			if ( is_array( $key ) || !isset( $old_options[$key] ) || !isset( $options[$key] ) || is_array( $old_options[$key] ) || is_array( $options[$key] ) )
				continue;
			
			$old_value_full = ('' == $old_options[$key]) ? "null" : wp_trim_words( $old_options[$key], 200, '' );
			$new_value_full = ('' == $options[$key]) ? "null" : wp_trim_words( $options[$key], 200, '' );
			
			// Prevent too long settings (email content)
			if ( $old_value_full != $new_value_full ) {
				for( $k=1; $k<20; $k++ ) {
					$old_value = wp_trim_words( $old_value_full, 10, '' );
					$new_value = wp_trim_words( $new_value_full, 10, '' );
					if ( $old_value != $new_value )
						break;
					
					$old_value_full = str_replace( $old_value, '', $old_value_full );
					$new_value_full = str_replace( $new_value, '', $new_value_full );
				}
			}
			else {
				$old_value = $old_value_full;
				$new_value = $new_value_full;
			}
			
			$text .= $key . ": " . $old_value . " >=> ". $new_value . "\r\n";
		}
		
		if ( $text ) {
			$text = sprintf( _n( 'User %1$s changed %2$d setting:','User %1$s changed %2$d settings:', $count, 'wp-base'), BASE('User')->get_name(), $count). "\r\n" . $text; 
			$this->log( trim( $text ) );
		}
		
		return true;
	}

	/**
     * Provide business options
	 * @param item: Pick the required option. Leave empty for all options.
	 * @since 2.0
 	 * @return mixed
     */
	function get_business_options( $item = null ) {
		$options = apply_filters( 'app_get_business_options', get_option( 'wp_base_business_options' ) ); 
		if ( $item !== null ) {
			if ( isset( $options[$item] ) )
				return $options[$item];
			else
				return false;
		}
		else
			return $options;
	}
	
	/**
     * Update business options
	 * @param item: Pick the required option. Leave empty for all options.
	 * @since 2.0
 	 * @return bool
     */
	function update_business_options( $value, $item = null ) {
		$old_options = $options = $this->get_business_options();
	
		if ( $item !== null )
			$options[$item] = $value;
		else
			$options = $value;

		if ( update_option( apply_filters( 'app_update_business_options', 'wp_base_business_options' ), $options ) ) {
			do_action( 'app_business_settings_changed', $old_options, $options );
			return true;
		}
		else
			return false;
	}

	/**
	 * Save a message to the log file
	 * @param $message	string	 Text that will be saved to the log file
	 * @param $test		bool	 Duplicate records are allowed
	 * @return none
	 */	
	function log( $message = '', $test = false ) {
		# Logging can be paused by setting this property. Reset to resume.
		if ( !empty( $this->pause_log ) )
			return;
		
		if ( ! ( $message || $test ) )
			return;
		
		if ( is_object( $message ) || is_array( $message ) )
			$message = print_r( $message, true );
		else if ( is_bool( $message ) )
			$message = $message ? 'true' : 'false';
		else if ( null === $message )
			$message = 'null';
		
		$to_put = '<b>['. date_i18n( $this->dt_format, $this->_time ) .']</b> '. htmlentities($message);
		
		# Prevent multiple messages with same text and same timestamp
		if ( $test || !file_exists( $this->log_file ) || strpos( @file_get_contents( $this->log_file ), $to_put ) === false ) 
			@file_put_contents( $this->log_file, $to_put . chr(10). chr(13), FILE_APPEND ); 
	}

	/**
	 * Return an array of preset base times, so that strange values are not set
	 * @return array
	 */		
	function time_base() {
		$default = array( 5,10,15,30,60,90,120,240,720 );
		$a = wpb_setting("additional_min_time");
		// Additional time bases
		if ( !empty( $a ) && is_numeric( $a ) )
			$default[] = $a;
		sort( $default ); 
		return apply_filters( 'app_time_base', array_unique( $default ) );
	}

	/**
	 *	Return minimum set interval time, in minutes
	 *  If not set, return a safe time.
	 *	@return integer
	 */		
	function get_min_time(){
		$min_time = wpb_setting("min_time");
		$calc_min_time = wpb_setting("calc_min_time");
		if ( 'auto' === $min_time && $calc_min_time && $calc_min_time > apply_filters( 'app_safe_min_time', 4 ) )
			return (int)$calc_min_time;
		else if ( 'auto' !== $min_time && $min_time && $min_time > apply_filters( 'app_safe_min_time', 4 ) )
			return (int)$min_time;
		else
			return apply_filters( 'app_safe_time', 60 );
	}

	/**
	 *	Try to calculate maximum possible time base value which divides service durations, paddings, wh settings all 
	 *  Result is in minutes
	 *	@return integer
	 */		
	function find_optimum_time_base() {
		$services = $this->get_services();
		$tbases = $this->time_base();
		rsort( $tbases );
		
		# Check from largest time base to smallest to find one that divides all services
		foreach ( $tbases as $tb ) {
			$tb_too_big = false;
			foreach ( $services as $service ) {
				if ( $service->duration % $tb != 0 || $this->get_break($service->ID) % $tb != 0 || $this->get_padding($service->ID) % $tb != 0 || !BASE('WH')->maybe_divide($tb) ) {
					$tb_too_big = true; // Does not divide: Try a smaller time base
					break;
				}
			}
			
			if ( $tb_too_big )
				continue;
			
			# Check if last value (smallest that we found) really satisfies
			if ( $service->duration % $tb == 0 && $this->get_break($service->ID) % $tb == 0 && $this->get_padding($service->ID) % $tb == 0 )
				 $result = $tb;
			else
				$result = 0;	// Could not calculate

			break;	
		}
		
		return $result;
	}
	
	/**
	 * Get appointment lower limit (lead time) in hours
	 * @param start: Lead time can be adjusted by addons depending on date/time (app start time)
	 * @since 2.0	
	 */
	function get_lower_limit( $start, $service_id = 0 ) {
		return apply_filters( 'app_lower_limit', wpb_setting( "app_lower_limit", 0 ), $start, $service_id );
	}
	
	/**
	 * Alias of get_app_limit
	 * @param start_ts: Lead time can be adjusted by addons depending on date/time (app start timestamp)
	 * @since 2.0	
	 */
	function get_upper_limit( $start_ts = 0, $service_id = 0 ) {
		return $this->get_app_limit( $start_ts, $service_id );
	}

	/**
	 *	Number of *days* that an appointment can be taken (Upper Limit)
	 *  @param start_ts: Optionally calculate limit based on timestamp of selected time slot (just passed to the filter)
	 *	@return integer
	 */
	function get_app_limit( $start_ts = 0, $service_id = 0 ) {
		if ( $maybe_upper_limit = wpb_setting("app_limit") ) {
			
			# If month is selected, fix +1 month issue when today is 31st of Jan
			if ( 'month' === wpb_setting("app_limit_unit") ) {
				
				$nof_months	= wpb_setting("app_limit");
				$day		= date( "j", $this->_time );
				$month		= date( "n", $this->_time );
				$year		= date ( "Y", $this->_time );
				$start		= mktime( 0,0,0, $month, $day, $year );
				$end		= strtotime('+ ' . $nof_months . ' months', $start);
			
				if ( date('d', $start) != date('d', $end) ) { 
					$end = strtotime('- ' . date('d', $end) . ' days', $end);
				}
				
				$upper_limit = ceil(($end-$start)/(24*3600));
			}
			else			
				$upper_limit = $maybe_upper_limit;
		}
		else
			$upper_limit = 365;

		return apply_filters( 'app_upper_limit', (int)$upper_limit, $start_ts, $service_id );
	}
	
	/**
	 * Return an array of weekdays
	 * @return array
	 */		
	function weekdays() {
		return array( 
			'Sunday' 	=> $this->get_text('sunday'), 
			'Monday' 	=> $this->get_text('monday'), 
			'Tuesday' 	=> $this->get_text('tuesday'), 
			'Wednesday' => $this->get_text('wednesday'), 
			'Thursday' 	=> $this->get_text('thursday'), 
			'Friday' 	=> $this->get_text('friday'), 
			'Saturday' 	=> $this->get_text('saturday') 
		);
	}

	/**
	 * Return all available statuses
	 * @return array
	 */		
	function get_statuses() {
		return apply_filters( 'app_statuses', array( 
			'confirmed'	=> $this->get_text('confirmed'),
			'paid'		=> $this->get_text('paid'),
			'pending'	=> $this->get_text('pending'),
			'running'	=> $this->get_text('running'),
			'completed'	=> $this->get_text('completed'),
			'removed'	=> $this->get_text('removed'),
			)
		 );
	}
	
	/**
	 * Return possibly translated name of the status
	 * @param key: Status whose name will be retrieved
	 * @since 2.0
	 * @return string
	 */		
	function get_status_name( $key ) {
		if ( !array_key_exists( $key, $this->get_statuses() ) )
			return $this->get_text( 'not_defined' );
			
		return $this->get_text( $key );
	}
	
	/**
	 * Return an array of user field labels/titles (name, email, phone, etc)
	 * @return array
	 */	
	function get_user_fields(){
		return BASE('User') ? BASE('User')->get_fields() : array();
	}

	/**
	 * Return an array of all available front end legend items
	 * @return array
	 */	
	function get_legend_items() {
		$legend_items = array( 
			'free'				=> __('Available', 'wp-base'),
			'notpossible'		=> __('Not available', 'wp-base'),
			'has_appointment'	=> __('Partly Busy', 'wp-base'),
			'busy'				=> __('Busy', 'wp-base'),
		);
		
		if ( !wpb_is_admin() ) {
			unset( $legend_items['has_appointment'] );
			if ( 'yes' === wpb_setting( 'hide_busy' ) )
				unset( $legend_items['busy'] );
		}

		return apply_filters( 'app_legend_items', $legend_items );
	}
	
	/**
	 * Datatable args 
	 */
	function get_datatable_admin_args() {
		return apply_filters( 'app_management_datatable_args', '
			"dom": \'T<"app_clear">lfrtip\',
					"tableTools": {
						"sSwfPath":_app_.tabletools_url
					},
			fnInitComplete: function ( oSettings ) {
				var dttt = jQuery("div.DTTT_container");
				var last_nav = $(".app-page").find(".tablenav .alignleft.actions").last();
				var margin_top = last_nav.css("margin-top"); 
				var height = $(".app-manage-second-row .app-manage-first-column").outerHeight();
				dttt.css("float","left").css("margin-bottom","0").css("height","auto");
				last_nav.after(dttt[0]).css("height","auto");
				
			},
			 "autoWidth": false,
			"bAutoWidth": false, 
			"responsive": false,
			"paging":   false,
			"ordering": false,
			"info":     false,
			"bFilter": false
		');
	}


/****************************************
* Methods for emails and notifications
*****************************************
*/

	/**
	 * Generate a dummy appointment record to be used for test
	 * @since 2.0
	 */		
	function test_app_record() {
		
		$r = new stdClass();
		$r->ID		= 0;
		$r->user	= 0;
		$r->location = 0;
		$r->service	= $this->get_first_service_id();
		$r->worker	= 0;
		$service	= $this->get_service( $r->service );
		$r->status	= "confirmed";
		$r->price	= !empty( $service->price ) ? $service->price : 0;
		$r->deposit = 0;
		$r->created	= date( 'Y-m-d H:i:s', $this->_time );
		$r->start	= date( 'Y-m-d H:i:s', $this->_time + 600 );
		$r->end		= date( 'Y-m-d H:i:s', $this->_time + wpb_get_duration( $r->service )*60 + 600 );
		foreach( $this->get_user_fields() as $f ) {
			$r->{$f} = sprintf( __( 'Test %s', 'wp-base' ), $this->get_text( $f ) );
		}
		
		return $r;
	}
	
	/**
	 * Central control point for *automatic* and user triggered SMS and email messages
	 * Manual emails (sent by admin request) are not controlled here
	 * @param $app_id: ID of the app whose confirmation will be sent
	 * @param $context: Pending, confirmation, notification, cancellation, edited
	 * @since 2.0
	 * @return bool
	 */		
	function maybe_send_message( $app_id, $context ) {
		$app = $this->get_app( $app_id );
		
		if ( empty( $app->ID ) )
			return false;
		
		if ( apply_filters( 'app_disable_client_messages', false, $app_id, $context ) )
			return false;
		
		// Child bookings of internal services do not create any auto message
		if ( $app->parent_id && $this->is_internal( $app->service ) )
			return false;
		
		$send = $resend = $edit = false;
		if ( 'manual-payments' == $context ) {
			$send = true;
			$context = 'pending';
		}
		else if ( 'pending' == $context && 'yes' == wpb_setting("send_pending") ) {
			$send = true;
			$context = 'pending';
		}
		else if ( 'notification' == $context && 'yes' == wpb_setting("send_notification") )
			$send = true;
		else if ( 'confirmation' == $context && 'yes' == wpb_setting("send_confirmation") )
			$send = true;
		else if ( 'completed' == $context && 'yes' == wpb_setting("send_completed") )
			$send = true;
		else if ( 'cancellation' == $context && 'yes' == wpb_setting("send_cancellation") )
			$send = true;
		else if ( 'edited' == $context ) {
			$edit = true;
			$app = $this->get_app( $app_id );
			if ( 'pending' == $app->status && 'yes' == wpb_setting("send_pending") ) {
				$send = true;
				$context = 'pending';
			}
			else if ( ('confirmed' == $app->status || 'paid' == $app->status) && 'yes' == wpb_setting("send_confirmation") ) {
				$send = true;
				$context = 'confirmation'; // Send confirmaton email to client and email to admin with Subject "edited"
			}
		}
		
		if ( $send )
			$this->send_email( $app_id, $context, false, $edit );
		
		do_action( 'app_maybe_send_message', $app_id, $context );
	}
	
	/**
	 * Send confirmation/cancellation/pending/completed email
	 * @param $app_id: ID of the app whose confirmation will be sent
	 * @param $context: cancellation, completed, pending, confirmation
	 * @param $resend: Confirmation message is being resent manually
	 * @param $edit: Booking edited on the front end (possible with $context=confirmed and $context=pending)
	 * @return bool
	 */		
	function send_email( $app_id, $context = 'confirmation', $resend = false, $edit = false ) {
		
		if ( !('cancellation' == $context || 'pending' == $context ||'completed' == $context) )
			$context = 'confirmation';

		$r = $this->get_app( $app_id );
		if ( empty( $r->ID ) )
			return false;
			
		// Do not send confirmation for child appointments - Except manual send
		if ( $r->parent_id && !$resend )
			return false;
		
		// If there is no valid user email try to find one from user data
		$maybe_email = wpb_get_app_meta( $app_id, 'email' );	
		if ( !is_email( $maybe_email ) ) {
			$email_found = false;
			// Try to find email of the user
			if ( $r->user ) {
				$app_user = BASE('User')->get_app_userdata( 0, $r->user );
				if ( isset( $app_user['email'] ) ) {
					$r->email = $app_user['email'];
					$email_found = true;
				}
			}
			if ( !$email_found ) {
				if ( wpb_is_admin() )
					$this->log( sprintf( __('No valid email is defined for appointment ID:%s','wp-base'), $r->ID ) );
				
				do_action( 'app_email_no_valid_email', $r, $app_id, $context );
				return false;
			}
		}
		else $r->email = $maybe_email;
		
		$subject = $this->_replace( 
						apply_filters( 'app_email_template', wpb_setting($context."_subject"), $r, $context."_subject" ),
						$r, $context."_subject" );
		$body = $this->_replace( 
						apply_filters( 'app_email_template', wpb_setting($context."_message"), $r, $context."_message"),
						$r, $context."_message" );
		$attach = apply_filters( 'app_email_attachment', array(), $r, $context );		

		$mail_result = wp_mail( 
			apply_filters( 'app_send_email_client_email', array( $r->email ), $body, $r, $app_id, $context, $resend, $edit ),
			$subject,
			$body,
			$this->message_headers( $context, $r ),
			$attach
		);
		
		// Log and Send a copy to admin
		if ( $mail_result ) {
			
			do_action( 'app_email_sent', $body, $r, $app_id, $context, $resend, $edit );
			
			// Log only if it is set so. 
			$context_local = $this->get_status_name('confirmed');
			if ( 'pending' == $context ||'completed' == $context )
				$context_local = $this->get_status_name($context);
			else if ( 'pending' == $context )
				$context_local = $this->get_status_name('removed');
			
			if ( 'yes' == wpb_setting("log_emails") )
				$this->log( sprintf( __('%1$s message sent to %2$s for appointment ID:%3$s','wp-base'), ucwords($context_local), $r->email, $app_id ) );
				
			// If resend, dont send copy to admin
			if ( $resend )
				return true;
			
			/*  Send a copy to admins and service provider */
			$to = array();
			
			if ( !apply_filters( 'app_'.$context.'_disable_admin', false, $r, $app_id ) )
				$to = BASE('User')->get_admin_email( );
			
			if ( !apply_filters( 'app_'.$context.'_disable_worker', false, $r, $app_id ) ) {
				$worker_email = $this->get_worker_email( $r->worker );
				if ( $worker_email )
					$to[]= $worker_email;
			}
			
			$to = apply_filters( 'app_send_email_admin_email', $to, $body, $r, $app_id, $context, $resend, $edit );
			
			if ( empty( $to ) )
				return true;
			
			// Let addons modify app_id
			$app_id_email = apply_filters( 'app_id_email', $app_id );
			$link_to_app = admin_url("admin.php?page=appointments&app_or_fltr=1&type=all&stype=app_id&app_s=".$app_id_email);
			
			// Note that both pending and confirmed appointments can be edited
			if ( $edit ) {			
				$subject = sprintf( __('Appointment edited (#%d)','wp-base'), $r->ID );
				$headers = $this->message_headers( 'confirmation_admin', $r );	// message after editing is a type of confirmation
				$context = 'edit_admin';
				$add_text  = sprintf( __('An appointment has been edited on %s.', 'wp-base'), wp_specialchars_decode(get_option('blogname'), ENT_QUOTES) );
				$add_text .= "\n\n";
				$add_text .= sprintf( __('The edited appointment has an ID %1$s and you can access it by clicking this link: %2$s','wp-base'), $app_id, $link_to_app );
				$add_text .= "\n\n";
				$add_text .= __('Below please find a copy of the message that has been sent to your client after editing:', 'wp-base');
				$attach = apply_filters( 'app_email_attachment', array(), $r, 'confirmation_admin' ); // Edit attachment is same as confirmation
			}
			else if ( 'completed' == $context ) {			
				$subject = sprintf( __('Appointment completed (#%d)','wp-base'), $r->ID );
				$headers = $this->message_headers( 'completed_admin', $r );
				$context = 'cancellation_admin';
				$add_text  = sprintf( __('An appointment has been completed on %s.', 'wp-base'), wp_specialchars_decode(get_option('blogname'), ENT_QUOTES) );
				$add_text .= "\n\n";
				$add_text .= sprintf( __('The completed appointment has an ID %1$s and you can access it by clicking this link: %2$s','wp-base'), $app_id, $link_to_app );
				$add_text .= "\n\n";
				$add_text .= __('Below please find a copy of the message that has been sent to your client after completion:', 'wp-base');
				$attach = apply_filters( 'app_email_attachment', array(), $r, $context );					
			}
			else if ( 'cancellation' == $context ) {			
				$subject = sprintf( __('Appointment cancelled (#%d)','wp-base'), $r->ID );
				$headers = $this->message_headers( 'cancellation_admin', $r );
				$context = 'cancellation_admin';
				$add_text  = sprintf( __('An appointment has been cancelled on %s.', 'wp-base'), wp_specialchars_decode(get_option('blogname'), ENT_QUOTES) );
				$add_text .= "\n\n";
				$add_text .= sprintf( __('The cancelled appointment has an ID %s and you can access it by clicking this link: %s','wp-base'), $app_id, $link_to_app );
				$add_text .= "\n\n";
				$add_text .= __('Below please find a copy of the message that has been sent to your client after cancellation:', 'wp-base');
				$attach = apply_filters( 'app_email_attachment', array(), $r, $context );					
			}
			else {
				$subject = sprintf( __('New appointment (#%d)','wp-base'), $r->ID );
				$headers = $this->message_headers( 'confirmation_admin', $r );
				$context = 'confirmation_admin';
				$add_text  = sprintf( __('A new appointment has been made on %s.', 'wp-base'), wp_specialchars_decode(get_option('blogname'), ENT_QUOTES) );
				$add_text .= "\n\n";
				$add_text .= sprintf( __('The new appointment has an ID %1$s and you can access it by clicking this link: %2$s','wp-base'), $app_id, $link_to_app );
				$add_text .= "\n\n";
				$add_text .= __('Below please find a copy of the message that has been sent to your client as confirmation:', 'wp-base');
				$attach = apply_filters( 'app_email_attachment', array(), $r, $context );
			}
			$add_text .= "\n\n\n";
			
			$context_text = $edit ? $context . '_edited' : $context;
			
			if ( wp_mail( 
				$to,
				apply_filters( 'app_email_subject_admin_copy', $this->_replace( $subject, $r, $context.'_subject_admin' ), $r ),
				apply_filters( 'app_email_body_admin_copy', $this->clear_links( $this->_replace( $add_text . $body, $r, $context.'_message_admin' ), $r ), $r ),
				$headers,
				$attach
				)
			) {
				do_action( 'app_email_sent', $body, $r, $app_id, $context, $resend, $edit );
				return true;
			}
			else {
				do_action( 'app_email_failed', $body, $r, $app_id, $context, $resend, $edit );
				return false;
			}
		}
		else {
			$this->log( sprintf( __('Message sending to %s for appointment ID:%s has failed.','wp-base'), $r->email, $app_id ) );
			do_action( 'app_email_failed', $body, $r, $app_id, $context, $resend, $edit );
		}
	}

	/**
	 * Send confirmation or cancellation or pending or completed email
	 * Replaced by send_mail method. Kept here for backwards compatibility
	 * @param app_id: ID of the app whose confirmation will be sent
	 * @param cancel: Send a cancellation message
	 * @param resend: Confirmation message is being resent manually
	 * @param edit: Booking edited (possible with confirmed and pending)
	 * @param pending: Send a pending message
	 * @param completed: Send a completed message
	 * @return bool
	 */		
	function send_confirmation( $app_id, $cancel = false, $resend = false, $edit = false, $pending = false, $completed =false ) {

		if ( $cancel )
			$context = 'cancellation';
		else if ( $completed )
			$context = 'completed';
		else if ( $pending )
			$context = 'pending';
		else
			$context = 'confirmation';
		
		$this->send_email( $app_id, $context, $resend, $edit );
	}
	
	/**
	 * Send notification email to admin
	 * @param cancel: Whether this is a cancellation
	 * This is different then copy of cancellation email sent to client. 
	 * Disabling of this is via "Notification" setting
	 * @since 1.0.2
	 */		
	function send_notification( $app_id, $cancel = false ) {
		// In case of cancellation, continue
		if ( !$cancel && 'yes' != wpb_setting("send_notification") )
			return false;
		
		$mail_result = false;
			
		$r = $this->get_app( $app_id );
		
		if ( empty( $r->ID ) )
			return false;
				
		// Do not send notification for child appointments
		if ( $r->parent_id )
			return false;
		
		// Multiple notification emails may be required. Then an array of emails can be returned here
		$admin_email = apply_filters( 'app_notification_email', BASE('User')->get_admin_email( ), $r );
		// Let addons modify app_id
		$app_id_email = apply_filters( 'app_id_email', $app_id );
		$link_to_app = admin_url("admin.php?page=appointments&app_or_fltr=1&type=all&stype=app_id&app_s=".$app_id_email);
		
		if ( $cancel ) {
			$subject = sprintf( __('An appointment has been cancelled (#%d)','wp-base'), $app_id );
			$body = sprintf( __('Appointment with ID %s has been cancelled by the client. You can access it by clicking this link: %s','wp-base'), $app_id );
			$body .= "\n\n";
			$body = sprintf( __('You can access it by clicking this link: %s','wp-base'), $link_to_app );
		}
		else {
			$subject = sprintf( __('An appointment requires your approval (#%d)','wp-base'), $app_id );
			$body = sprintf( __('The new appointment has an ID %s.','wp-base'), $app_id );
			$body .= "\n\n";
			$body = sprintf( __('You can approve or edit it by clicking this link: %s','wp-base'), $link_to_app );
		}
		
		$subject = apply_filters( 'app_notification_subject', $subject, $r );
		$body = apply_filters( 'app_notification_body', $body, $r );
		
		$mail_result = wp_mail( 
			$admin_email,
			$this->_replace( $subject, $r, 'notification_subject' ),
			$this->_replace( $body, $r, 'notification_message' ),
			$this->message_headers( 'notification', $r ),
			apply_filters( 'app_email_attachment', array(), $r, 'notification_admin' )
		);
			
		if ( $mail_result ) {
			do_action( 'app_email_sent', $body, $r, $app_id, 'notification' );
			if ( 'yes' == wpb_setting("log_emails") )
				$this->log( sprintf( __('Notification message sent to %1$s for booking ID:%2$s','wp-base'), BASE('User')->get_admin_email( true ), $app_id ) );
		}
			
		// Also notify service provider if he is allowed to confirm it
		// Note that message itself is different from that of the admin
		// Don't send repeated email to admin if he is the provider
		if ( $r->worker &&  $admin_email != $this->get_worker_email( $r->worker ) && 'yes' == wpb_setting('allow_worker_confirm') ) {

			if ( $cancel ) {
			/* Translators: First %s is for appointment ID and the second one is for date and time of the appointment */
				$body = sprintf( __('Cancelled appointment has an ID of %1$d and it was scheduled for %2$s.','wp-base'), $app_id, date_i18n( $this->dt_format, strtotime($r->start) ) );
			}
			else
				$body = sprintf( __('The new appointment has an ID %s for %s and you can confirm it using your profile page.','wp-base'), $app_id, date_i18n( $this->dt_format, strtotime($r->start) ) );

			$mail_result = wp_mail( 
				$this->get_worker_email( $r->worker ),
				$this->_replace( $subject, $r, 'notification_subject' ),
				$this->_replace( $body, $r, 'notification_message' ),
				$this->message_headers( 'notification_provider', $r ),
				apply_filters( 'app_email_attachment', array(), $r, 'notification_provider' )
			);
			
			if ( $mail_result ) {
				do_action( 'app_email_sent', $this->_replace( $body, $r, 'notification_message' ), $r, $app_id, 'notification_provider' );
				if ( 'yes' == wpb_setting("log_emails") )
					$this->log( sprintf( __('Notification message sent to %1$s for booking ID:%2$s','wp-base'), $this->get_worker_email( $r->worker ), $app_id ) );
				return true;
			}
			else {
				do_action( 'app_email_failed', $this->_replace( $body, $r, 'notification_message' ), $r, $app_id, 'notification_provider' );
				return false;
			}
		}
		else if ( $mail_result )
			return true;
		
		return false;
	}
	
	/**
	 * Find and clean all possible links in admin/SP email copies to prevent accidential clicks
	 * @since 3.0
	 */	
	function clear_links( $text, $r ) {
		if ( empty( $r->ID ) )
			return $text;
		
		$find = array();
		/* Replace CANCEL, EDIT, CONFIRM placeholders */
		foreach ( array('cancel','edit','confirm') as $action ) {
			$temp1 = wpb_add_query_arg( array('app_'.$action=>1, 'app_id'=>$r->ID, $action.'_nonce'=>$this->create_hash($r,$action)), home_url() );
			$temp2 = '<a href="'.$temp1.'">' . $this->get_text($action) . '</a>';
			$find[] = $temp2;
			$find[] = $temp1;
		}
		
		if ( !empty( $find ) )
			$text = str_replace( $find, __('(Link removed in admin/provider copy...)','wp-base'), $text );

		/* Replace PAYPAL placeholders */
		return str_replace( 'PAYPAL', __('(PayPal button removed in admin/provider copy...)','wp-base'), $text );
	}
	
	/**
	 * Create a hash
	 * @param $app: An appointment object, or a set of app_ids for cookie
	 * @param $anon_user_identifier: User email, phone, or name to identify anon user
	 * @since 3.0
	 */	
	function create_hash( $app, $context, $anon_user_identifier = null ) {
		if ( 'cancel' == $context )
			$context = '';	// For backwards compatibility
		
		if ( strpos( $context, 'cookie' ) === false )
			return md5( $app->ID . wpb_get_salt() . $context . strtotime( $app->created ) );
		else
			return md5( serialize( $app ) . wpb_get_salt() . $context . $anon_user_identifier );
	}
	
	/**
	 * Provide a list of available email types that client receives
	 * @since 3.0
	 * @return string
	 */	
	function email_types(){
		return apply_filters( 'app_email_types', array('confirmation','pending','reminder','dp_reminder','edited','cancellation','completed','follow_up','waiting_list','waiting_list_notify') );
	}

	/**
	 * Replace placeholders with real values for email subject and content (or any other text related to an app object)
	 * @param $text: Text (template) to be replaced with actual values
	 * @param $r: Appointment object
	 * @param $context: Where this method is called from - Subject and message for these [e.g. confirmation_message, confirmation_subject]: 
	 * confirmation, confirmation_admin, reminder, dp_reminder, reminder_provider, cancellation, cancellation_admin, notification_admin, notification_provider. 
	 * Attachment: confirmation_attachment, reminder_attachment, dp_reminder_attachment, cancellation_attachment
	 * This function can also be used as 1) general replace with $context='subject' 2) HTML preserved replace with $context='attachment' or 'text'
	 * @return string
	 */	
	function _replace( $text, $r, $context = '' ) {
		
		// To feed another text template, e.g. in client's native language
		$text = apply_filters( 'app_email_replace_pre', $text, $r, $context );

		$payment = $balance = $paid = 0;
		
		/* Total Price */
		$price = !empty( $r->price ) ? $r->price : 0;

		/* Required Deposit */
		$deposit_req = !empty( $r->deposit ) ? $r->deposit : 0;
			
		/* BALANCE and TOTAL_PAYMENT */
		if ( strpos( $text, "BALANCE" ) !== false || strpos( $text, "TOTAL_PAYMENT" ) !== false ) {
			$p = $this->get_total_paid_by_app( $r->ID, false );
			$paid = $p ? $p : 0;
			$balance = $paid/100 - $price - $deposit_req;
		}
		
		/* PAYPAL_BUTTON */
		# Cannot be used in subjects and attachments
		$paypal = '';
		if ( !empty( $r->ID ) && 'paypal_subject' != $context && 'yes' == wpb_setting('use_html') && 
			strpos( $context, "attachment" ) === false && strpos( $text, "PAYPAL" ) !== false && class_exists('WpB_Gateway_Paypal_Standard') ) {
			
			if ( !$balance ) {
				$p = $this->get_total_paid_by_app( $r->ID, false );
				$paid = $p ? $p : 0;
				$balance = $paid/100 - $price - $deposit_req;
			}
			if ( $balance < 0 ) {
				$pp = new WpB_Gateway_Paypal_Standard;
				$paypal = $pp->payment_form($r->ID, -1, abs($balance) );
			}
		}

		// Formatting Monetary Values
		$tax_percent		= !empty( $r->service ) ? $this->get_tax( $r->service ) : wpb_setting('tax', 0);
		$tax				= wpb_format_currency( '', $price * $tax_percent/100, true );
		$price_without_tax	= wpb_format_currency( '', $price - $tax, true );
		$balance			= wpb_format_currency( '', $balance, true );
		$price				= wpb_format_currency( '', $price, true );
		$payment			= wpb_format_currency( '', $paid/10, true );
		$deposit_req		= wpb_format_currency( '', $deposit_req, true );
		$downpayment		= wpb_format_currency( '', $this->calc_downpayment($r->price), true );

		/* PAYMENT_METHOD */
		$payment_method_public = $payment_method_name = '';
		$method_code = wpb_get_app_meta( $r->ID, 'payment_method' );
		if ( $method_code ) {
			if ( 'marketpress' == $method_code )
				$payment_method = 'MarketPress';
			else if ( 'woocommerce' == $method_code )
				$payment_method = 'WooCommerce';
			else {
				global $app_gateway_active_plugins;
				foreach ( (array)$app_gateway_active_plugins as $code=>$plugin ) {
					if ( $method_code == $plugin->plugin_name ) {
						$payment_method_public = $plugin->public_name;
						$payment_method_name = $plugin->plugin_name;
						break;
					}
				}
			}
		}
		
		/* MANUAL_PAYMENT_NOTE */
		$manual_payment_note = '';
		if ( ( $r->price > 0 || $r->deposit > 0 ) && 'manual-payments' == $payment_method_name ) {
			$manual_payment_note = $this->get_setting('gateways->manual-payments->email-note');
		}
		else $manual_payment_note = '';
		
		// Client data
		if ( !empty( $r->ID ) ) {
			$client_name =  BASE('User')->get_client_name( $r->ID, $r, false, 0 ); // Do not add client link, do not abbreviate
			$seats = !empty( $r->seats ) ? $r->seats : 1;
			foreach ( $this->get_user_fields() as $f ) {
				if ( 'name' == $f )
					continue;
				
				${$f} = wpb_get_app_meta( $r->ID, $f );
			}
			$note = wpb_get_app_meta( $r->ID, 'note' );
			
			$client_link = BASE('User')->get_client_name($r->ID, $r, true);
		}
		else {
			$cuid			= get_current_user_id();
			$client_name	= __('Test Client', 'wp-base');
			$first_name		= __('Test Client First Name', 'wp-base');
			$last_name		= __('Test Client Last Name', 'wp-base');
			$seats			= 1;
			$phone			= '+12345678901234';
			$email			= BASE('User')->get_admin_email( true );
			$address		= __('Test Address', 'wp-base');
			$city			= __('Test City', 'wp-base');
			$zip			= __('Test Zip', 'wp-base');
			$note			= __('This is a test appointment created by WP BASE','wp-base');
			$client_link 	= '';
		}
		
		/* CANCEL, EDIT, CONFIRM */
		foreach ( array('cancel','edit','confirm') as $action ) {
			${$action.'_reply'} = '';
			
			if ( empty( $r->ID ) )
				continue;
			
			if ( 'edit' == $action && ( !BASE('FEE') || BASE('FEE')->is_too_late( $r ) ) )
				continue;
			else if ( 'cancel' == $action && BASE('Cancel')->is_too_late( $r ) )
				continue;
			
			if ( 'yes' == wpb_setting('allow_'.$action) ) {
				${$action.'_reply'} = wpb_add_query_arg( array('app_'.$action=>1, 'app_id'=>$r->ID, $action.'_nonce'=>$this->create_hash($r,$action)), apply_filters( 'app_cancel_confirm_url', home_url() ) );
				if ( strpos( $context, 'attachment' ) !== false )
					${$action.'_reply'} = '<a href="'.${$action.'_reply'}.'">' . $this->get_text($action) . '</a>';
			}
		}

		# Check if [app_list] shortcode has been used in email. If so, run it.
		if ( !empty( $r->user ) && strpos( $text, "[app_list" ) !== false ) {
			if ( preg_match_all( '/' . get_shortcode_regex(array('app_list')) . '/', $text, $shortcode_arr, PREG_SET_ORDER ) ) {
				foreach( $shortcode_arr as $shortcode ) {
					// Check columns and remove unrelated ones
					$atts = shortcode_parse_atts( $shortcode[3] );
					if ( isset( $atts['columns'] ) )
						$columns = wpb_sanitize_commas( str_replace( array('edit','cancel','pdf','gcal'),'', $atts['columns'] ) );
					else
						$columns = 'id,service,worker,date_time,status';

					// Force user_id as owner of the booking
					$html = do_shortcode( '[app_list '. $shortcode[3] . ' _email="1" what="client" columns="'.$columns.'" user_id="'.$r->user.'"]');
					$text = str_replace( $shortcode[0], $html, $text );
				}
			}			
		}
		
		// Check if [app_hide] or [app_show] shortcode has been used in email. If so, run it.
		if ( 'subject' != $context && 'title' != $context && (strpos( $text, "[app_hide" ) !== false || strpos( $text, "[app_show" ) !== false) ) {
			$this->set_lsw( $r->location, $r->service, $r->worker );
			
			if ( preg_match_all( '/' . get_shortcode_regex(array('app_hide','app_show')) . '/', $text, $shortcode_arr, PREG_SET_ORDER ) ) {
				foreach( $shortcode_arr as $shortcode ) {
					// Check columns and remove unrelated ones
					$atts = shortcode_parse_atts( $shortcode[3] );
					if ( isset( $atts['if'] ) )
						$if = $this->_replace( $atts['if'], $r, 'subject' );
					else
						$if = '';
					
					if ( 'app_hide' == $shortcode[2] )
						$html = $this->hide_( $if, $shortcode[5] );
					else
						$html = $this->show_( $if, $shortcode[5] );
					$text = str_replace( $shortcode[0], $html, $text );
				}
			}			
		}
		
		// Strip [app_ shortcodes
		$text = wpb_strip_shortcodes( $text );
		
		$timezone = null;
		$timezones_enabled = BASE('Timezones') && BASE('Timezones')->is_enabled() ? true : false;
		if ( $timezones_enabled && $maybe_timezone = wpb_get_app_meta( $r->ID, 'timezone' ) )
			$timezone = $maybe_timezone;
		
		$format = $this->is_daily( $r->service ) ? $this->date_format : $this->dt_format;

		/* "MANUAL_PAYMENT_NOTE" should come at first, so that its content can be replaced too
		 * "END_DATE_TIME" should come before "DATE_TIME"
		 *  LOCATION ADDRESS before ADDRESS 
		 */
		$result = str_replace( 
					array(	"MANUAL_PAYMENT_NOTE", "PAYMENT_METHOD", "STATUS",
							"WORKER_PHONE", "WORKER_EMAIL", "WORKER_ID", "WORKER", 
							"SITE_NAME", "HOME_URL", "SERVICE_ID", "SERVICE",
							"DURATION",							
							"SERVER_END_DATE_TIME", "SERVER_START_DATE_TIME", "SERVER_DATE_TIME", 
							"CREATED", 
							"END_DATE_TIME", 
							"START_DATE_TIME", "DATE_TIME", "TIMEZONE",
							"END_TIME", "START_TIME",
							"LOCATION_MAP", "LOCATION_ADDRESS", "LOCATION_ID", "LOCATION", 
							"APP_ID", "CANCEL", "EDIT", "CONFIRM", "NOF_APPS", "SEATS",
							"CLIENT_LINK",							
							"CLIENT", "FIRST_NAME", "LAST_NAME", "PHONE", "ADDRESS", "EMAIL", "CITY", "POSTCODE", "NOTE",
							"PRICE_WITHOUT_TAX", "PRICE", "TAX_PERCENT", "TAX", "PAYPAL", "BALANCE", "DEPOSIT", "DOWN_PAYMENT", "TOTAL_PAYMENT", 
						),
					array(	$manual_payment_note, $payment_method_public, $this->get_status_name( $r->status ),
							BASE('User')->get_worker_phone($r->worker), $this->get_worker_email($r->worker), $r->worker, $this->get_worker_name($r->worker), 
							wp_specialchars_decode(get_option('blogname'), ENT_QUOTES), home_url(), $r->service, $this->get_service_name($r->service), 
							wpb_format_duration( wpb_get_duration( $r->service ) ),
							date_i18n( $format, strtotime( $r->end ) ), date_i18n( $format, strtotime( $r->start ) ), date_i18n( $format, strtotime( $r->start ) ), 
							mysql2date( $this->dt_format, $r->created ), 
							date_i18n( $format, $this->client_time( strtotime( $r->end ), $timezone ) ), 
							date_i18n( $format, $this->client_time( strtotime( $r->start ), $timezone ) ), date_i18n( $format, $this->client_time( strtotime( $r->start ), $timezone ) ), $timezone,
							date_i18n( $this->time_format, strtotime( $r->end ) ), date_i18n( $this->time_format, strtotime( $r->start ) ),
							'', wpb_get_location_meta( $r->location, 'address' ), $r->location, $this->get_location_name( $r->location ),  
							$r->ID, $cancel_reply, $edit_reply, $confirm_reply, 1, $seats,
							$client_link,							
							$client_name, $first_name, $last_name, $phone, $address, $email, $city, $zip, $note, 
							$price_without_tax, $price, $tax_percent, $tax, $paypal, $balance, $deposit_req, $downpayment, $payment,  
						),
					$text
				);
		
		// Format line breaks if html is selected or this is an attachment. Do not apply for subject field 		
		if ( ( wpb_setting('use_html') == 'yes' || strpos( $context, 'attachment' ) !== false || strpos( $context, 'text' ) !== false ) && strpos( $context, 'subject' ) === false && strpos( $context, 'title' ) === false  )
			$is_html_body = true;
		else
			$is_html_body = false;
		
		if ( $is_html_body && apply_filters( 'app_email_replace_use_wpautop', true, $r, $context ) )
			$result = wpautop(  wptexturize( $result ) );
				
		$result = apply_filters( 'app_email_replace', $result, $r, $context );

		// Check if addons completed the markup. If not, try to fix it.
		if ( $is_html_body && $result && strpos( $result, '</html>' ) === false )
			return '<!DOCTYPE html><html><body>' . str_replace( array('<!DOCTYPE html>','<html>','</html>','<body>','</body>'), '', $result ) . '</body></html>';
		else
			return $result;
	}

	/**
	 * Email message headers
	 * @param $app: Appt object (e.g. to change from field based on appt)
	 * @param $context: Where this method is called from (see _replace method)
	 */	
	function message_headers( $context = '', $app=null ) {
		$from_name = wp_specialchars_decode( wpb_setting( 'from_name', get_option( 'blogname' ) ), ENT_QUOTES );
		
		$from_email = wpb_setting('from_email');
		
		if ( !is_email( $from_email ) ) {
			$url = str_replace( array('http://','https://','www.'), '', site_url() );
			if ( strpos( $url, '/' ) !== false )
				$url = strstr( $url, '/', true );
			
			$from_email = 'no-reply@' . $url; # no-reply@domain if From email left empty
		}
		 
		$from_email = apply_filters( 'app_from_email', $from_email, $context, $app );
		
		if ( is_email( $from_email ) )
			$headers[] = "From: " . $from_name . " <" .$from_email. ">";
		else
			$headers[] = "From: " . $from_name . " <" . BASE('User')->get_admin_email( true ). ">";

		// HTML email or not
		if ( 'yes' == wpb_setting('use_html') )
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
		else
			$headers[] = 'Content-Type: text/plain;';
		
		// Modify message headers
		return apply_filters( 'app_message_headers', $headers, $context, $app );
	}			

/****************************************
* Methods for updating appointments
*****************************************

	/**
	 * Prevent auto increment go to too high values because of INSERT INTO DUPLICATE KEY clause or multiple deletes
	 * @since 2.0
	 */
	function adjust_auto_increment( $table = false ) {
		if ( false === $table )
			$table = $this->app_table;
		$max = $this->db->get_var( "SELECT MAX(ID) FROM " . $table . " " );
		if ( $max )
			 $this->db->query( "ALTER TABLE " . $table ." AUTO_INCREMENT=". ($max+1). " " );
	}

	/**
	 * Change status for a given booking (Manual changes by admin not included)
	 *
	 * @param $stat				string				New status
	 * @param $app_id_or_obj	integer|object		Booking ID or app object
	 * @param $force_childs		bool				Change status of childs too
	 * @param $renew_created	bool				Change created value with current time
	 * @return bool
	 */		
	function change_status( $stat, $app_id_or_obj, $force_childs = true, $renew_created = false ) {
		if ( !$app_id_or_obj || !$stat )
			return false;
		
		if ( is_numeric( $app_id_or_obj ) )
			$app_id = $app_id_or_obj;
		else if ( is_object( $app_id_or_obj ) ) {
			$app	= $app_id_or_obj;
			$app_id = $app->ID;
		}
		else
			return false;
		
		do_action( 'app_change_status_pre', $stat, $app_id, $force_childs );
		
		// For a completed booking, make the deposit 0
		if ( 'completed' == $stat )
			$update_what = array('status'	=> $stat, 'deposit' => 0);
		else if ( $renew_created )
			$update_what = array('status'	=> $stat, 'created' => date("Y-m-d H:i:s", $this->_time));
		else
			$update_what = array('status'	=> $stat);
			
		$result = $this->db->update( $this->app_table,
									$update_what,
									array('ID'		=> $app_id)
					);
					
		if ( $result ) {
			
			$app		= !empty( $app ) ? $app : $this->get_app( $app_id );
			$method 	= !empty( $app->payment_method ) ? $app->payment_method : '';
			$old_stat	= !empty( $app->status ) ? $app->status : '';
			
			if ( 'manual-payments' != $method && 'removed' === $stat && 
				( ('pending' === $old_stat && $method) || ('cart' == $old_stat && !$method ) ) ) {
				wpb_add_app_meta( $app_id, 'abandoned', $old_stat );
			}
			
			wpb_flush_cache();
			do_action( 'app_change_status', $app_id );
			do_action( 'app_status_changed', $stat, $app_id );
			
			// Send completed email
			if ( 'completed' == $stat ) {
				$this->maybe_send_message( $app_id, 'completed' );
			}

			// Change child status
			if ( $force_childs )
				do_action( 'app_change_status_children', $stat, $app_id );

			return true;
		}
		else
			return false;
	}

	/**
	 *	See update_appointments
	 *	For backwards compatibility
	 */	
	function remove_appointments( ) {
		$this->update_appointments( );
	}

	/**
	 *	Remove an appointment if not paid or expired
	 *	Clear expired appointments.
	 *  Change status to running if end time has not passed yet
	 *	Change status to completed if they are confirmed or paid
	 *	Change status to removed if they are pending or reserved
	 *  @since 2.0 (previously named as remove_appointments)
	 */	
	function update_appointments( ) {
		$did_something = false;
		
		$q = $this->reserved_status( __FUNCTION__ ); // Test appts not included		

		$cdate = apply_filters( 'app_auto_removal_ref_time', date( "Y-m-d H:i:s", $this->_time ) );
		$allow_late_booking = 'yes' === wpb_setting('allow_now');
		
		# All bookings will be expired after their end times
		# Pending and waiting ones may also expire at start time, if allow late booking not set
		# Confirmed and paid bookings will become running from start time until end time
		$expireds = $this->db->get_results( "SELECT * FROM " . $this->app_table . " WHERE start<'" . $cdate. "' AND (".$q.") " );
		if ( $expireds ) {
			$new_status = '';
			foreach ( $expireds as $expired ) {
				if ( $this->_time > strtotime( $expired->end, $this->_time ) ) {
					if ( 'pending' == $expired->status || 'reserved' == $expired->status || 'waiting' == $expired->status  )
						$new_status = 'removed';
					else if ( 'confirmed' == $expired->status || 'paid' == $expired->status || 'running' == $expired->status )
						$new_status = 'completed';
					else
						do_action( 'app_update_appointments', $expired );
				}
				else if ( !$allow_late_booking  && ('pending' == $expired->status || 'waiting' == $expired->status ) )
					$new_status = 'removed';
				else if ( 'confirmed' == $expired->status || 'paid' == $expired->status )
					$new_status = 'running';
				
				if ( $new_status && $new_status != $expired->status ) {
					$did_something = true;
					$deleted = false;
					// Auto delete before changing to removed if conditions meet
					if ( 'removed' === $new_status )
						$deleted = $this->auto_delete( $expired );
					if ( !$deleted )
						$this->change_status( $new_status, $expired );
				}
			}
			
			do_action( 'app_expired_appointments', $expireds );
		}
		
		$clear_secs		= (int)wpb_setting("clear_time") * 60 *60;				# Setting in hours
		$clear_secs_pp	= (int)wpb_setting("clear_time_pending_payment") * 60;	# Setting in minutes
		$clear_secs_pp	= $clear_secs_pp ? $clear_secs_pp : $clear_secs;
		$countdown_time	= (int)wpb_setting("countdown_time") * 60;				# Setting in minutes - Expiry time for cart items
		
		# Clear appointments that are staying in pending status long enough
		if ( $clear_secs > 0 || $clear_secs_pp > 0 || $countdown_time>0 ) {
			$expireds = $this->db->get_results( 
					"SELECT * FROM " . $this->app_table . " 
					WHERE 
					(status='waiting' AND $clear_secs>0 AND created<'" . date ("Y-m-d H:i:s", $this->_time - $clear_secs ). "')
					OR
					(status='pending' AND (payment_method IS null OR payment_method='' OR payment_method='manual-payments') 
					AND $clear_secs>0 AND created<'" . date ("Y-m-d H:i:s", $this->_time - $clear_secs ). "')
					OR
					(status='pending' AND payment_method IS NOT null AND payment_method<>'' AND payment_method<>'manual-payments' 
					AND $clear_secs_pp>0 AND created<'" . date ("Y-m-d H:i:s", $this->_time - $clear_secs_pp ). "')
					OR
					((status='cart' OR status='hold') AND $countdown_time>0 AND created<'" . date ("Y-m-d H:i:s", $this->_time - $countdown_time ). "')
			");
			if ( $expireds ) {
				foreach ( $expireds as $expired ) {
					// Auto delete before changing to removed if conditions meet
					$deleted = $this->auto_delete( $expired );

					if ( !$deleted )
						$this->change_status( 'removed', $expired );
				}
				$did_something = true;
			}
		}

		# Clear expired, removed appointments (Older than End time + delete_time)
		if ( 'yes' === wpb_setting( 'auto_delete' ) ) {
			
			$last_update = get_option( "app_last_hourly_update" ); // This is already cached by WP
			
			# To save mysql resources, run this once an hour
			if ( intval( $this->_time/3600 ) > $last_update ) {
				
				$delete_lag		= wpb_setting( 'auto_delete_time' ) ? wpb_setting( 'auto_delete_time' ) *3600 : 0; # In secs
				$delayed_date	= date( "Y-m-d H:i:s", $this->_time + $delete_lag );
				do_action( 'app_delete_pre', 'auto_several' );
				
				if ( $ids = $this->db->get_col( "SELECT * FROM {$this->app_table} WHERE status='removed' AND end<'{$delayed_date}' " ) ) {
					$ids_csv = implode( ',', $ids );
					if ( $this->db->query( "DELETE FROM {$this->app_table} WHERE ID IN ({$ids_csv})" ) ) {
						$this->db->query( "DELETE FROM {$this->meta_table} WHERE meta_type='app' AND object_id IN ({$ids_csv})" );
					}
					do_action( 'app_deleted', 'auto_several', $ids );
					$did_something = true;
				}
				update_option( "app_last_hourly_update", intval( $this->_time/3600 ) );
			}
		}
		
		# If made changes in the DB, clear cache 
		if ( $did_something )
			wpb_flush_cache( true );
		
		# Save last cache clear time
		update_option( "wp_base_last_update", $this->_time );
	}

	/**
	 * Automatically delete an expired record
	 * @param expired: App object to be deleted
	 * @since 2.0
	 */	
	function auto_delete( $expired ) {
		$result = false;
		if ( 'yes' === wpb_setting( 'auto_delete' ) ) {
			$delete_lag = wpb_setting( 'auto_delete_time' ) ? wpb_setting( 'auto_delete_time' ) *3600 : 0; # In secs
			
			if ( $this->_time > strtotime( $expired->end, $this->_time ) + $delete_lag ) {
				
				do_action( 'app_delete_pre', 'auto', $expired->ID ); 
				
				if ( $result = $this->db->query( $this->db->prepare("DELETE FROM ".$this->app_table." WHERE ID=%d LIMIT 1", $expired->ID) ) ) {
					$this->db->query( $this->db->prepare("DELETE FROM " .$this->meta_table." WHERE object_id=%d AND meta_type='app'", $expired->ID) );
					do_action( 'app_deleted', 'auto', $expired->ID ); 
				}
			}
		}
			
		return $result;
	}
	

}

	require_once( WPB_PLUGIN_DIR . "/includes/front.php" );

else:
	add_action( 'admin_notices', '_wpb_plugin_conflict_own' );
endif;

