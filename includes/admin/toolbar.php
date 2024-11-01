<?php
/**
 * WPB Admin Toolbar
 *
 * Adds WP BASE items to admin toolbar
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBToolbar' ) ) {

class WpBToolbar {
	
	/**
     * WP BASE Core + Front [+Admin] instance
     */
	protected $a = null;
	
	/**
     * Constructor
     */
	function __construct() {
		$this->a = BASE();
	}
	
	/**
     * Add hooks
     */
	function add_hooks(){
		add_action( 'wp_before_admin_bar_render', array( $this, 'admin_bar_render'), 99 ); 				// Add items to admin toolbar
		add_action( 'permalink_structure_changed', array( $this, 'rebuild_menu' ) );					// Rebuild WP BASE admin menu
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 ); 									// Save post ids if it has shortcodes, also clear cache
		add_action( 'delete_post', array( $this, 'delete_post' ) ); 									// Check if deleted post has shortcodes
		add_action( 'trash_post', array( $this, 'delete_post' ) ); 										// Check if deleted post has shortcodes
	}
	
	/**
	 * Check if admin toolbar is active
	 * @since 3.0
	 * @return bool
	 */		
	function is_active(){
		return 'yes' === wpb_setting( 'admin_toolbar' );
	}

	 /**
	 * Add items to admin toolbar
	 * @since 2.0
	 * http://codex.wordpress.org/Function_Reference/add_node
	 */	
	function admin_bar_render() {
		if ( is_network_admin() || !$this->is_active() )
			return;
		
		if ( current_user_can( WPB_ADMIN_CAP ) ) {
			$this->_admin_bar_render( __('WP BASE','wp-base') ); // Parent item
			$this->_admin_bar_render( __('Bookings','wp-base'), admin_url( 'admin.php?page=appointments' ), __('WP BASE','wp-base') );
			$this->_admin_bar_render( __('Schedules','wp-base'), admin_url( 'admin.php?page=app_schedules' ), __('WP BASE','wp-base') );
			$this->_admin_bar_render( __('Transactions','wp-base'), admin_url( 'admin.php?page=app_transactions' ), __('WP BASE','wp-base') );
			$this->_admin_bar_render( __('Monetary Settings','wp-base'), admin_url( 'admin.php?page=app_monetary' ), __('WP BASE','wp-base') );
			$this->_admin_bar_render( __('Display Settings','wp-base'), admin_url( 'admin.php?page=app_display' ), __('WP BASE','wp-base') );
			$this->_admin_bar_render( __('Business Settings','wp-base'), admin_url( 'admin.php?page=app_business' ), __('WP BASE','wp-base') );
			$this->_admin_bar_render( __('Global Settings','wp-base'), admin_url( 'admin.php?page=app_settings' ), __('WP BASE','wp-base') );
			$this->_admin_bar_render( __('Addons','wp-base'), admin_url( 'admin.php?page=app_addons' ), __('WP BASE','wp-base') );
			$this->_admin_bar_render( __('Tools','wp-base'), admin_url( 'admin.php?page=app_tools' ), __('WP BASE','wp-base') );
			$this->_admin_bar_render( __('Help','wp-base'), admin_url( 'admin.php?page=app_help' ), __('WP BASE','wp-base') );
		}
		$make_pages = wpb_setting('make_page');

		if ( $make_pages ) {
			if ( !is_array( $make_pages ) ) {
				$page = get_post( $make_pages );
				$this->_admin_bar_render( $page->post_title, get_permalink($make_pages), __('WP BASE','wp-base'),array(),'app-make-page-'.$page->ID );
			}
			else {
				$i=0;
				$items = array();
				foreach ( $make_pages as $page_id ) {
					$page = get_post( $page_id );
					if ( is_object( $page ) && ( 'post' == $page->post_type || 'page' == $page->post_type ) ) {
						$i++;
						$items[$page_id] = $page->post_title;
					}
					// Limit Make pages
					if ( $i >= 20 )
						break;
				}
				asort( $items );
				foreach ( $items as $page_id=>$title ) {
					$this->_admin_bar_render( $title, get_permalink($page_id), __('WP BASE','wp-base'),array(),'app-make-page-'.$page_id );
				}
			}
		}
		if ( $page_id = wpb_setting('list_page') )
			$this->_admin_bar_render( __('List of Bookings','wp-base'), get_permalink($page_id), __('WP BASE','wp-base') );

		if ( $page_id = wpb_setting('all_page') )
			$this->_admin_bar_render( __('All Appointments','wp-base'), get_permalink($page_id), __('WP BASE','wp-base') );

		if ( $page_id = wpb_setting('manage_page') )
			$this->_admin_bar_render( __('Manage Bookings','wp-base'), get_permalink($page_id), __('WP BASE','wp-base') );
	}
	
	/**
	 * Add's menu parent or submenu item.
	 * @param string $name the label of the menu item
	 * @param string $href the link to the item (settings page or ext site)
	 * @param string $parent Parent label (if creating a submenu item)
	 * @since 2.0
	 * @return void
	 * */
	function _admin_bar_render( $name, $href = '', $parent = '', $custom_meta = array(), $id='' ) {

		if ( !$this->is_active() )
			return;
		
		global $wp_admin_bar;

		if ( !function_exists('is_admin_bar_showing') || !is_admin_bar_showing() || !is_object($wp_admin_bar) )
			return;

		// Generate ID based on the current filename and the name supplied.
		if ( !$id )
			$id = sanitize_key( basename(__FILE__, '.php' ) . '-' . $name );

		// Generate the ID of the parent.
		if ( $parent )
			$parent = sanitize_key( basename(__FILE__, '.php' ) . '-' . $parent );

		// links from the current host will open in the current window
		$meta = strpos( $href, site_url() ) !== false ? array() : array( 'target' => '_blank' ); // external links open in new tab/window
		$meta = array_merge( $meta, $custom_meta );

		$wp_admin_bar->add_node( array(
			'parent' => $parent,
			'id' => $id,
			'title' => $name,
			'href' => $href,
			'meta' => $meta,
		) );
	}	

	/**
	 * Check if saved post has shortcodes, clear cache and save post ids
	 * @since 2.0
	 * @return null
	 */		
	function save_post( $post_id, $post, $options=false ) {
		
		$post = get_post( $post_id );
		
		if ( 'revision' == $post->post_type || 'attachment' == $post->post_type )
			return;
		
		$options = wpb_setting();

		$changed = false;
		$content = $post->post_content;
		$status = $post->post_status;
		if ( strpos( $content, '[app_' ) !== false ) {
			// Also check if we have the shortcodes
			if ( has_shortcode( $content, 'app_manage' ) ) {
				$options['manage_page']	= $post_id;
				$changed = true;
			}
			else if ( has_shortcode( $content, 'app_confirmation' ) || has_shortcode( $content, 'app_book' ) ) {
				$options['make_page'][] = $post_id;
				$options['make_page'] = array_unique( array_filter( $options['make_page'] ) );
				$changed = true;
			}
			else if ( has_shortcode( $content, 'app_list' ) ) {
				$options['list_page'] = $post_id;
				$changed = true;
			}
		}
		
		// If shortcode cleared
		if ( wpb_setting('manage_page') == $post_id && ( !has_shortcode( $content, 'app_manage' ) || 'trash' == $status ) ) {
			unset( $options['manage_page'] );
			$changed = true;
		}

		$key = isset( $options['make_page'] ) ? array_search( $post_id, (array)$options['make_page'] ) : false;
		if ( $key !== false && ('trash' == $status || !(has_shortcode( $content, 'app_confirmation' ) || has_shortcode( $content, 'app_book' )) ) ) {
			unset( $options['make_page'][$key] );
			$changed = true;
		}

		if ( wpb_setting('list_page') == $post_id 
			&& ( !has_shortcode( $content, 'app_list' ) || 'trash' == $status ) ) {
			unset( $options['list_page'] );
			$changed = true;
		}

		if ( $changed ) {
			delete_transient( 'app_content_'. $post->ID );
			$this->a->pause_log = true;
			$this->a->update_options( $options );
			wpb_flush_cache();
			unset( $this->a->pause_log );
		}
		
	}

	/**
	 * Check if deleted post was in the menu
	 * @since 2.0
	 * @return none
	 */		
	function delete_post( $post_id ) {
		$options = wpb_setting();

		$changed = false;

		if ( wpb_setting('manage_page') == $post_id ) {
			unset( $options['manage_page'] );
			$changed = true;
		}
		
		$make_page = wpb_setting('make_page');

		if ( !is_array( $make_page ) ) {
			if ( $make_page == $post_id ) {
				unset( $options['make_page'] );
				$changed = true;
			}
		}
		else {
			foreach ( $make_page as $key=>$page ) {
				if ( $page == $post_id ) {
					unset( $options['make_page'][$key] );
					$changed = true;
				}
			}
		}

		if ( wpb_setting('all_page') == $post_id ) {
			unset( $options['all_page'] );
			$changed = true;
		}

		if ( wpb_setting('list_page') == $post_id ) {
			unset( $options['list_page'] );
			$changed = true;
		}

		if ( $changed ) {
			delete_transient( 'app_content_'. $post_id );
			$this->a->pause_log = true;
			$this->a->update_options( $options );
			wpb_flush_cache();
			unset( $this->a->pause_log );
		}
	}

	/**
	 * Rebuild menu items 
	 * @since 2.0
	 * @return none
	 */		
	function rebuild_menu( ) {
		$posts = get_posts( array( 'post_type' => 'any', 'posts_per_page'   => 10000 ) );
		foreach ( $posts as $_post ) {
			if ( is_object( $_post ) && isset( $_post->ID ) && $_post->ID ) {
				$this->save_post( $_post->ID, $_post );
			}
		}
	}

	
}
	BASE('Toolbar')->add_hooks();
}