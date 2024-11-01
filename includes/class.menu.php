<?php
/**
 * WPB LSW Menu Controller
 *
 * Controls menu behaviour accounting for priority (variable leader/follower control)
 * Cuts out related subset of locations/services/workers 
 *
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBMenu' ) ) {

class WpBMenu {
	
	public $set_location, $set_service, $set_worker, $req_location, $req_service, $req_worker, $order_by, $norm, $priority;
	
	public $locations, $services, $workers;
	
	public $display_errors = true;
	
	private $error = array();
	
	private $force_priority;
	
	protected $a;
	
	# These will be used for priority comparisons
	# Just for better readability
	const LOCATION	= 'L';
	const SERVICE 	= 'S';
	const WORKER	= 'W';
	
	/**
	 * Constructor
	 * @param	$norm			object		WpBNorm instance
	 * @param	$order_by		string		How menus will be ordered
	 * @param	$force_priority	string		Change priority by instance, e.g. in admin inline edit
	 */
	function __construct( $norm, $order_by = "sort_order", $force_priority = false ) {
		$this->a = BASE();
		$this->norm					= $norm;
		$this->req_location			= $norm->location;
		$this->req_service			= $norm->service;
		$this->req_worker			= $norm->worker;
		$this->error				= $norm->error;
		$this->order_by				= $order_by;
		$this->force_priority		= $force_priority;
		
		if ( 'all' === $this->req_service )
			return;
		
		foreach ( str_split( $this->get_priority() ) as $lsw ) {
			switch( $lsw ) {
				case self::LOCATION:	$this->adjust_location();	break;
				case self::SERVICE:		$this->adjust_service();	break;
				case self::WORKER:		$this->adjust_worker();		break;
			}
		}
	}
	
	/**
	 * Whether display locations menu
	 * @return bool
	 */
	public function show_locations_menu(){
		return $this->norm->show_locations_menu && $this->is_locations_active();
	}
	
	/**
	 * Whether display services menu
	 * @return bool
	 */
	public function show_services_menu(){
		return $this->norm->show_services_menu;
	}

	/**
	 * Whether display locations menu
	 * @return bool
	 */
	public function show_workers_menu(){
		return $this->norm->show_workers_menu && $this->is_workers_active();
	}
	
	/**
	 * Whether locations is active
	 * @return bool
	 */
	private function is_locations_active(){
		return class_exists( 'WpBLocations' ) && $this->a->get_nof_locations();
	}

	/**
	 * Whether workers is active
	 * @return bool
	 */
	private function is_workers_active(){
		return class_exists( 'WpBSP' ) && $this->a->get_nof_workers();
	}

	/**
	 * Get a code showing priority among Location (L), Service (S), Worker (W)
	 * e.g. SLW means Service > Location > Worker
	 * @return 		string		Priority coded with 3 letters for Location (L), Service (S), Worker (W)
	 */
	private function get_priority(){
		if ( $this->force_priority )
			return $this->force_priority;
		
		if ( !empty( $this->priority ) )
			return $this->priority;
		
		return $this->priority = apply_filters( 'app_lsw_priority', wpb_setting( 'lsw_priority', WPB_DEFAULT_LSW_PRIORITY ), $this ); # default is in defaults.php
	}
	
	/**
	 * Check which variable has priority based on lsw_priority setting
	 * @param $first	string		L, S or W
	 * @param $second	string		L, S or W
	 * @return 			bool		true if $first is on the left of $second
	 */
	private function compare( $first, $second ) {
		$priority = $this->get_priority();
		return strpos( $priority, $first ) < strpos( $priority, $second );
	}
	
	/**
	 * Helper to check if no preference (i.e. worker=0) is allowed to stay
	 * @return bool
	 */
	private function no_pref_can_stay( ){
		if ( $this->force_priority )
			return false;
		
		return ('0' === (string)$this->a->get_wid()) && 'auto' === wpb_setting( 'client_selects_worker' ) && defined( 'WPB_AJAX' ) && WPB_AJAX;
	}
	
	/**
	 * Helper to get location or service or worker property value
	 * @return mix
	 */
	private function lsw( $context ) {
		if ( 'location' == $context )
			return $this->a->get_lid();
		
		if ( 'service' == $context )
			return $this->a->get_sid();
		
		if ( 'worker' == $context )
			return $this->a->get_wid();

	}
	
	/**
	 * Helper to preselect a "follower", e.g. when service has first priority, worker is a follower
	 * @param $context	string		location, service or worker
	 * @return none
	 */
	private function preselect_follower( $context ) {

		$vars = $this->{$context.'s'}; # e.g. $this->services
		
		$ids = !empty( $vars ) ? array_keys( $vars ) : array();
		$lsw = $this->lsw( $context );
	
		if ( $ids ) {
			if ( in_array( $this->{'req_'.$context}, $ids ) ) {
				$this->{'set_'.$context} = $this->{'req_'.$context};
				$this->{'show_'.$context.'_menu'} = false; # Force selection valid
			}
			else if ( $lsw && in_array( $lsw, $ids ) )
				$this->{'set_'.$context} = $lsw;
			else if ( 'worker' == $context && $this->no_pref_can_stay( ) )
				$this->{'set_'.$context} = 0;
			else 
				$this->{'set_'.$context} = key( $vars );
		}
		else
			$this->{'set_'.$context} = 0;
	}
	
	/**
	 * Helper to preselect a "leader", i.e. lsw that has first priority
	 * @param $context	string		location, service or worker
	 * @return none
	 */
	private function preselect_leader( $context ) {
		$lsw = $this->lsw( $context );
		
		$this->{'set_'.$context} =	$this->{'req_'.$context} > 0 
									? 
									$this->{'req_'.$context}
									: 
									( $lsw ? $lsw : key( $this->{$context.'s'} ) );
	}
	
	/**
	 * Manage locations object 
	 * @return none
	 */
	private function adjust_location(){
		if ( false === $this->req_location ) {
			$this->set_location = false;
			return;
		}

		if ( !class_exists( 'WpBLocations' ) ) {
			$this->set_location = false;
			return;
		}
		
		if ( $this->compare( self::SERVICE, self::LOCATION ) ) {
			
			$this->locations = $this->a->get_locations_by_service( $this->set_service, $this->order_by );
			
			if ( $this->is_workers_active() && $this->compare( self::WORKER, self::LOCATION ) )
				$this->locations = array_intersect_key( (array)$this->locations, (array)$this->a->get_locations_by_worker( $this->set_worker, $this->order_by ) );
			
			$this->preselect_follower( 'location' );
		}
		else if ( $this->is_workers_active() && $this->compare( self::WORKER, self::LOCATION ) ) {
			$this->locations = $this->a->get_locations_by_worker( $this->set_worker, $this->order_by );
		
			$this->preselect_follower( 'location' );
		}
		else {
			$this->locations = $this->a->get_locations( $this->order_by );
			$this->preselect_leader( 'location' );
		}
		
		$this->a->set_lsw( $this->set_location, false, false );
	}
	
	/**
	 * Manage services object
	 * @return none
	 */
	private function adjust_service(){
		if ( false === $this->req_service ) {
			$this->set_service = false;
			return;
		}
	
		if ( $this->is_locations_active() && $this->compare( self::LOCATION, self::SERVICE ) ) {
			
			$this->services = $this->a->get_services_by_location( $this->set_location, $this->order_by );
		
			if ( $this->is_workers_active() && $this->compare( self::WORKER, self::SERVICE ) )
				$this->services = array_intersect_key( (array)$this->services, (array)$this->a->get_services_by_worker( $this->set_worker, $this->order_by ) );
			
			$this->preselect_follower( 'service' );
		}
		else if ( $this->is_workers_active() && $this->compare( self::WORKER, self::SERVICE ) ) {
			$this->services = $this->a->get_services_by_worker( $this->set_worker, $this->order_by );
			$this->preselect_follower( 'service' );
		}
		else {
			$this->services = $this->a->get_services( $this->order_by );
			$this->preselect_leader( 'service' );
		}
		
		$this->a->set_lsw( false, $this->set_service, false );
	}
	
	/**
	 * Manage workers object
	 * @return none
	 */
	private function adjust_worker(){
		if ( false === $this->req_worker ) {
			$this->set_worker = false;
			return;
		}
	
		if ( !class_exists( 'WpBSP' ) ) {
			$this->set_worker = false;
			return false;
		}
		
		if ( $this->compare( self::SERVICE, self::WORKER ) ) {
			
			$this->workers = $this->a->get_workers_by_service( $this->set_service, $this->order_by );
			
			if ( $this->is_locations_active() && $this->compare( self::LOCATION, self::WORKER ) )
				$this->workers = array_intersect_key( (array)$this->workers, (array)$this->a->get_workers_by_location( $this->set_location, $this->order_by ) );
			
			$this->preselect_follower( 'worker' );
		}
		else if ( $this->is_locations_active() && $this->compare( self::LOCATION, self::WORKER ) ) {
			$this->workers = $this->a->get_workers_by_location( $this->set_location, $this->order_by );
			$this->preselect_follower( 'worker' );
		}
		else {
			$this->workers = $this->a->get_workers( $this->order_by );
			$this->preselect_leader( 'worker' );
		}

		$this->a->set_lsw( false, false, $this->set_worker );
		
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