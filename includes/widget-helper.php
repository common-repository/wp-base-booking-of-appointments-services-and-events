<?php
/**
 * WPB Widget Helper
 *
 * Common methods for widgets
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
 
 
/**
 * Intermediary Class to pass data
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit;
 
class WpBPassData{
    public static $app_widget_global = null;
 
    public static function set($data){ 
        return self::$app_widget_global = $data;
    }
     
    public static function get(){
        return self::$app_widget_global;
    }
}

/**
 * Widget Helper for WP BASE 
 * V1.99.300
 */	
 
class WpB_Widget_Helper extends WP_Widget {
	public $default_instance = array();

	/**
     * General construct template for each child
     */
	public function __construct( $id_base, $name, $widget_options = array(), $control_options = array() ) {
		$this->a = BASE();

		add_filter( 'app_dynamic_sidebar_before', array( $this, 'read_disabled' ) ); 	// Check exlusions and remove widgets
		add_action( 'admin_footer', array( $this, 'admin_footer' ) );

		parent::__construct( $id_base, $name, $widget_options, $control_options );
	}
	
	/**
     * Check if widget is active
	 * https://codex.wordpress.org/Function_Reference/is_active_widget
     */
	function is_active() {

		if ( is_active_widget( false, false, $this->id_base ) )
			return true;
		else
			return false;
	}

	/**
     * Use 'app_dynamic_sidebar_before' and Class to pass data to an independent class (WpBWidgetControl)
	 * Find settings for each widget in the dynamic sidebar (filter is once called per widget for all instances)
     */
	function read_disabled( $irrelevant ) {
		if ( !class_exists( 'WPBWidgetsPro' ) )
			return;

		$settings = $this->get_settings(); // Includes all instances' settings

		$temp = WpBPassData::get();
		$temp = is_array( $temp ) ? $temp : array();
		foreach ( $settings as $number=>$setting ) {
			if ( isset( $setting['disable'] ) &&  $setting['disable'] )
				$temp[] = $this->id_base.'-'.$number;
		}
		WpBPassData::set($temp);
	}

	function parse_instance( $instance ) {
		return wp_parse_args( $instance, $this->default_instance );
	}

	function widget( $args, $instance ) {
		$instance = $this->parse_instance( $instance );

		extract($args);

		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;

		$this->content( $instance );

		echo $after_widget;
	}

	function title_field( $title ) {
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'wp-base' ); ?></label>
			<input type="text" class="widefat" name="<?php echo $this->get_field_name('title')?>" value="<?php echo $title?>" />
		</p>
	<?php
	}
	
	function disable_field( $disabled ) {
		if ( !class_exists( 'WPBWidgetsPro' ) )
			return;
	?>
		<p>
			<label for="<?php echo $this->get_field_id('Disable'); ?>">
			<input type="checkbox" class="widefat" name="<?php echo $this->get_field_name('disable')?>" <?php checked($disabled)?> />
			<?php _e( 'Add Page by Page: At first, disable widget from all posts/pages and transfer inclusion control to "WP BASE Include Widgets" on post editor, and add page by page.', 'wp-base' ); ?>
			</label>
		</p>
	<?php
		
	}
	
	/**
	 * Analyse content of widget settings and generate subtitles based on app shortcodes
	 * @return numerically indexed array where index is instance (number), value is the subtitle
	 */	
	function get_subtitles( ) {
		$settings = $this->get_settings();
		if ( !is_array( $settings ) || empty( $settings ) )
			return false;
		
		$subtitles = array();
		foreach ( $settings as $number => $instance ) {
			$c = ' ';
			if ( isset( $instance['content'] ) && strpos( $instance['content'], '[app_' ) !== false ) {
				$scodes = wpb_shortcodes();
				if ( preg_match( '/' . get_shortcode_regex($scodes) . '/', $instance['content'], $c_arr ) ) {
					$c = str_replace( array( '[',']','app_' ), '', $c_arr[2] );
					$c = str_replace( '_', ' ', ucwords($c) );
				}
				else
					$c = __('Unknown','wp-base');
				
				$c = ' ('. $c .')';
			}
			$subtitles[$number] = $c;
		}
		
		return $subtitles;
	}

	/**
	 * Apply the subtitles dynamically to the admin widgets page
	 * @return null
	 */	
	function admin_footer( ) {
		if ( !class_exists( 'WPBWidgetsPro' ) )
			return;

		if ( 'widgets' != wpb_get_current_screen_id( ) )
			return;
		
		if( !$subtitles = $this->get_subtitles() )
			return;
		
		?>
		<script type="text/javascript">
		jQuery(document).ready(function ($) {
			var subtitles = <?php echo wp_json_encode( $subtitles ) ?>;
			var id_base = "<?php echo $this->id_base . '-' ?>";
			$.each( subtitles, function(i,v) {
				var w_title = $("div[id$='"+id_base+i+"']").find("h3");
				w_title.append("["+i+"] "+v);
			});
		});
		</script>
		<?php
	}
	
}