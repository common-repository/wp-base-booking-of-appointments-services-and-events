<?php
/**
 * WPB Front
 *
 * Includes methods and helpers for generating HTML for front end usually out of shortcodes
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBFront' ) && class_exists( 'WpBCore' ) ) {

class WpBFront extends WpBCore {

	function __construct(){
		parent::__construct();
	}

	/**
     * Add action and filter hooks
     */
	function add_hooks_front() {
		include_once( WPB_PLUGIN_DIR . '/includes/class.normalize.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/class.menu.php' );
		include_once( WPB_PLUGIN_DIR . '/includes/countdown.php' );								// Countdown shortcode
		include_once( WPB_PLUGIN_DIR . '/includes/front-cancel.php' );							// Handle cancel requests
		
		add_action( 'init', array( $this, 'handle_confirm' ), 3 ); 								// Check confirmation of an appointment by email link

		add_filter( 'the_posts', array($this, 'maybe_load_assets') );							// Determine if we use shortcodes on the page

		add_shortcode( 'app_book', array($this,'book') );										// New in V2.0, compact book shortcode	
		add_shortcode( 'app_list', array($this,'listing') );									// New in V2.0, replaces all_appointments and my_appointments	
		add_shortcode( 'app_is_mobile', array( $this, 'is_mobile_shortcode') );					// Check if user connected with a mobile
		add_shortcode( 'app_is_not_mobile', array( $this, 'is_not_mobile_shortcode') );			// Check if user connected with a mobile
		add_shortcode( 'app_no_html', array( $this, 'no_html') );								// Cleans everything inside, while loading js and css files
		add_shortcode( 'app_theme', array($this,'theme_selector') );							// Selects a theme on the front end
		
		$this->add_hooks();
	}

	/**
	 * Load style and script only when they are necessary
	 * http://beerpla.net/2010/01/13/wordpress-plugin-development-how-to-include-css-and-javascript-conditionally-and-only-when-needed-by-the-posts/
	 *
	 */		
	function maybe_load_assets( $posts ) {
		if ( empty($posts) || is_admin() ) 
			return $posts;
	
		$sc_found = false;
		
		foreach ( $posts as $post ) {
			if ( !is_object( $post ) )
				break;
			$post_content = $this->post_content( $post->post_content, $post );
			
			$post_content = apply_filters( 'app_the_posts_content', $post_content, $post );
		
			if ( has_shortcode( $post_content, 'app_confirmation') || has_shortcode( $post_content, 'app_book') ) {
				$this->load_assets( );
				do_action( 'app_shortcode_found', 'confirmation', $post );
				break;
			}
			if ( strpos( $post_content, '[app_' ) !== false ) {
				$sc_found = true;
				# We do not break here yet, because we may still find conf
			}
		}
		
		if ( $sc_found ) {
			$this->load_assets( );
			do_action( 'app_shortcode_found', '', $post );
		}
 
		return $posts;
	}
	
	/**
	 * Function to load all necessary scripts and styles
	 * Can be called externally, e.g. when forced from a page template
	 */	
	function load_assets( ) {
		# Prevent duplicate calling
		if ( !empty( $this->load_assets_called ) )
			return;

		$this->plugin_url = $this->get_plugin_url();							// Update if plugin will be protocol agnostic or not
		
		add_action( 'wp_enqueue_scripts', array( $this, 'add_default_js' ), 100 );
		add_action( 'wp_head', array( $this, 'wp_head' ) );
		add_action( 'wp_print_styles', array( $this, 'wp_head' ) );				// In case theme is missing wp_head action
		add_action( 'wp_print_scripts', array( $this, 'wp_head' ) );
		add_action( 'wp_footer', array( $this, 'wp_footer' ), 80 );				// Publish plugin specific scripts in the footer 	
		
		# Add a div for mobile
		if ( wpb_is_mobile() )
			add_filter( 'the_content', array( $this, 'content_end' ), 10000, 1 );

		$locale = strtolower( $this->get_locale() );
		$locale_short = current(explode('-',$locale ));
		
		wp_register_script( 'app-common', $this->plugin_url . '/js/app-common.min.js', array('jquery-ui-button'), self::version, true );

		if ( WpBDebug::is_debug() ) {
			if ( wpb_is_mobile() ) {
				wp_enqueue_script( 'jquery-mobile', 'https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js', array('jquery'), self::version );
				wp_enqueue_script( 'swipe', $this->plugin_url . '/js/swipe.js', array(), self::version );
			}
			else {
				wp_enqueue_script( 'jquery-ui-button' );
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_script( 'jquery-ui-datepicker' );

				global $wp_version;
				if ( version_compare( $wp_version, '4.6', '<' ) ) {
					foreach( array( $locale, $locale_short ) as $lcl ) {
						if ( file_exists( $this->plugin_dir . '/js/locale/datepicker-'.$lcl.'.js' ) ) {
							wp_enqueue_script( 'jquery-ui-datepicker-locale', $this->plugin_url . '/js/locale/datepicker-'.$lcl.'.js', array('jquery-ui-datepicker'), self::version );
							break;
						}
					}
				}

				wp_enqueue_script( 'jquery-qtip', $this->plugin_url . '/js/jquery.qtip.min.js', array('jquery'), self::version );
				wp_enqueue_script( 'jquery-multiselect', $this->plugin_url . '/js/jquery.multiselect.js', array('jquery-ui-core'), self::version, true );
				wp_enqueue_script( 'jquery-multiselect-filter', $this->plugin_url . '/js/jquery.multiselect.filter.js', array('jquery-ui-core'), self::version, true );
			}
			
			$this->enqueue_effects();
			wp_enqueue_script( 'jquery-datatables', $this->plugin_url . '/js/jquery.dataTables.min.js', array('jquery-ui-core'), self::version );
			wp_enqueue_script( 'jquery-quickfit', $this->plugin_url . '/js/jquery.quickfit.js', array('jquery'), self::version );
			wp_enqueue_script( 'isotope', $this->plugin_url . '/js/isotope.pkgd.min.js', array('jquery'), self::version );
			wp_enqueue_script( 'jquery-datatables-moment', $this->plugin_url . '/js/moment.min.js', array('jquery'), self::version );
			wp_enqueue_script( 'jquery-datatables-tabletools', $this->plugin_url . '/js/dataTables.tableTools.min.js', array('jquery'), self::version );
			wp_enqueue_script( 'jquery-datatables-responsive', $this->plugin_url . '/js/dataTables.responsive.min.js', array('jquery'), self::version );
			wp_enqueue_script( 'jquery-datatables-jqueryui', $this->plugin_url . '/js/dataTables.jqueryui.min.js', array('jquery'), self::version );
			wp_enqueue_script( 'jquery-datatables-jqueryui-responsive', $this->plugin_url . '/js/responsive.jqueryui.min.js', array('jquery'), self::version );
			wp_enqueue_script( 'jquery-scrollto', $this->plugin_url . '/js/jquery.scrollTo.min.js', array('jquery'), self::version );
			wp_enqueue_script( 'jquery-intl-tel-input', $this->plugin_url . '/js/intlTelInput.min.js', array('jquery'), self::version );
			wp_enqueue_script( 'jquery-blockui', $this->plugin_url . '/js/jquery.blockUI.js', array('jquery'), self::version );
		}
		else {
			if ( wpb_is_mobile() ) {
				wp_enqueue_script( 'jquery-mobile', 'https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js', array('jquery'), self::version );
				wp_enqueue_script( 'swipe', $this->plugin_url . '/js/swipe.js', array(), self::version );
			}
			else {
				wp_enqueue_script( 'jquery-ui-dialog' ); # ui-dialog conflicts with jquery-mobile
			}
			
			$this->enqueue_effects();
			wp_enqueue_script( 'wp-base-libs', $this->plugin_url . '/js/libs.js', array('jquery-ui-widget','jquery-ui-button','jquery-ui-datepicker'), self::version, true );
		}
		
		if ( $locale && 'en' != $locale && 'en-us' != $locale ) {
			foreach( array( $locale, $locale_short ) as $lcl ) {
				if ( file_exists( $this->plugin_dir . '/js/locale/'.$lcl.'.js' ) ) {
					wp_enqueue_script( 'jquery-ui-moment-locale', $this->plugin_url . '/js/locale/'.$lcl.'.js', array('jquery'), self::version );
					break;
				}
			}			
		}
		
		if ( 'yes' != wpb_setting('disable_css') ) {
			
			if ( WpBDebug::is_debug() ) {
				if ( wpb_is_mobile() ) {
					wp_enqueue_style( "jquery-ui-structure", $this->plugin_url . "/css-mobile/jquery.mobile.structure-1.4.5.min.css", array(), self::version );
				}
				else {
					wp_enqueue_style( "jquery-ui-structure", $this->plugin_url . "/css/jquery-ui.structure.min.css", array(), self::version );
					wp_enqueue_style( 'jquery-qtip', $this->plugin_url . '/css/jquery.qtip.css', array(), self::version );
					wp_enqueue_style( "jquery-multiselect", $this->plugin_url . "/css/jquery.multiselect.css", array(), self::version );
					wp_enqueue_style( "jquery-multiselect-filter", $this->plugin_url . "/css/jquery.multiselect.filter.css", array("jquery-multiselect"), self::version );
				}
				
				wp_enqueue_style( "jquery-ui-".sanitize_file_name( $this->selected_theme() ), $this->get_theme_file(), array(), self::version );
				wp_enqueue_style( 'jquery-datatables-responsive-ui', $this->plugin_url . '/css/responsive.jqueryui.css', array(), self::version );
				wp_enqueue_style( 'jquery-datatables-jqueryui', $this->plugin_url . '/css/dataTables.jqueryui.css', array(), self::version );
				wp_enqueue_style( 'jquery-datatables-tabletools', $this->plugin_url . '/css/dataTables.tableTools.css', array(), self::version );
				wp_enqueue_style( 'jquery-intl-tel-input', $this->plugin_url . '/css/intlTelInput.css', array(), self::version );
				wp_enqueue_style( 'wp-base', $this->get_front_css_file(), array(), self::version );
				if ( is_rtl() )
					wp_enqueue_style( 'wp-base-rtl', $this->plugin_url . '/css/common-rtl.css', array(), self::version );
			}
			else {
				if ( wpb_is_mobile() ) {
					wp_enqueue_style( "wp-base-libs-mobile-min", $this->plugin_url . "/css-mobile/libs.mobile.min.css", array(), self::version );
				}
				else {
					wp_enqueue_style( "wp-base-libs-min", $this->plugin_url . "/css/libs.min.css", array(), self::version );
				}
				
				wp_enqueue_style( "jquery-ui-".sanitize_file_name( $this->selected_theme() ), $this->get_theme_file(), array(), self::version );
				wp_enqueue_style( 'wp-base', $this->get_front_css_file(), array(), self::version );
				if ( is_rtl() )
					wp_enqueue_style( 'wp-base-rtl-min', $this->plugin_url . '/css/common-rtl.css', array(), self::version );
			}
		}
		
		wp_enqueue_style( "wp-base-updating", $this->plugin_url . "/css/updating.css", array(), self::version );
		
		$this->load_assets_called = true;
		
		# Let other addons/class/plugins load their js files, or dequeue files here
		do_action( 'app_load_assets' );
	}
	
	/**
	 * Enqueue used jQuery effects
	 * @since 3.0
	 */	
	function enqueue_effects(){
		$effects = array( 'drop' );
		$effects[] = wpb_setting( 'hide_effect' );
		$effects[] = wpb_setting( 'show_effect' );
		foreach ( array_unique( array_filter( $effects ) ) as $e ) { 
			wp_enqueue_script( 'jquery-effects-' . $e );
		}		
	}

	/**
	 * Adds js codes for default values
	 * This is deferred to run at template_redirect action so that $post vars are defined
	 * @param $args	array	to overwrite default values
	 * @since 2.0
	 */	
	function add_default_js( ) {
		# Prevent duplicate loading
		if ( !empty( $this->default_js_added ) )
			return;
		
		global $current_screen, $app_gateway_active_plugins, $wp_locale;

		$post = get_post();
		$post_id = isset( $post->ID ) ? $post->ID : 0;
		
		# Refresh url is found from a) Set "refresh url" post ID b) Set "refresh_url" url value c) current post/page url d) home_url
		$set_rurl = wpb_setting('refresh_url');
		if ( $set_rurl ) {
			if ( is_numeric( $set_rurl ) && $maybe_post_url = get_permalink( $set_rurl ) )
				$refresh_url = $maybe_post_url;
			else
				$refresh_url = $set_rurl;
		}
		else if ( $post_id && $maybe_post_url = get_permalink( $post_id ) )
			$refresh_url = $maybe_post_url;
		else
			$refresh_url = home_url();
		
		# Is user registration allowed?
		$do_register = is_multisite()	?
										in_array(get_site_option('registration'), array('all', 'user'))
										: 
										(int)get_option('users_can_register');
		
		$active_gateways = array();
		foreach ( (array)$app_gateway_active_plugins as $code=>$plugin ) {
			$active_gateways[] = $plugin->plugin_name;
		}
		
		$has_confirmation = $cart_values = false;
		$remaining_time = 0;
		
		$post_content = $this->post_content( isset( $post->post_content ) ? $post->post_content :'', $post );
		if ( has_shortcode( $post_content, 'app_confirmation') || has_shortcode( $post_content, 'app_book') ) {
			$has_confirmation = true;
			$remaining_time = BASE('Multiple')->remaining_time();
			if ( $remaining_time ) {
				wp_enqueue_script( 'jquery-plugin', $this->plugin_url . '/js/jquery.plugin.min.js', array('jquery'), self::version );
				wp_enqueue_script( 'jquery-countdown', $this->plugin_url . '/js/jquery.countdown.min.js', array('jquery','jquery-plugin'), self::version );
				if ( 'yes' != wpb_setting('disable_css') )
					wp_enqueue_style( "jquery-countdown", $this->plugin_url . "/css/jquery.countdown.css", array(), self::version);
				
				$cart_values = BASE('Multiple')->values();
			}
		}

		wp_enqueue_script( 'app-common' );
		
		if ( empty( $this->localize_script_added ) ) {
			wp_localize_script( 'app-common', '_app_',
				apply_filters( 'app_js_parameters',
					array(
						'ajax_url'						=> admin_url('admin-ajax.php'),
						'tabletools_url'				=> $this->tabletools_file,
						'nonce'							=> wp_create_nonce('front'),
						'iedit_nonce'					=> wp_create_nonce('inline_edit'),					// Required for FEBM
						'refresh_url'					=> $refresh_url,
						'con_error'						=> $this->get_text('connection_error'),
						'error_short'					=> $this->get_text('error_short'),
						'please_wait'					=> $this->get_text('please_wait'),
						'click_hint'					=> $this->get_text('click_to_book'),
						'click_to_remove'				=> $this->get_text('click_to_remove'),
						'removed'						=> $this->get_text('removed'),
						'warning_text'					=> $this->get_text('missing_field'),
						'opacity'						=> '0.6',											// General opacity, e.g. a button is selected. String is ok
						'modal'							=> 1,												// jQuery dialog is modal or not.
						'edited'						=> $this->get_text('appointment_edited'),
						'received'						=> $this->get_text('appointment_received'),
						'cc_legend'						=> $this->get_text('cc_form_legend'),
						'pay_now'						=> $this->get_text('pay_now'),
						'no_gateway'					=> $this->get_text('payment_method_error'),
						'too_less'						=> sprintf( $this->get_text('too_less'), BASE('Multiple')->get_apt_count_min() ),
						'checkout_button_tip'			=> $this->get_text('checkout_button_tip'),
						'post_id'						=> $post_id,
						'screen_base'					=> isset( $current_screen->base ) ? $current_screen->base : 0,
						'tab'							=> isset( $_GET['tab'] ) ? $_GET['tab'] : '',
						'current_user_id'				=> get_current_user_id(),
						'def_location'					=> $this->get_lid(),
						'def_service'					=> $this->get_first_service_id(),
						'def_worker'					=> $this->get_wid(),
						'def_timestamp'					=> !empty( $_GET['app_timestamp'] ) ? $_GET['app_timestamp'] : 0,
						'facebook'						=> $this->get_text('login_with_facebook'),
						'twitter'						=> $this->get_text('login_with_twitter'),
						'google'						=> $this->get_text('login_with_google'),
						'wordpress'						=> $this->get_text('login_with_wp'),
						'submit'						=> $this->get_text('submit_confirm'),
						'cancel'						=> $this->get_text('cancel'),
						'close'							=> $this->get_text('close'),
						'redirect'						=> $this->get_text('redirect'),
						'login_url'						=> wp_login_url(),
						'logged_in'						=> $this->get_text('logged_in'),
						'login_methods'					=> array_filter(explode( ',', wpb_setting('login_methods' ) )),
						'swatch'						=> wpb_setting('swatch' ),
						'username'						=> $this->get_text('username'),
						'password'						=> $this->get_text('password'),
						'login_debug'					=> BASE('Login') && BASE('Login')->get_debug_text(),
						'gg_client_id' 					=> wpb_setting('google-client_id'),
						'register' 						=> $do_register ? $this->get_text('register') : '',
						'registration_url'				=> $do_register ? wp_registration_url() : '',
						'localhost'						=> wpb_is_localhost() ? 1 : 0,		
						'active_gateways'				=> $active_gateways,
						'login_not_met'					=> 'yes' === wpb_setting('login_required') && !is_user_logged_in() ? 1 : 0,
						'use_effect'					=> 0,
						'book_table_resp_width'			=> 480, 					// At which px width 4 columns of book table reduced to 2
						'blink_starts'					=> 60,						// Countdown warning blink starts at (secs)
						'allow_focus'					=> wpb_is_ios() ? 0 : 1,	// Focus in iOS may cause issue
						'offset'						=> 200,						// ScrollTo offset from window top in px (should be positive)
						'dialog_offset'					=> 100,						// Dialog offset from window top in px (should be positive)
						'scroll_duration'				=> 1500,					// Scroll duration in msecs
						'remaining_time'				=> $remaining_time,			// In secs - This is default. Can be updated by ajax
						'cart_values'					=> $cart_values,			// Array of packed vals
						'countdown_pl'					=> array('Years','Months','Weeks','Days',$this->get_text('hours'),$this->get_text('minutes'),$this->get_text('seconds') ), 
						'countdown_sin'					=> array('Year','Month','Week','Day',$this->get_text('hour'),$this->get_text('minute'),$this->get_text('second') ),
						'is_rtl'						=> is_rtl() ? 1 : 0,
						'cancel_confirm_text'			=> $this->get_text('cancel_confirm_text'),
						'cancel_confirm_yes'			=> $this->get_text('cancel_confirm_yes'),
						'cancel_confirm_no'				=> $this->get_text('cancel_confirm_no'),
						'required'						=> $this->get_text('required'),
						'bp_displayed_user_id'			=> 0,						// To be overriden by bp, if it is active
						'bp_tab'						=> '',						// Ditto
						'tt_discounted_price'			=> $this->get_text('tt_discounted_price'),
						'tt_regular_price'				=> $this->get_text('tt_regular_price'),
						'is_mobile'						=> wpb_is_mobile() ? 1 : 0,
						'mp_active'						=> BASE('MarketPress') && BASE('MarketPress')->is_app_mp_page( $post_id ) ? 1 : 0,
						'wc_active'						=> BASE('WooCommerce') && BASE('WooCommerce')->is_app_wc_page( $post_id ) ? 1 : 0,
						'js_date_format'				=> wpb_dateformat_PHP_to_jQueryUI( $this->safe_date_format() ),
						'start_of_week'					=> $this->start_of_week,
						'monthNamesShort'   			=> array_values( $wp_locale->month_abbrev ),
						'dayNamesMin'       			=> array_values( $wp_locale->weekday_initial ),
						'location_filter'				=> 0,						// Adds filter to multiselect
						'service_filter'				=> 0,						// Ditto
						'worker_filter'					=> 0,						// Ditto
						'filter_label'					=> '',						// If left empty "Filter:"
						'filter_placeholder'			=> '',						// If left empty "Enter keywords"
						'app-terms-field-entry-warning'	=> $this->get_text('missing_terms_check'),
						'app_select_extras-warning'		=> $this->get_text('missing_extra'),
						'hide_effect'					=> wpb_setting( 'hide_effect' ),
						'show_effect'					=> wpb_setting( 'show_effect' ),
						'effect_speed'					=> 700,
						'updating_text'					=> $this->get_text( 'updating' ),
						'reading'						=> $this->get_text( 'reading' ),
						'booking'						=> $this->get_text( 'booking' ),
						'saving'						=> $this->get_text( 'saving' ),
						'calculating'					=> $this->get_text( 'calculating' ),
						'refreshing'					=> $this->get_text( 'refreshing' ),
						'checkout'						=> $this->get_text( 'checkout' ),
						'preparing_timetable'			=> $this->get_text( 'preparing_timetable' ),
						'preparing_form'				=> $this->get_text( 'preparing_form' ),
						'logging_in'					=> $this->get_text( 'logging_in' ),
						'done'							=> $this->get_text( 'done' ),
						'has_confirmation'				=> $has_confirmation ? 1 : 0,
						'lazy_load'						=> 'yes' === wpb_setting( 'lazy_load' ) && !wpb_is_admin() ? 1 : 0,
						'countdown_format'				=> 'hMS',
						'flex_modes'					=> array( 'fitHeightsPerRow', 'fitHeights', 'fitRows', 'moduloColumns', 'fitColumns', 'fitColumnsTitleTop'),
					)
				)
			);
			
			$this->localize_script_added = true;
		}
		
		$theme = (string)$this->selected_theme();
		$is_enabled = wpb_is_admin() ? 'yes' != wpb_setting('disable_css_admin') : 'yes' != wpb_setting('disable_css');
		
		if (  $is_enabled && ( 'start' == $theme || 'ui-darkness' == $theme || 'dark-hive' == $theme ||
			'south-street' == $theme || 'excite-bike' == $theme || 'vader' == $theme || 'dot-luv' == $theme || 'le-frog' == $theme || 
			'mint-choc' == $theme || 'black-tie' == $theme || 'trontastic' == $theme || 'swanky-purse' == $theme ) )
			$this->add2footer(
			'$("<style>.dataTables_info, .dataTables_wrapper .dataTables_paginate .fg-button, .app-list th, .app-book th, .appointments-list th, .app-weekly-hours-mins { color: #fff !important; }</style>").appendTo("head");
			');
			
		$this->default_js_added = true;
		
		add_action( 'wp_footer', 'wpb_updating_html' );
	}
	
	/**
	 * css that will be added to the head, again only for app pages
	 */
	function wp_head() {
		# A precaution if theme does not have wp_head but it has wp_print_styles or wp_print_scripts
		if ( !empty( $this->head_printed ) )
			return;
		?>
		<style type="text/css">
		<?php 
		foreach ( $this->get_legend_items() as $class=>$name ) {
			
			if ( !wpb_setting("color_set") ) {
				if ( wpb_setting($class."_color") )
					$color = wpb_setting($class."_color");
				else
					$color = wpb_get_preset( $class, 'start' );
			}
			else
				$color = wpb_get_preset( $class, wpb_setting("color_set") );

			$color = apply_filters( 'app_color', $color, $class );
			
			$selector = 'has_appointment' === $class ? 'table:not(.app-has-inline-cell) td.' : 'td.';
			echo $selector.$class.',div.'.$class.' {background-color: #'. $color .' !important;}';
		}
		
		if ( !is_admin() && '' != trim( wpb_setting("additional_css") ) ) {
			echo wpb_setting('additional_css');
		}
		?>
		</style>
		<?php
		$this->head_printed = true;		
	}
	
	/**
	 * Get post content. Allow theme builder type themes and page builders not using real post content hook into this filter
	 * Also see /includes/compat.php that already includes compatibility for some popular page builders
	 * @param $post: WP Post object
	 * @param $ajax: Whether this is an ajax request
	 * @since 2.0	
	 * @return string
	 */
	function post_content( $content, $post, $ajax = false ) {
		return apply_filters( 'app_post_content', $content, $post, $ajax );
	}

	/**
	 * Add a div to the content for Mobile Popup
	 * @param $content: WP Post content
	 * @since 2.0
	 * @return string
	 */
	function content_end( $content ) {
		if ( !wpb_is_mobile() )
			return $content;
		
		$add  = '<!-- Mobile popup by WP BASE -->';
		$add .= '<div id="app-front">';			 
		$add .= '<div id="app-msg-popup" class="ui-corner-all" style="max-width:400px;" data-role="popup"  data-transition="slideup" data-overlay-theme="'.wpb_setting('swatch').'" data-theme="'.wpb_setting('swatch').'">';
		$add .= '<div role="main" class="ui-corner-bottom ui-content ui-popup"><a href="#" data-rel="back" data-role="button" data-theme="'.wpb_setting('swatch').'" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>';
		$add .= '<h3 class="ui-title"></h3></div></div>';
		$add .= '</div><!-- End Mobile popup by WP BASE -->';
		
		# Prevent duplicates
		if ( strpos( $content, $add ) === false )
			$content = $content . $add;
		
		return $content;
	}

	/**
	 * Add a script to be used in the footer, checking duplicates
	 * In some themes, footer scripts were called twice. This function fixes it.
	 * @since 1.2.0
	 * @return none
	 */
	function add2footer( $j='', $on_top=false ) {
		if ( $j && strpos( $this->script, $j ) === false ) {
			if ( $on_top )
				$this->script = $j . $this->script;
			else
				$this->script = $this->script . $j;
			
			do_action( 'app_add2footer', $j );
		}
	}
	
	/**
	 * Load javascript to the footer
	 */
	function wp_footer() {
		$j = '';
		$this->script = apply_filters( 'app_footer_scripts', $this->script );
		
		$this->script = str_replace( 'response', 'r', $this->script );
		
		if ( $this->script ) {
			$j .= '<script type="text/javascript">';
			$j .= "jQuery(document).ready(function($) {";
			$j .= $this->script;
			$j .= "});</script>";
		}

		$post = get_post();
		$page_id = isset( $post->ID ) ? $post->ID : -1; 
		echo '<script type="text/template">';
		echo '<input type="hidden" class="app_footer_test" value="'.$page_id.'" />';
		echo '</script>';
		echo wpb_esc_rn( $j );
	}

	/**
	 * Format date for placeholder START_END to be used in calendar title
	 * @param start: Start timestamp
	 * @param end: End timestamp
	 * @since 2.0
	 * @return string
	 */	
	function format_start_end( $start, $end ) {
		if ( "F j, Y" == $this->date_format ) {
			if ( date( "F", $start ) == date( "F", $end ) ) {
				$formatted_end = date_i18n( "j", $end );
				if ( date_i18n( "j", $start ) == $formatted_end )
					return date_i18n( "F j", $start ) . ", " . date_i18n( "Y", $start );
				else
					return date_i18n( "F j", $start ) . " - " . $formatted_end . ", " . date_i18n( "Y", $start );
			}
			else if ( date( "Y", $start ) == date( "Y", $end ) )
				return date_i18n( "M j", $start ) . " - " . date_i18n( "M j", $end ) . ", " . date_i18n( "Y", $start );
			else
				return date_i18n( "M j, Y", $start ) . " - " . date_i18n( "M j, Y", $end );
		}
		else if ( strpos( $this->date_format, "-" ) !== false )
			return date_i18n($this->date_format, $start ) . " / " . date_i18n($this->date_format, $end );
		else
			return date_i18n($this->date_format, $start ) . " - " . date_i18n($this->date_format, $end );
	}

	/**
	 *	Shortcode which checks if user connected with a mobile device
	 *	@since 2.0
	 */
	function is_mobile_shortcode( $atts, $content='' ) {
		
		if ( !$content )
			return '';

		$pars = array(
				);
		extract( shortcode_atts( $pars, $atts, 'app_is_mobile' ) );
		
		if ( !wpb_is_mobile() )
			return WpBDebug::debug_text( __('Connected with non-mobile device. Content wrapped by shortcode ignored.','wp-base') );
		else
			return do_shortcode( $content ); # Allow execution of nested shortcodes
	}
	
	/**
	 *	Shortcode which checks if user connected with a mobile device
	 *	@since 2.0
	 */
	function is_not_mobile_shortcode( $atts, $content='' ) {
		
		if ( !$content )
			return '';

		$pars = array(
				);
		extract( shortcode_atts( $pars, $atts, 'app_is_mobile' ) );
		
		if ( wpb_is_mobile() )
			return WpBDebug::debug_text( __('Connected with mobile device. Content wrapped by shortcode ignored.','wp-base') );
		else
			return do_shortcode( $content );
	}

	/**
	 *	This shortcode does not produce any output
	 *	It can be used to load js and style files on a page where a custom template is used
	 *	@See /sample/sample-appointments-page.php
	 *	@since 2.0
	 */
	function no_html( $atts, $content='' ) {
		return '';
	}
	
	/**
	 *	This shortcode clears content if required conditions are not met, e.g. provider not selected
	 *  @param strip	string	Strip a single shortcode. Enter tag name here, e.g. app_workers
	 *	@since 2.0
	 */
	function hide( $atts, $content = '', $tag = 'app_hide', $where = '', $strip = '' ) {
		if ( !$content )
			return '';
		
		# Same shortcode is not allowed
		if ( has_shortcode( $content, $where ) )
			return WpBDebug::debug_text( __('Nesting of a shortcode inside the same type is not allowed.','wp-base') );
		
		$pars = array(
			'if'	=> '1=2',		# Condition to fulfill NOT hide
		);
		extract( shortcode_atts( $pars, $atts, 'app_hide' ) );
		
		$content = $this->hide_( $if, $content );
		
		# Add wrapper in order not to lose position
		if ( !$content )
			return '<div class="app-hide-wrapper" style="display:none"></div>';

		if ( $strip ) {
			# http://stackoverflow.com/a/9440785
			global $shortcode_tags;

			$stack = $shortcode_tags;
			$shortcode_tags = array($strip => 1);

			$content = strip_shortcodes($content);

			$shortcode_tags = $stack;
		}
		
		// if ( $where )
			// return '<div class="app-hide-wrapper parse-nested">'.$this->parse_nested( $content, $where).'</div>';
		// else
			return '<div class="app-hide-wrapper">'.do_shortcode( $content ).'</div>';

	}
	
	/**
	 *	Helper for hide function
	 *  @param if	string	Conditions to hide (If true, content is hidden - actually cleared). ALL conditions should match to hide
	 *  @param if	string	Content to be hidden/cleared
	 *	@since 2.0
	 */
	function hide_( $if, $content = '_' ) {
		
		$conditions = array_map('trim', explode( ',', wpb_sanitize_commas( $if ) ) );
		
		if ( empty( $conditions ) || !$content )
			return $content;
		
		foreach ( $conditions as $cond ) {
			$cond = str_replace( array(' ','_'), '', $cond );
			switch( $cond ) {
				case 'notloggedin':
				case 'notlogin':		if (!is_user_logged_in()) break; else return $content;
				case 'notsuperadmin':	if (!is_super_admin()) break; else return $content;
				case 'notadmin':		if (!current_user_can( WPB_ADMIN_CAP )) break; else return $content;
				case 'not'.$cond:
				case 'cannot'.$cond:	if (!current_user_can( $cond )) break; else return $content;
				case 'loggedin':		if (is_user_logged_in()) break; else return $content;
				case 'issuperadmin':	if (!is_super_admin()) break; else return $content;
				case 'isadmin':			if (current_user_can( WPB_ADMIN_CAP )) break; else return $content;
				case 'client':			if (current_user_can('app_client')) break; else return $content;
				case 'is'.$cond:
				case 'can'.$cond:		if (current_user_can( $cond )) break; else return $content;
				case 'allowcancel':
				case 'cancelallowed':	if ('yes' === wpb_setting('allow_cancel') ) break; else return $content;
				case 'donotallowcancel':
				case 'cancelnotallowed': if ('yes' !== wpb_setting('allow_cancel') ) break; else return $content;
				case 'allowedit':
				case 'editallowed':		if ('yes' === wpb_setting('allow_edit') ) break; else return $content;
				case 'donotallowedit':
				case 'editnotallowed':	if ('yes' !== wpb_setting('allow_edit') ) break; else return $content;
				case 'allowconfirm':
				case 'confirmallowed':	if ('yes' === wpb_setting('allow_confirm') ) break; else return $content;
				case 'donotallowconfirm':
				case 'confirmnotallowed': if ('yes' !== wpb_setting('allow_confirm') ) break; else return $content;
				case '1=2':				return $content;
			}
		}
		
		$comp_patterns = '(\<\>|\!\=|\=\=|\>\=|\<\=|\>|\<|\=)';
		
		foreach ( $conditions as $cond ) {
			$cond = str_replace( array('_'), '', $cond );
			
			# Retrieve < and >, because WP htmlencodes it
			$cond = html_entity_decode( $cond );
			$cond = str_ireplace( array(' lt ', ' le ', ' gt ', ' ge ', ' eq ', ' ne '), array('<', '<=', '>', '>=', '==', '!='), $cond );
			$cond = str_ireplace( ' ', '', $cond );
			if ( preg_match( '/(locationid|serviceid|workerid|providerid|userid|pageid|user|page)'.$comp_patterns.'([0-9])/', $cond, $matches ) ){
				switch( $matches[1] ) {
					case 'workerid':
					case 'providerid':	if ( version_compare( $this->get_wid(), $matches[3], $matches[2] ) ) break; else return $content;
					case 'serviceid':	if ( version_compare( $this->get_sid(), $matches[3], $matches[2] ) ) break; else return $content;
					case 'locationid':	if ( version_compare( $this->get_lid(), $matches[3], $matches[2] ) ) break; else return $content;
					case 'user':
					case 'userid':		if ( version_compare( get_current_user_id(), $matches[3], $matches[2] ) ) break; else return $content;
					case 'page':
					case 'pageid':		$post_test = get_post();
										$page_id = isset( $post_test->ID ) ? $post_test->ID : 0;
										if ( version_compare( $page_id, $matches[3], $matches[2] ) )
											break; 
										else return $content;
				}
			}
			else if ( preg_match( '/(service)'.$comp_patterns.'([0-9])/', $cond, $matches ) ){
				switch( $matches[1] ) {
					case 'service	':	$maybe_id = $this->find_service_id_from_name($matches[3]);
										if ( $maybe_id && version_compare( $this->get_sid(), $maybe_id, $matches[2] ) ) 
											break; 
										else return $content;
				}
			}
			else if ( preg_match( '/\$_GET\((.*?)\)'.$comp_patterns.'([0-9])/', $cond, $matches ) ) {
				if ( empty($_GET[$matches[1]]) || !version_compare( $_GET[$matches[1]], $matches[3], $matches[2] ) )
					return $content;
			}
			else if ( preg_match( '/(.*?)'.$comp_patterns.'([0-9])/', $cond, $matches ) ){
				# e.g. BALANCE lt 5
				if ( !version_compare( $matches[1], $matches[3], $matches[2] ) )
					return $content;
			}
			else if ( preg_match( '/(.*?)'.$comp_patterns.'(.*?)/', $cond, $matches ) ){
				# works in emails, because pattern is replaced e.g. PAYMENT_METHOD == manual-payments
				if ( !version_compare( $matches[1], $matches[3], $matches[2] ) )
					return $content;
			}
		}
		
		return '';		
	}

	/**
	 *	This shortcode shows content if ANY required condition is met: Opposite of hide
	 *	@since 2.0
	 */
	function show( $atts, $content='' ) {
		if ( !$content )
			return '';
		
		$pars = array(
			'if'	=> '1=1',		# Condition to fulfill show
		);
		extract( shortcode_atts( $pars, $atts, 'app_show' ) );
		
		$content = $this->show_( $if, $content );
		
		# Add wrapper in order not to lose position
		if ( !$content )
			return '<div class="app-hide-wrapper" style="display:none"></div>';
		else
			return '<div class="app-hide-wrapper">'.do_shortcode( $content ).'</div>';
	}
	
	/**
	 *	Helper for show function
	 *  @param if	string	Conditions to show (If true, content is displayed). ANY condition is enough to display content
	 *  @param if	string	Content to be displayed
	 *	@since 2.0
	 */
	function show_( $if, $content='_' ) {
		$neg_content = $this->hide_( $if, $content );
		$content = $neg_content ? '' : $content;
		return $content;
	}

	/**
	 *	Shortcode to select a theme on the front end
	 *	@since 2.0
	 */
	function theme_selector( $atts ) {
		
		if ( wpb_is_mobile() )
			return '';
		
		$pars = array(
			'placeholder'		=> '',								# In demo mode Random can be entered
			'title'				=> $this->get_text('select_theme'),	# Title
			'cap'				=> WPB_ADMIN_CAP,					# Capability to view the shortcode output
		);
		extract( shortcode_atts( $pars, $atts, 'app_theme' ) );

		if ( !wpb_current_user_can( $cap ) )
			return '';
		
		$themes = $this->get_themes();
		
		# Selected theme
		$cset = isset( $_GET['app_select_theme'] ) && ($_GET['app_select_theme'] === esc_attr(strtolower($placeholder))) ? $_GET['app_select_theme'] : false;
		if ( !$cset )
			$cset = isset( $_GET['app_select_theme'] ) && in_array( $_GET['app_select_theme'], $this->get_themes() ) ? $_GET['app_select_theme'] : false;
		if ( !$cset )
			$cset = isset( $this->sel_theme ) && in_array( $this->sel_theme, $this->get_themes() ) ? $this->sel_theme : false;
		if ( !$cset )
			$cset = wpb_get_session_val('app_theme', wpb_setting('theme'));
		
		$j = $s = '';
		
		$s .= '<div class="app_themes">';
		if ( "0" !== (string)$title ) {
			$s .= '<div class="app_themes_dropdown_title  app_title">';
			$s .= $title;
			$s .= '</div>';
		}
		
		$href = wpb_add_query_arg( array('app_select_theme'=>false,"rand"=>$placeholder) ) ."&app_select_theme=";
		
		$s .= '<select onchange="if (this.value) window.location.href=\''.$href.'\'+this.value" data-native-menu="false" data-theme="'.wpb_setting('swatch').'" name="app_select_theme" class="app-sc app_select_theme">';
		if ( $placeholder )
			$s .= '<option value="'.esc_attr(strtolower($placeholder)).'">' . $placeholder . ' ('. ucwords( $this->selected_theme()). ')</option>';
		foreach ( $themes as $theme ) {
			$theme_name = ucfirst( str_replace( "-", " ", $theme ) );
			$s .= '<option value="'.$theme.'" '. selected( $cset, $theme, false ) . '>' . $theme_name . '</option>';	
		}

		$s .= '</select>';
		$s .= '</div>';

		return $s;
	}

	/**
	 * Generate dropdown menu for users
	 * @since 2.0
	 */	
	function users( $atts ) {

		$pars = array(
			'title'				=> $this->get_text('select_user'),
			'show_avatar'		=> 1,
			'avatar_size'		=> '96',
			'autorefresh'		=> 'ajax',									# Ajax or 1
			'order_by'			=> 'display_name',
			'cap'				=> WPB_ADMIN_CAP,
		);
		
		extract( shortcode_atts( $pars, $atts, 'app_users' ) );
		
		if ( !wpb_current_user_can( $cap ) )
			return WpBDebug::debug_text( 'unauthorised' ); # Invisible to unauthorised users

		if ( !trim( $order_by ) )
			$order_by = 'ID';
		
		$users = apply_filters( 'app_users_dropdown', get_users( array( 'orderby'=>$order_by ) ) );
			
		if ( empty( $users ) )
			return;
			
		$j = $s = $e = '';
		
		$s .= '<div class="app_users">';
		if ( !is_numeric( $title ) || 0 !== intval( $title ) ) {
			$s .= '<div class="app_users_dropdown_title app_title">';
			$s .= $title;
			$s .= '</div>';
		}
		
		# Self user is always on top
		$s .= BASE('User')->app_dropdown_users( array( 
				'show_option_all'	=> __('Not registered user','wp-base'), 
				'echo'				=> 0, 
				'selected'			=> BASE('User')->read_user_id(), 
				'name'				=> 'app_select_users',
				'class'				=> 'app-sc app_select_users' 
				) 
			);

		$s .= '</div>';
		
		$timestamp = isset( $_GET['app_timestamp'] ) ? $_GET['app_timestamp'] : false;

		# First remove these parameters and add them again to make app_timestamp appear before js variable	
		$href = wpb_add_query_arg( array( "app_timestamp"=>false, "app_user_id" =>false ) );
		$href = apply_filters( 'app_user_href', wpb_add_query_arg( array( "app_timestamp"=>$timestamp, "app_user_id" => "'+selected_user" ), $href ) );
		
		$j .= "$('.app_select_users').change(function(){";
		$j .= "_app_.updating();";
		$j .= "selected_user=$('.app_select_users option:selected').val();";
		$j .= "$('.app_user_excerpt').hide();";
		$j .= "$('#app_user_excerpt_'+selected_user).show();";
		if ( 'ajax' === $autorefresh ) {
			$j .= '	var bob_data={wpb_ajax:true,action:"bob_update",app_user_id:selected_user};
					$.post(_app_.ajax_url, bob_data, function(response) {
						if ( response ) {
							if ( response.error ) {
								alert(response.error);
							}
							else if (response.result){
								$.each( response.result, function( k, v ) {
								  $(".app-"+k+"-field-entry").val(v);
								});
							}
						}
						else {
							alert("'.esc_js( $this->get_text('connection_error') ).'");
						}
					},"json");
			';
		}
		else {
			$j .= "window.location.href='".$href.";";
		}
		$j .= "});";
		
		$this->users_script = $j;
		
		$this->add2footer( $j );
		
		return $s;
	}

	/**
	 *	Shortcode showing user's or worker's, or all appointments
	 *	@since 2.0
	 */
	function listing( $atts=array() ) {
		
		# Let addons modify default columns
		$default_cols =  implode( ',', apply_filters( 'app_list_columns', array('id','service','client','date_time','status','cancel' ) ) );

		$atts = shortcode_atts( array(
			'title'			=> '',
			'columns'		=> $default_cols,
			'columns_mobile'=> $default_cols,
			'what'			=> 'client', 							// client, worker, all
			'user_id'		=> 0,
			'status'		=> implode( ',', apply_filters( 'app_list_status', array('paid','confirmed','pending','running' ) ) ),
			'service'		=> 0,									// Select a single or multiple services separated by comma
			'start'			=> 0,									// Start time of the appointments
			'end'			=> 0,									// End time of the appointments
			'order_by'		=> 'ID DESC',
			'limit'			=> 22,									// Client name character limit
			'edit_button'	=> $this->get_text('edit_button'),		// Text of edit button
			'edit_fields'	=> '',									// Which fields are allowed to be edited
			'cancel_button'	=> $this->get_text('cancel_button'),	// Text of cancel button
			'no_table'		=> 1, 									// Do Not display table if no results found
			'cap'			=> WPB_ADMIN_CAP, 						// Capability to view others appointments
			'id'			=> '',									// Optional ID
			'override'		=> 'inherit',							// Will admin obey edit/cancel global settings of clients or override them?
			'tabletools'	=> 1,									// Adds tabletools buttons
			'_tablesorter'	=> 1, 									// To disable tablesorter just in case
			'_as_tooltip'	=> 0,									// Use monthly shortcode as tooltip for users page.
			'_email'		=> 0,									// To be used in email. Automatically set to 1 by _replace method
			'_children_of'	=> 0,									// Show only (filter) children of the app given its ID
			'_wide_coverage'=> 0,									// Includes apps started before start and ending after end too. Only valid if start and end are both defined
		), $atts );
		
		extract( $atts );
	
		if ( "0" !== (string)$title ) {
			if ( !trim( $title ) ) {
				switch ( $what ) {
					case 'worker':
					case 'client':	$title = __('Bookings of USER_NAME', 'wp-base' ); break;
					case 'all':		$title = __('All Bookings', 'wp-base' ); break;
					default:		return WpBDebug::debug_text( __('Check "what" parameter in List shortcode','wp-base') ) ; break;
			
				}
			}
			$title_html = '<div class="app_title">' . esc_html( $title ) . '</div>';
		}
		else
			$title_html = '';

		# Check capability to view others appointments
		$can_edit = wpb_current_user_can( $cap );

		# Give a unique ID to the table
		if ( !$id ) {
			$this->nof_datatables = $this->nof_datatables + 1;
			$id = 'app_datatable_'. $this->nof_datatables;
		}
		
		# Set service clause
		$service_sql = '';
		if ( $service ) {
			$services = explode( ',', $service );
			if ( is_array( $services ) && !empty( $services ) ) {
				foreach ( $services as $s ) {
					if ( !is_numeric( $s ) )
						$s = $this->find_service_id_from_name( $s );

					# Allow only defined services
					if ( $this->service_exists( $s ) ) 
						$service_sql .= " service=".trim( $s )." OR ";
				}
			}
			$service_sql = rtrim( $service_sql, "OR " );
		}
		if ( !trim( $service_sql ) )
			$service_sql = ' 1=1 ';
		
		# Set status clause
		$statuses = explode( ',', $status );
		if ( empty( $statuses ) )
			return WpBDebug::debug_text( __('Check "status" parameter in List shortcode','wp-base') ) ;

		# Check for 'all'
		if ( in_array( 'all', $statuses ) )
			$stat = '1=1';
		else {
			$stat = '';
			foreach ( $statuses as $s ) {
				# Allow only defined stats
				if ( array_key_exists( trim( $s ), $this->get_statuses() ) ) 
					$stat .= " status='".trim( $s )."' OR ";
			}
			$stat = rtrim( $stat, "OR " );
		}
		
		# Set date/time clause
		if ( $start && $end ) {
			if ( $_wide_coverage )
				$datetime_sql = " start <= '" . wpb_date( $end ) . "' AND end > '" . wpb_date( $start ) . "' ";	
			else
				$datetime_sql = " start >= '" . wpb_date( $start ) . "' AND start <= '" . wpb_date( $end ) . "' ";
		}
		else {
			$datetime_sql = ' 1=1 AND';
			# This is different than is_busy. Here, we want to catch the start time of an appointment. So we dont look at app->end
			if ( $start ) {
				$datetime_sql = " start>='" . wpb_date( $start ) . "' AND";
			}
			if ( $end )
				$datetime_sql .= " start<'" . wpb_date( $end ) . "' ";
		}
		$datetime_sql = rtrim( $datetime_sql, "AND" );
		
		# If this is a client shortcode
		if ( 'client' === $what ) {

			$apps = $this->get_apps_from_cookie();
				
			if ( !is_array( $apps ) )
				return WpBDebug::debug_text( __('Try clearing your cookies','wp-base') ) ;
		
			$q = '';
			foreach ( $apps as $app_id ) {
				if ( is_numeric( $app_id ) )
					$q .= " ID=".$app_id." OR ";
			}
			$q = rtrim( $q, "OR " );
			
			# But he may as well has appointments added manually (requires being registered user) or we may be forcing to see a user
			if ( $user_id && $can_edit )
				$user_id = $user_id;
			else if ( isset( $_GET["app_user_id" ] ) && $can_edit )
				$user_id = $_GET["app_user_id" ];
			else if ( is_user_logged_in() )
				$user_id = get_current_user_id();

			if ( $user_id )
				$q .= " OR user=".$user_id;

			$q = ltrim( $q, " OR" );
			
			if ( $q && $stat ) {
				$query = "SELECT * FROM " . $this->app_table . " WHERE (".$q.") AND (".$stat.") AND ".$datetime_sql." AND (".$service_sql.") ORDER BY " . $this->sanitize_order_by( $order_by ) ." ";
				$results = $this->db->get_results( $query, OBJECT_K );
				wp_cache_set( 'apps_in_my_appointments', $results ); # Cache this so that we may use it.
			}
			else
				$results = false;
		}
		else if ( 'worker' === $what ) {

			# If no id is selected, get current user
			if ( $user_id && $can_edit )
				$user_id = $user_id;
			else if ( isset( $_GET["app_worker_id" ] ) && $can_edit )
				$user_id = $_GET["app_worker_id" ];
			else if ( isset( $_GET["app_user_id" ] ) && $can_edit )
				$user_id = $_GET["app_user_id" ];
			else if ( is_user_logged_in() )
				$user_id = get_current_user_id();
			
			# Only worker can see his bookings
			$q = $this->is_worker( $user_id ) ? "worker=".$user_id : "1=2";

			# Special case: If this is a single provider website, show staff appointments in his schedule too
			$workers = $this->get_workers();
			if ( current_user_can(WPB_ADMIN_CAP) && ( ( $workers && count( $workers ) == 1 ) || !$workers ) )
				$q .= ' OR worker=0';
			if ( $_as_tooltip )
				$results = $this->get_daily_reserve_apps_by_worker( $user_id, date("d F Y", strtotime( $start, $this->_time ) ) );
			else {
				$query = "SELECT * FROM " . $this->app_table . " WHERE (".$q.") AND (".$stat.") AND ".$datetime_sql." AND (".$service_sql.") ORDER BY ".$order_by ;
				$results = $this->db->get_results( $query, OBJECT_K  );
				wp_cache_set( 'apps_in_my_appointments', $results ); # Cache this so that we may use it.
			}
		}
		else if ( 'all' === $what ) {
			if ( $can_edit ) {
				$results = $this->db->get_results( "SELECT * FROM " . $this->app_table . " WHERE (".$stat.") AND ".$datetime_sql." AND (".$service_sql.") ORDER BY ".$this->sanitize_order_by( $order_by )." ", OBJECT_K );
				wp_cache_set( 'apps_in_all_appointments', $results ); # Cache this so that we may use it.
			}
			else
				return WpBDebug::debug_text( __('Not enough capability to view bookings when "what" attribute is "all"','wp-base') ) ;
		}
		else
			return WpBDebug::debug_text( __('Check "what" parameter in List shortcode','wp-base') ) ;
			
		# Can worker confirm pending appointments?		
		if ( $this->is_worker( get_current_user_id() ) && 'yes' === wpb_setting('allow_worker_confirm') )
			$add_confirm_column = true;
		else
			$add_confirm_column = false;
			
		# Can client edit his appointments? Can admin edit others apps?
		$override_edit = $can_edit && (('inherit' === $override && 'yes' === wpb_setting( 'allow_edit' )) || ('inherit' != $override && $override));
		if ( class_exists("WpBFEE") && (( !$can_edit &&  'yes' === wpb_setting('allow_edit') )  || $override_edit)) {
			$a_edit = true;
		}
		else
			$a_edit = false;
			
		/* Compacted parameters */
		$args = compact( array_keys( $atts ) );
		
		# Can client cancel own appointments? can admin cancel others appts? Can provider cancel own appts?
		$add_cancel_column = apply_filters( 'app_list_add_cancel_column', false, $args );

		$ret  = $c = '';
		$ret .= '<div class="app-sc app-list-wrapper">';
		$ret .= '<input type="hidden" class="app-list-shortcode-atts" data-atts="'.esc_attr(json_encode($atts)).'" />';
		
		/* Title */
		$ret .= str_replace( 'USER_NAME', BASE('User')->get_name( $user_id ), $title_html ); 

		/* Sanitize columns */
		$_columns = wpb_is_mobile() && trim( $columns_mobile ) ? $columns_mobile : $columns;
		$cols = array_map( 'strtolower', explode( ',', wpb_sanitize_commas( $_columns ) ) );

		$allowed = array( 'id','created','location','location_address','service','worker','client','email','phone','city','address','zip','country','note','price','date_time','date','day','time','status','confirm','cancel','edit','pdf','gcal','balance','deposit','total_paid','paypal' );
		# Filter edit, gcal, pdf depending on existence of addons
		$allowed = array_flip( $allowed );
		if ( !$add_cancel_column ) 
			unset( $allowed['cancel'] );
		if ( !$a_edit ) 
			unset( $allowed['edit'] );
		// TODO: Check this
		if ( !('yes' === wpb_setting('gcal_button') && class_exists( 'WpBGCal' ) ) )
			unset( $allowed['gcal'] );
		if ( !class_exists( 'WpBPDF' ) )
			unset( $allowed['pdf'] );
		if ( !class_exists( 'WpB_Gateway_Paypal_Standard' ) )
			unset( $allowed['paypal'] );
		$allowed = array_flip( $allowed );
		$allowed = apply_filters( 'app_list_allowed_columns', $allowed, $what );

		$colspan = 0;
		$ret  = apply_filters( 'app_list_before_table', $ret, $args );
		$ret .= '<table style="width:100%" id="'.$id.'" class="app-list my-appointments dt-responsive display dataTable no-wrap"><thead><tr>';
		
		foreach( $cols as $col ) {
			if ( ('client' === $what && 'client' === $col) || (('worker' === $what || 'provider' === $what) && 'worker' === $col) )
				continue;
			
			if ( !in_array( $col, $allowed ) )
				continue;
			
			if ( apply_filters( 'app_list_skip_col', false, $col, $args, $results, $cols ) )
				continue;
			
			$colspan++; 
			$ret .= '<th class="app-list-col app-list-'.$col.'-header">';
			switch ($col) {
				case 'id':			$ret .= $this->get_text('app_id'); break;
				case 'provider':
				case 'worker':		$ret .= $this->get_text('provider'); break;
				case 'confirm':		if ( $add_confirm_column ) {
										$ret .= __('Confirm', 'wp-base' );
									}
									break;
									# Addons may add more columns
				case 'gcal':		$ret .= $this->get_text('gcal'); break;
				case $col:			if ( 'udf_' == substr( $col, 0, 4 ) )
										$ret .= apply_filters('app_list_udf_column_title','',$col, $args); 
									else # Any other column can get its name from custom texts, i.e. deposit
										$ret .= apply_filters( 'app_list_column_name', $this->get_text($col), $col, $args); 
									break;
				default:			break;
			}
			$ret .= '</th>';
			
		}

		$ret .= '</tr></thead><tbody>';

		$no_results = true;
		$hard_limit = false;
		
		if ( $results ) {
			
			$parent_found = false;
			$start_microtime = wpb_microtime();
			# Prime meta cache
			wpb_update_app_meta_cache( array_keys( $results ) );
			
			if ( $_children_of && $this->get_app( $_children_of ) ) {
				$childs = BASE('Multiple')->get_children( $_children_of );
				$children = $childs ? array_keys( $childs ) : array();
			}
			else $children = array();

			foreach ( $results as $app_id=>$r ) {
				$is_parent = false;
				
				if ( $_children_of ) {
					if ( !in_array( $r->ID, $children ) )
						continue;
				}
				else if ( $this->is_app_recurring($app_id) || $main = $this->is_app_package($app_id) ) {
					if ( $r->parent_id )
						continue;
					else {
						$parent_found = true;
						$is_parent = true;
						if ( isset( $main ) )
							$r->service = $main; # Fix package service 
					}
				}
				
				if ( $this->is_internal( $r->service ) )
					continue;

				if ( apply_filters( 'app_list_skip_row', false, $cols, $args, $results, $r ) )
					continue;

				$no_results = false;
				$balance = 0;
				$ret .= '<tr>';
				foreach( $cols as $col ) {
					if ( ('client' === $what && 'client' === $col) || (('worker' === $what || 'provider' === $what) && 'worker' === $col) )
						continue;
					
					if ( !in_array( $col, $allowed ) )
						continue;
					
					if ( apply_filters( 'app_list_skip_cell', false, $col, $args, $results, $r ) )
						continue;
					
					$ret .= '<td class="'.$col.'-app-mng">';
					switch ( $col ) {
						# Redirect user to manage bookings page (on admin or front end), if it exists
						case 'id':		if ( current_user_can( WPB_ADMIN_CAP ) && !$_email ) {
											if ( is_admin() )
												$ret .= '<a href="'.admin_url('admin.php?page=appointments&type=all&stype=app_id&app_s='.$r->ID).'" title="'.sprintf( __('Click to manage booking #%s','wp-base'), $r->ID).'">' .apply_filters( 'app_ID_text', $r->ID, $r ) . '</a>';
											else if ( class_exists( 'WpBFEBM' ) && wpb_setting('manage_page') )
												$ret .= '<a href="'.wpb_add_query_arg( array( 'type'=>'all', 'stype'=>'app_id', 'app_s' => $r->ID ), get_permalink(wpb_setting('manage_page'))).'" title="'.sprintf( __('Click to manage booking #%s','wp-base'), $r->ID).'">' .apply_filters( 'app_ID_text', $r->ID, $r ) . '</a>';
											else 
												$ret .= '<a href="'.admin_url('admin.php?page=appointments&type=all&stype=app_id&app_s='.$r->ID).'" title="'.sprintf( __('Click to manage booking #%s','wp-base'), $r->ID).'">' .apply_filters( 'app_ID_text', $r->ID, $r ) . '</a>';
										}
										else
											$ret .= apply_filters( 'app_ID_text', $r->ID, $r );
										break;
									
						case 'created':	$ret .= date_i18n( $this->time_format, strtotime( $r->created ) + $this->get_client_offset($r->created) );
										break;
						case 'created_by':
										$ret .= wpb_get_app_meta( $r->ID, 'created_by' );
										break;
						case 'location':$ret .= apply_filters( 'app_list_location_name', $this->get_location_name( $r->location ), $r->ID );
										break;
						case 'location_address':
										$ret .= wpb_get_location_meta( $r->location, 'address' );
										break;
						case 'service':	$sname = apply_filters( 'app_list_service_name', $this->get_service_name( $r->service ), $r->ID );
										if ( !$is_parent )
											$ret .= $sname;
										else {
											$ret .= '<abbr class="app-list-service-name" data-app_id="'.$r->ID.'">'. $sname . '<abbr.';
										}
										break;
						case 'category':
										if ( $maybe_category = get_app_meta( $r->ID, 'category' ) )
											$ret .= $this->get_category_name( $maybe_category );
										else
											$ret .= $this->guess_category_name( $r->service );
										break;
						
						case 'provider':
						case 'worker':	$ret .= apply_filters( 'app_list_worker_name', $this->get_worker_name( $r->worker ), $r->ID );
										break;
						case 'client':	$ret .= apply_filters( 'app_list_client_name', BASE('User')->get_client_name( $r->ID, $r, true, $limit ), $r->ID );
										break;
						case 'price':	$ret .= wpb_format_currency( '', $r->price );
										break;
						case 'deposit':	$ret .= wpb_format_currency( '', $r->deposit );
										break;
						case 'total_paid':
										$paid = $this->get_total_paid_by_app( $r->ID );
										$ret .= wpb_format_currency( '', $paid/100 );
										break;
						case 'balance':
										$paid = $this->get_total_paid_by_app( $r->ID );
										$balance = $paid/100 - $r->price + $r->deposit;
										$ret .= wpb_format_currency( '', $balance );
										break;
						case 'paypal':	if ( !$balance ) {
											$paid = $this->get_total_paid_by_app( $r->ID );
											$balance = $paid/100 - $r->price + $r->deposit;
										}
										if ( $balance < 0 ) {
											$pp = new WpB_Gateway_Paypal_Standard;
											$ret .= $pp->payment_form( $r->ID, -1, abs($balance) );
										}
										break;											
						case 'email':	$ret .= apply_filters( 'app_list_email', $r->email, $r );
										break;
						case 'phone':	$ret .= apply_filters( 'app_list_phone', $r->phone, $r );
										break;
						case 'address':	$ret .= apply_filters( 'app_list_address', $r->address, $r );
										break;
						case 'zip':		$ret .= $r->zip;
										break;
						case 'city':	$ret .= $r->city;
										break;
						case 'country':	$ret .= $r->country;
										break;
						case 'note':	$ret .= $r->note;
										break;
						case 'date_time':
										if ( $this->is_daily( $r->service ) )
											$ret .= date_i18n( $this->date_format, strtotime( $r->start ) );
										else
											$ret .= date_i18n( $this->dt_format, strtotime( $r->start ) + $this->get_client_offset($r->start) );
										break;
						case 'date':				
										$ret .= date_i18n( $this->date_format, strtotime( $r->start ) + $this->get_client_offset($r->start) );
										break;
						case 'day':				
										$ret .= date_i18n( "l", strtotime( $r->start ) + $this->get_client_offset($r->start) );
										break;
						case 'time':				
										$ret .= date_i18n( $this->time_format, strtotime( $r->start ) + $this->get_client_offset($r->start) );
										break;
						case 'status':	$ret .= $this->get_status_name( $r->status );
										break;
						case 'confirm':	# If allowed so, a worker can confirm an appointment himself
										if ( $add_confirm_column ) {
											if ( 'pending' === $r->status ) {
												$is_disabled = '';
												$title_c = '';
											}
											else {
												$is_disabled = ' app-disabled-button';
												$title_c = 'title="'. $this->get_text('not_possible').'"';
											}

											$ret .= '<button class="app-list-confirm ui-button ui-state-default '.$is_disabled.'" name="app_confirm['.$r->ID.']" />'.__('Confirm','wp-base').'</button>';
										}
										break;
						case 'edit':	# If allowed so, a client can edit an appointment
										// TODO: Move this snippet to FEE
										if ( $a_edit ) {
											# We don't want completed appointments to be edited
											$stat = $r->status;
											$statuses = apply_filters( 'app_edit_allowed_status', array( 'pending','confirmed','paid' ), $r->ID, $args );
											$in_allowed_stat = in_array( $stat, $statuses );
											# Also check if edit time has been passed
											$is_edit_allowed = apply_filters( 'app_edit_is_edit_allowed', true, $r->ID );
											if ( $override_edit || ($in_allowed_stat && $is_edit_allowed && ($r->user == get_current_user_id() || $can_edit)) ) {
												$is_disabled = '';
												$title_c = '';
											}
											else {
												$is_disabled = ' app-disabled-button';
												if ( $r->user != get_current_user_id() && !$can_edit )
													$title_c = 'title="'. $this->get_text('unauthorised').'"';
												else if ( !$in_allowed_stat )
													$title_c = 'title="'. $this->get_text('not_possible').'"';
												else
													$title_c = 'title="'. $this->get_text('too_late').'"';
											}
											$ret .= '<button '.$title_c.' class="app-my-appointments-edit ui-button ui-state-default '.$is_disabled.'" name="app_edit['.$r->ID.']" >'.$edit_button.'</button>';
											/* Sort and filter User and UDF fields */
											$fields = $edit_fields;
											$allowed_fields  = apply_filters( 'app_confirmation_allowed_fields', $this->get_user_fields(), $app_id, $fields );
											$u_fields = array();
											if ( trim( $fields ) ) {
												$u_fields = explode( ",", wpb_sanitize_commas( $fields ) );
												$u_fields = array_map( 'strtolower', $u_fields );

												foreach ( $u_fields as $key=>$f ) {
													if ( !in_array( $f, $allowed_fields ) )
														unset( $u_fields[$key] );
												}
												$u_fields = array_filter( array_unique( $u_fields ) );
											}
											# If no special sorting set or nothing left, use defaults instead
											if ( empty( $u_fields ) ) {
												$u_fields = $allowed_fields;
												foreach( $u_fields as $key=>$f ) {
													if ( in_array( strtolower( $f ), $this->get_user_fields() ) && !wpb_setting("ask_".$f) )
														unset( $u_fields[$key] );
												}
											}
											$sorted_user_fields = $u_fields;
											$this->add2footer( 'user_fields_edit ='.json_encode(array_values($sorted_user_fields)).';');
										}
										break;
						case 'cancel':	# If allowed so, a client/editor/worker can cancel an appointment
										if ( $add_cancel_column ) {
											$ret .= apply_filters( 'app_list_add_cancel_button', '', $r, $args );
										}
										break;
							
						case $col:		$ret .= apply_filters( 'app_list_add_cell', '', $col, $r, $args );
										break;
					}
					$ret .= '</td>';
					
				}
				$ret .= '</tr>';
				
				if ( ( wpb_microtime() - $start_microtime ) > WPB_HARD_LIMIT ) {
					$hard_limit = true;
					break;
				}
			}
			# Apply dataTables
			if ( $_tablesorter ) {
				if ( $locale = $this->get_locale() )
					$this->add2footer( '$.fn.dataTable.moment( "'.wpb_moment_format().'","'.strtolower($locale).'");');
				else
					$this->add2footer( '$.fn.dataTable.moment( "'.wpb_moment_format().'");');

				if ( $tabletools )
					$params = ',
							"dom": \'T<"app_clear">lfrtip\',
							"tableTools": {
								"sSwfPath":_app_.tabletools_url
							}';
				else
					$params = '';

				# Datatable args excluding outer brackets
				$datatable_args = apply_filters( 'app_list_datatable_args', 
							'"bAutoWidth": true,
							"initComplete": _app_.style_buttons(),
							fnInitComplete: function ( oSettings ) {
								$(this).css({opacity: 0.0, visibility: "visible"}).animate({opacity: 1.0});
								var dttt = jQuery("div.DTTT_container");
								var last_nav = dttt.parents().find(".dataTables_length").first();
								var padding = last_nav.css("padding-bottom");
								var padding_top = last_nav.css("padding-top");
								dttt.css({"float":"left","margin-bottom":0,"padding-left":"6px","padding-bottom":padding,"padding-top":padding_top});
								last_nav.after(dttt[0]).css("height","auto");
								$(".dataTables_filter input").attr("placeholder", "'.esc_js( $this->get_text('search') ).'");
							},							
							"lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "'.esc_js( $this->get_text('all') ).'"] ],
							responsive: true,
							"aaSorting": [ ],
							"bJQueryUI": true,
							"aoColumnDefs" : [ {
								"bSortable" : false,
								"aTargets" : [ "app-list-gcal-header","app-list-confirm-header","app-list-edit-header","app-list-cancel-header", "app-list-pdf-header" ]
							} ],
							"language": {
								"info": "'.esc_js( $this->get_text('info') ).'",
								"paginate": {
								  "next": "'.esc_js( $this->get_text('next') ).'",
								   "previous": "'.esc_js( $this->get_text('previous') ).'"
								},
								"search": "",
								"lengthMenu": "'.esc_js( $this->get_text('length_menu') ).'"
							  }'.$params, $id, $what );
							  
				$this->add2footer( '
					$.extend( $.fn.dataTableExt.oJUIClasses, {
						"sFilterInput": "ui-toolbar ui-state-default",
						"sLengthSelect": "ui-toolbar ui-state-default"
					});
				
					$("#'.$id.'").DataTable({'. $datatable_args .'});
					$("#'.$id.'").on( "draw.dt", function () {
						_app_.style_buttons();
					} );
				');	
				
				$this->add2footer( '	
					$(document).on( "click", "#'.$id.' .app-list-cancel", function() {
						if ( $(this).is(":checked") || $(this).data("clicked", true) ) {					
							if ( $(this).hasClass("app-disabled-button")) {
								return false;
							}	
							var cancel_box = $(this);
							cancel_box.closest("tr").css("opacity","0.3");
							if ( !confirm("'. esc_js( $this->get_text('cancel_app_confirm') ) .'") ) {
								cancel_box.attr("checked", false);
								cancel_box.closest("tr").css("opacity","1");
								return false;
							}
							else{
								var cancel_id = $(this).attr("name").replace("app_cancel[","").replace("]","");
								if (cancel_id) {
									var cancel_data = {action: "cancel_app", app_id: cancel_id, cancel_nonce: "'. wp_create_nonce('cancel-app') .'", args:'.json_encode($args).'};
									$.post(_app_.ajax_url, cancel_data, function(response) {
										cancel_box.closest("tr").css("opacity","1");
											cancel_box.attr("disabled",true);
										if (response && response.error ) {
											alert(response.error);
										}
										else if (response && response.success) {
											alert("'.esc_js( $this->get_text('cancelled') ).'");
											window.location.href=window.location.href;
										}
										else {
											alert("'.esc_js( $this->get_text('connection_error') ).'");
										}
									}, "json");
								}
							}
						}
						
					});
				');
				
				if ( $parent_found ) {
					$this->add2footer("
					$('.app-list-service-name').qtip({
						overwrite: true,
						content: {
							text: function(event, api) {
								api.elements.content.html(_app_.please_wait);
								return $.ajax({
									url: ajaxurl, 
									type: 'POST',
									dataType: 'json', 
									data: {
										wpb_ajax: true,
										app_id: $(this).data('app_id'),
										atts: JSON.parse($(this).parents('app-list-wrapper').find('app-list-shortcode-atts').data('atts')),
										action: 'app_show_children_in_tooltip'
									}
								})
								.then(function(res) {
									var content = res.result;
									return content;
								}, function(xhr, status, error) {
									api.set('content.text', status + ': ' + error);
								});
							}
						},hide:qtip_hide,position:qtip_pos,style:qtip_small_style
					});
					");
				}
			}
		}
		else {
			$ret .= '<tr><td colspan="'.$colspan.'">'. $this->get_text('no_appointments'). '</td></tr>';
		}
			
		$ret .= '</tbody></table><div class="app_clear"></div>';
		
		if ( $hard_limit )
			$ret .= WpBDebug::debug_text( sprintf( __('Hard limit activated. Execution time: %s secs.', 'wp-base' ), number_format( wpb_microtime() - $start_microtime, 1 ) ) );
		
		# If no results, do not produce a table
		if ( $no_results && $no_table ) {
			$ret = '<div class="appointments-list">';
			$ret .= $this->get_text('no_appointments');
		}
		
		$ret  = apply_filters( 'app_list_table', $ret, $args );
		
		
		$ret .= '</div>';
			
		# Special processes to be run when edit is allowed
		if ( $a_edit )
			do_action( 'app_list_after', $args );
		
		return $ret;
	}

	/**
	 * Generate an input field to select start date
	 * @since 2.0
	 */	
	function select_date( $atts ) {

		extract( shortcode_atts( array(
			'title'				=> $this->get_text('select_date'),
			'date'				=> '',
		), $atts, 'app_select_date' ) );
		
		$app_limit = $this->get_app_limit();

		# Force a date
		$timestamp = isset( $_GET['app_timestamp'] ) ? $_GET['app_timestamp'] : false;
		
		if ( !$date && (string)$date !== "0" ) {	
			if ( $timestamp )
				$date = date( $this->date_format, $timestamp );
			else
				$date = date( $this->date_format, $this->_time );
		}
			
		$j = $s = $e = '';
		
		$s .= '<div class="app_date" data-role="date">';
		if ( '0' !== (string)$title ) {
			$s .= '<div class="app_date_title app_title">';
			$s .= $title;
			$s .= '</div>';
		}
		$s .= '<input type="text" data-maxdate="'.$this->get_app_limit().'" name="app_timestamp" class="app-sc app_select_date ui-toolbar ui-state-default" value="'.$date.'"/>';
		if ( !wpb_is_mobile() )
			$s .= '<em class="app-icon icon-calendar"></em>';
		
		$s .= '</div>';

		
		return $s;
	}

	/**
	 * Generate dropdown menu for services
	 */	
	function services( $atts, $content='' ) {
		
		$atts = shortcode_atts( array(
			'title'					=> $this->get_text('select_service'),
			'placeholder'			=> $this->get_text('select'),			// Since 2.0
			'class'					=> '',									// Since 2.0
			'description'			=> 'excerpt',
			'excerpt_length'		=> 55,
			'order_by'				=> 'sort_order',						// Also valid for categories
			'location'				=> 0, 									// Since 2.0
			'worker'				=> 0, 									// Forcing for a certain worker. since 1.2.3
			'category'				=> 0,									// Since 3.0. List only services in a certain category (force)
			'always_show_cat_title'	=> 0,									// Since 3.0. Displays cat title even empty or forced
			'category_optgroup'		=> 1,									// Use category optgroups. Since 2.0
			'hide_if'				=> '',									// Hide conditional. Since 2.0
			'_menu'					=> '',									// Menu object
		), $atts, 'app_services' );
		
		extract( $atts );
		
		if ( $hide_if && !$this->hide_( $hide_if ) )
			return '';
		
		$s = '';
		
		$menu = $_menu instanceof WpBMenu ? $_menu : new WpBMenu( new WpBNorm( $location, 0, $worker ), $order_by );
		$s .= $menu->display_errors();
		
		$services = $menu->services;

		if ( "0" === (string)$title )
			$title_html = '';
		else
			$title_html = '<div class="app_title">' . esc_html( $title ) . '</div>';

		$s .= '<div class="app_services '.$class.'">';
		$s .= $title_html;

		# This part tries to make category selection understandable
		$cats			= $this->get_categories( $order_by );		
		$category 		= apply_filters( 'app_services_category', $category, $atts );
		$selected_cat	= $category && is_numeric( $category ) ? $category : (!empty( $_GET['app_category'] ) && isset( $cats[$_GET['app_category']] ) ? $_GET['app_category'] : false);
		$use_cats 		= !$selected_cat && $category_optgroup && is_array( $cats ) && !empty( $cats ) ? true : false;
		$sel_cats		= $use_cats ? $cats : ( !empty($cats[$selected_cat]) ? array( $selected_cat=>$cats[$selected_cat] ) : array( 0=>array('name'=>'') ) );
		
		$s .= '<select %SERVICES_WITH_PAGE% data-desc="'.esc_attr($description).'" data-ex_len="'.intval($excerpt_length).'" data-native-menu="false" data-theme="'.wpb_setting('swatch').'" name="app_select_services" class="app-sc app_select_services app_ms">';
		if ( $placeholder || 'no' === wpb_setting('preselect_first_service') ) {
			$p_text = $placeholder ? $placeholder : $this->get_text('select');
			$s .= '<option value="" disabled %MAYBE_SELECTED% hidden>'.$p_text.'</option>';	# We will replace %MAYBE_SELECTED% placeholder later
		}
		
		$there_is_selected = false;
		$with_page = array();

		foreach ( $sel_cats as $cat_id => $cat ) {
			$s .= '%CATEGORY_OPTSTART%';
		
			$cat_has_service = false;
			
			foreach ( $services as $service ) {
				if ( $cat_id && !$this->is_in_category( $cat_id, $service->ID ) )
					continue;
				if ( $this->is_internal( $service->ID ) )
					continue;
				if ( apply_filters( 'app_services_skip_service', false, $service, $menu ) )
					continue;
				
				$e		= '';
				$page	= apply_filters( 'app_service_page', $service->page, $service, $menu );
				if ( $page ) { $with_page[] = $service->ID; }	
				$cat_has_service = true;

				# Check if this is the first service, so it would be displayed by default
				if ( $service->ID == $this->get_sid() ) {
					$sel = ' selected="selected"';
					$there_is_selected = true;
				}
				else $sel = '';

				# Add options
				$s .= '<option value="'.$service->ID.'"'.$sel.'>'. apply_filters( 'app_service_name_in_menu', $this->get_service_name( $service->ID ), $service ) . '</option>';
				
				# Do something for each service
				do_action( 'app_services_item', $service, $menu, $cat_id );
			}
			$s .= '%CATEGORY_OPTEND%';
			
			# Replace category optgroup placeholders				
			if ( $always_show_cat_title || ( $use_cats && $cat_has_service ) )
				$replace = array( '<optgroup data-category_id="'.intval($cat_id).'" label="'.esc_attr( $this->get_category_name( $cat_id ) ).'">', '</optgroup>' );
			else
				$replace = array();
			
			$s = str_replace( array( '%CATEGORY_OPTSTART%', '%CATEGORY_OPTEND%' ), $replace, $s ); 
		}
		
		$s .= '</select>';

		# Replace "with page" placeholder				
		$replace = !empty( $with_page ) ? 'data-with_page="'.implode( ',', $with_page ).'"' : '';
		$s = str_replace( '%SERVICES_WITH_PAGE%', $replace, $s );
		
		# Replace selected placeholder
		$replace = $there_is_selected ? '' : 'selected';
		$s = str_replace( '%MAYBE_SELECTED%', $replace, $s );
		
		$s .= '</div>';
	
		return $s;
	}

	/**
	 * Replace common placeholders in title texts
	 * @since 3.0
	 * @return string
	 */	
	function calendar_title_replace( $title ) {
		return str_replace( 
			array( "LOCATION", "WORKER", "SERVICE", "CATEGORY", ),
			array( 
				$this->get_location_name( $this->get_lid() ),
				$this->get_worker_name( $this->get_wid() ),
				$this->get_service_name( $this->get_sid() ),
				$this->guess_category_name( $this->get_sid() ),
				),
			$title
		);
	}
	
	/**
	 * Generate html codes for calendar subtitle
	 * @since 2.0
	 * @return string
	 */	
	function calendar_subtitle( $logged, $notlogged ) {
		$c ='';
		if ( is_user_logged_in() || 'yes' != wpb_setting("login_required") ) {
			if ( '0' !== (string)$logged )
				$c .= '<span>' . $logged . '</span>';
		}
		else if ( !( BASE('Login') && BASE('Login')->is_login_active() ) ) {
			if ( !is_numeric( $notlogged ) || 0 !== intval( $notlogged ) ) {
				$c .= str_replace( 
					array( 'LOGIN_PAGE', 'REGISTRATION_PAGE' ),
					array( '<a class="appointments-login_show_login" href="'.esc_attr( wp_login_url( get_permalink() ) ).'">'. __('Login','wp-base'). '</a>',
					'<a class="appointments-register" href="' . wpb_add_query_arg( 'redirect', get_permalink(), wp_registration_url() ) . '">' . __('Register','wp-base') . '</a>'
					),
					$notlogged );
			}
		}
		else if ( '0' !== (string)$notlogged ) {
			$c .= '<div class="app-sc appointments-login">';
				$c .= str_replace( 
					array( 'LOGIN_PAGE', 'REGISTRATION_PAGE' ),
					array( '<a class="appointments-login_show_login" href="'.esc_attr( wp_login_url( get_permalink() ) ).'">'. __('Login','wp-base'). '</a>',
					'<a class="appointments-register" href="' . wpb_add_query_arg( 'redirect', get_permalink(), wp_registration_url() ) . '">' . __('Register','wp-base') . '</a>'
					),
					$notlogged );
			$c .= '<div class="appointments-login_inner">';
			$c .= '</div>';
			$c .= '</div>';
		}
		
		return $c;
	}
	
	/**
	 * Normalize calendar start timestamp from settings or app_timestamp value
	 * @param $start	integer|string	Timestamp or date/time
	 * @since 3.0
	 * @return			integer			Timestamp
	 */	
	function calendar_start_ts( $start ) {
		if ( 'auto' === $start && $maybe_first = $this->find_first_free_slot() )
			$start = $maybe_first;
		else if ( !empty( $_GET['app_timestamp'] ) )
			$start =  $_GET['app_timestamp'];
		else if ( $start ) {
			$start = is_numeric( $start ) ? $start : strtotime( $start, $this->_time );
		}
		else
			$start = $this->_time;
		
		return $start;
	}
	
	/**
	 * Compact shortcode function that generates a full appointment page
	 * @since 2.0
	 */	
	function book( $atts ) {
		
		$mobile_type_def		= BASE('Pro') ? 'flex' : 'table';
		$mobile_title_def		= BASE('Pro') ? '0' : 'START';
		$default_is_select_date	= wpb_is_mobile() ? 0 : 1;
		$range_def				= BASE('Pro') ? '14 days' : '10';
	
		extract( shortcode_atts( array(
			'title'				=> 'SERVICE - START',
			'mobile_title'		=> $mobile_title_def,
			'location_title'	=> $this->get_text('select_location'),
			'service_title'		=> $this->get_text('select_service'),
			'worker_title'		=> $this->get_text('select_provider'),
			'location'			=> 0,
			'service'			=> 0,
			'category'			=> 0,					// Since 3.0. Force a category
			'worker'			=> 0,					// Set "auto" to select worker of page if this is a bio page
			'order_by'			=> 'sort_order',		// Name, id or sort_order. Single setting for all lsw + cat
			'type'				=> 'monthly',			// Table, weekly, monthly. Flex (Pro)
			'mobile_type'		=> $mobile_type_def,	// Table, weekly, monthly. Flex (Pro)
			'columns'			=> 'date,day,time,button',	// Table columns when type is table
			'columns_mobile'	=> 'time,button',
			'display'			=> 'with_break',		// Since 3.0. full, with_break, minimum
			'mode'				=> 1,					// Flex mode
			'mobile_mode'		=> 6,					// Flex mode for mobile
			'range'				=> 0,					// How much to display: week, 2weeks, month, 2months, day, number. Weekly and monthly calendar old "count" parameter is same as numerical range
			'start'				=> 0,
			'from_week_start'	=> 1,					// Days start from start day of the week
			'add'				=> 0,
			'swipe'				=> 1,					// Use swipe mobile function. When swipe=1 only range=1day is allowed
			'select_date'		=> $default_is_select_date,
			'logged'			=> 0,
			'notlogged'			=> $this->get_text('not_logged_message'),
			'_final_note_pre'	=> '',					// Required for Paypal Express (Please confirm ... amount)
			'_final_note'		=> '',
			'_app_id'			=> 0,					// Required for Paypal Express
			'_button_text'		=> $this->get_text('checkout_button'), // Required for Paypal Express
			'_layout'			=> 600,					// Experimental: Two divide confirmation form into 2 columns
			'_countdown'		=> 'auto',				// since 3.0 use countdown to refresh the page or not. 'auto' determines if a refresh is better
			'_countdown_hidden'	=> 0,					// whether countdown will be hidden
			'_countdown_title'	=> $this->get_text('conf_countdown_title'), // Use 0 to disable
			'_continue_btn'		=> 1,
			'_editing'			=> 0,					// Required for Paypal Express (Automatically set to 2)
			'_just_calendar'	=> 0,					// Exclude pagination + confirmation form and outer wrapper app-compact-book-wrapper
		), $atts, 'app_book' ) );
		
		$main_title = wpb_is_mobile() ? $mobile_title : $title;
		
		$sel_mode = wpb_is_mobile() ? $mobile_mode : $mode;
		
		$sel_type = wpb_is_mobile() ? strtolower( $mobile_type ) : strtolower( $type );

		if ( '0' === (string)$range ) {
			if ( 'flex' == $sel_type || 'weekly' == $sel_type )
				$range = '2 weeks';
			else if ( 'table' == $sel_type )
				$range = 10;
			else 
				$range = '2 months';
		}
		
		if ( is_numeric( $main_title ) && 0 === intval( $main_title ) )
			$main_title = 0;
		else if ( 1 === $main_title ) {
			if ( 'monthly' === $sel_type )
				$main_title = $this->get_text('monthly_title');
			else
				$main_title = $this->get_text('weekly_title');
		}
		
		$allowed_types = array( 'table', 'weekly', 'monthly', 'week', 'month' );
		if ( BASE('Pro') )
			$allowed_types[] = 'flex';
		
		$what_is_wrong = '';
		
		if ( !in_array( $sel_type, $allowed_types ) )
			$what_is_wrong = 'type';
		
		$_range = trim( str_replace( array( 'months','month','weeks','week','days','day', ), '', $range ) );

		if ( is_numeric( $range ) && $range > 0 ) {
			$pag_step = $range;
			$pag_unit = 'number';
		}
		else if ( strpos( $range, 'month' ) !== false ) {
			$pag_step = is_numeric( $_range) ? $_range : 1;
			$pag_unit = 'month';
		}
		else if ( strpos( $range, 'week' ) !== false ) {
			$pag_step = is_numeric( $_range) ? $_range : 1;
			$pag_unit = 'week';
		}
		else if ( strpos( $range, 'day' ) !== false ) {
			$pag_step = is_numeric( $_range) ? $_range : 1;
			$pag_unit = 'day';
		}
		else
			$what_is_wrong = 'range';
		
		# Weekly and monthly calendar "count" parameter is same as numerical range

		if ( $what_is_wrong )
			return WpBDebug::debug_text( sprintf( __( 'Check "%s" attribute in Book shortcode', 'wp-base'), $what_is_wrong ) );
		
		$use_swipe = wpb_is_mobile() && $swipe && ('table' === $sel_type || 'flex' === $sel_type) ? true : false;

		$ret = '';

		$menu = new WpBMenu( new WpBNorm( $location, $service, $worker, false ), $order_by );
		$ret .= $menu->display_errors();

		$add_location	= $menu->show_locations_menu();
		$add_service	= $menu->show_services_menu();
		$add_recurring	= $this->is_recurring( $this->get_sid() );
		$add_seldur		= BASE('SelectableDurations') && BASE('SelectableDurations')->is_duration_selectable( $this->get_sid() ) ? true : false;
		$add_worker		= $menu->show_workers_menu() && 'no' != wpb_setting('client_selects_worker') ? true : false;
		$add_seats		= BASE('GroupBookings');
		$cnt			= count( array_filter( array( $add_location, $add_service, $add_worker, $add_recurring, $add_seldur, $add_seats ) ) );
		
		$sp_class = $cnt > 0 ?  'app_'.$cnt.'_basis app-flex-item'  : '';
		// $sp_class = $cnt > 0 ?  'app-flex-item'  : '';
		if ( $add_seats )
			$sp_class = $sp_class . ' has-seats';
		
		if ( !$_just_calendar )
			$ret  .= '<div class="app-compact-book-wrapper">';
		
		$ret .= '<div class="app-compact-book-wrapper-gr1">';
		$ret .= '<div class="app-flex-menu">';
		
		$common_args = array(
			'location'	=> $this->get_lid(),
			'service'	=> $this->get_sid(),
			'category'	=> $category,
			'worker'	=> $this->get_wid(),
			'class'		=> $sp_class,
			'order_by'	=> $order_by,
			'_menu'		=> $menu,
		);
		
		# Manage Menu display order acc. to priority
		$pri = apply_filters( 'app_lsw_priority', wpb_setting( 'lsw_priority', WPB_DEFAULT_LSW_PRIORITY ), $menu );

		foreach ( str_split( $pri ) as $lsw ) {
			
			switch ( $lsw ) {
				case 'L':
					if ( $add_location )
						$ret .= BASE('Locations')->locations( array_merge( array( 'title'=>$location_title, ), $common_args ) );
				break;
				case 'S':
					if ( $add_service )
						$ret .= $this->services( array_merge( array( 'title'=>$service_title, ), $common_args ) );
					if ( $add_seats )
						$ret .= BASE('GroupBookings')->seats( $common_args );
					if ( $add_seldur )
						$ret .= BASE('SelectableDurations')->durations( $common_args );
				break;
				case 'W':
					if ( $add_worker  )
						$ret .= BASE('SP')->service_providers( array_merge( array( 'title'=>$worker_title, ), $common_args ) );
				break;
				default:
				break;
			}
		}
		
		if ( $add_recurring ) {
			$ret .= BASE('Recurring')->recurring( $common_args );
		}

		$ret .= '</div>'; # Close flex-menu
		
		$ret .= BASE('Login') ? BASE('Login')->login( $common_args ) : '';
		
		$start = $this->calendar_start_ts( $start );

		switch ( $sel_type ) {
			case 'flex'	:
			case 'table'	: $pagination = $use_swipe ? '' : $this->pagination( array('unit'=>$pag_unit,'step'=>$pag_step,'disable_legend'=>1,'start'=>$start,'select_date'=>$select_date) ); break;
			case 'week'		:
			case 'weekly'	: $pagination = $this->pagination( array('unit'=>'week','step'=>$pag_step,'start'=>$start,'select_date'=>$select_date) ); break;
			case 'month'	:
			case 'monthly'	: $pagination = $this->pagination( array('unit'=>'month','step'=>$pag_step,'start'=>$start,'select_date'=>$select_date) ); break;
			default			: $pagination = ''; break;
		}
		
		$common_args_cal = array(
			'location'			=> $this->get_lid(),
			'service'			=> $this->get_sid(),
			'category'			=> $category,
			'worker'			=> $this->get_wid(),
			'order_by'			=> $order_by,
			'logged'			=> $logged,
			'notlogged' 		=> $notlogged,
			'title'				=> $main_title,
			'start'				=> $start,
			'range'				=> $range,
			'swipe'				=> $swipe,
			'display'			=> $display, 			
			'from_week_start'	=> $from_week_start,	# Table and Flex only
		);
		
		if ( 'table' === $sel_type ) {
			$ret .= $this->book_table( array_merge( array( 'columns'=>$columns, 'class'=>'app-book-child' ), $common_args_cal ) );
		}
		else if ( 'flex' === $sel_type ) {
			if ( !BASE('Pro') )
				return WpBDebug::debug_text( sprintf( __( 'Check "%s" attribute in Book shortcode', 'wp-base'), 'type' ) );
				
			$ret .= BASE('Pro')->book_flex( array_merge( array( 'mode'=>$sel_mode, 'class'=>'app-book-child' ), $common_args_cal ) );
		}
		else {
			$class = $pag_step > 1 ? 'app_2column app-book-child' : 'app-book-child';
			for ( $i=1; $i<13; $i++ ) {
				$args = array_merge( array( 'class'=>$class, 'add'=>$i-1+$add ), $common_args_cal );
				if ( 'weekly' === $sel_type || 'week' === $sel_type )
					$ret .= $this->calendar_weekly( $args );  
				else
					$ret .= $this->calendar_monthly( $args );
				
				if ( $i >= $pag_step )
					break;
			}
		}
		// $ret .= '<div style="clear:both;"></div>';
		$ret .= '</div><!-- Close gr1 -->'; # Close gr1
		
		if ( $_just_calendar )
			return $ret;
			
		$ret .= '<div class="app-compact-book-wrapper-gr2">';
		if ( !$use_swipe )		
			$ret .= $pagination;
		
		$ret .= $this->confirmation( array('layout'				=> $_layout,
											'_editing'			=> $_editing,
											'app_id'			=> $_app_id,
											'button_text'		=> $_button_text,
											'final_note_pre'	=> $_final_note_pre,
											'final_note'		=> $_final_note,
											'countdown'			=> $_countdown,	
											'countdown_hidden'	=> $_countdown_hidden,		
											'countdown_title'	=> $_countdown_title, 
											'continue_btn'		=> $_continue_btn,
			
											)
										);
		$ret .= '</div><!-- Close gr2 -->'; # Close gr2
		$ret .= '</div><!-- Close compact-book-wrapper -->'; # Close compact-book-wrapper
		
		return $ret;
	}

	/**
	 * Shortcode function to generate list view booking
	 */	
	function book_table( $atts ) {
		
		$args1 = shortcode_atts( array(
			'title'			=> $this->get_text('weekly_title'),
			'book_now'		=> $this->get_text('book_now_short'),
			'logged'		=> '',
			'notlogged'		=> $this->get_text('not_logged_message'),
			'location'		=> 0,
			'service'		=> 0,
			'worker'		=> 0, 
			'start'			=> 0,						// Will not work for range=number
			'add'			=> 0,						// How many days to add
			'class'			=> '',						// Add a class
			'range'			=> 10,						// What to display: week, 2weeks, month, 2months, day, number
			'complete_day'	=> 0,						// Forces to complete the day when range=number
			'net_days'		=> 0,						// Compansate for empty days
			'columns'		=> 'date,day,time,button',	// date_time will be hidden for wide area
			'columns_mobile'=> 'time,button',
			'tabletools'	=> 0,
			'id'			=> '',
			'swipe'			=> 1,
			'only_buttons'	=> 0,						// Omit table and all columns except buttons
			'_max'			=> 99999,					// Maximum number while trying to finish day (Total number of rows in the table)				
			'_min'			=> 1,						// Minimum number of slots at which break is allowed at the end of a day				
			'_tablesorter'	=> 1,
			'_menu'			=> '',						// since 3.0. WpBMenu object
			
		), $atts, 'app_book_table' );
		
		extract( $args1 );
		
		$ret = '';

		$menu = $_menu instanceof WpBMenu ? $_menu : new WpBMenu( new WpBNorm( $location, $service, $worker ) );
		$ret .= $menu->display_errors();
		
		$use_swipe = wpb_is_mobile() && $swipe ? true : false;
		
		$args1['start'] = $start_ts = $this->calendar_start_ts( $start );
		
		if ( $use_swipe ) {
			$args1['range'] = '1day';
			$args1['class'] = 'app-swipe-child';
			$args4 = $args3 = $args2 = $args1;
			
			$args2['start'] = date( 'Y-m-d', $start_ts + 24*3600 ); 	# Doesnt need to be 00:00. book_table will correct it
			$args3['start'] = date( 'Y-m-d', $start_ts + 2*24*3600 );
			$args4['start'] = date( 'Y-m-d', $start_ts + 3*24*3600 );
			$upper_limit = $this->get_upper_limit();
			
			$ret .= '<div id="app-slider" class="app-swipe"><div class="app-swipe-wrap">' . 
					$this->book_table_( $args1 ) . $this->book_table_( $args2 ). $this->book_table_( $args3 ) . $this->book_table_( $args4 );
			
			for ( $i=4; $i<=$upper_limit; $i++ ) {
				$ret .= '<div class="app-sc appointments-wrapper app-swipe-child" data-type="book_table" data-title="'.esc_attr($title).'" data-logged="'.esc_attr($logged).'" data-notlogged="'.esc_attr($notlogged).'" data-start-ts="'.($start_ts + $i*24*3600).'"></div>';
			}
			$ret .= '</div></div>';
		}
		else {
			$ret .= $this->book_table_( $args1 );
		}

		return $ret;
	}
	
	/**
	 * Helper for book table
	 */	
	function book_table_( $pars ) {

		extract( $pars );
		
		# Give a unique ID to the table
		if ( !$id ) {
			$this->nof_datatables_book = $this->nof_datatables_book + 1;
			$id = 'app_datatable_book'. $this->nof_datatables_book;
		}

		$time = strtotime( date( 'Y-m-d', $this->calendar_start_ts( $start ) ) );

		if ( $add ) 
			$time = $add * 3600 *24 + $time;

		# Previous timestamps. If this is set, list is being updated after prev/next buttons and override the above $time value
		# We have to calculate start values from previous values, because start of this table depends on previous end value
		$ts			= isset( $_POST['app_last_timestamps'] ) ? json_decode( wp_unslash( $_POST['app_last_timestamps'] ) ) : false;
		$is_daily	= $this->is_daily( $this->get_sid() );

		# We are only interested in 0th element			
		if ( isset($ts[0]) && is_array( $ts[0] ) ) {
			
			$prev_clicked = !empty( $_POST['prev_clicked'] ) ? true : false;
			
			foreach ( $ts[0] as $_ts ) {
				if ( isset( $_ts->table_id ) && $id == $_ts->table_id ) {
					if ( $prev_clicked )
						$time = $_ts->start;
					else {
						if ( $is_daily )
							$time = $_ts->end + 86400;
						else
							$time = $_ts->end;
					}
					break;
				}
			}
		}

		# Range
		$add_for_empty_days = false;								// Can be set only for day selection
		$number_limit 		= 0; 									// Number of lines to be displayed. If not set, time setting will be used
		$_range = trim( str_replace( array( 'months','month','weeks','week','days','day', ), '', $range ) );
		
		if ( strpos( $range, 'month' ) !== false ) {
			$_range = is_numeric( $_range) ? $_range : 1;
			$upper_limit = (int)((wpb_last_of_month( $time,($_range-1) ) -$time)/86400);
		}
		else if ( strpos( $range, 'week' ) !== false ) {
			$_range = is_numeric( $_range) ? $_range : 1;
			$upper_limit = 7*$_range + 1 - (int)(( $time - wpb_sunday( $time ) )/86400);
		}
		else if ( strpos( $range, 'day' ) !== false ) {
			$_range = is_numeric( $_range) ? $_range : 1;
			$upper_limit = $_range;
			$add_for_empty_days = $net_days ? true : false;
		}
		else if ( $range && is_numeric( $range ) && $range > 0 ) {
			$number_limit = $range;
			$upper_limit = $this->get_app_limit();
			# Start scanning from current time or earliest allowed time
			$time = max( $time, strtotime( date("d F Y", $this->get_lower_limit($this->_time, $this->get_sid())*3600 + $this->_time -3600), $this->_time ) );
		}
		else
			return WpBDebug::debug_text( __('Check "range" attribute in book shortcode','wp-base') ) ;

		# Prepare title
		if ( '0' === (string)$title ) {
			$title_html = '';
		}
		else {
			# We dont know END value yet, because it may be variable. So put a placeholder there.
			$end_replace = $number_limit ? '%SON_PLaCeHOLDER%' : date_i18n($this->date_format, $this->client_time($time) + $upper_limit*86400-60 );
			$start_end_replace = $number_limit ? '%STND_PLaCeHOLDER%' : $this->format_start_end( $this->client_time($time), $this->client_time($time) + $upper_limit*86400-60 );
			$title = str_replace( 	array( "START_END","START", "END", ),
									array( 
										$start_end_replace,
										date_i18n($this->date_format, $this->client_time($time) ),
										$end_replace,
										),
									$title
			);

			$title_html = '<div class="app_title">' . esc_html( $this->calendar_title_replace( $title ) ) . '</div>';
		}

		# Prepare table HTML
		$c  = $j = '';
		if ( wpb_is_admin() )
			$c .= '<div class="app-sc appointments-wrapper appointments-wrapper-admin '.$class.'" data-start-ts="'.$time.'">';
		else
			$c .= '<div class="app-sc appointments-wrapper '.$class.'" data-start-ts="'.$time.'">';

		$c .= $title_html;
		
		$c .= $this->calendar_subtitle( $logged, $notlogged );
		
		$args 		= compact( array_keys( $pars ) );
		$allowed	= $only_buttons ? array('button') : array( 'date','day','time','date_time','server_date_time','server_day','seats_total','seats_left','seats_total_left','button' );
		$allowed	= apply_filters( 'app_book_table_allowed_columns', $allowed, $args );
		$_columns	= wpb_is_mobile() && trim( $columns_mobile ) ? $columns_mobile : $columns;
		$cols		= array_map( 'strtolower', explode( ',', wpb_sanitize_commas( $_columns ) ) );
		$colspan	= 0;

		$c .= $only_buttons ? '<div class="app-book">' : '<table id="'.$id.'" class="app-book dt-responsive display" data-time="'.date_i18n( $this->dt_format, $time ).'" ><thead><tr>';
		
		# Prepare table headers
		foreach( $cols as $col ) {
			if ( !in_array( $col, $allowed ) )
				continue;
			
			$colspan++;
			$c .= $only_buttons ? '' : '<th class="app-book-col app-book-'.strtolower($col).'">';
			
			if ( 'button' === $col )
				$c .= $only_buttons ? '' : $this->get_text('action');
			else if ( 'day' === $col )
				$c .= $this->get_text('day_of_week');
			else
				$c .= $this->get_text($col);

			$c .= $only_buttons ? '' : '</th>';
		}
		$c .= $only_buttons ? '' : '</tr></thead><tbody>';
		
		# Find free time slots
		$i				= 1;
		$more_day		= 0;
		$added			= 0;
		$scan_time		= false;
		$slots			= array();
		$first_start	= $time;
		$upper_limit	= min( $upper_limit, $this->get_app_limit() ); 	# Upper limit cannot be greater than global upper limit
		$final			= $time+($upper_limit*86400);
		$format 		= $is_daily ? $this->date_format : $this->dt_format;

		$calendar = new WpBCalendar();
		$calendar->setup( array( 'disable_limit_check'=>true ) );
		$calendar->start_hard_limit_scan();
		
		for ( $d = $time; $d < $final+$more_day; $d = $d+86400 ) {
			
			$slots = array_merge( $slots, $calendar->find_slots_in_day( $d ) );
			
			# Check if we are in limits
			if ( $scan_time = $calendar->is_hard_limit_exceeded() ) {
				break;
			}
			
			$count = count( $slots );
			
			if ( $number_limit && $count >= $number_limit )
				break;
			
			# After last step, Add extra day if we could not reach the goal
			if ( $add_for_empty_days && $d === ($final -86400) && $number_limit < $count ) {
				$more_day	= $more_day +86400;
				$final		= $final + 86400;
			}			
		}
		
		# Save latest end timestamp
		$last_end = $d;
		
		# Start creating html inside the table
		foreach ( $slots as $slot ) {
			
			$slot_start	= $slot->get_start();
			$slot_end	= $slot->get_end();
			$worker		= $slot->get_worker();

			if ( $number_limit ) {
				if ( (!$complete_day && $added >= $number_limit) || ($complete_day && $added >= $_max && $added <= $_min) ) {
					$last_end = $slot_start;
					break;
				}
			}
			
			$added++;
			
			$c .= $only_buttons ? '' : '<tr class="app-book-'.strtolower( date("l", $slot_start ) ).'">';
			
			foreach( $cols as $col ) {
				if ( !in_array( $col, $allowed ) )
					continue;
				
				if ( in_array( $col, array( 'seats_total', 'seats_total_left', 'seats_left' ) ) ) {
					$available_workers = 	isset( $slot->stat[$slot_start][$slot_end]['available'] )
											?
											$slot->stat[$slot_start][$slot_end]['available']
											:
											$slot->available_workforce(  );
				}
				
				$c .= $only_buttons ? '' : '<td class="app-book-'.strtolower($col).'">';

				switch ( $col ) {
					case 'date':			$c .= date_i18n($this->date_format, $this->client_time($slot_start)); break;
					case 'day':				$c .= date_i18n('l', $this->client_time($slot_start)); break;
					case 'server_day':		$c .= date_i18n('l', $slot_start); break;
					case 'time':			$c .= $is_daily ? '' : date_i18n($this->time_format, $this->client_time($slot_start)); break;
					case 'date_time':		$c .= $is_daily ? date_i18n($format, $slot_start) : date_i18n($format, $this->client_time($slot_start)); break;
					case 'server_date_time':$c .= date_i18n($format, $slot_start); break;
					case 'seats_total':		$c .= $worker ? 1 : $available_workers; break;
					
					case 'seats_left':		
						if ( $worker ) {
							/* Normally:
							 * $avail = $slot->is_working( ) ? 1 : 0;
							 * $busy = $slot->is_busy( ) ? 1: 0;
							 * But no need to calculate, because a worker can only have 1 seat capacity and
							 * If he had been busy, this line would not be displayed
							*/															
							$avail = 1;
							$busy = 0;
						} else {
							$avail = $available_workers; 
							$busy = !empty( $slot->stat[$slot_start][$slot_end]['count'] ) ? $slot->stat[$slot_start][$slot_end]['count'] : 0;
						}
						
						$c .= max( 0, $avail-$busy );
						break;
											
					case 'seats_total_left':
						$total = $worker ? 1 : $available_workers;
						if ( $worker )
							$left = 1;
						else {
							$busy = !empty( $slot->stat[$slot_start][$slot_end]['count'] ) ? $slot->stat[$slot_start][$slot_end]['count'] : 0;
							$left = max( 0, $available_workers - $busy );
						}
						
						$c .= $total .' / ' . $left;
						break;
						
					case 'button' :
						if ( $book_now ) {
							$button_text = str_replace( 
												array( "DATE", "DAY", "START", ),
												array(
													date_i18n( $this->date_format, $this->client_time($slot_start) ),
													date_i18n('l', $this->client_time($slot_start)),																
													date_i18n( $format, $this->client_time($slot_start) ),
													),
												$this->calendar_title_replace( $book_now )
										);
						}
						else if ( $only_buttons )
							$button_text = $is_daily ? date_i18n( $format, $slot_start ) : date_i18n( $format, $this->client_time($slot_start) );
						else
							$button_text = $this->get_text('book');
						
						$has_var_cl =  $button_text != $this->get_text('book') ? ' app-has-var' : ''; // Button text has variables, i.e. widths may be different
						
						$disabled_cl = !is_user_logged_in() && 'yes' === wpb_setting("login_required") ? ' app-disabled-button' : '';
						
						$maybe_float = $only_buttons ? 'app_left app_only_buttons app_mrmb' : '';
						
						$c .= '<div title="'.WpBDebug::time_slot_tt($slot).'" tabindex="'.$i.'" class="appointments-book-now '.$maybe_float.'">';
						$c .= '<button class="app-book-now-button ui-button ui-btn ui-btn-icon-left ui-icon-shop'.$disabled_cl.$has_var_cl.'" >'. $button_text .
						'<input type="hidden" class="app_get_val" value="'.$slot->pack( ).'" />'.'</button>';
						$c .= '</div>';
						$i++;
						break;
						
					case $col:		
						$c .= apply_filters( 'app_book_table_add_cell', '', $col, $slot, $args );
						break;
				}

				$c .= $only_buttons ? '' : '</td>';				
				
			}

			$c .= $only_buttons ? '' : '</tr>';
			
		}
		
		if ( !count( $slots ) ) {
			$none_free = '<span class="app_book_table_full">'. $this->get_text('no_free_time_slots'). '</span>';
			$c .= $only_buttons ? $none_free : '<tr><td colspan="'.$colspan.'">'.$none_free.'</td></tr>';
		}
		$c .= $only_buttons ? '<div style="clear:both"></div></div>' : '</tbody></table>';
		
		if ( (bool)$scan_time )
			$c .= WpBDebug::debug_text( sprintf( __('Hard limit activated. Execution time: %s secs.', 'wp-base' ), number_format( $scan_time, 1 ) ) );
		
		# Replace placeholders in HTML
		$c = str_replace( '%STND_PLaCeHOLDER%', $this->format_start_end( $first_start, $last_end ), $c );
		$c = str_replace( '%SON_PLaCeHOLDER%', date_i18n( $this->date_format, $last_end ), $c );
		
		$j .= '	temp = typeof $(document).data("last_values") =="undefined" ? new Array() : $(document).data("last_values");
				temp = $.grep(temp, function(el){ return el.table_id != "'.$id.'"; });
							
				temp.push({
					table_id: "'.$id.'",
					start: '.$first_start.',
					end: '.$last_end.',
					start_r: "'.date_i18n( 'D M d Y H:i:s O', $first_start).'",
					end_r: "'.date_i18n( 'D M d Y H:i:s O', $last_end).'"
				});
				$(document).data("last_values",temp);
				';
				
		# Apply dataTables only if there is a row
		if ( !wpb_is_mobile() && $_tablesorter && count( $slots ) ) {
			if ( $locale = $this->get_locale() ) {
				$this->add2footer( '$.fn.dataTable.moment( "'.wpb_moment_format().'","'.strtolower($locale).'");');
				$this->add2footer( '$.fn.dataTable.moment( "'.wpb_moment_format( true ).'","'.strtolower($locale).'");');
			}	
			else
				$this->add2footer( '$.fn.dataTable.moment( "'.wpb_moment_format().'");');
			
			if ( $tabletools )
				$params = ',
						"dom": \'T<"app_clear">lfrtip\',
						"tableTools": {
							"sSwfPath":_app_.tabletools_url
						}';
			else
				$params = '';

			# Datatable args excluding outer brackets
			$datatable_args = apply_filters( 'app_book_datatable_args', 
						'"AutoWidth": true,
						"bAutoWidth": false,
						destroy: true,
						"initComplete": function(){_app_.style_buttons();_app_.adjust_button_width();_app_.show_hide_book_table_columns();},
						responsive: true,
						"paging": false,
						"info": false,
						"bFilter": false,
						"bJQueryUI": true,
						"sDom": "lfrtip",
						"aaSorting": [ ],
						"language": {
						  "emptyTable": "'.esc_js( $this->get_text('no_free_time_slots') ).'"
						},
						"aoColumnDefs" : [ {
							"bSortable" : false,
							"aTargets" : [ "app-book-button" ]
						} ]'.$params, $id );
						  
			$j .= '	$("#'.$id.'.app-book").DataTable({'. $datatable_args .'});';
		}
		
		# Also save for ajax update
		$this->list_script .= $j;
		
		$this->add2footer( $j );
		
		$calendar->save_cache();

		$c .= '</div>'; // appointments-wrapper
		
		return $c;
	}
	
	/**
	 * Shortcode function to generate book now
	 */	
	function book_now( $atts ) {
	
		extract( shortcode_atts( array(
			'title'			=> 0,
			'book_now'		=> $this->get_text('book_now_long'),					// Text when available
			'booking_closed'=> $this->get_text('booking_closed'),					// Text when not available
			'logged'		=> 0,
			'notlogged'		=> $this->get_text('not_logged_message'),				
			'location'		=> 0,
			'service'		=> 0,
			'worker'		=> 0, 
			'start'			=> '1970-01-01 00:00:00', 
			'page'			=> 0,													// Since 3.0. ID of the page where description of the event stays
		), $atts, 'app_book_now' ) );
		
		// Check service
		if ( !$service )
			return WpBDebug::debug_text( __('A service selection is always required for Book Now shortcode','wp-base') ) ;

		$c  = '';
		
		$menu =  new WpBMenu( new WpBNorm( $location, $service, $worker ) );
		$c 	.= $menu->display_errors();		

		# Fix empty date
		if ( '1970-01-01 00:00:00' == $start ) {
			$start = $this->find_first_free_slot( );
		}
		
		$slot_start = strtotime( $start, $this->_time );
		$slot_end = $slot_start + wpb_get_duration( $this->get_sid(), $slot_start )*60;
		
		// Check final start value
		if ( !strtotime( $start ) )
			return WpBDebug::debug_text( sprintf( __('Please check start (%s) date of Book Now shortcode','wp-base'), $start) ) ;
		
		$find = array( "START_END", "START", "END", );
		$replace = 	array( 
						$this->format_start_end( $slot_start, $slot_end ),
						date_i18n( $this->dt_format, $slot_start ),
						date_i18n( $this->dt_format, $slot_end ),
						);

		if ( '0' === (string)$title ) {
			$title_html = '';
		}
		else {
			$title = str_replace( $find, $replace, $title );
			$title_html = '<div class="app_title">' . esc_html( $this->calendar_title_replace( $title ) ) . '</div>';
		}
		
		$book_now = '0' === (string)$book_now ? '' : str_replace( $find, $replace, $this->calendar_title_replace( $book_now ) );
		$booking_closed = '0' === (string)$booking_closed ? '' : str_replace( $find, $replace, $this->calendar_title_replace( $booking_closed ) );	

		
		if ( wpb_is_admin() )
			$c .= '<div class="app-sc app-book-now-wrapper appointments-wrapper appointments-wrapper-admin">';
		else
			$c .= '<div class="app-sc app-book-now-wrapper appointments-wrapper">';

		$c .= $title_html;
		
		$c .= $this->calendar_subtitle( $logged, $notlogged );
		
		# Check availability
		$calendar = new WpBCalendar();
		$calendar->setup( array( 'disable_lazy_load'=>true, ) );
		$slot = new WpBSlot( $calendar, $slot_start, $slot_end );
	
		if ( $reason = $slot->why_not_free( ) ) {
			$button_text = $booking_closed;
			$class = 'app-disabled-button';
			# Show reason to admin as tooltip
			$title = 'title="'. esc_attr(WpBDebug::display_reason( $reason )) . '"';
		}
		else if ( !is_user_logged_in() && 'yes' === wpb_setting("login_required") ) {
			$button_text = str_replace( 'LOGIN_PAGE', '<a class="appointments-login_show_login" href="javascript:void(0)">'. __('Login','wp-base'). '</a>', $notlogged );
			$class = 'app-disabled-button';
			$title = 'title="'. $this->get_text('not_logged_message') . '"';
		}
		else{
			$button_text = $book_now;
			$class = '';
			$title = '';
		}
		
		$c .= '<div class="appointments-book-now" title="">';
 		$c .= '<button data-reason="'.$reason.'" class="app-book-now-button ui-button '.$class.'" '.$title.' >'. $button_text .
		'<input type="hidden" class="app_get_val" value="'.$slot->pack().'" />'.'</button>';
			
		$c .= '</div>'; # appointments-list
				
		$c .= '</div>'; # appointments-wrapper
		
		$page = $page ? $page : get_post( );
		
		if ( $page_obj = get_post( $page ) ) {
			$j = '';
			if ( !empty( $page_obj->ID ) ) {
				include_once( WPB_PLUGIN_DIR . '/includes/front-ajax.php' );
				$tt = BASE('Ajax')->get_post_excerpt( $page_obj->ID, '96,96', 'alignleft' );
				if ( !$tt )
					$tt = BASE('Ajax')->get_excerpt( $page_obj->ID, '96,96', 'alignleft' );
			
				if ( $tt )
					$j .= "$('.app-book-now-button input.app_get_val[value=\"".$slot->pack()."\"]').parent().qtip({content: {text:'".$tt."',title:'".esc_js($this->get_service_name( $slot->get_service() ))."'},hide:qtip_hide,position:qtip_pos,style:qtip_n_style});";
				
				$this->add2footer( $j );
			}
		}
		
		return $c;
	}

	/**
	 * Monthly calendar shortcode default args
	 * @since 2.0
	 * @return array
	 */		
	function calendar_monthly_args() {
		return array(
			'title'				=> $this->get_text('monthly_title'),
			'logged'			=> $this->get_text('logged_message'),
			'notlogged'			=> $this->get_text('not_logged_message'),
			'location'			=> 0,
			'service'			=> 0,
			'worker'			=> 0, 
			'long'				=> 0,
			'class'				=> '',
			'add'				=> 0,
			'start'				=> 0, 				// Previously "date". "auto" starts from first available date
			'display'			=> 'with_break',	// Since 3.0. full, with_break, minimum
			'_widget'			=> 0,				// Use as widget. Makes quick check
			'_no_timetable'		=> 0, 				// since 2.0. Disable timetable, e.g. for admin side 
			'_width'			=> 100,				// since 2.0, width of the wrapper relative to page content width
			'_force_min_time'	=> 0,				// since 2.0. Force min time to be used, so that it can catch bookings (on admin side)
			'_admin'			=> 0,
			'_menu'				=> '',				// since 3.0. WpBMenu object
		);
	}

	/**
	 * Shortcode function to generate monthly calendar
	 */	
	function calendar_monthly( $atts ) {
		global $bp;
		
		$atts = shortcode_atts( $this->calendar_monthly_args(), $atts, 'app_monthly_schedule' );
		
		extract( $atts  );
		
		$c  = '';
		$menu = $_menu instanceof WpBMenu ? $_menu : new WpBMenu( new WpBNorm( $location, $service, $worker ) );
		$c .= $menu->display_errors();
		
		// Calculate timestamp of the day - Including client offset
		$cl_offset = $this->get_client_offset( );

		$month_start = wpb_first_of_month( $this->calendar_start_ts( $start ) + $cl_offset, $add  );
			
		if ( '0' === (string)$title ) {
			$title_html = '';
		}
		else { 
			$title = str_replace( "START", date_i18n("F Y",  $month_start ), $title );
			$title_html = '<div class="app_title">' . esc_html( $this->calendar_title_replace( $title ) ) . '</div>';
		}
		
		if ( $_width < 0 || $_width > 100 )
			$_width = 100;
		
		$widget_add = $_widget ? "-widget" : ""; 
	
		if ( wpb_is_admin() )
			$c .= '<div class="app-sc appointments-wrapper'.$widget_add.' appointments-wrapper-admin '.$class.'" style="width:'.$_width.'%" >';
		else if ( is_object( $bp ) )
			$c .= '<div class="app-sc appointments-wrapper'.$widget_add.' app-bp '.$class.'">';
		else
			$c .= '<div class="app-sc appointments-wrapper'.$widget_add.' '.$class.'" >';
		
		$c .= $title_html;
		
		$c .= $this->calendar_subtitle( $logged, $notlogged );
		
		$c .= '<div class="appointments-list">';

		$cl_offset = $this->get_client_offset( );

		$date = $month_start ? $month_start : $this->_time + $cl_offset;
	
		$month_start = wpb_first_of_month( $date );
		
		$days	= (int)date('t', $month_start);
		$first	= (int)date('w', strtotime(date('Y-m-01', $month_start)));
		$last	= (int)date('w', strtotime(date('Y-m-' . $days, $month_start)));
		
		$ret = $c;
		if ( wpb_is_admin() )
			$ret .= '<div class="app_monthly_schedule_wrapper app_monthly_schedule_wrapper_admin">';
		else
			$ret .= '<div class="app_monthly_schedule_wrapper" data-display_mode="'.esc_attr($display).'">';
		
		$ret  = apply_filters( 'app_monthly_schedule_before_table', $ret );
		$ret .= "<table>";
		$ret .= $this->_get_table_meta_row_monthly('thead', $long);
		$ret .= '<tbody>';
		
		$ret = apply_filters( 'app_monthly_schedule_before_first_row', $ret );
		
		if ( $first > $this->start_of_week )
			$ret .= '<tr><td class="no-left-border" colspan="' . ($first - $this->start_of_week) . '">&nbsp;</td>';
		else if ( $first < $this->start_of_week )
			$ret .= '<tr><td class="no-left-border" colspan="' . (7 + $first - $this->start_of_week) . '">&nbsp;</td>';
		else
			$ret .= '<tr>';
		
		$todays_no = date("w", $this->_time + $cl_offset); // Number of today
		$time_table = '';
		$j  = '';
		
		$setup_args = array( 'disable_lazy_load'=>$_widget, 'admin'=>$_admin, 'force_min_time'=>intval($_force_min_time) );
		$calendar = new WpBCalendar();
		$calendar->setup( $setup_args );
		$is_daily = $this->is_daily( );

		for ( $i=1; $i<=$days; $i++ ) {
			$date		= date( 'Y-m-' . sprintf("%02d", $i), $month_start );
			$day_start	= strtotime( "{$date} 00:00", $this->_time + $cl_offset ); 
			$day_end	= strtotime( "{$date} 23:59:59", $this->_time + $cl_offset );
			$dow		= (int)date('w', strtotime($date, $this->_time + $cl_offset) );
			if ( $this->start_of_week == $dow ) 
				$ret .= '</tr><tr>';
				
			$code = array();
			$class_name = '';
			$calendar->has_appointment = false;
			
			if ( !$is_daily && $day_end > $this->_time + $cl_offset + ( $this->get_app_limit() + 1 )*86400 )
				$code[] = 3; # no_time
			// Daily quick checks are not applicable if user time zone is different
			else if ( !$cl_offset && !$_admin && $reason = $calendar->slot( $day_start, $day_end )->why_not_free( 'quick' ) ) {
				$code[] = $reason;
			}
			// If nothing else, then it must be free unless all time slots are taken
			else {
				if ( !$_widget && $is_daily ) {
					if ( $reason = $calendar->slot( $day_start, $day_end )->why_not_free( ) )
						$code[] = $reason; 
				}
				else {
					$setup_args = array_merge( $setup_args, array( 'display'=>$_widget ? 'minimum' : $display, ) );
					$calendar->setup( $setup_args );
					$out = $calendar->find_slots_in_day( $day_start, 1 );
					
					if ( $calendar->all_busy )
						$code[] = 1; # All slots busy
					else if ( !count( $out ) )
						$code[] = 17; # We do not know the reason
				}
			}
			
			if ( $calendar->has_appointment )
				$class_name .= ' has_appointment';

			// Check for today
			if ( $this->_time + $cl_offset >= $day_start && $this->_time + $cl_offset < $day_end )
				$class_name .= ' today';
			
			if ( empty( $code ) ) {
				// If there is no timetable use "Click to book" text
				$click_hint_sel = isset( $_no_timetable ) && $_no_timetable ? $this->get_text('click_to_book') : $this->get_text('click_to_select_date');
				$click_hint_text = esc_attr( $click_hint_sel );
			}
			else
				$click_hint_text = WpBDebug::display_reason( $code );
			
			if ( $is_daily )
				$class_name .= ' daily';
			
			$class_name = ( empty( $code ) ? 'free' : 'notpossible '.wpb_code2reason( max( $code ) ) ) . $class_name;

			$ret .= '<td class="'.$class_name.' app_day app_day_'.$day_start.' app_worker_'.$calendar->get_worker().'" data-title="'.date_i18n($this->date_format, $this->client_time($day_start)).'" title="'.esc_attr($click_hint_text).'"><p>'.$i.'</p>
			<input type="hidden" class="appointments_select_time" value="'.$day_start .'" />
			<input type="hidden" class="app_get_val" value="'.$calendar->slot( $day_start, $day_start + 24*3600 )->pack().'" />';

			// Add a link to add a new booking when clicked on the cell
			if ( strpos( $class_name, 'free') !== false && current_user_can(WPB_ADMIN_CAP ) )
				$ret .='<input type="hidden" class="app_new_link" value="'.admin_url('admin.php?page=appointments&amp;add_new=1&amp;app_id=0&amp;app_worker='.$calendar->get_worker().'&amp;app_timestamp='.$day_start).'" />';
			
			// For daily services, add our packed values
			if ( $is_daily ) {
				$ret .= '<input type="hidden" class="app_get_val" value="'.$calendar->slot( $day_start, $day_end )->pack().'" />';
			}
			
			$ret .= '</td>';
		}
		
		$calendar->save_cache();
		
		// Markup cleanup
		$ret = str_replace( '<tr></tr>', '', $ret );
		
		$colspan = wpb_mod( 6 - $last + $this->start_of_week, 7 );
		
		if ( 0 == $colspan )
			$ret .= '</tr>'; 
		else if ( 1 == $colspan )
			$ret .= '<td class="no-right-border">&nbsp;</td></tr>';
		else
			$ret .= '<td class="no-right-border" colspan="' . $colspan . '">&nbsp;</td></tr>';

		$ret = apply_filters( 'app_monthly_schedule_after_last_row', $ret );		
		$ret .= '</tbody>';
		$ret .= '</table>';
		$ret  = apply_filters( 'app_monthly_schedule_after_table', $ret );
		$ret .= '</div>';
		
		$cl = $is_daily ? 'daily' : '';
		
		if ( !$_widget && !$_no_timetable ) {
			$ret .= '<div class="app_timetable_wrapper '.$cl.'" title="">';
			$ret .= $time_table;
			$ret .= '</div>';
		}
	
		$ret .= '<div style="clear:both"></div>';
		
		$ret .= '</div>'; # appointments-list
				
		$ret .= '</div>'; # appointments-wrapper
		
		return $ret;		
	}
	
	/**
	 * Shortcode function to generate weekly calendar
	 */	
	function calendar_weekly( $atts ) {
		
		global $bp;
		
		$atts = shortcode_atts( array(
			'title'				=> $this->get_text('weekly_title'),
			'logged'			=> $this->get_text('logged_message'),
			'notlogged'			=> $this->get_text('not_logged_message'),
			'location'			=> 0,
			'service'			=> 0,
			'worker'			=> 0,				// Worker Id or 'all'
			'long'				=> 0,
			'class'				=> '',
			'add'				=> 0,
			'start'				=> 0,				// Previously "date"
			'display'			=> 'with_break',	// Since 3.0. full, with_break, minimum
			'_width'			=> 100,				// since 2.0, width of the calendar relative to page content width
			'_force_min_time'	=> 0,				// Force min time (in minutes) to be used, so that it can catch bookings (on admin side)
			'_inline'			=> 0,				// Displays bookings inline instead of tooltip
			'_daily'			=> 0,				// Can be used as daily too
			'_admin'			=> 0,				// For admin side usage
			'_menu'				=> '',				// since 3.0. WpBMenu object
			
		), $atts, 'app_schedule' );
		
		extract( $atts );
			
		$ret  = '';
		$menu = $_menu instanceof WpBMenu ? $_menu : new WpBMenu( new WpBNorm( $location, $service, $worker ) );
		$ret .= $menu->display_errors();

		// Calculate timestamp of the day - Including client offset
		$cl_offset = $this->get_client_offset( );

		$time = $this->calendar_start_ts( $start ) + $cl_offset + ($add * 7 * 86400);
		
		$start_of_calendar = $_daily ? $time : ( 6 == $this->start_of_week ? wpb_saturday( $time ) : wpb_sunday( $time ) + $this->start_of_week * 86400 );
		
		if ( '0' === (string)$title ) {
			$title_html = '';
		}
		else {	
			$title = str_replace( 
					array( "START_END", "START", "END", ),
					array( 
						$this->format_start_end( $start_of_calendar, $start_of_calendar + 6*86400 ),
						date_i18n($this->date_format, $start_of_calendar ),
						date_i18n($this->date_format, $start_of_calendar + 6*86400 ),
						),
					$title
			);
			$title_html = '<div class="app_title">' . esc_html( $this->calendar_title_replace( $title ) ) . '</div>';
		}
			
		if ( $_width < 0 || $_width > 100 )
			$_width = 100;

		/* Compacted parameters */
		$args = compact( array_keys( $atts ) );

		
		if ( wpb_is_admin() )
			$ret .= '<div class="app-sc appointments-wrapper appointments-wrapper-admin '.$class.'" style="width:'.$_width.'%" >';
		else if ( is_object( $bp ) )
			$ret .= '<div class="app-sc appointments-wrapper app-bp '.$class.'" >';
		else
			$ret .= '<div class="app-sc appointments-wrapper '.$class.'" >';
		
		$ret .= $title_html;
		
		$ret .= $this->calendar_subtitle( $logged, $notlogged );
			
        $ret .= '<div class="appointments-list">';
		if ( wpb_is_admin() )
			$ret .= '<div class="app_schedule_wrapper app_schedule_wrapper_admin">';
		else
			$ret .= '<div class="app_schedule_wrapper">';
 
		$ret .= $this->calendar_weekly_($time, $args );
		
		$ret .= '</div>';
		$ret .= '</div>'; # appointments-list
		$ret .= '</div>'; # appointments-wrapper
		
		return $ret;
	}

	/**
	 * Helper for weekly calendar
	 */	
	function calendar_weekly_( $time=false, $args ) {
		
		$args['_widget'] = false;
		extract ( $args );

		# Calculate timestamp of the day - Including client offset
		$cl_offset = $this->get_client_offset( );	# In seconds
		$date = $time ? $time : $this->_time + $cl_offset;
		
		$sunday = wpb_sunday( $date ); # Timestamp of first Sunday of $timestamp - Works for calendars where week starts on Sunday and Monday
		$week_start = 6 == $this->start_of_week ? wpb_saturday( $date ) -6*86400 : wpb_sunday( $date ); # Care for calendars starting on Saturday
		
		$ret = '';
		
		$tbl_class = $class;
		$tbl_class = $tbl_class ? "class='{$tbl_class}'" : '';
		
		$ret .= apply_filters( 'app_schedule_before_table', $ret );
		$ret .= "<table {$tbl_class}>";
		if ( !$_daily )
			$ret .= $this->_get_table_meta_row('thead', $long);
		$ret .= '<tbody>';
		
		$ret = apply_filters( 'app_schedule_before_first_row', $ret );

		$days_to_scan = $_daily ? (array)date( "w", $date ) : wpb_arrange( range(0,6), false, true );

		$calendar = new WpBCalendar();
		$calendar->setup( array( 
							'admin'			=> $_admin, 
							'inline'		=> $_inline,
							'force_min_time'=> $_force_min_time,
							'display'		=> $display,
							)
						);
		
		$cell_vals = array();
		
		foreach ( $days_to_scan as $key=>$i ) {
			# Get html and js for each day (one column)
			$one_col = $calendar->find_slots_in_day( $week_start+$i*86400 + $cl_offset );
			$cell_vals = $cell_vals + $one_col;
		}

		$days		= $_daily ? array( -1, date( "w", $date ) ) : wpb_arrange( range(0,6), -1, true ); // Arrange days acc. to start of week
		$no_results = true;
		$min_time	=	$this->get_min_time()*60;
		$start		= -1* $cl_offset;
		// $start = 0;
		
		for ( $time_val = $start; $time_val < $start + 86400; $time_val = $time_val + $min_time ) {
			$display_row = false;
			$ret_temp = '';
				
			foreach ( $days as $key=>$i ) {
				
				if ( $i == -1 ) {
					# Admin side uses fixed hours:mins column
					$hours_mins = $_admin ? date_i18n($this->time_format, $time_val) : ( $this->is_daily() ? '' : date_i18n($this->time_format, $this->client_time($time_val)));
					$f_time_val = apply_filters( 'app_weekly_calendar_from', $hours_mins, $time_val );

					$ret_temp .= "<td class='app-weekly-hours-mins ui-state-default'>".$f_time_val."</td>";
				}
				else {
					
					$slot_start = $time_val + $week_start+$i*86400;

					if ( isset( $cell_vals[$slot_start] ) ) {
						$slot = $cell_vals[$slot_start];
						$ret_temp .= $slot->weekly_calendar_cell_html(  );
						if ( !$slot->reason ) # Always show free
							$display_row_slot = true;
						else if ( ('with_break' === $display || 'full' === $display) && 1 === $slot->reason ) # Almost always show busy
							$display_row_slot = true;
						else if ( 'with_break' === $display && $slot->reason && 16 > $slot->reason ) 		# Show holiday and breaks if selected
							$display_row_slot = true;
							
						$display_row = apply_filters( 'app_display_row_slot', $display_row_slot, $slot ); # Custom rule per slot
					}
					else {
						# We are creating empty cells that are not generated by calendar_timetable to complete the table
						$ret_temp .= '<td class="notpossible app_dummy app_slot_'.$slot_start.'" data-title="'.date_i18n($this->dt_format, $this->client_time($slot_start)).'" title="'.esc_attr(WpBDebug::display_reason( 16 )).'"></td>';
					}
				}
			}
			
			if ( apply_filters( 'app_display_row', $display_row, $calendar ) ) { # Custom rule per calendar
				$no_results = false;
				$ret .= '<tr>'; 
				$ret .= $ret_temp;
				$ret .= '</tr>';
			}
		}
		
		$calendar->save_cache();
		
		if ( $no_results )
			$ret .= '<tr><td class="app_center" colspan="8">' . $this->get_text( 'no_free_time_slots' ) .'</td></tr>';

		$ret = apply_filters( 'app_schedule_after_last_row', $ret );		
		$ret .= '</tbody>';
		$ret .= '</table>';
		$ret = apply_filters( 'app_schedule_after_table', $ret );
		
		return $ret;			
	}

	/**
	* Get weekly calendar table header
	* @return string
	*/	
	function _get_table_meta_row ($which, $long) {
		
		$cells = '<th class="hourmin_column">&nbsp;</th>';
		foreach( wpb_arrange( range(0,6), false ) as $day ) {
			$day_name_long = strtolower( date('l', strtotime("Sunday +{$day} days")) );
			$day_name = $long ?  $this->get_text($day_name_long) : $this->get_text( $day_name_long.'_short' );
			// $cells .= '</th><th class="app_'.$day_name_long.'">' . $day_name;
			$cells .= '<th class="app_'.$day_name_long.'"><span class="normal">' . $day_name . '</span><span class="initial">' . $this->get_text( $day_name_long.'_initial' ) . '</span></th>';
		}
		// $cells .= '</th>';
		return apply_filters( 'app_weekly_table_meta_row', "<{$which}><tr class='ui-state-default'>{$cells}</tr></{$which}>", $which, $long );
	}
	
	/**
	* Get monthly calendar table header
	* @return string
	*/	
	function _get_table_meta_row_monthly ($which, $long) {
		$cells ='';
		foreach( wpb_arrange( range(0,6), false ) as $day ) {
			$day_name_long = strtolower( date('l', strtotime("Sunday +{$day} days")) );
			$day_name = $long ?  $this->get_text( $day_name_long ) : $this->get_text( $day_name_long.'_short' );
			$cells .= '<th class="app_'.$day_name_long.'"><span class="normal">' . $day_name . '</span><span class="initial">' . $this->get_text( $day_name_long.'_initial' ) . '</span></th>';
		}
		return "<{$which}><tr class='ui-state-default'>{$cells}</tr></{$which}>";
	}
	
	/**
	 * Format a field of confirmation form
	 * @since 3.0
	 * @return string
	 */
	function confirmation_line_html( $field_name, $value='' ) {
		$value_html = $value ? '<span class="app-conf-text">'. $value . '</span>' : '';
		return wpb_is_hidden($field_name) ? '' : '<label><span class="app-conf-title">'.$this->get_text($field_name). '</span>'.$value_html.'</label>';
	}

	/**
	 * Default confirmation shortcode atts
	 * @since 3.0
	 * @return array
	 */	
	function confirmation_atts() {
		return array(
			'confirmation_title' 	=> $this->get_text('confirmation_title'),
			'button_text'			=> $this->get_text('checkout_button'),
			'name'					=> $this->get_text('name'),
			'first_name'			=> $this->get_text('first_name'),
			'last_name'				=> $this->get_text('last_name'),
			'email'					=> $this->get_text('email'),
			'phone'					=> $this->get_text('phone'),
			'address'				=> $this->get_text('address'),
			'city'					=> $this->get_text('city'),
			'zip'					=> $this->get_text('zip'),	
			'state'					=> $this->get_text('state'),			
			'country'				=> $this->get_text('country'),			
			'note'					=> $this->get_text('note'),
			'remember'				=> $this->get_text('remember'),
			'use_cart'				=> 'inherit',
			'final_note_pre'		=> '',
			'final_note'			=> '',
			'fields'				=> '',		// Since 2.0. Default user fields can be sorted
			'layout'				=> 600,		// 1 (column), 2(columns) or a number equal or greater than 600. Since 3.0.
			'countdown'				=> 'auto',	// since 3.0 use countdown to refresh the page or not. 'auto' determines if a refresh is better
			'countdown_hidden'		=> 0,		// whether countdown will be hidden
			'countdown_title'		=> $this->get_text('conf_countdown_title'), // Use 0 to disable
			'continue_btn'			=> 1,
			'app_id'				=> 0,
			'_editing'				=> '',		// If equals 2, this is confirmation of Paypal Express payment form only
		);
	}

	/**
	 * Shortcode function to generate a confirmation box
	 */	
	function confirmation( $atts=array() ) {
		$post = get_post();
		$post_id = isset( $post->ID ) ? $post->ID : 0;
		$post_content = isset( $post->content ) ? $post->content : '';		

		extract( shortcode_atts( $this->confirmation_atts(), $atts, 'app_confirmation' ) );
		
		// Confirmation form can only be added to the page once
		// However, some page builders scan the content, but they do not reflect it; they serve thru post_meta
		if ( !empty( $this->conf_form_added ) && strpos( $post_content, '<div class="app-sc app-conf-wrapper' ) !== false ) {
			return WpBDebug::debug_text( __('On a page only one instance of Confirmation Form is allowed; this confirmation form has been skipped.', 'wp-base' ) );
		}
	
		$ecommerce_addon_active = 	BASE('MarketPress') && BASE('MarketPress')->is_app_mp_page( $post_id ) ||
									BASE('WooCommerce') && BASE('WooCommerce')->is_app_wc_page( $post_id );
									
		$use_cart = !$ecommerce_addon_active && BASE('ShoppingCart') && (('inherit' === $use_cart && 'yes' === wpb_setting( 'use_cart' )) || ('inherit' != $use_cart && $use_cart));
		
		$mobile_cl = wpb_is_mobile() ? ' app-mobile' : ' app-non-mobile';
		
		$title_cl = 'above' === wpb_setting( 'conf_form_title_position' ) && !$_editing ? ' above-input' : '';

		// If editing, bring data of the owner of the app
		if ( $_editing && $app_id && $app = $this->get_app( $app_id ) )
			$service = $this->get_service( $app->service );
		
		/* Start preparing HTML */
		$cart_items = BASE('Multiple')->get_cart_items();
		$style = $_editing || !empty( $cart_items ) ? '' : 'style="display:none"';

		$ret = '';
		$ret .= $_editing ? '<div class="app-sc app-conf-wrapper app-edit-wrapper'.$mobile_cl.$title_cl.'">' : '<div class="app-sc app-conf-wrapper'.$mobile_cl.$title_cl.'" '.$style.'>';
		
		/* General Notes */
		$ret  = apply_filters( 'app_edit_general_notes', $ret, $app_id, $_editing );

		/* Title */
		$ret .= '<fieldset><legend class="app-conf-title">';
		$ret .= $confirmation_title;
		$ret .= '</legend>';

		$use_button		= !wpb_is_mobile() && $continue_btn && !$ecommerce_addon_active && $use_cart ? true : false;
		$button_html 	= $use_button 
						? 
						'<button class="app-cont-btn ui-button ui-btn ui-corner-all ui-btn-icon-left">' . $this->get_text('continue_button') . '</button>' 
						: 
						'';
						
		$use_countdown			= $countdown === 1 || ('auto' === $countdown && ($ecommerce_addon_active||$use_cart||BASE('Packages')||BASE('Recurring')) ) ? true : false;
		$display_countdown		= $use_countdown && !$countdown_hidden;
		$countdown_style		= $display_countdown ? '' : ' style="display:none" ';
		$countdown_title_style	= $use_button ? 'style="visibility:hidden"' : 'style="display:none"';
		$countdown_title_html	= $display_countdown && "0" !== (string)$countdown_title 
								?
								'<div class="app_countdown_dropdown_title app_title" '.$countdown_title_style.'>' . $countdown_title . '</div>'
								:
								'';
								
		$two_column 			= $use_button && $display_countdown ? 'app_2column_continue' : '';						
		
		/* Countdown & Continue Shopping */
		if ( !$_editing && ( $use_button || $use_countdown ) ) {
			$ret .= '<div class="app-conf-continue clearfix">';
			if ( $use_countdown ) {
				$ret .= '<div class="'.$two_column.'">' . $button_html .'</div>';
				$ret .= '<div ' . $countdown_style. ' class="app_countdown-wrapper clearfix '. $two_column .'">' . 
						$countdown_title_html . 
						'<div class="app-conf-countdown clearfix" data-height="72" data-size="70"></div>' .
						'</div>';
			}
			else {
				$ret .= '<label>';
				$ret .= '<span class="app-conf-title">'. '&nbsp;' .  '</span>';
				$ret .= $button_html;
				$ret .= '</label>';
			}
			$ret .= '</div>';
		}
		
		$gr_class = $data = ''; // Default is column=1
		if ( !$_editing && 2 == $layout )
			$gr_class = ' app_2column';
		else if ( !$_editing && is_numeric( $layout ) && $layout > 599 ) {
			$gr_class = ' app-conf-fields-gr-auto';
			$data = 'data-edge_width="'.$layout.'"';
		}

		$ret .= '<div '.$data.' class="app-conf-fields-gr app-conf-fields-gr1'.$gr_class.'">';

		$ret = apply_filters( 'app_confirmation_before_service', $ret, $app_id, $_editing );

		/* Service */
		$ret .= '<div class="app-conf-service">';
		if ( 1 === $_editing )
			$ret = apply_filters( 'app_edit_service', $ret, $app_id );
		else if ( 2 === $_editing ) {
			$service_id = isset( $service->ID ) ? $service->ID : 0;
			$ret .= '<label><span class="app-conf-title">'. $this->get_text('service_name') .  '</span>'. $this->get_service_name( $service_id ) . '</label>';
		}
		$ret .= '</div>';
		
		/* Worker */
		$ret .= '<div class="app-conf-worker" '.$style.'>';
		if ( 1 === $_editing )
			$ret  = apply_filters( 'app_edit_worker', $ret, $app_id );
		else if ( 2 === $_editing ) {
			$worker_id = isset( $app->worker ) ? $app->worker : 0;
			if ( $worker_id ) 
				$ret .= '<label><span class="app-conf-title">'. $this->get_text('provider_name') .  '</span>'. $this->get_worker_name( $worker_id ) . '</label>';
		}
		$ret .= '</div>';
		
		$ret  = apply_filters( 'app_confirmation_after_worker', $ret, $app_id, $_editing );
		
		/* Start */
		$ret .= '<div class="app-conf-start">';
		if ( 1 === $_editing )
			$ret  = apply_filters( 'app_edit_start', $ret, $app_id );
		else if ( 2 === $_editing ) {
			$_start = isset( $app->start ) ? $app->start : 0;
			$ret .= '<label><span class="app-conf-title">'. $this->get_text('date_time') .  '</span>'. date_i18n($this->dt_format, strtotime( $_start ) ) . '</label>';
		}
		$ret .= '</div>';
		
		/* End */
		$ret .= '<div class="app-conf-end">';
		$ret .= '</div>';
		
		/* Duration/Lasts */
		$ret .= '<div class="app-conf-lasts" style="display:none">';
		$ret .= '</div>';

		/* Cart Contents */
		$ret .= '<div class="app-conf-details" style="display:none">';
		$ret .= '</div>';

		/* Price */
		$ret .= '<div class="app-conf-price clearfix" '.$style.'>';
		if ( 1 === $_editing )
			$ret  = apply_filters( 'app_edit_price', $ret, $app_id );
		else if ( 2 === $_editing ) {
			$_price = isset( $app->price ) ? $app->price : 0;
			$ret .= '<label><span class="app-conf-title">'. $this->get_text('price') .  '</span>'. wpb_format_currency( '', $_price ) . '</label>';
		}
		$ret .= '</div>';
		
		/* Deposit */
		$ret .= '<div class="app-conf-deposit" '.$style.'>';
		$ret .= '</div>';

		/* Paypal Amount */
		$ret .= '<div class="app-conf-amount" '.$style.'>';
		$ret .= '</div>';
		
		$ret  = apply_filters( 'app_confirmation_after_booking_fields', $ret, $app_id, $_editing );

		if ( !$_editing && wpb_setting('payment_method_position', 'after_booking_fields') === 'after_booking_fields' ) {
			$ret .= $this->payment_methods();
		}

		$ret .= '</div><!-- Close app-conf-fields-gr1 -->'; // Closing of app-conf-fields-gr1
		
		$ret .= '<div class="app-conf-fields-gr app-conf-fields-gr2 clearfix '.$gr_class.'">';
		
		$ret  = apply_filters( 'app_confirmation_before_user_fields', $ret, $app_id, $_editing );
		
		$allowed_fields  = apply_filters( 'app_confirmation_allowed_fields', $this->get_user_fields(), $app_id, $fields );

		/* Sort and filter User and UDF fields */
		$u_fields = array();
		if ( trim( $fields ) ) {
			$u_fields = explode( ",", wpb_sanitize_commas( $fields ) );
			$u_fields = array_map( 'strtolower', $u_fields );

			foreach ( $u_fields as $key=>$f ) {
				if ( !in_array( $f, $allowed_fields ) )
					unset( $u_fields[$key] );
			}
			$u_fields = array_filter( array_unique( $u_fields ) );
		}
		
		# If no special sorting set or nothing left, use defaults instead
		if ( empty( $u_fields ) ) {
			$u_fields = $allowed_fields;
			foreach( $u_fields as $key=>$f ) {
				if ( in_array( strtolower( $f ), $this->get_user_fields() ) && !wpb_setting("ask_".$f) )
					unset( $u_fields[$key] );
			}
		}
		$sorted_user_fields = $u_fields;

		# Get values of all user variables like $user_name, $user_email, etc
		extract( BASE('User')->get_app_userdata( $app_id, BASE('User')->read_user_id() ), EXTR_PREFIX_ALL, 'user' );
		
		# A non-functional form so that browser autofill can be used
		$ret .='<form class="app-conf-client-fields" onsubmit="return false;">';
			
		foreach ( $sorted_user_fields as $f ) {
			# Standard user fields
			if ( in_array( $f, $this->get_user_fields() ) ) {
				# For user defined field using filter hook, e.g, telefax
				if ( !isset( ${$f} ) )
					${$f} = wpb_get_field_name( $f );
				$ret .= '<div class="app-'.$f.'-field" '.$style.'>';
				$ret .= '<label><span class="app-conf-title">'. ${$f} . '<sup> *</sup></span><input type="text" placeholder="'.$this->get_text($f.'_placeholder').'" class="app-'.$f.'-field-entry '.$mobile_cl.'" value="'.esc_attr(${'user_'.$f}).'" /></label>';
				$ret .= '</div>';
			}
			else {
				# Other fields, i.e. udf
				$ret .= apply_filters( 'app_confirmation_add_field', '', $app_id, $f, $fields );
			}
		}
		
		$ret  = apply_filters( 'app_confirmation_before_note_field', $ret, $app_id, $_editing );

		$ret .= '<div class="app-note-field" '.$style.'>';
		$ret .= '<label><span class="app-conf-title">'. $note . '</span><textarea class="app-note-field-entry '.$mobile_cl.'">'.esc_textarea($user_note).'</textarea>';
		$ret .= '</label>';
		$ret .= '</div>';
		
		$ret .= '</form>';

		/* Extra Fields outside form may come here */
		$ret  = apply_filters( 'app_confirmation_after_user_fields', $ret, $app_id, $_editing );

		/* Remember me - Only for non logged in users - Default is checked */
		if ( !is_user_logged_in() && !$_editing ) {
			$ret .= '<div class="app-remember-field" style="display:none">';
			$ret .= '<label><span class="app-conf-title">&nbsp;</span>';
			$ret .= '<span class="app-conf-text">';
			$ret .= '<input type="checkbox" checked="checked" class="app-remember-field-entry" '.$user_remember.' />&nbsp;';
			$ret .= $remember;
			$ret .= '</span>';
			$ret .= '</label></div>';
		}
			
		if ( !$_editing && wpb_setting('payment_method_position') === 'after_user_fields' ) {
			$ret .= $this->payment_methods( );
		}

		$ret .= '</div><!-- Close app-conf-fields-gr1 -->'; # Closing of app-conf-fields-gr2
		$ret .= '<div style="clear:both"></div>';

		if ( !$_editing && wpb_setting('payment_method_position') === 'after_user_fields_full' ) {
			$ret .= $this->payment_methods( true );
		}

		/* Instructions or forms */
		// This will be filled by post confirmation
		$ret .= '<div class="app_gateway_form" style="display:none;">';

		$ret .= '</div>';

		/* Whatever to add after payment gateways */
		$ret  = apply_filters( 'app_confirmation_before_buttons', $ret, $app_id );
		// Add something using shortcode parameters
		$ret .= $final_note_pre;
		
		/* Submit, Cancel Buttons */
		$button_text = apply_filters( 'app_confirmation_button_text', $button_text, $post_id );
		$ret .= '<div class="app-conf-buttons">';
		$ret .= '<input type="hidden" class="app-conf-final-value" />';
		$ret .= '<input type="hidden" class="has-cart" value="'.( $use_cart ? 1:0  ) .'"/>';
		$ret .= '<input type="hidden" class="app-disp-price" value="" />';
		$ret .= "<input type='hidden' class='app-user-fields' value='".wp_json_encode( $sorted_user_fields )."' />";
		if ( $app_id )
			$ret .= '<input type="hidden" class="app-edit-id" value="'.$app_id.'"/>';
		$ret .= '<input type="hidden" name="app_editing_value" class="app_editing_value" value="'.$_editing.'"/>'; // Use this as a checkback of what function we are using
		$ret .= '<input type="hidden" name="app_post_id" class="app_post_id" value="'.$post_id.'"/>'; // Use this as a checkback of what function we are using
		$ret .= '<button class="app-conf-button ui-button ui-btn ui-btn-icon-left ui-icon-check">'.$button_text.'</button>';
		if ( !class_exists('WpBPro') || !((wpb_setting('conf_form_hide_cancel') === 'without_cart' && !$use_cart) || wpb_setting('conf_form_hide_cancel') === 'yes') )
			$ret .= '<button class="app-conf-cancel-button app-cancel-button ui-button ui-btn ui-btn-icon-left ui-icon-delete" >'.($use_cart && !$_editing ? $this->get_text('cancel_cart') : $this->get_text('cancel')).'</button>';
		$ret .= '</div>';
		$ret .= '<div class="app-conf-final-note">';
		$ret .= $final_note;
		$ret .= '</div>';
		$ret .= '</fieldset>';
		
		$ret .= '</div><!-- Close app-sc app-conf-wrapper -->'; # Close app-sc app-conf-wrapper
		
		$this->add2footer( 'user_fields=' .wp_json_encode( $sorted_user_fields ).';' );
		
		$this->conf_form_added = true;
		return $ret;
	}
	
	/**
	 * Prepares HTML for payment methods
	 * @return string
	 */
	 function payment_methods( $is_full_width=false ){
		global $app_gateway_active_plugins;
		// Allow limiting of gateways according to i.e. previous user preferences or user IP
		$app_gateway_active_plugins = apply_filters( 'app_confirmation_active_gateways', $app_gateway_active_plugins );
		// Check radio if it is the only option
		$is_single_gateway =  $app_gateway_active_plugins && 1 == count( $app_gateway_active_plugins ) ? ' checked="checked"' : '';
		$settings = wpb_setting();	
		
		$ret = '';
		
		if ( wpb_is_mobile() ) {
			$ret .= '<fieldset data-iconpos="right" data-role="controlgroup" data-theme="'.wpb_setting('swatch').'" data-type="vertical" class="app-payment-field" style="display:none">';
			$ret .= '<legend>'.__('Payment method', 'wp-base' ) .'</legend>';
			foreach ( (array)$app_gateway_active_plugins as $plugin ) {
				$ret .= '<input id="app-radio-'.$plugin->plugin_name.'" type="radio" '.$is_single_gateway.' class="app_choose_gateway" name="app_choose_gateway" value="'.$plugin->plugin_name.'" />';
				$ret .= '<label for="app-radio-'.$plugin->plugin_name.'">'. $plugin->public_name .'</label>';
			}
			$ret .= '</fieldset>';
		}
		else {
			$ret .= '<div class="app-payment-field clearfix '.($is_full_width ? 'full_width' : '').'" style="display:none">';
			$ret .= '<span class="app-payment-title">'. $this->get_text('pay_with') .'<sup> *</sup></span>';
			$ret .= '<div class="app-payment-inner clearfix">';
			foreach ( (array)$app_gateway_active_plugins as $plugin ) {
				$ret .= '<div class="app-payment-gateway-item app-'.$plugin->plugin_name.'">';
				$ret .= '<input type="radio" '.$is_single_gateway.' class="app_choose_gateway" name="app_choose_gateway" value="'.$plugin->plugin_name.'" />';
				$ret .= '<a href="javascript:void(0)">';
				if ( $plugin->method_img_url ) {
				  $ret .= '<img src="' . $plugin->method_img_url . '" alt="' . $plugin->public_name . '" />';
				}
				$ret .= '<span>'. $plugin->public_name .'</span></a>';
				$ret .= '</div>';
				// Instructions: Contents of this will be read by qtip
				// TODO: Add here debug text in case credentials are empty
				$ret .= '<div class="app-'.$plugin->plugin_name.'-instructions" style="display:none">';
				$instr = isset( $settings['gateways'][$plugin->plugin_name]['instructions'] ) ? $settings['gateways'][$plugin->plugin_name]['instructions'] :'';
				$ret .= $instr;
				$ret .= '</div>';
			}
			$ret .= '</div>';
			$ret .= '</div>';
		}

		return $ret;		
	}

	/**
	 * Shortcode function to generate pagination links. Includes legend area
	 */	
	function pagination( $atts ) {
		
		$default_is_select_date = wpb_is_mobile() ? 0 : 1;
	
		extract( shortcode_atts( array(
			'step'				=> 1,
			'unit'				=> 'week',		// Number, day, week or month
			'month'				=> 0,			// Deprecated. For backwards compatibility 
			'start'				=> 0,			// Previously "date"
			'disable_legend'	=> 0,		// since 2.0
			'select_date'		=> $default_is_select_date,
		), $atts ) );
		
		$time = $this->calendar_start_ts( $start );

		$c = '';

		// Legends
		if ( !$disable_legend && 'yes' === wpb_setting('show_legend') ) {
			$c .= '<div class="appointments-legend">';
			foreach ( $this->get_legend_items() as $class=>$name ) {
				$c .= '<div class="app-legend-div">' .$name . '</div>';
			}
			$c .= '<div style="clear:both;"></div>';
			foreach ( $this->get_legend_items() as $class=>$name ) {
				$c .= '<div class="app-legend-div app-legend-div-color '.$class.'">&nbsp;</div>';
			}
			$c .= '<div style="clear:both;"></div>';
			$c .= '</div>';
			
		}

		// Pagination
		$c .= '<div class="appointments-pagination">';
		
		// Older versions have month, but not range
		if ( $month )
			$unit = 'month';
		
		if ( 'week' === $unit ) {
			$prev = $time - ($step*7*86400); 
			$next = $time + ($step*7*86400);
			$prev_min = $this->_time - $step*7*86400;
			$next_max = $this->_time + ($this->get_app_limit() + 7*$step ) *86400;
			if ( $step > 1 ) {
				$month_week_next = $this->get_text('next_weeks');
				$month_week_previous = $this->get_text('previous_weeks');
			}
			else {
				$month_week_next = $this->get_text('next_week');
				$month_week_previous = $this->get_text('previous_week');
			}
		}
		else if ( 'month' === $unit ) {
			$prev = wpb_first_of_month( $time, -1 * $step );
			$next = wpb_first_of_month( $time, $step );
			$prev_min = wpb_first_of_month( $this->_time, -1 * $step );
			$next_max = wpb_first_of_month( $this->_time, $step ) + $this->get_app_limit() * 86400;
			if ( $step > 1 ) {
				$month_week_next = $this->get_text('next_months');
				$month_week_previous = $this->get_text('previous_months');
			}
			else {
				$month_week_next = $this->get_text('next_month');
				$month_week_previous = $this->get_text('previous_month');
			}
		}
		else if ( 'day' === $unit || 'number' === $unit ) {
			$prev = $time - ($step*86400); 
			$next = $time + ($step*86400);
			$prev_min = $this->_time - $step*86400;
			$next_max = $this->_time + ($this->get_app_limit() + $step ) *86400;
			$month_week_next = $this->get_text('next');
			$month_week_previous = $this->get_text('previous');
		}
		else
			return WpBDebug::debug_text( __('Check "Unit" parameter in Pagination shortcode','wp-base') ) ;
		
		$cl_has_select_date = $select_date ? ' has-select-date' : '';
		
		if ( $prev <= $prev_min )
			$hide_prev = " style='visibility:hidden'";
		else
			$hide_prev = "";
		
		if ( wpb_is_mobile() ) {
			$month_week_next = $this->get_text('next');
			$month_week_previous = $this->get_text('previous');
		}
			
		$c .= '<div class="app_previous '.$prev.$cl_has_select_date.'"'.$hide_prev.'>';
		$c .= '<a href="javascript:void(0)"><em class="app-icon icon-left-open"></em><span>'. $month_week_previous . '</span></a>';
		$c .= '<input type="hidden" class="app_prev_unit app_unit" value="'.$unit.'" />';
		$c .= '<input type="hidden" class="app_prev_step app_step" value="'.$step.'" />';
		$c .= '</div>';

		if ( $select_date )  {
			$c .= $this->select_date( array( 'title'=>0 ) );
		}

		if ( $next >= $next_max )
			$hide_next = " style='visibility:hidden'";
		else
			$hide_next = "";

		$c .= '<div class="app_next '.$next.$cl_has_select_date.'"'.$hide_next.'>';
		$c .= '<a href="javascript:void(0)"><span>'. $month_week_next . '</span><em class="app-icon icon-right-open"></em></a>';
		$c .= '<input type="hidden" class="app_next_unit app_unit" value="'.$unit.'" />';
		$c .= '<input type="hidden" class="app_next_step app_step" value="'.$step.'" />';
		$c .= '</div>';

		$c .= '<div style="clear:both"></div>';
		$c .= '</div>';

		return $c;
	}

	/**
	 * Open the dialog with confirmation text as standalone, e.g. after gateway return or by email confirmation
	 * @since 3.0
	 * @return none
	 */	
	function open_confirmation_dialog( $app ) {
		
		if ( !('paid' == $app->status || 'confirmed' == $app->status) || !BASE('Pro') || wpb_is_mobile()  ) {
			$this->add2footer( 'alert("'.$this->get_text('appointment_received').'");' );
		}
		else {
			$this->load_assets();
			$this->add_default_js( );
			$this->add2footer( '_app_.open_dialog('.			
					json_encode( array( 	
						'confirm_text'		=> $this->get_dialog_text( $app ),
						'confirm_title'		=> $this->get_dialog_title( $app ),
						'refresh_url'		=> ('paid' == $app->status || 'confirmed' == $app->status) ? (wpb_setting('refresh_url') ? wpb_setting('refresh_url') : home_url()) : -1,	
					) )
				.');
			');
		}
	}
	
	/**
	 * Return final confirmation dialog title
	 * @param $context	string	confirmation, pending
	 * @since 3.0
	 */	
	function get_dialog_title( $app, $context='confirmation' ) {
		return $this->_replace( apply_filters( 'app_'.$context.'_title', wpb_setting($context.'_title'), $app, $context.'_title' ), $app, $context.'_title' );
	}
	
	/**
	 * Return final confirmation dialog text
	 * @param $context	string	confirmation, pending
	 * @since 3.0
	 */	
	function get_dialog_text( $app, $context='confirmation' ) {
		return $this->_replace( apply_filters( 'app_'.$context.'_text', wpb_setting($context.'_text'), $app, $context.'_text' ), $app, $context.'_text' );
	}

	
/****************************************
* Methods for confirmation
*****************************************

	/**
	 * Handle confirmation of an appointment by the client using email link
	 * @since 3.0
	 */	
	function handle_confirm() {
		/* confirm by the link in email */
		if ( empty( $_GET['app_confirm'] ) || empty( $_GET['app_id'] ) || empty( $_GET['confirm_nonce'] ) )
			return;

		if ( 'yes' != wpb_setting('allow_confirm') ) {
			if ( isset( $_REQUEST['app_id'] ) && isset( $_REQUEST['confirm_nonce'] ) ) {
				if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
					die( json_encode( array('error'=>esc_js( $this->get_text('confirm_disabled') ) ) ) );
				else
					wpb_notice( 'confirm_disabled', 'error' );
			}

			return;
		}
		
		if ( !empty( $_GET['app_confirmed'] ) ) {
			$app = $this->get_app( $_GET['app_confirmed'] );
			if ( $app )
				$this->open_confirmation_dialog( $app );
			
			return;
		}
		
		if ( !empty( $_GET['app_re_confirm_login'] ) && !is_user_logged_in() ) {
			wpb_notice( $this->get_text('login_for_confirm') );
			return;
		}

		$app_id = $_GET['app_id'];
		$app = $this->get_app( $app_id );
		
		if ( empty( $app->created ) || 'pending' != $app->status ) {
			wpb_notice( 'not_possible', 'error' );
			return; # Appt deleted completely
		}
		
		if (  $_GET['confirm_nonce'] != $this->create_hash( $app, 'confirm' ) ) {
			wpb_notice( 'not_possible', 'error' );
			return;			
		}
	
		# Check owner of the app. If he is a WP user and not logged in, make him log in
		if ( !empty( $app->user ) ) {
			if ( is_user_logged_in() && $app->user != get_current_user_id() )
				return; // User is not owner

			# If extra safety is required, force re-login
			$reauth = defined( 'WPB_EXTRA_SAFETY' ) && WPB_EXTRA_SAFETY ? true : false;
			
			if ( !is_user_logged_in() || $reauth ) {
				# Come back to the same page after login/re-login
				$redirect = wpb_add_query_arg( 'app_re_confirm_login', $app_id, wp_login_url( esc_url_raw($_SERVER['REQUEST_URI']), $reauth ) );
				wp_redirect( $redirect );
				exit;
			}
		}
		
		if ( $this->change_status( 'confirmed', $app_id ) ) {
			$this->log( sprintf( __('Client %1$s confirmed appointment having ID: %2$s','wp-base'), BASE('User')->get_client_name( $app_id, $app, false ), $app_id ) );
			$this->maybe_send_message( $app_id, 'confirmation' );
			# If headers not sent we can open the dialog
			if ( false && !headers_sent() ) {
				$this->open_confirmation_dialog( $app );
				return;
			}
			else {
				$url = wpb_setting('refresh_url') ? wpb_setting('refresh_url') : home_url();
				wp_redirect( wpb_add_query_arg( 'app_confirmed', $app_id, $url ) );
				exit;
			}
		}
		else {
			# If failed (maybe already confirmed before), do something else here
			do_action( 'app_confirm_failed', $app_id );
		}
	}
	

}
	BASE()->add_hooks_front();
	
	if ( is_admin() )
		include_once( WPB_PLUGIN_DIR . '/includes/admin/base-admin.php' );
	else
		$GLOBALS['appointments'] = BASE();	// For backwards compatibility

}
else {
	add_action( 'admin_notices', '_wpb_plugin_conflict_own' );
}

