<?php
/**
 * Default filters and constants
 *
 *
 * @since		3.0
 * @package 	WP BASe
 * @author  	Hakan Ozevin
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPB_URL', 'https://wp-base.com/' );
define( 'WPB_DEMO_WEBSITE', 'http://demo.wp-base.com/' );	
define( 'WPB_ADDON_DEMO_WEBSITE', 'http://addons.wp-base.com/' );	
define( 'WPB_NAME', __('WP BASE', 'wp-base') ); 					# Name that will be used on admin side.

if ( !defined( 'WPB_GCAL_SERVICE_ID' ) )
	define( 'WPB_GCAL_SERVICE_ID', -1 );

if ( !defined( 'WPB_ADMIN_CAP' ) )
	define( 'WPB_ADMIN_CAP', 'manage_options' );

if ( !defined( 'WPB_TUTORIAL_CAP' ) )
	define( 'WPB_TUTORIAL_CAP', 'manage_options' );

if ( !defined( 'WPB_HARD_LIMIT' ) )
	define( 'WPB_HARD_LIMIT', 10.0 );								# Hard limit set to 10 sec

if ( !defined( 'WPB_HUGE_NUMBER' ) )
	define( 'WPB_HUGE_NUMBER', 473000000 );							# This allows strtotime calculations for next 15 years (32 bit PHP can calculate upto year 2038)

if ( !defined( 'WPB_PRELOAD_TIMEOUT' ) )
	define( 'WPB_PRELOAD_TIMEOUT', 20 );							# Timeout set to 20 seconds

if ( !defined( 'WPB_DB_DOMINANT_EDGE' ) )
	define( 'WPB_DB_DOMINANT_EDGE', 50 );							# After this number of services/providers, WP BASE uses mysql dominantly

if ( !defined( 'WPB_DEFAULT_LSW_PRIORITY' ) )
	define( 'WPB_DEFAULT_LSW_PRIORITY', 'SLW' );					# How menus will be displayed, e.g. SLW means: Service > Location > Worker
																	# See class.menu.php
if ( !defined( 'WPB_PRICE_MISMATCH_ACTIVE' ) )
	define( 'WPB_PRICE_MISMATCH_ACTIVE', false );					# For future
																	

add_action( 'update_option_gmt_offset', 'wpb_flush_cache' );
add_action( 'update_option_start_of_week', 'wpb_flush_cache' );
add_action( 'update_option_timezone_string', 'wpb_flush_cache' );
add_action( 'app_time_base_tick', 'wpb_flush_cache' );				# Clear cache at every time base tick

