<?php
/**
 * WPB Payment Gateway Addons
 *
 * Parent class & register functions for payment gateways
 *
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if(!class_exists('WpB_Gateway_API')) {

  class WpB_Gateway_API {

    //private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
    public $plugin_name = '';

    //name of your gateway, for the admin side.
    public $admin_name = '';

    //public name of your gateway, for lists and such.
    public $public_name = '';

    //url for an image for your checkout method. Displayed on method form
    public $method_img_url = '';

    //url for an submit button image for your checkout method. Displayed on checkout form if set
    public $method_button_img_url = '';

    //whether or not ssl is needed for checkout page
    public $force_ssl = false;

    //always contains the url to send payment notifications to if needed by your gateway. Populated by the parent class
    public $ipn_url;
	
	public $checkout_error = false;

	/**
     * Constructor
     */	
	function __construct() {
		
		$this->a = BASE();
	 
		$this->checkout_error = false;	 

		$this->_generate_ipn_url();

		$this->on_creation();

		if( !empty( $_GET['app_paymentgateway'] ) ) {
			do_action( 'app_handle_payment_return_' . $_GET['app_paymentgateway'] );
		}

		add_action( 'app_gateway_settings', array($this, 'gateway_settings_box') );
		add_filter( 'app_gateway_settings_filter', array($this, 'process_gateway_settings') );
		add_action( 'wp_ajax_nopriv_app_checkout_return_' . $this->plugin_name, array( $this, 'process_checkout_return' ) );
		add_action( 'wp_ajax_app_checkout_return_' . $this->plugin_name, array( $this, 'process_checkout_return' ) );
		add_action( 'wp_ajax_nopriv_app_ipn_' . $this->plugin_name, array($this, 'process_ipn_return')); // Send Paypal to IPN function
		// add_action( 'wp_ajax_app_ipn_' . $this->plugin_name, array($this, 'process_ipn_return')); 	// Send Paypal to IPN function
		add_action( 'app_shortcode_found', array( $this, 'is_conf_page' ) );							// Set a flag if this is a conf page
		add_action( 'template_redirect', array( $this, 'force_ssl'), 2 );								// Check if SSL is *forced*
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );							// Print error/success message in js alert
		add_filter( 'app_plugin_url', array( $this, 'force_plugin_url' ) );								// Force https
		add_action( 'app_addon_settings_link', array( $this, 'settings_link' ) );						// Add settings link to Addons page
	}
	

    /****** Below are the public methods you may overwrite via a plugin ******/

    /**
     * Runs when your class is instantiated. Use to setup your plugin instead of __construct()
     */
    function on_creation() {
    }

	/**
     * Places a wrapper around payment form
     */	
	function _payment_form_wrapper($app_id, $post_id, $amount) {

		$content = $this->payment_form($app_id, $post_id, $amount);

		return $content;
    }

    /**
     * Return fields you need to add to the payment screen, like your credit card info fields.
     *
     * @param integer $app_id. ID of the Appointment for which payment would be done. Cannot be zero at this point.
     * @param integer $post_id. ID of the Make an Appointment post/page
	 * @param float $amount. Total full or down payment amount 
     */
    function payment_form($app_id, $post_id, $amount) {
    }

    /**
     * Use this to process any fields you added. Use the $_POST global,
     *
     * @param integer $app_id. ID of the Appointment for which payment would be done. Cannot be zero at this point.
     * @param integer $post_id. ID of the Make an Appointment post/page
     */
	function process_payment_form($app_id, $post_id) {
    }

    /**
     * Return the chosen payment details here for final confirmation. You probably don't need
     *  to post anything in the form as it should be in your $_SESSION var already.
     *
     * @param integer $app_id. ID of the Appointment for which payment would be done. Cannot be zero at this point.
     * @param integer $post_id. ID of the Make an Appointment post/page
     */
	function confirm_payment_form($app_id, $post_id) {
    }

    /**
     * Use this to do the final payment. Create the order then process the payment. If
     *  you know the payment is successful right away go ahead and change the order status
     *  as well.
     *
     * @param integer $app_id. ID of the Appointment for which payment would be done. Cannot be zero at this point.
     * @param integer $post_id. ID of the Make an Appointment post/page
     */
	function process_payment($app_id, $post_id) {
    }

    /**
     * Runs before page load incase you need to run any scripts before loading the success message page
     */
	function order_confirmation($order) {
    }

	/**
     * Echo a settings meta box with whatever settings you need for you gateway.
     *  Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
     *  You can access saved settings via $settings array.
     */
	function gateway_settings_box($settings) {

    }

    /**
     * Filters posted data from your settings form. Do anything you need to the $settings['gateways']['plugin_name']
     *  array. Don't forget to return!
     */
	function process_gateway_settings($settings) {

		return $settings;
    }

	/**
     * Use to handle any payment returns to the ipn_url. Do not display anything here. If you encounter errors
     *  return the proper headers. Exits after.
     */
	function process_ipn_return() {

    }

/* Not to be overwritten */

	/**
     * Adds a settings link
     */
	public static function settings_link( $filename ) {
		
		if ( $filename ==  pathinfo(__FILE__, PATHINFO_FILENAME) )
			echo ' | <a href="'.admin_url( 'admin.php?page=app_monetary&amp;tab=gateways#'.$filename ).'">'. __('Settings', 'wp-base' ) . '</a>';
	}

	/**
	 * Try to find $app_id from $_POST or session
	 */	
	function find_app_id(){
		$app_id = wpb_get_session_val('app_order_id');
		
		if ( !$app_id )
			$app_id = wpb_get_session_val('app_id');
		
		if ( !$app_id )
			$app_id = !empty( $_POST['app_id'] ) ? $_POST['app_id'] : 0;
		
		return $app_id;
	}

	/**
     * Find return url
     */	
	function return_url( ) {
		global $post;
		if ( !$ret = wpb_setting("refresh_url") ) {
			$ret = get_permalink( wpb_find_post_id() );
		}
		# Never let an undefined page, just in case
		if ( empty( $ret ) )
			$ret = esc_url_raw( $_SERVER['REQUEST_URI'] );
		
		// For backwards compatibility		
		$ret = apply_filters( 'app_paypal_return', $ret );
		$ret = apply_filters( 'app_payment_gateway_return', $ret );
		
		return $ret;
	}

	/**
     * Automatically called if this is a conf page, i.e. it contains [app_book or [app_confirmation
     */	
	function is_conf_page( $context ){
		if ( 'confirmation' != $context )
			return;
		
		$this->is_conf_page = true;
	}

	/**
     * Check if this is force SSL and redirect to https if it is so
	 * Before the_posts action, it always returns false
     */	
	function is_force_ssl( ){
		if ( !empty( $this->is_conf_page ) ) {
			$settings = wpb_setting();
			global $app_gateway_active_plugins;
			// We do not know which gateway will be selected. So we make https if any active gateway forces SSL
			foreach ( (array)$app_gateway_active_plugins as $code=>$plugin ) {
				$name = $plugin->plugin_name;
				if ( isset( $settings['gateways'][$name]['is_ssl'] ) && $settings['gateways'][$name]['is_ssl'] ) {
					return true;
					break;
				}
			}
		}
		return false;
	}
	
	/**
     * Forces the whole Appt page to be SSL
     */	
	function force_ssl( $post_id = null ){
		if( is_admin() || !$this->is_force_ssl() )
			return;

		$post_id = empty( $post_id ) ? get_the_ID() : $post_id;
		$target = get_permalink( $post_id );
		$goto = str_replace( 'http://', 'https://', $target );
		// Do nothing if already https
		if ( $target != $goto ) {
			if( !empty( $_GET ) ) {
				$goto .= '?' . http_build_query($_GET);
			}
			wp_redirect( $goto, 301 );
			exit;
		}
	}
	
	/**
     * Make plugin url protocol agnostic if this is a Force SSL page
     */	
	function force_plugin_url( $url ) {
		
		if ( $this->is_force_ssl() ) {
			$url = str_replace( 'http:', '', WPB_PLUGIN_URL );
		}

		return $url;
	}
		
	/**
     * Populates IPN url var
     */	
	 function _generate_ipn_url() {
		$this->ipn_url = admin_url( 'admin-ajax.php?app_paymentgateway='. $this->plugin_name );
    }

	/**
     * Returns the HTML of a standard CC form
     */	
	function standard_cc_form( $app_id ) {
		global $current_user;
		$content = '';
		
		$non_mobile_cl = wpb_is_mobile() ? '' : ' app-non-mobile';

		$email = $name = $address1 = $address2 = $city = $state = $zip = $country = $phone = '';
		// Combine user and app data here, priority being on app data
		// extract( array_merge( array_filter($this->a->get_app_userdata( $app_id, $current_user->ID )), array_filter($this->a->get_app_userdata( $app_id ) ) ) );
		extract( BASE('User')->get_app_userdata( $app_id, $current_user->ID ) );
		$address1 = $address;
				
		$content .= '<input type="hidden" class="app-cc-submit" value="'.$this->plugin_name.'" />
					<input type="hidden" class="app_id" value="'.$app_id.'" />
					
			<form onsubmit="javascript:return false;">
			<div class="app_billing_line">
				<label><span>'.__('Full Name:', 'wp-base').'<sup> *</sup></span>
				'.apply_filters( 'app_checkout_error_name', '' ).'
				<input name="name" type="text" class="app_cc_name'.$non_mobile_cl.'" value="'.esc_attr($name).'" /> </label>
			</div>

			<div class="app_billing_line">
				<label><span>'.__('Credit Card Number:', 'wp-base').'<sup> *</sup></span>
				'.apply_filters( 'app_checkout_error_card_num', '' ).'
				<input name="card_num" onkeyup="_app_.cc_card_pick(\'#cardimage\', \'#card_num\');"
				id="card_num" class="card-number app_cc_number credit_card_number input_field noautocomplete'.$non_mobile_cl.'"
				type="text" maxlength="22" />
				</label>
				<div class="hide_after_success nocard cardimage"  id="cardimage" style="background: url('.$this->a->plugin_url.'/images/card_array.png) no-repeat;"></div></label>
			</div>

			<div class="app_billing_line">
				<div class="app_billing_line_inner"><span class="'.$non_mobile_cl.'">'.__('Expiration Date:', 'wp-base').'<sup> *</sup></span>
				'.apply_filters( 'app_checkout_error_exp', '' ).'
					<label class="app-cc-label" for="exp_month">'.__('Month', 'wp-base').'
						<select data-theme="'.wpb_setting('swatch').'" name="exp_month" class="app_cc_exp_month'.$non_mobile_cl.'" id="exp_month">
						'.$this->_print_month_dropdown().'
						</select>
					</label>
					<label class="app-cc-label" for="exp_year">'.__('Year', 'wp-base').'
						<select data-theme="'.wpb_setting('swatch').'" name="exp_year" class="app_cc_exp_year'.$non_mobile_cl.'" id="exp_year">
						'.$this->_print_year_dropdown('', true).'
						</select>
					</label>
				</div>
			</div>

			<div class="app_billing_line">
				<label><span>'.__('Security Code:', 'wp-base').'<sup> *</sup></span>
				'.apply_filters( 'app_checkout_error_card_code', '' ).'
				<input id="card_code" name="card_code" class="app_cc_cvv input_field noautocomplete'.$non_mobile_cl.'"
				type="text" maxlength="4" /></label>
			</div>
			</form>

		';
      
		return $content;
	}

	/**
     * Returns the HTML of a standard CC form + Billing form
     */	
	function standard_billing_form( $app_id ) {
		global $current_user;
		$content = '';

		$email = $name = $address1 = $address2 = $city = $state = $zip = $country = $phone = '';
		// Combine user and app data here, priority being on app data
		extract( array_merge( array_filter($this->a->get_app_userdata( 0, $current_user->ID )), array_filter($this->a->get_app_userdata( $app_id ) ) ) );
		$address1 = $address;
				
		$non_mobile_cl = wpb_is_mobile() ? ' app-mobile' : ' app-non-mobile';
 
		$content .= '<input type="hidden" class="app-cc-submit" value="'.$this->plugin_name.'" />
					<input type="hidden" class="app_id" value="'.$app_id.'" />
		
			<div class="app_billing_line">
				<label><span>'.$this->a->get_text('cc_email').'<sup> *</sup></span>
				'.apply_filters( 'app_checkout_error_email', '' ).'
				<input name="email" type="text" class="app_cc_email'.$non_mobile_cl.'" value="'.esc_attr($email).'" /></label>
			</div>

			<div class="app_billing_line">
				<label><span>'.$this->a->get_text('cc_name').'<sup> *</sup></span>
				'.apply_filters( 'app_checkout_error_name', '' ).'
				<input name="name" type="text" class="app_cc_name'.$non_mobile_cl.'" value="'.esc_attr($name).'" /> </label>
			</div>

			<div class="app_billing_line">
				<label><span>'.$this->a->get_text('cc_address1').'<sup> *</sup></span>
				'.apply_filters( 'app_checkout_error_address1', '' ).'
				<input name="address1" type="text" class="app_cc_address1'.$non_mobile_cl.'" value="'.esc_attr($address1).'" placeholder="'.__('Street address, P.O. box, company name, c/o', 'wp-base').'"/>
				</label>
			</div>

			<div class="app_billing_line">
				<label><span>'.$this->a->get_text('cc_address2').'&nbsp;</span>
				<input name="address2" type="text" class="app_cc_address2'.$non_mobile_cl.'" value="'.esc_attr($address2).'" placeholder="'.__('Apartment, suite, unit, building, floor, etc.', 'wp-base').'"/>
				</label>
			</div>

			<div class="app_billing_line">
				<label><span>'.$this->a->get_text('cc_city').'<sup> *</sup></span>
				'.apply_filters( 'app_checkout_error_city', '' ).'
				<input name="city" type="text" class="app_cc_city'.$non_mobile_cl.'" value="'.esc_attr($city).'" /></label>
			</div>

			<div class="app_billing_line">
				<label><span>'.$this->a->get_text('cc_state').'</span>
				'.apply_filters( 'app_checkout_error_state', '' ).'
				<input name="state" type="text" class="app_cc_state'.$non_mobile_cl.'" value="'.esc_attr($state).'" /></label>
			</div>

			<div class="app_billing_line">
				<label><span>'.$this->a->get_text('cc_zip').'<sup> *</sup></span>
				'.apply_filters( 'app_checkout_error_zip', '' ).'
				<input id="mp_zip" name="zip" type="text" class="app_cc_zip'.$non_mobile_cl.'" value="'.esc_attr($zip).'" /></label>
			</div>

			<div class="ui-field-contain app_billing_line'.$non_mobile_cl.'">
				<label for="app_cc_country"><span>'.$this->a->get_text('cc_country').'<sup> *</sup></span>
				'.apply_filters( 'app_checkout_error_country', '' ).'
				<select data-theme="'.wpb_setting('swatch').'" id="app_cc_country" data-native-menu="false" class="filterable-select" name="country">';
				
		$content .= '<option >'.$this->a->get_text('select').'</option>';
		foreach (WpBConstant::countries( ) as $code=>$name) {
			$content .= '<option value="'.$code.'"'.selected($country, $code, false).'>'.esc_attr($name).'</option>';
		} 

		$content .= '
				</select>
				</label>
			</div>';
				
		$content .= '
			<div class="app_billing_line">
				<label><span>'.$this->a->get_text('cc_phone').'</span>
				<input name="phone" type="text" class="app_cc_phone'.$non_mobile_cl.'" value="'.esc_attr($phone).'" /></label>
			</div>

			<div class="app_billing_line">
				<label><span>'.$this->a->get_text('cc_number').'<sup> *</sup></span>
				'.apply_filters( 'app_checkout_error_card_num', '' ).'
				<input name="card_num" onkeyup="_app_.cc_card_pick(\'#cardimage\', \'#card_num\');"
				id="card_num" class="app_cc_number credit_card_number input_field noautocomplete'.$non_mobile_cl.'"
				type="text" maxlength="22" />
				</label>
				<div class="hide_after_success nocard cardimage"  id="cardimage" style="background: url('.$this->a->plugin_url.'/images/card_array.png) no-repeat;"></div></label>
			</div>

			<div class="app_billing_line'.$non_mobile_cl.'">
				<div class="app_billing_line_inner"><span class="'.$non_mobile_cl.'">'.$this->a->get_text('cc_expiry').'<sup> *</sup></span>
				'.apply_filters( 'app_checkout_error_exp', '' ).'
					<label class="app-cc-label'.$non_mobile_cl.'" for="exp_month">
						<select data-theme="'.wpb_setting('swatch').'" name="exp_month" class="app_cc_exp_month'.$non_mobile_cl.'" id="exp_month">
						'.$this->_print_month_dropdown().'
						</select>
					</label>
					<label class="app-cc-label'.$non_mobile_cl.'" for="exp_year">
						<select data-theme="'.wpb_setting('swatch').'" name="exp_year" class="app_cc_exp_year'.$non_mobile_cl.'" id="exp_year">
						'.$this->_print_year_dropdown('', true).'
						</select>
					</label>
				</div>
			</div>

			<div class="app_billing_line">
				<label><span>'.$this->a->get_text('cc_cvv').'<sup> *</sup></span>
				'.apply_filters( 'app_checkout_error_card_code', '' ).'
				<input id="card_code" name="card_code" class="app_cc_cvv input_field noautocomplete'.$non_mobile_cl.'"
				type="text" maxlength="4" /></label>
			</div>

		';
      
		return $content;
	}
	
	/**
     * Finds CC Type from CC number
	 * http://developer.ean.com/general-info/valid-card-types/
     */	
	function _get_card_type($number) {
		$num_length = strlen($number);

		if ($num_length > 10 && preg_match('/[0-9]+/', $number) >= 1) {
			if((substr($number, 0, 1) == '4') && (($num_length == 13)||($num_length == 16))) {
				return "Visa";
			} else if((substr($number, 0, 1) == '5' && ((substr($number, 1, 1) >= '1') && (substr($number, 1, 1) <= '5'))) && ($num_length == 16)) {
				return "Mastercard";
			} else if(substr($number, 0, 4) == "6011" && ($num_length == 16)) {
				return "Discover Card";
			} else if((substr($number, 0, 1) == '3' && ((substr($number, 1, 1) == '4') || (substr($number, 1, 1) == '7'))) && ($num_length == 15)) {
				return "American Express";
			}
		}
		
		return "";
	}

	/**
	* Print a dropdown of years
	* @param pfp: 4 digits
	*/	
	function _print_year_dropdown($sel='', $pfp = false) {
		$localDate=getdate();
		$minYear = $localDate["year"];
		$maxYear = $minYear + 15;

		$output = "<option>".$this->a->get_text('year')."</option>";
		for($i=$minYear; $i<$maxYear; $i++) {
			if ($pfp) {
				$output .= "<option value='". substr($i, 0, 4) ."'".($sel==(substr($i, 0, 4))?' selected':'').
				">". $i ."</option>";
			} else {
				$output .= "<option value='". substr($i, 2, 2) ."'".($sel==(substr($i, 2, 2))?' selected':'').
				">". $i ."</option>";
			}
		}
		return($output);
	}
	
	/**
	* Print a dropdown of months
	*
	*/	
	function _print_month_dropdown($sel='') {
		$output =  "<option>". ucwords( $this->a->get_text('month') )."</option>";
		$output .=  "<option " . ($sel==1?' selected':'') . " value='01'>01 - ". date_i18n("M",strtotime("1970-01-01")) ."</option>";
		$output .=  "<option " . ($sel==2?' selected':'') . "  value='02'>02 - ". date_i18n("M",strtotime("1970-02-01")) ."</option>";
		$output .=  "<option " . ($sel==3?' selected':'') . "  value='03'>03 - ". date_i18n("M",strtotime("1970-03-01")) ."</option>";
		$output .=  "<option " . ($sel==4?' selected':'') . "  value='04'>04 - ". date_i18n("M",strtotime("1970-04-01")) ."</option>";
		$output .=  "<option " . ($sel==5?' selected':'') . "  value='05'>05 - ". date_i18n("M",strtotime("1970-05-01")) ."</option>";
		$output .=  "<option " . ($sel==6?' selected':'') . "  value='06'>06 - ". date_i18n("M",strtotime("1970-06-01")) ."</option>";
		$output .=  "<option " . ($sel==7?' selected':'') . "  value='07'>07 - ". date_i18n("M",strtotime("1970-07-01")) ."</option>";
		$output .=  "<option " . ($sel==8?' selected':'') . "  value='08'>08 - ". date_i18n("M",strtotime("1970-08-01")) ."</option>";
		$output .=  "<option " . ($sel==9?' selected':'') . "  value='09'>09 - ". date_i18n("M",strtotime("1970-09-01")) ."</option>";
		$output .=  "<option " . ($sel==10?' selected':'') . "  value='10'>10 - ". date_i18n("M",strtotime("1970-10-01")) ."</option>";
		$output .=  "<option " . ($sel==11?' selected':'') . "  value='11'>11 - ". date_i18n("M",strtotime("1970-11-01")) ."</option>";
		$output .=  "<option " . ($sel==12?' selected':'') . "  value='12'>12 - ". date_i18n("M",strtotime("1970-12-01")) ."</option>";

		return($output);
	}


 	/**
	 * Redirects back to the page with error message added
	 */	
	function redirect_with_error( $post_id, $error ) {
		wp_redirect( wpb_add_query_arg( 'app_gateway_error', urlencode($error), get_permalink( $_REQUEST['post_id'] ) ) );
		exit;
	}
	
 	/**
	 * Adds an action to the footer to display either error or thank you js message
	 */	
	function template_redirect() {
		if ( isset( $_GET['app_gateway_error'] ) )
			$this->a->add2footer( 'alert("'. esc_js($_GET['app_gateway_error']).'");');
		else if ( isset( $_GET['app_gateway_success'] ) ) {
			wpb_set_session_val('app_total_amount', null);
			$app_id =  $_GET['app_gateway_success'];
			$app = $this->a->get_app( $app_id );
			if ( !$app )
				return;
			
			$this->a->open_confirmation_dialog( $app );
		}
	}
	

	/**
	 * Change appointment status to paid
	 * @param $hold_email	bool	If true, do not send confirmation message, e.g. 
	 */	
	function paid( $app_id, $hold_email=false ) {
		$problem = false;
		$app = $this->a->get_app( $app_id );
		$prev_stat = isset( $app->status ) ? $app->status : '';
		 # This will also change child appts
		if ( $app && $this->a->change_status( 'paid', $app_id ) ) {
			wpb_flush_cache();
			$app = $this->a->get_app( $app_id );
			# Worker=0 is intentional
			$slot = new WpBSlot( new WpBCalendar( $app->location, $app->service, 0 ), strtotime($app->start), strtotime($app->end) );
			add_filter( 'app_get_capacity', array( $this, 'increase_capacity' ), 10, 2 );
			if ( $slot->is_busy( ) ) {
				$problem = true;
				$message = sprintf( __('Time slot %1$s looks overbooked. Please check booking with ID %2$s. \r\rPossible reasons: \ra) Booking has been deleted or its status manually modified before \rb) If Disable Pending Appointments Time setting is less than 180 minutes, client may have waited too long on Paypal website and as the time slot has been freed another client has booked the same time slot. \rc) If Strict Check is not enabled, a manual booking has been made for the same time slot \rd) Temporary server failure.', 'wp-base'), date_i18n( $this->a->dt_format, strtotime($app->start) ), $this->a->get_app_link($app_id, true) );
			}
			else if ( 'confirmed' != $prev_stat && !$hold_email )
				$this->a->maybe_send_message( $app_id, 'confirmation' );
			
			remove_filter( 'app_get_capacity', array( $this, 'increase_capacity' ) );

		}
		else if ( 'paid' != $prev_stat ) {
			$problem = true;
			$message = sprintf( __('Payment gateway confirmation arrived, but status could not be changed. Please check booking with ID %s. \r\rPossible reasons: \ra) Booking has been deleted or its status manually modified before \rb) If Disable Pending Appointments Time setting is less than 180 minutes, client may have waited too long on Paypal website and as the time slot has been freed another client has booked the same time slot. \rc) If Strict Check is not enabled, a manual booking has been made for the same time slot \rd) Temporary server failure.', 'wp-base'), $this->a->get_app_link($app_id, true) );
		}
		
		if ( $problem ) {
			// Something wrong. Warn admin
			if ( 'yes' == wpb_setting('use_html') )
				$message = str_replace( '\r', '<br/>', $message );							
			wp_mail( BASE('User')->get_admin_email( ), sprintf(__('Problem with booking #%d','wp-base'), $app_id), $message, $this->a->message_headers() );
		}
		
		return !$problem;
	}

	/**
	 * Increase capacity temporarily by 1 to see if the paid time slot still free
	 */	
	function increase_capacity( $capacity, $ID ) {
		return ($capacity+1);
	}

  }

}


/**
 * Use this function to register your gateway plugin class
 *
 * @param string $class_name - the case sensitive name of your plugin class
 * @param string $plugin_name - the sanitized private name for your plugin
 * @param string $admin_name - pretty name of your gateway, for the admin side.
 * @param bool $global optional - whether the gateway supports global checkouts
 */
function wpb_register_gateway_plugin($class_name, $plugin_name, $admin_name, $global = false, $demo = false) {
	global $app_gateway_plugins;

	if (!is_array($app_gateway_plugins)) {
		$app_gateway_plugins = array();
	}

	if (class_exists($class_name)) {
		$app_gateway_plugins[$plugin_name] = array($class_name, $admin_name, $global, $demo);
	} else {
		return false;
	}
}

/**
 * Execute a gateway
 * @Since 2.0 
 */	
function wpb_run_gateway( $basename ) {
	global $app_gateway_plugins, $app_gateway_active_plugins;
	if ( empty( $app_gateway_plugins[$basename] ) )
		return false;
	
	$options = wpb_setting();
	
	// This will correct false running of gateway during first save of allowed
	if ( isset( $_POST['mp']['gateways']['allowed'] ) )
		$options['gateways']['allowed'] = $_POST['mp']['gateways']['allowed'];
	else if ( isset( $_POST['mp'] ) ) {
		//blank array if no checkboxes
		$options['gateways']['allowed'] = array();
	}
	$allowed = !empty( $options['gateways']['allowed'] ) && is_array( $options['gateways']['allowed'] ) ? $options['gateways']['allowed'] : array();
	
	if ( !in_array( $basename, $allowed ) )
		return false;
	
	$class_name = $app_gateway_plugins[$basename][0];
	
	if ( class_exists( $class_name ) ) {
		$app_gateway_active_plugins[] = new $class_name;
		return true;
	}
	
	return false;
}