<?php
/**
 * WPB Compat
 *
 * Provides classes to maintain compatibility for 3rd party plugins and software
 * 
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 *
 * Handles:
 * Automatic cleaning of page caches for selected Caching plugins
 * Also Automatic Adaptation for selected page builders & templates having WP BASE shortcodes
 *
 * In addition to the classes below, tested and already compatible with following page builders:
 * "Beaver Builder": https://www.wpbeaverbuilder.com/
 * "Elementor": https://elementor.com/
 * "Fusion Builder": https://avada.theme-fusion.com/fusion-builder-2/
 * "Layers": https://www.layerswp.com/
 * "Make": https://thethemefoundry.com/wordpress-themes/make/
 * "WP Bakery" (as text block): https://vc.wpbakery.com/
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBCache' ) ) {

class WpBCache {
	
	function __construct(){
		$this->a	= BASE();
	}
	
	protected function load_hooks(){
		add_action( 'app_flush_cache', array( $this, 'flush' ) );
	}
	
	/**
	 * Return ID's of all pages having a WP BASE shortcode
	 * @return array
	 */
	protected function get_page_ids(){
		$pages = BASE()->get_app_pages();
		
		return is_array( $pages ) ? array_keys( $pages) : array();
	}
	
	public function flush(){}
}
}

/*
 * Clean WP BASE related page caches created by W3 Total Cache 
 * 
 */
class WpBW3T extends WpBCache {
	
	function __construct(){
		parent::__construct();
	}
	
	public static function serve(){
		$me = new WpBW3T();
		$me->load_hooks();
	}

	/**
	 * @uses app_flush_cache action
	 */
	public function flush(){
		if ( !function_exists( 'w3tc_pgcache_flush_post' ) )
			return;
		
		$pages = $this->get_page_ids();
		# https://gist.github.com/avioli/0cfd7987a66f418ab563
		foreach ( (array)$pages as $page_id ) {
			w3tc_pgcache_flush_post( $page_id );
		}
	}
	
}

WpBW3T::serve();

/*
 * Clean WP BASE related page caches created by Super Cache 
 * 
 */
class WpBSuperCache extends WpBCache {
	
	function __construct(){
		parent::__construct();
	}
	
	public static function serve(){
		$me = new WpBSuperCache();
		$me->load_hooks();
	}
	
	/**
	 * @uses app_flush_cache action
	 */
	public function flush(){
		if ( !function_exists( 'wp_cache_post_change' ) )
			return;
		
		# http://z9.io/wp-super-cache-developers/
		$GLOBALS["super_cache_enabled"]=1;
		$pages = $this->get_page_ids();
		foreach ( (array)$pages as $page_id ) {
			wp_cache_post_change( $page_id );
		}
	}
	
}

WpBSuperCache::serve();

/*
 * Clean WP BASE related page caches created by WP-Rocket 
 * 
 */
class WpBRocket extends WpBCache {
	
	function __construct(){
		parent::__construct();
	}
	
	public static function serve(){
		$me = new WpBRocket();
		$me->load_hooks();
	}
	
	/**
	 * @uses app_flush_cache action
	 */
	public function flush(){
		if ( !function_exists( 'rocket_clean_post' ) )
			return;

		# Function is wp-rocket/inc/common/pure.php
		$pages = $this->get_page_ids();
		foreach ( (array)$pages as $page_id ) {
			rocket_clean_post( $page_id );
		}
	}
	
}

WpBRocket::serve();

/*
 * Clean WP BASE related page caches created by WP Fastest Cache 
 * 
 */
class WpBFastestCache extends WpBCache {
	
	function __construct(){
		parent::__construct();
	}
	
	public static function serve(){
		$me = new WpBFastestCache();
		$me->load_hooks();
	}
	
	/**
	 * @uses app_flush_cache action
	 */
	public function flush(){
		if ( !class_exists( 'WpFastestCache' ) )
			return;
		
		$instance = is_callable( $GLOBALS["wp_fastest_cache"], 'singleDeleteCache' ) ? $GLOBALS["wp_fastest_cache"] : new WpFastestCache();
		
		if ( !is_callable( $instance, 'singleDeleteCache' ) )
			return;
		
		$pages = $this->get_page_ids();
		foreach ( (array)$pages as $page_id ) {
			$instance->singleDeleteCache( false, $page_id );
		}
	}
	
}

WpBFastestCache::serve();


# Page Builders	
if ( !class_exists( 'WpBCompat' ) ) {

class WpBCompat {
	
	public function __construct(){
		$this->a = BASE();
	}
	
	protected function load_hooks(){
		add_filter( 'app_js_parameters', array( $this, 'js_parameters' ) );
		add_filter( 'app_post_content', array( $this, 'post_content' ), 10, 3 );
		add_filter( 'app_the_posts_content', array( $this, '_post_content' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'app_load_assets', array( $this, 'load_assets' ) );
		add_action( 'wp_print_scripts', array( $this, 'wp_print_scripts' ), 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 100 );
		// add_filter( 'the_posts', array( $this, 'the_posts' ), 10, 1 );
		add_filter( 'template_include', array( $this, 'template_check' ) );
		add_filter( 'embed_template', array( $this, 'template_check' ) );
		add_action( 'update_postmeta', array( $this, 'update_postmeta'), 10, 4 );
	}
	
	public function js_parameters( $param ) {
		return $param;
	}
	
	public function _post_content( $content, $post ) {
		return $this->post_content( $content, $post, false );
	}
	
	public function post_content( $content, $post, $ajax = false ) {
		return $content;
	}
	
	public function wp_print_scripts() {}
	public function wp_enqueue_scripts() {}
	public function admin_enqueue_scripts(){}
	public function load_assets(){}
	
	public function the_posts($posts) {
		return $posts;
	}

	public function template_check( $template ) {
		return $template;
	}
	
	public function update_postmeta( $meta_id, $object_id, $meta_key, $meta_value ) {}

}
}

/*
 * Support for Custom Templates
 * 
 */
class WpBCustomTemplate extends WpBCompat {
	
	private $template_content = array();
	
	public function __construct(){
		parent::__construct();
	}
	
	public static function serve(){
		$me = new WpBCustomTemplate();
		$me->load_hooks();
	}
	
	/*
	 * Retrieve template content from transient/class property
	 * @since 3.0
	 * @return string
	 */	
	public function post_content( $content, $post, $ajax = false ) {
		if ( !$ajax )
			return $content;
		
		if ( !empty( $post->ID ) ) {
			if ( !WpBDebug::is_debug() )
				$maybe_content = trim( get_transient( 'app_content_'. $post->ID ) ); 
			else 
				$maybe_content = !empty( $this->template_content[$post->ID] ) ? $this->template_content[$post->ID] : '';
			
			if ( strpos( $maybe_content, '[app_' ) !== false )
				$content .= $maybe_content;
		}
		
		return $content;
	}
	
	/*
	 * Support for templates having WP BASE shortcode: search for [app_
	 * Inspired from Robert's comment on: https://iandunn.name/2011/07/01/conditionally-loading-javascript-and-css-in-wordpress-plugins/
	 * @since 3.0
	 */	
	public function template_check( $template ) {
		$post = get_post();
		if ( empty( $post->ID ) )
			return $template;
		
		if ( !WpBDebug::is_debug() && get_transient( 'app_content_'. $post->ID ) )
			return $template;
		
		$found = false;
		$dir = get_stylesheet_directory() . DIRECTORY_SEPARATOR;
		$files = array( $template,  $dir . 'header.php', $dir . 'footer.php' );
		foreach( $files as $file ) {
			if( file_exists($file) ) {
				$contents = file_get_contents($file);
				if ( strpos( $contents, '[app_' ) !== false && strpos( $post->content, '[app_' ) === false ) {
					$found = true;
					if ( !WpBDebug::is_debug() )
						$this->template_content[$post->ID] = $contents;
					else
						set_transient( 'app_content_'. $post->ID, $contents, 24 * HOUR_IN_SECONDS );
					
					$this->a->add_default_js();
					$this->a->load_assets( );
					break;
				}
			}
		}
		if ( !WpBDebug::is_debug() && !$found )
			set_transient( 'app_content_'. $post->ID, ' ', 24 * HOUR_IN_SECONDS );
		
		return $template;
	}

	/*
	 * Delete transient on page_template change
	 * @uses action update_postmeta
	 * @since 3.0
	 */	
	public function update_postmeta( $meta_id, $object_id, $meta_key, $meta_value ) {
		if ( '_wp_page_template' != $meta_key )
			return;
		
		delete_transient( 'app_content_'.$object_id );
	}
	
}
WpBCustomTemplate::serve();


/*
 * Support for Divi
 * 
 */
class WpBDivi extends WpBCompat {
	
	public function __construct(){
		parent::__construct();
	}
	
	public static function serve(){
		$me = new WpBDivi();
		$me->load_hooks();
	}
	
	public function wp_print_scripts() {
		if ( wpb_is_mobile() ) {
			wp_dequeue_script( 'et-jquery-touch-mobile' );
			wp_dequeue_script( 'et-builder-modules-script' );
		}
	}
	
	public function post_content( $content, $post, $ajax = false ) {
		if ( !function_exists('et_builder_load_framework') )
			return $content;
		
		if ( strpos( $content, '[app_' ) === false )
			return $content;
		
		# Divi css rules overrides WP BASE
		# Prevent this by removing ID's added by Divi
		# If needed, css modifications should be done in wp-content/uploads/__app/front.css
		$this->a->add2footer( '
			var et_sc = $(document).find(".app-sc");
			$.each( et_sc, function(i,v) {
				var et_par = $(this).parents(\'div[id^="et_"]\');
				$.each( et_par, function(i,v){
					$(this).attr("id","");
				});
			});' );
			
		return $content;
	}
	
}
WpBDivi::serve();

/*
 * Support for WooCommerce
 * 
 */
class WpBWoo extends WpBCompat {
	
	public function __construct(){
		parent::__construct();
	}
	
	public static function serve(){
		$me = new WpBWoo();
		$me->load_hooks();
	}
	
	public function post_content( $prev_content, $post, $ajax = false ) {
		$content = (isset( $post->post_excerpt ) ? $post->post_excerpt : '') .' '. (isset( $post->post_content ) ? $post->post_content : '');
		return $prev_content . ' ' . $content;
	}
	
}
// WpBWoo::serve();

/*
 * Support for Live Composer
 * 
 */
class WpBWPBakery extends WpBCompat {
	
	public function __construct(){
		parent::__construct();
	}
	
	public static function serve(){
		$me = new WpBWPBakery();
		$me->load_hooks();
	}
	
	public function the_posts_content( $prev_content, $post ) {
		$content = (isset( $post->post_excerpt ) ? $post->post_excerpt : '') .' '. (isset( $post->post_content ) ? $post->post_content : '');
		return $prev_content . ' ' . $content;
	}

	public function post_content( $content, $post, $ajax = false ) {
		if ( !function_exists( 'dslc_json_decode' ) || !$ajax || empty($post->ID) )
			return $content;
		
		$meta = maybe_unserialize(get_post_meta( $post->ID, 'dslc_code', true ));
		$page_code_array = dslc_json_decode( $meta );
		
		foreach ( (array)$page_code_array as $element ) {
			if ( !isset( $element['content'] ) )
				continue;
			$content .= print_r( $element['content'], true );
		}
			
		return $content;
	}
	
}
WpBWPBakery::serve();

/*
 * Support for OnePager
 * OnePager supports PHP5.4+
 * https://docs.getonepager.com/basics/system-requirements#server-requirements
 *
 */
if ( version_compare(PHP_VERSION, '5.4.0') >= 0 ) :

class WpBOnePager extends WpBCompat {
	
	public function __construct(){
		parent::__construct();
	}
	
	public static function serve(){
		$me = new WpBOnePager();
		$me->load_hooks();
	}
	
	public function the_posts($posts) {
		if ( empty($posts) || is_admin() || !function_exists( 'onepager' ) ) 
			return $posts;
	
		$sc_found = false;
		
		foreach ( $posts as $post ) {
			if ( !isset( $post->ID ) )
				continue;
			$meta = maybe_unserialize( get_post_meta( $post->ID, 'onepager_sections', true ) );

			foreach ( (array)$meta as $section ) {
				$title = !empty( $section['contents']['title'] ) ? $section['contents']['title'] : '';
				$desc = !empty( $section['contents']['description'] ) ? $section['contents']['description'] : '';
				if ( has_shortcode( $title, 'app_confirmation') || has_shortcode( $title, 'app_book') ||
					has_shortcode( $desc, 'app_confirmation') || has_shortcode( $desc, 'app_book')) {
					$this->a->load_assets( );
					do_action( 'app_shortcode_found', 'confirmation' );
					break;
				}
				if ( strpos( $title, '[app_' ) !== false || strpos( $desc, '[app_' ) !== false ) {
					$sc_found = true;
				}
			}
		}
		
		if ( $sc_found ) {
			$this->a->load_assets( );
			do_action( 'app_shortcode_found', '' );
		}
 
		return $posts;		
	}
	
	public function post_content( $prev_content, $post, $ajax = false ) {
		if ( !function_exists( 'onepager' ) || !$ajax || empty($post->ID) )
			return $prev_content;
		
		$content = '';
		$sections = maybe_unserialize(get_post_meta( $post->ID, 'onepager_sections', true ));
	
		if ( is_array( $sections ) ) {
			$all_valid = array_filter( $sections, 
				function ( $section ) {
				return array_key_exists( 'slug', $section )
				 && array_key_exists( 'id', $section )
				 && array_key_exists( 'title', $section );
				} );
			
			if ( !empty( $all_valid ) ) {			
				foreach ( $all_valid as $section ) {
					$title = !empty( $section['contents']['title'] ) ? $section['contents']['title'] : '';
					$desc = !empty( $section['contents']['description'] ) ? $section['contents']['description'] : '';
					$content .= $title.$desc;
				};
			}
		}
			
		return $prev_content. $content;;
	}
	
}
WpBOnePager::serve();

endif;

/*
 * Support for Site Origin (Text Widget)
 * 
 */
class WpBSiteOrigin extends WpBCompat {
	
	public function __construct(){
		parent::__construct();
	}
	
	public static function serve(){
		$me = new WpBSiteOrigin();
		$me->load_hooks();
	}
	
	public function admin_enqueue_scripts() {
		global $pagenow;
		
		if ( !is_admin() || 'post.php' != $pagenow || !defined( 'SITEORIGIN_PANELS_VERSION' ) )
			return;
		
		wp_enqueue_script( 'app-editor-widget-js', WPB_PLUGIN_URL . '/js/admin-widgets.js', array( 'jquery' ), $this->a->version );
	}
	
}
WpBSiteOrigin::serve();

/*
 * Support for Unyson
 * 
 */
class WpBUnyson extends WpBCompat {
	
	public function __construct(){
		parent::__construct();
	}
	
	public static function serve(){
		$me = new WpBUnyson();
		$me->load_hooks();
	}
	
	public function post_content( $prev_content, $post, $ajax = false ) {
		if ( !function_exists('fw_ext_page_builder_get_post_content') )
			return $prev_content;
		
		global $shortcode_tags;
		$stack = $shortcode_tags;
		if ( $ajax ) {
			$app_shortcodes = wpb_shortcodes();
			foreach( $shortcode_tags as $scode=>$val ) {
				if ( in_array( $scode, $app_shortcodes ) )
					unset( $shortcode_tags[$scode] );
			}
		}
		$c = do_shortcode(fw_ext_page_builder_get_post_content($post));
		$shortcode_tags = $stack;
		if ( $c != $prev_content && empty( $this->lss_called ) &&  strpos( $c, 'app-sc' ) !== false ) {
			$this->a->load_assets( );
			$this->lss_called = true;
		}
			
		return $prev_content. $c;
	}
	
}
WpBUnyson::serve();

/*
 * Support for X Theme
 * 
 */
class WpBXTheme extends WpBCompat {
	
	public function __construct(){
		parent::__construct();
	}
	
	public static function serve(){
		$me = new WpBXTheme();
		$me->load_hooks();
	}
	
	/*
	 * X Theme has a js conflict with qtip as of March 2017:
	 * https://community.theme.co/forums/topic/apparent-conflict-with-qtip-library/#post-872048
	 * Solution is to load qtip after x-body.min.js of X Theme
	 */
	public function load_assets( ) {
		if ( !function_exists( 'x_enqueue_site_scripts' ) )
			return;
		
		if ( WpBDebug::is_debug() ) {
			wp_deregister_script( 'jquery-qtip' );
			wp_enqueue_script( 'jquery-qtip', WPB_PLUGIN_URL . '/js/jquery.qtip.min.js', array('jquery','x-site-body'), $this->a->version );
		}
		else {
			wp_deregister_script( 'wp-base-libs' );
			wp_enqueue_script( 'wp-base-libs', WPB_PLUGIN_URL . '/js/libs.js', array('jquery-ui-widget','jquery-ui-button','jquery-ui-dialog','jquery-ui-datepicker','x-site-body'), $this->a->version, true );
		}
	}
	
}
WpBXTheme::serve();

/*
 * Support for Popup Maker
 * Only one popup per page is supported
 * "Disable Overlay" checkbox should be checked for scrolling to work
 */
class WpBPopupMaker extends WpBCompat {
	
	private $popup_id = null;
	
	public function __construct(){
		parent::__construct();
	}
	
	public static function serve(){
		$me = new WpBPopupMaker();
		$me->load_hooks();
		add_action( 'popmake_preload_popup', array( $me, 'preload' ), 10, 1 );
	}
	
	/*
	 * Load assets when preload_popup action fires
	 */
	public function preload( $ID ) {
		$this->popup_id = $ID;
		$this->a->load_assets();
	}

	/*
	 * When popup is active, WP BASE checks popup content instead of page content
	 * Therefore WP BASE shortcodes on normal page content will not update
	 */
	public function js_parameters( $param ) {
		if ( !$this->popup_id )
			return $param;
				
		$param['post_id'] = $this->popup_id;
		return $param;
	}

}
WpBPopupMaker::serve();

/*
 * Support for WooCommerce Bookings
 * This extention loads JQuery UI styles remotely and on every page! (Not only to WC product page)
 * It forces using smoothness theme and it does not provide a theme selection option
 * This class fixes this for WP BASE pages
 */
class WpBWCBookings extends WpBCompat {
	
	public function __construct(){
		parent::__construct();
	}
	
	public static function serve(){
		$me = new WpBWCBookings();
		$me->load_hooks();
	}
	
	/*
	 * Remove style on WP BASE pages as it will otherwise override ours
	 */
	public function wp_enqueue_scripts( ) {
		if ( !class_exists( 'WC_Bookings' ) )
			return;
		
		# If not WP BASE page, do not touch
		if ( ! wp_style_is( 'wp-base' ) )
			return;
		
		wp_dequeue_style( 'jquery-ui-style' );
	}

}
WpBWCBookings::serve();
