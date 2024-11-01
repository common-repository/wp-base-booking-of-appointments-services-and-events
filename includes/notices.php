<?php
/**
 * WPB Notices
 *
 * Manages displaying notices
 *
 * Adapted from WP Core
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */	

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('WpBNotices') ) {
	
class WpBNotices {

	/**
     * WP BASE Core + Front [+Admin] instance
     */
	protected $a = null;
	
	/**
     * Notice buffer
     */
	private $system_notice = array();
	
	/**
     * Class of the notice
     */
	private $notice_class = 'updated';
	
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
		add_action( 'wp_footer', array( $this, 'display_notice' ) );
		add_action( 'login_footer', array( $this, 'display_notice' ) );
		add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );
	}

	/**
	 * Set a message to class variable
	 * @since 3.0
	 */	
	function set_notice( $m, $class = 'updated' ) {
		$this->system_notice[] = $m;
		$this->notice_class = $class;
	}
	
	/**
	 * Retrieve class message variable
	 * @since 3.0
	 */	
	function get_notice() {
		return !empty( $this->system_notice ) ? $this->system_notice : array();
	}

	/**
	 * Add js message for front end either with js or with jQuery dialog
	 * @since 2.0
	 */
	function front( $context=false ) {
		$title = $this->a->get_text('notice');
		$icon = 'info';
		if ( 'error' == $context ) {
			$title = $this->a->get_text('error_short');
			$icon = 'alert';
			$text = $this->a->get_text('error');
		}
		else if ( 'saved' == $context ) 
			$text = $this->a->get_text('saved');
		else if ( 'updated' == $context ) 
			$text = $this->a->get_text('updated');
		else if ( 'cancelled' == $context )
			$text = $this->a->get_text('cancelled');
		else if ( 'too_late' == $context ) {
			$text = $this->a->get_text('too_late');
			$icon = 'alert';
		}
		else if ( 'not_possible' == $context ) {
			$text = $this->a->get_text('not_possible');
			$icon = 'alert';
		}
		else if ( 'edit_disabled' == $context ) {
			$text = $this->a->get_text('edit_disabled');
			$icon = 'alert';
		}
		else if ( $this->a->get_text( $context ) && $context != $this->a->get_text( $context ) )
			$text = $this->a->get_text( $context );
		else if ( $context )
			$text = $context;
		else
			$text = $this->a->get_notice() ? $this->a->get_notice() : '';
		
		if ( !wpb_is_admin() && BASE('Pro') ) {
			$this->a->load_assets();
			$this->a->add_default_js( );
			$this->a->add2footer( '
				_app_.open_dialog('.json_encode(array( 
								'confirm_text'		=> $text,
								'confirm_title'		=> $title,
								'refresh_url'		=> -1,
								'icon'				=> $icon,							
								)
								).');
				
			');
		}
		else {
			$this->set_notice( $text );
		}
	}

	/**
	 * Displays a message as javascript alert
	 * @since 2.0
	 */	
	function display_notice( ) {
		$m = $this->get_notice();
	
		if ( !empty( $m ) ) {
			$text = implode( '\n', array_map( 'esc_js', $m ) );
			?>
			<script type="text/javascript">
				alert("<?php echo $text ?>");
			</script>
			<?php
		}
	}
	
	/**
	 * Prints an admin message
	 * @since 3.0
	 */
	function display_admin_notice( ) {
		$m = $this->get_notice();
		$class = $this->notice_class && is_string( $this->notice_class ) ? esc_attr( $this->notice_class ) : 'updated';
		
		if (!empty( $m ) ) {
			$text = implode( '<br/>', array_map( 'trim', $m ) );
			echo '<div class="'.$class.' app-dismiss app-notice is-dismissable"><p><b>[WP BASE]</b> '. $text .
			'</p><a class="notice-dismiss" data-what="general" title="'.esc_attr( __('Dismiss this notice', 'wp-base') ).'" href="javascript:void(0)"></a></div>';
		}
	}

	/**
	 * Renders an infobox
	 * @since 2.0
	 */
	function infobox( $visible, $hidden ) {
		if ( !has_action( 'admin_footer', array( $this, 'infobox_footer' ) ) )
			add_action( 'admin_footer', array( $this, 'infobox_footer' ) );
		?>
		<div class="postbox app-infobox">
			<div class="inside">
				<div class="app-infobox-col1">
				<?php if ( $hidden ): ?>
				<a href="javascript:void(0)" class="info-button" title="<?php _e('Click to toggle details', 'wp-base')?>">
					<img src="<?php echo WPB_PLUGIN_URL ."/images/help.png" ?>" alt="<?php _e('Information','wp-base')?>" />
				</a>
				<?php else: ?>
					<img src="<?php echo WPB_PLUGIN_URL ."/images/help.png" ?>" alt="<?php _e('Information','wp-base')?>" />
				<?php endif; ?>
				</div>
				
				<div class="app-infobox-col2">
					<span class="description">
					<?php echo $visible; ?>
					</span>
					<?php if ( $hidden ): ?>
						<span class="description">
						<?php _e( '<i>For details please click the "i" icon.</i>', 'wp-base'); ?>
						</span>
						<div class="app-instructions" style="display:none">
							<?php if ( is_array( $hidden ) ): ?>
							<ul class="app-info-list">
								<?php
									foreach ( $hidden as $key=>$line ) {
										if ( preg_match( '%<b>(.*?)</b>%', $line, $match ) )
											$id = 'id="'. sanitize_file_name( strtolower($match[1]) ) .'"';
										else
											$id = '';
										
										echo '<li '.$id.'>'. $line . '</li>';
									}
								?>	
							</ul>
							<?php else: ?>
								<span class="description">
								<?php echo $hidden; ?>
								</span>
							<?php endif; ?>
						</div>					
					
					<?php endif; ?>
				</div>
				<div style="clear:both"></div>
			</div>
		</div>
		<?php
		
		do_action( 'app_admin_after_info' );
	}
	
	/**
	 * js codes for infobox
	 * @since 2.0
	 */	
	function infobox_footer() {
	?>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			$(".info-button").click(function(){
				$(".app-instructions").toggle('fast');
			});
		});
		</script>

	<?php
	}

	
}
	
	BASE('Notices')->add_hooks();
}
