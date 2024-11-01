<?php
/**
 * WPB Users
 *
 * Methods for Clients (WP Users + Non logged in users)
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBUser' ) ) {

class WpBUser {
	
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
		add_action( 'app_save_settings', array( $this, 'save_settings' ), 12 );							// Save settings
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'deleted_user', array( $this, 'user_deleted' ), 12, 2 );
		add_filter( 'app_pre_confirmation_reply', array( $this, 'pre_confirmation_reply' ), 10, 2 );	// Control visibility of fields
		add_action( 'app_new_appointment', array( $this, 'new_appointment' ), 10, 2 );					// Write user data to DB
		add_action( 'app_edit_post_confirmation_check', array( $this, 'post_confirmation_handle' ) );	// Check after edit
		add_filter( 'appointments_tabs', array( $this, 'add_tab' ), 16 ); 								// Add tab
		add_action( 'app_user_tab', array( $this, 'settings' ) );										// Display HTML settings on Business Settings
	}
	
	/**
	 * Find client phone given his appointment
	 * @param app_id: Appointment ID
	 * @since 2.0
	 * @return string
	 */	
	function get_client_phone( $app_id ) {
		if ( !$phone = wpb_get_app_meta( $app_id, 'phone' ) ) {
			$app = $this->a->get_app( $app_id );
			if ( $app->user ) {
				$phone = get_user_meta( $app->user, 'app_phone', true );
			} 
		}

		return apply_filters( 'app_client_phone', preg_replace( "/[^0-9]/", "", $this->add_dial_code( $phone ) ), $app_id );
	}
	
	/**
	 * Find client phone given his ID
	 * @param $ID: user ID
	 * @since 2.0
	 * @return string
	 */	
	function get_worker_phone( $ID ) {
		$phone = get_user_meta( $ID, 'app_phone', true );
		return apply_filters( 'app_worker_phone', preg_replace( "/[^0-9]/", "", $this->add_dial_code( $phone ) ), $ID );
	}

	/**
     * Get admin phones or first admin phone
	 * @since 2.0
	 * @return mixed	array ($first:false) or string ($first:true)
     */
	function get_admin_phone( $first=false ) {
		$admin_phones = array();
		if ( wpb_setting('admin_phone') ) {
			$temp = array_filter( array_map( 'trim', explode( ',', wpb_setting('admin_email') ) ) );
			foreach( $temp as $phone ) {
				$admin_phones[] = $this->add_dial_code( $phone );
			}
		}
		if( empty( $admin_phones ) ) {
			$admin_email = get_option('admin_email');
			if ( $admin_email ) {
				$user = get_user_by( 'email', $admin_email );
				if ( $phone = get_user_meta( $user->ID, 'app_phone', true ) )
					$admin_phones[] = $this->add_dial_code( $phone );
			}
		}
			
		$admin_phones = apply_filters( 'app_get_admin_phones', $admin_phones );
		if ( $first )
			return apply_filters( 'app_get_admin_phone', current( $admin_phones ) );
		else
			return $admin_phones;
	}

	/**
	 * Add the default dialing code to a phone when required
	 * @since 2.0
	 * @return string
	 */	
	function add_dial_code( $phone ) {
		if ( $phone ) {
			if ( substr($phone, 0, 2) != '00' && substr($phone, 0, 1) != '+' ) {
				if ( substr($phone, 0, 1) == '0' )
					$phone = substr($phone, 1);

				$phone = '+' . wpb_setting('phone_code') . $phone;
			}
		}
		return $phone;		
	}

	/**
	 *	Check if current user has one of the capabilities
	 *  @param $cap 	array|string	List of required capabilities separated by comma or array. "none" also returns true
	 *  @since 2.0
	 */
	function is_capable( $cap ) {

		$is_capable = false;
		$caps = is_array( $cap ) ? $cap : explode( ",", $cap );

		foreach ( $caps as $c ) {
			if ( 'none' == strtolower( $c ) || current_user_can($c) ) {
				$is_capable = true;
				break;
			}
		}
		
		return $is_capable;
	}
	
	/**
	 *	Override WP capability check (Only for debug texts)
	 *  @param cap: Capability
	 *  @since 2.0
	 *	@return bool
	 */
	function _current_user_can( $cap ) {
		return apply_filters( 'app_current_user_can', current_user_can( $cap ) );
	}

	/**
	 * Return admin email(s)
	 * since 1.2.7
	 * @return mixed	returns string (the first email) if $first is true, returns array if $first is false 
	 */	
	function get_admin_email( $first=false ) {
		$admin_emails = array();
		if ( wpb_setting('admin_email') ) {
			$temp = explode( ',', wpb_setting('admin_email') );
			foreach( $temp as $email ) {
				$e = trim( $email );
				if ( is_email( $e ) )
					$admin_emails[] = $e;
			}
		}
		if( empty( $admin_emails ) ) {
			global $current_site;
			$admin_email = get_option('admin_email');
			if ( $admin_email )
				$admin_emails[] = $admin_email;
			else
				$admin_emails[] = 'admin@' . $current_site->domain;
		}
			
		$admin_emails = apply_filters( 'app_get_admin_emails', $admin_emails );
		if ( $first )
			return apply_filters( 'app_get_admin_email', current( $admin_emails ) );
		else
			return $admin_emails;
	}

	/**
	 * Find client name given his appointment, together with a link to his profile page if current user can edit users
	 * @param r: Appointment object
	 * @param add_link: Add link of the user. Disable this in emails
	 * @param limit: Character limit for name
	 * @return string
	 */	
	function get_client_name( $app_id, $r=null, $add_link=true, $limit=22 ) {
		$name = '';
		$limit = apply_filters( 'app_chr_limit', $limit );
		$result = (null !== $r) ? $r : $this->a->get_app( $app_id );
			
		if ( $result ) {
			// Client can be a user
			if ( $result->user )
				$name = $this->get_name( $result->user  );
	
			if ( !$name )
				$name = wpb_get_app_meta( $app_id, 'name' );

			if ( !$name && $maybe_email = wpb_get_app_meta( $app_id, 'email' ) )
				$name = $maybe_email;
			
			$name = stripslashes( $name );
			
			$full_name = $name;
			if ( $limit && strlen($name) > $limit + 3 )
				$name = mb_substr( $name, 0, $limit, 'UTF-8' ) . '...';
			
			// Also add other details of client
			$userdata = array_merge( array_filter($this->get_app_userdata( 0, $result->user )), array_filter($this->get_app_userdata( $app_id ) ) );
			// TODO: Make this a template
			$tt = $full_name;
			if ( isset($userdata['email']) )
				$tt = $tt .' ● '. $userdata['email'];
			if ( isset($userdata['phone']) )
				$tt = $tt .' ● '. $userdata['phone'];
			
			if ( $name && $add_link ) {
				if ( current_user_can( 'edit_users' ) && $result->user )
					$name = '<a href="'. admin_url("user-edit.php?user_id="). $result->user . '" target="_blank" title="'.$tt.'">'. $name . '</a>';
				else 
					$name = '<abbr title="'.$tt.'">'. $name . '</abbr>';
			}
		}
		if ( !$name )
			$name = stripslashes( $this->a->get_text('client') ); // Fallback

		return apply_filters( 'app_get_client_name', $name, $app_id, $r );
	}

	/**
	 * Find user name given his ID. User does not need to be a client
	 * User names are called from user cache. If not in cache, user cache is updated for that user.
	 * @return string
	 * @since 2.0
	 */	
	function get_name( $user_id = 0 ) {
		if ( !$user_id )
			$user_id = get_current_user_id();
		
		$name = '';

		$name_meta = get_user_meta( $user_id, 'app_name', true );
		if ( $name_meta )
			$name = $name_meta;
		else {
			$user_info = $this->_get_userdata( $user_id );
			if ( is_object( $user_info ) ) {
				if ( $user_info->display_name )
					$name = $user_info->display_name;
				else if ( $user_info->user_nicename )
					$name = $user_info->user_nicename;
				else if ( $user_info->user_login )
					$name = $user_info->user_login;
			}
		}
		
		$name = apply_filters( 'app_get_user_name', $name, $user_id );
		
		return stripslashes( $name );
	}
	
	/**
	 * Get userdata from cache
	 * @return false if not found, WP user object if found
	 * @since 2.0
	 */	
	function _get_userdata( $user_id ) {
		
		$userdata = wp_cache_get($user_id, 'users', false, $found);
		
		if ( !$found ) {
			$userdata = get_user_by( 'id', $user_id );
			wp_cache_set($user_id, $userdata, 'users');
		}
		
		return $userdata;
	}
	
	/**
	 * Find user data given his appointment or user ID
	 * @param app_id: App ID
	 * @param $prime_cache: Cache all apps because there is a loop with several calls, e.g on admin-bookings
	 * @return string
	 * @since 2.0
	 */		
	 function get_app_userdata( $app_id, $user_id = 0, $prime_cache=false ) {
		// Set all fields to '' at the beginning
		foreach( $this->get_fields() as $f ) {
			${$f} = '';
		}
		$note = $remember = '';
		 
		// If app_id is defined (editing), bring data of the owner of the app
		if ( $app_id && $app = $this->a->get_app( $app_id, $prime_cache ) ) {
			foreach( $this->get_fields() as $f ) {
				${$f} = wpb_get_app_meta( $app_id, $f );
			}

			$note = wpb_get_app_meta( $app_id, 'note' );
			$remember = '';
		}
		else if ( $user_id ) {
			foreach( $this->get_fields() as $f ) {
				${$f} = get_user_meta( $user_id, 'app_'.$f, true );
			}
			if ( !$name )
				$name = $this->get_name( $user_id );
			
			if ( !$email ) {
				$user_info = $this->_get_userdata( $user_id );
				$email = isset( $user_info->user_email ) ? $user_info->user_email : '';
			}
		}
		else if ( !$user_id ) {
			// Get user form data from his cookie
			$data = $this->get_userdata_from_cookie();

			// First try to read full field names from cookie
			foreach( $this->get_fields() as $f ) {
				if ( isset( $data[$f] ) )
					${$f} = sanitize_text_field( $data[$f] );
			}
			
			// User may have already saved his data before		
			if ( is_user_logged_in() ) {
				global $current_user;
				$user_info = $this->_get_userdata( $current_user->ID );
				
				foreach( $this->get_fields() as $f ) {
					$temp = get_user_meta( $current_user->ID, 'app_'.$f, true );
					${$f} = $temp ? $temp : ${$f};
				}
				if ( !$name )
					$name = $this->get_name( $current_user->ID );
				
				if ( !$email )
					$email = $user_info->user_email;
				
			}
		}
		# Note: $user_id == 0 is accepted, e.g. for non logged in users
		
		// create output
		$out = array();
		$out['remember'] = $remember;
		$out['note'] = $note;
		foreach( $this->get_fields() as $f ) {
			$out[$f] = ${$f};
		}
		
		// User data can be overriden, e.g. with $_GET['name']
		return apply_filters( 'app_userdata', $out, $this->get_fields() );
	}
	
	/**
	 * Helper to obfuscate user data
	 * @return string
	 * @since 2.0
	 */	
	function _convert_uuencode( $string ) {
		return convert_uuencode( $string . wpb_get_salt() );
	}

	/**
	 * Modified version of wp_dropdown_users
	 * https://codex.wordpress.org/Function_Reference/wp_dropdown_users
	 * @return string
	 * @since 2.0
	 */	
	function app_dropdown_users( $args = '' ) {
		// Width parameter is new
        $defaults = array(
                'show_option_all' => '', 'show_option_none' => '', 'hide_if_only_one_author' => '',
                'orderby' => 'display_name', 'order' => 'ASC',
                'include' => '', 'exclude' => '', 'multi' => 0,
                'show' => 'display_name', 'echo' => 1,
                'selected' => 0, 'name' => 'user', 'class' => '', 'id' => '', 'width' => '',
                'blog_id' => $GLOBALS['blog_id'], 'who' => '', 'include_selected' => false,
                'option_none_value' => -1
        );
        $defaults['selected'] = is_author() ? get_query_var( 'author' ) : 0;
        $r = wp_parse_args( $args, $defaults );
        $show = $r['show'];
        $show_option_all = $r['show_option_all'];
        $show_option_none = $r['show_option_none'];
        $option_none_value = $r['option_none_value'];
        $query_args = wp_array_slice_assoc( $r, array( 'blog_id', 'include', 'exclude', 'orderby', 'order', 'who' ) );
        $query_args['fields'] = array( 'ID', 'user_login', $show );
        $users = get_users( $query_args );
        $output = '';
        if ( ! empty( $users ) && ( empty( $r['hide_if_only_one_author'] ) || count( (array)$users ) > 1 ) ) {
				# This condition is new
				if ( $r['width'] )
					$style = " style='width:".$r['width']."'";
				else
					$style = '';
			
                $name = esc_attr( $r['name'] );
                if ( $r['multi'] && ! $r['id'] ) {
                        $id = '';
                } else {
                        $id = $r['id'] ? " id='" . esc_attr( $r['id'] ) . "'" : " id='$name'";
                }
                $output = "<select data-native-menu='false' data-theme='".wpb_setting('swatch')."' name='{$name}'{$id} class='" . $r['class'] . "' {$style} >\n";
                if ( $show_option_all ) {
                        $output .= "\t<option value='0'>$show_option_all</option>\n";
                }
                if ( $show_option_none ) {
                        $_selected = selected( $option_none_value, $r['selected'], false );
                        $output .= "\t<option value='" . esc_attr( $option_none_value ) . "'$_selected>$show_option_none</option>\n";
                }
                $found_selected = false;
                foreach ( (array) $users as $user ) {
                        $user->ID = (int) $user->ID;
                        $_selected = selected( $user->ID, $r['selected'], false );
                        if ( $_selected ) {
                                $found_selected = true;
                        }
						# New: If $show = display_name, replace it with get_name
						if ( 'display_name' == $show )
							$display = $this->get_name( $user->ID );
						else
							$display = ! empty( $user->$show ) ? $user->$show : '('. $user->user_login . ')';
						
						/* Developers: Sample usage of 'app_dropdown_users_display' filter in functions.php of the theme:
							function wpb_dropdown_users_display( $display, $user ) {
								$app_user = BASE('User')->_get_userdata( $user->ID );
								return BASE('User')->get_name( $user->ID ) . " - " . $app_user->user_email;	
							}
							add_filter( 'app_dropdown_users_display', 'wpb_dropdown_users_display', 10, 2 ); 
						*/
						$display = apply_filters( 'app_dropdown_users_display', $display, $user ); 
                        $output .= "\t<option value='$user->ID'$_selected>" . esc_html( $display ) . "</option>\n";
                }
                if ( $r['include_selected'] && ! $found_selected && ( $r['selected'] > 0 ) ) {
                        $user = get_userdata( $r['selected'] );
                        $_selected = selected( $user->ID, $r['selected'], false );
                        $display = ! empty( $user->$show ) ? $user->$show : '('. $user->user_login . ')';
                        $output .= "\t<option value='$user->ID'$_selected>" . esc_html( $display ) . "</option>\n";
                }
                $output .= "</select>";
        }
        /**
         * Filter the wp_dropdown_users() HTML output.
         *
         * @since 2.3.0
         *
         * @param string $output HTML output generated by wp_dropdown_users().
         */
        $html = apply_filters( 'app_dropdown_users', $output );
        if ( $r['echo'] ) {
                echo $html;
        }
        return $html;
	}

	/**
	 * Get user ID from submitted user form
	 * since 2.0
	 */
	function read_user_id() {
		return isset( $_REQUEST["app_user_id"] ) ? $_REQUEST["app_user_id"] : get_current_user_id();
	}

	/**
	 * Sanitize and apply filter to submitted data
	 * May die if required fields are empty
	 * @param $validate		string	Validate only if set to 'validate'
	 * @Since 3.0
	 * @return array|none	If used as an action hook, returns none. If used as a method call, returns array (sanitized user submit)
	 */	
	function sanitize_submitted_userdata( $validate='validate' ){
		$sub_user_data = array();
		
		$app_user_data = isset($_POST['app_user_data']) ? json_decode( wp_unslash($_POST['app_user_data']), true ) : array();
		// $app_user_data = is_array( $app_user_data ) ? $app_user_data : array();
		
		// Sanitize and Apply check filter
		foreach( (array)$app_user_data as $f=>$value ) {
			if ( strpos( $f, 'udf_' ) !== false )
				continue;
			
			$sub_user_data[$f] = !empty( $value ) ? trim(sanitize_text_field( $value )) : '';
			
			if ( 'validate' === $validate ) {
				/**
				 * Filter should return false if submitted value validates. 
				 * If not validates, a string value will be regarded as reason of not validation (error)
				 * goTO: class name to which screen will be scrolled to
				 */
				 if ( $error = apply_filters( "app_".$f."_validation", empty( $sub_user_data[$f] ), $sub_user_data[$f], $app_user_data, $f ) ) {
					if ( is_string( $error ) )
						die( json_encode( array("error"=>$error, "goTo"=>"app-".$f."-field-entry" ) ) );
					else
						wpb_json_die( $f );
				 }
			}
		}
		
		return $sub_user_data;		
	}
	
	/**
	 * Control visibility of required fields
	 * @Since 3.0
	 * @return array
	 */	
	function pre_confirmation_reply( $reply_array, $new_value_arr ) {
		// Check which user fields will be asked from the client (visible ones are always required)
		// Filtered by the shortcode attr
		$user_fields = json_decode( wp_unslash( $_POST['app_user_fields'] ), true );

		$ask = array();
		$ask['note'] = wpb_setting("ask_note") ? "ask" : '';
		$ask['remember'] = wpb_setting("ask_remember") ? "ask" : '';
		foreach( $this->a->get_user_fields() as $f ) {
			$ask[$f] = in_array( $f, array_values( $user_fields ) ) ? 'ask' : '';
		}
			
		return array_merge( $reply_array, $ask );
	}

	/**
	 * Handle/Check submitted user data at post confirmation
	 * @param $app_id_edit:	Integer		Appt ID only if editing
	 * @Since 3.0
	 * @return none if check fails or array
	 */	
	function post_confirmation_handle( $app_id_edit ) {
		
		$sub_user_data = $this->sanitize_submitted_userdata( );
		
		if ( $app_id_edit ) {
			$app = $this->a->get_app( $app_id_edit );
			$user_id = $app->user;
			$userdata = $this->_get_userdata( $user_id );
			$user_email = $userdata->user_email;
			$user_name = $this->get_name( $user_id );
		}
		else if ( is_user_logged_in( ) ) {
			// On behalf booking
			$user_id = !empty( $_POST['app_user_id'] ) ? $_POST['app_user_id'] : get_current_user_id();
			$userdata = $this->_get_userdata( $user_id );
			$user_email = $userdata->user_email;
			$user_name = $this->get_name( $user_id );
		}
		else {
			$user_id = 0;
			$user_email = '';
			$user_name = '';
		}

		$sub_user_data['name'] = !empty( $sub_user_data['name'] ) ? $sub_user_data['name'] : $user_name;
		$sub_user_data['email'] = !empty( $sub_user_data['email'] ) ? $sub_user_data['email'] : $user_email;
		$note = isset( $_POST["app_note"] ) ? sanitize_text_field( $_POST["app_note"] ) : '';
		$sub_user_data['note'] = apply_filters( 'app_note_field', $note );
		
		# Disable remember only if selected so		
		if ( !wpb_setting('ask_remember') || !empty( $_POST["app_remember"] ) )
			$sub_user_data['remember'] = 1;
		else
			$sub_user_data['remember'] = '';
		
		/* Additional checks for email */
		if ( wpb_setting("ask_email") && !is_email( $sub_user_data['email'] ) )
			wpb_json_die( 'email', 'app-email-field-entry' );
		
		# Email blacklist check
		$mod_keys = trim( get_option('blacklist_keys') );
		if ( wpb_setting("ask_email") && $sub_user_data['email'] && $mod_keys ) {
			$words = explode("\n", $mod_keys );

			foreach ( (array) $words as $word ) {
				$word = trim($word);

				if ( empty($word) ) { continue; }

				// Do some escaping magic so that '#' chars in the
				// spam words don't break things:
				$word = preg_quote($word, '#');

				$pattern = "#$word#i";
				if ( preg_match($pattern, $sub_user_data['email']) )
					die( json_encode( array( 'error'=>$this->a->get_text('blacklisted') ) ) );
			}
			
			// Multisite email check
			if ( is_multisite() && is_email_address_unsafe( $user_email ) )
				die( json_encode( array( 'error'=>$this->a->get_text('blacklisted') ) ) );
		}
		
		# Check if user is not logged in and submitted email is registered
		# Without this check, someone may login instead of another person using his email
		if ( !is_user_logged_in() && wpb_setting("ask_email") && $sub_user_data['email'] ) {
			$maybe_user = get_user_by( 'email', $sub_user_data['email'] );
			if ( $maybe_user ) {
				die( json_encode( array( 'error'=>$this->a->get_text('login_required') ) ) );
			}
		}

		if ( !$user_id && $sub_user_data['email'] && 'yes' == wpb_setting('auto_register_client') ) {
			if ( $maybe_user_id = $this->create_user( $sub_user_data ) ) {
				$user_id = $maybe_user_id;
				/* Auto login
				 * https://codex.wordpress.org/Function_Reference/wp_set_current_user#Examples
				 * For WC and MP, don't wait for setting
				 */
				if ( !is_user_logged_in() && ('yes' == wpb_setting('auto_register_login') || 
				(BASE('WooCommerce') && BASE('WooCommerce')->is_app_wc_page()) || (BASE('MarketPress') && BASE('MarketPress')->is_app_mp_page()) ) ) {
					$user = get_user_by( 'id', $user_id ); 
					if ( $user ) {
						wp_set_current_user( $user_id, $user->user_login );
						wp_set_auth_cookie( $user_id );
						do_action( 'wp_login', $user->user_login );
					}
				}
			}
		}
		
		return array( $user_id => $sub_user_data );
	}
	
	/**
	 * Save user submitted data to app meta and save cookie
	 */	
	function new_appointment( $insert_id, $sub_user_data ) {
		$app = $this->a->get_app( $insert_id );
		$user_id = !empty( $app->user ) ? $app->user : 0;
		
		// Booking on Behalf
		$current_user_id = get_current_user_id();
		if ( $current_user_id && $current_user_id != $user_id )
			wpb_add_app_meta( $insert_id, 'booking_on_behalf', $current_user_id );
		
		# Save user data even in on behalf case ($user_id is not necessarily current user id)
		if ( $user_id ) {
			foreach( (array)apply_filters( 'app_new_appointment_save_user_meta', $this->get_fields(), $user_id, $insert_id, $sub_user_data ) as $f ) {
				if ( isset( $sub_user_data[$f] ) )
					update_user_meta( $user_id, 'app_'.$f, $sub_user_data[$f] );
			}
		}

		foreach ( $sub_user_data as $f=>$value ) {
			if ( $value ) {
				wpb_add_app_meta( $insert_id, $f, $value );
			}
		}
		
		$this->save_cookie( $insert_id, $user_id, false );
	}

	/**
	 * Save a cookie so that not logged in client can follow his appointments
	 */	
	function save_cookie( $app_id, $user_id, $userdata ) {
		
		# DO NOT SAVE data in COOKIE if user is logged in
		if ( is_user_logged_in() )
			return;

		# Do not save cookie if user_id is different (on behalf booking)
		if ( $user_id && get_current_user_id() != $user_id )
			return;
		
		# It should come here with ajax request, so headers should not be sent, but just in case
		if ( headers_sent() )
			return;
		
		# Add 365 days grace time
		$expire = $this->a->_time + 3600 * 24 * ( wpb_setting("app_limit") + 365 );
		$expire = apply_filters( 'app_cookie_time', $expire );
		
		$cookiepath = defined('COOKIEPATH') ? COOKIEPATH :  "/";
		$cookiedomain = defined('COOKIEDOMAIN') ? COOKIEDOMAIN : '';
		
		if ( !empty( $userdata['remember'] ) ) {
		
			unset( $userdata['remember'] );
			
			$userdata = apply_filters( 'app_cookie_userdata', $userdata, $user_id );
			$userdata['timestamp'] = $this->a->_time;
				
			$ser_data = serialize( array( 	
						'hash'		=> $this->a->create_hash( $userdata, 'userdata_cookie', $this->anon_user_identifier( $userdata ) ), 
						'userdata'	=> $userdata,
				) );
				
			if ( setcookie("wpb_userdata", $ser_data, $expire, $cookiepath, $cookiedomain) )
				$_COOKIE["wpb_userdata"] = $ser_data;
		}
		else {
			# Do not remember selected - Delete cookie if exists
			setcookie("wpb_userdata", '', time() - 24*3600, $cookiepath, $cookiedomain);
			unset( $_COOKIE["wpb_userdata"] );
		}

		if ( $app_id ) {
			$apps = $this->a->get_apps_from_cookie();
				
			$apps[] = $app_id;
			$apps = array_filter( array_unique( $apps ) );
			
			$ser_data = serialize( array( 	
							'hash'		=> $this->a->create_hash( $apps, 'bookings_cookie', $this->anon_user_identifier( $userdata ) ), 
							'bookings'	=> $apps,
					) );
			
			if ( setcookie("wpb_bookings", $ser_data, $expire, $cookiepath, $cookiedomain) )
				$_COOKIE["wpb_bookings"] = $ser_data;
		}
	}
	
	/**
	 *	Get user data from cookie by checking hash
	 *	To prevent tampering with cookie
	 *  @return array
	 *  @since 3.0
	 */	
	function get_userdata_from_cookie() {
		if ( empty( $_COOKIE["wpb_userdata"] ) )
			return array();
		
		$data = maybe_unserialize( wp_unslash( $_COOKIE["wpb_userdata"] ) );
		if ( empty( $data['hash'] ) || empty( $data['userdata'] ) || !is_array( $data['userdata'] ) || $data['hash'] != $this->a->create_hash( $data['userdata'], 'userdata_cookie', $this->anon_user_identifier( $data['userdata'] ) ) )
			return array();
		
		return $data['userdata'];
	}
	
	/**
	 *	Find a string from user data to identify a returning anon user
	 *  @return string
	 *  @since 3.0
	 */	
	function anon_user_identifier( $userdata ) {
		if ( !empty( $userdata['email'] ) )
			return $userdata['email'];
		
		if ( !empty( $userdata['name'] ) )
			return $userdata['name'];

		$first_name = !empty( $userdata['first_name'] ) ? $userdata['first_name'] : '';
		$last_name = !empty( $userdata['last_name'] ) ? $userdata['last_name'] : '';
		
		if ( '' != $first_name.$last_name )
			return $first_name .' '. $last_name;

		if ( !empty( $userdata['phone'] ) )
			return $userdata['phone'];
		
		if ( !empty( $userdata['timestamp'] ) )
			return $userdata['timestamp'];
		
		// If no userdata provided salt will also work
		return wpb_get_salt();
	}

	/**
	 * Save admin edited client values
	 * @return array|false
	 */		
	function inline_edit_save( $result_app_id ) {
		$user_data = json_decode( wp_unslash($_POST['app_user_data']), true );
		if ( !is_array( $user_data ) )
			return false;
		
		$result = false;
		$user_data['name']	= $_POST['name'];	// Exceptional, because field name is "cname"
		
		foreach( $this->a->get_user_fields() as $f ) {
			if ( wpb_update_app_meta( $result_app_id, $f, $user_data[$f] ) )
				$result = $user_data;
		}
		
		if ( wpb_update_app_meta( $result_app_id, 'note', $_POST['note'] ) )
			$result = $user_data;
		
		if ( $result )
			wpb_flush_cache();
		
		return $result;

	}

	/**
	 *	Add tab
	 */
	function add_tab( $tabs ) {
		$tabs['user'] = __('Users', 'wp-base');
		
		return $tabs;
	}
	
	function save_settings() {
		if ( isset( $_POST["profileuser_id"] ) ) {
			$result = $this->save_profile();
		}

		if ( 'save_user' == $_POST["action_app"] ) {
			$options = wpb_setting();
			
			if ( isset( $_POST["login_required"] ) ) {
				$options["login_required"]				= $_POST["login_required"];
			}
			
			$options["default_worker"]				= !empty( $_POST["default_worker"] ) ? $_POST["default_worker"] : $this->a->get_default_worker_id();

			$options["auto_register_client"]		= $_POST["auto_register_client"];
			$options["auto_register_client_notify"]	= $_POST["auto_register_client_notify"];
			$options["auto_register_login"]			= $_POST["auto_register_login"];
			
			if ( $this->a->update_options( $options ) ) {
				wpb_notice( 'saved' );
			}
		}			
	}

	/**
	 *	Change display of dropdown
	 */
	function dropdown_users_display( $display, $user ) {
		$app_user = BASE('User')->_get_userdata( $user->ID );
		return BASE('User')->get_name( $user->ID ) . " (" . $app_user->user_email .")";	
	}

	/**
	 *	Display HTML codes
	 */
	function settings() {
	?>
		<div id="poststuff" class="metabox-holder">
		<form class="app_form" method="post" action="<?php echo wpb_add_query_arg( null, null ) ?>" >
			<div class="postbox">
			<h3 class="hndle"><span class="dashicons dashicons-admin-users"></span><span><?php _e('Users', 'wp-base') ?></span></h3>
				<div class="inside">
				
				<table class="form-table">
				
					<?php
						if ( !BASE('Login') ) {
							wpb_login_required_setting();
						}
					?>
					
					<tr id="default-worker">
						<th scope="row" ><?php WpBConstant::echo_setting_name('default_worker') ?></th>
						<td>
						<?php
						add_filter( 'app_dropdown_users_display', array( $this, 'dropdown_users_display' ), 10, 2 ); 

						BASE('user')->app_dropdown_users( array( 'show'=>'user_login', 'selected' => wpb_setting("default_worker") ? wpb_setting("default_worker") : 0, 'name'=>'default_worker' ) );
						
						remove_filter( 'app_dropdown_users_display', array( $this, 'dropdown_users_display' ) );
						?>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('default_worker') ?></span>
						</td>
					</tr>

					<tr id="auto-register-client">
						<th scope="row" ><?php WpBConstant::echo_setting_name('auto_register_client') ?></th>
						<td>
						<select name="auto_register_client">
						<option value="no" <?php if ( wpb_setting('auto_register_client') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
						<option value="yes" <?php if ( wpb_setting('auto_register_client') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
						</select>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('auto_register_client') ?></span>
						</td>
					</tr>

					<tr id="auto-register-client-notify">
						<th scope="row" ><?php WpBConstant::echo_setting_name('auto_register_client_notify') ?></th>
						<td>
						<select name="auto_register_client_notify">
						<option value="no" <?php if ( wpb_setting('auto_register_client_notify') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
						<option value="yes" <?php if ( wpb_setting('auto_register_client_notify') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
						</select>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('auto_register_client_notify') ?></span>
						</td>
					</tr>

					<tr>
						<th scope="row" ><?php WpBConstant::echo_setting_name('auto_register_login') ?></th>
						<td>
						<select name="auto_register_login">
						<option value="no" <?php if ( wpb_setting('auto_register_login') <> 'yes' ) echo "selected='selected'"?>><?php _e('No', 'wp-base')?></option>
						<option value="yes" <?php if ( wpb_setting('auto_register_login') == 'yes' ) echo "selected='selected'"?>><?php _e('Yes', 'wp-base')?></option>
						</select>
						<span class="description app_bottom"><?php WpBConstant::echo_setting_desc('auto_register_login') ?></span>
						</td>
					</tr>


				</table>
				</div>
			</div>
			<input type="hidden" name="action_app" value="save_user" />
			<?php wp_nonce_field( 'update_app_settings', 'app_nonce' ); ?>         
			<p class="submit">
			<input class="button-primary app-save-payment-btn" type="submit" name="submit_settings" value="<?php _e('Save Settings', 'wp-base') ?>" />
			</p>
		</form>
		</div>		
	<?php
	}
	
	function admin_menu(){
		add_users_page( 'Your Bookings', __('Your Bookings','wp-base'), 'read',  'your_appointments', array( $this,'show_profile') );	
	}

	/**
	 * Deletes a client/worker's database records in case he is deleted
	 * @param int|null $reassign ID of the user to reassign posts and links to.
	 * @since 1.0.4
	 */
	function user_deleted( $ID, $reassign=null ) {
		if ( !$ID )
			return;
			
		$r1 = BASE('WH')->remove( $ID, 'worker' );
		
		$assign_to = $reassign && $this->a->is_worker( $reassign ) ? $reassign : 0;
		
		// Also modify app table
		$r2 = $this->a->db->update( $this->app_table,
						array( 'worker'	=> $assign_to ),
						array( 'worker'	=> $ID )
					);
					
		$r3 = $this->a->db->update( $this->a->app_table,
						array( 'user'	=> $reassign ),
						array( 'user'	=> $ID )
					);

		if ( $r1 || $r2 || $r3 )
			wpb_flush_cache();
	}

	/**
	 * Saves changes in user profile
	 */
	function save_profile( $bp_user_id=false ) {
		$current_user_id = get_current_user_id();
		
		if ( $bp_user_id )
			$profileuser_id = $bp_user_id;
		else {
			if ( isset( $_POST['profileuser_id'] ) )
				$profileuser_id = $_POST['profileuser_id'];
			else if ( $_POST['app_bp_settings_user'] )
				$profileuser_id = $_POST['app_bp_settings_user'];
		}
		
		// Only user himself can save his data
		if ( $current_user_id != $profileuser_id )
			return;
		
		if ( wpb_is_demo() ) {
			wpb_notice( 'demo' );
			return;
		}

		$r = false;
		// Save user meta
		foreach ( $this->get_fields() as $f ) {
			if ( isset( $_POST['app_'.$f] ) )
				if ( update_user_meta( $profileuser_id, 'app_'.$f, $_POST['app_'.$f] ) )
					$r = true;
		}
			
		if ( $r ) {
			wpb_notice( 'saved' );
		}

		// Client or worker cancels own appointment
		if ( 'yes' == wpb_setting('allow_cancel') && !empty( $_POST['app_cancel'] ) && is_array( $_POST['app_cancel'] ) ) {
			foreach ( $_POST['app_cancel'] as $app_id=>$value ) {
				if ( $this->a->change_status( 'removed', $app_id ) ) {
					$app = $this->a->get_app( $app_id );
					if ( $app->worker && $app->worker == get_current_user_id() ) {
						$meta_val = 'worker_cancelled';
						$text = sprintf( __('Provider %1$s cancelled booking with ID: %2$s','wp-base'), $this->a->get_worker_name( $app->worker ), $app_id );
					}
					else {
						$meta_val = 'cancelled';
						$text = sprintf( __('Client %1$s cancelled booking with ID: %2$s','wp-base'), $this->get_client_name( $app_id, null, false ), $app_id );
					}
					
					wpb_update_app_meta( $app_id, 'abandoned', $meta_val );
					$this->a->log( $text );
					$this->a->send_notification( $app_id, true ); // This is always sent regardless of email settings
				}	
			}
			wp_redirect( wpb_add_query_arg( 'app_cancelled', $app_id ) );
			exit;
		}
		
		do_action( 'app_save_profile', $profileuser_id );
	
		// Only user who is a worker can save the rest
		if ( !$this->a->is_worker( $profileuser_id ) )
			return $r;
	
		// Confirm an appointment using profile page
		if ( 'yes' == wpb_setting('allow_worker_confirm') && isset( $_POST['app_confirm'] ) && is_array( $_POST['app_confirm'] ) && !empty( $_POST['app_confirm'] ) ) {
			foreach ( $_POST['app_confirm'] as $app_id=>$value ) {
				if ( $this->a->change_status( 'confirmed', $app_id, false ) ) {
					$this->a->log( sprintf( __('Service Provider %1$s manually confirmed appointment with ID: %2$s','wp-base'), $this->get_worker_name( $current_user_id ), $app_id ) );
					$this->a->maybe_send_message( $app_id, 'confirmation' );
				
					wpb_notice( __( 'Selected appointment has been confirmed', 'wp-base' ) );

				}	
			}
		}
		
		// Save working hours table
		if ( 'save_working_hours' == $_POST["action_app"] && 'yes' == wpb_setting("allow_worker_wh") ) {

			$r = BASE('WH')->save_wh( $profileuser_id );

		}
		
		return $r;
	}
	
	/**
	 * Displays appointment schedule on the user profile 
	 */
	function show_profile( $profileuser=false ) {
		$current_user_id = get_current_user_id();
		$profileuser_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : $current_user_id;
		
		// Only user or admin can see his data
		if ( $current_user_id != $profileuser_id && !current_user_can('list_users') )
			return;
			
		// For other than user himself, display data as readonly
		$is_readonly = $current_user_id != $profileuser_id ? ' readonly="readonly"' : '';
		$is_readonly = apply_filters( 'app_show_profile_readonly', $is_readonly, $profileuser_id );
		$gcal = 'yes' == wpb_setting("gcal") ? '' : ' gcal="0"'; // Default is already enabled
	?>
		<div class="wrap">

		<h2 class="app-dashicons-before dashicons-id-alt"><?php printf( __('%s Bookings & Settings', 'wp-base'), WPB_NAME ); ?></h2>
		
			<h3 class="nav-tab-wrapper">
				<?php

				if ( $this->a->is_worker( $profileuser_id ) ) {
					$tab = ( !empty($_GET['tab']) ) ? $_GET['tab'] : 'weekly';
					$class = ( 'weekly' == $tab ) ? ' nav-tab-active' : '';
					$tabs = array(
						'weekly'      	=> __('Weekly Schedule', 'wp-base'),
						'4weeks' 	    => __('4 Weeks Schedule', 'wp-base'),
						'monthly' 	    => __('Monthly Schedule', 'wp-base'),
						'3months'		=> __('3 Months Schedule', 'wp-base'),
						'appointments'	=> __('Bookings', 'wp-base'),
						'services'		=> __('Services', 'wp-base'),
						'working_hours'	=> __('Working Hours', 'wp-base'),
						'holidays'		=> __('Holidays', 'wp-base'),
						'annual'		=> __('Annual Schedules', 'wp-base'),
						'gcal'			=> __('Google Calendar', 'wp-base'),
						'settings'		=> __('Profile Settings', 'wp-base'),
					);
					if ( !( 'yes'== wpb_setting('allow_worker_create_service') && wpb_admin_access_check( 'manage_own_services', false ) ) )
						unset( $tabs['services'] );
					if ( !( 'yes' == wpb_setting("allow_worker_wh") && wpb_admin_access_check( 'manage_own_work_hours', false ) ) ) {
						unset( $tabs['working_hours'] );
						unset( $tabs['holidays'] );
					}
					// TODO: Move this to Annual
					if ( !( BASE('Annual') && 'yes' == wpb_setting('allow_worker_annual') && wpb_admin_access_check( 'manage_own_work_hours', false ) ) )
						unset( $tabs['annual'] );
					// TODO: Move this to GCal
					if( !( BASE('GCal') && 'yes' == wpb_setting("gcal_api_allow_worker") ) )
						unset( $tabs['gcal'] );

				}
				else {
					$tab = ( !empty($_GET['tab']) ) ? $_GET['tab'] : 'settings';
					$class = ( 'settings' == $tab ) ? ' nav-tab-active' : '';
					$tabs = array(
						'appointments'	=> __('Bookings', 'wp-base'),
						'gcal'			=> __('Google Calendar', 'wp-base'),
						'settings'		=> __('Settings', 'wp-base'),
					);
					// TODO: Move this to GCal
					$allow = wpb_setting('gcal_api_allow_client');
					if( !BASE('GCal') || !( 'yes' == $allow || ('members_only'==$allow && BASE('GCal')->is_member($profileuser_id) )) )
						unset( $tabs['gcal'] );					
				}
				
				$tabhtml = array();

				$tabs = apply_filters( 'appointments_user_profile_tabs', $tabs, $profileuser_id );

				foreach ( $tabs as $stub => $title ) {
					$class = ( $stub == $tab ) ? ' nav-tab-active' : '';
					$href = admin_url( 'users.php?page=your_appointments&tab=' . $stub );
					$tabhtml[] = '	<a href="' . $href . '" class="nav-tab'.$class.'" id="app_tab_'.$stub.'">'.$title.'</a>';
				}

				echo implode( "\n", $tabhtml );
				?>
			</h3>
			<div class="clear"></div>

			<?php 
			$user_id = $profileuser_id;
			
			switch( $tab ) {
				case 'weekly'	:
					if( !$this->a->is_worker( $profileuser_id ) )
						break;
					?>
					<div id="poststuff" class="metabox-holder">
						<div class="postbox">
							<div class="inside">
							<?php
							echo $this->a->pagination( array( 'select_date'=>1, 'disable_legend'=>1 ) );
							$wscodes = BASE('Schedules')->weekly_shortcodes(1, $profileuser_id);
							echo current( $wscodes );
							echo $this->a->pagination( array( 'select_date'=>0, ) );
							?>
							</div>
						</div>		
					</div>
					<?php
					break;
				
				case '4weeks':
					if( !$this->a->is_worker( $profileuser_id ) )
						break;
					?>
					<div id="poststuff" class="metabox-holder">
						<div class="postbox">
							<div class="inside">
							<?php
							echo $this->a->pagination( array( 'select_date'=>1, 'disable_legend'=>1, 'step'=>4 ) );
							foreach( BASE('Schedules')->weekly_shortcodes(4, $profileuser_id) as $scode ) {
								echo $scode;
							}
							echo '<div style="clear:both"></div>';
							echo $this->a->pagination( array( 'select_date'=>0, 'step'=>4 ) );
							?>
							</div>
						</div>		
					</div>
					<?php
					break;

				case 'monthly':
					if( !$this->a->is_worker( $profileuser_id ) )
						break;
					?>
					<div id="poststuff" class="metabox-holder">
						<div class="postbox">
							<div class="inside">
							<?php
							echo $this->a->pagination( array( 'select_date'=>1, 'disable_legend'=>1, 'unit'=>'month','step'=>1 ) );
							$mscodes = BASE('Schedules')->monthly_shortcodes(1, $profileuser_id);
							echo $mscodes[0];
							echo $this->a->pagination( array( 'select_date'=>0, 'unit'=>'month','step'=>1 ) );
							?>
							</div>
						</div>		
					</div>
					<?php
					break;
					
				case '3months':
					if( !$this->a->is_worker( $profileuser_id ) )
						break;
					?>
					<div id="poststuff" class="metabox-holder">
						<div class="postbox">
							<div class="inside">
							<?php
							echo $this->a->pagination( array( 'select_date'=>1, 'disable_legend'=>1, 'unit'=>'month','step'=>3 ) );
							foreach( BASE('Schedules')->monthly_shortcodes(3, $profileuser_id) as $scode ) {
								echo $scode;
							}
							echo '<div style="clear:both"></div>';
							echo $this->a->pagination( array( 'select_date'=>0, 'unit'=>'month','step'=>3 ) );			
							?>
							</div>
						</div>		
					</div>
					<?php
					break;

				case 'appointments':
					?><div id="poststuff" class="metabox-holder">
						<div class="postbox">
							<div class="inside">
							<form id="app-your-profile" method="post"> <?php
							if ( $this->a->is_worker( $profileuser_id ) ) {
								echo $this->a->listing( array( 'what'=>'worker', 'columns'=>'id,service,client,date_time,status,cancel,confirm' ) );
							}
							else {
								echo $this->a->listing( );
							}
						?> 
							<input type="hidden" name="action_app" value="save_profile" />
							<input type="hidden" name="profileuser_id" value="<?php echo $profileuser_id ?>" />
							<?php wp_nonce_field( 'update_app_settings', 'app_nonce' ); ?>
							</form>
			
							</div>
						</div>		
					</div>
								
					<?php
					break;
					
				case 'services':
						if ( 'yes'!= wpb_setting('allow_worker_create_service') )
							break;	
						
						BASE('AdminServices')->services_tab( $profileuser_id );

					break;
					
				case 'working_hours':
					if( !$this->a->is_worker( $profileuser_id ) || 'yes' != wpb_setting("allow_worker_wh") )
						break;

					BASE('WH')->render_tab( $profileuser_id );
					
					break;
					
				case 'holidays':
					if( !$this->a->is_worker( $profileuser_id ) || 'yes' != wpb_setting("allow_worker_wh") )
						break;

					BASE('Holidays')->render_tab( $profileuser_id );
					
					break;

				case 'annual':
					if( !$this->a->is_worker( $profileuser_id ) || !class_exists('WpBAnnual') || 'yes' != wpb_setting("allow_worker_annual") )
						break;
	
					BASE('Annual')->render_tab( $profileuser_id );
					
					break;
					
				case 'gcal':
					$uid = $profileuser_id;
					
					if ( !BASE('GCal') )
						break;
					
					if ( $this->a->is_worker( $uid ) ) {
						if ( 'yes' != wpb_setting('gcal_api_allow_worker') )
							break;
					}
					else {
						$allow = wpb_setting('gcal_api_allow_client');
						if( !( 'yes' == $allow || ('members_only'==$allow && BASE('GCal')->is_member($uid)) ) )
							break;
					}
					?>
					<div id="poststuff" class="metabox-holder">
					
					<?php BASE('GCal')->settings_for_own_account( $profileuser_id ); ?>
					
					</div>
					<?php
					break;
					
				case 'settings':
				?>
					<form id="app-your-profile" method="post">
						<div id="poststuff" class="metabox-holder">
							<div class="postbox">
								<div class="inside">
									<table class="form-table">
									<?php foreach( $this->get_fields() as $f ) { ?>
										<tr>
										<th><label><?php echo $this->a->get_text($f) ?></label></th>
										<td>
										<input type="text" name="app_<?php echo $f?>" value="<?php echo get_user_meta( $profileuser_id, 'app_'.$f, true ) ?>" <?php echo $is_readonly ?> />
										</td>
										</tr>
									<?php } ?>
									
									<?php do_action( 'app_show_profile', $profileuser_id ) ?>

									</table>
						
								</div>
							</div>
								<?php do_action( 'app_show_profile_outer', $profileuser_id ) ?>
								<input type="hidden" name="action_app" value="save_profile" />
								<input type="hidden" name="profileuser_id" value="<?php echo $profileuser_id ?>" />
								<?php wp_nonce_field( 'update_app_settings', 'app_nonce' ); ?>
								<p class="submit">
								<input type="submit" class="button-primary" value="<?php _e('Save Profile Settings', 'wp-base') ?>" />
								</p>		
						</div>
					</form>

				<?php
				break;
			}	?>

		</div>
		<script type='text/javascript'>
		jQuery(document).ready(function($){
		<?php if ( 'yes' == wpb_setting('allow_cancel') ) { ?>
			$('.app-list-cancel').click(function(e) {
				e.preventDefault();
				if ( !confirm('<?php echo esc_js( __("Are you sure to cancel the selected appointment?",'wp-base') ) ?>') ) 
					{return false;}
				else {
					var form = $('#app-your-profile');
					var self= $(this),
					tempElement = $("<input type='hidden'/>");

					tempElement
						.attr("name", self.attr("name"))
						.val(self.val())
						.appendTo(form);
					form.submit();
				}
			});
		<?php }
			if ( 'yes' == wpb_setting('allow_worker_confirm') ) { ?>
				$(document).on("click", ".app-list-confirm", function(e) {
					e.preventDefault();
					if ( !confirm('<?php echo esc_js( __("Are you sure to confirm the selected appointment?",'wp-base') ) ?>') ) 
						{return false;}
					else {
						/* http://stackoverflow.com/questions/4605671/jquery-submit-doesnt-include-submitted-button */
						var form = $('#app-your-profile');
						var self= $(this),
						tempElement = $("<input type='hidden'/>");

						tempElement
							.attr("name", self.attr("name"))
							.val(self.val())
							.appendTo(form);
						form.submit();
					}
				});
		<?php } ?>
		});
		</script>
	<?php
	}
	
	public function get_fields(){
		return apply_filters( 'app_user_fields', array('name', 'first_name', 'last_name', 'email', 'phone', 'address', 'city', 'zip') );
	}
	
	/**
	 * Create a user from an array of fields ($data['email'], $data['name'], etc ).
	 * If user already exists, returns his ID
	 * Also save user meta
	 * @param $notify_admin	bool		Whether notify admin 
	 * @param $notify_user	bool|null	Whether notify user (If null, auto_register_client_notify is in effect. If false, user not notified. If true, user is notified) 
	 * @return mix		false|integer	User ID on success, false on failure
	 */
	function create_user( $data, $notify_admin=true, $notify_user=null ) {
		foreach( $this->get_fields() as $f ) {
			${$f} = !empty( $data[$f] ) ? $data[$f] : '';
		}
		
		$user_id = false;
		$wp_user = get_user_by( 'email', $email );
		
		if ( empty( $wp_user->ID ) ) {
			$password = empty( $data['password'] ) ? wp_generate_password(12, false) : $data['password'];
			$username = $name
				? preg_replace('/[^_0-9a-z]/i', '_', strtolower($name))
				: preg_replace('/[^_0-9a-z]/i', '_', strtolower($first_name)) . '_' . preg_replace('/[^_0-9a-z]/i', '_', strtolower($last_name))
			;
			$count = 0;
			while (username_exists($username)) {
				$username .= rand(0,9);
				if (++$count > 10) break;
			}
			
			$user_id = wp_insert_user( array(
				'user_login'	=> $username,
				'user_pass'		=> $password,
				'user_email'	=> $email,
				'first_name'	=> $first_name,
				'last_name'		=> $last_name,
				) );
				
			if ( is_wp_error( $user_id ) ) {
				$this->a->log( $user_id->get_error_message() );
				return false;
			}
			
			if ( function_exists( 'add_new_user_to_blog' ) )
				add_new_user_to_blog( $user_id, $email, get_option('default_role') );
			
			$notify = ('yes' == wpb_setting('auto_register_client_notify') && false !== $notify_user ) || true === $notify_user ? ( $notify_admin ? 'both' : 'user' ) : ( $notify_admin ? 'admin' : 'none' );
			
			if ( !empty( $user_id ) && 'none' != $notify )
				wp_new_user_notification( $user_id, null, $notify );
			
			if ( !empty( $user_id ) ) {
				$new_user = new WP_user( $user_id );
				$new_user->add_role( 'wpb_client' );
			}
		}
		else
			$user_id = $wp_user->ID;
		
		if ( empty( $user_id ) )
			return false;
		
		foreach ( $this->get_fields() as $f ) {
			if ( !empty( $data[$f] ) )
				update_user_meta( $user_id, 'app_'.$f, $data[$f] );
		}

		return $user_id;
	}
	

}
	BASE('User')->add_hooks();
}