<?php
/**
 * WPB Admin Monetary Settings
 *
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBAdminMonetarySettings' ) ) {
	
class WpBAdminMonetarySettings{

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
     * Add admin actions
     */
	function add_hooks() {
		add_action( 'app_submenu_before_business', array( $this, 'add_submenu' ), 12 );
		add_action( 'app_save_settings', array( $this, 'save_settings' ), 12 );
	}	

	/**
     * Add submenu page to main admin menu
     */
	function add_submenu(){
		wpb_add_submenu_page('appointments', __('WPB Monetary Settings','wp-base'), __('Monetary Settings','wp-base'), array(WPB_ADMIN_CAP,'manage_monetary_settings'), "app_monetary", array($this,'settings'));
	}
	
	/**
     * Save admin settings
     */
	function save_settings() {

		if ( isset( $_POST['app_nonce'] ) && !wp_verify_nonce($_POST['app_nonce'],'update_app_settings') ) {
			wpb_notice( 'unauthorised', 'error' );
			return;
		}
		
		$options = wpb_setting();
		$settings_changed = false;
		
		if ( 'save_payment' == $_POST["action_app"] ) {
			$settings_changed = true;
			
			$options["payment_required"]			= $_POST["payment_required"];
			$options['currency'] 					= $_POST['currency'];
			$options['curr_symbol_position']		= isset( $_POST['curr_symbol_position'] ) ? $_POST['curr_symbol_position'] : 1;
			$options['curr_decimal']				= $_POST['curr_decimal'];
			$options['decimal_separator']			= $_POST['decimal_separator'];
			$options['thousands_separator']			= $_POST['thousands_separator'];
			$options["tax"]							= preg_replace( "/[^0-9\.]/", "", $_POST["tax"] );
			
			$options["percent_downpayment"]			= preg_replace( "/[^0-9]/", "", str_replace( '%', '', $_POST["percent_downpayment"] ) );
			$options["fixed_downpayment"]			= wpb_sanitize_price( $_POST["fixed_downpayment"] );
			$options['add_deposit']					= $_POST['add_deposit'];
			$options['members_no_payment'] 			= isset( $_POST['members_no_payment'] );
			$options["members"]						= isset($_POST["members"]) ? maybe_serialize( $_POST["members"] ) :'';
			if ( isset( $_POST['return'] ) )
				$options['return'] 					= $_POST['return'];		// PayPal Return page
			if ( isset( $_POST['item_name'] ) )
				$options['item_name'] 				= $_POST['item_name'];	// PayPal Item name
		}
		else if ( 'save_gateway' == $_POST["action_app"] ) {
			$settings_changed = true;
			if ( isset( $_POST['mp'] ) ) {
				global $app_gateway_plugins;
				foreach ( (array)$app_gateway_plugins as $name=>$plugin ) {
					if ( isset( $_POST['mp']['gateways'][$name] ) )
						$options['gateways'][$name] = wp_unslash( $_POST['mp']['gateways'][$name] );
				}
			}

			if ( isset( $_POST['mp']['gateways']['allowed'] ) )
				$options['gateways']['allowed'] = $_POST['mp']['gateways']['allowed'];
			else {
				//blank array if no checkboxes
				$options['gateways']['allowed'] = array();
			}
		}

		if ( $settings_changed ) {
			if ( $this->a->update_options( $options ) ) {
				wpb_notice( 'saved' );
			}
		}		
	}
	
	/**
	 * Admin Global Settings HTML code 
	 */
	function settings() {

		wpb_admin_access_check( 'manage_monetary_settings' );
		
	?>
		<div class="wrap app-page">
		<h2 class="app-dashicons-before dashicons-admin-settings"><?php echo __('Monetary Settings','wp-base'); ?></h2>
		<h3 class="nav-tab-wrapper">
			<?php
			$tab = ( !empty($_GET['tab']) ) ? $_GET['tab'] : 'main';
			
			$tabs = array(
				'gateways'		=> __('Payment Gateways', 'wp-base'),
			);
			
			$tabhtml = array();

			
			$tabs = apply_filters( 'appointments_monetary_tabs', $tabs );

			$class = ( 'main' == $tab ) ? ' nav-tab-active' : '';
			$tabhtml[] = '	<a href="' . admin_url( 'admin.php?page=app_monetary' ) . '" class="nav-tab'.$class.'">' . __('General', 'wp-base') . '</a>';

			foreach ( $tabs as $stub => $title ) {
				$class = ( $stub == $tab ) ? ' nav-tab-active' : '';
				$tabhtml[] = '	<a href="' . admin_url( 'admin.php?page=app_monetary&amp;tab=' . $stub ) . '" class="nav-tab'.$class.'" id="app_tab_'.$stub.'">'.$title.'</a>';
			}

			echo implode( "\n", $tabhtml );
			
			$wp_editor_settings = array( 'editor_height'=> WPB_EDITOR_HEIGHT );
			
			?>
		</h3>
		<div class="clear"></div>
		
 		<?php // wpb_infobox( __('Anything related to payments and money is set up on this page.', 'wp-base') ); ?>

		<?php switch( $tab ) {

		case 'main': ?>
		
        <div id="poststuff" class="metabox-holder meta-box-sortables">

		<form id="app-payment-form" class="app_form" method="post" action="">

		<div id ="wpb-general-monetary-settings" class="postbox <?php echo postbox_classes('wpb-general-monetary-settings', wpb_get_current_screen_id( true ) ) ?>">
			<button type="button" class="handlediv" aria-expanded="true">
				<span class="screen-reader-text"><?php  _e( 'Toggle panel: General Monetary Settings' ) ?></span>
				<span class="toggle-indicator" aria-hidden="true"></span>
			</button>
			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<h3 class="hndle"><span><?php _e('General', 'wp-base'); ?></span></h3>
		<div class="inside">
			<table class="form-table">
			
			<tr id="payment-required">
				<th scope="row" ><?php WpBConstant::echo_setting_name('payment_required') ?></th>
				<td>
				<select name="payment_required">
				<option value="no" <?php if ( wpb_setting('payment_required') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
				<option value="yes" <?php if ( wpb_setting('payment_required') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
				</select>
				<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('payment_required') ?></span>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php WpBConstant::echo_setting_name('currency') ?></th>
				<td>
				<select name="currency">
				<?php
				$sel_currency = (wpb_setting('currency')) ? wpb_setting('currency') : 'USD';
		
				foreach (WpBConstant::currencies() as $k => $v) {
				echo '<option value="' . $k . '"' . ($k == $sel_currency ? ' selected' : '') . '>' . esc_html($v[0], true) .' - '. wpb_format_currency($k)  . '</option>' . "\n";
				}
				?>
				</select>
				</td>
	        </tr>
			
			<tr>
				<th scope="row"><?php WpBConstant::echo_setting_name('curr_symbol_position') ?></th>
				<td>
				<?php
					$csp = ( wpb_setting('curr_symbol_position') ) ? wpb_setting('curr_symbol_position') : 0;
					$cd = ( wpb_setting('curr_decimal') ) ? wpb_setting('curr_decimal') : 0 ;
				?>
					<label><input value="1" name="curr_symbol_position" type="radio"<?php checked($csp, 1); ?>>
					<?php echo wpb_format_currency(wpb_setting('currency')); ?>100</label>&nbsp;&nbsp;&nbsp;&nbsp;
					<label><input value="2" name="curr_symbol_position" type="radio"<?php checked($csp, 2); ?>>
					<?php echo wpb_format_currency(wpb_setting('currency')); ?> 100</label>&nbsp;&nbsp;&nbsp;&nbsp;
					<label><input value="3" name="curr_symbol_position" type="radio"<?php checked($csp, 3); ?>>
					100<?php echo wpb_format_currency(wpb_setting('currency')); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;
					<label><input value="4" name="curr_symbol_position" type="radio"<?php checked($csp, 4); ?>>
					100 <?php echo wpb_format_currency(wpb_setting('currency')); ?></label>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php WpBConstant::echo_setting_name('curr_decimal') ?></th>
				<td>
					<label><input value="1" name="curr_decimal" type="radio"<?php checked( $cd, 1 ); ?>>
					<?php _e('Yes', 'wp-base') ?></label>&nbsp;&nbsp;&nbsp;&nbsp;
					<label><input value="0" name="curr_decimal" type="radio"<?php !checked( $cd, 0 ); ?>>
					<?php _e('No', 'wp-base') ?></label>
				</td>
			</tr>			
		
			<tr>
				<th scope="row"><?php WpBConstant::echo_setting_name('decimal_separator') ?></th>
				<td>
				<input value="<?php echo esc_attr(wpb_setting('decimal_separator')); ?>" style="width:50px" name="decimal_separator" type="text" />
				<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('decimal_separator') ?></span>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php WpBConstant::echo_setting_name('thousands_separator') ?></th>
				<td>
				<input value="<?php echo esc_attr(wpb_setting('thousands_separator')); ?>" style="width:50px" name="thousands_separator" type="text" />
				<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('thousands_separator') ?></span>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php WpBConstant::echo_setting_name('tax') ?></th>
				<td>
				<input value="<?php echo esc_attr(wpb_setting('tax')); ?>" style="width:50px" name="tax" type="text" />
				<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('tax') ?></span>
				</td>
			</tr>

			<?php do_action( 'app_payment_general_settings' ) ?>

			</table>
		</div>
		</div>

		<div id="wpb-prepayment-settings" class="postbox <?php echo postbox_classes('wpb-prepayment-settings', wpb_get_current_screen_id( true ) ) ?>">
			<button type="button" class="handlediv" aria-expanded="true">
				<span class="screen-reader-text"><?php  _e( 'Toggle panel: Prepayment Settings' ) ?></span>
				<span class="toggle-indicator" aria-hidden="true"></span>
			</button>
		<h3 class="hndle"><span><?php _e('Prepayment', 'wp-base'); ?></span></h3>
		<div class="inside">
			<table class="form-table">
			
			<tr>
				<th scope="row"><?php WpBConstant::echo_setting_name('percent_downpayment') ?></th>
				<td>
				<input value="<?php echo esc_attr(wpb_setting('percent_downpayment')); ?>" style="width:50px" name="percent_downpayment" type="text" />
				<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('percent_downpayment') ?></span>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php WpBConstant::echo_setting_name('fixed_downpayment'); echo ' ('. wpb_format_currency(). ')'; ?></th>
				<td>
				<input value="<?php echo esc_attr(wpb_setting('fixed_downpayment')); ?>" style="width:50px" name="fixed_downpayment" type="text" />
				<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('fixed_downpayment') ?></span>
				</td>
			</tr>
			
			<tr id="add-deposit">
				<th scope="row" ><?php WpBConstant::echo_setting_name('add_deposit') ?></th>
				<td>
				<select name="add_deposit">
				<option value="no" <?php if ( wpb_setting('add_deposit') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
				<option value="yes" <?php if ( wpb_setting('add_deposit') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
				</select>
				<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('add_deposit') ?></span>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php WpBConstant::echo_setting_name('members_no_payment') ?></th>
				<td>
				<input type="checkbox" name="members_no_payment" <?php if ( wpb_setting("members_no_payment") ) echo 'checked="checked"' ?> />
				<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('members_no_payment') ?></span>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><?php WpBConstant::echo_setting_name('members') ?></th>
				<td>
				<?php
					$meta = maybe_unserialize( wpb_setting("members") );
					global $wp_roles;
					if ( is_object( $wp_roles ) ) {
						echo '<div style="float:left"><select class="add_roles_multiple" multiple="multiple" name="members[level][]" >';
						foreach (  $wp_roles->roles as $key=>$role ) {
							if ( isset($meta["level"] ) && is_array( $meta["level"] ) && in_array( $key, $meta["level"] ) )
								$sela = 'selected="selected"';
							else
								$sela = '';
							echo '<option value="'.$key.'"' . $sela . '>'. $role['name'] . '</option>';
						}
						echo '</select></div>';
					}
					else
						echo '<input type="text" size="40" value="'. __('No user roles have been defined yet','wp-base').'" readonly="readonly" />';
				?>
				<div style="float:left;width:80%;margin-left:5px;">
				<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('members') ?></span>
				</div>
				<div style="clear:both"></div>
				</td>
			</tr>
			
	        </table>
		</div>
		</div>

			<input type="hidden" name="action_app" value="save_payment" />
			<?php wp_nonce_field( 'update_app_settings', 'app_nonce' ); ?>         
			<p class="submit">
            <input class="button-primary app-save-payment-btn" type="submit" name="submit_settings" value="<?php _e('Save Settings', 'wp-base') ?>" />
          </p>
        </form>
 

		<script type="text/javascript">
			var ms_ops2 = {
				noneSelectedText:'<?php echo esc_js( __('Select Roles', 'wp-base' )) ?>',
				checkAllText:'<?php echo esc_js( __('Check all', 'wp-base' )) ?>',
				uncheckAllText:'<?php echo esc_js( __('Uncheck all', 'wp-base' )) ?>',
				selectedText:'<?php echo esc_js( __('# selected', 'wp-base' )) ?>',
				selectedList:5,
				minWidth: 400,
				position: {
				  my: 'left bottom',
				  at: 'left top'
			   }
			};
			jQuery(document).ready(function($){
				$(".add_roles_multiple").multiselect(ms_ops2);
				
				// var tab = typeof urlParam === 'function' && $.urlParam('tab') ? '_'+$.urlParam('tab') : '';
				// postboxes.add_postbox_toggles(pagenow+tab);
			});
		</script>

		</div>

		<?php break;

		case 'gateways': ?>
		
        <div id="poststuff" class="metabox-holder meta-box-sortables">

		<form id="app-payment-form" class="app_form" method="post" action="">
		<input type="hidden" name="gateway_settings" value="1" />
		
         <div id="mp_gateways" class="postbox <?php echo postbox_classes('mp_gateways', wpb_get_current_screen_id( true ) ) ?>">
			<button type="button" class="handlediv" aria-expanded="true">
				<span class="screen-reader-text"><?php  _e( 'Toggle panel: Payment gateways' ) ?></span>
				<span class="toggle-indicator" aria-hidden="true"></span>
			</button>
			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
            <h3 class="hndle"><span><?php _e('Active Payment Gateways', 'wp-base') ?></span></h3>
            <div class="inside">
			<span class="description app_bottom"><?php _e('After you select a payment gateway and save, its settings will be revealed below.','wp-base') ?></span>
              <table class="form-table">
                <tr>
				<?php
                //check network permissions
				global $app_gateway_plugins;
				?>

					<th scope="row"><?php
					if ( !empty( $app_gateway_plugins ) )
						_e('Select Payment Gateway(s)', 'wp-base'); 
					else
						_e('No Payment Gateway Found', 'wp-base');
					?>
					</th>
					<td>
				<?php
 
                foreach ( (array)$app_gateway_plugins as $code => $plugin ) {
 
                    ?><label><input type="checkbox" class="app_allowed_gateways app_no_save_alert" name="mp[gateways][allowed][]" value="<?php echo $code; ?>"<?php echo (in_array($code, $this->a->get_setting('gateways->allowed', array()))) ? ' checked="checked"' : ''; ?> /> <?php echo esc_attr($plugin[1]); ?></label><br /><?php
					
				}
                ?>
        				</td>
                </tr>
              </table>
            </div>
          </div>
 		
		<div id="tabs2" class="app-tabs">
			<ul></ul>
		  
          <?php
          //for adding additional settings for a payment gateway plugin
          do_action( 'app_gateway_settings', wpb_setting() );
          ?>
		</div>

			<input type="hidden" name="action_app" value="save_gateway" />
			<?php wp_nonce_field( 'update_app_settings', 'app_nonce' ); ?>         
			<p class="submit">
            <input class="button-primary app-save-payment-btn" type="submit" name="submit_settings" value="<?php _e('Save Settings', 'wp-base') ?>" />
          </p>
        </form>
		
		</div>
		<?php break;
		
		case $tab:				do_action( 'app_monetary_'.$tab.'_tab' ); break;
		
		} // End of the big switch ?>
		</div><!-- Wrap -->
		
		<script type="text/javascript">
		jQuery(document).ready(function($){
			var tab = typeof $.urlParam === 'function' && $.urlParam('tab') ? '_'+$.urlParam('tab') : '';
			postboxes.add_postbox_toggles(pagenow+tab);
		});
		</script>

	<?php
	}		

}

	BASE('AdminMonetarySettings')->add_hooks();
}