<?php
/**
 * WPB Admin Help
 *
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBAdminHelp' ) ) {

class WpBAdminHelp {
	
	const wpb_uploads_url = 'https://wp-base.com/uploads';
	const support_email = "support@wp-base.com";
	
	private $support_form_error;

	/**
     * WP BASE Core + Front [+Admin] instance
     */
	protected $a = null;

	/**
     * Constructor
     */
	function __construct(){
		$this->a = BASE();
	}

	/**
     * Add actions
     */
	function add_hooks() {

		include_once( WPB_PLUGIN_DIR . '/includes/lib/pointer-tutorials.php' );
		
		add_action( 'init', array($this, 'check_tutorial1') );								// Later than Core continue_tutorial
		add_action( 'admin_init', array($this, 'tutorial2'), 120 );							// Add tutorial 2
		add_action( 'admin_init', array($this, 'tutorial3'), 140 );							// Add tutorial 3
		add_action( 'app_submenu_after_tools', array( $this, 'add_submenu' ), 12 );

		// Support email
		add_action( 'admin_init', array( $this, 'send_support_email') );					// Handle support email
		add_action( 'admin_notices', array( $this, 'support_form_error' ) );				// Display errors

		if ( BASE('PDF') )
			add_action( 'wp_ajax_app_create_manual', array( $this, 'create_manual' ) );		// Create Pdf Manual
		
	}
	
	/**
     * Check when tutorial 1 will be initiated
	 * Tutorial1 has front end requirement
     */
	function check_tutorial1(){
		if ( $this->a->continue_tutorial() )
			add_action( 'wp_loaded', array($this, 'tutorial1') );						// Add tutorial 1 to the front end + admin if tutorial session is set
		else
			add_action( 'admin_init', array($this, 'tutorial1') );						// Add tutorial 1 to admin only
	}
	
	function tutorial1() {
		
		$tutorial = new WpB_Pointer_Tutorial('app_tutorial1', 'Quick Start');
		$tutorial->set_textdomain = 'wp-base';
		$tutorial->set_capability = WPB_ADMIN_CAP;
		$tutorial->add_icon( $this->a->plugin_url . '/images/large-greyscale.png' );
		
		# Safe selector in case the selector is hidden or not available at all
		# Pointer will not display if the attached selector is not visible, that is a javascript restriction that cannot be fixed
		# "safe Selector" is a jQuery selector (without $( ), just inside the paranthesis, that you know that always visible on the page
		# However, use this as the last resort; try to choose selectors that are always visible
		if ( is_admin() )
			$tutorial->set_safe_selector('tr:visible:first'); 
		else
			// $tutorial->set_safe_selector('th.app-book-col:nth-of-type(2)'); 
			$tutorial->set_safe_selector('.appointments-pagination'); 
		
		$tutorial->add_step(admin_url('admin.php?page=app_settings'), $this->a->app_name.'_page_app_settings', 'select[name="app_page_type"]', __('Tutorial Introduction', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js( __('Welcome to WP BASE plugin. This tutorial will help you to make a quick start by using the default settings of the plugin. You can restart this tutorial any time by clicking the link on the %s page.', 'wp-base' ) ) , '<a href="'.admin_url("admin.php?page=app_help#tutorials").'" target="_blank" title="'.esc_js(__( 'Click here for Help page','wp-base')).'">'.esc_js(__( 'Help','wp-base')).'</a>' ) . 
			'<br/><span class="app-tutorial-note">'. sprintf( esc_js( __('Note: In some steps of this tutorial, %s are being used to prevent unintended clicking and thus breaking of the flow of the tutorial. You will notice a slightly darker background when modal window is in effect.', 'wp-base' ) ) , '<a href="https://en.wikipedia.org/wiki/Modal_window" target="_blank" title="'.esc_js(__( 'Click here to access the definition of the term in Wikipedia','wp-base')).'">'.esc_js(__( 'modal windows','wp-base')).'</a>' ).'</span></p>',
		    'position' => array( 'edge' => '', 'align' => 'center' ),
		));
		
		$tutorial->add_step(admin_url('admin.php?page=app_settings'), $this->a->app_name.'_page_app_settings', 'select[name="app_page_type"]', __('Creating a front end appointment page', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('We will let WP BASE to create a fully functional Make an Appointment page using this selection which defines the type and look of main booking form.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
			'modal' => 1,
		));

		$page_id = (int)$this->a->first_app_page_id();
		$page_url = get_permalink( $page_id );

		$tutorial->add_step(admin_url('admin.php?page=app_settings'), $this->a->app_name.'_page_app_settings', 'input[name="create_page_btn"]', __('Creating a front end appointment page', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('Normally you would pick your desired layout from the list.', 'wp-base' )).
			'<br/><b>'. esc_js(__('For this tutorial to run correctly, select "Weekly calendar" and then click "Create Page Now".','wp-base'))  .'</b></p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
			'req_expression' => '$("select[name=\"app_page_type\"]").val()=="weekly" || $("#wp-admin-bar-app-make-page-'.$page_id.'").length>0',
			'req_warning' => esc_js(__('Please first select "Weekly calendar" and then click CREATE PAGE NOW button to continue!','wp-base') ),
		));
		

		$tutorial->add_step(admin_url('admin.php?page=app_settings'), $this->a->app_name.'_page_app_settings', '#wp-admin-bar-admin-wp-base', __('WP BASE Menu on Admin Toolbar', 'wp-base'), array(
		    'content'  => '<p>' . esc_js( __('You can access the newly created page hovering over this "WP BASE" menu link. You will notice that this menu includes link to admin pages (Transactions, Settings, Help, etc) too. When you create other WP BASE pages, they will also be added to the bottom of the list.', 'wp-base' ) ) . '</p>',
		    'position' => array( 'edge' => 'top', 'align' => 'center' ),
		));
		
		
		$tutorial->add_step(admin_url('admin.php?page=app_settings'), $this->a->app_name.'_page_app_settings', '.app_form .app_existing_make_page:first', __('Accessing Appointment Page', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('Access link to the page we have just created can also be seen here. Now we will visit the front end to see how this page works. We will take you to that page when you click Next, as if you have clicked the link.', 'wp-base' )).'<br/><b>'. esc_js(__('Click Next.','wp-base'))  .'</b></p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'top' ),
			'modal'	=> 1,
		));
		
		// If hook is numeric, then it is in the front end
		$tutorial->add_step($page_url, $page_id, '.app-flex-menu', __('Make an Appointment Page', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('Plugin created a fully functional page for you, including this table to pick a free %1$s for the pre-selected service*. It is possible to select number of weeks in the settings of the %2$s %3$s which generated this table. It is also possible to choose another time slot selection method, e.g. monthly calendar or table view.', 'wp-base' )),
			'<a href="'.admin_url("admin.php?page=app_help#time-slot").'" target="_blank" title="'.esc_js(__( 'Click here to see the detailed explanation of time slot','wp-base')).'">'.esc_js(__( 'time slot','wp-base')).'</a>',
			'<a href="'.admin_url("admin.php?page=app_help&tab=shortcodes#app-book-shortcode").'" target="_blank" title="'.esc_js(__( 'Click here for the reference information about Book shortcode','wp-base')).'">'.esc_js(__( 'app_book','wp-base')).'</a>',
			'<a href="https://codex.wordpress.org/Shortcode" target="_blank" title="'.esc_js(__( 'Click here to access WordPress website to read explanation of "Shortcode"','wp-base')).'">'.esc_js(__( 'shortcode','wp-base')).'</a>' )	.
			'<br/><span class="app-tutorial-note">'. sprintf( esc_js( __('*Note: WP BASE always selects a service on page load. If there are more than one services defined, this service is the one on the top of the %s list as default. Pre selecting (and sorting) by alphabetical order, ID or price is also possible.', 'wp-base' ) ) , '<a href="'.admin_url("admin.php?page=app_business#custom-sorting").'" target="_blank" title="'.esc_js(__( 'Click here to view Services page. Click Info image there and have a look at the "Sorting" item.','wp-base')).'">'.esc_js(__( 'Services','wp-base')).'</a>' ).'</span></p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'bottom' ),
			'modal'	=> 1,
		));

		$tutorial->add_step($page_url, $page_id, '.appointments-pagination', __('Browse', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('Using previous/next buttons your client can browse through available time slots. These buttons are automatically adjusted so that each click will bring following or previous weeks or months, in this example 2 weeks. Also client can directly select a date by clicking the date select field which uses %s.', 'wp-base' )),'<a href="https://jqueryui.com/datepicker/" target="_blank" title="'.esc_js(__( 'Click here to access jQuery website for reference information about datepicker','wp-base')).'">'.esc_js(__( 'datepicker','wp-base')).'</a>' ).'</p>',
		    'position' => array( 'edge' => 'top', 'align' => 'center' ),
			'modal'	=> 1,
		));

		$tutorial->add_step($page_url, $page_id, '.appointments-wrapper', __('Selecting an appointment time', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('We will apply for an appointment as if we are the client. You can click Next or Previous buttons to select another date/time.', 'wp-base' )),'<a href="'.admin_url("admin.php?page=app_help#time-slot").'" target="_blank" title="'.esc_js(__( 'Click here to see the detailed explanation of time slot','wp-base')).'">'.esc_js(__( 'time slot','wp-base')).'</a>' ).'<br/><b>'. esc_js(__('Select any free time slot by clicking one of the Book Now buttons.','wp-base'))  .'</b></p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'center' ),
			'req_expression' => '$(".app-conf-wrapper:visible").length',
			'req_warning' => esc_js(__('Please first click on any BOOK NOW button to continue!','wp-base') ),
			'next_trigger'	=> 'app-conf-wrapper-opened', // An event that will trigger next
		));
		

		$tutorial->add_step($page_url, $page_id, '.app-conf-wrapper', __('Filling confirmation form fields', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('Fields to be displayed (name, email, phone, address, postal code, etc) are selectable from %s. If displayed, filling of these fields are mandatory and they are marked as (*). Since you are a logged in user, Name and email fields should have been pre populated.', 'wp-base' )),'<a href="'.admin_url("admin.php?page=app_display#conf-form-fields").'" target="_blank" title="'.esc_js(__( 'Click here to access selection of form fields','wp-base')).'">'.esc_js(__( 'display settings','wp-base')).'</a>' ).'</p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'top' ),
			'modal'	=> 1,
			'offset'	=> 40,	// Top offset of the pointer box. If left empty: 220
		));
		
		$tutorial->add_step($page_url, $page_id, '.app-conf-wrapper input:visible:last', __('Testing form fields', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('Try to send the form using an empty required field and check if an error message appears. Please note that you can customize/translate all front end messages and texts using %s.', 'wp-base' )),'<a href="'.admin_url("admin.php?page=app_display&tab=custom_texts").'" target="_blank" title="'.esc_js(__( 'Click here to access Custom Texts page','wp-base')).'">'.esc_js(__( 'Custom Texts','wp-base')).'</a>' ).'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
		));
		
		$tutorial->add_step($page_url, $page_id, '.app-conf-wrapper', __('Submitting confirmation form', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('Now complete the form. Please use a real email address so that you can receive %s.', 'wp-base' )),'<a href="'.admin_url("admin.php?page=app_settings&tab=email#confirmation-message").'" target="_blank" title="'.esc_js(__( 'Click to view the template which will be used for the confirmation message','wp-base')).'">'.esc_js(__( 'confirmation message','wp-base')).'</a>' ).'<br/><b>'. 
			esc_js(__('Click Checkout when done. You need to wait until you receive a confirmation message. Then click Next to continue.','wp-base'))  .'</b></p>',
		    'position' => array( 'edge' => 'top', 'align' => 'bottom' ),
			'next_trigger'	=> 'app-conf-dialog-opened', // An event that will trigger next
			
		));
		
		if ( BASE('Pro') ) {

			$tutorial->add_step($page_url, $page_id, '.app-conf-dialog-content', __('Confirmation Message', 'wp-base'), array(
				'content'  => '<p>' . sprintf( esc_js(__('The confirmation message dialog includes appointment details for the client. You can customize and format the dialog by %s. You can even use images, videos, etc.', 'wp-base' )),'<a href="'.admin_url("admin.php?page=app_settings#confirmation-dialog-content").'" target="_blank" title="'.esc_js(__( 'Click to view the setting to customize the confirmation message','wp-base')).'">'.esc_js(__( 'Confirmation Message Dialog Content setting','wp-base')).'</a>' ).'<br/><b>'. esc_js(__('Click Close on the dialog.','wp-base'))  .'</b></p>',
				'position' => array( 'edge' => 'left', 'align' => 'center' ),
				'req_expression' => "!$('.app-conf-dialog-content:visible').length",
				'req_warning' => esc_js(__('Please click Close button on the confirmation dialog to continue!','wp-base') ),
				'next_trigger'	=> 'onload', // Fires on load of the page
				'modal' => 1,
			));
		}
		
		$tutorial->add_step($page_url, $page_id, '.appointments-wrapper', __('Refresh/Redirection', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('Note that page has been refreshed to update available time slots. Instead of refresh it is also possible to redirect the client to another page, for example to a %s.', 'wp-base' )),'<a href="'.admin_url("admin.php?page=app_settings&tab=advanced#refresh-url").'" target="_blank" title="'.esc_js(__( 'Click to view the setting for redirection page','wp-base')).'">'.esc_js(__( 'Thank You page','wp-base')).'</a>' ).'<br/><b>'. esc_js(__('Click Next to check the result on the admin side.','wp-base'))  .'</b></p>',
		    'position' => array( 'edge' => '', 'align' => 'center' ),
			'modal'=>1,
			'disable_prev'=>1,	// Disable previous button
		));
		
		$tutorial->add_step(admin_url('admin.php?page=appointments&type=all&app_or_fltr=1'), 'toplevel_page_appointments', 'table.app-manage td.column-status', __('Checking the result', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('The booking we have just made should be displayed here as "Confirmed". If not displayed, please ensure that you have completed all of the steps in the tutorial.', 'wp-base' )). '<br/>'.sprintf( esc_js(__('If displayed here, but its status is not Confirmed (then it must be Pending) then your settings are not default. This is the expected behaviour if %s is set as No and actually it is desirable in some of the businesses.', 'wp-base' )),'<a href="'.admin_url("admin.php?page=app_settings#auto-confirm").'" target="_blank" title="'.esc_js(__( 'Click to view Auto Confirm setting which determines the status of bookings with zero amount or if you do not ask payment from the client to accept an appointment.','wp-base')).'">'.esc_js(__( 'Auto Confirm setting','wp-base')).'</a>' ).'</p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
		));
		
		$tutorial->add_step(admin_url('admin.php?page=appointments&type=all&app_or_fltr=1'), 'toplevel_page_appointments', 'table.app-manage tr', __('Checking confirmation email', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('As a result of this booking, two emails have been sent: The first one to the client, the second one to the admin email, %s. Check if you have received those emails. If you did, then you can skip the next step.', 'wp-base' )), BASE('User')->get_admin_email( true ) ). '</p>',
		    'position' => array( 'edge' => '', 'align' => 'center' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_tools'), $this->a->app_name.'_page_app_tools', '#app_log', __('Troubleshooting: email not received', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('If you did not receive an email, check if log file includes any record about it. Email sent records are recorded in the log file, unless you disable using %s. Log file includes email success and error records, as well as actions by the admin, for example status and setting changes, deleting of booking records.', 'wp-base' )), '<a href="'.admin_url("admin.php?page=app_settings&tab=email#log-emails").'" target="_blank" title="'.esc_js(__( 'Click to view the setting for disabling email log records','wp-base')).'">'.esc_js(__( 'related setting','wp-base')).'</a>' ). 
			'<br/>'.sprintf( esc_js(__('If you see an error message, try to send a %s. Especially if this is a newly installed website, you may be not sending any emails at all.', 'wp-base' )),'<a href="'.admin_url("admin.php?page=app_settings&tab=email#send-test-email").'" target="_blank" title="'.esc_js(__( 'Click to view the control for sending a test email','wp-base')).'">'.esc_js(__( 'test email','wp-base')).'</a>' ).'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'top' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_tools'), $this->a->app_name.'_page_app_tools', '#app_log', __('End of tutorial', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('In this tutorial he have already created a fully functional appointment booking system. However, this may not completely suit needs of your business.', 'wp-base' )).'<br/>'. 
			esc_js(__('In the next tutorial we will explore some of the settings and show you how to adjust them so that you can apply your business rules to the plugin.', 'wp-base' )).'<br/><b>'.
			'<a href="'.admin_url("admin.php?app_tutorial2-start=1").'" target="_blank" title="'.esc_js(__( 'Clicking this link will start the next tutorial ','wp-base')).'">'.esc_js(__( 'Click here to start the next tutorial','wp-base')).'</a>'  .'</b></p>',
		    'position' => array( 'edge' => '', 'align' => 'top' )
		));
		
				
		if ( isset( $_GET["tutorial"] ) && 'restart1' == $_GET["tutorial"] )
			$tutorial->restart();
			
		$tutorial->initialize();
		
		return $tutorial;
    }

	
	function tutorial2() {

		$tutorial = new WpB_Pointer_Tutorial('app_tutorial2', 'Essential Settings', false);
		$tutorial->set_textdomain = 'wp-base';
		$tutorial->set_capability = WPB_ADMIN_CAP;
		$tutorial->add_icon( $this->a->plugin_url . '/images/large-greyscale.png' );
		$tutorial->set_safe_selector('table:first'); 
		
		$tutorial->add_step(admin_url('admin.php?page=app_settings&tab=main'), $this->a->app_name.'_page_app_settings', 'select[name="app_page_type"]', __('Tutorial Introduction', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js( __('Welcome again. This tutorial assumes that either you have completed the previous one, or you are already familiar with basic functionality of WP BASE. You can restart this tutorial any time by clicking the link on the %s page.', 'wp-base' ) ) , '<a href="'.admin_url("admin.php?page=app_help#tutorials").'" target="_blank" title="'.esc_js(__( 'Click here for Help page','wp-base')).'">'.esc_js(__( 'Help','wp-base')).'</a>' ) . 
			'<br/><span class="app-tutorial-note">'. sprintf( esc_js( __('Note: In some steps of this tutorial, %s are being used to prevent unintended clicking and thus breaking of the flow of the tutorial. You will notice a slightly darker background when modal window is in effect.', 'wp-base' ) ) , '<a href="https://en.wikipedia.org/wiki/Modal_window" target="_blank" title="'.esc_js(__( 'Click here to access the definition of the term in Wikipedia','wp-base')).'">'.esc_js(__( 'modal windows','wp-base')).'</a>' ).'</span></p>',
		    'position' => array( 'edge' => '', 'align' => 'center' ),
			'modal' => 1,
		));
		
		$tutorial->add_step(admin_url('admin.php?page=app_settings&tab=main'), $this->a->app_name.'_page_app_settings', 'li.toplevel_page_appointments li.current', __('Global Settings', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('Settings which are effective website-wise are under "Global Settings". Generally these settings do not need to be changed after once you set them up and start accepting bookings.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
			'modal' => 1,
		));

		$tutorial->add_step(admin_url('admin.php?page=app_settings&tab=main'), $this->a->app_name.'_page_app_settings', 'select[name="min_time"]', __('Selecting Time Base', 'wp-base'), array(
		    'content'  => '<p>' . esc_js( __('Time Base is an important parameter of WP BASE which you should decide before accepting any bookings. It defines the minimum time that you can select for the duration of any of your services. As a result, it also defines the minimum duration of any of your appointments.', 'wp-base' ) ) . '</p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'top' ),
			'modal' => 1,
		));
		
		$tutorial->add_step(admin_url('admin.php?page=app_settings&tab=main'), $this->a->app_name.'_page_app_settings', 'select[name="min_time"]', __('Selecting Time Base', 'wp-base'), array(
		    'content'  => '<p>' . esc_js( __('Your service durations should be divisible by the selection. For example, if you have services with durations of 60 and 30 minutes, you should select 30 and for 45 and 30 minutes services, you should select 15. In addition, it is a good idea to select the maximum possible value, although not a requirement.', 'wp-base' ) ) .
			'<br/><b>'. esc_js(__('Does it sound complicated? Do not worry. Then just choose 1 hour here and we will return this setting later.','wp-base'))  .'</b></p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'top' ),
			'req_expression' => '$("select[name=\"min_time\"]").val()!="auto"',
			'req_warning' => esc_js(__('Please do not choose AUTO for this tutorial to work correctly!','wp-base') ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_settings&tab=main'), $this->a->app_name.'_page_app_settings', '.button-primary', __('Saving Settings', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('Click Save Settings to continue. If you try to navigate away from a setting page without saving your changes, you will get a browser warning to prevent accidential loss of changed settings.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
			// 'req_expression' => '!app_input_changed_global',
			// 'req_warning' => esc_js(__('Please click SAVE SETTINGS to continue!','wp-base') ),
			'next_trigger'	=> 'onload', // Fires on load of the page
		));

		$tutorial->add_step(admin_url('admin.php?page=app_settings&tab=main'), $this->a->app_name.'_page_app_settings', '.nav-tab-wrapper', __('Settings Saved Message', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('You should have received a "Settings Saved" message on top of the page. We will visit this page again after we define the Services.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'top', 'align' => 'top' ),
			'modal' => 1,
		));

		$tutorial->add_step(admin_url('admin.php?page=app_business'), $this->a->app_name.'_page_app_business', 'li.toplevel_page_appointments li.current', __('Business Settings', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('Settings which formulate your "business rules" into the plugin are under Business Settings. These include defining of your services, their durations, prices, your working hours. With Service Providers addon you can also add %s and define their working hours. These settings are expected to be changed during the progress of your business, for example when you employ a new specialist or to define new holidays, change working hours, or prices.', 'wp-base' )), '<a href="'.admin_url("admin.php?page=app_help#service-provider").'" target="_blank" title="'.esc_js(__( 'Click here for definition of service provider','wp-base')).'">'.esc_js(__( 'Service Provider','wp-base')).'</a>' ) .'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
			'modal' => 1,
		));
		
		$tutorial->add_step(admin_url('admin.php?page=app_business'), $this->a->app_name.'_page_app_business', '.app-infobox', __('Services', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('WP BASE is basically about making clients book %s, in other words "selling" services for a definite period of time. Therefore defining your services is the most important setting in this plugin.', 'wp-base' )), '<a href="'.admin_url("admin.php?page=app_help#service").'" target="_blank" title="'.esc_js(__( 'Click here for definition of service','wp-base')).'">'.esc_js(__( 'services','wp-base')).'</a>' ) .'</p>',
		    'position' => array( 'edge' => '', 'align' => 'top' ),
			'modal' => 1,
		));

		$tutorial->add_step(admin_url('admin.php?page=app_business'), $this->a->app_name.'_page_app_business', '.info-button', __('Fields in Service Definition', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('Description of the fields used to define services can be revealed by clicking this image.', 'wp-base' )), '<a href="'.admin_url("admin.php?page=app_help#capacity").'" target="_blank" title="'.esc_js(__( 'Click here for Help page','wp-base')).'">'.esc_js(__( 'help page','wp-base')).'</a>' ) .'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'right' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_business'), $this->a->app_name.'_page_app_business', '#add_service', __('Adding a New Service', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('You can add new service by clicking this button. A sample service should have been installed during installation. You can edit and even delete that too, but in the end at least one service should be left in this table.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
			'req_expression' => '$("#services-table tbody tr").length>1',
			'req_warning' => esc_js(__('Please click ADD NEW SERVICE to continue!','wp-base') ),
			'next_trigger'	=> 'app-add-new-service-clicked',
		));

		$tutorial->add_step(admin_url('admin.php?page=app_business'), $this->a->app_name.'_page_app_business', '#add_services_form .app-service-name', __('Service Name', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('You should enter a name for the service, which will be used in front end, admin side and emails as the definition of this service. Try to keep it short and make use of the "Description Page", which will be explained a few steps later, if you need a long text to describe the service. A service with empty name field cannot be saved. Actually you can delete a service only by emptying its name field.', 'wp-base' )), wpb_setting("min_time")).'</p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'center' ),
			'req_expression' => '$("#services-table input.app_service_name [value=\"\"]").length==0',
			'req_warning' => esc_js(__('Please ENTER a NAME for every service to continue!','wp-base') ),
		));
		
		$tutorial->add_step(admin_url('admin.php?page=app_business'), $this->a->app_name.'_page_app_business', '#add_services_form .app-service-capacity', __('Setting Capacity', 'wp-base'), array(
			'content'  => '<p>' . sprintf( esc_js(__('Normally each service provider serves a single client at a time. Capacity field can be used to change this behavior, but required only if you have resources less than available workforce, or on the contrary, if you can handle more than a single client at any time slot for this service. Please see this %s for detailed explanation and real life examples.', 'wp-base' )), '<a href="'.admin_url("admin.php?page=app_help#capacity").'" target="_blank" title="'.esc_js(__( 'Click here for Help page','wp-base')).'">'.esc_js(__( 'help page','wp-base')).'</a>' ) .'</p>',
			'position' => array( 'edge' => 'bottom', 'align' => 'middle' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_business'), $this->a->app_name.'_page_app_business', '#add_services_form .app-service-duration', __('Setting Duration', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('How long the provider will be unavailable (reserved, booked) starting from the appointment time is determined by Duration setting. Note that it can be set in steps of %s minutes, because that is the Time Base value we have selected in this tutorial just a few steps before.', 'wp-base' )), wpb_setting("min_time")).'</p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'center' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_business'), $this->a->app_name.'_page_app_business', '#add_services_form .app-service-page', __('Setting Description Page', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('A description page including the details of the service you are offering can be created and connected to the service. Then full text or excerpt from the page will be displayed in the tooltip when selecting the service on the front end. If the page has a %s, it will be displayed too.', 'wp-base' )), '<a href="https://codex.wordpress.org/Post_Thumbnails" target="_blank" title="'.esc_js(__( 'Click here for description of featured image on wordpress.org website','wp-base')).'">'.esc_js(__( 'featured image','wp-base')).'</a>' ).'</p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'center' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_business'), $this->a->app_name.'_page_app_business', '#add_services_form .app-service-page', __('Setting Description Page', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('During installation, a sample page with title "Sample Service Description" has been created and connected to the sample service. This page is "Private", therefore it is not visible to public. You may want to %s at it to see how featured image has been used.', 'wp-base' )), '<a href="'.admin_url('edit.php?s=default+service+description&post_status=all&post_type=page').'" target="_blank" title="'.esc_js(__( 'Click here for the page list where you can access sample service description page','wp-base')).'">'.esc_js(__( 'have a look','wp-base')).'</a>' ).'</p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'center' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_business'), $this->a->app_name.'_page_app_business', '#services-table', __('Custom Sorting', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('You will remember from the Quick Start tutorial that a service was preselected even if you did not choose any. Services will be listed in the order as they are displayed here and the one on top of this list will be preselected. You can change the order of the services as you desire.', 'wp-base' )) .'</p>', 
		    'position' => array( 'edge' => 'top', 'align' => 'bottom' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_business'), $this->a->app_name.'_page_app_business', '#services-table', __('Custom Sorting', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('To change the order of the services, use drag and drop technique: Place the cursor on the service to be moved. %s. Press click and hold click button as you move the row to the desired location and release click. Repeat this for other services as required.', 'wp-base' )), 
			'<abbr class="app_service_tr">'.esc_js(__( 'Cursor will change like here, on this sentence','wp-base')).'</abbr>' ). '</p>',
		    'position' => array( 'edge' => 'top', 'align' => 'bottom' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_business'), $this->a->app_name.'_page_app_business', '.button-primary', __('Save Services', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('Do not forget to save your settings. Clicking Add New Service button does NOT save it to the database until you click the Save button.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
			'next_trigger'	=> 'onload', // Fires on load of the page
			'req_next_expr'	=> '$(document).find("div.app-nag-saved").length>0', // Expression to make next_trigger run. If left empty: 1==1
		));
		
		if ( BASE('SP') ) {
			$tutorial->add_step(admin_url('admin.php?page=app_business'), $this->a->app_name.'_page_app_business', '#app_tab_workers', __('Service Providers', 'wp-base'), array(
				'content'  => '<p>' . sprintf( esc_js(__('On Service Providers tab, you can optionally define your staff. When a new service provider (also called specialist, or worker) added, working hour settings of "Business Representative" are copied to their values. Therefore it is a good idea to skip this step for now and define your staff AFTER you set your working hours.', 'wp-base' )), '<a href="https://codex.wordpress.org/Post_Thumbnails" target="_blank" title="'.esc_js(__( 'Click here for description of featured image in wordpress.org','wp-base')).'">'.esc_js(__( 'featured image','wp-base')).'</a>' ).'</p>',
				'position' => array( 'edge' => 'left', 'align' => 'middle' ),
				'modal' => 1
			));
		}

		$tutorial->add_step(admin_url('admin.php?page=app_business&tab=working_hours'), $this->a->app_name.'_page_app_business', '#app_tab_working_hours', __('Selecting Working Hours', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('On this tab you should set your business working hours. ', 'wp-base' )).
			'<br/><b>'. esc_js(__('Click Working Hours tab.','wp-base'))  .'</b></p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
			'req_expression' => '$(document).find("#app_sel_wh_options_btn").length',
			'req_warning' => esc_js(__('Please click on WORKING HOURS tab to continue!','wp-base') ),
			'next_trigger'	=> 'onload',
			'req_next_expr'	=> '$(document).find("#app_sel_wh_options_btn").length', 
		));
		
		$tutorial->add_step(admin_url('admin.php?page=app_business&tab=working_hours'), $this->a->app_name.'_page_app_business', '#app_sel_wh_options_btn', __('Selecting Services', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('Below you will see a table that displays the settings of the service and service provider which can be selectable from the left-hand side "List for" menu. Service providers will be included in the menu as you define them*. You can set the below working tables for each of them. It is also possible to open more than one working hours table and edit them all at once.', 'wp-base' )).
			'<br/><span class="app-tutorial-note">'. sprintf( esc_js( __('Note: Service providers can be defined with Service Providers Addon.', 'wp-base' ) ) , '<a href="'.admin_url('admin.php?page=app_business&tab=working_hours').'" target="_blank" title="'.esc_js(__( 'Click here to view Working Hours page','wp-base')).'">'.esc_js(__( 'Working Hours page','wp-base')).'</a>' ).'</span></p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
		));
		
		$tutorial->add_step(admin_url('admin.php?page=app_business&tab=working_hours'), $this->a->app_name.'_page_app_business', '.app_row5.app_thursday', __('Setting Your Business Hours', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('The Working Hours table is quite intuitive: Working time intervals are denoted with green cells. A cell can be selected/deselected as green by directly clicking on the cell. A deselected (non-green) cell means it is a "break time".', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'center' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_business&tab=working_hours'), $this->a->app_name.'_page_app_business', 'table', __('Setting Your Business Hours', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('Columns denote week days and rows denote working hour intervals per day. Rows are incremented by time base. For example, for 30 minutes time base, 9:00 means "from 9:00 to 10:30".', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'top' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_business&tab=working_hours'), $this->a->app_name.'_page_app_business', '.app-weekly-hours-mins.app_row5', __('Setting Your Business Hours', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('You can select/deselect a whole row by clicking on the row header, as in Excel.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'middle' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_business&tab=working_hours'), $this->a->app_name.'_page_app_business', '.app_thursday', __('Setting Your Business Hours', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('You can select/deselect a whole column/day by clicking on the column header', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'top' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_business&tab=working_hours'), $this->a->app_name.'_page_app_business', '.hourmin_column', __('Setting Your Business Hours', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('You can select/deselect the whole table by clicking on the uppermost left cell.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'top' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_business&tab=working_hours'), $this->a->app_name.'_page_app_business', 'button.app-copy-wh', __('Setting Your Business Hours', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('You can also copy a whole table to another one when two or more tables are open. You can find the details of copy/paste process under "i" button above.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_business&tab=working_hours'), $this->a->app_name.'_page_app_business', '.info-button', __('Setting Your Business Hours', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('Clicking this info button shows additional information, for example when service working hours are used.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_business&tab=working_hours'), $this->a->app_name.'_page_app_business', '.button-primary', __('Save Working Hours', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('Do not forget to save the changes.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
			'next_trigger'	=> 'onload',
			'req_next_expr'	=> '$(document).find("div.app-nag-saved").length>0',			
		));

		if ( BASE('SP') ) {
			
			$tutorial->add_step(admin_url('admin.php?page=app_business&tab=working_hours'), $this->a->app_name.'_page_app_business', '#app_tab_workers', __('Selecting Service Providers', 'wp-base'), array(
				'content'  => '<p>' . esc_js(__('We can now define Service Providers. ', 'wp-base' )).
				'<br/><b>'. esc_js(__('Click Service Providers tab.','wp-base'))  .'</b></p>',
				'position' => array( 'edge' => 'left', 'align' => 'center' ),
				'req_expression' => '$(document).find("#add_worker").length',
				'req_warning' => esc_js(__('Please click on SERVICE PROVIDERS tab to continue!','wp-base') ),
				'next_trigger'	=> 'onload', // Fires on load of the page
				'req_next_expr'	=> '$(document).find("#add_worker").length', 
			));

			$tutorial->add_step(admin_url('admin.php?page=app_business&tab=workers'), $this->a->app_name.'_page_app_business', '.app-infobox', __('Service Providers', 'wp-base'), array(
				'content'  => '<p>' . esc_js(__('Adding service providers is optional. If no service providers are defined, WP BASE assumes that there is a single specialist, the Business Representative, (selectable from settings) capable of giving all of the services.', 'wp-base' )).'</p>',
				'position' => array( 'edge' => '', 'align' => 'center' ),
			));
			
			$tutorial->add_step(admin_url('admin.php?page=app_business&tab=workers'), $this->a->app_name.'_page_app_business', '.info-button', __('Fields in Service Provider Definition', 'wp-base'), array(
				'content'  => '<p>' . sprintf( esc_js(__('Description of the fields used to define service providers can be revealed by clicking the "info" image.', 'wp-base' )), '<a href="'.admin_url("admin.php?page=app_help#capacity").'" target="_blank" title="'.esc_js(__( 'Click here for Help page','wp-base')).'">'.esc_js(__( 'help page','wp-base')).'</a>' ) .'</p>',
				'position' => array( 'edge' => 'bottom', 'align' => 'top' ),
			));

			$tutorial->add_step(admin_url('admin.php?page=app_business&tab=workers'), $this->a->app_name.'_page_app_business', '#add_worker', __('Adding a New Service Provider', 'wp-base'), array(
				'content'  => '<p>' . esc_js(__('You can add new service provider by clicking this button.', 'wp-base' )).
				'<br/><b>'. esc_js(__('Altough service providers are optional, for this tutorial to run correctly, please click Add New Service Provider button.','wp-base'))  .'</b></p>',
				'position' => array( 'edge' => 'left', 'align' => 'center' ),
				'req_expression' => '$("#workers-table tbody tr").length>1',
				'req_warning' => esc_js(__('Please click ADD NEW SERVICE PROVIDER to continue!','wp-base') ),
				'next_trigger'	=> 'app-add-new-worker-clicked',
			));

			$tutorial->add_step(admin_url('admin.php?page=app_business&tab=workers'), $this->a->app_name.'_page_app_business', '#add_workers_form .app-worker-user', __('Selecting a User as Service Provider', 'wp-base'), array(
				'content'  => '<p>' . sprintf( esc_js(__('A dropdown menu of users of your website will be displayed in Service Providers field. This is because service providers must be members of of your website. You can add new users by WordPress %s page and then assign them to be provider.', 'wp-base' )), $this->_a( $this->_t('Users > Add New'), 'user-new.php', $this->_t('Click to add new user')  ) ).'</p>',
				'position' => array( 'edge' => 'bottom', 'align' => 'center' ),
			));

			$tutorial->add_step(admin_url('admin.php?page=app_business&tab=workers'), $this->a->app_name.'_page_app_business', '#add_workers_form .app-worker-name', __('Service Provider Name', 'wp-base'), array(
				'content'  => '<p>' . sprintf( esc_js(__('You can enter a display name for the provider, which will be used in front end, admin side and emails as the name of this provider. If left empty, a display name will be automatically generated using WordPress user data of the provider.', 'wp-base' )), wpb_setting("min_time")).'</p>',
				'position' => array( 'edge' => 'bottom', 'align' => 'center' ),
			));

			$tutorial->add_step(admin_url('admin.php?page=app_business&tab=workers'), $this->a->app_name.'_page_app_business', '#add_workers_form .app-worker-services', __('Assigning Services', 'wp-base'), array(
				'content'  => '<p>' . sprintf( esc_js(__("Service providers must be assigned to at least one service. From the dropdown services menu, you can select one or more services. It is not possible to save a service provider to the database without a service assigned. Actually you can remove service provider task of a user by clearing Services Provided selection for them.", 'wp-base' )), '<a href="'.admin_url("admin.php?page=app_help#capacity").'" target="_blank" title="'.esc_js(__( 'Click here for Help page','wp-base')).'">'.esc_js(__( 'help page','wp-base')).'</a>' ) .'</p>',
				'position' => array( 'edge' => 'bottom', 'align' => 'middle' ),
			));

			$tutorial->add_step(admin_url('admin.php?page=app_business&tab=workers'), $this->a->app_name.'_page_app_business', '#add_workers_form .app-worker-dummy', __('"Dummy" Selection', 'wp-base'), array(
				'content'  => '<p>' . sprintf( esc_js(__('A service provider may be set as %s. This means, there is no such a user and emails that they are supposed to receive will be sent to another (this time real) person. You can select this user on %s page.', 'wp-base' )), $this->_a( $this->_t('"dummy"'), 'admin.php?page=app_help#dummy', $this->_t('Click to view definition of dummy') ), $this->_a( $this->_t('Advanced Settings'), 'admin.php?page=app_business&tab=workers#dummy-assigned-to', $this->_t('Click to view related setting') ) ).'</p>',
				'position' => array( 'edge' => 'bottom', 'align' => 'center' ),
			));

			$tutorial->add_step(admin_url('admin.php?page=app_business&tab=workers'), $this->a->app_name.'_page_app_business', '#add_workers_form .app-worker-price', __('Additional Price', 'wp-base'), array(
				'content'  => '<p>' . sprintf( esc_js(__('You can define an additional price for the service provider which will be added to the service price if that specialist picked by the client.', 'wp-base' )), wpb_setting("min_time")).
				'<br/><span class="app-tutorial-note">'. sprintf( esc_js( __('Note: This property can be used to add "options" to a service (or a facility) for which price differs from the main price.', 'wp-base' ) ) , '<a href="'.admin_url('admin.php?page=app_business&tab=working_hours').'" target="_blank" title="'.esc_js(__( 'Click here to view Working Hours page','wp-base')).'">'.esc_js(__( 'Working Hours page','wp-base')).'</a>' ).'</span></p>',
				'position' => array( 'edge' => 'bottom', 'align' => 'center' ),
			));

			$tutorial->add_step(admin_url('admin.php?page=app_business&tab=workers'), $this->a->app_name.'_page_app_business', '#add_workers_form .app-worker-page', __('Bio Page', 'wp-base'), array(
				'content'  => '<p>' . sprintf( esc_js(__('Similar to Description Page of services, a bio page including information about the service provider can be created and full text or excerpt can be displayed in the tooltip on the front end. If the page has a %s or user has an %s, it will be displayed too.', 'wp-base' )), 
				'<a href="https://codex.wordpress.org/Post_Thumbnails" target="_blank" title="'.esc_js(__( 'Click here for description of featured image on wordpress.org website','wp-base')).'">'.esc_js(__( 'featured image','wp-base')).'</a>',
				'<a href="https://codex.wordpress.org/Using_Gravatars" target="_blank" title="'.esc_js(__( 'Click here to view definition of "Gravatar" and "avatar" on wordpress.org','wp-base')).'">'.esc_js(__( 'avatar','wp-base')).'</a>').
				'<br/><span class="app-tutorial-note">'. sprintf( esc_js( __('Note: For service providers, displaying their avatar in the tooltip is also possible, and actually the %s.', 'wp-base' ) ) , 
				'<a href="'.admin_url('admin.php?page=app_help&tab=shortcodes#app-service-providers-shortcode').'" target="_blank" title="'.esc_js(__( 'Click here to view description of Service Providers shortcode. See "description" attribute there','wp-base')).'">'.esc_js(__( 'default setting','wp-base')).'</a>' ).'</span></p>',
				'position' => array( 'edge' => 'bottom', 'align' => 'center' ),
			));


			$tutorial->add_step(admin_url('admin.php?page=app_business&tab=workers'), $this->a->app_name.'_page_app_business', '#workers-table', __('Custom Sorting', 'wp-base'), array(
				'content'  => '<p>' . sprintf( esc_js(__('As in the case of Services, by default*, service providers will be listed in the order as they are displayed here. You can change the order of the service providers. To do so, use drag and drop technique: Place the cursor on the provider to be moved. %s. Press click and hold click button as you move the row to the desired location and release click.', 'wp-base' )), '<abbr class="app_service_tr">'.esc_js(__( 'Cursor will change like here, on this sentence','wp-base')).'</abbr>' ).
				'<br/><span class="app-tutorial-note">'. sprintf( esc_js( __('*Note: Ordering alphabetically, or by ID or price, etc is possible by setting %s accordingly.', 'wp-base' ) ) , '<a href="'.admin_url('admin.php?page=app_help&tab=shortcodes#app-service-providers-shortcode').'" target="_blank" title="'.esc_js(__( 'See "order_by" attribute in the shortcode description.','wp-base')).'">'.esc_js(__( 'shortcode attributes','wp-base')).'</a>' ).'</span></p>',
				'position' => array( 'edge' => 'top', 'align' => 'bottom' ),
			));

			$tutorial->add_step(admin_url('admin.php?page=app_business&tab=workers'), $this->a->app_name.'_page_app_business', '.button-primary', __('Save Service Providers', 'wp-base'), array(
				'content'  => '<p>' . esc_js(__('Do not forget to save your settings. Clicking Add New Service Provider button does NOT save it to the database until you click the Save button.', 'wp-base' )).'</p>',
				'position' => array( 'edge' => 'left', 'align' => 'center' ),
				'next_trigger'	=> 'onload', // Fires on load of the page
				'req_next_expr'	=> '$(document).find("div.app-nag-saved").length>0', // Expression to make next_trigger run. If left empty: 1==1
			));
		}

		$tutorial->add_step(admin_url('admin.php?page=app_settings'), $this->a->app_name.'_page_app_settings', 'select[name="min_time"]', __('Selecting Time Base', 'wp-base'), array(
		    'content'  => '<p>' . esc_js( __('Now we can return to General Settings and finalize Time Base setting. Selecting it as "Auto" will let WP BASE calculate optimum setting for you. By optimum, we mean the maximum value which can divide all service durations and break times.', 'wp-base' ) ) .
			'<br/><b>'. esc_js(__('Select Time Base setting as Auto.','wp-base'))  .'</b></p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'top' ),
			'req_expression' => '$("select[name=\"min_time\"]").val()=="auto"',
			'req_warning' => esc_js(__('Please CHOOSE AUTO to continue!','wp-base') ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_settings'), $this->a->app_name.'_page_app_settings', 'input[name="app_lower_limit"]', __('Lower Limit', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('If your service requires some kind of preparation (lead time), you may want to limit "last minute" appointments. You can set it in this field.', 'wp-base' )) .'</p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'top' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_settings'), $this->a->app_name.'_page_app_settings', 'input[name="app_limit"]', __('Upper Limit', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('You may want to avoid appointments which are supposed to take place at a date so late that you would not be able to predict your schedule. In this field, you can set number of days to be available for booking, starting from application day.', 'wp-base' )) .'</p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'top' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=app_settings'), $this->a->app_name.'_page_app_settings', 'input[name="clear_time"]', __('Releasing Pending Appointments', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('You may not want the appointment stay in pending status forever: Client may be redirected to the payment gateway, but they do not complete the transaction at all and one of your time slots will be blocked in vain. Regardless of payment is required or not, pending appointments can be freed by %s setting automatically.', 'wp-base' )),
			'<a href="'.admin_url('admin.php?page=admin.php?page=app_settings#clear-time').'" target="_blank" title="'.esc_js(__( 'Click here to view this setting on General Global Settings','wp-base')).'">'.esc_js(__( 'Disable pending appointments after','wp-base')).'</a>' ).'</p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'top' ),
		));

		// $tutorial->add_step(admin_url('admin.php?page=app_settings'), $this->a->app_name.'_page_app_settings', 'select[name="auto_confirm"]', __('Automatic Confirmation', 'wp-base'), array(
		    // 'content'  => '<p>' . esc_js(__('In some businesses, an authenticity check of the appointment submission is required before accepting it and in some others business owners want the submission to be confirmed immediately. You can select which behaviour will be applied using "Auto Confirm" setting. Please read the explanation beside the setting and see the table which summarizes effect of the setting on appointment status.', 'wp-base' )).'</p>',
		    // 'position' => array( 'edge' => 'bottom', 'align' => 'top' ),
		// ));

		// $tutorial->add_step(admin_url('admin.php?page=app_settings&tab=login'), $this->a->app_name.'_page_app_settings', 'select[name="login_required"]', __('If You Require Login', 'wp-base'), array(
		    // 'content'  => '<p>' . esc_js(__('You can set whether client is required to log in the website to apply for an appointment. ', 'wp-base' )).'</p>',
		    // 'position' => array( 'edge' => 'top', 'align' => 'top' ),
			// 'modal' => 1,
		// ));
		
		// $tutorial->add_step(admin_url('admin.php?page=app_settings&tab=login'), $this->a->app_name.'_page_app_settings', '.app-login-method', __('Front End Login Methods', 'wp-base'), array(
		    // 'content'  => '<p>' . esc_js(__('WP BASE comes with Google, Facebook, Twitter and WordPress login support, all of which allows client login on the front end without leaving the page. As you certainly know that keeping the client on the same page is important for increasing your conversion ratio.', 'wp-base' )).'</p>',
		    // 'position' => array( 'edge' => 'bottom', 'align' => 'top' ),
			// 'modal' => 1,
		// ));

		// $tutorial->add_step(admin_url('admin.php?page=app_settings'), $this->a->app_name.'_page_app_settings', '.button-primary', __('Save Settings', 'wp-base'), array(
		    // 'content'  => '<p>' . esc_js(__('Do not forget to save your settings.', 'wp-base' )).'</p>',
		    // 'position' => array( 'edge' => 'left', 'align' => 'center' ),
			// 'next_trigger'	=> 'onload',
			// 'req_next_expr'	=> '$(document).find("div.app-nag-saved").length>0', 
		// ));

		$tutorial->add_step(admin_url('admin.php?page=app_display'), $this->a->app_name.'_page_app_display', '#conf-form-fields', __('Requiring Information from Client', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('You may want to ask the client to fill personal info fields. This option lets you accept appointments without login requirement, while collecting necessary data from them. The fields here apply "globally", i.e. for the whole website. But if you need, you can define them per page basis, using shortcode attributes. This subject will be explained in detail in "Look and Feel" tutorial.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'bottom', 'align' => 'center' ),
			'modal' => 1,
		));
		
		// $tutorial->add_step(admin_url('admin.php?page=app_display'), $this->a->app_name.'_page_app_display', '.button-primary', __('Save Settings', 'wp-base'), array(
		    // 'content'  => '<p>' . esc_js(__('Do not forget to save your settings.', 'wp-base' )).'</p>',
		    // 'position' => array( 'edge' => 'left', 'align' => 'center' ),
			// 'next_trigger'	=> 'onload',
			// 'req_next_expr'	=> '$(document).find("div.app-nag-saved").length>0', 
		// ));

		// $tutorial->add_step(admin_url('admin.php?page=app_settings'), $this->a->app_name.'_page_app_settings', '#app_tab_payment', __('Payment Settings', 'wp-base'), array(
		    // 'content'  => '<p>' . esc_js(__('For other settings on this page you can check the help documentation.', 'wp-base' )).
			// '<br/><b>'. esc_js(__('Click Payment tab.','wp-base'))  .'</b></p>',
		    // 'position' => array( 'edge' => 'left', 'align' => 'center' ),
			// 'req_expression' => '$(document).find("#app-gateways-form").length',
			// 'req_warning' => esc_js(__('Please click on PAYMENT tab to continue!','wp-base') ),
			// 'next_trigger'	=> 'onload', // Fires on load of the page
			// 'req_next_expr'	=> '$(document).find("#app-gateways-form").length', 
		// ));

		$tutorial->add_step(admin_url('admin.php?page=app_monetary'), $this->a->app_name.'_page_app_monetary', 'select[name="payment_required"]', __('Do You Require Payment?', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('You can set whether client is asked for a payment to accept their appointment. If this setting is Yes, appointment will be in pending status until a succesful payment is completed, e.g. over PayPal.', 'wp-base' )) .'</p>',
		    'position' => array( 'edge' => 'top', 'align' => 'bottom' ),
			'modal' => 1,
		));

		// $tutorial->add_step(admin_url('admin.php?page=app_monetary'), $this->a->app_name.'_page_app_monetary', '.button-primary', __('Save Settings', 'wp-base'), array(
		    // 'content'  => '<p>' . esc_js(__('Do not forget to save your settings.', 'wp-base' )).'</p>',
		    // 'position' => array( 'edge' => 'left', 'align' => 'center' ),
			// 'next_trigger'	=> 'onload',
			// 'req_next_expr'	=> '$(document).find("div.app-nag-saved").length>0', 
		// ));

		$tutorial->add_step(admin_url('admin.php?page=app_monetary&tab=gateways'), $this->a->app_name.'_page_app_monetary', '#app-payment-form', __('Payment Gateways', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('If you require payment, you must activate at least one %s. If you have more than one gateway activated, client can pick any one of them in a selection pane in front end confirmation form during checkout stage. If there is only one active gateway, that one will be preselected on the front end and selection pane will not be displayed.', 'wp-base' )),
			'<a href="https://en.wikipedia.org/wiki/Payment_gateway" target="_blank" title="'.esc_js(__( 'Click here to view the definition of Payment Gateway on Wikipedia website','wp-base')).'">'.esc_js(__( 'payment gateway','wp-base')).'</a>' ).'</p>',
		    'position' => array( 'edge' => 'top', 'align' => 'left' ),
			'modal' => 1,
		));

		
		$tutorial->add_step(admin_url('admin.php?page=app_monetary&tab=gateways'), $this->a->app_name.'_page_app_monetary', '#app-payment-form', __('End of tutorial', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('In this tutorial he have covered essential settings which are usually sufficient for most of the businesses.', 'wp-base' )).'<br/>'. 
			esc_js(__('In the next tutorial we will show how to manage bookings and how to add manual appointments.', 'wp-base' )).'<br/><b>'.
			'<a href="'.admin_url("admin.php?tutorial=restart3").'" target="_blank" title="'.esc_js(__( 'Clicking this link will start the next tutorial ','wp-base')).'">'.esc_js(__( 'Click here to start the next tutorial','wp-base')).'</a>'  .'</b></p>',
		    'position' => array( 'edge' => '', 'align' => 'center' )
		));
				
		if ( isset( $_GET["tutorial"] ) && 'restart2' == $_GET["tutorial"] )
			$tutorial->restart();
			
		$tutorial->initialize();
		
		return $tutorial;
    }
	
	function tutorial3() {

		$tutorial = new WpB_Pointer_Tutorial('app_tutorial3', 'Adding and Editing Manual Bookings', false);
		$tutorial->set_textdomain = 'wp-base';
		$tutorial->set_capability = WPB_ADMIN_CAP;
		$tutorial->add_icon( $this->a->plugin_url . '/images/large-greyscale.png' );
		$tutorial->set_safe_selector('table.app-manage'); 
		
		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.info-button', __('Appointment List', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('Appointment records are grouped by their statuses. You can see explanation of these statuses by clicking the Info icon.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
		));
		
		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.add-new-h2', __('Adding a New Booking', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('When your clients make appointments using the page we have shown on Quick Start, bookings will be added to this page automatically. But you can always manually add a new booking e.g. when you recieve the request over phone, or email. Actually it is possible to use WP BASE only like this, without the front end part.', 'wp-base' )).'<br/><b>'.esc_js(__('Please click ADD NEW button and then click Next.','wp-base')).'</b></p>',
		    'position' => array( 'edge' => 'top', 'align' => 'bottom' ),
			'req_expression' => '$("select[name=\"user\"]").length',
			'req_warning' => esc_js(__('Please first click ADD NEW button to continue!','wp-base') ),
		));
		
		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.app_users:visible:first', __('Selecting a Client', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('If client is not a registered user of the website, you can select "Not registered user" and enter their data in the fields. If they are registered users, you can pick your clients from this menu. As you select a user, their user data will be auto populated. ', 'wp-base' )).'<br/><b>'.esc_js(__('Please select a user from the list or enter a new client with name and email.','wp-base')).'</b></p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
			'req_expression' => '$(document).find(".app_iedit_email input").first().val()!=""',
			'req_warning' => esc_js(__('Please first SELECT a USER (e.g. yourself) from the dropdown menu to continue!','wp-base') ),

		));

		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', 'input[name="email"]:first', __('Editing Client Data', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('You can overwrite pre populated values. Please note that these user data are only used in WP BASE. For example changing user email here does not affect WordPress email of the user.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.app_iedit_service:first', __('Selecting a Service', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('You can select the service for this booking from the list. As you select a service, its price will be auto populated in the price fields below. These values are taken from %s settings.', 'wp-base' )), '<a href="'.admin_url("admin.php?page=app_business").'" target="_blank" title="'.esc_js(__( 'Click here to view Service Settings','wp-base')).'">'.esc_js(__( 'Services','wp-base')).'</a>' ).'</p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
			
		));

		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.app_iedit_lock:first', __('Locking Price', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('As you change service and provider, price field will automatically adapt itself to the new selection. However, we may not want this behaviour, e.g. for an existing appointment. For this purpose a lock checkbox is provided which prevents accidential overwriting of the price.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
			
		));

		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.app_iedit_price:first', __('Price Field', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('The auto populated price can be overwritten, for example if you are applying a special price to the client for this booking.', 'wp-base' )).'<br/><b>'.esc_js(__('Please enter a price in the price field. Uncheck the "lock" checkbox if it is checked. Note that Total Due and Balance fields change on the fly.','wp-base')).'</b></p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
			'req_expression' => 'parseFloat($(document).find(".app_iedit_price input").first().val())>0',
			'req_warning' => esc_js(__('Please enter a price to continue!','wp-base') ),
		));

		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.app_iedit_deposit:first', __('Deposit Field', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('Deposit is the security amount you normally return to the client after booking has completed. The value here is automatically set to zero by the plugin when appointment has been completed.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.app_iedit_payment:first', __('Payment Field', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('Payment field is a read only field and automatically calculated from the sum of manual or auto payments related to this booking. When there are payments, hovering on this field will display all transactions for this booking.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.app_iedit_balance:first', __('Balance Field', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('Balance field is the total of price and deposit minus sum of all payments. A negative number means client owes to you. ', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', 'select[name="app_balance"]', __('Balance Filter', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('Speaking of which, you can filter the bookings according to their balance. Therefore you can easily follow your receivables.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.app_iedit_date_time:first', __('Start Field', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('Here, you should enter the start date and time of the appointment. As you select a date and time, end date and time will also adjust according to the duration of the service. This behaviour can be changed in %s.', 'wp-base' )), '<a title="'.esc_js(__( 'Click here to view this setting in Advanced Settings page','wp-base') ).'" href="'.admin_url("admin.php?page=app_settings&amp;tab=advanced#strict_check").'" target="_blank">'. esc_js(__( 'Strict Check Setting','wp-base')).'</a>' ).'</p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.app_iedit_date_time:first', __('Start Field', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('You will see that some time values are not selectable (Provided that strict check is selected as Yes). That is because those time values at that date may be not available, because there is already an appointment there or it is not %s.', 'wp-base' )), '<a title="'.esc_js(__( 'Click here to view Working Hours settings','wp-base') ).'" href="'.admin_url("admin.php?page=app_business&amp;tab=working_hours").'" target="_blank">'. esc_js(__( 'working time','wp-base')).'</a>' ).'</p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.app_iedit_admin_note:first', __('Start Field', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('You can use admin notes field to keep track of client habits and let other admins know about them.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.app_iedit_status:first', __('Status Field', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('Status entry defines the current state of this booking. It affects emails sent to the client and the future state of the booking. For example, a confirmed booking becomes "completed" after end time has passed, but a pending booking becomes "removed".', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
		));
		
		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.info-button', __('Statuses', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('Remember that detailed explanation about statuses are revealed by clicking the Info icon.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'middle' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', 'select[name="app_new_status"]', __('Bulk Status Change', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('If you want to change status of more than one booking, you can use Bulk Status Change property.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.app_iedit_status:first', __('Status Field', 'wp-base'), array(
		    'content'  => '<p><b>' . esc_js(__('Now please change the status of this booking to Pending and click Next.', 'wp-base' )).'</b></p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
			'req_expression' => '$(".app-manage").find(".app_iedit_status select").first().val()=="pending"',
			'req_warning' => esc_js(__('Please select status as PENDING to continue!','wp-base') ),
		));
		
		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.app_iedit_send_mail:first', __('Sending emails Manually', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('If you require payment, confirmation email is automatically sent after an automatic payment, e.g. paid via Paypal. However if you are confirming appointments manually, you should check this checkbox for a confirmation email to be sent.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
		));
		
		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.app_iedit_send_mail:first', __('Sending emails Manually', 'wp-base'), array(
		    'content'  => '<p>' . sprintf( esc_js(__('You can also use this option for resending the confirmation email, e.g. after rescheduling an appointment. Please note that only certain checkboxes are enabled, that is, which %s will be used depends on the status of the booking.', 'wp-base' )), '<a title="'.esc_js(__( 'Click here to view confirmation message template as an example.|Also check other templates (Pending, Cancellation, Completed).','wp-base') ).'" href="'.admin_url("admin.php?page=app_settings&amp;tab=email#confirmation-message").'" target="_blank">'. esc_js(__( 'email template','wp-base')).'</a>' ).'</p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.save:first', __('Saving the Record', 'wp-base'), array(
		    'content'  => '<p><b>' . esc_js(__('After you finished all entries, click Save and then click Next.', 'wp-base' )).'</b></p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
			'req_expression' => '$(".app-manage").find("input[name=\"app_id\"]").first().val()>0 || $(".app-manage").find(".error:visible").first().length>0',
			'req_warning' => esc_js(__('Please click SAVE to continue!','wp-base') ),
		));
		
		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', '.cancel:first', __('Result of Save Action', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('The result of save action is shown here. Normally you should get a success message. In some cases you may get an error message, which will indicate the nature of the problem.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
		));
		
		$tutorial->add_step(admin_url('admin.php?page=appointments'), 'toplevel_page_appointments', 'ul.subsubsub li a[href*="pending"]:first', __('Listing Bookings', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('As we added this appointment as "Pending" we will see it under Pending Bookings.', 'wp-base' )).'<br/><b>'.esc_js(__('Click Pending and then click Next.','wp-base')).'</b></p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
			'req_expression' => '$("ul.subsubsub li a[href*=\"pending\"]:first").hasClass("current")',
			'req_warning' => esc_js(__('Please click PENDING link to continue!','wp-base') ),
		));
		
		$tutorial->add_step(admin_url('admin.php?page=appointments&type=pending'), 'toplevel_page_appointments', '.user-inner:first', __('Editing a Booking', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('You can edit any booking record except those generated by Google Calendar Addon. To edit a booking hover on the record and then click Edit.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
			'req_expression' => '$(".cancel:first").length>0',
			'req_warning' => esc_js(__('Please click EDIT link to continue!','wp-base') ),
		));
		
		$tutorial->add_step(admin_url('admin.php?page=appointments&type=pending'), 'toplevel_page_appointments', '.cancel:first', __('Make Changes', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('You can make changes on an existing booking record as before or just view the details and then Cancel. Please note that changes you made are NOT saved until you click the Save button.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'left', 'align' => 'center' ),
			
		));
		
		$tutorial->add_step(admin_url('admin.php?page=appointments&type=pending'), 'toplevel_page_appointments', '#app-search-form', __('Search', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('As you start to have many bookings, it may be difficult to locate the record of an existing one. Search function is useful for this purpose. It lets you search in different fields, for example, appointment date, name, email, phone of the client, or a special keyword you entered in admin notes.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'top', 'align' => 'bottom' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=appointments&type=pending'), 'toplevel_page_appointments', '#app-search-form', __('Search', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('A useful search function is searching over multiple bookings where you know their IDs and you want to compare them. Enter IDs seperated by comma or space and select App ID. Then those bookings will be selected and displayed on the same page.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'top', 'align' => 'bottom' ),
		));

		$tutorial->add_step(admin_url('admin.php?page=appointments&type=pending'), 'toplevel_page_appointments', 'select[name="app_m"]', __('Filters', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('You can also use filter and sort functions. You can filter for a certain month or week, for a certain service, for a certain provider and combinations of them. Your selections are saved as your preference until you clean them using the Reset button.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => 'right', 'align' => 'center' ),
		));

		// $tutorial->add_step(admin_url('admin.php?page=appointments&type=pending'), 'toplevel_page_appointments', '.DTTT_container', __('TableTools Buttons', 'wp-base'), array(
		    // 'content'  => '<p>' . esc_js(__('After you filter the bookings, using these buttons you can export the selected bookings to Excel or copy to clipboard, or print them.', 'wp-base' )).'</p>',
		    // 'position' => array( 'edge' => 'left', 'align' => 'center' ),
		// ));

		// $tutorial->add_step(admin_url('admin.php?page=appointments&type=pending'), 'toplevel_page_appointments', '.app-csv-gprnt', __('Export Bookings and End of Tutorial', 'wp-base'), array(
		    // 'content'  => '<p>' . esc_js(__('To export complete set of bookings, we recommend to use the download CSV file feature of Export/Import addon.', 'wp-base' )).'<br/><b>'.esc_js(__('End of the tutorial.', 'wp-base' )).'</b></p>',
		    // 'position' => array( 'edge' => 'bottom', 'align' => 'center' ),
		// ));
		$tutorial->add_step(admin_url('admin.php?page=appointments&type=pending'), 'toplevel_page_appointments', 'select[name="app_m"]', __('End of tutorial', 'wp-base'), array(
		    'content'  => '<p>' . esc_js(__('Our tutorials end here. If you have questions use forums in wordpress.org or if you have a premium addon, contact us using the Contact tab.', 'wp-base' )).'</p>',
		    'position' => array( 'edge' => '', 'align' => 'center' )
		));
		
		if ( isset( $_GET["tutorial"] ) && 'restart3' == $_GET["tutorial"] )
			$tutorial->restart();
			
		$tutorial->initialize();
		
		return $tutorial;
	}

	/*******************************
	* Support Email
	********************************
	*/
	
	/**
     * send support email
     */
	function send_support_email() {
		if ( !isset($_POST['app_support_email']) )
			return;
		
		foreach( array( 'app_support_client_email', 'app_support_subject', 'app_support_content' ) as $req ) {
			if( !isset( $_POST[$req] ) || !trim( $_POST[$req] ) ) {
				$this->support_form_error = 'field_missing';
				return;
			}
		}
		
		if ( !is_email( $_POST['app_support_client_email'] ) ) {
				$this->support_form_error = 'wrong_email';
				return;
		}
		
		if ( stripos( $_POST['app_support_subject'], 'license problem' ) === false ) {
			$found = false;
			foreach ( get_plugins( ) as $path=>$info ){
				if ( 'WP BASE Addon:' == substr( $info['Name'], 0, 14 ) ) {
					$data = get_file_data( WP_PLUGIN_DIR.'/'.$path, wpb_default_headers(), 'plugin' );
					$stat = !empty( $data['ID'] ) ? (string)get_option( 'wpb_license_status_' . $data['ID'] ) : '';
					if ( 'valid' === $stat ) {
						$found = true;
						break;
					}
				}
			}
			if ( !$found ) {
				$this->support_form_error = 'no_valid_license';
				return;
			}
		}
		
		$attach = array();
		$blog_name = sanitize_file_name( get_bloginfo( 'name' ) );
		$filename_zip = $this->a->uploads_dir . "/wp_base_all_". $blog_name. "_". date('F')."_".date('d')."_".date('Y').".zip";
		
		if ( !empty( $_POST['app_support_include'] ) && BASE('EXIM') ) {

			if ( class_exists( 'ZipArchive' ) ) {
				$zip = new ZipArchive();

				// Delete previous zip file if it exists
				if ( file_exists( $filename_zip ) ) 
					unlink( $filename_zip );
				
				
				if ( $zip->open($filename_zip, ZIPARCHIVE::CREATE) === true ) {
					if ( $filename_db = BASE('EXIM')->export_db( true, true ) ) { // Exclude transaction table
						if ( file_exists( $this->a->uploads_dir . "/". $filename_db ) ) {
							$zip->addFile( $this->a->uploads_dir . "/". $filename_db, $filename_db );
						}
					}
					if ( $filename_set = BASE('EXIM')->export_settings( true ) ) {
						if ( file_exists( $this->a->uploads_dir . "/". $filename_set ) ) {
							$zip->addFile( $this->a->uploads_dir . "/". $filename_set, $filename_set );
						}
					}
					
					$attach[] = $filename_zip;
				}
				
				// Compress and save changes
				$zip->close();
			}
			else {
				if ( $filename_db = BASE('EXIM')->export_db( true, true ) ) { // Exclude transaction table
					if ( file_exists( $this->a->uploads_dir . "/". $filename_db ) ) {
						$attach[] = $this->a->uploads_dir . "/". $filename_db;
					}
				}
				if ( $filename_set = BASE('EXIM')->export_settings( true ) ) {
					if ( file_exists( $this->a->uploads_dir . "/". $filename_set ) ) {
						$attach[] = $this->a->uploads_dir . "/". $filename_set;
					}
				}
			}
		}
		
		global $wp_version, $wp_db_version;
	
		$c = $_POST['app_support_content'];
		$c .= "\r\n";
		$c .= "-------------------------------------------\r\n";
		$c .= "\r\n";
		$c .= __('email:','wp-base');
		$c .= " " . $_POST['app_support_client_email'];
		$c .= "\r\n";
		$c .= __('URL:','wp-base');
		$c .= " " . get_bloginfo('url');
		$c .= "\r\n";
		$c .= __('WP BASE Version:','wp-base');
		$c .= " " . $this->a->version;
		$c .= "\r\n";
		$c .= __('WordPress Version:','wp-base');
		$c .= " " . $wp_version;
		$c .= "\r\n";
		$c .= __('WordPress Database Version:','wp-base');
		$c .= " " . $wp_db_version;
		$c .= "\r\n";
		$c .= __('PHP Version:','wp-base');
		$c .= " " . PHP_VERSION;
		$c .= "\r\n";
		$c .= __('PHP Max Input Variables:','wp-base');
		$c .= " " . ini_get( 'max_input_vars' );		
		$c .= "\r\n";
		$c .= __('Date/time format:','wp-base');
		$c .= " " . $this->a->dt_format;
		$c .= "\r\n";
		$c .= __('Week starts on:','wp-base');
		$c .= " " . jddayofweek( $this->a->start_of_week, 1 );
		$c .= "\r\n";
		$c .= __('Local time:','wp-base');
		$c .= " " . date( 'Y-m-d H:i:s', $this->a->_time );
		$c .= "\r\n";
		$c .= __('GMT time:','wp-base');
		$c .= " " . date( 'Y-m-d H:i:s');
		$c .= "\r\n";
		$c .= __('Timezone offset:','wp-base');
		$c .= " " . get_option('gmt_offset');
		$c .= "\r\n";
		$c .= __('Timezone string:','wp-base');
		$c .= " " . get_option('timezone_string');
		$c .= "\r\n";
		$c .= __('Locale:','wp-base');
		$c .= " " . $this->a->get_locale();
		$c .= "\r\n";
		$c .= "\r\n";
		
		if ( BASE('Addons') ) {
			$c .= __('Active Addons:', 'wp-base' ) . "\r\n";
			foreach ( BASE('Addons')->get_all_addons( ) as $plugin ){
				if ( BASE('Addons')->is_active( $plugin ) ) {
					$info = BASE('Addons')->get_addon_info( $plugin );
					$c .= str_replace( 'WP BASE Addon: ', '', $info['Name'] ) . " ". $info['Version'] . "\r\n";
				}
			}
		}
		
		$c .= __('Addons as WP Plugin:', 'wp-base' ) . "\r\n";
		foreach ( get_plugins( ) as $path=>$info ){
			if ( 'WP BASE Addon:' == substr( $info['Name'], 0, 14 ) ) {
				$data = get_file_data( WP_PLUGIN_DIR.'/'.$path, wpb_default_headers(), 'plugin' );
				$license = !empty( $data['ID'] ) ? (string)get_option( 'wpb_license_key_' . $data['ID'] ) : '';
				
				$c .= str_replace( 'WP BASE Addon: ', '', $info['Name'] ) . " ". $info['Version'] . " ". $license ."\r\n";
			}
		}
		
		if ( 'yes' == wpb_setting('use_html') )
			$c = str_replace( "\r\n", "<br />", $c );
		
		$to = array( self::support_email );
		if ( isset ( $_POST['app_support_copy'] ) )
			$to[] = $_POST['app_support_client_email'];

		if ( wp_mail( $to, $_POST['app_support_subject'], $c, $this->a->message_headers(), $attach ) ) {
			add_action( 'admin_notices', array( $this, 'support_email_success' ) );
			unset( $_POST['app_support_client_email'] );
			unset( $_POST['app_support_content'] );
			unset( $_POST['app_support_subject'] );
		}
		else
			add_action( 'admin_notices', array( $this, 'support_email_fail' ) );

		// Delete zip file
		if ( file_exists( $filename_zip ) ) 
			unlink( $filename_zip );
	}

	/**
	 * Prints error message on top of Admin page 
	 */
	function support_form_error( ) {
		if ( empty( $this->support_form_error ) )
			return;
		
		$error = $this->support_form_error;
		
		if ( 'field_missing' === $error )
			echo '<div class="error"><p><b>[WP BASE]</b> '. __('Please fill in the required field.','wp-base').'</p></div>';
		else if ( 'wrong_email' === $error )
			echo '<div class="error"><p><b>[WP BASE]</b> '. __('Submitted email is not correct.','wp-base').'</p></div>';
		else if ( 'no_valid_license' === $error )
			echo '<div class="error"><p><b>[WP BASE]</b> '. sprintf( __('We are sorry, but it looks like you do not have any active licenses. If your issue is about activating a license, please write "License Problem" in the email subject field and try again. If you do not have a license, use wordpress.org forums for technical issues. For other requests please use %s.', 'wp-base' ), '<a target="_blank" href="'.WPB_URL.'contact-us/">'.__('our contact form','wp-base').'</a>').'</p></div>';
		else
			echo '<div class="error"><p><b>[WP BASE]</b> '. __('An error occurred. Please try again','wp-base').'</p></div>';
	}

	/**
	 * Prints "Support email successful" message on top of Admin page 
	 */
	function support_email_success( ) {
		echo '<div class="updated fade"><p><b>[WP BASE]</b> '. __('Your support request email has been successfully sent.','wp-base').'</p></div>';
	}

	/**
	 * Prints "Support email failed" message on top of Admin page 
	 */
	function support_email_fail( ) {
		echo '<div class="error"><p><b>[WP BASE]</b> '. __('Sending the support request email has failed. Please check if you can actually send any emails from this website. For example, logout and try to receive an email using "Lost your password?" link of WordPress admin login page.','wp-base').'</p></div>';
	}

	/**
     * Add submenu page to main admin menu
     */
	function add_submenu(){
		add_submenu_page('appointments', __('WPB Help','wp-base'), __('Help','wp-base'), WPB_ADMIN_CAP, "app_help", array($this,'help'));
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
	 * Help tabs 
	 * @param echo: If true send to browser. If false return the html.
	 */
	function help( $echo=true ) {
	
		?>
		<div class="wrap app-help-page">
		<h2 class="app-dashicons-before dashicons-welcome-learn-more"><?php echo __('Help','wp-base'); ?></h2>
		<h3 class="nav-tab-wrapper">
			<?php
			$tab = ( !empty($_GET['tab']) ) ? $_GET['tab'] : 'main';
			
			$tabs = array(
				// 'business'    	=> __('Business Settings', 'wp-base'),
				'settings'    	=> __('Global Settings', 'wp-base'),
				'shortcodes'    => __('Shortcodes', 'wp-base'),
				'support'    	=> __('Support', 'wp-base'),
				'about'    		=> __('About', 'wp-base')
			);
			
			$tabhtml = array();

			$class = ( 'main' == $tab ) ? ' nav-tab-active' : '';
			$tabhtml[] = '	<a href="' . admin_url( 'admin.php?page=app_help' ) . '" class="nav-tab'.$class.'">' . __('General', 'wp-base') . '</a>';

			foreach ( $tabs as $stub => $title ) {
				$class = ( $stub == $tab ) ? ' nav-tab-active' : '';
				$tabhtml[] = '	<a href="' . admin_url( 'admin.php?page=app_help&amp;tab=' . $stub ) . '" class="nav-tab'.$class.'" id="app_tab_'.$stub.'">'.$title.'</a>';
			}

			echo implode( "\n", $tabhtml );
			?>
		</h3>
		<div class="clear"></div>
		<?php switch( $tab ) {
		
		case 'main':	?>
			<div id="poststuff" class="metabox-holder">
				<div class="postbox">
				<div class="inside">
					<?php $this->general_help(); ?>
				</div>
				</div>
				
				<?php if ( BASE('PDF') ) { ?>
				<form action="<?php echo admin_url('admin-ajax.php?action=app_create_manual'); ?>" method="post" style="float:left;margin-top:5px; width:200px;">
					<input type="hidden" name="action" value="app_create_manual" />
					<input type="submit" class="button-secondary" value="<?php _e('Create Pdf Manual','wp-base') ?>" title="<?php _e('If you click this button a pdf help file will be saved on your PC.','wp-base') ?>" />
				</form>
				<?php } ?>
			</div>
			
		<?php break;	
		
		case 'business':	?>
			<div id="poststuff" class="metabox-holder">
				<div class="postbox">
				<div class="inside">
					<?php $this->business_help(); ?>		
				</div>
				</div>
			</div>
		<?php break;

		case 'settings':	?>
			<div id="poststuff" class="metabox-holder">
			<?php wpb_infobox( __('Help texts here are identical to the texts beside each setting on several setting pages. They are collected here just for convenience. Addon which uses that setting is also shown. You can sort columns and search for a term.','wp-base') ) ?>
				<div class="postbox">
				<div class="inside">
					<?php $this->settings_help(); ?>		
				</div>
				</div>
			</div>
		<?php break;
		
		case 'shortcodes':	?>
			<div id="poststuff" class="metabox-holder">
			<?php wpb_infobox( __( 'WP BASE uses shortcodes to generate output on the front end. This gives you the flexibility to customize your appointment pages using the WordPress post editor without the need for php coding. There are several parameters of the shortcodes by which your customizations can be highly flexible. On the other hand, if you don\'t set these parameters, WP BASE will still function properly. Thus, setting parameters is fully optional and most of them are about the look of the front end.', 'wp-base' ) ); ?>
				<div class="postbox app_shortcodes_help">
				<div class="inside">
					<?php $this->shortcodes_help(); ?>
				</div>
				</div>
			</div>
		<?php break;
		
		case 'addons':	

		?>
			<div id="poststuff" class="metabox-holder">
				<div class="postbox">
				<div class="inside">
					<script type="text/javascript">
					  jQuery(document).ready(function ($) {
						  var head = $("div#tabs").find("h2");
						  $.each( head, function(i,v) {
							var head_text = $(this).text();
							$(this).parent("div").attr("id", "tabs-"+i );
							$("div#tabs ul").first().append('<li><a href="#tabs-'+i+'">'+head_text+'</a></li>');
						  });
						$("div#tabs").tabs().removeClass("ui-widget");
					  });
					</script>
					<?php
						$desc = WpBConstant::addon_desc();
						echo $desc[0];
					?>
				
					<div id="tabs">
						<ul></ul>
					<?php $this->addons_help(); ?>
					</div>
				</div>
				</div>
			</div>
		<?php break;

		case 'faq':	?>
			<div id="poststuff" class="metabox-holder">
				<div class="postbox app_wrap">
					<div class="app_faq_wrap" id="app_faq_wrap"></div>
					<script type="text/javascript">
					jQuery(document).ready(function($){
						var title_save = '';
						$('ul li b').each(function(){
							var n = $(this).closest('ul').attr('id');
							$('.app_faq_wrap').append( '<a href="#'+n+'">' + $(this).html() + '</b></a><br />');
							var first_ul = $(this).closest('ul').after( '<a href="#app_faq_wrap">Go to Top</b></a>');
							var title = $(this).parents('div').first().find('h2').html();
							if ( title_save != title ) {
								$("a[href='#"+n+"']").before('<h4>'+title+'</h4>');
								title_save = title;
							}
						});
						$('.app_wrap ul').css('position','relative').css('padding-top','20px').css('font-size','14px');
						$('.app_wrap ul ul').css('list-style-type','square');
						$('.app_wrap ul ul li').css('margin-left','15px');
						$('#app_faq_wrap a').css('line-height','2em');
					});
					</script>

					<div class="postbox">
					<div class="inside">
					<?php $this->faq_help(); ?>
					</div>
					</div>

				</div>
			</div>
		<?php break;
		
		case 'support':

		?>
			<div id="poststuff" class="metabox-holder">
			<?php wpb_infobox( sprintf( __( 'If you have a valid license for one of our %1$s, you can use this form to send an email about the issue to our support team. If you do not have a license please use wordpress.org forums for technical issues and %2$s for other requests.', 'wp-base' ), '<a target="_blank" href="'.WPB_URL.'addons/">'.__('Premium Addons','wp-base').'</a>', '<a target="_blank" href="'.WPB_URL.'contact-us/">'.__('our contact form','wp-base').'</a>' ) ) ?>
				<form id="app_support_request_form" class="app_form" method="post" action="<?php echo wpb_add_query_arg(null, null) ?>">
				<div class="postbox">
				<h3 class="hndle"><span><?php _e('Support Request Form', 'wp-base') ?></span></h3>
				<div class="inside">
					
					<table class="form-table">

					<tr id="app_support_client_email">
						<th scope="row" ><?php _e('Your email', 'wp-base') ?></th>
						<td>
						<input type="text" name="app_support_client_email" size="90" value="<?php if (isset($_POST['app_support_client_email'])) echo $_POST['app_support_client_email']; else echo BASE('User')->get_admin_email( true ); ?>" />
						</td>
					</tr>
					<tr id="app_support_subject">
						<th scope="row" ><?php _e('Subject', 'wp-base') ?></th>
						<td>
						<input type="text" name="app_support_subject" size="90" value="<?php if (isset($_POST['app_support_subject'])) echo $_POST['app_support_subject']; else printf( __('Support Request from %s','wp-base'), wp_specialchars_decode( get_option('blogname'), ENT_QUOTES ) ); ?>" />
						</td>
					</tr>
					<tr id="app_support_content">
						<th scope="row" ><?php _e('Your Message', 'wp-base') ?></th>
						<td>
						<textarea style="width:800px;height:100px" name="app_support_content" ><?php if (isset($_POST['app_support_content'])) echo $_POST['app_support_content'];?></textarea>
						</td>
					</tr>

					<?php if ( BASE('EXIM') ) { ?>
					<tr>
						<td colspan="2">
						<input id="app_support_include" name="app_support_include" value="1" type="checkbox" checked="checked"/>&nbsp;
						<?php _e("Automatically attach current settings and plugin related database tables, plus data of the users who are related to the plugin (service providers and clients).",'wp-base') ?>
						</td>
					</tr>
					
					<?php } ?>

					<tr>
						<td colspan="2">
						<input id="app_support_copy" name="app_support_copy" value="1" type="checkbox" <?php checked( isset($_POST['app_support_copy']),1, true ) ?> />&nbsp;
						<?php _e("Click to receive a copy of this form submission to your email.",'wp-base') ?>
						</td>
					</tr>

					</table>
				
					
				</div>
				</div>
					<input name="app_support_email" type="hidden" />
					<input id="app_support_button" type="submit" class="button-primary" value="<?php _e('Send Support Request Email','wp-base') ?>" />
				</form>
			</div>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			$('#app_support_button').click(function(e) {
				if ( $('#app_support_include').is(':checked') ) {
					e.preventDefault();
					if ( !confirm('<?php echo esc_js( __("Together with your message, your current settings and plugin related database tables, plus data of the posts and users who are related to the plugin (service providers and clients) will also be attached. Transaction information will NEVER be included. All of your data will be kept confidential. Are you sure to continue?",'wp-base') ) ?>') ) {return false;}
					else{
						var form = $('#app_support_request_form');
						var focus = '';
						if ( !$.trim( form.find("#app_support_content textarea").val() ) ) {
							focus = '#app_support_content';
						}
						else if ( !$.trim( form.find("#app_support_subject input").val() ) ) {
							focus = '#app_support_subject';
						}
						else if ( !$.trim( form.find("#app_support_client_email input").val() ) ) {
							focus = '#app_support_client_email';
						}
						var cur_action = form.attr("action");
						var index = cur_action.indexOf("#");
						if (index > 0) {
							cur_action = cur_action.substring(0, index);
						}
						form.attr("action",cur_action+focus);						
						form.submit();
					}
				}
			});
			$('textarea:visible').autosize();
		});
		</script>
		<?php
		break;

		case 'about':	
			global $wp_version, $wp_db_version;
		?>
			<div id="poststuff" class="metabox-holder">
				<div class="postbox">
					<div class="inside">
					<h3><?php _e( 'VERSION INFO', 'wp-base') ?></h3>
						<p>
						<?php
						_e('PHP Version:','wp-base');
						echo " " . PHP_VERSION;
						?>
						</p>
						<p>
						<?php
						_e('PHP Max Input Variables:','wp-base');
						echo " " . ini_get( 'max_input_vars' );
						?>
						</p>
						<p>
						<?php
						_e('WP BASE Version:','wp-base');
						echo " " . $this->a->version;
						?>
						</p>
						<p>
						<?php
						_e('WordPress Version:','wp-base');
						echo " " . $wp_version;
						?>
						</p>
						<p>
						<?php
						_e('WordPress Database Version:','wp-base');
						echo " " . $wp_db_version;
						?>
						</p>
						<p>
						<?php
						_e('Latest WP BASE DB Version:','wp-base');
						echo " " . WPB_LATEST_DB_VERSION;
						?>
						</p>
						<p>
						<?php
						_e('Installed WP BASE DB Version:','wp-base');
						echo " " . get_option( 'wp_base_db_version' );
						?>
						</p>
						<?php
						if ( BASE('Addons') ) {
							echo "<p><b>". __('Installed Addons:', 'wp-base' ) . "</b></p>";

							foreach ( BASE('Addons')->get_all_addons( ) as $plugin ){
								$info = BASE('Addons')->get_addon_info( $plugin );
								$active = BASE('Addons')->is_active( $plugin ) ? __('(Active)','wp-base') : __('(Inactive)','wp-base');
								echo str_replace( 'WP BASE Addon: ', '', $info['Name'] ) . " ". $info['Version'] . ' '. $active. "<br />";
							}
						}
						
						echo "<p><b>". __('Addons as WP Plugin:', 'wp-base' ) . "</b></p>";
						foreach ( get_plugins( ) as $path=>$info ){
							if ( 'WP BASE Addon:' == substr( $info['Name'], 0, 14 ) ) {
								$active = is_plugin_active( $path ) ? __('(Active)','wp-base') : __('(Inactive)','wp-base');
								echo str_replace( 'WP BASE Addon: ', '', $info['Name'] ) . " ". $info['Version'] . ' '. $active. "<br />";
							}
						}
						?>
						
					</div>
				</div>
			</div>
		<?php break;

		} // End of switch
	}
	
	/**
	 * General Help Content
	 */	
	function general_help( $return = false ) {
		ob_start();
		?>
			<h3><?php _e( 'INTRODUCTION', 'wp-base') ?></h3>
			
			<p><span class="description">
			<?php _e('Booking of Appointments, Services and Events WordPress plugin (in short, WP BASE) is a complete solution to accept appointments and schedule based bookings on your WordPress website. It is created as a WordPress plugin starting from the development stage, therefore its codes are optimized for WordPress.','wp-base') ?>
			</span></p>
			
			<p><span class="description">
			<?php _e('With WP BASE, your clients can make an appointment based on your available times, if set so, pay full price or deposit of the cost, edit and cancel their appointment by themselves without the need for your intervention. You and/or your employees can also add a new appointment or make corrections on an existing one.','wp-base') ?>
			</span></p>
			
			<p><span class="description">
			<?php _e('WP BASE has been developed to cover all functions related to make an appointment from a person in the first place, however, in some cases it can also be used to book resources (technical material, equipment, etc), locations (meeting or function rooms, etc), vehicles with driver (hourly or daily tour, airport transfer, etc), or shared facilities (Tennis court, barbeque pit, etc).','wp-base') ?>
			</span></p>

			<p><span class="description">
			<?php printf( __('On our %s you can view some of the possible applications.','wp-base'), '<a href="'.WPB_DEMO_WEBSITE.'" target="_blank">'.__('demo website','wp-base').'</a>' ) ?>
			</span></p>

			<h3><?php _e( 'ADDONS', 'wp-base') ?></h3>

			<p><span class="description">
			<?php printf( __('While covering many needs of the businesses, WP BASE functionality can be further extended and expanded using %s. Addons are WordPress plugins tailored to cover needs for certain applications. There are several Free and Premium addons which makes WP BASE a complete ecosystem.','wp-base'), '<a href="'.WPB_URL.'addons/" target="_blank">'.__('Addons','wp-base').'</a>' ) ?>
			</span></p>

			<p><span class="description">
			<?php printf( __('Usage examples can be seen on our %s','wp-base'), '<a href="'.WPB_ADDON_DEMO_WEBSITE.'" target="_blank">'.__('addons demo website','wp-base').'</a>' ) ?>
			</span></p>

			<h3><?php _e( 'FURTHER HELP', 'wp-base') ?></h3>

			<p><span class="description">
			<?php printf( __('Documentation over various aspects of WP BASE and addons can be found on %s','wp-base'), '<a href="'.WPB_URL.'knowledge-base/" target="_blank">'.__('Knowledge Base','wp-base').'</a>' ) ?>
			</span></p>

			<p><span class="description">
			<?php printf( __('If you own a valid license, you can send an email to our support team using %s','wp-base'), '<a href="'.admin_url('admin.php?page=app_help&tab=support').'" target="_blank">'.__('WP BASE support form','wp-base').'</a>' ) ?>
			</span></p>

			<p><span class="description">
			<?php printf( __('You can open a support topic on %s','wp-base'), '<a href="https://wordpress.org/support/plugin/wp-base-booking-of-appointments-services-and-events/" target="_blank">'.__('WordPress Community forums','wp-base').'</a>' ) ?>
			</span></p>

			<h3><?php _e( 'FOR THE FIRST TIME USER', 'wp-base') ?></h3>

			<p><span class="description">
			<?php _e('Being a complete solution and largely customizable plugin, setting up WP BASE may seem difficult on the first look, but for basic applications it is quite straightforward. If you are a first time user, we recommend that you follow the tutorials below, after which you will not only have a fully functional set up, but you will also be equipped with enough information to manage the bookings and set up. Tutorials have references to these help and setting pages when more detailed explanation looks to be beneficial.','wp-base') ?>
			</span></p>
			
			<h4><?php _e( 'Tutorials', 'wp-base') ?></h4>
			
			<ol  id="tutorials" style="padding-left:30px;">
				<li><?php	$link = wpb_add_query_arg( array( 'tutorial'=>'restart1' ), admin_url("admin.php?page=app_settings") ); ?>
					<a href="<?php echo $link ?>" ><?php _e( '<b>Quick Start:</b> Creating a Functional Make an Appointment Page', 'wp-base' ) ?></a>
				</li>
				<li><?php	$link = wpb_add_query_arg( array( 'tutorial'=>'restart2' ), admin_url("admin.php?page=app_settings") ); ?>
					<a href="<?php echo $link ?>" ><?php _e( '<b>Essential Settings:</b> Defining Services, Working Hours and Important Global Settings', 'wp-base' ) ?></a>
				</li>
			
				<li><?php	$link = wpb_add_query_arg( array( 'tutorial'=>'restart3' ), admin_url("admin.php?page=appointments") ); ?>
					<a href="<?php echo $link ?>" ><?php _e( '<b>Managing Bookings:</b> Adding a New Appointment and Editing Existing Ones', 'wp-base' ) ?></a>
				</li>
			</ol>
			
			<h5><?php _e( 'Notes on Tutorials', 'wp-base') ?></h5>
			<ul id="tutorial-notes" style="padding-left:30px;">
				<li><?php printf( __('The step-by-step interactive tutorials consist of several steps which may include references to other admin pages or external resources, e.g. Wikipedia. These references are indicated with an %s and a tooltip will summarize the function of the connected link.','wp-base'), 
				$this->_a( $this->_t('underline'),'', $this->_t('This link is provided as an example and clicking it will not do anything.') ) ) ?>
				</li>
				<li><?php  _e('It is recommended, but not required, to run the Quick Start tutorial using one of the WordPress default themes. This will help you identify the theme issue, if there is a one in your theme and prevent unexpected behaviour of the tutorial pop-up if there are extraordinary javascript controls in your theme.', 'wp-base') ?>
				</li>
				<li><?php  _e('It is recommended to run the tutorials in the order as presented above. Otherwise you may find yourself unfamiliar with a term or function which has been explained or referenced in a previous tutorial.', 'wp-base') ?>
				</li>
				<li><?php  _e('You can close the window on which tutorial is running even if tutorial has not completed yet. The step you were on will be saved and when you visit the same page again, tutorial will resume from the saved step. This applies to every single user; for every one of them tutorial will continue from the page and step they have left.', 'wp-base') ?>
				</li>
				<li><?php  _e('If you want to cancel the tutorial without resuming later on, click the "Dismiss" link on top right of the pop-up.', 'wp-base') ?>
				</li>
				<li><?php  _e('Any tutorial can be restarted using the links above.', 'wp-base') ?>
				</li>
				<li><?php  _e('Some steps may require your interaction, for example filling a field, selecting an option, clicking a button. <b>Such requests are shown as bold</b> and you will not be able to proceed to the next step without fulfilling the request. Instead, when you click the "Next" button you will see a warning message.', 'wp-base') ?>
				</li>
				<li><?php  _e('You are expected to click the "Next" button on the tutorial pop-up to proceed to the next step, except when you have already done an action you are requested, for example clicking a save button.', 'wp-base') ?>
				</li>
				<li><?php  _e('You can return to the previous step by clicking the "Previous" button.', 'wp-base') ?>
				</li>
			</ul>

			<h3><?php _e( 'DEFINITIONS', 'wp-base') ?></h3>
			<p><span class="description">
			<?php _e('These are some of the terminology used throughout WP BASE:','wp-base') ?>
			</span></p>
			
			<p><span class="description">
			<?php _e('<b>Admin:</b> A user who has the WordPress "manage_options" capability. More than one admins are allowed in WP BASE; e.g. each of them will get notification email messages.','wp-base') ?>
			</span></p>

			<p><span class="description">
			<?php _e('<b>Location:</b> settings can be used a) to show the address of the service when they are served at more than one place b) to group and categorise services especially when there are numerous services.','wp-base') ?>
			</span></p>

			<p id="service"><span class="description">
			<?php _e('<b>Service:</b> The action of doing help or work for the client. WP BASE is basically about booking of services with or without nominated service providers and with or without a predetermined price. Services can be hourly or daily, free or paid, may be requiring down payment or not. Hourly services can be from 10 minutes up to 24 hours. Services longer than 24 hours is not currently supported.','wp-base') ?>
			</span></p>
			
			<p id="service-provider"><span class="description">
			<?php printf( __('<b>Provider:</b> The person who is providing the service client is applying and booking for. A provider may give a single or multiple services. Providers are optional, i.e. you can be the sole provider giving the services. If no service providers are defined, WP BASE assumes that there is a single provider giving all of the services. This sole provider can be selected using %s setting. Only members of the website can be assigned as providers.','wp-base'), '<a href="'.admin_url('admin.php?page=app_settings&tab=login#default-worker').'">'. WpBConstant::get_setting_name( 'default_worker' ) .'</a>' ) ?>
			</span></p>

			<p id="dummy"><span class="description"> 
			<?php _e('<b>Dummy Service Provider:</b> is a "fake" service provider for whom emails will be forwarded to another (real) user, selected on Advanced Settings page. You can use dummy option to enrich your service provider alternatives and variate your working schedules. For example, suppose that you are giving Service A only on weekdays, and Service B only on weekends. Then you can define Provider 1 to give Service A working on weekdays, and Provider 2 to give Service B working on weekends. Note that using dummies is not a direct alternative of setting service capacity. Both options have its advantages and different area of application.','wp-base') ?>
			</span></p>

			<p><span class="description">
			<?php _e('<b>Working Hours:</b> Define in which hours and days service providers are available and  as a result, in which hours and days services are bookable. Working hours are related to service providers with the exception of "Unassigned Provider" which defines business working hours. Service providers\' working hours can be outside working hours of the business.','wp-base') ?>
			</span></p>

			<p><span class="description">
			<?php _e('<b>Security Deposit:</b> Deposit is, by definition, a sum of money or equivalent given as security for an item acquired for temporary use. For example if you are renting some facilities and these facilities include some items that can be damaged by the client, you can ask a deposit in advance and refund it after client return the facility and you check and see that there was no harm. Since deposits are almost always delivered as manual payments (cash, check, or other valuables) and kept in special accounts, deposit amount is NOT added to the one that client pays via online payment gateways, e.g. Paypal. However, they are added to the total due and affect the balance of the booking. ','wp-base') ?>
			</span></p>
			
			<p><span class="description">
			<?php _e('<b>Down Payment:</b> Down Payment is a percentage or portion of the service price and it is again for security, but in this case, to cover any indirect losses in case client cancels the booking or does not show up at all. When payment is required to confirm a booking, this is the amount that client needs to pay through online payment gateways.','wp-base') ?>
			</span></p>

			<p><span class="description">
			<?php _e('<b>Status:</b> is the latest state of an appointment. Statuses can be automatically (for example a pending appointment automatically becomes "removed" after a preset time) or manually changed, i.e. using admin side Appointments page or front end page having Manage Bookings Addon shortcode. These are statuses used in WP BASE:','wp-base') ?>
			</span></p>
		

			<ul style="list-style-type:disc;padding-left:30px;">
			<?php
			foreach ( WpBConstant::app_desc() as $key=>$line ) {
				echo "<li>". $line . "</li>";
			}
			?>
			</ul>

			<p id="time-slot"><span class="description">
			<?php _e('<b>Time slot:</b> An interval of time with an exact start and end value which defines the appointment start and end date/time. Unless it is a break time or holiday, each time slot can turn into an appointment, when booked. WP BASE produces time slots based on duration of the service to be booked and working hours of the service provider. For example, for a service of one hour duration and a service provider working from 9 to 5 and having a break between 12pm-1pm, time slots will be: 9am, 10am, 11am, 1pm, 2pm, 3pm, 4pm. Time slots can be presented on the front end in list view, monthly calendar view or weekly calendar view, depending on the shortcode used.','wp-base') ?>
			</span></p>
			
			<p id="capacity"><span class="description">
			<?php _e('<b>Service Capacity:</b> is the number of clients that can be served at a given time slot. Normally service capacity is determined by the number of service providers. For example if you employ 4 hairdressers for a hairstyling saloon, you have a capacity of 4 for hair cut service, for any time slot that these providers are working. This means any time slot can be booked 4 times by different clients. There are cases, however, that you need to increase or decrease the service capacity. You can do this by entering the required number inside the capacity field of the related service. For example, if you are organizing city tours, and you have a minibus for 10 passengers, you can simply enter 10 as the capacity value. Then 10 different clients can book for the city tour. Or, as an example of decreasing capacity, suppose there are 3 dentists in a dental clinic having 2 examination rooms. Then service capacity has to be set to 2 although there are 3 dentists who can use it. If some resources are limiting your services like in this case, you can set the capacity according to the number of resources.','wp-base') ?>
			</span></p>
			
			<p id="location-capacity"><span class="description">
			<?php _e('<b>Location Capacity:</b> is the number of services that can be given at a given time slot for the selected location. Contrary to service capacity, it cannot be increased. Usage example: A "Hairdresser" location has "Gents\' Haircut" and "Ladies\' Haircut" services. There are 7 hairdressers who can work in both of the services (or some can work only in one of the services, which will not make any difference for this example), but there are only 5 barber chairs. If chairs can be used for both services, then set location capacity as 5 which will limit total bookable slots as 5 at any given time. However if 3 of the chairs are for Gents\' and 2 of them are for Ladies\', set service capacities as 3 and 2, respectively.','wp-base') ?>
			</span></p>
			
			<p id="parent-child-bookings"><span class="description">
			<?php _e('<b>Parent & Child Bookings:</b> WP BASE has several Addons which can create multiple bookings at a single checkout. Examples of such Addons are Shopping Cart, WooCommerce, Packages, Recurring Appointments, Extras. When bookings are written to the database, one of them (usually the one which will start last) is automatically selected to lead the others. This one is called parent booking and the rest are called child bookings. Each child booking is called a sibling relative to the other children.','wp-base') ?>
			</span></p>
			
			<?php
		$c = ob_get_contents();
		ob_end_clean();

		if ( $return )
			return $c;
		else
			echo $c;
	}
	
	/**
	 * Business Help Content
	 */	
	function business_help( $return = false ) {
		ob_start();
		
		 _e('Note: Help texts here are identical to the texts inside the related setting pages. They are collected here just for convenience.','wp-base') ?>

		<h2><a href="<?php echo admin_url('admin.php?page=app_business&amp;tab=locations') ?>"><?php _e( 'Locations', 'wp-base') ?></a></h2>
		
		<?php
			foreach ( WpBConstant::location_desc() as $key=>$line ) {
				echo '<p><span class="description">'. $line . "</span></p>";
			}
		?>	
		<h2><a href="<?php echo admin_url('admin.php?page=app_business&amp;tab=services') ?>"><?php _e( 'Services', 'wp-base') ?></a></h2>
		
		<?php
			foreach ( WpBConstant::service_desc() as $key=>$line ) {
				echo '<p><span class="description">'. $line . "</span></p>";
			}
		?>						
		<h2><a href="<?php echo admin_url('admin.php?page=app_business&amp;tab=workers') ?>"><?php _e( 'Service Providers', 'wp-base') ?></a></h2>
		
		<?php
			foreach ( WpBConstant::worker_desc() as $key=>$line ) {
				echo '<p><span class="description">'. $line . "</span></p>";
			}
		?>						
		
		<h2><a href="<?php echo admin_url('admin.php?page=app_business&amp;tab=working_hours') ?>"><?php _e( 'Working Hours', 'wp-base') ?></a></h2>
		
		<?php
			foreach ( WpBConstant::wh_desc() as $key=>$line ) {
				echo '<p><span class="description">'. $line . "</span></p>";
			}
		$c = ob_get_contents();
		ob_end_clean();

		if ( $return )
			return $c;
		else
			echo $c;
	}

	/**
	 * Settings Help Content
	 */	
	function settings_help( $return = false ) {
		ob_start();
		
		  ?>


		<div class="app_mt"></div>
		<table style="width:100%" class="widefat fixed striped app-settings-list">
			<thead>
			<tr>
				<th style="width:25%; text-align:left;"><?php _e( 'Name [Internal Name]', 'wp-base') ?></th>
				<th style="width:60%; text-align:left;"><?php _e( 'Description', 'wp-base') ?></th>
				<th style="width:15%; text-align:left;"><?php _e( 'Addon', 'wp-base') ?></th>
			</tr>
			</thead>
		<?php
			$defaults = WpBConstant::defaults(true,'all');
			$titles = array();
			foreach ( $defaults as $def ) {
				$titles[] = $def[1];
			}
			array_multisort($titles, SORT_ASC, $defaults);
			
			foreach ( $defaults as $key=>$val ) {
				if ( !$val[1] || !$val[2] )
					continue;
				
				$cl = !empty( $val[3] ) ? 'data-addon="'.esc_attr($val[3]).'" title="'.esc_attr( sprintf( __('Requires %s addon','wp-base'), $val[3] ) ).'"' : '';
				
				echo '<tr '.$cl.'><td>';
				echo $val[1] . '<br/>['. $key. ']';
				echo '</td><td>';
				echo $val[2];
				echo '</td><td>';
				echo !empty( $val[3] ) ? $val[3] : '';
				echo '</td></tr>';
			}
		?>
		</tbody>
		</table>
		<script type="text/javascript">
		
		jQuery(document).ready(function($){
			var sstring = "<?php echo isset( $_GET['app_s'] ) ? urldecode( $_GET['app_s'] ) : ''; ?>";
			
			// Do not give navigation away alert for search and no of items fields
			$.extend( $.fn.dataTableExt.oStdClasses, {
				"sFilterInput": "app_no_save_alert",
				"sLengthSelect": "app_no_save_alert"
			});
			dt_api = $('.app-settings-list').DataTable({"dom": 'T<"app_clear">lfrtip',
					"oSearch": { "sSearch": sstring },
					"tableTools": {
						"sSwfPath":<?php echo json_encode(WPB_PLUGIN_URL.'/js/copy_csv_xls_pdf.swf') ?>
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

		});
		</script>		
		<?php

		$c = ob_get_contents();
		ob_end_clean();

		if ( $return )
			return $c;
		else
			echo $c;
	}

	/**
	 * Shortcodes Help Content
	 */	
	function shortcodes_help( $return = false ) {
		
		ob_start();
		?>
		<span class="description">
		</span>
		<br />
		<br />
		
		<script type="text/javascript">
      	  jQuery(document).ready(function ($) {
			  var head = $("div#tabs").find("li.app_help_sc_title");
			  $.each( head, function(i,v) {
				var head_text = $(this).find("span.app_help_sc_title_txt").text();
				var par_id = $(this).parents("div.app_help_sc").attr("id");
				$("div#tabs ul").first().append('<li><a href="#'+par_id+'">'+head_text+'</a></li>');
			  });
			$("div#tabs").tabs().removeClass("ui-widget");
          });
      	</script>
		
		<div id="tabs">
			<ul></ul>

		<?php
		$shortcodes = WpBConstant::shortcode_desc();
		$k = 0;
		foreach ( $shortcodes as $shortcode => $data ) {
			$image_name = str_replace( '_', '-', $shortcode ). '.jpg';
			$cl = ( $k % 2 != 0 ) ? 'class="odd"' : '';
			echo '<div class="app_help_sc" id="'.str_replace( '_', '-', $shortcode ).'-shortcode" '.$cl.'>';
			echo '<ul>';
			echo '<li class="app_help_sc_title"><span class="app_help_t1">' . __('Name:', 'wp-base'). ' </span><span class="app_help_sc_title_txt">' . $data['name'] . '</span></li>';
			echo '<li><span class="app_help_t1">' . __('Shortcode: ', 'wp-base'). ' </span><code>['. $shortcode . ']</code><br /></li>';
			echo '<li><span class="app_help_t1">' . __('Description:', 'wp-base'). ' </span> ' . $data['description'] . '</li>';
			if ( file_exists( $this->a->plugin_dir . "/help-images/" . $image_name ) ) {
				echo "<li>";
				echo "<span style='float:left'>" . __('<span class="app_help_t1">Result Example: </span>', 'wp-base' ) . "</span>";
				echo "<span style='float:left;margin-left:10px'>";
				echo "<img width='70%' src='". $this->a->plugin_url . "/help-images/" . $image_name . "' />";
				echo "</span>";
				echo "<div style='clear:both'></div>";
				echo "</li>";
			}
			echo '<li><span class="app_help_t1">'. __('Attributes:', 'wp-base' ) . '</span><ul>';
			
			$parameters = $data['parameters' ];
			ksort( $parameters  );
			foreach ( $parameters as $par_name => $par_desc ) {
				echo '<li><span class="app_help_t2">'. $par_name . ': </span>' .$par_desc . '</li>';
			}
			echo '</ul>';
			echo '</li></ul></div>';
			$k++;
		}
		
		?>
		</div>
		<?php

		$c = ob_get_contents();
		ob_end_clean();

		if ( $return )
			return $c;
		else
			echo $c;
	}



	/**
	 * Create pdf Manual and download it to user's PC
	 */	
	function create_manual() {
		if ( !class_exists('WpBPDF')  )
			return;
		
		if ( !isset( $_POST["action"] ) || $_POST["action"] != 'app_create_manual'  ) {
			return;
		}
		
		$subject = __("WP BASE Manual",'wp-base');
		$blogname = wp_specialchars_decode( get_option('blogname'), ENT_QUOTES );

		$content = "<h1>". $subject . "</h1>";
		$content .= "<pagebreak />";

		$content .= $this->general_help( true );
		$content .= "<pagebreak />";

		$content .= "<h2>". __( 'BUSINESS SETTINGS', 'wp-base') . "</h2>";
		$content .= $this->business_help( true );
		$content .= "<pagebreak />";

		$content .= "<h2>". __( 'GLOBAL SETTINGS', 'wp-base') . "</h2>";
		$content .= $this->settings_help( true );
		$content .= "<pagebreak />";
		
		$content .= "<h2>". __( 'SHORTCODES', 'wp-base'). "</h2>";
		$content .= $this->shortcodes_help( true );
		$content .= "<pagebreak />";
		
		$content .= "<h2>". sprintf( __( 'CURRENT SETTINGS FOR %1$s (%2$s)', 'wp-base'), $blogname, get_option('siteurl') ) . "</h2>";
		$content .= "<table><tr><th style='width: 60%; text-align:left'>". __('Setting','wp-base'). "</th><th style='width: 20%; text-align:left'>". __('Default','wp-base') . "</th><th style='width: 20%; text-align:left'>". __('Current','wp-base') ."</th></tr>";
		foreach ( WpBConstant::defaults('all') as $name=>$val ) {
			// Skip settings with no descriptions
			if ( empty( $val[1] ) || empty( $val[2] ) )
				continue;
			$content .= "<tr><td>";
			$content .=	$val[1] . "</td><td>" . $val[0] . "</td><td>" . wpb_setting($name) . "</td></tr>";	
			
		}
		$content .= "</table>";
		$content .= "<pagebreak />";

		// Add entries for indexing
		$pattern = '/<p><span class="description">(.*?)<b>(.*?):<\/b>/is';
		$replace = '<p><span class="description">$1<b><indexentry content="$2" />$2:</b>';
		$content = preg_replace($pattern, $replace, $content);
		
		$pattern = '/<h2>(.*?)<\/h2>/is';
		$replace = '<h2><indexentry content="$1" />$1</h2>';
		$content = preg_replace($pattern, $replace, $content);

		include_once( $this->a->plugin_dir . '/includes/addons/mpdf/vendor/autoload.php');
		$mpdf = new \Mpdf\Mpdf();
		
		// Header and footer
		$mpdf->DefHeaderByName('myheader', array( 
			'L' => array( 'content' => __("WP BASE MANUAL",'wp-base'), 'font-size' => 10 ), 
			'R' => array( 'content' => 'V'. $this->a->version, 'font-size' => 10 )
		));
		$mpdf->DefFooterByName('myfooter', array( 
			'L' => array( 'content' => date_i18n( $this->date_format, $this->_time ), 'font-size' => 10 ), 
			'R' => array( 'content' => '{PAGENO}', 'font-size' => 10)
		));
		
		// Styling
		$style = '@page { margin: 2cm; margin-left: 2.5cm;  header:myheader; footer:myfooter; table {border: 1px solid; border-style: solid;}}';
		
		// Generate
		$mpdf->WriteHTML($style,1);			// css
		$mpdf->SetSubject( $subject );		// File pdf settings
		$mpdf->SetTitle( $subject );
		$mpdf->SetAuthor( $blogname );
		$mpdf->SetCreator( $blogname );

		$mpdf->WriteHTML( $content, 2 );	// html
		$mpdf->InsertIndex(1,1);			// Index
		
		$filename = sanitize_file_name( $subject ).'-'.date( 'd-m-Y',$this->local_time ).'.pdf';

		$mpdf->Output( $filename, "D" );	// Send to browser for download
		
		exit;
	}



}

	BASE('AdminHelp')->add_hooks();
}


