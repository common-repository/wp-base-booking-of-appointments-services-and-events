<?php
/**
 * WPB Front Ajax
 *
 * Handles ajax requests of front end
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBAjax' ) ) {

class WpBAjax {
	
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
		add_action( 'wp_ajax_update_calendars', array( $this, 'update_calendars' ) ); 			// Update calendars acc to pulldown menu selections
		add_action( 'wp_ajax_nopriv_update_calendars', array( $this, 'update_calendars' ) ); 	// Update calendars acc to pulldown menu selections
		add_action( 'wp_ajax_bring_timetable', array( $this, 'bring_timetable' ) ); 			// Get timetable html codes
		add_action( 'wp_ajax_nopriv_bring_timetable', array( $this, 'bring_timetable' ) ); 		// Get timetable html codes
		add_action( 'wp_ajax_bring_book_table', array( $this, 'book_table_ajax' ) ); 			// Get book table html codes
		add_action( 'wp_ajax_nopriv_bring_book_table', array( $this, 'book_table_ajax' ) ); 	// Get book table html codes
		add_action( 'wp_ajax_pre_confirmation', array( $this, 'pre_confirmation' ) ); 			// Get pre_confirmation results
		add_action( 'wp_ajax_nopriv_pre_confirmation', array( $this, 'pre_confirmation' ) ); 	// Get pre_confirmation results
		add_action( 'wp_ajax_pre_confirmation_update', array( $this, 'pre_confirmation_update' ) ); 		// Get pre_confirmation results in case update price
		add_action( 'wp_ajax_nopriv_pre_confirmation_update', array( $this, 'pre_confirmation_update' ) ); 	// Get pre_confirmation results in case update price
		add_action( 'wp_ajax_post_confirmation', array( $this, 'post_confirmation' ) ); 		// Do after final confirmation
		add_action( 'wp_ajax_nopriv_post_confirmation', array( $this, 'post_confirmation' ) );	// Do after final confirmation
		add_action( 'wp_ajax_bob_update', array( $this, 'update_user_fields' ) ); 				// Update conf form
		add_action( 'wp_ajax_app_show_children_in_tooltip', array( $this, 'show_children_in_tooltip' ) );
		add_action( 'wp_ajax_nopriv_app_show_children_in_tooltip', array( $this, 'show_children_in_tooltip' ) );
		add_action( 'wp_ajax_app_lsw_tooltip', array( $this, 'lsw_tooltip' ) );
		add_action( 'wp_ajax_nopriv_app_lsw_tooltip', array( $this, 'lsw_tooltip' ) );
	}

	/**
	 * Update calendars using ajax
	 * @since 2.0
	 */	
	function update_calendars() {
		
		if ( !defined( 'WPB_AJAX' ) )
			define( 'WPB_AJAX', true );
		
		$page = get_post( $_POST['page_id'] );
		
		if ( !empty( $_POST["page_id"] ) )
			wpb_set_session_val('app_post_id', $_POST["page_id"]);

		// Admin side scree base info. E.g app-pro_page_app_schedules
		$screen_base = isset( $_POST['screen_base'] ) ? $_POST['screen_base'] : '';
		
		// This can be coming from BP tab
		if ( !$page && strpos($_POST['bp_tab'],'wpb-book-me') !== false && !empty( $_POST['bp_displayed_user_id']) && BASE('BuddyPress') ) {
			$bp_book_me_tab = true;
			$worker_id = $_POST['bp_displayed_user_id'];
			$content = BASE('BuddyPress')->screen_content_app_book_me(true, $worker_id); // Return without do_shortcode
		}
		else {
			$bp_book_me_tab = false;
			$worker_id = $_POST['app_worker_id'];
			$content = $page ? $this->a->post_content( $page->post_content, $page, true ) : '';
		}
		
		$this->a->set_lsw( $_POST['app_location_id'], $_POST['app_service_id'], $worker_id );
		$calendar = new WpBCalendar(  );
	
		if ( $_POST["app_timestamp"] )
			$_GET["app_timestamp"] = $_POST["app_timestamp"];

		$time = !empty( $_GET["app_timestamp"] ) ? $_GET["app_timestamp"] : $this->a->_time;
			
		$result = $result_compact = $result_menus = $hide = $widgets = array();
		$result_first =  '';
		
		// Default values 
		$month = 0;
		$step = 2;
		$unit = 'week';
		$keep_pagination = false;
		$j = '';
		
		// If pagination buttons send unit and step info
		if ( !empty( $_POST['unit'] ) && !empty( $_POST['step'] ) ) {
			$step = (int)$_POST['step'];
			$unit = $_POST['unit'];
		}
		else {
			if ( $bp_book_me_tab || $page ) {
				if ( preg_match( '/' . get_shortcode_regex(array('app_next')) . '/', $content, $m ) ) {
					$atts = shortcode_parse_atts( $m[3] ); // Get attributes
					if ( isset( $atts['month'] ) )
						$month = (int)$atts['month'];
					if ( isset( $atts['step'] ) )
						$step = (int)$atts['step'];	
					if ( isset( $atts['unit'] ) )
						$unit = $atts['unit'];	
				}
				else
					$keep_pagination = true;
			}
			else if ( $this->a->app_name.'_page_app_schedules' == $screen_base ) {
				// This is for schedules page
				if ( !empty( $_REQUEST['tab'] ) ) {
					switch ( $_REQUEST['tab'] ) {
						case 'daily'	: 	$month = 0;$step = 1; $unit = 'day'; break;
						case 'weekly'	: 	$month = 0;$step = 1; $unit = 'week'; break;
						case '4weeks'	: 	$month = 0;$step = 4; $unit = 'week'; break;
						case 'monthly'	: 	$month = 1;$step = 1; $unit = 'month'; break;
						case '3months'	: 	$month = 1;$step = 3; $unit = 'month'; break;
						default	: 			$month = 0;$step = 1; $unit = 'week'; break;
					}
				}
				else {
					$month = 0; 
					$step = 1;
					$unit = 'week';
				}
			}
			else {
				// Profile page
				$month = 0; 
				$step = 4;
				$unit = 'week';
			}
		}

		// Older versions have month, but not unit
		if ( $month )
			$unit = 'month';

		if ( 'month' == $unit ) {
			$prev = wpb_first_of_month( $time, -1 * $step );
			$next = wpb_first_of_month( $time, $step );
			$prev_min = wpb_first_of_month( $this->a->_time, -1 * $step );
			$next_max = wpb_last_of_month( $this->a->_time + $this->a->get_app_limit() * 86400 );
		}
		else if ( 'day' == $unit || 'number' == $unit ) {
			$prev = $time - ($step*86400); 
			$next = $time + ($step*86400);
			$prev_min = $this->a->_time - $step*86400;
			$next_max = $this->a->_time + ($this->a->get_app_limit() + $step ) *86400;
		}
		else {
			// Week
			$prev = $time - ($step*7*86400); 
			$next = $time + ($step*7*86400);
			$prev_min = $this->a->_time - $step*7*86400;
			$next_max = $this->a->_time + ($this->a->get_app_limit() + 7*$step ) *86400;
		}
		
		if ( $prev <= $prev_min )
			$prev = "hide";

		if ( $next >= $next_max )
			$next = "hide";
		
		if ( $bp_book_me_tab || $page ) {
			
			if ( !wpb_is_mobile() ) {
				$content = preg_replace( '/' . get_shortcode_regex(array('app_is_mobile')) . '/','', $content );
			}
			else if ( wpb_is_mobile() ) {
				$content = preg_replace( '/' . get_shortcode_regex(array('app_is_not_mobile')) . '/','', $content );
			}
	
			# LSW menus 
			# Using app_menu_shortcodes, app_view_shortcodes and app_shortcodes filter, user defined shortcodes can be created, e.g. app_services_button_view
			$menus = apply_filters( 'app_menu_shortcodes', array( 'app_services' ) );
			
			$result_menus = array();
			while ( preg_match( '/' . get_shortcode_regex( $menus ) . '/', $content, $matches ) ) {
				if ( preg_match_all( '/' . get_shortcode_regex(array('app_hide')) . '/', $matches[0], $shortcode_arr, PREG_SET_ORDER ) ) {
					$menu_orig = $matches[0];
					$menu_hide_done = $matches[0];
					foreach( $shortcode_arr as $shortcode ) {
						$atts = shortcode_parse_atts( $shortcode[3] );
						// Also parse nested
						$html = $this->a->hide( $atts, $shortcode[5], 'app_hide', $matches[2] );
						$menu_hide_done = str_replace( array($shortcode[0],'[/'.$shortcode[2].']'), array($html,''), $menu_hide_done );
						$hide[] = $html;
						$content = str_replace( array($shortcode[0],'[/'.$shortcode[2].']'), '', $content );
					}
					
					$html = do_shortcode( $menu_hide_done );
					$result_menus[] = $html;
					$content = str_replace( $menu_orig, '', $content );
				}
				else {
					$html = do_shortcode( $matches[0] );
					$result_menus[] = $html;
					$content = str_replace( $matches[0], $html, $content );
				}
			}
			// The first one may have or may have not some nested menus
			$result_first = array_shift( $result_menus );
			
			if ( preg_match_all( '/' . get_shortcode_regex(array('app_hide')) . '/', $content, $shortcode_arr, PREG_SET_ORDER ) ) {
				foreach( $shortcode_arr as $shortcode ) {
					$atts = shortcode_parse_atts( $shortcode[3] );
					$atts = is_array( $atts ) ? $atts : array();
					$html = $this->a->hide( $atts, $shortcode[5] );
					$content = str_replace( $shortcode[0], $html, $content );
					$hide[] = $html;
				}
			}
			
			# Using app_menu_shortcodes, app_view_shortcodes and app_shortcodes filter, user defined shortcodes can be created, e.g. app_services_button_view
			$view_scodes = apply_filters( 'app_view_shortcodes', array('app_schedule','app_monthly_schedule','app_book_table','app_book_flex') );

			if ( preg_match_all( '/' . get_shortcode_regex( $view_scodes ) . '/', $content, $shortcode_arr ) ) {
				foreach( $shortcode_arr[0] as $shortcode ) {
					$result[] = do_shortcode( $shortcode );
				}
			}
			
			$compact_scodes = apply_filters( 'app_compact_shortcodes', array('app_book') );

			// Compact Book shortcode should be handled differently, because it uses appointments-wrapper more than once
			if ( preg_match_all( '/' . get_shortcode_regex( $compact_scodes ) . '/', $content, $shortcode_arr, PREG_SET_ORDER ) ) {
				foreach( $shortcode_arr as $shortcode ) {
					$atts = shortcode_parse_atts( $shortcode[3] );
					$atts = is_array( $atts ) ? $atts : array();
					$html = $this->a->book( array_merge( $atts, array( '_just_calendar'=>1 ) ) );
					$content = str_replace( $shortcode[0], $html, $content );
					$result_compact[] = $html;
				}
			}
			
			// Widgets
			if ( isset( $_POST['used_widgets'] ) && is_array( $_POST['used_widgets'] ) ) {
				$ids = $_POST['used_widgets'];
				$ops = get_option( 'widget_appointments_shortcode' );
				$scodes = array_keys( WpBConstant::shortcode_desc() );
				foreach ( $ids as $id ) {
					if ( isset( $ops[$id]['content'] ) && preg_match( '/' . get_shortcode_regex($scodes) . '/', $ops[$id]['content'], $irrelevant ) )
						$widgets[$id] = do_shortcode( $ops[$id]['content'] );
				}
			}
		}
		else if ( $screen_base ) {
			// This is for calendars page 
			if ( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] ) {
				switch ( $_REQUEST['tab'] ) {
					case 'daily'	: 	$wscodes = BASE('Schedules')->weekly_shortcodes(1, false, 'app-schedule-daily');
										$result[] = current( $wscodes );
										break;
										
					case 'weekly'	: 	$wscodes = BASE('Schedules')->weekly_shortcodes(1);
										$result[] = current( $wscodes );
										break;
										
					case '4weeks'	: 	foreach( BASE('Schedules')->weekly_shortcodes(4) as $scode ) {
											$result[] = $scode;
										}
										break;
										
					case 'monthly'	: 	$mscodes = BASE('Schedules')->monthly_shortcodes(1);
										$result[] = current( $mscodes );
										break;
										
					case '3months'	: 	foreach( BASE('Schedules')->monthly_shortcodes(3) as $scode ) {
											$result[] = $scode;
										}					
										break;
										
					default	: 			$wscodes = BASE('Schedules')->weekly_shortcodes(1);
										$result[] = current( $wscodes );
										break;
				}
			}
			else {
				$wscodes = BASE('Schedules')->weekly_shortcodes(1);
				$result[] = current( $wscodes );
			}		
		}
		else {
			// BP user page
			foreach ( BASE('Schedules')->weekly_shortcodes(4, $worker_id, 'app_2column') as $ws ) {
				$result[] = $ws;
			}
			
			foreach ( BASE('Schedules')->monthly_shortcodes(2, $worker_id, 'app_2column') as $ms ) {
				$result[] = $ms;
			}
		}

		// Retrieve saved javascript
		$j = $this->a->script;
		
		// Save Cache
		$calendar->save_cache( $page );
	
		die( json_encode( array('html'			=> $result, 
								'html_c'		=> $result_compact,
								'html_first'	=> $result_first,
								'html_single'	=> $result_menus,
								'html_hide'		=> $hide,								
								'prev'			=> $keep_pagination ? '' : $prev, 
								'next'			=> $keep_pagination ? '' : $next, 
								'j'				=> $j,
								'widgets'		=> $widgets,
								) 
							) 
						);
	}
	
	/**
	 * Handles ajax request for revealing available time tables for a day for monthly calendar
	 * @since 2.0
	 * @return json
	 */	
	function bring_timetable(){
		if ( isset( $_POST['app_value'] ) ) {
			$slot		= new WpBSlot( $_POST['app_value'] );
			$location	= $slot->get_location();
			$service	= $slot->get_service();
			$worker		= $slot->get_worker();
			$start		= $slot->get_start();
			$app_id		= $slot->get_app_id();
			
		}
		else
			wp_send_json_error( );
		
		$html = '';
		$calendar = new WpBCalendar( $location, $service, $worker );
		$calendar->setup( array( 'display'=>!empty( $_POST['display_mode'] ) ? $_POST['display_mode'] : '' ) );
		
		foreach ( $calendar->find_slots_in_day( $start ) as $slot_start=>$slot_obj ) {
			$html .= $slot_obj->monthly_calendar_cell_html( );
		}

		wp_send_json_success( $calendar->wrap_cells( $start, $html ) );
	}

	/**
	 * Bring a book table by ajax request and place it in html buffer
	 * Called by swipe js function 
	 */	
	function book_table_ajax() {
		if ( !isset( $_POST['start_ts'] ) )
			wp_send_json_error();
		
		$start = date('Y-m-d', $_POST['start_ts'] );
		$title = isset( $_POST['title'] ) ? html_entity_decode( $_POST['title'] ) : '';
		$logged = isset( $_POST['logged'] ) ? html_entity_decode( $_POST['logged'] ) : '';
		$notlogged = isset( $_POST['notlogged'] ) ? html_entity_decode( $_POST['notlogged'] ) : '';
		
		if ( isset( $_POST['type'] ) && 'book_flex' == $_POST['type'] && BASE('Pro') ) {
			$html = BASE('Pro')->book_flex( array(
										'range'		=> '1day',
										'start'		=> $start, 
										'title'		=> $title, 
										'logged'	=> $logged, 
										'notlogged'	=> 0,
										'swipe'		=> 0,			// We want a single result
										'mode'		=> 6,										
										)
			);
		}
		else {						
			$html = $this->a->book_table(array(
										'range'		=> '1day',
										'start'		=> $start, 
										'title'		=> $title, 
										'logged'	=> $logged, 
										'notlogged'	=> 0,
										'swipe'		=> 0,			// We want a single result
																				
										)
									);
		}
		
		die( json_encode( array( 'html'=>$html, ) ) );
	}
	
	/**
	 * Check and return necessary fields to the front end when there is a change (usually to update price)
	 * @return json object
	 */
	function pre_confirmation_update( ) {
		$this->pre_confirmation( true );
	}
	
	/**
	 * Check and return necessary fields to the front end
	 * @param update_price: Function is used for updating (price). Do not touch cart
	 * @return json object
	 */
	function pre_confirmation( $update=false ) {
		
		if ( !check_ajax_referer( 'front', false, false ) )
			die( json_encode( array('error'=>esc_js( $this->a->get_text('unauthorised') ) ) ) );

		global $wpdb;
		
		$has_cart = !empty( $_POST['has_cart'] ) ? true : false;

		// Clear previous cart items.
		if ( !$update && !$has_cart ) {
			BASE('Multiple')->empty_cart();
		}
		
		if ( !$update ) {
			BASE('Multiple')->check_cart();
		}
		
		if ( !empty( $_POST["deleted_value"] ) ) {
			BASE('Multiple')->remove_item( $_POST["deleted_value"] );
		}
		
		if ( !isset( $_POST["value"] ) ) {
			die( json_encode( array(
				'start'			=> $this->a->confirmation_line_html( 'date_time' ),
				'end'			=> $this->a->confirmation_line_html( 'end_date_time' ),
				'lasts'			=> $this->a->confirmation_line_html( 'lasts' ),
				'cart_contents'	=> $this->a->confirmation_line_html( 'details' ),
				'price'			=> $this->a->confirmation_line_html( 'price' ),
				'amount'		=> $this->a->confirmation_line_html( 'down_payment' ),
				'deposit'		=> $this->a->confirmation_line_html( 'deposit' ),
			)));
		}
		else {
			if ( is_array( $_POST["value"] ) ) {
				$value_arr = array_unique( $_POST["value"] );
				sort( $value_arr );
				$count = count( $value_arr );
			}
			else {
				$value_arr = (array)$_POST["value"];
				$count = 1;
			}
		}
		
		# Check if this request is coming from BP Book Me tab. Then we will not touch provider.
		$bp_book_me = !empty( $_POST['bp_tab'] ) && strpos( $_POST['bp_tab'], 'wpb-book-me' ) !== false ? true : false;
		
		# Allow addons to make additional check here
		do_action( 'app_pre_confirmation_check', $value_arr );
		
		$there_is_daily = false;
		$total_amount	= $total_price = $total_deposit = $total_list_price = $lasts = $end_max = 0;
		$new_value_arr	= $services_selected_pre = $workers_selected_pre = $datetime_arr = array();
		
		$reply_array	= apply_filters( 'app_pre_confirmation_reply_initial', array(), $value_arr );
		$value_arr		= apply_filters( 'app_pre_confirmation_modify_val', $value_arr );
		$is_multiple	= count( $value_arr ) > 1 || $has_cart ? true : false;
		$reply_array	= apply_filters( 'app_pre_confirmation_reply_pre', $reply_array, $value_arr );
		
		foreach ( $value_arr as $key=>$val ) {

			$slot	= new WpBSlot( $val );
			$service = $slot->get_service();
			$is_daily = $this->a->is_daily( $service );
			$app_id	= $slot->get_app_id();

			# Assign a worker at this stage so that we can show final price to the client
			# It is possible to have worker=0 at this stage, if client_selects_worker is selected
			if ( !($bp_book_me && $slot->get_worker() ) ) {
				$worker = $slot->assign_worker( $is_multiple );
			}
			
			# Create a new "value pack" with new worker
			$new_val = $slot->pack( );

			# Check availability
			if ( !$app_id && $reason = $slot->why_not_free( 'single' ) ) {
				$reason = strtok( $reason, " " ); // Take first word only
				
				switch ( $reason ) {
					case 'blocked':
					case 'now':		$text = $this->a->get_text('too_late'); break;
					case 'past':	$text = $this->a->get_text('past_date'); break;
					case 'busy':	$text = $this->a->get_text('already_booked'); break;
					default:		$text = $this->a->get_text('not_working'); break;
				}
				
				$debug_text = WpBDebug::is_debug() ? ' '. date_i18n( $this->a->dt_format, $slot->get_start() ). ' '. wpb_code2reason( $reason ) .' '. $val : '';
				die( json_encode( array("error"=> $text . $debug_text )));
			}
			
			if ( $has_cart && !$app_id ) {
				if ( !$app_id = BASE('Multiple')->add( $new_val ) ) {
					$this->a->log( $this->a->db->last_error );
					die(json_encode( array( 'error'=>current_user_can(WPB_ADMIN_CAP) ? sprintf( __('Booking cannot be saved. Last DB error: %s', 'wp-base' ), $this->a->db->last_error) : $this->a->get_text('error') ) ) );
				}
				
				$slot->set_app_id( $app_id );
				# Create a new "value pack" with new app_id
				$new_val = $slot->pack( );
			}

			$new_value_arr[] = $new_val;
			
			$pax				= apply_filters( 'app_pax', 1, $service, $slot );
			$price				= $slot->get_price( );
			$list_price			= $slot->get_list_price( );
			$total_price		+= $price * $pax;
			$total_list_price	+= $list_price * $pax;
			
			$services_selected_pre[] = apply_filters( 'app_pre_confirmation_service_name', $this->a->get_service_name( $service ), $val );
			
			$deposit = apply_filters( 'app_confirmation_deposit', $slot->get_deposit(), $new_val );
			
			# Client can select this: "In multi appointments, deposit is the maximum deposit and taken only once"
			if ( 'yes' === wpb_setting( 'deposit_cumulative' ) )
				$total_deposit += $deposit * $pax;
			else
				$total_deposit = max( $deposit * $pax, $total_deposit );
			
			$lasts += apply_filters( 'app_pre_confirmation_duration', wpb_get_duration( $service ), $new_val );
			
			// Conditions where we will not show workers selection menu
			if ( $worker && ( $bp_book_me || $is_multiple || 'no' != wpb_setting('client_selects_worker') ) )
				$workers_selected_pre[] = $this->a->get_worker_name( $slot->get_worker() );
			
			// Check if there any daily services			
			if ( $is_daily )
				$there_is_daily = true;
			
			$datetime_arr[$val] = date_i18n( $is_daily ? $this->a->date_format : $this->a->dt_format, $this->a->client_time($slot->get_start()) );
			
			$end_max = max( $end_max, $slot->get_end() );
		}
		
		/* Worker */
		$_workers_by_service = $this->a->get_workers_by_service( $service );
		$js = '';

		if ( !empty( $workers_selected_pre ) ) {
			$worker_html  = $this->a->confirmation_line_html( 'provider_name', implode( ", ", array_unique( $workers_selected_pre ) ) );
		} 
		else if ( !empty( $_workers_by_service ) && !$is_multiple && 'forced' == wpb_setting('client_selects_worker') && $this->a->get_nof_workers() ) {
			$worker_html  = '<label for="app_select_workers_conf"><span class="app-conf-title">'. $this->a->get_text('provider_name').  '</span>';
			// Run available workers to see who is available
			if ( !empty( $_workers_by_service ) ) {
				// Check each worker for availability
				$workers_pre = array();
				foreach ( $_workers_by_service as $_worker ) {
					$slot->set_worker( $_worker->ID );
					if ( $slot->why_not_free( 'single' ) )
						continue;
					else
						$workers_pre[$_worker->ID] = $this->a->get_worker_name( $_worker->ID );
				}
				if ( count( $workers_pre ) > 1 ) {
					if ( !wpb_is_mobile() ) {
						$js = '$(".app_select_workers_conf").multiselect({multiple:false,selectedList:1, minWidth:"60%",classes:"app_select_workers_conf app_ms"});';
					}
					$worker_html .= '<select tabindex="1" data-native-menu="false" data-theme="'.wpb_setting('swatch').'" id="app_select_workers_conf" class="app_select_workers_conf app_ms">';
					foreach ( $workers_pre as $wid => $wname ) {
						$worker_html .= '<option value="'.$wid.'">';
						$worker_html .= $wname;
						$worker_html .= '</option>';
						
						$_w = $this->a->get_worker( $wid );
						
						if ( !empty( $_w->page ) ) {
							$page_id = $_w->page;
							$tt = $this->a->get_post_excerpt( $page_id, '96,96', 'alignleft' );
							if ( !$tt )
								$tt = $this->a->get_excerpt( $page_id, '96,96', 'alignleft' );
						
							if ( $tt )
								$js .= "$('.app_select_workers_conf input[value=\"".$wid."\"]').parent().qtip({content: {text:'".$tt."',title:'".esc_js($this->a->get_worker_name( $wid ))."'},hide:qtip_hide,position:qtip_pos,style:qtip_n_style});";
						}
					}
					$worker_html .= '</select>';
				}
				else {
					$key = key($workers_pre);
					$worker_html .= $key ? $workers_pre[$key] : '';
				}
			}
			else
				$worker_html .= $this->a->get_text('not_working');
			
			$worker_html .= '</label>';
		}
		else
			$worker_html = '';
		
		/* Prices */
		WpBDebug::set( 'price_before_confirmation_filter', $total_price );
		
		# If an addon changes total price in a cart, it comes here (Extras, Seats, coupons)
		$total_list_price	= apply_filters( 'app_confirmation_total_list_price', $total_list_price, $new_value_arr );
		$total_price		= apply_filters( 'app_confirmation_total_price', $total_price, $new_value_arr );
		$total_amount		= apply_filters( 'app_confirmation_total_amount', $this->a->calc_downpayment( $total_price ), $new_value_arr, $total_price );
		# Before deposit addition, total amount cannot be greater than total price
		$total_amount 		= $total_amount > $total_price ?  $total_price : $total_amount;
		
		if ( 'yes' === wpb_setting( 'add_deposit' ) )
			$total_amount = $total_amount + $total_deposit;
		
		# Do not allow negative amounts
		$total_price		= max( 0, $total_price ); 
		$total_amount		= max( 0, $total_amount );
		$total_deposit		= max( 0, $total_deposit );
		$payment_expected 	= $total_amount > 0 && 'yes' === wpb_setting("payment_required");

		WpBDebug::set( 'final_price', $total_price );
		WpBDebug::set( 'list_price', $total_list_price );

		# If a discount is applied, highlight it
		$price_html = '';
		if ( !wpb_is_hidden('price') && $total_list_price > $total_price )
			$price_html = '<label><span class="app-conf-title">' . $this->a->get_text('price') . '</span><span class="app-conf-text app_old_price">'. wpb_format_currency( wpb_setting("currency"), $total_list_price ) . '</span>'.
			'<span title="'.WpBDebug::price_tt().'" class="app_new_price">'. wpb_format_currency( wpb_setting("currency"), $total_price ) . '</span><div style="clear:both"></div></label>';
		else if ( !wpb_is_hidden('price') && $total_price > 0 )
			$price_html = '<label><span class="app-conf-title">' . $this->a->get_text('price') . '</span><span title="'.WpBDebug::price_tt().'" class="app-conf-text app_current_price">'. wpb_format_currency( wpb_setting("currency"), $total_price ) . '</span><div style="clear:both"></div></label>';
		
		$booking_fields = array(
			'new_value'	=> $new_value_arr,
			'service'	=> wpb_is_hidden('service') ? '' : $this->a->confirmation_line_html( 'service_name', implode( ", ", array_unique($services_selected_pre) ) ),
			'worker'	=> wpb_is_hidden('provider') ? '' : $worker_html,
			'worker_js'	=> $js,
			'start'		=> $this->a->confirmation_line_html( 'date_time', current($datetime_arr) ),
			'end'		=> $this->a->confirmation_line_html( 'end_date_time', date_i18n( $there_is_daily ? $this->a->date_format : $this->a->dt_format, $end_max ) ),
			'lasts'		=> $lasts ? $this->a->confirmation_line_html( 'lasts', wpb_format_duration( $lasts ) ) : '',
			'price'		=> $price_html,
			'disp_price'=> wpb_format_currency( '', $total_price, true ),
			'deposit'	=> $total_deposit > 0 ? $this->a->confirmation_line_html( 'deposit', wpb_format_currency( wpb_setting("currency"), $total_deposit ) ) : '',
			'amount'	=> $payment_expected ? $this->a->confirmation_line_html( 'down_payment', wpb_format_currency( wpb_setting("currency"), $total_amount ) ) : '',
			'payment'	=> $payment_expected ? 'ask':'', 
		);
			
		die( wp_json_encode( apply_filters( 'app_pre_confirmation_reply', array_merge( $reply_array, $booking_fields ), $new_value_arr ) ) );
	}
	
	/**
	 * Make checks on submitted fields and save appointment
	 * @return json object
	 */
	function post_confirmation() {
		
		if ( !defined( 'WPB_AJAX' ) )
			define( 'WPB_AJAX', true );

		/* Primary Checks and arrangements*/
		if ( !check_ajax_referer( 'front',false,false ) )
			die( json_encode( array('error'=>esc_js( $this->a->get_text('unauthorised') ) ) ) );
		
		wpb_session_start();
		ob_start();

		/* Final login required check */
		if ( apply_filters( 'app_post_confirmation_login_check', ('yes' == wpb_setting('login_required') && !is_user_logged_in() ) ) )
			die( json_encode( array( 'error'=>$this->a->get_text('unauthorised') ) ) );
		
		/* Update GCal or whoever modifies booking availability */
		do_action( 'app_cron' );
	
		/* If editing, this $_POST will arrive, but Paypal Express uses value=2: */
		$is_editing = isset( $_POST['editing'] ) && 1 == $_POST['editing'] ? 1 : 0;

		/* If we are editing we will have an app_id > 0 */
		$app_id_edit = isset( $_POST["app_id"] ) && $is_editing ? $_POST["app_id"] : 0;

		/* If we are not editing, Double check whether val variable is emptied */
		if ( !$app_id_edit && empty( $_POST["value"] ) ) {
			$error = WpBDebug::is_debug() ? $this->a->get_text('error') : sprintf( __('POST["value"] is empty in %1$s line %2$d in version %3$s','wp-base'), basename(__FILE__), __LINE__, $this->a->version );
			die( json_encode( array("error"=>$error ) ) );
		}

		/* User data - Check and save meta, return normalized data and user_id */
		$data = BASE('User')->post_confirmation_handle( $app_id_edit );
		$user_id = key( $data );
		$sub_user_data = current( $data );

		# Addons make additional handling here
		# FEE finalizes ajax by itself - It does not return back here
		do_action( 'app_post_confirmation_handle', $app_id_edit, $sub_user_data );

		wpb_set_session_val('app_value', $_POST["value"]);
		
		# Post ID may be lost during payment steps. So lets save it in a session variable
		if ( !empty( $_POST["post_id"] ) )
			wpb_set_session_val('app_post_id', $_POST["post_id"]);

		if ( !$this->check_spam() )
			die( json_encode( array("error"=>apply_filters( 'app_spam_message',esc_js($this->a->get_text('spam'))))));

		/* Submitted booking data - Count of appts */
		if ( is_array( $_POST["value"] ) ) {
			$value_arr = array_unique( $_POST["value"] );
			rsort( $value_arr );
			$count = count( $value_arr );
		}
		else {
			$value_arr = (array)$_POST["value"];
			$count = 1;
		}
		
		do_action( 'app_post_confirmation_check', $value_arr );
		
		/* Check appt lower limit. In this case if it is zero */
		if ( !$count )				
			die( json_encode( array("error"=>apply_filters( 'app_too_less_message', sprintf( esc_js( $this->a->a->get_text('too_less') ), BASE('Multiple')->get_apt_count_min( $value_arr ) )))));
		
		/* Make max count check */
		if ( $count > 1 ) {
			// If multiple appts is not active picking more than 1 slot is not allowed
			if ( !BASE('Multiple')->is_active() ) {
				$error = WpBDebug::is_debug() ? $this->a->get_text('error') : sprintf( __('More than one time slot is submitted in %1$s line %2$d in version %3$s','wp-base'), basename(__FILE__), __LINE__, $this->a->version );
				die( json_encode( array("error"=>$error ) ) );
			}
			
			do_action( 'app_post_check_multiple', $value_arr );
		}
		
		/* Let addons modify the value, e.g. packages */
		$value_arr = apply_filters( 'app_post_confirmation_modify_val', $value_arr );
		$count = count( $value_arr );
		$is_multiple = $count > 1 ? true : false;

		/* Check and calculate monetary things */
		$total_price = $total_amount = $total_deposit = 0;
		$new_value_arr = array();
		$sel_payment_method = '';
		if ( 'yes' == wpb_setting("payment_required") ) {
			global $app_gateway_active_plugins;
			if ( isset( $_POST["app_payment_method"] ) )
				$sel_payment_method = $_POST["app_payment_method"];
			else if ( is_array( $app_gateway_active_plugins ) && 1 == count( $app_gateway_active_plugins ) )
				$sel_payment_method = $app_gateway_active_plugins[0]->plugin_name; 	// Check if there is a single gateway activated
		}
		$sel_payment_method = apply_filters( 'app_selected_payment_method', $sel_payment_method, $value_arr );
		
		foreach ( $value_arr as $val ) {

			$slot = new WpBSlot( $val );
			$service = $slot->get_service();
			$app_id = $slot->get_app_id();
			
			// Take booking to hold status: It will not count as reserved now
			BASE('Multiple')->hold( $app_id );
			
			$worker = $slot->assign_worker( $is_multiple, true );

			# Check assignment of workers
			# worker=0 is only allowed if service working hours cover it
			if( !$worker && !$slot->is_service_working( ) ) {
				// Unhold: Take back to In Cart - Nobody can book it yet until client fixes the selection, or session expires
				BASE('Multiple')->unhold( $app_id );
				die( json_encode( array("error"=> esc_js( $this->a->get_text('not_possible'). ' '. date_i18n( $this->a->dt_format, $slot->get_start() ) ))));
			}

			/* Create a new "value pack" with new worker */
			$new_val = $slot->pack( );
			$new_value_arr[] = $new_val;

			$pax = apply_filters( 'app_pax', 1, $service, $slot );
			$price = $slot->get_price( );
			$total_price += $price *$pax;

			/* In multi appointments, deposit calculus can be selected */
			$deposit = apply_filters( 'app_confirmation_deposit', $slot->get_deposit(), $new_val );
			if ( 'yes' == wpb_setting( 'deposit_cumulative' ) )
				$total_deposit += $deposit *$pax;
			else
				$total_deposit = max( $deposit, $total_deposit );
		}
		
		$total_price = apply_filters( 'app_confirmation_total_price', $total_price, $new_value_arr );
		$total_amount = apply_filters( 'app_confirmation_total_amount', $this->a->calc_downpayment($total_price), $new_value_arr, $total_price );
		// Before deposit addition, total amount cannot be greater than total price
		$total_amount = $total_amount > $total_price ?  $total_price : $total_amount;
		
		if ( 'yes'== wpb_setting( 'add_deposit' ) )
			$total_amount = $total_amount + $total_deposit;
		
		/* Do not allow negative amounts */
		$total_price = $total_price < 0 ? 0 : $total_price; 
		$total_amount = $total_amount < 0 ? 0 : $total_amount;
		$total_deposit = $total_deposit < 0 ? 0 : $total_deposit;

		/* Do total price check for addons modifying prices */
		do_action( 'app_post_confirmation_final_price_check', $new_value_arr, $total_price, $total_amount, $total_deposit );
		
		$payment_expected = 'yes' === wpb_setting("payment_required") && $total_amount ? true : false;

		if ( $payment_expected && !$sel_payment_method ) {
			BASE('Multiple')->unhold( $app_id );
			die( json_encode( array("error"=>apply_filters( 'app_method_error_message',esc_js($this->a->get_text('payment_method_error'))))));
		}
		
		/* Check availability */
		foreach ( $new_value_arr as $val ) {

			$slot = new WpBSlot( $val );
			$app_id = $slot->get_app_id();
		
			if ( $reason = $slot->why_not_free( 'single' ) ) {
				$reason = strtok( $reason, " " ); // Take first word only
				switch ( $reason ) {
					case 'blocked':
					case 'now':		$text = $this->a->get_text('too_late'); break;
					case 'past':	$text = $this->a->get_text('past_date'); break;
					case 'busy':	$text = $this->a->get_text('already_booked'); break;
					default:		$text = $this->a->get_text('not_working'); break;
				}
				BASE('Multiple')->unhold( $app_id );

				$debug_text = WpBDebug::is_debug() ? ' '. date_i18n( $this->a->dt_format, $start ). ' '. $reason .' '. $val : '';
				die( json_encode( array("error"=> $text . $debug_text )));
			}
		}

		$parent_id = 0; // If possible, the first appointment (which is starting last) will be parent
		$suggested_id = (int)$this->a->db->get_var( "SELECT MAX(ID) FROM " . $this->a->app_table . " " ) + $count; // Try to let parent app has highest ID
		
		/* Checks are ok. Do the last arrangements and save to database here */
		foreach ( $new_value_arr as $val ) {

			$slot = new WpBSlot( $val );
			$category = $slot->get_category();
			$service = $slot->get_service();
			$worker = $slot->get_worker();
			$app_id = $slot->get_app_id();
			$start = $slot->get_start();
			$end = $slot->get_end();
			
			# If price is zero or payment is not required, follow auto_confirm
			$status = !$payment_expected && 'yes' === wpb_setting("auto_confirm") ? 'confirmed' : 'pending'; 
			$status = apply_filters( 'app_post_confirmation_status', $status, $total_price, $slot );
			
			$data = apply_filters( 'app_post_confirmation_save_data', array(
					'ID'			=> 	$app_id ? $app_id : $suggested_id,
					'parent_id'		=>	$parent_id,
					'created'		=>	date("Y-m-d H:i:s", $this->a->_time),
					'user'			=>	$user_id,
					'location'		=>	$slot->get_location(),
					'service'		=>	$service,
					'worker'		=> 	$worker,
					'price'			=>	!$parent_id ? $total_price : 0,	// In multi, price and deposit are written to parent
					'deposit'		=>	!$parent_id ? $total_deposit : 0,
					'status'		=>	$status,
					'start'			=>	$this->a->is_daily($service) ? date("Y-m-d " . "00:00:00", $start) : date("Y-m-d H:i:s", $start) ,
					'end'			=>	$this->a->is_daily($service) ? date("Y-m-d " . "00:00:00", $end) : date("Y-m-d H:i:s", $end ),
					'seats'			=>	apply_filters( 'app_pax', 1, $service, $slot ),
					'payment_method'=>	$sel_payment_method,
				), $val );
	
			if ( $app_id )
				$result = $this->a->db->update( $this->a->app_table, $data, array( 'ID'=>$app_id ) );
			else {
				if ( !$result = $this->a->db->insert( $this->a->app_table, $data ) ) {
					// We might have a race condition. Forget parent having highest ID.
					$data['ID'] = 'null';
					$result = $this->a->db->insert( $this->a->app_table, $data );
				}
			}
			
			if ( !$result ) {
				$this->a->log( $this->a->db->last_error );
				BASE('Multiple')->unhold( $app_id );
				die( json_encode( array( 'error' => WpBDebug::is_debug() 
								? 
								sprintf( __('Booking cannot be saved. Last DB error: %s', 'wp-base' ), $this->a->db->last_error ) 
								: 
								$this->a->get_text('save_error') )	) 
				);
			}

			$insert_id = $app_id ? $app_id : $this->a->db->insert_id; // Save insert ID
			$suggested_id = $suggested_id - 1;
			
			if ( $category )
				wpb_update_app_meta( $insert_id, 'category', $category );
			

			# A new appointment is accepted, so clear cache
			wpb_flush_cache();
			
			wpb_set_session_val('app_id', $insert_id);
			
			if ( !$parent_id ) {
				$parent_id = wpb_set_session_val('app_order_id', $insert_id );
			}
				
			do_action( 'app_new_appointment', $insert_id, $sub_user_data );
			
		} /* End foreach */
		
		# Save last booked worker
		if ( $user_id && $worker && 'yes' === wpb_setting('preselect_latest_worker') )
			update_user_meta( $user_id, 'app_last_booked_worker', $worker );

		# Trigger send message for pending, payment not required cases */
		# If MP or WC is the selected method, skip this
		if ( 'marketpress' == $sel_payment_method || 'woocommerce' == $sel_payment_method ) {
			// Do something
		}
		else if ( !$payment_expected && 'pending' == $status ) {
			$this->a->maybe_send_message( $parent_id, 'notification' );
			$this->a->maybe_send_message( $parent_id, 'pending' );
		}
		else if ( $payment_expected && 'manual-payments' == $sel_payment_method && 'pending' == $status ) {
			$this->a->maybe_send_message( $parent_id, 'manual-payments' );
		}
		else if ( 'confirmed' == $status ) {
			$this->a->maybe_send_message( $parent_id, 'confirmation' );
		}

		/* Confirm/Pending text && title */
		$app = $this->a->get_app( $parent_id );
		if ( 'confirmed' == $status || 'paid' == $status ) { 
			$popup_text = BASE('Pro') && !wpb_is_mobile() ? $this->a->get_dialog_text( $app ) : '';
			$popup_title = $this->a->get_dialog_title( $app );
		}
		else {
			$popup_text = BASE('Pro') && !wpb_is_mobile() ? $this->a->get_dialog_text( $app, 'pending' ) : '';
			$popup_title = $this->a->get_dialog_title( $app, 'pending' );
		}
		
		// There is no form for manual payments, so it is skipped
		// Also MP and WC may allow zero priced services
		if ( ( $payment_expected && 'manual-payments' != $sel_payment_method ) || in_array( $sel_payment_method, array('marketpress','woocommerce' ) ) ) {
			global $app_gateway_active_plugins;
		
			$form = '';
			foreach ( (array)$app_gateway_active_plugins as $plugin ) {
				// Insert only form of the selected gateway
				if ( $sel_payment_method == $plugin->plugin_name ) {
					$form = $plugin->_payment_form_wrapper( $parent_id, wpb_get_session_val('app_post_id'), $total_amount );
					break;
				}
			}

			wpb_set_session_val('app_total_amount', $total_amount);
			
			// Connect to gateway. We are sure that payment method is set
			do_action( 'app_payment_submit_' . $sel_payment_method, $parent_id, '' );
			
			if ( $maybe_error = wpb_get_cart_error() )
				die( json_encode( array( "error"=> strip_tags( $maybe_error ) ) ) );

			die( json_encode(
				apply_filters( 'app_post_confirmation_reply',
					array(
						"cell"				=> $_POST["value"], 
						"app_id"			=> $parent_id,
						"is_editing"		=> $is_editing,
						"refresh"			=> 0,
						"price"				=> $total_amount,
						"f_amount"			=> str_replace( "AMOUNT", wpb_format_currency( '', $total_amount), $this->a->get_text('pay_now') ),
						'method'			=> $sel_payment_method,
						'form'				=> $form,
						'confirm_text'		=> $popup_text,
						'confirm_title'		=> $popup_title,
						),
						'payment_expected'
					)
				)
			);
		}
		else {
			
			BASE('Multiple')->empty_cart();
			
			die( json_encode(
				# refresh_url key can be used for custom redirect urls (e.g. depending on service)
				apply_filters( 'app_post_confirmation_reply',
					array(
						"cell"				=> $_POST["value"], 
						"app_id"			=> $parent_id,
						"is_editing"		=> $is_editing,
						"refresh"			=> 1,
						'method'			=> '',
						'confirm_text'		=> $popup_text,
						'confirm_title'		=> $popup_title,
						),
						'payment_not_expected'
					)
				)
			);
		}
	}

	/**
	 * Update conf form user fields with data from selected user
	 * since 2.0
	 */	
	function update_user_fields(  ) {
		$user_id = isset( $_POST['app_user_id'] ) ? $_POST['app_user_id'] : 0;
		
		if ( $user_id ) 
			die( json_encode( array( "result" => BASE('User')->get_app_userdata( 0, $user_id ) ) ) );
		else
			die( json_encode( array( 'error'=>1 ) ) );

	}

	/**
	 * Dynamically show payment in qtip content
	 * @since 2.0
	 */	
	function show_children_in_tooltip() {
		if ( empty( $_POST['app_id'] ) )
			wp_send_json( array( 'result' => esc_js(__('Unexpected error','wp-base')) ) );
		
		$atts = isset( $_POST['atts'] ) ? json_decode( wp_unslash($_POST['atts']) ) : array();
		$atts['_children_of'] = $_POST['app_id'];
		$atts['title'] = __('Connected bookings','wp-base');
		
		wp_send_json( array( 'result' => preg_replace( '%<thead(.*?)</thead>%', '', $this->a->listing( $atts ) ) ) );
	}
	
	/**
	 * Generate an excerpt from the selected service/worker page
	 * @since 1.0
	 * @return string
	 */	
	function get_excerpt( $page_id, $thumb_size, $thumb_class, $worker_id=0, $excerpt_length=55 ) {
		$page = get_post( $page_id );
		$text = empty( $page->post_content ) ? '' : $page->post_content;
		$text = $this->a->post_content( $text, $page );
		$text = wpb_strip_shortcodes( $text );
		$text = wp_trim_words( $text, apply_filters('app_excerpt_length', $excerpt_length) );
		$text = apply_filters( 'app_description_text', $text, $page_id, $worker_id, 'excerpt' );
		$text = html_entity_decode( $text );
		$text = str_replace(']]>', ']]&gt;', $text);
		
		$thumb = $this->get_thumbnail( $page_id, $thumb_size, $thumb_class, $worker_id );
		
		return apply_filters( 'app_excerpt', $thumb. $text, $page_id, $worker_id );
	}

	/**
	 * Get the post excerpt for the selected service/worker page
	 * @since 2.0
	 * @return string
	 */	
	function get_post_excerpt( $page_id, $thumb_size, $thumb_class, $worker_id=0, $excerpt_length=55 ) {
		$page = get_post( $page_id );
		$text = empty( $page->post_excerpt ) ? '' : $page->post_excerpt;
		$text = wpb_strip_shortcodes( $text );
		$text = apply_filters( 'app_description_text', $text, $page_id, $worker_id, 'post_excerpt' );
			
		$thumb = $this->get_thumbnail( $page_id, $thumb_size, $thumb_class, $worker_id );
		
		return apply_filters( 'app_excerpt', $thumb. html_entity_decode( $text ), $page_id, $worker_id );
	}

	/**
	 * Fetch content from the selected service/worker page
	 * @since 1.0
	 * @return string
	 */	
	function get_content( $page_id, $thumb_size, $thumb_class, $worker_id=0 ) {
		$page = get_post( $page_id );
		$text = empty( $page->post_content ) ? '' : $page->post_content;
		$text = $this->a->post_content( $text, $page );
		$text = wpb_strip_shortcodes( $text );
		$text = apply_filters( 'app_description_text', $text, $page_id, $worker_id, 'content' );
			
		$thumb = $this->get_thumbnail( $page_id, $thumb_size, $thumb_class, $worker_id );
		
		$text = apply_filters( 'app_pre_content', wpautop( wptexturize ( $text ) ), $page_id, $worker_id  );

		return apply_filters( 'app_content', $thumb. html_entity_decode( $text ), $page_id, $worker_id );
	}
	
	/**
	 * Get html code for thumbnail or avatar
	 * @since 1.0
	 * @return string
	 */	
	function get_thumbnail( $page_id, $thumb_size, $thumb_class, $worker_id ) {
	
		if ( $thumb_size && 'none' !== $thumb_size ) {
			if ( strpos( $thumb_size, 'avatar' ) !== false ) {
				if ( strpos( $thumb_size, ',' ) !== false ) {
					$size_arr = explode( ",", $thumb_size );
					$size = $size_arr[1];
				}
				else
					$size = 96;
				$thumb = get_avatar( $worker_id, $size );
				if ( $thumb_class ) {
					$thumb = str_replace( "class='", "class='".$thumb_class." ", $thumb );
					$thumb = str_replace( 'class="', 'class="'.$thumb_class.' ', $thumb );
				}
				$thumb = str_replace( "'",'"', $thumb );
			}
			else {
				if ( strpos( $thumb_size, ',' ) !== false )
					$size = explode( ",", $thumb_size );
				else
					$size = $thumb_size;
					
				$thumb = get_the_post_thumbnail( $page_id, $size, apply_filters( 'app_thumbnail_attr', array('class'=>$thumb_class) ) );
				
			}
		}
		else
			$thumb = '';
	
		return apply_filters( 'app_thumbnail', $thumb, $page_id, $worker_id );
	}
	
	/**
	 * Prepare HTML for location/service/worker descriptions to be displayed in tooltip
	 * 
	 * @return string
	 */
	 function lsw_tooltip(){
		 $id		= !empty( $_POST['id'] ) ? $_POST['id'] : 0;
		 $desc		= !empty( $_POST['desc'] ) ? $_POST['desc'] : 'excerpt';
		 $lsw		= !empty( $_POST['lsw'] ) ? $_POST['lsw'] : 'services';
		 $length	= !empty( $_POST['ex_len'] ) ? $_POST['ex_len'] : 55;
		 
		 switch ( $lsw ) {
			 case 'locations':	$var = $this->a->get_location( $id ); break;
			 case 'services':	$var = $this->a->get_service( $id ); break;
			 case 'workers':	$var = $this->a->get_worker( $id ); break;
		 }
		 
		 $thumb_size = apply_filters( 'app_thumbnail_size', '96,96', $var, $id );
		 $thumb_class = apply_filters( 'app_thumbnail_class', 'alignleft', $var, $id );
		 
		switch ( $desc ) {
			case 'none'		:		break;
			case 'excerpt'	:		$html = $this->get_excerpt( $var->page, $thumb_size, $thumb_class, $id, $length ); break;
			case 'post_excerpt'	:	$html = $this->get_post_excerpt( $var->page, $thumb_size, $thumb_class, $id, $length ); break;
			case 'content'	:		$html = $this->get_content( $var->page, $thumb_size, $thumb_class, $id ); break;
			default			:		$html = $this->get_excerpt( $var->page, $thumb_size, $thumb_class, $id, $length ); break;
		}
		
		wp_send_json( array( 'result' => $html ) );
	}
	
	/**
	 * Check for too frequent back to back apps
	 * return true means no spam
	 * @return bool
	 */
	function check_spam() {
		global $wpdb;
		if ( !wpb_setting("spam_time") || !$apps = $this->get_apps_from_cookie() )
			return true;
			
		if ( !is_array( $apps ) || empty( $apps ) )
			return true;
			
		// Get details of the appointments
		$q = '';
		foreach ( $apps as $app_id ) {
			// Allow only numeric values
			if ( is_numeric( $app_id ) )
				$q .= " ID=".$app_id." OR ";
		}
		$q = rtrim( $q, "OR " );
		
		$checkdate = date( 'Y-m-d H:i:s', $this->_time - wpb_setting("spam_time") );  
		
		$results = $this->db->get_results( "SELECT * FROM " . $this->app_table . 
					" WHERE created>'".$checkdate."' AND status='pending' AND (".$q.")  " );
		// A recent app is found
		if ( $results )
			return false;
		
		return true;
	}
	
}
	BASE('Ajax')->add_hooks();
}