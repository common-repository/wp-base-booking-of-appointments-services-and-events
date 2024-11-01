<?php
/**
 * WPB Booking
 *
 * Handles booking functions
 *
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpB_Booking' ) ) {

class WpB_Booking {
	
	# Unique booking ID
	# integer		
 	public $ID;

	# Parent booking ID, if there is one
	# integer		
	public $parent_id;
	
	# Creation time of the booking in mysql format (Y-m-d H:i:s)
	# string		
	public $created;
	
	# WordPress user ID of the client booking belongs to. For not logged in clients, zero
	# integer		
	public $user;
	
	# Location ID of the booking
	# integer		
	public $location;

	# Service ID of the booking
	# integer		
	public $service;

	# Service Provider ID assigned to the booking. WP user ID of the SP
	# integer		
	public $worker;

	# Status of the booking. Common values: pending, confirmed, paid, running, cart, removed, test, waiting
	# string		
	public $status;

	# Start time of the booking in mysql format (Y-m-d H:i:s)
	# string		
	public $start;

	# End time of the booking in mysql format (Y-m-d H:i:s)
	# string		
	public $end;

	# Number of seats/pax booking occupies. 1 for single bookings. May be greater than 1 for Group Bookings
	# integer		
	public $seats;			

	# Unique gcal ID created by Google Calendar
	# string		
	public $gcal_ID;

	# Latest update time of the Google Calendar entry for the booking
	# string		
	public $gcal_updated;	

	# Price of the booking including decimal sign (.), without currency symbol	
	# string		
	public $price;

	# Security deposit for the booking including decimal sign (.), without currency symbol
	# string		
	public $deposit;

	# Name of the payment method for the booking. 
	# Common values: manual-payments, paypal-standard, stripe, paymill
	# string				
	public $payment_method;	
 
	# WP BASE Core instance 
	protected $a;
	
	/**
	 * Constructor
	 * @param	$ID				integer				Booking ID
	 * @param	$prime_cache	bool				Also cache last 100 bookings
	 */
	public function __construct( $ID = null, $prime_cache = false ) {
		$this->a = BASE();

		if ( $ID && $app = $this->a->get_app( $ID, $prime_cache ) ) {
			foreach ( get_object_vars( $app ) as $key => $value ) {
				$this->$key = $value;
			}
			
		}
	}
	
	public function get_ID(){
		return $this->ID;
	}
	
	/**
	 * Function to normalize args
	 * @param args		array
	 * @since 3.0
	 * @return array
	 */
	public function normalize_args( $args ) {
		$args['location']	= isset( $args['location'] ) && $this->a->location_exists( $args['location'] ) ? $args['location'] : $this->a->read_location_id(); 
		$args['service']	= isset( $args['service'] ) && $this->a->service_exists( $args['service'] ) ? $args['service'] : $this->a->read_service_id();
		$args['worker']		= isset( $args['worker'] ) && $this->a->worker_exists( $args['worker'] ) ? $args['worker'] : $this->a->read_worker_id();

		$args['start'] = !empty( $args['start'] ) ? wpb_strtotime( $args['start'] ) : $this->a->_time; 
		
		if ( empty( $args['end'] )  )
			$args['end'] = $args['start'] + wpb_get_duration( $args['service'] )*60;
		else 
			$args['end'] = wpb_strtotime( $args['end'] );

		return $args;	
	}

	/**
	 * Add a booking
	 * Does not check availability of the slot. If required, use wpb_is_free before calling this
	 * @param $args	array	Booking parameters (All optional) see wpb_add_booking
	 * @since 3.0
	 * @return integer (Booking ID) on success, false on fail. Reason of failure can be read from string: BASE()->db->last_error
	 */
	 public function add( $args = array() ) {
		$args['start'] = !empty( $args['start'] ) ? wpb_strtotime( $args['start'] ) : $this->a->find_first_free_slot( $args['location'], $args['service'], $args['worker'] ); 
		
		$args = $this->normalize_args( $args );
		
		$args['parent_id']	= isset( $args['parent_id'] ) ? $args['parent_id'] : 0;
		
		$created = !empty( $args['created'] ) ? wpb_strtotime( $args['created'] ) : ( isset( $args['ID'] ) ? '' : $this->a->_time );
		if ( $created )
			$args['created'] = date( 'Y-m-d H:i:s', $created );
		
		$args['start']		= date( 'Y-m-d H:i:s', $args['start'] );
		$args['end']		= date( 'Y-m-d H:i:s', $args['end'] );
		$args['status']		= !empty( $args['status'] ) && array_key_exists( $args['status'], $this->a->get_statuses() ) ? $args['status'] : 'confirmed';

		$args['price']		= isset( $args['price'] ) ? wpb_sanitize_price( $args['price'] ) : 0;
		$args['deposit']	= isset( $args['deposit'] ) ? wpb_sanitize_price( $args['deposit'] ) : '';
		
		if ( isset( $args['ID'] ) )
			return $this->a->db->update( $this->a->app_table, $args, array( 'ID' => $args['ID'] ) );
		else
			return $this->a->db->insert( $this->a->app_table, $args );		
	}
	

}
}