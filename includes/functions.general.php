<?php
/**
 * General functions
 *
 * Functions about time, data handling and other stuff
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;
 
 /**
 * Add meta data field to an appt.
 *
 * @since 3.0
 *
 * @param int    $app_id	 App ID.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 * @param bool   $unique     Optional. Whether the same key should not be added.
 *                           Default true.
 * @return int|false Meta ID on success, false on failure.
 */
function wpb_add_app_meta( $app_id, $meta_key, $meta_value, $unique = true ) {
	return WpBMeta::add_metadata( 'app', $app_id, $meta_key, $meta_value, $unique ); 
}

/**
 * Retrieve app meta field for a booking.
 *
 * @since 3.0
 *
 * @param int    $post_id Post ID.
 * @param string $key     Optional. The meta key to retrieve. By default, returns
 *                        data for all keys. Default empty.
 * @param bool   $single  Optional. Whether to return a single value.
 * @return mixed Will be an array if $single is false. Will be value of meta data
 *               field if $single is true.
 */
function wpb_get_app_meta( $app_id, $key = '', $single = true ) {
        return WpBMeta::get_metadata('app', $app_id, $key, $single);
}

/**
 * Update app meta field based on app ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and app ID.
 *
 * If the meta field for the post does not exist, it will be added.
 *
 * @since 3.0
 *
 * @param int    $app_id     Post ID.
 * @param string $meta_key   Metadata key.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 * @param mixed  $prev_value Optional. Previous value to check before removing.
 *                           Default empty.
 * @return int|bool Meta ID if the key didn't exist, true on successful update,
 *                  false on failure.
 */
function wpb_update_app_meta( $app_id, $meta_key, $meta_value, $prev_value = '' ) {
	return WpBMeta::update_metadata( 'app', $app_id, $meta_key, $meta_value, $prev_value );	
}

/**
 * Update the metadata cache for the given app IDs.
 * Useful to update cache for several ID before preparing a list, e.g. [app_list]
 *
 * @since 3.0
 *
 * @param int|array $object_ids Array or comma delimited list of object IDs to update cache for
 * @return array|false Metadata cache for the specified objects, or false on failure.
 */
function wpb_update_app_meta_cache($object_ids) {
	return WpBMeta::update_meta_cache( 'app', $object_ids );	
}

/**
 * Remove metadata matching criteria from an appt record.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 *
 * @since 3.0
 *
 * @param int    $app_id 	 Booking ID.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Optional. Metadata value. Must be serializable if
 *                           non-scalar. Default empty.
 * @return bool True on success, false on failure.
 */
function wpb_delete_app_meta( $app_id, $meta_key, $meta_value = '' ) {
	return WpBMeta::delete_metadata('app', $app_id, $meta_key, $meta_value);
}

 /**
 * Add meta data field to a service
 *
 * @since 3.0
 *
 * @param int    $service_id	 Service ID.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 * @param bool   $unique     Optional. Whether the same key should not be added.
 *                           Default true.
 * @return int|false Meta ID on success, false on failure.
 */
function wpb_add_service_meta( $service_id, $meta_key, $meta_value, $unique = true ) {
	return WpBMeta::add_metadata( 'service', $service_id, $meta_key, $meta_value, $unique ); 
}

/**
 * Retrieve service meta field for a booking.
 *
 * @since 3.0
 *
 * @param int    $service_id	 Service ID.
 * @param string $key     Optional. The meta key to retrieve. By default, returns
 *                        data for all keys. Default empty.
 * @param bool   $single  Optional. Whether to return a single value.
 * @return mixed Will be an array if $single is false. Will be value of meta data
 *               field if $single is true.
 */
function wpb_get_service_meta( $service_id, $key = '', $single = true ) {
	return WpBMeta::get_metadata('service', $service_id, $key, $single);
}

/**
 * Update service meta field based on service ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and service ID.
 *
 * If the meta field for the post does not exist, it will be added.
 *
 * @since 3.0
 *
 * @param int    $service_id     service ID.
 * @param string $meta_key   Metadata key.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 * @param mixed  $prev_value Optional. Previous value to check before removing.
 *                           Default empty.
 * @return int|bool Meta ID if the key didn't exist, true on successful update,
 *                  false on failure.
 */
function wpb_update_service_meta( $service_id, $meta_key, $meta_value, $prev_value = '' ) {
	return WpBMeta::update_metadata( 'service', $service_id, $meta_key, $meta_value, $prev_value );	
}

/**
 * Remove metadata matching criteria from a service record.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 *
 * @since 3.0
 *
 * @param int    $service_id Service ID.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Optional. Metadata value. Must be serializable if
 *                           non-scalar. Default empty.
 * @return bool True on success, false on failure.
 */
function wpb_delete_service_meta( $service_id, $meta_key, $meta_value = '' ) {
	return WpBMeta::delete_metadata('service', $service_id, $meta_key, $meta_value);
}

 /**
 * Add meta data field to a location
 *
 * @since 3.0
 *
 * @param int    $loc_id	 Location ID.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 * @param bool   $unique     Optional. Whether the same key should not be added.
 *                           Default true.
 * @return int|false Meta ID on success, false on failure.
 */
function wpb_add_location_meta( $loc_id, $meta_key, $meta_value, $unique = true ) {
	return WpBMeta::add_metadata( 'location', $loc_id, $meta_key, $meta_value, $unique ); 
}

/**
 * Retrieve location meta field for a booking.
 *
 * @since 3.0
 *
 * @param int    $loc_id  Location ID.
 * @param string $key     Optional. The meta key to retrieve. By default, returns
 *                        data for all keys. Default empty.
 * @param bool   $single  Optional. Whether to return a single value.
 * @return mixed Will be an array if $single is false. Will be value of meta data
 *               field if $single is true.
 */
function wpb_get_location_meta( $loc_id, $key = '', $single = true ) {
	return WpBMeta::get_metadata('location', $loc_id, $key, $single);
}

/**
 * Update location meta field based on location ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and service ID.
 *
 * If the meta field for the post does not exist, it will be added.
 *
 * @since 3.0
 *
 * @param int    $service_id     service ID.
 * @param string $meta_key   Metadata key.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 * @param mixed  $prev_value Optional. Previous value to check before removing.
 *                           Default empty.
 * @return int|bool Meta ID if the key didn't exist, true on successful update,
 *                  false on failure.
 */
function wpb_update_location_meta( $loc_id, $meta_key, $meta_value, $prev_value = '' ) {
	return WpBMeta::update_metadata( 'location', $loc_id, $meta_key, $meta_value, $prev_value );	
}

/**
 * Remove metadata matching criteria from a location record.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 *
 * @since 3.0
 *
 * @param int    $loc_id     Location ID.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Optional. Metadata value. Must be serializable if
 *                           non-scalar. Default empty.
 * @return bool True on success, false on failure.
 */
function wpb_delete_location_meta( $loc_id, $meta_key, $meta_value = '' ) {
	return WpBMeta::delete_metadata('location', $loc_id, $meta_key, $meta_value);
}

/**
 * Remove all metadata of a location record.
 *
 * @since 3.0
 *
 * @param int    $loc_id     Location ID.
 * @return bool True on success, false on failure.
 */
function wpb_delete_location_metadata( $loc_id ) {
	return WpBMeta::delete_metadata_by_oid( 'location', $loc_id );
}

/**
 * Get salt. If it does not exist, create it
 * @since 2.0
 */	
function wpb_get_salt(){
	# Create a salt, if it doesn't exist from the previous installation
	if ( !$salt = get_option( "appointments_salt" ) ) {
		$salt = mt_rand();
		add_option( "appointments_salt", $salt ); # Save it to be used until it is cleared manually
	}
	return $salt;
}

/**
 * Limit length of a text
 * @param limit: Character limit for text
 * @since 2.0
 * @return string
 */	
function wpb_cut( $text, $limit = 22, $word = false ) {
	$limit = apply_filters( 'app_chr_limit', $limit );
	$full_text = $text;
	if ( $word )
		$text= wp_trim_words( $text, $limit, '...' );
	else if ( $limit && strlen($text) > $limit + 3 )
			$text = mb_substr( $text, 0, $limit, 'UTF-8' ) . '...';

	if ( strlen( $full_text ) > strlen( $text ) )
		$text = '<abbr title="'.$full_text.'">'. $text . '</abbr>';
	
	return $text;
}

/**
 * Fixes modulus operator of php against negative results
 * @since 2.0
 */
function wpb_mod( $a, $n ) {
	return ($a % $n) + ($a < 0 ? $n : 0);
}

/**
 * Packs an array into a string with : as glue
 * @param $sep	string	Separator
 * @return string
 */

function wpb_implode( $input, $sep = ':' ) {
	if ( !is_array( $input ) || empty( $input ) )
		return false;
	return $sep. implode( $sep, array_filter( array_unique($input) ) ) . $sep;
}
	
/**
 * Packs a string into an array assuming : as glue
 * @param $sep	string	Separator
 * @return array
 */	
function wpb_explode( $input, $sep = ':' ){
	if ( !is_string( $input ) )
		return false;
	return array_filter( array_unique( explode( $sep , ltrim( $input , $sep ) ) ) );
}

/**
 * Remove spaces and make comma delimited options unique 
 * @return string
 */
function wpb_sanitize_commas( $options, $sort = false ) {
	$arr = explode( ',', $options );
	$arr = array_map( 'trim', $arr );
	$arr = array_unique( $arr );
	if ( 'reverse' == $sort )
		rsort( $arr );
	else if ( $sort )
		sort( $arr );
	return implode( ',', array_filter( $arr ) );
}

/**
* Get timestamp in seconds to measure execution time
* @since 2.0
* @return float
*/	
function wpb_microtime( ) {
	list( $usec, $sec ) = explode( " ", microtime() );
	return ( (float)$usec + (float)$sec );
}

/**
 * Converts number of seconds to hours:mins acc to the WP time format setting
 * @param $show_2400	bool	If this is an 'end' time, allow 24:00
 * @return string
 */	
function wpb_secs2hours( $secs, $show_2400 = false ) {
	$min = (int)($secs / 60);
	$hours = "00";
	if ( $min < 60 )
		$hours_min = $hours . ":" . $min;
	else {
		$hours = (int)($min / 60);
		if ( $hours < 10 )
			$hours = "0" . $hours;
		$mins = $min - $hours * 60;
		if ( $mins < 10 )
			$mins = "0" . $mins;
		$hours_min = $hours . ":" . $mins;			
	}
	if ( BASE()->time_format )
		$hours_min = date( BASE()->time_format, strtotime( $hours_min . ":00" ) );
	
	# Exception - show 24:00 at midnight only if selected
	if ( $show_2400 && '00:00' === $hours_min && $secs )
		$hours_min = '24:00';
		
	return $hours_min;
}

/**
 * Convert any time format (incl. most custom formats) to military format
 * @param $correct_2400		bool	If this is an 'end' time, behave 24:00 as 00:00
 * @since 1.0.3
 * @return string
 */	
function wpb_to_military( $time, $correct_2400 = false ) {
	
	$time_format = BASE()->time_format;
	
	# Already in military format
	if ( 'H:i' == $time_format ) {
		if ( $correct_2400 && '24:00' === $time )
			$time = '00:00';
		
		return $time;
	}
	
	# In one of the default formats
	if ( 'g:i a' == $time_format || 'g:i A' == $time_format )
		return date( 'H:i', strtotime( $time ) );
		
	# Custom format. Use a reference time
	# ref is expected to be something like 23saat45dakika
	$ref = date_i18n( $time_format, strtotime( "23:45" ) );
	if ( strpos( $ref, "23" ) !== false )
		$twentyfour = true;
	else
		$twentyfour = false;
	
	# Now ref could be something like saat,dakika	
	$ref = ltrim( str_replace( array( '23', '45' ), ',', $ref ), ',' );
	$ref_arr = explode( ',', $ref );
	
	if ( isset( $ref_arr[0] ) ) {
		$s = $ref_arr[0]; # separator. We will replace it by :
		if ( !empty($ref_arr[1]) )
			$e = $ref_arr[1];
		else {
			$e = 'PLACEHOLDER';
			$time = $time. $e; # Add placeholder at the back
		}
		if ( $twentyfour )
			$new_e = '';
		else
			$new_e = ' a';
	}
	else if ( false === $ref_arr ) {
		# Only possible without separators, e.g. 23saat45dakika becomes 23
		if ( "23" === (string)$ref )
			return date( 'H:i', strtotime( $time .':00' ) );
		else
			return $time; # We do not have an idea what format this can be
	}
	else
		return $time; # We do not support this format

	return date( 'H:i', strtotime( str_replace( array($s,$e), array(':',$new_e), $time ) ) );
}

/**
 * Convert military formatted time (H:i) value to current time format
 * @since 2.0
 * @return string
 */	
function wpb_from_military( $time ) {
	list( $hours, $mins ) = explode( ':', $time );
	return wpb_secs2hours( $hours *60*60 + $mins *60 );
}

/**
 * Find timestamp of first day of month for a given time
 * @param $time		integer		Timestamp for which first day will be found
 * @param $add		integer		How many months to add
 * @return integer (timestamp)
 * @since 1.0.4
 */	
function wpb_first_of_month( $time, $add = 0 ) {
	$year = date( "Y", $time );
	$month = date( "n",  $time );
	
	return mktime( 0, 0, 0, $month+$add, 1, $year );
}	

/**
 * Find timestamp of midnight of last day of month for a given time
 * @param $time		integer		Timestamp for which first day will be found
 * @param $add		integer		How many months to add
 * @return integer (timestamp)
 * @since 2.0
 */	
function wpb_last_of_month( $time, $add = 0 ) {
	$year = date( "Y", $time );
	$month = date( "n",  $time );
	$last_day = date( "t",  $time ); 
	
	return mktime( 23, 59, 0, $month+$add, $last_day, $year ) + 60; # Add 60 seconds to have a full day 
}	

/**
 * Returns the timestamp of Sunday of the current week or selected date
 * This is used for calendars using Sunday and Monday as week start
 * @param timestamp: Timestamp of the selected date or false for current time
 * @return integer (timestamp)
 */	
function wpb_sunday( $timestamp = false ) {

	$date = $timestamp ? $timestamp : BASE()->_time;
	# Return today's timestamp if today is sunday and start of the week is set as Sunday
	if ( "Sunday" == date( "l", $date ) && "0" === (string)BASE()->start_of_week )
		return strtotime("today", $date );
	else
		return strtotime("last Sunday", $date );
}

/**
 * Returns the timestamp of Saturday of the current week or selected date
 * This is used for calendars using Saturday as week start
 * @param timestamp: Timestamp of the selected date or false for current time
 * @since 3.0
 * @return integer (timestamp)
 */	
function wpb_saturday( $timestamp = false ) {

	$date = $timestamp ? $timestamp : BASE()->_time;
	# Return today's timestamp if today is saturday and start of the week is set as Saturday
	if ( "Saturday" == date( "l", $date ) && 6 == BASE()->start_of_week )
		return strtotime("today", $date );
	else
		return strtotime("last Saturday", $date );
}

/**
 * Format duration for front end
 * @param duration: Minutes to be formatted
 * @return string
 * @since 2.0
 */
function wpb_format_duration( $duration ) {
	if ( !$duration )
		return '';
	
	# First, cases from 24h+
	# Case exact 24h
	if ( 1440 == $duration )
		return '1 '. BASE()->get_text('day');
	
	# Case 24h - 48h
	if ( $duration > 1440 && $duration < 2880 )
		return intval( ceil( $duration/60 ) ) . ' '. BASE()->get_text('hours');

	# Case 48h+
	$days = intval( ceil( $duration/1440 ) );
	if ( $days >= 2 )
		return $days .' '. BASE()->get_text('days');
	
	$format = BASE()->get_options('duration_format');

	if ( $duration < 60 || 'minutes' == $format )
		return $duration . ' ' . BASE()->get_text('minutes');
	
	if ( !$format || 'hours_minutes' == $format ) {		
		$hours = floor( $duration/60 );
		if ( $hours > 1 )			
			$hour_text = $hours . ' ' . BASE()->get_text('hours');
		else
			$hour_text = $hours . ' ' . BASE()->get_text('hour');
		
		$mins = $duration - $hours *60; 
		if ( $mins ) {
			if ( $mins > 1 )
				$min_text = ' ' . $mins . ' ' . BASE()->get_text('minutes');
			else
				$min_text = ' ' . $mins . ' ' . BASE()->get_text('minute');
		}
		else 
			$min_text = ''; 
			
		return $hour_text . $min_text;
	}
	
	$hours = intval( ceil( $duration/60 ) );
	
	if ( 'top_hours' == $format ) {
		if ( $hours > 1 )
			return $hours . ' ' . BASE()->get_text('hours');
		else
			return $hours . ' ' . BASE()->get_text('hour');
	}
	
	# Fallback
	if ( $hours > 1 )
		return number_format_i18n( $duration/60, 1 ) . ' ' . BASE()->get_text('hours');
	else
		return number_format_i18n( $duration/60, 1 ) . ' ' . BASE()->get_text('hour');
}

/**
 *	Change a duration into human readable format for admin, e.g. 2h 15min
 *  @param duration: Duration in minutes
 *  @since 3.0
 */	
function wpb_readable_duration( $duration ) {
	$hours = floor( $duration/60 );
	if ( $hours > 0 )
		$hour_text = $hours . BASE()->get_text('hour_short');
	else
		$hour_text = '';
	
	$mins = $duration - $hours *60; 
	if ( $mins ) {
		$min_text = $mins . BASE()->get_text('min_short');
	}
	else 
		$min_text = ''; 
		
	return trim( $hour_text . ' '. $min_text );
}

/**
 *	Inputs timestamp or date/time and outputs timestamp
 *	Does not check if input timestamp is valid  
 *  @param $dt:		integer/string		Timestamp or date/time
 *  @since 3.0
 *	@return 		integer				Timestamp
 */	
function wpb_strtotime( $dt ) {
	return is_numeric( $dt ) ? $dt : strtotime( $dt, BASE()->_time );
}

/**
 *	Inputs timestamp or date/time and outputs date/time in mysql format (Y-m-d H:i:s)
 *	Does not check if input date/time is valid  
 *  @param $dt:		integer/string		Timestamp or date/time
 *  @since 3.0
 *	@return 		integer				Timestamp
 */
function wpb_date( $dt ) {
	return is_string( $dt ) ? date("Y-m-d H:i:s", strtotime( $dt, BASE()->_time ) ) : date("Y-m-d H:i:s", $dt );
}

/**
 * Calculate prices with 2 significant digits
 * @return float
*/	
function wpb_round( $price ) {
	return round( intval($price*100)/100, 2 );	
}

/**
 * Return the thousand separator for prices.
 * @since  3.0
 * @return string
 */
function wpb_thousands_separator() {
	$separator = apply_filters( 'app_thousands_separator', BASE()->get_options( 'thousands_separator' ) );
	return stripslashes( $separator );
}

/**
 * Return the decimal separator for prices.
 * @since  3.0
 * @return string
 */
function wpb_decimal_separator() {
	$separator = apply_filters( 'app_decimal_separator', BASE()->get_options( 'decimal_separator' ) );
	return $separator ? stripslashes( $separator ) : '.';
}

/**
 * Make a price ready to be written to DB
 * http://php.net/manual/en/function.floatval.php#114486
 * @since  3.0
 * @return string
 */
function wpb_sanitize_price( $num ) {
    $dotPos = strrpos($num, '.');
    $commaPos = strrpos($num, ',');
    $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos : 
        ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);
   
    if (!$sep) {
        return wpb_round( floatval(preg_replace("/[^0-9]/", "", $num)) );
    } 

    return wpb_round( floatval(
        preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
        preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
    ) );
}

/**
 * Format a number to display currency
 * @Since 2.0 
 */	
function wpb_format_currency($currency = '', $amount = false, $hide_symbol = false, $force_decimal = false) {
	
	if ( !$currency ) {
		if ( !$currency = BASE()->get_options('currency') )
		$currency = 'USD';
	}

	// get the currency symbol
	include_once( WPB_PLUGIN_DIR . '/includes/constant-data.php' );
	$currencies = WpBConstant::currencies();
	$symbol = $currencies[$currency][1];
	// if many symbols are found, rebuild the full symbol
	$symbols = explode( ', ', $symbol );
	if ( is_array( $symbols ) ) {
		$symbol = "";
		foreach ( $symbols as $temp ) {
		 $symbol .= '&#x'.$temp.';';
		}
	} else {
		$symbol = '&#x'.$symbol.';';
	}
	if ( $hide_symbol )
		$symbol ='';
	
	//check decimal option
	if ( !$force_decimal && "0" === (string)BASE()->get_options('curr_decimal') ) {
		$decimal_place = 0;
		$zero = '0';
	} else {
		$decimal_place = 2;
		$zero = '0'. wpb_decimal_separator().'00';
	}
	
	// If amount is saved in another decimal point system
	$amount = str_replace( ',', '.', $amount );
	
	$symbol_position = BASE()->get_options('curr_symbol_position');

	//Format currency amount
	if ( $amount ) {
		
		if ( $amount < 0 ) {
			$minus = '-';
			$amount = abs( $amount );
		}
		else $minus = '';
		
		$formatted_amount = number_format( $amount, $decimal_place, wpb_decimal_separator(), wpb_thousands_separator() );

		if ( !$symbol_position || $symbol_position == 1  )
			return $minus . $symbol . $formatted_amount;
		else {
			switch ( (int) BASE()->get_options('curr_symbol_position') ) {
				case 2:		return $symbol . ' ' . $minus . $formatted_amount; break;
				case 3:		return $minus. $formatted_amount . $symbol; break;
				case 4:		return $minus. $formatted_amount . ' ' . $symbol; break;
				default:	return $minus . $symbol . $formatted_amount; break;
			}
		}
	} else if ( false === $amount || '' === (string)$amount ) {
		return $symbol;
	} else {
		if ( !$symbol_position || $symbol_position == 1 )
			return $symbol . $zero;
		else {
			switch ( (int)$symbol_position ) {
				case 2:		return $symbol . ' ' . $zero; break;
				case 3:		return $zero . $symbol; break;
				case 4: 	return $zero . ' ' . $symbol; break;
				default:	return $symbol . $zero; break;
			}
		}
	}
}

/**
 * Converts 1 to 1st, 2 to 2nd, etc
 * https://stackoverflow.com/a/3110033
 * @return string
 */
function wpb_ordinal($number) {
	$ends = array('th','st','nd','rd','th','th','th','th','th','th');
	if ((($number % 100) >= 11) && (($number%100) <= 13))
		return $number. 'th';
	else
		return $number. $ends[$number % 10];
}
	
/**
 * Remove tabs and breaks and some spaces to compress js
 * @return string
*/	
function wpb_esc_rn( $text ) {
	$text = str_replace( array("\t","\n","\r"), "", $text );
	$text = str_replace( array(" (","( "), "(", $text );
	$text = str_replace( array(" )",") "), ")", $text );
	$text = preg_replace( '/\s\s+/', ' ', $text );
	$text = preg_replace("/\s*([\/[=|)|(|}|{]])\s*/", "$1", $text);
	return $text;
}

/**
 * Clear app shortcodes
 * Fast, but not 100% fail safe
 * Use in non-critical content modifications only
 * For critical functions use get_shortcode_regex( wpb_shortcodes() ) 
 * @since 1.1.9
 */
function wpb_strip_shortcodes( $content ) {
	if ( !is_string( $content ) )
		return $content;
	else
		return preg_replace( '%\[app_(.*?)\]%s', '', $content );
}

/**
 * Provide a list of supported WP BASE shortcodes, i.e. array( 'app_book', app_services',... )
 * User defined shortcodes can be added using the provided filter 
 * @since 3.0
 * @return array
 */
function wpb_shortcodes() {
	# During install, an option should have been created
	$scodes = get_option( 'wp_base_shortcodes' );
	
	# If empty, something must have gone wrong, regenerate it
	if ( empty( $scodes ) ) {
		include_once( WPB_PLUGIN_DIR . '/includes/constant-data.php' );
		$scodes = array_keys( WpBConstant::shortcode_desc() );
		update_option( 'wp_base_shortcodes', $scodes );
	}
	
	return apply_filters( 'app_shortcodes', $scodes );
}
	
/**
 *	Check if user is connected with a mobile device
 *	@since 2.0
 */
function wpb_is_mobile( ) {
	include_once( WPB_PLUGIN_DIR . '/includes/lib/Mobile_Detect.php' );
	$mobile_detect = new WpB_Mobile_Detect();
	$is_mobile = $mobile_detect->isMobile();
	if ( 'yes' != BASE()->get_options('is_tablet_mobile') )
		$result = $is_mobile;
	else {
		$is_tablet = $mobile_detect->isTablet();
		$result = $is_mobile || $is_tablet;
	}
	
	return apply_filters( 'app_is_mobile', $result );
}

/**
 *	Check if user connected with a tablet
 *	@since 2.0
 */
function wpb_is_tablet( ) {
	include_once( WPB_PLUGIN_DIR  . '/includes/lib/Mobile_Detect.php' );
	$mobile_detect = new WpB_Mobile_Detect();
	return $mobile_detect->isTablet();
}

/**
 *	Check if user is connected with a device with iOS operating system
 *	@since 2.0
 */
function wpb_is_ios(){
	include_once( WPB_PLUGIN_DIR . '/includes/lib/Mobile_Detect.php' );
	$mobile_detect = new WpB_Mobile_Detect();
	if ( $mobile_detect->is('iOS') )
		return true;
	else
		return false;
}

/**
 *	Check if user is connected with a device with android operating system
 *	@since 2.0
 */
function wpb_is_android(){
	include_once( WPB_PLUGIN_DIR . '/includes/lib/Mobile_Detect.php' );
	$mobile_detect = new WpB_Mobile_Detect();
	if ( $mobile_detect->is('AndroidOS') )
		return true;
	else
		return false;
}

/**
 * Determine if this is localhost
 * @since 2.0
 */
function wpb_is_localhost() {
	$whitelist = array( '127.0.0.1', '::1' );
	if( in_array( $_SERVER['REMOTE_ADDR'], $whitelist) )
		return true;
	else
		return false;
}

/**
 * Determine if this is an admin page or doing ajax on behalf of an admin page
 * @since 2.0
 */
function wpb_is_admin() {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		if ( !empty( $_POST['post_id'] ) )
			return false;
		if ( !empty( $_POST['page_id'] ) )
			return false;
		if ( !empty( $_POST['screen_base'] ) )
			return true;
		
		global $bp;
		
		if ( is_object( $bp ) && isset( $bp->displayed_user->domain ) ) {
			return false;
		}
	}
	
	return is_admin();
}

/**
 * Check if user has at least one of the required capability
 * @param $cap	array|string	Comma delimited string or array with required caps to check
 * @since 3.0
 * @return bool
 */
function wpb_current_user_can( $cap ) {
	return BASE('User') ? BASE('User')->is_capable( $cap ) : current_user_can( $cap );
}

/**
 * Provide WP BASE settings or a single setting
 * @param $item: Pick the required key. Leave empty for all options.
 * @param $fallback:	Return that value if option set, but not true
 * @since 2.0
 * @return array
 */
function wpb_setting( $item = null, $fallback = null ) {

	$options = apply_filters( 'app_options', get_option( 'wp_base_options' ) ); 
	if ( null === $item )
		return (array)$options;
	
	if ( empty( $options[$item] ) && null !== $fallback )
		return $fallback;
	
	if ( isset( $options[$item] ) )
		return $options[$item];
	else
		return '';
}

/**
 * Session start
 * WP BASE does not use PHP sessions, but uses its own session cookie. See class.session.php
 * @since 2.0
 * @return string (Session ID)
 */
function wpb_session_start(){
	return BASE()->session()->get_id();
}

/**
 * Read a session variable
 * Will also attempt to start session
 * @param $item: Pick the required key. 
 * @param $fallback:	Return that value if $item is not set
 * @since 3.0
 * @return mixed
 */
function wpb_get_session_val( $item, $fallback = null ) {
	$val = BASE()->session()->get( $item );
	if ( null === $val )
		return $fallback;
	
	return $val;
}

/**
 * Write a session variable
 * Will also attempt to start session
 * @param $value: 		Value to be written. 
 * @param $fallback:	Return that value if $item is not set
 * @since 3.0
 * @return mixed
 */
function wpb_set_session_val( $item, $value ) {
	return BASE()->session()->set( $item, $value );
}

