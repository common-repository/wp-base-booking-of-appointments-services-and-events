<?php
/**
 * WPB Multi Site
 *
 * Methods specific to multi site
 *
 * Adapted from WP Core
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */	

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBMU' ) ) {

class WpBMU {
	
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
	
	function add_hooks(){
		add_action ( 'init', array( $this, 'check_install' ), 1 );
		add_action( 'remove_user_from_blog', array( $this, 'remove_user_from_blog' ) );			// Remove his records only for that blog
		add_action( 'delete_blog', array( $this, 'delete_blog' ), 10, 2);						// Uninstall tables for a deleted blog
	}
	
	function check_install() {
		WpBInstall::auto_install();	// Check blogs and install tables when there is traffic
	}
	
	/**
	 * Removes a worker's database records in case he is removed from that blog
	 * @param ID: user ID
	 * @param blog_id: ID of the blog that user has been removed from
	 * @since 1.2.3
	 */
	function remove_user_from_blog( $ID, $blog_id='' ) {
		if ( !$ID || !$blog_id )
			return;
	
		global $wpdb;
		
		if ( !method_exists( $wpdb, 'get_blog_prefix' ) )
			return;
			
		$prefix = $wpdb->get_blog_prefix( $blog_id );
		
		if ( !$prefix )
			return;
		
		$r1 = BASE('WH')->remove( $ID, 'worker' );
		
		// Also modify app table: Assign users work to general staff
		$r2 = $wpdb->update( $prefix . "base_bookings",
						array( 'worker'	=>	0 ),
						array( 'worker'	=> $ID )
					);
					
		if ( $r1 || $r2 )
			wpb_flush_cache();
	}

	/**
	 * Remove tables for a deleted blog
	 * @since 1.0.2
	 */	
	function delete_blog( $blog_id, $drop ) {
		
		if ( $blog_id >1 ) {
			switch_to_blog( $blog_id );
			wpb_uninstall_s( $blog_id );
			restore_current_blog();
		}
	}
	
}

	BASE('MU')->add_hooks();
}
