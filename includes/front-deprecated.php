<?php
/**
 * WPB Deprecated
 *
 * Deprecated shortcodes
 *
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;
 
if ( !class_exists( 'WpBDeprecated' ) ) {
	 
class WpBDeprecated {

	/**
     * Add actions and filters
     */
	function add_hooks() {
		add_shortcode( 'app_service_providers', array($this,'service_providers') );				// Deprecated in favor of app_workers
		add_shortcode( 'app_all_appointments', array($this,'all_appointments') );				// Deprecated. Keeping this for backwards compatibility
		add_shortcode( 'app_my_appointments', array($this,'my_appointments') );					// Deprecated. Keeping this for backwards compatibility
		add_shortcode( 'app_paypal', array($this,'paypal') );									// Deprecated. Keeping this for backwards compatibility
		add_shortcode( 'app_pagination', array($this,'pagination') );							// Deprecated. Keeping this for backwards compatibility
	}
	
	/**
	 * Shortcode showing service providers (Deprecated)
	 * @since 1.0
	 * @until 3.0
	 */
	function service_providers( $atts ) {
		return WpBDebug::debug_text( 'Service Providers Shortcode has been deprecated. Please replace it with Workers Shortcode.', 'wp-base' );
	}

	/**
	 * Shortcode showing all appointments (Deprecated)
	 * @since 1.2.7
	 * @until 2.0
	 */
	function all_appointments( $atts ) {
		_deprecated_function( __FUNCTION__, '2.0', 'listing' );
		return WpBDebug::debug_text( 'All Appointments Shortcode has been deprecated. Please replace it with List of Bookings Shortcode.', 'wp-base' );
	}
	
	/**
	 * Shortcode showing user's or worker's appointments (Deprecated)
	 * @until 2.0
	 */
	function my_appointments( $atts ) {
		_deprecated_function( __FUNCTION__, '2.0', 'listing' );
		return WpBDebug::debug_text( 'My Appointments Shortcode has been deprecated. Please replace it with List of Bookings Shortcode.', 'wp-base' );
	}
	
	/**
	 * Deprecated as of V2.0. 
	 * Now it is a part of payment gateway system:
	 * includes/addons/paypal-standard.php
	 */
	function paypal( $atts ) {
		return WpBDebug::debug_text( 'This shortcode has been deprecated. Remove this shortcode and use PayPal Standard Addon.', 'wp-base');
	}
	
	/**
	 * Shortcode for pagination (Deprecated)
	 * @since 1.0
	 * @until 3.0
	 */
	function pagination( $atts ) {
		return WpBDebug::debug_text( 'Pagination Shortcode has been deprecated. Please replace it with app_next Shortcode.', 'wp-base' );
	}

	 
}
	BASe('Deprecated')->add_hooks();
}