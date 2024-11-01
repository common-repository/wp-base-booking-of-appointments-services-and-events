<?php
/**
 * WPB Working Hours
 *
 * Methods for Working Hours
 *
 * The power of this class comes from its scalability: 
 * Execution time will not increase with increasing number of workers or services
 * Some parts of this class may be quite difficult to follow. 
 * That is not intentional but a result of trying to keep codes optimal in speed
 *
 * A brief explanation about principle of operation:
 * If you are familiar with electronics, this is a kind of A/D conversion and multiple bit digital value processing. FYI, I am an electrical/electronics engineer.
 * Schema of DB table of working hours has been designed such that each column represents a 5 minute interval of the day.
 * Also 7 days for each 5 minute interval are packed in the same column. Actually I wanted to have a separate column for each 5 minute in the *week* but mySQL does not allow so many columns (max 1017 for innoDB - see note).
 * During saving of settings, working hours of services/providers (subjects) are divided into 5 minute fragments and written into the correct column of the DB. 
 * What is actually done here is optimization during writing to DB. 
 * Then querying for working time of a subject (or multiple subjects) is just checking if its ID is in the matching column or not, 
 * after A/D converting time value to be checked to its column number (I call this number system Wh domain).
 * Since one column can include thousands of subject IDs, and querying for one variable or for an array with thousand keys is almost the same thing,
 * thousands of subject Wh results become available at the same time, without much increase in total processing time (memory usage will increase by number of subjects obviously).
 * I didn't see this method in anywhere else. I may be the first person to use it (invent?) to save and query thousands of schedules at the same time.
 *
 * Note: Altough myISAM is the default engine for working hour tables, I chose to keep compatibility with innoDB engine. In other words, as mySQL engine innoDB can be used instead.
 *
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBWH' ) ) {

class WpBWH {
	
	/**
     * WP BASE instance
     */
	protected $a = null;
	
	/**
     * Constructor
     */
	function __construct() {
		$this->a = BASE();
	}
	
	function add_hooks(){
		add_action( 'app_installed', array( $this, 'new_install' ), 10, 2 );
		add_action( 'app_activated', array( $this, 'new_install' ), 10, 2 );
		add_action( 'app_new_worker_added', array( $this, 'new_worker' ), 14, 1 );
		add_action( 'app_new_service_added', array( $this, 'new_service' ), 14, 1 );
		add_action( 'app_service_deleted', array( $this, 'remove_service' ), 10, 1 );
		add_action( 'app_worker_deleted', array( $this, 'remove_worker' ), 10, 1 );
		
		add_action( 'app_save_settings', array( $this, 'save_wh' ), 12 );				// Save settings
		add_filter( 'appointments_business_tabs', array( $this, 'add_tab' ), 12 ); 		// Add tab
		add_action( 'app_business_working_hours_tab', array( $this, 'render_tab' ) );	// Display HTML settings on Business Settings
	}
	
	/**
     * Get current location ID
	 * @since 3.0
	 * @return integer
     */
	function get_location( $slot=null ) {
		# For now only location=0 is available
		return apply_filters( 'app_wh_location', 0, $slot );
	}
	
	/**
     * Add default working hours to the DB: For worker and service
     */
	function new_install( $installed_version, $previous_version ) {

		foreach( array('worker','service') as $subject ) {
			
			if ( 'worker' == $subject )
				$who = $this->a->get_default_worker_id();
			else
				$who = $this->a->get_first_service_id();
			
			if ( $this->get( $who, $subject ) )
				continue;

			$this->add_default( $who, $subject );
		}
	}
	
	/**
     * Add default working hours to DB for new service
	 * @param $who		integer		ID of the subject
	 * @return none
     */
	function new_service( $who ) {
		$this->add_default( $who, 'service' );
	}

	/**
     * Add default working hours to DB for new worker
	 * @param $who		integer		ID of the subject
	 * @return none
     */
	function new_worker( $who ) {
		$this->add_default( $who, 'worker' );
	}

	/**
     * Add default working hours to DB
	 * @param $who		integer		ID of the subject
	 * @param $subject	string		'worker', 'service' or 'alt'
	 * @return bool
     */
	function add_default( $who, $subject ){
		
		switch( $subject ) {
			case 'worker':	$table = $this->a->wh_w_table; break;
			case 'service':	$table = $this->a->wh_s_table; break;
			case 'alt':		$table = $this->a->wh_a_table; break;
		}
		
		$wh[$subject] = $this->get( 'all', $subject );
		if ( !is_array( $wh[$subject] ) )
			$wh[$subject] = array_fill(0,2016,''); 
		
		$d_worker = $this->a->get_default_worker_id();
		
		if ( ($who == $d_worker && 'worker' == $subject) || !$this->get( $d_worker, 'worker' ) ) {
			$vals = array_fill( 0, 288, '' ); // Sunday
			foreach ( range(1,5) as $day ) {
				$vals = array_merge( $vals, array_fill( 0, 96, '' ), array_fill( 0, 108, 1 ), array_fill( 0, 84, '' ) );
			}
			$vals = array_merge( $vals, array_fill( 0, 288, '' ) ); // Saturday
		}
		else
			$vals = $this->get( $d_worker, 'worker' );

		// Combine vals with wh
		$new_vals = array();
		foreach ( $vals as $key=>$val ) {
			$wh_val = isset( $wh[$subject][$key] ) ? $wh[$subject][$key] : '';
			if ( $val )
				$new_vals[$key]	= $this->add_item( $wh_val, $who );
			else if ( $wh_val )
				$new_vals[$key]	= $wh_val;
			else 
				$new_vals[$key] = '';
		}
		
		ksort( $new_vals );
		
		if ( $this->pack_n_save( $new_vals, $table ) ) {
			wpb_flush_cache();
			return true;
		}
		
		return false;
	}
	
	/**
     * Return a Wh domain array where key is Wh no and value is who is working in that slot
	 * @param $who				integer|string|array	'all' for all elements of the subject, ID for a particular element, an array or a list of elements
	 * @param $subject			string					'worker', 'service' or 'alt'
	 * @param $replace_with		integer					Replace $who with what ID. e.g. Replace Alt ID with worker ID 
	 * @return array
     */
	function get( $who='all', $subject = 'worker', $replace_with=false ) {
		$location = $this->get_location();
		
		# Special case: If alt_id:-1, this is holiday
		if ( 'alt' === $subject && -1 == $who )
			return array_fill(0,2016,'');
		
		$who_text = $who;
		if ( is_array( $who ) ) {
			if( empty( $who ) )
				return array_fill(0,2016,'');
			
			$who_text = implode( '_', $who );
		}
		
		$replace_with_text = (false === $replace_with) ? 'none' : $replace_with;
		$identifier = wpb_cache_prefix() . 'wh_'. $who_text . '_' . $subject . '_' . $location. '_' . $replace_with_text;
		
		$wh = wp_cache_get( $identifier );
		
		if ( false === $wh ) {
			# Can be used for external optimize, e.g. when subjects are numerous
			$wh = apply_filters( 'app_wh_optimize', false, $who, $subject, $replace_with );
			
			if ( false === $wh ) {
		
				switch( $subject ) {
					case 'worker':	$table = $this->a->wh_w_table; break;
					case 'service':	$table = $this->a->wh_s_table; break;
					case 'alt':		$table = $this->a->wh_a_table; break;
				}
				$wh = array();
				
				$identifier2 = wpb_cache_prefix() . 'wh_raw_'. $subject . '_' . $location;
				$wh_raw = wp_cache_get( $identifier2 );
				
				if ( false === $wh_raw ) {
					$wh_raw = $this->a->db->get_row( $this->a->db->prepare( "SELECT * FROM " . $table . " WHERE ID=%d", $location ), ARRAY_A );
					
					wp_cache_set( $identifier2, $wh_raw );
				}
					
				if ( is_array( $wh_raw ) ) {
				
					for ( $k=0; $k<288; $k++ ) {
						if ( isset( $wh_raw['c'.$k] ) ) {
							$temp = explode( '|', $wh_raw['c'.$k] );
							foreach( $temp as $day=>$ids ) {
								if ( $day >=7 )
									break;
								
								if ( is_array( $who ) )
									$wh[$day*288+$k] = implode(',', array_intersect( $who, explode( ',', $ids ) ));
								else if ( 'all' == $who )
									$wh[$day*288+$k]= $ids;
								else
									$wh[$day*288+$k] = in_array( $who, explode( ',', $ids ) ) ? ($replace_with ? $replace_with : $who) : '';
							}
						}
					}
					
					$wh = array_replace( array_fill(0,2016,''), $wh );
					ksort( $wh );
				}
				
				wp_cache_set( $identifier, $wh );
			}
		}

		return $wh;
	}
	
	/**
     * Combine alternative schedules and regular ones according to assignments
	 * @param $who				integer|string|array	'all' for all elements of the subject, ID for a particular element, an array or a list of elements
	 * @param $subject			string					'worker', 'service' or 'alt'
	 * @param $year_week_no		string					year + week no
	 * @return array
     */
	function combine_alt( $who='all', $subject = 'worker', $year_week_no ) {
		return apply_filters( 'app_combine_alt', $this->get( $who, $subject ), $who, $subject, $year_week_no );
	}
	
	/**
     * Convert timestamp to Wh domain
	 * @param $slot_start	integer		Timestamp
	 * @return integer					A number showing $slot_start resides in which 5th min of the week
     */
	function to( $slot_start ) {
		return intval((( $slot_start - wpb_sunday()) % 604800)/300);
	}
	
	/**
     * Convert Wh number to timestamp
	 * @param $no		integer			A number showing $slot_start resides in which 5th min of the week
	 * @param $ts		integer|false	Timestamp to be taken account for week calculation. If left empty, current week
	 * @return integer					Timestamp
     */
	function from( $no, $ts=false ) {
		if ( false === $ts )
			$ts = $this->a->_time;
		
		$day_no = date( 'w', $ts );
		if ( $no < $this->a->start_of_week * 288 )
			$no = $no + 2016;
		
		return wpb_sunday( $ts ) + $no *300;
	}
	
	/**
     * Find all available workers in a time slot
	 * @param $slot			WpBSlot object
	 * @return array|null
     */
	function available_workers( $slot ) {
		$slot_start	= $slot->get_start();
		$slot_end	= $slot->get_end();
		$identifier = wpb_cache_prefix() . 'avail_workers_'. $slot_start. '_' . $slot_end;
		
		$out = wp_cache_get( $identifier );
		
		if ( false === $out ) {
			$year_week_no = date( "Y", $slot_start ). wpb_time2week( $slot_start );
			$out = $this->available_subjects( $this->to( $slot_start ), $this->to( $slot_end ), false, 'worker', $year_week_no );
			
			wp_cache_set( $identifier, $out );
		}
		
		return $out;
	}
	
	/**
     * Find all available services in a time slot
	 * @param $slot			WpBSlot object
	 * @return array|null
     */
	function available_services( $slot, $who=false ) {
		$slot_start	= $slot->get_start();
		$slot_end	= $slot->get_end();
		$who_text	= false === $who ? 'all' : $who;
		$identifier	= wpb_cache_prefix() . 'avail_services_'. $slot_start. '_' . $slot_end .'_'. $who_text;
		
		$out = wp_cache_get( $identifier );
		
		if ( false === $out ) {
			$year_week_no = date( "Y", $slot_start ). wpb_time2week( $slot_start );
			$out = $this->available_subjects( $this->to( $slot_start ), $this->to( $slot_end ), $who, 'service', $year_week_no );
			
			wp_cache_set( $identifier, $out );
		}
		
		return $out;
	}

	/**
     * Find all available subjects in an interval in Wh domain
	 * @param $start, $end		Integer			Start and end wh:no values
	 * @param $who				Integer			ID of subject
	 * @param $subject			string			'worker', 'service' or 'alt'
	 * @param $year_week_no		string|false	year + week no
	 * @return array|null
     */
	function available_subjects( $start, $end, $who=false, $subject = 'worker', $year_week_no=false ) {
		$location = $this->get_location();
		
		# If Annual Schedules are not active, result is independent of week number
		$week_text = (false === $year_week_no) || !class_exists('WpBAnnual') ? 'none' : $year_week_no;
		$who_text = (false === $who) ? 'all' : $who;
		$identifier = wpb_cache_prefix() . 'avail_subj_'. $start .'_'. $end .'_'. $who_text .'_'. $subject .'_'. $week_text;
		
		$out = wp_cache_get( $identifier );
		
		if ( false === $out ) {
		
			if ( false === $year_week_no || !$this->get( 'all', 'alt' ) )
				$wh = $this->get( 'all', $subject );
			else
				$wh = $this->combine_alt( 'all', $subject, $year_week_no );

			if ( 0 === ($end - $start) ) {
				$out = null;
			}
			else if ( 1 == ($end - $start) || -2015 == ($end - $start) ) {
				if ( isset( $wh[$start] ) && trim( $wh[$start] ) )
					$out = explode( ',', $wh[$start] );
				else
					$out = null;				
			}
			else {
				if ( $who )
					$ids = array( $who );
				else {
					if ( 'worker' == $subject ) {
						$ids = (array)$this->a->get_worker_ids();
						$ids[] = $this->a->get_default_worker_id();
						$ids = array_unique( $ids );
					}
					else $ids = $this->a->get_service_ids();
				}

				if ( ($end - $start) < 0 )
					$end = $end + 2016;
				
				$in = $out = array();
				
				for ( $k=$start; $k<$end; $k++ ) {
					$mod_k = wpb_mod( $k, 2016 );
					if ( isset( $wh[$mod_k] ) && trim($wh[$mod_k]) )
						$in[] = explode( ',', $wh[$mod_k] );
					else
						$out = 0;
				}
				$in = array_filter( $in );
				
				if ( is_array( $out ) && sizeof( $in ) >= 2 ) {
					// Foreach is faster than array_reduce
					$acc = $ids;
					foreach ( $in as $a ) {
						$acc = array_intersect( $acc, $a );
					}
					$out = $acc;
				}
				else if ( empty( $in ) )
					$out = null; // Complete break for anyone of the subject
				
				if ( null !== $out && $who ) {
					$found_one = false;
					foreach ( $in as $a ) {
						if ( in_array( $who, $a ) ) {
							$found_one = true;
							break;
						}
					}
					if ( !$found_one ) {
						$out = null; // Complete break for tested subject
					}
				}
			}
			
			wp_cache_set( $identifier, $out );
		}

		return $out;
	}
	
	/**
     * Return all turning points for a given service or worker
	 * A turning point is a value in minutes where subject starts working either at day start or after a break
	 * Example: 9am-1pm, 2pm-6pm work gives array( 540, 840 ) 
	 * @param $who			Integer			ID of subject
	 * @param $subject		string			'worker', 'service' or 'alt'
	 * @since 3.0
	 * @return array
     */
	function find_turning_points( $who, $subject ) { 
		$avails = $this->get( $who, $subject );
		
		$cells = preg_grep( '/(^'.$who.'$|^'.$who.',|,'.$who.',|,'.$who.'$)/', $avails );
		
		$net_cells = array_keys( array_filter( $cells ) );
		if ( empty( $net_cells ) || 2016 == count( $net_cells ) ) // All working or all holiday
			return array();
		
		$initial_el = array_shift( $net_cells );
		$initial = array( 0=>$initial_el, 1=>array($initial_el) );
		
		$res = array_reduce( $net_cells, 
			function( $carry, $item ) {
				if ( $item > $carry[0] + 1 ) {
					$carry[1][] = $item;
				}
				$carry[0] = $item;
				return $carry;
			},
			$initial
		);
		
		$func1 = function($value) {
			return wpb_mod( $value, 288);
		};
		
		$func2 = function($value) {
			return $value * 5;
		};
		
		return array_map( $func2, array_unique( array_map( $func1, $res[1] ) ) );
	}
	
	/**
     * Check if a certain time base value divides all wh table turning points
	 * @param $tb	integer		Time base value in minutes
	 * @since 3.0
	 * @return bool
     */
	function maybe_divide( $tb ){
		
		$saw = array( 	'service'	=> $this->a->get_services(),
						'worker'	=> $this->a->get_workers(),
						'alt'		=> $this->get_schedules(),
					);
					
		foreach ( $saw as $what=>$subjects ) {
			if ( empty( $subjects ) )
				continue;
			
			foreach ( $subjects as $subject ) {
				
				$subject_id = isset( $subject->ID ) ? $subject->ID : key($subject);
				$arr = $this->find_turning_points( $subject_id, $what );
				if ( empty( $arr ) )
					continue;
				
				foreach( $arr as $el ) {
					if ( 0 != wpb_mod( $el, $tb ) )
						return false;
				}
			}
		}
		
		return true;
	}
	
	/**
     * Find working hour start and end times of a worker or unassigned worker
	 * This is used as an optimization by reducing looped time slots
	 * @param $day_start	integer		Start timestamp of the day to be checked
	 * @param $s			integer		Service ID
	 * @param $w			integer		Worker ID
	 * @return array (in minutes)
     */
	function find_limits( $day_start, $s, $w ) {
		$location = $this->get_location();
		
		if ( $w ) {
			$who = $w;
			$subject = 'worker';
			$who_text = 'worker'. $w;
		}
		else if ( $s ) {
			$who = $s;
			$subject = 'service';
			$who_text = 'service'. $s;
		}
		else
			return array( 'first'=>0, 'last'=>24*60 );
		
		$year_week_no = date( 'Y', $day_start ) . wpb_time2week( $day_start );		
		$identifier	= wpb_cache_prefix() . 'daily_limits_'. $who_text . '_' . $location . '_'. $day_start;
		$limits = wp_cache_get( $identifier );
		
		if ( false === $limits ) {
			$no = $this->to( $day_start );

			if ( false === $year_week_no || !$this->get( 'all', 'alt' ) )
				$avails = $this->get( $who, $subject );
			else 
				$avails = $this->combine_alt( $who, $subject, $year_week_no );

			$avails_day = array_slice( $avails, $no, 288 );
			
			$slots = preg_grep( '/(^'.$who.'$|^'.$who.',|,'.$who.',|,'.$who.'$)/', $avails_day );
			
			$first= max( 0, 5*key($slots) );
			end( $slots );
			$last = min( 60*24, 5*key($slots) );
			
			$limits = array( 'first'=>$first, 'last'=>$last );
			
			wp_cache_set( $identifier, $limits );
		}
		
		return $limits;
	}

	/**
     * Find available weekly wh cells of a worker or if worker_id=0, that of $job_service_id 
	 * @param $day_start: Start timestamp of the day within the week to be retreived
	 * @return array or false
     */
	function find_cells( $job_service_id, $worker_id, $year_week_no ) {
		$location = $this->get_location();
		
		if ( $worker_id ) {
			$who = $worker_id;
			$subject = 'worker';
			$who_text = 'worker'. $worker_id;
		}
		else if ( $job_service_id ) {
			$who = $job_service_id;
			$subject = 'service';
			$who_text = 'service'. $job_service_id;
		}
		else
			return false;

		$identifier = wpb_cache_prefix() . 'daily_slots_'. $who_text . '_' . $location . '_'. $year_week_no;
		$slots = wp_cache_get( $identifier );
		
		if ( false === $slots ) {
					
			if ( false === $year_week_no || !$this->get( 'all', 'alt' ) )
				$avails = $this->get( $who, $subject );
			else 
				$avails = $this->combine_alt( $who, $subject, $year_week_no );

			$slots = preg_grep( '/(^'.$who.'$|^'.$who.',|,'.$who.',|,'.$who.'$)/', $avails );

			wp_cache_set( $identifier, $slots );
		}

		return $slots;
	}
	
	/**
     * Find number of available wh cells of a worker or unassigned worker for a given time 
	 * and recurring $nof_days times at the same time of the day. There may be gaps in between.
	 * @param $css: Timestamp to be checked
	 * @param $nof_days: Recurring for how many days?
	 * @param limit: Optional limit. If number of available slots reach this value, counting will be terminated
	 * @return integer
     */
	function count_lateral_cells( $job_service_id, $worker_id, $slot_start_start, $nof_days, $limit=null ) {
		$count = $missed = 0;
		for ( $d=0; $d<$nof_days; $d++ ) {
			$slot_start = $slot_start_start + $d*86400;
			$no = $this->to( $slot_start );
			$year_week_no = date( 'Y', $slot_start) . wpb_time2week( $slot_start );
			$slots = $this->find_cells( $job_service_id, $worker_id, $year_week_no ); 
			if ( isset( $slots[$no] ) )
				$count++;
			else
				$missed++;
				
			if ( $missed > $nof_days - $limit )
				break;
			if ( $limit && $count >= $limit )
				break;
		}
		return $count;
	}

	/**
     * Quick check if worker is available in ANY part of the day (acc to server)
	 * This check will not work and be skipped if client is in a different timezone
	 * @param $slot: WpBSlot object
	 * @return array or false
     */
	function is_working_day( $slot ) {
		$location = $slot->get_location( );
		$location = $this->get_location( $slot );
		
		$service = $slot->get_service();
		$who = $slot->get_worker(); 
		if ( !$who ){ $who = 'all'; }
		
		// Even workers on different timezone, server time is taken into account
		$day_start = strtotime( date("Y-m-d", $slot->get_start() ) );
		$identifier = wpb_cache_prefix() . 'avails_by_day_'. $who . '_' . $location . '_'. $service .'_' . $day_start;
		$nof_avail = wp_cache_get( $identifier );
		
		if ( false === $nof_avail ) {
			$year_week_no = date( 'Y', $day_start ) . wpb_time2week( $day_start );		
			$no = $this->to( $day_start );
			
			if ( false === $year_week_no || !$this->get( 'all', 'alt' ) )
				$avails = $this->get( $who, 'worker' );
			else 
				$avails = $this->combine_alt( 'all', 'worker', $year_week_no );
			
			$avails_day = array_slice( $avails, $no, 288 );
			$avails_day = array_filter( array_unique( explode( ',', implode( ',', $avails_day ) ) ) );

			if ( 'all' === $service )
				$workers_by_service = $this->a->get_worker_ids( );
			else
				$workers_by_service = $this->a->get_worker_ids_by_service( $service );
		
			$nof_avail = count( array_intersect( (array)$workers_by_service, $avails_day ) );
		
			wp_cache_set( $identifier, $nof_avail );
		}

		if ( $nof_avail > 0 )
			return true;
		else
			return false;
	}

	/**
     * Quick check if current service is available in ANY part of the day
	 * @param $slot: WpBSlot object
	 * @return array or false
     */
	function is_working_day_for_service( $slot ) {
		$location = $slot->get_location( );
		$location = $this->get_location( $slot );
		
		$who = $slot->get_service();
		if ( !$who )
			return false;
		
		$day_start = strtotime( date("Y-m-d", $slot->get_start() ) );
		$identifier = wpb_cache_prefix() . 'avails_by_day_for_service_'. $who . '_' . $location . '_'. $day_start;
		$nof_avail = wp_cache_get( $identifier );
		
		if ( false === $nof_avail ) {
			$year_week_no = date( 'Y', $day_start ) . wpb_time2week( $day_start );		
			$no = $this->to( $day_start );

			if ( false === $year_week_no || !$this->get( 'all', 'alt' ) )
				$avails = $this->get( $who, 'service' );
			else
				$avails = $this->combine_alt( $who, 'service', $year_week_no );
				
			$avails_day = array_slice( $avails, $no, 288 );
			$avails_day = array_filter( array_unique( explode( ',',implode( ',', $avails_day ) ) ) );
		
			$nof_avail = count( $avails_day );
			
			wp_cache_set( $identifier, $nof_avail );
		}
		
		return $nof_avail;
	}

	/**
     * Check if subject is NOT working in given interval, except holidays
	 * @param $slot		WpBSlot object
	 * @return bool
     */
	function is_break( $slot, $who, $subject = 'worker' ) {
		$slot_start		= $slot->get_start();
		$slot_end		= $slot->get_end();
		$year_week_no	= date( "Y", $slot_start ). wpb_time2week( $slot_start );
		
		return $this->is_break_wh( $this->to( $slot_start ), $this->to( $slot_end ), $who, $subject, $year_week_no );
	}
	
	/**
     * Check if subject is NOT working in given interval in wh domain, except holidays
	 * @param start, end	Integer		Start and end wh:no values
	 * @return integer|false			false, 2, 4 or 5 can be returned
     */
	function is_break_wh( $start, $end, $who, $subject = 'worker', $year_week_no ) {
		$subjects = $this->available_subjects( $start, $end, $who, $subject, $year_week_no );
		if ( !$subjects ) {
			if ( null === $subjects )
				return 5; # complete_brk Break until end - can be used to skip the day for optimization
			else
				return 2; # no_workers
		}
		
		return in_array( $who, $subjects ) ? false : 4; # Break
	}
	
	/**
     * Draw working hours table
	 * @param wh_selected: an array of subject|who values
	 * @param subject: Worker, service or alternative
	 * @param bp: false or BuddyPress user ID
	 * @return string (html)
     */
	function draw( $wh_selected ) {
		global $current_user;
		
		if ( is_admin( ) )
			add_action( 'admin_footer', array( $this, 'footer' ) );
		else
			add_action( 'wp_footer', array( $this, 'footer' ) );

		$r  = '';
		$r .= apply_filters( 'app_admin_wh_before', $r, $wh_selected );

		$r .= '<div class="postbox">';
		$r .= '<h3 class="hndle"><span>'. __('Working Hour Schedules', 'wp-base'). '</span></h3>';
		$r .= '<div class="inside" id="app-wh">';
	
		foreach( $wh_selected as $val ) {
			list( $subject_s, $who_s ) = explode( '|', $val );
			$r .= $this->draw_s( $who_s, $subject_s, '25' );
		}			
		$r .= "<div style='clear:both'></div>";

		$r .= "</div></div>";
		

		return $r;
	}
	
	/**
     * Draw working hours table helper
	 * @param who: ID of the subject
	 * @param subject: Worker, service or alternative
	 * @param width: Width of the calendar in%. If given, it also adds copy/paste buttons
	 * @return string
     */
	function draw_s( $who, $subject = 'worker', $width='' ) {

		switch( $subject ) {
			case 'worker':	$pre_text = ($who == $this->a->get_default_worker_id()) ? __('Business Rep','wp-base') : $this->a->get_text('provider');
							$whose=  $pre_text.': '. $this->a->get_worker_name( $who ); 
							break;
			case 'service':	$whose= $this->a->get_text('service') .': '. $this->a->get_service_name( $who );				
							break;
			case 'alt':		$whose = __('Alt','wp-base') .': ' . $this->get_schedule_name( $who );
							break;
		}

		$r  = '';
		if ( $width )
			$r .= '<div class="appointments-wrapper appointments-wrapper-admin" style="width:'.$width.'%" >';
		else
			$r .= '<div class="appointments-wrapper">';
		$r .= '<div class="app_title app_c">' . $whose . '</div>';
        $r .= '<div class="appointments-list">';
		$r .= '<div class="app_schedule_wrapper app_schedule_wrapper_admin">';
		$r .= '<table class="app-wh app-wh-'.$subject.'|'.$who.'" width="100%">';
		$r .= "<tbody>";
		$r .= $this->a->_get_table_meta_row('thead', false);
		
		$step = $this->a->get_min_time()/5;
		$days = wpb_arrange( array(0,1,2,3,4,5,6), -1, false );
		$copy_text = __('Copy to clipboard','wp-base');
		$vals = array();
		$cl_offset = $this->a->get_client_offset( );
		$no_offset = intval($cl_offset/300);
		
		for ( $k=0; $k<288; $k=$k+$step ) {
			$cl_row = 'app_row'. (intval($k/$step) + 1);
			$r  .='<tr>';
			foreach ( $days as $day ) {
				$no = wpb_mod( ($day*288 +$k + $no_offset ), 2016 );
				if ( -1 == $day ) {
					$text = ($step == 288) ? __('All Day','wp-base') : date_i18n( $this->a->time_format, $this->from( wpb_mod($no-$no_offset, 2016) ) );
					$r .= "<td class='app-weekly-hours-mins ui-state-default ".$cl_row."'>".$text."</td>";
				}
				else {
					$cl_column = ' app_'.strtolower( date('l', $this->from( wpb_mod($no, 2016) ) ) );
					if ( $this->is_break_wh( wpb_mod($no, 2016), wpb_mod($no, 2016)+$step, $who, $subject, false ) ) {
						$vals[$no] = 0;
						$r .= "<td class='notpossible app_select_wh ".$cl_row.$cl_column."'>
								<input type='hidden' class='app_wh_value' data-no='".wpb_mod($no, 2016). "' value='0' /></td>";
					} 
					else {
						$vals[$no] = 1;
						$r .= "<td class='free app_select_wh ".$cl_row.$cl_column."'>
								<input type='hidden' class='app_wh_value' data-no='".wpb_mod($no, 2016). "' value='1' /></td>";
					}
				}
			}
			$r  .='</tr>';
		}
		
		for ( $no=0; $no<2016; $no++ ) {
			if ( !isset( $vals[$no] ) )
				$vals[$no] = $last_val;
			else
				$last_val = $vals[$no];
		}
		ksort( $vals );
		
		$app_wh_vals = '';
		foreach( $vals as $no=>$val ) {
			$app_wh_vals = $app_wh_vals . $val;
		}
		
		$r .= "<input type='hidden' name='app_wh[$subject][$who]' class='app_coded_val' value='$app_wh_vals' />";
		$r .= "</tbody></table>";
		if ( $width )
			$r .= '<div class="app_mt app_c"><button data-status="copy_ready" class="app-copy-wh ui-button ui-state-default ui-corner-all ui-shadow">'.$copy_text.'</button></div>';
		$r .= "</div></div></div>";
		$r .= '<input type="hidden" name="app_select_wh[]" class="app-select-wh-val" value="'.$subject.'|'.$who.'" />';
		
		return $r;
	}
	
	/**
	 * Get Alt schedules
	 * @return array
     */
	function get_schedules(){
		return apply_filters( 'app_get_schedules', array() ); 
	}

	/**
	 * Get name of the schedule given ID
	 * @param $ID	integer		Schedule ID
	 * @return string
     */
	function get_schedule_name( $ID ){
		$s = $this->get_schedules();
		return (isset( $s[$ID]['name'] ) ? stripslashes( $s[$ID]['name'] ) : '');
	}

	/**
     * Add script to footer
     */
	function footer(){
		if ( !empty( $this->script_added ) )
			return;
	?>
		<script type='text/javascript'>
		jQuery(document).ready(function ($) {
			$("#app_sel_wh_options").multiselect({
				noneSelectedText:'<?php echo esc_js( __('Select services/providers', 'wp-base' )) ?>',
				selectedList:5,
				classes:"app_workers",
				minWidth:300
			});
			
			var step = parseInt(<?php echo $this->a->get_min_time()/5 ?>);
			
			function update_coded(me){
				var par = me.parents("table.app-wh");
				var target = par.find("td").not(".app-weekly-hours-mins");
				var coded_field = par.find(".app_coded_val");
				var coded_f_val = coded_field.val();
				$.each( target, function(i,v) {
					var inp = $(this).find(".app_wh_value");
					var val = inp.val();
					var repeat = val;
					if ( step > 1 ) {
						repeat = Array(step+1).join(val);
					}
					var no = inp.data("no");
					// http://stackoverflow.com/a/1431109
					coded_f_val = coded_f_val.substr(0, no) + repeat + coded_f_val.substr(no + step);
				});
				coded_field.val(coded_f_val);
			}
			
			$(document).on("click", ".app_select_wh",function(){
				if ($(this).hasClass("free")){
					$(this).removeClass("free").addClass("notpossible");
					$(this).find(".app_wh_value").val(0); 
				}
				else {
					$(this).removeClass("notpossible").addClass("free");
					$(this).find(".app_wh_value").val(1);
				}
				update_coded($(this));
			});
			
			$(document).on("click", "table.app-wh th",function(){
				var me = $(this);
				var par = me.parents("table.app-wh");
				me.toggleClass("free");
				var cl = me.attr("class").replace("free","");
				var target = par.find("td."+cl);
				if ( me.hasClass("hourmin_column") ) {
					target = par.find("td").not(".app-weekly-hours-mins");
					par.find(".app-weekly-hours-mins").toggleClass("free-s");
					par.find("th").not(me).toggleClass("free");
				}
				$.each( target, function(i,v) {
					if (me.hasClass("free")){
						$(this).addClass("free").removeClass("notpossible");
						$(this).find(".app_wh_value").val(1); 
					}
					else {
						$(this).addClass("notpossible").removeClass("free");
						$(this).find(".app_wh_value").val(0);
					}
				});
				update_coded(me);
			});
			
			$(document).on("click", "table.app-wh td.app-weekly-hours-mins",function(){
				var me = $(this);
				me.toggleClass("free-s");
				var par = me.parents("table.app-wh");
				var cl = me.attr("class").replace("free-s","");
				$.each(cl.split(" "),function(i,v){
					if ( v.substr(0,7) =="app_row") {
						cl = v;
						return false;
					}
				});
				var target = par.find("td."+cl).not(me);
				$.each( target, function(i,v) {
					if (me.hasClass("free-s")){
						$(this).addClass("free").removeClass("notpossible");
						$(this).find(".app_wh_value").val(1); 
					}
					else {
						$(this).addClass("notpossible").removeClass("free");
						$(this).find(".app_wh_value").val(0);
					}
				});
				update_coded(me);
			});

			var copy_ready_text = "<?php echo esc_js( __('Copy to clipboard','wp-base') ) ?>";
			var copied_text = "<?php echo esc_js(__('Copied (Click to release)','wp-base') )?>";
			var paste_ready_text = "<?php echo esc_js( __('Paste data of WHOSE','wp-base') )?>";
			var pasted_text = "<?php echo esc_js( __('Undo','wp-base') ) ?>";
			var whose_text = "<?php echo esc_js( __('copied table','wp-base') ) ?>";
			
			$(document).on("click", ".app-copy-wh",function(e){
				e.preventDefault();
				var wh_val ={};
				var wh_old_val ={};
				var status = $(this).data("status");
				var par = $(this).parents(".app_schedule_wrapper");
				var others = $(document).find(".app-copy-wh").not(this);
				var slot_inputs = par.find(".app_wh_value");
				if ( "copy_ready" == status ) {
					$.each(slot_inputs, function(i,v){
						var no = $(this).data("no");
						var val = $(this).val();
						wh_val[no] = val;
					});
					$(document).data("wh_val",wh_val);
					$(this).data("status","copied").text(copied_text);
					whose_text = $(this).parents(".appointments-wrapper").find(".app_title").text();
					others.data("status","paste_ready").text(paste_ready_text.replace("WHOSE",whose_text));
				}
				else if ( "copied" == status ) {
					$(".app-copy-wh").data("status","copy_ready").text(copy_ready_text);
				}
				else if ( "paste_ready" == status ) {
					$.each(slot_inputs, function(i,v){
						var no = $(this).data("no");
						var old_val = $(this).val();
						wh_old_val[no] = old_val;
						$(this).data("wh_old_val", wh_old_val );
						if ( typeof $(document).data("wh_val") !== "undefined" ) {
							wh_val = $(document).data("wh_val");
							var new_val = wh_val[no];
							$(this).val(new_val);
							var td = $(this).parent(".app_select_wh");
							if ( 1 == parseInt(new_val) ) {td.removeClass("notpossible").addClass("free");}
							else{td.removeClass("free").addClass("notpossible");}
						}
					});
					$(this).data("status","pasted").text(pasted_text);
					// others.data("status","paste_ready").text(paste_ready_text.replace("WHOSE",whose_text));
					update_coded(slot_inputs);					
				}
				else if ( "pasted" == status ) {
					$.each(slot_inputs, function(i,v){
						var no = $(this).data("no");
						if ( typeof $(this).data("wh_old_val") !== "undefined" ) {
							wh_old_val = $(this).data("wh_old_val");
							var old_val = wh_old_val[no];
							$(this).val(old_val);
							var td = $(this).parent(".app_select_wh");
							if ( 1 == parseInt(old_val) ) {td.removeClass("notpossible").addClass("free");}
							else{td.removeClass("free").addClass("notpossible");}
						}
					});
					$(this).data("status","paste_ready").text(paste_ready_text.replace("WHOSE",whose_text));
					update_coded(slot_inputs);					
				}
			});
		});
		</script>
	<?php
		$this->script_added = true;	
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
			$tabs['working_hours'] = __('Working Hours', 'wp-base');
		
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
			/* Descriptions */
			$desc = WpBConstant::wh_desc();
			$visible = $desc[0];
			unset( $desc[0] );
			wpb_infobox( $visible, $desc );
			
			$workers = $this->a->get_workers();
			$default_worker = $this->a->get_default_worker_id();
			$default_worker_name = $this->a->get_worker_name( $default_worker, false );
			// Defaults
			$wh_selected = isset($_POST['app_select_wh']) && is_array($_POST['app_select_wh']) 
							? $_POST['app_select_wh'] 
							: array( 'worker|'.$default_worker ); 
			$wh_selected[] = 'worker|'.$default_worker;
			$wh_selected = array_unique( $wh_selected );

			?>
			<div class='postbox app_prpl'>
			<div class='app-submit app_2column'>
			<div class="app_mt">
				<form method="post">
				<span class='app_provider_list app_mr10'><?php _e('List for', 'wp-base')?></span>
					<select multiple id="app_sel_wh_options" name="app_select_wh[]">
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
								$s = in_array( 'worker|'.$profileuser_id, $wh_selected ) ? " selected='selected'" : '';
								echo '<option value="worker|'.$profileuser_id.'"'.$s.'>' . $this->a->get_worker_name( $profileuser_id, false ) . '</option>';
							}
							else {
								foreach ( $workers as $worker ) {
									$s = in_array( 'worker|'.$worker->ID, $wh_selected ) ? " selected='selected'" : '';
									echo '<option value="worker|'.$worker->ID.'"'.$s.'>' . $this->a->get_worker_name( $worker->ID, false ) . '</option>';
								}
							}
						}
					if ( class_exists( 'WpBSP' ) ) { ?>
						</optgroup>
						<?php }	 ?>	
						
						<optgroup label="<?php _e('Services','wp-base') ?>" class="optgroup_service">
						<?php
						if ( $profileuser_id )
							$services = $this->a->db->get_results("SELECT * FROM " . $this->a->services_table . " WHERE ID IN ( SELECT object_id FROM ".$this->a->meta_table." WHERE meta_type='service' AND meta_key='created_by' AND meta_value='".esc_sql($profileuser_id)."' )  ORDER BY sort_order,ID " ); 				
						else
							$services = $this->a->get_services();
						if ( $services ) {
							foreach ( $services as $service ) {
								$s = in_array( 'service|'. $service->ID, $wh_selected ) ? " selected='selected'" : '';
								// $disabled = $this->a->is_package( $service->ID ) ? ' disabled="disabled"' : '';
								$disabled = '';
								echo '<option value="service|'.$service->ID.'"'. $s.$disabled .'>' . $this->a->get_service_name( $service->ID ) . '</option>';
							}
						}
						?>
						</optgroup>
											
						<?php if ( class_exists( 'WpBAnnual' ) && !$profileuser_id ) { ?>
						<optgroup label="<?php _e('Alternative Schedules','wp-base') ?>" class="optgroup_alt">
						<?php
						$schedules = $this->get_schedules();
						if ( $schedules ) {
							foreach ( $schedules as $ID=>$schedule ) {
								if ( in_array( 'alt|'. $ID, $wh_selected ) ) {
									$s = " selected='selected'";
								}
								else
									$s = '';							
								echo '<option value="alt|'.$ID.'"'. $s .'>' . stripslashes( $schedule["name"] ) . '</option>';
							}
						}
						?>
						</optgroup>
						<?php } ?>
					</select>
					<button id="app_sel_wh_options_btn" class="ui-button ui-state-default ui-corner-all ui-shadow"><?php _e('Show','wp-base') ?></button>
				</div>
			</form>	
			</div>
			<div class='app-submit app_2column'>
			<?php do_action( 'app_wh_admin_submit' ) ?>
			</div>
			<div style="clear:both"></div>
			</div>
			

		<form class="app_form" method="post"  action="<?php echo wpb_add_query_arg( null, null )?>">
		
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php echo __('Save Working Hours', 'wp-base') ?>" />
			</p>

		<div id="tabs" class="app-tabs">
			<ul></ul>
			
			<?php
				$bp_user_id = $bp ? $profileuser_id : false;			
				echo $this->draw( $wh_selected );
	
				$disabled = $this->a->get_nof_workers() ? '' : '';
				
				if ( !$profileuser_id ) {
			?>
			
			<div class='postbox'>
			<h3 class="hndle"><span><?php echo __('Advanced', 'wp-base') ?></span></h3>

			<div class='inside'>
				<table class="form-table">
				<tr id="service-wh-covers">
					<th scope="row"><?php WpBConstant::echo_setting_name('service_wh_covers') ?></th>
					<td>
					<select name="service_wh_covers">
					<option value="no" <?php if ( wpb_setting('service_wh_covers') != 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
					<option value="yes" <?php if ( wpb_setting('service_wh_covers') == 'yes' ) echo "selected='selected'"?><?php echo $disabled?>><?php _e('Yes', 'wp-base')?></option>
					</select>
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('service_wh_covers') ?></span>
					</td>
				</tr>
				<tr id="service_wh_check">
					<th scope="row"><?php WpBConstant::echo_setting_name('service_wh_check') ?></th>
					<td>
					<select name="service_wh_check">
					<option value="no" <?php if ( wpb_setting('service_wh_check') != 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
					<option value="yes" <?php if ( wpb_setting('service_wh_check') == 'yes' ) echo "selected='selected'"?><?php echo $disabled?>><?php _e('Yes', 'wp-base')?></option>
					</select>
					<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('service_wh_check') ?></span>
					</td>
				</tr>
				</table>
			</div>
			</div>
			<?php } ?>
		</div><!-- Tabs -->
			
			<p class="submit">
			<input type="hidden" name="app_bp_settings_user" value="<?php echo $bp_user_id ?>" />
			<input type="hidden" name="location" value="0" />
			<input type="hidden" name="action_app" value="save_working_hours" />
			<input type="hidden" name="app_nonce" value="<?php echo wp_create_nonce( 'update_app_settings' ) ?>" />
			<input type="submit" class="button-primary" value="<?php echo  __('Save Working Hours', 'wp-base') ?>" />
			</p>
		</form>
			
		</div>

		<?php
	}
	
	/**
     * Remove service from wh table
	 * @param $ID		integer		ID of the service
	 * @return bool
     */
	function remove_service( $ID ) {
		$this->remove( $ID, 'service' );
	}
	
	/**
     * Remove worker from wh table
	 * @param $ID		integer		ID of the worker
	 * @return bool
     */
	function remove_worker( $ID ) {
		$this->remove( $ID, 'worker' );
	}

	/**
     * Remove a certain element from wh table
	 * @param $who		integer		ID of the subject
	 * @param $subject	string		'worker', 'service' or 'alt'
	 * @return bool
     */
	function remove( $who, $subject ) {
		switch( $subject ) {
			case 'worker':	$table = $this->a->wh_w_table; break;
			case 'service':	$table = $this->a->wh_s_table; break;
			case 'alt':		$table = $this->a->wh_a_table; break; 
		}
		
		$result = false;
		$new_vals = array();
		foreach ( $this->get( 'all', $subject ) as $key=>$val ) {
			$new_vals[$key]	= $this->remove_item( $val, $who );
		}
		ksort( $new_vals );
		
		
		$result1 = $this->pack_n_save( $new_vals, $table );

		// Also remove holidays and annual schedules
		$options = $this->a->get_business_options();
		
		if ( isset( $options['holidays'][$subject] ) ) {
			foreach ( (array)$options['holidays'][$subject] as $who_id=>$val ) {
				if ( $who_id == $who )
					unset( $options['holidays'][$subject][$who] );
			}
		}
		if ( isset( $options['alt_schedule_pref'][$subject] ) ) {
			foreach ( (array)$options['alt_schedule_pref'][$subject] as $year_week_no=>$who_id ) {
				if ( $who_id == $who )
					unset( $options['alt_schedule_pref'][$subject][$year_week_no][$who] );
			}
		}
		
		$result2 = $this->a->update_business_options( $options );
		
		if ( $result1 || $result2 ) {
			wpb_flush_cache();
			return true;
		}
		else return false;
	
	}

	/**
     * Replace a certain element with another element at wh table + Holidays + Alts
	 * @param $who			integer		Find (worker id, service id, alt id)
	 * @param $with_whom	integer		Replace (worker id, service id, alt id)
	 * @return bool
     */
	function replace( $who, $with_whom, $subject ) {
		switch( $subject ) {
			case 'worker':	$table = $this->a->wh_w_table; break;
			case 'service':	$table = $this->a->wh_s_table; break;
			case 'alt':		$table = $this->a->wh_a_table; break; 
		}
		
		$result = false;
		$new_vals = array();
		foreach ( $this->get( 'all', $subject ) as $key=>$val ) {
			if ( in_array( $who, explode(',',$val) ) ) {
				$new_vals[$key]	= $this->remove_item( $val, $who );
				$new_vals[$key]	= $this->add_item( $val, $with_whom );
			}
			else
				$new_vals[$key] = $val;
		}
		ksort( $new_vals );
		
		
		$result1 = $this->pack_n_save( $new_vals, $table );

		// Also remove holidays and annual schedules
		$options = $this->a->get_business_options();
		
		if ( isset( $options['holidays'][$subject] ) ) {
			foreach ( (array)$options['holidays'][$subject] as $who_id=>$val ) {
				if ( $who_id == $who && isset( $options['holidays'][$subject][$who] ) ) {
					$options['holidays'][$subject][$with_whom] = $options['holidays'][$subject][$who];
					unset( $options['holidays'][$subject][$who] );
				}
			}
		}
		if ( isset( $options['alt_schedule_pref'][$subject] ) ) {
			foreach ( (array)$options['alt_schedule_pref'][$subject] as $year_week_no=>$who_id ) {
				if ( $who_id == $who && isset($options['alt_schedule_pref'][$subject][$year_week_no][$who]) ) {
					$options['alt_schedule_pref'][$subject][$year_week_no][$with_whom] = $options['alt_schedule_pref'][$subject][$year_week_no][$who];
					unset( $options['alt_schedule_pref'][$subject][$year_week_no][$who] );
				}
			}
		}
		
		$result2 = $this->a->update_business_options( $options );
		
		if ( $result1 || $result2 ) {
			wpb_flush_cache();
			return true;
		}
		else return false;
	
	}

	/**
     * Save Wh settings: Decode submit value, pack all subject items together, encode and save
     */
	function save_wh( $profileuser_id=false ) {
		if ( isset( $_POST['app_nonce'] ) && !wp_verify_nonce($_POST['app_nonce'],'update_app_settings') ) {
			wpb_notice( 'unauthorised', 'error' );
			return;
		}
		
		if ( 'save_working_hours' != $_POST["action_app"] || !isset( $_POST['app_wh'] ) || !is_array( $_POST['app_wh'] ) )
			return;
		
		global $current_user;
		
		$location = $this->get_location();
	
		$result = $result2 = false;
		$wh = $updated_workers = array();
		
		$options = wpb_setting();
		
		if ( isset( $_POST["service_wh_covers"] ) )
			$options["service_wh_covers"]			= $_POST["service_wh_covers"];
		if ( isset( $_POST["service_wh_check"] ) )
			$options["service_wh_check"]			= $_POST["service_wh_check"];
		
		if ( $this->a->update_options( $options ) )
			$result2 = true; 
		
		// Save sort order
		$sort = '';
		foreach( $_POST['app_wh'] as $subject=>$who_data ) {
			foreach( $who_data as $who=>$vals ) {
				$sort .= $subject.'|'.$who.',';
			}
		}
		
		foreach( array('worker','service','alt') as $subject ) {
			if ( empty( $_POST['app_wh'][$subject] ) )
				continue;
			
			switch( $subject ) {
				case 'worker':	$table = $this->a->wh_w_table; break;
				case 'service':	$table = $this->a->wh_s_table; break;
				case 'alt':		$table = $this->a->wh_a_table; break; 
			}
			
			$wh[$subject] = $this->get( 'all', $subject );
	
			foreach( $_POST['app_wh'][$subject] as $who=>$coded_val ) {

				$vals = str_split( $coded_val );

				// Combine vals with wh
				$new_vals = array();
				foreach ( $vals as $key=>$val ) {
					$wh_val = isset( $wh[$subject][$key] ) ? $wh[$subject][$key] : '';
					if ( $val )
						$new_vals[$key]	= $this->add_item( $wh_val, $who );
					else if ( $wh_val )
						$new_vals[$key]	= $this->remove_item( $wh_val, $who );
					else 
						$new_vals[$key] = '';
				}
				
				$wh[$subject] = $new_vals;
				if ( 'worker' == $subject )
					$updated_workers[]= $who;
			}
			
			ksort( $new_vals );
			
			if ( $this->pack_n_save( $new_vals, $table ) ) {
				wpb_flush_cache();
				$result = true;
			}
		}

		if ( $result ) {
			wpb_flush_cache();
			// TODO: Optimize during configuration - remove items in services too, but if capacity is not increased
			if ( $this->a->get_nof_workers() && !empty($updated_workers) && 'no' != $options["service_wh_covers"] ) {
				$wh_services = $this->get( 'all','service' );
				foreach ( $updated_workers as $w_id ) {
					$serv = $this->a->get_services_by_worker( $w_id );
					if ( empty( $serv ) )
						continue;
					
					$serv_ids = implode( ',', array_keys( $serv ) );
					$wh_worker = $this->get( $w_id );
					foreach( $wh_worker as $key=>$val ) {
						if ( !$val )
							continue;
						
						$wh_services_val = isset( $wh_services[$key] ) ? $wh_services[$key] : '';
						$wh_services[$key]= $this->add_item( $wh_services_val, $serv_ids );
					}
				}
				$this->pack_n_save( $wh_services, $this->a->wh_s_table );
				wpb_flush_cache();
			}
		}

		if ( $result || $result2 ) {		
			wpb_notice( 'saved' );
		}
	}
	
	/**
     * Add a new value to a comma delimited string
	 * @param $wh_val	string		Comma delimited string
	 * @param $who		integer		ID of subject
	 * @return string				Comma delimited string
     */
	function add_item( $wh_val, $who ) {
		return wpb_sanitize_commas( $wh_val .','. $who, true );
	}
	
	/**
     * Remove a value from a comma delimited string
	 * @param $wh_val	string		Comma delimited string
	 * @param $who		integer		Subject Id to be removed
	 * @return string				Comma delimited string
     */
	function remove_item( $wh_val, $who ) {
		$arr = explode(',',$wh_val);
		$arr = array_flip($arr);
		unset( $arr[$who] );
		return implode( ',', array_flip( $arr ) );
	}

	/**
     * Pack a 2016-element $new_vals array into 288 and actually save
	 * Save data in an easily usable way (not easily readable, though)
	 * @param $new_vals		array	Encoded working hours array
	 * @param $table		string	mySQL table name
	 * @return integer|bool		Insert query result
     */
	 function pack_n_save( $new_vals, $table ) {
		$location = $this->get_location();

		$col_vals = array();
		foreach ( range(0,6) as $day ) {
			for ( $k=0; $k<288; $k++ ) {
				$no = $day*288 +$k;
				$new_val = isset( $new_vals[$no] ) ? $new_vals[$no] : '';
				$col_val = isset( $col_vals[$k] ) ? $col_vals[$k] : '';
				$col_vals[$k] = $col_val . $new_val .'|';
			}
		}
		unset( $new_vals );
		ksort( $col_vals );

		// Prepare query
		$columns = $values = $update = '';
		foreach ( $col_vals as $key=>$val ) {
			if ( !str_replace('|','',$val ) )
				$val = '';
			$columns .= '`c'.( $key ).'`,';
			$values .= "'".$val."',";
			$update .= "`c".$key."`='".$val."',";
		}
		unset( $col_vals );

		$columns = rtrim( $columns, ',' );
		$values = rtrim( $values, ',' );
		$update = rtrim( $update, ',');
		
		return $this->a->db->query( 
			"INSERT INTO ". $table . " (ID,$columns) VALUES ($location,$values)
			ON DUPLICATE KEY UPDATE $update 
			");		
	}
		
}

	BASE('WH')->add_hooks();
}


