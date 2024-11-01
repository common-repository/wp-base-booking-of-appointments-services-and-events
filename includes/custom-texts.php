<?php
/**
 * WPB Custom Texts
 *
 * Allows localization of front end texts without a translation tool
 *
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBCustomTexts' ) ) {

class WpBCustomTexts {
	
	/**
     * Default texts
     */
	var $default_texts = null;

	/**
     * Default additional texts
     */
	var $add_default_texts = array();
	
	/**
     * Replace rules cache
     */
	var $replace_rules = null;

	/**
     * Constructor
     */
	function __construct() {
		$this->a = BASE();
	}

	/**
     * Add actions and filters
     */
	function add_hooks() {
		add_filter( 'appointments_display_tabs', array( $this, 'add_tab' ), 16 ); 				// Add tab
		add_action( 'app_display_custom_texts_tab', array( $this, 'settings' ), 16 );			// Display HTML settings
		add_action( 'app_save_settings', array( $this, 'save_settings' ), 16 );					// Save settings
		
		add_filter( 'gettext', array( $this, 'global_text_replace' ), 10, 3 );
	}
	
	/**
     * Global quick text replace
	 * @param $tr_text: translated text
	 * @param $text: original text
	 * @param $domain: Text domain (wp-base is searched for)
     */
	function global_text_replace( $tr_text, $text, $domain ) {
		if ( 'wp-base' != $domain || !wpb_is_admin() || !$rep_raw = apply_filters( 'app_replace_texts', get_option( 'wp_base_replace_texts' ), $domain ) ) {
			// remove_filter( 'gettext', array( $this, 'global_text_replace' ) );
			return $tr_text;
		}
		// if ( 'wp-base' != $domain )
			// return $tr_text;
		
		if ( null === $this->replace_rules ) {
			$rules = array();
			$reps = explode( ',', wpb_sanitize_commas( $rep_raw ) );
			foreach ( $reps as $rep ) {
				if ( strpos( $rep, '|' ) === false )
					continue;
				list( $find, $replace ) = explode( '|', $rep );
				if ( $find ) {
					$rules[$find] = $replace;
					$rules[ucwords($find)] = ucwords($replace);
				}
				if ( strpos( $find, ' ' ) ) {
					$rules[ucwords($find)] = ucwords($replace);
				}
			}
			$this->replace_rules = array_filter( $rules );
		}
		
		if ( !empty( $this->replace_rules ) )
			$tr_text = str_replace( array_keys( $this->replace_rules ), array_values( $this->replace_rules ), $tr_text );
		
		return $tr_text;
	}

	/**
     * Get a custom text if $key is set or all custom texts if $key is not set
	 * To be used in Custom Texts page
	 * @return string or array
     */
	function get_custom_texts( $key=null ) {
		# Simple cache
		if ( isset( $this->custom_texts ) )
			$custom_texts = $this->custom_texts;
		else
			$custom_texts = $this->custom_texts = get_option( 'wp_base_texts' );
		
		if ( $key ) {
			if ( isset( $custom_texts[$key] ) )
				return $custom_texts[$key];
			else
				return '';
		}
		else return $custom_texts;
	}	

	/**
     * Get a help text if $key is set or all help texts if $key is not set
	 * @return string or array
     */
	function get_help_text( $key=null ) {
		$this->help_texts = array(
					'our_staff'					=> __('Text displayed to admin for service provider when no particular provider is selected', 'wp-base'),
					'a_specialist'				=> __('Text displayed to client for service provider when no particular provider is selected', 'wp-base'),
					'not_defined'				=> __('Text displayed when a service, provider, etc variable does not have a displayable name. Possibly because of a record being deleted', 'wp-base' ),
					'unknown'					=> __('Text displayed when details of a setting is unknown. Possibly because the addon which made the setting has been disabled.', 'wp-base' ),
					'app_id'					=> __('Column header for appointment ID for List of Bookings', 'wp-base' ),
					'created'					=> __('Column header of Creation Date for List of Bookings', 'wp-base' ),
					'created_by'				=> __('Column header of Creation by field for List of Bookings', 'wp-base' ),
					'location'					=> __('Column header of Location for List of Bookings', 'wp-base' ),
					'location_note'				=> __('Column header of Location Note for List of Bookings', 'wp-base' ),
					'service'					=> __('Column header of Service for List of Bookings', 'wp-base' ),
					'category'					=> __('Column header of Category for List of Bookings', 'wp-base' ),
					'provider'					=> __('Column header of Provider for List of Bookings', 'wp-base' ),
					'client'					=> __('Column header of Client for List of Bookings', 'wp-base' ),
					'date_time'					=> __('Column header of starting time of booking for List of Bookings', 'wp-base' ),
					'end_date_time'				=> __('Column header of end time of booking for List of Bookings', 'wp-base' ),
					'server_date_time'			=> __('Column header of Server Date/time for List of Bookings', 'wp-base' ),
					'server_day'				=> __('Column header of Server Day for List of Bookings', 'wp-base' ),
					'seats_total'				=> __('Column header of total capacity', 'wp-base' ),
					'seats_left'				=> __('Column header of available capacity', 'wp-base' ),
					'seats_total_left'			=> __('Column header of total and available capacity separated with "/"', 'wp-base' ),
					'date'						=> __('Column header of Date in editing form', 'wp-base' ),
					'day_of_week'				=> __('Column header of Week Day in editing form', 'wp-base' ),
					'time'						=> __('Column header of Time in editing form', 'wp-base' ),
					'status'					=> __('Column header of Status for List of Bookings', 'wp-base' ),
					'edit'						=> __('Column header of Edit for List of Bookings', 'wp-base' ),
					'cancel'					=> __('Column header of Cancel for List of Bookings', 'wp-base' ),
					'paypal'					=> __('Column header of PayPal for List of Bookings', 'wp-base' ),
					'pdf'						=> __('Column header of Pdf download for List of Bookings', 'wp-base' ),
					'action'					=> __('Column header of Action for Bookings shortcode', 'wp-base' ),
					'no_appointments'			=> __('Text for List of Bookings when there are no appointments to be displayed','wp-base'),
					'edit_button'				=> __('Edit button text in List of Bookings','wp-base'), // Edit button text, since 2.0
					'cancel_button'				=> __('Cancel button text in List of Bookings','wp-base'), // Cancel button text, since 2.0
					'pdf_download'				=> __('Pdf Download button text in List of Bookings','wp-base'), // Cancel button text, since 2.0
					'pdf_tooltip'				=> __('Tooltip title for Pdf Download button','wp-base'), // Cancel button text, since 2.0
					'gcal_button'				=> __('Tooltip title for Google Calendar button','wp-base'),
					'submit_confirm'			=> __('Button text for confirming an appointment', 'wp-base'),
					'edit_app_confirm'			=> __('Javascript text that is displayed before editing an appointment','wp-base'),
					'cancel_app_confirm'		=> __('Javascript text that is displayed before cancelling an appointment','wp-base'),
					'cancelled'					=> __('Javascript text that is displayed after an appointment has been cancelled','wp-base'),
					'connection_error'			=> __('Javascript text that is displayed when ajax request fails','wp-base'),
					'no_preference'				=> __('Selection in dropdowns when there is no preference, (e.g. when client does not particularly select a provider)', 'wp-base'),
					'all'						=> __('Selection in provider dropdown when no particular provider is selected', 'wp-base'),
					'select'					=> __('General select text', 'wp-base'),
					'select_date'				=> __('Text displayed above date selection field', 'wp-base'),
					'select_user'				=> __('Text displayed above users dropdown', 'wp-base'),
					'select_location'			=> __('Text displayed above locations dropdown', 'wp-base'),
					'select_button'				=> __('Text for select button of locations/services/service providers dropdowns', 'wp-base'),
					'select_service'			=> __('Text displayed above services dropdown', 'wp-base'),
					'select_provider'			=> __('Text displayed above service providers dropdown', 'wp-base'),
					'select_duration'			=> __('Text displayed above duration selection dropdown', 'wp-base'),
					'select_recurring'			=> __('Text displayed above number of repeats and repeat unit selection dropdowns', 'wp-base'),
					'select_seats'				=> __('Text displayed above pax/seats selection dropdown', 'wp-base'),
					'select_language'			=> __('Text displayed above language selection dropdown', 'wp-base'),
					'excerpt_more'				=> __('Text for more title/link for excerpts', 'wp-base' ),
					'login_required'			=> __('Message displayed to client when they submitted an existing email and not logged in yet', 'wp-base' ),
					'login_message'				=> __('Text beside the login link', 'wp-base' ),
					'redirect'					=> __('Javascript message displayed before client is redirected to the login page', 'wp-base' ),
					'login'						=> __('Text for the login link', 'wp-base'),
					'login_with_facebook'		=> __('Button text to login with Facebook account', 'wp-base'),
					'login_with_twitter'		=> __('Button text to login with Twitter account', 'wp-base'),
					'login_with_google'			=> __('Button text to login with Google+ account', 'wp-base'),
					'login_with_wp'				=> __('Button text to login with WordPress account', 'wp-base'),
					'logged_in'					=> __('Message displayed to client after a successful login', 'wp-base'),
					'confirmation_title'		=> __('Title for confirmation form', 'wp-base' ),
					'save_button'				=> __('Button text for save (finalise edit)', 'wp-base' ),
					'checkout_button'			=> __('Button text for checkout (finalise booking)', 'wp-base' ),
					'checkout_button_tip'		=> __('Tooltip text for checkout button', 'wp-base' ),
					'pay_now'					=> __('Button text for credit card data submit. AMOUNT placeholder will be replaced with formatted payable amount, including currency sign.', 'wp-base' ),
					'please_wait'				=> __('Message displayed while submitting a form', 'wp-base' ),
					'cc_form_legend'			=> __('Title above credit card form', 'wp-base' ),
					'continue_button'			=> __('Button text for continue (finalise selections and display confirmation form)', 'wp-base' ),
					'appointment_received'		=> __('Javascript message displayed after a successful booking and no confirmation/pending dialog text exists', 'wp-base' ),
					'appointment_edited'		=> __('Javascript message displayed after a successful editing', 'wp-base' ),
					'missing_field'				=> __('Javascript message displayed when a required field is left empty','wp-base'),
					'missing_terms_check'		=> __('Javascript message displayed when a terms and conditions checkbox are not checked by the client','wp-base'),
					'missing_extra'				=> __('Javascript message displayed when selection of an extra is required, but client did not pick one','wp-base'),
					'name'						=> __('Title for Name field in the confirmation form','wp-base'),
					'first_name'				=> __('Title for First Name field in the confirmation form and user page','wp-base'),
					'last_name'					=> __('Title for Last Name field in the confirmation form and user page','wp-base'),
					'email'						=> __('Title for email field in the confirmation form and user page','wp-base'),
					'phone'						=> __('Title for Phone field in the confirmation form and user page','wp-base'),
					'address'					=> __('Title for Address field in the confirmation form and user page','wp-base'),
					'city'						=> __('Title for City field in the confirmation form and user page','wp-base'),
					'zip'						=> __('Title for Postcode field in the confirmation form and user page','wp-base'),
					'state'						=> __('Title for State field in the confirmation form and user page','wp-base'),
					'country'					=> __('Title for Country field in the confirmation form and user page','wp-base'),
					'name_placeholder'			=> __('Placeholder for Name field in the confirmation form','wp-base'),
					'first_name_placeholder'	=> __('Placeholder for First Name field in the confirmation form','wp-base'),
					'last_name_placeholder'		=> __('Placeholder for Last Name field in the confirmation form','wp-base'),
					'email_placeholder'			=> __('Placeholder for email field in the confirmation form','wp-base'),
					'phone_placeholder'			=> __('Placeholder for Phone field in the confirmation form','wp-base'),
					'address_placeholder'		=> __('Placeholder for Address field in the confirmation form','wp-base'),
					'city_placeholder'			=> __('Placeholder for City field in the confirmation form','wp-base'),
					'zip_placeholder'			=> __('Placeholder for Postcode field in the confirmation form','wp-base'),
					'state_placeholder'			=> __('Placeholder for State field in the confirmation form','wp-base'),
					'country_placeholder'		=> __('Placeholder for Country field in the confirmation form','wp-base'),		
					'note'						=> __('Title for Note field in the confirmation form','wp-base'),
					'gcal'						=> __('Title for GCal column in List of Bookings','wp-base'),
					'remember'					=> __('Text beside Remember Me field in the confirmation form','wp-base'),
					'pay_with'					=> __('Text beside Payment field in the confirmation form when more than one payment option/gateway is active','wp-base'),
					'click_to_remove'			=> __('Hint text to remove an appointment from confirmation form','wp-base'),
					'click_to_book'				=> __('Hint text to add a booking in calendar','wp-base'),
					'click_to_select_date'		=> __('Hint text to add select a date in monthly calendar','wp-base'),
					'removed'					=> __('Text in tooltip when an appointment has been removed from confirmation form','wp-base'),
					'year'						=> __('Text for year','wp-base'),
					'month'						=> __('Singular text for month','wp-base'),
					'months'					=> __('Plural text for month','wp-base'),
					'monthly'					=> __('Text for bookings recurring every month','wp-base'),
					'biweekly'					=> __('Text for bookings recurring every other week','wp-base'),
					'week'						=> __('Singular text for week','wp-base'),
					'weeks'						=> __('Plural text for week','wp-base'),
					'weekly'					=> __('Text for bookings recurring every week','wp-base'),
					'eod'						=> __('Text for bookings recurring every other day','wp-base'),
					'eod_except_sunday'			=> __('Text for bookings recurring every other day except Sunday','wp-base'),
					'weekday'					=> __('Text for bookings recurring every weekday','wp-base'),
					'weekend'					=> __('Text for bookings recurring every weekend','wp-base'),
					'day'						=> __('Singular text for day','wp-base'),
					'days'						=> __('Plural text for day','wp-base'),
					'daily'						=> __('Text for bookings recurring every day','wp-base'),
					'hour'						=> __('Singular text for hour','wp-base'),
					'hours'						=> __('Plural text for hour','wp-base'),
					'hour_short'				=> __('Short form of hour','wp-base'),
					'minute'					=> __('Singular text for minute','wp-base'),
					'minutes'					=> __('Plural text for minute','wp-base'),
					'min_short'					=> __('Short form of minute','wp-base'),
					'second'					=> __('Singular text for second','wp-base'),
					'seconds'					=> __('Plural text for seconds','wp-base'),
					'no_repeat'					=> __('Text for Recurring Appointments pulldown menu to select no repeat','wp-base'),
					'limit_exceeded'			=> __('Javascript message displayed when selected number of appointments exceeds permitted number. Keep %d which will be replaced by actual limit', 'wp-base'),
					'blacklisted'				=> __('Javascript message displayed when client applies with a blacklisted email address', 'wp-base'),
					'too_less'					=> __('Javascript message displayed when selected number of appointments is less than the permitted number. Keep %d which will be replaced by actual limit', 'wp-base'),
					'already_booked'			=> __('Javascript message displayed when selected time slot is no more available, e.g. because of another simultaneous booking', 'wp-base'),
					'location_name'				=> __('Title for Location in the confirmation form', 'wp-base' ),
					'location_names'			=> __('Plural of the Location title in the confirmation form', 'wp-base' ),
					'service_name'				=> __('Title for Service in the confirmation form', 'wp-base' ),
					'service_names'				=> __('Plural of the Service title in the confirmation form', 'wp-base' ),
					'lasts'						=> __('Title for Duration of the selected service(s) in the confirmation form', 'wp-base' ),
					'details'					=> __('Title for items in the cart: Title for list of selected slots in the confirmation form when more than one time slot is selected', 'wp-base' ),
					'price'						=> __('Title for Price in the confirmation form', 'wp-base' ),
					'coupon'					=> __('Title for Coupon in the confirmation form', 'wp-base' ),
					'coupon_placeholder'		=> __('Description of coupon field in the confirmation form', 'wp-base' ),
					'provider_name'				=> __('Title for Service Provider in the confirmation form', 'wp-base' ),
					'provider_names'			=> __('Plural of the Service Provider title in the confirmation form', 'wp-base' ),
					'unauthorised'				=> __('Message displayed after an unauthorised access', 'wp-base'),
					'error'						=> __('Javascript message displayed after a General/unknown error', 'wp-base'),
					'error_short'				=> __('Title for general/unknown error message display', 'wp-base'),
					'spam'						=> __('Javascript message displayed after a too frequent booking attempt', 'wp-base'),
					'payment_method_error'		=> __('Message displayed when payment is required, but no payment method is selected', 'wp-base'),
					'save_error'				=> __('Javascript message displayed when appointment could not be saved possibly because of a server error', 'wp-base'),
					'nothing_changed'			=> __('Javascript message displayed when client did not make any changes', 'wp-base'),
					'wrong_value'				=> __('Javascript message displayed when submitted field is not acceptable. Keep %s which will be replaced by field name', 'wp-base'),
					'next_week'					=> __('Pagination button text for Next Week','wp-base'),
					'next_weeks'				=> __('Pagination button text for Next Weeks','wp-base'),
					'previous'					=> __('Pagination button text for Previous','wp-base'),
					'previous_week'				=> __('Pagination button text for Previous Week','wp-base'),
					'previous_weeks'			=> __('Pagination button text for Previous Weeks','wp-base'),
					'next'						=> __('Pagination button text for Next','wp-base'),
					'next_month'				=> __('Pagination button text for Next Month','wp-base'),
					'next_months'				=> __('Pagination button text for Next Months','wp-base'),
					'previous_month'			=> __('Pagination button text for Previous Month','wp-base'),
					'previous_months'			=> __('Pagination button text for Previous Months','wp-base'),
					'monthly_title'				=> __('Title of the monthly calendar. LOCATION, SERVICE, WORKER, START will be replaced by their real values', 'wp-base'),
					'logged_message'			=> __('Text displayed below calendar title when client is logged in', 'wp-base'),
					'not_logged_message'		=> __('Text displayed below calendar title when login is required and client is not logged in. LOGIN_PAGE will be replaced by the login url', 'wp-base'),
					'weekly_title'				=> __('Title above the weekly calendar. LOCATION, SERVICE, WORKER, START, END will be replaced by their real values', 'wp-base'),
					'book_now_long'				=> __('Book Now shortcode button text displayed when booking is possible. LOCATION, SERVICE, WORKER, START will be replaced by their real values', 'wp-base'),
					'book_now_short'			=> __('Book in Table View button text. LOCATION, SERVICE, WORKER, START will be replaced by their real values', 'wp-base'),
					'booking_closed'			=> __('Book Now shortcode button text displayed when booking is not possible, i.e. fully booked or allowed booking time has passed', 'wp-base'),
					'sunday'					=> __('Sunday',	'wp-base'),
					'monday'					=> __('Monday',	'wp-base'),
					'tuesday'					=> __('Tuesday',	'wp-base'),
					'wednesday'					=> __('Wednesday',	'wp-base'),
					'thursday'					=> __('Thursday',	'wp-base'),
					'friday'					=> __('Friday',	'wp-base'),
					'saturday'					=> __('Saturday',	'wp-base'),
					'sunday_short'				=> __('Short form of Sunday', 'wp-base'),
					'monday_short'				=> __('Short form of Monday', 'wp-base'),
					'tuesday_short'				=> __('Short form of Tuesday', 'wp-base'),
					'wednesday_short'			=> __('Short form of Wednesday', 'wp-base'),
					'thursday_short'			=> __('Short form of Thursday', 'wp-base'),
					'friday_short'				=> __('Short form of Friday', 'wp-base'),
					'saturday_short'			=> __('Short form of Saturday', 'wp-base'),
					'sunday_initial'			=> __('Initial letter of Sunday', 'wp-base'),
					'monday_initial'			=> __('Initial letter of Monday', 'wp-base'),
					'tuesday_initial'			=> __('Initial letter of Tuesday', 'wp-base'),
					'wednesday_initial'			=> __('Initial letter of Wednesday', 'wp-base'),
					'thursday_initial'			=> __('Initial letter of Thursday', 'wp-base'),
					'friday_initial'			=> __('Initial letter of Friday', 'wp-base'),
					'saturday_initial'			=> __('Initial letter of Saturday', 'wp-base'),
					'pending'					=> __('Text for status pending', 'wp-base'),
					'pending_approval'			=> __('Text for status pending and an automatic payment is not expected, e.g. payment is not required, price is zero, or manual payment is selected', 'wp-base'),
					'pending_payment'			=> __('Text for status pending and an automatic payment via a gateway is expected', 'wp-base'),
					'running'					=> __('Text for status happening now', 'wp-base'),
					'paid'						=> __('Text for status paid', 'wp-base'),
					'confirmed'					=> __('Text for status confirmed', 'wp-base'),
					'completed'					=> __('Text for status completed', 'wp-base'),
					'reserved'					=> __('Text for status reserved by Google Calendar', 'wp-base'),
					'removed'					=> __('Text for status removed', 'wp-base'),
					'test'						=> __('Text for status test', 'wp-base'),
					'waiting'					=> __('Text for status in Waiting List', 'wp-base'),
					'cart'						=> __('Text for status in Cart', 'wp-base'),
					'hold'						=> __('Text for status Temporary', 'wp-base'),
					'too_late'					=> __('Javascript message displayed when client attempts to cancel/edit/add an appointment, but it is too late','wp-base'),
					'cancel_disabled'			=> __('Javascript message displayed when client attempts to cancel an appointment, but cancellation is turned off','wp-base'),
					'edit_disabled'				=> __('Javascript message displayed when client attempts to edit an appointment, but editing is turned off','wp-base'),
					'seats'						=> __('Title for number of seats in the confirmation form', 'wp-base'),
					'pax'						=> __('Used for each guest/seat booked in Group Bookings', 'wp-base' ),
					'participants'				=> __('Header for List of Participants of Group Bookings', 'wp-base' ),
					'participant_title'			=> __('Title for each participant of Group Bookings. %d will be replaced by the order in the list.','wp-base'),
					'nop_placeholder'			=> __('Placeholder for participant name field. %d will be replaced by the order in the list.', 'wp-base' ),
					'mop_placeholder'			=> __('Placeholder for participant email field. %d will be replaced by the order in the list.', 'wp-base' ),
					'pop_placeholder'			=> __('Placeholder for participant phone field. %d will be replaced by the order in the list.', 'wp-base' ),
					'aop_placeholder'			=> __('Placeholder for participant address field. %d will be replaced by the order in the list.', 'wp-base' ),
					'not_enough_capacity'		=> __('Javascript message displayed when client attempts to take seats more than the capacity. This may happen when different services with different capacities are to be booked', 'wp-base'),
					'not_published'				=> __('Javascript text displayed when client attempts to book a service which is not published (expired or not open yet)','wp-base'),
					'no_free_time_slots'		=> __('HTML text displayed when there are no free time slots in the table or block','wp-base'),
					'quota'						=> __('Javascript text displayed when client attempts to book a service which is out of quota','wp-base'),
					'past_date'					=> __('Javascript text displayed when client attempts to edit a booking with past date/time','wp-base'),
					'not_working'				=> __('Javascript text displayed when client attempts to edit date/time of a booking and provider is not working','wp-base'),
					'not_available'				=> __('Javascript text displayed when client attempts to edit date/time of a booking and time slot has been booked','wp-base'),
					'not_possible'				=> __('Javascript text displayed when requested action or selection is not possible','wp-base'),
					'timezone_title'			=> __('Title of Time Zone selection pulldown menu', 'wp-base'),
					'use_server_timezone'		=> __('Text for selection of using server timezone instead of dedected client local timezone', 'wp-base'),
					'countdown_title'			=> __('Title of Next Appointment Countdown', 'wp-base'),
					'conf_countdown_title'		=> __('Title of countdown on confirmation form', 'wp-base'),
					'select_theme'				=> __('Title of Theme selection pulldown menu', 'wp-base'),
					'search'					=> __('Placeholder value in table Search field', 'wp-base'),
					'info'						=> __('Localization of pagination under tables. Keep _PAGE_ and _PAGES_ which is the current page no and total number of pages, respectively.', 'wp-base'),
					'length_menu'				=> __('Localization for pulldown menu that selects the number of records to be displayed in the tables. Keep _MENU_ which stands for the pulldown menu itself.', 'wp-base'),
					'balance'					=> __('Column header of Balance for List of Bookings', 'wp-base'),
					'deposit'					=> __('Column header of Deposit for List of Bookings', 'wp-base'),
					'down_payment'				=> __('Column header of Prepayment for List of Bookings', 'wp-base'),
					'total_paid'				=> __('Column header of Total Paid amount for List of Bookings', 'wp-base'),
					'username'					=> __('Placeholder value in login Username field', 'wp-base'),
					'password'					=> __('Placeholder value in login Password field', 'wp-base'),
					'register'					=> __('Placeholder value in login Register field', 'wp-base'),
					'paypal_express_note'		=> __('Text that will be displayed in final confirmation stage with Paypal Express Checkout. The first %s is the amount to be paid, the second one is account name (email) of the client.', 'wp-base'),
					'cc_email'					=> __('Email title on credit card form', 'wp-base'),
					'cc_name'					=> __('Full Name title on credit card form', 'wp-base'),
					'cc_address1'				=> __('Address Line 1 title on credit card form', 'wp-base'),
					'cc_address2'				=> __('Address Line 2 title on credit card form', 'wp-base'),
					'cc_city'					=> __('City title on credit card form', 'wp-base'),
					'cc_state'					=> __('State/Province/Region title on credit card form', 'wp-base'),
					'cc_zip'					=> __('Postcode title on credit card form', 'wp-base'),
					'cc_country'				=> __('Country title on credit card form', 'wp-base'),
					'cc_phone'					=> __('Phone title on credit card form', 'wp-base'),
					'cc_number'					=> __('Credit Card Number title on credit card form', 'wp-base'),
					'cc_expiry'					=> __('Expiration Date title on credit card form', 'wp-base'),
					'cc_cvv'					=> __('Security Code title on credit card form', 'wp-base'),
					'cc_declined'				=> __('Error message coming from payment gateway, e.g. declined. Keep %s which will be replaced by error code.', 'wp-base'),
					'invalid_cc_number'	 		=> __('Error message when Credit Card Number field is empty or card number is invalid', 'wp-base' ),
					'invalid_expiration'		=> __('Error message when expiration month and/or year field is empty or invalid', 'wp-base' ),
					'invalid_cvc'				=> __('Error message when security code is empty or invalid', 'wp-base' ),
					'expired_card'		 		=> __('Error message when credit card has expired', 'wp-base' ),
					'invalid_cardholder' 		=> __('Error message when credit cardholder is invalid', 'wp-base' ),
					'processing' 				=> __('Processing text when connecting to payment gateway', 'wp-base' ),
					'cancel_cart'				=> __('Button text on confirmation form to clear cart contents and refresh page', 'wp-base' ),
					'cancel_confirm_text'		=> __('Text displayed to confirm cancellation of current process (checkout, edit, etc)', 'wp-base' ),
					'cancel_confirm_yes'		=> __('Text to confirm cancellation of current process', 'wp-base' ),
					'cancel_confirm_no'			=> __('Text to quit cancellation of current process', 'wp-base' ),
					'close'						=> __('Close button text', 'wp-base' ),
					'clear'						=> __('Clear signature button text', 'wp-base' ),
					'required'					=> __('Note added under confirmation form when there is at least one required field', 'wp-base' ),
					'next_day'					=> __('Note added to details field on confirmation form to notify a booking ending next day', 'wp-base' ),
					'bp_title'					=> __('BuddyPress user page main tab title', 'wp-base' ),
					'bp_bookings'				=> __('BuddyPress user page bookings tab title for client', 'wp-base' ),
					'bp_bookings_as_client'		=> __('BuddyPress user page bookings tab title for provider as client', 'wp-base' ),
					'bp_bookings_as_provider'	=> __('BuddyPress user page bookings tab title for provider as provider', 'wp-base' ),
					'bp_schedules'				=> __('BuddyPress user page Schedules tab title', 'wp-base' ),
					'bp_services'				=> __('BuddyPress user page Services tab title', 'wp-base' ),
					'bp_wh'						=> __('BuddyPress user page Working Hours tab title', 'wp-base' ),
					'bp_holidays'				=> __('BuddyPress user page Holidays tab title', 'wp-base' ),
					'bp_annual'					=> __('BuddyPress user page Annual Schedules tab title', 'wp-base' ),
					'bp_settings'				=> __('BuddyPress user page Settings tab title', 'wp-base' ),
					'bp_book_me'				=> __('BuddyPress user page Book Me tab title', 'wp-base'),
					'bp_use_book_me'			=> __('BuddyPress WP BASE checkbox title to select whether to add a Book Me tab', 'wp-base'),			
					'saved'						=> __('Javascript message after settings saved.', 'wp-base'),
					'deleted'					=> __('Javascript message after one or more records deleted.', 'wp-base'),
					'updated'					=> __('Javascript message after one or more records updated.', 'wp-base'),
					'notice'					=> __('Dialog title for notice type messages', 'wp-base'),
					'proceed'					=> __('Javascript message displayed when client is asked to confirm to proceed', 'wp-base'),
					'export_csv'				=> __('Button text for export bookings in CSV file format','wp-base'),
					'tt_regular_price'			=> __('Tooltip text displayed for regular price when there is a discounted price','wp-base'),
					'tt_discounted_price'		=> __('Tooltip text displayed for price discounted by coupon or custom pricing','wp-base'),
					'tt_coupon'					=> __('Tooltip text displayed for price discounted by coupon','wp-base'),
					'price_mismatch'			=> __('Javascript message in case there is a mismatch of calculated price and price previously displayed to the client and new price is higher.','wp-base'),
					'price_mismatch_lower'		=> __('Javascript message in case there is a mismatch of calculated price and price previously displayed to the client and new price is lower.','wp-base'),
					'extra'						=> __('Title for Extra in confirmation form, bookings page table and list of bookings','wp-base'),
					'nof_jobs_total'			=> __('Title for number of total jobs of a package in confirmation form, bookings page table and list of bookings','wp-base'),
					'nof_jobs_completed'		=> __('Title for number of completed jobs of a package in confirmation form, bookings page table and list of bookings','wp-base'),
					'nof_jobs_cancelled'		=> __('Title for number of cancelled jobs of a package in confirmation form, bookings page table and list of bookings','wp-base'),
					'nof_jobs_remaining'		=> __('Title for number of remaining jobs of a package in confirmation form, bookings page table and list of bookings','wp-base'),
					'login_for_cancel'			=> __('Message displayed when login is required to cancel a booking','wp-base'),
					'login_for_edit'			=> __('Message displayed when login is required to edit a booking','wp-base'),
					'login_for_confirm'			=> __('Message displayed when login is required to confirm a booking','wp-base'),
					'add_to_cart'				=> __('Button text to add a product to shopping cart of WooCommerce or MarketPress', 'wp-base'),
					'auto_assign_login'			=> __('Message displayed when login is required to be assigned as a service provider','wp-base'),
					'auto_assign_intro'			=> __('Javascript message displayed after user is auto assigned as a Service Provider','wp-base'),
					'pdf'						=> __('Title text of confirmation attachment for my appointments table', 'wp-base' ),
					'pdf_download'				=> __('Button text to download a confirmation attachment for my appointments table', 'wp-base' ),
					'pdf_tooltip'				=> __('Tooltip text that explains function of pdf download button.','wp-base'),
					'updating'					=> __('Spinner panel message while an update is in progress','wp-base'),
					'reading'					=> __('Spinner panel message while ajax data read is in progress','wp-base'),
					'booking'					=> __('Spinner panel message while booking is being saved','wp-base'),
					'saving'					=> __('Spinner panel message while data is being saved','wp-base'),
					'calculating'				=> __('Spinner panel message while price is being recalculated after a form field change','wp-base'),
					'refreshing'				=> __('Spinner panel message while page is being refreshed','wp-base'),
					'checkout'					=> __('Spinner panel message while processing checkout','wp-base'),
					'preparing_timetable'		=> __('Spinner panel message while time table is being prepared','wp-base'),
					'preparing_form'			=> __('Spinner panel message while booking views are being prepared','wp-base'),
					'logging_in'				=> __('Spinner panel message while login is being prepared','wp-base'),
					'done'						=> __('Spinner panel message when ajax jobs finished','wp-base'),
					'gdpr_userdata_title'		=> __('Title for GDPR userdata Group','wp-base'),
					'gdpr_udf_title'			=> __('Title for GDPR UDF Group','wp-base'),
					'yes'						=> __('Translation for Yes, e.g. to show a check box is checked','wp-base'),
					'no'						=> __('Translation for No, e.g. to show a check box is not checked','wp-base'),
			);
		
		if ( $key ) {
			if ( isset( $this->help_texts[$key] ) )
				return $this->help_texts[$key];
			else
				return '';
		}
		else return $this->help_texts;
	}	

	/**
     * Get a default text if $key is set or all default texts if $key is not set
	 * @return string or array
     */
	function get_default_texts( $key=null ) {
		if ( null !== $this->default_texts ) {
			if ( null === $key )
				return $this->default_texts;

			if ( isset( $this->default_texts[$key] ) )
				return $this->default_texts[$key];
			else
				return '';
		}
		
		global $wp_locale;
		$this->default_texts = array(
			'our_staff'					=> __('Staff', 'wp-base'),
			'a_specialist'				=> __('A specialist', 'wp-base'),
			'not_defined'				=> __('Not defined', 'wp-base'),
			'unknown'					=> __('Unknown', 'wp-base'),
			'app_id'					=> __('ID', 'wp-base' ),
			'created'					=> __('Created at', 'wp-base' ),
			'created_by'				=> __('Created by', 'wp-base' ),
			'location'					=> __('Location', 'wp-base' ),
			'location_note'				=> __('Location Note', 'wp-base' ),
			'category'					=> __('Category', 'wp-base' ),
			'service'					=> __('Service', 'wp-base' ),
			'provider'					=> __('Provider', 'wp-base' ),
			'client'					=> __('Client', 'wp-base' ),
			'date_time'					=> __('Starts', 'wp-base' ),
			'end_date_time'				=> __('Ends', 'wp-base' ),
			'server_date_time'			=> __('Server Date/time', 'wp-base' ),
			'server_day'				=> __('Server Day', 'wp-base' ),
			'lasts'						=> __('Lasts', 'wp-base' ),
			'details'					=> __('Details', 'wp-base' ),
			'seats_total'				=> __('Seats Total', 'wp-base' ),
			'seats_left'				=> __('Seats Left', 'wp-base' ),
			'seats_total_left'			=> __('Seats Total/Left', 'wp-base' ),
			'date'						=> __('Date', 'wp-base' ),
			'day_of_week'				=> __('Day', 'wp-base' ),
			'time'						=> __('Time', 'wp-base' ),
			'status'					=> __('Status', 'wp-base' ),
			'action'					=> __('Action', 'wp-base' ),
			'edit'						=> __('Edit', 'wp-base' ),
			'cancel'					=> __('Cancel', 'wp-base' ),
			'paypal'					=> __('PayPal', 'wp-base' ),
			'no_appointments'			=> __('No appointments','wp-base'),
			'edit_button'				=> __('Edit','wp-base'),
			'cancel_button'				=> __('Cancel','wp-base'),
			'gcal_button'				=> __('Click to submit this appointment to your Google Calendar account','wp-base'),
			'submit_confirm'			=> __('Submit', 'wp-base'),
			'edit_app_confirm'			=> __('You are about to edit an existing appointment. Click OK to continue.','wp-base'),
			'cancel_app_confirm'		=> __('Are you sure to cancel the selected appointment?','wp-base'),
			'cancelled'					=> __('Selected appointment cancelled.','wp-base'),
			'connection_error'			=> __('A connection error occurred.','wp-base'),
			'no_preference'				=> __('No preference', 'wp-base'),
			'all'						=> __('Any', 'wp-base'),
			'select'					=> __('Please select', 'wp-base'),
			'select_date'				=> __('Date', 'wp-base'),
			'select_user'				=> __('User', 'wp-base'),
			'select_location'			=> __('Location', 'wp-base'),
			'select_service'			=> __('Service', 'wp-base'),
			'select_provider'			=> __('Specialist', 'wp-base'),
			'select_duration'			=> __('Duration', 'wp-base'),
			'select_button'				=> __('Refresh', 'wp-base'),
			'select_recurring'			=> __('Repeat', 'wp-base'),
			'select_seats'				=> __('Number of Guests', 'wp-base'),
			'select_language'			=> __('Select Language', 'wp-base'),
			'excerpt_more'				=> __('More information <span class="meta-nav">&rarr;</span>', 'wp-base' ),
			'login_required'			=> __('It looks like you have previously registered to our website. Please login to proceed.', 'wp-base' ),
			'login_message'				=> __('Click here to login:', 'wp-base' ),
			'redirect'					=> __('Login required to make an appointment. Now you will be redirected to login page.', 'wp-base' ),
			'login'						=> __('Login', 'wp-base'),
			'login_with_facebook'		=> __('Login with Facebook', 'wp-base'),
			'login_with_twitter'		=> __('Login with Twitter', 'wp-base'),
			'login_with_google'			=> __('Login with Google+', 'wp-base'),
			'login_with_wp'				=> __('Login with WordPress', 'wp-base'),
			'logged_in'					=> __('You are now logged in', 'wp-base'),
			'confirmation_title'		=> __('Please fill in the form and confirm:', 'wp-base' ),
			'save_button'				=> __('Save changes', 'wp-base' ),
			'checkout_button'			=> __('Checkout', 'wp-base' ),
			'checkout_button_tip'		=> __('Click to apply for the appointment', 'wp-base' ),
			'pay_now'					=> __('Pay AMOUNT', 'wp-base' ),
			'please_wait'				=> __('Please Wait...', 'wp-base' ),
			'cc_form_legend'			=> __('Please enter your credit card details below and confirm', 'wp-base' ),
			'continue_button'			=> __('Add another time slot', 'wp-base' ),
			'appointment_received'		=> __('We have received your appointment. Thanks!', 'wp-base' ),
			'appointment_edited'		=> __('Your booking has been successfully changed.', 'wp-base' ),
			'missing_field'				=> __('Please fill in the required field','wp-base'),
			'missing_terms_check'		=> __('Please accept Terms and Conditions','wp-base'),
			'missing_extra'				=> __('Please select at least one option from the list','wp-base'),
			'name'						=> __('Name','wp-base'),
			'first_name'				=> __('First Name','wp-base'),
			'last_name'					=> __('Last Name','wp-base'),
			'email'						=> __('Email','wp-base'),
			'phone'						=> __('Phone','wp-base'),
			'address'					=> __('Address','wp-base'),
			'city'						=> __('City','wp-base'),
			'zip'						=> __('Postcode','wp-base'),
			'state'						=> __('State','wp-base'),
			'country'					=> __('Country','wp-base'),
			'name_placeholder'			=> '&nbsp;',
			'first_name_placeholder'	=> '&nbsp;',
			'last_name_placeholder'		=> '&nbsp;',
			'email_placeholder'			=> '&nbsp;',
			'phone_placeholder'			=> '&nbsp;',
			'address_placeholder'		=> '&nbsp;',
			'city_placeholder'			=> '&nbsp;',
			'zip_placeholder'			=> '&nbsp;',
			'state_placeholder'			=> '&nbsp;',
			'country_placeholder'		=> '&nbsp;',			
			'note'						=> __('Note','wp-base'),
			'gcal'						=> __('GCal','wp-base'),
			'remember'					=> __('Remember me','wp-base'),
			'pay_with'					=> __('Pay with','wp-base'),
			'click_to_remove'			=> __('Click to remove','wp-base'),
			'click_to_book'				=> __('Click to add a booking','wp-base'),
			'click_to_select_date'		=> __('Click to pick date','wp-base'),
			'removed'					=> __('Removed!','wp-base'),
			'year'						=> __('Year','wp-base'),
			'month'						=> __('month','wp-base'),
			'months'					=> __('months','wp-base'),
			'monthly'					=> __('Monthly','wp-base'),
			'biweekly'					=> __('Every other week','wp-base'),
			'week'						=> __('week','wp-base'),
			'weeks'						=> __('weeks','wp-base'),
			'weekly'					=> __('Weekly','wp-base'),
			'eod'						=> __('Every other day','wp-base'),
			'eod_except_sunday'			=> __('EOD except Sunday','wp-base'),
			'weekday'					=> __('Weekday','wp-base'),
			'weekend'					=> __('Weekend','wp-base'),
			'day'						=> __('day','wp-base'),
			'days'						=> __('days','wp-base'),
			'daily'						=> __('Daily','wp-base'),
			'hour'						=> __('hour','wp-base'),
			'hours'						=> __('hours','wp-base'),
			'hour_short'				=> __('h','wp-base'),
			'minute'					=> __('minute','wp-base'),
			'minutes'					=> __('minutes','wp-base'),
			'min_short'					=> __('min','wp-base'),
			'second'					=> __('second','wp-base'),
			'seconds'					=> __('seconds','wp-base'),
			'no_repeat'					=> __('No repeat','wp-base'),
			'limit_exceeded'			=> __('We are sorry, but number of appointments limit (%d) has been reached.', 'wp-base'),
			'blacklisted'				=> __('We are sorry, but the provided email cannot be accepted. Please contact website admin for details.', 'wp-base'),
			'too_less'					=> __('You should select at least %d time slot to proceed.', 'wp-base'),
			'already_booked'			=> __('We are sorry, but this time slot is no longer available. Please refresh the page and try another time slot. Thank you.', 'wp-base'),
			'location_name'				=> __('Location name', 'wp-base' ),
			'location_names'			=> __('Location names', 'wp-base' ),
			'service_name'				=> __('Service name', 'wp-base' ),
			'service_names'				=> __('Service names', 'wp-base' ),
			'price'						=> __('Price', 'wp-base' ),
			'coupon'					=> __('Discount code', 'wp-base' ),
			'coupon_placeholder'		=> __('Use discount coupon here', 'wp-base' ),
			'provider_name'				=> __('Specialist', 'wp-base' ),
			'provider_names'			=> __('Specialists', 'wp-base' ),
			'unauthorised'				=> __('You are not authorised. Try to login again.', 'wp-base'),
			'error'						=> __('Something went wrong. Please try again, if applicable, after refreshing the page. If problem persists, contact website admin.', 'wp-base'),
			'error_short'				=> __('Error', 'wp-base'),
			'spam'						=> __('You have already applied for an appointment. Please wait until you hear from us.', 'wp-base'),
			'payment_method_error'		=> __('Payment method is not selected', 'wp-base'),
			'save_error'				=> __('Appointment could not be saved. Please contact website admin.', 'wp-base'),
			'nothing_changed'			=> __('You did not make any changes.', 'wp-base'),
			'wrong_value'				=> __('Please check submitted %s entry!', 'wp-base'),
			'next'						=> __('Next','wp-base'),
			'next_week'					=> __('Next Week','wp-base'),
			'next_weeks'				=> __('Next Weeks','wp-base'),
			'previous'					=> __('Previous','wp-base'),
			'previous_week'				=> __('Previous Week','wp-base'),
			'previous_weeks'			=> __('Previous Weeks','wp-base'),
			'next_month'				=> __('Next Month','wp-base'),
			'next_months'				=> __('Next Months','wp-base'),
			'previous_month'			=> __('Previous Month','wp-base'),
			'previous_months'			=> __('Previous Months','wp-base'),
			'monthly_title'				=> __('SERVICE - START', 'wp-base'),
			'logged_message'			=> __('Click a free day to apply for an appointment.', 'wp-base'),
			'not_logged_message'		=> __('You need to login to make an appointment. Click here to login: LOGIN_PAGE OR click here to register: REGISTRATION_PAGE', 'wp-base'),
			'weekly_title'				=> __('SERVICE - START_END', 'wp-base'),
			'book_now_long'				=> __('Book Now for SERVICE on START', 'wp-base'),
			'book_now_short'			=> __('Book Now', 'wp-base'),
			'booking_closed'			=> __('Booking closed',	'wp-base'),
			'sunday'					=> $wp_locale->get_weekday( 0 ),
			'monday'					=> $wp_locale->get_weekday( 1 ),
			'tuesday'					=> $wp_locale->get_weekday( 2 ),
			'wednesday'					=> $wp_locale->get_weekday( 3 ),
			'thursday'					=> $wp_locale->get_weekday( 4 ),
			'friday'					=> $wp_locale->get_weekday( 5 ),
			'saturday'					=> $wp_locale->get_weekday( 6 ),
			'sunday_short'				=> $wp_locale->get_weekday_abbrev( $wp_locale->get_weekday( 0 ) ),
			'monday_short'				=> $wp_locale->get_weekday_abbrev( $wp_locale->get_weekday( 1 ) ),
			'tuesday_short'				=> $wp_locale->get_weekday_abbrev( $wp_locale->get_weekday( 2 ) ),
			'wednesday_short'			=> $wp_locale->get_weekday_abbrev( $wp_locale->get_weekday( 3 ) ),
			'thursday_short'			=> $wp_locale->get_weekday_abbrev( $wp_locale->get_weekday( 4 ) ),
			'friday_short'				=> $wp_locale->get_weekday_abbrev( $wp_locale->get_weekday( 5 ) ),
			'saturday_short'			=> $wp_locale->get_weekday_abbrev( $wp_locale->get_weekday( 6 ) ),
			'sunday_initial'			=> $wp_locale->get_weekday_initial( $wp_locale->get_weekday( 0 ) ),
			'monday_initial'			=> $wp_locale->get_weekday_initial( $wp_locale->get_weekday( 1 ) ),
			'tuesday_initial'			=> $wp_locale->get_weekday_initial( $wp_locale->get_weekday( 2 ) ),
			'wednesday_initial'			=> $wp_locale->get_weekday_initial( $wp_locale->get_weekday( 3 ) ),
			'thursday_initial'			=> $wp_locale->get_weekday_initial( $wp_locale->get_weekday( 4 ) ),
			'friday_initial'			=> $wp_locale->get_weekday_initial( $wp_locale->get_weekday( 5 ) ),
			'saturday_initial'			=> $wp_locale->get_weekday_initial( $wp_locale->get_weekday( 6 ) ),
			'pending'					=> __('Pending', 'wp-base'),
			'pending_approval'			=> __('Pending Approval', 'wp-base'),
			'pending_payment'			=> __('Pending Payment', 'wp-base'),
			'running'					=> __('In Progress', 'wp-base'),
			'paid'						=> __('Paid', 'wp-base'),
			'confirmed'					=> __('Confirmed', 'wp-base'),
			'completed'					=> __('Completed', 'wp-base'),
			'reserved'					=> __('Reserved by GCal', 'wp-base'),
			'removed'					=> __('Removed', 'wp-base'),
			'test'						=> __('Test', 'wp-base'),
			'waiting'					=> __('Waiting List', 'wp-base'),
			'cart'						=> __('In Cart', 'wp-base'),
			'hold'						=> __('Temporary', 'wp-base'),
			'too_late'					=> __('We are sorry, but it is too late to cancel or modify this booking.','wp-base'),
			'cancel_disabled'			=> __('Cancellation of appointments is disabled. Please contact website admin.','wp-base'),
			'edit_disabled'				=> __('Editing of appointments is disabled. Please contact website admin.','wp-base'),
			'seats'						=> __('Pax', 'wp-base'),
			'pax'						=> __('Pax', 'wp-base' ),
			'participants'				=> __('Participants', 'wp-base' ),
			'participant_title'			=> __('%d. Guest','wp-base'),
			'nop_placeholder'			=> __('Name of the %d. guest', 'wp-base' ),
			'mop_placeholder'			=> __('email of the %d. guest', 'wp-base' ),
			'pop_placeholder'			=> __('Phone of the %d. guest', 'wp-base' ),
			'aop_placeholder'			=> __('Address of the %d. guest', 'wp-base' ),
			'not_enough_capacity'		=> __('We are sorry, but we do not have enough capacity to fulfil the request at the moment. Please refresh the page and try other time slots. Thank you.', 'wp-base'),
			'not_published'				=> __('Booking is not possible at the moment.', 'wp-base'),
			'no_free_time_slots'		=> __('Not available', 'wp-base'),
			'quota'						=> __('Sorry, but you have reached the booking quota. No additional bookings are allowed.', 'wp-base'),
			'past_date'					=> __('You cannot select a past date/time.', 'wp-base'),
			'not_working'				=> __('Sorry, service or provider is not available for the selected date/time. Please pick another time.', 'wp-base'),
			'not_available'				=> __('This time slot has been already booked. Please pick another time.', 'wp-base'),
			'not_possible'				=> __('Action or selection is not possible.', 'wp-base'),
			'timezone_title'			=> __('Select your timezone', 'wp-base'),
			'use_server_timezone'		=> __('Use Server Timezone', 'wp-base'),
			'countdown_title'			=> __('Your next appointment', 'wp-base'),
			'conf_countdown_title'		=> __('For you we are holding this slot as long as:', 'wp-base'),
			'select_theme'				=> __('Theme', 'wp-base'),
			'search'					=> __('Search', 'wp-base'),
			'info'						=> __('Showing page _PAGE_ of _PAGES_', 'wp-base'),
			'length_menu'				=> __('Display _MENU_ bookings', 'wp-base'),
			'balance'					=> __('Balance', 'wp-base'),
			'deposit'					=> __('Deposit', 'wp-base'),
			'down_payment'				=> __('Amount to pay now', 'wp-base'),
			'total_paid'				=> __('Paid', 'wp-base'),
			'username'					=> __('Username', 'wp-base'),
			'password'					=> __('Password', 'wp-base'),
			'register'					=> __('Register', 'wp-base'),
			'paypal_express_note'		=> __('Please confirm your final payment for this order totaling %s. It will be made via your %s PayPal account.', 'wp-base'),
			'cc_email'					=> __('Email', 'wp-base'),
			'cc_name'					=> __('Full Name', 'wp-base'),
			'cc_address1'				=> __('Address Line 1', 'wp-base'),
			'cc_address2'				=> __('Address Line 2', 'wp-base'),
			'cc_city'					=> __('City', 'wp-base'),
			'cc_state'					=> __('State/Province/Region', 'wp-base'),
			'cc_zip'					=> __('Postcode', 'wp-base'),
			'cc_country'				=> __('Country', 'wp-base'),
			'cc_phone'					=> __('Phone', 'wp-base'),
			'cc_number'					=> __('Credit Card Number', 'wp-base'),
			'cc_expiry'					=> __('Expiration Date', 'wp-base'),
			'cc_cvv'					=> __('Security Code', 'wp-base'),
			'cc_declined'				=> __('There was a problem with your submission. Please try again. Error message: %s', 'wp-base'),
			'invalid_cc_number'	 		=> __('Please enter a valid Credit Card Number.', 'wp-base' ),
			'invalid_expiration'		=> __('Please choose a valid Expiration Date.', 'wp-base' ),
			'invalid_cvc'				=> __('Please enter a valid Card CVC', 'wp-base' ),
			'expired_card'		 		=> __('Card is no longer valid or has expired', 'wp-base' ),
			'invalid_cardholder' 		=> __('Invalid cardholder', 'wp-base' ),
			'processing' 				=> __('Processing...', 'wp-base' ),
			'cancel_cart'				=> __('Clear', 'wp-base'),
			'cancel_confirm_text'		=> __('Are you sure to cancel current process?', 'wp-base' ),
			'cancel_confirm_yes'		=> __('Yes, I want to cancel', 'wp-base' ),
			'cancel_confirm_no'			=> __('No, I want to continue', 'wp-base' ),
			'close'						=> __('Close', 'wp-base' ),
			'clear'						=> __('Clear', 'wp-base' ),
			'required'					=> __('Required', 'wp-base' ),
			'next_day'					=> __('Ends next day', 'wp-base' ),
			'bp_title'					=> __('Bookings', 'wp-base'),
			'bp_bookings'				=> __('My Bookings', 'wp-base'),
			'bp_bookings_as_client'		=> __('My Bookings as Client', 'wp-base'),
			'bp_bookings_as_provider'	=> __('As Provider', 'wp-base'),
			'bp_schedules'				=> __('Schedules', 'wp-base'),
			'bp_services'				=> __('Services', 'wp-base' ),
			'bp_wh'						=> __('Working Hours', 'wp-base'),
			'bp_holidays'				=> __('Holidays', 'wp-base'),
			'bp_annual'					=> __('Annual Schedules', 'wp-base'),
			'bp_settings'				=> __('WP BASE Settings', 'wp-base'),
			'bp_book_me'				=> __('Book Me', 'wp-base'),
			'bp_use_book_me'			=> __('Add a Book Me tab', 'wp-base'),	
			'saved'						=> __('Settings saved.', 'wp-base'),
			'deleted'					=> __('Selected record(s) deleted.', 'wp-base'),
			'updated'					=> __('Selected record(s) updated.', 'wp-base'),
			'notice'					=> __('Notice', 'wp-base'),
			'proceed'					=> __('Click OK to proceed.', 'wp-base'),
			'export_csv'				=> __('Export bookings as CSV file','wp-base'),
			'tt_regular_price'			=> __('Regular price','wp-base'),
			'tt_discounted_price'		=> __('Special price for you','wp-base'),
			'tt_coupon'					=> __('Price after coupon applied','wp-base'),
			'price_mismatch'			=> __('We are sorry, our campaign you were eligible previously has recently expired. Please review the regular price and if you agree, please confirm.','wp-base'),
			'price_mismatch_lower'		=> __('We are glad to inform you that we can offer even a better price now. Please review the new price and confirm again.','wp-base'),
			'extra'						=> __('Extra','wp-base'),
			'nof_jobs_total'			=> __('Total Jobs','wp-base'),
			'nof_jobs_completed'		=> __('Completed Jobs','wp-base'),
			'nof_jobs_cancelled'		=> __('Cancelled Jobs','wp-base'),
			'nof_jobs_remaining'		=> __('Remaining Jobs','wp-base'),
			'login_for_cancel'			=> __('Please login in order to cancel the appointment','wp-base'),
			'login_for_edit'			=> __('Please login in order to edit the booking','wp-base'),
			'login_for_confirm'			=> __('Please login in order to confirm the booking','wp-base'),
			'add_to_cart'				=> __('Add to Cart', 'wp-base'),
			'auto_assign_login'			=> __('Please login to be assigned as a service provider','wp-base'),
			'auto_assign_intro'			=> sprintf( __('Congratulations! You are a service provider of SITE_NAME now. Using this page you can set your profile, arrange working hours and define services you want to give.', 'wp-base'), wp_specialchars_decode(get_option('blogname'), ENT_QUOTES) ),
			'pdf'						=> __('Pdf','wp-base'),
			'pdf_download'				=> __('Download','wp-base'),
			'pdf_tooltip'				=> __('Confirmation in pdf form will be downloaded to your PC.','wp-base'),
			'updating'					=> __('Updating...','wp-base'),
			'reading'					=> __('Reading data...','wp-base'),
			'booking'					=> __('Processing booking...','wp-base'),
			'saving'					=> __('Saving...','wp-base'),
			'refreshing'				=> __('Refreshing','wp-base'),
			'calculating'				=> __('Calculating','wp-base'),
			'checkout'					=> __('Processing checkout...','wp-base'),
			'preparing_timetable'		=> __('Checking available times...','wp-base'),
			'preparing_form'			=> __('Preparing form...','wp-base'),
			'logging_in'				=> __('Preparing login...','wp-base'),
			'done'						=> __('Done...','wp-base'),
			'gdpr_userdata_title'		=> __('User Info for Booking','wp-base'),
			'gdpr_udf_title'			=> __('Additional User Info for Booking','wp-base'),
			'yes'						=> __('Yes','wp-base'),
			'no'						=> __('No','wp-base'),

		);
		
		$this->default_texts = apply_filters( 'app_default_texts', $this->default_texts );
		
		if ( null === $key )
			return $this->default_texts;

		if ( isset( $this->default_texts[$key] ) )
			return $this->default_texts[$key];
		else
			return '';
	}

	/**
	 *	Add "Custom Texts" tab
	 */
	function add_tab( $tabs ) {
		$tabs['custom_texts'] = __('Custom Texts', 'wp-base');
		return $tabs;
	}

	/**
	 * Save settings 
	 */
	function save_settings() {

		if ( 'save_custom_texts' != $_POST["action_app"] )
			return;
		
		$saved = false;
		
		if ( isset( $_POST['app_custom_texts'] ) && is_array( $_POST['app_custom_texts'] ) ) {
			foreach ( $_POST['app_custom_texts'] as $key=>$custom_text ) {
				if ( isset( $_POST['app_custom_texts'][$key] ) )
					$this->texts[$key] = trim( sanitize_text_field( $custom_text ) ); // Immediately update cache
			}		
			if( update_option( 'wp_base_texts', $this->texts, false ) )	// Do not autoload
				$saved = true;
		}
		
		if ( isset( $_POST['replace_texts'] ) ) {
			if ( update_option( 'wp_base_replace_texts', trim($_POST['replace_texts']) ) )	// Do not autoload
				$saved = true;			
		}
		
		do_action( 'app_custom_texts_maybe_updated' );
		
		if ( $saved )
			wpb_notice( 'saved' );
	}
	
	/**
	 * Admin settings HTML code 
	 */
	function settings() {

		wpb_admin_access_check( 'manage_display_settings' );
		
		add_action( 'admin_footer', array( $this, 'admin_footer' ), 100 );
		
		?>
		
		<div class='wrap app-page'>
			<div id="poststuff" class="metabox-holder">
				<?php
				
				wpb_infobox( __( 'Here you can quickly customize <b>admin side</b> terms and localize (translate) some or all of the <b>front end</b> texts (javascript messages, column names, day names, field titles, etc).', 'wp-base'),
									__( '<b>Admin side text replace</b> is intended for quick terminology change, not for translation. To make a full localization for the admin side, use a translation tool, e.g. POEdit, instead.','wp-base' )
									.'<br />'.
									__( '<b>Front end text replace</b> usage: Search for the original text using the Search field. Matching results will be dynamically filtered. Enter your custom text in "Your Text" field for the desired text. Repeat this for every text you wish. Click Save at the end. If you are using a caching plugin, do not forget to clear the cache. Now, your text will be in effect. If no custom text is entered for a certain message or front end field, then the default text will be used.', 'wp-base')
									.'<br />'.
									__( 'Note for <b>shortcode texts:</b> When a shortcode parameter (e.g. monthly_title: Title of monthly calendar) is not explicitly set, it will be replaced by your custom text here or the default text if no custom text is entered. For such parameters the text is selected as: 1) Text entered in the shortcode for the parameter, 2) If (1) is not set, custom text, 3) If (2) is not set, the default text.', 'wp-base')
								);
							
				do_action( 'app_admin_custom_texts_after_info' );				
				?>
				<form class="app_form" method="post" action="<?php echo wpb_add_query_arg(null,null) ?>">
				
				<?php remove_filter( 'gettext', array( $this, 'global_text_replace' ) ); ?>
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Admin Side Text Replace', 'wp-base') ?></span></h3>
				<table class="widefat">
				<tr>
					<td>
					<textarea placeholder="<?php _e('Enter find|replace pairs. Example: service provider|specialist, provider|specialist, services|facilities, service|facility','wp-base') ?>" cols="90" style="width:100%" name="replace_texts"><?php echo stripslashes( get_option( 'wp_base_replace_texts' ) ); ?></textarea>
					<br />
					<span class="description app_bottom"><?php _e('Here you can enter old text|new text pairs to replace a term globally on admin side. Separate multiple entries by comma, e.g. "service provider|specialist, service|facility" will replace all instances of "service provider" with "specialist" and "service" with "facility". Replace is done from left to right, therefore if there are coinciding words, use more complex one earlier, e.g. service provider before service. If the replacement matches a word and its first letter is uppercase, it is replaced as well. For example, Service will be replaced with Facility.', 'wp-base') ?></span>
					</td>
				</tr>
				</table>
				</div>
				<?php add_filter( 'gettext', array( $this, 'global_text_replace' ), 10, 3 ); ?>
			
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Front End Text Replace', 'wp-base') ?></span></h3>
					<div class="inside">
						<table class="widefat fixed striped app-custom-texts">
							<thead>
							<tr>
								<th style="width:12%; text-align:left;"><?php _e( 'Name', 'wp-base') ?></th>
								<th style="width:28%; text-align:left;"><?php _e( 'Description', 'wp-base') ?></th>
								<th style="width:28%; text-align:left;"><?php _e( 'Default Text (Not editable)', 'wp-base') ?></th>
								<th style="width:28%; text-align:left;"><?php _e( 'Your Text (Editable)', 'wp-base') ?></th>
							</tr>
							</thead>
						<?php
						$default_texts = $this->get_default_texts( );
						$default_texts = array_merge( $default_texts, $this->add_default_texts );
						ksort( $default_texts );
						foreach ( $default_texts as $key=>$default_text ) {
							?>
							<tr><td tabindex="-1">
								<?php echo $key ?>
							</td><td>
							<?php  
								echo $this->get_help_text( $key );
							?>
							</td><td><textarea cols="45" style="width:95%" readonly="readonly"><?php echo stripslashes( $default_text ) ?></textarea>
							</td><td>
								<textarea cols="45" style="width:95%" name="app_custom_texts[<?php echo $key ?>]"><?php 
									echo esc_textarea(stripslashes( $this->get_custom_texts( $key ) ));
							?></textarea>
							<?php do_action( 'app_admin_custom_texts_after_editable', $key, $default_text ) ?>
							</td>
							</tr>
							<?php
						}
						?>
						</table>
					</div>
				</div>
				<?php 
				echo wp_nonce_field( 'update_app_settings', 'app_nonce', true, false ); 
				?>
				<p class="submit">
					<div class="app_custom_texts_hidden" style="display:none"></div>
					<input type="hidden" value="save_custom_texts" name="action_app" />
					<input type="submit" class="button-primary app_custom_texts_submit" value="<?php _e('Save Custom Texts','wp-base'); ?>" />
				</p>
				
				</form>
			</div>
		
		</div>
		<?php
	}
	
	function admin_footer(){
		if ( !empty( $this->script_added ) )
			return;
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			var sstring = "<?php echo isset( $_GET['app_s'] ) ? urldecode( $_GET['app_s'] ) : '' ?>";
			
			$('textarea:visible').autosize();
			// Do not give navigation away alert for search and no of items fields
			$.extend( $.fn.dataTableExt.oStdClasses, {
				"sFilterInput": "app_no_save_alert",
				"sLengthSelect": "app_no_save_alert"
			});
			dt_api = $('.app-custom-texts').DataTable({"dom": 'T<"app_clear">lfrtip',
					"oSearch": { "sSearch": sstring },
					"tableTools": {
						"sSwfPath":_app_.tabletools_url
					},
			fnInitComplete: function ( oSettings ) {
				var dttt = jQuery("div.DTTT_container");
				dttt.css("float","left").css("margin","0 50px");
				var dt_length = $(".app-page").find(".dataTables_length").first();
				dt_length.after(dttt[0]).css("height","auto");
				
			},
			"lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
			"autoWidth": true,
			"bAutoWidth": true, 
			"responsive": true});
			
			$(".app_custom_texts_submit").click( function(e){
				e.preventDefault();
				var par = $(this).parents("form");
				var search = par.find("input[type=search]").val();
				var action = par.attr("action");
				par.attr("action",action+"&app_s="+search);
				
				// Submit hidden fields
				// https://datatables.net/plug-ins/api/fnGetHiddenNodes
				var nodes;
				var display = par.find('tbody tr');
				nodes = dt_api.rows().nodes().toArray();
				/* Remove nodes which are being displayed */
				for ( var i=0 ; i<display.length ; i++ ) {
					var iIndex = $.inArray( display[i], nodes );

					if ( iIndex != -1 ) {
						nodes.splice( iIndex, 1 );
					}
				}				
				par.find(".app_custom_texts_hidden").append(nodes);
				par.submit();
			});
		});
		</script>
		<?php
		
		$this->script_added = true;
	}
	
}	

	BASE('CustomTexts')->add_hooks();
}

