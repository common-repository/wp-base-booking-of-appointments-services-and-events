<?php
/**
 * WPB LSW Normalize
 *
 * Normalizes lsw (location/service/worker) values
 *
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBNorm' ) ) {

class WpBNorm {
	
	public $location, $service, $worker;
	
	public $show_locations_menu	= true;
	public $show_services_menu	= true;
	public $show_workers_menu	= true;
	public $display_errors		= true;
	
	public $error = array();
	
	protected $a;
	
	/**
	 * Constructor
	 * @param	$location		integer|string		Set location ID or 'auto' or location name
	 * @param	$service		integer|string		Set service ID or 'all' or 'auto' or service name
	 * @param	$worker			integer|string		Set worker ID or 'all' or 'auto'
	 * @param	$update_lsw		bool				Whether update lsw. Set to true only for standalone situations, e.g. app_recurring shortcode when used alone
	 */
	function __construct( $location, $service, $worker, $update_lsw=false ) {
		$this->a = BASE();
		
		if ( 'all' === $service || 'all' === $worker ) {
			$this->service = 'all';
			$this->worker = 'all';
			$this->a->set_lsw( false, 'all', 'all' );
			return;
		}

		$this->normalize_location( $location );
		$this->normalize_service( $service );
		$this->normalize_worker( $worker );
		
		if ( $update_lsw )
			$this->a->set_lsw( $this->location, $this->service, $this->worker );
				
	}

	/**
	 * Normalize location ID, i.e if name is given find its ID
	 * @param $ID	integer|string		ID to be normalized
	 * @return 		integer				Location ID
	 */
	private function normalize_location( $ID ) {
		if ( !empty( $_GET['app_location_id'] ) && $this->a->location_exists( $_GET['app_location_id'] ) ) {
			$this->location = $_GET['app_location_id'];
			return;
		}		
		
		if ( '0' === (string)$ID || false === $ID ) {
			$this->location = $ID;
			return;
		}

		if ( is_numeric( $ID ) && $this->a->location_exists( $ID ) ) {
			$this->location = $ID;
			$this->show_locations_menu = false;
			return;
		}
		
		$maybe_id = false;
		if ( $maybe_id = $this->a->find_location_id_from_name( $ID ) )
			$ID = $maybe_id;
		else if ( 'auto' === $ID && $maybe_id = $this->a->find_location_for_page( ) )
			$ID = $maybe_id;

		if ( $maybe_id ) {
			$this->location = $maybe_id;
			return;
		}

		$this->error[] = sprintf( __( 'Location %s is not correct', 'wp-base' ), print_r( $ID, true ) );
	}
	
	/**
	 * Normalize service ID, i.e if name is given find its ID
	 * @param $ID	integer|string		ID to be normalized
	 * @return 		integer				Service ID
	 */
	private function normalize_service( $ID ) {
		if ( !empty( $_GET['app_service_id'] ) && $this->a->service_exists( $_GET['app_service_id'] ) ) {
			$this->service = $_GET['app_service_id'];
			return;
		}

		if ( '0' === (string)$ID || false === $ID ) {
			$this->service = $ID;
			return;
		}
		
		if ( ( is_numeric( $ID ) && $this->a->service_exists( $ID ) ) ) {
			$this->service = $ID;
			$this->show_services_menu = false;
			return;
		}

		$maybe_id = false;
		if ( $maybe_id = $this->a->find_service_id_from_name( $ID ) )
			$ID = $maybe_id;
		else if ( 'auto' === $ID && $maybe_id = $this->a->find_service_for_page( ) )
			$ID = $maybe_id;
		
		if ( $maybe_id ) {
			$this->service = $maybe_id;
			return;
		}

		$this->error[] = sprintf( __( 'Service %s is not correct', 'wp-base' ), print_r( $ID, true ) );
	}
	
	/**
	 * Normalize worker ID, i.e if auto is selected find ID
	 * @param $ID	integer|string		ID to be normalized
	 * @return 		integer				Worker ID
	 */
	private function normalize_worker( $ID ) {
		if ( !empty( $_GET['app_worker_id'] ) && $this->a->worker_exists( $_GET['app_worker_id'] ) ) {
			$this->worker = $_GET['app_worker_id'];
			return;
		}			
		
		if ( '0' === (string)$ID || false === $ID  ) {
			$this->worker = $ID;
			return;
		}

		if ( is_numeric( $ID ) && $this->a->worker_exists( $ID ) ) {
			$this->worker = $ID;
			$this->show_workers_menu = false;
			return;
		}
		
		if ( 'auto' === $ID && $maybe_id = $this->a->find_worker_for_page( ) ) {
			$this->worker = $maybe_id;
			return;
		}

		$this->error[] = sprintf( __( 'worker %s is not correct', 'wp-base' ), print_r( $ID, true ) );
	}
	
	/**
	 * Display errors
	 * @return string
	 */
	public function display_errors(){
		if ( empty( $this->error ) || !$this->display_errors || !WpBDebug::is_debug() )
			return;
		
		$out = '<ul>';
		foreach ( $this->error as $error ) {
			$out .= '<li>'. $error . '</li>';
		}

		$out .= '</ul>';
		$out .= '<div class="clearfix" style="clear:both"></div>';
		
		return $out;
	}
}
}