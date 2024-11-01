<?php
/**
* Force Payment Required on a particular page
*/
add_filter( 'app_options', 'app_force_payment' );
function app_force_payment( $options ){
    if ( wpb_find_post_id() != 123 )
        return $options;
    
    $options['payment_required'] = 'yes';
    return $options;
}

/**
* Fill calendar cell with available worker count
* Return array
*/	
add_filter( 'app_weekly_calendar_cell_fill', 'app_calendar_cell_fill', 10, 2 );
add_filter( 'app_monthly_calendar_cell_fill', 'app_calendar_cell_fill', 10, 2 );
function app_calendar_cell_fill( $arr, $slot ) {
    if ( $slot->reason || is_admin() && !(defined( 'WPB_AJAX' ) && WPB_AJAX ) )
        return $arr;
    
    $start = $slot->get_start();
    $end = $slot->get_end();
    $busy = isset( $slot->stat[$start][$end]['count'] ) ? $slot->stat[$start][$end]['count'] : 0;
    if ( $slot->get_worker() )
        $avail = 1;
    else
    	$avail = isset( $slot->stat[$start][$end]['available'] ) ? $slot->stat[$start][$end]['available'] : $slot->available_workforce(  );
  	
    $fill = !empty($arr['fill']) ? '<div>'.$arr['fill'].'</div><div>'.max( 0, $avail-$busy ).'</div>' : max( 0, $avail-$busy );
    
    return array( 'class_name'=>'app-cell-centered', 'fill'=>$fill );
}

/**
* Validation for zip submission for UDF Addon
* Returning false means value validates.
*/	
add_filter( 'app_zip_validation', 'app_zip_validation', 10, 4 );
function app_zip_validation( $result, $value, $values_arr, $field ) {
    $zips = array( 83000, 83150 );
    if ( !in_array( $value, $zips ) )
        return "We do not serve to that area!";
    else 
    	return false;
}

/**
* Validation for udf_4 submission for UDF Addon
* Accepts values 3...10
* Returning false means value validates.
*/	
add_filter( 'app_udf_4_validation', 'app_udf_4_validation', 10, 4 );
function app_udf_4_validation( $result, $value, $values_arr, $field ) {
    if ( $value < 3 )
        return "Submitted number ({$value}) is too small";
    else if ( $value > 10 )
        return "Submitted number ({$value}) is too large";
	else    
    	return false;
}

/**
* 
* Hiding price field of service setting for non-admin users
*/	
add_filter( 'app_services_add_service', 'app_hide_service_price', 10, 3 );
function app_hide_service_price( $html, $no, $service ){
    if ( current_user_can( WPB_ADMIN_CAP ) )
        return $html;
    
	$html = preg_replace( '%<input class="app_service_price" style="(.*?)"(.*?)/>%',
                         '<input class="app_service_price" style="visibility:hidden"$2',
                         $html );
    
    return $html;
}

/**
* Example of custom time table display for monthly calendar for a 12-hours service (ID:24)
*/	
add_filter( 'app_timetable_cell_fill', 'app_custom_time_display', 10, 2 );
function app_custom_time_display( $display, $slot ) {
    $service = $slot->get_service();
    if ( $service != 24 || BASe()->is_package( $service )  )
        return $display;
    
    if ( date( 'G', $slot->get_start() ) < 12 )
        return 'Morning Session';
    else
        return 'Afternoon Session';
}

/**
* Remove time display (i.e. show only date) for emails and confirmation form for a 12-hours service (ID:8)
*/	
add_filter( 'app_is_daily', 'app_is_daily', 10, 2 );
function app_is_daily( $result, $ID ) {
	if ( 8 == $ID )
        return true;
    else
        return $result;
}

/**
* Modify date format without changing WP date format
* Outputs like Sat June 24, 2017 (Google format)
*/	
add_filter( 'app_date_format', 'app_date_format' );
function app_date_format( $previous ) {
	return 'D F j, Y';   
}

/**
* Use service filter in Services dropdown
* Service options are filtered out as client types some letters
* Useful if you have many services
*/	
add_filter( 'app_js_parameters', 'app_use_service_filter' );
function app_use_service_filter( $param ) {
	$param['service_filter'] = 1;
    $param['filter_label'] = "Search:";	// Optional, if omitted "Filter:" is used
    $param['filter_placeholder'] = "Type some letters";	// Optional, if omitted "Enter keywords" is used
    return $param;
}

/**
* Override user field values (userdata) by url $_GET values
* Values should be urlencoded
* e.g. http://example.com/make-a-booking/?name=Hakan+Ozevin&email=example@mail.com
* will prepopulate name and email fields as given
*/	
add_filter( 'app_userdata', 'app_userdata', 10, 2 );
function app_userdata( $data, $fields ) {
    foreach ( $fields as $f ) {
        if ( isset( $_GET[$f] ) )
            $data[$f] = urldecode($_GET[$f]);
    }
    return $data;
}

/**
* Override UDF values by url $_GET values
* Values should be urlencoded
* e.g. http://example.com/make-a-booking/?udf_4=Free+Gift&udf_5=My+best+friend
* or
* http://example.com/make-a-booking/?gift=Free+Gift&for=My+best+friend
* will prepopulate UDF_4 field as "Free Gift" and UDF_5 field as "My best friend"
*/	
add_filter( 'app_udf_value', 'app_udf_value', 10, 3 );
function app_udf_value( $value, $udf, $app_id ) {
    $udf_id = $udf['ID'];
    if ( isset( $_GET['udf_'.$udf_id] ) )
        $value = urldecode($_GET['udf_'.$udf_id]);
    
    // Or, if you already know that udf_4 is for gift name and udf_5 is for the recipient:
     if ( $udf_id == 4 && isset( $_GET['gift'] ) )
         $value = urldecode($_GET['gift']);
     else if ( $udf_id == 5 && isset( $_GET['for'] ) )
         $value = urldecode($_GET['for']);
    
    return $value;
}

/**
* This is a function which can be used in advanced pricing. Call it like:
* RESULT = my_special_price(SERVICE_ID);
* @return integer|float|string (WP BASE accepts all and sanitizes/formats price)
*/
function my_special_price( $service_id ){
    return 10 + $service_id*100;
}

/**
* If user is logged in or his fields are already prepopulated
* Hide confirmation form client fields
* @return array
*/
add_filter( 'app_pre_confirmation_reply', 'app_hide_user_fields', 10, 2 );
function app_hide_user_fields( $reply, $val_arr ) {
    $reply['extra_js'] = 'if ($(".app-email-field-entry").val() != "" ){$("form.app-conf-client-fields").hide();}';
    return $reply;
}

/**
* Create a user and make him Service Provider (worker)
* If user is already WP user, assign him as worker
* @return integer|false
*/
// add_action( 'admin_init', 'app_add_worker' );
function app_add_worker(){
    if ( $id = wpb_create_user( array('email'=>'hakan+test4@wp-base.com','name'=>'Hakan Ozevin') ) )
        wpb_add_worker( array( 'ID'=>$id, 'price'=>'$1.234,56','service'=>'1,2, 3,4,5, 6,7,8, 10 ,' ) );
 
}

/**
* Use this carefully
* It will create a booking at every visit to the website
* @return integer|false
*/
// add_action( 'wp_loaded', 'app_add_booking', 2000 );
function app_add_booking(){
    wpb_add_booking( array( 'service'=>10, ) );
}
