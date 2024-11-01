<?php
/**
 * Booking functions
 *
 * Functions about booking, services, workers and some other useful stuff
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Generate a dropdown menu of countries with country code or country name as value, using an intelligent guess
 * @param $value:	string	Optional selected country value (Code or full name that should match to WpBConstant::countries()
 * Since 3.0
 */
if ( !function_exists('wpb_countries') ) {
	function wpb_countries( $value = '' ) {
		include_once( WPB_PLUGIN_DIR . '/includes/constant-data.php' );
		$countries = WpBConstant::countries();
		if ( !is_array( $countries ) || empty( $countries ) )
			return BASE()->debug_text( __('Check Countries array','wp-base') );
		
		$c  = '<select name="app_country">'. "\n";
		$c .= '<option value="">'. __('Select country','wp-base')."</option>\n";
		if ( is_string( key( $countries ) ) ) {		
			foreach ($countries as $code=>$country ) {
				$c .= '<option value="'.$code.'" '.selected($code,$value,false).'>'. esc_html( $country )."</option>\n";
			}
		}
		else {
			foreach ($countries as $country ) {
				$c .= '<option value="'.esc_html( $country ).'" '.selected($country,$value,false).'>'. esc_html( $country )."</option>\n";
			}
		}
		$c .= "</select>\n";
		
		return $c;
	}
}

/**
 * Check if a time slot free to be booked
 * @param $args	array	Time slot parameters (All except start optional):
 * 		start 		integer/string	Start of the slot as timestamp or date time in any standard format, preferably Y-m-d H:i:s
 * 		end			integer/string	End of the slot as time stamp or date time. If left empty, end time is calculated from duration of service
 * 		location	integer			ID of location. If left empty or related var does not exist, read from current values
 * 		service		integer			ID of service. If left empty or related var does not exist, read from current values
 * 		worker		integer			ID of service provider. If left empty or related var does not exist, read from current values
 * @since 3.0
 * @return bool		true is slot free, false if not free
 */
function wpb_is_free( $args ) {
	return !wpb_why_not_free( $args );
}

/**
 * Return a code pointing why a time slot is not available to be booked
 * @param $args	array	Time slot parameters (All except start optional):
 * 		start 		integer/string	Start of the slot as timestamp or date time in any standard format, preferably Y-m-d H:i:s
 * 		end			integer/string	End of the slot as time stamp or date time. If left empty, end time is calculated from duration of service
 * 		location	integer			ID of location. If left empty or related var does not exist, read from current values
 * 		service		integer			ID of service. If left empty or related var does not exist, read from current values
 * 		worker		integer			ID of service provider. If left empty or related var does not exist, read from current values
 * @since 3.0
 * @return string (see wpb_code2reason function for return value) if slot is not available, false if slot is free
 */
function wpb_why_not_free( $args ) {
	$booking = new WpB_Booking();
	$args = $booking->normalize_args( $args );
	
	$calendar = new WpBCalendar( $args['location'], $args['service'], $args['worker'] );
	$code = $calendar->slot( $args['start'], $args['end'] )->why_not_free();
	
	return wpb_code2reason( $code ); # e.g. 'busy'
}

/**
 * Return a booking object
 * @param $booking_id	integer		Unique booking ID
 * @param $prime_cache	bool		Optional. Also save 100 latest bookings into cache. Graceful to mysql server in case you are creating a list of bookings
 * @since 3.0
 * @return object	Properties:
 *		ID				integer		Unique booking ID
 *		parent_id		integer		Parent booking ID, if there is one
 *		created			string		Creation time of the booking in mysql format (Y-m-d H:i:s)
 *		user			integer		WordPress user ID of the client booking belongs to. For not logged in clients, zero
 *		location		integer		Location ID of the booking
 *		service			integer		Service ID of the booking
 *		worker			integer		Service Provider ID assigned to the booking. WP user ID of the SP
 *		status			string		Status of the booking. Common values: pending, confirmed, paid, running, cart, removed, test, waiting
 *		start			string		Start time of the booking in mysql format (Y-m-d H:i:s)
 *		end				string		End time of the booking in mysql format (Y-m-d H:i:s)
 *		seats			integer		Number of seats/pax booking occupies. 1 for single bookings. May be greater than 1 for Group Bookings
 *		gcal_ID			string		Unique gcal ID created by Google Calendar
 *		gcal_updated	string		Latest update time of the Google Calendar entry for the booking
 *		price			string		Price of the booking including decimal sign (.), without currency symbol
 *		deposit			string		Security deposit for the booking including decimal sign (.), without currency symbol
 *		payment_method	string		Name of the payment method for the booking. Common values: manual-payments, paypal-standard, stripe, paymill
 */
function wpb_get_booking( $booking_id, $prime_cache = false ) {
	return new WpB_Booking( $booking_id, $prime_cache );
}

/**
 * Add a booking
 * Does not check availability of the slot. If required, use wpb_is_free before calling this
 * @param $arg	array	Booking parameters (All optional):
 *		parent_id		integer			Parent ID of the booking if this is a child booking (0 if left empty)
 *		created			integer/string	Creation date/time as timestamp or date time in any standard format, preferably Y-m-d H:i:s. If left empty, current date/time
 *		user			integer			Client ID (Wordpress user ID of the client)
 *		location		integer			Location ID. It must exist in the DB
 *		service			integer			Service ID. It must exist in the DB
 *		worker			integer			Worker ID (WordPress user ID of the service provider)
 *		status			integer			Status of the booking. Must be an existing status (pending, confirmed, paid, running, cart, removed). If left empty, "confirmed"
 *		start			integer/string	Start of the slot as timestamp or date time in any standard format, preferably Y-m-d H:i:s. If not set, first free time will be used
 *		end				integer/string	End of the slot as time stamp or date time. If left empty, end time is calculated from duration of service
 *		price			string			Price of the booking. Comma and/or point allowed, e.g. $1.234,56
 *		deposit			string			Security deposit for the booking. Comma and/or point allowed, e.g. $1.234,56
 *		payment_method	string			Name of the payment method for the booking. Common values: manual-payments, paypal-standard, stripe, paymill
 * @since 3.0
 * @return integer (Booking ID) on success, false on fail. Reason of failure can be read from string: BASE()->db->last_error
 */
function wpb_add_booking( $args = array() ) {
	$booking = new WpB_Booking;
	return $booking->add( $args );
}

/**
 * Edit a booking
 * @param $arg	array	Booking parameters (All optional except ID):
 *		ID	integer			Booking ID to be edited
 *		For the rest, see wpb_add_booking. 'created' only updated if explicitly set
 * @since 3.0
 * @return bool			True on success, false on fail. Reason of failure can be read from string: BASE()->db->last_error
 */
function wpb_edit_booking( $args ) {
	$args['ID'] = isset( $args['ID'] ) ? $args['ID'] : 0;
	return wpb_add_booking( $args );
}


/**
 * Delete one or more bookings 
 * @param $ids 			array|string	Array or comma delimited string of booking IDs to be deleted
 * @param $status		string			Optional status of the bookings to delete
 * @since 3.0
 * @return bool
 */		
function wpb_delete_booking( $ids, $status = '' ) {
	$ids = is_array( $ids ) ? $ids : wpb_explode( wpb_sanitize_commas( $ids ), ',' );
	if ( empty( $ids ) )
		return false;
	
	$q = '';
	$result = false;
	
	foreach ( $ids as $app_id ) {
		$q .= " ID=". esc_sql($app_id). " OR";
	}
	
	$q = rtrim( $q, " OR" );
	
	if ( $status && array_key_exists( $status, BASE()->get_statuses() ) ) {
		$q = "(".$q.") AND status='". $status. "'";
	}
	
	$result = BASE()->db->query( "DELETE FROM " .BASE()->app_table. " WHERE (".$q.") " );
	
	if ( $result ) {
		BASE()->db->query( BASE()->db->prepare("DELETE FROM " .BASE()->meta_table. " WHERE (%s) AND meta_type='app'", str_replace( 'ID','object_id', $q )) );
		return true;
	}
	
	return $result;
}

/**
 * Change status of one or more bookings
 * @param $ids			array|string 	Array or comma delimited string of booking IDs
 * @param $new_status	string			New status. It should be a valid status (pending, paid, confirmed, completed, etc)
 * @param $old_status	string			Optional old status to change from. It should be a valid status
 * @since 3.0
 * @return mix			false|integer	Number of records changed on success, false on failure		
 */
function wpb_change_status( $ids, $new_status, $old_status = '' ) {
	$ids = is_array( $ids ) ? $ids : wpb_explode( wpb_sanitize_commas( $ids ), ',' );
	if ( empty( $ids ) )
		return false;
	
	$q = '';
	$result = false;
	
	foreach ( $ids as $app_id ) {
		$q .= " ID=". esc_sql($app_id). " OR";
	}
	
	$q = rtrim( $q, " OR" );
	
	if ( $old_status && array_key_exists( $old_status, BASE()->get_statuses() ) )
		$q = "(".$q.") AND status='". $old_status. "'";
	
	if ( array_key_exists( $new_status, BASE()->get_statuses() ) )
		$result = BASE()->db->query( "UPDATE " . BASE()->app_table . " SET status='".$new_status."' WHERE " . $q . " " );
	
	return $result;
}

/**
 * Return a service object
 * @param	$ID						ID of the service to be retrieved
 * @since 3.0
 * @return object	Properties:
 *		internal	integer			0=Not internal, 1=Internal
 *		sort_order	integer			Order of the service in the list.
 *		name		string			Name of the service.
 *		capacity	integer			Capacity of the service
 *		duration	integer			Duration of the service in minutes
 *		padding		integer			Padding before in minutes
 *		break_time	integer			Padding after in minutes
 *		locations	string			IDs of locations for which this service is available, : delimited, e.g. :1:3:4:
 *		categories	string			IDs of categories this service have, : delimited, e.g. :1:3:4:
 *		price		string			Price of service.
 *		deposit		string			Deposit for the service.
 *		page		integer			ID of the page/post that will be used as description page.
 *
 */
function wpb_get_service( $ID ) {
	return BASE()->get_service( $ID );
}

/**
 * Add a service to the DB
 * @param $arg	array	Parameters (All optional):
 *		internal	bool			Whether service is internal
 *		sort_order	integer			Order of the service in the list. If left empty, service will be at added to the end
 *		name		string			Name of the service. Defaults to custom text for 'service'
 *		capacity	integer			Capacity of the service
 *		duration	integer			Duration of the service in minutes
 *		padding		integer			Padding before in minutes
 *		break_time	integer			Padding after in minutes
 *		locations	array/string	An array or comma delimited string with IDs of locations for which this service is available. Location must be existing
 *		categories	array/string	An array or comma delimited string with IDs of categories for this service. Existence of category is not checked
 *		price		string			Price of service. Comma and/or point allowed, e.g. $1.234,56
 *		deposit		string			Deposit for the service. Comma and/or point allowed, e.g. $1.234,56
 *		page		integer			ID of the page/post that will be used as description page.
 *
 * @since 3.0
 * @return service ID on success, false on fail. Reason of failure can be read from string: BASE()->db->last_error
 */
function wpb_add_service( $args = array() ) {
	
	$r = false;	
	$locations_included	= array();
	$locations			= isset( $args['locations'] ) ? $args['locations'] : array();
	$l_to_check			= is_array( $locations ) ? $locations : wpb_explode( wpb_sanitize_commas( $locations ), ',' );
	
	foreach ( (array)$l_to_check as $loc ) {
		if ( BASE()->location_exists( $loc ) )
			$locations_included[] = $loc;
	}
	
	$data = array( 
			'internal'			=> !empty( $args["internal"] ) ? 1 : 0,
			'name'				=> !empty( $args['name'] ) ? $args['name'] : BASE()->get_text('service'),											
			'capacity'			=> !empty( $args["capacity"] ) ? preg_replace("/[^0-9]/", "", $args["capacity"]) : '',									
			'duration'			=> !empty( $args["duration"] ) ? preg_replace("/[^0-9]/", "", $args["duration"]) : BASE()->get_min_time(),
			'padding'			=> !empty( $args["padding"] ) ? preg_replace("/[^0-9]/", "", $args["padding"]) : '',
			'break_time'		=> !empty( $args["break_time"] ) ? preg_replace("/[^0-9]/", "", $args["break_time"]) :'',
			'locations'			=> !empty( $locations_included ) ? wpb_implode( $locations_included ) : '',
			'categories'		=> !empty( $args['categories'] ) && is_array($args['categories']) ? wpb_implode( wpb_sanitize_commas($args['categories']) ) : '',
			'price'				=> !empty( $args['price'] ) ? wpb_sanitize_price( $args['price'] ) : 0,
			'deposit'			=> !empty( $args["deposit"] ) ? wpb_sanitize_price( $args["deposit"] ) : '', 
			'page'				=> !empty( $args['page'] ) ? (int)$args['page'] : '',
		);
	
	if ( !empty( $args['sort_order'] ) )
		$data['sort_order'] = intval($args['sort_order']);
	else if ( empty( $args['ID'] ) ) {
		$max_sort_order = BASE()->db->get_var( "SELECT MAX(sort_order) FROM " . BASE()->services_table );
		$data['sort_order'] = intval($max_sort_order) + 1;
	}
	
	if ( !empty( $args['ID'] ) ) {
		if ( BASE()->db->update( BASE()->services_table, $data, array( 'ID'=>$args['ID'] ) ) )
			$r = $args['ID'];
	}
	else if ( BASE()->db->insert( BASE()->services_table, $data ) ) {
		$r = BASE()->db->insert_id;
		BASE('WH')->add_default( $r, 'service' );
	}
	
	return $r;
}

/**
 * Edit a service
 * @param $arg	array	Service parameters (All optional except ID):
 *		ID	integer			Service ID to be edited
 *		For the rest, see wpb_add_service. sort_order only updated if explicitly set
 * @since 3.0
 * @return bool			True on success, false on fail. Reason of failure can be read from string: BASE()->db->last_error
 */
function wpb_edit_service( $args ) {
	$args['ID'] = isset( $args['ID'] ) ? $args['ID'] : 0;
	return wpb_add_service( $args );
}

/**
 * Delete a service and related metas and Work Hours
 * @param $ID	integer		Service to be deleted
 * @since 3.0
 * @return		bool		True on success, false on fail. Reason of failure can be read from string: BASE()->db->last_error
 */
function wpb_delete_service( $ID ) {
	$r = BASE()->db->query( BASE()->db->prepare("DELETE FROM " .BASE()->services_table. " WHERE ID=%d LIMIT 1", $ID) );
	if ( $r ) {
		BASE()->db->query( BASE()->db->prepare("DELETE FROM " .BASE()->meta_table. " WHERE object_id=%d AND meta_type='service'", $ID) );
		BASE('WH')->remove( $ID, 'service' );
	}

	return $r;
}

/**
 * Return total duration (minutes) of a service given its ID
 * @param $ID			integer		Service whose duration will be checked
 * @param $slot_start	integer		Optional timestamp of slot for which service duration will be returned. Significant if duration is not constant
 * @return integer
 * @since 3.0
 */	
function wpb_get_duration( $ID, $slot_start = null ) {
	$s = wpb_get_service( $ID );
	$duration = isset( $s->duration ) && $s->duration ? $s->duration : 0; 
	return apply_filters( 'app_get_duration', intval( $duration ), $ID, $slot_start );
}

/**
 * Returns service provider (worker) object
 * @param	$ID						ID of the worker to be retrieved = WP User ID
 * @since 3.0
 * @return object	Properties:
 *		sort_order			integer			Order of the SP in the list
 *		name				string			Display name of the SP
 *		services_provided	string			IDs of services this SP is providing, : delimited, e.g. :1:3:4:
 *		price				string			Additional price of the SP
 *		page				integer			ID of the page/post of bio page of SP
 *		dummy				integer			0=Not dummy, 1=Dummy
 */
function wpb_get_worker( $ID ) {
	return BASE()->get_worker( $ID );
}

/**
 * Adds a service provider (Assigns a WP user as Service Provider)
 * Working hours will be taken from that of Business Representative
 * Person to be assigned as a service provider must be existing as a WP user
 * @param $arg	array	Parameters (All optional):
 *		ID					integer			User WP ID. If left empty, current user will be assigned.
 *		sort_order			integer			Order of the SP in the list. If left empty, SP will be at added to the end
 *		name				string			Display name. If left empty, it will created from display name or user_login
 *		services_provided	array/string	An array or comma delimited string with IDs of services provided by SP. If left empty, first service in list will be used
 *		price				string			Additional price of worker. Comma and/or point allowed, e.g. $1.234,56
 *		page				integer			ID of the page/post that will be used as bio page.
 *
 * @since 3.0
 * @return true on success, false on fail. Reason of failure can be read from string: BASE()->db->last_error
 */
function wpb_add_worker( $args = array() ) {
	$user_id = !empty( $args['ID'] ) ? $args['ID'] : ( function_exists('get_current_user_id') ? get_current_user_id() : false );
	$user = get_user_by( 'ID', $user_id );
	if ( empty( $user->user_login ) ) {
		BASE()->db->last_error = __('User does not exist','wp-base');
		return false;
	}
	
	$r = false;
	$services_provided = array();
	$services = isset( $args['services_provided'] ) ? $args['services_provided'] : array();
	$s_to_check = is_array( $services ) ? $services : wpb_explode( wpb_sanitize_commas( $services ), ',' );
	foreach ( (array)$s_to_check as $s ) {
		if ( BASE()->service_exists( $s ) )
			$services_provided[] = $s;
	}
	
	if ( empty( $services_provided ) )
		$services_provided[] = BASE()->get_first_service_id();
	
	$data = array( 
			'ID'				=> $user_id,
			'name'				=> !empty($user->display_name) ? $user->display_name : $user->user_login,											
			'services_provided'	=> !empty( $services_provided ) ? wpb_implode( $services_provided ) : '',
			'price'				=> !empty( $args['price'] ) ? wpb_sanitize_price( $args['price'] ) : 0,
			'page'				=> !empty( $args['page'] ) ? (int)$args['page'] : '',
			);
	
	if ( !empty( $args['sort_order'] ) )
		$data['sort_order'] = intval( $args['sort_order'] );
	else if ( !BASE()->worker_exists( $user_id ) ) {
		$max_sort_order = BASE()->db->get_var( "SELECT MAX(sort_order) FROM " . BASE()->workers_table );
		$data['sort_order'] = intval($max_sort_order) + 1;
	}
	
	if ( BASE()->worker_exists( $user_id ) ) {
		if ( BASE()->db->update( BASE()->workers_table, $data, array( 'ID'=>$user_id ) ) )
			$r = $user_id;
	}
	else if ( BASE()->db->insert( BASE()->workers_table, $data ) ) {
		BASE('WH')->add_default( $user_id, 'worker' );
		$r = $user_id;
	}
	
	return $r;
}

/**
 * Edits a service provider
 * @param $arg	array	Parameters:
 *		See wpb_add_worker for description. sort_order only updated if explicitly set
 * @since 3.0
 * @return bool		true on success, false on fail. Reason of failure can be read from string: BASE()->db->last_error
 */
function wpb_edit_worker( $args ) {
	return wpb_add_worker( $args );
}

/**
 * Delete a service provider and related metas  and Work Hours (Unassign a WP user from SP)
 * WP user records are not deleted. 
 * @param $ID	integer		Service provider to be deleted
 * @since 3.0
 * @return		bool		True on success, false on fail. Reason of failure can be read from string: BASE()->db->last_error
 */
function wpb_delete_worker( $ID ) {
	$r = BASE()->db->query( BASE()->db->prepare("DELETE FROM " .BASE()->workers_table. " WHERE ID=%d LIMIT 1", $ID) );
	if ( $r ) {
		BASE()->db->query( BASE()->db->prepare("DELETE FROM " .BASE()->meta_table. " WHERE object_id=%d AND meta_type='worker'", $ID) );
		if ( $ID != BASE()->get_default_worker_id() )
			BASE('WH')->remove( $ID, 'worker' );
	}

	return $r;
}

/**
 * Create a WP user from an array of fields ($data['email'], $data['name'], etc ).
 * If user already exists, returns his ID
 * Also save user meta
 * @param $data			array		User info fields. Keys of array are:
 *		name			string		Either name, first_name or last_name required
 *		first_name		string		Ditto
 *		last_name		string		Ditto
 *		email			string		Should be valid email
 *		phone			string
 *		password		string		If left empty, a password will be automatically created
 *		address			string
 *		city			string
 *		zip				string		Postcode
 *		state			string		For future
 *		country			string		For future
 * 
 * @param $notify_admin	bool		Whether notify admin. Optional. 
 * @param $notify_user	bool|null	Whether notify user (If null, "auto_register_client_notify" setting is in effect. If false, user not notified (default). If true, user is notified) 
 * @since 3.0
 * @return mix		false|integer	User ID on success, false on failure
 */
function wpb_create_user( $data, $notify_admin = false, $notify_user = false ) {
	return BASE('User')->create_user( $data, $notify_admin, $notify_user );
}

