<?php
/**
 * WPB Admin Transactions
 *
 * Display and recording of transactions
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'WpBTransactions' ) ) {

class WpBTransactions {
	
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
		add_action( 'app_menu_before_all', array( $this, 'add_menu' ), 30 ); 			// Called to add menu item
	}
	
	/**
     * Add submenu page
     */
	function add_menu(){
		wpb_add_submenu_page('appointments', __('WPB Transactions','wp-base'), __('Transactions','wp-base'), array(WPB_ADMIN_CAP,'manage_transactions'), "app_transactions", array($this,'transaction_list'));
	}

	/**
	 *	Get transaction records
	 */
	function get_admin_transactions($type, $startat, $num, $force_app=false) {

		global $current_user;
		
		// Forec app_id for payment tooltip
		if ( $force_app ) {
			$_GET['app_s'] = $force_app;
			$_GET['stype'] = 'app_id';
		}
	
		// Search. Also selecting of one or more bookings
		if( isset( $_GET['app_s'] ) && trim( $_GET['app_s'] ) != '' ) {
			$s = wpb_sanitize_price( $_GET['app_s'] );
			$stype = $_GET['stype'];
			switch ( $stype ) {
				case 'app_id':				if ( strpos( $s, ',' ) !== false ) {
												$ses = explode( ',', $s );
												$q = ' 1=2 OR ';
												foreach ( $ses as $s1 ) {
													$q .= " transaction_app_ID='".trim( $s1 )."' OR ";
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
													$q .= " transaction_app_ID='".trim( $s1 )."' OR ";
												}
												$q = rtrim( $q, "OR " );
												$add = " AND (".$q.") "; 
											}
											else {
												$add = " AND transaction_app_ID='{$s}' ";
											}
											break;
				case 'name':				$add = " AND ( name LIKE '%{$s}%' OR user IN ( SELECT ID FROM {$this->a->db->users} WHERE user_login LIKE '%{$s}%' ) ) "; break;
				case 'transaction_id':		$add = " AND transaction_paypal_ID='{$s}' "; break;
				case 'amount':				$add = " AND ( IFNULL(transaction_total_amount,0) /100 ) = '{$s}' "; 
											break;
				case 'amount_gt':			$add = " AND ( IFNULL(transaction_total_amount,0) /100 ) > '{$s}' "; 
											break;
				case 'amount_lt':			$add = " AND ( IFNULL(transaction_total_amount,0) /100 ) < '{$s}' "; 
											break;
				default:					$add = apply_filters( 'app_transaction_search_switch', $add, $stype, $s ); break;
			}
		}
		else
			$add = "";

		// User filter/sort preferences
		$pref = get_user_meta( $current_user->ID, 'app_admin_pref', true );
		$pref = is_array( $pref ) ? $pref : array();
		
		foreach ( array( 'location_id', 'service_id', 'gateway', 'order_by', 'm' ) as $i ) {
			if( isset( $_GET['app_'.$i] ) )
				${$i} = esc_sql($_GET['app_'.$i]);
			else if ( isset( $pref[$i] ) )
				${$i} = esc_sql($pref[$i]);
			else
				${$i} =  '';			
		}
		$app_m = $m;

		// Filters		
		if ( !isset( $_GET['app_or_fltr'] ) ) {
			if( $location_id )
				$add .= " AND transaction_app_ID IN ( SELECT ID FROM {$this->a->app_table} WHERE location='{$location_id}' ) ";

			if( $service_id )
				$add .= " AND transaction_app_ID IN ( SELECT ID FROM {$this->a->app_table} WHERE service='{$service_id}' )  ";

			if ( $gateway )
				$add .= " AND transaction_gateway='{$gateway}' ";
			
			if ( $app_m ) {
				$year = (int) substr( $app_m, 0, 4 );
				$month = (int) substr( $app_m, 4, 2 );
				if ( $year && $month ) {
					$date_start = $year ."-". $month . "-01 00:00:00";
					$add .= " AND transaction_stamp > UNIX_TIMESTAMP('{$date_start}') AND transaction_stamp < UNIX_TIMESTAMP(DATE_ADD('{$date_start}', INTERVAL 1 MONTH))";
				}
			}
		}
		
		$test = str_replace( array('desc', 'asc', '|',' ' ), '', strtolower( $order_by ) );
		if ( $test && in_array( $test, array('transaction_app_id','transaction_stamp') ) )
			$order_by = str_replace( '|', ' ', $order_by );
		else
			$order_by = "transaction_stamp DESC";

		switch($type) {

			case 'all':
						$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$this->a->transaction_table} WHERE (1=1) {$add} ORDER BY {$order_by} LIMIT {$startat}, {$num}";
						break;
			case 'past':
						$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$this->a->transaction_table} WHERE transaction_status IN ('paid','Completed','Processed') {$add} ORDER BY {$order_by} LIMIT {$startat}, {$num}";
						break;
			case 'pending':
						$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$this->a->transaction_table} WHERE transaction_status IN ('pending') {$add} ORDER BY {$order_by} LIMIT {$startat}, {$num}";
						break;
			case 'future':
						$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$this->a->transaction_table} WHERE transaction_status IN ('future') {$add} ORDER BY {$order_by} LIMIT {$startat}, {$num}";
						break;
			default:
						$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$this->a->transaction_table} WHERE transaction_status IN ('paid','Completed','Processed') {$add} ORDER BY {$order_by} LIMIT {$startat}, {$num}";
						break;

		}

		return $this->a->db->get_results( $sql, OBJECT_K );
	}

	/**
	 *	Find if a Paypal transaction is duplicate or not
	 */
	function duplicate_transaction($app_id, $amount, $currency, $timestamp, $paypal_ID, $status, $note,$content=0) {
		$sql = $this->a->db->prepare( "SELECT transaction_ID FROM {$this->a->transaction_table} WHERE transaction_app_ID = %d AND transaction_paypal_ID = %s AND transaction_stamp = %d LIMIT 1 ", $app_id, $paypal_ID, $timestamp );

		$trans = $this->a->db->get_var( $sql );
		if(!empty($trans)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 *	Save or edit a Transaction record in the database
	 */
	function record($app_id, $amount, $currency, $timestamp, $paypal_ID, $status, $note, $gateway='', $transaction_ID=0) {
		
		// Create a uniqe ID in manual payments, if not given by user
		if ( 'manual-payments' == $gateway && !$paypal_ID )
			$paypal_ID = uniqid('auto_');

		$data = array();
		$data['transaction_app_ID']			= $app_id;
		$data['transaction_paypal_ID']		= $paypal_ID;
		$data['transaction_stamp']			= $timestamp;
		$data['transaction_currency']		= $currency;
		$data['transaction_status']			= $status;
		$data['transaction_total_amount']	= intval(strval($amount * 100));
		$data['transaction_note']			= wp_unslash( $note );
		$data['transaction_gateway']		= $gateway;
		
		// If we are editing a manual payment
		if ( $transaction_ID ) {
			// In the query we add manual payment check too, since we do not want an auto transaction to be edited 
			$result = $this->a->db->update( $this->a->transaction_table, $data, array('transaction_ID' => $transaction_ID, 'transaction_gateway'=>'manual-payments') );
		}
		else {
			$existing_id = $this->a->db->get_var( $this->a->db->prepare( "SELECT transaction_ID FROM {$this->a->transaction_table} WHERE transaction_app_ID = %d AND transaction_paypal_ID = %s LIMIT 1", $app_id, $paypal_ID ) );

			if( $existing_id ) {
				$result = $this->a->db->update( $this->a->transaction_table, $data, array('transaction_ID' => $existing_id) );
			} else {
				$result = $this->a->db->insert( $this->a->transaction_table, $data );			
			}
		}

		return $result;
	}

	/**
	 *	Get total row number of previous query
	 */
	function get_total() {
		return $this->a->db->get_var( "SELECT FOUND_ROWS();" );
	}

	/**
	 *	Generate html for Transactions page
	 */
	function transaction_list() {
		
		wpb_admin_access_check( 'manage_transactions' );

		global $pagenow, $action, $type, $current_user, $wpdb, $wp_locale;

		wp_reset_vars( array('type') );

		if ( empty( $type ) ) $type = 'past';

		/* Search */
		$filter = array();
		if ( isset( $_GET['app_s'] ) ) {
			$s = stripslashes($_GET['app_s']);
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
		
		// User filter/sort preferences
		$pref = get_user_meta( $current_user->ID, 'app_admin_pref', true );
		$pref = is_array( $pref ) ? $pref : array();
		
		foreach ( array( 'location_id', 'service_id', 'gateway', 'order_by', 'm' ) as $i ) {
			if( isset( $_GET['app_'.$i] ) )
				${$i} = $_GET['app_'.$i];
			else if ( isset( $pref[$i] ) )
				${$i} = $pref[$i];
			else
				${$i} =  '';			
		}
		$app_m = $m;

		?>
		<div class='wrap app-page'>
			<h2 class="app-dashicons-before dashicons-money"><?php echo __('Transactions','wp-base'); ?></h2>

			<div class="app-manage-row clearfix">
				<div class="alignleft actions app-manage-first-column" style="display:none">
					<ul class="subsubsub">
						<li><a href="<?php echo wpb_add_query_arg('type', 'past'); ?>" class="rbutton <?php if($type == 'past') echo 'current'; ?>"><?php  _e('Recent transactions', 'wp-base'); ?></a> | </li>
						<li><a href="<?php echo wpb_add_query_arg('type', 'pending'); ?>" class="rbutton <?php if($type == 'pending') echo 'current'; ?>"><?php  _e('Pending transactions', 'wp-base'); ?></a> | </li>
						<li><a href="<?php echo wpb_add_query_arg('type', 'future'); ?>" class="rbutton <?php if($type == 'future') echo 'current'; ?>"><?php  _e('Future transactions', 'wp-base'); ?></a></li>
					</ul>
				</div>

				<div class="alignright actions app-manage-second-column">
					<form id="app-search-form" method="get" action="<?php echo wpb_add_query_arg('page', 'app_transactions'); ?>" class="search-form">
						<input type="hidden" value="app_transactions" name="page" />
						<input type="hidden" value="all" name="type" />
						<input type="hidden" value="1" name="app_or_fltr" />
						<input type="text" value="<?php echo esc_attr($s); ?>" name="app_s" placeholder="<?php _e('Enter a search term','wp-base'); ?>" />
						<select name="stype" title="<?php _e('Select which field to search. For appointment ID search, multiple IDs separated with comma or space is possible.','wp-base') ?>">
							<option value="name" <?php selected( $stype, 'name' ); ?>><?php _e('Name','wp-base'); ?></option>
							<option value="app_id" <?php selected( $stype, 'app_id' ); ?> title="<?php _e('Multiple IDs separated with comma or space is possible','wp-base')?>"><?php _e('App. ID','wp-base'); ?></option>
							<option value="transaction_id" <?php selected( $stype, 'transaction_id' ); ?>><?php _e('Transaction ID','wp-base'); ?></option>
							<option value="amount" <?php selected( $stype, 'amount' ); ?>><?php _e('Amount =','wp-base'); ?></option>
							<option value="amount_gt" <?php selected( $stype, 'amount_gt' ); ?>><?php _e('Amount &gt;','wp-base'); ?></option>
							<option value="amount_lt" <?php selected( $stype, 'amount_lt' ); ?>><?php _e('Amount &lt;','wp-base'); ?></option>
							<?php do_action( 'app_transaction_search_options', $stype ) ?>
						</select>			
						<input type="submit" class="button app-search-button" value="<?php _e('Search','wp-base'); ?>" />
					</form>
				</div>
			</div>

			<div class="tablenav top app-manage-first-row">

				<div class="alignleft actions app-manage-first-column">
					<form method="get" action="<?php echo wpb_add_query_arg('page', 'appointments'); ?>" >
						<input type="hidden" value="app_transactions" name="page" />
						<input type="hidden" value="<?php echo $type?>" name="type" />
						<input type="hidden" value="1" name="app_filter_reset" />
						<input type="submit" class="button" value="<?php _e('Reset','wp-base'); ?>" />
					</form>
				</div>

				<div class="alignleft actions app-manage-second-column">
					<form method="get" action="<?php echo wpb_add_query_arg('page', 'appointments'); ?>" >
					<?php
					switch($type) {
						case 'all':			$where = " WHERE 1=1 "; break;
						case 'past':		$where = " WHERE transaction_status IN ('paid','Completed','Processed') "; break;
						case 'pending':		$where = " WHERE transaction_status IN ('pending') "; break;
						case 'future':		$where = " WHERE transaction_status IN ('future') "; break;
						default:			$where = " WHERE transaction_status IN ('paid','Completed','Processed') "; break;
					}
					
					$where = apply_filters( 'app_admin_transactions_where', $where, $type );
					
					$months = $wpdb->get_results( "
						SELECT DISTINCT 
							MONTH(FROM_UNIXTIME(transaction_stamp)) AS month,
							YEAR(FROM_UNIXTIME(transaction_stamp)) AS year
						FROM {$this->a->transaction_table}
						{$where}
						GROUP BY month, year
						ORDER BY transaction_stamp
					" );

					$month_count = $months ? count( $months ) : 0;

					if ( $month_count && ( 1 != $month_count || 0 != $months[0]->month ) ) {
						$add_class = $app_m ? 'class="app-option-selected"' : '';
						?>
						<select name="app_m" style="float:none;" <?php echo $add_class ?>>
							<option value=""><?php _e('Filter by month','wp-base'); ?></option>						
						<?php
						
						foreach ( $months as $arc_row ) {
							if ( 0 == $arc_row->year )
								continue;

							$month = zeroise( $arc_row->month, 2 );
							$year = $arc_row->year;

							printf( "<option %s value='%s'>%s</option>\n",
								selected( $app_m, $year . $month, false ),
								esc_attr( $arc_row->year . $month ),
								/* translators: 1: month name, 2: 4-digit year */
								sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
							);
						}
						?>
						</select>
					<?php }					

					$locations = $this->a->get_locations( 'name' );
					if ( $locations ) { 
						$add_class = $location_id ? 'class="app-option-selected"' : '';
					?>
					<select name="app_location_id" style="float:none;" <?php echo $add_class ?>>
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
					<select name="app_service_id" style="float:none;" <?php echo $add_class ?>>
						<option value=""><?php _e('Filter by service','wp-base'); ?></option>
					<?php
					$services = $this->a->get_services( 'name' );
					if ( $services ) {
						foreach ( $services as $service ) {
							if ( $service_id == $service->ID )
								$selected = " selected='selected' ";
							else
								$selected = "";
							echo '<option '.$selected.' value="'.$service->ID.'">'. $this->a->get_service_name( $service->ID ) .'</option>';
						}
					}
					?>
					</select>			

					<input type="hidden" value="app_transactions" name="page" />
					<input type="hidden" value="<?php echo $type?>" name="type" />						
					<input type="submit" class="button" value="<?php _e('Filter','wp-base'); ?>" />
					</form>
				</div>

				<div class="alignleft actions app-manage-third-column">
					<form id="app-sort-form" method="get" action="<?php echo wpb_add_query_arg('page', 'appointments'); ?>" >
						<input type="hidden" value="app_transactions" name="page" />
						<input type="hidden" value="<?php echo $type?>" name="type" />
						<input type="hidden" value="<?php echo $location_id?>" name="app_location_id" />
						<input type="hidden" value="<?php echo $service_id?>" name="app_service_id" />
						<select name="app_order_by" style='float:none;'>
							<option value=""><?php _e('Sort by','wp-base'); ?></option>
							<option value="transaction_stamp|DESC" <?php selected( $order_by, 'transaction_stamp|DESC' ); ?>><?php _e('Transaction date (Most recent first)','wp-base'); ?></option>
							<option value="transaction_stamp" <?php selected( $order_by, 'transaction_stamp' ); ?>><?php _e('Transaction date (Most recent last)','wp-base'); ?></option>
							<option value="transaction_app_ID|DESC" <?php selected( $order_by, 'transaction_app_ID|DESC' ); ?>><?php _e('App. ID (Highest first)','wp-base'); ?></option>
							<option value="transaction_app_ID" <?php selected( $order_by, 'transaction_app_ID' ); ?>><?php _e('App. ID (Lowest first)','wp-base'); ?></option>
						</select>			
						<input type="submit" class="button" value="<?php _e('Sort','wp-base'); ?>"  />
					</form>
				</div>

			<?php
				if ( is_admin() )
					$paged = empty( $_GET['paged'] ) ? 1 : (int)$_GET['paged'];
				else
					$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
				
				$rpp = wpb_setting("records_per_page", 50);

				$startat = ($paged - 1) * $rpp;

				$transactions = $this->get_admin_transactions($type, $startat, $rpp);
				$total = $this->get_total();

				$trans_navigation = paginate_links( array(
					'base' => wpb_add_query_arg( 'paged', '%#%' ),
					'format' => '',
					'total' => ceil($total / $rpp),
					'current' => $paged
				));
				
			if ( $trans_navigation ) { ?>
				<div class="alignright actions app-manage-fourth-column">
				<div class='tablenav-pages'><?php echo $trans_navigation?></div>
				</div>
			<?php } ?>
			

		</div>
			<?php
				$this->mytransactions( $transactions );
			?>
		</div>
		<?php
		
		add_action( 'admin_footer', array( $this, 'footer' ) );
	}
	
	/**
	 *	Add script to adjust control part of bookings table
	 */		
	function footer(){
		if ( !empty( $this->a->footer_script_added ) )
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
		
		$this->a->footer_script_added = true;
	}

	/**
	 * Render table
	 * @param short: Render table for tooltip (hide unnecessary columns)
	 */
	function mytransactions( $transactions, $short=false ) {
		
		global $app_gateway_plugins, $app_addons;

		$columns = array();

		$columns['db_id']	= '<abbr title="'.__('ID of the transaction in the database','wp-base','wp-base').'">'. __('ID','wp-base'). '</abbr>';
		$columns['user']	= __('Client','wp-base');
		$columns['app_id']	= '<abbr title="'.__('Booking ID for which the transaction has been made','wp-base').'">'.__('BID','wp-base'). '</abbr>';
		$columns['date']	= __('Date/Time','wp-base');
		$columns['service']	= __('Service','wp-base');
		$columns['amount']	= __('Amount','wp-base');
		$columns['transid']	= '<abbr title="'.__('For automatic payments, ID of the transaction provided by the payment gateway. For manual payments, optional reference number. If left empty, a unique ID will be automatically added.','wp-base').'">'.__('Reference','wp-base'). '</abbr>';
		$columns['note']	= '<abbr title="'.__('Mouse over the icon to read transaction note (e.g. last digits of credit card number). Icon is only displayed when there is a note.','wp-base').'">'.__('Note','wp-base'). '</abbr>';
		$columns['status']	= __('Status','wp-base');
		$columns['gateway']	= __('Gateway','wp-base');
		
		// Columns which will be hidden when short table is selected (e.g. to display transactions in tooltip)
		$hide_when_short = array('user','app_id','service','note','status');
			
		?>
			<table class="wp-list-table widefat dt-responsive display dataTable striped app-transactions">
				<thead>
				<tr>
				<?php
					foreach($columns as $key => $col) {
						$hide = $transactions && $short && ( in_array( $key, $hide_when_short ) ) ? ' style="display:none"' : '';
						$col_primary = in_array( $key, array( 'user' ) ) ? " column-primary" : "";
						// $col_check = in_array( $key, array( 'db_id' ) ) ? " check-column" : "";
						?>
						<th <?php echo $hide; ?> class="manage-column column-<?php echo $key.$col_primary; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
						<?php
					}
				?>
				</tr>
				</thead>

				<tbody>
					<?php
					if($transactions) {
						foreach($transactions as $tkey => $t) {
							$hide = $short ? ' style="display:none"' : '';
							$service_id = $this->a->db->get_var( "SELECT service FROM " . $this->a->app_table . " WHERE ID=". $t->transaction_app_ID . " " );
							?>
							<tr class="app-tr app-trans-tr app-trans-<?php echo $t->transaction_ID ?>">
								<th scope="row" class="column-db_id check-column">
									<?php
									if ( isset( $app_gateway_plugins[$t->transaction_gateway] ) ) {
										if ( 'manual-payments' == $t->transaction_gateway ) {
											if ( BASE('Addons')->is_gateway_active( 'manual-payments' ) ) {											
												echo '<a href="javascript:void(0)" data-app_id="'.$t->transaction_app_ID.'" data-db_id="'.$t->transaction_ID.'" class="app-trans-edit" title="'.__('Click to edit manual payment','wp-base').'">' . 
												$t->transaction_ID .
												'</a>';
											}
											else echo $t->transaction_ID;
										}
										else
											echo '<span title="'.__('Payments coming from payment gateways cannot be edited','wp-base').'">' . $t->transaction_ID .'</span>';
									}
									else echo $t->transaction_ID;
									?>
								
								</th>
								<td <?php echo $hide; ?> class="column-user column-primary has-row-actions">
									<div class="user-inner">
									<?php
										echo BASE('User')->get_client_name( $t->transaction_app_ID, null, true );
										echo '<span class="booking-info">'.$this->a->get_app_link($t->transaction_app_ID) .'</span>';
										echo '<span class="booking-info">'.wpb_cut( $this->a->get_service_name( $service_id ) ) .'</span>';
										echo '<span class="booking-info">'. wpb_format_currency( $t->transaction_currency, $t->transaction_total_amount / 100 ) .'</span>';
										echo '<span class="booking-info">'. date_i18n($this->a->dt_format, $t->transaction_stamp) .'</span>';
										echo '</div><div class="row-actions">';
										if ( isset( $app_gateway_plugins[$t->transaction_gateway] ) ) {
											if ( 'manual-payments' == $t->transaction_gateway ){
												if ( BASE('Addons')->is_gateway_active( 'manual-payments' ) ) {
													$tt = __('Click to edit manual payment','wp-base');
													$style = '';
												}
												else {
													$tt = __('Manual Payment gateway is not active','wp-base');
													$style = ' style="opacity:0.3"';
												}
												 echo '<div'.$style.'><span class="dashicons dashicons-edit"></span><a href="javascript:void(0)" data-app_id="'.$t->transaction_app_ID.'" class="app-trans-edit" title="'.$tt.'">' . 
												__('Edit', 'wp-base') .'</a></div>'; 
											}
											else 
												 echo '<span title="'.__('Payments coming from payment gateways cannot be edited','wp-base').'">' . __('Cannot be edited', 'wp-base').'</span>';
										}
									?>
									</div>
									<button type="button" class="toggle-row">
										<span class="screen-reader-text"><?php _e( 'Show more details', 'wp-base' ) ?></span>
									</button>
								</td>
								<td <?php echo $hide; ?> class="column-app_id" data-colname="<?php echo esc_attr(strip_tags($columns['app_id'])) ?>">
									<?php
										echo $this->a->get_app_link($t->transaction_app_ID);
									?>
								
								</td>
								<td class="column-date" data-colname="<?php echo $columns['date'] ?>">
									<?php
										echo '<span title="'. date_i18n("l", $t->transaction_stamp). '">' . date_i18n($this->a->dt_format, $t->transaction_stamp). '</span>';

									?>
								</td>
								<td <?php echo $hide; ?> class="column-service" data-colname="<?php echo $columns['service'] ?>">
								<?php
								echo wpb_cut( $this->a->get_service_name( $service_id ) );
								?>
								</td>
								<td class="column-amount" data-colname="<?php echo $columns['amount'] ?>"><span>
									<?php
										echo wpb_format_currency( $t->transaction_currency, $t->transaction_total_amount / 100 );
									?>
									</span>
									<input type="hidden" class="app-trans-amount-net" value="<?php echo $t->transaction_total_amount / 100 ?>" />
									<input type="hidden" class="app-trans-currency" value="<?php echo $t->transaction_currency ?>" />
								</td>
								<td class="column-transid" data-colname="<?php echo esc_attr(strip_tags($columns['transid'])) ?>">
									<?php
										if(!empty($t->transaction_paypal_ID))
											echo $t->transaction_paypal_ID;
									?>
								</td>
								<td <?php echo $hide; ?> class="column-note" data-colname="<?php echo esc_attr(strip_tags($columns['note'])) ?>">
									<?php
										if(!empty($t->transaction_note))
											echo '<span class="dashicons dashicons-admin-comments" title="'.$t->transaction_note.'"></span>';
										else
											echo '&nbsp;';
									?>
								</td>
								<td <?php echo $hide; ?> class="column-status" data-colname="<?php echo $columns['status'] ?>">
									<?php
										if(!empty($t->transaction_status)) {
											echo $t->transaction_status;
										} else {
											echo __('None yet','wp-base');
										}
									?>
								</td>
								<td class="column-gateway" data-colname="<?php echo $columns['gateway'] ?>">
									<?php
										if(!empty($t->transaction_gateway)) {
											if ( isset( $app_gateway_plugins[$t->transaction_gateway] ) ) {
												$plugin = $app_gateway_plugins[$t->transaction_gateway];
												echo wpb_cut( $plugin[1], 1, true );
											}
											else echo $this->a->get_text('unknown');											
										} else {
											echo __('-','wp-base');
										}
									?>
									<input type="hidden" class="app-trans-gateway" value="<?php echo $t->transaction_gateway ?>" />
								</td>
							</tr>
							<?php
						}
					} else {
						$columncount = $columns ? count($columns) : 0;
						?>
						<tr class="alternate" >
							<td colspan="<?php echo $columncount; ?>" scope="row"><?php _e('No Transactions have been found.','wp-base'); ?></td>
						</tr>
						<?php
					}
					?>

				</tbody>
				
				<tfoot>
				<tr>
				<?php
					foreach($columns as $key => $col) {
						$hide = $transactions && $short && ( in_array( $key, $hide_when_short ) ) ? ' style="display:none"' : '';
						$col_primary = in_array( $key, array( 'user' ) ) ? " column-primary" : "";
						// $col_check = in_array( $key, array( 'db_id' ) ) ? " check-column" : "";
						?>
						<th <?php echo $hide; ?> class="manage-column column-<?php echo $key.$col_primary; ?>" scope="col"><?php echo $col; ?></th>
						<?php
					}
				?>
				</tr>
				</tfoot>
			</table>
			<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$(".app-transactions").DataTable({ <?php echo $this->a->get_datatable_admin_args() ?> });
			});
			</script>
			
		<?php
		do_action( 'app_mytransactions_after', $transactions );
	}	
}
	BASE('Transactions')->add_hooks();
}	