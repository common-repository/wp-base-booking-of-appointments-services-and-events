<?php
/**
 * WPB Admin Display Settings
 *
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBAdminDisplaySettings' ) ) {
	
class WpBAdminDisplaySettings{

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
		add_action( 'app_submenu_before_business', array( $this, 'add_submenu' ), 18 );
		add_action( 'app_save_settings', array( $this, 'save_settings' ), 12 );
	}	

	/**
     * Add submenu page to main admin menu
     */
	function add_submenu(){
		wpb_add_submenu_page('appointments', __('WPB Display Settings','wp-base'), __('Display Settings','wp-base'), array(WPB_ADMIN_CAP,'manage_display_settings'), "app_display", array($this,'settings'));
	}
	
	/**
     * Save admin settings
     */
	function save_settings() {

		if ( isset( $_POST['app_nonce'] ) && !wp_verify_nonce($_POST['app_nonce'],'update_app_settings') ) {
			wpb_notice( 'unauthorised' );
			return;
		}
		
		$settings_changed = $saved = false;
		
		$options = wpb_setting();
	
		// Save general settings
		if ( 'save_display_general' == $_POST["action_app"] ) {
			
			$options["duration_format"]				= $_POST["duration_format"];
			$options["show_legend"]					= $_POST["show_legend"];
			$options["hide_busy"]					= $_POST["hide_busy"];
			$options["theme"]						= $_POST["theme"];
			$options["color_set"]					= $_POST["color_set"];
			foreach ( $this->a->get_legend_items() as $class=>$name ) {
				$options[$class."_color"]			= str_replace( '#', '', $_POST[$class."_color"]);
			}
			/* Which user fields will be required */
			foreach ( $this->a->get_user_fields() as $f ) {
				$options["ask_".$f]					= isset( $_POST["ask_".$f] );
			}
			$options["ask_note"]					= isset( $_POST["ask_note"] );
			$options["ask_remember"]				= isset( $_POST["ask_remember"] );			

			if ( $this->a->update_options( $options ) ) {
				wpb_notice( 'saved' );
			}
		}
			
	}
	
	/**
	 * Admin display settings HTML code 
	 * @since 2.0
	 */
	function settings() {

		wpb_admin_access_check( 'manage_display_settings' );

	?>
		<div class="wrap app-page">
		<h2 class="app-dashicons-before dashicons-welcome-view-site"><?php echo __('Display Settings','wp-base'); ?></h2>
		<h3 class="nav-tab-wrapper">
			<?php
			$tab = isset($_GET['tab']) && $_GET['tab'] ? $_GET['tab'] : 'general';
			
			$tabs = array(
				'general'		=> __('General', 'wp-base'),
			);
			
			$tabhtml = array();

			// If someone wants to remove or add a tab
			$tabs = apply_filters( 'appointments_display_tabs', $tabs );

			$class = ( 'general' == $tab ) ? ' nav-tab-active' : '';

			foreach ( $tabs as $stub => $title ) {
				$class = ( $stub == $tab ) ? ' nav-tab-active' : '';
				$tabhtml[] = '	<a href="' . admin_url( 'admin.php?page=app_display&amp;tab=' . $stub ) . '" class="nav-tab'.$class.'" id="app_tab_'.$stub.'">'.$title.'</a>';
			}

			echo implode( "\n", $tabhtml );
			?>
		</h3>
		<div class="clear"></div>
		
		<?php switch( $tab ) {
			
		case 'general':
		?>
		<div id="poststuff" class="metabox-holder">
		<?php  // wpb_infobox( sprintf( __('WP BASE plugin makes it possible for your clients to apply for appointments from the front end and for you to enter appointments from backend. On this page, you can set settings which will be valid throughout the website. Please note that some settings can be overridden per page basis by setting appropriate shortcode parameters. For example, user info fields can be set on the Confirmation shortcode overriding settings on this page. If you do not know how to proceed, consider following the %s.', 'wp-base'), $this->a->_a( $this->a->_t('tutorials'), 'admin.php?page=app_help#tutorials', $this->a->_t('Click to access Tutorials on the Help page') ) ) ); ?>
			<form class="app_form" method="post" action="<?php echo wpb_add_query_arg( null, null )?>">

			<div class="postbox">
			<h3 class="hndle"><span><?php _e('General Display Settings', 'wp-base') ?></span></h3>
				<div class="inside">
				<table class="form-table">
						
					<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('duration_format') ?></th>
					<td>
						<select name="duration_format">
						<option value="minutes" <?php if ( wpb_setting('duration_format') == 'minutes' ) echo "selected='selected'"?>><?php _e('Minutes, e.g. 90 minutes', 'wp-base')?></option>
						<option value="hours_minutes" <?php if ( wpb_setting('duration_format') == 'hours_minutes' ) echo "selected='selected'"?>><?php _e('Hours-minutes, e.g. 1 hour 30 minutes', 'wp-base')?></option>
						<option value="top_hours" <?php if ( wpb_setting('duration_format') == 'top_hours' ) echo "selected='selected'"?>><?php _e('Top Hours, e.g. 2 hours when 90 mins', 'wp-base')?></option>
						<option value="hours" <?php if ( wpb_setting('duration_format') == 'hours' ) echo "selected='selected'"?>><?php _e('Exact Hours, e.g. 1.5 hour', 'wp-base')?></option>
						</select>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('duration_format') ?></span>
					</td>
					</tr>

					<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('show_legend') ?></th>
					<td>
						<select name="show_legend">
						<option value="no" <?php if ( wpb_setting('show_legend') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
						<option value="yes" <?php if ( wpb_setting('show_legend') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
						</select>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('show_legend') ?></span>
					</td>
					</tr>

					<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('theme') ?></th>
					<td>
						<select name="theme">
						<?php
						foreach ( $this->a->get_themes() as $theme ) {
							$theme_name = ucfirst( str_replace( "-", " ", $theme ) );
							echo '<option '.selected(wpb_setting('theme'),$theme, false).' value="'.$theme.'">'. $theme_name . '</option>';
						}
						?>
						</select>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('theme') ?></span>
						<?php if ( class_exists( 'WpBPro' ) ) { ?>
						<span class="description app_bottom"><?php printf( __(' Tip: To include your customized jquery-ui theme, prepare and download the custom theme using jquery-ui themeroller on its website. Upload the "theme_name" folder to %s. Rename the main css file as "style.css" or "jquery-ui.css". You can create your own theme file (independent of jquery-ui) in the same manner. Then the theme will be selectable in this pulldown menu under "theme_name" name. Time slot color setting should be set as "Custom" and you can pick the colors you want.','wp-base'), $this->a->custom_folder().'css/') ?></span>
						<?php } ?>
					</td>
					</tr>


					<tr>
						<th scope="row" ><?php WpBConstant::echo_setting_name('color_set') ?></th>
						<td>
						<div class="alignleft app_mr">
							<?php 
							$cset = wpb_setting('color_set');
							?>
							<select name="color_set">
							<option value="0" <?php selected($cset,0) ?>><?php _e('Custom', 'wp-base')?></option>
							<option value="1" <?php selected($cset,1) ?>><?php _e('Legacy 1', 'wp-base')?></option>
							<option value="2" <?php selected($cset,2) ?>><?php _e('Legacy 2', 'wp-base')?></option>
							<option value="3" <?php selected($cset,3) ?>><?php _e('Legacy 3', 'wp-base')?></option>
							<optgroup label="<?php _e('Theme colors','wp-base') ?>" >
							<?php 
							foreach ( $this->a->get_themes() as $theme ) {
								$theme_name = ucfirst( str_replace( "-", " ", $theme ) );
								echo '<option value="'.$theme.'" '. selected( $cset, $theme, false ) . '>' . $theme_name . '</option>';	
							}
							?>
							</optgroup>
							</select>
						</div>
						<div class="alignleft">
							<div class="app_preset_samples clearfix" <?php if ( (string)$cset === (string)0 ) echo 'style="display:none"' ?>>
								<label class="app_mr">
								<?php _e('Sample:', 'wp-base') ?>
								</label>
								<?php foreach ( $this->a->get_legend_items() as $class=>$name ) { ?>
								<label>
									<span>
										<?php echo $name ?>:
									</span>
									<span class="app_mrl">
										<a href="javascript:void(0)" class="pickcolor <?php echo $class?> hide-if-no-js" <?php if ( (string)$cset !== (string)0 ) echo 'style="background-color:#'. wpb_get_preset($class, wpb_setting('color_set')). '"' ?>></a>
									</span>
						
								</label>
							<?php } ?>
							</div>
						</div>
						<div style="clear:both"></div>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('color_set') ?></span>
						</td>
					</tr>
						
					<tr class="app_custom_color_row" <?php if ( (string)$cset !== "0" ) echo 'style="display:none"'?>>
						<th scope="row" ><?php _e('Custom Color Set', 'wp-base')?></th>
						<td>
						<div class="app_custom_color_wrap">
						<?php foreach ( $this->a->get_legend_items() as $class=>$name ) { ?>
							<div class="app_3col">
								<div class="app_ptmr"><?php echo $name ?>:</div>
								<div class="app_mrl">
									<input style="width:60px" type="text" class="colorpicker_input" maxlength="7" name="<?php echo $class?>_color" id="<?php echo $class?>_color" value="<?php if( wpb_setting($class."_color") ) echo '#' . wpb_setting($class."_color") ?>" />
								</div>
							
							</div>
						<?php } ?>
							<span class="description app_bottom"><?php _e('If you have selected Custom color set, for each cell enter 6-digit Hex code of the color manually or use the colorpicker which will be displayed as you click on the field. Note: "Partly busy" is for admin side only.', 'wp-base') ?></span>
						</div>
						</td>
					</tr>
			<script type="text/javascript">
			jQuery(document).ready(function($){
				var hex = new Array;
				
				$('select[name="color_set"]').change(function() {
					var n = $('select[name="color_set"] :selected').val();
					if ( n == 0) { $(".app_custom_color_row").show(); $(".app_preset_samples").hide(); }
					else { $(".app_custom_color_row").hide(); 
					$(".app_preset_samples").show();
					<?php 
					foreach ( $this->a->get_legend_items() as $class=>$name ) {
						echo $class .'=[];';
						for ( $k=1; $k<=3; $k++ ) {
							echo $class .'['. $k .'] = "'. wpb_get_preset( $class, $k ) .'";';
						}
						
						echo 'var '. $class.'_obj={';
						$els ='';
						foreach ( $this->a->get_themes() as $theme ) {
							$els .= '"'.$theme . '":"' . wpb_get_preset( $class, $theme ) . '",';
						}
						echo rtrim( $els, ',');
						echo '};';
						?>
						if ( $.isNumeric(n) ) {
						<?php
							echo '$(".app_preset_samples").find("a.'. $class .'").css("background-color", "#"+'. $class.'[n]);';
						?>
						}
						else {
						<?php
							echo '$(".app_preset_samples").find("a.'. $class .'").css("background-color", "#"+'. $class.'_obj[n]);';
						?>
						}
						<?php
					} 
					?>
				}
			});

				/* https://make.wordpress.org/core/2012/11/30/new-color-picker-in-wp-3-5/ */
				var app_color_ops = {
					defaultColor: false,
					change: function(event, ui){},
					clear: function() { alert("<?php echo esc_js(__('Invalid color code','wp-base') ) ?>") },
					hide: true,
					palettes: true
				};
				$('.colorpicker_input').wpColorPicker(app_color_ops);
			});
			</script>
			
					<tr>
					<th scope="row" ><?php WpBConstant::echo_setting_name('hide_busy') ?></th>
					<td>
						<select name="hide_busy">
						<option value="no" <?php if ( wpb_setting('hide_busy') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
						<option value="yes" <?php if ( wpb_setting('hide_busy') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
						</select>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('hide_busy') ?></span>
					</td>
					</tr>
					
					<tr id="conf-form-fields">
						<th scope="row" ><?php _e('Default User Info Fields in the confirmation form', 'wp-base')?></th>
						<td class="has-checkbox">
						<?php 
						foreach ( $this->a->get_user_fields() as $f ) { ?>
							<label>
								<input type="checkbox" name="ask_<?php echo $f?>" <?php if ( wpb_setting("ask_".$f) ) echo 'checked="checked"' ?> /><span><?php echo wpb_get_field_name($f) ?></span></label>
							<?php 
						} ?>
						<label>
							<input type="checkbox" name="ask_note" <?php if ( wpb_setting("ask_note") ) echo 'checked="checked"' ?> /><span><?php echo wpb_get_field_name('note') ?></span>
						</label>
						<label>
							<input type="checkbox" name="ask_remember" <?php if ( wpb_setting("ask_remember") ) echo 'checked="checked"' ?> /><span><?php echo wpb_get_field_name('remember') ?></span>
						</label>
						<?php
						do_action( 'app_admin_select_fields' );
						?>
						<br />
						<span class="description app_bottom"><?php _e('The selected fields will be available in the confirmation area and client will be required to fill them. If selected, entering value for name, first name, last name, email, phone, address, city, postcode fields is mandatory. Note on Remember Me field: When checked, on the front end non-logged in client can select whether their form data will be saved in a cookie. It is not displayed for logged in users.', 'wp-base') ?>
						<?php 
						if ( class_exists( 'WpBUDF' ) ) { ?>
							<br />
							<?php printf( __( 'Note: You can also add unlimited number of UDFs (user defined fields) using the %s tab. These fields can be overriden by user_fields attribute of the Confirmation shortcode. Therefore, different user fields can be asked from the client on different pages. By that attribute you can also change the display order of the fields on the confirmation form.', 'wp-base' ), '<a href="'.admin_url('admin.php?page=app_settings&amp;tab=udf').'">UDFs</a>')  ?>
							<?php 
						} ?>
						</span>
						</td>
					</tr>

				</table>
			</div>
			</div>
				<p class="submit">
					<input type="hidden" name="action_app" value="save_display_general" />
					<?php wp_nonce_field( 'update_app_settings', 'app_nonce', true, true ); ?>
					<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'wp-base') ?>" />
				</p>
			</form>
		</div>
		
		<?php
		break;
		// Specialized tab - Do not use existing tab names
		case $tab:				do_action( 'app_display_'.$tab.'_tab' ); break;
		} // End of switch
		?>
		</div><!-- Wrap -->
	<?php
	}	

}

	BASE('AdminDisplaySettings')->add_hooks();
}
