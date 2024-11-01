<?php
/**
 * WPB Schedules
 *
 * Allows creation of monthly and weekly schedule tabs on admin and user pages
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBSchedules' ) ) {

class WpBSchedules {
	
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
		add_action( 'app_menu_before_all', array( $this, 'add_menu' ), 20 ); 						// Called to add menu item
		add_action( 'wp_ajax_app_bookings_in_tooltip', array( $this, 'bookings_in_tooltip' ) );		// Show tooltips
		add_filter( 'app_weekly_calendar_cell_fill', array( $this, 'fill' ), 10, 2 );				// Fill weekly cell with booking info
	}

	/**
     * Add admin menu
     */
	function add_menu(){
		wpb_add_submenu_page('appointments', __('WPB Schedules','wp-base'), __('Schedules','wp-base'), array(WPB_ADMIN_CAP,'manage_schedules'), "app_schedules", array($this,'render'));
	}

	/**
	 * Find requested worker id
	 * @param all: Force to select all workers instead of current user
	 * @since 2.0
	 */	
	function find_req_worker_id( ) {
		if ( $maybe_worker = $this->a->read_worker_id() )
			$user_id = $maybe_worker;
		else if ( !empty( $_POST['app_worker_id'] ) && 'all' == $_POST['app_worker_id'] )
			$user_id = 'all';
		else if ( !empty( $_POST['app_user_id'] ) )
			$user_id = $_POST['app_user_id'];
		else
			$user_id = get_current_user_id();
		
		return $user_id;
	}
	
	/**
	 * Dynamically show bookings in qtip content
	 * @since 2.0
	 */	
	function bookings_in_tooltip() {
		if ( empty( $_POST['app_val'] ) )
			wp_send_json( array( 'result' => esc_js(__('Unexpected error','wp-base')) ) );

		$slot	= new WpBSlot( $_POST['app_val'] );
		$start	= $slot->get_start();
		$end	= $slot->get_end();
		$worker = $slot->get_worker();
		$what	= 'all' === $slot->get_service() ? 'all' : 'worker'; # For service=all show all bookings
		
		if ( isset( $_POST['weekly'] ) && $_POST['weekly'] ) {
			$tooltip_status = apply_filters( 'app_list_tooltip_status', 'paid,confirmed,pending,running', 'weekly_schedule' );
			$shortcode = '[app_list _wide_coverage=1 user_id="'. $worker . '" status="'.$tooltip_status.'" what="'.$what.'" columns="id,service,client,worker,status" order_by="start" _tablesorter="0" start="'.date("Y-m-d H:i:s",$start).'" end="'.date("Y-m-d H:i:s",$end).'" title="'.date_i18n($this->a->dt_format, $start).'"]';
		}
		else {
			$tooltip_status = apply_filters( 'app_list_tooltip_status', 'paid,confirmed,pending,running', 'monthly_schedule' );
			$date = date('Y-m-d', $start );
			$shortcode = '[app_list _wide_coverage1 user_id="'.$worker.'" _as_tooltip="1" status="'.$tooltip_status.'" what="'.$what.'" columns="ID,service,client,worker,time,status" order_by="start" _tablesorter="0" start="'.$date.' 00:00" end="'.$date.' 23:59:59" title="'.date_i18n($this->a->date_format, $start).'"]';
		}

		wp_send_json( array( 'result' => do_shortcode( $shortcode ) ) );
	}

	/**
	 * Generate a set of default weekly shortcodes to be used in user pages
	 * @param count: How many weeks
	 * @param worker: Id of the worker. Can also be zero (unassigned provider)
	 * @param class: Class to add to the calendar
	 * @since 2.0
	 * @return array
	 */	
	function weekly_shortcodes( $count=4, $worker=false, $class='' ) {
		
		$worker = false === $worker ? $this->find_req_worker_id() : $worker;
		$service = $this->a->get_min_max_service_by_worker( $worker );
		
		# Seasonal wh settings are not used for all case 
		if ( 'all' === $worker )
			remove_filter( 'app_combine_alt', array( BASE('Annual'), 'combine_alt' ), 10 );

		$add_class	= 1 == $count ? ' app-has-inline-cell' : '';
		$daily		= strpos( $class, 'app-schedule-daily' ) !== false ? 1 : 0;
		
		$weekly_shortcodes = array();

		for ( $add=0; $add<$count; $add=$add+1 ) {
			$weekly_shortcodes[] = $this->a->calendar_weekly( 
				array(
				'title'				=> $daily ? "START" : "START_END",
				'logged'			=> 0,
				'worker'			=> $worker,
				'service'			=> isset($service->ID) ? $service->ID : 0,
				'add'				=> $add,
				'class'				=> $class. $add_class,
				'display'			=> 'with_break',				
				'_inline'			=> 1 === $count ? 1 : '',
				'_force_min_time'	=> 60,
				'_width'			=> $class ? '' : absint(100/$count),
				'_admin'			=> 1,
				'_daily'			=> $daily,
				) 
			);
		}
		
		return $weekly_shortcodes;
	}

	/**
	 * Generate a set of default monthly shortcodes to be used in user pages
	 * @param count: How many months
	 * @param worker: Id of the worker. Can also be zero (unassigned provider)
	 * @param class: Class to add to the calendar
	 * @since 2.0
	 * @return array
	 */	
	function monthly_shortcodes( $count=3, $worker=false, $class='' ) {
		
		$worker = false === $worker ? $this->find_req_worker_id() : $worker;
		$service = $this->a->get_min_max_service_by_worker( $worker );
		
		if ( 'all' === $worker )
			remove_filter( 'app_combine_alt', array( BASE('Annual'), 'combine_alt' ), 10 );
		
		$monthly_shortcodes = array();

		for ( $add=0; $add<$count; $add=$add+1 ) {
			$monthly_shortcodes[] = $this->a->calendar_monthly( 
				array( 
				'title'			=> "START",
				'logged'		=> 0,
				'service'		=> isset($service->ID) ? $service->ID : 0,
				'worker'		=> $worker,
				'add'			=> $add,
				'class'			=> $class,
				'_no_timetable'	=> 1, 
				'_preload'		=> 1, 
				'_width'		=> $class ? '' : number_format( abs( 100/$count ), 3 ),
				'_admin'		=> 1,
				) 
			);
		}
		
		return $monthly_shortcodes;
	}
	
	/**
	 * Fill the weekly calendar cell for daily and weekly
	 * @since 3.0
	 * @return array
	 */
	 function fill( $fill_arr, $slot ) {
		
		if ( ! $slot->calendar->is_inline() )
			return $fill_arr;
		
		$fill = '';
		$worker = $slot->get_worker();
		$service = $slot->get_service();

		if ( 'all' === $service )
			$apps = $this->a->get_reserve_apps( date( "W", $slot->get_start() ) );
		else if ( $worker )
			$apps = $this->a->get_daily_reserve_apps_by_worker( $slot->get_worker(), date( 'Y-m-d', $slot->get_start() ) );
		else if ( $this->a->get_nof_workers() )
			$apps = $this->a->get_reserve_unassigned_apps( $slot->get_location(), $slot->get_service(), 0, date( "W", $slot->get_start() ) );
		else
			$apps = $this->a->get_daily_reserve_apps_by_service( $slot->get_service(), date( 'Y-m-d', $slot->get_start() ) );
		
		if ( $apps ) {
			$found = false;
			$more = '';
			$class_name_arr = array();
			foreach ( $apps as $app ) {
				$slot_start	= $slot->get_start();
				$slot_end	= $slot->get_end();
				$start		= strtotime( $app->start );
				$end		= strtotime( $app->end );
				
				if ( $end < $slot_start )	# Too early
					continue;
					
				if ( $start > $slot_end )	# Too late
					continue;
				
				$content = $this->a->_replace( wpb_setting( 'schedule_content', 'CLIENT_LINK START_TIME-END_TIME' ), $app, 'subject' );
				$margin_top = $margin_bottom = 0;
				$height = 62; // In px (% does not work)
				
				if ( $start >= $slot_start && $end <= $slot_end ) {	# Completely inside
					$found			= true;
					$margin_top		= $height * number_format( ($start - $slot_start)/36, 2 )/100;
					$margin_bottom	= $height * number_format( ($slot_end - $end)/36, 2 )/100;
					$height			= $height - $margin_top - $margin_bottom;
					break;
				}
				else if ( $start < $slot_start && $slot_start < $end && $end <=$slot_end ) {	# Started before, ends inside or at cce
					$found			= true;
					$margin_bottom	= $height * number_format( ($slot_end - $end)/36, 2 )/100;
					$height			= $height - $margin_bottom;
					$more			= 'border-top:none;';
					$content		= '';
					$class_name_arr[] = 'app_no_border_top';
					break;
				}
				else if ( $slot_start <= $start && $start < $slot_end && $slot_end < $end ) {	# Starts inside or just at ccs and ends outside
					$found		= true;
					$margin_top = $height * number_format( ($start - $slot_start)/36, 2 )/100;
					$height		= $height - $margin_top;
					$more		= 'border-bottom:none;';
					$class_name_arr[] = 'app_no_border_bottom';
					break;
				}
				else if ( $start < $slot_start && $end > $slot_end ) {	# Starts outside and ends outside
					$found		= true;
					$height		= $height + 2;
					$more		= 'border-bottom:none;border-top:none;';
					$content	= '';
					$class_name_arr[] = 'app_no_border_top';
					$class_name_arr[] = 'app_no_border_bottom';
					break;
				}
			}
			if ( $found ) {
				$class_name_arr[] = 'has_inline_appointment';
				$fill .= '<div class="app-cell-inline" style="'.$more.'height:'.$height.'px;margin-top:'.$margin_top.'px;margin-bottom:'.$margin_bottom.'px;">' . $content . '</div>';
			}
		}
		
		if ( $fill ) {
			$fill_arr['fill'] = $fill;
			$fill_arr['class_name'] = !empty( $class_name_arr ) ? implode( ' ', $class_name_arr ) : '';
		}
		
		return $fill_arr;
	}

	/**
	 * Generate HTML for schedules
	 * @since 2.0
	 */
	function render() {

		wpb_admin_access_check( 'manage_schedules' );
		
		global $wpdb, $pagenow, $current_user;
		
		$worker_id = !empty($_GET['app_worker_id']) ? $_GET['app_worker_id'] : $this->a->read_worker_id();

	?>
		<div class="wrap app-page">
		<h2 class="app-dashicons-before dashicons-calendar-alt"><?php echo __('Booking Schedules','wp-base'); ?></h2>
		
			<?php
			$workers = $this->a->get_workers();
			$default_worker = $this->a->get_default_worker_id();
			$default_worker_name = $this->a->get_worker_name( $default_worker, false );
			$href = wpb_add_query_arg( array('app_worker_id'=>false,"rand"=>1) ) ."&app_worker_id=";
			?>
			<div class='app-submit'>
				<span class='app_provider_list'>
				<?php _e('List for:', 'wp-base')?>
				</span>
				&nbsp;
				<select onchange="if (this.value) window.location.href='<?php echo $href ?>'+this.value" class="app_select_workers" id="app_worker_id" name="app_worker_id">
					<option ><?php _e('Select provider', 'wp-base') ?></option>
					<option <?php selected( 'all', $worker_id ); ?> value="all"><?php _e('All', 'wp-base') ?></option>
					<option <?php selected( $worker_id, $default_worker ); ?> value="<?php echo $default_worker ?>"><?php printf( __('Business Rep. (%s)', 'wp-base'), $default_worker_name) ?></option>
					<?php
					if ( $workers ) {
						foreach ( $workers as $worker ) {
							// Do not show default worker a second time
							if ( $default_worker == $worker->ID )
								continue;
							if ( $worker_id == $worker->ID )
								$s = " selected='selected'";
							else
								$s = '';
							echo '<option value="'.$worker->ID.'"'.$s.'>' . $this->a->get_worker_name( $worker->ID, false ) . '</option>';
						}
					}
					?>
				</select>
			</div>
		
			<h3 class="nav-tab-wrapper">
			<?php
			$tab = ( !empty($_GET['tab']) ) ? $_GET['tab'] : 'weekly';

			$tabs = array(
				'weekly'      	=> __('Weekly', 'wp-base'),
				'4weeks' 	    => __('4 Weeks', 'wp-base'),
				'monthly' 	    => __('Monthly', 'wp-base'),
				'3months'		=> __('3 Months', 'wp-base'),
			);
			
			$tabhtml = array();

			
			$tabs = apply_filters( 'app_schedules_tabs', $tabs );

			$class = ( 'weekly' == $tab ) ? ' nav-tab-active' : '';

			foreach ( $tabs as $stub => $title ) {
				$class = ( $stub == $tab ) ? ' nav-tab-active' : '';
				if ( isset( $_GET['app_worker_id'] ) )
					$href = wpb_add_query_arg( array( 'app_worker_id' => $_GET['app_worker_id'] ), admin_url( 'admin.php?page=app_schedules&amp;tab=' . $stub ) );
				else
					$href = admin_url( 'admin.php?page=app_schedules&amp;tab=' . $stub );
				$tabhtml[] = '	<a href="' . $href . '" class="nav-tab'.$class.'" id="app_tab_'.$stub.'">'.$title.'</a>';
			}

			echo implode( "\n", $tabhtml );
			?>
			</h3>
			<div class="clear"></div>
			<div id="poststuff" class="metabox-holder">
			<div class="postbox">
				<div class="inside">
			<?php 
			if ( $worker_id ) {
				
				switch( $tab ) {
					case 'daily'	:
						echo $this->a->pagination( array( 'select_date'=>1, 'disable_legend'=>1, 'unit'=>'day', 'step'=>1 ) );
						$wscodes = $this->weekly_shortcodes(1, false, 'app-schedule-daily');
						echo current( $wscodes );
						echo $this->a->pagination( array( 'select_date'=>0, 'unit'=>'day', 'step'=>1 ) );
						
					break;

					case 'weekly'	:
						echo $this->a->pagination( array( 'select_date'=>1, 'disable_legend'=>1, ) );
						$wscodes = $this->weekly_shortcodes(1);
						echo current( $wscodes );
						echo $this->a->pagination( array( 'select_date'=>0, ) );
						
					break;
					
					case '4weeks':
						echo $this->a->pagination( array( 'select_date'=>1, 'disable_legend'=>1, 'step'=>4 ) );
						foreach( $this->weekly_shortcodes(4) as $scode ) {
							echo $scode;
						}
						echo '<div style="clear:both"></div>';
						echo $this->a->pagination( array( 'select_date'=>0, 'step'=>4 ) );
						break;

					case 'monthly':
						echo $this->a->pagination( array( 'select_date'=>1, 'disable_legend'=>1, 'unit'=>'month', 'step'=>1 ) );
						$mscodes = $this->monthly_shortcodes(1);
						echo current( $mscodes );
						echo $this->a->pagination( array( 'select_date'=>0, 'unit'=>'month', 'step'=>1 ) );
						break;
						
					case '3months':
						echo $this->a->pagination( array( 'select_date'=>1, 'disable_legend'=>1, 'unit'=>'month', 'step'=>3 ) );
						foreach( $this->monthly_shortcodes(3) as $scode ) {
							echo $scode;
						}
						echo '<div style="clear:both"></div>';
						echo $this->a->pagination( array( 'select_date'=>0, 'unit'=>'month', 'step'=>3 ) );						
						break;	
				}
			}
			else
				_e( 'Nothing selected','wp-base');
			
			?>
			</div>
			</div>		
			</div>
		</div>
		<?php
	}
	
}
	BASE('Schedules')->add_hooks();
}