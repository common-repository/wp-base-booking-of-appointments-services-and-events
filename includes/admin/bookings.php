<?php
/**
 * WPB Admin Bookings
 *
 * Handles admin booking functions
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBAdminBookings' ) ) {
	
class WpBAdminBookings{

	/**
     * Bookings page identifier
     */
	public $bookings_page;

	/**
     * WP BASE Core + Front [+Admin] instance
     */
	protected $a = null;

	/**
     * Constructor
     */
	function __construct(){
		$this->a = BASE();
	}

	/**
     * Add admin actions
     */
	function add_hooks() {
		// Addsubmenu page
		add_action( 'app_menu_before_all', array( $this, 'add_menu' ) );
		add_action( 'app_menu_for_worker', array( $this, 'add_menu_for_worker' ) );
		
		// Column control
		add_filter( 'default_hidden_columns', array( $this, 'default_hidden_columns' ), 10, 2 );	// Hide columns when nothing has selected
		// Ajax
		add_action( 'wp_ajax_inline_edit', array( $this, 'inline_edit' ) ); 						// Add/edit appointments
		add_action( 'wp_ajax_inline_edit_save', array( $this, 'inline_edit_save' ) ); 				// Save edits
		add_action( 'wp_ajax_update_inline_edit', array( $this, 'update_inline_edit' ) ); 			// 
		add_action( 'wp_ajax_app_populate_user', array( $this, 'populate_user' ) );					// Populate user in New Booking
		add_action( 'wp_ajax_app_show_payment_in_tooltip', array( $this, 'show_payment_in_tooltip' ) );
	}	

	/**
     * Add menu and submenu page to main admin menu for admin
     */
	function add_menu(){
		$this->menu_page = add_menu_page('WP BASE', WPB_NAME, WPB_ADMIN_CAP,  'appointments', array($this,'appointment_list'),'dashicons-calendar', '25.672');
		add_filter( "manage_{$this->menu_page}_columns", array( $this, 'manage_columns' ) );
		
		$this->bookings_page = add_submenu_page('appointments', __('WPB Bookings','wp-base'), __('Bookings','wp-base'), WPB_ADMIN_CAP, "appointments", array($this,'appointment_list'));
	}
	
	/**
     * Add menu and submenu page to main admin menu for worker
     */
	function add_menu_for_worker() {
		if ( empty( $this->menu_page ) ) {
			$def_caps = WpBConstant::get_default_caps();
			unset( $def_caps['manage_own_work_hours'] );
			unset( $def_caps['manage_own_services'] );
			
			wpb_add_menu_page('Appointments Bookings', WPB_NAME, $def_caps, 'appointments', array($this,'appointment_list'), 'dashicons-calendar', '25.672');
		}
		
		if ( empty( $this->bookings_page ) )
			add_submenu_page('appointments', __('WP BASE Bookings','wp-base'), __('Bookings','wp-base'), 'manage_own_bookings', "appointments", array($this,'appointment_list'));
	}
	
	/**
	 *	Define an array of allowed columns in bookings page
	 *	Called after addons loaded
	 */		
	function get_allowed_columns() {
		$allowed = array( 'delete','client','id','created','created_by','email','phone','city','address','zip','note','date_time','date','day','time','end_date_time','location','location_note','service','worker','status','price','deposit','total_paid','balance' );
		return apply_filters( 'app_bookings_allowed_columns', $allowed, '' );
	}
	
	/**
     * Add some columns as hidden to Bookings page if there is no selection before
     */
	function default_hidden_columns( $hidden, $screen ) {
		if ( !(isset( $screen->id ) && 'toplevel_page_appointments' == $screen->id) )
			return $hidden;
		
		$hidden_cols = array('created','created_by','email','phone','city','address','zip','note','date','day','time','end_date_time','location','location_note','price','deposit','total_paid','balance');
		if ( !empty( $hidden_cols ) )
			$hidden = is_array( $hidden ) ? array_unique( array_merge( $hidden, $hidden_cols ) ) : $hidden_cols;
		
		return $hidden;
	}

	/**
	 *	Add hide checkboxes to admin screen options
	 *	Output is fed to manage_{screen}_columns filter
	 *	@param $arr is irrelevant
	 *  @since 2.0
	 *	@return array
	 */		
	function manage_columns( $arr ) {
	 
		// Get out of here if we are not on our settings page
		if ( !is_admin() || empty( $this->menu_page ) || wpb_get_current_screen_id( ) != $this->menu_page )
			return;
		
		$args = array();

		// We don't yet know which columns are actually used, because they can be controlled by a shortcode that is happening after this function call.
		// Therefore we will hide them by js later on in myapps method.
		foreach ( $this->get_allowed_columns() as $col ) {
			if ( 'worker' == $col )
				$args['worker']	= $this->a->get_text('provider');
			else if ( 'id' == $col )
				$args['id']	= $this->a->get_text('app_id');
			else if ( 'udf_' == substr( $col, 0, 4 ) )
				$args[$col]	= apply_filters('app_list_udf_column_title','',$col); 
			else
				$args[$col]	= $this->a->get_text($col);
		}
		
		return $args;
	}

	/**
	 *	Creates the list for Appointments admin page
	 *  Also used by Front End Management addon
	 */		
	function appointment_list(  $return			= false,	// true also means called by [app_manage] 
								$only_own		= 0, 
								$allow_delete	= 1, 
								$count			= 0, 
								$override		= 'inherit', 
								$status			= 'any', 
								$columns		= '', 
								$columns_mobile	= '', 
								$add_export		= 0, 
								$sel_location	= 0,
								$febm_cap 		= ''	// FEBM capability
							) {
									
		global $type, $wpdb, $wp_locale, $post, $current_user;
		
		if ( !(current_user_can( WPB_ADMIN_CAP ) || ($this->a->is_worker( $current_user->ID ) && 'yes' === wpb_setting('allow_worker_edit') && wpb_current_user_can( 'manage_own_bookings' ) )
			|| ( $return && current_user_can( $febm_cap ) )) )
			wp_die( __('You do not have sufficient permissions to access this page.','wp-base') );
			
		
		// If worker allowed to edit his appointments, show only his.
		if ( wpb_is_admin() && !current_user_can( WPB_ADMIN_CAP) )
			$only_own = 1;
		
		// Check if such a location exists
		$sel_location = $sel_location && $this->a->location_exists( $sel_location ) ? $sel_location : 0;

		/* Find which status is called */
		wp_reset_vars( array('type') );

		$stat = explode( ',', wpb_sanitize_commas( $status ) );

		// Set status if it is not determined by $_GET['type']
		if( empty($type) ) {
			if( 'all' == $status ) 
				$type = 'all';
			else if ( in_array( 'paid', $stat ) || in_array( 'confirmed', $stat ) )
				$type = 'active';
			else if ( !empty( $stat ) && in_array( current( $stat ), array_keys( $this->a->get_statuses() ) ) )
				$type = current( $stat );
		}
		
		$type = empty( $type ) ? 'all' : $type;
		
		// Limit statuses acc to front end selection + Exclude invalid statuses
		if ( in_array( 'any', $stat, true ) )
			$statii = $this->a->get_statuses();
		else
			$statii = array_intersect_key( $this->a->get_statuses(), array_flip( $stat ) );
		// $statii is all statuses, $stat is the selected status
		$statii = apply_filters( 'app_admin_statuses', $statii, $stat ); 

		/* Search */
		$filter = array();
		if ( isset( $_GET['app_s'] ) ) {
			$s = stripslashes( $_GET['app_s'] );
			$filter['s'] = $s;
		} else {
			$s = '';
		}
		
		/* Search type */
		if ( isset( $_GET['stype'] ) ) {
			$stype = $_GET['stype'];
		} else {
			$stype = '';
		}
		
		$s = apply_filters( 'app_search', $s, $stype );
		
		// User filter/sort preferences
		$pref = get_user_meta( $current_user->ID, 'app_admin_pref', true );
		$pref = is_array( $pref ) ? $pref : array();
		
		foreach ( array( 'location_id', 'service_id', 'worker_id', 'order_by', 'balance','m' ) as $i ) {
			if( isset( $_GET['app_'.$i] ) )
				${$i} = $_GET['app_'.$i];
			else if ( isset( $pref[$i] ) )
				${$i} = $pref[$i];
			else
				${$i} =  '';			
		}
		$worker_id = is_numeric($worker_id) ? (int)$worker_id : '';
		$balance_sel = $balance;
		$app_m = $m;
		
		ob_start();
		
		if ( wpb_is_mobile() )
			$align = "alignright";
		else
			$align = "alignleft";
		
		?>
	<div class="wrap app-page clearfix">
		<h2 class="app-dashicons-before dashicons-analytics"><?php echo __('Bookings','wp-base'); ?><a href="javascript:void(0)" class="add-new-h2"><?php _e('Add New', 'wp-base')?></a>
		<img class="add-new-waiting" style="display:none;" src="<?php echo admin_url('images/wpspin_light.gif')?>" alt="">
		</h2>
		
		<div class="app-manage-row clearfix">
			<div class="alignleft actions app-manage-first-column">
				<ul class="subsubsub">
					<?php 
						if ( wpb_is_admin() || 'all' == $type || in_array( 'all', $stat ) ) { ?>
						<li><a style="background-color:#F2F2F2;" href="<?php echo wpb_add_query_arg(array('type'=>'all','paged'=>false,'add_new'=>false,'cpy_from'=>false)); ?>" class="rbutton <?php if($type == 'all') echo 'current'; ?>"><?php  _e('All', 'wp-base'); ?></a> | </li>
					<?php }
					foreach ( $statii as $status=>$text ) { 
						if ( 'paid' == $status ) {
					?>
						<li ><a style="background-color:#F2F5A9;" href="<?php echo wpb_add_query_arg(array('type'=>'active','paged'=>false,'add_new'=>false,'cpy_from'=>false)); ?>" class="rbutton <?php if($type == 'active') echo 'current'; ?>"><?php  _e('Upcoming', 'wp-base'); ?></a> | </li>
					<?php }
						else if ( 'confirmed' != $status ) { 
							$bg = '';
							if ( 'pending' == $status || 'reserved' == $status )
								$bg= ' style="background-color:#F2F5A9;"';
							else if ( 'running' == $status )
								$bg= ' style="background-color:#BCF5A9;"';
							else if ( 'completed' == $status || 'removed' == $status )
								$bg= ' style="background-color:#F5A9A9;"';
							else
								$bg= ' style="background-color:#F2F2F2;"';
						?>
						<li ><a <?php echo $bg?> href="<?php echo wpb_add_query_arg(array('type'=>$status,'paged'=>false,'add_new'=>false,'cpy_from'=>false)); ?>" class="rbutton <?php if($type == $status ) echo 'current'; ?>"><?php echo $text; ?></a> | </li>
					<?php }
					} ?>
					<li><a href="javascript:void(0)" class="info-button" title="<?php _e('Click to toggle information about statuses', 'wp-base')?>"><img src="<?php echo WPB_PLUGIN_URL . '/images/information.png'?>" alt="" /></a></li>
				</ul>
			</div>
		
			<div class="alignright actions app-manage-second-column">
			<form id="app-search-form" method="get" action="<?php echo wpb_add_query_arg('page', 'appointments'); ?>" class="search-form">
				<input type="hidden" value="appointments" name="page" />
				<input type="hidden" value="all" name="type" />
				<input type="hidden" value="1" name="app_or_fltr" />
				<input type="text" value="<?php echo esc_attr($s); ?>" name="app_s" placeholder="<?php _e('Enter a search term','wp-base'); ?>"/>
				<?php $add_class = $stype === 'app_id' ? 'class="app-option-selected"' : ''; ?>
				<select name="stype" <?php echo $add_class ?> title="<?php _e('Select which field to search. For appointment ID search, multiple IDs separated with comma or space is possible.','wp-base') ?>">
					<option value="name" <?php selected( $stype, 'name' ); ?>><?php _e('Client Name','wp-base'); ?></option>
					<option value="app_id" <?php selected( $stype, 'app_id' ); ?> title="<?php _e('Multiple IDs separated with comma or space is possible','wp-base')?>"><?php _e('App. ID','wp-base'); ?></option>
					<option value="app_date" <?php selected( $stype, 'app_date' ); ?>><?php _e('App. Date','wp-base'); ?></option>
					<option value="email" <?php selected( $stype, 'email' ); ?>><?php _e('Email','wp-base'); ?></option>
					<option value="phone" <?php selected( $stype, 'phone' ); ?>><?php _e('Phone','wp-base'); ?></option>
					<option value="address" <?php selected( $stype, 'address' ); ?>><?php _e('Address','wp-base'); ?></option>
					<option value="city" <?php selected( $stype, 'city' ); ?>><?php _e('City','wp-base'); ?></option>
					<option value="note" <?php selected( $stype, 'note' ); ?>><?php _e('Note','wp-base'); ?></option>
					<option value="admin_note" <?php selected( $stype, 'admin_note' ); ?>><?php _e('Admin Note','wp-base'); ?></option>
					<?php do_action( 'app_search_options', $stype ) ?>
				</select>			
				<input type="submit" class="button app-search-button" value="<?php _e('Search','wp-base'); ?>" />
				<script type="text/javascript">
				jQuery(document).ready(function ($) {
					$("select[name='stype']").change( function() {
						dpicker();
					});
					function dpicker(){
						$this = $("select[name='stype']");
						var field = $this.parents("form").find("input[name='app_s']");
						if ( parseInt($this.length) > 0 && $this.val() == 'app_date' ){
							field.addClass("datepicker").datepicker({
								dateFormat:'yy-mm-dd',
								firstDay:_app_.start_of_week
							});
						}
						else {
							field.removeClass("datepicker").datepicker("destroy");
						}
					}
					dpicker();
				});
				</script>
			
			</form>
			</div>
		</div>
		
		<div class="postbox status-description" style="display:none;">
			<div class="inside description ">
			<ul>
				<?php
					foreach ( WpBConstant::app_desc() as $key=>$line ) {
						echo "<li>". $line . "</li>";
					}
				?>	
			</ul>
			</div>
		</div>

		
		<div class="tablenav top app-manage-first-row">
			<div class="alignright actions app-manage-first-column">
				<form id="app-reset-form" method="get" action="<?php echo wpb_add_query_arg('page', 'appointments'); ?>" >
					<input type="hidden" value="appointments" name="page" />
					<input type="hidden" value="<?php echo $type?>" name="type" />
					<input type="hidden" value="1" name="app_filter_reset" />
					<input type="submit" class="button" value="<?php _e('Reset','wp-base'); ?>" />
				</form>
			</div>
			
			<div class="alignright actions app-manage-second-column">
				<form id="app-filter-form" method="get" action="<?php echo wpb_add_query_arg('page', 'appointments'); ?>" >
					<?php
					do_action( 'app_admin_bookings_form_filter_pre' );
					
					switch($type) {
						case 'all':			$where = " WHERE 1=1 "; break;
						case 'running':		$where = " WHERE status IN ('running') "; break;
						case 'active':		$where = " WHERE status IN ('confirmed', 'paid') "; break;
						case 'pending':		$where = " WHERE status IN ('pending') "; break;
						case 'completed':	$where = " WHERE status IN ('completed') ";	break;
						case 'removed':		$where = " WHERE status IN ('removed') "; break;
						case 'reserved':	$where = " WHERE status IN ('reserved') "; break;
						default:			$where = $this->a->db->prepare( " WHERE status IN (%s) ", $type ); break;
					}
					
					$where = apply_filters( 'app_admin_apps_where', $where, $type );
					
					if ( $only_own )
						$where .= " AND worker={$current_user->ID} "; 

					if ( $sel_location )
						$where .= $this->a->db->prepare( " AND location=%d ", $sel_location ); 
					
					$months = $wpdb->get_results( "
						SELECT DISTINCT YEAR( start ) AS year, MONTH( start ) AS month
						FROM {$this->a->app_table}
						{$where}
						ORDER BY start
					" );

					$month_count = $months ? count( $months ) : 0;

					if ( $month_count && ( 1 != $month_count || 0 != $months[0]->month ) ) {
						$add_class = $app_m ? 'class="app-option-selected"' : '';
						
						$mode = $this->a->start_of_week ? 3 : 4;
						$weeks = $wpdb->get_results( "
							SELECT DISTINCT YEARWEEK( start, {$mode} ) AS yearweek
							FROM {$this->a->app_table}
							{$where}
							ORDER BY start
						" );
						?>
						<select name="app_m" <?php echo $add_class ?>>
							<option value=""><?php _e('Filter by month/week','wp-base'); ?></option>
							<optgroup label="<?php echo ucwords( $this->a->get_text('month') ) ?>">							
							<?php
							
							foreach ( $months as $arc_row ) {
								if ( 0 == $arc_row->year )
									continue;

								$month = zeroise( $arc_row->month, 2 );
								$year = $arc_row->year;

								printf( "<option %s value='%s'>%s</option>\n",
									selected( $app_m, $year . $month, false ),
									esc_attr( $arc_row->year . $month ),
									sprintf( __( '%1$s %2$d', 'wp-base' ), $wp_locale->get_month( $month ), $year )
								);
							}
							
							?>
							</optgroup>
							<optgroup label="<?php echo ucwords( $this->a->get_text('week') ) ?>">
							<?php
							
							foreach ( $weeks as $arc_row ) {
								if ( !$arc_row->yearweek )
									continue;

								$yearweek	= 'w'. $arc_row->yearweek;
								$year		= substr( $yearweek, 1, 4 );
								$week_no	= substr( $yearweek, -2 );
								$first_day	= 1 + ((7+$this->a->start_of_week - strftime("%w", mktime(0,0,0,1,1,$year)))%7);
								$start_ts	= mktime(0,0,0,1,$first_day,$year) + ($week_no-1)*7*24*3600;	
								$end_ts		= $start_ts + 6*24*3600;
								$start_date = date_i18n( "j M Y", (int)$start_ts );
								$end_date	= date_i18n( "j M Y", (int)$end_ts );
								
								printf( "<option %s value='%s'>%s</option>\n",
									selected( $app_m, $yearweek, false ),
									esc_attr( $yearweek ),
									sprintf( __( '%1$s - %2$s', 'wp-base' ), $start_date, $end_date )
								);
							}						
							?>
							</optgroup>
						</select>
					<?php } 						
				
					$add_class = $balance_sel ? 'class="app-option-selected"' : ''; ?>
					
					<select name="app_balance" <?php echo $add_class ?>>
						<option value=""><?php _e('Filter by balance','wp-base'); ?></option>
						<option value="negative" <?php selected($balance_sel,'negative') ?>><?php _e('Negative balance','wp-base'); ?></option>
						<option value="positive" <?php selected($balance_sel,'positive') ?>><?php _e('Positive balance','wp-base'); ?></option>
						<option value="zero" <?php selected($balance_sel,'zero') ?>><?php _e('Zero balance','wp-base'); ?></option>
					</select>

					<?php
					$locations = $this->a->get_locations( 'name' );
					if ( $locations && !$sel_location ) { 
						$add_class = $location_id ? 'class="app-option-selected"' : '';
						?>
						<select name="app_location_id" <?php echo $add_class ?>>
							<option value=""><?php _e('Filter by location','wp-base'); ?></option>
						<?php
							foreach ( $locations as $location ) {
								if ( $location_id == $location->ID )
									$selected = " selected='selected' ";
								else
									$selected = "";
								echo '<option '.$selected.' value="'.$location->ID.'">'. $this->a->get_location_name( $location->ID ) .'</option>';
							}
						?>
						</select>			
					<?php } 

						$add_class = $service_id ? 'class="app-option-selected"' : '';
					?>
					<select name="app_service_id" <?php echo $add_class ?>>
						<option value=""><?php _e('Filter by service','wp-base'); ?></option>
						<?php
						$services = $sel_location ? $this->a->get_services_by_location( $sel_location, 'name' ) : $this->a->get_services( 'name' );
						if ( $services ) {
							foreach ( $services as $service ) {
								if ( $this->a->is_package( $service->ID ) )
									continue;
								
								if ( $service_id == $service->ID )
									$selected = " selected='selected' ";
								else
									$selected = "";
								echo '<option '.$selected.' value="'.$service->ID.'">'. $this->a->get_service_name( $service->ID ) .'</option>';
							}
						}
						?>
					</select>			

					<?php
					$workers = $this->a->get_workers( 'name' );
					$add_class = $worker_id || "0" === (string)$worker_id ? 'class="app-option-selected"' : '';
					?>
					<select name="app_worker_id" <?php echo $add_class ?>>
						<option value=""><?php _e('Filter by service provider','wp-base'); ?></option>
					<?php if ( $workers ) {  ?>	
						<option value="0" <?php selected($worker_id,0) ?>><?php _e('Unassigned','wp-base'); ?></option>
					<?php	
						foreach ( $workers as $worker ) {
							if ( $worker_id == $worker->ID )
								$selected = " selected='selected' ";
							else
								$selected = "";
							echo '<option '.$selected.' value="'.$worker->ID.'">'. $this->a->get_worker_name( $worker->ID ) .'</option>';
						}
					}
					?>
					</select>
					
					<?php do_action( 'app_admin_bookings_form_filter' )  ?>

					<input type="hidden" value="appointments" name="page" />
					<input type="hidden" value="<?php echo $type?>" name="type" />						
					<input type="submit" class="button" value="<?php _e('Filter','wp-base'); ?>" />
				</form>
			</div>
		</div>
		

		<div class="tablenav top app-manage-second-row">
			<div class="<?php echo $align ?> actions app-manage-first-column">
				<form id="app-bulk-change-form" method="post" action="<?php echo wpb_add_query_arg('page', 'appointments'); ?>" >
					<input type="hidden" value="appointments" name="page" />
					<input type="hidden" value="<?php if ( isset( $post->ID ) ) echo $post->ID; else echo 0; ?>" name="page_id" />
					<input type="hidden" value="app_status_change" name="action_app" />
					<input type="hidden" value="1" name="app_status_change" />
					<?php wp_nonce_field( 'update_app_settings', 'app_nonce' ); ?>
					<select name="app_new_status">
						<option value=""><?php _e('Bulk status change','wp-base'); ?></option>
						<?php foreach ( $this->a->get_statuses() as $value=>$name ) {
								if ( 'running' == $value )
									continue;
							echo '<option value="'.$value.'">'.$name.'</option>';
						} ?>
					</select>			
					<input type="submit" class="button app-change-status-btn" value="<?php _e('Change','wp-base'); ?>" />
				</form>
			</div>
		
			<div class="<?php echo $align ?> actions app-manage-second-column">
				<form id="app-sort-form" method="get" action="<?php echo wpb_add_query_arg('page', 'appointments'); ?>" >
					<input type="hidden" value="appointments" name="page" />
					<input type="hidden" value="<?php echo $type ?>" name="type" />
					<input type="hidden" value="<?php echo $location_id ?>" name="app_location_id" />
					<input type="hidden" value="<?php echo $service_id ?>" name="app_service_id" />
					<input type="hidden" value="<?php echo $worker_id ?>" name="app_worker_id" />
					<input type="hidden" value="<?php echo $app_m ?>" name="app_m" />
					<input type="hidden" value="<?php echo $balance_sel ?>" name="app_balance" />
					<select name="app_order_by">
						<option value=""><?php _e('Sort by','wp-base'); ?></option>
						<option value="start" <?php selected( $order_by, 'start' ); ?>><?php _e('Appt. date (Earliest first)','wp-base'); ?></option>
						<option value="start_DESC" <?php selected( $order_by, 'start_DESC' ); ?>><?php _e('App. date (Latest first)','wp-base'); ?></option>
						<option value="ID" <?php selected( $order_by, 'ID' ); ?>><?php _e('ID (Lowest first)','wp-base'); ?></option>
						<option value="ID_DESC" <?php selected( $order_by, 'ID_DESC' ); ?>><?php _e('ID (Highest first)','wp-base'); ?></option>
					</select>			
					<input type="submit" class="button" value="<?php _e('Sort','wp-base'); ?>" />
				</form>
			</div>
			<?php
				if ( is_admin() )
					$paged = empty($_GET['paged']) ? 1 : (int)$_GET['paged'];
				else
					$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
				
				$rpp = $count ? $count : wpb_setting( 'records_per_page', 20 ); # Records per page

				$startat	= ($paged - 1) * $rpp;
				$apps		= $this->get_admin_apps($type, $startat, $rpp, $only_own, $sel_location);
				$total		= $this->get_apps_total( );
			
				if ( is_admin() || !get_option( 'permalink_structure' ) ) {
					$navigation = paginate_links( array(
						'base'		=> wpb_add_query_arg( 'paged', '%#%' ),
						'format'	=> '',
						'total'		=> ceil($total / $rpp),
						'current'	=> max( 1, $paged )
					));
				}
				else {
					$navigation =  paginate_links( array(
						'base'		=> str_replace( WPB_HUGE_NUMBER, '%#%', esc_url( get_pagenum_link( WPB_HUGE_NUMBER ) ) ),
						'format'	=> '/page/%#%',
						'current'	=> max( 1, $paged ),
						'total'		=> ceil($total / $rpp),
						'add_args'	=> array( 'page'=>false,),
					) );
				}
				
				if ( $navigation ) {
				?>	
					<div class="alignright actions app-manage-third-column">
						<div class='tablenav-pages'><?php echo $navigation ?></div>
					</div>
				<?php
				}
				?>

		</div>
		<?php
			$this->myapps($type, $only_own, $allow_delete, $count, $override, '', $columns, $columns_mobile, $startat, $rpp, $apps, $sel_location );
			
			if ( $add_export && BASE('EXIM') )
				BASE('EXIM')->export_csv_html( $this->a->get_text('export_csv'), $only_own );
		?>
	
	</div> <!-- wrap -->
		
		<script type="text/javascript">
		jQuery(document).ready(function($){
			$(".info-button").click(function(){
				$(".status-description").toggle('fast');
			});

			$(".app-change-status-btn").click(function(e){
				var button = $(this);
				e.preventDefault();
				$("td.column-delete input:checkbox:checked").each(function() {
					button.after('<input type="hidden" name="app[]" value="'+$(this).val()+'"/>');
				});
				$('#app-bulk-change-form').submit();
			});
		});
		</script>
		

		<?php
		
		if ( wpb_is_admin() )
			add_action( 'admin_footer', array( $this, 'footer' ) );
		else if ( 'yes' != wpb_setting( 'fem_disable_auto_adapt' ) )
			add_action( 'wp_footer', array( $this, 'footer' ) );
		
		$c = ob_get_contents();
		ob_end_clean();

		if ( $return )
			return $c;
		else
			echo $c;
	}
	
	/**
	 *	Add script to adjust control part of bookings table
	 */		
	function footer(){
		if ( !empty( $this->footer_script_added ) )
			return;
		
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($){	
			
			_app_.adjust_manage_controls();
			$(window).resize(function(){
				_app_.adjust_manage_controls();
			});
			
		});
		</script>
		<?php
		
		$this->footer_script_added = true;
	}

	/**
	 *	Return results for appointments
	 */		
	function get_admin_apps($type, $startat, $num, $only_own=0, $sel_location=0) {
	
		global $current_user;
	
		// Search. Also selecting of one or more bookings
		if( isset( $_GET['app_s'] ) && trim( $_GET['app_s'] ) != '' ) {
			$s = esc_sql( $_GET['app_s'] );
			$stype = esc_sql($_GET['stype']);
			$s = apply_filters( 'app_search', $s, $stype );
			switch ( $stype ) {
				case 'app_id':	if ( strpos( $s, ',' ) !== false ) {
									$ses = explode( ',', $s );
									$q = ' 1=2 OR ';
									foreach ( $ses as $s1 ) {
										$q .= " ID='".trim( $s1 )."' OR ";
									}
									$q = rtrim( $q, "OR " );
									$add = " AND (".$q.") "; 
								}
								else if ( strpos( $s, ' ' ) !== false ) {
									$ses = explode( ' ', $s );
									$q = ' 1=2 OR ';
									foreach ( $ses as $s1 ) {
										if ( '' == trim( $s1 ) )
											continue;
										$q .= " ID='".trim( $s1 )."' OR ";
									}
									$q = rtrim( $q, "OR " );
									$add = " AND (".$q.") "; 
								}
								else {
									$add = " AND ID='{$s}' ";
								}
								break;
				case 'app_date':$add = " AND DATE(start)='{$s}' "; break;				
				case 'name':	$add = " AND ( user IN ( SELECT ID FROM {$this->a->db->users} AS users WHERE user_login LIKE '%{$s}%' OR user_nicename LIKE '%{$s}%' OR ID IN ( SELECT user_id FROM {$this->a->db->usermeta} AS usermeta WHERE users.ID=usermeta.user_id AND meta_value LIKE '%{$s}%' AND (meta_key='first_name' OR meta_key='last_name')  ) ) ) "; break;
				case 'email':	$add = " AND ( user IN ( SELECT ID FROM {$this->a->db->users} WHERE user_email LIKE '%{$s}%' ) ) "; break;
				case 'phone':	$add = " AND ( user IN ( SELECT ID FROM {$this->a->db->users} WHERE ID IN ( SELECT user_id FROM {$this->a->db->usermeta} WHERE meta_key='app_phone' AND meta_value LIKE '%{$s}%' ) ) ) "; 
								break;
				case 'address':	$add = " AND ( user IN ( SELECT ID FROM {$this->a->db->users} WHERE ID IN ( SELECT user_id FROM {$this->a->db->usermeta} WHERE meta_key='app_address' AND meta_value LIKE '%{$s}%' ) ) ) ";
								break;
				case 'city':	$add = " AND ( user IN ( SELECT ID FROM {$this->a->db->users} WHERE ID IN ( SELECT user_id FROM {$this->a->db->usermeta} WHERE meta_key='app_city' AND meta_value LIKE '%{$s}%' ) ) ) ";
								break;
				case 'note':	$add = " AND ( ID IN ( SELECT object_id FROM {$this->a->meta_table} WHERE meta_type='app' AND meta_key='note' AND meta_value LIKE '%{$s}%' ) ) "; 
								break;
				case 'admin_note':	
								$add = " AND ( ID IN ( SELECT object_id FROM {$this->a->meta_table} WHERE meta_type='app' AND meta_key='admin_note' AND meta_value LIKE '%{$s}%' ) ) "; 
								break;
				default:		$add = apply_filters( 'app_search_switch', '', $stype, $s ); break;
			}
		}
		else
			$add = "";
			
		
		// User filter/sort preferences
		$pref = get_user_meta( $current_user->ID, 'app_admin_pref', true );
		$pref = is_array( $pref ) ? $pref : array();
		
		foreach ( array( 'location_id', 'service_id', 'worker_id', 'order_by', 'balance', 'm' ) as $i ) {
			if( isset( $_GET['app_'.$i] ) )
				${$i} = esc_sql( $_GET['app_'.$i] );
			else if ( isset( $pref[$i] ) )
				${$i} = esc_sql( $pref[$i] );
			else
				${$i} =  '';			
		}
		$balance_sel = $balance;
		$app_m = $m;

		if ( !isset( $_GET['app_or_fltr'] ) ) {
			// Filters		
			if ( $location_id )
				$add .= " AND location='". esc_sql( $location_id ) ."' ";

			if ( $service_id )
				$add .= " AND service='". esc_sql( $service_id ) ."' ";
			
			// Allow filtering for unassigned provider
			if ( $worker_id || "0" === (string)$worker_id ) 
				$add .= " AND worker='". esc_sql( $worker_id ) ."' ";
			
			if ( $only_own )
				$add .= " AND worker='{$current_user->ID}' ";
			
			if ( $app_m ) {
				// Year + Week
				if ( 'w' == substr( $app_m, 0, 1 ) ) {
					$mode = $this->a->start_of_week ? 3 : 4;
					$add .= " AND YEARWEEK(start,{$mode})='". substr( $app_m, 1 ) ."' ";
				}
				else {
					$year = (int)substr( $app_m, 0, 4 );
					$month = (int)substr( $app_m, 4, 2 );
					if ( $year && $month )
						$add .= " AND YEAR(start)='". $year ."' AND MONTH(start)='" .$month ."' ";
				}
			}
			
			if ( $balance_sel ) {
				if ( 'negative' == $balance_sel ) 
					$add .= " AND ( IFNULL((SELECT SUM(transaction_total_amount) FROM {$this->a->transaction_table} AS tr WHERE tr.transaction_app_ID=app.ID),0)/100 - IFNULL(price,0) - IFNULL(deposit,0) < 0 ) ";
				else if ( 'positive' == $balance_sel ) 
					$add .= " AND ( IFNULL((SELECT SUM(transaction_total_amount) FROM {$this->a->transaction_table} AS tr WHERE tr.transaction_app_ID=app.ID),0)/100 - IFNULL(price,0) - IFNULL(deposit,0) > 0 ) ";
				else if ( 'zero' == $balance_sel ) 
					$add .= " AND ( IFNULL((SELECT SUM(transaction_total_amount) FROM {$this->a->transaction_table} AS tr WHERE tr.transaction_app_ID=app.ID),0)/100 - IFNULL(price,0) - IFNULL(deposit,0) = 0 ) ";
			}
		}
		
		if ( $sel_location )
			$add .= " AND location='". esc_sql($sel_location)."' ";
		
		// Sanitize Order by
		$test = str_replace( array(' desc', ' inc', ' ' ), '', strtolower( $order_by ) );
		if ( $test && in_array( $test, array('id','start', 'id_desc', 'start_desc') ) )
			$order_by = str_replace( '_', ' ', $order_by );
		else
			$order_by = "ID DESC";
		
		$add = apply_filters( 'app_admin_apps_sql_add', $add, $type, $order_by, $startat, $num );

		switch($type) {
			case 'all':
						$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$this->a->app_table} AS app WHERE 1=1 {$add} ORDER BY {$order_by} LIMIT {$startat}, {$num}";
						break;
			case 'running':
						$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$this->a->app_table} AS app WHERE status IN ('running') {$add} ORDER BY {$order_by} LIMIT {$startat}, {$num}";
						break;
			case 'active':
						$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$this->a->app_table} AS app WHERE status IN ('confirmed', 'paid') {$add} ORDER BY {$order_by} LIMIT {$startat}, {$num}";
						break;
			case 'pending':
						$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$this->a->app_table} AS app WHERE status IN ('pending') {$add} ORDER BY {$order_by} LIMIT {$startat}, {$num}";
						break;
			case 'completed':
						$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$this->a->app_table} AS app WHERE status IN ('completed') {$add} ORDER BY {$order_by} LIMIT {$startat}, {$num}";
						break;
			case 'removed':
						$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$this->a->app_table} AS app WHERE status IN ('removed') {$add} ORDER BY {$order_by} LIMIT {$startat}, {$num}";
						break;
			case 'reserved':
						$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$this->a->app_table} AS app WHERE status IN ('reserved') {$add} ORDER BY {$order_by} LIMIT {$startat}, {$num}";
						break;
			default:
						$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$this->a->app_table} AS app WHERE status IN ('".$type."') {$add} ORDER BY {$order_by} LIMIT {$startat}, {$num}";
						break;
		}
		
		$sql = apply_filters( 'app_admin_apps_sql', $sql, $type, $add, $order_by, $startat, $num );
		
		return $this->a->db->get_results( $sql );

	}
	
	/**
	 * Get total number from previous query
	 * @return integer 
	 */		
	function get_apps_total( ) {
		return $this->a->db->get_var( "SELECT FOUND_ROWS();" );
	}
	
	/**
	 * Helper function for displaying bookings
	 */		
	function myapps(	$type = 'active', 
						$only_own = 0, 
						$allow_delete = 1, 
						$count, 
						$override, 
						$status, 
						$columns = '', 
						$columns_mobile = '', 
						$startat, 
						$rpp, 
						$apps,
						$sel_location = 0	) {

		// Load defaults and sanitize columns
		if ( '' == trim($columns) )
			$columns = apply_filters( 'app_bookings_default_columns','client,id,created,created_by,email,phone,address,zip,city,country,date_time,end_date_time,location,service,worker,status,price,deposit,total_paid,balance');
		
		$_columns	= wpb_is_mobile() && trim( $columns_mobile ) ? $columns_mobile : $columns;
		$cols		= explode( ',', wpb_sanitize_commas( $_columns ) );
		$cols		= array_map( "strtolower", $cols );
		array_unshift( $cols, 'delete' );
		
		// Find hidden columns
		$hidden_columns = array();
		if ( function_exists( 'get_current_screen' ) && function_exists( 'get_hidden_columns' ) ){
			$screen = get_current_screen();
			if ( is_object( $screen ) )
				$hidden_columns = get_hidden_columns( $screen );
		}
		$hidden_columns = apply_filters( 'app_bookings_hidden_columns', $hidden_columns );

		$args = compact( $cols );

		$colspan = 0;
		$j = '';
		$used_cols = array();
		
		$ret  = apply_filters( 'app_bookings_before_table', '<form class="app_form" method="post" >', $args );
		$ret .= '<table class="wp-list-table widefat app-manage dt-responsive display dataTable striped">';
		$ret .= '<thead>';
		
		/* hf : Common for header-footer */
		$hf = '<tr>'; 
		foreach( $cols as $col ) {
			if ( in_array( $col, $this->get_allowed_columns() ) ) {
				$used_cols[] = $col; // Used for js to hide not allowed columns. Hidden columns should also be here!

				$hidden = in_array( $col, $hidden_columns ) ? "hidden" : "";
				$col_primary = in_array( $col, array( 'client' ) ) ? " column-primary" : "";
				$col_check = in_array( $col, array( 'delete' ) ) ? " check-column" : "";
				if ( !$hidden )
					$colspan++; 
				
				$hf .= '<th id="'.$col.'" class="manage-column column-'.$col.' '. $hidden.$col_primary.$col_check. '">';
				
				switch ($col) {
					case 'delete':		$hf .= '<input type="checkbox" class="app_no_save_alert" />'; 
										break;
					case 'id':			$hf .= $this->a->get_text('app_id'); 
										break;
					case 'provider':
					case 'worker':		$hf .= $this->a->get_text('provider'); 
										break;
										// Addons may add more columns
					case $col:			if ( 'udf_' == substr( $col, 0, 4 ) )
											$hf .= apply_filters('app_bookings_udf_column_title','',$col); 
										else
											$hf .= apply_filters( 'app_bookings_column_title', str_replace( ':', '', $this->a->get_text($col)), $col); // E.g. status, address, phone
										break;
					default:			break;
				}
				$hf .= '</th>';
			}
		}
		$hf .= '</tr>';
		
		$ret .= $hf. '</thead>';
		// Remove id from foot
		$ret .= '<tfoot>' . preg_replace( '/<th id="(.*?)"/is','<th ', $hf ). '</tfoot>';
		
		$ret .= '<tbody>';
		$ret  = apply_filters( 'app_bookings_after_table', $ret, $args );
		echo $ret;
		
		$ret = '';
		
		$prices = $deposits = $total_paids = $balances =array();
		if ( $apps ) {
			
			remove_filter( 'gettext', array( 'WpBCustomTexts', 'global_text_replace' ) );
			$no_results = false;
			
			foreach ( $apps as $r ) {
				
				$parent		= $r->parent_id ? $this->a->get_app( $r->parent_id, true ) : $r;
				$client		= !empty( $r->user ) ? $r->user : ( !empty($r->email) ? $r->email : BASE('User')->get_client_name( $r->ID, $r, true ) );
				$cl			= $r->parent_id ? ' multi-app-child' : '';
				$rebook_js	= "window.location.href='". wpb_add_query_arg( array('add_new'=>1,'cpy_from'=>$r->ID) )."'";
				
				$ret		.= '<tr class="app-tr'.$cl.'">';
				
				foreach( $cols as $col ) {
					if ( !in_array( $col, $this->get_allowed_columns() ) )
						continue;
						
					$hidden			= in_array( $col, $hidden_columns ) ? " hidden" : "";
					$col_primary	= in_array( $col, array('client' ) ) ? " column-primary" : "";
					$col_check		= in_array( $col, array( 'delete' ) ) ? " check-column" : "";
					
					$ret .= '<td class="column-'.$col.$hidden.$col_primary.$col_check. '">';
					switch ( $col ) {
						case 'delete':	
							$ret .= '<input type="checkbox" class="app_no_save_alert" name="app[]" value="'. $r->ID .'" />';
							break;
							
						case 'id':		
							$ret .= '<span class="span_app_ID">'. apply_filters( 'app_ID_text', $r->ID, $r ) .'</span>';
							if ( 'reserved' != $r->status && !$r->parent_id )
								$ret .= '<div><input type="button" class="rebook-button button-secondary button-petit" onclick="'.$rebook_js.'" value="'.__('Rebook','wp-base').'" /></div>'; 
							break;
										
						case 'client':	
							$ret .= '<div class="user-inner">';
							$client_name = '<span class="app-client-name">'. BASE('User')->get_client_name( $r->ID, $r, true ). apply_filters( 'app_bookings_add_text_after_client', '', $r->ID, $r ) . '</span>';
							$link = wpb_add_query_arg(array('type'=>'all','app_or_fltr'=>1,'app_s'=>$r->ID,'stype'=>'app_id') );
							if ( !$r->parent_id ) {
								if ( $children = BASE('Multiple')->get_children( $r->ID, $apps ) ) {
									$title = esc_attr( sprintf( __( 'Parent of %s', 'wp-base' ), '#'. implode( ', #', wp_list_pluck( $children, 'ID' ) ) ) );
									$ret .= '<a href="'.$link.'"><span class="dashicons dashicons-migrate app_mr5" title="'.$title.'"></span></a>'.$client_name;
								}
								else
									$ret .= '<span class="dashicons app_mr5"></span>'.$client_name;
							}
							else
								$ret .= '<a href="'.$link.'"><span class="dashicons dashicons-migrate reverse app_mr5" title="'.sprintf( __( 'Child of #%d', 'wp-base' ), $r->parent_id ).'"></span></a><span class="app-inner-child">' . $client_name . '</span>'; 
							
							$ret .= '<span class="booking-info">'.apply_filters( 'app_ID_text', $r->ID, $r ) .'</span>';
							$ret .= '<span class="booking-info">'.apply_filters( 'app_bookings_service_name', wpb_cut( $this->a->get_service_name( $r->service ) ), $r ) .'</span>';
							$ret .= '<span class="booking-info">'.date_i18n( $this->a->dt_format, strtotime( $r->start ) ) .'</span>';
							$ret .= '</div>';
							$dashicon = 'reserved' == $r->status ? 'dashicons-editor-expand' : 'dashicons-edit';
							$ret .= '<div class="row-actions"><span class="dashicons '.$dashicon.'"></span><a href="javascript:void(0)" class="app-inline-edit" title="'.__('Click to edit booking','wp-base').'">';
							if ( 'reserved' == $r->status ) 
								$ret .= __('Details', 'wp-base'); 
							else 
								$ret .= __('Edit', 'wp-base');
							$ret .= '</a>';
							$ret .= '</div>';
							break;

						case 'created':	
							$ret .= date_i18n( $this->a->dt_format, strtotime( $r->created ) );
							break;
										
						case 'created_by':
							$ret .= $this->created_by( $r->ID );
							break;
							
						case 'location':
							$ret .= $this->a->get_location_name( $r->location );
							break;
							
						case 'location_address':
							$ret .= wpb_get_location_meta( $r->location, 'address' );
							break;
							
						case 'service':	
							$ret .= apply_filters( 'app_bookings_service_name', wpb_cut( $this->a->get_service_name( $r->service ) ), $r );
							break;
							
						case 'provider':
						case 'worker':	
							$ret .= wpb_cut( $this->a->get_worker_name( $r->worker ) );
							break;
							
						case 'price':	
							$price = !empty( $r->price ) ? (float)$r->price : 0;
							$ret .= wpb_format_currency( '', $price);
							$prices[$client] = isset($prices[$client]) ? $prices[$client] + $price : $price;
							break;
							
						case 'deposit':	
							$deposit = !empty( $r->deposit ) ? (float)$r->deposit : 0;
							$ret .= wpb_format_currency( '', $deposit);
							$deposits[$client] = isset($deposits[$client]) ? $deposits[$client] + $deposit : $deposit;
							break;
							
						case 'total_paid':	
							$paid = $this->a->get_total_paid_by_app( $r->ID );
							$ret .= wpb_format_currency( '', $paid/100 );
							$total_paids[$client] = isset($total_paids[$client]) ? $total_paids[$client] + $paid : $paid;
							break;
							
						case 'balance':	
							$paid		= $this->a->get_total_paid_by_app( $r->ID );
							$balance	= $paid/100 - (float)$r->price - (float)$r->deposit;
							$ret 		.= wpb_format_currency( '', $balance );
							$balances[$client] = isset($balances[$client]) ? $balances[$client] + $balance : $balance;
							break;
							
						case 'email':	
						case 'phone':	
						case 'city':	
						case 'address':	
						case 'zip':
						case 'country':
						case 'note':	
							$ret .= wpb_get_app_meta( $r->ID, $col );
							break;
										
						case 'date_time':				
							$ret .= '<span title="'. date_i18n("l", strtotime($r->start)). '">'.date_i18n( $this->a->dt_format, strtotime( $r->start ) ). '</span>';
							break;
							
						case 'date':				
							$ret .= '<abbr title="'. date_i18n("l", strtotime($r->start)). '">'. date_i18n( $this->a->date_format, strtotime( $r->start ) ). '</abbr>';
							break;
							
						case 'day':				
							$ret .= date_i18n( "l", strtotime( $r->start ) );
							break;
							
						case 'time':				
							$ret .= date_i18n( $this->a->time_format, strtotime( $r->start ) );
							break;
							
						case 'end_date_time':				
							$ret .= '<span title="'. date_i18n("l", strtotime($r->end)). '">'.date_i18n( $this->a->dt_format, strtotime( $r->end ) ). '</span>';
							break;
							
						case 'status':	
							$parent_status = $r->parent_id ? $parent->status : $r->status;
							$parent_id = $r->parent_id ? $r->parent_id : $r->ID;
							
							if ( 'paid' == $parent_status )
								$ret .= '<a href="'.admin_url('admin.php?page=app_transactions&amp;type=past&amp;stype=app_id&amp;app_s=').$parent_id.'" title="'.__('To view the transaction, click this link','wp-base').'">'. $this->a->get_status_name( 'paid' ) . '</a>';
							else if ( 'pending' == $parent_status ) {
								if ( empty($parent->price) || empty($parent->payment_method) || 'manual-payments' == $parent->payment_method )
									$ret .= $this->a->get_text('pending_approval');
								else
									$ret .= $this->a->get_text('pending_payment');
							}
							else
								$ret .= '<span class="app-status">'.$this->a->get_status_name( $r->status ).'</span>';
							
							if ( 'removed' === $r->status ) {
								$reason = '';
								if ( $stat = wpb_get_app_meta( $r->ID, 'abandoned' ) ) {
									
									switch( $stat ) {
										case 'cart':				$reason = __( 'Abandoned in cart','wp-base' ); break;
										case 'pending':				$reason = __( 'Abandoned after checkout','wp-base' ); break;
										case 'cancelled':			$reason = __( 'Client cancelled','wp-base' ); break;
										case 'worker_cancelled':	$reason = __( 'Provider cancelled','wp-base' ); break;
										case 'editor_cancelled':	$reason = __( 'Editor cancelled','wp-base' ); break;
										default:					$reason = __( 'Abandoned','wp-base' ); break;
									}
									
									$ret .= '<span class="app-abandon">['. $reason .']</span>';
								}
							}
							break;
									
						case $col:		
							$ret .= apply_filters( 'app_bookings_add_cell', '', $col, $r, $args );
							break;
					}
					$ret .= '</td>';
				}
				$ret .= '</tr>';
			}
			echo $ret;

		}
		else {
			?>
			<tr class="alternate app-tr">
				<td colspan="<?php echo $colspan; ?>" scope="row"><?php _e('No matching bookings have been found.','wp-base'); ?></td>
			</tr>
			<?php
		}
			?>

			</tbody>
		</table>
		<?php
		# Only for "Removed" tab
		if ( isset( $_GET["type"] ) && 'removed' == $_GET["type"] ) {
			global $post;
			if ( $allow_delete && wpb_admin_access_check( 'delete_bookings', false ) ) {
			?>
				<p>
				<input type="submit" id="delete_removed" class="button-secondary" value="<?php _e('Permanently Delete Selected Records', 'wp-base') ?>" title="<?php _e('Clicking this button permanently deletes selected records', 'wp-base') ?>" />
				<?php wp_nonce_field( 'delete_or_reset', 'app_delete_nonce' ); ?>
				<input type="hidden" value="<?php if ( isset( $post->ID ) ) echo $post->ID; else echo 0; ?>" name="page_id" />
				<input type="hidden" name="delete_removed" value="delete_removed" />
				<input type="hidden" name="action_app" value="delete_removed" />
				</p>		
				
			
			<?php }
		}
		do_action( 'app_admin_apps_form' );
		
		foreach( array('price','deposit','total_paid','balance') as $money_var ) {
			echo '<div id="'.$money_var.'-tt" style="display:none">';
				printf( __( '%s totals on this page:', 'wp-base' ), ucfirst( $this->a->get_text($money_var) ) );
				echo '<br/>';
				foreach( ${$money_var."s"} as $client=>${$money_var} ) {
					if ( is_numeric( $client ) && $client > 0 ) {
						$userdata = BASE('User')->get_app_userdata( 0, $client, true );
						$display = isset($userdata['name']) ? $userdata['name'] : ( isset($userdata['email']) ? $userdata['email'] : $client);
					}
					else if ( is_email( $client ) )
						$display = $client; // TODO: Find client name from email
					else
						$display = $client;
					
					echo $display .': '. wpb_format_currency( '', ${$money_var} ) .'<br/>';
				}
			echo '</div>';
		}
		?>
		</form>
		<?php
		// Hide column control
		$hide_cols = array_diff( $this->get_allowed_columns(), $used_cols );
		foreach( $hide_cols as $hide_col ) {
			$j .= "$('input[name=\"".$hide_col."-hide\"]').parent('label').hide();";
		}
		// Always hide control of these
		$j .= "$('input[name=\"delete-hide\"]').parent('label').hide();";

		?>
		<script type="text/javascript">

		/* Parameters of FEBM Shortcode */
		var sel_location =<?php echo $sel_location ?>;
		var only_own =<?php echo $only_own ?>;
		var override='<?php echo $override ?>';
	
		jQuery(document).ready(function($){
			// Hide columns in screen options
			<?php echo $j ?>
			$('body').css('minWidth','100px');
			
			<?php if ( $apps && !isset( $_GET['add_new'] ) ) { ?>
				var table = $(".app-manage").DataTable({ <?php echo $this->a->get_datatable_admin_args() ?> });
			<?php } ?>
			
		});
		</script>
		<?php
	}
	
	/**
	 * Bring user data when user selected in New Bookings
	 * @since 2.0
	 */	
	function populate_user() {
		if ( !check_ajax_referer( 'inline_edit', false, false ) )
			die( json_encode( array('error'=>esc_js( $this->a->get_text('unauthorised') ) ) ) );

		$text = __('Unknown or empty user ID','wp-base');
		
		if ( isset( $_POST['user_id'] ) && $_POST['user_id'] )
			$user_id = $_POST['user_id'];
		else
			wp_send_json( array( 'error'=>esc_js( $text ) ) );
		
		$r = BASE('User')->get_app_userdata( 0, $user_id );
		$r = apply_filters( 'app_inline_edit_populate_user', $r, $user_id );
		
		if ( isset( $r['error'] ) || !is_array( $r ) )
			wp_send_json( array( 'error'=>esc_js( $text ) ) );
		else
			wp_send_json( $r );
	}

	/**
	 * Modify time pulldown menu as as location, service, provider, start date changes 
	 * @since 2.0
	 */	
	function update_inline_edit() {
		
		if ( !check_ajax_referer( 'inline_edit', false, false ) )
			die( json_encode( array('error'=>esc_js( $this->a->get_text('unauthorised') ) ) ) );
		
		$start_time = isset( $_POST['start_time'] ) ? $_POST['start_time'] : '00:00';
		
		$app_id = $_REQUEST["app_id"];
		$app = $this->a->get_app( $app_id );
		if ( empty( $app->ID ) ) {
			$app = new stdClass();
			$app->start = date( 'Y-m-d H:i:s', strtotime( str_replace( ',','', $_POST['start_date'] ). " " . wpb_to_military( $start_time ) ) );
		}
		
		$updated = !empty( $_POST['updated'] ) ? $_POST['updated'] : '';
		$priority_set = wpb_setting( 'lsw_priority', WPB_DEFAULT_LSW_PRIORITY );
		$forced_loc = !empty( $_POST['sel_location'] ) ? $_POST['sel_location'] : 0;
		$force_priority = false;
		
		$selected_location = !empty( $_POST['location'] ) ? $_POST['location'] : 0;
		if ( $forced_loc ) {
			$selected_location = $forced_loc;
			$force_priority = str_replace( 'L', '', $priority_set ) === 'SW' ? 'LSW' : 'LWS';
		}		
		else if ( $selected_location && 'location' === $updated ) {
			$force_priority = str_replace( 'L', '', $priority_set ) === 'SW' ? 'LSW' : 'LWS';
		}
		
		$selected_service = !empty( $_POST['service'] ) ? $_POST['service'] : 0;
		if ( $selected_service && 'service' === $updated ) {
			$force_priority = str_replace( 'S', '', $priority_set ) === 'LW' ? 'SLW' : 'SWL';
		}
		
		$only_own = !empty( $_POST['only_own'] ) ? esc_sql( $_POST['only_own'] ) : false;
		if ( $only_own ) {
			$selected_worker = get_current_user_id();
			$force_priority = str_replace( 'W', '', $priority_set ) === 'LS' ? 'WLS' : 'WSL';
		}
		else if ( !empty( $_POST['worker'] ) ) {
			$selected_worker = $_POST['worker'];
			if ( 'worker' === $updated )
				$force_priority = str_replace( 'W', '', $priority_set ) === 'LS' ? 'WLS' : 'WSL';
		}
		else
			$selected_worker = 0;
	
		$menu = new WpBMenu( new WpBNorm( $selected_location, $selected_service, $selected_worker ), 'name', $force_priority );

		# Locations
		$nothing_to_select	= true;
		$locations_html 	= '<select data-lsw="location" name="location">';
		$locations = $this->a->get_locations( 'name' );
		
		if ( $locations ) {
			foreach ( $locations as $location ) {
				
				if ( $this->a->get_lid() == $location->ID ) {
					$sel = ' selected="selected"';
					$selected_location = $location->ID;
				}
				else
					$sel = '';
				
				$locations_html .= '<option value="'.$location->ID.'"'.$sel.'>'. $this->a->get_location_name( $location->ID ) . '</option>';
				$nothing_to_select = false;
			}
		}
		
		if ( $nothing_to_select )
			$locations_html .= '<option disabled="disabled">' . $this->a->get_text('no_free_time_slots'). '</option>';			
		$locations_html .= '</select>';

		# Services
		$nothing_to_select	= true;
		$services_html 		= '<select data-lsw="service" name="service">';
		$services 			= $this->a->get_services( 'name' );
		
		if ( $services ) {
			foreach ( $services as $service ) {
				
				if ( $this->a->get_sid() == $service->ID ) {
					$sel = ' selected="selected"';
					$selected_service = $service->ID;
				}
				else
					$sel = '';
				
				$disabled = $this->a->is_package( $service->ID ) ? ' disabled="disabled"' : '';
				$services_html .= '<option value="'.$service->ID.'"'.$sel.$disabled.'>'. $this->a->get_service_name( $service->ID ) . '</option>';
				$nothing_to_select = false;
			}
		}
		
		if ( $nothing_to_select )
			$services_html .= '<option disabled="disabled">' . $this->a->get_text('no_free_time_slots'). '</option>';			
		$services_html .= '</select>';

		# Service providers
		$nothing_to_select	= true;
		$workers_html		= '<select data-lsw="worker" name="worker">';
		$workers 			= $this->a->get_workers( 'name' );
		
		if ( $workers ) {
			foreach ( $workers as $worker ) {

				if ( $this->a->get_wid() == $worker->ID ) {
					$sel = ' selected="selected"';
					$selected_worker = $worker->ID;	// ...until we find one
					$nothing_to_select = false;
				}
				else
					$sel = '';
				
				$workers_html .= '<option value="'.$worker->ID.'"'.$sel.'>'. $this->a->get_worker_name( $worker->ID, false ) . '</option>';
			}
		}
		
		if ( $nothing_to_select && $this->a->get_nof_workers() )
			$workers_html .= '<option value="0" selected="selected">'. __('Unassigned provider', 'wp-base') . '</option>';
		$workers_html .= '</select>';

		$d_style = $this->a->is_daily( $selected_service ) ? ' style="" disabled="disabled"' : '';
		
		$ss = $this->a->get_service( $selected_service ); // Selected service object

		$calendar = new WpBCalendar( $selected_location, $selected_service, $selected_worker );
		
		$min_secs = 60 * $this->a->get_min_time();

		$start_time_html = '';
		$start_time_html .= '<select name="start_time" '.$d_style.'>';
		$selected_t = 0;
		$selected_found = $selected_found_late = false;
		$time_options = array();
		$start_time_db = date( $this->a->time_format, strtotime( $app->start ) ); // Start time in database in 3:00 pm format
		for ( $t=0; $t<3600*24; $t=$t+$min_secs ) {
			$dhours = wpb_secs2hours( $t ); // Hours in 08:30 pm format
			$s = $d = '';
			if ( $this->is_strict_check() ) {
				if ( $app_id && $app->service == $selected_service && $app->worker == $selected_worker 
					&& strtotime( $_POST['start_date'], $this->a->_time )  == strtotime( date('Y-m-d', strtotime( $app->start ) ) ) 
					&& $dhours == $start_time_db )
					$lsw_date_changed = false;
				else
					$lsw_date_changed = true;

				$start_timestamp = strtotime( $_POST['start_date'] . " " . $dhours );
				
				$slot = new WpBSlot( $calendar, $start_timestamp, $start_timestamp + $min_secs );
				$slot->set_app_id( $app_id );
				
				if ( $lsw_date_changed && ( $slot->is_busy( ) || !$slot->is_working( ) ) )
					$d = ' disabled="disabled"';
				else if ( !$selected_t )
					$selected_t = $t;	// Pick first enabled time for end time in case nothing is selected
			}

			if ( $dhours == $start_time && !$d ) {
				if ( $selected_found )
					$selected_found_late = true; // We have to fix: There are double selections
				$selected_found = true;
				$selected_t = $t;	// We found a real selected time. Override previous.
				$s = " selected='selected'";
			}
			else if ( $app_id && $dhours == $start_time_db && !$d && !$selected_found ) {
				$selected_found = true;
				$selected_t = $t;	// We found a real selected time (The one in the db). Override previous.
				$s = " selected='selected'";
			}
			
			$time_options[$dhours] = $s.$d;
		}
		
		foreach ( $time_options as $dhours=>$sd ) {
			if ( $selected_found_late && $dhours != $start_time )
				$sd = ''; // Remove the first selected, which is not correct in this selection
			$start_time_html .= '<option value="'.$dhours. '"'.$sd.'>';
			$start_time_html .= $dhours;
			$start_time_html .= '</option>';
		}
		
		$start_time_html .= '</select>';

		// End time
		$end_time_html = '';
		$end_time_html .= '<select name="end_time" '.$d_style.'>';

		for ( $t=0; $t<3600*24; $t=$t+$min_secs ) {
			$dhours = wpb_secs2hours( $t ); // Hours in am 08:30 format
			if ( $t == ( $selected_t + $ss->duration * 60 ) ) {
				$s = " selected='selected'";
				$d = '';
			}
			else {
				$s = '';
				$d = ' disabled="disabled"';
			}
			
			$end_time_html .= '<option value="'.$dhours.'"'.$s.$d.'>';
			$end_time_html .= $dhours;
			$end_time_html .= '</option>';
		}
		$end_time_html .= '</select>';
		
		if ( $this->is_strict_check() )
			$out = array( 'start_time_sel'=>$start_time_html, 'end_time_sel'=>$end_time_html, 'locations_sel'=>$locations_html, 'services_sel'=>$services_html, 'workers_sel'=>$workers_html, 'blocked_days'=>json_encode($this->blocked_days( $calendar, $_POST['start_date'] )), );
		else
			$out = array( 'locations_sel'=>$locations_html, 'services_sel'=>$services_html, 'workers_sel'=>$workers_html );
		
		// Update price and deposit only if unlocked
		if ( isset( $_POST['locked_check'] ) && empty( $_POST['locked'] ) ) {
			$day_ts = strtotime( $_POST['start_date'], $this->a->_time );
			list( $hours, $mins ) = explode( ':', date( 'H:i', strtotime( $app->start ) ) );
			$seconds = $selected_t ? $selected_t : $hours *60*60 + $mins *60;
			$slot_start = $day_ts + $seconds;
			$slot_end = $slot_start + wpb_get_duration( $selected_service, $slot_start )*60;
			
			$slot = new WpBSlot( $calendar, $slot_start, $slot_end );
			$slot->set_app_id( $app_id );
			
			$pax = apply_filters( 'app_pax', !empty($app->seats) ? $app->seats : 1, $selected_service, 0 );
			$price = apply_filters( 'app_inline_edit_update_price', $pax * $slot->get_price( ), $slot );
			$deposit = apply_filters( 'app_inline_edit_update_deposit', $pax * $slot->get_deposit(), $slot );
			
			$out = array_merge( $out, array( 'price'=>$price, 'deposit'=>$deposit ) );
		}
		
		die( json_encode( $out ) );
	}
	
	/**
	 * Find who made the booking in the first place
	 * @since 3.0
	 * @return string
	 */	
	function created_by( $app_id ) {
		$meta = wpb_get_app_meta( $app_id, 'booking_on_behalf' );
		if ( $meta ) {
			$who = get_user_by( 'id', $meta );
			if ( $this->a->is_worker( $meta ) )
				$name = $this->a->get_worker_name( $meta );
			else
				$name = $who->display_name;
			
			$tt = $name;
			if ( $email = get_user_meta( $meta, 'app_email', true ) )
				$tt = $tt .' ● '. $email;
			if ( $phone = get_user_meta( $meta, 'app_phone', true ) )
				$tt = $tt .' ● '. $phone;
			
			if ( current_user_can( 'edit_users' ) )
				$created_by = '<a href="'. admin_url("user-edit.php?user_id="). $meta . '" target="_blank" title="'.$tt.'">'. $name . '</a>';
			else
				$created_by = $name;
		}
		else
			$created_by = __( 'Client', 'wp-base') ;
		
		return $created_by;
	}
	
	/**
	 * Dynamically show payment in qtip content
	 * @since 2.0
	 */	
	function show_payment_in_tooltip() {
		if ( empty( $_POST['app_id'] ) )
			wp_send_json( array( 'result' => __('Unexpected error','wp-base') ) );
		
		$transactions = $this->a->get_admin_transactions('past', 0, 100, $_POST['app_id']); // Force app_id
		ob_start();		
		$this->a->mytransactions( $transactions, true ); // Get short table
		$result = preg_replace( '%<tfoot>(.*?)</tfoot>%is', '', ob_get_contents() ); // Remove footer
		ob_end_clean();
		wp_send_json( array( 'result' => $result ) );
	}
	
	/**
	 * Helper to find blocked days
	 * @since 3.0
	 * @return array
	 */	
	function blocked_days( $calendar, $start_date ) {
		if ( !$this->is_strict_check() )
			return array();
		
		$start_date_ts	= strtotime( $start_date );
		$first_day_ts	= wpb_first_of_month($start_date_ts);
		$last_day_ts	= wpb_last_of_month($start_date_ts, 1);
		$allowed_days	= $calendar->find_available_days( $first_day_ts, $last_day_ts );
		$all_days 		= array();
		
		for ( $d = $first_day_ts; $d < $last_day_ts; $d = $d +86400 ) {
			$all_days[] = date( 'Y-m-d', $d );
		}
		
		return array_values( array_diff( $all_days, $allowed_days ) );		
	}

	/**
	 * Edit or create appointments on admin side
	 */	
	function inline_edit( $echo=false, $colspan=0 ) {
		
		if ( !$echo ) {
			if ( !check_ajax_referer( 'inline_edit',false,false ) )
				die( json_encode( array('error'=>esc_js( $this->a->get_text('unauthorised') ) ) ) );
		}
		
		$sel_location = esc_sql( $_POST['sel_location'] ); 	// Location ID from FEBM
		$only_own = esc_sql( $_POST['only_own'] );			// User is allowed to edit/add only own appointments
		
		$safe_date_format = $this->a->safe_date_format();
		
		$min_secs = 60 * $this->a->get_min_time();
		
		$app_id = isset( $_REQUEST["app_id"] ) ? $_REQUEST["app_id"] : 0;
		if ( $app_id ) {
			$app = $this->a->get_app( $app_id );
			$start_date = date_i18n( $safe_date_format, strtotime( $app->start ) );
			$end_date = date_i18n( $safe_date_format, strtotime( $app->end ) );
				
			$start_time = date_i18n( $this->a->time_format, strtotime( $app->start ) );
			$end_time = date_i18n( $this->a->time_format, strtotime( $app->end ) );
			$end_datetime = date_i18n( $this->a->dt_format, strtotime( $app->end ) );
			
			$app->deposit = isset( $app->deposit ) ? $app->deposit : 0;
			
			$calendar = new WpBCalendar( $app->location, $app->service, $app->worker );
		}
		else {
			/* Rebook */
			if ( isset( $_REQUEST['add_new'] ) && isset( $_REQUEST['cpy_from'] ) && $app = $this->a->get_app( $_REQUEST['cpy_from'] ) ) {
				$app_id = $app->ID = 0;
				$app->location = $sel_location ? $sel_location : $app->location;
				$app->status = 'confirmed';
				$app->parent_id = 0;
				$app->payment_method = '';
				foreach ( $this->a->get_user_fields() as $f ) {
					${$f} = wpb_get_app_meta( $_REQUEST['cpy_from'], $f );
				}			
			}	
			else {
				/* Add New */
				$app = new stdClass();
				$app_id = $app->ID = 0;
				// Set other fields to default so that we don't get notice messages
				$app->user = $app->parent_id = $app->deposit = 0;
				$app->location = $sel_location ? $sel_location : 0;
				$app->created = $app->status = $app->payment_method = '';
				foreach ( $this->a->get_user_fields() as $f ) {
					${$f} = '';
				}
				
				# Starting point of finding $app->service
				$force_priority = false;
				$maybe_service_id = 0;
				if ( !empty( $_GET['app_service'] ) && $this->a->service_exists( $_GET['app_service'] ) ) {
					$maybe_service_id = $_GET['app_service'];
					$force_priority = 'SWL';
				}
				
				$maybe_worker_id = 0;
				if ( $only_own )
					$maybe_worker_id = get_current_user_id();
				else if ( !empty( $_REQUEST['app_worker'] ) && $this->a->is_worker( $_REQUEST['app_worker'] ) )
					$maybe_worker_id = $_REQUEST['app_worker'];
				
				if ( $maybe_worker_id )
					$force_priority = 'WSL';

				$menu = new WpBMenu( new WpBNorm( 0, $maybe_service_id, $maybe_worker_id ), 'sort_order', $force_priority );
				
				$app->location = $this->a->get_lid();
				$app->service = $this->a->get_sid();
				$app->worker = $this->a->get_wid();

			}
			
			$duration = wpb_get_duration( $app->service ) * 60;	// in secs

			$calendar = new WpBCalendar( $app->location, $app->service, $app->worker );
			
			// If selected thru Calendar page add a booking
			if ( isset( $_REQUEST['app_timestamp'] ) ) {
				$req_timestamp	= $_REQUEST['app_timestamp'];
				$start_date		= date( $safe_date_format, $req_timestamp );
				$end_date		= date( $safe_date_format, $req_timestamp + $duration );
				$start_time		= date( $this->a->time_format, $req_timestamp );
				$end_time		= date( $this->a->time_format, $req_timestamp + $duration );
				$first			= $req_timestamp;
				$end_ts			= $req_timestamp + $duration;
			}
			else {
				// Find first available day/time
				$workers = $this->a->get_workers( "name" ); // The order in pulldown
				$test_worker = $app->worker ? $app->worker : ( is_array( $workers ) ? key( $workers ) : 0 );
				$first = $this->a->find_first_free_slot( $app->location, $app->service, $test_worker );
				$first = $first ? $first : ( $this->a->is_daily($app->service) ? strtotime('tomorrow'):( intval( $this->a->_time/$min_secs ) + 1 )*$min_secs );
				
				if ( $this->a->is_daily( $app->service ) ) {
					$start_time = $end_time = '00:00';
					$start_date = date_i18n( $this->a->date_format, $first );
					$end_date = date_i18n( $this->a->date_format, $first + $duration );
				}
				else {
					$start_time = date_i18n( $this->a->time_format, $first );
					$end_time = date_i18n( $this->a->time_format, $first + $min_secs );
					$start_date = date_i18n( $safe_date_format, $first );
					$end_date = date_i18n( $safe_date_format, $first + $min_secs );
				}
				
				$end_ts = $first + $min_secs;
			}
			
			$app->price = $calendar->slot( $first, $end_ts )->get_price( );
		}

		$class_add = $app_id ? "" : "inline-edit-row-add-new";
		
		$app = apply_filters( 'app_inline_edit_app', $app );

		$is_daily = $this->a->is_daily( $app->service );
		
		// If this is a new appt, generate an id to be used by javascript
		$js_id = $app_id ? $app_id : uniqid();

		$html = '';
		$html .= '<tr class="inline-edit-row inline-edit-row-post quick-edit-row-post '.$class_add.'">';
		if ( isset( $_POST["col_len"] ) )		
			$html .= '<td colspan="'.$_POST["col_len"].'" class="colspanchange">';
		else if ( $colspan )		
			$html .= '<td colspan="'.$colspan.'" class="colspanchange">';
		else
			$html .= '<td colspan="7" class="colspanchange">';
		
	/* LEFT COLUMN */
		$html .= '<fieldset class="inline-edit-col-left">';
		$html .= '<div class="inline-edit-col">';
		$html .= '<h4 class="app_iedit_client_h">'.__('CLIENT', 'wp-base').'</h4>';
		
		/* user */
		$html .= '<label class="app_iedit_user">';
		$html .= '<span class="title">'.__('User', 'wp-base'). '</span>';
		$html .= '<span class="input-text-wrap">';
		$html .= BASE('User')->app_dropdown_users( apply_filters( 'app_inline_edit_users_args', 
							array( 
								'show_option_all'	=> __('Not registered user','wp-base'), 
								'echo'				=> 0, 
								'selected'			=> $app->user, 
								'name'				=> 'user',
								'class'				=> 'app_users', 
								'id'				=> 'app_users_'.$js_id 
							),
							$app )
						);
		$html .= '</span>';
		$html .= '</label>';
		
		/* Client fields */
		foreach ( $this->a->get_user_fields() as $f ) {
			$field_name = 'name' === $f ? 'cname' : $f;
			$value = isset( $_REQUEST['add_new'] ) && isset(${$f}) ? ${$f} : wpb_get_app_meta( $app->ID, $f );
			if ( wpb_is_demo() ) {
				if ( 'email' === $f )
					$value = 'email@example.com';
				else if ( 'phone' === $f )
					$value = '0123456789';
				else
					$value = 'Demo '. $f;
			}
			$html .= '<label class="app_iedit_'.$f.'">';
			$html .= '<span class="title">'.wpb_get_field_name($f). '</span>';
			$html .= '<span class="input-text-wrap">';
			$html .= '<input type="text" name="'.$field_name.'" class="ptitle" value="'.$value.'" />';
			$html .= '</span>';
			$html .= '</label>';
		}
		
		/* UDF fields */
		$html  = apply_filters( 'app_inline_edit_user_fields', $html, $app_id );
		
		$html .= '</div>';
		$html .= '</fieldset>';
		
	/* CENTER COLUMN */
		$html .= '<fieldset class="inline-edit-col-center">';
		$html .= '<div class="inline-edit-col">';

		$html .= '<h4 class="app_iedit_lsw_h">'.__('LOCATION - SERVICE - PROVIDER', 'wp-base').'</h4>';

		/* Locations */
		$style = $sel_location ? ' style="display:hidden" ' : '';
		$html .= '<label class="app_iedit_location" '.$style.'>';
		$html .= '<span class="title">'.__('Location', 'wp-base'). '</span>';
		$html .= '<span class="input-text-wrap" >';
		$html .= '<select name="location">';
		if ( $sel_location )
			$html .= '<option value="'.$sel_location.'" selected="selected">'. $sel_location . '</option>';
		else {
			$locations = $this->a->get_locations( "name" );
			// Always add an "No location" field
			$html .= '<option value="0">'. __('None', 'wp-base') . '</option>';
			if ( $locations ) {
				foreach ( $locations as $location ) {
					if ( $app->location == $location->ID )
						$sel = ' selected="selected"';
					else
						$sel = '';
					$html .= '<option value="'.$location->ID.'"'.$sel.'>'. stripslashes( $location->name ) . '</option>';
				}
			}
		}
		$html .= '</select>';
		$html .= '</span>';
		$html .= '</label>';

		/* Services */
		$services = $sel_location ? $this->a->get_services_by_location( $sel_location, "name" ) : $this->a->get_services( "name" );
		$services_by_worker = $only_own ? $this->a->get_services_by_worker( get_current_user_id() ) : $this->a->get_services( "name" );
		$sbw_ids = is_array( $services_by_worker ) ? array_keys( $services_by_worker ) : array();
		$nothing_to_select = true;
		
		$html .= '<label class="app_iedit_service">';
		$html .= '<span class="title">'.__('Service', 'wp-base'). '</span>';
		$html .= '<span class="input-text-wrap" >';
		$html .= '<select name="service">';
		if ( $services ) {
			foreach ( $services as $service ) {
				if ( $only_own && !in_array( $service->ID, $sbw_ids ) )
					continue;
				
				$disabled = $this->a->is_package( $service->ID ) ? ' disabled="disabled"' : '';
				$sel = $app->service == $service->ID && !$disabled ? ' selected="selected"' : '';
				$html .= '<option value="'.$service->ID.'"'.$sel.$disabled.'>'. stripslashes( $service->name ) . '</option>';
				$nothing_to_select = false;
			}
		}
		if ( $nothing_to_select )
			$html .= '<option disabled="disabled">' . $this->a->get_text('no_free_time_slots'). '</option>';
		$html .= '</select>';
		$html .= '</span>';
		$html .= '</label>';
		
		/* Recurring */
		$html  = apply_filters( 'app_inline_edit_after_service', $html, $app_id );
		
		/* Workers */
		$nothing_to_select = true;
		$html .= '<label class="app_iedit_worker">';
		$html .= '<span class="title">'.__('Provider', 'wp-base'). '</span>';
		$html .= '<span class="input-text-wrap" >';
		$html .= '<select name="worker">';
		
		if ( !$this->is_strict_check() ) {
			// Always add an "Our staff" field
			$html .= '<option value="0">'. __('Unassigned provider', 'wp-base') . '</option>';
			$workers = $this->a->get_workers( "name" );
			$nothing_to_select = false;
		}
		else
			$workers = $this->a->get_workers_by_service( $app->service );
		
		if ( $workers ) {
			foreach ( $workers as $worker ) {
				if ( $only_own && $worker->ID != get_current_user_id() )
					continue;
				
				if ( $app->worker == $worker->ID ) {
					$sel = ' selected="selected"';
				}
				else
					$sel = '';
				$html .= '<option value="'.$worker->ID.'"'.$sel.'>'. $this->a->get_worker_name( $worker->ID, false ) . '</option>';
				$nothing_to_select = false;
			}
		}
		if ( $nothing_to_select )
			$html .= '<option disabled="disabled">' . $this->a->get_text('no_free_time_slots'). '</option>';
		$html .= '</select>';
		$html .= '</span>';
		$html .= '</label>';

		$html .= '<h4 class="app_iedit_price_h">'.sprintf( __('PRICING - PAYMENT (%s)', 'wp-base'), wpb_format_currency( wpb_setting('currency') ) ).'</h4>';
		/* Selected payment method - Don't show for a new app */
		if ( $app_id ) {
			$html .= '<label class="app_iedit_payment_method">';
			$html .= '<span class="title"><abbr title="'.__('This is the payment method selected at checkout.|It does not indicate that payment has realised','wp-base').'">'.__('Method', 'wp-base'). '</abbr></span>';
			$html .= '<span class="app-input-text-wrap">';
			$payment_method = __('None', 'wp-base');
			if ( $method_code = $app->payment_method ) {
				if ( 'marketpress' == $method_code )
					$payment_method = 'MarketPress';
				else if ( 'woocommerce' == $method_code )
					$payment_method = 'WooCommerce';
				else {
					global $app_gateway_plugins;
					foreach ( (array)$app_gateway_plugins as $code => $plugin ) {
						if ( $method_code == $code ) {
							$payment_method = $plugin[1];
							break;
						}
					}
				}
			}
			$html .= $payment_method;
			$html .= '</span>';
			$html .= '</label>';
		}

		/* Lock */
		$checked = $app_id ? checked( null, wpb_get_app_meta( $app_id, 'unlocked' ), false ) : '';
		$html .= '<label class="app_iedit_lock">';
		$html .= '<span class="title"><abbr title="'.__('If locked, editing any other field will not change price and deposit','wp-base').'">'.__('Locked', 'wp-base'). '</abbr></span>';
		$html .= '<span class="input-text-wrap">';
		$html .= '<input type="checkbox" name="locked" '.$checked.' />';
		$html .= '<input type="hidden" name="locked_check" value="1" />';
		$html .= '</span>';
		$html .= '</label>';

		/* Price */
		$price_readonly = wpb_get_app_meta( $app_id, 'unlocked' ) ? '' : ' readonly="readonly"';
		$html .= '<label class="app_iedit_price">';
		$html .= '<span class="title">'.__('Price', 'wp-base'). '</span>';
		$html .= '<span class="input-text-wrap">';
		$html .= '<input type="text" name="price" style="width:50%" class="ptitle" value="'.wpb_format_currency( false, $app->price, true ).'" '.$price_readonly.'/>';
		$html .= '</span>';
		$html .= '</label>';

		/* Refundable deposit */
		$html .= '<label class="app_iedit_deposit">';
		$html .= '<span class="title">'.__('Deposit', 'wp-base'). '</span>';
		$html .= '<span class="input-text-wrap">';
		$html .= '<input type="text" name="deposit" style="width:50%" class="ptitle" value="'.wpb_format_currency( false, $app->deposit, true).'" '.$price_readonly.'/>';
		$html .= '</span>';
		$html .= '</label>';
		
		$html .= '<div class="app_hr" ></div>';
		
		/* Total due */
		$html .= '<label class="app_iedit_due">';
		$html .= '<span class="title">'.__('Total due', 'wp-base'). '</span>';
		$html .= '<span class="input-text-wrap">';
		$html .= '<input type="text" name="total_due" style="width:50%" class="ptitle" value="'.wpb_format_currency( false, ($app->price + $app->deposit), true ).'" readonly="readonly" />';
		$html .= '</span>';
		$html .= '</label>';

		$html .= '<div class="app_hr" ></div>';

		/* Payment */
		if ( $app_id )
			$p = $this->a->db->get_var( "SELECT SUM(transaction_total_amount) FROM {$this->a->transaction_table} WHERE transaction_app_ID = ".$app_id );
		$p = ( isset( $p ) && $p ) ? $p/100 : 0;
		
		$payment_text = $p ? '<abbr class="app-payment-ttip">'. __('Payment', 'wp-base') .'</abbr>' : __('Payment', 'wp-base');
		if ( $p ) {
			$js_tooltip = "
			$('.app-payment-ttip').qtip({
				overwrite: true,
				content: {
					text: function(event, api) {
						api.elements.content.html(_app_.please_wait);
						return $.ajax({
							url: ajaxurl, 
							type: 'POST',
							dataType: 'json', 
							data: {
								app_id: $(this).parents('.inline-edit-row').find('input[name=\"app_id\"]').val(),
								action: 'app_show_payment_in_tooltip'
							}
						})
						.then(function(res) {
							var content = res.result;
							return content;
						}, function(xhr, status, error) {
							api.set('content.text', status + ': ' + error);
						});
					}
				},hide:qtip_hide,position:qtip_pos,style:qtip_small_style
			});
			";
		}
		else $js_tooltip = '';
		$js_tooltip .='
		$(".inline-edit-row").find("[title][title!=\"\"]").each(function() {
			$(this).qtip({
					content: {
						text: $(this).attr("title").replace("|","<br/>")
					},
					hide:qtip_hide,position:qtip_pos,style:qtip_style
			});
		});
		';

		$html .= '<label class="app_iedit_payment">';
		$html .= '<span class="title">'. $payment_text. '</span>';
		$html .= '<span class="input-text-wrap">';
		$html .= '<input type="text" name="payment" style="width:50%" value="'.wpb_format_currency( false, $p, true).'" readonly="readonly" />';
		$html .= '</span>';
		$html .= '</label>';
		
		$html .= '<div class="app_hr app_thick" ></div>';

		/* Balance */
		$html .= '<label class="app_iedit_balance">';
		$html .= '<span class="title"><b>'.__('Balance', 'wp-base'). '</b></span>';
		$html .= '<span class="input-text-wrap">';
		$html .= '<input type="text" name="balance" style="width:50%" value="'.wpb_format_currency( false, $p - $app->price - $app->deposit, true).'" readonly="readonly" />';
		$html .= '</span>';
		$html .= '</label>';
		
		$html .= '</div>';
		$html .= '</fieldset>';
		
	/* RIGHT COLUMN */
		$html .= '<fieldset class="inline-edit-col-right">';
		$html .= '<div class="inline-edit-col">';
		
		if ( $app_id )
			$html .= '<h4 class="app_iedit_app_h">'.__('APPOINTMENT', 'wp-base') . ' (ID: ' .$app_id . ')</h4>' ;
		else
			$html .= '<h4 class="app_iedit_app_h app_blink">'.__('NEW APPOINTMENT', 'wp-base') . '</h4>' ;
		
		$html  = apply_filters( 'app_inline_edit_appointment_fields', $html, $app_id, $js_id );
			
		/* Created - Don't show for a new app */
		if ( $app_id ) {
			$html .= '<label class="app_iedit_created">';
			$html .= '<span class="title">'.__('Created at', 'wp-base'). '</span>';
			$html .= '<span class="app-input-text-wrap" >';
			$html .= date_i18n( $this->a->dt_format, strtotime($app->created) );
			$html .= '</span>';
			$html .= '</label>';
			
			$html .= '<label class="app_iedit_created_by">';
			$html .= '<span class="title">'.__('Created by', 'wp-base'). '</span>';
			$html .= '<span class="app-input-text-wrap" >';
			$html .= $this->created_by( $app_id );
			$html .= '</span>';
			$html .= '</label>';
		}
		
		$d_style = $is_daily ? ' style="" disabled="disabled"' : '';

		/* Start */
		$html .= '<fieldset>';
		$html .= '<label for="start_date_'.$js_id.'" class="app_iedit_date_time title">';
		$html .= __('Start', 'wp-base');
		$html .= '</label>';
		$html .= '<div class="app_iedit_date_time_holder" >';
		$html .= '<div class="app_iedit_start_date" >';
		$html .= '<input type="text" id="start_date_'.$js_id.'" name="start_date" class="datepicker" size="12" value="'.$start_date.'" />';
		$html .= '<input type="hidden" class="blocked-days" value="'.esc_attr(json_encode( $this->blocked_days( $calendar, $start_date ) )).'" />';
		$html .= '</div>';
		$html .= '<div class="app_iedit_start_time" >';
		
		$html .= '<select name="start_time" '.$d_style.'>';
		$selected_found = false;
		$html2 = '';
		
		// $calendar = new WpBCalendar( $app->location, $app->service, $app->worker );
		
		for ( $t=0; $t<3600*24; $t=$t+$min_secs ) {
			$dhours = wpb_secs2hours( $t ); // Hours in 08:30 format
			if ( $dhours == $start_time ) {
				$s = " selected='selected'";
				$selected_found = true;
				$d = ''; // Disabled or not
			}
			else {
				$s = '';
				if ( $this->is_strict_check() ) {
					$ss = $this->a->get_service( $app->service ); // Selected service object
					$duration = isset( $ss->duration ) && $ss->duration ? $ss->duration : 0; 
					$start_timestamp = strtotime( $start_date . " " . $dhours );
					$end_timestamp = $start_timestamp + $duration * 60;
					if ( $calendar->slot( $start_timestamp, $end_timestamp )->why_not_free( ) )
						$d = ' disabled="disabled"';
					else
						$d = '';
				}
				else {
					$d = '';
				}
			}
			
			$html2 .= '<option'.$s.$d.'>';
			$html2 .= $dhours;
			$html2 .= '</option>';
		}
		// If the set time is not in the list, lets add it to the top of options
		if ( $app_id && !$selected_found ) {
			$html .= "<option selected='selected'>";
			$html .= $start_time;
			$html .= '</option>';
		}
		$html .= $html2;	// Write the rest of the options
		
		$html .= '</select>';
		$html .= '</div>';
		// $html .= '<div style="clear:both;"></div>';
		$html .= '</div>';
		$html .= '</fieldset>';
		
		/* End */
		$readonly = $this->is_strict_check() ? ' readonly="readonly"' : '';
		$html .= '<fieldset>';
		$html .= '<label for="end_date_'.$js_id.'" class="app_iedit_date_time title">';
		$html .= __('End', 'wp-base');
		$html .= '</label>';
		$html .= '<div class="app_iedit_date_time_holder" >';
		$html .= '<div class="app_iedit_end_date" >';
		$html .= '<input type="text" id="end_date_'.$js_id.'" name="end_date" class="datepicker" size="12" value="'.$end_date.'" '.$d_style.$readonly.'/>';
		$html .= '</div>';
		$html .= '<div class="app_iedit_end_time" >';
		
		$html .= '<select name="end_time" '.$d_style.'>';
		$selected_found = false;
		$html2 = '';
		for ( $t=0; $t<3600*24; $t=$t+$min_secs ) {
			$d = '';
			$dhours = wpb_secs2hours( $t ); // Hours in 08:30 format
			if ( $dhours == $end_time ) {
				$s = " selected='selected'";
				$selected_found = true;
			}
			else {
				$s = '';
				if ( $this->is_strict_check() )
					$d = ' disabled="disabled"';
			}
			
			$html2 .= '<option'.$s.$d.'>';
			$html2 .= $dhours;
			$html2 .= '</option>';
		}
		// If the set time is not in the list, lets add it to the top of options
		if ( $app_id && !$selected_found ) {
			$html .= "<option selected='selected'>";
			$html .= $end_time;
			$html .= '</option>';
		}
		$html .= $html2;

		$html .= '</select>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</fieldset>';

		/* Parent */
		if ( BASE('Multiple')->is_active() ) {
			if ( $app->parent_id ) {
				$parent = $this->a->get_app( $app->parent_id );
				$parent_date = $parent ? sprintf( __( 'starting at <b>%s</b>', 'wp-base' ), mysql2date( $this->a->dt_format, $parent->start ) ) : '';
				$parent_id = $parent ? $parent->ID : '';
			}
			else 
				$parent_id = $parent_date = '';

			$html .= '<label class="app_iedit_parent"><span class="title">';
			$html .= __( 'Parent', 'wp-base' );
			$html .= '</span>';
			$html .= '<input type="text" name="parent_id" style="width:15%"  value="'.$parent_id.'"/>&nbsp;';
			$html .= $parent_date;
			$html .= '</span></label>';
		}
		
		/* Client Note */
		$html .= '<label class="app_iedit_note">';
		$html .= '<span class="title"><abbr title="'.__('This is the note submitted by the client','wp-base').'">'.wpb_get_field_name('note'). '</abbr></span>';
		$html .= '<textarea name="note">';
		$html .= stripslashes( wpb_get_app_meta( $app->ID, 'note' ) );
		$html .= '</textarea>';
		$html .= '</label>';
		
		/* Admin Note */
		$html .= '<label class="app_iedit_admin_note">';
		$html .= '<span class="title"><abbr title="'.__('This is only visible to admins, e.g. in order to write internal notes','wp-base').'">'.__('Admin Note', 'wp-base'). '</abbr></span>';
		$html .= '<textarea name="admin_note">';
		$html .= stripslashes( wpb_get_app_meta( $app->ID, 'admin_note' ) );
		$html .= '</textarea>';
		$html .= '</label>';
		
		/* Status */
		$statuses = $this->a->get_statuses();
		$html .= '<label class="app_iedit_status">';
		$html .= '<span class="title">'.__('Status', 'wp-base'). '</span>';
		$html .= '<span class="input-text-wrap">';
		$html .= '<select name="status">';
		if ( $statuses ) {
			foreach ( $statuses as $status => $status_name ) {
					if ( $app->status == $status )
					$sel = ' selected="selected"';
				else {
					$sel = '';
					if ( 'running' == $status )
						continue;
				}
				$html .= '<option value="'.$status.'"'.$sel.'>'. $status_name . '</option>';
			}
		}
		$html .= '</select>';
		$html .= '</span>';
		$html .= '</label>';
		
		$html  = apply_filters( 'app_inline_edit_appointment_fields_after', $html, $app_id );

		$html .= '</div>';
		$html .= '</fieldset>';
		
		$html .= '<div style="clear:both"></div>';

		/* General fields required for save and cancel */
		$html .= '<div class="inline-edit-save clearfix">';
		$html .= '<h4 class="app_iedit_actions">'.__('ACTIONS', 'wp-base').'</h4>';
		$html .= '<input type="hidden" name="app_id" value="'.$app_id.'" />';
		$html .= '<input type="hidden" name="parent_id" value="'.$app->parent_id.'" />';
		$html .= '<a href="javascript:void(0)" title="'.__('Cancel', 'wp-base').'" class="button-secondary cancel alignleft">'.__('Cancel','wp-base').'</a>';
		$html .= '<img class="waiting alignleft" style="display:none;" src="'.admin_url('images/wpspin_light.gif').'" alt="">';
		$html .= '<span class="error alignleft" style="display:none"></span>';

		if ( 'reserved' == $app->status ) {
			$js = 'style="display:none"';
			$title = __('GCal reserved appointments cannot be edited here. Edit them in your Google calendar.', 'wp-base');
		}
		else {
			$js = 'href="javascript:void(0)"';
			$title = __('Click to save or update', 'wp-base');
		}
		$save_text = $app_id ? __('Update','wp-base') : __('Save','wp-base');
		$html .= '<a '.$js.' title="'.$title.'" class="button-primary save alignright">'.$save_text.'</a>';

		/* emails */
		$html .= '<div class="alignright app_iedit_email_actions">';
		if ( $app_id )
			$text = __('(Re)Send email:','wp-base');
		else
			$text = __('Send email:','wp-base');
		
		$html .= '<label class="app_iedit_send_mail">';
		$html .= '<span class="title">'.$text.'</span>'. '&nbsp;&nbsp;&nbsp;&nbsp;';
		$html .= '</label>';

		/* Confirmation email */
		// Default is "checked" for a new appointment
		if ( $app_id ) {
			$c = '';
			if ( 'pending' == $app->status || 'removed' == $app->status || 'completed' == $app->status )
				$d = ' disabled="disabled"';
			else
				$d = '';
		}
		else {
			$c = ' checked="checked"';
			$d = '';
		}
			
		$html .= '<label class="app_iedit_send_confirm">';
		$html .= '<span class="title">';
		$html .= '<input type="checkbox" name="resend" value="1" '.$c.$d.' />&nbsp;' .__('Confirmed','wp-base') . '&nbsp;&nbsp;';
		$html .= '</span>';
		$html .= '</label>';

		/* Pending email */
		if ( $app_id ) {
			$c = '';
			if ( 'confirmed' == $app->status || 'paid' == $app->status || 'removed' == $app->status || 'completed' == $app->status )
				$d = ' disabled="disabled"';
			else
				$d = '';
		}
		else {
			$c = '';
			$d = ' disabled="disabled"';
		}
			
		$html .= '<label class="app_iedit_send_pending">';
		$html .= '<span class="title">';
		$html .= '<input type="checkbox" name="send_pending" value="1" '.$c.$d.' />&nbsp;' .__('Pending','wp-base') . '&nbsp;&nbsp;';
		$html .= '</span>';
		$html .= '</label>';

		/* Completed email */
		if ( $app_id ) {
			$c = '';
			if ( 'confirmed' == $app->status || 'paid' == $app->status || 'pending' == $app->status || 'removed' == $app->status )
				$d = ' disabled="disabled"';
			else
				$d = '';
		}
		else {
			$c = '';
			$d = ' disabled="disabled"';
		}
			
		$html .= '<label class="app_iedit_send_completed">';
		$html .= '<span class="title">';
		$html .= '<input type="checkbox" name="send_completed" value="1" '.$c.$d.' />&nbsp;' .__('Completed','wp-base') . '&nbsp;&nbsp;';
		$html .= '</span>';
		$html .= '</label>';

		/* Cancellation email */
		if ( $app_id ) {
			$c = '';
			if ( 'confirmed' == $app->status || 'paid' == $app->status || 'pending' == $app->status || 'removed' == $app->status || 'completed' == $app->status )
				$d = ' disabled="disabled"';
			else
				$d = '';
		}
		else {
			$c = '';
			$d = ' disabled="disabled"';
		}
		$html .= '<label class="app_iedit_send_cancel">';
		$html .= '<span class="title">';
		$html .= '<input type="checkbox" name="send_cancel" value="1" '.$d.' />&nbsp;' . __('Cancelled','wp-base');
		$html .= '</span>';
		$html .= '</label>';

		$html .= '</div>';
		$html = apply_filters( 'app_inline_edit_after_email_actions', $html, $app_id );

		$html .= '</div>';
		
		$html = apply_filters( 'app_inline_edit_after_actions', $html, $app_id );

		$html .= '</td>';
		$html .= '</tr>';
		
		if ( $echo )
			echo $html;
		else
			die( json_encode( array( 'result'=>$html, 'id'=>$js_id, 'js_tooltip'=>$js_tooltip, 'locked'=>$price_readonly ? 1 : 0 ) ) );
	}

	/**
	 * Save edited or new appointments on admin side
	 */	
	function inline_edit_save() {
		
		if ( !check_ajax_referer( 'inline_edit',false,false ) )
			die( json_encode( array('error'=>esc_js( $this->a->get_text('unauthorised') ) ) ) );
	
		if ( wpb_is_demo() )
			die( json_encode( array("result" => __('<span class="app-error">Changes cannot be saved in DEMO mode!</span>', 'wp-base') ) ) );
	
		$app_id = $_POST["app_id"]; // This can be existing app (update) or a new one (insert)
		global $wpdb, $current_user;
		$app = $this->a->get_app( $app_id );
		
		$is_update = false;
		$data = array();
		if ( ! empty( $app->ID  ) ) {
			$data['ID'] = $app->ID;
			$is_update = true;
			// Lsw data before save
			$app_location = $app->location;
			$app_service = $app->service;
			$app_worker = $app->worker;
			$app_start = $app->start;
			$previous_stat = $app->status;
		}
		else {
			$data['created']	= date("Y-m-d H:i:s", $this->a->_time );
			$data['ID'] 		= 'null';
			$app_location = $app_service = $app_worker = $app_start = 0;
			$previous_stat = '';
		}
		
		$data['user']			= $_POST['user'];
		$data['location']		= $_POST['location'];
		$data['service']		= $_POST['service'];
		$data['worker']			= $_POST['worker'];
		$data['price']			= $_POST['price'];
		$data['deposit']		= $_POST['deposit'];
		
		$service				= $this->a->get_service( $_POST['service'] ); // Service object
		
		if ( empty( $service->ID ) )
			die( json_encode( array("result" => __('<span class="app-error">Service does not exist!</span>', 'wp-base') ) ) );
		
		if ( $this->a->is_daily( $service->ID ) ) {
			$_POST['start_time'] 	= $_POST['end_time'] = '00:00:00';
			$_POST['end_date'] 		= date( 'Y-m-d', strtotime($_POST['start_date']) + wpb_get_duration( $service->ID ) *60 );
		}

		// Clear comma from date format. It creates problems in php5.2
		$data['start']		= date( 'Y-m-d H:i:s', strtotime( str_replace( ',','', $_POST['start_date'] ). " " . wpb_to_military( $_POST['start_time'] ) ) );
		$data['end']		= date( 'Y-m-d H:i:s', strtotime( str_replace( ',','', $_POST['end_date'] ). " " . wpb_to_military( $_POST['end_time'] ) ) );
		if ( isset( $_POST['parent_id'] ) ) {
			$data['parent_id']	= absint($_POST['parent_id']);
		}
		$data['status']		= $_POST['status'];
		$resend				= $_POST["resend"];
		$send_pending		= $_POST["send_pending"];
		$send_completed		= $_POST["send_completed"];
		$send_cancel		= $_POST["send_cancel"];
		
		// Let addons modify data
		$data = apply_filters( 'app_inline_edit_save_data', $data );
		
		if ( strtotime( $data['start'] ) > strtotime( $data['end'] ) )
			die( json_encode( array("result" => __('<span class="app-error">Booking starting time cannot be later than ending time!</span>', 'wp-base') ) ) );

		/* Strict check - Disabled if sending to removed, test or completed status */
		if ( $this->is_strict_check() && 'removed' != $data['status'] && 'completed' != $data['status'] && 'test' != $data['status'] ) {
	
			// Check if this service available in this location
			if ( $this->a->get_nof_locations() ) {
				$checks = $this->a->get_services_by_location( $data['location'] );
				$found = false;
				if ( $checks ) {
					foreach ( $checks as $check ) {
						if ( $check->ID == $data['service'] ) {
							$found = true;
							break;
						}
					}
				}
				if ( !$found )
					die( json_encode( array("result" => __('<span class="app-error">Selected service is not available in this location!</span>', 'wp-base') ) ) );
			}
			
			// Check if provider gives this service
			if ( $data['worker'] ) {
				$workers_by_s = $this->a->get_workers_by_service( $data['service'] );
				$found = false;
				if ( $workers_by_s ) {
					foreach ( $workers_by_s as $worker_by_s ) {
						if ( $worker_by_s->ID == $data['worker'] ) {
							$found = true;
							break;
						}
					}
				}
				if ( !$found )
					die( json_encode( array("result" => __('<span class="app-error">Selected service provider is not giving selected service!</span>', 'wp-base') ) ) );
			}
			
			$slot = new WpBSlot( new WpBCalendar( $data['location'], $data['service'], $data['worker'] ),
								strtotime( $data['start'], $this->a->_time ),
								strtotime( $data['end'], $this->a->_time )
								);
			
			# Reduce current app, because it may give us extra space 			
			if ( $is_update ) {
				$slot->set_app_id( $app_id );
				$virtual = clone $app;
				$virtual->ID = -1* intval( $app->ID ); 
				$virtual->seats = !empty( $app->seats ) ? -1 * intval($app->seats) : -1;
				$virtual_arr = array( $virtual->ID => $virtual );
			}
			else
				$virtual_arr = array( );
			
			# If we are editing, service provider is already available for this booking time. 
			if ( $data['location'] === $app_location && $data['service'] === $app_service && $data['worker'] === $app_worker && $data['start'] === $app_start ) {
				if ( ( empty($_POST['app_seats']) || $_POST['app_seats'] <= $app->seats ) ){
					# Same lsw, same or less seats. No need to check.
				}
				else {
					if ( $reason = $slot->is_busy( $virtual_arr ) )
						die( json_encode( array("result" => __('<span data-reason="'.$reason.'" class="app-error">This time slot is not available!</span>', 'wp-base') ) ) );
				}
			}
			else {
				if ( $data['worker'] && !$slot->is_working( ) )
					die( json_encode( array("result" => __('<span class="app-error">Selected service provider is not working at selected time interval!</span>', 'wp-base') ) ) );
				
				# Unassigned provider case
				if ( !$data['worker'] && 'yes' == wpb_setting( 'service_wh_check' ) && !$slot->is_service_working( ) ) {
					die( json_encode( array("result" => __('<span class="app-error">Selected service is not available at selected time interval!</span>', 'wp-base') ) ) );
				}

				if ( $reason = $slot->is_busy( $virtual_arr ) )
					die( json_encode( array("result" => sprintf( __('<span class="app-error">This time slot is not available! Reason: %s</span>', 'wp-base'), wpb_code2reason( $reason ) ) ) ) );
				
			}
		}
		
		do_action( 'app_inline_edit_save_before_save', $data, $is_update );

		$update_result = $insert_result = $email_sent = $user_data = null;
		$result_app_id = 0; # Resulting app_id (if update, equals app_id. If insert, wpdb->insert_id on success, 0 on error.
		
		if( $is_update ) {
			# Update
			$data = apply_filters( 'app_inline_edit_update_pre', $data );
			
			$result_app_id = $app_id;
			if ( $update_result = $wpdb->update( $this->a->app_table, $data, array('ID' => $app_id) ) ) {
				$user_data = BASE('User')->inline_edit_save( $result_app_id );
				
				do_action( 'app_inline_edit_updated', $result_app_id, $data, $app );
				
				if ( $resend ) {
					# Never allow confirmation email for removed appointments even if it is checked by accident
					if ( 'removed' == $data['status'] )
						do_action( 'app_admin_removed', $app_id );
					else
						$email_sent = $this->a->send_email( $app_id, 'confirmation', true ); # Do not send copy to admin
				}
				else if ( $send_pending && 'pending' == $data['status'] )
					$email_sent = $this->a->send_email( $app_id, 'pending', true ); 
				else if ( $send_completed && 'completed' == $data['status'] )
					$email_sent = $this->a->send_email( $app_id, 'completed', true ); 
				else if ( $send_cancel && 'removed' == $data['status'] )
					$email_sent = $this->a->send_email( $app_id, 'cancellation', true );
				
				$this->a->update_appointments( );
				
				if ( $previous_stat != $data['status'] )
					do_action( 'app_admin_status_changed', $data['status'], $result_app_id );
			}
			else if ( $update_result !== false ) {
				# Maybe we did not make any changes on app DB, but just in user/udf fields
				$user_data = BASE('User')->inline_edit_save( $result_app_id );
				
				if ( apply_filters( 'app_inline_edit_no_change', false, $result_app_id, $data, $app ) )
					$update_result = true;
				
				if ( $update_result || $user_data ) {
					wpb_flush_cache();
					do_action( 'app_inline_edit_user_fields_updated', $result_app_id, $data, $app );
				}
			}
		} 
		else {
			# Insert
			$data = apply_filters( 'app_inline_edit_insert_pre', $data );
		
			if( $insert_result = $wpdb->insert( $this->a->app_table, $data ) ) {
				$result_app_id = $wpdb->insert_id;
				
				$user_data = BASE('User')->inline_edit_save( $result_app_id );

				# Who made the booking? Record only if BOB
				if ( isset( $current_user->ID ) && $current_user->ID != $data['user'] ) {
					wpb_add_app_meta( $result_app_id, 'booking_on_behalf', $current_user->ID );
				}
				
				do_action( 'app_inline_edit_inserted', $result_app_id, $data, $app );
				
				if ( $resend )
					$email_sent = $this->a->send_email( $wpdb->insert_id );
				else if ( $send_pending && 'pending' == $data['status'] )
					$email_sent = $this->a->send_email( $wpdb->insert_id, 'pending' ); 
				else if ( $send_completed && 'completed' == $data['status'] )
					$email_sent = $this->a->send_email( $wpdb->insert_id, 'completed' ); 
				
				$this->a->update_appointments( );				
			}
		}
		
		# Transfer new values so that external tr can be changed dynamically
		# First fill with submitted user data
		$new_results = $user_data ? $user_data : array();		

		if ( empty( $app->parent_id ) ) {
			if ( $result_app_id )
				$user_text = BASE('User')->get_client_name( $result_app_id, null, true );
			else if ( !empty( $data['name'] ) )
				$user_text = $data['name'];	
		}
		else
			$user_text = false; # User name in child does not change

		if( !empty( $data['status'] ) ) {
			$stats = $this->a->get_statuses();
			if ( isset( $stats[$data['status']] ) )
				$stat_text = $stats[$data['status']];
			else
				$stat_text = $this->a->get_text('not_defined');
		} else
			$stat_text = __('None yet','wp-base');
		
		$p = $this->a->get_total_paid_by_app( $result_app_id, false );
		$total_paid = $p ? $p : 0;
		$balance = $total_paid/100 - $data['price'] - $data['deposit'];
		
		$new_results = array_merge( $new_results, 
			array(
				"result_app_id"	=>	$result_app_id,
				"user"			=>	!empty( $user_text ) ? $user_text : '',
				"date_time"		=>	mysql2date( $this->a->dt_format, $data['start'] ),
				"end_date_time"	=>	mysql2date( $this->a->dt_format, $data['end'] ),
				"location"		=>	$this->a->get_location_name( $data['location'] ),
				"service"		=>	$this->a->get_service_name( $data['service'] ),
				"worker"		=>	$this->a->get_worker_name( $data['worker'] ),
				"status"		=>	$stat_text,
				"price"			=>	wpb_format_currency( '', $data['price'], true ),
				"deposit"		=>	wpb_format_currency( '', $data['deposit'], true ),
				"total_paid"	=>	wpb_format_currency( '', $total_paid/100, true ),
				"balance"		=>	wpb_format_currency( '', $balance, true ),
				"collapse"		=>	'yes' == wpb_setting( 'admin_edit_collapse' ) ? 1 : 0,
		) );
		
		if ( $result_app_id )
			$app_id = $result_app_id;
		
		# To resend email without making any changes
		if ( $resend && !$email_sent )
			$email_sent = $this->a->send_email( $app_id, 'confirmation', true );

		if ( $send_pending && !$email_sent )
			$email_sent = $this->a->send_email( $app_id, 'pending', true );
		
		if ( $send_completed && !$email_sent )
			$email_sent = $this->a->send_email( $app_id, 'completed', true );

		# Save this in any case
		# Default is locked. We are saving unlock selection.
		if ( isset( $_POST['locked_check'] ) && empty( $_POST['locked'] ) )
			$lock_changed = wpb_update_app_meta( $app_id, 'unlocked', true ); 
		else
			$lock_changed = wpb_delete_app_meta( $app_id, 'unlocked' );
		
		if ( isset( $_POST['admin_note'] ) )
			$admin_note_changed = wpb_update_app_meta( $app_id, 'admin_note', $_POST['admin_note'] );
		
		if ( $update_result ) {
			# Log change of status
			if ( $data['status'] != $app->status ) {
				$this->a->log( sprintf( __('Status changed from %1$s to %2$s by %3$s for appointment ID:%4$d','wp-base'), $app->status, $data["status"], $current_user->user_login, $app->ID ) );
			}
			die( json_encode( array_merge( array("result" => __('<span class="app-success">Changes saved.</span>', 'wp-base') ), $new_results) ) );
		}
		else if ( $insert_result )
			die( json_encode( array_merge( array("result" => __('<span class="app-success">New appointment successfully saved.</span>', 'wp-base') ), $new_results) ) );
		else if ( $resend || $send_pending || $send_completed ) {
			if ( $email_sent )
				die( json_encode( array("result" => __('<span class="app-success">Email has been sent.</span>', 'wp-base') ) ) );
			else
				die( json_encode( array("result" => __('<span class="app-error">Email could not be sent! Check log file if reason has been recorded.</span>', 'wp-base') ) ) );
		}
		else if ( $lock_changed || $admin_note_changed ) {
			die( json_encode( array_merge( array("result" => __('<span class="app-success">Changes saved.</span>', 'wp-base') ), $new_results) ) );
		}
		else if ( $update_result === false || $insert_result === false )
			die( json_encode( array("result" => __('<span class="app-error">Record could not be saved!</span>', 'wp-base') ) ) );
		else
			die( json_encode( array("result" => __('<span class="app_b">You did not make any changes...</span>', 'wp-base') ) ) );
	}

	/**
	 * Check if strict check is asked ("True" means capabilities limited)
	 * return bool
	 * @since 2.0
	 */		
	function is_strict_check(){
		
		$override = isset( $_POST['override'] ) ? $_POST['override'] : 'inherit';

		if ( !$override )
			return true;
		else if ( 1 == $override )
			return false;
		else if ( 'inherit' == $override && 'yes' == wpb_setting('strict_check') )
			return true;
		
		return false;
	}

}
	BASE('AdminBookings')->add_hooks();
}