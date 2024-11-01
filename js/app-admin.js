
jQuery(document).ready(function($){
	var sel_location = (typeof sel_location === 'undefined') ? '0' : sel_location;
	var only_own = (typeof only_own === 'undefined') ? '0' : only_own;
	var override = (typeof override === 'undefined') ? 'inherit' : override;
	if( typeof _app_.user_fields=="undefined"){_app_.user_fields={};}
	
	$("table").find("th#price, th#deposit, th#total_paid, th#balance").each(function() {
		var id = $(this).attr("id");
		var ttext = $("#"+id+"-tt").html();
		$(this).qtip({
			content: {
				text: ttext
			},
			style:qtip_n_style,hide:qtip_hide,position:qtip_pos
		});
	});				


	$("#delete_removed").click( function() {
		if ( !confirm(_app_.delete_confirm) ) 
		{return false;}
		else {
			return true;
		}
	});
	
	/* Bulk status change select all rows */
	var th_sel = $("th.column-delete input:checkbox");
	var td_sel = $("td.column-delete input:checkbox");
	th_sel.change( function() {
		if ( $(this).is(':checked') ) {
			td_sel.attr("checked","checked");
			th_sel.not(this).attr("checked","checked");
		}
		else{
			td_sel.removeAttr('checked');
			th_sel.not(this).removeAttr('checked');
		}
	});
	var col_len = $("table").find("tr:first th").length;
			
	_app_.populate_user = function(me){
		var sel_user = me.val();
		var par = me.parents(".inline-edit-col");
		if ( sel_user == 0 ) {
			// Clear fields for unregistered user
			$.each(_app_.user_fields, function( i, v ) {
				par.find(".app_iedit_"+v+" input").val("");
			});						
			return false;
		}
		_app_.updating('reading');
		var data = {
			action: 'app_populate_user', 
			user_id:sel_user, 
			_ajax_nonce: _app_.iedit_nonce
		};
		$.post(ajaxurl, data, function(response) {
			if ( response && response.error ){
				alert(response.error);
			}
			else if (response) {
				$.each(response, function( i, v ) {
					par.find(".app_iedit_"+i+" input").val(v);
				});						
			}
			else {alert(_app_.con_error);}
		},'json');
	}
	
	_app_.configure_multiselect = function(id){
		$('#app_users_'+id).multiselect({multiple:false,selectedList:1,minWidth:'80%',classes:'app_users',close:function(){var me=$(this);_app_.populate_user(me);}}).multiselectfilter();
		$('#app_extras_'+id).multiselect({selectedList:3,minWidth:'80%',classes:'app_extras',close:function(){}}).multiselectfilter();
	}
	
	_app_.collapse_record = function(el){
		var row = el.parents(".inline-edit-row");
		row.fadeOut(700, function(){
			row.remove();
		});
		row.prev(".app-tr").show("slow");		
	}

	/* Cancel */
	$("table.app-manage").on("click", ".cancel", function(){
		_app_.collapse_record($(this));
	});
	
	/* Add new */
	$(".add-new-h2").on('click', function(){
		$(".add-new-waiting").show();
		_app_.updating('reading');
		var data = {
			wpb_ajax:true,
			action: 'inline_edit',
			add_new:$_GET['add_new'],
			cpy_from:$_GET['cpy_from'],
			app_worker:$_GET['app_worker'],
			app_timestamp:$_GET['app_timestamp'],
			col_len: col_len, 
			app_id:0, 
			_ajax_nonce: _app_.iedit_nonce, 
			sel_location:sel_location,
			only_own:only_own,
			override:override
		};
		$.post(ajaxurl, data, function(response) {
			$(".add-new-waiting").hide();
			if ( response && response.error ){
				alert(response.error);
			}
			else if (response) {
				$("table.widefat").prepend(response.result);
				_app_.configure_multiselect(response.id);
				try {
					eval(response.js_tooltip); 
				} catch (e) {
					if (e instanceof SyntaxError) {
						console.log(e.message);
					}
				}
				if ( typeof app_fem_fix_overflow == 'function' ){ app_fem_fix_overflow();}
			}
			else {alert(_app_.con_error);}
		},'json');
	});
	
	// Edit
	$("table").on("click", ".app-inline-edit", function(){
		var app_parent = $(this).parents(".app-tr");
		_app_.updating('reading');
		app_parent.find(".waiting").css("visibility", "visible");
		var app_id = app_parent.find(".span_app_ID").html();
		var data = {
			wpb_ajax:true,
			action: 'inline_edit', 
			col_len: col_len, 
			app_id: app_id, 
			_ajax_nonce: _app_.iedit_nonce, 
			sel_location:sel_location,
			only_own:only_own,
			override:override
		};
		$.post(ajaxurl, data, function(response) {
			app_parent.find(".waiting").css("visibility", "hidden");
			if ( response && response.error ){
				alert(response.error);
			}
			else if (response) {
				app_parent.hide();
				var iedit_row = response.result;
				var inserted_row = $(iedit_row).insertAfter(app_parent);
				inserted_row.find(".inline-edit-col .blocked-days").val(response.blocked_days);
				var dpicker_id = inserted_row.find(".datepicker").attr("id");
				$("#"+dpicker_id).datepicker("refresh");
				if ( parseInt(response.locked) > 0 ) {
					inserted_row.data("locked", true );
				}
				try {
					eval(response.js_tooltip); 
				} catch (e) {
					if (e instanceof SyntaxError) {
						console.log(e.message);
					}
				}
				_app_.configure_multiselect(response.id);
				if ( typeof app_fem_fix_overflow == 'function' ){ app_fem_fix_overflow();}
			}
			else {alert(_app_.con_error);}
		},'json');
	});
	
	/* Redraw multiselect */
	$(window).resize(function () {
		$.each($(".app_users,.app_extras"), function(i,v){
			if ( $(this).data().hasOwnProperty( "echMultiselect" ) ) {
				$(this).multiselect("refresh");
			}
		});
	});
	
	// Change balance and total due as price or deposit changes
	function recalculate(obj){
		var par = obj.parents("tr.inline-edit-row");
		var deposit = par.find('input[name="deposit"]').val();
		if (typeof deposit == "undefined" || $.trim(deposit)==''){
			deposit = 0;
		}
		var price = par.find('input[name="price"]').val();
		if (typeof price == "undefined" || $.trim(price)==''){
			price = 0;
		}
		var payment = par.find('input[name="payment"]').val();
		var balance = parseFloat(payment) - parseFloat(price) - parseFloat(deposit);
		var total_due = parseFloat(price) + parseFloat(deposit);
		par.find('input[name="balance"]').val(_app_.number_format(balance));
		par.find('input[name="total_due"]').val(_app_.number_format(total_due));
	}
	$("table").on("keyup", 'input[name="price"],input[name="deposit"]', function(){recalculate($(this));} );

	// Renew working hours as location, service, provider, start date changes 
	function update_inline_edit(obj){
		var par = obj.parents("tr.inline-edit-row"); 
		if ( obj.hasClass('app_seats') && !obj.val() ) {return false;}
		if ( obj.hasClass('app_extras') &&  par.data("locked") ) {return false;}
		
		if ( $.inArray( parseInt( obj.val()), _app_.daily_services ) == -1 ) {
			$('.app-is-daily').text('');
			par.find('select[name="start_time"], input[name="end_date"], select[name="end_time"]').css('text-decoration','none').attr('disabled',false);
		}
		else {
			// Daily
			$('.app-is-daily').text(_app_.daily_text);
			par.find('select[name="start_time"], input[name="end_date"], select[name="end_time"]').css('text-decoration','line-through').attr('disabled',true);
		}

		var app_id =  par.find('input[name="app_id"]').val();
		var location = par.find('select[name="location"] option:selected').val();
		var locations_sel = par.find('select[name="location"]');
		var service =  par.find('select[name="service"] option:selected').val();
		var services_sel = par.find('select[name="service"]');
		var worker =  par.find('select[name="worker"] option:selected').val();
		var workers_sel = par.find('select[name="worker"]');
		var start_date =  par.find('input[name="start_date"]').val();
		var start_time = par.find('select[name="start_time"] option:selected').val();
		var start_time_sel = par.find('select[name="start_time"]');
		var end_time_sel = par.find('select[name="end_time"]');
		var price_sel = par.find('input[name="price"]');
		var deposit_sel = par.find('input[name="deposit"]');
		locations_sel.attr("disabled","disabled");
		services_sel.attr("disabled","disabled");
		workers_sel.attr("disabled","disabled");
		_app_.updating();
		var post_data = {
			wpb_ajax:true,
			action: 'update_inline_edit',
			app_id:app_id,
			updated:obj.data("lsw"),
			location:location, 
			service:service, 
			worker:worker, 
			locked:par.find('input[name="locked"]').is(':checked') ? 1:0,
			locked_check:par.find('input[name="locked_check"]').val(),
			start_date:start_date, 
			start_time:start_time, 
			_ajax_nonce: _app_.iedit_nonce, 
			sel_location:sel_location,
			only_own:only_own,
			override:override,
			app_seats:par.find('input[name="app_seats"]').val(),
			app_extras:par.find(".app_extras option:selected") ? par.find(".app_extras option:selected").map(function(){return this.value}).get().join(",") : false,
			app_extras_check:par.find('input[name="app_extras_check"]').val(),
			app_multilang:par.find(".app_multilang option:selected").val(),
			app_multilang_check:par.find('input[name="app_multilang_check"]').val()
		};
		$.post(ajaxurl, post_data, function(response) {
			
			locations_sel.attr("disabled",false);
			services_sel.attr("disabled",false);
			workers_sel.attr("disabled",false);
			
			if ( response && response.error ){
				alert(response.error);
			}
			else if (response) {
				if (response.start_time_sel) {
					start_time_sel.replaceWith(response.start_time_sel);
				}
				if (response.end_time_sel) {
					end_time_sel.replaceWith(response.end_time_sel);
				}
				if (response.locations_sel) {
					locations_sel.replaceWith(response.locations_sel);
				}
				if (response.services_sel) {
					services_sel.replaceWith(response.services_sel);
				}
				if (response.workers_sel) {
					workers_sel.replaceWith(response.workers_sel);
				}
				if (response.price) {
					price_sel.val(_app_.number_format(response.price));
				}
				if (response.deposit) {
					deposit_sel.val(_app_.number_format(response.dposit));
				}
				par.find(".blocked-days").val(response.blocked_days);
				var dpicker_id = par.find(".datepicker").attr("id");
				$("#"+dpicker_id).datepicker("refresh");

				recalculate(price_sel);						
			}
			else {alert(_app_.con_error);}
		},'json');
		
	}
	$("table").on("change", 'select.app_extras, select[name="location"], select[name="service"], select[name="worker"], select[name="start_time"]', function(){update_inline_edit($(this));} );
	$("table").on("keyup", 'input[name="app_seats"]', function(){update_inline_edit($(this));} );

	$("table").on("focus", ".datepicker", function(e){
		if( $(e.target).data('focused')!='yes' ) {
			$(".datepicker").datepicker({dateFormat: _app_.js_date_format, firstDay:_app_.start_of_week, 
				onSelect:function(dateText){ 
					 if ( $(this).attr("name")=="start_date" ) {
						$(this).parents(".inline-edit-col").find("input[name=end_date]").datepicker("setDate", dateText );
						update_inline_edit($(this));
					 }
				},
				beforeShowDay: function(date){
					var string = $.datepicker.formatDate("yy-mm-dd", date);
					var datelist = $(this).parents(".inline-edit-col").find(".blocked-days").val();
					if (datelist) {
						return [$.inArray(string, JSON.parse(datelist))==-1];
					}
					else {
						return [true];
					}
				}				
			  });
		}
		 $(e.target).data('focused','yes');
	});

	/* Save */
	$("table").on("click", ".save", function(){
		var save_button = $(this);
		save_button.attr("disabled", true);
		var save_parent = save_button.parents(".inline-edit-row");
		_app_.updating('saving');
		save_parent.find(".waiting").show();
		var user = save_parent.find('select[name="user"] option:selected').val();
		var name = save_parent.find('input[name="cname"]').val();
		var app_user_data = {};
		$.each(_app_.user_fields, function( i, v ) {
			app_user_data[v] = save_parent.find('input[name="'+v+'"]').val();
		});
		var location = save_parent.find('select[name="location"] option:selected').val();
		if (typeof location == "undefined"){
			location = 0;
		}
		var service = save_parent.find('select[name="service"] option:selected').val();
		var worker = save_parent.find('select[name="worker"] option:selected').val();
		if (typeof worker == "undefined"){
			worker = 0;
		}
		var price = save_parent.find('input[name="price"]').val();
		var deposit = save_parent.find('input[name="deposit"]').val();
		if (typeof deposit == "undefined"){
			deposit = 0;
		}					
		var start_date = save_parent.find('input[name="start_date"]').val();
		var start_time = save_parent.find('select[name="start_time"] option:selected').val();
		var end_date = save_parent.find('input[name="end_date"]').val();
		var end_time = save_parent.find('select[name="end_time"] option:selected').val();
		var parent_id = save_parent.find('input[name="parent_id"]').val();
		var note = save_parent.find('textarea[name="note"]').val();
		var status = save_parent.find('select[name="status"] option:selected').val();
		var resend = 0;
		if (save_parent.find('input[name="resend"]').is(':checked') ) {resend=1;}
		var send_pending = 0;
		if (save_parent.find('input[name="send_pending"]').is(':checked') ) {send_pending=1;}
		var send_completed = 0;
		if (save_parent.find('input[name="send_completed"]').is(':checked') ) {send_completed=1;}
		var send_cancel = 0;
		if (save_parent.find('input[name="send_cancel"]').is(':checked') ) {send_cancel=1;}						
		var app_id = save_parent.find('input[name="app_id"]').val();
		var post_data = {
			wpb_ajax:true,
			action: 'inline_edit_save', 
			user:user, name:name, 
			app_user_data:JSON.stringify(app_user_data),
			location:location, service:service, worker:worker,
			locked:save_parent.find('input[name="locked"]').is(':checked')?1:0,
			locked_check:save_parent.find('input[name="locked_check"]').val(),
			price:price, deposit:deposit, 
			start_date:start_date, start_time:start_time, end_date:end_date, end_time:end_time, 
			parent_id:parent_id, 
			note:note,
			admin_note:save_parent.find('textarea[name="admin_note"]').val(),			
			status:status, 
			resend:resend, send_pending:send_pending, 
			send_cancel:send_cancel, send_completed:send_completed, 
			app_id: app_id, _ajax_nonce: _app_.iedit_nonce,
			sel_location:sel_location,
			only_own:only_own,
			override:override,
			app_seats:save_parent.find(".app_seats").val(),					
			app_extras:save_parent.find(".app_extras option:selected").map(function(){return this.value}).get().join(","),
			app_extras_check:save_parent.find('input[name="app_extras_check"]').val()
		};
		
		var parti = {};
		$.each( ["nop","mop","pop","aop"], function(i,v){
			for (k = 1; k <= 30; k++) { 
				parti[v+"_"+k] = save_parent.find(".app-"+v+"-field-entry-"+k).val();
			}
		});
		post_data = $.extend(post_data,{parti_data:JSON.stringify(parti)});
		
		var udfs = {};
		$.each( _app_.udf_ids, function(i,v){
			var field = save_parent.find(".app-udf-field-entry-"+v);
			if ( parseInt(field.length) > 0 ) {
				if ( field.hasClass("app_checkbox") ) {
					udfs["udf_"+v] = field.is(':checked') ? 1 : 0;
				}
				else {
					udfs["udf_"+v] = field.val();
				}
			}
		});
		post_data = $.extend(post_data,{udf_data:JSON.stringify(udfs)});
		
		$.post(ajaxurl, post_data, function(response) {
			save_button.attr("disabled", false);
			save_parent.find(".waiting").hide();
			if (typeof app_input_changed !== "undefined"){
				app_input_changed=false;
			}
			if ( response ) {
				if ( response.error ){
					save_parent.find(".error").html(response.error).show().delay(10000).fadeOut('slow');
				}
				else if (response.result) {
					save_parent.find(".error").html(response.result).show().delay(10000).fadeOut('slow');
					var prev_row = save_parent.prev(".app-tr");
					if (response.result_app_id) {
						var nid = response.result_app_id;
						save_parent.find('input[name="app_id"]').val(nid);
						if (nid != app_id){
							save_parent.find(".app_iedit_app_h").append(" (ID:"+nid+")");
						}
						if (nid){
							save_parent.find("a.save").text(_app_.update_text);
						}
					}
					
					if (response.user) {prev_row.find(".user-inner .app-client-name").html(response.user);}
					$.each( response, function(i,v){
						if ( prev_row.find(".column-"+i).length >0 ) {
							if ( i == "price" || i == "deposit" || i == "total_paid" || i == "balance" ) {
								prev_row.find(".column-"+i).text(function () {
									return $(this).text().replace(/[0-9\.\,\-]+/, v); 
								});
							}
							else {
								prev_row.find(".column-"+i).text(v);
							}
						}
					});
					
					prev_row.addClass("ui-state-highlight");
					setTimeout(function(){
						prev_row.removeClass("ui-state-highlight");
					}, 10000);

					if (parseInt(response.collapse)>0){
						setTimeout(function(){
							_app_.collapse_record(save_button);
						},500);
					}
				}
			}
			else {alert(_app_.con_error);}
		},'json');
	});

	/* Lock price and deposit fields */
	$("table").on("change", 'input[name="locked"]', function(){
		var par = $(this).parents("tr.inline-edit-row");
		var price = par.find('input[name="price"]');
		var deposit = par.find('input[name="deposit"]');
		if ( $(this).is(':checked') ) {
			price.attr("readonly","readonly");
			deposit.attr("readonly","readonly");
			par.data("locked", true );
		}
		else {
			price.attr("readonly",false);
			deposit.attr("readonly",false);
			par.data("locked", false );
		}
	});
	
	/* Allow email check boxes based on status */
	$("table").on("change", 'select[name="status"]', function(){
		var pars = $(this).parents("tr.inline-edit-row");
		if ( $(this).val() == 'paid' || $(this).val() == 'confirmed' ) {
			pars.find('input[name="resend"]').attr('disabled',false);
			pars.find('input[name="send_pending"]').attr('checked',false).attr('disabled',true);
			pars.find('input[name="send_completed"]').attr('checked',false).attr('disabled',true);
			pars.find('input[name="send_cancel"]').attr('checked',false).attr('disabled',true);
		}
		else if ( $(this).val() == 'pending' ) {
			pars.find('input[name="resend"]').attr('checked',false).attr('disabled',true);
			pars.find('input[name="send_pending"]').attr('disabled',false);
			pars.find('input[name="send_completed"]').attr('checked',false).attr('disabled',true);
			pars.find('input[name="send_cancel"]').attr('checked',false).attr('disabled',true);
		}
		else if ( $(this).val() == 'removed' ) {
			pars.find('input[name="resend"]').attr('checked',false).attr('disabled',true);
			pars.find('input[name="send_pending"]').attr('checked',false).attr('disabled',true);
			pars.find('input[name="send_completed"]').attr('checked',false).attr('disabled',true);
			pars.find('input[name="send_cancel"]').attr('disabled',false);
		}
		else if ( $(this).val() == 'completed' ) {
			pars.find('input[name="resend"]').attr('checked',false).attr('disabled',true);
			pars.find('input[name="send_pending"]').attr('checked',false).attr('disabled',true);
			pars.find('input[name="send_completed"]').attr('disabled',false);
			pars.find('input[name="send_cancel"]').attr('checked',false).attr('disabled',true);
		}
		else {
			pars.find('input[name="resend"]').attr('disabled',false);
			pars.find('input[name="send_pending"]').attr('disabled',false);
			pars.find('input[name="send_completed"]').attr('disabled',false);
			pars.find('input[name="send_cancel"]').attr('disabled',false);
		}
	});
	
	/* Do not allow checking of more than one checkbox */
	$("table").on("change", '.app_iedit_email_actions input:checkbox', function(){
		var this_el = $(this);
		if ( this_el.is(':checked') ) {
			this_el.parents(".inline-edit-row").find('input').not(this_el).attr('checked',false);
		}
	});
	
	/* Sortable control */
	if (typeof (jQuery.ui.sortable) != 'undefined') {
		// http://stackoverflow.com/questions/1307705/jquery-ui-sortable-with-table-and-tr-width
		var fixHelper = function(e, $this)  {
			var tr = $this.find("td").parent("tr");
			var $originals = tr.children();
			var $helper = tr.clone();
			$helper.children().each(function(index)	{
				$(this).width($originals.eq(index).width());
			});
			return $helper;
		}
			
		var onSort = function( event, ui ) {
			app_input_changed_global=true;
			app_input_changed=true;
			app_submit_clicked=false;
		}
	
		$("#locations-table tbody").sortable({
			items: ".app_location_tr",
			change: onSort,
			helper: fixHelper
		});
		var sortService = $("#services-table").sortable({
			items: "tbody:visible",
			forcePlaceholderSize: true,
			forceHelperSize: true,
			start: function( event, ui ) {
				ui.item.nextAll("tbody.service-tabs-container").first().appendTo(ui.item);
			},
			stop: function(event, ui) {
				 ui.item.after(ui.item.find("tbody.service-tabs-container"));
			},
			change: onSort,
			helper: fixHelper
		});
		$(document).on("service-settings-opened", function(){
			sortService.sortable("disable");
			$(".app_service_tr").css("cursor","pointer");
		});
		$(document).on("service-settings-closed", function(){
			sortService.sortable("enable");
			$(".app_service_tr").css("cursor","move");
		});			
		$("#services-table tr.app_time_dur").sortable({
			items: ".app-time-dur-rule",
			change: onSort
		});
		$("#categories-table").sortable({
			items: ".app_category_tr",
			change: onSort,
			helper: fixHelper
		});
		$("#workers-table tbody").sortable({
			items: ".app_worker_tr",
			change: onSort,
			helper: fixHelper
		});
		$("#udf-table tbody").sortable({
			items: ".app_udf_tr",
			change: onSort,
			helper: fixHelper
		});
		$("#app-wh").sortable({
			items: ".appointments-wrapper",
			change: onSort
		});
		$("#variations-table tbody").sortable({
			items: ".app_variation_tr",
			change: onSort,
			helper: fixHelper
		});
		$("#extras-table tbody").sortable({
			items: ".app_extra_tr",
			change: onSort,
			helper: fixHelper
		});				
		$("#services-table tr.app_packages").sortable({
			items: ".app-package-seq",
			change: onSort,
			helper: fixHelper
		});
	}

	/* Tab control */
	$.each( $(".app-tabs"), function(ip,vp){
		var tabs_wrap = $(this);
		var head = $(this).find("h3.hndle");
		var tab_id = $(this).attr("id");
		if (!head.length){
			$(this).hide();
			return false;
		}
		$.each( head, function(i,v) {
			var head_text = $(this).find("span").text();
			var dashicon = $(this).find("span.dashicons").first().prop('outerHTML');
			if ( !dashicon){dashicon="";}
			$(this).parent(".postbox").attr("id", tab_id+"-"+i );
			tabs_wrap.find("ul").first().append('<li><a href="#'+tab_id+'-'+i+'">'+dashicon+head_text+'</a></li>');
		});
		$(this).tabs({
			create: function( event, ui ) {
				var href = ui.tab.find("a").attr("href");
				var form = $(this).parents("form.app_form");
				var cur_action = form.attr("action");
				var index = cur_action.indexOf("#");
				if (index > 0) {
					cur_action = cur_action.substring(0, index);
				}
				form.attr("action",cur_action+href);
				select_tab($(this));
			},
			activate: function( event, ui ) {
				var href = ui.newTab.find("a").attr("href");
				var form = $(this).parents("form.app_form");
				var cur_action = form.attr("action");
				var index = cur_action.indexOf("#");
				if (index > 0) {
					cur_action = cur_action.substring(0, index);
				}
				form.attr("action",cur_action+href);
			}
		}).removeClass("ui-widget ui-corner-all");
	});	
	
	/* Remote select tab by hash */
	function select_tab(_this){
		var hash = location.hash;
		if (hash) {
			var tab_id = $(document).find(hash).parents(".ui-tabs-panel").attr("id");
			if ( tab_id ) {
				var index = tab_id.replace("tabs-","");
				_this.tabs("option", "active", index);
			}
		}
	}
	
	/* Prevent scrolling because of hash if dialog is opened
	 * http://stackoverflow.com/a/3659153 */
	$(document).on("app-conf-dialog-opened", function(){
		window.location.hash = 'app_no_jump';
	});

	/* Display a hidden div on load */
	var hash = String(location.hash);
	if (hash){
		$(hash).show("slow");
	}
	
	/* Catch $_GET vars
	 * http://stackoverflow.com/a/1586333
	 */
	 var $_GET = {};
	_app_.read_get = function() {
		var parts = window.location.search.substr(1).split("&");
		// var $_GET = {};
		for (var i = 0; i < parts.length; i++) {
			var temp = parts[i].split("=");
			$_GET[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
		}
		return $_GET;
	}
	
	$_GET = _app_.read_get();
	if ( parseInt($_GET['add_new']) == 1 ) {
		$(".add-new-h2").click();
	}
	
});
