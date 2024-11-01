<?php
/**
 * WPB Debug
 *
 * Tools for debugging for admin
 *
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBDebug' ) ) {
	
class WpBDebug {
	
    public static $app_debug_global = array();
	
    public static function set($key,$value){ 
        self::$app_debug_global[$key] = $value;
    }
     
    public static function get($key){
        return isset( self::$app_debug_global[$key] ) ? self::$app_debug_global[$key] : null;
    }
	
	public static function is_debug() {
		if ( 'yes' === wpb_setting('debug_mode') && BASE('User')->_current_user_can( WPB_ADMIN_CAP ) )
			return true;
		
		return false;
	}
	
	/**
	 * Create tooltip text for time slots
	 * ● is black circle character in https://en.wikipedia.org/wiki/Interpunct
	 * @return string
	 */	
	public static function time_slot_tt($slot) {
		$tt = '';
		if ( !self::is_debug() )
			return;
		
		if ( BASE('Timezones') ) {
			$tt .= '●TZ &rarr; ' . BASE('Timezones')->get_user_pref() . ' ● ';
			if ( BASE('Timezones')->is_enabled() )
				$tt .= __('Server time','wp-base'). ' &rarr; '. date_i18n(BASE()->dt_format, $slot->get_start() + BASE()->get_padding($slot->get_service())*60 ) ;
			else
				$tt .= __('Time zone handling disabled','wp-base') ;
		}

		if ( null !== self::get('time_var_rule') ) {
			$tt .= '●Applied Time Variant Durations rule: ' . self::get('time_var_rule');
			self::set('time_var_rule', null);
		}
		
		if ( $tt )
			return 'WP BASE debug:●' . trim( $tt, ' ● ' ) ;
	}
	
	/**
	 * Create tooltip text for new price slot (Discounted price)
	 * @return string
	 */	
	public static function price_tt() {
		$tt = '';
		if ( !self::is_debug() )
			return;
		
		if ( null !== self::get('package_price') ) {
			$tt .= '●P. Base price: ' . wpb_format_currency( '', self::get('package_price') );
			// self::set( 'base_price', null );
		}
		else if ( null !== self::get('base_price') ) {
			$tt .= '●Last item price: ' . wpb_format_currency( '', self::get('base_price') );
			// self::set( 'base_price', null );
		}		
		if ( null !== self::get('var_dur') ) {
			$tt .= '●Item price after Time Variant Durations: ' . wpb_format_currency( '', self::get('var_dur') );
			self::set( 'var_dur', null );
		}
		if ( null !== self::get('sel_dur') ) {
			$tt .= '●Item price after Selectable Durations: ' . wpb_format_currency( '', self::get('sel_dur') );
			self::set( 'sel_dur', null );
		}
		if ( null !== self::get('ep') ) {
			$tt .= '●Item price after Easy Custom Pricing: ' . wpb_format_currency( '', self::get('ep') );
		}
		
		if ( null !== self::get('price_before_confirmation_filter') ) {
			$tt .= '●Total price before Coupons & Extras: ' . wpb_format_currency( '', self::get('price_before_confirmation_filter') );
		}

		if ( 'yes' != BASE()->get_options('apply_coupon_to_extras') ) {
			if ( null !== self::get('coupon') ) {
				list( $max_discount, $latest_id ) = explode( ' ● ',  self::get('coupon') );
				$tt .= '●Discount by coupon ' . $latest_id . ': -'. wpb_format_currency( '', $max_discount );
				self::set( 'coupon', null );
			}
			if ( null !==  self::get('after_coupons') ) {
				$tt .= '●After Coupons: '. wpb_format_currency( '', self::get('after_coupons' ) );
				self::set( 'after_coupons', null );
			}
		}
		
		if ( null !== self::get('extras') ) {
			$extras = self::get('extras');
			foreach ( $extras as $id=>$e ) {
				$tt .= ' ● ' .esc_attr( wp_unslash( $e['name'] ) ). ': '. wpb_format_currency( '', $e['price'] );
			}
			// self::set( 'extras', null );
		}
		
		if ( null !== self::get('after_extras') ) {
			$tt .= '●Price with extras: ' . wpb_format_currency( '', self::get('after_extras') );
			// self::set( 'after_extras', null );
		}

		if ( 'yes' == BASE()->get_options('apply_coupon_to_extras') ) {
			if ( null !== self::get('coupon') ) {
				list( $max_discount, $latest_id ) = explode( ' ● ',  self::get('coupon') );
				$tt .= '●Discount by coupon ' . $latest_id . ': -'. wpb_format_currency( '', $max_discount );
				self::set( 'coupon', null );
			}
			if ( null !== self::get('after_coupons') ) {
				$tt .= '●After Coupons: '. wpb_format_currency( '', self::get('after_coupons' ) );
				self::set( 'after_coupons', null );
			}
		}
		if ( null !== self::get('after_seats') ) {
			$nof_seats = self::get( 'nof_seats' ) ? self::get( 'nof_seats' ) : 1;
			$tt .= '●After Group Bookings ('.$nof_seats.'): ' . wpb_format_currency( '', self::get('after_seats') );
			self::set( 'after_seats', null );
			self::set( 'nof_seats', null );
		}
		if ( null !== self::get('after_recurring') ) {
			$repeat = self::get( 'repeat' ) ? self::get( 'repeat' ) : 1;
			$tt .= '●After Recurring ('.$repeat.'): ' . wpb_format_currency( '', self::get('after_recurring') );
			self::set( 'after_recurring', null );
			self::set( 'repeat', null );
		}
		if ( null !== self::get('ap') ) {
			$tt .= '●After Advanced Custom Pricing: ' . wpb_format_currency( '', self::get('ap') );
			self::set( 'ap', null );
		}
		if ( null !== self::get('final_price') ) {
			$tt .= '●Final price: ' . wpb_format_currency( '', self::get('final_price') );
			self::set( 'final_price', null );
		}
		
		if ( $tt )
			return 'WP BASE debug:●' . trim( $tt, ' ● ' ) ;		
	}

	/**
	 * Return a debug text for admin
	 * @param $title:	string	Set a custom title 
	 */
	public static function debug_text( $text, $title=false ) {
		if ( self::is_debug() ) {
			$title = $title ? $title : esc_html(__( 'Debug texts are only visible to admins. You can turn them off on Global Settings > Advanced', 'wp-base' ) );
			return '<abbr class="app-debug" data-titlebar="true" data-title="WpB Debug" title="' . $title . '" >' 
					. esc_html( __( 'WpB Debug:', 'wp-base' ) ) . '</abbr> '. $text;
		}
		else
			return '';
	}

	/**
	 * Return a debug text for admin to be used in tooltip, explaining why a slot is not available
	 * @param $code		Reason code coming from class WpBSlot
	 * @return string
	 */
	public static function display_reason( $code ) {
		if ( ! self::is_debug() )
			return '';
		
		switch ( $code ) {
			case 1:		$reason = __( 'Providers on this time slot are busy', 'wp-base' ); break;
			case 2:		$reason = __( 'There are no providers for this time slot', 'wp-base' ); break;
			case 3:		$reason = __( 'Not enough time to accept an appointment here: Either there is another appointment or break in the proceeding slots', 'wp-base' ); break;
			case 4:		
			case 5:		$reason = __( 'There is a break on this time slot', 'wp-base' ); break;
			case 6:		$reason = __( 'Proceeding days not available', 'wp-base' ); break;
			case 7:		$reason = __( 'Location capacity full', 'wp-base' ); break;
			case 8:		$reason = __( 'Service is marked as not working for this time slot', 'wp-base' ); break;
			case 9:
			case 10:	$reason = __( 'Time slot is holiday for the selected provider', 'wp-base' ); break;
			case 11:	$reason = __( 'Time slot is blocked due to Lead Time setting', 'wp-base' ); break;
			case 12:	$reason = __( 'Time slot is of a past time for today', 'wp-base' ); break;
			case 13:	$reason = __( 'Time slot is of a past date', 'wp-base' ); break;
			case 14:	$reason = __( 'Time slot exceeds Booking Submission Upper Limit setting', 'wp-base' ); break;
			case 15:	$reason = __( 'Service not published', 'wp-base' ); break;
			case 16:	$reason = __( 'This slot is here just to display another one in the same row.', 'wp-base' ); break;
			case 17:	$reason = __( 'Unavailability reason cannot be determined', 'wp-base' ); break;
			case 18:	$reason = __( 'Slot is unavailable due to custom function', 'wp-base' ); break;
			default:	$reason = __( 'Not possible to book. No further detail available', 'wp-base' ); break;
		}
		
		return __('WpB Debug:','wp-base' ). ' '. $reason;
	}

}
}