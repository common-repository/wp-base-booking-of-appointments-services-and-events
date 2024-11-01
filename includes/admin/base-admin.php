<?php
/**
 * WPB Admin
 *
 * Handles admin pages and includes methods for admin side
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBAdmin' ) ) {

class WpBAdmin extends WpBFront {
	
	/**
     * Page IDs for Make an Appointment and List of Bookings pages
	 * created by Quick Start
	 * @Integer
     */
	public $created_page_id = 0;
	public $created_list_page_id = 0;

	/**
     * Admin message displayed in admin nag after save, edit, cancel operations
	 * @String
     */
	public $save_message = '';

	/**
     * Constructor
     */
	function __construct() {
		parent::__construct();
	}

	/**
     * Add admin side actions
     */
	function add_hooks_admin() {
		
		if ( !defined( 'WPB_EDITOR_HEIGHT' ) )
			define( 'WPB_EDITOR_HEIGHT', '300px' );														// Height of WP Editor used in settings

		include_once( WPB_PLUGIN_DIR . '/includes/constant-data.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/admin/bookings.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/admin/monetary-settings.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/admin/display-settings.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/admin/global-settings.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/admin/help.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/admin/services.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/admin/toolbar.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/admin/transactions.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/admin/welcome.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/admin/tinymce.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/lib/plugin-updater.php' );
		
		add_action( 'admin_print_scripts', array( $this, 'admin_head' ) );								// Print some js codes to header
		add_action( 'admin_footer', array( $this, 'admin_footer' ) );									// Add scripts to footer
		add_action( 'admin_footer', 'wpb_updating_html' );												// Spinner panel
		add_action( 'admin_menu', array(  $this, 'admin_init' ) ); 										// Creates admin settings window. Correct action, because screen names are generated just before this hook
		add_action( 'admin_notices', array( $this, 'admin_notices' ) ); 								// Warns admin
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );							// Load scripts
		add_action( 'admin_print_styles', array( $this, 'admin_css' ) );								// Add style to admin pages
		add_filter( 'plugin_action_links_' . WPB_PLUGIN_BASENAME, array( $this, 'add_action_links' ));	// Add Settings link
		add_action( 'wp_dashboard_setup', array( $this, 'add_app_counts') );							// Add app counts
		add_filter( 'manage_pages_columns', array( $this, 'manage_pages_add_column' ) );				// Add column to Manage Pages
		add_action( 'manage_pages_custom_column', array($this, 'manage_pages_column_content'), 10, 2 );	// Add content tp column td
		add_action( 'wp_ajax_delete_log', array( $this, 'delete_log' ) ); 								// Clear log
		add_action( 'wp_ajax_dismiss', array( $this, 'dismiss' ) );
		
	}

	/**
	 * Load styles
	 */	
	function admin_css() {
		// Do not load on non wp-base pages
		if ( !$this->is_app_page() ) {
			if ( 'widgets' === wpb_get_current_screen_id( ) ) {
				wp_enqueue_style( 'wp-base-admin', WPB_PLUGIN_URL . '/css/admin.css', array(), self::version );
			}
			
			return;
		}
		
		if ( 'yes' != wpb_setting('disable_css_admin') ) {

			// These should come first so admin.css can overwrite styles
			if ( $this->is_like_app_front_page() ) {
				wp_enqueue_style( 'wp-base', $this->get_front_css_file(), array(), self::version );
			}
			wp_enqueue_style( 'wp-base-admin', WPB_PLUGIN_URL . '/css/admin.css', array(), self::version );
			wp_enqueue_style( 'wp-base-updating', WPB_PLUGIN_URL . "/css/updating.css", array(), self::version );
			wp_enqueue_style( 'jquery-multiselect', WPB_PLUGIN_URL . '/css/jquery.multiselect.css', array(), self::version );
			wp_enqueue_style( 'jquery-multiselect-filter', WPB_PLUGIN_URL . '/css/jquery.multiselect.filter.css', array(), self::version );
			wp_enqueue_style( 'jquery-ui-structure', WPB_PLUGIN_URL . '/css/jquery-ui.structure.min.css', array(), self::version );
			wp_enqueue_style( "jquery-ui-".$this->selected_theme(), $this->get_theme_file( ), array(), self::version );
			wp_enqueue_style( 'jquery-qtip', WPB_PLUGIN_URL . '/css/jquery.qtip.css', array(), self::version );
			wp_enqueue_style( 'jquery-datatables-responsive', WPB_PLUGIN_URL . '/css/responsive.dataTables.css', array(), self::version );
			wp_enqueue_style( 'jquery-datatables-responsive-ui', WPB_PLUGIN_URL . '/css/responsive.jqueryui.css', array(), self::version );
			wp_enqueue_style( 'jquery-datatables-tabletools', WPB_PLUGIN_URL . '/css/dataTables.tableTools.css', array(), self::version );
			wp_enqueue_style( 'jquery-datatables-jqueryui', WPB_PLUGIN_URL . '/css/dataTables.jqueryui.css', array(), self::version );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'editor-buttons' );	// Fix for: wp_editor call does not load editor.min.css on emails page
			if ( is_rtl() ) {
				wp_enqueue_style( "wp-base-rtl", $this->plugin_url . "/css/common-rtl.css", array('wp-base-admin'), self::version );
				wp_enqueue_style( "wp-base-admin-rtl", $this->plugin_url . "/css/admin-rtl.css", array('wp-base-admin'), self::version );
			}
		}
	}

	/**
	 * Load scripts
	 */	
	function admin_scripts() {
		wp_enqueue_script( 'wp-color-picker' ); // This has a strange conflict with mobile theme or mobile.js if used later
		wp_register_script( 'jquery-app-qtip', WPB_PLUGIN_URL . '/js/jquery.qtip.min.js', array('jquery'), self::version );
		wp_register_script( 'jquery-app-multiselect', WPB_PLUGIN_URL . '/js/jquery.multiselect.js', array('jquery-ui-core','jquery-ui-widget', 'jquery-ui-position'), self::version );
		wp_register_script( 'jquery-app-multiselect-filter', WPB_PLUGIN_URL . '/js/jquery.multiselect.filter.js', array('jquery-app-multiselect'), self::version );
		wp_register_script( 'jquery-app-autosize', WPB_PLUGIN_URL . '/js/jquery.autosize-min.js', array('jquery'), self::version);
		wp_register_script( 'codemirror', WPB_PLUGIN_URL . '/js/codemirror.min.js', array(), self::version);
		wp_register_script( 'jquery-blockui', $this->plugin_url . '/js/jquery.blockUI.js', array('jquery'), self::version );
		wp_register_script( 'app-common', WPB_PLUGIN_URL . '/js/app-common.min.js', array('jquery','jquery-ui-button', 'jquery-app-qtip','jquery-blockui'), self::version );	
		wp_register_script( 'app-admin', WPB_PLUGIN_URL . '/js/app-admin.js', array('jquery','app-common'), self::version );
		
		$screen_id = wpb_get_current_screen_id( );
		if ( 'widgets' === $screen_id ) {
			wp_enqueue_script( 'jquery-app-qtip' );
		}

		if ( !$this->is_app_page() )
			return;

		// Only for admin
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'codemirror' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-multidatespicker', WPB_PLUGIN_URL . '/js/jquery-ui.multidatespicker.js', array('jquery-ui-datepicker'), self::version );
		
		// Common for front and admin

		wp_enqueue_script( 'jquery-ui-button' );
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-app-autosize' );
		wp_enqueue_script( 'jquery-ui-datepicker' );

		global $wp_version;
		$locale = strtolower( $this->get_locale() );
		$locale_short = current(explode('-',$locale ));
		if ( version_compare( $wp_version, '4.6', '<' ) ) {
			foreach( array( $locale, $locale_short ) as $lcl ) {
				if ( file_exists( $this->plugin_dir . '/js/locale/datepicker-'.$lcl.'.js' ) ) {
					wp_enqueue_script( 'jquery-ui-datepicker-locale', WPB_PLUGIN_URL . '/js/locale/datepicker-'.$lcl.'.js', array('jquery-ui-datepicker'), self::version );
					break;
				}
			}
		}
		wp_enqueue_script( 'jquery-app-multiselect' );
		wp_enqueue_script( 'jquery-app-multiselect-filter' );
		wp_enqueue_script( 'jquery-datatables', WPB_PLUGIN_URL . '/js/jquery.dataTables.min.js', array('jquery'), self::version );
		if ( !$locale || 'en' == $locale || 'en-us' == $locale )
			wp_enqueue_script( 'jquery-datatables-moment', WPB_PLUGIN_URL . '/js/moment.min.js', array('jquery'), self::version );
		else
			wp_enqueue_script( 'jquery-datatables-moment', WPB_PLUGIN_URL . '/js/moment-with-locales.min.js', array('jquery'), self::version );
		wp_enqueue_script( 'jquery-datatables-tabletools', WPB_PLUGIN_URL . '/js/dataTables.tableTools.min.js', array('jquery'), self::version );
		wp_enqueue_script( 'jquery-datatables-responsive', WPB_PLUGIN_URL . '/js/dataTables.responsive.min.js', array('jquery'), self::version );
		wp_enqueue_script( 'jquery-datatables-jqueryui', WPB_PLUGIN_URL . '/js/dataTables.jqueryui.min.js', array('jquery'), self::version );
		wp_enqueue_script( 'jquery-qtip', WPB_PLUGIN_URL . '/js/jquery.qtip.min.js', array('jquery'), self::version );
		wp_enqueue_script( 'jquery-blockui' );

		wp_enqueue_script( 'app-common' );
		wp_enqueue_script( 'app-admin' );

		if ( $this->is_like_app_front_page() )
			$this->add_default_js();
		else
			$this->localize_admin_script();
		
	}
	
	/**
	 * Set vars for app-admin.js
	 */	
	function localize_admin_script() {
		if ( !empty( $this->localize_script_added ) )
			return;

		# app-common is called before app-admin. Therefore localize script should come before app-common
		wp_localize_script( 'app-common', '_app_', apply_filters( 'app_admin_js_parameters', array(
			'ajax_url'			=> admin_url('admin-ajax.php'),
			'images_url'		=> WPB_PLUGIN_URL . '/images/',
			'tabletools_url'	=> $this->tabletools_file,
			'js_date_format'	=> wpb_dateformat_PHP_to_jQueryUI( $this->safe_date_format() ),
			'user_fields'		=> $this->get_user_fields(),
			'delete_confirm'	=> 	__('Are you sure to delete the selected record(s)?','wp-base'),
			'con_error'			=> $this->get_text('connection_error'),
			'start_of_week'		=> $this->start_of_week,
			'curr_decimal'		=> wpb_setting('curr_decimal'),
			'thousands_sep'		=> wpb_thousands_separator(),
			'decimal_sep'		=> wpb_decimal_separator(),
			'iedit_nonce'		=> wp_create_nonce('inline_edit'),
			'daily_text'		=> __('Daily', 'wp-base'),
			'update_text'		=> __('Update','wp-base'),
			'iedit_extend'		=> array(),
			'updating_text'		=> $this->get_text( 'updating' ),
			'reading'			=> $this->get_text( 'reading' ),
			'saving'			=> $this->get_text( 'saving' ),
			'done'				=> $this->get_text( 'done' ),

		) ) );
			
		$this->localize_script_added = true;
	}

	/**
	 * Add scripts for settings page
	 * @since 2.0
	 */	
	function admin_scripts_settings() {
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-app-autosize');
	}
	
	/**
	 * Add css for settings page
	 * @since 2.0
	 */	
	function admin_css_settings() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'jquery-colorpicker-css', WPB_PLUGIN_URL . '/css/colorpicker.css', false, self::version);
	}

	/**
	 * Makes a text translatable by defining translation folder 
	 * @param text: text to be translated
	 * @since 2.0
	 * @return string
	 */	
	function _t( $text ) {
		return __( $text, 'wp-base' );
	}
	
	/**
	 * Returns an admin anchor link
	 * @param href: href of the anchor
	 * @param title: title of the anchor (already translated)
	 * @param text: text of the anchor (already translated)
	 * @since 2.0
	 * @return string
	 */	
	function _a( $text, $href='', $title='' ) {
		$t = $title ? ' title="'. $title.'"' : '';
		$h = $href ? admin_url($href) : 'javascript:void(0)';
		return '<a href="'.$h.'"'.$t.' target="_blank">' . $text . '</a>';
	}
	
	/**
	 * Add settings link to Plugins page
	 * @since 2.0
	 */	
	function add_action_links( $links ) {
		$wpb_links = array(
			'<a href="' . admin_url( 'admin.php?page=app_settings' ) . '">'.__('Settings','wp-base').'</a>',
			'<a href="'.WPB_DEMO_WEBSITE.'" target="_blank">'.__('Demo','wp-base').'</a>',
			'<a href="' . admin_url( 'admin.php?page=app_help' ) . '">'.__('Help','wp-base').'</a>',
		 );
		return array_merge( $links, $wpb_links );		
	}

	/**
	 * Add app status counts widget on admin Dashboard
	 * @since 2.0
	 */	
	function add_app_counts() {
		wp_add_dashboard_widget( 'app_dashboard_widget', WPB_NAME, array( $this, 'add_app_counts_' ) );
	}
	function add_app_counts_() {
	
		global $wpdb;
		
		echo '<ul>';
		$num_running = $wpdb->get_var("SELECT COUNT(ID) FROM " . $this->app_table . " WHERE status='running' " );
        $num = number_format_i18n( $num_running );
		$text = _n( 'Appointment In Progress', 'Appointments In Progress', intval( $num_running ) );
		if ( current_user_can( WPB_ADMIN_CAP ) ) {
			$text = "<a href='admin.php?page=appointments&type=running'>$num $text</a>";
		}
		echo '<li><span class="dashicons dashicons-backup" style="margin-right:5px"></span>';
		echo $text;
		echo '</li>';

		$num_active = $wpdb->get_var("SELECT COUNT(ID) FROM " . $this->app_table . " WHERE status='paid' OR status='confirmed' " );
        $num = number_format_i18n( $num_active );
        $text = _n( 'Upcoming Appointment', 'Upcoming Appointments', intval( $num_active ) );
        if ( current_user_can( WPB_ADMIN_CAP ) ) {
            $text = "<a href='admin.php?page=appointments&type=active'>$num $text</a>";
        }
		echo '<li><span class="dashicons dashicons-clock" style="margin-right:5px"></span>';
		echo $text;
        echo '</li>';
		
		$num_pending = $wpdb->get_var("SELECT COUNT(ID) FROM " . $this->app_table . " WHERE status='pending' " );
        $num = number_format_i18n( $num_pending );
		$text = _n( 'Pending Appointment', 'Pending Appointments', intval( $num_pending ) );
		if ( current_user_can( WPB_ADMIN_CAP ) ) {
			$text = "<a href='admin.php?page=appointments&type=pending'>$num $text</a>";
		}
		echo '<li><span class="dashicons dashicons-calendar-alt" style="margin-right:5px"></span>';
		echo $text;
		echo '</li>';
		echo '</ul>';
		
		echo '<p><span>';
		printf( __( 'You are using %1$s version %2$s', 'wp-base'), '<a href="https://wp-base.com" target="_blank">'. WPB_NAME.'</a>', $this->version );
		echo '</span></p>';

		echo '<form method="get" action="'.admin_url('admin.php').'">';
		echo '<p class="submit">';
		echo '<input type="submit" class="button button-primary" value="'.__('Add New Booking','wp-base').'">';
		echo '<input type="hidden" name="page" value="appointments">';
		echo '<input type="hidden" name="add_new" value="1">';
		echo '</p>';
		echo '</form>';
	}
	
	/**
	 *	Adds a column to Manage Pages
	 *	@since 2.0  
	 */
	function manage_pages_add_column( $columns ) {
		return 
			array_slice( $columns, 0, 3, true ) +
			array( "wp_base" => "WP BASE" ) +
			array_slice( $columns, 3, count( $columns ) - 1, true );
	}
	
	/**
	 *	Adds column td content to Manage Pages
	 *	@since 2.0  
	 */
	function manage_pages_column_content( $column_name, $post_id ) {
		if ( 'wp_base' == $column_name ) {
			$post = get_post( $post_id );
			if ( strpos( $post->post_content, '[app_' ) !== false )
				echo '<span class="dashicons dashicons-calendar" title="'.__('This page includes WP BASE shortcode','wp-base') .'"></span>';
		}
	}

	/**
	 * Determine if this is an WP BASE page
	 * This should be called later than admin_init:
	 * https://codex.wordpress.org/Function_Reference/get_current_screen#Usage_Restrictions
	 * @param $page: Query for a specific page: bookings, transactions, calendars, business, settings, addons, tools, help
	 * @since 2.0
	 * @return bool
	 */
	function is_app_page( $page='' ) {

		if ( !$screen_id = wpb_get_current_screen_id( ) )
			return false;

		if ( !$page || 'bookings' === $page ) {
			if ( 'toplevel_page_appointments' == $screen_id )
				return true;
			else if ( strpos( $screen_id, $this->app_name.'_page' ) !== false 
					|| strpos( $screen_id, 'users_page_your_appointments' ) !== false 
					|| strpos( $screen_id, 'profile_page_your_appointments' ) !== false )
				return true;
		}
		else if ( 'bookings' === $page ) {
			if ( 'toplevel_page_appointments' == $screen_id )
				return true;
		}
		else if ( strpos( $screen_id, $this->app_name.'_page_app_'.$page ) !== false ) {
			return true;
		}
		
		return false;
	}

	/**
	 * Determine if this is similar to a WP BASE front page
	 * It means, an admin page that requires front end css and js files (e.g. users page, calendars page)
	 * Note: This should be called later than admin_init:
	 * https://codex.wordpress.org/Function_Reference/get_current_screen#Usage_Restrictions
	 * @since 2.0
	 * @return bool
	 */
	function is_like_app_front_page() {
		$screen_id =  wpb_get_current_screen_id( );
		if ( $this->app_name.'_page_app_schedules' == $screen_id || $this->app_name.'_page_app_business' == $screen_id || 
			'users_page_your_appointments' == $screen_id || 'profile_page_your_appointments' == $screen_id )
			return true;
		else
			return false;
	}

	/**
	 * Print codes to header
	 * @since 2.0
	 */	
	function admin_head() {
		// dashicon as TinyMce button + WP BASE Column on Manage Pages
		?><style type="text/css">i.mce-i-icon {font: 400 20px/1 dashicons;padding: 0;vertical-align: top;speak: none;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;margin-left: -2px;padding-right: 2px}
		.manage-column.column-apb{width:5.5em}
		ul#wp-admin-bar-admin-wpbaseshortcodes-default li#wp-admin-bar-admin-help {border-bottom:1px solid darkgrey;}
		.app-notice{position:relative;}
		a.notice-dismiss {text-decoration:none;outline:none;-webkit-box-shadow:none;}
		.app_dropdown_pages{width:100%;}
		</style>
		<?php
		if ( !$this->is_app_page() )
			return;
		
		if ( !$this->is_app_page('calendars') || !isset( $_GET['tab'] ) || 'daily' != $_GET['tab'] ) {
			$this->wp_head();
		}
		
		if ( '' != trim( wpb_setting("additional_css_admin") ) ) {
			echo '<style type="text/css">'; 
			echo wpb_setting('additional_css_admin');
			echo '</style>';
		}
	}

	/**
	 * Add scripts to footer
	 * @since 2.0
	 */	
	function admin_footer() {
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			$("a.notice-dismiss").click(function(e){
				var $this = $(this);
				var what = $this.data("what");
				if (!what){return false;}
				if ("general" == what){
					$this.parents("div.is-dismissable").hide("slow");
					return false;
				}
				
				$.post(ajaxurl, {action:"dismiss", what:$this.data("what")}, function(response) {
					if ( response && response.error ) {
					}
					else if ( response.data && what == response.data ) {
						$this.parents("div.is-dismissable").hide("slow");
					}
				},'json');	
			});
			
		});
		</script>
		<?php
		if ( !$this->is_app_page() )
			return;
		
		$this->navigate_away_warning();
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			if (typeof jQuery.qtip != 'undefined' ){
				$(".app-page").find("[title][title!=\"\"]").each(function() {
					$(this).qtip({
						content: {
							text: $(this).attr("title").split("|").join("<br/>")
						},
						style:qtip_n_style,hide:qtip_hide,position:qtip_pos
					});
				});				
			}
			
		});
		</script>
		<?php

		// Add codes only for profile and calendars page
		if ( !$this->is_like_app_front_page() )
			return;
		
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			$(document).on( "click", ".appointments-list table td.free:not(.app_select_wh), td.busy", function(){
				var link = $(this).find(".app_new_link").val();
				window.location.href = link;
			});
		});
		</script>
		<?php	

		parent::wp_footer();
	}
	
	/**
	 * Warn user if he navigates away before saving a form
	 * Note: Custom message is no longer displayed by modern browsers. However, warning dialog is opened.
	 * @since 2.0
	 */	
	function navigate_away_warning() {
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			app_input_changed_global= false; // As a helper for tutorial
			var app_input_changed=false;
			var app_submit_clicked=false;
			$(".app_form, .app-manage").on("change","input.app-copy-wh,input:not(.app_no_save_alert), select:not(.app_no_save_alert), textarea", function() {
				app_input_changed_global=true;
				app_input_changed=true;
				app_submit_clicked=false;
			});
			$(".app_form, .app-manage").on("click","button.app-copy-wh, td.app_select_wh:not(.wh_dummy), table.app-wh th, table.app-wh td.app-weekly-hours-mins", function() {
				app_input_changed_global=true;
				app_input_changed=true;
				app_submit_clicked=false;
			});	
			$(".app_form").on("click","input:submit", function() {
				app_submit_clicked=true;
			});
			$("table.app-manage").on("click", ".save", function(){
				app_submit_clicked=true;
			});
			window.onbeforeunload = function(){
				if ( !app_submit_clicked && (app_input_changed || $(document).data('app_input_changed')) ) {
					return "<?php echo esc_js( __('[WP BASE] The changes you made will be lost if you navigate away from this page.','wp-base') )?>";
				}
			};
		});
		</script>
		<?php		
	}

	/**
	 * Check if there are more than one shortcodes for certain shortcode types
	 * @since 1.0.5
	 * @return bool
	 */		
	function has_duplicate_shortcode( $post_id ) {
		$post = get_post( $post_id );
		if ( is_object( $post) && $post && strpos( $post->post_content, '[app_' ) !== false ) {
			if ( substr_count( $post->post_content, '[app_locations' ) > 1 || substr_count( $post->post_content, '[app_services' ) > 1 
				|| substr_count( $post->post_content, '[app_workers' ) > 1 || substr_count( $post->post_content, '[app_confirmation' ) > 1 
				|| substr_count( $post->post_content, '[app_login' ) > 1 || substr_count( $post->post_content, '[app_manage' ) > 1 ) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 *	Warn admin if no services defined or duration is wrong or ...
	 */	
	function admin_notices() {
	
		if ( !current_user_can( WPB_ADMIN_CAP ) )
			return;
	
		global $wpdb, $current_user;
		$r = false;

		if ( !$this->get_db_version() || version_compare( $this->get_db_version(), WPB_LATEST_DB_VERSION, '<' ) ) {
			_wpb_reactivate_required();
			$r = true;
		}
		
		// Warn if Cancel link is used, but cancellation is not enabled
		$msgs = wpb_setting('confirmation_message').' '. wpb_setting('reminder_message').' '. wpb_setting('pending_message');
		
		if ( 'yes' != wpb_setting('allow_cancel') && !$this->is_dismissed('cancel') && false !== strpos( do_shortcode($msgs), 'CANCEL') ) {
			echo '<div class="app-notice notice is-dismissable"><p>' .
				__('<b>[WP BASE]</b> You have used CANCEL placeholder in at least one of the email message templates, but you did not enable cancellation. With these settings, cancel link will not be displayed in the emails. Please correct "Allow client cancel own appointments" setting on Global Settings tab or remove CANCEL placeholder from the email message template.', 'wp-base') .
			'</p><a class="notice-dismiss" data-what="cancel" title="'.__('Dismiss this notice for this session', 'wp-base').'" href="javascript:void(0)"></a>'.
			'</div>';
			$r = true;
		}				
		
		// Warn if payment required, but no payment method is selected
		global $app_gateway_active_plugins;
		if ( !$this->is_dismissed('payment') && 'yes' == wpb_setting('payment_required') && empty($app_gateway_active_plugins) 
			&& !(BASE('MarketPress') && 'yes' != wpb_setting('use_mp')) && !(BASE('WooCommerce') && 'yes' != wpb_setting('wc_enabled'))  ) {
			echo '<div class="app-notice notice is-dismissable"><p>' .
				__('<b>[WP BASE]</b> You have selected "Payment Required", but no payment method is active. With these settings, it may not be possible to complete a booking on the front end.', 'wp-base') .
			'</p><a class="notice-dismiss" data-what="payment" title="'.__('Dismiss this notice for this session', 'wp-base').'" href="javascript:void(0)"></a>'.
			'</div>';
			$r = true;
		}

		// Check for duplicate shortcodes for a visited page
		if ( !empty( $_GET['post'] ) && !$this->is_dismissed('duplicate') && $this->has_duplicate_shortcode( $_GET['post'] ) ) {
			echo '<div class="app-notice notice is-dismissable"><p>' .
			__('<b>[WP BASE]</b> More than one instance of services, service providers, confirmation or login shortcodes on the same page may cause problems.</p>', 'wp-base' ).
			'</p><a class="notice-dismiss" data-what="duplicate" title="'.__('Dismiss this notice for this session', 'wp-base').'" href="javascript:void(0)"></a>'.
			'</div>';
		}

		// Make the rest of the checks only when a service OR time base have been changed 
		if ( !get_user_meta( $current_user->ID, 'app_service_check_needed' ) )
			return;
		
		$results = $this->get_services();
		if ( !$results ) {
			echo '<div class="error"><p>' .
				__('<b>[WP BASE]</b> You must define at least once service.', 'wp-base') .
			'</p></div>';
			$r = true;
		}
		else { 
			// Check if any location is unassigned
			if ( $this->get_nof_locations() ) {

				$locations = $this->get_locations();
				foreach ( $locations as $location ) {
					if ( !$this->is_dismissed('no_location') && !$this->get_services_by_location( $location->ID ) ) {
						echo '<div class="app-notice notice is-dismissable"><p>' .
						__('<b>[WP BASE]</b> One of your locations does not have a service assigned. Delete locations you are not using.', 'wp-base') .
						'</p><a class="notice-dismiss" data-what="location" title="'.__('Dismiss this notice for this session', 'wp-base').'" href="javascript:void(0)"></a>'.
						'</div>';
						$r = true;
						break;
					}
				}
			}
			// Check services
			foreach ( $results as $result ) {
				if ( $result->duration < $this->get_min_time() ) {
					echo '<div class="error"><p>' .
						__('<b>[WP BASE]</b> One of your services has a duration smaller than time base. Please visit Services tab and after making your corrections save new settings or try changing Time Base setting as "Auto".', 'wp-base') .
					'</p></div>';
					$r = true;
					break;
				}
				if ( $result->duration % $this->get_min_time() != 0 || $result->break_time % $this->get_min_time() != 0 || $result->padding % $this->get_min_time() != 0 ) {
					echo '<div class="error"><p>' .
						__('<b>[WP BASE]</b> One of your service duration or padding is not divisible by the time base. Please visit Services tab and after making your corrections save new settings or try changing Time Base setting as "Auto".', 'wp-base') .
					'</p></div>';
					$r = true;
					break;
				}
				if ( !$this->is_package($result->ID) && ( $result->padding + $result->duration + $result->break_time ) > 1440 ) {
					echo '<div class="error"><p>' .
						__('<b>[WP BASE]</b> One of your services has a duration (plus paddings) greater than 24 hours. WP BASE does not support services exceeding 1440 minutes (24 hours). ', 'wp-base') .
					'</p></div>';
					$r = true;
					break;
				}
				
				if ( !$this->is_dismissed('no_sp') && !$this->get_capacity( $result->ID ) && !$this->is_package( $result->ID ) ) {
					echo '<div class="app-notice notice is-dismissable"><p>' .
						sprintf( __('<b>[WP BASE]</b> One of your services does not have a service provider assigned (and capacity is not set). Delete %s you are not using.', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_business&tab=services').'" title="'.__('Click to access Services settings','wp-base').'">'. __('services','wp-base').'</a>') .
						'</p><a class="notice-dismiss" data-what="no_sp" title="'.__('Dismiss this notice for this session', 'wp-base').'" href="javascript:void(0)"></a>'.
					'</p></div>';
					$r = true;
					break;
				}
				if ( BASE('Categories') && BASE('Categories')->get_nof_categories() && !$result->categories && !$this->is_dismissed('no_category') ) {
					echo '<div class="app-notice notice is-dismissable"><p>' .
						sprintf( __('<b>[WP BASE]</b> One of your services does not have a category assigned. Such services may not be selectable on the front end. %s', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_business&tab=services#tabs-1').'" title="'.__('Click to access Services settings','wp-base').'">'. __('Click to fix.','wp-base').'</a>') .
						'</p><a class="notice-dismiss" data-what="no_category" title="'.__('Dismiss this notice for this session', 'wp-base').'" href="javascript:void(0)"></a>'.
					'</p></div>';
					$r = true;
					break;
				}
				if ( !$r )
					delete_user_meta( $current_user->ID, 'app_service_check_needed' );
			}
		}
		
		return $r;
	}

	/**
	 *	Dismiss warning messages for the current user for the session
	 *	@since 1.1.7
	 */
	function dismiss() {
		global $current_user;
		$what = isset( $_POST['what'] ) ? $_POST['what'] : false;
		if ( !$what )
			return;
		
		if ( update_user_meta( $current_user->ID, 'app_dismiss_'.$what, $this->_time ) ) {
			wp_send_json_success( $what );
		}
	}
	
	/**
	 *	Check if a message is dismissed and not expired
	 *	@since 3.0
	 */
	function is_dismissed( $what ) {
		global $current_user;
		if ( !$when = get_user_meta( $current_user->ID, 'app_dismiss_'.$what, true ) )
			return false;
		
		if ( $this->_time - (int)$when > 60*60*24*7 ) {
			#expired
			delete_user_meta( $current_user->ID, 'app_dismiss_'.$what );
			return false;
		}
		
		return true;
	}

	/**
	 * Prints "A new page created" message on top of Admin page
	 * @since 2.0 	 
	 */
	function page_created( ) {
		echo '<div class="updated app-dismiss"><p><b>[WP BASE]</b> '. sprintf( __('A new make an Appointment page created. %s','wp-base'), '<a href="'. get_permalink( $this->created_page_id ). '">'.__('View Page','wp-base') .'</a>'  ).
		'</p><a class="notice-dismiss" data-what="general" title="'.__('Dismiss this notice', 'wp-base').'" href="javascript:void(0)"></a></div>';
	}

	/**
	 * Prints "A new page created" message on top of Admin page 
	 * @since 2.0 	 
	 */
	function page_created_list( ) {
		echo '<div class="updated app-dismiss"><p><b>[WP BASE]</b> '. sprintf( __('A new List of Bookings page created. %s','wp-base'), '<a href="'. get_permalink( $this->created_list_page_id ). '">'.__('View Page','wp-base') .'</a>'  ).
		'</p><a class="notice-dismiss" data-what="general" title="'.__('Dismiss this notice', 'wp-base').'" href="javascript:void(0)"></a></div>';
	}

	/**
	 * Prints "Demo" message on top of Admin page 
	 * @since 2.0
	 */
	function demo( ) {
		echo '<div class="error"><p><b>[WP BASE]</b> '. __('In Demo mode settings cannot be changed.','wp-base').'</p></div>';
	}

	/**
	 * Prints "Restored" message on top of Admin page 
	 * @since 2.0
	 */
	function restored( ) {
		echo '<div class="updated app-dismiss"><p><b>[WP BASE]</b> '. __('Settings have been set to their default values.','wp-base').
		'</p><a class="notice-dismiss" data-what="general" title="'.__('Dismiss this notice', 'wp-base').'" href="javascript:void(0)"></a></div>';
	}

	/**
	 * Prints DB reset message on top of Admin page 
	 * @since 2.0
	 */
	function reset_db( ) {
		echo '<div class="updated app-dismiss"><p><b>[WP BASE]</b> '. __('Database tables have been reset.','wp-base').
		'</p><a class="notice-dismiss" data-what="general" title="'.__('Dismiss this notice', 'wp-base').'" href="javascript:void(0)"></a></div>';
	}

	/**
	 * Prints "saved" message on top of Admin page 
	 */
	function saved( ) {
		echo '<div class="updated app-nag-saved app-notice is-dismissable"><p><b>[WP BASE]</b> '. __('Settings saved.','wp-base').
		'</p><a class="notice-dismiss" data-what="general" title="'.__('Dismiss this notice', 'wp-base').'" href="javascript:void(0)"></a></div>';
	}
	
	/**
	 * Prints "deleted" message on top of Admin page 
	 */
	function deleted( ) {
		echo '<div class="updated app-nag-saved app-notice is-dismissable"><p><b>[WP BASE]</b> '. __('Selected record(s) deleted.','wp-base').
		'</p><a class="notice-dismiss" data-what="general" title="'.__('Dismiss this notice', 'wp-base').'" href="javascript:void(0)"></a></div>';
	}

	/**
	 * Prints "updated" message on top of Admin page 
	 */
	function updated( ) {
		echo '<div class="updated app-nag-saved app-notice is-dismissable"><p><b>[WP BASE]</b> '. __('Selected record(s) updated.','wp-base').
		'</p><a class="notice-dismiss" data-what="general" title="'.__('Dismiss this notice', 'wp-base').'" href="javascript:void(0)"></a></div>';
	}

	/**
	 * Prints anauth message on top of Admin page 
	 */
	function unauthorised( ) {
		echo '<div class="error app-notice is-dismissable"><p><b>[WP BASE]</b> '. __('You are not authorised to do this.','wp-base').
		'</p><a class="notice-dismiss" data-what="general" title="'.__('Dismiss this notice', 'wp-base').'" href="javascript:void(0)"></a></div>';
	}
	
	/**
	 * Prints upload error message on top of Admin page 
	 * @since 2.0
	 */
	function reset_error( ) {
		echo '<div class="error app-notice is-dismissable"><p><b>[WP BASE]</b> '. __('An error occurred during resetting. Check log file to see if details of the error has been recorded.','wp-base').
		'</p><a class="notice-dismiss" data-what="general" title="'.__('Dismiss this notice', 'wp-base').'" href="javascript:void(0)"></a></div>';
	}

	/**
	 * Display Lite version notice
	 * @since 2.0
	 */
	function lite() {
	?>
		<div id="poststuff" class="metabox-holder">
		<span class="description app_bottom"><?php _e('This option is only available in the Developer version.', 'wp-base') ?></span>
		</div>
	<?php
	}

	/**
	 *	Admin pages init stuff, save settings
	 *
	 */
	function admin_init() {
	
		global $current_user;
	
		// Save and reset sort/filter preferences - Currently inactive
		$pref = get_user_meta( $current_user->ID, 'app_admin_pref', true );
		$pref = is_array( $pref ) ? $pref : array();
		
		foreach ( array( 'location_id', 'service_id', 'provider_id', 'order_by', 'balance', 'gateway', 'm' ) as $i ) {
			if( isset( $_GET['app_'.$i] ) )
				$pref[$i] = $_GET['app_'.$i];
		}
		if ( isset( $_GET['app_filter_reset'] ) )
			delete_user_meta( $current_user->ID, 'app_admin_pref' );
		else if ( !empty( $pref ) && get_user_meta( $current_user->ID, 'app_save_admin_pref', true ) ) // Save filter prefs only if selected so (not implemented yet)
			update_user_meta( $current_user->ID, 'app_admin_pref', $pref );
		
		do_action( 'app_menu_before_all' ); // e.g. Bookings page
		do_action( 'app_submenu_before_business' ); // e.g. Monetary Settings page
		wpb_add_submenu_page('appointments', __('WPB Business Settings','wp-base'), __('Business Settings','wp-base'), array(WPB_ADMIN_CAP,'manage_locations','manage_services','manage_working_hours','manage_extras'), "app_business", array($this,'business'));
		do_action( 'app_submenu_before_tools' ); // e.g. Global Settings page
		wpb_add_submenu_page('appointments', __('WPB Tools','wp-base'), __('Tools','wp-base'), array(WPB_ADMIN_CAP,'manage_tools'), "app_tools", array($this,'tools'));
		do_action( 'app_submenu_after_tools' ); // e.g. Help page


		if ( 'yes' == wpb_setting('allow_worker_edit') && $this->is_worker( $current_user->ID ) ) {
			do_action( 'app_menu_for_worker' );
		}
		
		if ( isset( $_REQUEST["action_app"] ) && wpb_is_demo() ) {
			add_action( 'admin_notices', array( $this, 'demo' ) );
			return;
		}

		// Use this action for addons, depending classes, etc to save settings with $_GET
		do_action( 'app_save_settings_with_get' );
		
		// If there is no save request, nothing to do
		if ( !isset( $_POST["action_app"] ) ) {
			return;
		}
		
		$this->save_settings();
		// Use this action for addons, depending classes, etc to save settings with $_POST
		do_action( 'app_save_settings' );
	}
	
	/**
	 *	Admin save settings
	 */
	function save_settings() {
		
		if ( isset( $_POST['app_nonce'] ) && !wp_verify_nonce($_POST['app_nonce'],'update_app_settings') ) {
			wpb_notice( 'unauthorised', 'error' );
			return;
		}
		// TODO: BP may use here
		
		$flush_cache = false;
		$result = $updated = $inserted = false;

		# Change status bulk
		if ( $this->change_status_bulk() )
			$flush_cache = true;

		# Delete removed appointments
		if ( isset( $_POST['app_delete_nonce'] ) && wp_verify_nonce($_POST['app_delete_nonce'],'delete_or_reset') &&
				isset( $_POST['delete_removed'] ) && 'delete_removed' == $_POST['delete_removed']		) {
			
			wpb_admin_access_check( 'delete_bookings' );
			
			if ( $this->delete_removed() )
				$flush_cache = true;
		}
		
		# Reset settings
		if ( isset( $_POST['app_delete_nonce'] ) && wp_verify_nonce($_POST['app_delete_nonce'],'delete_or_reset') &&
				isset( $_POST['restore_defaults'] ) && 'restore_defaults' == $_POST['restore_defaults'] ) {
					
			wpb_admin_access_check( 'manage_tools' );		
					
			if ( defined( 'WPB_DISABLE_RESET' ) && WPB_DISABLE_RESET ) {
				wp_die( $this->get_text('unauthorised') );
			}
			else {
				if ( isset( $_POST['reset_templates_check'] ) && !empty( $_POST['reset_templates'] ) ) {
					delete_option( 'wp_base_options' );
					update_option( 'wp_base_options', WpBConstant::defaults() );
				}
				else {
					$old_templates = array_intersect_key( wpb_setting(), WpBConstant::defaults( 'only_templates' ) );
					update_option( 'wp_base_options', array_merge( WpBConstant::defaults( ), $old_templates ) );
				}
				
				wpb_flush_cache();
				add_action( 'admin_notices', array( $this, 'restored' ) );
				# Check and regenerate admin toolbar
				wpb_rebuild_menu();
				$this->log( sprintf( __('Default settings are restored by user %s','wp-base'), BASE('User')->get_name() ) );
			}
			return;
		}
		
		// Reset database
		if ( isset( $_POST['app_delete_nonce'] ) && wp_verify_nonce($_POST['app_delete_nonce'],'delete_or_reset') &&
			isset( $_POST['reset_db'] ) && 'reset_db' == $_POST['reset_db'] ) {
				
			wpb_admin_access_check( 'manage_tools' );	
				
			if ( defined( 'WPB_DISABLE_RESET' ) && WPB_DISABLE_RESET ) {
				wp_die( $this->get_text('unauthorised') );
			}
			else {
				$tables = array(
					$this->app_table,
					$this->meta_table,					
					$this->transaction_table,
					$this->locations_table,
					$this->services_table,
					$this->workers_table,
					$this->wh_w_table,
					$this->wh_s_table,
					$this->wh_a_table,
				);
			
				$tables = apply_filters( 'app_reset_tables', $tables );
				
				$error = '';
				
				foreach ( $tables as $table ) {
					// As a safety measure, allow only truncating of tables having {prefix}base_ in the name
					if ( strpos( $table, $this->db->prefix .'base_' ) !== false ) {
						$result = $this->db->query( 'TRUNCATE ' . $table );
						if ( !$result )
							$error .= sprintf( __('Error truncating table: %s <br />', 'wp-base'), $table );
					}
				}
				
				if ( !$error ) {
					$this->log( sprintf( __('Database tables truncated by user %s','wp-base'), BASE('User')->get_name() ) );
					add_action( 'admin_notices', array( $this, 'reset_db' ) );
				}
				else {
					$error = rtrim( $error, "<br />" );
					$this->log( $error );
					add_action( 'admin_notices', array( $this, 'reset_error' ) );
				}
				wpb_flush_cache();
			}
			return;
		}
	
		if ( $flush_cache )
			wpb_flush_cache();
	}
	
	/**
	 * Delete removed app records 
	 * $param to_delete: If array given deletes those apps provide that status match ('Cart')
	 * Since 2.0 - previously it was integrated in admin_init method
	 */		
	function delete_removed( $to_delete=false ) {
		if ( false === $to_delete )
			$to_delete = isset( $_POST["app"] ) && is_array( $_POST["app"] ) ? $_POST["app"] : array();

		if ( empty( $to_delete ) )
			return false;
		
		$q = '';
		foreach ( (array)$to_delete as $app_id ) {
			$q .= " ID=". (int)$app_id. " OR";
		}
		$q = rtrim( $q, " OR" );
		$q = apply_filters( 'app_delete_removed_query', $q );
		$stat = isset($_POST["delete_removed"]) && 'delete_removed' == $_POST["delete_removed"] ? "removed" : "cart";
		do_action( 'app_delete_pre', 'removed', $to_delete );
		
		$result = $this->db->query( $this->db->prepare("DELETE FROM " .$this->app_table. " WHERE (".$q.") AND status=%s", $stat) );
		
		if ( $result ) {
			
			$this->db->query( $this->db->prepare("DELETE FROM " .$this->meta_table. " WHERE (%s) AND meta_type='app'", str_replace( 'ID','object_id', $q )) );
			
			if ( 'cart' != $stat ) {	
				$userdata = BASE('User')->_get_userdata( get_current_user_id() );
				$user_login = isset( $userdata->user_login ) ? $userdata->user_login : esc_url_raw( $_SERVER['REMOTE_ADDR'] );
				add_action( 'admin_notices', array ( $this, 'deleted' ) );
				$this->log( sprintf( __('Appointment(s) with id(s): %1$s deleted by user %2$s', 'wp-base' ),  implode( ', ', $to_delete ), $user_login ) );
			}
			do_action( 'app_deleted', 'removed', $to_delete );
			$this->adjust_auto_increment();
			return true;
		}
	}

	/**
	 * Bulk status change
	 * Since 2.0 - previously it was integrated in admin_init method
	 */		
	function change_status_bulk() {
		global $current_user;
		
		if ( isset( $_POST["app_status_change"] ) && $_POST["app_new_status"] && isset( $_POST["app"] ) && is_array( $_POST["app"] ) ) {
			
			$q = '';
			foreach ( $_POST["app"] as $app_id ) {
				$q .= " ID=". esc_sql($app_id). " OR";
			}
			
			$last_app = $this->get_app( $app_id ); // Saving the latest status will be enough
			$old_status = $last_app->status;
			$q = rtrim( $q, " OR" );
			
			// Make a status re-check here - It should be in status map
			$new_status = $_POST["app_new_status"];
			if ( $new_status != $old_status && array_key_exists( $new_status, $this->get_statuses() ) ) {
				$result = $this->db->query( "UPDATE " . $this->app_table . " SET status='".$new_status."' WHERE " . $q . " " );
				if ( $result ) {

					// Email on bulk change - Confirmation
					if ( 'yes' == wpb_setting('send_confirmation_bulk') 
						&& ( ( 'confirmed' == $new_status  && 'paid' != $old_status ) || ( 'paid' == $new_status && 'confirmed' != $old_status ) ) ) {
						foreach ( $_POST["app"] as $app_id ) { 
							$this->send_email( $app_id );
						}
					}
					
					// Email on bulk change - Pending
					if ( 'yes' == wpb_setting('send_pending_bulk') && 'pending' == $new_status ) {
						foreach ( $_POST["app"] as $app_id ) { 
							$this->send_email( $app_id, 'pending' );
						}
					}

					// Email on bulk change - Cancellation
					if ( 'yes' == wpb_setting('send_cancellation_bulk') && 'removed' == $new_status ) {
						foreach ( $_POST["app"] as $app_id ) { 
							$this->send_email( $app_id, 'cancellation' ); 
						}
					}
					
					wpb_notice( 'updated' );
					
					do_action( 'app_bulk_status_change', $new_status, $_POST["app"] );
					$userdata = BASE('User')->_get_userdata( $current_user->ID );
					$this->log( sprintf( __('Status of Appointment(s) with id(s):%1$s changed to %2$s by user:%3$s', 'wp-base' ),  implode( ', ', $_POST["app"] ), $new_status, $userdata->user_login ) );
					
					return true;
				}
			}
		}
	}

	/**
	 * Admin business definitions HTML code 
	 * @since 2.0
	 */
	function business() {

	?>
		<div class="wrap app-page">
		<h2 class="app-dashicons-before dashicons-store"><?php echo __('Business Settings','wp-base'); ?></h2>
		<h3 class="nav-tab-wrapper">
			<?php
			$tab = !empty( $_GET['tab'] ) ? $_GET['tab'] : 'services';
			
			$tabhtml = array();
			
			$tabs = apply_filters( 'appointments_business_tabs', array() );

			$class = ( 'services' == $tab ) ? ' nav-tab-active' : '';

			foreach ( $tabs as $stub => $title ) {
				$class = ( $stub == $tab ) ? ' nav-tab-active' : '';
				$tabhtml[] = '	<a href="' . admin_url( 'admin.php?page=app_business&amp;tab=' . $stub ) . '" class="nav-tab'.$class.'" id="app_tab_'.$stub.'">'.$title.'</a>';
			}

			echo implode( "\n", $tabhtml );
			?>
		</h3>
		<div class="clear"></div>
		
		<?php switch( $tab ) {

		# Specialized tab - Do not use existing tab names
		case $tab:				do_action( 'app_business_'.$tab.'_tab' ); break;
		} 
		?>
		</div><!-- Wrap -->
	<?php
	}
	
	/**
	 *	Admin tools page HTML
	 * @since 2.0
	 */		
	function tools() {

		wpb_admin_access_check( 'manage_tools' );
		
	?>
		<div class="wrap">
		<h2 class="app-dashicons-before dashicons-admin-tools"><?php echo __('Tools','wp-base'); ?></h2>
		<h3 class="nav-tab-wrapper">
			<?php
			$tab = ( !empty($_GET['tab']) ) ? $_GET['tab'] : 'log';
			
			$tabs = array(
				'log'				=> __('Logs', 'wp-base'),
				'reset'				=> __('Reset', 'wp-base'),
			);
			
			$tabhtml = array();

			
			$tabs = apply_filters( 'appointments_tools_tabs', $tabs );

			$class = ( 'log' == $tab ) ? ' nav-tab-active' : '';

			foreach ( $tabs as $stub => $title ) {
				$class = ( $stub == $tab ) ? ' nav-tab-active' : '';
				$tabhtml[] = '	<a href="' . admin_url( 'admin.php?page=app_tools&amp;tab=' . $stub ) . '" class="nav-tab'.$class.'" id="app_tab_'.$stub.'">'.$title.'</a>';
			}

			echo implode( "\n", $tabhtml );
			?>
		</h3>
		<div class="clear"></div>
		<div id="poststuff" class="metabox-holder">
		<?php switch( $tab ) {
	/*******************************
	* Log tab
	********************************
	*/
		case 'log':	?>
		<div class="postbox">
			<div class="inside" style="word-wrap:break-word;" id="app_log">
			<?php
				if ( wp_is_writable( $this->uploads_dir ) ) {
					if ( file_exists( $this->log_file ) ) 
						echo nl2br( file_get_contents( $this->log_file ) );
					else
						echo __( 'There are no log records yet.', 'wp-base' );
				}
				else
					echo __( 'Uploads directory is not writable.', 'wp-base' );
				?>
			</div>
		</div>
			<table class="form-table">
				<tr>
					<th scope="row" >
					<input type="button" id="log_clear_button" class="button-primary" value="<?php _e('Clear Log File') ?>" title="<?php _e('Clicking this button deletes logs saved on the server') ?>" />
					</th>
				</tr>
			</table>
		<?php break;
	/*******************************
	* Reset tab
	********************************
	*/
		case 'reset' :
			wpb_admin_access_check( 'manage_tools' );
			
			$wp_nonce = wp_nonce_field( 'update_app_settings', 'app_nonce', true, false );
			wpb_infobox( sprintf( __('Here you can return settings and/or WP BASE related database tables to the point of first installation of the plugin, e.g. after you finished testing. To completely disable this function, add %s to wp-config.php.', 'wp-base'), '<code>define("WPB_DISABLE_RESET", true);</code>' ) );
		?>
		<div class="postbox">
			<div class="inside">
				<?php 
				if ( defined( 'WPB_DISABLE_RESET' ) && WPB_DISABLE_RESET )
					_e( 'Disabled by WPB_DISABLE_RESET', 'wp-base' );
				else {
				?>
					<table>
						<tr class="app_impex">
							<td class="app_b"><?php _e('Global Settings','wp-base') ?></td>
							<td class="app_c">			
							<form id="restore_defaults_form" method="post">
								<?php echo $wp_nonce; ?>
								<input type="hidden" name="restore_defaults" value="restore_defaults" />
								<input type="hidden" name="action_app" value="restore_defaults" />
								<?php wp_nonce_field( 'delete_or_reset', 'app_delete_nonce' ); ?>
								<input type="submit" id="restore_defaults_button" class="button-secondary" value="<?php _e('Restore to default settings') ?>" title="<?php _e('Clicking this button will set all settings to their default values') ?>" />
								<br />
								<div class="app_chkbx">
									<input type="hidden" name="reset_templates_check" value="yes" />
									<input type="checkbox" name="reset_templates" value="yes" />&nbsp;
									<span class="description app_bottom"><?php  _e('Also reset email and SMS templates', 'wp-base') ?></span>
								</div>						
							</form>
							</td>
						</tr>
						<tr class="app_impex">
							<td class="app_b"><?php _e('Database Tables (Bookings, Transactions and Business Settings)','wp-base') ?></td>
							<td class="app_c">			
							<form id="reset_db_form" method="post">
								<?php echo $wp_nonce; ?>
								<input type="hidden" name="reset_db" value="reset_db" />
								<input type="hidden" name="action_app" value="reset_db" />
								<?php wp_nonce_field( 'delete_or_reset', 'app_delete_nonce' ); ?>
								<input type="submit" id="reset_db_button" class="button-secondary" value="<?php _e('Reset Database Tables') ?>" title="<?php _e('Clicking this button will clear all WP BASE related database tables.') ?>" />
							</form>
							</td>
						</tr>						
					</table>
				<?php } ?>
				
			</div>
		</div>
		<?php break;

		
		case $tab:	do_action( 'app_tools_'.$tab.'_tab' ); 
					break;
		
		} // End of switch
		?>
		</div>
		</div>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			
			$('.app-codemirror').each(function(index, elements) {
				CodeMirror.fromTextArea(elements, {
						lineNumbers: true,
						firstLineNumber: 1,
						matchBrackets: true,
						indentUnit: 4,
						mode: 'text/x-php',
						styleActiveLine: true,
						readOnly: $(this).attr("readonly") ? true: false
				});
			});	
			
			$('#restore_defaults_button').click(function(e) {
				e.preventDefault();
				if ( !confirm('<?php echo esc_js( __("This action will restore all WP BASE settings to the defaults. Database records (bookings, transactions, locations, services, service providers, working hours) will not be changed. Are you sure to do this?",'wp-base') ) ?>') ) {return false;}
				else{
					$('#restore_defaults_form').submit();
				}
			});
			
			$('#reset_db_button').click(function(e) {
				e.preventDefault();
				if ( !confirm('<?php echo esc_js( __("WARNING!! This action will clear all existing database records (bookings, transactions, locations, services, service providers, working hours). Are you sure to do this?",'wp-base') ) ?>') ) {return false;}
				else{
					if ( !confirm('<?php echo esc_js( __("Are you REALLY SURE TO DELETE the database records?",'wp-base') ) ?>') ) {return false;}
					else{ $('#reset_db_form').submit(); }
				}
			});

			$('#log_clear_button').click(function() {
				if ( !confirm('<?php echo esc_js( __("Are you sure to clear the log file?",'wp-base') ) ?>') ) {return false;}
				else{
					$('.add-new-waiting').show();
					var data = {action: 'delete_log', _ajax_nonce: '<?php echo wp_create_nonce('delete_log') ?>'};
					$.post(ajaxurl, data, function(response) {
						$('.add-new-waiting').hide();
						if ( response && response.error ) {
							alert(response.error);
						}
						else{
							$("#app_log").html('<?php echo esc_js( __("Log file cleared...",'wp-base') ) ?>');
						}
					},'json');							
				}
			});
		});
		</script>
		<?php

	}
	
	/**
	 *	Delete log file
	 */		
	function delete_log(){
		if ( !check_ajax_referer( 'delete_log', false, false ) )
			die( json_encode( array( 'error'=> $this->get_text('unauthorised') ) ) );

		if ( wpb_is_demo() ) {
			die( json_encode( array( 'error' => __( 'In DEMO mode log file cannot be deleted', 'wp-base' ) ) ) );
		}
		
		if( file_exists( $this->log_file ) )
			unlink( $this->log_file );
		
		sleep( 1 );
		
		if ( file_exists( $this->log_file ) )
			die( json_encode( array( 'error' => __( 'Log file could not be deleted', 'wp-base' ) ) ) );
		
		die( json_encode( array( 'success' => 1 ) ) );
	}
	

}

	BASE('Admin')->add_hooks_admin();
	$GLOBALS['appointments'] = BASE('Admin');	// For backwards compatibility

}
else {
	add_action( 'admin_notices', '_wpb_plugin_conflict_own' );
}

