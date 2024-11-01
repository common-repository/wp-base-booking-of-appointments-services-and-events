<?php
/**
 * WPB Constant
 *
 * Default help texts, default templates, default settings which are loaded on demand and usually on admin side 
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;

class WpBConstant {
	
	public static $min_user_id = null;

	public static $_confirmation_message = "Dear CLIENT,

We are pleased to confirm your appointment for SITE_NAME.

Here are the appointment details:
Booking ID: APP_ID
Requested service: SERVICE
Date and time: DATE_TIME TIMEZONE

WORKER will assist you for this service.

Kind regards,
SITE_NAME";

	public static $_confirmation_text = "Dear CLIENT,

We confirm your appointment:
Booking ID: APP_ID
Requested service: SERVICE
Date and time: DATE_TIME TIMEZONE

WORKER will assist you for this service.

A confirmation email will be sent to the submitted email address EMAIL soon.

Kind regards,
SITE_NAME";

	public static $_pending_message = "Dear CLIENT,

We have received your appointment submission for SITE_NAME.

Here are the details of the appointment you have requested:
Reference Number: APP_ID
Requested service: SERVICE
Date and time: DATE_TIME TIMEZONE

MANUAL_PAYMENT_NOTE

We will confirm your appointment as soon as possible and we will send a confirmation message to EMAIL

Kind regards,
SITE_NAME";

	public static $_completed_message = "Dear CLIENT,

Your appointment for SITE_NAME you have made on CREATED has been just completed.
Booking ID: APP_ID
Completed service: SERVICE
Date and time: DATE_TIME TIMEZONE

Thank you for your interest in our services. We would like to give service to you again.

Kind regards,
SITE_NAME";

	public static $_cancellation_message = "Dear CLIENT,

Your appointment for SITE_NAME has been just cancelled.

Here are the details for the cancelled appointment:
Booking ID: APP_ID
Requested service: SERVICE
Date and time: DATE_TIME TIMEZONE

Kind regards,
SITE_NAME";

	public static $_reminder_message = "Dear CLIENT,

We would like to remind your appointment with SITE_NAME.

Here are your appointment details:
Booking ID: APP_ID
Requested service: SERVICE
Date and time: DATE_TIME TIMEZONE

WORKER will assist you for this service.

Kind regards,
SITE_NAME";

	public static $_dp_reminder_message = "Dear CLIENT,

Our accounting system shows a negative balance of BALANCE regarding the booking you made on CREATED.

Here are the appointment details:
Booking ID: APP_ID
Requested service: SERVICE
Date and time: DATE_TIME TIMEZONE

If you have already made a payment, please ignore this email.
 
Kind regards,
SITE_NAME";

	public static $_follow_up_message = "Dear CLIENT,

We have not heard from you after END_DATE_TIME. 

We would like to inform you that our services, including SERVICE, are even better now and it will be a great pleasure for us to serve you. 
 
If you have any inquiries please do not hesitate to contact us,
SITE_NAME";

	public static $_waiting_list_message = "Dear CLIENT,

We have received your appointment submission for SITE_NAME.

Here are the details of the appointment you have requested:
Reference Number: APP_ID
Requested service: SERVICE
Date and time: DATE_TIME TIMEZONE

Your submission has been added to our waiting list. If the required time slot becomes available we will send a notification message to EMAIL in which there will be information about how you shall proceed.

Please note that such an availability is upto other guests rescheduling their bookings. Thank you for your understanding.

Kind regards,
SITE_NAME";

	public static $_waiting_list_notify_message = "Dear CLIENT,

We are glad to inform you that there is an opening for your appointment submission you made on CREATED for SITE_NAME.

Here are the details of the appointment you have requested:
Reference Number: APP_ID
Requested service: SERVICE
Date and time: DATE_TIME TIMEZONE

Please click the below link to confirm your previous submission and follow the instructions there:
WAITING_LIST_LINK

We have to emphasize that availability is limited and there may be other guests also applied to the requested time frame. Our system works with first-come-first-served basis.

Therefore it is for your own benefit to act immediately before this opportunity fades away. 

Kind regards,
SITE_NAME";

	public static $_dummy_content = "Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?

Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.	
";	
	
	public static $_confirmation_message_sms = "Dear CLIENT, we confirm your booking with ref. APP_ID on DATE_TIME for SERVICE. WORKER will assist you. Thanks, SITE_NAME";
	public static $_pending_message_sms = "Dear CLIENT, thanks for SERVICE submission for DATE_TIME. Confirmation will be sent to EMAIL soon. SITE_NAME";
	public static $_completed_message_sms = "Dear CLIENT, SERVICE appointment you booked on CREATED has been completed. We hope to see you again. Thanks, SITE_NAME";
	public static $_cancellation_message_sms = "Dear CLIENT, your appointment with reference APP_ID on DATE_TIME for SERVICE has been cancelled. Regards, SITE_NAME";
	public static $_reminder_message_sms = "Dear CLIENT, this is a reminder for appointment (ref: APP_ID) on DATE_TIME for SERVICE that will be given by WORKER. SITE_NAME";

	public static $_confirmation_message_sms_admin = "New booking (ref: APP_ID) on SITE_NAME on DATE_TIME for SERVICE/WORKER by CLIENT (PHONE)";
	public static $_pending_message_sms_admin = "New pending booking (ref: APP_ID) on SITE_NAME on DATE_TIME for SERVICE/WORKER by CLIENT (PHONE)";
	public static $_completed_message_sms_admin = "Completed appointment (ref: APP_ID) on SITE_NAME created on CREATED for SERVICE/WORKER by CLIENT (PHONE)";
	public static $_cancellation_message_sms_admin = "Cancelled appointment (ref: APP_ID) on SITE_NAME for SERVICE/WORKER on DATE_TIME by CLIENT (PHONE)";
	public static $_reminder_message_sms_worker = "Upcoming appointment (ref: APP_ID) on SITE_NAME for client CLIENT (PHONE) on DATE_TIME for SERVICE";
	public static $_cancellation_message_sms_worker = "Cancelled appointment (ref: APP_ID) on SITE_NAME for SERVICE/WORKER on DATE_TIME by CLIENT (PHONE)";


	public static $_terms ='Please read these Terms of Service ("Terms", "Terms of Service") carefully before using the HOME_URL website (the "Service") operated by <b>SITE_NAME</b> ("us", "we", or "our").
Your access to and use of the Service is conditioned on your acceptance of and compliance with these Terms. These Terms apply to all visitors, users and others who access or use the Service.
By accessing or using the Service you agree to be bound by these Terms. If you disagree with any part of the terms then you may not access the Service.

Accounts

When you create an account with us, you must provide us information that is accurate, complete, and current at all times. Failure to do so constitutes a breach of the Terms, which may result in immediate termination of your account on our Service.
You are responsible for safeguarding the password that you use to access the Service and for any activities or actions under your password, whether your password is with our Service or a third-party service.
You agree not to disclose your password to any third party. You must notify us immediately upon becoming aware of any breach of security or unauthorized use of your account.

Links To Other Web Sites

Our Service may contain links to third-party web sites or services that are not owned or controlled by <b>SITE_NAME</b>.
<b>SITE_NAME</b> has no control over, and assumes no responsibility for, the content, privacy policies, or practices of any third party web sites or services. You further acknowledge and agree that <b>SITE_NAME</b> shall not be responsible or liable, directly or indirectly, for any damage or loss caused or alleged to be caused by or in connection with use of or reliance on any such content, goods or services available on or through any such web sites or services.
We strongly advise you to read the terms and conditions and privacy policies of any third-party web sites or services that you visit.

Termination

We may terminate or suspend access to our Service immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.
All provisions of the Terms which by their nature should survive termination shall survive termination, including, without limitation, ownership provisions, warranty disclaimers, indemnity and limitations of liability.
We may terminate or suspend your account immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.
Upon termination, your right to use the Service will immediately cease. If you wish to terminate your account, you may simply discontinue using the Service.
All provisions of the Terms which by their nature should survive termination shall survive termination, including, without limitation, ownership provisions, warranty disclaimers, indemnity and limitations of liability.

Governing Law

These Terms shall be governed and construed in accordance with the laws of United States, without regard to its conflict of law provisions.
Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights. If any provision of these Terms is held to be invalid or unenforceable by a court, the remaining provisions of these Terms will remain in effect. These Terms constitute the entire agreement between us regarding our Service, and supersede and replace any prior agreements we might have between us regarding the Service.

Changes

We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material we will try to provide at least 30 days notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion.
By continuing to access or use our Service after those revisions become effective, you agree to be bound by the revised terms. If you do not agree to the new terms, please stop using the Service.

Contact Us

If you have any questions about these Terms, please contact us.';

	public static function privacy_content() { return __('We collect information about you during the checkout process on our website. This information may include, but is not limited to, your name, email address, phone number and any other details that might be requested from you for the purpose of processing your orders.
Handling this data also allows us to:
- Send you important account/order/service information.
- Respond to your queries, requests, or complaints.
- Process payments and to prevent fraudulent transactions. We do this on the basis of our legitimate business interests.
- Set up and administer your account, provide technical and/or customer support, and to verify your identity.

Additionally we may also collect the following information:
- Location and traffic data (including IP address and browser type) if you place an order, or if we need to estimate costs and/or legal requirements based on your location.
- Account email/password to allow you to access your account, if you have one.
- If you choose to create an account with us or if an account is automatically created for you, your name, address, and email address, which will be used to populate the checkout for future orders.',
'wp-base' ); }

	public static function gcal_description() {
		return __("Client Name: CLIENT
Client email: EMAIL
Client Phone: PHONE
Service Name: SERVICE
Service Provider Name: WORKER", 'wp-base');
	}

	public static function gcal_description_client() {
		return __("Service Name: SERVICE
Service Provider Name: WORKER", 'wp-base');
	}

	/**
     * Provide default values for the settings
	 * @param $include_templates	Include email and SMS templates or not. If 'only_templates' then return only templates
	 * @param $item: Pick the required default item. Leave empty for all defaults.
	 * @param $el: Pick the required array element from the selected item. 1 for name, 2 for description of the setting. 
	 * @since 2.0
 	 * @return mixed
     */
	public static function defaults( $include_templates=true, $item = null, $el=0 ) {
		global $wpdb, $wp_locale;
		if ( self::$min_user_id !== null )
			$default_worker = self::$min_user_id;
		else {
			$default_worker = self::$min_user_id = $wpdb->get_var( "SELECT MIN(ID) FROM $wpdb->users" );
		}
		$login_methods = function_exists( 'wpb_login_methods' ) ? implode( ',', wpb_login_methods() ) : ''; // All checked at startup
		$editable = implode( ',', array('location','service','worker','date','time') );
		$blog_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		
		$defaults = array(
			'refresh_url'					=> array( '', __('Return (Thank You) Page', 'wp-base'), __('ID or url of the post/page that client will be returned after succesful submission or payment of the booking. If left empty, page will be refreshed (or client will be returned to the same page after gateway website). Note: 1) If you enter a numeric value, it should be ID of a public post/page from your website. 2) If you enter a url, it can be external too. 3) In case of a payment gateway error, instead of this page client is redirected to the page they made booking from.', 'wp-base') ),
			'min_time'						=> array( 60, __('Time base', 'wp-base'), __('Minimum time that can be selectable for service durations, paddings, manual appointment and working hours intervals. Service durations and paddings can only be set as multiples of this value. This value also determines setting resolution of starting times from on the hour: For example, if you want 9:15am, 10:15am, etc time slots, even if your service durations are 60 minutes, you should select 15 minutes. "Auto" setting will try to find the optimum (maximum possible value). For best performance, it is recommended to set this value to auto after you completed settings for services. Default: 1 hour', 'wp-base') ),
			'app_lower_limit'				=> array( 0, __('Booking submission lower limit - Lead Time (hours)', 'wp-base'), __('This setting will block potentially available time slots for the set time value starting from current time. For example, if you need 2 days to evaluate and accept an appointment submission, enter 48 here. Default: 0 (no blocking - bookings can be made if start time has not passed)', 'wp-base') ),
			'app_limit'						=> array( 365, __('Booking submission upper limit', 'wp-base'), __('Maximum number of days or months from current day (excluding current day) that a client can make a booking. Month selection will handle staying in target month. For example with 2 months limit, on 28th, 29th, 30th and 31st of December 2017, bookings can be made for 28th of February 2018 utmost. Tip: To turn off all services for booking, e.g. during a maintenance, you can enter a negative number here. Default: 365 days', 'wp-base') ),
			'app_limit_unit'				=> array( 'day', '', '' ),
			/* http://stackoverflow.com/questions/21444721/paypal-payments-standard-expiration */
			'clear_time'					=> array( 24, __('Pending Approval bookings auto removal time (hours)', 'wp-base'), __('Pending bookings that require manual approval, in other words which do not have a payment method or payment method is manual payment, will be automatically removed (not deleted) when this period of time has been passed counting from booking submission. As a result corresponding time slot will be freed. Enter 0 to disable. Default: 24. Note: Pending and GCal reserved bookings whose starting time have been passed will be immediately removed, regardless of this or any other setting.', 'wp-base') ),
			'clear_time_pending_payment'	=> array( 15, __('Pending Payment bookings auto removal time (mins)', 'wp-base'), __('Same as above, but applies to bookings that has a payment method other than manual payment. If left empty or set to 0, the above "Pending bookings auto removal time" setting will be applied. If both settings are 0, then auto removal of pending bookings will be disabled. Default: 15', 'wp-base') ),
			'countdown_time'				=> array( 12, __('Maximum Allowed Checkout Time (mins)', 'wp-base'), __('Multiple Appointments addons block time slots temporarily while client is making their selections. This is the maximum time client is allowed to finish shopping and checkout. If enabled, there will be a dynamic countdown on confirmation form displaying how much time has been left. The idea of this limitation is freeing blocked time slots if client silently abandons shopping, without hitting the cancel button. As countdown expires, page will be refreshed, temporary records will be cleared and blocked time slots will be freed. Enter 0 to disable (not recommended). Default: 12', 'wp-base'), 'MA' ),
			'preselect_latest_service'		=> array( 'no', __('Remember Client\'s Latest Service Selection', 'wp-base'), __('Whether automatically select last booked service by the client.', 'wp-base'),'Extended Service Features & Categories' ),
			'time_slot_calculus_legacy'		=> array( 'no', __('Use Time Base in Time Slot Calculus', 'wp-base'), __('By default (setting as "No") time slots will be generated based on service duration, e.g. 8:00, 9:00, etc for a 60 minutes service. If you want time slots to be created based on "Time Base" instead, set this setting as Yes and set Time Base accordingly and do NOT use "auto". Note that all service durations should be divisible by Time Base. For example for a time base of 30 minutes, you will have time slots of 8:00, 8:30, 9:00, 9:30, etc in this case.', 'wp-base'),'Extended Service Features & Categories' ),
			// 'apply_paddings'				=> array( 'no', __('Always Apply Paddings to Time Slot Creation', 'wp-base'), __('By default (setting as "No") paddings are only applied before and/or after existing bookings. If you select Yes, paddings will be taken into account even there are no bookings. This may effectively reduce available time slots, but guaranteeing buffer before and/or after a possible booking.', 'wp-base'),'Extended Service Features & Categories' ),
			'preselect_latest_worker'		=> array( 'yes', __('Remember Client\'s Latest Provider Selection', 'wp-base'), __('Whether automatically select last booked provider by the client, if possible.', 'wp-base'),'Service Providers' ),
			'default_worker'				=> array( $default_worker, __('Business Representative', 'wp-base'), __('If no providers defined, appointments will be assigned to this user. Also his working hours will be used as template for new providers and services.', 'wp-base'), 'Service Providers' ),
			'client_selects_worker'			=> array( 'auto', __('Client Selects Service Provider', 'wp-base'), __('Whether service provider pulldown menu is displayed so that client can pick a service provider giving the service. If "forced to pick one", "No preference" option will not be displayed. If "may leave unselected" and client does not pick one, WP BASE will automatically assign an SP with "Assignment Method of Service Provider" setting.', 'wp-base'), 'Service Providers' ),
			'assign_worker'					=> array( 'random', __('Assignment Method of Service Provider', 'wp-base'), __('How service provider is assigned to the service if not selected by client. If selected as First Available or Random, first available or a random service provider is assigned, respectively. If selected as Business representative, he will be assigned (If he is not available, a random provider among those giving that service will be picked). Note: 1) First Available Provider setting uses the order you made on the SP list. If the one on top of the list (who is giving the service) is not available, then the second one is tested. If he is not available either, the 3rd one is tested and so on. 2) If capacity is increased and all possible SPs are busy but there is still available capacity, booking will be accepted and a provider will not be assigned. This is the normal, intended behaviour. Example: Capacity is set to 3 and there are 2 SPs and 2 bookings at the time slot. A 3rd booking will be allowed, SP=0 will be assigned. A 4th booking will not be allowed.', 'wp-base'), 'Service Providers' ),
			'auto_confirm'					=> array( 'yes', __('Auto confirm', 'wp-base'), __('Setting this as Yes will automatically confirm all booking submissions if payment is not required or service is free. Setting as No will keep new bookings in Pending status. Note: "Payment required" case will still require a payment, provided that price > 0. If price is zero, then auto confirm setting will determine booking status.', 'wp-base') ),
			'allow_confirm'					=> array( 'yes', __('Allow client confirm appointments by email', 'wp-base'), __('Whether to allow clients confirm (Change status from "pending" to "confirmed") their appointments using the link in any email they receive. This link is added by using CONFIRM placeholder in email bodies. Please note that this can also be used to verify provided email address. Note: Admin and service provider will get a notification email.', 'wp-base') ),
			'allow_now'						=> array( 'no', __('Allow late booking', 'wp-base'), __('Setting this as Yes will allow booking of a time slot when current time is within selected time slot, i.e. appointment start time has been passed, but it has not ended yet.', 'wp-base') ),
			'late_booking_time'				=> array( '', __('Late booking permission time (mins)', 'wp-base'), __('If late booking is allowed, Defines number of minutes that booking can still be made counting from start of the appointment. Leaving empty means booking is accepted until the last minute before appointment finishes.', 'wp-base') ),
			'allow_cancel'					=> array( 'no', __('Allow client cancel own appointments', 'wp-base'), __('Whether to allow clients cancel their appointments using the link in confirmation and reminder emails or using Booking List table or for logged in users, using check boxes in their profile pages. For the email case, you will also need to add CANCEL placeholder to the email message content. Note: Admin and service provider will always get a notification email in case of a cancellation.', 'wp-base') ),
			'allow_worker_cancel'			=> array( 'no', __('Providers can Cancel Own Appointments', 'wp-base'), __('Whether to allow providers cancel their appointments using using Booking List table or using check boxes in their profile pages. Cancellation lead time is still valid.', 'wp-base'), 'Service Providers' ),
			'cancel_limit'					=> array( 24, __('Cancellation limit (hours)', 'wp-base'), __('Number of hours upto which client can cancel the appointment relative to the appointment start time. For example, entering 24 will disable cancellations one day before the appointment is due. In such a case any cancellation request will be replied with "Too late to cancel" response.', 'wp-base') ),
			'cancel_page'					=> array( 0, __('Appointment cancelled page', 'wp-base'), __('In case he is cancelling using the email link, the page that client will be redirected after cancellation.', 'wp-base') ),
			'login_required'				=> array( 'no', __('Login required', 'wp-base'), __('Whether you require the client to login the website to apply for an appointment.', 'wp-base') ),
			'login_methods'					=> array( $login_methods, __('Front end login methods','wp-base'), __('Select which front end methods will be provided to the client to use to login. If login required and no methods selected, client is supposed to login using other methods, e.g. wp-login page. If this is the case, by default Login shortcode provides link to WordPress login page. After login, client will be redirected back to the page where they clicked the link.','wp-base') ),
			'facebook-no_init'				=> array( 0, __('My website already uses Facebook','wp-base'), __('By default, Facebook script will be loaded by the plugin. If you are already running Facebook scripts, to prevent any conflict, check this option.','wp-base') ),
			'facebook-app_id'				=> array( '', __('Facebook App ID','wp-base'), sprintf(__("Enter your App ID number here. If you don't have a Facebook App yet, you will need to create one <a href='%s' target='_blank'>here</a>", 'wp-base'), 'https://developers.facebook.com/apps') ),
			'twitter-app_id'				=> array( '', __('Twitter Consumer Key','wp-base'), sprintf(__('Enter your Twitter App ID number here. If you don\'t have a Twitter App yet, you will need to create one <a href="%s" target="_blank">here</a>', 'wp-base'), 'https://dev.twitter.com/apps/new') ),
			'twitter-app_secret'			=> array( '', __('Twitter Consumer Secret','wp-base'), __('Enter your Twitter App ID Secret here.', 'wp-base') ),
			'google-client_id'				=> array( '', __('Google Client ID','wp-base'), sprintf( __('Enter your Google Client ID here (OAuth 2.0 client ID). If you don\'t have Google Client ID yet, you will need to create one <a href="%s" target="_blank">here</a>', 'wp-base'), 'https://developers.google.com/+/web/api/rest/oauth') ),
			'show_legend'					=> array( 'yes', __('Show Legend', 'wp-base'), __('Whether to display description fields above the pagination (next/previous dates buttons) area.', 'wp-base') ),
			'hide_busy'						=> array( 'no', __('Hide Busy Status', 'wp-base'), __('If you select "Yes", busy slots will be shown as unavailable instead of having a separate color.', 'wp-base') ),
			'theme'							=> array( 'start', __('Theme', 'wp-base'), sprintf( __('jQuery UI theme that will be used in calendar, table, datepicker, dialog, multi select dropdown, tooltip and button elements on the Front End. For examples <a href="%s" target="_blank">click here</a> (select "Gallery" there).', 'wp-base'), 'http://jqueryui.com/themeroller/' ) ),
			'conf_form_hidden_fields'		=> array( '', __('Hidden Fields on Confirmation Form','wp-base'), __('Selected fields will not be displayed in the form. Fields with empty values are not displayed even not selected as hidden here. For example zero price is not displayed.','wp-base'), 'Advanced Features' ),
			'conf_form_hide_cancel'			=> array( '', __('Hide Cancel Button on Confirmation Form','wp-base'), __('Hiding Cancel button may improve conversion rates. However, we do not recommend this when WP BASE Shopping Cart is active, because Cancel button also functions as "Empty Cart".','wp-base'), 'Advanced Features' ),
			'conf_form_title_position'		=> array( 'same', __('Title fields position','wp-base'), __('Titles in the confirmation form user fields can be selected as above or at the same line with input fields.','wp-base'), 'Advanced Features' ),
			'addon_fields_position'			=> array( 'before_user_fields', __('Addon Fields Position on Confirmation Form', 'wp-base'), __('With this setting, fields related to addons, e.g. coupon selection, extra selection can be presented in different positions on the form.', 'wp-base'), 'Advanced Features' ),
			'payment_method_position'		=> array( 'after_booking_fields', __('Payment Methods Position on Confirmation Form', 'wp-base'), __('With this setting, payment method selection can be presented in different positions on the form. If you have three or more active payment gateways, we recommend "full row" selection.', 'wp-base'), 'Advanced Features' ),
			'hide_effect'					=> array( 'scale', __('Effect When Closing Dialogs', 'wp-base'), sprintf( __('jQuery UI %s when closing a jQuery UI dialog.', 'wp-base'), '<a href="http://jqueryui.com/effect/" class="app_bottom" target="_blank">'.__('effect','wp-base').'</a>' ), 'Advanced Features' ),
			'show_effect'					=> array( 'drop', __('Effect When Opening Dialogs', 'wp-base'), sprintf( __('jQuery UI %s when opening a jQuery UI dialog.', 'wp-base'), '<a href="http://jqueryui.com/effect/" class="app_bottom" target="_blank">'.__('effect','wp-base').'</a>' ), 'Advanced Features' ),
			'spinner'						=> array( '', __('Spinner', 'wp-base'), __('Spinner displayed in the info panel during ajax calls.', 'wp-base'), 'Advanced Features' ),
			'ask_terms'						=> array( 'no', __('Enable', 'wp-base'), __('Whether to enable terms and conditions checkbox on confirmation form. If enabled, client needs to check the checkbox (accept terms) to continue to checkout.', 'wp-base'), 'Advanced Features' ),
			'terms_label'					=> array( 'By clicking this checkbox you accept our {Terms and Conditions}', __('Label', 'wp-base'), sprintf( __('This is the text beside terms and conditions checkbox. On the front end, clicking on the words inside curly brackets will open the Terms & Conditions dialog. Example: <code>I accept {Terms and Conditions}.</code> becomes <code>I accept %s.</code>', 'wp-base'), '<a href="javascript:void(0)" class="app-open-terms app_bottom">'.__('Terms and Conditions','wp-base').'</a>' ), 'Advanced Features' ),
			'mobile_theme'					=> array( 'jquery-mobile', __('Mobile Theme', 'wp-base'), __('jQuery UI theme that will be used in calendar, table, datepicker, dialog, multi select, tooltip and button elements on the Front End when client is connected with a mobile device (As default, tablets are not considered as mobile).', 'wp-base'), 'Advanced Features' ),
			'swatch'						=> array( 'a', __('Color Scheme (Swatch)', 'wp-base'), __('Color scheme (also called "swatch") for mobile devices. Selected theme may not have all swatches. Then "a" is selected.', 'wp-base'), 'Advanced Features' ),
			'admin_theme'					=> array( 'smoothness', __('Admin Theme', 'wp-base'), __('jQuery UI theme that will be used in calendar, table, datepicker, dialog, multi select, tooltip and button elements on the Admin side.', 'wp-base'), 'Advanced Features' ),
			'color_set'						=> array( 'start', __('Time Slot Colors', 'wp-base'), __('There are suggested color sets here which will match with the theme, but you can select any other set too or you can enter your custom colors using color picker, settings of which are visible after you select "Custom".', 'wp-base') ),
			'free_color'					=> array( '48c048', '', '' ),
			'has_appointments_color'		=> array( 'ffa500', '', '' ),
			'busy_color'					=> array( 'ffffff', '', '' ),
			'notpossible_color'				=> array( 'ffffff', '', '' ),
			'ask_name'						=> array( '1', '', '' ),
			'ask_first_name'				=> array( '', '', '' ),
			'ask_last_name'					=> array( '', '', '' ),
			'ask_email'						=> array( '1', '', '' ),
			'ask_phone'						=> array( '1', '', '' ),
			'ask_address'					=> array( '', '', '' ),
			'ask_city'						=> array( '', '', '' ),
			'ask_zip'						=> array( '', '', '' ),
			'ask_state'						=> array( '', '', '' ),
			'ask_country'					=> array( '', '', '' ),
			'ask_note'						=> array( '1', '', '' ),
			'ask_remember'					=> array( '', '', '' ),
			'admin_email'					=> array( get_option('admin_email'), __('Admin email(s)', 'wp-base'), __('You can enter a special admin email here. Multiple emails separated with comma is possible. If left empty, WordPress admin email setting will be used.', 'wp-base') ),
			'from_name'						=> array( $blog_name, __('From name', 'wp-base'), __('Name that will be used in "from name" field of outgoing emails. If left empty, blog name will be used.', 'wp-base') ),
			'from_email'					=> array( '', __('From email', 'wp-base'), __('Email address that will be used in "from" field of outgoing emails. If left empty, no-reply@yourdomain will be used.', 'wp-base') ),
			'log_emails'					=> array( 'yes', __('Log Sent email Records', 'wp-base'), sprintf( __('Whether to log confirmation and reminder email records in the %s (Not the content of the emails).', 'wp-base'), '<a class="app_bottom" href="'.admin_url("admin.php?page=app_tools&amp;tab=log").'">'.__('log file').'</a>') ),
			'use_html'						=> array( 'yes', __('Use HTML in emails', 'wp-base'), __('Selecting this as Yes will allow HTML codes, e.g. images, colors, fonts, etc. to be used in emails.', 'wp-base') ),
			'send_confirmation'				=> array( 'yes', __('Send Confirmation email (Single)', 'wp-base'), __('Whether to send an email after confirmation of the appointment. Note: Admin and service provider will also get a copy as separate emails.', 'wp-base') ),
			'send_confirmation_bulk'		=> array( 'no', __('Send Confirmation email (Bulk)', 'wp-base'), __('Send Confirmation email to the client(s) when "bulk" status change of confirmed or paid is applied to appointments on admin side. ', 'wp-base') ),
			'send_pending'					=> array( 'yes', __('Send Pending email to the Client (Single)', 'wp-base'), __('Whether to send an email after an appointment has been booked in pending status.', 'wp-base') ),
			'send_pending_bulk'				=> array( 'no', __('Send Pending email to the Client (Bulk)', 'wp-base'), __('Send email to the client(s) when "bulk" status change of pending is applied to appointments on admin side. ', 'wp-base') ),
			'send_notification'				=> array( 'yes', __('Send Pending email to Admin', 'wp-base'), __('You may want to receive a notification email whenever a new appointment is made from front end in pending status. This email is only sent if your approval is required. Note: Notification email is also sent to the service provider, if they are allowed to confirm. That is, "Allow Service Provider Confirm Own Appointments" is set as Yes.', 'wp-base') ),
			'send_completed'				=> array( 'no', __('Send Completed email (Single)', 'wp-base'), __('Whether to send an email after an appointment has been completed.', 'wp-base') ),
			'send_completed_bulk'			=> array( 'no', __('Send Completed email (Bulk)', 'wp-base'), __('Send email to the client(s) when "bulk" status change of completed is applied to appointments on admin side. ', 'wp-base') ),
			'send_cancellation'				=> array( 'yes', __('Send Cancellation email (Single)', 'wp-base'), __('Whether to send an email after cancellation of the appointment. Note: Admin and service provider will also get a copy as separate emails.', 'wp-base') ),
			'send_cancellation_bulk'		=> array( 'no', __('Send Cancellation email (Bulk)', 'wp-base'), __('Same as above but whether to send emails to clients when "bulk" status change of removed is applied to appointments on admin side.', 'wp-base') ),
			'send_reminder'					=> array( 'yes', __('Send Reminder email to the Client', 'wp-base'), __('Whether to send reminder email(s) to the client before the appointment.', 'wp-base'), 'Reminder and Follow-up emails' ),
			'reminder_time'					=> array( '24', __('Reminder email Sending Time for the Client (hours)', 'wp-base'), __('Defines how many hours  before the appointment start time reminder email will be sent to the client. Multiple reminders are possible. To do so, enter reminding hours separated with a comma, e.g. 48,24. Note: Reminder email is not sent if booking is made after reminder is due. For example, if booking has been done 36 hours before the appointment, 48-hours-before reminder will not be sent, 24-hours-before reminder will be.', 'wp-base'), 'Reminder and Follow-up emails' ),
			'send_reminder_worker'			=> array( 'yes', __('Send Reminder email to the Provider', 'wp-base'), __('Whether to send reminder email(s) to the service provider before the appointment.', 'wp-base'), 'Reminder and Follow-up emails' ),
			'reminder_time_worker'			=> array( '4', __('Reminder email Sending Time for the Provider (hours)', 'wp-base'), __('Same as Reminder email Sending Time for the Client, but defines the time for service provider.', 'wp-base'), 'Reminder and Follow-up emails' ),
			'send_dp_reminder'				=> array( 'yes', __('Send Due Payment Reminder email to the Client', 'wp-base'), __('Whether to send due payment reminder email(s) to the clients in intervals selected below. This email is only sent for the selected appointment statuses when balance (total payments minus total price for the appointment) is negative and its absolute value is greater than the amount selected below.', 'wp-base'), 'Reminder and Follow-up emails' ),
			'dp_reminder_limit'				=> array( '', sprintf( __('Due Payment Reminder Sending Limit of Balance (%s)', 'wp-base'), BASE()->get_options('currency', 'USD') ), __('Due payment reminder is only sent if balance is negative and absolute value of balance for the appointment is greater than this amount. For example, if this value is set as 10$, an appointment with -9$ balance will not result to a reminder email, but -11$ will. Leave empty if you want to remind client in case of any negative balance.', 'wp-base'), 'Reminder and Follow-up emails' ),
			'dp_reminder_statuses'			=> array( 'paid,confirmed,completed', __('Appointment Statuses Due Payment emails Applied to', 'wp-base'), __('Only clients having appointments with selected status(es) will receive due payment reminder email. If none selected, due payment emails will not be sent at all.', 'wp-base'), 'Reminder and Follow-up emails' ),
			'dp_reminder_time'				=> array( '72,48', __('Due Payment Reminder email Sending Time (hours)', 'wp-base'), __('Defines the time in hours that reminder email will be sent after the appointment has been booked (creation time). Note that this is different than appointment reminder email where appointment start time is taken as reference. Multiple reminders are possible. To do so, enter reminding hours separated with a comma, e.g. 48,72.', 'wp-base'), 'Reminder and Follow-up emails' ),
			'dp_reminder_subject'			=> array( '', __('Due Payment Reminder email Subject', 'wp-base'), '', 'Reminder and Follow-up emails' ),
			'dp_reminder_message'			=> array( '', __('Due Payment Reminder email Message', 'wp-base'), '', 'Reminder and Follow-up emails' ),
			'send_follow_up'				=> array( 'yes', __('Send Follow-up email to the Client', 'wp-base'), __('Whether to send follow-up email(s) to the client', 'wp-base'), 'Reminder and Follow-up emails' ),
			'send_waiting_list'				=> array( 'yes', __('Send Submission email to the Client', 'wp-base'), __('Whether to send an email to the client informing that their submission has been received and added to the waiting list.', 'wp-base'), 'Waiting List' ),
			'send_waiting_list_notify'		=> array( 'yes', __('Send Notification email to the Client', 'wp-base'), __('Whether to send an email to the client informing that there is an opening in the submissions for waiting list. If they applied for more than one slot and those slots become available at the same time, information of openings will be combined and client will receive a single email.', 'wp-base'), 'Waiting List' ),
			'follow_up_time'				=> array( '30,60,120', __('Follow-up email Sending Time (days)', 'wp-base'), __('Defines how many days after no client activity an email will be sent, counting from their latest finalised booking end time (in completed or cancelled status). Multiple mails at different days are possible. To do so, enter desired delay days separated with a comma, e.g. 30,60.', 'wp-base'), 'Reminder and Follow-up emails' ),
			'sms_service'					=> array( '', __('SMS Service', 'wp-base'), __('Service company that will be used to send SMS', 'wp-base'), 'SMS' ),
			'phone_code'					=> array( '', __('Default Dialing Code', 'wp-base'), __('This code will be prepended to the phone numbers starting with 0 and without + or 00.', 'wp-base'), 'SMS' ),
			'admin_phone'					=> array( '', __('Admin phone(s)', 'wp-base'), __('Enter admin phones that will receive notification messages. Multiple phones separated with comma is possible.', 'wp-base'), 'SMS' ),
			'log_sms'						=> array( 'yes', __('Log Sent SMS Records', 'wp-base'), sprintf( __('Whether to log confirmation and reminder SMS records in the %s (Not the content of the messages).', 'wp-base'), '<a class="app_bottom" href="'.admin_url("admin.php?page=app_tools&amp;tab=log").'">'.__('log file').'</a>'), 'SMS' ),
			'twilio_from'					=> array( '+15005550006', __('Twilio "From" phone', 'wp-base'), sprintf( __('A Twilio phone number (in E.164 format) or alphanumeric sender ID enabled for the type of message you wish to send. For details %s. With test credentials, use this number: +15005550006', 'wp-base'), '<a href="https://www.twilio.com/docs/api/rest/sending-messages#post-parameters-conditional" target="_blank">'.__('click here','wp-base').'</a>' ), 'SMS' ),
			'twilio_account_id'				=> array( '', __('Twilio Account ID', 'wp-base'), sprintf( __('Get your Account ID/Auth Token pair after you login %s and enter here. If you use test credentials, SMS sending will not be realised, but just be simulated. You can check the results in log file or in your Twilio account.', 'wp-base'), '<a href="https://www.twilio.com/user/account/settings" target="_blank">'.__('your Twilio account','wp-base').'</a>' ), 'SMS' ),
			'twilio_auth_token'				=> array( '', __('Twilio Auth Token', 'wp-base'), sprintf( __(' ', 'wp-base'), '<a href="https://www.twilio.com/user/account/settings" target="_blank">'.__('your Twilio account','wp-base').'</a>' ), 'SMS' ),
			'plivo_from'					=> array( '', __('Plivo "Source" phone', 'wp-base'), sprintf( __('A source phone number if you wish to send SMS to US and Canada phones. For details %s.', 'wp-base'), '<a href="https://manage.plivo.com/dashboard/" target="_blank">'.__('click here','wp-base').'</a>' ), 'SMS' ),
			'plivo_account_id'				=> array( '', __('Plivo Account ID', 'wp-base'), sprintf( __('Get your Account ID/Auth Token pair after you login %s and enter here.', 'wp-base'), '<a href="https://manage.plivo.com/dashboard/" target="_blank">'.__('your Plivo account','wp-base').'</a>' ), 'SMS' ),
			'plivo_auth_token'				=> array( '', __('Plivo Auth Token', 'wp-base'), sprintf( __(' ', 'wp-base'), '<a href="https://www.twilio.com/user/account/settings" target="_blank">'.__('your Twilio account','wp-base').'</a>' ), 'SMS' ),
			'nexmo_from'					=> array( '', __('Nexmo "From" phone', 'wp-base'), sprintf( __('A Nexmo registered phone number. For details %s', 'wp-base'), '<a href="https://help.nexmo.com/hc/en-us/articles/204017023-USA-Direct-route-Features-Restrictions" target="_blank">'.__('click here','wp-base').'</a>' ), 'SMS' ),
			'nexmo_api_key'					=> array( '', __('Nexmo API Key', 'wp-base'), sprintf( __('Get your API Key/Secret pair after you login %s and enter here', 'wp-base'), '<a href="https://dashboard.nexmo.com/settings" target="_blank">'.__('your Nexmo account','wp-base').'</a>' ), 'SMS' ),
			'nexmo_api_secret'				=> array( '', __('Nexmo API Secret', 'wp-base'), sprintf( __('Get your API Key/Secret pair after you login %s and enter here', 'wp-base'), '<a href="https://dashboard.nexmo.com/settings" target="_blank">'.__('your Nexmo account','wp-base').'</a>' ), 'SMS' ),
			'send_confirmation_sms'			=> array( 'yes', __('Send Confirmation SMS', 'wp-base'), __('Whether to send an SMS after confirmation of the appointment.', 'wp-base'), 'SMS' ),
			'send_confirmation_sms_bulk'	=> array( 'no', __('Send Confirmation SMS (Bulk)', 'wp-base'), __('Send Confirmation SMS to the client(s) when "bulk" status change of confirmed or paid is applied to appointments on admin side. ', 'wp-base'), 'SMS' ),
			'send_confirmation_sms_worker'	=> array( 'yes', __('Send Confirmation SMS (Provider)', 'wp-base'), __('Whether to send an SMS after confirmation of the appointment.', 'wp-base'), 'SMS' ),
			'send_confirmation_sms_admin'	=> array( 'yes', __('Send Confirmation SMS (Admin)', 'wp-base'), __('Whether to send an SMS after confirmation of the appointment.', 'wp-base'), 'SMS' ),
			'send_pending_sms'				=> array( 'yes', __('Send Pending SMS', 'wp-base'), __('Whether to send an SMS after an appointment has been booked in pending status.', 'wp-base'), 'SMS' ),
			'send_pending_sms_bulk'			=> array( 'no', __('Send Pending SMS (Bulk)', 'wp-base'), __('Send SMS to the client(s) when "bulk" status change of pending is applied to appointments on admin side. ', 'wp-base'), 'SMS' ),
			'send_pending_sms_worker'		=> array( 'no', __('Send Pending SMS (Provider)', 'wp-base'), __('Whether to send an SMS after an appointment has been booked in pending status.', 'wp-base'), 'SMS' ),
			'send_pending_sms_admin'		=> array( 'yes', __('Send Pending SMS (Admin)', 'wp-base'), __('Whether to send an SMS after an appointment has been booked in pending status.', 'wp-base'), 'SMS' ),
			'send_completed_sms'			=> array( 'no', __('Send Completed SMS', 'wp-base'), __('Whether to send an SMS after an appointment has been completed.', 'wp-base'), 'SMS' ),
			'send_completed_sms_bulk'		=> array( 'no', __('Send Completed SMS (Bulk)', 'wp-base'), __('Send SMS to the client(s) when "bulk" status change of completed is applied to appointments on admin side. ', 'wp-base'), 'SMS' ),
			'send_completed_sms_worker'		=> array( 'no', __('Send Completed SMS (Provider)', 'wp-base'), __('Whether to send an SMS after an appointment has been completed.', 'wp-base'), 'SMS' ),
			'send_completed_sms_admin'		=> array( 'no', __('Send Completed SMS (Admin)', 'wp-base'), __('Whether to send an SMS after an appointment has been completed.', 'wp-base'), 'SMS' ),
			'send_reminder_sms'				=> array( 'yes', __('Send Reminder SMS to the Client', 'wp-base'), __('Whether to send reminder SMS message(s) to the client before the appointment.', 'wp-base'), 'SMS' ),
			'reminder_time_sms'				=> array( '24', __('Reminder SMS Sending Time for the Client (hours)', 'wp-base'), __('Defines how many hours  before the appointment will take place reminder SMS will be sent to the client. Multiple reminders are possible. To do so, enter reminding hours separated with a comma, e.g. 48,24. Note: Reminder SMS is not sent if booking is made after reminder is due. For example, if booking has been done 36 hours before the appointment, 48-hours-before reminder will not be sent, 24-hours-before reminder will be.', 'wp-base'), 'SMS' ),
			'send_reminder_sms_worker'		=> array( 'yes', __('Send Reminder SMS to the Provider', 'wp-base'), __('Whether to send reminder SMS message(s) to the service provider before the appointment.', 'wp-base'), 'SMS' ),
			'reminder_time_sms_worker'		=> array( '4', __('Reminder SMS Sending Time for the Provider (hours)', 'wp-base'), __('Same as Reminder SMS Sending Time for the Client, but defines the time for service provider.', 'wp-base'), 'SMS' ),
			'send_cancellation_sms'			=> array( 'yes', __('Send Cancellation SMS', 'wp-base'), __('Whether to send an SMS after cancellation of the appointment.', 'wp-base'), 'SMS' ),
			'send_cancellation_sms_bulk'	=> array( 'no', __('Send Cancellation SMS (Bulk)', 'wp-base'), __('Same as above but whether to send SMS messages to clients when "bulk" status change of removed is applied to appointments on admin side.', 'wp-base'), 'SMS' ),
			'send_cancellation_sms_worker'	=> array( 'yes', __('Send Cancellation SMS (Provider)', 'wp-base'), __('Whether to send an SMS after cancellation of the appointment.', 'wp-base'), 'SMS' ),
			'send_cancellation_sms_admin'	=> array( 'yes', __('Send Cancellation SMS (Admin)', 'wp-base'), __('Whether to send an SMS after cancellation of the appointment.', 'wp-base'), 'SMS' ),
			'payment_required'				=> array( 'no', __('Payment required at booking instant', 'wp-base'), sprintf( __('Whether you require a payment to accept appointments at the moment of booking submission. If selected Yes, client is asked to pay through Paypal or another selected payment gateway and the appointment will be in pending status until payment is confirmed by the gateway. If selected No, appointment will be in pending status until you manually approve it using the %s unless Auto Confirm is set as Yes. Also see %s setting. Note: Manual Payments gateway is a special case for which status should be updated manually, e.g. when client pays at your shop locally, when you cash client\'s check, etc.', 'wp-base'), '<a href="'.admin_url('admin.php?page=appointments').'">'.__('Bookings page', 'wp-base').'</a>', '<a href="'.admin_url('admin.php?page=app_settings#auto-confirm').'">'.__('Auto confirm', 'wp-base').'</a>' ) ),
			'currency'						=> array( 'USD', __('Website Currency - Symbol', 'wp-base'), '' ),
			'base_country'					=> array( 'US', __('Country Business Based in', 'wp-base'), '' ),
			'tax'							=> array( '', __('Tax (%)', 'wp-base'), __('Tax, e.g. VAT, in percent. WP BASE assumes that your prices already include tax. This setting will only be used to calculate "price without tax" value.', 'wp-base') ),
			'curr_symbol_position' 			=> array( 1, __('Currency Symbol Position', 'wp-base'), '' ),
			'curr_decimal' 					=> array( 1, __('Show Decimal in Prices', 'wp-base'), '' ),
			'thousands_separator'			=> array( $wp_locale->number_format['thousands_sep'], __('Thousands Separator', 'wp-base'), '' ),
			'decimal_separator'				=> array( $wp_locale->number_format['decimal_point'], __('Decimal Separator', 'wp-base'), '' ),
			'cancel_return'					=> array( get_option('home'), __('Cancel Return Page', 'wp-base'), __('The page that client will be returned when he clicks the cancel link on Paypal website.', 'wp-base') ),
			'item_name'						=> array( __('Payment for SERVICE', 'wp-base'), __('Item name', 'wp-base'), sprintf( __('Description of item on Paypal and 2checkout purchase pages. <abbr title="%s">Email placeholders</abbr> can be used. For example, <code>Payment for booking #APP_ID on DATE_TIME</code>', 'wp-base'), WpBConstant::email_desc(0) ), 'PayPal Standard' ),
			'percent_downpayment'			=> array( '', __('Prepayment (%)', 'wp-base'), __('You may want to ask a certain percentage of the service price as prepayment (Also called down payment or advance payment - not to be confused with security deposit), e.g. 25. Leave this field empty to ask for full price.', 'wp-base') ),
			'fixed_downpayment'				=> array( '', __('Prepayment (fixed)', 'wp-base'), __('Similar to percent prepayment, but a fixed amount will be asked from the client per appointment. If both fields are filled, only the fixed down payment will be taken into account. Tip: The down payment calculations are the same for every service. To have different formula for each selected service, you can use Custom Pricing Addon. ', 'wp-base') ),
			'add_deposit'					=> array( '', __('Add Security Deposit to Total Amount', 'wp-base'), __('By default (setting No), security deposits are not collected using payment gateways due to difficulties in refunding. If you select Yes, deposit will be added to the amount that the client will pay through the payment gateway.', 'wp-base') ),
			'members_no_payment' 			=> array( '', __('Don\'t ask Prepayment from Privileged User Roles', 'wp-base'), __('Below selected role(s) will not be asked for a down payment. This does not necessarily mean that service will be free of charge for them. Such member appointments are automatically confirmed. Tip: This setting allows certain roles, e.g. known, trusted clients, to be exempt from a down payment to make a booking. If you want to apply special price to selected roles, use Custom and Variable Pricing addon.', 'wp-base') ),
			'members_discount'				=> array( '', __('Discount for Privileged User Roles (%)', 'wp-base'), __('Selected role(s) will get a discount given in percent, e.g. 20. Leave this field empty for no discount. Tip: If you enter 100, service will be free of charge for these members.', 'wp-base') ),
			'members'						=> array( '', __('Privileged User Roles', 'wp-base'), __('Selected role(s) will not be asked advance payment, depending on the above selection.', 'wp-base') ),
			'additional_min_time'			=> array( '', __('Additional Time Base (minutes)', 'wp-base'), __('If selectable time bases do not fit your business, you can add a new one, e.g. 90. Note: 1) After you save this additional time base, you must select it using the time base setting in the General tab. 2) Minimum allowed time base setting is 5 minutes. 3) Entered value should be divisible by 5. For example, 24 is not allowed and it will be rounded to 25.', 'wp-base') ),
			'admin_min_time'				=> array( '', __('Manual Booking Time Base (minutes)', 'wp-base'), __('This setting may be used to provide flexibility while manually setting and editing the appointments. For example, if you enter here 20, you can re-assign an appointment to 3 different providers for 20 minutes intervals even selected time base is 60 minutes. If you leave this empty, then time base setting on General tab will be applied also on the manual bookings.', 'wp-base') ),
			'spam_time'						=> array( 0, __('Minimum Time to Pass for New Appointment (secs)', 'wp-base'), __('You can limit appointment application frequency to prevent spammers who can block your appointments. This is only applied to pending appointments. Enter 0 to disable. Tip: To prevent any further appointment applications of a client before a payment or manual confirmation, enter a huge number here.', 'wp-base') ),
			'allow_worker_wh'				=> array( 'no', __('Providers can Set Own Working Hours', 'wp-base'), __('Whether you let service providers to set their working hours and holidays using their navigation tab in BuddyPress (Requires BuddyPress addon) or their profile page in regular WordPress. Also allows editing working hours of self created services.', 'wp-base'), 'Service Providers' ),
			'allow_worker_annual'			=> array( 'no', __('Providers can Set Own Annual Schedules', 'wp-base'), __('Requires Seasonal and Alternating Work Hours Addon. Whether you let service providers to set their annual schedules using their navigation tab in BuddyPress (Requires BuddyPress addon) or their profile page in regular WordPress. They are also allowed to add new alternative schedules, but not to delete them.', 'wp-base'), 'Service Providers' ),
			'allow_worker_confirm'			=> array( 'no', __('Providers can Confirm Own Appointments', 'wp-base'), __('Whether you let service providers to confirm pending appointments assigned to them using their navigation tab in BuddyPress (Requires BuddyPress addon) or their profile page in regular WordPress.', 'wp-base'), 'Service Providers' ),
			'allow_worker_edit'				=> array( 'no', __('Providers can Edit Own Appointments', 'wp-base'), __('Whether you let service providers to edit own appointments on the admin side. Tip: To allow editing on the front end instead of admin side, use Front End Management Addon.', 'wp-base'), 'Service Providers' ),
			'allow_worker_create_service'	=> array( 'no', __('Providers can Create/Edit Own Services', 'wp-base'), __('If you select yes, providers can create and edit services on their profile pages. They can view and edit only services created by themselves. Admin can view and edit all services on Business Settings. Provider will be automatically assigned to services they created. With Extended Service Features addon, provider can also create categories, but cannot delete them.', 'wp-base' ), 'Service Providers' ),
			'allow_worker_delete_service'	=> array( 'no', __('Providers can Delete Own Services', 'wp-base'), __('If you select yes, providers can delete any service created by themselves using their profile pages. If you select No, deletion by service provider is not allowed. If you select If Empty, provider can delete own services only if there are no past or future bookings for that service (except test appointments).', 'wp-base'), 'Service Providers' ),
			'dummy_assigned_to'				=> array( 0, __('Assign Dummy Service Providers to', 'wp-base'), __('You can define "Dummy" service providers to enrich your service provider alternatives and variate your working schedules. They will behave exactly like ordinary users except the emails they are supposed to receive will be forwarded to the user you select here. Note: 1) You cannot select another dummy user. It must be a user which is not set as dummy. 2) Using dummies is a legacy method and in new projects it is not recommended. You may be able to get the same functionality by increasing capacity and setting service working hours.', 'wp-base'), 'Service Providers' ),
			'duration_format'				=> array( 'hours_minutes', __('Service Duration Display Format', 'wp-base'), __('With this setting, you can select display format of durations on the front end (minutes, hours, hours+minutes).', 'wp-base') ),
			'disable_css'					=> array( 'no', __('Disable css Files (Front End)', 'wp-base'), sprintf( __('If you have your own styling for the front end, you may want to disable css files coming with WP BASE by selecting Yes. Tip: If you want to disable just the plugin specific css file (front.css) and keep the other css files belonging to javascript libraries, leave this setting as No, prepare your own front.css file and upload it to the folder %s. Then your front.css file will be taken into account instead of front.css of the plugin. This method will prevent your css file being overwritten with plugin updates.', 'wp-base'), BASE()->custom_folder() .'css/' ), 'Advanced Features' ),
			'additional_css'				=> array( '', __('Additional css Rules (Front end)', 'wp-base'), __('You can add css rules to customize styling. These will be added to the front end appointment page(s) only.', 'wp-base'), 'Advanced Features' ),
			'disable_css_admin'				=> array( 'no', __('Disable css Files (Admin Side)', 'wp-base'), sprintf( __('If you have your own styling for the admin side, you may want to disable css files coming with WP BASE by selecting Yes. Tip: If you want to disable just the plugin specific css file (admin.css) and keep the other css files belonging to javascript libraries, leave this setting as No, prepare your own admin.css file and upload it to the folder %s. Then your admin.css file will be taken into account instead of admin.css of the plugin. This method will prevent your css file being overwritten with plugin updates.', 'wp-base'), BASE()->custom_folder() .'css/' ), 'Advanced Features' ),
			'additional_css_admin'			=> array( '', __('Additional css Rules (Admin side)', 'wp-base'), __('You can add css rules to customize styling. These will be added to the admin side only, e.g. to user profile page.', 'wp-base'), 'Advanced Features' ),
			'location_post_type'			=> array( 'page', __('Post Type for Location Description Pages', 'wp-base'), __('Post type that will be used for location descriptions. Default: "page"', 'wp-base') ),
			'description_post_type'			=> array( 'page', __('Post Type for Service Description Pages', 'wp-base'), __('Post type that will be used for service descriptions. Default: "page"', 'wp-base') ),
			'bio_post_type'					=> array( 'page', __('Post Type for Service Provider Bio Pages', 'wp-base'), __('Post type that will be used for service provider bio pages. Default: "page"', 'wp-base') ),
			'records_per_page'				=> array( '', __('Number of Booking Records per Page', 'wp-base'), __('Number of records to be displayed on admin bookings and transactions pages, i.e. number of bookings and transactions per page. If left empty: 20.', 'wp-base') ),
			'records_per_page_business'		=> array( '', __('Number of Business Records per Page', 'wp-base'), __('Number of records to be displayed on business settings page, i.e. number of locations, services and service providers per page. If left empty: 10.', 'wp-base') ),
			'error_tracking'				=> array( 'no', __('Javascript Error Tracking', 'wp-base'), __('When you enable this option, if there is a Javascript error on the front end or the admin side, it will be displayed as an admin notice.', 'wp-base') ),
			'debug_mode'					=> array( defined('WP_DEBUG') && WP_DEBUG ? 'yes' : 'no', __('Debug Mode', 'wp-base'), __('Displays information about configuration errors and time slots (why they are not available) in tooltips and on the page. These texts are only visible to admins. Therefore debug mode can stay enabled without affecting the clients.', 'wp-base') ),
			'cache'							=> array( '', __('Native Object Caching', 'wp-base'), __('Saving time consuming query results may help loading of pages having Booking Views faster. To enable internal object caching select "On". Selection "On + Preload" will try to automatically preload selected pages after existing cache is expired, improving first visit load time. Native object caching feature is different than what caching plugins do and it can be used in combination with supported plugins to get even better results.', 'wp-base') ),
			'preload_pages'					=> array( '', __('Pages to be Preloaded', 'wp-base'), __('When caching option "On + Preload" is selected, these pages will be preloaded.', 'wp-base') ),
			'lazy_load'						=> array( 'yes', __('Lazy Load Calendars', 'wp-base'), __('Enabling Lazy Loading defers creating of calendars until page is fully loaded, preventing any search engine penalties due to slow page load. Then calendars are automatically updated by ajax. This is especially recommended if booking area is outside user viewpoint as user will not notice calendar is created later.', 'wp-base') ),
			'lsw_priority'					=> array( 'SLW', __('Location/Service/Provider Menu Priority', 'wp-base'), __('Determines Location/Service/Provider menus behavior when Locations and/or Service Providers are active. Less priority menus will follow higher ones.', 'wp-base'),'Extended Service Features & Categories' ),
			'preselect_first_service'		=> array( 'yes', __('Preselect First Possible Service', 'wp-base'), __('By default (setting "Yes"), Make an Appointment pages load by assuming that first possible service has been preselected, therefore booking tables and calendars are not left empty and booking is ready as page loads. By setting "No" you can change this behaviour, e.g. in order to force client to pick a service themselves. Note: 1) First possible service is normally the one on the top of the Services list. You can change the sorting of this list to make your desired service as first or "default" service. 2) When Locations and/or Service Providers are active, this setting will also be valid for them.', 'wp-base'),'Extended Service Features & Categories' ),
			'log_settings'					=> array( 'yes', __('Log Setting Changes', 'wp-base'), sprintf( __('Whether any change in global settings will be recorded in the %s. If selected, user who made the change, time of the change and old and new values are also saved.', 'wp-base'), '<a class="app_bottom" href="'.admin_url("admin.php?page=app_tools&amp;tab=log").'">'.__('log file').'</a>') ),
			'strict_check'					=> array( 'yes', __('Strict Check for Manual Entries', 'wp-base'), __('If this option is selected as Yes, manual booking entries will be checked against availability of the service and service provider and they will be rejected in case client cannot be served. If selected as "No", admin has unrestricted access over manual booking entries.', 'wp-base') ),
			'admin_edit_collapse'			=> array( 'yes', __('Collapse Record after Successful Update', 'wp-base'), __('If this option is selected as Yes, successfully updated record will be automatically collapsed in admin bookings page. If selected as No, clicking Cancel button is required for the same process.', 'wp-base') ),
			'admin_toolbar'					=> array( 'yes', __('Add WP BASE to Admin Toolbar', 'wp-base'), __('If this option is selected as Yes, WP BASE menu items and pages with WP BASE shortcodes can be selected from admin toolbar.', 'wp-base') ),
			'schedule_content'				=> array( 'CLIENT_LINK START_TIME-END_TIME', __('Template for Booking Schedule Items', 'wp-base'), sprintf( __('Bookings in %1$s will be displayed according to this template. <abbr title="%2$s">Booking placeholders</abbr> will be replaced by their real values.', 'wp-base'), '<a class="app_bottom" href="'.admin_url("admin.php?page=app_schedules").'">'.__('Booking Schedules').'</a>', WpBConstant::email_desc(0) ) ),
			'auto_delete'					=> array( 'no', __('Auto Delete Expired Appointments', 'wp-base'), __('As default (setting as "No"), expired appointments (those did not realise because of cancellation or no confirmation) are marked as "removed" and kept in the database until you manually delete them. If you set this setting as Yes, such appointments will be automatically deleted.', 'wp-base') ),
			'auto_delete_time'				=> array( 24, __('Auto Delete Lead Time (Hours)', 'wp-base'), __('Waiting time for expired appointments to be permanently deleted after their expiration. If you set 0, they will be directly deleted before they are marked as removed.', 'wp-base') ),
			'is_tablet_mobile'				=> array( 'no', __('Consider tablets as mobile', 'wp-base'), __('If selected as yes, tablets can use mobile phone specific features, e.g. swipe function and mobile themes.', 'wp-base') ),
			'app_page_type'					=> array( 'two_months_half', '', '' ),
			'gcal_location'					=> array( '', __('Google Calendar Location','wp-base'), __('Enter the text that will be used as location field in Google Calendar. If left empty, your website description is sent instead. Note: You can use ADDRESS, CITY, POSTCODE, COUNTRY placeholders which will be replaced by their real values taken from user submitted form data.', 'wp-base'), 'Google Calendar' ),
			'gcal_button'					=> array( 'yes', __('Google Calendar Button Settings', 'wp-base'), __('Whether to let client access his Google Calendar account using Google Calendar button. Button is inserted in List of Bookings shortcode and user page/tab if applicable.', 'wp-base'), 'Google Calendar' ),
			'gcal_service_name'				=> array( 'GCal Event', __('Service Name (Internal only)','wp-base'), __('Imported Google Calendar events are saved as WP BASE bookings, so that they can reserve working hours of the providers. A virtual, uneditable, undeletable, admin side only service is used for this purpose. You can change the name of this service.', 'wp-base'), 'Google Calendar' ),
			'gcal_api_scope'				=> array( 'all', __('Bookings will be sent to Google Calendar for', 'wp-base'), __('If you select "Any booking", all bookings made from this website will be sent to the selected calendar. If you select "Unassigned bookings", only bookings which do not have an assigned service provider will be sent. Note: Unassigned bookings may happen if you add them manually or capacity of a service has been increased and either there is no service provider defined for that service or existing ones are unavailable.', 'wp-base'), 'Google Calendar' ),
			'gcal_status_for_insert'		=> array( 'paid,confirmed', __('Booking statuses for which events will be created and updated', 'wp-base'), __('Select booking statuses for which a corresponding event will be created/updated in Google Calendar. If none selected, no events will be created. Tip: If you want to just import events from GCal, but not export bookings to GCal, uncheck all.', 'wp-base'), 'Google Calendar' ),
			'gcal_status_for_delete'		=> array( 'pending,removed', __('Booking statuses for which events will be deleted', 'wp-base'), __('Select booking statuses for which corresponding event will be deleted from Google Calendar. If you want to keep even your past events, uncheck all. Note: If service provider has been changed, event will be deleted from calendar of old provider in any case and then it will be moved to the calendar of the new provider.', 'wp-base'), 'Google Calendar' ),
			'gcal_api_allow_worker'			=> array( 'yes', __('Allow Service Providers for Google Calendar API Integration', 'wp-base'), __('Whether you let your service providers to integrate with their own Google Calendar account using their profile page. Note: Each of them will need to set up their accounts following the steps as listed in the Instructions on the following tabs (These instructions will also be shown in their profile pages).', 'wp-base'), 'Google Calendar' ),
			'gcal_allow_worker_summary'		=> array( 'no', __('Allow Service Providers for Event Summary and Description', 'wp-base'), __('Whether you let your service providers to enter own event summary and descriptions using their profile page. If you select "No" (not allowed) the below settings will be used as templates for event summary and description for them. ', 'wp-base'), 'Google Calendar' ),
			'gcal_worker_summary'			=> array( sprintf( __( '%s Appointment','wp-base' ), 'SERVICE' ), __('Event Summary for Providers', 'wp-base'), __('If you do not allow providers to enter their event summary and description, values you enter here will be used as their Event summary and description templates.', 'wp-base'), 'Google Calendar' ),
			'gcal_worker_description'		=> array( self::gcal_description(), __('Event Description for Providers', 'wp-base'), sprintf( __('Same as "Event Summary for Providers" setting. For the above two fields, <abbr title="%s">booking placeholders</abbr> can be used. During export to GCal, these placeholders will be replaced by their real values.', 'wp-base'), WpBConstant::email_desc(0) ), 'Google Calendar' ),
			'gcal_api_allow_client'			=> array( 'no', __('Allow Clients for Google Calendar API Integration', 'wp-base'), __('Whether you let registered clients (WordPress members) to integrate with their own Google Calendar account using their profile page. Note: Each of them will need to set up their accounts following the steps as listed in Instructions (These instructions will also be shown in their profile pages). If you choose "Only allowed user roles", in the below setting you can select user roles that can use GCal integration.', 'wp-base'), 'Google Calendar' ),
			'gcal_api_allow_client_attendee'=> array( 'no', __('Allow Clients to Send Copies of their Bookings to Additional Calendars ', 'wp-base'), sprintf( __('Whether clients can add calendars of other people (e.g. family members) to send a copy of each of their bookings as read-only. For clients, number of additional calendars are internally limited to 100.', 'wp-base'), '<a href="http://docs.simplecalendar.io/find-google-calendar-id/" target="_blank">'.__('calendar ID','wp-base').'</a>'), 'Google Calendar' ),
			'gcal_members'					=> array( array(), __('User Roles Allowed for GCal Integration And Additional Calendars', 'wp-base'), __('If above permission settings are set as "Only allowed user roles", then you can select those role(s) here. Multiple selections are allowed.', 'wp-base'), 'Google Calendar' ),
			'gcal_client_summary'			=> array( sprintf( __( '%s Appointment','wp-base' ), 'SERVICE' ), __('Event Summary for Clients', 'wp-base'), __('Clients are not allowed to enter their event summary and description settings. The values you enter here will be used as their Event summary and description templates.', 'wp-base'), 'Google Calendar' ),
			'gcal_client_description'		=> array( self::gcal_description_client(), __('Event Description for Clients', 'wp-base'), sprintf( __('Same as "Event Summary for Client" setting. For the above two fields, <abbr title="%s">booking placeholders</abbr> can be used. During export to GCal, these placeholders will be replaced by their real values.', 'wp-base'), WpBConstant::email_desc(0) ), 'Google Calendar' ),
			'gcal_api_mode'					=> array( 'none', __('Integration Mode', 'wp-base'), __('Selects method of communication of WP BASE with GCal. WP BASE &rarr; GCal setting sends/exports bookings to your selected Google calendar, but events directly entered in your Google Calendar account are not imported back to WP BASE and thus they do not reserve your available working times. WP BASE &harr; GCal setting works in both directions: In addition to sending bookings to create/update events in GCal, a new created event in GCal will be imported to the WP BASE database and reserve one booking capacity for the event duration. If event is updated, corresponding booking will be updated too. This synchronization is not immediate; it requires at least some traffic to your website and handled around 10 minutes intervals. Note: Before a new booking is accepted, your Google Calendar is re-checked for changes and if the selected time slot is no more available, booking is prevented. Therefore in normal process flow, this 10 minute update frequency will not cause any problem.', 'wp-base'), 'Google Calendar' ),
			'gcal_summary'					=> array( sprintf( __( '%s Appointment','wp-base' ), 'SERVICE' ), __('Event Summary (Name)', 'wp-base'), __('Each booking exported to GCal creates a calendar "event". This template defines name of the event (also known as event summary).', 'wp-base'), 'Google Calendar' ),
			'gcal_description'				=> array( self::gcal_description(), __('Event Description', 'wp-base'), sprintf( __('Each bookings exported to GCal creates a calendar "event". This template defines details of the event, e.g. Service name: SERVICE, etc. For the above two fields, <abbr title="%s">booking placeholders</abbr> can be used. During export to GCal, these placeholders will be replaced by their real values.', 'wp-base'), WpBConstant::email_desc(0) ), 'Google Calendar' ),
			'gcal_attendees'				=> array( '', __('Additional Calendars to Receive Copies of your Bookings', 'wp-base'), sprintf( __('You can add calendars of other people (e.g. co-workers) to send a copy of each booking as read-only (they will have new bookings and updates as events in their calendar, but they cannot change or delete the booking or corresponding event in your calendar). In GCal terminology, these people are known as Event Attendees. You can use their primary calendar (use their email address) or another calendar (use their desired %s in email format). You can add multiple calendars/emails separated by comma. ', 'wp-base'), '<a href="http://docs.simplecalendar.io/find-google-calendar-id/" target="_blank">'.__('calendar ID','wp-base').'</a>'), 'Google Calendar' ),
			'gcal_client_id'				=> array( '', __('Client ID', 'wp-base'), __('Enter Google Client ID', 'wp-base'), 'Google Calendar' ),
			'gcal_client_secret'			=> array( '', __('Client Secret', 'wp-base'), __('Enter Google Client Secret', 'wp-base'), 'Google Calendar' ),
			'gcal_selected_calendar'		=> array( '', __('Calendar to be used', 'wp-base'), __('Select the Google calendar in which your appointments will be saved. Your email address represents your primary calendar.','wp-base'), 'Google Calendar' ),
			'use_cart'						=> array( 'yes', __('Globally Enable Shopping Cart', 'wp-base'), __('Enables shopping cart selection for every applicable shortcode. Tip: If you want to use cart on a particular page, and not on other booking pages, you can do it so by Advanced Features addon: Set "No" here and use [app_confirmation use_cart="yes"]', 'wp-base'), 'Shopping Cart' ),
			'site_langs'					=> array( '', __('Website Languages', 'wp-base'), __('Select all languages that will be used in the website. In paranthesis WordPress locale codes are given.', 'wp-base'), 'Multi Language' ),
			'deposit_cumulative'			=> array( 'no', __('Calculate Deposit as Cumulative', 'wp-base'), __('By default (setting "No") deposit is calculated as maximum among all selected services, e.g. $30 from services requiring deposits of $10, $20, $30. If you select "Yes", then total amount will be asked from the client; in this example, $60.', 'wp-base') ),
			'reminder_email_gap'			=> array( 12, __('Reminder email Gap between Child Bookings (hours)', 'wp-base'), sprintf( __('Time within which reminder emails for %s will not be sent, preventing email flood. For example setting 12 hours will prevent reminder emails if there is another appointment in the same booking whose start time is 12 hours earlier or 12 hours later.', 'wp-base'), '<a class="app_bottom" href="'.admin_url('admin.php?page=app_help#parent-child-bookings').'">'.__('child bookings','wp-base').'</a>'), 'MA' ),
			'apt_count_max'					=> array( '', __('Max Number of Time Slots', 'wp-base'), __('Maximum number of selectable time slots for a single checkout. Client gets a warning message if they attempt to add a time slot exceeding this limit. Enter 0 for no limitation.', 'wp-base'), 'MA' ),
			'apt_count_min'					=> array( '', __('Min Number of Time Slots', 'wp-base'), __('Minimum accepted number of time slots for a single checkout. Different than maximum limit, check is commenced at checkout stage. Entering 0 or leaving empty means 1.', 'wp-base'), 'MA' ),
			'apply_coupon_to_extras'		=> array( 'no', __('Apply Coupons also to Extras', 'wp-base'), __('If selected as yes, coupon discounts will also be applied to extras.', 'wp-base'), 'coupons' ),
			'apply_coupon_once'				=> array( 'no', __('Apply Only One Coupon per Booking', 'wp-base'), __('Valid for shopping cart and if "Apply coupons also to extras" is "No". By default (setting "No"), coupon discounts are applied to each applicable booking in the shopping cart. If selected as yes, only one coupon discount will be applied per cart. If there are more than one matching coupon to the cart contents, the one applying maximum net discount will be used.', 'wp-base'), 'coupons' ),
			// 'action_if_price_mismatch'		=> array( 'if_price_higher', __('Action upon Price Mismatch', 'wp-base'), __('Defines what to do (whether notify client and apply old price) in case of currently calculated price (new price) does not match previously displayed price (old price). As client makes their selections, price on the confirmation form is updated on the fly. Then price is recalculated when client submits the form for checkout. It is unlikely, but still possible that conditions may have changed, so that previously displayed price is no more valid because of expiration of the applied coupon, change of custom pricing conditions, etc. The first 3 selections apply new/correct/current price: By default ("Apply new price - Notify if it is higher"), if new price is higher, a message will be displayed (see price_mismatch in Custom Texts). If you set "Always notify", submission is interrupted with a message with "price_mismatch" or "price_mismatch_lower" custom text depending on new price being higher or lower. Setting "Do not notify" will ignore price difference completely. If message is displayed, client is asked to confirm the new price which is automatically updated on the form. "Apply old price - Do not notify" setting will silently accept old/displayed price.', 'wp-base') , 'Advanced Features' ),
			'allow_edit'					=> array( 'no', __('Allow Client Edit Own Appointments','wp-base'), __('Whether you let client edit own appointments on the front end. Client can activate editing popup form by one of the following methods: 1) Clicking Edit button in WordPress user page, 2) Clicking Edit button in List Of Bookings, 3) Clicking the link in emails. This link is created by inserting EDIT placeholder to the email body.','wp-base'),'Front End Edit & Reschedule' ),
			'edit_limit'					=> array( '', __('Editing Lower Limit (hours)','wp-base'), __('Number of hours from appointment start time until which client can edit their appointment. For example, entering 24 will disable editing one day before the appointment is due. In such a case any editing request will be replied with "Too late" response. Note: Admins and those who have given editing capability with "cap" attribute are not limited with this setting.','wp-base'),'Front End Edit & Reschedule' ),
			'edit_upper_limit'				=> array( 60, __('Editing Upper Limit (days)','wp-base'), __('Number of days from current day that new appointment can take place. If left empty, global Upper Limit will be used.','wp-base'),'Front End Edit & Reschedule' ),
			'editable'						=> array( $editable, __('Editable Booking Fields','wp-base'), __('Select which booking fields can be edited. Note: Editable user fields (email, name, phone, etc) and UDF fields can be limited on Listing shortcode. ','wp-base'),'Front End Edit & Reschedule' ),
			'edit_higher_price_allowed'		=> array( 'yes', __('Allow Client Make Higher Price Selections','wp-base'), __('During edit, whether you allow client make selections that may result in higher total price than original booking price.','wp-base') ),
			'edit_update_price_when_lower'	=> array( 'yes', __('Update Price When Edited Values Result in Lower Price'), __('During and after edit, whether booking price will be updated when edited values result in lower price. You may want to select "No" if you are not willing to offer refunds.','wp-base'),'Front End Edit & Reschedule' ),
			'service_wh_covers'				=> array( 'no', __('Service Working Hours Cover Service Providers', 'wp-base'), __('if set as "Yes", when working hours of a service provider are changed, working hours of related services (those service provider is serving for) are also updated to cover new working time slots, if there is any. This ensures that service is always available when any of the service providers are available. If you select No, service working hours setting will not be affected from service provider setting changes.', 'wp-base') ),
			'service_wh_check'				=> array( 'no', 'Always Check Available Times of Services', 'By default (setting "No"), service working hours and holidays are only taken into account when service provider is not selected, e.g. when client did not make a selection yet, or service capacity increased beyond available service providers. if you set as "Yes", service working hours will always be checked in addition to those of the service provider. This may be required when service has limited availability, e.g. available only weekend afternoons.' ),
			'extra_required'				=> array( 'no', __('Selection Required', 'wp-base'), __('Whether client is forced to pick an option in order to finalize booking submission.', 'wp-base'),'Extras' ),
			'extra_multiplied_with_pax'		=> array( 'no', __('Extras Multiplied with Pax', 'wp-base'), __('Only effective when Group Bookings addon is active. Selected extras are multiplied by selected number of pax/seats and price adjusted accordingly.', 'wp-base'),'Extras' ),
			'ep_if_several'					=> array( 'min', __('Price to Apply upon Multiple Rule Match', 'wp-base'), __('If there are several matching rules, price returned can be selected among minimum, maximum or average of the non-zero prices calculated by matching rules.', 'wp-base'), 'Custom and Variable Pricing' ),
			'gmap_api_key'					=> array( '', __('Google Maps API key','wp-base'), __('Enter API key.','wp-base'), 'Locations' ),
			'gmap_width'					=> array( '300', __('Map Width (px)','wp-base'), __('Map width in pixels','wp-base'), 'Locations' ),
			'gmap_height'					=> array( '200', __('Map Height (px)','wp-base'), __('Map height in pixels','wp-base'), 'Locations' ),
			'gmap_zoom'						=> array( '16', __('Zoom','wp-base'), __('Higher means closer (1-21)','wp-base'), 'Locations' ),
			'use_mp'						=> array( 'no', __('Enable Integration','wp-base'), __('Enables integration with MarketPress e-commerce plugin. That is, services can be sold as MarketPress products.', 'wp-base'), 'MarketPress' ),
			'mp_product_name'				=> array( 'SERVICE - DATE_TIME', __('Product Name in Cart','wp-base'), sprintf( __('Defines how the selected booking will be displayed in the cart. All <abbr title="%s">booking placeholders</abbr> can be used.','wp-base'), WpBConstant::email_desc(0) ), 'MarketPress' ),
			'wc_enabled'					=> array( 'no', __('Enable Integration','wp-base'), __('Enables integration with WooCommerce e-commerce plugin. That is, services can be sold as WooCommerce products.', 'wp-base'), 'WooCommerce' ),
			'wc_product_name'				=> array( 'SERVICE - DATE_TIME', __('Product Name in Cart','wp-base'), sprintf( __('Defines how the selected booking will be displayed in the cart. All <abbr title="%s">booking placeholders</abbr> can be used.','wp-base'), WpBConstant::email_desc(0) ), 'WooCommerce' ),
			'wc_product_name_in_email'		=> array( 'SERVICE', __('Product Name in emails and Order Form','wp-base'), sprintf( __('Defines how the selected booking will be displayed in emails and order form. All <abbr title="%s">booking placeholders</abbr> can be used.','wp-base'), WpBConstant::email_desc(0) ), 'WooCommerce' ),
			'wc_product_meta'				=> array( 'From: DATE_TIME To: END_DATE_TIME', __('Product Details in emails and Order Form','wp-base'), sprintf( __('Details of booking that will be added below product name in emails and order form. All <abbr title="%s">booking placeholders</abbr> can be used.','wp-base'), WpBConstant::email_desc(0) ), 'WooCommerce' ),
			'wc_direct_checkout'			=> array( 'no', __('Direct Checkout after Confirming Form','wp-base'), __('By default (setting "No"), after client confirms the form, booking is added to WC cart and client stays on booking page so that they can add more bookings. If you want them to have only one booking per cart, you can select "Yes". Then they will be redirected to WC checkout page after confirm.', 'wp-base'), 'WooCommerce' ),
			'auto_register_client'			=> array( 'yes', __('Auto Register Client','wp-base'), __('Whether register client as WP user upon submission of booking. Only effective if client submits a valid email that does not belong to an existing member (In such a case client is asked to login first).','wp-base') ),
			'auto_register_client_notify'	=> array( 'yes', __('Notify User about Auto Register','wp-base'), __('Whether auto registered client will get an email informing their login credentials. Admin will always get a notification.','wp-base') ),
			'auto_register_login'			=> array( 'no', __('Auto Register Auto Login','wp-base'), __('Whether auto registered client will be automatically logged in. This is always assumed "Yes" on WooCommerce and MarketPress product pages.','wp-base') ),
			'bp_use_book_me'				=> array( 'no', __('Create a Book Me Tab in User Profile','wp-base'), __('Whether a functional make a booking page will be added to profile tab of service providers. If "Service Provider Selects" is selected, provider can turn on or turn off this feature using their settings tab.','wp-base'), 'Buddypress' ),
			'bp_book_me_content'			=> array( '[app_book]', __('Book Me Tab Content','wp-base'), __('Add shortcodes and other text that will make the content of the book me tab. During booking, service provider whose profile is being displayed will be automatically selected (Client cannot select another provider).','wp-base'), 'Buddypress' ),
			'fem_additional_css'			=> array( '', __('Additional css Rules','wp-base'), __('You can add css rules to customize styling of Front End Booking Management table. These will be added to the front end appointment management page only.','wp-base'),'Front End Booking Management' ),
			'fem_disable_auto_adapt'		=> array( 'no', __('Disable Auto Adapt','wp-base'), __('Normally Front End Appointment Management Addon will try to adapt the styling of Front End Management page to your theme in case of overflows in the fields. If you have already customized the page using css or javascript, you may want to disable this feature.','wp-base'),'Front End Booking Management' ),
			'confirmation_attach'			=> array( 'no', __('Create and Attach pdf File', 'wp-base'), __('Whether to attach a pdf file that will be created from the below fields. If attachment field is empty, file will not be attached (empty file will not be sent).', 'wp-base'), 'PDF' ),
			'pending_attach'				=> array( 'no', __('Create and Attach pdf File', 'wp-base'), __('Whether to attach a pdf file that will be created from the below fields. If attachment field is empty, file will not be attached (empty file will not be sent).', 'wp-base'), 'PDF' ),
			'completed_attach'				=> array( 'no', __('Create and Attach pdf File', 'wp-base'), __('Whether to attach a pdf file that will be created from the below fields. If attachment field is empty, file will not be attached (empty file will not be sent).', 'wp-base'), 'PDF' ),
			'reminder_attach'				=> array( 'no', __('Create and Attach pdf File', 'wp-base'), __('Whether to attach a pdf file that will be created from the below fields. If attachment field is empty, file will not be attached (empty file will not be sent).', 'wp-base'), 'PDF' ),
			'dp_reminder_attach'			=> array( 'no', __('Create and Attach pdf File', 'wp-base'), __('Whether to attach a pdf file that will be created from the below fields. If attachment field is empty, file will not be attached (empty file will not be sent).', 'wp-base'), 'PDF' ),
			'follow_up_attach'				=> array( 'no', __('Create and Attach pdf File', 'wp-base'), __('Whether to attach a pdf file that will be created from the below fields. If attachment field is empty, file will not be attached (empty file will not be sent).', 'wp-base'), 'PDF' ),
			'cancellation_attach'			=> array( 'no', __('Create and Attach pdf File', 'wp-base'), __('Whether to attach a pdf file that will be created from the below fields. If attachment field is empty, file will not be attached (empty file will not be sent).', 'wp-base'), 'PDF' ),
			'enable_timezones'				=> array( 'yes', __('Include Time Zone in Time Calculations', 'wp-base'), __('If selected as "Yes", time zone of the client is taken into account during display of booking UI\'s, list of bookings and emails. Admin side and database records are not affected. If selected as No, time zone of the client will only be recorded for information.', 'wp-base'), 'Timezones' ),
			'allow_client_set_tz'			=> array( 'no', __('Allow Clients Select Own Timezone', 'wp-base'), __('If selected as "Yes", clients can manually select their time zone in their profile page. If this setting does not match with automatic dedection, manual setting will be valid.', 'wp-base'), 'Timezones' ),
			'allow_worker_set_tz'			=> array( 'yes', __('Allow Service Providers Select Own Timezone', 'wp-base'), __('If selected as "Yes", service providers can manually select their time zone in their profile page. If this setting does not match with automatic dedection, manual setting will be valid.', 'wp-base'), 'Timezones' ),
			'event_post_type'				=> array( 'post,page', __('Supported Post Types', 'wp-base'), __('Select post types Event Bookings will be used for. Setting metabox will be added to post edit pages of the selected post types.', 'wp-base'), 'Timezones' ),
		);
		
		$default_templates = array(
			'confirmation_subject'			=> array( __('Confirmation of your appointment','wp-base'), __('Confirmation Email Subject', 'wp-base'), '' ),
			'confirmation_message'			=> array( self::$_confirmation_message, __('Confirmation email Message', 'wp-base'), '' ),
			'pending_subject'				=> array( __('We have received your booking','wp-base'), __('Pending email Subject', 'wp-base'), '' ),
			'pending_message'				=> array( self::$_pending_message, __('Pending email Message', 'wp-base'), '' ),
			'reminder_subject'				=> array( __('Reminder for your appointment on DATE_TIME','wp-base'), __('Reminder email Subject', 'wp-base'), '' ),
			'reminder_message'				=> array( self::$_reminder_message, __('Reminder email Message', 'wp-base'), '' ),
			'dp_reminder_subject'			=> array( __('Due Payment Reminder','wp-base'), __('Due Payment Reminder email Subject', 'wp-base'), '' ),
			'dp_reminder_message'			=> array( self::$_dp_reminder_message, __('Due Payment Reminder email Message', 'wp-base'), '' ),
			'cancellation_subject'			=> array( __('Your appointment has been cancelled','wp-base'), __('Cancellation Email Subject', 'wp-base'), '' ),
			'cancellation_message'			=> array( self::$_cancellation_message, __('Cancellation email Message', 'wp-base'), '' ),
			'completed_subject'				=> array( __('Your appointment has been completed','wp-base'), __('Completed email Subject', 'wp-base'), '' ),
			'completed_message'				=> array( self::$_completed_message, __('Completed email Message', 'wp-base'), '' ),
			'follow_up_subject'				=> array( __('Warmest greetings from SITE_NAME','wp-base'), __('Follow-up email Subject', 'wp-base'), '' ),
			'follow_up_message'				=> array( self::$_follow_up_message, __('Follow-up email Message', 'wp-base'), '' ),
			'waiting_list_subject'			=> array( __('We have received your submission for SITE_NAME','wp-base'), __('Submission Received email Subject', 'wp-base'), __('Subject of email which confirms client that their submission has been added to the waiting list.','wp-base') ),
			'waiting_list_message'			=> array( self::$_waiting_list_message, __('Submission Received email Message', 'wp-base'), __('Body of email which informs client that their submission has been added to the waiting list.','wp-base') ),
			'waiting_list_notify_subject'	=> array( __('Urgent action required for SITE_NAME','wp-base'), __('Notification email Subject', 'wp-base'), __('Subject of email which confirms client that there is an opening for the requested time slot.','wp-base') ),
			'waiting_list_notify_message'	=> array( self::$_waiting_list_notify_message, __('Notification email Message', 'wp-base'), __('Body of email which informs client that there is an opening for the requested time slot. WAITING_LIST_LINK should be included in the message so that client can confirm their request is still valid.','wp-base') ),
			'confirmation_title'			=> array( 'Confirmation of Your Booking', __('Confirmation Message Dialog Title', 'wp-base'), __('Title of the confirmation pop-up which will be displayed to the client after confirmed or paid bookings.','wp-base'), 'Advanced Features' ),
			'confirmation_text'				=> array( self::$_confirmation_text, __('Confirmation Message Dialog Content', 'wp-base'), sprintf( __('This will be displayed to the client in a dialog pop-up after confirmed or paid bookings. All <abbr title="%s">booking placeholders</abbr> can be used. If left empty, a plain javascript message will be displayed instead (see appointment_received in Custom Texts).','wp-base'), WpBConstant::email_desc(0) ), 'Advanced Features' ),
			'pending_title'					=> array( 'We have received your submission', __('Pending Message Dialog Title', 'wp-base'), __('Title of the pending pop-up which will be displayed to the client when an appointment submission is received in pending status.','wp-base'), 'Advanced Features' ),
			'pending_text'					=> array( self::$_pending_message, __('Pending Message Dialog Content', 'wp-base'), sprintf( __('This will be displayed to the client in a dialog pop-up after pending bookings. All <abbr title="%s">booking placeholders</abbr> can be used. If left empty, a plain javascript message will be displayed instead (see appointment_received in Custom Texts).','wp-base'), WpBConstant::email_desc(0) ), 'Advanced Features' ),
			'terms_title'					=> array( 'Terms & Conditions', __('Dialog Title', 'wp-base'), __('Title of the pop-up which displays terms and conditions.','wp-base'), 'Advanced Features' ),
			'terms_text'					=> array( self::$_terms, __('Dialog Content', 'wp-base'), __('Terms & Conditions text. SITE_NAME and HOME_URL placeholders will be replaced by their actual values.','wp-base' ), 'Advanced Features' ),
			'confirmation_message_sms'		=> array( self::$_confirmation_message_sms, __('Confirmation SMS Message', 'wp-base'), '' ),
			'confirmation_message_sms_worker'=> array( self::$_confirmation_message_sms_admin, __('Confirmation SMS Message (Provider)', 'wp-base'), '', 'SMS' ),
			'confirmation_message_sms_admin'=> array( self::$_confirmation_message_sms_admin, __('Confirmation SMS Message (Admin)', 'wp-base'), '', 'SMS' ),
			'pending_message_sms'			=> array( self::$_pending_message_sms, __('Pending SMS Message', 'wp-base'), '', 'SMS' ),
			'pending_message_sms_worker'	=> array( self::$_pending_message_sms_admin, __('Pending SMS Message (Provider)', 'wp-base'), '', 'SMS' ),
			'pending_message_sms_admin'		=> array( self::$_pending_message_sms_admin, __('Pending SMS Message (Admin)', 'wp-base'), '', 'SMS' ),
			'cancellation_message_sms'		=> array( self::$_cancellation_message_sms, __('Cancellation SMS Message', 'wp-base'), '', 'SMS' ),
			'cancellation_message_sms_worker'=> array( self::$_cancellation_message_sms_admin, __('Cancellation SMS Message (Provider)', 'wp-base'), '', 'SMS' ),
			'cancellation_message_sms_admin'=> array( self::$_cancellation_message_sms_admin, __('Cancellation SMS Message (Admin)', 'wp-base'), '', 'SMS' ),
			'confirmation_attachment'		=> array( '', __('Confirmation email Attachment Text', 'wp-base'), __('Contents of PDF file. HTML allowed and will be formatted with related css rules.', 'wp-base'), 'PDF' ),
			'pending_attachment'			=> array( '', __('Pending email Attachment Text', 'wp-base'), __('Contents of PDF file. HTML allowed and will be formatted with related css rules.', 'wp-base'), 'PDF' ),
			'completed_attachment'			=> array( '', __('Completed email Attachment Text', 'wp-base'), __('Contents of PDF file. HTML allowed and will be formatted with related css rules.', 'wp-base'), 'PDF' ),
			'reminder_attachment'			=> array( '', __('Reminder email Attachment Text', 'wp-base'), __('Contents of PDF file. HTML allowed and will be formatted with related css rules.', 'wp-base'), 'PDF' ),
			'dp_reminder_attachment'		=> array( '', __('Due Payment Reminder email Attachment Text', 'wp-base'), __('Contents of PDF file. HTML allowed and will be formatted with related css rules.', 'wp-base'), 'PDF' ),
			'follow_up_attachment'			=> array( '', __('Follow up email Attachment Text', 'wp-base'), __('Contents of PDF file. HTML allowed and will be formatted with related css rules.', 'wp-base'), 'PDF' ),
			'cancellation_attachment'		=> array( '', __('Cancellation email Attachment Text', 'wp-base'), __('Contents of PDF file. HTML allowed and will be formatted with related css rules.', 'wp-base'), 'PDF' ),
			'confirmation_css'				=> array( '', __('css Codes for Attachment pdf File', 'wp-base'), __( 'Important: Only css for block elements (p, div, table, td, tr, etc) are allowed.', 'wp-base' ), 'PDF' ),
			'pending_css'					=> array( '', __('css Codes for Attachment pdf File', 'wp-base'), __( 'Important: Only css for block elements (p, div, table, td, tr, etc) are allowed.', 'wp-base' ), 'PDF' ),
			'completed_css'					=> array( '', __('css Codes for Attachment pdf File', 'wp-base'), __( 'Important: Only css for block elements (p, div, table, td, tr, etc) are allowed.', 'wp-base' ), 'PDF' ),
			'reminder_css'					=> array( '', __('css Codes for Attachment pdf File', 'wp-base'), __( 'Important: Only css for block elements (p, div, table, td, tr, etc) are allowed.', 'wp-base' ), 'PDF' ),
			'dp_reminder_css'				=> array( '', __('css Codes for Attachment pdf File', 'wp-base'), __( 'Important: Only css for block elements (p, div, table, td, tr, etc) are allowed.', 'wp-base' ), 'PDF' ),
			'follow_up_css'					=> array( '', __('css Codes for Attachment pdf File', 'wp-base'), __( 'Important: Only css for block elements (p, div, table, td, tr, etc) are allowed.', 'wp-base' ), 'PDF' ),
			'cancellation_css'				=> array( '', __('css Codes for Attachment pdf File', 'wp-base'), __( 'Important: Only css for block elements (p, div, table, td, tr, etc) are allowed.', 'wp-base' ), 'PDF' ),

		);
		
		
		if ( 'only_templates' === $include_templates )
			$defaults = $default_templates;
		else if ( $include_templates )
			$defaults = array_merge( $defaults, $default_templates );

		if ( !$item ) {
			$out = array();
			foreach ( $defaults as $name=>$value ) {
				$out[$name] = is_array( $value ) ? $value[0] : $value;
			}
		}
		else if ( 'all' == $item )
			$out = $defaults;
		else
			$out = isset( $defaults[$item][$el] ) ? $defaults[$item][$el] : __('Undefined','wp-base');
			
		return $out;
	}
	
	/**
     * Provide setting name
	 * @param item: Pick the required default item.
	 * @since 2.0
 	 * @return string
     */
	public static function get_setting_name( $item ) {
		return self::defaults( true, $item, 1 );
	}

	/**
     * Print setting name
	 * @param item: Pick the required default item.
	 * @since 2.0
 	 * @return string
     */
	public static function echo_setting_name( $item ) {
		echo self::get_setting_name( $item );
	}

	/**
     * Provide setting description
	 * @param item: Pick the required default item.
	 * @since 2.0
 	 * @return string
     */
	public static function get_setting_desc( $item ) {
		return self::defaults( true, $item, 2 );
	}

	/**
     * Print setting description
	 * @param item: Pick the required default item.
	 * @since 2.0
 	 * @return string
     */
	public static function echo_setting_desc( $item ) {
		echo self::get_setting_desc( $item );
	}

	/**
     * Location descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function location_desc( ) {
		return array( 
			__( '<i>Here you can optionally add locations. After you define locations, you can select services to be given in these locations in Services tab.</i>', 'wp-base'),
			__('Locations are to be used when you have different business rules depending on the selection where service is being given, e.g. extras offered, special discounts and coupons applied. If you just want to group your services in services selection menu, prefer "Categories" instead.', 'wp-base'),
			__('As you click "Add New Location" button a new empty row will be inserted.', 'wp-base'),
			__('New inserted record(s) will not be saved unless you fill in the name field and click "Save Locations" button (Save button is only visible if there is at least one record added).', 'wp-base'),
			__('<b>ID field:</b> is automatically given by the system and it is unique. You can use this ID in shortcodes or in some addon (for example Advanced Pricing) settings.', 'wp-base'),
			__('<b>Name field:</b> You can use anything as the location name here. This will be displayed to the client on the front end in locations pulldown menu, in list of bookings, in emails, etc.', 'wp-base'),
			__('<b>Address field:</b> You can use this as reminder for yourself or location address for your clients as LOCATION_ADDRESS placeholder in email massages will be replaced with this value. When Google Map API is enabled, hovering over location on confirmation form will display map of the address.', 'wp-base'),
			__('<b>Capacity field:</b> is optional and it can be used to limit total number of services that can be given per time slot when resources are shared and they are less then sum of potential capacity of services. Trying to increase capacity will have no effect. If you need to limit each service separately, use service capacity field instead. Example: A "Hairdresser" location has "Gents\' Haircut" and "Ladies\' Haircut" services. There are 7 hairdressers who can work in both of the services (or some can work only in one of the services, which will not make any difference for this example), but there are only 5 barber chairs. If chairs can be used for both services, then set location capacity as 5 which will limit total bookable slots as 5 at any given time. However if 3 of the chairs are for Gents\' and 2 of them are for Ladies\', set service capacities as 3 and 2, respectively.', 'wp-base'),
			__('<b>Add. Price field:</b> is optional additional price that will be added to the service price if this location is selected by the client.', 'wp-base'),
			__('<b>Description page:</b> is an optional page describing the location. The content of this page will be read from the database and it will be displayed as a tooltip in locations pulldown menu on the front end. Pages with status "publish" and "private" are displayed and selectable. Type of this page (page, post, custom post) can be selected from Advanced &rarr; Admin settings (Advanced Features addon is required). ', 'wp-base'),
			__('You can add as many records (locations) as you wish and they will only be saved after you hit Save Locations button.', 'wp-base'),
			__('You can use location setting to group/categorize your services too.', 'wp-base'),
			__('To delete a record empty Name field and Save.', 'wp-base'),
			__('Custom Sorting: In selection menus items are displayed according to the order here, by default (Other display orders can be set in the related shortcode, e.g. sorting in alphabetical order - see order_by attribute of the shortcode). To change the sort order, select the row with your mouse and move it to the new position and then click Save.', 'wp-base'),
		);
	}

	/**
     * Service descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function service_desc( ) {
		return array( 
			__( '<i>Here you should define your services. <b>There must be at least one service defined.</b></i>', 'wp-base'),
			__('As you click "Add New Service" button a new empty row will be inserted.', 'wp-base'),
			__('New inserted record(s) will not be saved unless you fill in the name field and click "Save Services" button (Save button is only visible if there is at least one record added).', 'wp-base'),
			__('<b>ID field:</b> is automatically given by the system and it is unique. You can use this ID in shortcodes or in some addon (for example Advanced Pricing) settings.', 'wp-base'),
			__('<b>Int (Internal) field:</b> Requires Extended Service Features Addon. Services marked as internal are not displayed in services pulldown menu. You can use it for services which you assign providers only internally, e.g. for appointments made by phone. Note: While client <u>can not select</u> an internal service, they <u>can book</u> an internal service, provided that it is preselected by admin using "service" attribute in booking shortcodes. This means, admin can practically override internal feature of such a service for desired shortcodes, and thus for desired pages.', 'wp-base'),
			__('<b>Name field:</b> You can use anything as the service name here. This will be displayed to the client on the front end in Services pulldown menu, in list of bookings, in emails, etc.', 'wp-base'),
			__('<b>Locations field:</b> Requires Locations Addon. Setting this field is optional and it is used to assign your service to certain location(s). <u>If none selected, service will be available for all locations.</u>', 'wp-base'),
			__('<b>Categories field:</b> Requires Extended Service Features Addon. It can be used to group your services for display convenience, e.g. on front end services drop down selection menu. To assign a service to one or more categories, first you need to define the categories on "Categories" tab. After you save the categories, you can select them in this field. Contrary to locations, if you defined categories, <u>every service must be assigned to at least one category.</u> Otherwise it may not be possible to pick the unassigned service on the front end.', 'wp-base'),
			sprintf( __('<b>Capacity field:</b> is an optional field to change (increase or decrease) your available workforce of the service for all available time slots. If capacity is increased, the additional "virtual" workforce will use working hours of the service (WP BASE allows setting working hours of each service independently). If left empty or as zero, number in square brackets, which is the total number of service providers assigned to this service, will be applied. Note: 1) When there are no service providers defined, WP BASE assumes that business representative can serve all services and sets capacity as 1 for each service (You can still increase this value). 2) This is the available capacity in general. Actual availability for <i>each time slot</i> depends on SPs working at that particular slot and it will be automatically calculated by WP BASE. Tip: To turn off/close a service, set its capacity as negative, e.g. -1. To turn off ALL services, set %s to a negative value instead. Closed services cannot be selected on the front end, but they are available on admin side as usual.', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_settings#app-upper-limit').'">'.WpBConstant::get_setting_name('app_limit').'</a>'),
			sprintf( __('<b>Notes about Capacity setting:</b> You can increase capacity field when there are no assigned SPs or when there are some SPs. For the latter case, WP BASE will always try to assign available SPs first. Also see %1$s setting: If capacity is increased and all possible SPs are busy but there is still available capacity, booking will be accepted and a provider will not be assigned. This is the normal, intended behaviour. Example: Capacity is set to 3 and there are 2 SPs and 2 bookings at the time slot with case a) existing bookings are not assigned to anyone, case b) one booking is assigned to an SP, the other not assigned to anyone and case c) bookings are assigned to both SPs. A 3rd booking will be allowed and in case a) it will be assigned to the SP depending on %1$s, in case b) it will be assigned to the SP who is free and in case c) it will not be assigned to anyone (SP=0). A 4th booking will not be allowed, even when one SP seems to be free.', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_business&tab=workers#assign-worker').'">'.WpBConstant::get_setting_name('assign_worker').'</a>'),
			sprintf( __('<b>Duration field:</b> is the number of minutes that this service lasts. It can only be selected in increments of %s.', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_settings#time-base').'">'.WpBConstant::get_setting_name('min_time').'</a>'),
			__('<b>Padding before field:</b> Requires Extended Service Features Addon. It is the period of the break in minutes that will be added before any appointment of this service that has been already made. For example, if your service lasts 45 minutes and you want to have 15 minutes preparing time before each booked appointment, set 15 here. It can only be selected in increments of Time Base.', 'wp-base'),
			__('<b>Padding after field:</b> Requires Extended Service Features Addon. It is the period of the break in minutes that will be added after any appointment. Then next appointment can be made after duration+padding after time. For example, if your service lasts 60 minutes and you want to have 30 minutes rest after each appointment, set 30 here. It can only be selected in increments of Time Base.', 'wp-base'),
			sprintf( __('<b>Notes about Padding settings:</b> 1) Paddings are applied to appointments already made, not to potential appointments. Therefore if you do not have any bookings, you will not notice any change in time slots. If you want to add gaps even if there are no appointments, use working hours settings instead and "add breaks" as you wish (clearing a cell in working hours table means adding a break). If you add these gaps in service working hours, also set %s as Yes. 2) Sum of duration and paddings shall not exceed 24 hours. For this reason, paddings are not available for "all day" (24 hour) services.', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_business&tab=working_hours#service_wh_check').'">'.WpBConstant::get_setting_name('service_wh_check').'</a>'),
			sprintf( __('<b>Price field:</b> is the price of the service. You can leave empty for free services. Note: A service having a price does not necessarily mean it requires advance payment, e.g. if you will collect the payment at your business location. You can set this requirement by %s setting.', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_monetary#payment-required').'">'.WpBConstant::get_setting_name('payment_required').'</a>'),
			sprintf( __('<b>Security Deposit field:</b> Requires Extended Service Features Addon. It is the *Refundable* deposit for the service. This can be added to the payable amount using %s setting. This value is saved in the booking record to calculate balance and reset to zero in the record after appointment is completed. Therefore upon completion, not refunded deposit yields to a positive balance in favor of the client. You can refund the deposit using Manual Payments, making balance even.', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_monetary#add-deposit').'">'.WpBConstant::get_setting_name('add_deposit').'</a>'),
			__('<b>Description page:</b> is an optional page describing the service. The content of this page will be read from the database and it will be displayed as a tooltip in services pulldown menu on the front end. Pages with status "publish" and "private" are displayed and selectable. Type of this page (page, post, custom post) can be selected from Advanced &rarr; Admin settings (Advanced Features addon is required). ', 'wp-base'),
			__('You can add as many records (services) as you wish and they will only be saved after you hit Save Services button.', 'wp-base'),
			__('Some service related addons add a "More" link just after ID. When you click that link, you can view and set additional settings required by these addons.', 'wp-base'),
			__('To delete a record empty Name field and Save. Note: If you are a service provider, deletion of self created services may be restricted. If you see a warning message, contact to admin,', 'wp-base'),
			sprintf( __('<b>Custom Sorting:</b> In front end selection menus, items are displayed according to the order here, by default (Other display orders can be set in the related shortcode, e.g. sorting in alphabetical order - see order_by attribute of the shortcode). To make a custom sorting, select the row with your mouse and move it to the new position (drag and drop) and then click Save. Sorting is done on current page only. If you have more than one page of items, increase %s setting to a value to cover all items, make sorting and then revert back to previous records per page value. Categories are sortable too. If you are using categories, you may also want to sort them. Note: If you are a service provider and sorting using your profile page, your own services can only be sorted among themselves. In addition to this, if admin has changed sorting of your services and placed them in higher priority than before, then you may not make new sorting at all. In such cases ask admin to sort your services for you.', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_settings#records-per-page-business').'">'.WpBConstant::get_setting_name('records_per_page_business').'</a>'),
		);
	}
	
	/**
     * Worker descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function worker_desc( ) {
		return array( 
			__( '<i>Here you can optionally select your service providers, i.e. workers, and assign them to certain services.</i>', 'wp-base'),
			__('As you click "Add New Service Provider" button a new empty row will be inserted.', 'wp-base'),
			__('New inserted record(s) will not be saved unless you assign at least one service and click "Save Service Providers" button (Save button is only visible if there is at least one record added).', 'wp-base'),
			__('<b>ID field:</b> is automatically filled by the system and it is WordPress user ID of the service provider. You can use this ID in shortcodes or in some addon (for example Advanced Pricing) settings.', 'wp-base'),
			sprintf( __('<b>Service Provider field:</b> Select your service provider from the pulldown menu. The menu includes all users of the website. A service provider must be a registered user of the website. To add a new user %s. ', 'wp-base'), '<a href="'.admin_url('user-new.php').'">' . __('Click here', 'wp-base') . '</a>'),
			__('<b>Display Name field:</b> You can use anything as the service provider name here. This will be displayed to the client on the front end in Services Providers pulldown menu, in emails, etc.', 'wp-base'),
			__('<b>Services Provided field:</b> use this to assign your service provider to a single or multiple services. You must select at least one service.', 'wp-base'),
			__('<b>Dummy field:</b> Check the checkbox if this is a "dummy" service provider. A dummy behaves exactly like a normal user, i.e. it has its own user account, working hours, holidays, but all emails it is supposed the receive are forwarded to the selected user in the Advanced tab.', 'wp-base'),
			__('<b>Add. Price field:</b> is the optional additional price of the service provider which will be added to the service price if client picks up this service provider.', 'wp-base'),
			__('<b>Bio Page field:</b> is an optional page selection describing this service provider. The content of this page will be read from the database and it will be displayed as a tooltip in service providers pulldown menu on the front end. Pages with status "publish" and "private" are displayed and selectable. Type of this page (page, post, custom post) can be selected from Advanced settings. ', 'wp-base'),
			__('You can add as many records (service providers) as you wish and they will only be saved after you hit Save Service Providers button.', 'wp-base'),
			__('To delete a record, i.e. unassign a user as service provider, deselect all Services Provided checkboxes and Save. This does not delete the user.', 'wp-base'),
			sprintf( __('Custom Sorting: In front end selection menus, items are displayed according to the order here, by default (Other display orders can be set in the related shortcode, e.g. sorting in alphabetical order). To make a custom sorting, select the row with your mouse and move it to the new position (drag and drop) and then click Save. Sorting is done on current page only. If you have more than one page of items, increase %s setting to a value to cover all items, make sorting and then revert back to previous records per page value.', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_settings#records-per-page-business').'">'.WpBConstant::get_setting_name('records_per_page_business').'</a>'),
		);
	}
	

	/**
     * Working hour descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function wh_desc( ) {
		return apply_filters( 'app_wh_desc', array( 
			__( 'Here you can define working hours for your services and service providers on days of the week basis. ', 'wp-base'),
			__( 'WP BASE provides a table for each service and provider using which you can easily set working hours. You just need to set working time slots. Not selected slots will mean "break times".', 'wp-base'),
			__( 'When you add new services and service providers, their working hours will be set to the default schedule (Business Representative). Then you can edit their schedule by selecting their names from the dropdown menu below. Please note that <b>every service and service provider has their own working hour settings</b>. So, do not forget to change "List For" setting to set for a certain name.', 'wp-base'),
			__( 'You can open more than one table by checking the check boxes beside the service/provider name in the pulldown menu, e.g. to set more than one table at once and/or to copy/paste working hours  as explained below.', 'wp-base'),
			__( 'To set a working interval, click the appropriate cell to make it green. Continue with other cells until the desired working schedule is produced.', 'wp-base'),
			__( 'It is possible to select/deselect multiple cells:', 'wp-base'),
			__( '<b>To select/deselect a column (a week day):</b> Click on the column header (week day name, e.g. Wednesday)', 'wp-base'),
			__( '<b>To select/deselect a row (a time interval for every week day):</b> Click the row header (time of the day, e.g. 1:00pm)', 'wp-base'),
			__( '<b>To select/deselect all cells of the table (complete week):</b> Click the upper left cell', 'wp-base'),
			__( 'It is also possible to <b>copy</b> cell settings of an entire table (source) to another one (target) or more than one, e.g. copy Service Provider A\'s working hours to Service Provider B and C:', 'wp-base'),
			__( 'Open all source and target working hour tables at the same time using the selection puldown menu and checking all desired source and target tables.', 'wp-base'),
			__( 'Click the "Copy to clickboard" button under the source table. Text of button will change from "Copy to clickboard" to "Copied (click to release)".', 'wp-base'),
			__( 'Click the "Paste data of..." button under the target table. You will see that source table values are copied to the target table. Text of button will change from "Paste data of..." to "Undo". You can click the button again to <b>undo</b> the paste operation.', 'wp-base'),
			__( 'Repeat the above step for all desired target tables.', 'wp-base'),
			__( 'If you have other copy operations from other source tables, click the button under source table: Button text will change from "Copied (click to release)" to "Copy to clickboard". Repeat the above steps as much as desired.', 'wp-base'),
			__( 'Click "Save Working Hours" button.', 'wp-base'),
			__( '<b>NOTES:</b>', 'wp-base'),
			__( 'Please note that front end calendars generated by WP BASE are affected by the settings here: i) Start time of the calendars are determined by start hour of working hours setting ii) Break times modify time slots accordingly; WP BASE always tries to maximize working hours, filling any gaps as much as possible. For example suppose 2 hours services from working hours 8am to 6pm and a break between 12pm-1pm. Then available slots will be 8am, 10am, 1pm, 3pm (NOT 8am, 10am, 2pm, 4pm. If these are the desired slots, define a break of 12pm-2pm instead).', 'wp-base'),
			__( 'Rows of working hours tables are incremented by time base. Also a shift from exact hour of the day is possible within time base value. For example, setting a working hour interval of 8:15 to 9:15 requires a time base of 15 minutes.', 'wp-base'),
			__( 'On the front end, <b>service working hours</b> are used when there is no provider selected or not defined at all. When the capacity of the service is increased, additional workforce also uses service working hours. In all other cases, in other words when a service provider is namely selected, their working hours will be used.', 'wp-base'),
			sprintf( __( 'By default, when working hours of one or more service providers have been changed, working hours of the related services will also be automatically adapted so that they will cover all new added working hour slots, in order to ensure that service is available at all times that providers work, so that "no preference" selection may reflect all available slots (Working hour tables of services do not need to be opened prior to saving of the tables, but it is recommended to do so to check the result). This may also be important when service providers have the capability to change working hours of themselves, but not that of the services they are giving. If this not the desired behavior (for example a single provider giving service A in the morning, service B in the afternoon) you can change it by <b>%s</b> setting. Note: This is intentionally valid for selected cells only, not for deselected ones; When you deselect a slot in working hours of the provider, i.e. add a break time, this will NOT result in a deselection in service working hours.', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_business&tab=working_hours#service-wh-covers').'">'.WpBConstant::get_setting_name('service_wh_covers').'</a>'),
		)
		);
	}

	/**
     * Working annual schedules descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function annual_desc( ) {
		return apply_filters( 'app_annual_desc', array( 
			__( 'Here you can define "alternative" weekly schedules and use them to prepare seasonal working schedules for your providers and even services. ', 'wp-base'),
			__( 'An alternative schedule is a weekly working hours table which will be used in a week or weeks of the year instead of "regular" working hours as you select. Without such a system annual scheduling would be impossible. Using a weekly system allows setting quite fast, provided that alternative schedules are repeated throughout the year.', 'wp-base'),
			__( 'As an example model, a business using shift work can be given. Businesses having seasonal working hour changes can also get use of this feature.', 'wp-base'),
			__( 'To use seasonal scheduling, first of all you need to define alternative schedules using related tab. You just need to give a name actually. Determine how many alternative schedules you need and add them with a name (This name is internal only), e.g. Day Shift, Swing Shift, Night Shift, Afternoons Off, etc. Click Save.', 'wp-base'),
			__( 'Then go to Working Hours tab, select newly added alternative schedules under "Alternative Schedules" optgroup and set working hours for each of them like regular working hours.', 'wp-base'),
			__( 'You can get use of copy feature during this process.', 'wp-base'),
			__( 'After you save the working hours of the alternative schedules, return to this page.', 'wp-base'),
			__( 'Using the pulldown menus, select which weekly schedule to use for each week of the year: Regular, holiday (a complete off week - a quicker way to assign holidays as opposed to daily ones on Holidays tab), or any alternative schedule you have created.', 'wp-base'),
			__( 'After you save the seasonal schedule, the page will display all year plan per week, each with a symbolic working hour display. Daily holidays set with Holidays tab will be shown with a different color covering all day (default:blue).', 'wp-base'),
			__( 'Please note that these tables are intended to give an overall idea and they are just approximation of the real working hours. And they are not clickable and editable. To edit on daily and hourly basis, use Working Hours tab as before.', 'wp-base'),
			__( 'In addition to the current year, you can also set schedules for proceeding two years. If you need further years, there is a filter hook for this (app_alt_schedule_years).', 'wp-base'),
	
		)
		);
	}

	/**
     * Appointments descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function app_desc( ) {
		return apply_filters( 'app_app_desc', array( 
			__('<b>Upcoming (Paid or Confirmed):</b> a) Paid: Paid and confirmed via Paypal or another payment gateway. Please note that "paid" does not mean all paid up; if only down payment was paid, booking will still be regarded as paid. Tip: 1) You can filter and display such bookings (those having negative balance) using "balance" filter pulldown menu in Bookings page. 2) You can use Due Payment Reminder addon to send automatic emails to clients who have negative balance. b) Confirmed: Manually confirmed or due to "Auto Confirm" setting set as Yes, automatically confirmed. A paid appointment is identical to a confirmed one in terms of functionality. At the start time of the appointment, status is automatically changed to Happening Now. Note: A confirmed booking can be automatically turned into paid if client pays via PayPal button in client emails or List of Bookings.', 'wp-base'),
			__('<b>Pending:</b> Client applied for the appointment, but not yet paid or appointment has not yet manually confirmed. Such an appointment will still reserve booking capacity until automatically removed with "Disable pending appointments after" setting. Therefore a pending appointment is similar to paid and confirmed ones in terms of reserving available time slots, but different in terms of email functions: Client does NOT get a confirmation email and admin gets a notification email at the instant of booking, if set so.', 'wp-base'),
			__('<b>Reserved by GCal:</b> If you import appointments from Google Calender using Google Calendar API, that is, synchronize your calendar with WP BASE, events in your Google Calendar will be regarded as appointments and they will be shown here. These records cannot be edited here. Use your Google Calendar instead. They will be automatically updated in WP BASE too.', 'wp-base'),
			__('<b>In Progress:</b> Appointment has been started, not yet finished and thus happening at the moment. This status will be automatically changed to removed at the appointment end time. You cannot change the status of an appointment to In Progress manually.', 'wp-base'),
			__('<b>Completed:</b> Appointment end time has been passed (finished) when it was in confirmed or paid status. Such appointments do not reserve any booking capacity.', 'wp-base'),
			__('<b>Removed:</b> Appointment was not paid for or was not confirmed manually in the allowed time. Such appointments do not reserve any booking capacity. Permanent deletion of an appointment record can only be done in this status.', 'wp-base'),
			__('Please note that future (Confirmed, paid, pending, reserved) and current appointments (In Progress) reserve booking capacity, but past ones (Completed, removed) do not.', 'wp-base'),
			__('Addons, for example Test Appointments Addon, can add new statuses.', 'wp-base'),
		)
		);
	}

	/**
     * Email descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function email_desc( $key=false ) {
		$desc = apply_filters( 'app_email_desc', array(
			__('For all the email subject and message contents and also in some other templates (e.g. Manual Payment instructions), you can use the following placeholders which will be replaced by their real values: APP_ID (unique ID of the booking), STATUS (Custom text for status of the booking), SITE_NAME, HOME_URL (Homepage url of the website), CLIENT (Name of client), FIRST_NAME, LAST_NAME, LOCATION (name of the location), LOCATION_ID, LOCATION_ADDRESS, SERVICE (Name of service), SERVICE_ID, WORKER (Name of provider), WORKER_ID, WORKER_PHONE, WORKER_EMAIL, CREATED (Booking date/time of the appointment), DATE_TIME (Starting date/time of the appointment), END_DATE_TIME (End date/time of the appointment), START_TIME, END_TIME (Start and end times without dates), PRICE, TAX_PERCENT (without % sign), TAX (as amount), PRICE_WITHOUT_TAX, DEPOSIT (Required deposit), DOWN_PAYMENT (Required down payment), TOTAL_PAYMENT (total payment including manual payment(s) and via payment gateways), BALANCE (total payments for this appointment minus total price minus deposit), PAYMENT_METHOD (Public name of the payment gateway method), PHONE, NOTE, ADDRESS, CITY, POSTCODE, EMAIL (Client\'s email), CANCEL (Adds a cancellation link to the email body), PAX (Number of ‘seats’ bought with Group Bookings - Seats Addon), NOF_APPS (Number of appointments booked with Shopping Cart Addon). Monetary values are formatted with selected currency format, but currency sign not added.', 'wp-base'),
			__('CANCEL and CONFIRM placeholders will be replaced with a link to allow cancel or confirm, respectively. Links are created only if cancellation and confirmation by client is allowed. If client had made the booking when they were logged in, they will be prompted to login again if not already.', 'wp-base'),
			__('List of Bookings shortcode <code>[app_list]</code> can be used in emails, e.g. to send a list of upcoming appointments and booking history. Also <code>[app_show]</code> and <code>[app_hide]</code> can be used, e.g. to add or remove texts conditionally. Any other WP BASE shortcode will simply be stripped from the email.', 'wp-base'),
			__('If Advanced Features Addon is active, CATEGORY placeholder will be replaced by the selected category, if there is one.', 'wp-base'),
			__('If Coupons Addon is active, COUPON_ID, COUPON_CODE, COUPON_DISCOUNT will be replaced by ID, code and net applied amount of the coupon which was used during booking, respectively.', 'wp-base'),
			__('If Extras Addon is active, EXTRA placeholder will be replaced by the name of the extra(s) that client have chosen.', 'wp-base'),
			__('If Front End Edit Addon is active and editing is allowed, EDIT placeholder will be replaced with a link that redirects client to the website and opens edit dialog. If client is not logged in, they will be prompted to do so.', 'wp-base'),
			__('If Google Calendar Addon is active, GCAL_BUTTON will be replaced with a clickable GCal button which client can use to add the appointment to their GCal account manually.', 'wp-base'),
			__('If Group Bookings Addon is active, PAX placeholder will be replaced by number of guests and PARTICIPANTS will be replaced by a list of submitted participants.', 'wp-base'),
			__('LOCATION_MAP can be used and replaced with Google map of the location provided that all of these conditions are met: 1) Locations Addon is active 2) "Use HTML in emails" settings is Yes 3) Google Static Maps API is correctly configured. If one of these conditions are not met, placeholder will be cleared from the email.', 'wp-base'),
			__('PAYPAL placeholder can be used and replaced with a clickable PayPal Pay Now button provided that all of these conditions are met: 1) Paypal Standard Checkout gateway is active 2) "Use HTML in emails" settings is Yes 3) Balance for the booking is negative (For a new booking email this means payable amount is non zero. For other emails, e.g. reminders, this means total payments for the booking is less than total due amount) regardless of "Payment required at booking instant" setting. If one of these conditions are not met, placeholder will be cleared from the email.', 'wp-base'),
			__('If Timezone Selection Addon is active, DATE_TIME and END_DATE_TIME values will be calculated with time zone of the client saved during booking. If you want to display appointment start and end times based on server time as well, you can use the following placeholders: SERVER_DATE_TIME, SERVER_END_DATE_TIME. With Time Zone Selection addon, also TIMEZONE (Timezone selected by client) can be used.', 'wp-base'),
			__('Use User Defined Fields (UDFs) like this: UDF_n where n is the ID of the UDF, e.g. UDF_1, UDF_2, etc.', 'wp-base'),
			)
		);
		if ( $key !== false && isset( $desc[$key] ) )
			return $desc[$key];
		else
			return $desc;
	}

	/**
     * Export/import descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function impex_desc( ) {
		return apply_filters( 'app_impex_desc', array(
			__('<b>Export Bookings in CSV format</b> is suitable for further analysing and creating statistics of your bookings.', 'wp-base'), 
			__('To export all booking records, check the checkboxes to select columns you want to include (Your selection of checked columns will be saved for later export actions). Then click Export button. A CSV file will be downloaded to your PC uncluding ALL booking records (with selected columns). You can open this file with Excel or OpenOffice Calc and analyse records in detail.', 'wp-base'), 
			__('You can also export a partial or filtered result set using DataTables TableTools buttons on top of the bookings table. To do so, filter the bookings using filter menus, e.g. by month, service, etc. If the filtered result set occupies more than one page, set Number of Records per Page setting to cover all records. Then using CSV or Excel button on top of the Bookings table, export records to your PC.', 'wp-base' ),
			__('Importing with CSV format is not supported. Instead use SQL export/import explained below.', 'wp-base'), 
			__('<b>Export and Import Global Settings</b> is suitable for backing up your settings or copying them to another website. WP BASE stores global settings in WordPress options database table. Your custom texts are also exported to this file.', 'wp-base'),
			__('To export Global Settings, click the Export Settings button. A file with json format will be downloaded to your PC.', 'wp-base'),
			__('To import Global Settings to the same or another website, select "Include email and SMS Templates" and "Include Custom Texts" checkboxes as required: If you want to keep your current texts for these settings, leave checkboxes unchecked; if you want to import these texts and overwrite current settings, check the related checkboxes. Then click the related Choose File button, point the previously imported json file and then click Import Settings button. The json file will be uploaded to your server and you will get a message after upload finishes. If you get an error message, ensure that json file is the correct one.', 'wp-base'),
			__('<b>Export and Import Database Tables</b> is again suitable for backing up your appointment records or copying them to another website. This export not only includes WP BASE database tables, but also parts of related WordPress tables, for example posts having WP BASE shortcodes, users who are service providers, etc.', 'wp-base'),
			__('To export database tables, click Export Database Tables button. An SQL file (source) will be saved on your PC including these tables and records: 1) All records of WP BASE locations, services, service providers, working hours, holidays, alternative schedules, annual assignments, transactions, and bookings. 2) WordPress users who are service providers and/or WP BASE clients 3) Posts/pages which include any WP BASE shortcode 4) Location, service, service provider description pages 5) Appointment Cancelled and PayPal Return Pages 6) Post meta and user meta of the exported posts and users', 'wp-base'),
			__('<b>To Import Database Tables</b> to the same or another website (target), click the related Choose File button, point the previously exported SQL file. If you do not want to import WP BASE pages and Bookings + Transactions, uncheck related check box. Then click Import Database Table button. The SQL file will be uploaded to your server and a series of operations will be commenced:', 'wp-base'),
			__('WP BASE tables on the target website will be truncated (all records erased) and new (source) records will be added. WordPress user and post tables are not erased and new records may be added:', 'wp-base'),
			__('Users in the source records will be checked to match by their email. If a matching user email is found on the target website, user will not be inserted, and new added records will be adjusted with their existing user ID.', 'wp-base'),
			__('If user is not found on the target website, they will be created. Their ID will be kept the same, if possible. If this is not possible, new added records will be adjusted with this new user ID.', 'wp-base'),
			__('If user record is succesfully created or a matching user is found to be already existing, source user metas will be checked for existence on the target. They will be updated or created as required.', 'wp-base'),
			__('Posts/pages in the source records will be checked to match by the content. If the same content is found on the target, post will not be inserted, and ID of the existing post will be used to adjust settings and other database records.', 'wp-base'),
			__('If post content is not found, new post will be created. Its ID will be kept the same, if possible. If not, new added records will be adjusted with this new post ID.', 'wp-base'),
			__('If post is succesfully created or a matching post is found to be existing, source post metas will be checked for existence on the target. They will be updated or created as required.', 'wp-base'),
			__('After these operations you should see a message indication the result: Failure, success or partial success. In every case you can check the log file for the details of the result.', 'wp-base'),
			__('DB prefix of source and target can be different. Export/Import between multisite installation and solo installation is also allowed. Please note that when exporting from a multi site installation and importing to a solo installation, not only the blog users, but all related multisite users are transferred/created.', 'wp-base'),
			__('Do NOT use another tool (e.g. phpmyadmin) to import records using the exported file. WP BASE is performing numerous actions to adjust the source file to the target. Without these actions import may end with unintended results.', 'wp-base'), 
			__('Limitations: 1) Target and source websites should be using the same WP BASE database version. Otherwise import may partly or fully fail. You can check the database version by Help > About > Installed DB version. 2) WP BASE widgets are exported and imported. However, you may need to rebuild them using Appearance > Widgets page.', 'wp-base'),
		)
		);
	}

	/**
     * Export/import descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function import_a_plus_desc( ) {
		return apply_filters( 'app_import_a_plus_desc', array(
			__('Importing from A+ copies A+ appointments, transactions, services and service providers records to WP BASE database tables installed on the same website. A+ tables will remain untouched.', 'wp-base'),
			sprintf( __('Please note that this import is mainly intended for a newly installed WP BASE, that is, WP BASE tables are expected to be empty. If this is not your case, we highly recommend that you take a backup using %s function first (Select both Export Settings and Export Database Tables).', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_tools&tab=impex').'">'.__('export','wp-base').'</a>' ),
			__('Import is only possible if both plugins are installed on the same website. A+ does not need to be activated and actually its being deactivated is preferable.', 'wp-base'),
			__('Since working hours system of the two plugins are completely different, working hours cannot be imported and they should be created manually.', 'wp-base'),
			__('WP BASE Services table will be cleared before import. This process will maintain Appointments+ Service IDs to be used as they are.', 'wp-base'),
			__('If WP BASE Service Providers table is not empty, existing records will be replaced with Appointments+ values.', 'wp-base'),
			sprintf( __('The other WP BASE table records will be added without resetting the existing data. If this is not your intention, you may want to %s WP BASE tables first.', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_tools&tab=reset').'">'.__('reset','wp-base').'</a>' ),
			sprintf( __('A+ accepts non WP users as clients (as well as WP BASE), and it is possible to register these clients as WP users during import, provided that client has email in appointments table. To do so, set %1$s to Yes. You may also want to set %2$s if you want clients to receive their login credentials.', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_settings&tab=login#auto-register-client').'">'.self::get_setting_name('auto_register_client').'</a>', '<a href="'.admin_url('admin.php?page=app_settings&tab=login#auto-register-client-notify').'">'.self::get_setting_name('auto_register_client_notify').'</a>' ),
			__('If Auto Register Client is selected, after user registration process admin will not get an email, but they can check created users list in the log file.', 'wp-base'),
			__('Existing A+ settings will also be imported. Since WP BASE has much more features than A+, missing settings will be completed from current values. You may also want to reset settings to their default values before importing.', 'wp-base'),
			__('To start the import, simply click the related button and confirm the javascript message. The actual import process may take a few minutes depending on the size of your records.', 'wp-base'),
			__('After the import process, you should see a message indicating the result: Failure, success or partial success. In every case you can check the log file for the details of the result.', 'wp-base'),
			__('To import A+ from a website (source) to a WP BASE installed on another website (target), you can use this method: Install WP BASE and Export/Import Addon on both websites. In the source, import A+ to WP BASE and then export Settings and Database Tables to your PC. Then you can import these two files to the target from your PC. Multisite WordPress to solo WordPress export/imports and vice versa are allowed.', 'wp-base'),
			__('Import will also work for Appointments Lite, the free version of A+.', 'wp-base'),
		)
		);
	}

	/**
     * Addon page descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function addon_desc( ) {
		return array( 
			__( '<i>Addons extend the functionality of WP BASE. Most addons add setting fields to Settings page. By default, all addons are automatically activated. We recommend that you deactivate addons you are not using. After you have finished configuring your website, click <b>Deactivate Unused Addons</b> button to find out which addons are not in use and automatically deactivate unused ones.</i>', 'wp-base')
		);
	}

	/**
     * Coupon descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function coupon_desc( ) {
		return array( 
			__('<i>Coupons (a.k.a discount codes, promo codes) are alphanumerical character sets which allow clients get discounts when they enter the correct code in the confirmation form coupon entry field which is automatically displayed when applicable.</i>', 'wp-base'),
			__('As you click "Add New Coupon" button a new empty row will be inserted on top of the existing coupons. Coupons are listed in reverse order, thus most recent ones are on top.', 'wp-base'),
			__('<b>ID field:</b> is automatically given by the system and it is unique.', 'wp-base'),
			__('<b>Code field:</b> is set of characters your client supposed to enter to gain a discount. Letters are case insensitive. Only alphanumeric characters are allowed, with "-" (dash without quotation marks) being exception and having a special meaning: Numeric value after dash stands for user ID that code is valid for. This is called a <b>user specific</b> coupon. For user specific coupons, the below "for users" setting is irrelevant, including "everybody" setting. Example: Code QwE123-5 can only be used by the (logged in) client having WordPress user ID 5.', 'wp-base'),
			__('<b>Discount (%) and Discount (Currency) fields:</b> define the discount that will be applied to the sales price. Discount (%), applies a discount over the total price by the percentage entered. Discount (Currency), applies a fixed discount over the total price by the value entered. If both of them are entered, fixed discount will be used. Tip: Entering "100" in the percentage field will make the service(s) free of charge.', 'wp-base'),
			__('<b>Max uses field:</b> defines how many times this coupon can be used. If left empty there is no usage limit.', 'wp-base'),
			__('<b>Used field:</b> is read-only and displays how many times this coupon was used.', 'wp-base'),
			__('<b>Valid from field:</b> defines the date (including the date) coupon can be used starting from. If left empty, coupon can be used immediately.', 'wp-base'),
			__('<b>Valid until field:</b> defines expiry date of the coupon. Including that date and onwards, coupon cannot be used. If left empty, coupon can be used indefinitely (or until max uses limit).', 'wp-base'),
			__('<b>Applies to field:</b> defines for which services and/or providers this coupon will be applied. Multiple selection is possible. This means, depending on "with match" setting, in case of any match or if all of the selections match the coupon will be applicable. If nothing is selected, coupon will be valid for all bookings.', 'wp-base'),
			__('<b>With match field:</b> defines how multiple selections of "applies to" setting will be handled. "Any match" will result in the coupon to be applicable when any one of the services and providers selected in "applies to" setting are subjects of the booking. For example when "applies to" has "Sample Service, Service Provider SP A" selections, Sample Service given by any provider or any service given by SP A make the coupon applicable. "All must match" setting limits the coupon to be applicable only when all "applies to" selections are subjects of the booking. Please note that for this selection only one pick from services and one pick from providers is reasonable. For the same example, coupon will be applicable for only Sample Service given by SP A. Tip: To make "Sample Service given by SP A or SP B" logic possible, you can enter a second record with the same code and settings except "applies to" is "Sample Service, SP B".', 'wp-base'),
			__('<b>For users field:</b> defines to whom this coupon will be applied. That is, only clients having selected WP user role can use the coupon, allowing you to generate member only coupons. You can select multiple WP user roles. Not selecting any role means rule will be applied to everyone, including non logged in users. To apply for all logged in users, but not for non logged in users, select all roles.', 'wp-base'),
			__('You can add as many records (coupons) as you wish and they will only be saved after you hit Save Coupons button.', 'wp-base'),
			__('New inserted record(s) will not be saved unless you fill in the code field and click "Save Coupons" button (Save button is only visible if there is at least one record added).', 'wp-base'),
			__('To delete a record empty Code field and Save.', 'wp-base'),
			__('A coupon becomes inactive if a) No discount is defined, b) it is used "max uses" times, c) "Valid from" date has not yet arrived, d) "Valid until" date has arrived. Inactive coupons are shown faded.', 'wp-base'),
			__('Coupon entry field in the confirmation form is displayed only if there is at least one active coupon applicable to the selected service or provider and user role of the client.', 'wp-base'),
			__('It is possible to have more than one coupon with the same code with different discount and different "applies to" setting, e.g. to apply higher discount for a particular service or provider selection. In this case coupon with the highest net discount will be applied.', 'wp-base'),
			__('Coupons addon runs after Easy Custom Pricing rules and Advanced Custom Pricing codes.', 'wp-base'),
			__('When client enters correct coupon code, total price on the confirmation form will be updated on the fly.', 'wp-base'),
		);
	}

	/**
     * Easy pricing descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function ep_desc( ) {
		return array( 
			__('With <b>Easy Custom Pricing</b>, you can add as many records as you wish to apply discount or override regular price of the service depending on selected service, provider, date, day and time of the appointment and WP user role of the client. Each Easy Custom Pricing record is called a <b>rule</b>.', 'wp-base'),
			__('As you click "Add New Rule" button a new empty row will be inserted on top of the existing rules. Rules are listed in reverse order, thus most recent ones are on top.', 'wp-base'),
			__('<b>ID field:</b> is automatically given by the system and it is unique.', 'wp-base'),
			__('<b>Discount (%) and Price (Currency) fields:</b> determine the net price that will be applied to the sales price. Discount (%), applies a discount over the total price by the percentage entered. Price (Currency), overrides the regular price (which is the sum of the selected location, service and provider prices) by the value entered. If both of them are entered, price field will be used. Tip: 1) Entering "100" in the discount field will make the service free of charge as far as rule applies. 2) Negative discount is allowed which means surcharge of the regular price.', 'wp-base'),
			__('<b>Applies to field:</b> defines for which services and/or providers this rule will be applied. Multiple selection is possible. This means, depending on "with match" setting, in case of any match or if all of the selections match the rule will be applicable. If nothing is selected, rule will be valid for all booking submissions.', 'wp-base'),
			__('<b>With match field:</b> defines how multiple selections of "applies to" setting will be handled. "Any match" will result in the rule to be applicable when any one of the services and providers selected in "applies to" setting are subjects of the booking. For example when "applies to" has "Sample Service, Service Provider SP A" selections, Sample Service given by any provider or any service given by SP A make the rule applicable. "All must match" setting limits the rule to be applicable only when all "applies to" selections are subjects of the booking. Please note that for this selection only one pick from services and/or one pick from providers is reasonable. For the same example, rule will be applicable for only Sample Service given by SP A. Tip: To make "Sample Service given by SP A or SP B" logic possible, you can enter a second record with the same settings except "applies to" is "Sample Service, SP B".', 'wp-base'),
			__('<b>For users field:</b> defines to whom this rule will be applied. That is, clients having selected WP user role will be subject to the rule. You can select multiple WP user roles. Not selecting any role means rule will be applied to everyone, including non logged in users. To apply for all logged in users, but not for non logged in users, select all roles.', 'wp-base'),
			__('<b>From, To Pax fields:</b> require Group Bookings addon. They define range of seat/pax selection for which the rule will be applied. Either of the fields can be left empty. An empty From Pax field is considered as 1, and an empty To Pax field is taken as maximum possible value.', 'wp-base'),
			__('<b>Valid for field:</b> defines the recurrence of this rule. "Selected range" will make the rule applied to bookings whose starting time is between From and To date/times entered in the proceeding four fields. Every sunday, monday, etc will make the rule applied for every selected week day for bookings whose starting time are between start and end times selected in the proceeding two fields. With this selection, from/to dates will be irrelevant and be hidden. "Always" setting will make date limitation void and date/time fields will be hidden.', 'wp-base'),
			__('<b>From, To Date/time fields:</b> define time range for which rule will be applied. Selection values are included in the range, e.g. 10:00 start and 10:00 end times will match bookings made for appointments starting at 10:00. For recurring week days, dates fields will be irrelevant and be hidden. Set time fields to "All day" for the rule to be valid for the whole day. Please note that this range is for the starting time of the appointment: Rules do not check for the ending time. Also this is the time of the appointment submission is made for, NOT time of the submission.', 'wp-base'),
			__('You can add as many records (rules) as you wish and they will only be saved after you hit Save Changes button.', 'wp-base'),
			__('New inserted record(s) will not be saved unless you fill in the price or discount field and click "Save Changes" button on the page.', 'wp-base'),
			__('Either a discount or price value is required to save a rule. As a result, to <b>delete a record</b> empty discount and price fields and Save.', 'wp-base'),
			__('A rule becomes inactive if selected dates are impossible to apply, for example From value is later than To value (Equal From and To values do not mean inactive and actually match for that value only). Inactive rules are shown faded and they do not have any effect on the price.', 'wp-base'),
			__('If there are more than one rule matching to the booking submission, price will be determined by "Price to be applied if more than one rule match" setting (Default: rule leading to lowest net price).', 'wp-base'),
			__('If no rules match to the booking submission, regular price of the service will be valid.', 'wp-base'),
			__('It is possible to use Easy Custom Pricing and Advanced Custom Pricing at the same time (actually, back to back). Advanced Custom Pricing changes prices after Easy Custom Pricing. For example, you can get the net price calculated by Easy Custom Pricing and use it in prepayment field of Advanced Custom Pricing (Using PRICE placeholder in Advanced Custom Pricing fields will be sufficent for this. The rest is automatic.)', 'wp-base'),
		);
	}

	/**
     * Advanced pricing descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function ap_desc( ) {
		return array( 
			__('With <b>Advanced Custom Pricing</b>, You can override prices depending on a certain condition or combination of several conditions.', 'wp-base'),
			__('Write your code in php as usual and just replace known WP BASE variables with placeholders given below, e.g. to compare name of the service client is applying for, use SERVICE.', 'wp-base'),
			__('If your expression does not make any changes, price generated by WP BASE will be applied. So you don\'t need to worry about "else" condition. See example 1.', 'wp-base'),
			__('All codes should be valid php. If codes are not valid, you will get an error message after saving and those codes will not be executed; price generated by WP BASE will be applied. So wrong coding here will not crash your website.', 'wp-base'),
			__('You need to use at least one RESULT placeholder which will be transferred as the resulting price. Please see the examples where RESULT is always on the left hand side of the equal sign, which means transferring of the calculated value there. If you do not use a RESULT placeholder, you will get an error message after saving and those codes will not be executed; price generated by WP BASE will be applied.', 'wp-base'),
			__('As far as they are valid php, you can use as many expressions as you wish, thus you can combine several cases together.', 'wp-base'),
			__('You can permanently disable this addon by adding <code>define("APP_DISABLE_ADVANCED_PRICING", true);</code> in wp-config.php', 'wp-base'),
			__('It is possible to use Easy Custom Pricing and Advanced Custom Pricing at the same time (actually, back to back). Advanced Custom Pricing changes prices after Easy Custom Pricing. For example, you can get the net price calculated by Easy Custom Pricing and use it in prepayment field of Advanced Custom Pricing (Using PRICE placeholder in Advanced Custom Pricing fields will be sufficent for this. The rest is automatic.)', 'wp-base'),
			__('These are the placeholders which will be replaced by their real values:', 'wp-base'),
			__('RESULT: Transfers the calculated price to the system and overrides the old price if has been changed. <b>Always required.</b>', 'wp-base'),
			__('PRICE: Price calculated until this point by WP BASE. This is also the return value if conditions of the codes in the below Final Price and Prepayment fields are not met', 'wp-base'),
			__('NAME, FIRST_NAME, LAST_NAME, EMAIL, PHONE, ADDRESS, CITY, ZIP, STATE, COUNTRY: Submitted client field values. Empty string is returned if that field is not in the form.', 'wp-base'),
			__('LOCATION_PRICE: Additional price of the selected location. If location is not selected, or price is not set returns 0', 'wp-base'),
			__('SERVICE_PRICE: Price of the selected service. If price is not set returns 0', 'wp-base'),
			__('WORKER_PRICE: Additional price of the selected provider. If provider is not selected, or price is not set returns 0', 'wp-base'),
			__('LOCATION_ID: ID of the selected location. If location is not selected, or there are no locations defined returns 0', 'wp-base'),
			__('SERVICE_ID: ID of the selected service.', 'wp-base'),
			__('WORKER_ID: ID of the selected provider. If provider is not selected, or there are no providers defined returns 0', 'wp-base'),
			__('LOCATION, SERVICE, WORKER: Name of the selected location, service or worker, respectively.', 'wp-base'),
			__('DATE_TIME: Date/time of the current appointment in "Y-m-d H:i:s" format, e.g. 2013-09-25 13:15:00', 'wp-base'),
			__('DATE: Date of the current appointment in "Y-m-d" format, e.g. 2013-09-25', 'wp-base'),
			__('WEEKDAY: Full name for the day of the current appointment in English, e.g. Sunday, Monday, etc', 'wp-base'),
			__('HOUR: Starting hour of the current appointment in 24 hours (military) format, e.g. 13', 'wp-base'),
			__('MINUTE: Starting minute part of the current appointment, e.g. 15 for an appointment starting at 13:15', 'wp-base'),
			__('DURATION: Effective duration of the current service in minutes. Includes duration modifications by Variable Durations addon.', 'wp-base'),
			__('REPEAT: Number of repeats selected by the client for Recurring Appointments addon. Returns 1 if addon is not active', 'wp-base'),
			__('REPEAT_UNIT: Repeat unit (Recurrence of the repeat: day, week, 2week, month) selected by the client for Recurring Appointments addon.', 'wp-base'),
			__('PAX: Number of guests/seats booked in the current appointment with Group Bookings Addon. Returns 1 if addon is not active.', 'wp-base'),
			__('NOF_APPS: Number of time slots booked in the current appointment booking process. Requires any one of the multiple appointments addons. Returns 1 if such an addon is not activate.', 'wp-base'),
			__('START: Timestamp for starting time of the current appointment. You can use this for more complex date/time comparisons', 'wp-base'),
			__('END: Timestamp for end time of the current appointment. You can use this for more complex date/time comparisons', 'wp-base'),
			__('USER_ID: ID of the user who is applying for the current appointment', 'wp-base'),
			__('USER_NAME: Login name of the user who is applying for the current appointment', 'wp-base'),
			__('TOTAL_MONTHLY_ORDERS: Total amount of money for the last 30 days paid by the logged in user applying for the current appointment. Deposits are not taken into account', 'wp-base'),
			__('TOTAL_WEEKLY_ORDERS: Total amount of money for the last 7 days paid by the logged in user applying for the current appointment. Deposits are not taken into account', 'wp-base'),
			__('TOTAL_MONTHLY_APPS: Total number of paid, confirmed or completed appointments within the last 30 days by the logged in user applying for the current appointment', 'wp-base'),
			__('TOTAL_WEEKLY_APPS: Total number of paid, confirmed or completed appointments within the last 7 days by the logged in user applying for the current appointment', 'wp-base'),
			__('PAYMENT_METHOD: Admin name of the payment method, e.g. manual-payments, paypal-standard, etc', 'wp-base'),
			sprintf( __('Example 1 - Applying a discount of 20%% for appointments between 15:00-17:00 for logged in users (happy hours). For the rest of the time and users, price generated by WP BASE (full price or deposit) will be valid.:%s', 'wp-base'), '<code>if (is_user_logged_in() AND HOUR>=15 AND HOUR<=17){RESULT=PRICE*0.8;}</code>' ),
			sprintf( __('Example 2 - Manually quick overriding of all prices with $12.3 except for "Sample Service" when you have hundreds of services or providers and you don\'t want to edit them one by one, e.g. during a campaign. Note that service name comparison is case insensitive:%s', 'wp-base'), '<code>if (SERVICE != "sample service"){RESULT=12.3;}</code>' ),
			sprintf( __('Example 3 - Making appointments free for user with ID=1 or with username "demo":%s', 'wp-base'), "<code>if (1==USER_ID || 'demo'==USER_NAME){RESULT=0;}</code>" ),
			sprintf( __('Example 4 - Applying a discount of 30%% to logged in users who paid for 100$ in the past 30 days:%s', 'wp-base'), "<code>if (TOTAL_MONTHLY_ORDERS>=100){RESULT=PRICE*0.7;}</code>" ),
			sprintf( __('Example 5 - Making the 11th appointment free for logged in users who applied and paid for 10 appointments in the past 7 days (Also works for 21st appointment free for 20, etc. - Take 10, get 1 free):%s', 'wp-base'), "<code>if (0==TOTAL_WEEKLY_APPS % 10){RESULT=0;}</code>" ),
			sprintf( __('Example 6 (With Shopping Cart Addon) - Making ONE time slot of service "Manicure" free of charge after third time slot booked (Buy 3, get a special service free):%s', 'wp-base'), '<code>global $free_applied; if (!$free_applied AND NOF_APPS>=4 AND "Manicure"==SERVICE){RESULT=0; $free_applied=true;}</code>' ),
			sprintf( __('Example 7 (With Group Bookings Addon) - Applying special variable discount (5, 10 or $15) when there are 2, 3, 4+ guests/pax selected:%s', 'wp-base'), "<code>switch(PAX){case 1: RESULT=PRICE; break; case 2: RESULT=PRICE-5;break; case 3: RESULT=PRICE-10;break; default: RESULT=PRICE-15;break;}</code>" ),
			sprintf( __('Example 8 (With UDFs Addon) - Assuming in UDF_1 seats for children is being asked ($10 each), and in UDF_2 seats for adults is being asked ($20 each), calculate price:%s', 'wp-base'), "<code>RESULT = UDF_1*10 + UDF_2*20;</code>" ),

		);
	}

	/**
     * Extras descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function extra_desc( ) {
		return array( 
			__('<i>Extras can be additional equipment, material, facility or another service (called "extra service") in addition to the currently selected one which shall be offered to the client as an option on the confirmation form.</i>', 'wp-base'),
			__('Each row entered here is a different extra record which may be displayed as a pulldown menu selection option on the confirmation form depending on whether parameters of the appointment (service, provider, starting date, time, day of the week) and user role match to the requirements defined in the record.', 'wp-base'),
			__('Only extras which comply the requirements will be presented to the client on the front end. Therefore number of selectable options in the selection menu may vary for each booking.', 'wp-base'),
			__('Using settings of the records, you can control the options to offer to the client, from the most simplest case (directly displaying all of the options without any conditionals) to the more complex ones (Showing completely or partially different selection options to different users and/or at different times).', 'wp-base'),
			__('When client picks up an option from the pulldown menu, total price will be updated on the fly.', 'wp-base'),
			__('When client submits the booking using this selection, it will be saved in the database, therefore you can review and make necessary arrangements based on the requirements of the selected option. Tip: You can view Extras as a separate column on Bookings page using "Screen Options".', 'wp-base'),
			__('If you want to offer a product or unlisted service as an extra, use "Add New Item as Extra" button, if you want to offer an existing WP BASE service as an extra, use "Add Existing Service as Extra" button. Mixing two types is allowed.', 'wp-base'),
			__('Availability of extra services is checked against selected time slot, and unavailable services are not offered to the client. Please note that currently selected service are not taken into account for availability check. For this reason, if provider gives both selected and extra services, it is possible that he may be assigned for both. WP BASE does not consider this as overbooking.', 'wp-base'),
			__('Extra service should be different from the selected service. Tip: You can use Group Bookings addon for this purpose.', 'wp-base'),
			__('Extra service cannot be a service package.', 'wp-base'),
			__('Duration of extra service is not added to the main service, but recorded as a separate booking starting from selected appointment time. Therefore if duration of main service is 1 and that of extra service is 2, total duration will be 2 hours, not 3.', 'wp-base'),
			__('Upon succesful submission, each extra service will create an appointment connected to (child of) the main appointment.', 'wp-base'),
			__('As you click "Add..." button a new empty row will be inserted.', 'wp-base'),
			__('<b>ID field:</b> is automatically given by the system and it is unique.', 'wp-base'),
			__('<b>Name field:</b> is the display name of the option which will be used in the pulldown menu on the confirmation form. Name field is always required to save a record.', 'wp-base'),
			__('<b>+Price field:</b> is the <b>additional price</b> that will be added to the regular price in case this extra is selected. It can be empty (zero additional price) or even negative, for example if selecting that option leads to less cost for you.', 'wp-base'),
			__('<b>Description page:</b> is an optional page that may be used to explain scope of supply of the extra. If this page has a WP post excerpt it will be used. Otherwise an automatic excerpt will be created from the content of the page. If the page has a featured image, it will also be included. The resulting description will be displayed beside selection menu as tooltip. Pages with status "publish" and "private" are selectable. Type of this page (page, post, custom post) is the same that of Services and it can be selected from Advanced &rarr; Admin settings (Advanced Features addon is required). ', 'wp-base'),
			__('<b>Applies to field:</b> defines for which services and/or provider selection(s) this option will be displayed. Multiple selection is possible. This means, depending on "with match" setting, in case of any match or if all of the selections match option will be visible. If nothing is selected, option will always be available.', 'wp-base'),
			__('<b>With match field:</b> defines how multiple selections of "applies to" setting will be handled. "Any match" will result in the option to be visible when any one of the services and providers selected in "applies to" setting are subjects of the booking. For example when "applies to" has "Sample Service, Service Provider SP A" selections, Sample Service given by any provider or any service given by SP A makes the option visible. "All must match" setting limits the option to be visible only when all "applies to" selections are subjects of the booking. Please note that for this selection only one pick from services and/or one pick from providers is reasonable. For the same example, option will be applicable for only Sample Service given by SP A. Tip: To display the option when "Sample Service given by SP A or SP B", you can enter a second record with the same settings except "applies to" is "Sample Service, SP B".', 'wp-base'),
			__('<b>For users field:</b> defines to whom this option will be displayed. That is, clients having selected WP user role will be eligible to view and select the option. You can pick multiple WP user roles which means more users can be eligible. Not selecting any role means option will be visible to everyone, including non logged in users. To apply for all logged in users, but not for non logged in users, select all roles. This setting allows you offer role specific options to your members.', 'wp-base'),
			__('<b>Valid for field:</b> defines what type of date selections will make this option visible. "Selected range" will make the option visible for appointments whose starting time is between From and To date/times entered in the proceeding four fields. Every sunday, monday, etc will make the option visible for every selected week day for bookings whose starting time are between start and end times selected in the proceeding two fields. With this selection, from/to dates will be irrelevant and be hidden. "Anytime" setting will make date limitation void and date/time fields will be hidden.', 'wp-base'),
			__('<b>From, To fields:</b> define time range of the appointment for which option is visible. Selected values are included in the range, e.g. 10:00 start and 10:00 end times will match bookings made for appointments starting at 10:00. For recurring week days, dates fields will be irrelevant and be hidden. Set time fields to "All day" for the option to be visible for any time of day. Please note that this range is for the starting time of the appointment: Addon does not check the ending time. Also this is the time of the appointment submission is made for, NOT time of the submission.', 'wp-base'),
			__('You can add as many records (extras) as you wish and they will only be saved after you hit Save Records button.', 'wp-base'),
			__('New inserted record(s) will not be saved unless you fill in the "Name" field and click "Save Extras" button (Save button is only visible if there is at least one record added).', 'wp-base'),
			__('To delete a record empty "Name" field and Save.', 'wp-base'),
			__('A record becomes inactive if selected dates are impossible to apply, for example From value is later than To value (Equal From and To values do not mean inactive and actually match for that value only). Inactive records are shown faded and they are not visible as option on the front end in any case.', 'wp-base'),
			__('If no records match to the booking submission, select extras pulldown menu will not be displayed to the client.', 'wp-base'),
			__('Sorting: Extras are displayed in the order as displayed in the list. To change the sort order, select the row with your mouse and move the complete row to the new position (drag and drop) and then click Save.', 'wp-base'),
		);
	}
	
	/**
     * UDF descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function group_bookings_desc( ) {
		return array( 
			sprintf( __('Group Bookings addon allows your clients book for more than one person/seat (also called %s) for the service for any time slot.', 'wp-base'), '<a href="https://en.wiktionary.org/wiki/pax#Etymology_2" target="_blank">'.__('pax','wp-base').'</a>' ),
			__('In order to work, capacity of the service should be greater than 1, set either by number of service providers or by manual capacity setting.', 'wp-base'),
			__('To activate Group Bookings for a service, click <b>Enable</b> checkbox.', 'wp-base'),
			__('When minimum value is entered, client cannot apply for less than this number. If left empty, minimum selection will be taken as 1.', 'wp-base'),
			__('When maximum value is entered, client cannot apply for greater than this number. If left empty or set higher than capacity of the service, maximum selection will be set to capacity value.', 'wp-base'),
			__('While entering minimum and maximum selection values are optional, they can be set to the same number, e.g. to sell a fixed number of seats.', 'wp-base'),
			__('You can select to ask participants\' details on the confirmation form by <b>participant fields</b> settings.', 'wp-base'),
			__('Name, email, phone and address of the participants may be selected to be filled separately. "No" means that field will not be displayed. "Optional" means field will be displayed, but filling is not mandatory. "Required" means field has to be filled, otherwise form will not be submitted.', 'wp-base'),
			__('If enabled for the current service, <code>[app_book]</code> will automatically add a selection dropdown menu after service selection menu. A stand alone <code>[app_seats]</code> shortcode is also available to create this menu in case of modular shortcodes.', 'wp-base'),
			__('When group bookings is enabled for a service, a "G" will be seen on top-right of service ID in the List of Services.', 'wp-base'),
			__('When client makes a new pax selection, Booking Views will be automatically updated and free slots will be displayed based on the new pax selection and actual available space. Price will be calculated as regular price times pax.', 'wp-base'),
			__('When client checks out, a single booking will be created with the submitted pax. This value can be manually changed on admin Bookings page, if required.', 'wp-base'),
			__('Group Bookings can be used in combination with any other addon. For example when used in combination with Shopping Cart, client can select pax for each item separately.', 'wp-base'),
		);
	}
	
	/**
     * UDF descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function udf_desc( ) {
		return array( 
			__('As you click "Add New UDF" button a new empty row will be inserted.', 'wp-base'),
			__('<b>ID field:</b> is automatically given by the system and it is unique. You can use this ID in other settings to evaluate submitted values. For example, UDF_1 in email message settings will be replaced by the real value of UDF with ID=1 submitted by the client.', 'wp-base'),
			__('<b>Name field:</b> You can use anything as the UDF name here, but it is recommended to be unique if you plan to call it in your customization codes using the name, instead of ID.', 'wp-base'),
			__('<b>Type field:</b> Select type of the form field. In addition to standard HTML fields (text, text area, pulldown menu, checkbox) the following selections are possible: 1) "Phone" will add an input field with phone formatting and validation. 2) "Date" will add an input field with datepicker. Maximum selectable days from today can be entered in options setting. 3) "Function" will add a pulldown menu whose output shall be generated by the custom function entered in Options setting.', 'wp-base'),
			__('<b>Active field:</b> UDFs marked as active will be displayed in the confirmation form. Check the checkbox of the UDF to make it active. Note: If a UDF is specifically selected in an attribute (e.g. <code>[app_confirmation fields="udf_1"]</code>), then it will be visible regardless of active setting.', 'wp-base'),
			__('<b>Required field:</b> Check the checkbox if you want to set filling of the field as mandatory. If a field is set as required and it is visible, client will get a warning message if he submits the field empty.', 'wp-base'),
			__('<b>Options field:</b> 1) For text, textarea and phone fields, this is the placeholder value. 2) For pulldown menu, values separated by commas will be your selection options. For example 0-1,1-5,5-10,11+ will create 4 selection options. It is also possible to use CAPACITY placeholder here which will be replaced by capacity of the service: 1-CAPACITY will give selection options from 1 to whatever service capacity is. 3) For checkbox, the value entered here is the text beside the checkbox. 4) For Date type, this is the maximum number of days selectable in the datepicker counted from today. Negative values are possible, e.g. entering -6575 will only allow picking birthdays for users who are 18 years and older. 5) For Function this is the name of the user function that will create pulldown menu html. If function does not exist, an empty html is outputted.', 'wp-base'),
			__('You can add as many records (fields) as you wish and they will only be saved after you hit Save UDF button.', 'wp-base'),
			__('New inserted field(s) will not be saved unless you fill in the name field and click "Save UDFs" button (Save button is only visible if there is at least one record added).', 'wp-base'),
			__('The inserted field will be visible in appointments records and you can edit submitted values for each appointment there.', 'wp-base'),
			__('To delete a record empty Name field and Save.', 'wp-base'),
			__('<b>Sorting:</b> On front end items are displayed according to the order here, as default. To make a custom sorting, select the row with your mouse and move it to the new position (drag and drop) and then click Save. Note: You can do sorting in confirmation shortcode too. For example <code>[app_confirmation fields="name,udf_3,udf_1"]</code> will display the fields on the form in the order as they are written: name, udf_3 and udf_1.', 'wp-base'),
				
		);
	}
	
	/**
     * Selectable durations descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function sel_dur_desc( ) {
		return array( 
			__('Selectable Durations allow your clients pick desired duration of service on the front end by a pull down menu.', 'wp-base'),
			sprintf( __('This menu is automatically displayed by <code>[app_book]</code>. If you are not using this shortcode, e.g. in case of modular shortcodes, you can manually add the menu to the desired position on the page with <code>[app_durations]</code>. For description of attributes for both shortcodes see %s.', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_help&tab=shortcodes').'">'.__('related help page','wp-base').'</a>'),
			sprintf( __('You can limit which durations are selectable on the front end using the multiselect pulldown menu in the settings. This menu includes selectable values from regular duration of the service upto 24 hours in increments of the %s. Note: It is not possible to select duration less than regular duration. If this is required, try reducing regular duration and/or time base.', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_settings#time-base').'">'.WpBConstant::get_setting_name('min_time').'</a>'),
			__('To activate the selectable durations feature for a particular service, check the "Enable" checkbox and select at least 2 durations. It is allowed, but not recommended to omit the regular duration, because on first page load, WP BASE will display the available slots based on regular duration and omitting this value may be confusing.', 'wp-base'),
			__('If you want to apply service price based on client duration selection, fill in the Unit Price field. If you leave this field empty, service price will be fixed at the regular price regardless of selected duration.', 'wp-base'),
			__('If you set a unit price, regular price of the service will also be matched to this value. For example, for a 30 minutes service if you enter $100 as unit price, regular price will be automatically set to $50.', 'wp-base'),
			__('Unit price is also effective for Time Variant durations.', 'wp-base'),
			__('If both Selectable Durations and Time Variant Durations are enabled, for overlapping time slots the latter one has priority.', 'wp-base'),
			__('Selectable Durations and Recurring Appointments can be used in combination. For example, client can select 1, 2 or 4 hours of a service recurring every week for 1 to 8 weeks.', 'wp-base'),
			__('Selectable duration is not possible for packages. If Packages is active, durations cannot be selected on the front end.', 'wp-base'),
		);
	}

	/**
     * Time variable durations descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function time_dur_desc( ) {
		return array( 
			__('Time and SP Variant Durations allow you preset different service durations for different week days and times. For example you can set a service to last for 1 hour in the morning, 2 hours on Monday afternoons, 3 hours on Friday afternoons.', 'wp-base'),
			__('It is also possible to set different durations for different service providers, for example if a particular service provider is slower than the others.', 'wp-base'),
			__('The regular duration of the service is overridden if a row of settings (called a "rule") matches with the selected service provider and date/time.', 'wp-base'),
			__('To activate the selectable durations feature for a particular service, check the "Enable" checkbox and add at least one rule by clicking "Add Rule" button.', 'wp-base'),
			__('In the inserted new row, select the desired new duration, service provider, week day and time for which rule will be effective. Selected from/to values are included in the range, e.g. from 10:00, to 10:00 settings will match for time slots starting at 10:00 only. Addon does not account for the end time of the slot.', 'wp-base'),
			__('If you have the same rule for multiple days of the week, simply add a rule for each day.', 'wp-base'),
			__('In case there are more than one matching rules, you can select the rule to be applied: First match, last match, min duration, max duration and regular duration.', 'wp-base'),
			__('You can custom sort the rules by selecting the rule row with mouse and move it to the selected position.', 'wp-base'),
			__('If you want to apply service price based on duration of the service, fill in the Unit Price field in Selectable Durations part. If you leave this field empty, service price will be fixed at the regular price regardless of selected duration.', 'wp-base'),
			__('If both Selectable Durations and Time Variant Durations are enabled, the latter one has priority provided that at least a rule matches.', 'wp-base'),
			__('Time & SP Variant Durations and Recurring Appointments can be used in combination. For example if a 2 hours training is set to be 4 hours at the weekends in Time variant Durations, selecting 7 days will automatically include extra hours for the weekends and price will be correctly calculated from unit price.', 'wp-base'),
			__('Time & SP Variant Durations selection is not meaningful for a package, because the duration of a package is determined by its jobs. You can use time variant duration settings directly in the services making the jobs of a package, however. Example: Consider a back-to-back service package of Hair cut and Manicure which normally lasts for 60 minutes each and 120 minutes in total. Suppose that you have a hairdresser who cuts hair at 90 minutes. You can define a rule for him/her in "Hair Cut" service (not in the package) and when he/she is picked up, hair cut + manicure package will last for 150 minutes, not 120.', 'wp-base'),
		);
	}

	/**
     * Recurring Appointments descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function recurring_desc( ) {
		return array( 
			__('Recurring Appointments addon allows your clients pick number of repeats of service on the front end by two pull down menus (Number of repeats and recurrence frequency).', 'wp-base'),
			__('You can select which recurrence frequencies (called repeat units), are available to the client on the front end e.g. daily, weekly, monthly. To do so, select desired repeat units by checking <b>Allow</b> checkbox.', 'wp-base'),
			sprintf( __('You can also select maximum number of repeats for each repeat unit by entering required number in the "Max" field. In any case, on the front end client will not be able to pick up a repeat number exceeding your %s. For example if your upper limit is 60 days, weekly selection will be limited to 8.', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_settings#app-upper-limit').'">'.WpBConstant::get_setting_name('app_limit').'</a>'),
			__('There are 3 monthly modes each having different behaviour. <u>Choose only one of them</u>. When client attempts to pick 31st of January (2018) for 4 months: 1) "Same day of week" will pick 31st of January, 28th of February, 28th of March, 25th of April, because they are all Wednesday. 2) "Same day of month" will pick 31st of January, 28th of February, 31st of March and 30th of April. That is, it will compensate impossible dates with last day of the month, staying in the desired month. 3) "Same day of month - strict" will not allow 31st of January to be picked in the first place, because 31st of February is not possible.', 'wp-base'),
			__('To activate recurring feature of a service, <b>Enabled</b> checkbox should be checked and at least one repeat unit should be marked as "Allow". Max fields can be left empty.', 'wp-base'),
			__('One of the cool features of the addon is customizing front end selection menu using <b>View of the menu</b> field. REPEAT_UNIT and REPEAT placeholders used in this field will be replaced with repeat unit (i.e. daily, weekly, monthly, etc) dropdown and number of repeats (1,2,3,...) dropdown, respectively. The default value is "REPEAT_UNIT for REPEAT times" which may be displayed as "Monthly for 3 times", for example. Modifying custom texts for weekly, monthly etc terms and using this field you can have a user friendly selection <em>per service</em>. REPEAT placeholder must always be used. REPEAT_UNIT can be omitted, provided that there is only one allowed repeat unit.', 'wp-base'),
			__('When a service is enabled and correctly configured as recurring, a "R" will be seen on top-right of service ID in the List of Services.', 'wp-base'),
			__('Time & SP Variant Durations and Recurring Appointments can be used in combination. For example if a 2 hours training is set to be 4 hours at the weekends in Time variant Durations, selecting 7 days will automatically include extra hours for the weekends and price will be correctly calculated from unit price.', 'wp-base'),
			__('Recurring Appointments and Selectable Durations can be used in combination. For example, client can select 1, 2 or 4 hours of a service recurring every week for 1 to 8 weeks (numbers are arbitrary).', 'wp-base'),
			__('Recurring Appointments and Packages can be used in combination. For an example see Packages addon. Please note that both addons require several iterations to mark a time slot as available or not. Therefore when used in combination, execution time may be quite high. To keep this time in tolerable limits, 1) Use lesser number of time slots to be scanned, for example by selecting one month instead of two months for Monthly Calendar View, 2) Use smaller maximum allowed repeats, 3) Avoid sequences in Packages, 4) If possible avoid using service providers and use capacity field instead, 5) If you know that there will be just a few available slots, consider using "Book Now Single Button" instead of calendar view.', 'wp-base'),
			__('You may consider using Shopping Cart in combination with Recurring Appointments. Then your client can remove unsuitable time slots on the confirmation form and add preferred ones.', 'wp-base'),
		);
	}

	/**
     * EXtended Service Features descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function esf_desc( ) {
		return array( 
			__('Extended Service Features addon extends existing functionality and adds new ones to services. As a part of it, in Limits area you can "fine tune" settings for various limits per service.', 'wp-base'),
			__('Limits for booking, cancelling and editing are already available globally, which means there is only one setting for each limit which is valid for all services.', 'wp-base'),
			__('These settings allow you to define every one of them individually and more precisely (in minutes instead of hours) for each service. If you leave a setting <i>blank</i>, global setting will be applied instead. Note: Entering zero does not mean leaving blank: it will override the global setting with zero for that service.', 'wp-base'),
			__('These settings also allow you to set different limits for weekday (Monday to Friday), weekends (Saturday and Sunday) and for definable special days.', 'wp-base'),
			__('For Lower Limit, Editing Limit and Cancellation Limit, the day here corresponds to the day that that appointment starts. In case of Upper Limit, this is the day of the submission in order to book that appointment. Tip: Entering a negative upper value completely turns off booking of the service. Therefore, using negative value for Special Days for Upper Value can be used to close booking *on* (not *for*) certain dates.', 'wp-base'),
			__('Special Days can be defined in its tab by clicking "Special Days" link in the settings or by directly clicking the tab. Simply select your special days on the calendar by clicking on desired dates for current year and following years.', 'wp-base'),
			__('Special Days are used in common with Quotas addon. Special days defined here are valid for both addons.', 'wp-base'),
			__('<b>Lower limit</b> (also called lead time) is the minimum time between start of the appointment and submission time to book for it.', 'wp-base'),
			__('<b>Upper limit</b> is the number of days that a client can apply for an appointment in advance, counted from submission date.', 'wp-base'),
			__('<b>Editing limit</b> is used in conjunction with Front End Edit addon and it is the minimum time between start of the appointment and start and finish editing.', 'wp-base'),
			__('<b>Cancellation limit</b> is the minimum time between start of the appointment and attempt to cancel a booking.', 'wp-base'),
		);
	}

	/**
     * Quotas descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function quotas_desc( ) {
		return array( 
			__('Quotas addon allows you to limit number of bookings of logged-in clients for each service for certain time intervals, namely for weekdays (Monday to Friday), weekends (Saturday and Sunday), definable special days, for weeks, months and total upcoming (paid, confirmed, pending), by checking previous bookings of the clients in those intervals.', 'wp-base'),
			__('To activate Quotas, simply enter quotas (number of bookings limit) in the related fields of the service which are visible when you click the "More" link. One or more fields can be left empty, which means there is no limitation for that interval.', 'wp-base'),
			__('While checking if quotas are full or not, previous bookings are queried for appointment start times, but not for the submission date/times. Quotas addon looks into neither when submissions are made, nor end times of the appointments.', 'wp-base'),
			__('Each interval is evaluated separately and none of the quotas must be reached or exceeded to make that time interval available to the client. For example, consider weekday quota as 2 and weekly quota as 5. For a particular Monday, if client makes 2 bookings, he cannot make another one for that Monday, but he can do 3 more in total for the other days of the week. Or, if he made one booking for each weekday, he cannot book another one for the whole week because he reached the weekly quota (Note that in this case weekend will also be blocked although there is no special quota setting for weekends).', 'wp-base'),
			__('Special Days can be defined in its tab by clicking the "Special Days" link in the settings or by directly clicking the tab. Simply select your special days on the calendar for current year and proceeding years, if you wish. Each special day is evaluated independently. For example, if you define 14th of February and 31st of December as special days and set a quota of 1, when client books for 14th of February, he can still book for 31st of December.', 'wp-base'),
			__('Special Days are used in common with Extended Service Features addon. Special days defined here are applied to both addons.', 'wp-base'),
			__('Except for Total Upcoming case, booking statuses used in quota evaluation are "upcoming" (pending, paid, confirmed), "running now" and "completed". The inclusion of completed status is necessary for intended functionality of quotas. In the above example where client had a booking for each weekday, he has to wait until next week to make an additional booking, which is the correct meaning of a "weekly quota". If completed status had not been included, client would be able to book a new one on Monday after current Monday appointment completed, a second one on Tuesday in the same way, and so on.', 'wp-base'),
			__('Total Upcoming setting defines the limit for total number of upcoming (pending, paid, confirmed), in other words, open bookings. This can be especially useful if you are accepting bookings over an extended period of time, say 1 year.', 'wp-base'),
			__('To modify booking statuses used in quotas and thus changing the above explained bahaviour, two different filter hooks are also provided.', 'wp-base'),
			sprintf( __('Note that quotas only work for logged in clients. Therefore you may want to set logging in mandatory by adjustıng %s as Yes.', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_settings&tab=login#login-required').'">'.WpBConstant::get_setting_name('login_required').'</a>'),
			__('As the main idea of quotas is managing limited resources, in this case which is services and service providers whose working hours are saved based on server time zone, client time zone is not taken into account.', 'wp-base'),
		);
	}

	/**
     * Packages descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function packages_desc( ) {
		return array( 
			__('Packages Addon allows you to combine <b>two or more services</b> (each of them is called a <b>job</b>) and sell them as if a single service, for example hair cut + manicure package consisting of hair cut and manicure services.', 'wp-base'),
			__('Jobs can start at the same time, can be back-to-back or separated with preselected time in minutes or days. To do so, a <b>delay</b> setting is provided for each job that will be used to wait for the next job to start.', 'wp-base'),
			__('Delay value is calculated from starting of the current job. Therefore in a package "hair cut - 60 minutes delay - manicure" where both services have duration of 60 minutes, hair cut and manicure will be served back-to-back.', 'wp-base'),
			__('Entering zero delay means current and next job will be served at the same time. Hair cut - no delay - manicure will result in two services being booked for the same time slot.', 'wp-base'),
			sprintf(__('If in minutes, delay time should be multiple of the %s. If it is not set so, it will be automatically rounded during save.', 'wp-base'), '<a href="'.admin_url('admin.php?page=app_settings#time-base').'">'.WpBConstant::get_setting_name('min_time').'</a>'),
			__('You may want to use <b>internal</b> services in packages. An internal service is a service which cannot be directly booked on the front end. For example, a hair dying package may consist of two internal services: just dying and dye processing. These two services cannot be ordered separately and should be set as internal. Note: Extended Service Features addon is required to define a service as internal.', 'wp-base'),
			__('Delay setting in packages allows micro-managing working hours. In hair dying example in between dying and dye processing there is a one hour waiting time when client is in the machine. Then we can define a package as: Dying (internal) - 120 minutes delay - Dye Processing (internal). In the 60 minutes gap, service provider is available for another client.', 'wp-base'),
			__('Some or all of the jobs can be the same service. For example for a training course (T) and 1 day delay (1DD), you can define such a package (TP/1W): T-1DD-T-1DD-T-1DD-T-1DD-T. Please note that this makes a 5 courses package lasting for 5 days and if service provider does not work at the weekends, booking for only Mondays is possible.', 'wp-base'),
			__('Other packages can be selected as jobs. For example we can create a 2 weeks training course from the above example: TP/2W = TP/1W-7DD-TP/1W. This may be further used for a 4 weeks package: TP/4W = TP/2W-14DD-TP/2W', 'wp-base'),
			__('Packages as jobs have a default maximum nesting depth of 2. This means Package 1 may include Package 2 as a job including Package 3 as a job, but Package 3 cannot include any package further. This also prevents circular referencing: Package 2 cannot include Package 1 and Package 3 cannot include 1 or 2. Obviously, a package cannot include itself as a job.', 'wp-base'),
			__('<b>Sequence</b> is the order of the jobs. In the first example, WP BASE will search for availability of first hair cut and then manicure, but we may actually accept that manicure is served first. Then, we should define this as a second sequence: Manicure - 60 minutes delay - hair cut.', 'wp-base'),
			__('For each possible and desired combination of the order of jobs, you need to define a sequence. In theory you can add as many sequences as you wish, however, each sequence means an additional execution time. Therefore avoid unnecessary combinations. For example, there is no need to add a sequence if jobs are served at the same time.', 'wp-base'),
			__('In hair dying example we would not want another sequence, because dye processing cannot be earlier than dying.', 'wp-base'),
			__('The order of sequences may be important. During creating free time slots on the front end, WP BASE will start checking availability of the first sequence and only proceed to the next sequence if current one is not available. As a result, you should place the sequences according to the preferred job order. You can change the ordering of sequences as explained below.', 'wp-base'),
			__('First of all create your services that will make the package (which will be jobs of the package), because only saved services can be selected when configuring a package.', 'wp-base'),
			__('Then you should add a sequence: A package must have at least one sequence. To create a sequence, click "Add Sequence" button. A new row with "Add Job" button will be inserted.', 'wp-base'),
			__('Click "Add Job" button to add a new job to the sequence. Select the service and desired delay for the job.', 'wp-base'),
			__('Continue adding jobs and setting them as much as required. At least two jobs are required per sequence. Tip: Recently added job copies adjustments of the previous job. Therefore if the jobs are identical as in the first training courses example above, setting the first job correctly will make creating the package quite easy.', 'wp-base'),
			__('Continue adding sequences and jobs for them as long as required.', 'wp-base'),
			__('You can set packages for other services in the same manner in parallel.', 'wp-base'),
			__('To activate the packages feature, check the "Enable" checkbox.', 'wp-base'),
			__('Click "Save Services" button.', 'wp-base'),
			__('To make a custom sorting (moving a sequence to an upper or lower position), select the sequence row with your mouse and move it to the new position.', 'wp-base'),
			__('You can delete a job by selecting "Delete job" selection (or leave as "Select Service" before save) and saving services.', 'wp-base'),
			__('A sequence is automatically cleared when there are no jobs or one job left, because at least 2 jobs are required to make a package.', 'wp-base'),
			__('When a service is enabled and correctly configured as a package, a "P" will be seen on top-right of service ID in the List of Services.', 'wp-base'),
			__('Selectable duration is not possible for packages. If Packages is active, durations cannot be selected on the front end.', 'wp-base'),
			__('Time & SP Variant Durations selection is not meaningful for a package, because the duration of a package is determined by its jobs. You can use time variant duration settings directly in the services making the jobs of a package, however.', 'wp-base'),
			__('Packages and Recurring Appointments can be used in combination. For the 5 days training package example, you can let the client pick week as repetition unit and client can book for 1, 2, 3,... weeks of the package. Regular price will be number of repeats multiplied by package price. Special pricing based on number of weeks can be defined in Advanced Pricing tab of Custom & Variable Pricing Addon.', 'wp-base'),
			__('Packages cannot be edited using Front End Edit.', 'wp-base'),
			__('Other application examples: Packages is a powerful tool that can be used in various applications for which other similar plugins will fail to manage. One example is assigning more than one service provider to a single client for a single booking. This can also be variable: Imagine a cleaning service which is preferably done with 3 workers, but you do not want to lose the client if you have only 2. Then you can configure sequence 1 for 3 workers and sequence 2 for 2 workers. WP BASE will show time slots free when there is only 2 workers available, but assign 3 if possible.', 'wp-base'),
			__('Another real life example: Sometimes dentists call another expert dentist for delicate operations. Packages addon can manage such an arrangement: Dentist A and his assistant starts a 2-hours operation and prepares the patient. After 30 minutes an expert dentist B joins and he stays for 1 hour. The last 30 minutes is completed by assistant alone. Dentist B is not full time working for A, but system knows his availability because he integrated his Google Calendar with WP BASE of Dentist A\'s website. Dentist B can also login to website and change his working hours as he wishes.', 'wp-base'),
			__('Services longer than 24 hours: You can combine jobs of the same service to create a package lasting more than 24 hours. For example, by adding 3 jobs of a 12 hourse service back to back you can have a package of 36 hours. However, time slots generated for such a package may be slightly different from a single 36 hours service, depending on the Booking View used.', 'wp-base'),
			__('Packages do not have their own working hours, holidays, capacity and padding settings.', 'wp-base'),
			__('While real duration of a package comes from its jobs and delays you can still set <b>duration</b> for a package which determines its availability per day. For the training course example setting 24 hours (all day) will make it available once a day. If you have morning and afternoon classes, you can set 12 hours.', 'wp-base'),
		);
	}

	/**
     * Waiting List descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function waiting_list_desc( ) {
		return array( 
			__('Waiting List allows you to define an additional capacity for your services for which clients can apply when desired time slot is fully booked.', 'wp-base'),
			__('To activate the waiting list feature for a particular service, check the "Enable" checkbox and enter a capacity greater than zero.', 'wp-base'),
			__('When client selects a time slot with waiting list and checkouts, it will be saved as a regular booking except its status being "waiting".', 'wp-base'),
			__('When there is an opening in the selected slot, clients in the waiting list will get a notification email.', 'wp-base'),
			__('If they choose to respond the link in this email and selected time slot is still free, a) If payment is not required their booking will be automatically confirmed, b) If payment is required then they will be required to complete the payment.', 'wp-base'),
			__('Waiting List can be used in conjunction with SMS addon. Then clients will get SMS notification.', 'wp-base'),
		);
	}

	/**
     * Woocommerce descriptions
	 * @since 3.0
 	 * @return array
     */
	public static function woocommerce_desc( ) {
		return array( 
			__('With <b>WooCommerce</b> Addon, you can sell your services as WooCommerce (WC) products with other WP BASE services or WC physical/digital products using cart and payment gateways provided by WC.', 'wp-base'),
			__('To do so, first set "Enable Integration" as Yes and create a product page by clicking the "Create a Booking Page" button. We call such a page, WC-BASE page.', 'wp-base'),
			__('That is it! Now your services will be regarded as WC products and your clients can add them to their shopping carts, making exactly the same selections as on a regular Make a Booking page', 'wp-base'),
			sprintf( __('You can have as many WC-BASE pages as you wish and then select them on %s to edit. For example you can change the shortcode to <code>[app_book service=1]</code> and on that page only Service 1 will be sold.', 'wp-base'), '<a href="'.admin_url('edit.php?post_type=product').'">'.__('Products Page','wp-base').'</a>'),
			__('You can have the shortcode either in "Product short description" field (the post excerpt, which is the default) or in "Product description" field (the post content), but NOT in both. Product description field should not be empty. If you have nothing as description, add a "non breaking space" character: <code>&amp;nbsp;</code>', 'wp-base'),
			__('You can delete a WC-BASE page without affecting existing bookings, except for those who are already in WC cart.', 'wp-base'),
			__('If a booking with "In Cart" status expire, client will see "session expired" message of WC and will not be allowed to proceed for payment.', 'wp-base'),
		);
	}

	/**
     * Shortcode descriptions
	 * @since 2.0
 	 * @return array
     */
	public static function shortcode_desc( ) {
		$desc= array(
			'app_book'			=> array(	
									'name'			=> 'Book',
									'description'	=>  __('Creates a complete booking interface with service and date/time selection and confirmation, login forms and previous/next buttons. This shortcode is by itself sufficent to create a functional booking page.', 'wp-base' ),
									'parameters'	=> array(
										'title'			=> __('Calendar or Table title for non-mobile devices. Placeholders "START_END","START", "END", "LOCATION", "WORKER", "SERVICE" will be automatically replaced by their real values. Enter 0 for no title. Default value depends on the view type.', 'wp-base' ),
										'mobile_title'	=> __('Calendar or Table title for mobile devices. See "title" for details.', 'wp-base' ),
										'location_title'=> __('Only with Locations Addon. Text above the location selection dropdown menu. Enter 0 for no title. Default: "Location"', 'wp-base' ),
										'service_title'	=> __('Text above the service selection dropdown menu. Enter 0 for no title. Default: "Service"', 'wp-base' ),
										'worker_title'	=> __('Only with Service Providers Addon. Text above the provider selection dropdown menu. Enter 0 for no title. Default: "Specialist"', 'wp-base' ),
										'location'		=> __('Only with Locations Addon. You can enter Location ID or name if you want location preselected and fixed. Note: Location name query is case insensitive. Default: "0" (Location is selected by dropdown).', 'wp-base' ),
										'service'		=> __('You can optionally enter service ID or name if you want service preselected and fixed. Note: Service name query is case insensitive. Default: "0" (Service is selected by dropdown).', 'wp-base' ),
										'category'		=> __('Only with Extended Service Features & Categories Addon. You can enter category ID if you want to limit services selectable from a particular category. Default: "0" (All services are selectable).', 'wp-base' ),
										'worker'		=> __('Only with Service Providers Addon. You can enter provider ID if you want service provider preselected and fixed. Default: "0" (Service provider is selected by dropdown).', 'wp-base' ),
										'order_by'		=> __('Defines in which order menu items (e.g services) are displayed. Possible values: ID, name, sort_order. Optionally DESC (descending) can be used, e.g. "name DESC" will reverse the order. Default: "sort_order" (The order you see on the admin side)', 'wp-base' ),
										'type'			=> __('Type of Booking View when client is connected with a non-mobile device: flex (with Advanced Features Addon only), table, weekly, monthly. "flex" selection uses Flex View, "table" selection uses Table View as basis. "weekly" and "monthly" use Weekly and Monthly calendar shortcodes respectively. Default: "monthly"', 'wp-base' ),
										'mobile_type'	=> __('Type of Booking View when client is connected with a mobile device: flex (with Advanced Features Addon only), table, weekly, monthly. "flex" selection uses Flex View, "table" selection uses Table View as basis. "weekly" and "monthly" use Weekly and Monthly calendar shortcodes respectively. Default: "table (if Advanced Features not activated), flex - Mode 6 (if Advanced Features activated)"', 'wp-base' ),
										'columns'		=> __('Only if type is "table". Columns to be displayed. The sequence of the columns in the parameter also defines order of the columns. Permitted values that should be separated by comma are: date, time, day, server_date_time, date_time, button (case insensitive). When Timezones addon is active, date_time value will display local time to user. To display server local time, use server_date_time. Default: "date,day,time,button" (short)', 'wp-base' ),
										'columns_mobile'=> __('Only if type is "table". Columns to be displayed when user is connected with a mobile device. The sequence of the columns in the parameter also defines order of the columns. For description see "columns" parameter. Default: "date_time,button"', 'wp-base' ),
										'mode'			=> sprintf( __('Only if type is "flex". Flex View supports different display modes from 1 to 6. Enter the desired mode number here. You can see examples in %s. Default: "1"', 'wp-base' ), '<a href="'.WPB_ADDON_DEMO_WEBSITE.'" _target="blank">'.__('demo website','wp-base').'</a>' ),
										'mobile_mode'	=> __('Only if type is "flex". Flex View mode when client is connected with a mobile device. For description see "mode" parameter. Default: "6"', 'wp-base' ),
										'range'			=> __('Date range of the time slots which will be displayed. For weekly and monthly calendars, only numeric part is taken into account. Permissible values: a numerical value, e.g. 2 (Number of weeks or months in Calendar View and fixed number of free time slots in Table View) or "n days", "n weeks", "n months",  where n is number of days/weeks/months (e.g 15 days, 2 weeks, month). Default: "2 months" (for Monthly Calendar), "2 weeks" (for the rest) ', 'wp-base' ),
										'from_week_start'=> __('Only if type is "flex". Set as "1" if Flex View will display from first day of the week, including past days in the week. Set "0" to start from current day. Default: "1"', 'wp-base' ),
										'start'			=> __('Normally time slots starts from current day. If you want to force it to start from a certain date, enter that date here. Most date formats are supported, but YYYY-MM-DD is recommended. Default: "0" (Current day) ', 'wp-base' ),
										'add'			=> __('Number of months (Monthly Calendar), weeks (Weekly Calendar) or days (Table View or Flex View) to add to the current date or selected date. Default: "0" (Current day) ', 'wp-base' ),
										'swipe'			=> __('Enable "swipe" functionality (sliding left or right) for mobile devices. Note: Automatically sets range attribute to "1 day". Default: "1" (Enabled)', 'wp-base' ),
										'select_date'	=> __('Displays a datepicker to jump to the selected date within allowed limits. Set 1 to enable, 0 to disable.', 'wp-base' ),
										'logged'		=> __('Subtitle text that will be displayed after the title only to the clients who are logged in or you don\'t require a login. Enter 0 for no subtitle. Default: ""', 'wp-base' ),
										'notlogged'		=> __('Subtitle text that will be displayed after the title only to the clients who are not logged in and you require a login. LOGIN_PAGE and REGISTRATION_PAGE placeholders will be replaced with your website\'s login and registration page url respectively. Enter 0 for no subtitle. Default: "You need to login to make an appointment. Click here to login: LOGIN_PAGE OR click here to register: REGISTRATION_PAGE"', 'wp-base' ),
									)),
			'app_book_flex'		=> array(	
									'name'			=> 'Book in Flex View',
									'description'	=>  __('Creates a booking layout where each day is a presented as a "flex box". Time slots are picked up by clickable buttons. Confirmation shortcode is required on the same page to make a complete booking page.', 'wp-base' ),
									'parameters'	=> array(
										'title'			=> __('Text that will be displayed as the title. Placeholders "START_END","START", "END", "LOCATION", "WORKER", "SERVICE" will be automatically replaced by their real values. Enter 0 for no title. Default: "Our schedule from START to END"', 'wp-base' ),
										'logged'		=> __('Subtitle text that will be displayed after the title only to the clients who are logged in or you don\'t require a login. Enter 0 for no subtitle. Default: "Click on a free box to apply for an appointment."', 'wp-base' ),
										'notlogged'		=> __('Subtitle text that will be displayed after the title only to the clients who are not logged in and you require a login. LOGIN_PAGE and REGISTRATION_PAGE placeholders will be replaced with your website\'s login and registration page url respectively. Enter 0 for no subtitle. Default: "You need to login to make an appointment. Click here to login: LOGIN_PAGE OR click here to register: REGISTRATION_PAGE"', 'wp-base' ),
										'location'		=> __('Only with Locations Addon. You can enter Location ID or name if you want location preselected and fixed. Note: Location name query is case insensitive. Default: "0" (Location is selected by dropdown).', 'wp-base' ),
										'service'		=> __('You can optionally enter service ID or name if you want service preselected and fixed. Note: Service name query is case insensitive. Default: "0" (Service is selected by dropdown).', 'wp-base' ),
										'worker'		=> __('Only with Service Providers Addon. You can enter provider ID if you want service provider preselected and fixed. Default: "0" (Service provider is selected by dropdown).', 'wp-base' ),
										'mode'			=> sprintf( __('Defines the method to be used to stack the blocks. Allowed values: 1,2,3,4,5 or 6. The first four are vertical, the last two are horizontal layouts. See our %s for examples. Default: "1"', 'wp-base' ), '<a href="'.WPB_ADDON_DEMO_WEBSITE.'" _target="blank">'.__('demo website','wp-base').'</a>' ),
										'range'			=> __('Date range of the blocks which will be displayed. Permissible values: "n days", "n weeks", "n months",  where n is number of days/weeks/months (e.g 15 days, 2 weeks, month). Please note that week and month selections do not necessarily mean 7, 14 or 30 days, but whatever left in the subsequent week or month. If you want to be precise, use "days". This value will be automatically limited by "Appointments Upper Limit" global setting. Default: "2 weeks" ', 'wp-base' ),
										'from_week_start'=> __('When set as 1, starts from starting day of the week, even if it has been a past day. Set 0 to start from current day. Default: 1', 'wp-base' ),
										'start'			=> __('Normally list starts from current day. If you want to force it to start from a certain date, enter that date here. Most date formats are supported, but YYYY-MM-DD is recommended. Note: This value will also affect other subsequent calendars on the same page. Default: "0" (Current day) ', 'wp-base' ),
										'add'			=> __('Number of days to add to the current date or selected date. Default: "0" (Current day) ', 'wp-base' ),
										'skip_empty_days'=> __('When set as 1, skips display of days without having a free time slot. Enter 0 to disable. Default: 0 (Empty days are displayed)', 'wp-base' ),
										'class'			=> __('A css class name for the wrapper. Default is empty.', 'wp-base' ),
										'swipe'		 	=> __('Set to 1 to enable, 0 to disable swipe functionality (sliding left or right) for mobile devices. Default: "1" (Enabled) ', 'wp-base' ),
									)),
			'app_book_table'	=> array(	
									'name'			=> 'Book in Table View',
									'description'	=>  __('Creates a table in which each row is a bookable time slot with clickable button on one column. Columns to be displayed and their order in the table can be selected. Confirmation shortcode is required on the same page to make a complete booking page.', 'wp-base' ),
									'parameters'	=> array(
										'title'			=> __('Text that will be displayed as the title. Placeholders "START_END","START", "END", "LOCATION", "WORKER", "SERVICE" will be automatically replaced by their real values. Enter 0 for no title. Default: "Our schedule from START to END"', 'wp-base' ),
										'logged'		=> __('Subtitle text that will be displayed after the title only to the clients who are logged in or you don\'t require a login. Enter 0 for no subtitle. Default: "Click on a free box to apply for an appointment."', 'wp-base' ),
										'notlogged'		=> __('Subtitle text that will be displayed after the title only to the clients who are not logged in and you require a login. LOGIN_PAGE and REGISTRATION_PAGE placeholders will be replaced with your website\'s login and registration page url respectively. Enter 0 for no subtitle. Default: "You need to login to make an appointment. Click here to login: LOGIN_PAGE OR click here to register: REGISTRATION_PAGE"', 'wp-base' ),
										'location'		=> __('Only with Locations Addon. You can enter Location ID or name if you want location preselected and fixed. Note: Location name query is case insensitive. Default: "0" (Location is selected by dropdown).', 'wp-base' ),
										'service'		=> __('You can optionally enter service ID or name if you want service preselected and fixed. Note: Service name query is case insensitive. Default: "0" (Service is selected by dropdown).', 'wp-base' ),
										'worker'		=> __('Only with Service Providers Addon. You can enter provider ID if you want service provider preselected and fixed. Default: "0" (Service provider is selected by dropdown).', 'wp-base' ),
										'columns'		=> __('Columns to be displayed. The sequence of the columns in the parameter also defines order of the columns. Permitted values that should be separated by comma are: date, time, day, server_date_time, date_time, button (case insensitive). When Timezones addon is active, date_time value will display local time to user. To display server local time, use server_date_time. Default: "date,day,time,button" (short)', 'wp-base' ),
										'columns_mobile'=> __('Columns to be displayed when user is connected with a mobile device. The sequence of the columns in the parameter also defines order of the columns. Permitted values that should be separated by comma are: date, time, day, date_time, button (case insensitive). Default: "date_time,button"', 'wp-base' ),
										'range'			=> __('Date range of the time slots which will be included in the list. Permissible values: "n days", "n weeks", "n months",  where n is number of days/weeks/months (e.g 15 days, 2 weeks, month), a numerical value, e.g. 10 (fixed number of free time slots, therefore time range is variable). Please note that week and month selections do not necessarily mean 7, 14 or 30 days, but whatever left in the subsequent week or month. If you want to be precise, use "days". This value will be automatically limited by "Appointments Upper Limit" global setting. Default: "10" (10 free time slots) ', 'wp-base' ),
										'start'			=> __('Normally list starts from the current day. If you want to force it to start from a certain date, enter that date here. Most date formats are supported, but YYYY-MM-DD is recommended. Note: This value will also affect other subsequent calendars on the same page. Default: "0" (Current day) ', 'wp-base' ),
										'complete_day'	=> __('If set to 1 and when range is selected as a fixed number, list will continue until day is completed. Please note that displayed number of slots will not be fixed any more. Default: "0" (Day will not be completed)', 'wp-base' ),
										'net_days'		=> __('If set to 1 and when range is selected as day, list will compansate for non working days and continue until required number of days displayed. Default: "0" (Day will not be completed)', 'wp-base' ),
										'id' 			=> __('Optional id attribute for the table. If you do not provide an id, plugin will automatically assign an id in "app_datatable_n" form where n is the order of occurrence of the table on the page. Default: "" (Id provided by WP BASE) ', 'wp-base' ),
										'class'			=> __('A css class name for the table. Default is empty.', 'wp-base' ),
										'swipe'		 	=> __('Set to 1 to enable, 0 to disable swipe functionality on mobile devices. Default: "1" (Enabled) ', 'wp-base' ),
									)),
			'app_book_now'		=> array(	
									'name'			=> 'Book Now Single Button',
									'description'	=>  __('Creates a button which can be used to book an appointment whose start and end times are fixed, e.g. a birthday party. Confirmation shortcode is required on the same page to make a complete booking page.', 'wp-base' ),
									'parameters'	=> array(
										'title'			=> __('Text that will be displayed above the button. Placeholders "START_END","START", "END", "LOCATION", "WORKER", "SERVICE" will be automatically replaced by their real values. Enter 0 for no title. Default: 0', 'wp-base' ),
										'logged'		=> __('Subtitle text that will be displayed after the title only to the clients who are logged in or you don\'t require a login. Enter 0 for no subtitle. Default: 0', 'wp-base' ),
										'notlogged'		=> __('Subtitle text that will be displayed after the title only to the clients who are not logged in and you require a login. LOGIN_PAGE and REGISTRATION_PAGE placeholders will be replaced with your website\'s login and registration page url respectively. Enter 0 for no subtitle. Default: "You need to login to make an appointment. Click here to login: LOGIN_PAGE OR click here to register: REGISTRATION_PAGE"', 'wp-base' ),
										'location'		=> __('Only with Locations Addon. You can enter Location ID or name if you want location preselected and fixed. Note: Location name query is case insensitive. Default: "0" (Location is selected by dropdown).', 'wp-base' ),
										'service'		=> __('(Required) Service ID or name. Note: Service name query is case insensitive.', 'wp-base' ),
										'worker'		=> __('Only with Service Providers Addon. You can enter provider ID if you want service provider preselected and fixed. Default: "0" (Service provider is selected by dropdown).', 'wp-base' ),
										'start'			=> __('(Required) Start date/time of the appointment. Most date formats are supported, but YYYY-MM-DD hh:mm:ss is recommended. Default: "0" (None) ', 'wp-base' ),
										'book_now'		=> __('Text that will be displayed on the button when booking is possible. "START", "LOCATION", "WORKER", "SERVICE" will be replaced by their real values. Default: "Book Now for SERVICE on START" ', 'wp-base' ),
										'booking_closed'=> __('Text that will be displayed on the button when booking is not possible, e.g. capacity full, service expired, etc. "START", "LOCATION", "WORKER", "SERVICE" will be replaced by their real values. Note: Admin can see the reason as a tooltip when mouse is over the button. Default: "Booking closed" ', 'wp-base' ),
									)),
			'app_confirmation'	=> array(	
									'name'			=> 'Confirmation',
									'description'	=>  __('Inserts a form which displays the details of the selected appointment and has fields which should be filled by the client. <b>If one of the modular booking method is used, this shortcode is required to complete an appointment.</b>', 'wp-base' ),
									'parameters'	=> array(
										'title'			=> __('Text above fields. Default: "Fill in the form below and confirm:"', 'wp-base' ),
										'button_text'	=> __('Text of the button that asks client to confirm the appointment. Default: "Checkout"', 'wp-base' ),
										'name'			=> __('name, email, phone, address, city, note, zip, country: Descriptive title of the fields. e.g. to ask for state instead of country, use <code>country="State"</code>. Note: This applies to the current shortcode/page. To make a global change, you can use "Custom Texts" instead.', 'wp-base' ),
										'email'			=> __('See name field.', 'wp-base' ),
										'phone'			=> __('See name field.', 'wp-base' ),
										'address'		=> __('See name field.', 'wp-base' ),
										'city'			=> __('See name field.', 'wp-base' ),
										'zip'			=> __('See name field.', 'wp-base' ),
										'country'		=> __('See name field.', 'wp-base' ),
										'note'			=> __('See name field.', 'wp-base' ),
										'fields'		=> __('Fields that will be displayed on the form. Permits filtering (e.g. when you have more than one appointment page with different confirmation user field requirements) and custom sorting display order of user info fields and UDF. Allowed fields (comma separated): name,email,phone,address,city,postcode,country and udf_n where n is the ID of the UDF. Fields will be displayed in the entry order. If left empty, default settings will be in effect. Default:"" (Default user field and UDF setting and order will be used)', 'wp-base' ),
										'use_cart'		=> __('Requires Shopping Cart Addon. Whether to enable shopping cart option for this page, i.e. if client can select more than one time slot and make a single checkout/payment. Possible values: 0 (Do not allow shopping cart), inherit (Follow global settings), 1 (Multiple appointments allowed regardless of global settings). Default: "inherit" (Global settings will be used)', 'wp-base' ),
										'continue_btn'	=> __('Only with Shopping Cart or WooCommerce. Enter 0 to disable "Add another time slot" button. Default: "1" (visible)', 'wp-base' ),
										'countdown'		=> __('Only with Shopping Cart or WooCommerce. Enter 1 to enable, 0 to disable remaining time countdown. When the countdown reaches zero, page is refreshed.', 'wp-base' ),
										'countdown_hidden'=> __('Only with Shopping Cart or WooCommerce. Enter 1 to hide remaining time countdown. Default: "0" (visible)', 'wp-base' ),
										'countdown_title'=> __('Only with Shopping Cart or WooCommerce. Title of remaining time countdown. Enter 0 to hide. Default: See custom text conf_countdown_title', 'wp-base' ),
									
									)),
			'app_login'			=> array(	
									'name'			=> 'Login',
									'description'	=>  __('Inserts front end login buttons for Facebook, Twitter and Wordpress. If login is not required or user is already logged in, this shortcode does nothing.', 'wp-base' ),
									'parameters'	=> array(
										'login_text'	=> __('Text above the login buttons, proceeded by a login link. Default: "Click here to login:"', 'wp-base' ),
										'redirect_text'	=> __('Javascript text if front end login is not set and user is redirected to login page. Default: "Login required to make an appointment. Now you will be redirected to login page."', 'wp-base' ),
									)),
			'app_monthly_schedule'	=> array(	
									'name'			=> 'Monthly Calendar',
									'description'	=>  __('Creates a monthly calendar plus time tables whose free time slots are clickable to apply for an appointment. Confirmation shortcode is required on the same page to make a complete booking page.', 'wp-base' ),
									'parameters'	=> array(
										'title'		=> __('Text that will be displayed as the schedule title. Enter 0 for no title. Placeholders START and END will be automatically replaced by their real values. Default: "Our schedule for START"', 'wp-base' ),
										'logged'	=> __('Text that will be displayed after the title only to the clients who are logged in or you don\'t require a login. Default: "Click on a free box to apply for an appointment."', 'wp-base' ),
										'notlogged'	=> __('Subtitle text that will be displayed after the title only to the clients who are not logged in and you require a login. LOGIN_PAGE and REGISTRATION_PAGE placeholders will be replaced with your website\'s login and registration page url respectively. Enter 0 for no subtitle. Default: "You need to login to make an appointment. Click here to login: LOGIN_PAGE OR click here to register: REGISTRATION_PAGE"', 'wp-base' ),
										'location'	=> __('Only with Locations Addon. You can enter Location ID or name if you want location preselected and fixed. Note: Location name query is case insensitive. Default: "0" (Location is selected by dropdown).', 'wp-base' ),
										'service'	=> __('You can enter service ID or name if you want service preselected and fixed. Note: Service name query is case insensitive. Default: "0" (Service is selected by dropdown).', 'wp-base' ),
										'worker'	=> __('Only with Service Providers Addon. You can enter provider ID if you want service provider preselected and fixed. Default: "0" (Service provider is selected by dropdown).', 'wp-base' ),
										'add'		=> __('Number of months to add to the schedule to use for preceding months\' schedules. Enter 1 for next month, 2 for the other month, so on. Default: "0" (Current month) ', 'wp-base' ),
										'start'		=> __('Normally calendar starts from the current month. If you want to force it to start from a certain month, enter a date inside that month here. Most date formats are supported, but YYYY-MM-DD is recommended. Notes: 1) This value will also affect other subsequent calendars on the same page. 2) It is sufficient to enter a date inside the month. 3) To make date selection variable see app_select_date shortcode. Default: "0" (Current month) ', 'wp-base' ),
										'long'		=> __('If entered 1, long week days are displayed on the calendar row, e.g. "Saturday" instead of "Sa". Default: "0"', 'wp-base' ),
										'class'		=> __('A css class name for the calendar. Default is empty. Tip: Apply "app_2column" for a side by side layout of 2 adjacent shortcodes.', 'wp-base' ),
									)),
			'app_next'			=> array(	
									'name'			=> 'Next/Previous',
									'description'	=>  __('Inserts previous/next week/month buttons, date selection field and Legend area. Note: There is no app_previous shortcode.', 'wp-base' ),
									'parameters'	=> array(
										'step'			=> __('Number of days, weeks or months that selected time will increase or decrease with each next or previous link click. For example you may consider entering 4 if you have 4 schedule tables on the page. Default: "1"', 'wp-base' ),
										'unit'			=> __('Unit of time that pagination will use. Permitted values: number, day, week, month. Default: "week"', 'wp-base' ),
										'date'			=> __('For description, please see "date" parameter of app_monthly_schedule shortcode. This is only required if this shortcode resides above any schedule shortcodes. Otherwise it will follow date settings of the schedule shortcodes. Default: "0" (Current week or month) ', 'wp-base' ),
										'select_date'	=> __('Displays a datepicker to jump to the selected date within allowed limits. Set 1 to enable, 0 to disable. Default: "1" (Enabled)', 'wp-base' ),
										'disable_legend'=> __('Disable legend area. Default: "0" (Legend is displayed) ', 'wp-base' ),
									)),
			'app_schedule'		=> array(	
									'name'			=> 'Weekly Calendar',
									'description'	=>  __('Creates a weekly calendar whose cells can be clicked to apply for an appointment. Since calendar uses a common time of day column for the complete week, it may not be suitable for irregular working hours, for example different starting hours for week days or break(s) shorter than service durations. Then use monthly calendar instead. Confirmation shortcode is required on the same page to make a complete booking page.', 'wp-base' ),
									'parameters'	=> array(
										'title'		=> __('Text that will be displayed as the schedule title. Placeholders START and END will be automatically replaced by their real values. Default: "Our schedule from START to END"', 'wp-base' ),
										'logged'	=> __('Text that will be displayed after the title only to the clients who are logged in or you don\'t require a login. Default: "Click on a free box to apply for an appointment."', 'wp-base' ),
										'notlogged'	=> __('Subtitle text that will be displayed after the title only to the clients who are not logged in and you require a login. LOGIN_PAGE and REGISTRATION_PAGE placeholders will be replaced with your website\'s login and registration page url respectively. Enter 0 for no subtitle. Default: "You need to login to make an appointment. Click here to login: LOGIN_PAGE OR click here to register: REGISTRATION_PAGE"', 'wp-base' ),
										'location'	=> __('Only with Locations Addon. You can enter Location ID or name if you want location preselected and fixed. Note: Location name query is case insensitive. Default: "0" (Location is selected by dropdown).', 'wp-base' ),
										'service'	=> __('You optionally enter service ID or name if you want service preselected and fixed. Note: Service name query is case insensitive. Default: "0" (Service is selected by dropdown).', 'wp-base' ),
										'worker'	=> __('Only with Service Providers Addon. You can enter provider ID if you want service provider preselected and fixed. Default: "0" (Service provider is selected by dropdown).', 'wp-base' ),
										'long'		=> __('If entered 1, long week days are displayed on the schedule table row, e.g. "Saturday" instead of "Sa". Default: "0" (short)', 'wp-base' ),
										'class'		=> __('A css class name for the calendar. Default is empty. Tip: Apply "app_2column" for a side by side layout of 2 adjacent shortcodes.', 'wp-base' ),
										'add'		=> __('Number of weeks to add to the schedule to use for preceding weeks\' schedules. Enter 1 for next week, 2 for the other week, so on. Default: "0" (Current week) ', 'wp-base' ),
										'start'		=> __('Normally calendar starts from the current week. If you want to force it to start from a certain week, enter a date inside that week here. Most date formats are supported, but YYYY-MM-DD is recommended. Notes: 1) This value will also affect other subsequent calendars on the same page. 2) Date value will NOT change starting day of week. As a result of this: i) It is not possible to force the calendar start from the selected date. It will always start from the weekday set in WordPress settings. ii) It is sufficient to enter a date inside the week. 3) To make date selection variable see app_select_date shortcode. Default: "0" (Current week) ', 'wp-base' ),
										'hide_rows'	=> __('Whether to hide a row to save space if all the cells of the row are unavailable. Select 1 to hide unavailable rows. Default:"0" (Unavailable rows are not hidden)', 'wp-base' ),
										'width'		=> __('Width of the calendar in percent relative to the page content width. Note: Since calendars are responsive (adapts themselves to the width of screen/page/wrapper width), changing the width of an external wrapper will also make the same effect. For an example see page content of a Make an Appointment page having side by side layout created by "Quick Start". Default:"100"', 'wp-base' ),
									)),
			'app_select_date'	=> array(	
									'name'			=> 'Select Date',
									'description'	=>  __('Creates an input field (powered with jQuery datepicker) using which starting point of the calendars on the same page will be changed. Start week day of the calendars will not change; calendars will fit themselves to cover the date selected here. This may be useful for displaying far future dates rather than clicking next month/week button of app_next shortcode. Note that this can be used as a separate shortcode or used in pagination area by setting select_date in pagination shortcode.', 'wp-base' ),
									'parameters'	=> array(
										'title'		=> __('Text above the select menu. Enter 0 for no title. Default: "Please select a date"', 'wp-base' ),
										'date'		=> __('Selected date during first load. Most date formats are supported, but YYYY-MM-DD is recommended. Default: "0" (current date, i.e. today)', 'wp-base' ),
									)),

			'app_services'		=> array(	
									'name'			=> 'Services',
									'description'	=>  __('Creates a dropdown menu of available services.', 'wp-base' ),
									'parameters'	=> array(
										'title'			=> __('Text above the select menu. Enter 0 for no title. Default: "Service"', 'wp-base' ),
										'location'		=> __('You may want to display services belonging to a certain location. Then enter location ID here. Default: "0"', 'wp-base' ),
										'category'		=> __('Only with Extended Service Features & Categories Addon. You can enter category ID if you want to limit services selectable from a particular category. Default: "0" (All services are selectable).', 'wp-base' ),
										'category_optgroup'	=> __('If set as 1 and there are categories defined, groups services under category names. If a service has more than one category, it will be displayed more than once, under each group. Default: "1" (Services are displayed with grouping)', 'wp-base' ),
										'worker'		=> __('In some cases, you may want to display services which are given only by a certain provider. In that case enter provider ID here. Note: order_by parameter will not work in combination with this one. Default: "0" (all defined services). Note: Multiple selections are not allowed.', 'wp-base' ),
										'order_by'		=> __('Defines in which order the services will be displayed. Possible values: ID, name, duration, price, sort_order. Optionally DESC (descending) can be used, e.g. "name DESC" will reverse the order. Note: When no service has yet selected, the (default) service to be displayed on a page is still determined by sort order. Default: "sort_order"', 'wp-base' ),
										'description'	=> __('Selects which part of the description page will be displayed in the tooltip when when mouse is over the name of a service. Selectable values are "none", "excerpt" (excerpt is created by WP BASE), "post_excerpt" (WP BASE uses Excerpt entered in post editor), "content". Default: "excerpt"', 'wp-base' ),
										'excerpt_length'=> __('Number of words that will be used from the content to create the excerpt. Only valid if description is selected as "excerpt". Default: "55"', 'wp-base' ),
										'thumb_size'	=> __('Inserts the post thumbnail if page has a featured image. Selectable values are "none", "thumbnail", "medium", "full" or a 2 numbers separated by comma representing width and height in pixels, e.g. 32,32. Default: "96,96"', 'wp-base' ),
										'thumb_class'	=> __('css class that will be applied to the thumbnail. Default: "alignleft"', 'wp-base' ),
										'class'			=> __('A css class name for the menu wrapper. Default is empty. Tip: Apply "app_2column" for a side by side layout of 2 adjacent shortcodes.', 'wp-base' ),
										// 'hide_if'		=> __('Hide the shortcode output if given condition is met. For details see "if" attribute of app_hide.', 'wp-base' ),
									)),
			'app_users'			=> array(	
									'name'			=> 'User selection',
									'description'	=>  __('Creates a dropdown menu of all users with which you can make a booking on behalf of any website member using the make an appointment page. Tip: To make a booking on behalf of a non member, choose "Not registered user" from the dropdown, select date/time of booking and then change user data on the confirmation form.', 'wp-base' ),
									'parameters'	=> array(
										'title'			=> __('Text above the select menu. Enter 0 for no title. Default: "Select a user"', 'wp-base' ),
										'class'			=> __('A css class name for the menu wrapper. Default is empty. Tip: Apply "app_2column" for a side by side layout of 2 adjacent shortcodes.', 'wp-base' ),
										'show_avatar'	=> __('Enter "0" to omit avatar display in the tooltip. Note: To display the avatar "Show avatar" should have been selected in Wordpress Settings > Discussion > Avatars. Default: "1" (avatar is displayed)', 'wp-base' ),
										'avatar_size'	=> __('Size of the avatar in pixels. Maximum is 512. Default: "96"', 'wp-base' ),
										'order_by'		=> __('Sort order of the users. Possible values: ID, login, email, nicename, display_name. Default: "display_name"', 'wp-base' ),
										'cap'			=> __('WordPress user capability. Users who have this capability can view and use the dropdown generated by the shortcode. Multiple capabilities separated by commas are allowed. When "none" is entered, everyone can view. Default: "manage_options"', 'wp-base' ),
									)),
			'app_countdown'		=> array(	
									'name'			=> 'Countdown',
									'description'	=>  __('Inserts a countdown dynamically displaying remaining time to next appointment for the logged in provider or client.', 'wp-base' ),
									'parameters'	=> array(
										'mode' 		=> __('refresh, cart, countdown. Default: "countdown"', 'wp-base' ),
										'id' 		=> __('Optional id attribute for the table. If you do not provide an id, plugin will automatically assign an id in "app_countdown_n" form where n is the order of occurrence of the table on the page. Default: "" (Id provided by WP BASE) ', 'wp-base' ),
										'class' 	=> __('Css class name of the inner wrapper. Default: "app_countdown" ', 'wp-base' ),
										'format'	=> __('Display format of the output, e.g. "dHMS", which is the default, will countdown using days (unless it is not zero), hours, minutes and seconds. Lowercase means, that time part will be showed if not zero. Uppercase means, that time part will always be displayed. As default, days will only be displayed when non zero, the rest will be shown even if they are zero.', 'wp-base' ),
										'status'	=> __('Which status(es) will be included. Possible values: all, paid, confirmed, pending, reserved or combinations of them separated with comma. Default: "paid,confirmed,pending"', 'wp-base' ),
										'silent'	=> __('If selected as 1, it is hidden. Default: 0', 'wp-base' ),
										'size'		=> __('Width of a digit in px only for flip counter. Supported sizes are 70, 82, 127, 254. Note that if the content width is not wide enough, digits may overlap. Default: 70.', 'wp-base' ),
										'minutes'	=> __('How many minutes to add to the countdown. It can take negative values. For example, if you have a "Doors opening time" of 2 hours before the booking, enter -120 (=>2 hours) here. Default: 0', 'wp-base' ),
										'expired'	=> __('Text to be displayed when countdown expires. Default: Started', 'wp-base' ),
									)),
									
			'app_hide'			=> array(	
									'name'			=> 'Hide Content',
									'description'	=>  __('Hides content if condition in "if" attribute is satisfied (true). Contrary to most of the shortcodes, this is an enclosing shortcode. Content should be "wrapped" like this: <code>[app_hide if="..."]Your content...[/app_hide]</code>. Multiple conditions, separated with comma, can be defined. Then ALL of the conditions should be met to hide the content.', 'wp-base' ),
									'parameters'	=> array(
										'if' 		=> __('Condition by which wrapped content will be hidden if result is true. Allowed values: 1) Expressions that give a boolean result. logged_in, not_loggedin, not_logged_in, not_admin, not_super_admin, cannot_$cap ($cap is capability. For example cannot_edit_post, cannot_manage_options, etc). Examples: if="not_logged_in", if="cannot_read" 2) A comparison in the following format: Variable Operator Integer. Variables: worker, provider, service, location, page, user. Operators: lt, le, gt, ge, eq, ne (less than, less than or equal to, greater than, greater than or equal to, equal, not equal - respectively) or !=, ==, =. Examples: if="worker==0", if="service gt 0", if="page ne 123".  Default: "1=2" (Content is not hidden) ', 'wp-base' ),
									)),
			'app_show'			=> array(	
									'name'			=> 'Show Content',
									'description'	=>  __('Shows content if condition in "if" attribute is satisfied (true). Contrary to most of the shortcodes, this is an enclosing shortcode. Content should be "wrapped" like this: <code>[app_show if="..."]Your content...[/app_show]</code>. This does the opposite of Hide content with one difference: If multiple conditions are used, if ANY condition is met, content will be displayed.', 'wp-base' ),
									'parameters'	=> array(
										'if' 		=> __('Condition by which wrapped content will be shown if result is true. For details see Hide Content.', 'wp-base' ),
									)),
			'app_is_mobile'		=> array(	
									'name'			=> 'is Mobile',
									'description'	=>  __('Content of this shortcode will be displayed, or in case of another shortcode, executed if viewer is connected with a mobile device. Contrary to most of the shortcodes, this is an enclosing shortcode. Content should be "wrapped" like this: <code>[app_is_mobile]Your content...[/app_is_mobile]</code>. This shortcode has no attributes to set.', 'wp-base' ),
									'parameters'	=> array(
									)),
			'app_is_not_mobile'	=> array(	
									'name'			=> 'is not Mobile',
									'description'	=>  __('Content of this shortcode will be displayed, or in case of another shortcode, executed if viewer is NOT connected with a mobile device. Contrary to most of the shortcodes, this is an enclosing shortcode. Content should be "wrapped" like this: <code>[app_is_not_mobile]Your content...[/app_is_not_mobile]</code>. This shortcode has no attributes to set.', 'wp-base' ),
									'parameters'	=> array(
									)),
			'app_list'		=> array(	
									'name'			=> 'List of Bookings',
									'description'	=>  __('Inserts a sortable table which displays bookings of all or current or selected user. This shortcode can also be used in emails.', 'wp-base' ),
									'parameters'	=> array(
										'title' 		=> __('Title text. Enter 0 for no title. Placeholder USER_NAME will be replaced by the name of user whose appointments are being displayed. Default: "All Bookings" (if "what" setting is "all") or "Bookings of USER_NAME" (in the rest of "what" setting)', 'wp-base' ),
										'columns'		=> __('Columns of the table. These are also the variables of the booking. The sequence of the columns in the parameter also defines display order of the columns. Permitted values that should be separated by comma are (case insensitive): id, created, location, location_address, service, worker, client, price, deposit, total_paid, deposit, balance, email, phone, city, address, note, date_time, date, day, time, status, cancel, edit, pdf, gcal, paypal, udf_n (where n is the ID of the UDF). Note: 1) One column from worker and client will be displayed depending on "what" parameter. 2) cancel, edit, pdf, gcal, paypal, udf columns are only visible if global settings allows and related Addon or gateway is activated. Default: "id, service, worker, client, date_time, status, cancel, edit, pdf, gcal"', 'wp-base' ),
										'columns_mobile'=> __('Columns of the table when the user is connected with a mobile device. For description see "columns" parameter.', 'wp-base' ),
										'what' 			=> __('What to be displayed in the list. Permitted values: client (shows bookings of the client taking the services), provider (shows bookings of the service provider giving the services), all (bookings belonging to all users and providers in the system filtered with user_id, service, status, start, end attributes) Default: "client"', 'wp-base' ),
										'user_id' 		=> __('Enter the ID of the user whose list will be displayed to the admin. If omitted, a) If $_GET["app_user_id"] is set in the url, that user will be selected, b) Otherwise appointments of current user will be displayed. A non-admin user can only view own bookings. If used in an email, it will always display the results for the client who is receiving the email. Default: "0" (current user)', 'wp-base' ),
										'service' 		=> __('An optional comma delimited list of service IDs to be included. If you want to filter the list for particular services, you can use this parameter. Default: "" (All services are included)', 'wp-base' ),
										'status'  		=> __('Which status(es) will be included. Possible values: all, paid, confirmed, completed, pending, removed, reserved, running (In Progress) or combinations of them separated with comma. Default: "paid,confirmed,pending,running"', 'wp-base' ),
										'order_by' 		=> __('Sort order of the appointments on the first page load. Possible values: ID, start, end. Optionally DESC (descending) can be used, e.g. "start DESC" will reverse the order. Default: "ID DESC". Note: This is the sort order as page loads. Table can be dynamically sorted by any column at the front end.', 'wp-base' ),
										'limit'  		=> __('Limit of characters of client name. If number of characters of client name is greater than this number plus 3, then client name will be abbreviated with abbr tag. Full name will be displayed in its tooltip. Default: "22"', 'wp-base' ),
										'start'  		=> __('Minimum start date of the appointments to be displayed. Most date formats are supported, but YYYY-MM-DD is recommended. Default: "0" (No start limit) ', 'wp-base' ),
										'end' 			=> __('Maximum end date of the appointments to be displayed. Most date formats are supported, but YYYY-MM-DD is recommended. Default: "0" (No end limit) ', 'wp-base' ),
										'edit_button' 	=> __('Requires Front End Edit Addon. Text for edit button. Default: "Edit"', 'wp-base' ),
										'cancel_button' => __('Text for cancel button. Default: "Cancel"', 'wp-base' ),
										'no_table' 		=> __('Enter 0 to generate a table even if there are no appointments in the list. Default: "1" (A table will not be generated if there are no bookings)', 'wp-base' ),
										'id' 			=> __('Optional id attribute for the table. If you do not provide an id, plugin will automatically assign an id in "app_datatable_n" form where n is the order of occurrence of the table on the page. Default: "" (Id is provided by WP BASE) ', 'wp-base' ),
										'cap'			=> __('WordPress user capability. Users who have this capability can view and use other users\' bookings. Multiple capabilities separated by commas are allowed. Warning: When "none" is entered, everyone can view. Default: "manage_options"', 'wp-base' ),
										'override'		=> __('Whether to override cancel and edit capability of admin for other users\' bookings. Possible values: 0 (Do not allow cancel and edit), inherit: Follow global settings, 1: override (Edit and cancel allowed for admin regardless of global settings). Default: "inherit" (Global settings will be used)', 'wp-base' ),

									)),
			'app_no_html'		=> array(	
									'name'			=> 'No HTML',
									'description'	=>  __('Clear the contents which are "wrapped": <code>[app_no_html]Your content...[/app_no_html]</code>. This is useful for custom templates and "theme builders" since it makes WP BASE core to load required javascript and css files while not generating any output. For details and usage example see /sample/sample-appointments-page.php file. This shortcode has no attributes to set.', 'wp-base' ),
									'parameters'	=> array(
									)),
			'app_theme'			=> array(	
									'name'			=> 'Theme Selector',
									'description'	=>  __('Inserts a dropdown menu of selectable themes, so that you, as admin, can change and see different theme results on the front end. Note: This selection is temporary and valid only on the current browser for the current session and user. It does not change the setting for selected theme.', 'wp-base' ),
									'parameters'	=> array(
										'title'		=> __('Text above the menu. Enter 0 for no title. Default: "jQuery UI Theme"', 'wp-base' ),
										'cap'		=> __('WordPress user capability. Users who have this capability can view the theme selection pulldown menu. Multiple capabilities separated by commas are allowed. When "none" is entered, everyone can view. Default: "manage_options"', 'wp-base' ),
									)),
																				
		);
		
		if ( !class_exists( 'WpBPro' ) )
			$desc = array_intersect_key( $desc, array_flip( array( 'app_book', 'app_list', 'app_theme', 'app_is_mobile', 'app_is_not_mobile' ) ) );

		return apply_filters( 'app_shortcode_desc', $desc );
	}
	
	public static function get_default_caps(){
		return array(
			'manage_bookings',
			'delete_bookings',
			'manage_own_bookings',
			'manage_schedules',
			'manage_transactions',
			'manage_monetary_settings',
			'manage_display_settings',
			'manage_locations',
			'delete_locations',
			'manage_services',
			'delete_services',
			'manage_own_services',
			'manage_workers',
			'delete_workers',
			'manage_working_hours',
			'manage_own_work_hours',
			'manage_extras',
			'delete_extras',
			'manage_global_settings',
			'manage_addons',
			'manage_licenses',
			'manage_tools',
		);
	}


	public static function countries( ) {
		return apply_filters( 'app_countries', array(
		  "AF" => "Afghanistan",
		  "AL" => "Albania",
		  "DZ" => "Algeria",
		  "AS" => "American Samoa",
		  "AD" => "Andorra",
		  "AO" => "Angola",
		  "AI" => "Anguilla",
		  "AQ" => "Antarctica",
		  "AG" => "Antigua And Barbuda",
		  "AR" => "Argentina",
		  "AM" => "Armenia",
		  "AW" => "Aruba",
		  "AU" => "Australia",
		  "AT" => "Austria",
		  "AZ" => "Azerbaijan",
		  "BS" => "Bahamas",
		  "BH" => "Bahrain",
		  "BD" => "Bangladesh",
		  "BB" => "Barbados",
		  "BY" => "Belarus",
		  "BE" => "Belgium",
		  "BZ" => "Belize",
		  "BJ" => "Benin",
		  "BM" => "Bermuda",
		  "BT" => "Bhutan",
		  "BO" => "Bolivia",
		  "BA" => "Bosnia And Herzegowina",
		  "BW" => "Botswana",
		  "BV" => "Bouvet Island",
		  "BR" => "Brazil",
		  "IO" => "British Indian Ocean Territory",
		  "BN" => "Brunei Darussalam",
		  "BG" => "Bulgaria",
		  "BF" => "Burkina Faso",
		  "BI" => "Burundi",
		  "KH" => "Cambodia",
		  "CM" => "Cameroon",
		  "CA" => "Canada",
		  "CV" => "Cape Verde",
		  "KY" => "Cayman Islands",
		  "CF" => "Central African Republic",
		  "TD" => "Chad",
		  "CL" => "Chile",
		  "CN" => "China",
		  "CX" => "Christmas Island",
		  "CC" => "Cocos (Keeling) Islands",
		  "CO" => "Colombia",
		  "KM" => "Comoros",
		  "CG" => "Congo",
		  "CD" => "Congo, The Democratic Republic Of The",
		  "CK" => "Cook Islands",
		  "CR" => "Costa Rica",
		  "CI" => "Cote D‌’Ivoire",
		  "HR" => "Croatia (Local Name: Hrvatska)",
		  "CU" => "Cuba",
		  "CY" => "Cyprus",
		  "CZ" => "Czech Republic",
		  "DK" => "Denmark",
		  "DJ" => "Djibouti",
		  "DM" => "Dominica",
		  "DO" => "Dominican Republic",
		  "TP" => "East Timor",
		  "EC" => "Ecuador",
		  "EG" => "Egypt",
		  "SV" => "El Salvador",
		  "GQ" => "Equatorial Guinea",
		  "ER" => "Eritrea",
		  "EE" => "Estonia",
		  "ET" => "Ethiopia",
		  "FK" => "Falkland Islands (Malvinas)",
		  "FO" => "Faroe Islands",
		  "FJ" => "Fiji",
		  "FI" => "Finland",
		  "FR" => "France",
		  "FX" => "France, Metropolitan",
		  "GF" => "French Guiana",
		  "PF" => "French Polynesia",
		  "TF" => "French Southern Territories",
		  "GA" => "Gabon",
		  "GM" => "Gambia",
		  "GE" => "Georgia",
		  "DE" => "Germany",
		  "GH" => "Ghana",
		  "GI" => "Gibraltar",
		  "GR" => "Greece",
		  "GL" => "Greenland",
		  "GD" => "Grenada",
		  "GP" => "Guadeloupe",
		  "GU" => "Guam",
		  "GT" => "Guatemala",
		  "GN" => "Guinea",
		  "GW" => "Guinea-Bissau",
		  "GY" => "Guyana",
		  "HT" => "Haiti",
		  "HM" => "Heard And Mc Donald Islands",
		  "VA" => "Holy See (Vatican City State)",
		  "HN" => "Honduras",
		  "HK" => "Hong Kong",
		  "HU" => "Hungary",
		  "IS" => "Iceland",
		  "IN" => "India",
		  "ID" => "Indonesia",
		  "IR" => "Iran (Islamic Republic Of)",
		  "IQ" => "Iraq",
		  "IE" => "Ireland",
		  "IL" => "Israel",
		  "IT" => "Italy",
		  "JM" => "Jamaica",
		  "JP" => "Japan",
		  "JO" => "Jordan",
		  "KZ" => "Kazakhstan",
		  "KE" => "Kenya",
		  "KI" => "Kiribati",
		  "KP" => "Korea, Democratic People‌’s Republic Of",
		  "KR" => "Korea, Republic Of",
		  "KW" => "Kuwait",
		  "KG" => "Kyrgyzstan",
		  "LA" => "Lao People‌’s Democratic Republic",
		  "LV" => "Latvia",
		  "LB" => "Lebanon",
		  "LS" => "Lesotho",
		  "LR" => "Liberia",
		  "LY" => "Libyan Arab Jamahiriya",
		  "LI" => "Liechtenstein",
		  "LT" => "Lithuania",
		  "LU" => "Luxembourg",
		  "MO" => "Macau",
		  "MK" => "Macedonia, Former Yugoslav Republic Of",
		  "MG" => "Madagascar",
		  "MW" => "Malawi",
		  "MY" => "Malaysia",
		  "MV" => "Maldives",
		  "ML" => "Mali",
		  "MT" => "Malta",
		  "MH" => "Marshall Islands",
		  "MQ" => "Martinique",
		  "MR" => "Mauritania",
		  "MU" => "Mauritius",
		  "YT" => "Mayotte",
		  "MX" => "Mexico",
		  "FM" => "Micronesia, Federated States Of",
		  "MD" => "Moldova, Republic Of",
		  "MC" => "Monaco",
		  "MN" => "Mongolia",
		  "MS" => "Montserrat",
		  "MA" => "Morocco",
		  "MZ" => "Mozambique",
		  "MM" => "Myanmar",
		  "NA" => "Namibia",
		  "NR" => "Nauru",
		  "NP" => "Nepal",
		  "NL" => "Netherlands",
		  "AN" => "Netherlands Antilles",
		  "NC" => "New Caledonia",
		  "NZ" => "New Zealand",
		  "NI" => "Nicaragua",
		  "NE" => "Niger",
		  "NG" => "Nigeria",
		  "NU" => "Niue",
		  "NF" => "Norfolk Island",
		  "MP" => "Northern Mariana Islands",
		  "NO" => "Norway",
		  "OM" => "Oman",
		  "PK" => "Pakistan",
		  "PW" => "Palau",
		  "PA" => "Panama",
		  "PG" => "Papua New Guinea",
		  "PY" => "Paraguay",
		  "PE" => "Peru",
		  "PH" => "Philippines",
		  "PN" => "Pitcairn",
		  "PL" => "Poland",
		  "PT" => "Portugal",
		  "PR" => "Puerto Rico",
		  "QA" => "Qatar",
		  "RE" => "Reunion",
		  "RO" => "Romania",
		  "RU" => "Russian Federation",
		  "RW" => "Rwanda",
		  "KN" => "Saint Kitts And Nevis",
		  "LC" => "Saint Lucia",
		  "VC" => "Saint Vincent And The Grenadines",
		  "WS" => "Samoa",
		  "SM" => "San Marino",
		  "ST" => "Sao Tome And Principe",
		  "SA" => "Saudi Arabia",
		  "SN" => "Senegal",
		  "SC" => "Seychelles",
		  "SL" => "Sierra Leone",
		  "SG" => "Singapore",
		  "SK" => "Slovakia (Slovak Republic)",
		  "SI" => "Slovenia",
		  "SB" => "Solomon Islands",
		  "SO" => "Somalia",
		  "ZA" => "South Africa",
		  "GS" => "South Georgia, South Sandwich Islands",
		  "ES" => "Spain",
		  "LK" => "Sri Lanka",
		  "SH" => "St. Helena",
		  "PM" => "St. Pierre And Miquelon",
		  "SD" => "Sudan",
		  "SR" => "Suriname",
		  "SJ" => "Svalbard And Jan Mayen Islands",
		  "SZ" => "Swaziland",
		  "SE" => "Sweden",
		  "CH" => "Switzerland",
		  "SY" => "Syrian Arab Republic",
		  "TW" => "Taiwan",
		  "TJ" => "Tajikistan",
		  "TZ" => "Tanzania, United Republic Of",
		  "TH" => "Thailand",
		  "TG" => "Togo",
		  "TK" => "Tokelau",
		  "TO" => "Tonga",
		  "TT" => "Trinidad And Tobago",
		  "TN" => "Tunisia",
		  "TR" => "Turkey",
		  "TM" => "Turkmenistan",
		  "TC" => "Turks And Caicos Islands",
		  "TV" => "Tuvalu",
		  "UG" => "Uganda",
		  "UA" => "Ukraine",
		  "AE" => "United Arab Emirates",
		  "GB" => "United Kingdom",
		  "US" => "United States",
		  "UM" => "United States Minor Outlying Islands",
		  "UY" => "Uruguay",
		  "UZ" => "Uzbekistan",
		  "VU" => "Vanuatu",
		  "VE" => "Venezuela",
		  "VN" => "Viet Nam",
		  "VG" => "Virgin Islands (British)",
		  "VI" => "Virgin Islands (U.S.)",
		  "WF" => "Wallis And Futuna Islands",
		  "EH" => "Western Sahara",
		  "YE" => "Yemen",
		  "YU" => "Yugoslavia",
		  "ZM" => "Zambia",
		  "ZW" => "Zimbabwe"
		) );
	}
	
	//Currency list - http://www.xe.com/symbols.php
	//Middle parameter is symbol which is hex: http://www.mikezilla.com/exp0012.html
	public static function currencies() {
		return apply_filters( 'app_currencies', array(
			"ALL"=> array("Albania, Leke", "4c, 65, 6b"),
			"AFN"=> array("Afghanistan, Afghanis", "60b"),
			"ARS"=> array("Argentina, Pesos", "24"),
			"AWG"=> array("Aruba, Guilders (Florins)", "192"),
			"AUD"=> array("Australia, Dollars", "24"),
			"AZN"=> array("Azerbaijan, New Manats", "43c, 430, 43d"),
			"BSD"=> array("Bahamas, Dollars", "24"),
			"BHD"=> array("Bahrain, Dinars", "2E, 62F, 2E, 628" ),
			"BD"=> array("Bahrain, Dinars", "42, 44" ),
			"BBD"=> array("Barbados, Dollars", "24"),
			"BYR"=> array("Belarus, Rubles", "70, 2e"),
			"BZD"=> array("Belize, Dollars", "42, 5a, 24"),
			"BMD"=> array("Bermuda, Dollars", "24"),
			"BOB"=> array("Bolivia, Bolivianos", "24, 62"),
			"BAM"=> array("Bosnia and Herzegovina, C. Marka", "4b, 4d"),
			"BWP"=> array("Botswana, Pulas", "50"),
			"BGN"=> array("Bulgaria, Leva", "43b, 432"),
			"BRL"=> array("Brazil, Reais", "52, 24"),
			"BND"=> array("Brunei Darussalam, Dollars", "24"),
			"KHR"=> array("Cambodia, Riels", "17db"),
			"CAD"=> array("Canada, Dollars", "24"),
			"KYD"=> array("Cayman Islands, Dollars", "24"),
			"CLP"=> array("Chile, Pesos", "24"),
			"CNY"=> array("China, Yuan Renminbi", "a5"),
			"COP"=> array("Colombia, Pesos", "24"),
			"CRC"=> array("Costa Rica, Colon", "20a1"),
			"HRK"=> array("Croatia, Kuna", "6b, 6e"),
			"CUP"=> array("Cuba, Pesos", "20b1"),
			"CZK"=> array("Czech Republic, Koruny", "4b, 10d"),
			"DKK"=> array("Denmark, Kroner", "6b, 72"),
			"DOP"=> array("Dominican Republic, Pesos", "52, 44, 24"),
			"XCD"=> array("East Caribbean, Dollars", "24"),
			"EGP"=> array("Egypt, Pounds", "45, 47, 50"),
			"SVC"=> array("El Salvador, Colones", "24"),
			"EEK"=> array("Estonia, Krooni", "6b, 72"),
			"EUR"=> array("Euro", "20ac"),
			"FKP"=> array("Falkland Islands, Pounds", "a3"),
			"FJD"=> array("Fiji, Dollars", "24"),
			"GEL"=> array("Georgia, lari", "6c, 61, 72, 69"),
			"GHC"=> array("Ghana, Cedis", "a2"),
			"GIP"=> array("Gibraltar, Pounds", "a3"),
			"GTQ"=> array("Guatemala, Quetzales", "51"),
			"GGP"=> array("Guernsey, Pounds", "a3"),
			"GYD"=> array("Guyana, Dollars", "24"),
			"HNL"=> array("Honduras, Lempiras", "4c"),
			"HKD"=> array("Hong Kong, Dollars", "24"),
			"HUF"=> array("Hungary, Forint", "46, 74"),
			"ISK"=> array("Iceland, Kronur", "6b, 72"),
			"INR"=> array("India, Rupees", "20a8"),
			"IDR"=> array("Indonesia, Rupiahs", "52, 70"),
			"IRR"=> array("Iran, Rials", "fdfc"),
			"IMP"=> array("Isle of Man, Pounds", "a3"),
			"ILS"=> array("Israel, New Shekels", "20aa"),
			"JMD"=> array("Jamaica, Dollars", "4a, 24"),
			"JPY"=> array("Japan, Yen", "a5"),
			"JEP"=> array("Jersey, Pounds", "a3"),
			"KZT"=> array("Kazakhstan, Tenge", "43b, 432"),
			"KES"=> array("Kenyan Shilling", "4B, 73, 68, 73"),
			"KWD"=> array("Kuwait, dinar", "4B, 57, 44"),
			"KGS"=> array("Kyrgyzstan, Soms", "43b, 432"),
			"LAK"=> array("Laos, Kips", "20ad"),
			"LVL"=> array("Latvia, Lati", "4c, 73"),
			"LBP"=> array("Lebanon, Pounds", "a3"),
			"LRD"=> array("Liberia, Dollars", "24"),
			"LTL"=> array("Lithuania, Litai", "4c, 74"),
			"MKD"=> array("Macedonia, Denars", "434, 435, 43d"),
			"MYR"=> array("Malaysia, Ringgits", "52, 4d"),
			"MUR"=> array("Mauritius, Rupees", "20a8"),
			"MXN"=> array("Mexico, Pesos", "24"),
			"MNT"=> array("Mongolia, Tugriks", "20ae"),
			"MAD"=> array("Morocco, dirhams", "64, 68"),
			"MZN"=> array("Mozambique, Meticais", "4d, 54"),
			"NAD"=> array("Namibia, Dollars", "24"),
			"NPR"=> array("Nepal, Rupees", "20a8"),
			"ANG"=> array("Netherlands Antilles, Guilders", "192"),
			"NZD"=> array("New Zealand, Dollars", "24"),
			"NIO"=> array("Nicaragua, Cordobas", "43, 24"),
			"NGN"=> array("Nigeria, Nairas", "20a6"),
			"KPW"=> array("North Korea, Won", "20a9"),
			"NOK"=> array("Norway, Krone", "6b, 72"),
			"OMR"=> array("Oman, Rials", "fdfc"),
			"PKR"=> array("Pakistan, Rupees", "20a8"),
			"PAB"=> array("Panama, Balboa", "42, 2f, 2e"),
			"PYG"=> array("Paraguay, Guarani", "47, 73"),
			"PEN"=> array("Peru, Nuevos Soles", "53, 2f, 2e"),
			"PHP"=> array("Philippines, Pesos", "50, 68, 70"),
			"PLN"=> array("Poland, Zlotych", "7a, 142"),
			"QAR"=> array("Qatar, Rials", "fdfc"),
			"RON"=> array("Romania, New Lei", "6c, 65, 69"),
			"RUB"=> array("Russia, Rubles", "440, 443, 431"),
			"SHP"=> array("Saint Helena, Pounds", "a3"),
			"SAR"=> array("Saudi Arabia, Riyals", "fdfc"),
			"RSD"=> array("Serbia, Dinars", "414, 438, 43d, 2e"),
			"SCR"=> array("Seychelles, Rupees", "20a8"),
			"SGD"=> array("Singapore, Dollars", "24"),
			"SBD"=> array("Solomon Islands, Dollars", "24"),
			"SOS"=> array("Somalia, Shillings", "53"),
			"ZAR"=> array("South Africa, Rand", "52"),
			"KRW"=> array("South Korea, Won", "20a9"),
			"LKR"=> array("Sri Lanka, Rupees", "20a8"),
			"SEK"=> array("Sweden, Kronor", "6b, 72"),
			"CHF"=> array("Switzerland, Francs", "43, 48, 46"),
			"SRD"=> array("Suriname, Dollars", "24"),
			"SYP"=> array("Syria, Pounds", "a3"),
			"TWD"=> array("Taiwan, New Dollars", "4e, 54, 24"),
			"THB"=> array("Thailand, Baht", "e3f"),
			"TTD"=> array("Trinidad and Tobago, Dollars", "54, 54, 24"),
			"TRY"=> array("Turkey, Lira", "54, 4c"),
			"TRL"=> array("Turkey, Liras", "20a4"),
			"TVD"=> array("Tuvalu, Dollars", "24"),
			"UAH"=> array("Ukraine, Hryvnia", "20b4"),
			"AED"=> array("United Arab Emirates, dirhams", "64, 68"),
			"GBP"=> array("United Kingdom, Pounds", "a3"),
			"USD"=> array("United States of America, Dollars", "24"),
			"UYU"=> array("Uruguay, Pesos", "24, 55"),
			"UZS"=> array("Uzbekistan, Sums", "43b, 432"),
			"VEF"=> array("Venezuela, Bolivares Fuertes", "42, 73"),
			"VND"=> array("Vietnam, Dong", "20ab"),
			"XAF"=> array("BEAC, CFA Francs", "46, 43, 46, 41"),
			"XOF"=> array("BCEAO, CFA Francs", "46, 43, 46, 41"),
			"YER"=> array("Yemen, Rials", "fdfc"),
			"ZMW"=> array("Zambia, Kwachas", "5a, 4b" ),
			"ZWD"=> array("Zimbabwe, Zimbabwe Dollars", "5a, 24"),
			"POINTS"=> array("Points", "50, 6f, 69, 6e, 74, 73"),
			"CREDITS"=> array("Credits", "43, 72, 65, 64, 69, 74, 73")
			)
		);
	}
	
	public static function phone_codes() {
		return apply_filters( 'app_phone_codes', array
			(
			93 => "Afghanistan",
			355 => "Albania",
			213 => "Algeria",
			1 => "American Samoa",
			376 => "Andorra",
			244 => "Angola",
			1 => "Anguilla",
			1 => "Antigua and Barbuda",
			54 => "Argentina",
			374 => "Armenia",
			297 => "Aruba",
			247 => "Ascension",
			61 => "Australia",
			43 => "Austria",
			994 => "Azerbaijan",
			1 => "Bahamas",
			973 => "Bahrain",
			880 => "Bangladesh",
			1 => "Barbados",
			375 => "Belarus",
			32 => "Belgium",
			501 => "Belize",
			229 => "Benin",
			1 => "Bermuda",
			975 => "Bhutan",
			591 => "Bolivia",
			387 => "Bosnia and Herzegovina",
			267 => "Botswana",
			55 => "Brazil",
			1 => "British Virgin Islands",
			673 => "Brunei",
			359 => "Bulgaria",
			226 => "Burkina Faso",
			257 => "Burundi",
			855 => "Cambodia",
			237 => "Cameroon",
			1 => "Canada",
			238 => "Cape Verde",
			1 => "Cayman Islands",
			236 => "Central African Republic",
			235 => "Chad",
			56 => "Chile",
			86 => "China",
			57 => "Colombia",
			269 => "Comoros",
			242 => "Congo",
			682 => "Cook Islands",
			506 => "Costa Rica",
			385 => "Croatia",
			53 => "Cuba",
			357 => "Cyprus",
			420 => "Czech Republic",
			243 => "Democratic Republic of Congo",
			45 => "Denmark",
			246 => "Diego Garcia",
			253 => "Djibouti",
			1 => "Dominica",
			1 => "Dominican Republic",
			670 => "East Timor",
			593 => "Ecuador",
			20 => "Egypt",
			503 => "El Salvador",
			240 => "Equatorial Guinea",
			291 => "Eritrea",
			372 => "Estonia",
			251 => "Ethiopia",
			500 => "Falkland (Malvinas) Islands",
			298 => "Faroe Islands",
			679 => "Fiji",
			358 => "Finland",
			33 => "France",
			594 => "French Guiana",
			689 => "French Polynesia",
			241 => "Gabon",
			220 => "Gambia",
			995 => "Georgia",
			49 => "Germany",
			233 => "Ghana",
			350 => "Gibraltar",
			30 => "Greece",
			299 => "Greenland",
			1 => "Grenada",
			590 => "Guadeloupe",
			1 => "Guam",
			502 => "Guatemala",
			224 => "Guinea",
			245 => "Guinea-Bissau",
			592 => "Guyana",
			509 => "Haiti",
			504 => "Honduras",
			852 => "Hong Kong",
			36 => "Hungary",
			354 => "Iceland",
			91 => "India",
			62 => "Indonesia",
			870  => "Inmarsat Satellite",
			98 => "Iran",
			964 => "Iraq",
			353 => "Ireland",
			972 => "Israel",
			39 => "Italy",
			225 => "Ivory Coast",
			1 => "Jamaica",
			81 => "Japan",
			962 => "Jordan",
			7 => "Kazakhstan",
			254 => "Kenya",
			686 => "Kiribati",
			965 => "Kuwait",
			996 => "Kyrgyzstan",
			856 => "Laos",
			371 => "Latvia",
			961 => "Lebanon",
			266 => "Lesotho",
			231 => "Liberia",
			218 => "Libya",
			423 => "Liechtenstein",
			370 => "Lithuania",
			352 => "Luxembourg",
			853 => "Macau",
			389 => "Macedonia",
			261 => "Madagascar",
			265 => "Malawi",
			60 => "Malaysia",
			960 => "Maldives",
			223 => "Mali",
			356 => "Malta",
			692 => "Marshall Islands",
			596 => "Martinique",
			222 => "Mauritania",
			230 => "Mauritius",
			262 => "Mayotte",
			52 => "Mexico",
			691 => "Micronesia",
			373 => "Moldova",
			377 => "Monaco",
			976 => "Mongolia",
			382 => "Montenegro",
			1 => "Montserrat",
			212 => "Morocco",
			258 => "Mozambique",
			95 => "Myanmar",
			264 => "Namibia",
			674 => "Nauru",
			977 => "Nepal",
			31 => "Netherlands",
			599 => "Netherlands Antilles",
			687 => "New Caledonia",
			64 => "New Zealand",
			505 => "Nicaragua",
			227 => "Niger",
			234 => "Nigeria",
			683 => "Niue Island",
			850 => "North Korea",
			1 => "Northern Marianas",
			47 => "Norway",
			968 => "Oman",
			92 => "Pakistan",
			680 => "Palau",
			507 => "Panama",
			675 => "Papua New Guinea",
			595 => "Paraguay",
			51 => "Peru",
			63 => "Philippines",
			48 => "Poland",
			351 => "Portugal",
			1 => "Puerto Rico",
			974 => "Qatar",
			262 => "Reunion",
			40 => "Romania",
			7 => "Russian Federation",
			250 => "Rwanda",
			290 => "Saint Helena",
			1 => "Saint Kitts and Nevis",
			1 => "Saint Lucia",
			508 => "Saint Pierre and Miquelon",
			1 => "Saint Vincent and the Grenadines",
			685 => "Samoa",
			378 => "San Marino",
			239 => "Sao Tome and Principe",
			966 => "Saudi Arabia",
			221 => "Senegal",
			381 => "Serbia",
			248 => "Seychelles",
			232 => "Sierra Leone",
			65 => "Singapore",
			421 => "Slovakia",
			386 => "Slovenia",
			677 => "Solomon Islands",
			252 => "Somalia",
			27 => "South Africa",
			82 => "South Korea",
			34 => "Spain",
			94 => "Sri Lanka",
			249 => "Sudan",
			597 => "Suriname",
			268 => "Swaziland",
			46 => "Sweden",
			41 => "Switzerland",
			963 => "Syria",
			886 => "Taiwan",
			992 => "Tajikistan",
			255 => "Tanzania",
			66 => "Thailand",
			228 => "Togo",
			690 => "Tokelau",
			1 => "Trinidad and Tobago",
			216 => "Tunisia",
			90 => "Turkey",
			993 => "Turkmenistan",
			1 => "Turks and Caicos Islands",
			688 => "Tuvalu",
			256 => "Uganda",
			380 => "Ukraine",
			971 => "United Arab Emirates",
			44 => "United Kingdom",
			1 => "United States of America",
			1 => "U.S. Virgin Islands",
			598 => "Uruguay",
			998 => "Uzbekistan",
			678 => "Vanuatu",
			379 => "Vatican City",
			58 => "Venezuela",
			84 => "Vietnam",
			681 => "Wallis and Futuna",
			967 => "Yemen",
			260 => "Zambia",
			263 => "Zimbabwe"
			)
		);
 	}
	
	/**
	 * The list of predefined languages
	 * For WordPress locales, see https://translate.wordpress.org/
	 * For W3C locales, see http://www.iana.org/assignments/language-subtag-registry/language-subtag-registry
	 * See also #33511
	 * Facebook locales used to be available at https://www.facebook.com/translations/FacebookLocales.xml
	 *
	 * For each language:
	 * [code]     => ISO 639-1 language code
	 * [locale]   => WordPress locale
	 * [name]     => name
	 * [dir]      => text direction
	 * [flag]     => flag code
	 * [w3c]      => W3C locale
	 * [facebook] => Facebook locale
	 *
	 * Facebook locales without equivalent WordPress locale:
	 * 'ay_BO' (Aymara)
	 * 'ck_US' (Cherokee)
	 * 'en_IN' (English India)
	 * 'gx_GR' (Classical Greek)
	 * 'ig_NG' (Igbo)
	 * 'ja_KS' (Japanese Kansai)
	 * 'lg_UG' (Ganda)
	 * 'nd_ZW' (Ndebele)
	 * 'ny_MW' (Chewa)
	 * 'qu_PE' (Quechua)
	 * 'se_NO' (Northern Sami)
	 * 'tl_ST' (Klingon)
	 * 'wo_SN' (Wolof)
	 * 'yi_DE' (Yiddish)
	 * 'zu_ZA' (Zulu)
	 * 'zz_TR' (Zazaki)
	 */
	public static function languages(){
		return array(
		'af' => array(
			'code'     => 'af',
			'locale'   => 'af',
			'name'     => 'Afrikaans',
			'dir'      => 'ltr',
			'flag'     => 'za',
			'facebook' => 'af_ZA',
		),
		'ak' => array(
			'facebook' => 'ak_GH',
		),
		'am' => array(
			'facebook' => 'am_ET',
		),
		'ar' => array(
			'code'     => 'ar',
			'locale'   => 'ar',
			'name'     => 'العربية',
			'dir'      => 'rtl',
			'flag'     => 'arab',
			'facebook' => 'ar_AR',
		),
		'arq' => array(
			'facebook' => 'ar_AR',
		),
		'ary' => array(
			'code'     => 'ar',
			'locale'   => 'ary',
			'name'     => 'العربية المغربية',
			'dir'      => 'rtl',
			'flag'     => 'ma',
			'facebook' => 'ar_AR',
		),
		'as' => array(
			'code'     => 'as',
			'locale'   => 'as',
			'name'     => 'অসমীয়া',
			'dir'      => 'ltr',
			'flag'     => 'in',
			'facebook' => 'as_IN',
		),
		'az' => array(
			'code'     => 'az',
			'locale'   => 'az',
			'name'     => 'Azərbaycan',
			'dir'      => 'ltr',
			'flag'     => 'az',
			'facebook' => 'az_AZ',
		),
		'azb' => array(
			'code'     => 'az',
			'locale'   => 'azb',
			'name'     => 'گؤنئی آذربایجان',
			'dir'      => 'rtl',
			'flag'     => 'az',
		),
		'bel' => array(
			'code'     => 'be',
			'locale'   => 'bel',
			'name'     => 'Беларуская мова',
			'dir'      => 'ltr',
			'flag'     => 'by',
			'w3c'      => 'be',
			'facebook' => 'be_BY',
		),
		'bg_BG' => array(
			'code'     => 'bg',
			'locale'   => 'bg_BG',
			'name'     => 'български',
			'dir'      => 'ltr',
			'flag'     => 'bg',
			'facebook' => 'bg_BG',
		),
		'bn_BD' => array(
			'code'     => 'bn',
			'locale'   => 'bn_BD',
			'name'     => 'বাংলা',
			'dir'      => 'ltr',
			'flag'     => 'bd',
			'facebook' => 'bn_IN',
		),
		'bo' => array(
			'code'     => 'bo',
			'locale'   => 'bo',
			'name'     => 'བོད་ཡིག',
			'dir'      => 'ltr',
			'flag'     => 'tibet',
		),
		'bre' => array(
			'w3c'      => 'br',
			'facebook' => 'br_FR',
		),
		'bs_BA' => array(
			'code'     => 'bs',
			'locale'   => 'bs_BA',
			'name'     => 'Bosanski',
			'dir'      => 'ltr',
			'flag'     => 'ba',
			'facebook' => 'bs_BA',
		),
		'ca' => array(
			'code'     => 'ca',
			'locale'   => 'ca',
			'name'     => 'Català',
			'dir'      => 'ltr',
			'flag'     => 'catalonia',
			'facebook' => 'ca_ES',
		),
		'ceb' => array(
			'code'     => 'ceb',
			'locale'   => 'ceb',
			'name'     => 'Cebuano',
			'dir'      => 'ltr',
			'flag'     => 'ph',
			'facebook' => 'cx_PH',
		),
		'ckb' => array(
			'code'     => 'ku',
			'locale'   => 'ckb',
			'name'     => 'کوردی',
			'dir'      => 'rtl',
			'flag'     => 'kurdistan',
			'facebook' => 'cb_IQ',
		),
		'co' => array(
			'facebook' => 'co_FR',
		),
		'cs_CZ' => array(
			'code'     => 'cs',
			'locale'   => 'cs_CZ',
			'name'     => 'Čeština',
			'dir'      => 'ltr',
			'flag'     => 'cz',
			'facebook' => 'cs_CZ',
		),
		'cy' => array(
			'code'     => 'cy',
			'locale'   => 'cy',
			'name'     => 'Cymraeg',
			'dir'      => 'ltr',
			'flag'     => 'wales',
			'facebook' => 'cy_GB',
		),
		'da_DK' => array(
			'code'     => 'da',
			'locale'   => 'da_DK',
			'name'     => 'Dansk',
			'dir'      => 'ltr',
			'flag'     => 'dk',
			'facebook' => 'da_DK',
		),
		'de_CH' => array(
			'code'     => 'de',
			'locale'   => 'de_CH',
			'name'     => 'Deutsch',
			'dir'      => 'ltr',
			'flag'     => 'ch',
			'facebook' => 'de_DE',
		),
		'de_CH_informal' => array(
			'code'     => 'de',
			'locale'   => 'de_CH_informal',
			'name'     => 'Deutsch',
			'dir'      => 'ltr',
			'flag'     => 'ch',
			'w3c'      => 'de-CH',
			'facebook' => 'de_DE',
		),
		'de_DE' => array(
			'code'     => 'de',
			'locale'   => 'de_DE',
			'name'     => 'Deutsch',
			'dir'      => 'ltr',
			'flag'     => 'de',
			'facebook' => 'de_DE',
		),
		'de_DE_formal' => array(
			'code'     => 'de',
			'locale'   => 'de_DE_formal',
			'name'     => 'Deutsch',
			'dir'      => 'ltr',
			'flag'     => 'de',
			'w3c'      => 'de-DE',
			'facebook' => 'de_DE',
		),
		'dzo' => array(
			'code'     => 'dz',
			'locale'   => 'dzo',
			'name'     => 'རྫོང་ཁ',
			'dir'      => 'ltr',
			'flag'     => 'bt',
			'w3c'      => 'dz',
		),
		'el' => array(
			'code'     => 'el',
			'locale'   => 'el',
			'name'     => 'Ελληνικά',
			'dir'      => 'ltr',
			'flag'     => 'gr',
			'facebook' => 'el_GR',
		),
		'en_AU' => array(
			'code'     => 'en',
			'locale'   => 'en_AU',
			'name'     => 'English',
			'dir'      => 'ltr',
			'flag'     => 'au',
			'facebook' => 'en_US',
		),
		'en_CA' => array(
			'code'     => 'en',
			'locale'   => 'en_CA',
			'name'     => 'English',
			'dir'      => 'ltr',
			'flag'     => 'ca',
			'facebook' => 'en_US',
		),
		'en_GB' => array(
			'code'     => 'en',
			'locale'   => 'en_GB',
			'name'     => 'English',
			'dir'      => 'ltr',
			'flag'     => 'gb',
			'facebook' => 'en_GB',
		),
		'en_NZ' => array(
			'code'     => 'en',
			'locale'   => 'en_NZ',
			'name'     => 'English',
			'dir'      => 'ltr',
			'flag'     => 'nz',
			'facebook' => 'en_US',
		),
		'en_US' => array(
			'code'     => 'en',
			'locale'   => 'en_US',
			'name'     => 'English',
			'dir'      => 'ltr',
			'flag'     => 'us',
			'facebook' => 'en_US',
		),
		'en_ZA' => array(
			'code'     => 'en',
			'locale'   => 'en_ZA',
			'name'     => 'English',
			'dir'      => 'ltr',
			'flag'     => 'za',
			'facebook' => 'en_US',
		),
		'eo' => array(
			'code'     => 'eo',
			'locale'   => 'eo',
			'name'     => 'Esperanto',
			'dir'      => 'ltr',
			'flag'     => 'esperanto',
			'facebook' => 'eo_EO',
		),
		'es_AR' => array(
			'code'     => 'es',
			'locale'   => 'es_AR',
			'name'     => 'Español',
			'dir'      => 'ltr',
			'flag'     => 'ar',
			'facebook' => 'es_LA',
		),
		'es_CL' => array(
			'code'     => 'es',
			'locale'   => 'es_CL',
			'name'     => 'Español',
			'dir'      => 'ltr',
			'flag'     => 'cl',
			'facebook' => 'es_CL',
		),
		'es_CO' => array(
			'code'     => 'es',
			'locale'   => 'es_CO',
			'name'     => 'Español',
			'dir'      => 'ltr',
			'flag'     => 'co',
			'facebook' => 'es_CO',
		),
		'es_CR' => array(
			'code'     => 'es',
			'locale'   => 'es_CR',
			'name'     => 'Español',
			'dir'      => 'ltr',
			'flag'     => 'cr',
			'facebook' => 'es_LA',
		),
		'es_ES' => array(
			'code'     => 'es',
			'locale'   => 'es_ES',
			'name'     => 'Español',
			'dir'      => 'ltr',
			'flag'     => 'es',
			'facebook' => 'es_ES',
		),
		'es_GT' => array(
			'code'     => 'es',
			'locale'   => 'es_GT',
			'name'     => 'Español',
			'dir'      => 'ltr',
			'flag'     => 'gt',
			'facebook' => 'es_LA',
		),
		'es_MX' => array(
			'code'     => 'es',
			'locale'   => 'es_MX',
			'name'     => 'Español',
			'dir'      => 'ltr',
			'flag'     => 'mx',
			'facebook' => 'es_MX',
		),
		'es_PE' => array(
			'code'     => 'es',
			'locale'   => 'es_PE',
			'name'     => 'Español',
			'dir'      => 'ltr',
			'flag'     => 'pe',
			'facebook' => 'es_LA',
		),
		'es_VE' => array(
			'code'     => 'es',
			'locale'   => 'es_VE',
			'name'     => 'Español',
			'dir'      => 'ltr',
			'flag'     => 've',
			'facebook' => 'es_VE',
		),
		'et' => array(
			'code'     => 'et',
			'locale'   => 'et',
			'name'     => 'Eesti',
			'dir'      => 'ltr',
			'flag'     => 'ee',
			'facebook' => 'et_EE',
		),
		'eu' => array(
			'code'     => 'eu',
			'locale'   => 'eu',
			'name'     => 'Euskara',
			'dir'      => 'ltr',
			'flag'     => 'basque',
			'facebook' => 'eu_ES',
		),
		'fa_AF' => array(
			'code'     => 'fa',
			'locale'   => 'fa_AF',
			'name'     => 'فارسی',
			'dir'      => 'rtl',
			'flag'     => 'af',
			'facebook' => 'fa_IR',
		),
		'fa_IR' => array(
			'code'     => 'fa',
			'locale'   => 'fa_IR',
			'name'     => 'فارسی',
			'dir'      => 'rtl',
			'flag'     => 'ir',
			'facebook' => 'fa_IR',
		),
		'fi' => array(
			'code'     => 'fi',
			'locale'   => 'fi',
			'name'     => 'Suomi',
			'dir'      => 'ltr',
			'flag'     => 'fi',
			'facebook' => 'fi_FI',
		),
		'fo' => array(
			'code'     => 'fo',
			'locale'   => 'fo',
			'name'     => 'Føroyskt',
			'dir'      => 'ltr',
			'flag'     => 'fo',
			'facebook' => 'fo_FO',
		),
		'fr_BE' => array(
			'code'     => 'fr',
			'locale'   => 'fr_BE',
			'name'     => 'Français',
			'dir'      => 'ltr',
			'flag'     => 'be',
			'facebook' => 'fr_FR',
		),
		'fr_CA' => array(
			'code'     => 'fr',
			'locale'   => 'fr_CA',
			'name'     => 'Français',
			'dir'      => 'ltr',
			'flag'     => 'quebec',
			'facebook' => 'fr_CA',
		),
		'fr_FR' => array(
			'code'     => 'fr',
			'locale'   => 'fr_FR',
			'name'     => 'Français',
			'dir'      => 'ltr',
			'flag'     => 'fr',
			'facebook' => 'fr_FR',
		),
		'fuc' => array(
			'facebook' => 'ff_NG',
		),
		'fy' => array(
			'code'     => 'fy',
			'locale'   => 'fy',
			'name'     => 'Frysk',
			'dir'      => 'ltr',
			'flag'     => 'nl',
			'facebook' => 'fy_NL',
		),
		'ga' => array(
			'facebook' => 'ga_IE',
		),
		'gd' => array(
			'code'     => 'gd',
			'locale'   => 'gd',
			'name'     => 'Gàidhlig',
			'dir'      => 'ltr',
			'flag'     => 'scotland',
		),
		'gl_ES' => array(
			'code'     => 'gl',
			'locale'   => 'gl_ES',
			'name'     => 'Galego',
			'dir'      => 'ltr',
			'flag'     => 'galicia',
			'facebook' => 'gl_ES',
		),
		'gn' => array(
			'facebook' => 'gn_PY',
		),
		'gu' => array(
			'code'     => 'gu',
			'locale'   => 'gu',
			'name'     => 'ગુજરાતી',
			'dir'      => 'ltr',
			'flag'     => 'in',
			'facebook' => 'gu_IN',
		),
		'hau' => array(
			'facebook' => 'ha_NG',
		),
		'haz' => array(
			'code'     => 'haz',
			'locale'   => 'haz',
			'name'     => 'هزاره گی',
			'dir'      => 'rtl',
			'flag'     => 'af',
		),
		'he_IL' => array(
			'code'     => 'he',
			'locale'   => 'he_IL',
			'name'     => 'עברית',
			'dir'      => 'rtl',
			'flag'     => 'il',
			'facebook' => 'he_IL',
		),
		'hi_IN' => array(
			'code'     => 'hi',
			'locale'   => 'hi_IN',
			'name'     => 'हिन्दी',
			'dir'      => 'ltr',
			'flag'     => 'in',
			'facebook' => 'hi_IN',
		),
		'hr' => array(
			'code'     => 'hr',
			'locale'   => 'hr',
			'name'     => 'Hrvatski',
			'dir'      => 'ltr',
			'flag'     => 'hr',
			'facebook' => 'hr_HR',
		),
		'hu_HU' => array(
			'code'     => 'hu',
			'locale'   => 'hu_HU',
			'name'     => 'Magyar',
			'dir'      => 'ltr',
			'flag'     => 'hu',
			'facebook' => 'hu_HU',
		),
		'hy' => array(
			'code'     => 'hy',
			'locale'   => 'hy',
			'name'     => 'Հայերեն',
			'dir'      => 'ltr',
			'flag'     => 'am',
			'facebook' => 'hy_AM',
		),
		'id_ID' => array(
			'code'     => 'id',
			'locale'   => 'id_ID',
			'name'     => 'Bahasa Indonesia',
			'dir'      => 'ltr',
			'flag'     => 'id',
			'facebook' => 'id_ID',
		),
		'ido' => array(
			'w3c'      => 'io',
		),
		'is_IS' => array(
			'code'     => 'is',
			'locale'   => 'is_IS',
			'name'     => 'Íslenska',
			'dir'      => 'ltr',
			'flag'     => 'is',
			'facebook' => 'is_IS',
		),
		'it_IT' => array(
			'code'     => 'it',
			'locale'   => 'it_IT',
			'name'     => 'Italiano',
			'dir'      => 'ltr',
			'flag'     => 'it',
			'facebook' => 'it_IT',
		),
		'ja' => array(
			'code'     => 'ja',
			'locale'   => 'ja',
			'name'     => '日本語',
			'dir'      => 'ltr',
			'flag'     => 'jp',
			'facebook' => 'ja_JP',
		),
		'jv_ID' => array(
			'code'     => 'jv',
			'locale'   => 'jv_ID',
			'name'     => 'Basa Jawa',
			'dir'      => 'ltr',
			'flag'     => 'id',
			'facebook' => 'jv_ID',
		),
		'ka_GE' => array(
			'code'     => 'ka',
			'locale'   => 'ka_GE',
			'name'     => 'ქართული',
			'dir'      => 'ltr',
			'flag'     => 'ge',
			'facebook' => 'ka_GE',
		),
		'kab' => array(
			'code'     => 'kab',
			'locale'   => 'kab',
			'name'     => 'Taqbaylit',
			'dir'      => 'ltr',
			'flag'     => 'dz',
		),
		'kin' => array(
			'w3c'      => 'rw',
			'facebook' => 'rw_RW',
		),
		'kk' => array(
			'code'     => 'kk',
			'locale'   => 'kk',
			'name'     => 'Қазақ тілі',
			'dir'      => 'ltr',
			'flag'     => 'kz',
			'facebook' => 'kk_KZ',
		),
		'km' => array(
			'code'     => 'km',
			'locale'   => 'km',
			'name'     => 'ភាសាខ្មែរ',
			'dir'      => 'ltr',
			'flag'     => 'kh',
			'facebook' => 'km_KH',
		),
		'kn' => array(
			'facebook' => 'kn_IN',
		),
		'ko_KR' => array(
			'code'     => 'ko',
			'locale'   => 'ko_KR',
			'name'     => '한국어',
			'dir'      => 'ltr',
			'flag'     => 'kr',
			'facebook' => 'ko_KR',
		),
		'ku' => array(
			'facebook' => 'ku_TR',
		),
		'ky_KY' => array(
			'facebook' => 'ky_KG',
		),
		'la' => array(
			'facebook' => 'la_VA',
		),
		'li' => array(
			'facebook' => 'li_NL',
		),
		'lin' => array(
			'facebook' => 'ln_CD',
		),
		'lo' => array(
			'code'     => 'lo',
			'locale'   => 'lo',
			'name'     => 'ພາສາລາວ',
			'dir'      => 'ltr',
			'flag'     => 'la',
			'facebook' => 'lo_LA',
		),
		'lt_LT' => array(
			'code'     => 'lt',
			'locale'   => 'lt_LT',
			'name'     => 'Lietuviškai',
			'dir'      => 'ltr',
			'flag'     => 'lt',
			'facebook' => 'lt_LT',
		),
		'lv' => array(
			'code'     => 'lv',
			'locale'   => 'lv',
			'name'     => 'Latviešu valoda',
			'dir'      => 'ltr',
			'flag'     => 'lv',
			'facebook' => 'lv_LV',
		),
		'mg_MG' => array(
			'facebook' => 'mg_MG',
		),
		'mk_MK' => array(
			'code'     => 'mk',
			'locale'   => 'mk_MK',
			'name'     => 'македонски јазик',
			'dir'      => 'ltr',
			'flag'     => 'mk',
			'facebook' => 'mk_MK',
		),
		'ml_IN' => array(
			'code'     => 'ml',
			'locale'   => 'ml_IN',
			'name'     => 'മലയാളം',
			'dir'      => 'ltr',
			'flag'     => 'in',
			'facebook' => 'ml_IN',
		),
		'mlt' => array(
			'facebook' => 'mt_MT',
		),
		'mn' => array(
			'code'     => 'mn',
			'locale'   => 'mn',
			'name'     => 'Монгол хэл',
			'dir'      => 'ltr',
			'flag'     => 'mn',
			'facebook' => 'mn_MN',
		),
		'mr' => array(
			'code'     => 'mr',
			'locale'   => 'mr',
			'name'     => 'मराठी',
			'dir'      => 'ltr',
			'flag'     => 'in',
			'facebook' => 'mr_IN',
		),
		'mri' => array(
			'w3c'      => 'mi',
			'facebook' => 'mi_NZ',
		),
		'ms_MY' => array(
			'code'     => 'ms',
			'locale'   => 'ms_MY',
			'name'     => 'Bahasa Melayu',
			'dir'      => 'ltr',
			'flag'     => 'my',
			'facebook' => 'ms_MY',
		),
		'my_MM' => array(
			'code'     => 'my',
			'locale'   => 'my_MM',
			'name'     => 'ဗမာစာ',
			'dir'      => 'ltr',
			'flag'     => 'mm',
			'facebook' => 'my_MM',
		),
		'nb_NO' => array(
			'code'     => 'nb',
			'locale'   => 'nb_NO',
			'name'     => 'Norsk Bokmål',
			'dir'      => 'ltr',
			'flag'     => 'no',
			'facebook' => 'nb_NO',
		),
		'ne_NP' => array(
			'code'     => 'ne',
			'locale'   => 'ne_NP',
			'name'     => 'नेपाली',
			'dir'      => 'ltr',
			'flag'     => 'np',
			'facebook' => 'ne_NP',
		),
		'nl_BE' => array(
			'code'     => 'nl',
			'locale'   => 'nl_BE',
			'name'     => 'Nederlands',
			'dir'      => 'ltr',
			'flag'     => 'be',
			'facebook' => 'nl_BE',
		),
		'nl_NL' => array(
			'code'     => 'nl',
			'locale'   => 'nl_NL',
			'name'     => 'Nederlands',
			'dir'      => 'ltr',
			'flag'     => 'nl',
			'facebook' => 'nl_NL',
		),
		'nl_NL_formal' => array(
			'code'     => 'nl',
			'locale'   => 'nl_NL_formal',
			'name'     => 'Nederlands',
			'dir'      => 'ltr',
			'flag'     => 'nl',
			'w3c'      => 'nl-NL',
			'facebook' => 'nl_NL',
		),
		'nn_NO' => array(
			'code'     => 'nn',
			'locale'   => 'nn_NO',
			'name'     => 'Norsk Nynorsk',
			'dir'      => 'ltr',
			'flag'     => 'no',
			'facebook' => 'nn_NO',
		),
		'oci' => array(
			'code'     => 'oc',
			'locale'   => 'oci',
			'name'     => 'Occitan',
			'dir'      => 'ltr',
			'flag'     => 'occitania',
			'w3c'      => 'oc',
		),
		'ory' => array(
			'facebook' => 'or_IN',
		),
		'pa_IN' => array(
			'code'     => 'pa',
			'locale'   => 'pa_IN',
			'name'     => 'ਪੰਜਾਬੀ',
			'dir'      => 'ltr',
			'flag'     => 'in',
			'facebook' => 'pa_IN',
		),
		'pl_PL' => array(
			'code'     => 'pl',
			'locale'   => 'pl_PL',
			'name'     => 'Polski',
			'dir'      => 'ltr',
			'flag'     => 'pl',
			'facebook' => 'pl_PL',
		),
		'ps' => array(
			'code'     => 'ps',
			'locale'   => 'ps',
			'name'     => 'پښتو',
			'dir'      => 'rtl',
			'flag'     => 'af',
			'facebook' => 'ps_AF',
		),
		'pt_BR' => array(
			'code'     => 'pt',
			'locale'   => 'pt_BR',
			'name'     => 'Português',
			'dir'      => 'ltr',
			'flag'     => 'br',
			'facebook' => 'pt_BR',
		),
		'pt_PT' => array(
			'code'     => 'pt',
			'locale'   => 'pt_PT',
			'name'     => 'Português',
			'dir'      => 'ltr',
			'flag'     => 'pt',
			'facebook' => 'pt_PT',
		),
		'pt_PT_ao90' => array(
			'code'     => 'pt',
			'locale'   => 'pt_PT_ao90',
			'name'     => 'Português',
			'dir'      => 'ltr',
			'flag'     => 'pt',
			'facebook' => 'pt_PT',
		),
		'rhg' => array(
			'code'     => 'rhg',
			'locale'   => 'rhg',
			'name'     => 'Ruáinga',
			'dir'      => 'ltr',
			'flag'     => 'mm',
		),
		'ro_RO' => array(
			'code'     => 'ro',
			'locale'   => 'ro_RO',
			'name'     => 'Română',
			'dir'      => 'ltr',
			'flag'     => 'ro',
			'facebook' => 'ro_RO',
		),
		'roh' => array(
			'w3c'      => 'rm',
			'facebook' => 'rm_CH',
		),
		'ru_RU' => array(
			'code'     => 'ru',
			'locale'   => 'ru_RU',
			'name'     => 'Русский',
			'dir'      => 'ltr',
			'flag'     => 'ru',
			'facebook' => 'ru_RU',
		),
		'sa_IN' => array(
			'facebook' => 'sa_IN',
		),
		'sah' => array(
			'code'     => 'sah',
			'locale'   => 'sah',
			'name'     => 'Сахалыы',
			'dir'      => 'ltr',
			'flag'     => 'ru',
		),
		'si_LK' => array(
			'code'     => 'si',
			'locale'   => 'si_LK',
			'name'     => 'සිංහල',
			'dir'      => 'ltr',
			'flag'     => 'lk',
			'facebook' => 'si_LK',
		),
		'sk_SK' => array(
			'code'     => 'sk',
			'locale'   => 'sk_SK',
			'name'     => 'Slovenčina',
			'dir'      => 'ltr',
			'flag'     => 'sk',
			'facebook' => 'sk_SK',
		),
		'sl_SI' => array(
			'code'     => 'sl',
			'locale'   => 'sl_SI',
			'name'     => 'Slovenščina',
			'dir'      => 'ltr',
			'flag'     => 'si',
			'facebook' => 'sl_SI',
		),
		'sna' => array(
			'facebook' => 'sn_ZW',
		),
		'so_SO' => array(
			'code'     => 'so',
			'locale'   => 'so_SO',
			'name'     => 'Af-Soomaali',
			'dir'      => 'ltr',
			'flag'     => 'so',
			'facebook' => 'so_SO',
		),
		'sq' => array(
			'code'     => 'sq',
			'locale'   => 'sq',
			'name'     => 'Shqip',
			'dir'      => 'ltr',
			'flag'     => 'al',
			'facebook' => 'sq_AL',
		),
		'sr_RS' => array(
			'code'     => 'sr',
			'locale'   => 'sr_RS',
			'name'     => 'Српски језик',
			'dir'      => 'ltr',
			'flag'     => 'rs',
			'facebook' => 'sr_RS',
		),
		'srd' => array(
			'w3c'      => 'sc',
			'facebook' => 'sc_IT',
		),
		'su_ID' => array(
			'code'     => 'su',
			'locale'   => 'su_ID',
			'name'     => 'Basa Sunda',
			'dir'      => 'ltr',
			'flag'     => 'id',
		),
		'sv_SE' => array(
			'code'     => 'sv',
			'locale'   => 'sv_SE',
			'name'     => 'Svenska',
			'dir'      => 'ltr',
			'flag'     => 'se',
			'facebook' => 'sv_SE',
		),
		'sw' => array(
			'facebook' => 'sw_KE',
		),
		'syr' => array(
			'facebook' => 'sy_SY',
		),
		'szl' => array(
			'code'     => 'szl',
			'locale'   => 'szl',
			'name'     => 'Ślōnskŏ gŏdka',
			'dir'      => 'ltr',
			'flag'     => 'pl',
			'facebook' => 'sz_PL',
		),
		'ta_IN' => array(
			'code'     => 'ta',
			'locale'   => 'ta_IN',
			'name'     => 'தமிழ்',
			'dir'      => 'ltr',
			'flag'     => 'in',
			'facebook' => 'ta_IN',
		),
		'ta_LK' => array(
			'code'     => 'ta',
			'locale'   => 'ta_LK',
			'name'     => 'தமிழ்',
			'dir'      => 'ltr',
			'flag'     => 'lk',
			'facebook' => 'ta_IN',
		),
		'tah' => array(
			'code'     => 'ty',
			'locale'   => 'tah',
			'name'     => 'Reo Tahiti',
			'dir'      => 'ltr',
			'flag'     => 'pf',
		),
		'te' => array(
			'code'     => 'te',
			'locale'   => 'te',
			'name'     => 'తెలుగు',
			'dir'      => 'ltr',
			'flag'     => 'in',
			'facebook' => 'te_IN',
		),
		'tg' => array(
			'facebook' => 'tg_TJ',
		),
		'th' => array(
			'code'     => 'th',
			'locale'   => 'th',
			'name'     => 'ไทย',
			'dir'      => 'ltr',
			'flag'     => 'th',
			'facebook' => 'th_TH',
		),
		'tl' => array(
			'code'     => 'tl',
			'locale'   => 'tl',
			'name'     => 'Tagalog',
			'dir'      => 'ltr',
			'flag'     => 'ph',
			'facebook' => 'tl_PH',
		),
		'tr_TR' => array(
			'code'     => 'tr',
			'locale'   => 'tr_TR',
			'name'     => 'Türkçe',
			'dir'      => 'ltr',
			'flag'     => 'tr',
			'facebook' => 'tr_TR',
		),
		'tt_RU' => array(
			'code'     => 'tt',
			'locale'   => 'tt_RU',
			'name'     => 'Татар теле',
			'dir'      => 'ltr',
			'flag'     => 'ru',
			'facebook' => 'tt_RU',
		),
		'tuk' => array(
			'w3c'      => 'tk',
			'facebook' => 'tk_TM',
		),
		'tzm' => array(
			'facebook' => 'tz_MA',
		),
		'ug_CN' => array(
			'code'     => 'ug',
			'locale'   => 'ug_CN',
			'name'     => 'Uyƣurqə',
			'dir'      => 'ltr',
			'flag'     => 'cn',
		),
		'uk' => array(
			'code'     => 'uk',
			'locale'   => 'uk',
			'name'     => 'Українська',
			'dir'      => 'ltr',
			'flag'     => 'ua',
			'facebook' => 'uk_UA',
		),
		'ur' => array(
			'code'     => 'ur',
			'locale'   => 'ur',
			'name'     => 'اردو',
			'dir'      => 'rtl',
			'flag'     => 'pk',
			'facebook' => 'ur_PK',
		),
		'uz_UZ' => array(
			'code'     => 'uz',
			'locale'   => 'uz_UZ',
			'name'     => 'Oʻzbek',
			'dir'      => 'ltr',
			'flag'     => 'uz',
			'facebook' => 'uz_UZ',
		),
		'vec' => array(
			'code'     => 'vec',
			'locale'   => 'vec',
			'name'     => 'Vèneto',
			'dir'      => 'ltr',
			'flag'     => 'veneto',
		),
		'vi' => array(
			'code'     => 'vi',
			'locale'   => 'vi',
			'name'     => 'Tiếng Việt',
			'dir'      => 'ltr',
			'flag'     => 'vn',
			'facebook' => 'vi_VN',
		),
		'xho' => array(
			'facebook' => 'xh_ZA',
		),
		'yor' => array(
			'facebook' => 'yo_NG',
		),
		'zh_CN' => array(
			'code'     => 'zh',
			'locale'   => 'zh_CN',
			'name'     => '中文 (中国)',
			'dir'      => 'ltr',
			'flag'     => 'cn',
			'facebook' => 'zh_CN',
		),
		'zh_HK' => array(
			'code'     => 'zh',
			'locale'   => 'zh_HK',
			'name'     => '中文 (香港)',
			'dir'      => 'ltr',
			'flag'     => 'hk',
			'facebook' => 'zh_HK',
		),
		'zh_TW' => array(
			'code'     => 'zh',
			'locale'   => 'zh_TW',
			'name'     => '中文 (台灣)',
			'dir'      => 'ltr',
			'flag'     => 'tw',
			'facebook' => 'zh_TW',
		),
		);	
		
	}
}


