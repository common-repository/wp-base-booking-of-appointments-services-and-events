<?php
/**
 * WPB Holidays
 *
 * Handles holiday settings, definitions and methods for services and service providers
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBHolidays' ) ) {

class WpBHolidays {
	
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
		add_action( 'app_save_settings', array( $this, 'save_holidays' ), 12 );
		add_filter( 'appointments_business_tabs', array( $this, 'add_tab' ), 14 ); 		// Add tab
		add_action( 'app_business_holidays_tab', array( $this, 'render_tab' ) );		// Display HTML settings on Business Settings
	}
	

	/**
	 * Check if today (or a 24 hour time period between ccs-cce) is holiday for a worker or service
	 * @param $slot		WpBSlot object
	 * @return bool
	 */		
	function is_holiday( $slot, $subject = 'worker' ) {
		$who = 'worker' === $subject ? $slot->get_worker() : $slot->get_service();
		$h = $this->a->get_business_options('holidays');
		$h_set = isset( $h[$subject][$who] ) ? $h[$subject][$who] : false;
		if ( !$h_set )
			return false;
		
		$slot_start = $slot->get_start();
		$slot_end = $slot->get_end();
		
		// Service provider local time is taken into account
		if ( 'worker' == $subject &&  $who )
			$offset = $this->a->get_client_offset( $slot_start, get_user_meta( $who, 'app_timezone_string', true ) );
		else
			$offset = 0;
		
		$holidays = explode( ',', $h_set );
		foreach ( $holidays as $holiday ) {
			$h_start = strtotime( $holiday, $this->a->local_time + $offset );
			$h_end = $h_start + 86400;
			if ( $slot_end > $h_start && $slot_start < $h_end )
				return true;
		}
	
		return false;
	}
	
	/**
	 * Check if today is holiday for a worker/service in Wh domain
	 * @return bool
	 */		
	function is_holiday_wh( $start, $end, $who, $year_week_no, $subject = 'worker' ) {
		$year = substr( $year_week_no, 0, 4 );
		$week = substr( $year_week_no, 4 );
		$ts = wpb_week2time( $week, $year );
		// Shift 1 sec
		$slot_start = BASE('WH')->from( $start+0.004, $ts );
		$slot_end = BASE('WH')->from( $end-0.004, $ts );
		$slot = new WpBSlot( new WpBCalendar( 0, $who, $who ), $slot_start, $slot_end );
		
		return $this->is_holiday( $slot, $subject );
	}
	
/****************************************************
* Methods for admin
*****************************************************
*/

	/**
	 *	Add tab
	 */
	function add_tab( $tabs ) {
		if ( wpb_admin_access_check( 'manage_working_hours', false ) )
			$tabs['holidays'] = __('Holidays', 'wp-base');
		
		return $tabs;
	}

	/**
	 * Display HTML codes
	 * @param $profileuser_id: If this is called by a user from his profile	 
	 */
	function render_tab( $profileuser_id=false, $bp=false ) {
		
		wpb_admin_access_check( 'manage_working_hours' );
		?>
		<div id="poststuff" class="metabox-holder">
			<?php 

			wpb_infobox( __('Here you can define your holidays during which related service or provider will be unavailable. Note that every service and provider has own holidays. To set holidays, select related service or provider from List for pulldown, select days to mark as holiday on the yearly calendar and click Save. You can browse through years using &larr; and &rarr; buttons on the calendar.', 'wp-base') );
			
			$workers = $this->a->get_workers();
			$default_worker = $this->a->get_default_worker_id();
			$default_worker_name = $this->a->get_worker_name( $default_worker, false );
			// Defaults
			$subject_selected = isset($_POST['app_select_subject_for_holiday']) ? $_POST['app_select_subject_for_holiday'] : 'worker|'.$default_worker; 

			?>
			<div class='postbox app_prpl'>
			<div class='app-submit app_2column'>
			<div class="app_mt">
				<form method="post">
				<span class='app_provider_list app_mr10'><?php _e('List for', 'wp-base')?></span>
					<select id="app_select_subject_for_holiday" name="app_select_subject_for_holiday">
					<?php if ( class_exists( 'WpBSP' ) ) { ?>
						<optgroup label="<?php _e('Service Providers','wp-base') ?>" class="optgroup_worker">
					<?php } 
						if ( !in_array( $default_worker, (array)$this->a->get_worker_ids() ) ) { 
					?>	
						<option value="worker|<?php echo $default_worker ?>"><?php printf( __('Business Rep. (%s)', 'wp-base'), $default_worker_name) ?></option>
					<?php
						}
						if ( $workers ) {
							if ( $profileuser_id ) {
								$s = 'worker|'.$profileuser_id == $subject_selected ? " selected='selected'" : '';
								echo '<option value="worker|'.$profileuser_id.'"'.$s.'>' . $this->a->get_worker_name( $profileuser_id, false ) . '</option>';
							}
							else {
								foreach ( $workers as $worker ) {
								$s = 'worker|'.$worker->ID == $subject_selected ? " selected='selected'" : ''; 
								echo '<option value="worker|'.$worker->ID.'"'.$s.'>' . $this->a->get_worker_name( $worker->ID, false ) . '</option>';
								}
							}
						}
					 if ( class_exists( 'WpBSP' ) ) { ?>
						</optgroup>
						<?php } ?>
						
						<optgroup label="<?php _e('Services','wp-base') ?>" class="optgroup_service">
						<?php
						if ( $profileuser_id )
							$services = $this->a->db->get_results("SELECT * FROM " . $this->a->services_table . " WHERE ID IN ( SELECT object_id FROM ".$this->a->meta_table." WHERE meta_type='service' AND meta_key='created_by' AND meta_value=".$profileuser_id." )  ORDER BY sort_order,ID " ); 				
						else
							$services = $this->a->get_services();
						if ( $services ) {
							foreach ( $services as $service ) {
								$s = 'service|'. $service->ID == $subject_selected ? " selected='selected'" : '';
								$disabled = $this->a->is_package( $service->ID ) ? ' disabled="disabled"' : '';
								echo '<option value="service|'.$service->ID.'"'. $s .$disabled.'>' . $this->a->get_service_name( $service->ID ) . '</option>';
							}
						}
						?>
						</optgroup>
					
					</select>
					<button class="ui-button ui-state-default ui-corner-all ui-shadow"><?php _e('Show','wp-base') ?></button>
				</div>
			</form>	
			</div>
			<div class='app-submit app_2column'>
			<?php do_action( 'app_wh_admin_submit', 'holidays' ) ?>
			</div>
			<div style="clear:both"></div>
			</div>
			
			<?php
				list( $subject, $who ) = explode( '|', $subject_selected );
				echo $this->draw( $who, $subject, $bp );
			?>
			
		</div>

		<?php
	}
	
	/**
	 *	Render holidays
	 */
	 function draw( $who, $subject = 'worker', $bp=false ) {
		global $current_user;
		
		if ( is_admin( ) )
			add_action( 'admin_footer', array( $this, 'admin_footer' ) );
		else
			add_action( 'wp_footer', array( $this, 'admin_footer' ) );

		$year = date("Y", $this->a->local_time );

		switch( $subject ) {
			case 'worker':	$pre_text = ($who == $this->a->get_default_worker_id()) ? __('Business Rep','wp-base') : $this->a->get_text('provider');
							$whose=  $pre_text.': '. $this->a->get_worker_name( $who ); 
							break;
			case 'service':	$whose= $this->a->get_text('service') .': '. $this->a->get_service_name( $who );				
							break;
		}
		
		$options = $this->a->get_business_options();
		$holidays = isset( $options['holidays'][$subject][$who] ) ? $options['holidays'][$subject][$who]: '';

		$r  = apply_filters( 'app_admin_holidays_before', '' );
		$r .= "<form class='app_form app_wh_annual_form' method='post' action='".wpb_add_query_arg( null, null )."'>";
		$r .= '<p class="submit">
			<input type="submit" class="button-primary" value="'. __('Save Holidays', 'wp-base') .'" />
			</p>';

		$r .= '<div class="postbox">';
		$r .= '<h3 class="hndle"><span>'. sprintf( __('Holidays of %s','wp-base'), $whose ). '</span></h3>';
		$r .= '<div class="inside">';
		
		if ( $bp )
			$r .= '<div id="full-year-bp" class="box"></div>';
		else
			$r .= '<div id="full-year" class="box"></div>';
		$r .= '<input type="hidden" name="holidays" id="altField" value="'.$holidays.'" />';

		$r .= '<div style="clear:both"></div>';
		
		if ( !$bp ) {
			$r .= '<fieldset class="app_mt"><label>';
			$r .= '<span class="title app_mr5 app_b">'.__('Days picked per click','wp-base').'</span>';
			$r .= '<span class="input-text-wrap app_mr5">';
			$r .= '<input type="text" name="days_picked_per_click" class="days_picked_per_click app_no_save_alert app_50" value="1" />';
			$r .= '</span>';
			$r .= '<span class="description app_bottom">';
			$r .= __('You can pick more than one day at once by entering desired number of days. There is no need to save. Then as you click a day, following days will also be selected/deselected accordingly. Note: This setting is not saved to the database and defaults to 1.', 'wp-base' );  
			$r .= '</span>';
			$r .= "</label></fieldset>";
		}

		$r .= "</div></div>";
		
		if ( $bp )
			$r .='<input type="hidden" name="app_bp_settings_user" value="'.$who.'">';

		
		$r .= '<input type="hidden" name="location" value="0" />
			<input type="hidden" name="action_app" value="save_holidays" />
			<input type="hidden" name="app_select_subject_for_holiday" value="'.$subject.'|'.$who.'" />
			<input type="hidden" name="who" value="'.$who.'" />
			<input type="hidden" name="subject" value="'.$subject.'" />
			<input type="hidden" name="app_nonce" value="'.wp_create_nonce( 'update_app_settings' ).'" />
			<p class="submit">
			<input type="submit" class="button-primary" value="'. __('Save Holidays', 'wp-base') .'" />
			</p>';
			
		$r .= '</form>';
		
		return $r;
	}
	
	/**
     * Add script to footer
     */
	function admin_footer(){
		if ( isset( $this->script_added ) && $this->script_added )
			return;
		
		$default_worker = $this->a->get_default_worker_id();
		$subject_selected = isset($_POST['app_select_subject_for_holiday']) ? $_POST['app_select_subject_for_holiday'] : 'worker|'.$default_worker; 
		list( $subject, $who ) = explode( '|', $subject_selected );
		$options = $this->a->get_business_options();
		$holidays = isset( $options['holidays'][$subject][$who] ) ? $options['holidays'][$subject][$who]: '';
		$addDates = $holidays ? explode( ',',$holidays ) : array();
	?>
		<script type='text/javascript'>
		jQuery(document).ready(function ($) {
			$("#app_select_subject_for_holiday").multiselect({
				multiple:false,
				selectedList:1,
				classes:"app_workers",
				minWidth:300
			});
			
			var today = new Date();
			var y = today.getFullYear();
			var dpc = parseInt($('.days_picked_per_click').val());
			dpc = dpc > 0  ? dpc : 1;
			var holidays = <?php echo json_encode($addDates); ?>;
			var settings = {
				addDates : holidays,
				dateFormat: "yy-mm-dd", 
				firstDay:<?php echo $this->a->start_of_week ?>, 
				defaultDate: y+"-01-01",
				altField: '#altField',
				onSelect: function(){
					app_input_changed=true;
					$(document).data('app_input_changed',true);					
					},
				stepMonths:12,
				mode: 'daysRange',
				autoselectRange: [0,dpc],
				numberOfMonths: [3,4]
				};
			var mdp = $('#full-year').multiDatesPicker(settings);
			
			$('#full-year-bp').multiDatesPicker({
				addDates : holidays,
				dateFormat: "yy-mm-dd", 
				firstDay:<?php echo $this->a->start_of_week ?>, 
				defaultDate: y+"-01-01",
				altField: '#altField',
				onSelect: function(){app_input_changed=true;},
				stepMonths:12,
				numberOfMonths: [4,3]
			});

			$(document).on( "blur", ".days_picked_per_click", function(){
				dpc = parseInt( $(this).val() ) > 0  ? parseInt( $(this).val() ) : 1;
				settings['autoselectRange'] = [0,dpc];
				mdp.multiDatesPicker('setMode',settings);
			});

		});
		</script>
	<?php
		$this->script_added = true;	
	}

	/**
     * Save holidays settings
     */
	function save_holidays( $profileuser_id=false ) {

		if ( 'save_holidays' != $_POST["action_app"] || !isset( $_POST['holidays'] ) )
			return;
		
		global $current_user;
		
		$who		= $profileuser_id ? $profileuser_id : $_POST['who'];
		$subject	= $profileuser_id ? 'worker' : $_POST['subject'];
		$holidays	= $_POST['holidays'];
		
		$b_options = $this->a->get_business_options();
		$b_options['holidays'][$subject][$who] = wpb_sanitize_commas($holidays);
		
		if ( $this->a->update_business_options( $b_options ) ) {
			wpb_flush_cache();
			wpb_notice( 'saved' );
		}
		
	}
	
}
	
	BASE('Holidays')->add_hooks();
}