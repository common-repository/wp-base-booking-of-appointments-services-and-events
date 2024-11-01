<?php
/**
 * WPB Admin Services
 *
 * Handles display and creation of services on admin side 
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

if ( !class_exists( 'WpBAdminServices' ) ) {

class WpBAdminServices {
	
	var $row_number = 0;

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
     * Add actions and filters
     */
	function add_hooks() {
		add_filter( 'appointments_business_tabs', array( $this, 'add_services_tab' ), 4 ); 		// Add tab, should have higher priority than Locations
		add_action( 'app_business_services_tab', array( $this, 'services_tab' ) );
		add_action( 'app_save_settings', array( $this, 'save_settings' ) );
	}
	
	/**
	 *	Add "services" tab
	 */
	function add_services_tab( $tabs ) {
		if ( wpb_admin_access_check( 'manage_services', false ) )
			$tabs['services'] =  __('Services', 'wp-base');

		return $tabs;
	}
	
	/**
	 *	Return number of columns
	 */
	function nof_columns(){
		$tabs = apply_filters( 'app_admin_services_add_more_tab', array() );
		if ( $tabs && count( $tabs ) == 1 )
			return 14;
		else
			return BASE('ESF') ? (BASE('Locations') ? 12 : 11) :7;
	}
	
	/**
	 * Render tab
	 * @param $by_worker: Worker is viewing/creating own services
	 */
	function services_tab( $by_worker=false ) {
		
		wpb_admin_access_check( 'manage_services' );
		
		global $current_user;
		?>	
		<div id="poststuff" class="metabox-holder">
		<?php
			/* Descriptions */
			$desc = WpBConstant::service_desc();
			$visible = $desc[0];
			unset( $desc[0] );
			wpb_infobox( $visible, $desc );
			
			do_action( 'app_admin_services_after_info' );
		
			?>
		<form id="add_services_form" class="app_form" method="post" action="<?php echo wpb_add_query_arg( null, null )?>">

		<div id="tabs" class="app-tabs">
			<ul></ul>
		
			<div class="postbox">
			<h3 class="hndle"><span><?php _e('List of Services', 'wp-base') ?></span></h3>

			<?php do_action( 'app_admin_services_before_add_new' ) ?>

			<div class='app-submit alignleft' style="width:30%">
				<input type="button" id="add_service" class='button-secondary' value='<?php _e( 'Add New Service', 'wp-base' ) ?>' />
				<img class="add-new-waiting" style="display:none;" src="<?php echo admin_url('images/wpspin_light.gif')?>" alt="">
			</div>
		
			<?php do_action( 'app_admin_services_after_add_new' ) ?>

			<?php
				if ( $this->a->get_nof_locations() )
					$loc_width = 16;
				else
					$loc_width = 10;
				if ( $this->a->get_nof_categories() )
					$cat_width = 16;
				else
					$cat_width = 10;
				$name_width = max( 20, (40-$loc_width-$cat_width) );
				
				$colspan = 6;
				
			?>
			<table class="widefat app-services-table" id="services-table" >
				<thead>
				<tr>
					<th class="app-service-id-th" style="width:6%"><span class="app-service-id"><?php _e( 'ID', 'wp-base') ?></span><?php if ( apply_filters( 'app_admin_services_add_more_th', false ) ) $this->add_more_th(); ?></th>
					<th class="app-service-name" style="width:20%"><abbr title="<?php _e('You must enter a name, otherwise service will not be saved!', 'wp-base') ?>"><?php _e( 'Name*', 'wp-base') ?></abbr></th>
					<?php if ( BASE('ESF') ) : ?>
						<th class="app-service-internal" style="width:4%"><abbr title=<?php _e('Internal: Service cannot be selected using Services pulldown menu','wp-base') ?>"><?php _e( 'Int', 'wp-base') ?></abbr></th>
					<?php $colspan = $colspan +1; endif; ?>
					<?php if ( BASE('Locations') ) : ?>
						<th class="app-service-location" style="width:<?php echo $loc_width?>%"><?php _e( 'Locations', 'wp-base') ?></th>
					<?php $colspan = $colspan +1; endif; ?>
					<?php if ( BASE('Categories') ) : ?>
						<th class="app-service-category" style="width:<?php echo $cat_width?>%"><?php _e( 'Categories', 'wp-base') ?></th>
					<?php $colspan = $colspan +1; endif; ?>
					<th class="app-service-capacity" style="width:7%"><?php _e( 'Capacity', 'wp-base') ?></th>
					<th class="app-service-duration" style="width:6.5%"><?php _e( 'Duration', 'wp-base') ?></th>
					<?php if ( BASE('ESF') ) : ?>
						<th class="app-service-break" style="width:6.5%"><?php _e( 'Padding before', 'wp-base') ?></th>
						<th class="app-service-break" style="width:6.5%"><?php _e( 'Padding after', 'wp-base') ?></th>
					<?php $colspan = $colspan +2; endif; ?>
					<th class="app-service-price" style="width:7%"><?php echo __( 'Price', 'wp-base') . ' ('. wpb_format_currency( wpb_setting('currency') ). ')' ?></th>
					<?php if ( BASE('ESF') ) : ?>
						<th class="app-service-deposit" style="width:7%"><?php echo __( 'Security Deposit', 'wp-base') . ' ('. wpb_format_currency( wpb_setting('currency') ). ')' ?></th>
					<?php $colspan = $colspan +1; endif; ?>
					<th class="app-service-page" style=""><?php _e( 'Description page', 'wp-base') ?></th>
				</tr>
				</thead>
				

				<?php
				if( empty( $_GET['paged'] ) )
					$paged = 1;
				else
					$paged =(int) $_GET['paged'];
				
				$rpp = (int)wpb_setting("records_per_page_business");
				$rpp = $rpp > 30 || $rpp < 1 ? 10 : $rpp;	// Limited to 30 services/page

				$startat = ($paged - 1) * $rpp;
				
				$key = 'created_by';
				
				if ( $by_worker )
					$total = $this->a->db->get_var("SELECT COUNT(ID) FROM ".$this->a->services_table." WHERE ID IN ( SELECT object_id FROM ".$this->a->meta_table." WHERE meta_type='service' AND meta_key='created_by' AND meta_value=".get_current_user_id()." ) " );
				else
					$total = $this->a->get_nof_services();
				
				$trans_navigation = paginate_links( array(
					'base' => wpb_add_query_arg( 'paged', '%#%' ),
					'format' => '',
					'total' => ceil($total / $rpp),
					'current' => $paged
				));

				if ( $trans_navigation ) {
					echo '<div class="tablenav">';
					echo "<div class='tablenav-pages'>$trans_navigation</div>";
					echo '</div>';
				}
				
				$max_id = $this->a->db->get_var("SELECT MAX(ID) FROM " . $this->a->services_table . " " );
																															
				if ( $by_worker )
					$services = $this->a->db->get_results("SELECT * FROM " . $this->a->services_table . " WHERE ID IN ( SELECT object_id FROM ".$this->a->meta_table." WHERE meta_type='service' AND meta_key='created_by' AND meta_value=".get_current_user_id()." )  ORDER BY sort_order,ID LIMIT {$startat}, {$rpp} " ); 				
				else
					$services = $this->a->db->get_results("SELECT * FROM " . $this->a->services_table . " ORDER BY sort_order,ID LIMIT {$startat}, {$rpp} " ); 				

				if ( is_array( $services ) && $nos = count( $services ) ) {
					foreach ( $services as $service ) {
						echo $this->add_service( true, $service );
						if ( $service->ID > $max_id )
							$max_id = $service->ID;
					}
				}
				else {
					echo '<tr class="no_services_defined"><td colspan="'.$colspan.'">'. __( 'No services defined', 'wp-base' ) . '</td></tr>';
				}
				?>
				
				</table>
		</div>

		<?php do_action( 'app_admin_services_after_per', $by_worker ) ?>
		
		</div><!-- Tabs -->
				
			<div class='submit' id='div_save_services' <?php if ($max_id==null) echo 'style="display:none"' ?>>
				<input type="hidden" name="by_worker" value="<?php echo $by_worker;?>" /> 
				<input type="hidden" name="number_of_services" id="number_of_services" value="<?php echo $max_id;?>" /> 
				<input type="hidden" name="action_app" value="save_services" />
				<?php wp_nonce_field( 'update_app_settings', 'app_nonce' ); ?>
				<input id="save_services_button" class='button-primary save_services' type='submit' value='<?php _e( 'Save Services', 'wp-base' ) ?>' />
				&nbsp;&nbsp;
				<?php _e( '<i>Tip: To delete a service, just clear its name and save.</i>', 'wp-base' ); ?>
			</div>
			
			</form>
			<script type="text/javascript">
			jQuery(document).ready(function($){
				var ms_ops = {
					noneSelectedText:'<?php echo esc_js( __('Select', 'wp-base' )) ?>',
					checkAllText:'<?php echo esc_js( __('Check all', 'wp-base' )) ?>',
					uncheckAllText:'<?php echo esc_js( __('Uncheck all', 'wp-base' )) ?>',
					selectedText:'<?php echo esc_js( __('# selected', 'wp-base' )) ?>',
					selectedList:5,
					position: {
					  my: 'left bottom',
					  at: 'left top'
					},
					minWidth:"170px",
					classes:"app_select_small"
				};
				$(".add_location_multiple").multiselect(ms_ops);
				$(".add_category_multiple").multiselect(ms_ops);
				$('#add_service').click(function(e){
					e.preventDefault();
					$('.add-new-waiting').show();
					var n = 1;
					if ( $('#number_of_services').val() > 0 ) {
						n = parseInt( $('#number_of_services').val() ) + 1;
					}
					$('#services-table').append('<?php echo wpb_esc_rn( $this->add_service( false, '' ) )?>');
					$('#number_of_services').val(n);
					$('#div_save_services').show();
					$('.no_services_defined').hide();
					$(".add_location_multiple").multiselect(ms_ops);
					$(".add_category_multiple").multiselect(ms_ops);
					$('.add-new-waiting').hide();
					$('input[name="services\[-'+n+'\]\[name\]"]').focus();
					$(document).trigger("app-add-new-service-clicked");
				});

				$('#save_services_button').click(function(e) {
					var emptied = false;
					$( ".app_service_name").each(function() {
						if ($.trim( $(this).val()) == ''){emptied = true;}
					});
					if (emptied) {
						e.preventDefault();
						if ( !confirm('<?php echo esc_js( __("You are about to delete at least one of the services. There may be bookings using these services. Are you sure to do this?",'wp-base') ) ?>') ) {return false;}
						else{
							$('#add_services_form').submit();
						}
					}
				});
				
			});
			</script>
		</div>
		<?php 		
	}
	
	/**
	 *	Add a service
	 *  @param _php: True if this will be used in first call, false if this is js
	 *  @param service: Service object that will be displayed (only when php is true)
	 */	
	function add_service( $_php = false, $service='' ) {
		if ( $_php ) {
			if ( is_object($service) ) {
				$n = $service->ID;
				$sign = '';
				$internal = isset( $service->internal ) ? $service->internal : 0;
				$name = $service->name;
				$capacity = isset( $service->capacity ) ? $service->capacity : 0;
				$price = $service->price;
				$deposit = isset( $service->deposit ) ? $service->deposit : '';
				$padding = isset( $service->padding ) && $service->padding ? $service->padding : 0;
				$break_time = isset( $service->break_time ) && $service->break_time ? $service->break_time : 0;
				
				if ( isset( $service->daily ) )				
					$daily_checked = checked( $service->daily, 1, false );
				else
					$daily_checked = '';
				
				$capacity_by_worker = $this->a->get_capacity( $service->ID );
				
				$class = 'class="app_service_name"';
			 }
			 else return;
		}
		else {
			$n = "'+n+'";
			$sign = '-'; // Negative sign is added for insert to prevent race condition
			$internal=0;
			$name = '';
			$capacity = '0';
			$price = '';
			$deposit = '';
			$daily_checked = '';
			$capacity_by_worker = 'N/A';
			$class = '';
			$padding = $break_time = 0;
		}
		
		$min_time = $this->a->get_min_time();
		
		$this->row_number++;
		$cl_even = $this->row_number % 2 ? "app-even" : "";
		
		$html = '';
		$html .= '<tbody><tr class="app_service_tr '.$cl_even.'"><td>';
		$html .= '<span class="app-service-id">' . $n .'</span>';
		$html  = apply_filters( 'app_admin_services_after_id', $html, $n, $service );
		if ( $_php )
			$html  = apply_filters( 'app_admin_services_add_more', $html, $n, $service );	
		$html .= '</td><td>';
		
		$html .= '<input style="width:100%" type="text" '.$class.' name="services['.$sign.$n.'][name]" value="'.esc_attr(stripslashes( $name )).'" title="'.stripslashes( $name ).'" placeholder="'.esc_js(__('You MUST enter a name!','wp-base') ) .'" />';
		$html = apply_filters( 'app_admin_add_field', $html, $n, $service, 'services', 'service_name' );
		
		/* Internal */
		if ( BASE('ESF') ) {
			$html .= '</td><td class="app_internal">';
			$html .= '<input type="checkbox" name="services['.$sign.$n.'][internal]" '.checked($internal,1,false).' value="1" />';
		}
		
		$html .= '</td><td>';

		$html  = apply_filters( 'app_admin_services_after_internal', $html, $_php, $n, $sign, $service );

		/* Capacity */
		if ( !$this->a->is_package( $n ) ) {
			$html .= '<input style="width:50%" type="text" name="services['.$sign.$n.'][capacity]" value="'.$capacity.'" />';
			if ( $_php ) {
				if ( $capacity_by_worker )
					$html .= '<span class="app_net"> ['. $capacity_by_worker .']</span>';
				else
					$html .= '<abbr class="app-error" title="'.__('Warning: Check service providers assigned to this service','wp-base').'" style="font-size:10px;"> ['. $capacity_by_worker .']</abbr>';
			}
		}
		
		$html .= '</td><td>';
		
		$k_max = apply_filters( 'app_selectable_durations', (int)(1440/$min_time) );
		
		$class = ( $_php && is_object( $service ) && $service->duration % $min_time != 0 ) ? 'class="error"': '';
		
		$html .= '<select '.$class.' name="services['.$sign.$n.'][duration]" >';
		
		for ( $k=1; $k<=$k_max; $k++ ) {
			
			$text = ($k * $min_time) == 1440 ? __( 'All day', 'wp-base' ) : wpb_readable_duration($k * $min_time);
			
			if ( $_php && is_object( $service ) && $k * $min_time == $service->duration )
				$html .= '<option value="'.($k * $min_time).'" selected="selected">'. $text . '</option>';
			else
				$html .= '<option value="'.($k * $min_time).'">'. $text . '</option>';
		}
		$html .= '</select>';
		$html .= '</td><td>';
		
		if ( BASE( 'ESF' ) ) {
			if ( !$this->a->is_package( $n ) && !$this->a->is_daily( $n ) ) {
				
				/* Padding (padding before) */
				$class = ( $_php && is_object( $service ) && $padding % $min_time != 0 ) ? 'class="error"': '';
				$html .= '<select '.$class.' name="services['.$sign.$n.'][padding]" >';
				for ( $k=0; $k<=$k_max; $k++ ) {
					$selected = $_php && is_object( $service ) && $k * $min_time == $padding ? 'selected="selected"' : '';
					$html .= '<option value="'.($k * $min_time).'" '.$selected.'>'. wpb_readable_duration($k * $min_time) . '</option>';
				}
				$html .= '</select>';
				$html .= '</td><td>';

				/* Break time (padding after ) */
				$class = ( $_php && is_object( $service ) && $service->break_time % $min_time != 0 ) ? 'class="error"': '';
				$html .= '<select '.$class.' name="services['.$sign.$n.'][break_time]" >';
				for ( $k=0; $k<=$k_max; $k++ ) {
					$selected = $_php && is_object( $service ) && $k * $min_time == $break_time ? 'selected="selected"' : '';
					$html .= '<option value="'.($k * $min_time).'" '.$selected.'>'. wpb_readable_duration($k * $min_time) . '</option>';

				}
				$html .= '</select>';
				$html .= '</td><td>';
			}
			else
				$html .= '</td><td></td><td>';
		}
		
		/* Price */
		$html .= '<input class="app_service_price" style="width:100%" type="text" name="services['.$sign.$n.'][price]" value="'.$price.'" />';
		
		if ( BASE('ESF') ) {
			/* Deposit */
			$html .= '</td><td>';
			$html .= '<input class="app_service_deposit" style="width:100%" type="text" name="services['.$sign.$n.'][deposit]" value="'.$deposit.'" />';
		}
		
		/* Description page */
		if ( wpb_setting("description_post_type") )
			$post_type= wpb_setting("description_post_type");
		else
			$post_type= 'page';
			
		if ( 'page' == $post_type )
			$pages = get_pages( apply_filters('app_pages_filter',array('post_type'=>$post_type, 'post_status'=>'publish,private') ) );
		else
			$pages = get_posts( apply_filters('app_pages_filter',array('post_type'=>$post_type, 'post_status'=>'publish,private','numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC') ) );
			
		$html .= '</td><td class="app_service_page">';
		$html .= '<select name="services['.$sign.$n.'][page]" style="width:95%">';
		$html .= '<option value="0">'. __('None','wp-base') .'</option>';
		if ( is_array( $pages ) ) {
			foreach( $pages as $page ) {
				$title = $_php ? esc_attr( $page->post_title ) : esc_js( $page->post_title );
					
				$s = $_php && is_object( $service ) && $service->page == $page->ID ? ' selected="selected"' : '';
				$html .= '<option value="'.$page->ID.'"'.$s.'>'. $title . '</option>';
			}
		}
		$html .= '</select>';
		$html .= '</td></tr></tbody>';
		
		/* Add More tabs */
		if ( $_php ) {
			$tabs = apply_filters( 'app_admin_services_add_more_tab', array() );
			if ( !empty( $tabs ) ) {
				$html .= '<tbody class="service-tabs-container" style="display:none">';
				if ( count($tabs) > 1 ) {
					$html .= "<tr class='app_service_tab_head' data-service-id='service-{$n}' >";
					$html .= '<th colspan="2" rowspan="2">';
					$html .= '<ul>';
					foreach ( $tabs as $tab=>$name ) {
						$html .= "<li><a href='#{$tab}_{$n}'><span class='tab-title'>{$name}</span></a></li>";	// $tab is class_name and $n is service ID
					}
					$html .= '</ul>';				
					$html .= '</th><td colspan="'.($this->nof_columns()-2).'"></td></tr>';
				}
			}
	 
			$html = apply_filters( 'app_admin_services_after_tr', $html, $sign.$n, $service );
			
			if ( !empty( $tabs ) )
				$html .= '</tbody >';
		}
		
		return apply_filters( 'app_services_add_service', $html, $sign.$n, $service );
	}
	
	/**
     * Add more/less to th
	 * @since 2.0
     */
	public function add_more_th( ) {
		$app_services = BASE('AdminServices');
		if ( is_admin() )	
			add_action( 'admin_footer', array( $this, 'footer' ) );
		else
			add_action( 'wp_footer', array( $this, 'footer' ) );
		
		// If we are calling this action, we would want to add more/less control under ID too
		if ( !has_action( 'app_admin_services_add_more' ) )
			add_filter( 'app_admin_services_add_more', array( $this, 'add_more' ), 10, 3 );	// Add more link to service ID th
	}

	/**
     * Add more/less link to services tab, under ID td
	 * @since 2.0
     */
	function add_more( $html, $n, $service ) {

		$html .= '<br /><small><span class="dashicons dashicons-arrow-down app_lts_more"></span><a title="'.__("Click to display/hide more service settings added by Addons",'wp-base').'" href="javascript:void(0)" class="app_lts_more">'. __("More",'wp-base') . '</a></small>';
	
		return $html;
	}

	/**
     * Add more/less control scripts
	 * @since 2.0
     */
	function footer( ) {
		if ( !empty( $this->script_added ) )
			return;
		?>
		<script type='text/javascript'>

		jQuery(document).ready(function ($) {
			
			$('.service-tabs-container').tabs().removeClass("ui-widget");
			
			var opened =false;
			$(document).on("click", "table .app_lts_more_all", function(){
				var tr = $("#services-table tbody").find("tr").not(".app_service_tr");
				var dash_all = $(this).parents("table").find(".dashicons");
				if ( !opened ) {
					tr.show();
					opened = true;
					$("a.app_lts_more_all, a.app_lts_more").text("<?php echo esc_js( __("Less",'wp-base'))?>");
					dash_all.removeClass("dashicons-arrow-down").addClass("dashicons-arrow-up");
					$(document).trigger("service-settings-opened");
				}
				else {
					tr.hide();
					opened = false;
					$("a.app_lts_more_all, a.app_lts_more").text("<?php echo esc_js( __("More",'wp-base'))?>");
					dash_all.removeClass("dashicons-arrow-up").addClass("dashicons-arrow-down");
					$(document).trigger("service-settings-closed");
				}
			});
			
			$(document).on("click", "table .app_lts_more", function(){
				var tbody = $(this).parents("tbody").next("tbody.service-tabs-container");
				var dash = $(this).parents("tr.app_service_tr").find(".dashicons");
				tbody.toggle();
				if ( tbody.css('display') != 'none' ) {
					$(this).parent().find("a.app_lts_more").text("<?php echo esc_js( __("Less",'wp-base'))?>");
					dash.removeClass("dashicons-arrow-down").addClass("dashicons-arrow-up");
					$(document).trigger("service-settings-opened");
				}
				else {
					$(this).parent().find("a.app_lts_more").text("<?php echo esc_js( __("More",'wp-base'))?>");
					dash.removeClass("dashicons-arrow-up").addClass("dashicons-arrow-down");
					$(document).trigger("service-settings-closed");
				}

			});
		});
		</script>
		<?php
		$this->script_added = true;
	}	
	
	/**
	 *	Admin save service settings
	 */
	function save_settings() {
		
		if ( isset( $_POST['app_nonce'] ) && !wp_verify_nonce($_POST['app_nonce'],'update_app_settings') ) {
			wpb_notice( 'unauthorised', 'error' );
			return;
		}
		
		if ( !('save_services' == $_POST["action_app"] && is_array( $_POST["services"] )) )
			return;
		
		if ( !wpb_admin_access_check( 'manage_services', false ) && !wpb_admin_access_check( 'manage_own_services', false ) ) {
			wpb_notice( 'unauthorised', 'error' );
			return;
		}
		
		$max_vars = ini_get( "max_input_vars" );
		
		if ( count( $_POST, COUNT_RECURSIVE ) >= $max_vars ) {
			wpb_notice( sprintf( __( 'Warning! Input variables exceeded %1$d. Some part of your setting might not be saved. Either decrease "%2$s" setting or increase "Maximum input variables" PHP setting.', 'wp-base' ), $max_vars, WpBConstant::get_setting_name('records_per_page_business') ), 'error' );
		}
		
		global $current_user;
		
		$result = $updated = $inserted = false;

		# To arrange sort among pages, add 100 per page
		$page_add = empty( $_GET['paged'] ) ? 0 : ($_GET['paged']-1) * 100;
		$max_sort = $this->a->db->get_var( "SELECT MAX(sort_order) FROM " . $this->a->services_table );
		$by_worker = !empty($_POST['by_worker']) ? true : false;

		if ( $by_worker ) {
			
			$max_sort = $max_sort ? $max_sort+1 : 1;
			$max_sort_not_us = $this->a->db->get_var( "SELECT MAX(sort_order) FROM " . $this->a->services_table." WHERE ID NOT IN ( SELECT object_id FROM ".$this->a->meta_table." WHERE meta_type='service' AND meta_key='created_by' AND meta_value=".get_current_user_id()." ) " );
			$max_sort_not_us = $max_sort_not_us ? $max_sort_not_us : 0;
		}
		
		$i = 0;	# For sort_order
		foreach ( $_POST["services"] as $ID=>$service ) {
			$ID = (int)$ID;
			$r = false;
			
			if ( '' != trim( $service["name"] ) ) {
				$pro_arr = BASE('ESF') ? array( 'padding'	=> isset($service["padding"]) ? preg_replace("/[^0-9]/", "", $service["padding"]) : '',
												'break_time'=> isset($service["break_time"]) ? preg_replace("/[^0-9]/", "", $service["break_time"]) :'',
												'internal'	=> isset($service["internal"]) ? 1 : 0,
												'deposit'	=> isset($service["deposit"]) ? preg_replace("/[^0-9\.]/", "", $service["deposit"]) : '', 
												'categories'=> isset($service['categories']) && is_array($service['categories']) ? wpb_implode($service['categories']) : '',
											)
											: 
											array();
							
				if ( BASE('Locations') ) {
					$locs = !empty($service['locations']) && is_array($service['locations']) ? wpb_implode($service['locations']) : '';				
					$pro_arr = array_merge( $pro_arr, array('locations'=>$locs ) ); 
				}
			
				# Update or insert?
				$existing_service = $ID > 0 ? $this->a->db->get_row( "SELECT * FROM " . $this->a->services_table . " WHERE ID=".$ID." " ) : false;
				
				# Fix sort_order since only partial services will be sent
				if ( $existing_service ) {
					if ( $by_worker ) {
						if ( $existing_service->sort_order < $max_sort_not_us )
							$new_sort = $existing_service->sort_order;
						else
							$new_sort = ($max_sort_not_us +1 +$i + $page_add);
					}
					else
						$new_sort = $i + $page_add;
					
					$r = $this->a->db->update( $this->a->services_table, 
								array_merge( $pro_arr, array( 
									'sort_order'=> $new_sort,
									'name'		=> $service["name"],
									'capacity'	=> isset($service["capacity"]) ? preg_replace("/[^0-9]/", "", $service["capacity"]) :'',									
									'duration'	=> $service["duration"],
									'price'		=> wpb_sanitize_price( $service["price"] ), 
									'page'		=> $service["page"],
									) ),
								array( 'ID'		=> $ID )
							);
					if ( $r ) {
						$result = true;
						do_action( 'app_service_updated', $ID );
					}
					else {
						do_action( 'app_service_update_attempt', $ID );
					}
					do_action( 'app_service_maybe_updated', $ID );
				}
				else {
					$r = $this->a->db->insert( $this->a->services_table, 
								array_merge( $pro_arr, array( 
									'sort_order'=> ($i + $max_sort),
									'name'		=> $service["name"], 
									'capacity'	=> isset($service["capacity"]) ? preg_replace("/[^0-9]/", "", $service["capacity"]) :'',
									'duration'	=> $service["duration"],
									'price'		=> wpb_sanitize_price( $service["price"] ), 
									'page'		=> $service["page"],
									'categories'=> isset($service['categories']) && is_array($service['categories']) ? wpb_implode($service['categories']) : '',
									) )
								);
					if ( $r ) {
						$result = true;
						$ID = $this->a->db->insert_id;	
						wpb_add_service_meta( $ID, 'created_by', get_current_user_id() );
						wpb_add_service_meta( $ID, 'created_at', date( 'Y-m-d H:i:s', $this->a->_time ) );
						do_action( 'app_new_service_added', $ID );
					}
				}
				
				# Assign worker to the service
				if ( $r && $by_worker && $this->a->is_worker( $current_user->ID ) && 'yes'== wpb_setting('allow_worker_create_service') ) {
					$sp = $this->a->db->get_var( $this->a->db->prepare("SELECT services_provided FROM " . $this->a->workers_table . " WHERE ID=%d", $current_user->ID) );
					$sp_arr = $sp ? wpb_explode( $sp ) : array();
					$sp_arr[] = $ID;
					$this->a->db->update( $this->a->workers_table, 
								array( 'services_provided'	=> wpb_implode( $sp_arr ) ),
								array( 'ID' 				=> $current_user->ID )
								);
				}
			}
			else {
				# Entering an empty name means attempt to delete a service
				$r = false;
				$can_delete = wpb_setting('allow_worker_delete_service'); # yes, no, if_empty
				$is_owner = !empty($_POST['by_worker']) && ( $_POST['by_worker'] == wpb_get_service_meta( $ID, 'created_by' ) );
				
				if ( $has_auth = wpb_admin_access_check( 'delete_services', false ) ) {
				
					if ( empty($_POST['by_worker']) || ( 'yes'== $can_delete && $is_owner ) ||
						('if_empty'== $can_delete && $is_owner && !$this->a->db->get_var( $this->a->db->prepare("SELECT COUNT(ID) FROM " .$this->a->app_table. " WHERE service=%d AND status<>'test'",$ID) ) ) ){
						
						do_action( 'app_service_delete', $ID );
						
						$r = $this->a->db->query( $this->a->db->prepare("DELETE FROM " .$this->a->services_table. " WHERE ID=%d LIMIT 1", $ID) );
						if ( $r ) {
							$this->a->db->query( $this->a->db->prepare("DELETE FROM " .$this->a->meta_table. " WHERE object_id=%d AND meta_type='service'", $ID) );
							do_action( 'app_service_deleted', $ID );
						}
					}
				}
				
				if ( $r ) {
					# Update related bookings
					do_action( 'app_service_deleted', $ID );
					$this->a->adjust_auto_increment( $this->a->services_table );
					$r1 = $this->a->db->query( $this->a->db->prepare("UPDATE ". $this->a->app_table . " SET service=0, status='removed' WHERE ID=%d", $ID) );
				
					# Remove deleted service also from workers table 
					$r2 = $this->a->db->query( "UPDATE ". $this->a->workers_table . " SET services_provided = REPLACE(services_provided,':".esc_sql($ID)."','') ");
					
					if ( $r || $r2 || $r3 )
						$result = true;
				}
				else {
					if ( !$has_auth )
						$m = 'unauthorised';
					else if ( !$can_delete || 'no' == $can_delete )
						$m = __('Deletion is turned off by admin.','wp-base');
					else
						$m = __('Deletion failed. There may be active bookings for this service.','wp-base');
					
					wpb_notice( $m, 'error' );
				}
			}
			$i++;
		}
		
		if( $result ) {
			wpb_notice( 'saved' );

			update_user_meta( $current_user->ID, 'app_service_check_needed',  true );
			
			# Calculate min time
			wpb_flush_cache();
			$options = wpb_setting();
			$options['calc_min_time'] = $this->a->find_optimum_time_base();
			$this->a->update_options( $options );
		}
	}

}
	
	BASE('AdminServices')->add_hooks();
}