<?php
/**
 * WPB Custom Functions
 *
 * Allows including custom functions to the project
 * Custom functions are checked in a sandbox before being saved. If any compile error is met, they are not activated
 * Uses the same principle as WP activates a plugin; therefore it does not guarantee to catch runtime errors during save
 *
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBCustomFunctions' ) ) {

class WpBCustomFunctions {
	
	/**
     * WP BASE instance
     */
	protected $a = null;
	
	/**
     * Constructor
     */
	function __construct() {
		$this->a = BASe();
	}
	
	function add_hooks(){
		add_action( 'wp_loaded', array( $this, 'save' ) );								// Custom functions should be saved before wp_loaded, where they are executed
		add_action( 'wp_loaded', array($this, 'execute'), 1000 );						// Eval custom functions
		add_filter( 'appointments_tools_tabs', array( $this, 'add_tab' ) );				// Add tab to Tools
		add_action( 'app_tools_custom_functions_tab', array( $this, 'render_tab' ) );	// Create tab HTML
	}

	/**
	 *	Execute custom functions
	 *	@since 3.0
	 */
	function execute(){
		# Execute only once
		if ( did_action( 'app_custom_functions_executed' ) )
			return;
		
		if ( defined( 'WPB_DISABLE_CUSTOM_FUNCTIONS' ) && WPB_DISABLE_CUSTOM_FUNCTIONS )
			return;
		
		# If custom functions are saved, they are already eval'ed. Do not execute them a second time.
		if ( did_action( 'app_custom_functions_saved' ) )
			return;
		
		if ( !trim( wpb_setting('custom_functions') ) )
			return;
		
		if ( !wpb_setting('custom_functions_no_error') )
			return;

		eval( wpb_setting('custom_functions') );
		// include_once( $this->test_file() ); 
		
		do_action( 'app_custom_functions_executed' );
	}
	
	/**
	 * Add tabs to Tools
	 * @uses appointments_tools_tabs filter
	 * @return string
	 */
	function add_tab( $tabs ) {
		$tabs['custom_functions']	= __('Custom Functions', 'wp-base');
		return $tabs;
	}
	
	/**
	* Check & Save custom functions
	* Partly from wp-admin/plugins.php
	* @since 3.0
	*/
	function save(){
		
		// Clean up request URI from temporary args for screen options/paging uri's to work as expected.
		$_SERVER['REQUEST_URI'] = remove_query_arg(array('error', 'deleted', 'activate', 'activate-multi', 'deactivate', 'deactivate-multi', '_error_nonce'), $_SERVER['REQUEST_URI']);

		if ( isset( $_REQUEST["action_app"] ) && wpb_is_demo() ) {
			wpb_notice( 'demo' );
			return;
		}

		if ( !isset( $_REQUEST["action_app"] ) ) {
			return;
		}
		
		if ( 'error_scrape' == $_REQUEST["action_app"] ) {
			
			if ( ! WP_DEBUG ) {
				error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
			}

			@ini_set('display_errors', true); # Ensure that Fatal errors are displayed.
			
			# Go back to "sandbox" scope so we get the same errors as before
			include_once( $this->test_file() ); 

			exit;
		}

		if ( 'save_custom_functions' != $_REQUEST["action_app"] )
			return;
		
		if ( defined( 'WPB_DISABLE_EDITING_CUSTOM_FUNCTIONS' ) && WPB_DISABLE_EDITING_CUSTOM_FUNCTIONS )
			return;
		
		$options = wpb_setting();
		$options['custom_functions']	= rtrim( ltrim( trim( wp_unslash( $_POST['custom_functions'] ) ),'<?php' ), '?>' );
		
		// Find names of user entered functions and check for duplicates
		preg_match_all('/function[\s\n]+(\S+)[\s\n]*\(/i', $options['custom_functions'], $user_func_names);
		$user_func_a = count( $user_func_names[1] );
		$user_func_b = count( array_unique( $user_func_names[1] ) );

		// Find all names of declared user functions and match with names of user entered functions
		$declared_func			= get_defined_functions();
		$declared_func_user		= array_intersect( $user_func_names[1], $declared_func['user'] );
		$declared_func_internal = array_intersect( $user_func_names[1], $declared_func['internal'] );
		
		if ( $user_func_a != $user_func_b || count( $declared_func_user ) != 0 || count( $declared_func_internal ) != 0 )
			$duplicate_error = true;
		else
			$duplicate_error = false;

		# If there is a duplicate function error, no need to check.
		if ( !$duplicate_error ) {
			
			# At first, assume that codes have errors
			$code_error = true;
			$this->a->pause_log = true;
			$options['custom_functions_no_error'] = false;
			$this->a->update_options( $options );
			unset( $this->a->pause_log );
			
			if ( file_exists( $this->test_file() ) )
				unlink( $this->test_file() );
			
			$result = $this->run_code_in_sandbox( admin_url('admin.php?page=app_tools&tab=custom_functions&error=true' ), $options['custom_functions'] );
			
			if ( is_wp_error( $result ) ) {
				if ( 'unexpected_output' == $result->get_error_code() ) {
					$redirect = self_admin_url('admin.php?page=app_tools&tab=custom_functions&error=true&charsout=' . strlen($result->get_error_data()) );
					wp_redirect( add_query_arg('_error_nonce', wp_create_nonce('custom-functions-error'), $redirect) );
					exit;
				} else {
					wp_die( $result );
				}
			}
			
			$code_error = false;
		}
		
		if ( $duplicate_error || $code_error )
			$options['custom_functions_no_error'] = '';					# There is error
		else
			$options['custom_functions_no_error'] = $this->a->_time;	# No error. Record last success time
		
		$this->a->pause_log = true;
		if ( $this->a->update_options( $options ) ) {
			wpb_notice( 'saved' );
		}
		unset( $this->a->pause_log );

		if ( file_exists( $this->test_file() ) )
			unlink( $this->test_file() );
			
		do_action( 'app_custom_functions_saved' );
	}
	
	/**
	* Codes are first written in a file and include'd to see if they will trigger an error
	* This is the sandboxed file in /uploads/__app/ folder
	* It can be deleted
	* @since 3.0
	*/
	function test_file(){
		return $this->a->custom_folder( ).'your-custom-functions.php';
	}
	
	/**
	* Run code in a sandbox
	* Based on the idea in activate_plugin in wp-admin/includes/plugin.php
	* @since 3.0
	*/
	function run_code_in_sandbox( $redirect='', $code ) {
		
		ob_start();
		
		file_put_contents( $this->test_file(), '<?php ' . $code );

		if ( !empty( $redirect ) )
			wp_redirect( add_query_arg('_error_nonce', wp_create_nonce('custom-functions-error'), $redirect) );

		include_once( $this->test_file() ); 
			
		if ( ob_get_length() > 0 ) {
			$output = ob_get_clean();
			return new WP_Error('unexpected_output', __('Your code generated unexpected output.', 'wp-base'), $output);
		}
		ob_end_clean();	
		
		return null;
	}
	
	/**
	* Create admin HTML for custom functions
	* @since 3.0
	*/
	function render_tab(){
		wpb_admin_access_check( 'manage_tools' );
		
		$wp_nonce = wp_nonce_field( 'update_app_settings', 'app_nonce', true, false );
		wpb_infobox( sprintf( __('Here you can write your custom PHP functions. These functions are executed at "wp_loaded" action. To prevent execution of these codes, add %1$s to wp-config.php. To prevent editing, but not execution use %2$s', 'wp-base'), '<code>define("WPB_DISABLE_CUSTOM_FUNCTIONS", true);</code>', '<code>define("WPB_DISABLE_EDITING_CUSTOM_FUNCTIONS", true);</code>' ) );
		?>
		<form method="post">
			<p class="submit">
				<input type="submit"class="button-primary" value="<?php _e('Save Custom Functions') ?>"  />
			</p>

			<div class="postbox">
			<div class="inside">
		
				<table class="form-table fixed">

				<tr><td>
					<div class="app_b app_mb"><?php _e( 'Status:', 'wp-base' ) ?></div>
					<div>
					<?php
					$readonly = false;
					if ( defined( 'WPB_DISABLE_CUSTOM_FUNCTIONS' ) && WPB_DISABLE_CUSTOM_FUNCTIONS ) {
						_e( 'Disabled by WPB_DISABLE_CUSTOM_FUNCTIONS', 'wp-base' );
						$readonly = true;
					}
					else if ( defined( 'WPB_DISABLE_EDITING_CUSTOM_FUNCTIONS' ) && WPB_DISABLE_EDITING_CUSTOM_FUNCTIONS ) {
						_e( 'Editing disabled by WPB_DISABLE_EDITING_CUSTOM_FUNCTIONS (Codes will run but read-only)', 'wp-base' );
						$readonly = true;
					}
					else if ( !trim( wpb_setting('custom_functions') ) )
						_e( 'No code entered', 'wp-base' );
					else if ( wpb_setting('custom_functions_no_error') ) {
						printf( __( 'Code is valid. Last saved at %s', 'wp-base' ), date_i18n( $this->a->dt_format, wpb_setting('custom_functions_no_error') ) );
					}
					else if ( isset($_GET['error']) ) {
						_e( 'There are some error(s) in your code', 'wp-base' );
						if ( isset($_GET['charsout']) )
							$errmsg = sprintf(__('Your code generated %d characters of <strong>unexpected output</strong>. Check log file.'), $_GET['charsout']);
						else
							$errmsg = __('Your code will not be activated because it triggered a <strong>fatal error</strong>.');
						?>
						<div id="message" class="error"><p><?php echo $errmsg; ?></p>
						<?php
							if ( ! isset( $_GET['charsout'] ) && wp_verify_nonce( $_GET['_error_nonce'], 'custom-functions-error' ) ) {
								$iframe_url = add_query_arg( array(
									'action_app'   => 'error_scrape',
									'_wpnonce' 		=> urlencode( $_GET['_error_nonce'] ),
								), admin_url( 'admin.php?page=app_tools&tab=custom_functions' ) );
							?>
							<iframe style="border:0" width="100%" height="70px" src="<?php echo esc_url( $iframe_url ); ?>"></iframe>
						<?php
							}
						?>
						</div>
					<?php
					}
					else {
						_e( 'Your code could not be verified. It will not be executed.', 'wp-base' );
					}
					?>
					</div>
				</td></tr>
				
				<tr><td>
					<div class="app_b app_mb"><?php _e( 'Your Custom Functions:', 'wp-base' ) ?></div>
					<div>
						<textarea cols="90" style="width:90%" class="app-codemirror" name="custom_functions" <?php if ($readonly) echo 'readonly'; ?>><?php echo esc_textarea( wpb_setting('custom_functions')); ?></textarea>
					</div>
				</td></tr>

				</table>
				<?php echo $wp_nonce; ?>
			
				
			</div>
		</div>
			<p class="submit">
				<input type="hidden" name="action_app" value="save_custom_functions" />
				<input type="submit"class="button-primary" value="<?php _e('Save Custom Functions') ?>"  />
			</p>
		</form>
		<?php
	}
	
	
}
	BASe('CustomFunctions')->add_hooks();
}