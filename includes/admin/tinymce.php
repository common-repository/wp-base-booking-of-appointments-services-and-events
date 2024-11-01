<?php
/**
 * WPB Admin tinyMCE
 *
 * Tool insert shortcodes to post editor
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBAdminTinymce' ) ) {
	
class WpBAdminTinymce{

	/**
     * Add admin actions
     */
	function add_hooks() {
		add_action( 'wp_ajax_appTinymceOptions', array( $this, 'tinymce_options'), 100 );
		add_action( 'admin_init', array( $this, 'tinymce_load'), 20 );
	}	
	
	/**
	 *	Adds tinyMCE editor to the post editor
	 *	@since 2.0  
	 */
	function tinymce_load() {
		if ( (current_user_can('edit_posts') || current_user_can('edit_pages')) && get_user_option('rich_editing') == 'true') {
			add_filter( 'mce_external_plugins', array($this, 'tinymce_add_plugin') );
			add_filter( 'mce_buttons', array($this,'tinymce_register_button') );
			add_filter( 'mce_external_languages', array($this,'tinymce_load_langs') );
		}
	}

	/**
	 * @see		http://codex.wordpress.org/TinyMCE_Custom_Buttons
	 * @since 2.0  
	 */
	function tinymce_register_button($buttons) {
		array_push($buttons, "separator", "wpbaseshortcodes");
		return $buttons;
	}

	/**
	 * @see		http://codex.wordpress.org/TinyMCE_Custom_Buttons
	 * @since 2.0  
	 */
	function tinymce_load_langs($langs) {
		$langs["wpbaseshortcodes"] =  WPB_PLUGIN_URL . '/includes/admin/tinymce/langs/langs.php';
		return $langs;
	}

	/**
	 * @see		http://codex.wordpress.org/TinyMCE_Custom_Buttons
 	 * @since 2.0  
	 */
	function tinymce_add_plugin($plugin_array) {
		$plugin_array['wpbaseshortcodes'] = WPB_PLUGIN_URL . '/includes/admin/tinymce/editor_plugin.js';
		return $plugin_array;
	}
	
	/**
	 * TinyMCE dialog content
	 * @since 2.0  
	 */
	function tinymce_options() {
		$selection = wp_unslash( $_GET['selection'] );		// Get copied content
		$selection = str_replace( ']', ' ]', $selection );	// A little trick to make regex simple
	
		$sel_shortcode = '';
		$pars = array();
		if ( preg_match( '%\[app_(.*?) (.*?)\]%s', $selection, $m ) ) {
			$sel_shortcode = 'app_'. $m[1];					// Selected shortcode in the editor
			$atts = shortcode_parse_atts( $m[2] ); 			// Get attributes of the selected shortcode with values
			if ( is_array( $atts ) )
				$pars = $atts;
		}
		
		$title = $sel_shortcode ? __("WP BASE Shortcode Edit", 'wp-base') : __("WP BASE Shortcode Insert", 'wp-base'); 
		$insert = $sel_shortcode ? __("Replace", 'wp-base') : __("Insert", 'wp-base');
		$adminpage = !empty( $_GET['adminpage'] ) ? $_GET['adminpage'] : ''; // "post-php" for post editor, "widgets-php" for widgets
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
				<title><?php echo esc_html( $title ) ?></title>
				<script type="text/javascript" src="<?php echo includes_url() ?>js/tinymce/tiny_mce_popup.js"></script>
				<link rel="stylesheet" href="<?php echo includes_url() ?>js/tinymce/skins/lightgray/content.min.css" type="text/css" media="all" />
				<script type="text/javascript" src="<?php echo includes_url() ?>js/jquery/jquery.js"></script>
				<script type="text/javascript" src="<?php echo WPB_PLUGIN_URL ?>/js/jquery.autosize-min.js"></script>

				<script type="text/javascript">
				
				jQuery(document).ready(function($){
					$('textarea').autosize();
					var selection = <?php echo json_encode( $selection ) ?>;

					var insertAppShortcode = function (ed) {
						var selected = $('#select_shortcode').val();
						var output = '';
						if ( selected != 'app_is_mobile' && selected != 'app_is_not_mobile' && selected != 'app_hide' && selected != 'app_show' && selected != 'app_no_html' ) {
							output = '[' +  selected + ' ';
							add = '';
							$.each($('.input_'+selected), function(){
								var val = $(this).val();
								var shortcode = $(this).parent().siblings().find('label').text();
								if ( $.trim(val) !=''){
									add = add + ' ' + shortcode + '="'+val+'" ';
								}
							});
							output = $.trim(output + add);
							output = output + ']';
						}
						else{
							output = '[' +  selected + ']' + selection + '[/' +  selected + ']';
						}
						
						tinyMCEPopup.execCommand('mceInsertContent', 0, output);
						tinyMCEPopup.editor.execCommand('mceRepaint');
						tinyMCEPopup.editor.focus();
						tinyMCEPopup.close();
					};
					
					$('#select_shortcode').change(function(){
						var selected = $('#select_shortcode').val();
						$('.desc_all').hide();
						$('.table_all').hide();
						if (parseInt(selected) != -1 ) {
							$('.desc_general').hide();
							$('.desc_'+selected).show();
							$('.table_'+selected).show();
						}
						else {
							$('.desc_general').show();
						}
					});
					
					$('#insert').click(function(e){
						e.preventDefault();
						insertAppShortcode();
					});
					
				});
				</script>
				<style type="text/css">
				td.info {
					vertical-align: top;
					color: #777;
				}
				</style>

				
			</head>
			<body class="mce-content-body">

				<form method="post" action="#">
				
				<?php
					$shortcodes = WpBConstant::shortcode_desc();
					
					$is_for_email = $adminpage === 'post-php' || $adminpage === 'widgets-php' ? false : true; 
					$allowed_in_email = array( 'app_list', 'app_show', 'app_hide' ); // In emails only list, hide and show allowed
					
					// An array defining which shortcode reside in which optgroup
					$opt = array( 0, 1, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 3, 3, 3, 3, 3, 3, 3, 3, 4, 4, 4, 4, 0);
					$opt_text = array( '', __('Compact Booking','wp-base'), __('Modular Booking', 'wp-base'), __('Helpers','wp-base'), __('Addon Shortcodes','wp-base') );
					$opt_no = $sc_no = 1;
					echo '<select name="select_shortcode" id="select_shortcode">';
					echo '<option value="-1">' . __('Select Shortcode','wp-base'). '</option>';
					if ( class_exists( 'WpBPro' ) && !$is_for_email ) {
						foreach ( $shortcodes as $shortcode => $data ) {
							if ( $opt[$sc_no] == $opt_no && $opt[$sc_no-1] != $opt_no )
								echo '<optgroup label="'.$opt_text[$opt_no].'" >';
							echo '<option value="'.$shortcode.'" '.selected($sel_shortcode,$shortcode,false).'>' . $data['name']. '</option>';
							if ( $opt[$sc_no+1] != $opt_no ) {
								echo '</optgroup>';
								$opt_no++;
							}
							$sc_no++;
						}
					}
					else {
						foreach ( $shortcodes as $shortcode=>$data ) {
							if ( $is_for_email && !in_array( $shortcode, $allowed_in_email ) )
								continue;
							
							echo '<option value="'.$shortcode.'" '.selected($sel_shortcode,$shortcode,false).'>' . $data['name']. '</option>';
						}
					}
					echo '</select>';
					
					if ( !$sel_shortcode ) {
						echo '<div class="desc_general" style="margin:10px 0 10px 0">';
						echo '<p>';
						_e( 'From the above pulldown menu, select shortcode you want to use, fill in the parameter fields and click Insert when done. You can also edit an existing shortcode by completely selecting it with mouse or keyboard <em>before</em> clicking the Insert/Edit WP-BASE Shortcode button.', 'wp-base' ); 					
						if ( class_exists( 'WpBPro' ) && !$is_for_email ) {
							echo '<p>';
							_e( '<b>Compact Booking</b> shortcode <code>[app_book]</code> is an easy to configure shortcode which is sufficent to create a fully functional appointment page by itself. It will do the job for most of the applications, even with default settings. However, its settings and layouts it can create are limited.', 'wp-base' ); 					
							echo '</p>';
							echo '<p>';
							_e( 'For more complex applications, you can use combinations of <b>Modular Booking</b> shortcodes. Some examples are combining different calendars on the same page, starting booking from a certain future date, displaying more than one pagination blocks, moving some selections to widget area, booking on behalf, etc. To create a fully functional appointment page you need to use 1) Exactly one <code>[app_confirmation]</code> 2) At least one of the <code>[app_book_table]</code>, <code>[app_book_now]</code>, <code>[app_monthly_schedule]</code>, <code>[app_schedule]</code> shortcodes.', 'wp-base' ); 					
							echo '</p>';
						}
						echo '<p>';
						printf( __( 'For usage examples please see our %1$s and %2$s.', 'wp-base' ), '<a href="'.WPB_DEMO_WEBSITE.'" target="_blank">'.__('demo website', 'wp-base' ).'</a>', '<a href="'.WPB_ADDON_DEMO_WEBSITE.'" target="_blank">'.__('Addons demo website', 'wp-base' ).'</a>' ); 					
						echo '</p>';
						echo '</div>';
					}
					
					foreach ( $shortcodes as $shortcode=>$data ) {
						if ( $sel_shortcode == $shortcode )
							$d = '';
						else
							$d = 'display:none;';
						echo '<div class="desc_all desc_'.$shortcode.'" style="'.$d.'margin:10px 0 10px 0"><b>' . __('Name:', 'wp-base'). '</b> ' . $data['name'] . '<br />';
						echo '<b>' . __('Shortcode:', 'wp-base'). '</b> ['. $shortcode . ']<br />';
						echo '<b>' . __('Description:', 'wp-base'). '</b> ' . $data['description'];
						if ( isset( $data['example'] ) )
							echo '<br /><b>' . __('Example:', 'wp-base'). '</b> ' . $data['example'];
						echo '</div>';
					}
				
				?>
					<div id="general_panel" class="panel current">
	
						<?php foreach ( $shortcodes as $shortcode => $data ) { 
						if ( $sel_shortcode == $shortcode )
							$d = '';
						else
							$d = 'style="display:none;"';
						?>
						  <table <?php echo $d ?> class="table_all table_<?php echo $shortcode ?>" border="0" cellpadding="4" cellspacing="0">
								<tr>
									<th style="width:10%"><?php _e('Parameter', 'wp-base') ?></th>
									<th style="width:20%"><?php _e('Value', 'wp-base') ?></th>
									<th style="width:70%"><?php _e('Description', 'wp-base') ?></th>
								</tr>
								<?php
							if ( isset( $data['parameters' ] ) ) {								
								// ksort( $data['parameters' ] );
								foreach ( $data['parameters'] as $par_name => $par_desc ) {	?>
								<tr>
									<td><label><?php echo $par_name; ?></label></td>
									<td>
										<textarea style="min-height:50px;width:95%" class="input_<?php echo $shortcode ?>"><?php if ( $sel_shortcode == $shortcode && isset($pars[$par_name]) ) echo $pars[$par_name]; ?></textarea>
									</td>
									<td class="info"><?php echo $par_desc ?></td>
								</tr>
						<?php }
							}
						}
							?>
						</table>

					</div>

					<div class="mceActionPanel" style="margin-bottom:10px">
						<div style="float: left">
							<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'wp-base'); ?>" onclick="tinyMCEPopup.close();" />
						</div>

						<div style="float: right">
							<input type="submit" id="insert" name="insert" value="<?php echo $insert; ?>" />
						</div>
						
						<div style="clear:both"></div>
					</div>
				</form>
			</body>
		</html>
		<?php
		exit(0);
	}

}
	BASE('AdminTinymce')->add_hooks();
}	