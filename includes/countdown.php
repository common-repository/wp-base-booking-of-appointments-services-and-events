<?php
/**
 * WPB Countdown
 *
 * Creates a configurable countdown
 *
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

/*

@goto is the page that visitor will be redirected to when countdown expires. Default is null (No redirection).
Tip: Just to refresh the current page (e.g. letting other plugins to redirect the visitor or cleaning the countdown) enter: window.location.href

@title: Show event title. Supported values: "yes" or "no"
If set to "yes", the event countdown will also include the event title.

*/

if ( !class_exists( 'WpBCountDown' ) ) {

class WpBCountDown {

	var $nof_countdown = 0;

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

	/**
	 * Hooks 
	 */	
	public function add_hooks () {
		add_shortcode( 'app_countdown', array( $this, 'shortcode') );
		add_action( 'app_load_assets', array( $this, 'load_assets' ), 14 );			// Add js files to front end
	}

	/**
	 * Load scripts
	 */		
	function load_assets() {
		if ( !WpBDebug::is_debug() )
			return;
		wp_enqueue_script( 'jquery-plugin', $this->a->plugin_url . '/js/jquery.plugin.min.js', array('jquery'), $this->a->version );
		wp_enqueue_script( 'jquery-countdown', $this->a->plugin_url . '/js/jquery.countdown.min.js', array('jquery','jquery-plugin'), $this->a->version );
		if ( 'yes' != wpb_setting('disable_css') ) {
			wp_enqueue_style( "jquery-countdown", $this->a->plugin_url . "/css/jquery.countdown.css", array(), $this->a->version);
		}
	}

	/**
	 * Generate shortcode
	 */	
	function shortcode( $atts ) {
	
		extract( shortcode_atts( array(
		'id'		=> '',
		'mode'		=> 'countdown',			// If 'cart' show remaining time to checkout. If 'refresh' ordinary countdown to refresh page
		'silent'	=> 0,					// If true, do not display counter
		'status'	=> implode( ',', apply_filters( 'app_countdown_status', array('paid','confirmed','pending','running' ) ) ),
		'format'	=> 'dHMS',
		'goto'		=> 'window.location.href',
		'class'		=> '',
		'size'		=> 70,
		'minutes'	=> 0,					// Minutes to add for countdown and minutes to countdown for refresh
		'title'		=> $this->a->get_text('countdown_title'),
		'expired'	=> __('Time to attend!', 'wp-base')
		), $atts ) );
		
		// Give a unique ID to the wrapper
		if ( !$id ) {
			$this->nof_countdown = $this->nof_countdown + 1;
			$id = 'app_countdown_'. $this->nof_countdown;
		}

		$goto = trim( $goto );
			
		// Do not add quotes for page refresh
		if ( $goto && $goto != "window.location.href" )
			$goto = json_encode( $goto );

		switch ($size) {
			case 70:	$height = 72; break;
			case 82:	$height = 84; break;
			case 127:	$height = 130; break;
			case 254:	$height = 260; break;
			default:	$size = 70; $height = 72; break;
		}
		
		$cdate = date ("Y-m-d H:i:s", $this->a->local_time );
		
		if ( 'refresh' == $mode ) {
			$secs = 60*(int)$minutes;
		}
		else if ( 'cart' == $mode ) {
			// For example to be used in Woocommerce Checkout page
			$ids = BASE('Multiple')->get_cart_items();
			if ( empty( $ids ) )
				return;
			
			if ( wpb_setting("clear_time_pending_payment") > 0 )
				$clear_secs = wpb_setting("clear_time_pending_payment") * 60;
			else if ( wpb_setting("clear_time") > 0 )
				$clear_secs = wpb_setting("clear_time") * 60 *60;
			else
				return '';
			
			$query = 
				"SELECT *
				FROM {$this->a->app_table}
				WHERE created>'" . date ("Y-m-d H:i:s", $this->a->local_time - (int)$clear_secs ). "'
				AND	ID IN (". implode( ',', $ids ) .")
				AND (status='cart' OR status='hold')		
				ORDER BY created ASC
				LIMIT 1
				";
				
			$result = $this->a->db->get_row( $query );
			
			// Find how many seconds left
			if ( !$result )
				return ''; 
			else
				$secs = strtotime( $result->created ) - current_time('timestamp') + $clear_secs;
		}
		else {
			if ( !is_user_logged_in() )
				return;

			// Set status clause
			$statuses = explode( ',', $status );
			if ( !is_array( $statuses ) || empty( $statuses ) )
				return $this->a->debug_text( __('Check "status" parameter in Countdown shortcode','wp-base') ) ;

			// Check for 'all'
			if ( in_array( 'all', $statuses ) )
				$stat = '1=1';
			else {
				$stat = '';
				foreach ( $statuses as $s ) {
					// Allow only defined stats
					if ( array_key_exists( trim( $s ), $this->a->get_statuses() ) ) 
						$stat .= " status='".trim( $s )."' OR ";
				}
				$stat = rtrim( $stat, "OR " );
			}
			
			$cuid = get_current_user_id();
			
			$query =
				$this->a->db->prepare(			
				"SELECT *
				FROM {$this->a->app_table}
				WHERE start > '".date ("Y-m-d H:i:s", $this->a->_time )."'
				AND	(user=%d OR worker=%d)
				AND ({$stat})		
				ORDER BY start ASC
				LIMIT 1
				",$cuid, $cuid );
			$result = $this->a->db->get_row( $query );
			
			// Find how many seconds left to the event
			if ( !$result )
				return ''; 
			else
				$secs = strtotime( $result->start ) - current_time('timestamp') + 60 * (int)$minutes;
		}

		$script  = '';
		$script .= "$('#".$id."').countdown({
					format: '".$format."',
					expiryText: '".$expired ."',
					until: ".$secs.","
		;
		if ($goto) {
			$script .= "onExpiry: function(){window.location.href=".$goto.";},";
		}
		
		$script .= "onTick: function () { },";
	
		$script .= "alwaysExpire: true});";

		$this->a->add2footer( $script );
		
		$silent_style = $silent ? ' style="display:none" ' : '';
		$title_html = trim( $title ) ? '<div '.$silent_style.' class="app_countdown_dropdown_title app_title">' . $title . '</div>' : '';

		$markup = '<div class="app-sc app_countdown-wrapper">' . $title_html . 
			"<div id='{$id}' class='app_countdown {$class}' data-height='{$height}' data-size='{$size}'></div>" .
		'</div>' . "<div style='clear:both'></div>";

		return $markup;
	}
	

}

	BASE('Countdown')->add_hooks();
}

