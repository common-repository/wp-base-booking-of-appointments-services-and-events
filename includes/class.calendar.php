<?php
/**
 * WPB Calendar Class
 *
 * A "calendar" is a set of back-to-back time slots, usually in the range of days or weeks
 * This class handles required time range to generate HTML out of time slot availability
 *
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBCalendar' ) ) {

class WpBCalendar {
	
	private $location, $service, $worker, $_time, $slot, $microtime, $format, $cl_offset;
	
	public $cache = array();
	
	public $has_appointment = false;
	
	public $all_busy = null;
	
	private $disable_lazy_load, $disable_limit_check, $disable_waiting_list, $admin, $assign_worker, $inline, $display = false;
	
	private $force_min_time = 0;
	
	protected $a, $uniq;
	
	function __construct( $location = false, $service = false, $worker = false ) {
		$this->a = BASE();

		$this->location = false === $location ? $this->a->get_lid() : $location;
		$this->service 	= false === $service ? $this->a->get_sid() : $service;
		$this->worker 	= false === $worker ? $this->a->get_wid() : $worker;
		
		if ( 'all' === $this->worker ) {
			$this->worker = $this->a->get_default_worker_id();
			$this->service = 'all';
			add_filter( 'app_get_duration', array( $this, 'force_duration' ), WPB_HUGE_NUMBER, 3 ); 
			add_filter( 'app_is_daily', array( $this, 'force_is_daily' ), WPB_HUGE_NUMBER, 2 ); 
		}
		
		# If no workers is defined, business rep. is the worker
		if ( !$this->worker && !$this->a->get_nof_workers() )
			$this->worker = $this->a->get_default_worker_id();
		
		$this->_time		= $this->a->_time;
		$this->cl_offset	= $this->a->get_client_offset( );
		
		$this->start_hard_limit_scan();	# Calendar call can last for limited time
	}
	
	/**
	 * Preset properties
	 * @return none
	 */	
	public function setup( $args ) {
		$this->disable_lazy_load	= !empty( $args['disable_lazy_load'] );
		$this->disable_limit_check	= !empty( $args['disable_limit_check'] );	# Not used
		$this->disable_waiting_list = !empty( $args['disable_waiting_list'] );
		$this->admin				= !empty( $args['admin'] );
		$this->assign_worker		= !empty( $args['assign_worker'] );
		$this->inline				= !empty( $args['inline'] );
		$this->force_min_time		= !empty( $args['force_min_time'] ) ? (int)$args['force_min_time'] : 0;	# Forces time base as set amount (Usually 60 for 60 mins)
		$this->display				= !empty( $args['display'] ) ? $args['display'] : '';					# full, with_break or none
	}
	
	/**
	 * Set a duration for 'all' service case
	 * @return integer
	 */	
	public function force_duration( $duration, $ID, $slot_start=null ) {
		if ( 'all' === $ID )
			$duration = 60;
		
		return $duration;
	}
	
	/**
	 * Reset is_daily for 'all' service case
	 * @return integer
	 */	
	public function force_is_daily( $result, $ID ) {
		if ( 'all' === $ID )
			$result = false;
		
		return $result;
	}

	/**
	 * Getters
	 * @return integer
	 */	
	public function get_worker( ) {
		return $this->worker;
	}
	
	public function get_location(){
		return $this->location;
	}
	
	public function get_service( ){
		return $this->service;
	}
	
	/**
	 * Check if inline
	 * @return bool
	 */	
	public function is_inline(){
		return !empty( $this->inline );
	}

	/**
	 * Check if used on admin side
	 * @return bool
	 */	
	public function is_admin(){
		return $this->admin;
	}

	/**
	 * Check if waiting list enabled
	 * @return bool
	 */	
	public function is_waiting_list_allowed(){
		return ! $this->disable_waiting_list;
	}

	/**
	 * Record microtime value to check scanning time
	 * @return none
	 */	
	public function start_hard_limit_scan(){
		$this->microtime = wpb_microtime();
	}
	
	/**
	 * Hard limit time in seconds
	 * @return float
	 */	
	private function hard_limit(){
		return apply_filters( 'app_hard_limit', WPB_HARD_LIMIT );
	}
	
	/**
	 * Check if scanning time exceeded
	 * @return float|false		Total scan time if exceeded, false if not
	 */	
	public function is_hard_limit_exceeded(){
		$scan_time = wpb_microtime() - $this->microtime;
		if ( $scan_time > $this->hard_limit() )
			return $scan_time;
		
		return false;
	}
	
	/**
	 * Get when last scan started
	 * @return float
	 */	
	public function scan_start_time() {
		return $this->microtime;
	}
	
	/**
	 * Create a Slot object
	 * @return object
	 */	
	public function slot( $slot_start, $slot_end=false ) {
		return $this->slot = new WpBSlot( $this, $slot_start, $slot_end );
	}
	
	/**
	 * Find the start and end points for the day
	 * @return string $first:$last (timestamps)
	 */	
	private function limits( $day_start ) {
		if ( ( 'all' === $this->service && !$this->inline ) || 'full' === $this->display )
			return strval( $day_start ) .':'. strval( 60*60*24 + $day_start );
		
		if ( !$this->cl_offset && !$this->a->is_package( $this->service ) ) {
			$limits	= BASE('WH')->find_limits( $day_start, $this->service, $this->worker );
			$_start	= $limits['first'];
			$_end	= $limits['last'];
		}
		else {
			$_start = 0;
			$_end	= 24*60;
		}
		
		$start	= apply_filters( 'app_schedule_starting_hour', $_start, $day_start, $this );
		$end	= apply_filters( 'app_schedule_ending_hour', $_end, $day_start, $this );
		
		$first	= $start *60 + $day_start; 	# Timestamp of the first cell to be scanned in a day
		$last	= $end *60 + $day_start; 	# Last cell as ditto
		
		return $first .':'. $last;
	}
	
	/**
	 * Find free or all slots in a day
	 * Several optimizations are used here for faster processing
	 * @param $day_start	Integer	Timestamp of the day we are scanning
	 * @param $find_one		Bool	Checking for just one or more free slot(s). Number entered is number of free slots we are looking for
	 * 								This is an optimization for monthly calendar. 
	 * @return array				Keys are $slot_start, values are WpBSlot object
	 */	
	public function find_slots_in_day( $day_start, $find_one=false ) {
		$this->all_busy = null;
		$this->has_appointment = false;
		$out = array();
		
		if ( apply_filters( 'app_skip_day', false, $day_start, $this ) )
			return $out;
		
		$apply_fix		= $this->force_min_time || ('yes' === apply_filters( 'app_time_slot_calculus_legacy', wpb_setting( 'time_slot_calculus_legacy' ), $this ) ); # Whether apply fix time
		$step_min		= $this->force_min_time ? $this->force_min_time*60 : $this->a->get_min_time()*60;				  # Either min time or forced time													// Time base
		$step_ser		= $apply_fix ? $step_min : wpb_get_duration( $this->service, $day_start ) *60;				
		
		$step = $step_ser; # First apply the service duration as step - then slow down if required

		list( $first, $last ) = explode( ':', $this->limits( $day_start ) );
		
		$i = 1;
		for ( $t=$first; $t<$last; $t=$t+$step ) {
			
			if ( !$step )
				break;
			
			$reason				= null;
			$is_longer_break	= true;
			$slot_start			= apply_filters( 'app_ccs', $t - $this->cl_offset, $this ); 					
			$step_ser			= $apply_fix ? $step_min : wpb_get_duration( $this->service, $slot_start ) * 60;	# This is here for variable durations Addon

			if ( $this->slot( $slot_start )->is_past_today( ) )
				continue;
			
			if ( $step_ser != $step_min ) {
				$slot = new WpBSlot( $this, $slot_start, $slot_start+$step );
				if ( $reason = $slot->why_not_free() ) {
					if ( $step != $step_ser ) {
						# Increase to normal service length
						$slot = new WpBSlot( $this, $slot_start, $slot_start+$step_ser );
						if ( $maybe_reason = $slot->why_not_free() )
							$reason = $maybe_reason;
					}
				}
				
				# We are using integers here to save more in DB cache
				$is_past = 12 === $reason || 13 === $reason;
				$is_longer_break = !$this->cl_offset && (5 === $reason || 6 === $reason || 11 === $reason || $is_past ); # wpb_code2reason function for explanation of these numbers
				
				if ( !$reason || $is_longer_break || ($this->cl_offset && ( $is_past || 11 === $reason )) )
					$step = $step_ser;
				else
					$step = $step_min; 
			}
			
			if ( null === $reason ) {
				$slot = new WpBSlot( $this, $slot_start, $slot_start+$step );
				$reason = $slot->why_not_free( );
				
				if ( !$reason && $step != $step_ser ) {
					# Increase from $step (must be equal to $step_min) to $step_ser to find if there is a reason
					$slot = new WpBSlot( $this, $slot_start, $slot_start+$step_ser );
					$reason = $slot->why_not_free( );
				}
			}
			
			if ( $this->assign_worker )
				$slot->assign_worker( false, true );

			if ( $slot->has_appointment )
				$this->has_appointment = true;
			
			if ( $reason ) {
				if ( !$is_longer_break ) { # Prevents slowing down too early
					$step = $step_min;
				}
				
				if ( $this->is_hard_limit_exceeded() ) {	# Prevent too long scans
					$hard_limit = true;
					break;
				}
				
				if ( 0 === ( ($slot_start - $first) % $step_ser ) && ( 'full' === $this->display || 'with_break' === $this->display ) )	
					$out[$slot_start] = $slot;	# If we also want not available slots with reason in $slot->reason

				if ( 1 === $reason && null === $this->all_busy )
					$this->all_busy = true;			# Check if all slots are busy (or unavailable, but not free)
				
				if ( 9 == $reason || 10 == $reason )
					break;
				else
					continue;
			}
			
			if ( !$reason )
				$this->all_busy = false;			# We found a free slot, so all slots are not busy
			
			$out[$slot_start] = $slot;
			
			if ( $find_one && count( $out ) >= $find_one ) {
				if ( !$this->admin || $this->has_appointment )
					break;
			}
		}
		
		return $out;
	}
	
	/**
	 * Find available days in a time interval
	 * @param $from		string|integer		Start time as Timestamp or date/time. Includes the day $from belongs from 00:00
	 * @param $to		string|integer		End time to check as timestamp or day/time. Includes the day $to belongs until midnight
	 * @since 3.0
	 * @return array						Values are days in Y-m-d format
	 */		
	public function find_available_days( $from, $to ) {
		$out	= array();
		$start	= strtotime( date( 'Y-m-d', is_numeric( $from ) ? $from : strtotime( $from, $this->a->_time ) ), $this->a->_time );
		$end	= strtotime( date( 'Y-m-d', is_numeric( $to ) ? $to : strtotime( $to, $this->a->_time ) ), $this->a->_time );

		for ( $d = $start; $d < $end; $d = $d + 86400 ) {
			
			$maybe_found_day = $this->find_slots_in_day( $d, 1 );
			if ( !empty( $maybe_found_day ) )
				$out[] = date( 'Y-m-d', $d );
		}
		
		return array_values( $out );		
	}
	
	/**
	 * Wraps Timetable cells with required div elements
	 * @since 3.0
	 * @return string
	 */		
	public function wrap_cells( $day_start, $html ) {
		// We need this only for the first timetable
		// Otherwise $time will be calculated from $day_start
		$time = !empty( $_GET["app_timestamp"] ) ? $_GET["app_timestamp"] + $this->cl_offset : $this->_time + $this->cl_offset;
		// If today is a working day, shows its free times by default
		$style = date( 'Ymd', $day_start ) == date( 'Ymd', $time ) ? '' : ' style="display:none"'; 
		$ret  = '<div class="app_timetable app_timetable_'.$day_start.'"'.$style.'>';
		$ret .= '<div class="app_timetable_title">';
		$ret .= date_i18n( $this->a->date_format, $day_start );
		$ret .= '</div>';
		$ret .= '<div class="app_timetable_flex">';	

		$ret .=	$html;

		$ret .= '</div></div>';
		
		return $ret;
	}
	
	/**
	 * Check if lazy load will be executed
	 * Lazy load is not run for Monthly Calendar widget, on admin side
	 * @since 3.0
	 * @return bool
	 */		
	public function is_lazy_load_allowed( ) {
		if ( $this->disable_lazy_load )
			return false;
		
		if ( 'yes' != wpb_setting( 'lazy_load' ) || is_admin() )
			return false;

		return true;
	}
	
	/**
	 * Check if lazy load is in progress
	 * Lazy load will run if it is allowed and not ajax
	 * @since 3.0
	 * @return bool
	 */		
	function doing_lazy_load(){
		return $this->is_lazy_load_allowed( ) && !defined( 'WPB_AJAX' );
	}
	
	/**
	 * Add previous rolling and current caches and saves them
	 * Also combines separate calendar caches on the same page
	 * @since 3.0
	 * @return bool
	 */	
	public function save_cache( ) {
		if ( !wpb_use_cache() || $this->doing_lazy_load() )
			return;

		$cache = get_transient( 'wpb_bookable' );
		$cache = !empty( $cache ) && is_array( $cache ) ? $cache : array();
		return set_transient( 'wpb_bookable', array_merge( $cache, $this->cache ) );
	}
	
}
}