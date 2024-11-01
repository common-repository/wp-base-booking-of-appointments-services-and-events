<?php
/**
 * WPB Widgets
 *
 * Methods to create and display widgets
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;

class WpB_Widget_Services extends WpB_Widget_Helper {

	var $default_instance = array(
		'title' => '',
		'number' => 5,
		'disable'=> 0,
	);

	function __construct() {
		$this->a = BASE();
		$widget_ops = array( 'description' => __( 'List of services and links to their description pages', 'wp-base') );
		parent::__construct( 'appointments_services', __( 'WP BASE Services', 'wp-base' ), $widget_ops );
	}

	function content( $instance ) {

		extract( $instance );
		
		$number = !empty( $number ) && is_numeric( $number ) ? $number : 10;

		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "base_services" . " WHERE page > 0 AND internal=0 LIMIT ".$number." ");
		
		if ( $results ) {
			echo '<ul>';
			foreach ( $results as $result ) {
				echo '<li>';
				
				echo '<a href="'.get_permalink($result->page).'" >'. $this->a->get_service_name( $result->ID ) . '</a>';
				
				echo '</li>';
			}
			echo '</ul>';
		}
	}

	function form( $instance ) {
		$instance = $this->parse_instance( $instance );
		$this->title_field( $instance['title'] );
		?>

		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e( 'Number of services to show:', 'wp-base' ); ?></label>
			
			<input type="text" size="2" name="<?php echo $this->get_field_name('number')?>" value="<?php echo $instance['number']?>" />
			
		</p>
		<?php
		$this->disable_field( $instance['disable'] );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = $this->parse_instance( $new_instance );
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = (int) $new_instance['number'];
		$instance['disable'] = isset( $new_instance['disable'] ) && $new_instance['disable'] ? 1 : 0;
		return $instance;
	}
}

class WpB_Widget_Service_Providers extends WpB_Widget_Helper {

	var $default_instance = array(
		'title' => '',
		'number' => 5,
		'disable'=> 0,
	);

	function __construct() {
		$this->a = BASE();
		$widget_ops = array( 'description' => __( 'List of service providers and links to their bio pages', 'wp-base') );
		parent::__construct( 'appointments_service_providers', __( 'WP BASE Service Providers', 'wp-base' ), $widget_ops );
	}

	function content( $instance ) {

		extract( $instance );
		
		$number = !empty( $number ) && is_numeric( $number ) ? $number : 10;

		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "app_workers" . " WHERE page >0 LIMIT ".$number." ");
		
		if ( $results ) {
			echo '<ul>';
			foreach ( $results as $result ) {
				echo '<li>';
				
				echo '<a href="'.get_permalink($result->page).'" >'. $this->a->get_worker_name( $result->ID ) . '</a>';
				
				echo '</li>';
			}
			echo '</ul>';
		}
	}

	function form( $instance ) {
		$instance = $this->parse_instance( $instance );
		$this->title_field( $instance['title'] );
		?>

		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e( 'Number of service providers to show:', 'wp-base' ); ?></label>
			
			<input type="text" size="2" name="<?php echo $this->get_field_name('number')?>" value="<?php echo $instance['number']?>" />
			
		</p>
		<?php
		$this->disable_field( $instance['disable'] );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = $this->parse_instance( $new_instance );
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = (int) $new_instance['number'];
		$instance['disable'] = isset( $new_instance['disable'] ) && $new_instance['disable'] ? 1 : 0;
		return $instance;
	}
}

/**
* Monthly Calendar widget
* Adds a monthly calendar for this month, next month, etc to the sidebar
*/	
class WpB_Widget_Monthly_Calendar extends WpB_Widget_Helper {
	

	var $default_instance = array(
		'title' 			=> '',
		'calendar_title' 	=> 'START',
		'add' 				=> 0,
		'page_id'			=> 0,
		'service_id'		=> 0,
		'disable'			=> 0,
	);

	function __construct() {
		$this->a = BASE();

		add_action( 'template_redirect', array( $this, 'check_usage'), 20 );			// Check if app shortcode widget is used
		add_action( 'wp_head', array( $this, 'wp_head' ) );
		add_action( 'wp_print_styles', array( $this, 'wp_head' ) );
		add_action( 'wp_print_scripts', array( $this, 'wp_head' ) );
		add_action( 'wp_footer', array( &$this, 'wp_footer' ) );
		$widget_ops = array( 'description' => __( 'A monthly calendar that redirects user to the selected appointment page when a free day is clicked. ', 'wp-base') );
		parent::__construct( 'appointments_monthly_calendar', __( 'WP BASE Monthly Calendar', 'wp-base' ), $widget_ops );
	}
	
	/**
     * Check if in any widget WP BASE shortcode is used and active.
	 * @return array: numeric part of widget ids if used, false if not used
     */
	function needs_load() {
		# If scripts already called, no need to test further
		if ( did_action( 'app_load_assets' ) )
			return false;
		
		$ops = get_option( 'widget_appointments_monthly_calendar' );
		$post = get_post();
		if ( empty( $ops ) || !is_array( $ops ) || !isset( $post->ID ) )
			return;
		
		$scodes = wpb_shortcodes();
		$used = $meta_ex = $meta_in = array();
		
		if ( class_exists( 'WPBWidgetsPro' ) ) {
			$meta_name = wpb_is_mobile() ? 'app_excluded_widgets_mobile' : 'app_excluded_widgets';
			$meta_ex = explode( ',', get_post_meta( $post->ID, $meta_name, true ) );
			$meta_name = wpb_is_mobile() ? 'app_included_widgets_mobile' : 'app_included_widgets';
			$meta_in = explode( ',', get_post_meta( $post->ID, $meta_name, true ) );
		}		
		
		foreach( $ops as $id=>$op ) {

			$widget_id = 'appointments_monthly_calendar-'.$id;
			
			if ( !is_active_widget( false,$widget_id,'appointments_monthly_calendar') )
				continue;
			
			if ( class_exists( 'WPBWidgetsPro' ) ) {
				if ( in_array( $widget_id, $meta_ex ) || !isset( $op['disable'] ) || ( $op['disable'] && !in_array( $widget_id, $meta_in ) )  )
					continue;
			}
			
			$used[] = $id;				
		}
		
		if ( !empty( $used ) )
			return $used;
		else
			return false;
	}

	/**
     * Check if WP BASE shortcode is used. If yes, load scripts
	 * @return none
     */
	function check_usage() {
		if ( $this->needs_load() ) {
			$this->a->load_assets( );
		}
	}

	
	function wp_head() {
		if ( !$this->is_active() )
			return;
		
		$this->a->wp_head();
	}
	
	function wp_footer( ) {
		if ( !$this->is_active() )
			return;
		
		$settings = $this->get_settings();
		
		if ( isset( $settings[$this->number] ) )
			$instance = $settings[$this->number];
		else 
			$instance = null;
			
		if ( is_array( $instance ) ) {
			extract( $instance );
			
			$href = get_permalink( $instance["page_id"] );
			
			$script  = '';
			$script .= '<script type="text/javascript">';
			$script .= "jQuery(document).ready(function($) {";
			$script .= '$("div.app_monthly_calendar_widget td.free").click(function(){';
			$script .= 'var def_timestamp = $(this).find(".appointments_select_time").val();';
			if ( $href = get_permalink( $instance["page_id"] ) )
				$script .= 'window.location.href="'.$href.'?app_timestamp="+def_timestamp;';
			else
				$script .= 'window.location.search ="?app_timestamp="+def_timestamp;';
			$script .= '});';
			$script .= "});</script>";
			
			echo $script;
		}
	}

	function content( $instance ) {

		extract( $instance );

		do_action( 'app_calendar_widget_before_content', $instance );
		
		if ( !empty( $instance['service_id'] ) && $this->a->service_exists( $instance['service_id'] ) )
			$sel_service = $instance['service_id'];
		else if ( $maybe_id = $this->a->get_sid() )
			$sel_service = $maybe_id;	
		else if ( $maybe_id = $this->a->read_service_id() )
			$sel_service = $maybe_id;
		else
			$sel_service = $this->a->get_first_service_id();
		
		$args = array(	'service'			=> $sel_service,
						'title'				=> $calendar_title,
						'notlogged'			=> 0,
						'logged'			=> 0,
						'add'				=> $add,
						'class'				=> 'app_monthly_calendar_widget',
						'_force_min_time'	=> 60,
						'_widget'			=> 1,
						);
		
		echo BASE()->calendar_monthly( $args );
		
		do_action( 'app_calendar_widget_after_content', $instance );
	}

	function form( $instance ) {
		$instance = $this->parse_instance( $instance );
		$this->title_field( $instance['title'] );
		?>
		<p>
			<label title="<?php echo esc_attr( __( 'Calendar title accept placeholders "START_END","START", "END", "LOCATION", "WORKER", "SERVICE". Default: START (Selected month)', 'wp-base' ) ); ?>" for="<?php echo $this->get_field_id('calendar_title'); ?>"><?php _e( 'Calendar title:', 'wp-base' ); ?></label>
			
			<input type="text" style="width:100%" name="<?php echo $this->get_field_name('calendar_title')?>" value="<?php echo esc_attr( $instance['calendar_title'] ) ?>" />
			
		</p>

		<p>
			<label title="<?php echo esc_attr( __( 'The page that the client will be redirected when they click a free day slot (Supports only WP Page type).', 'wp-base' ) ); ?>" for="<?php echo $this->get_field_id('page_id'); ?>"><?php _e( 'Appointment page:', 'wp-base' ); ?></label>
			<?php
			if ( $dropdown = wp_dropdown_pages( array( 'echo' => false, 'selected' => $instance['page_id'], 'show_option_none'=>__('Current Page (refresh)','wp-base'),'name' => $this->get_field_name('page_id'), 'class' => 'app_dropdown_pages' ) ) )
				echo $dropdown;
			else
				echo '<span class="app_b app_bottom">' . __( 'There are no pages!', 'wp-base' ) .'</span>';
			?>
		</p>
		
		<p>
			<label title="<?php echo esc_attr( __( 'Service to be used as the basis of availability. Selecting "auto" will pick the service on the page if there is one. Otherwise first service on services list will be used.', 'wp-base' ) ); ?>" for="<?php echo $this->get_field_id('service_id'); ?>"><?php _e( 'Preferred service:', 'wp-base' ); ?></label>
			<br/>
			<?php
			echo '<select name="'.$this->get_field_name('service_id').'">';
			echo '<option value="0">'. __( 'Auto', 'wp-base' ). '</option>';
			$services = $this->a->get_services( 'name' );
			if ( $services ) {
				foreach ( $services as $service ) {
					echo '<option value="'.$service->ID.'" '. selected( $service->ID, $instance['service_id'], false ). '>'. $this->a->get_service_name( $service->ID ). '</option>';
				}
			}
			echo '</select>';
			?>
		</p>
		
		<p>
			<label title="<?php echo esc_attr( __( 'You can use more than one instance of this widget to show several months on the same page. To accomplish this, set this value 0 for the current month, 1 for the next month, and so on.', 'wp-base' ) ); ?>" for="<?php echo $this->get_field_id('add'); ?>"><?php _e( 'Months to add to current month:', 'wp-base' ); ?></label>
			
			<input type="text" style="width:20%" name="<?php echo $this->get_field_name('add')?>" value="<?php echo $instance['add']?>" />
			
		</p>
		<?php
		$this->disable_field( $instance['disable'] );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = $this->parse_instance( $new_instance );
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['calendar_title'] = sanitize_text_field( $new_instance['calendar_title'] );
		$instance['add'] = (int) $new_instance['add'];
		$instance['page_id'] = (int) $new_instance['page_id'];
		$instance['service_id'] = (int) $new_instance['service_id'];
		$instance['disable'] = isset( $new_instance['disable'] ) && $new_instance['disable'] ? 1 : 0;
		return $instance;
	}
}

class WpB_Widget_Theme_Selector extends WpB_Widget_Helper {

	var $default_instance = array(
		'title' => '',
		'cap' => '',
		'disable'=> 0,
	);

	function __construct() {
		$this->a = BASE();
		$widget_ops = array( 'description' => __( 'Lets you select a WP BASE theme on the front end, e.g. during website design', 'wp-base') );
		parent::__construct( 'appointments_theme_selector', __( 'WP BASE Theme Selector', 'wp-base' ), $widget_ops );
	}

	function content( $instance ) {

		extract( $instance );
		
		echo $this->a->theme_selector( array( 'cap' => $cap ) );

	}

	function form( $instance ) {
		$instance = $this->parse_instance( $instance );
		$this->title_field( $instance['title'] );
		?>

		<p>
			<label for="<?php echo $this->get_field_id('cap'); ?>"><?php _e( 'User capability to view the selector:', 'wp-base' ); ?></label>
			
			<input type="text" size="2" name="<?php echo $this->get_field_name('cap')?>" value="<?php echo $instance['cap']?>" />
			
		</p>
		<?php
		$this->disable_field( $instance['disable'] );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = $this->parse_instance( $new_instance );
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['cap'] = sanitize_text_field( $new_instance['cap'] );
		$instance['disable'] = isset( $new_instance['disable'] ) && $new_instance['disable'] ? 1 : 0;
		return $instance;
	}
}
