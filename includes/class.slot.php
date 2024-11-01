<?php
/**
 * WPB Slot
 *
 * Everything about availability check and control of the shortest bookable time frame (Slot) in a period of time (Calendar)
 * Slot can also be initiated without a Calendar, for example to check availability of a single slot before confirming a booking
 * 
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBSlot' ) ) {

class WpBSlot {
	
	/**
     * WP BASE instance
     */
	protected $a = null;
	
	private $location, $service, $worker, $_time, $slot_start, $slot_end;
	
	private $app_id, $category = 0;
	
	public $stat = array();
	
	public $calendar, $reason;
	
	public $has_appointment, $format, $is_waiting = false;
	
	public $quick = 0;
	
	/**
	 * Constructor
	 * @param	$calendar_or_val	object/string	Either WpBCalendar object or packed val (see "pack" method)
	 * @param	$slot_start			integer			Slot start timestamp
	 * @param	$slot_end			integer			Slot end timestamp
	 * @param	$app_id				integer			Booking ID
	 */
	function __construct( $calendar_or_val, $slot_start=false, $slot_end=false, $app_id=false ) {
		$this->a 			= BASE();
		$this->_time 		= $this->a->_time;
		
		if ( $calendar_or_val instanceof WpBCalendar ) {
			$calendar 			= $calendar_or_val;
			$this->calendar 	= $calendar;
			$this->location 	= $calendar->get_location();
			$this->service 		= $calendar->get_service();
			$this->worker 		= $calendar->get_worker();
			$this->slot_start	= $slot_start;
			$this->slot_end		= false === $slot_end ? $slot_start + wpb_get_duration( $this->service, $slot_start )*60 : $slot_end;
			$this->app_id		= false === $app_id ? 0 : $app_id;
		}
		else if ( is_string( $calendar_or_val ) && strpos( $calendar_or_val, '_' ) !== false ){
			$val = $calendar_or_val;
			list( $this->slot_start, $this->slot_end, $this->location, $this->service, $this->worker, $this->app_id, $this->category, $this->is_waiting ) = explode( "_", $val );
			$this->slot_end = $this->slot_start + wpb_get_duration( $this->service, $this->slot_start )*60;
		}
		else if ( 'test' === $calendar_or_val ) {
			#Create a test object by copying properties of test_app object
			foreach ( get_object_vars( $this->a->test_app_record( ) ) as $key => $value ) {
				$this->$key = $value;
			}
			$this->app_id = isset( $this->ID ) ? $this->ID : 0;
		}
		
		$this->format	= $this->a->is_daily( $this->service ) ? $this->a->date_format : $this->a->dt_format;
		$this->category = !empty( $_POST['app_category'] ) ? $_POST['app_category'] : ( $this->app_id ? wpb_get_app_meta( $this->app_id, 'category' ) : null );
	}
	
	/**
	 * Getters and setters
	 * @return integer or void
	 */
	public function get_location(){
		return $this->location;
	}
	
	public function get_category(){
		return $this->category;
	}

	public function get_service( ){
		return $this->service;
	}

	public function set_service( $service ) {
		$this->service = $service;
	}

	public function get_worker( ) {
		return $this->worker;
	}
	
	public function set_worker( $worker ) {
		$this->worker = $worker;
	}
	
	public function get_start(){
		return $this->slot_start;
	}
	
	public function set_start( $slot_start ) {
		$this->slot_start = $slot_start;
	}
	
	public function get_end(){
		return $this->slot_end;
	}
	
	public function set_end( $slot_end ) {
		$this->slot_end = $slot_end;
	}

	public function get_app_id(){
		return $this->app_id;
	}
	
	public function set_app_id( $app_id ){
		$this->app_id = $app_id;
	}
	
	public function maybe_waiting(){
		return $this->is_waiting;
	}

	public function mark_waiting_list( $is ) {
		$this->is_waiting = ($this->calendar instanceof WpBCalendar) && $this->calendar->is_waiting_list_allowed() && $is;
	}

	/**
	 * Assign a worker depending on setting or by client selection
	 * @param $is_multiple	bool	Whether multiple appointments
	 * @Param $final		bool	If final, we try to assign a random worker if there is none assigned yet
	 * @since 2.0
	 * @return integer (worker id)
	 */
	public function assign_worker( $is_multiple, $final=false ) {
		
		# Let addons select worker by other criteria, i.e. least occupied, most popular, cheapest, etc.
		$assign_worker_as		= apply_filters( 'app_assign_worker_as', wpb_setting('assign_worker'), $this, $is_multiple, $final );
		$client_selects_worker	= apply_filters( 'app_client_selects_worker', wpb_setting('client_selects_worker'), $this, $is_multiple, $final );
		$assigned_worker 		= apply_filters( 'app_assign_worker_pre', $this->worker, $this, $is_multiple, $final );

		# If we asked service provider to be selected in the confirmation form, lets set it here
		# In multiple case client selects will not work
		if ( "0" === (string)$assigned_worker && !$is_multiple && 'no' != $client_selects_worker ) {
			if ( !empty( $_POST['app_worker'] ) ) {
				$this->worker = $_POST['app_worker'];
				# This is already split. So we are using the one without virtual
				if ( !$this->why_not_free( 'single' ) )
					$assigned_worker = $_POST['app_worker'];
			}
			else {
				$_workers = $this->a->get_workers_by_service( $this->service );
				foreach ( (array)$_workers as $_worker ){
					$this->worker = $_worker->ID;
					if ( $this->why_not_free( 'single' ) )
						continue;
					else {
						# Pre select first available worker
						$assigned_worker = $_worker->ID;
						break;
					}
				}
			}
		}
		
		/* Assign default provider */
		if ( "0" === (string)$assigned_worker && 'default_worker' == $assign_worker_as ) {
			$maybe_sel_worker = $this->a->get_default_worker_id();
			$serv_by_worker = $this->a->get_services_by_worker( $maybe_sel_worker );
			if ( isset( $serv_by_worker[$this->service] ) ) {
				$this->worker = $maybe_sel_worker;
				if ( !$this->why_not_free( 'single' ) )
					$assigned_worker = $maybe_sel_worker;
			}
		}
		
		/* Assign a random provider if this is final stage or default worker is not available (busy or not giving this service) */
		if ( "0" === (string)$assigned_worker && ( $is_multiple || $final || 'first_worker' == $assign_worker_as || 'random' == $assign_worker_as || 'default_worker' == $assign_worker_as ) ) {
			$_workers = $this->a->get_workers_by_service( $this->service );
			if ( !empty( $_workers ) ) {
				# Randomize - Not in multiple and package
				if ( !$is_multiple && 'first_worker' != $assign_worker_as )
					shuffle( $_workers );
				$_workers = apply_filters( 'app_random_worker', $_workers, $this, $is_multiple, $final );
				# Check each worker for availability
				foreach ( $_workers as $_worker ){
					$this->worker = $_worker->ID;
					if ( $result = $this->why_not_free( 'single' ) ) {
						continue;
					}
					else {
						$assigned_worker = $_worker->ID; // Found one
						break;
					}
				}
			}
		}
		
		$this->worker = $assigned_worker;
		
		# Let addons select worker by other criteria, i.e. least occupied, most popular, cheapest, etc.
		return $this->worker = apply_filters('app_assign_worker', $this->worker, $this, $is_multiple, $final );
	}

	/**
	 * Pack (encode) several fields as a string using glue "_"
	 * ccs_cce_location_service_worker_appID_category_waiting
	 * @return string
	 */	
	public function pack( ){
		return	$this->slot_start . "_" . 
				$this->slot_end . "_" . 
				$this->location . "_" . 
				$this->service . "_" . 
				$this->worker . "_" .
				$this->app_id . "_" . 				
				(int)$this->category . "_" .
				(int)$this->is_waiting;
	}
	
	/**
	 * Get Sale Price for the current location, service and worker
	 * @param list_price: If set true, discounts are not applied
	 * @return string
	 */	
	public function get_price( $list_price=false ) {
		
		$service_id = apply_filters( 'app_real_service', $this->service, $this, __FUNCTION__ );
		
		if ( !$this->a->service_exists( $service_id ) )
			$price = 0;
		else {
			$location = $this->a->get_location( $this->location );
			$service = $this->a->get_service( $service_id );
			$worker = $this->a->get_worker( $this->worker );
		
			$location_price	= !empty( $location->price ) ? $location->price : 0;
			$service_price	= !empty( $service->price ) ? $service->price : 0;
			
			# Add at least the min price of the workers giving a service
			if ( $worker )
				$worker_price = !empty( $worker->price ) ? $worker->price : 0;
			else
				$worker_price = $this->a->get_min_max_worker_price( $this->service );
			
			# Prices act as a part of service (e.g. Variable Durations) are fed to this filter
			$price = $location_price + apply_filters( 'app_service_price', $service_price, $this ) + $worker_price;
		}

		WpBDebug::set('base_price', $price );
		
		# When "Extras Multiplied with Pax" is selected, Extras are fed here
		$price = apply_filters( 'app_get_price', $price, $this, $list_price );
		
		return wpb_round( $price );
	}

	/**
	 * Get List (aka regular) price for the current service and worker
	 * @return string
	 */	
	public function get_list_price( ) {
		return $this->get_price( true );
	}
	
	public function get_deposit( ) {
		$service_id = apply_filters( 'app_real_service', $this->service, $this, __FUNCTION__ );
		$service = $this->a->get_service( $service_id );
		return !empty( $service->deposit ) ? wpb_round( $service->deposit ) : 0;
	}

	/**
	 * Check if today is a working day for worker
	 * This is a quick check before going into details of the working hours
	 * @return bool
	 */		
	private function is_working_day( ) {
		return BASE('WH')->is_working_day( $this );
	}

	/**
	 * Check if today is a working day for service
	 * This is a quick check before going into details of the working hours
	 * @return bool
	 */		
	private function is_working_day_for_service( ) {
		if ( 'all' === $this->service || $this->service < 0 )
			return true;
		
		return BASE('WH')->is_working_day_for_service( $this );
	}
	
	/**
	 * Check if today is holiday for a worker
	 * @return bool
	 */		
	public function is_holiday( ) {
		return BASE('Holidays')->is_holiday( $this, 'worker' );
	}

	/**
	 * Check if today is holiday
	 * @return bool
	 */		
	public function is_holiday_for_service( ) {
		if ( 'all' === $this->service )
			return false;

		return BASE('Holidays')->is_holiday( $this, 'service' );
	}

	/**
	 * Check if it is break time for non-zero worker
	 * Checks for service break if worker is not selected
	 * @return bool
	 */		
	public function is_break( ) {
		if ( $this->worker ) {
			$w_or_s = $this->worker;
			$subject = 'worker';
		}
		else {
			$w_or_s = $this->service;
			$subject = 'service';
			
			if ( $this->service < 0 )
				return false;
		}
		
		return BASE('WH')->is_break( $this, $w_or_s, $subject );
	}

	/**
	 * Check if current worker is working at this time slot
	 * @return bool
	 * @since 1.2.2
	 */		
	public function is_working( ) {
		if ( $this->is_holiday( ) )
			return false;
		if ( $this->is_break( ) )
			return false;
		
		return true;
	}

	/**
	 * Check if a service is "working"
	 * @return bool
	 * @since 2.0
	 */		
	public function is_service_working( ) {
		if ( $this->is_holiday_for_service( ) )
			return false;
		
		if ( $this->service < 0 )
			return true;
		
		if ( BASE('WH')->is_break( $this, $this->service, 'service' ) )
			return false;
		
		return true;
	}
	
	/**
	 * Check if service is working or worker is giving this service
	 * @return bool
	 */		
	public function is_service_possible( ) {
		
		if ( 'all' === $this->service || $this->service < 0 )
			return true;
		
		$service = apply_filters( 'app_real_service', $this->service, $this, __FUNCTION__ );

		if ( !$this->worker || ( $this->worker && 'yes' === wpb_setting( 'service_wh_check' ) ) ) {
			if( !$this->is_service_working( $this->slot_start, $this->slot_end, $service ) )
				return false;			
		}
		
		if ( $this->worker ) {
			# Does selected worker give this service?
			$workers = $this->a->get_worker_ids_by_service( $service );
			if ( !$workers || !in_array( $this->worker, (array)$workers ) )
				return false;
		}
		
		# If nothing else, service is possible at this slot
		return true;
	}
	
	/**
	 * Return available number of workforce (workers +- capacity increase) that can give a service for a time slot (Just working hours, not appointments)
	 * e.g if one worker works between 8-11 and another works between 13-15, there is no worker between 11-13
	 * If $no=0, gives exact number of available workers
	 * @param $no: Comparison value. Greater than this number is required. If availability will turn out equal or less than this value, we will not get exact number.
	 * since 1.0.6
	 * @return integer
	 */		
	public function available_workforce( $service=false, $no=0 ) {
		if ( 'all' === $this->service )
			return WPB_HUGE_NUMBER;
		
		if ( false === $service )
			$service = $this->service;

		# We dont need to do anything special if:
		# A worker is selected
		# There are no workers -> Business rep selected in calendar
		# This is an event
		if ( $this->worker || $service < -1 )
			return $this->a->get_capacity( $service );
			
		$capacity	= $this->a->get_capacity( $service );
		$workers	= (array)$this->a->get_worker_ids_by_service( $service );
		$n			= $capacity - count( $workers ); // Net capacity increase or decrease
		
		if ( $n < 0 ) {
			# If capacity is less than or equal to appts, we cant fulfill
			# Cut short to save time
			if ( $capacity <= $no )
				return $capacity;
			
			$n = 0;
		}
		
		# Check if increased capacity ($n_net) is really available
		# Increased capacity uses service wh
		$n_net = 0;
		if ( $n > 0 ) {
			if ( count( (array)BASE('WH')->available_services( $this, $service ) ) )
				$n_net = $n;
		}
		
		$avail_workers = (array)BASE('WH')->available_workers( $this );
			
		$count = ($n_net + count( array_intersect( $workers, $avail_workers ) ) );

		if ( !$count && null === $avail_workers )
			return false;
		else
			return $count;
	}
	
	/**
	 * Number of pax a worker can serve per slot
	 * @return integer
	 */		
	function servicable_slot_count( $service ) {
		$count = 1; # Default
		
		# Business rep can serve as much as capacity
		if ( !$this->a->get_nof_workers() )
			$count = $this->a->get_capacity( $service );	
		
		return apply_filters( 'app_servicable_slot_count', $count, $this, $service ); 
	}
	
	/**
	 * Check if a cell is not available, i.e. all appointments taken OR we dont have enough workers for this time slot
	 * Better should have been called is_not_available, but it ended up like this... 
	 * @virtual app: e.g. coming from packages
	 * @return mixed	string|false
	 */		
	public function is_busy( $virtual=false ) {
		
		$location	= $this->location;
		$service	= apply_filters( 'app_real_service', $this->service, $this, __FUNCTION__ );
		$pax		= apply_filters( 'app_pax', 1, $service, $this );
		$worker		= $this->worker;
		$week		= date( "W", $this->slot_start );
		$sorted		= true;		# Whether results will become/stay sorted
		$apps		= array();
		$data		= array();	# Number and ID of appointments for this time frame
		$result 	= false;
		$no_workers = !$this->a->get_nof_workers();
		$yes_workers= !$no_workers;

		if ( $no_workers || 'all' === $service )
			$apps = $this->a->get_reserve_apps( $week );
		else {
			if ( $worker )
				$apps = $this->a->get_reserve_apps_by_worker( $worker, $week );
			else
				$apps = $this->a->get_reserve_apps_by_service( $location, $service, $week );
			
			$unassigned = $this->a->get_reserve_unassigned_apps( $location, $service, $worker, $week );
			if ( !empty( $unassigned ) ) {
				$apps = array_merge( $apps, $unassigned );
				$sorted = false;
			}
			
			# GCal
			$gcal_apps = $this->a->get_reserve_apps_by_service( $location, WPB_GCAL_SERVICE_ID, $week );
			if ( !empty( $gcal_apps ) ) {
				$apps = array_merge( $apps, $gcal_apps );
				$sorted = false;
			}
		}
		
		if ( !empty( $virtual ) ) {
			$apps = array_merge( $virtual, $apps ); # Virtual may have negative seats. So we are taking it at the beginning.
			$sorted = false;
		}
		
		$apps = array_filter( $apps );

		if ( !empty( $apps ) ) {
			foreach ( $apps as $app ) {
				$s = $app->service;
				
				if ( $s < -1 )
					continue;
				
				$padding	= $this->a->get_padding( $s )*60;
				$app_start	= strtotime( $app->start );
				
				if ( $sorted && $app_start > $this->slot_end + $padding )
					break;
				
				$break = $this->a->get_break( $s )*60;
				
				if ( $this->slot_end > ($app_start - $padding) && $this->slot_start < strtotime( $app->end ) + $break ) {
					$this->has_appointment = true;
					$seats = !empty( $app->seats ) ? $app->seats : 1; 
					
					$data[$s]['count']	= isset( $data[$s]['count'] ) ?  $data[$s]['count'] + $seats : $seats;
					$data[$s]['app']	= isset( $data[$s]['app'] ) ?  $data[$s]['app'] .','. $app->ID : $app->ID;

					# If a worker is selected and checking for current service, finding one appt is enough to be busy
					# If a business rep is selected (no workers defined), capacity is taken into account
					if ( $worker && $s === $service && $data[$s]['count'] >= $this->servicable_slot_count( $service ) ) {
						if ( $normally_busy = apply_filters( 'app_slot_is_busy_pre', 1, $this, $data[$s]['count'], $pax, 1, $virtual ) )
							return $normally_busy;
					}
				}
			}
		}

		$data = array_filter( $data );
		
		# Include current service to check for availability, in any case
		if ( !isset( $data[$service]['count'] ) )
			$data[$service]['count'] = 0;
		
		# Ensure that $service is always last key
		$temp = array( $service => $data[$service] );
		unset( $data[$service] );
		$data = $data + $temp;
		
		# We have to check all service bookings, because $data array includes all bookings *we are responsible for*
		# and some of them may not be current service!
		# i.e. bottleneck in another service affects our availability for this service
		foreach ( $data as $s => $dt ) {

			if ( $s < -1 )
				continue;
				
			$avail = (int)$this->available_workforce( $s, $dt['count'] );
			
			# Save to use for "seats left" - values can be zero
			if ( $s == $service ) {
				$this->stat[$this->slot_start][$this->slot_end]['count']	= $dt['count'];
				$this->stat[$this->slot_start][$this->slot_end]['app']	= isset($dt['app']) ? $dt['app'] : '';
				if ( 0 === $dt['count'] )
					$this->stat[$this->slot_start][$this->slot_end]['available'] = $avail;
			}
			
			if ( $dt['count'] + $pax > $avail ) {
				if ( !$avail ) {
					$result = 2;	# no-workers
					break;
				}
				else if ( $s != $service ) {
					if ( !( $no_workers && $avail > 1 ) ) { # If default worker is overloaded, check only current service
						$result = 1;	# busy because of another service
						break;
					}
				}
				else if ( $result = apply_filters( 'app_slot_is_busy', 1, $this, $dt['count'], $pax, $avail, $virtual ) ) {
					break; # busy because of this service
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Get a cached result of why_not_bookable (Always detailed)
	 * @param $command: 'single', 'quick', or 'single_quick'
	 * @since 3.0
	 * @return string or false
	 */	
	public function why_not_free( $command='', $virtual=false ) {
		if ( 'all' === $this->service )
			return $this->reason = $this->why_not_bookable_s( );
		
		$this->quick = strpos( $command, 'quick' ) === false ? 0 : 1;
		if ( $virtual )
			return $this->reason = $this->why_not_bookable_s( );

		$lsw = $this->location.'_'.$this->service.'_'.$this->worker;
			
		if ( ( $this->calendar instanceof WpBCalendar ) && empty( $this->calendar->cache ) && wpb_use_cache() ) {
			$this->calendar->cache = get_transient( 'wpb_bookable' );
			$this->calendar->cache = !empty( $this->calendar->cache ) && is_array( $this->calendar->cache ) ? $this->calendar->cache : array();
		}
		
		if ( isset( $this->calendar->cache[$lsw][$this->slot_start][$this->slot_end][$this->quick] ) )
			return $this->reason = $this->calendar->cache[$lsw][$this->slot_start][$this->slot_end][$this->quick];
		
		$this->reason = strpos( $command, 'single' ) !== false ? $this->why_not_bookable_s( ) : $this->why_not_bookable( );
		return $this->calendar->cache[$lsw][$this->slot_start][$this->slot_end][$this->quick] = $this->reason;
	}
	
	/**
	 * Determine if lazy load in progress
	 * Lazy load can only be used if slot is instantiated by WpBCalendar and property $lazy_load_allowed of WpBCalendar is true
	 * Also it is not applied when doing ajax
	 * @since 3.0
	 * @return bool
	 */		
	public function doing_lazy_load( ) {
		return ( $this->calendar instanceof WpBCalendar ) && $this->calendar->doing_lazy_load( );
	}

	/**
	 * Check if all conditions to book is NOT met within start-end and return reason why not
	 * Wrapper for why_not_bookable_s
	 * @since 2.0
	 * @return mixed (false:bookable or integer: reason why not bookable)
	 */		
	public function why_not_bookable( ) {
		if ( $this->doing_lazy_load( ) )
			return 2;
			
		# Packages does not directly use why_not_bookable_s, because job[s] is definitely not $this->service
		if ( $this->a->is_package() )
			return BASE('Packages')->why_not_bookable( $this );
		else if ( $reason = $this->why_not_bookable_s( ) )
			return $reason;
		
		# Recurring is here, because on page load repeat=1 and recurring is not actually recurring
		if ( $this->a->is_recurring() )
			return BASE('Recurring')->why_not_bookable( $this );
	}

	/**
	 * Package friendly version of why_not_bookable (single time slot check version)
	 * @since 2.0
	 */		
	public function why_not_bookable_s( $virtual=false ) {
		if ( $this->doing_lazy_load( ) )
			return 2;
		
		$reason = false;
		$duration = wpb_get_duration( $this->service );
		if ( !$duration )
			return 15; # not_published
		else if ( $this->_time >= $this->slot_start + $duration ) {
			if ( strtotime( date( 'd-m-Y', $this->_time ) ) >= $this->slot_start + $duration )
				return 13; 	// past yesterday_or_before Passed days (not today - For performance reasons, today is handled separately)
		}
		
		if ( $this->_time + $this->a->get_lower_limit( $this->slot_start, $this->service ) * 3600 > $this->slot_end )
			$reason = 11; # blocked
		else if ( ($this->worker && $this->is_holiday( ) ) || (!$this->worker && $this->is_holiday_for_service( ) ) )
			$reason = 10; # holiday
		else if ( ($this->worker && !$this->is_working_day( )) || (!$this->worker && !$this->is_working_day_for_service( ) ) )
			$reason = 9; # alldayoff
		else if ( $this->a->get_app_limit( $this->slot_start, $this->service ) < ceil( ( $this->slot_start - $this->_time ) /86400 ) )
			$reason = 14; # upper_limit

		if ( ('all' != $this->service && $this->quick) || $reason ) {
			return $reason;
		}
		
		if ( !$this->is_service_possible( ) )
			$reason = 8; # service_notworking 
		else if ( $maybe_reason = apply_filters( 'app_is_unavailable', null, $this, $virtual )  )
			$reason = $maybe_reason; # 7 location_capacity_full or similar
		else if ( $break_reason = $this->is_break( ) )
			$reason = $break_reason; # 4 or 5: break or complete_brk (tells if this is complete break)
		else if ( $busy_reason = $this->is_busy( $virtual ) )
			$reason = $busy_reason; # 1 or 2: busy or no_workers
		
		# If nothing else, this slot is bookable - except today: It can be still unavailable
		return $reason;
	}
	
	/**
	 * Check if past for today
	 * This is to complement why not bookable function
	 * @since 2.0
	 * @return bool
	 */	
	 public function is_past_today( ) {
		if ( 'yes' === wpb_setting( 'allow_now' ) ) {
			$duration = wpb_get_duration( $this->service );
			$late_booking_time = apply_filters( 'app_late_booking_time', wpb_setting( 'late_booking_time' ), $this );
			$allowed_mins =  $late_booking_time ? $late_booking_time + 1 : $duration;
			$allowed_secs = 60*min( $allowed_mins, $duration );
		}
		else
			$allowed_secs = 0;
		
		if ( $this->_time > ($this->slot_start+$allowed_secs) && date( 'Y-m-d', $this->slot_start ) == date( 'Y-m-d', $this->_time ) )
			return true;
		else
			return false;
	}

	/**
	 * Create HTML codes for a slot of Monthly Calendar
	 * @since 3.0
	 * @return string
	 */	
	public function monthly_calendar_cell_html( ) {
		$fill = apply_filters( 'app_timetable_cell_fill', date_i18n( $this->a->time_format, $this->a->client_time($this->slot_start) ), $this );
		$click_hint_text = $this->reason ? WpBDebug::display_reason( $this->reason ) : $this->a->get_text('click_to_book');
		
		$add_class = '';
		$farr = apply_filters( 'app_monthly_calendar_cell_fill', array( 'class_name'=>'', 'fill'=>$fill ), $this );
		if ( is_array( $farr ) ) {
			if ( isset( $farr['class_name'] ) ) { $add_class = $farr['class_name'];}
			if ( isset( $farr['fill'] ) ) { $fill = $farr['fill']; }
		}
		
		$class = $this->reason ? 'notpossible '. wpb_code2reason( $this->reason ) : 'free';
		
		return '<div data-reason="'.$this->reason.'" class="app_timetable_cell '.$class.' '. $add_class.' app_slot_'.$this->slot_start.'" data-title="'.date_i18n($this->a->dt_format, $this->a->client_time($this->slot_start) ).'" title="'.esc_attr( $click_hint_text ).'">'.
					$fill. '<input type="hidden" class="app_get_val" value="'.$this->pack( ).'" /></div>';

	}
	
	/**
	 * Create HTML codes for a slot of Weekly Calendar
	 * @since 3.0
	 * @return string
	 */	
	public function weekly_calendar_cell_html( ) {
		$new_link = '';
		$click_hint_text = $this->reason ? WpBDebug::display_reason( $this->reason ) : $this->a->get_text('click_to_book');
		
		if ( current_user_can(WPB_ADMIN_CAP ) ) {
			$new_link ='<input type="hidden" class="app_new_link" value="'.admin_url('admin.php?page=appointments&add_new=1&app_id=0&app_worker='.$this->worker.'&app_timestamp='.$this->slot_start).'" />';
		}
		
		# This part comes from Schedules
		$add_class = $fill = '';
		$farr = apply_filters( 'app_weekly_calendar_cell_fill', array( 'class_name'=>'', 'fill'=>'' ), $this );
		if ( is_array( $farr ) ) {
			if ( isset( $farr['class_name'] ) ) { $add_class = $farr['class_name'];}
			if ( isset( $farr['fill'] ) ) { $fill = $farr['fill']; }
		}
			
		$class = $this->reason ? 'notpossible '. wpb_code2reason( $this->reason ) : 'free';
		
		/* Adjustment for schedules */
		# 4 weeks
		if ( 'all' === $this->service && $this->has_appointment )
			$class .= ' has_appointment';

		# daily and weekly schedule
		if ( $fill && $this->calendar instanceof WpBCalendar && $this->calendar->is_inline() ) {
			$class = str_replace( array( 'busy', 'notpossible', 'free', 'has_appointment' ), '', $class );
		}

		return '<td data-reason="'.$this->reason.'" class="'.$class.' '. $add_class.' app_slot_'.$this->slot_start.'" data-title="'.date_i18n($this->format, $this->a->client_time($this->slot_start)).'" title="'.$click_hint_text.'">'.
				$fill.'<input type="hidden" class="app_get_val" value="'.$this->pack( ).'" />'.
				$new_link. '</td>';
	}

	
}
}