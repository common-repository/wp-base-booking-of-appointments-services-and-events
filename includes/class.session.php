<?php
/**
 * WPB Session
 *
 * This is a wrapper class for WP_Session
 * A subset of Easy Digital Downloads' class-session by Pippin Williamson
 *
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPB_Session Class
 *
 * @since 3.0
 */
class WPB_Session {

	/**
	 * Holds our session data
	 *
	 * @var array
	 * @access private
	 * @since 3.0
	 */
	private $session;

	/**
	 * Get things started
	 *
	 * Defines our WP_Session constants, includes the necessary libraries and
	 * retrieves the WP Session instance
	 *
	 * @since 3.0
	 */
	public function __construct() {

		if( ! $this->should_start_session() ) {
			return;
		}

		if ( ! defined( 'WP_SESSION_COOKIE' ) ) {
			define( 'WP_SESSION_COOKIE', 'wp_base_session' );
		}

		if ( ! class_exists( 'Recursive_ArrayAccess' ) ) {
			include_once WPB_PLUGIN_DIR . '/includes/lib/class-recursive-arrayaccess.php';
		}

		if ( ! class_exists( 'WP_Session' ) ) {
			include_once WPB_PLUGIN_DIR . '/includes/lib/class-wp-session.php';
			include_once WPB_PLUGIN_DIR . '/includes/lib/wp-session.php';
		}

		add_filter( 'wp_session_expiration_variant', array( $this, 'set_expiration_variant_time' ), 99999 );
		add_filter( 'wp_session_expiration', array( $this, 'set_expiration_time' ), 99999 );

		$this->session = WP_Session::get_instance();

		return $this->session;
	}

	/**
	 * Retrieve session ID
	 *
	 * @access public
	 * @since 3.0
	 * @return string Session ID
	 */
	public function get_id() {
		return $this->session->session_id;
	}

	/**
	 * Retrieve a session variable
	 *
	 * @access public
	 * @since 3.0
	 * @param string $key Session key
	 * @return mixed Session variable
	 */
	public function get( $key ) {

		$key    = sanitize_key( $key );
		$return = false;

		if ( isset( $this->session[ $key ] ) && ! empty( $this->session[ $key ] ) ) {

			preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $this->session[ $key ], $matches );
			if ( ! empty( $matches ) ) {
				$this->set( $key, null );
				return null;
			}
			
			if ( is_numeric( $this->session[ $key ] ) ) {
				$return = $this->session[ $key ];
			} else {

				$maybe_json = json_decode( $this->session[ $key ] );

				// Since json_last_error is PHP 5.3+, we have to rely on a `null` value for failing to parse JSON.
				if ( is_null( $maybe_json ) ) {
					$is_serialized = is_serialized( $this->session[ $key ] );
					if ( $is_serialized ) {
						$value = unserialize( $this->session[ $key ] );
						$this->set( $key, (array) $value );
						$return = $value;
					} else {
						$return = $this->session[ $key ];
					}
				} else {
					$return = json_decode( $this->session[ $key ], true );
				}

			}
		}

		return $return;
	}

	/**
	 * Set a session variable
	 *
	 * @since 3.0
	 *
	 * @param string $key Session key
	 * @param int|string|array $value Session variable
	 * @return mixed Session variable
	 */
	public function set( $key, $value ) {

		$key = sanitize_key( $key );

		if ( is_array( $value ) ) {
			$this->session[ $key ] = wp_json_encode( $value );
		} else {
			$this->session[ $key ] = esc_attr( $value );
		}

		return $this->session[ $key ];
	}

	/**
	 * Force the cookie expiration variant time to 23 hours
	 *
	 * @access public
	 * @since 3.0
	 * @param int $exp Default expiration (1 hour)
	 * @return int
	 */
	public function set_expiration_variant_time( $exp ) {
		return ( 30 * 60 * 23 );
	}

	/**
	 * Force the cookie expiration time to 24 hours
	 *
	 * @access public
	 * @since 3.0
	 * @param int $exp Default expiration (1 hour)
	 * @return int Cookie expiration time
	 */
	public function set_expiration_time( $exp ) {
		return ( 30 * 60 * 24 );
	}

	/**
	 * Determines if we should start sessions
	 *
	 * @since  3.0
	 * @return bool
	 */
	public function should_start_session() {

		$start_session = true;

		if( ! empty( $_SERVER[ 'REQUEST_URI' ] ) ) {

			$blacklist = $this->get_blacklist();
			$uri       = ltrim( $_SERVER[ 'REQUEST_URI' ], '/' );
			$uri       = untrailingslashit( $uri );

			if( in_array( $uri, $blacklist ) ) {
				$start_session = false;
			}

			if( false !== strpos( $uri, 'feed=' ) ) {
				$start_session = false;
			}

			if( false !== strpos( $uri, 'wp_scrape_key' ) ) {
				// Starting sessions while saving the file editor can break the save process, so don't start
				$start_session = false;
			}

		}

		return apply_filters( 'app_start_session', $start_session );
	}

	/**
	 * Retrieve the URI blacklist
	 *
	 * These are the URIs where we never start sessions
	 *
	 * @since  3.0
	 * @return array
	 */
	public function get_blacklist() {

		$blacklist = apply_filters( 'app_session_start_uri_blacklist', array(
			'feed',
			'feed/rss',
			'feed/rss2',
			'feed/rdf',
			'feed/atom',
			'comments/feed'
		) );

		// Look to see if WordPress is in a sub folder or this is a network site that uses sub folders
		$folder = str_replace( network_home_url(), '', get_site_url() );

		if( ! empty( $folder ) ) {
			foreach( $blacklist as $path ) {
				$blacklist[] = $folder . '/' . $path;
			}
		}

		return $blacklist;
	}


}
