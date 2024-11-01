<?php
/**
 * WPB Welcome
 *
 * Opens an informative popup on first installation or after an update
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBWelcome' ) ) {

class WpBWelcome {
	
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
		add_action( 'admin_init', array( $this, 'welcome_init' ), 8, 2 );		// Welcome popup
	}

	/**
	 * Triggers welcome popup
	 * @since 2.0
	 */	
	function welcome_init( ) {
		if ( !get_user_meta( get_current_user_id(), 'app_welcome', true ) )
			return;

		// Import Suggestion from A+ or ApB
		if ( function_exists('wpb_import_from_a_plus') && wpb_admin_access_check( 'manage_tools', false ) && !get_option( 'app_imported_from_a_plus' ) && wpb_import_from_a_plus( true ) ) {
			add_action( 'admin_notices', '_wpb_suggest_a_plus_import' );
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'welcome_script' ) );
		add_action( 'admin_footer', array( $this, 'welcome' ) );
	}
	
	/**
	 * Loads jQuery UI dialog
	 * @since 2.0
	 */	
	function welcome_script() {
		wp_enqueue_style( 'jquery-ui-structure', WPB_PLUGIN_URL . '/css/jquery-ui.structure.min.css', array(), $this->a->version );
		wp_enqueue_style( 'jquery-ui-'.$this->a->selected_theme( ), $this->a->get_theme_file( ), array(), $this->a->version );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-effects-drop' );
	}

	 /**
	 * Displays welcome popup
	 * @since 2.0
	 */	
	function welcome(  ) {
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) || wpb_is_mobile() )
			return;
		
		delete_user_meta( get_current_user_id(), 'app_welcome' );
		
		?>
		<div id="app-welcome" style="display:none">
			<p><?php _e( 'Thank you for choosing WP BASE.', 'wp-base' ) ?></p>
			<p><?php _e( 'WP BASE is the most advanced Services and Appointment bookings WordPress plugin available on the market. It can be easily set up for most business models, however some sophisticated applications may require a little bit more effort. We have several tools and documentation to help you set up WP BASE to fulfill your business requirements.', 'wp-base' ) ?></p>
			<p><?php _e( 'If you are a first time user, we recommend you to have a look at these links:', 'wp-base' ) ?></p>
			<ul>
				<li><a href="<?php echo WPB_DEMO_WEBSITE ?>" target="_blank"><?php _e( 'Our demo website includes several application examples','wp-base' )?></a></li>
				<li><a href="<?php echo WPB_URL .'knowledge-base/' ?>" target="_blank"><?php _e( 'Our Knowledge Base provides reference information','wp-base' )?></a></li>
				<li><a href="<?php echo admin_url('admin.php?page=app_settings&tutorial=restart1') ?>" target="_blank"><?php _e( 'Our interactive tutorials assists you to make a quick start','wp-base' )?></a></li>
				<li><a href="<?php echo admin_url('admin.php?page=app_settings') ?>" target="_blank"><?php _e( 'You can visit Global Settings page to create a booking page and adjust other settings','wp-base' )?></a></li>
				<li><a href="javascript:void(0)" class="app_close_welcome"><?php _e( 'You can close this popup if you are already familiar with WP BASE','wp-base' )?></a></li>
			</ul>
		</div>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			var w = window.innerWidth;
			var dwidth = 0;
			if (w > 600) {dwidth = 600;}
			else{ dwidth= w-30;}
			
			$("#app-welcome").dialog({
				title:"<?php echo esc_js( __( 'Welcome to WP BASE - Bookings for Appointments, Services and Events', 'wp-base' ) ) ?>",
				modal:true,
				width: dwidth,
				position: { my: "center", at: "center", of: window },
				show: { effect: "drop", direction:"up", duration: 800 },
				hide: { effect: "drop", direction:"down", duration: 800 },
				dialogClass: "app-welcome"
			}).parent(".ui-dialog").css({"border-radius": "10px 10px 10px 10px","box-shadow": "0 0 25px 5px #999"});
			
			$(".app_close_welcome").click(function(){
				$("#app-welcome").dialog("close");
			});
		});
		</script>
		<?php
	}
	
	
}
	BASE('Welcome')->add_hooks();
}