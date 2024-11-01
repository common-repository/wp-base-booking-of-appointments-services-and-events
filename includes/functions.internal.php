<?php
/**
 * Internal functions
 *
 * Manages internal affairs. Has little or no use for ordinary user.
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

 if ( ! defined( 'ABSPATH' ) ) exit;
 
 /**
 * Find current admin side screen ID
 * @param $add_tab	bool	Whether add $_GET['tab']
 * @uses get_current_screen()
 * @since 3.0
 * @return string
 */
function wpb_get_current_screen_id( $add_tab = false ) {
	if ( function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();
		
		$tab = $add_tab && !empty( $_GET['tab'] ) ? '_' . $_GET['tab'] : '';
		
		if ( isset( $screen->id ) )
			return $screen->id . $tab;
	}
	
	return '';
}

/**
 * Find id of the product post being used
 * Includes ajaxed requests made from a page
 * @return integer
 * @since 2.0
 */
function wpb_find_post_id() {
	$post = get_post();
	if ( isset( $post->ID ) )
		$post_id = $post->ID;
	else if ( !empty( $_POST["post_id"] ) )
		$post_id = $_POST["post_id"];
	else if ( !empty( $_POST["page_id"] ) )
		$post_id = $_POST["page_id"];
	else
		$post_id = wpb_get_session_val('app_post_id', 0);
	
	return $post_id;
}
	
/**
 * Check if cache shall be used
 * In addition to the setting, Quotas and Timezones prevent usage of cache
 * @since 3.0
 * @return bool
 */	
function wpb_use_cache(){

	if ( !empty( $_GET['force-cache'] ) && md5( wpb_get_salt() . 'force-cache' ) == $_GET['force-cache'] ) {
		return true;
	}
		
	return !wpb_setting( 'cache' ) || ( is_user_logged_in() && BASE('Quotas') && BASE('Quotas')->get_quotas( BASE()->get_sid() ) )
			?
			false
			:
			true;
}

/**
 * Flush WP BASE cache by invalidating cache prefix
 * Optionally also preload cache: Recreate a fresh cache
 * @param timed: make a time controlled cache clear
 * @since 3.0
 * @return none
 */
function wpb_flush_cache( $timed = false ){
		
	delete_transient( 'wpb_bookable' );
	
	# Increment prefix, so WP BASE caches using 'wp_base_cache_prefix' prefix are invalid
	# Idea from http://tollmanz.com/invalidation-schemes/
	wp_cache_incr( 'wp_base_cache_prefix' );
	
	if ( !$timed )
		do_action( 'app_flush_cache' );
	else
		do_action( 'app_flush_cache_timed' );
	
	# Preload current page during Ajax, e.g. after a new booking 
	if ( !( defined( 'WPB_AJAX' ) && WPB_AJAX && 'preload' === wpb_setting( 'cache' ) ) )
		return;
	
	wpb_maybe_preload_cache( wpb_find_post_id() );
}

/**
 * Call pages remotely so cache can be preloaded
 * @param $pages	string		Optional comma delimited page IDs to call. If left empty, settings will be used
 * @since 3.0
 * @return bool|null	null if settings do not allow, true on success, false if loopback fails
 */
function wpb_maybe_preload_cache( $pages='' ){
	if ( !$pages && 'preload' != wpb_setting( 'cache' ) )
		return null;
	
	$pages	= explode( ',', $pages ? $pages : wpb_setting("preload_pages") );
	
	if ( empty( $pages ) )
		return null;

	$hash = md5( wpb_get_salt() . 'force-cache' );
	
	foreach ( (array)$pages as $page_id ) {
		$page = get_post( $page_id );
		if ( empty( $page->ID ) ||  empty( $page->post_content ) || strpos( $page->post_content, '[app_' ) === false )
			continue;
		
		$r = wp_remote_get( add_query_arg( 'force-cache', $hash, get_permalink( $page->ID ) ), 
							array( 'timeout' => WPB_PRELOAD_TIMEOUT, 'blocking' => false,  ) 
							);

		if ( is_wp_error( $r ) )
			return false;
	}

	return true;	
}

/**
 * Get cache prefix
 * @since 3.0
 * @return string
 */
function wpb_cache_prefix(){
	$prefix = wp_cache_get( 'wp_base_cache_prefix' );
	
	if ( false === $prefix ) {
		$prefix = 1;
		wp_cache_set( 'wp_base_cache_prefix', $prefix );
	}
	
	return 'wp_base_cache_' . $prefix . '_';
}

/**
 * Get headers to read file data
 * @since 3.0
 * @return array
 */
function wpb_default_headers(){
	return array(
		'Name'			=> 'Plugin Name',
		'Author'		=> 'Author',
		'Description' 	=> 'Description',
		'Plugin URI' 	=> 'Plugin URI',
		'Version' 		=> 'Version',
		'Detail' 		=> 'Detail',
		'Class' 		=> 'Class Name',
		'ID' 			=> 'ID',
		'Category' 		=> 'Category',
	);
}

/**
 * Instantiate and register an addon
 * Closure prevents app_active_extensions filter to be removed
 * @since 3.0
 *
 * @param $identifier	string		Part of name of the addon class (class name: WpB + $identifier)
 * @param $file			string		File name including absolute path
 * @return none
 */
function wpb_addon_run( $identifier, $file ) {
	if ( BASE( $identifier ) ) {
		
		add_filter( 'app_active_extensions', 
			function( $result ) use ( $identifier, $file ){ 
				if ( is_array( $result ) )
					$result[$file] = $identifier;
				else 
					$result = array( $file => $identifier );

				return $result; 
			}, 
		10, 1 );
		
		BASE( $identifier )->add_hooks();
		
		if ( !wpb_is_admin() )
			return;
		
		if ( !class_exists( 'WPB_Plugin_Updater' ) ) {	
			include_once( WPB_PLUGIN_DIR . "/includes/lib/plugin-updater.php" );
		}
		
		$data = get_file_data( $file, wpb_default_headers(), 'plugin' );
		
		$wpb_updater = new WPB_Plugin_Updater( WPB_URL, $file, array(
			'version'   => $data['Version'],
			'license'   => trim( get_option( 'wpb_license_key_'. $data['ID'] ) ),
			'item_id' 	=> $data['ID'],
			'Category' 	=> $data['Category'],
			'author'    => $data['Author'], 
			)
		);
		
	
		add_filter( 'plugin_action_links_'. plugin_basename( $file ), 
			function( $links ) use ( $file ){
				return array_merge( $links, array( '<a target="_blank" href="'.WPB_URL.'knowledge-base/'.str_replace( 'payment-gateway-', '', pathinfo($file, PATHINFO_FILENAME) ).'/">'. __('Help', 'wp-base' ) . '</a>' ) );
			}, 
		10, 1 );		
	}
}

/**
 * Disable identical addon
 * An addon in WP Plugin format will be loaded first and prevent same filename in includes/addons folder to be loaded again
 */
function wpb_disable_addon( $result, $addon, $file ) {
	if ( $result || strpos( str_replace( '\\', '/', dirname( $file ) ), 'includes/addons' ) !== false )
		return $result;
	$filename = pathinfo( $file, PATHINFO_FILENAME );
	if ( $addon == $filename )
		return sprintf( __( 'Disabled by WP BASE %s plugin', 'wp-base' ), ucwords( str_replace('-', ' ', $filename ) ) );

	return $result;
}

/**
 * Load and Run Main class (of a payment gateway addon)
 */
function wpb_load_main( $file ) {
	$basename = str_replace( 'payment-gateway-', '', basename( $file, '.php' ) );
	include_once( plugin_dir_path( $file ) . $basename . '/'. $basename . '.php' );

	global $app_gateway_plugins, $app_gateway_active_plugins;
	if ( empty( $app_gateway_plugins[$basename] ) )
		return false;
	
	$options = BASE()->get_options();
	
	# This will correct false running of gateway during first save of allowed
	if ( isset( $_POST['mp']['gateways']['allowed'] ) )
		$options['gateways']['allowed'] = $_POST['mp']['gateways']['allowed'];
	else if ( isset( $_POST['mp'] ) ) {
		# Blank array if no checkboxes
		$options['gateways']['allowed'] = array();
	}
	$allowed = !empty( $options['gateways']['allowed'] ) && is_array( $options['gateways']['allowed'] ) ? $options['gateways']['allowed'] : array();
	
	if ( !in_array( $basename, $allowed ) )
		return false;
	
	$class_name = $app_gateway_plugins[$basename][0];
	
	if ( class_exists( $class_name ) ) {
		$app_gateway_active_plugins[] = new $class_name;
		return true;
	}
	
	return false;
}

/**
 * Sanitizes url against XSS attacks
 * @param $a, $b, $c	mixed		Same as add_query_arg
 * @since 2.0
 * @return string
 */	
function wpb_add_query_arg( $a, $b = false, $c = false ) {
	if ( is_array( $a ) ) {
		if ( false === $b && false === $c )
			$b = esc_url_raw( $_SERVER['REQUEST_URI'] );
		
		return add_query_arg( $a, $b );
	}
	else {
		if ( $c === false )
			$c = esc_url_raw( $_SERVER['REQUEST_URI'] );
		
		return add_query_arg( $a, $b, $c );
	}
}

/**
 * Find difference of two associated arrays recursively
 * http://nl3.php.net/manual/en/function.array-diff-assoc.php#111675
 * @return array
 */
function wpb_array_diff_assoc_recursive($array1, $array2) {
	$difference=array();
	
	foreach($array1 as $key => $value) {
		if( is_array($value) ) {
			if( !isset($array2[$key]) || !is_array($array2[$key]) ) {
				$difference[$key] = $value;
			} else {
				$new_diff = wpb_array_diff_assoc_recursive($value, $array2[$key]);
				if( !empty($new_diff) )
					$difference[$key] = $new_diff;
			}
		} else if( !array_key_exists($key,$array2) || $array2[$key] !== $value ) {
			$difference[$key] = $value;
		}
	}
	
	return $difference;
}

/**
 * Remove duplicate email messages by app ID
 * @param $messages		array	array of objects or array of arrays
 * @return array
 */		
function wpb_array_unique_by_ID( $messages ) {
	if ( !is_array( $messages ) || empty( $messages ) )
		return false;
	$idlist = array();
	
	$result = $messages;
	foreach ( $messages as $key=>$message ) {
		if ( isset( $message['ID'] ) ) {
			if ( in_array( $message['ID'], $idlist ) )
				unset( $result[$key] );
			else
				$idlist[] = $message['ID'];
		}
		else if ( isset( $message->ID ) ) {
			if ( in_array( $message->ID, $idlist ) )
				unset( $result[$key] );
			else
				$idlist[] = $message->ID;
		}
	}
	return $result;
}

/**
 * Arranges days array acc. to start of week, e.g 1234567 (Week starting with Monday)
 * @param days: input array
 * @param prepend: What to add as first element
 * @pram nod: If number of days (true) or name of days (false)
 * @return array
 */	
function wpb_arrange( $days, $prepend, $nod = false ) {
	if ( BASE()->start_of_week ) {
		for ( $n = 1; $n <= BASE()->start_of_week; $n++ ) {
			array_push( $days, array_shift( $days ) );
		}
		// Fix for displaying past days; apply only for number of days
		if ( $nod ) {
			$first = false;
			$temp = array();
			foreach ( $days as $key=>$day ) {
				if ( !$first ) 
					$first = $day; // Save the first day
				if ( $day < $first )
					$temp[$key] = $day + 7; // Latter days should be higher than the first day
				else
					$temp[$key] = $day;
			}
			$days = $temp;
		}
	}
	if ( false !== $prepend )
		array_unshift( $days, $prepend );

	return $days;
}

/**
 * Find timestamp of the first moment of a given week defined with wpb_time2week function
 * @since 2.0
 * @return string
 */	
function wpb_week2time( $week_no, $year = false ) {
	if ( false === $year )
		$year = date( "Y", BASE()->_time );
	
	// http://www.php.net/manual/en/function.strftime.php#77489
	$first_day = 1 + ((7+BASE()->start_of_week - strftime("%w", mktime(0,0,0,1,1,$year)))%7);
	$first_date = mktime(0,0,0,1,$first_day,$year);
	
	return $first_date + ($week_no-1)*7*24*3600;
}

/**
 * Find week number for a given timestamp
 * This number is used only internally: It does not comply with ISO8601
 * @since 2.0
 * @return string
 */	
function wpb_time2week( $timestamp = false ) {
	
	$start_of_week = BASE()->start_of_week;
	
	if ( false === $timestamp )
		$timestamp = BASE()->_time;
	
	if ( 1 == $start_of_week )
		return intval(strftime( "%W", $timestamp ));
	else if ( "0" === (string)$start_of_week )
		return intval(strftime( "%U", $timestamp ));
	else {
		$year = date( "Y", $timestamp );
		$first_day = 1 + (7 + $start_of_week - strftime("%w", mktime(0,0,0,1,1,$year)))%7;
		$first_date = mktime(0,0,0,1,$first_day,$year);
		
		$first_day_sunday = 1 + ((7 - strftime("%w", mktime(0,0,0,1,1,$year)))%7);
		$first_date_sunday = mktime(0,0,0,1,$first_day_sunday,$year);
		
		$add = $first_date_sunday > $first_date ? 1 : 0;
		
		return strftime( "%U", $timestamp ) + $add;
	}
}

/**
 * Find number of weeks in a year
 * This number is used only internally: It does not comply with ISO8601
 * @since 2.0
 * @return integer
 */	
function wpb_nof_weeks( $year ) {
	
	$start_of_week = BASE()->start_of_week;
	
	$first_day = 1 + ((7+$start_of_week - strftime("%w", mktime(0,0,0,1,1,$year)))%7);
	$first_date = mktime(0,0,0,1,$first_day,$year);
	
	$first_day_ny = 1 + ((7+$start_of_week - strftime("%w", mktime(0,0,0,1,1,$year+1)))%7);
	$first_date_ny = mktime(0,0,0,1,$first_day_ny,$year+1);
	
	return intval(($first_date_ny - $first_date)/(7*24*3600));
}

/*
 * Matches each symbol of PHP date format standard
 * with jQuery equivalent codeword
 * http://stackoverflow.com/a/16725290
 * @author Tristan Jahier
 */
function wpb_dateformat_PHP_to_jQueryUI( $php_format )
{
	$SYMBOLS_MATCHING = array(
		// Day
		'd' => 'dd',
		'D' => 'D',
		'j' => 'd',
		'l' => 'DD',
		'N' => '',
		'S' => '',
		'w' => '',
		'z' => 'o',
		// Week
		'W' => '',
		// Month
		'F' => 'MM',
		'm' => 'mm',
		'M' => 'M',
		'n' => 'm',
		't' => '',
		// Year
		'L' => '',
		'o' => '',
		'Y' => 'yy',
		'y' => 'y',
		// Time
		'a' => '',
		'A' => '',
		'B' => '',
		'g' => '',
		'G' => '',
		'h' => '',
		'H' => '',
		'i' => '',
		's' => '',
		'u' => ''
	);
	$jqueryui_format = "";
	$escaping = false;
	for($i = 0; $i < strlen($php_format); $i++)
	{
		$char = $php_format[$i];
		if($char === '\\') // PHP date format escaping character
		{
			$i++;
			if($escaping) $jqueryui_format .= $php_format[$i];
			else $jqueryui_format .= '\'' . $php_format[$i];
			$escaping = true;
		}
		else
		{
			if($escaping) { $jqueryui_format .= "'"; $escaping = false; }
			if(isset($SYMBOLS_MATCHING[$char]))
				$jqueryui_format .= $SYMBOLS_MATCHING[$char];
			else
				$jqueryui_format .= $char;
		}
	}
	
	return $jqueryui_format;
}

/**
 * Return set date time format in Moment Format
 * http://stackoverflow.com/questions/30186611/php-dateformat-to-moment-js-format
 * @param $time_only: No date, just time
 * @since 2.0
 * @return string
 */		
function wpb_moment_format( $time_only = false ) {
	$replacements = array(
		'd' => 'DD',
		'D' => 'ddd',
		'j' => 'D',
		'l' => 'dddd',
		'N' => 'E',
		'S' => 'o',
		'w' => 'e',
		'z' => 'DDD',
		'W' => 'W',
		'F' => 'MMMM',
		'm' => 'MM',
		'M' => 'MMM',
		'n' => 'M',
		't' => '', // no equivalent
		'L' => '', // no equivalent
		'o' => 'YYYY',
		'Y' => 'YYYY',
		'y' => 'YY',
		'a' => 'a',
		'A' => 'A',
		'B' => '', // no equivalent
		'g' => 'h',
		'G' => 'H',
		'h' => 'hh',
		'H' => 'HH',
		'i' => 'mm',
		's' => 'ss',
		'u' => 'SSS',
		'e' => 'zz', // deprecated since version 1.6.0 of moment.js
		'I' => '', // no equivalent
		'O' => '', // no equivalent
		'P' => '', // no equivalent
		'T' => '', // no equivalent
		'Z' => '', // no equivalent
		'c' => '', // no equivalent
		'r' => '', // no equivalent
		'U' => 'X',
	);
	if ( $time_only )
		$momentFormat = strtr(BASE()->time_format, $replacements);
	else
		$momentFormat = strtr(BASE()->dt_format, $replacements);
	
	return $momentFormat;
}

/**
 * Return a default color for a selected box class
 * @return string
 */		
function wpb_get_preset( $class, $set ) {
	if ( 1 == $set )
		switch ( $class ) {
			case 'free'				:	return '48c048';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'ffffff';
			case 'notpossible'		:	return 'ffffff';
			default					:	return 'ddfd1b';
		}
	else if ( 2 == $set )
		switch ( $class ) {
			case 'free'				:	return '73ac39';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return '616b6b';
			case 'notpossible'		:	return '8f99a3';
			default					:	return 'ddfd1b';
		}
	else if ( 3 == $set )
		switch ( $class ) {
			case 'free'				:	return '40BF40';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'ff0000';
			case 'notpossible'		:	return 'c0c0c0';
			default					:	return 'ddfd1b';
		}
	else if ( 'ui-lightness' == $set )
		switch ( $class ) {
			case 'free'				:	return '1c94c4';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'f6a828';
			case 'notpossible'		:	return 'dddddd';
			default					:	return 'ddfd1b';
		}
	else if ( 'ui-darkness' == $set )
		switch ( $class ) {
			case 'free'				:	return 'ffffff';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return '000000';
			case 'notpossible'		:	return 'dddddd';
			default					:	return 'ddfd1b';
		}
	else if ( 'smoothness' == $set )
		switch ( $class ) {
			case 'free'				:	return 'b3d4fc';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'cd0a0a';
			case 'notpossible'		:	return 'fcefa1';
			default					:	return 'ddfd1b';
		}
	else if ( 'start' == $set )
		switch ( $class ) {
			case 'free'				:	return '6eac2c';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'f8da4e';
			case 'notpossible'		:	return 'aaaaaa';
			default					:	return 'ddfd1b';
		}	
	else if ( 'redmond' == $set )
		switch ( $class ) {
			case 'free'				:	return '79b7e7';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'fcefa1';
			case 'notpossible'		:	return 'aaaaaa';
			default					:	return 'ddfd1b';
		}	
	else if ( 'sunny' == $set )
		switch ( $class ) {
			case 'free'				:	return 'feeebd';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'd34d17';
			case 'notpossible'		:	return '817865';
			default					:	return 'ddfd1b';
		}	
	else if ( 'overcast' == $set )
		switch ( $class ) {
			case 'free'				:	return '3383bb';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'c0402a';
			case 'notpossible'		:	return 'eeeeee';
			default					:	return 'ddfd1b';
		}	
	else if ( 'le-frog' == $set )
		switch ( $class ) {
			case 'free'				:	return '4ce90b';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'fcefa1';
			case 'notpossible'		:	return 'e6e6e6';
			default					:	return 'ddfd1b';
		}	
	else if ( 'flick' == $set )
		switch ( $class ) {
			case 'free'				:	return '0073ea';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'ff0084';
			case 'notpossible'		:	return 'dddddd';
			default					:	return 'ddfd1b';
		}	
	else if ( 'pepper-grinder' == $set )
		switch ( $class ) {
			case 'free'				:	return 'f7f3de';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'b83400';
			case 'notpossible'		:	return '6e4f1c';
			default					:	return 'ddfd1b';
		}	
	else if ( 'eggplant' == $set )
		switch ( $class ) {
			case 'free'				:	return '734d99';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return '994d53';
			case 'notpossible'		:	return 'dcd9de';
			default					:	return 'ddfd1b';
		}	
	else if ( 'dark-hive' == $set )
		switch ( $class ) {
			case 'free'				:	return '0972a5';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'cd0a0a';
			case 'notpossible'		:	return 'aaaaaa';
			default					:	return 'ddfd1b';
		}	
	else if ( 'cupertino' == $set )
		switch ( $class ) {
			case 'free'				:	return '2779aa';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'ffef8f';
			case 'notpossible'		:	return 'dddddd';
			default					:	return 'ddfd1b';
		}	
	else if ( 'south-street' == $set )
		switch ( $class ) {
			case 'free'				:	return '45c800';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'fcefa1';
			case 'notpossible'		:	return 'aaaaaa';
			default					:	return 'ddfd1b';
		}	
	else if ( 'blitzer' == $set )
		switch ( $class ) {
			case 'free'				:	return 'ebefeb';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'cc0000';
			case 'notpossible'		:	return 'aaaaaa';
			default					:	return 'ddfd1b';
		}	
	else if ( 'humanity' == $set )
		switch ( $class ) {
			case 'free'				:	return 'e6e6e6';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'cb842e';
			case 'notpossible'		:	return 'aaaaaa';
			default					:	return 'ddfd1b';
		}	
	else if ( 'hot-sneaks' == $set )
		switch ( $class ) {
			case 'free'				:	return 'ffff38';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'ff3853';
			case 'notpossible'		:	return 'f7f7ba';
			default					:	return 'ddfd1b';
		}	
	else if ( 'excite-bike' == $set )
		switch ( $class ) {
			case 'free'				:	return 'c5ddfc';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'e69700';
			case 'notpossible'		:	return 'e6b900';
			default					:	return 'ddfd1b';
		}	
	else if ( 'vader' == $set )
		switch ( $class ) {
			case 'free'				:	return 'cccccc';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return '121212';
			case 'notpossible'		:	return '888888';
			default					:	return 'ddfd1b';
		}	
	else if ( 'dot-luv' == $set )
		switch ( $class ) {
			case 'free'				:	return '0b58a2';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'a32d00';
			case 'notpossible'		:	return '333333';
			default					:	return 'ddfd1b';
		}	
	else if ( 'mint-choc' == $set )
		switch ( $class ) {
			case 'free'				:	return 'add978';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return '5f391b';
			case 'notpossible'		:	return 'aaaaaa';
			default					:	return 'ddfd1b';
		}	
	else if ( 'black-tie' == $set )
		switch ( $class ) {
			case 'free'				:	return 'ffeb80';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'cd0a0a';
			case 'notpossible'		:	return 'aaaaaa';
			default					:	return 'ddfd1b';
		}	
	else if ( 'trontastic' == $set )
		switch ( $class ) {
			case 'free'				:	return '8cce3b';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return 'f1ac88';
			case 'notpossible'		:	return 'f6ecd5';
			default					:	return 'ddfd1b';
		}	
	else if ( 'swanky-purse' == $set )
		switch ( $class ) {
			case 'free'				:	return 'f8eec9';
			case 'has_appointment'	:	return 'ffa500';
			case 'busy'				:	return '4f4221';
			case 'notpossible'		:	return 'aaaaaa';
			default					:	return 'ddfd1b';
		}	
}

/**
 * Determine if this is a demo installation where saving of settings are not allowed
 * @since 2.0
 */
function wpb_is_demo(){
	if ( defined( 'WPB_DEMO_MODE' ) && WPB_DEMO_MODE && !(function_exists('is_super_admin') && is_super_admin()) )
		return true;
	
	return false;
}
	
/**
 * Add submenu page with multiple capability option
 * @param $capability	array|string	Comma delimited string or array with required caps to check (any of them is sufficient)
 * @see add_submenu_page WP function for other parameters: https://developer.wordpress.org/reference/functions/add_submenu_page/
 * @since 3.0
 * @return string
 */
function wpb_add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '' ) {
	$caps = is_array( $capability ) ? $capability : explode( ',', $capability );
	
	foreach ( $caps as $cap ) {
		if ( current_user_can( $cap ) )
			return add_submenu_page( $parent_slug, $page_title, $menu_title, $cap, $menu_slug, $function );
	}
}

/**
 * On admin side, print an admin notice. On front end open javascript message box or jQuery dialog 
 * @param 	$msg	string 		Message to be displayed or Admin function to be called
 * @param	$class	string		Optional class name for admin nag
 * @since 3.0
 * @return none
 */
function wpb_notice( $msg, $class = 'updated' ) {
	if ( is_admin() && class_exists( 'WpBAdmin' ) ) {
		if ( method_exists( 'WpBAdmin', $msg ) )
			add_action( 'admin_notices', array( BASE('Admin'), $msg ) );
		else {
			BASE('Notices')->set_notice($msg, $class);
		}
	}
	else
		BASE('Notices')->front( $msg );
}

/**
 * Rebuild WP BASE admin menu items  
 * @return none
 */		
function wpb_rebuild_menu(){
	include_once( WPB_PLUGIN_DIR . "/includes/admin/toolbar.php" );
	BASE('Toolbar')->rebuild_menu();	
}

/**
 * Outputs an infobox on admin side
 * @param	$visible	string		Visible content
 * @param	$hidden		string		Optional hidden content
 * @since 3.0 
 * @return none
 */		
function wpb_infobox( $visible, $hidden = false ) {
	include_once( WPB_PLUGIN_DIR . "/includes/notices.php" );
	BASE('Notices')->infobox( $visible, $hidden );
}

/**
 * Unobstructive spinner panel
 * @since 3.0
 */
function wpb_updating_html(){
	?>	<div id="app-updating-panel" class="app-updating-panel" style="display:none">
	<?php if ( 'clock' == wpb_setting( 'spinner' ) ) { ?>
		<div class="app-spinner clock" id="clock">
			<div class="shadow"></div>
			<div class="dial">
				<div class="hour hand"></div>
				<div class="minute hand"></div>
			</div>
		</div>
	<?php } else if ( 'cell' == wpb_setting( 'spinner' ) ) { ?>	
		<div class="app-cssload-loader2">
			<div class="cssload-side"></div>
			<div class="cssload-side"></div>
			<div class="cssload-side"></div>
			<div class="cssload-side"></div>
			<div class="cssload-side"></div>
			<div class="cssload-side"></div>
			<div class="cssload-side"></div>
			<div class="cssload-side"></div>
		</div>
	<?php } else if ( 'window' == wpb_setting( 'spinner' ) ) { ?>		
		<div id="xLoader">
			<div class="glistening-window">
			<span></span>
			<span></span>
			<span></span>
			<span></span>
			</div>
		</div>
	<?php } else { ?>
		<div id="app-cssload-loader">
			<div class="cssload-dot"></div>
			<div class="cssload-dot"></div>
			<div class="cssload-dot"></div>
			<div class="cssload-dot"></div>
			<div class="cssload-dot"></div>
			<div class="cssload-dot"></div>
			<div class="cssload-dot"></div>
			<div class="cssload-dot"></div>
		</div>
	<?php } ?>	
		<div class="app-updating">WP BASE</div>		
	</div>
	<?php		
}

/**
 * Turns an unavail code into readable
 * @param $no: Code number
 * @since 3.0
 * @return string
 */
function wpb_code2reason( $no ) {
	$codes = array(
		1	=> 'busy',
		2	=> 'no_workers',
		3	=> 'no_time',
		4	=> 'break',
		5	=> 'complete_brk',
		6	=> 'lateral',
		7	=> 'location_capacity_full',
		8	=> 'service_not_working',
		9	=> 'all_day_off',
		10	=> 'holiday',
		11	=> 'blocked',
		12	=> 'past_today',
		13	=> 'past_yesterday_or_before',
		14	=> 'upper_limit',
		15	=> 'not_published',
		16	=> 'app_interim',
		17	=> 'unknown',
		18	=> 'custom',
	);
	
	$codes = apply_filters( 'app_notavailable_codes', $codes, $no );
		
	return isset( $codes[$no] ) ? $codes[$no] : '';
}

/**
 * Return a selected field name
 * @return string (name of the field)
 */		
function wpb_get_field_name( $key ) {
	
	$field_name = BASE()->get_text( $key );
	$field_name = $field_name ? $field_name : ucwords( str_replace('_',' ', $key) );

	return apply_filters( 'app_get_field_name', $field_name, $key );
}

/**
 * Return an array of login methods
 * @since 2.0
 * @return array
 */	
function wpb_login_methods(){
	return apply_filters( 'app_login_methods', array('Facebook', 'Twitter', 'Google+', 'WordPress') );
}

/**
 * Creates HTML for login required setting
 * @since 3.0
 */	
function wpb_login_required_setting(){
?>
	<tr id="login-required">
		<th scope="row" ><?php WpBConstant::echo_setting_name('login_required') ?></th>
		<td>
		<select name="login_required">
		<option value="no" <?php if ( wpb_setting('login_required') != 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
		<option value="yes" <?php if ( wpb_setting('login_required') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
		</select>
		<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('login_required') ?></span>
		</td>
	</tr>
<?php	
}

/**
 * Die showing which field has a problem
 * @param $goto		string	jQuery selector on the document to scroll to
 * @return json object
 */
function wpb_json_die( $field_name, $goto = '' ) {
	die( json_encode( array("error"=>sprintf( esc_js( BASE()->get_text('wrong_value')), wpb_get_field_name($field_name)), "goTo"=>$goto)));
}

/**
 * Check if user can access an admin page
 * @param $cap	array|string	Comma delimited string or array with required caps to check (any one of them is sufficient)
 * @param $die	bool			If true die, if false return result
 * @since 3.0
 * @return none|bool
 */
function wpb_admin_access_check( $cap = '', $die = true ) {
	
	$one_of_these = array( WPB_ADMIN_CAP ); # Admin capability can access everything
	
	if ( function_exists( 'is_multisite' ) && is_multisite() )
		$one_of_these[] = 'super_admin'; # Not a capability, but it works
	
	if ( $cap ) {
		$caps_arr = is_array( $cap ) ? $cap : explode( ',', $cap );
		$one_of_these = array_unique( array_merge( $one_of_these, $caps_arr ) );
	}
	
	if ( !wpb_current_user_can( $one_of_these ) ) {
		if ( $die )
			wp_die( __('You do not have sufficient permissions to access this page.','wp-base'), __('Unauthorised','wp-base'), array("back_link"=>true));
		else
			return false;
	}

	return true;	
}

/**
* Whether a field is hidden in confirmation form
* @param $field		string		Name of the confirmation form field to be checked
* @since 3.0
* @return bool
*/	
function wpb_is_hidden( $field ) {
	return in_array( $field, explode( ',', wpb_setting("conf_form_hidden_fields") ) );
}

/**
 * Return fields to be hidden in the confirmation form
 * @since 3.0
 * @return array
 */	
function wpb_conf_form_fields(){
	return apply_filters( 'app_conf_form_fields', array('service', 'provider', 'date_time', 'end_date_time', 'lasts', 'details', 'price', 'deposit', 'down_payment' ) );
}

/**
 * Cart error getter
 * @since 3.0
 * @return string
 */	
function wpb_get_cart_error(){
	return BASE()->checkout_error;
}

/**
 * Cart error setter
 * @since 3.0
 */	
function wpb_set_cart_error( $msg, $context = 'checkout' ){
	BASE()->checkout_error = $msg;
}