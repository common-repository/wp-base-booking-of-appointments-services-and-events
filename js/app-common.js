/**
 * WP BASE Common scripts
 *
 * @author		Hakan Ozevin
 * @package     WP BASE
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version		3.0.0Beta38
 * @since       3.0
 */

 /* _app_ properties and methods are public, rest is private */
(function( _app_, $, undefined ) {
	
	/* Get user timezone */
	if (typeof jstz != 'undefined' && typeof _app_.use_jstz != 'undefined') {
		var timezone = jstz.determine();
		var tzstring = timezone.name();
		var tz_data = {action:"app_get_timezone", tzstring:tzstring, _ajax_nonce:_app_.nonce}; 
		$.post(_app_.ajax_url, tz_data);		
	}
	
	/* http://phpjs.org/functions/wpb_number_format/ */
	_app_.number_format = function(number) {
		if ( parseInt(_app_.curr_decimal) == 0 ) {decimals=0;} else{decimals=2;}
		
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number;
		var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
		var sep = _app_.thousands_sep;
		var dec = _app_.decimal_sep;
		var s = '';
		var toFixedFix = function (n, prec) {
			var k = Math.pow(10, prec)
			return '' + (Math.round(n * k) / k)
			  .toFixed(prec)
		}
		// @todo: for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}
	
	_app_.adjust_manage_controls = function() {
		var form = $("#wpbody-content .app-page form").first();
		var search = $("form#app-search-form");
		var reset = $("form#app-reset-form");
		var filter_par = $("form#app-filter-form").parent(".app-manage-second-column");
		var pag = $(".tablenav-pages").parent();
		if ( form.css("display") == "flex" ){
			if ( reset.parent(".app-manage-first-row .app-manage-first-column").length >0 ){
				reset.clone().appendTo(filter_par);
				reset.remove();
			}
			if ( search.parents(".app-manage-row").length > 0 ){
				search.clone().appendTo(".app-manage-first-row .app-manage-first-column");
				search.remove();
			}
			if ( pag.parent(".app-manage-second-row").length >0 ){
				var pag_par = $("<div class='pag-parent tablenav' />").html(pag.clone()).insertAfter("form.app_form");
				pag.remove();
			}
		}
		else {
			if ( search.parent(".app-manage-first-row .app-manage-first-column").length >0 ){
				search.clone().appendTo(".app-manage-row .app-manage-second-column");
				search.remove();
			}
			if ( reset.parent(".app-manage-second-column").length > 0 ) {
				reset.clone().appendTo(".app-manage-first-row .app-manage-first-column");
				reset.remove();
			}
			if ( pag.parent(".pag-parent").length > 0 ) {
				pag.clone().appendTo(".app-manage-second-row");
				$(pag,".pag-parent").remove();
			}
		}				
	}

	/* Hide/Show Book in Table View Columns acc to parent width  */
	_app_.show_hide_book_table_columns = function(){
		var edge = _app_.book_table_resp_width;
		if ( parseInt( edge ) > 0 ){
			$.each( $(document).find("table.app-book"), function(i,v){
				var tbl = $(this);
				if (!tbl.find("th.app-book-date").length || !tbl.find("th.app-book-time").length || !tbl.find("th.app-book-date_time").length){return false;}
				if (parseInt(tbl.outerWidth())<parseInt(edge)){
					tbl.find(".app-book-date,.app-book-day,.app-book-time").hide();
					tbl.find(".app-book-date_time").show();
				}
				else{
					tbl.find(".app-book-date,.app-book-day,.app-book-time").show();
					tbl.find(".app-book-date_time").hide();
				}
				
			});
		}
	};
	$(window).resize(function(){
		_app_.show_hide_book_table_columns();
	});
	
		
	/* Adjust button width for Book in Table View  */
	_app_.adjust_button_width = function(){
		// var table = $("table.app-book, div.app-book");
		// $.each( table,function(i,v){
			// var maxWidth = 0;
			// var buttons = $(this).find("button.app-has-var");
			// maxWidth = Math.max.apply(null, buttons.map(function (){ 
				// var me = $(this);
				// var my_width = me.css("width").replace("px","");
				// return my_width;
			// }).get());
			// buttons.css("width", maxWidth+"px");
		// });
	};

	/* Adjust height for mode fitHeights */
	_app_.fitHeights = function(blocks){
		if (blocks.length<2){return false;}
		var maxHeight = Math.max.apply(null, blocks.map(function (){ 
			var me = $(this);
			var netHeight=0;
			var pad_bottom= parseInt(me.find(".app_compact_day_button_holder").css("padding-bottom").replace("px",""));
			var pad_top= parseInt(me.find(".app_compact_day_button_holder").css("padding-top").replace("px",""));
			var my_offset = me.offset();
			var my_offsetTop = my_offset.top;
			var my_height = me.height();
			var my_bottom = my_offsetTop + my_height;
			var my_child = me.find("button").last();
			if (my_child.length) {
				my_child_height = my_child.height();
				my_child_offset = my_child.offset();
				my_child_offsetTop = my_child_offset.top;
				my_child_bottom = my_child_height + my_child_offsetTop;
				netHeight=my_height+my_child_bottom-my_bottom+10+pad_bottom+pad_top;
			}
			return netHeight;
		}).get());	
		
		blocks.height(maxHeight);
	};
	
	/* Adjust height for mode fitHeightsPerRow */
	_app_.fitHeightsPerRow = function(blocks){
		if (blocks.length<2){return false;}
		var me = blocks.first();
		var my_offset = me.offset();
		first_offsetTop = my_offset.top;
		var row = blocks.filter(function( index ) {
			var o_offset = $(this).offset();
			o_offsetTop = o_offset.top;
			return o_offsetTop == first_offsetTop;
		});
		_app_.fitHeights(row);
		blocks = blocks.not(row);
		_app_.fitHeightsPerRow(blocks);
	};
	
	/* Adjust margin bottom for title on horizontal layout */
	_app_.fitColumns = function(blocks){
		if (!blocks.length){return false;}
		$.each(blocks, function(i,v){
			var par = $(this).find(".app_compact_day_button_holder");
			var title = par.find("div.app_compact_day_title");
			var my_height = title.innerHeight();
			var par_height = par.innerHeight();
			if ( my_height < par_height-5 ){
				var dif = par_height - my_height;
				title.css("margin-bottom", dif +"px");
			}
		});
	};

	_app_.arrange_flex = function() {
		$.each(_app_.flex_modes, function(i,mode) {
			var $container = $("div.app_compact_"+mode);
			if ( parseInt( $container.length ) < 1 ){return true;}
			$.each($container, function(i,v){
				if ($(this).length && $(this).is(":hidden")){return true;}
				var $blocks = $(this).find("div.app_compact_day");
				$(this).imagesLoaded( function() {
					if ( "fitRows" == mode || "moduloColumns" == mode  ) {
						$(this).isotope({
						  itemSelector: ".app_compact_day",
							layoutMode: mode
						});
					}
					else if ( "fitHeights" == mode ){
						_app_.fitHeights($blocks);
					}
					else if ( "fitHeightsPerRow" == mode ){
						_app_.fitHeightsPerRow($blocks);
					}
					else if ( "fitColumns" == mode ){
						_app_.fitColumns($blocks);
					}
				});
			
				if ( "fitHeights" == mode ) {
					$(window).on("resize", function(){
						_app_.fitHeights($blocks);
					});
				}
				else if ( "fitHeightsPerRow" == mode ) {
					$(window).on("resize", function(){
						_app_.fitHeightsPerRow($blocks);
					});
				}
			});
		});
	};

	/* Credit Card evaluation */
	_app_.cc_card_pick = function(card_image, card_num){
		if (card_image == null) {
			card_image = "#cardimage";
		}
		if (card_num == null) {
			card_num = "#card_num";
		}

		var numLength = $(card_num).val().length;
		var number = $(card_num).val();
		if (numLength > 10)
		{
			if((number.charAt(0) == "4") && ((numLength == 13)||(numLength==16))) { $(card_image).removeClass(); $(card_image).addClass("cardimage visa_card"); }
			else if((number.charAt(0) == "5" && ((number.charAt(1) >= "1") && (number.charAt(1) <= "5"))) && (numLength==16)) { $(card_image).removeClass(); $(card_image).addClass("cardimage mastercard"); }
			else if(number.substring(0,4) == "6011" && (numLength==16)) 	{ $(card_image).removeClass(); $(card_image).addClass("cardimage amex"); }
			else if((number.charAt(0) == "3" && ((number.charAt(1) == "4") || (number.charAt(1) == "7"))) && (numLength==15)) { $(card_image).removeClass(); $(card_image).addClass("cardimage discover_card"); }
			else { $(card_image).removeClass(); $(card_image).addClass("cardimage nocard"); }

		}
	};
	
	/* Read $_GET url parameter */
	/* http://stackoverflow.com/a/25359264 */
	$.urlParam = function(name){
		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
		if (results==null){
		   return null;
		}
		else{
		   return results[1] || 0;
		}
	};	

	var mobile = (parseInt(_app_.is_mobile)>0) ? true:false;
	var swatch = _app_.swatch ? _app_.swatch : 'a';
	qtip_hide={fixed:true,delay:300};
	if ( parseInt(_app_.is_rtl) == 1 ) {
		 qtip_pos={	my: 'top right',
					at: 'bottom left',
					viewport:$(window)
		};
	}
	else {
		qtip_pos={viewport:$(window)};
	}
	qtip_style={widget:true,def:false,classes:"app_qtip"};
	qtip_small_style={widget:true,def:false,classes:"app_small_qtip"};
	qtip_n_style={widget:true,def:false,classes:"app_narrow_qtip"};


	$(document).ready(function($){
		/* Dummy functions for non-mobile and mobile devices */
		/* They return nothing and still chainable */
		if (mobile) {
			$.fn.qtip = function() {
				return $.fn.qtip;
			};
		}
		if (typeof $.fn.enhanceWithin === "undefined") {
			$.fn.enhanceWithin = function() {
				return $.fn.enhanceWithin;
			};
		}
		if (typeof $.mobile == "object" && typeof $.mobile.loading === "undefined") {
			$.mobile.loading = function() {
				return $.mobile.loading;
			};
		}
		
		/* Do some init stuff - Also run after datatables updates table */
		_app_.style_buttons = function(){
			if (mobile) {return false;}
			$(document).on({
				mouseenter: function () {
					$(this).addClass("ui-state-hover");
				},
				mouseleave: function () {
					$(this).removeClass("ui-state-hover");
				}
			}, ".ui-button");
			
			$(".app-book-now-button").button({
			  icons: { primary: "ui-icon-cart" }
			}).qtip();
			$(".app-conf-cancel-button").button({
			  icons: { primary: "ui-icon-cancel" }
			});
			$(".app-list-cancel").button({
			  icons: { primary: "ui-icon-trash" }
			});
			$(".app-conf-button").button({
			  icons: { primary: "ui-icon-check" }
			});
			$(".app-cont-btn").button({
			  icons: { primary: "ui-icon-arrowreturnthick-1-n" }
			});
			$(".app-my-appointments-edit").button({
			  icons: { primary: "ui-icon-pencil" }
			});
			$(".app-pdf-button").button({
			  icons: { primary: "ui-icon-document" }
			});
			$(".ui-button").addClass("ui-state-default ui-shadow ui-btn-"+swatch).qtip({style:qtip_style});	
		};
		
		update_elements();
		start_countdown({});
		_app_.arrange_flex();
		
		var pre_process = false;
		var at_checkout = false;
		var app_mp_active = parseInt(_app_.mp_active)>0 && typeof marketpress == 'function' ? true:false;
		var app_wc_active = parseInt(_app_.wc_active)>0 ? true:false;
		var hide_effect = _app_.hide_effect ? {effect:_app_.hide_effect, direction:"up", duration:parseInt(_app_.effect_speed)} : '';
		var show_effect = _app_.show_effect ? {effect:_app_.show_effect, direction:"up", duration:parseInt(_app_.effect_speed)} : '';
		
		selected_user=$(".app_select_users option:selected").val();
		if (typeof selected_user==="undefined"){selected_user=_app_.current_user_id;}
		if (typeof selected_timestamp==="undefined"){selected_timestamp=_app_.def_timestamp;}

		build_cart();
		
		/* Add tooltip for manual payments */
		var manual_tt = $.trim($(".app-manual-payments-instructions").html());
		if ( manual_tt ) {
			$(".app-manual-payments").qtip({
				content: {
					text: manual_tt
				},hide:qtip_hide,position:{my:"top left",at:"bottom center",viewpoint:$(window)},style:{widget:true,def:false}
			});
		}
		
		/* Event listener to add tooltip to display appointments in calendar cells after pagination clicks */
		$(document).on("calendars_updated", update_elements );
		
		/* Check if a service has selectable duration*/
		_app_.has_duration = function(sid){
			var services = typeof _app_.services_with_dur !=="undefined" ? _app_.services_with_dur : [];
			if ($.inArray(sid.toString(),services) > -1){return true;}
			else{return false;}
		};
		
		/* Add tooltip to display appointments in calendar cells and various style corrections */
		function update_elements( when ) {
			if ( false && !mobile && typeof $.fn.intlTelInput == "function" ) {
				$.each( $(".app-phone-field-entry"), function(){
					$this = $(this);
					$this.intlTelInput({
						initialCountry: "auto",
						geoIpLookup: function(callback) {
							$.get('http://ipinfo.io', function() {}, "jsonp").always(function(resp) {
								var countryCode = (resp && resp.country) ? resp.country : "";
								callback(countryCode);
							});
						}
						// ,
						// utilsScript: _app_.plugin_url+"/js/utils.js"
					});
				});
			}
			if ( typeof $.fn.datepicker == "function" ) {
				$.each( $('.app-date-field-entry'), function(){
					$this = $(this);
					$this.datepicker({
						dateFormat:_app_.js_date_format,
						firstDay:_app_.start_of_week,
						maxDate: $this.attr("data-maxdate") ? $this.data("maxdate") : null,
						changeMonth: true,
						changeYear: true,
						monthNamesShort: _app_.monthNamesShort,
						dayNamesMin: _app_.dayNamesMin,
					});
				});
				
				$.each( $('.app_select_date'), function(){
					$this = $(this);
					$this.datepicker({
						dateFormat: _app_.js_date_format,
						firstDay:_app_.start_of_week, 
						maxDate:$this.data("maxdate") ? parseInt($this.data("maxdate")) : null,
						minDate:0,						
						changeMonth: true,
						changeYear: true,
						monthNamesShort: _app_.monthNamesShort,
						dayNamesMin: _app_.dayNamesMin,
						onSelect:function(){
							$('input.app_select_date').val($(this).val());
							_app_.updating();
							selected_timestamp = parseInt($this.datepicker('getDate')/1000);
							_app_.update_calendars();
						}
					});
				});
			}			
			if ( !mobile && typeof $.fn.multiselect !=="undefined") {
				var loc_ops = {multiple:false,selectedList:1,classes:'app_locations app_ms'};
				var service_ops = {multiple:false,selectedList:1,classes:'app_services app_ms'};
				var worker_ops = {multiple:false,selectedList:1,classes:'app_workers app_ms'};
				var filter_ops = {label:(_app_.filter_label ? _app_.filter_label : 'Filter:'), placeholder: (_app_.filter_placeholder ? _app_.filter_placeholder : 'Enter keywords')};  
				
				if ( parseInt(_app_.location_filter)>0 ) {$('.app_select_locations').multiselect(loc_ops).multiselectfilter(filter_ops);}
				else {$('.app_select_locations').multiselect(loc_ops);}	
				if ( parseInt(_app_.service_filter)>0 ) {$('.app_select_services').multiselect(service_ops).multiselectfilter(filter_ops);}				
				else {$('.app_select_services').multiselect(service_ops);}
				if ( parseInt(_app_.worker_filter)>0 ) {$('.app_select_workers').multiselect(worker_ops).multiselectfilter(filter_ops);}
				else {$('.app_select_workers').multiselect(worker_ops);}				
				$('.app_select_durations').multiselect({multiple:false,selectedList:1,classes:'app_durations app_ms'});
				$('.app_select_users').multiselect({multiple:false,selectedList:1, classes:'app_users app_ms'}).multiselectfilter(filter_ops);
				$('.app_select_theme').multiselect({multiple:false,selectedList:1, classes:'app_themes app_ms'});
				$(".app_timezone_string").multiselect({multiple:false,selectedList:1, classes:"app_timezones app_ms"}).multiselectfilter(filter_ops);
				$(".app_wh_timezone").multiselect({multiple:false,selectedList:1, classes:"app_timezones app_ms"}).multiselectfilter(filter_ops);
				$(".app_select_seats").multiselect({multiple:false,selectedList:1,minWidth:"30%",classes:"app_seats app_ms"});
				$(".app_select_extras").multiselect({selectedList:3, classes:"app_extras app_ms"});
				$(".app_select_repeat").multiselect({multiple:false,selectedList:1,minWidth:"30%",classes:"app_repeat app_ms"});
				$(".app_select_repeat_unit").multiselect({multiple:false,selectedList:1,minWidth:"30%",classes:"app_repeat_unit app_ms"});
				$(".app_select_lang").multiselect({multiple:false,selectedList:1, classes:"app_multilang app_ms"}).multiselectfilter(filter_ops);
			}

			
			_app_.style_buttons();			
			$(".ui-button").addClass("ui-shadow ui-btn-"+swatch).enhanceWithin();
			$(".app-compact-book-wrapper, .app-ms").enhanceWithin();
			$(".app_ms").enhanceWithin();
			
			if ( typeof $.fn.quickfit !== "undefined" ) {
				// $(".app_title").quickfit({min:12, max:20});
				// $(".appointments-wrapper .app_title").quickfit({min:14, max:19});
				// $(".app_monthly_schedule_wrapper th").quickfit({min:8, max:16});
				$("button.ui-multiselect span").quickfit({min:13, max:16});
				$(".app-conf-details dl").quickfit({min:10.5, max:13});
				$(".app-book-flex-button").quickfit({min:8, max:14});
				// $(".appointments-pagination span, .appointments-pagination input").quickfit({min:7, max:14});
			}
			
			adjust_menu_padding();
			if ( when != "pre-conf" ) {
				run_swipe();
			}
			
			$(".appointments-wrapper, .appointments-wrapper-widget, .app-conf-wrapper, .app-list-wrapper, .app-debug").find("[title][title!='']").each(function() {
				var title = $(this).attr("title");
				// There is interpunkt mark here
				var ttip = title ? title.replace(/\●/g,"<br />") : '';
				$(this).qtip({
					content: {
						text: ttip,
						title: $(this).data('title')
					},
					style:qtip_n_style
				});
			});

			$('.has_inline_appointment,.app_schedule_wrapper_admin .busy,.app_schedule_wrapper_admin .has_appointment,.app_monthly_schedule_wrapper_admin .has_appointment,.app_monthly_schedule_wrapper_admin .busy').qtip({
				overwrite: true,
				content: {
					text: function(event, api) {
						var weekly = $(this).parents().hasClass('app_schedule_wrapper_admin') ? 1:0;
						api.elements.content.html(_app_.please_wait);
						return $.ajax({
							url: ajaxurl, 
							type: 'POST',
							dataType: 'json', 
							data: {
								weekly: weekly,
								app_val: $(this).find('.app_get_val').val(),
								action: 'app_bookings_in_tooltip'
							}
						})
						.then(function(res) {
							var content = res.result;
							return content;
						}, function(xhr, status, error) {
							api.set('content.text', status + ': ' + error);
						});
					}
				},hide:qtip_hide,position:{my: 'top center',at: 'bottom center',viewport:$(window)},style:qtip_small_style
			});
			
			$(document).on( "mouseenter", ".ui-multiselect-menu.app_ms.app_locations label,.ui-multiselect-menu.app_ms.app_services label, .ui-multiselect-menu.app_ms.app_workers label", function(event){
				var $this = $(this);
				var rad = $this.find("input"); // radio input in menu 
				var id = rad.val(); // service, worker id
				var lsw = "services";
				if ( $this.parents(".ui-multiselect-menu").hasClass("app_locations") ) {
					lsw = "locations";
				}
				else if ( $this.parents(".ui-multiselect-menu").hasClass("app_workers") ) {
					lsw = "workers";
				}
				
				var slc = $(document).find("select.app_ms.app_select_"+lsw).first(); // Select element
				var pages = slc.data("with_page").toString(); // comma delimited services or workers
				if (parseInt(pages.length)==0){return false;}

				var arr = pages.split(",");
				if ( !arr || $.inArray(id,arr)==-1 ){return false;}

				var title = slc.find("option").filter( function(){ return $(this).val()==id;} ).text();
				var desc = slc.data("desc"); // excerpt, page_content, etc
				var ex_len = slc.data("ex_len"); // excerpt length
				var cache_cl = 'app-desc-cache-'+lsw+'-'+id;
				var cache = $('<div class="'+cache_cl+'" />');
				
				$this.qtip({
					overwrite: false,
					content: {
						text: function(event, api) {
							if ( parseInt( $(document).find('.'+cache_cl).length ) > 0 ) {
								return $(document).find('.'+cache_cl).html();
							}
							api.elements.content.html(_app_.please_wait);
							return $.ajax({
								url: ajaxurl, 
								type: 'POST',
								dataType: 'json', 
								data: {
									wpb_ajax: true,
									id: id,
									desc: desc,
									ex_len: ex_len,
									lsw: lsw,
									action: 'app_lsw_tooltip'
								}
							})
							.then(function(res) {
								var content = res.result;
								cache.html(content).appendTo($(document.body)).hide();
								return content;
							}, function(xhr, status, error) {
								api.set('content.text', status + ': ' + error);
							});
						},
						title:title
					},
					show: {
						event: event.type,
						ready: true
					},
					hide:qtip_hide,position:qtip_pos,style:qtip_n_style
				}, event);				
			});
			
		}
		
		/* Adjust padding right for last visible menu element */
		/* Also match last items width to first one if there are 2 lines */
		/* This has to come after multiselect for a smooth transition */
		/* TODO: add flex-basis */
		function adjust_menu_padding(){
			var menus = $("div.app-flex-menu div.app-flex-item:visible");
			var cnt = menus.length;
			var has_2lines = false;
			$.each( menus, function(i,v){
				me = $(this);
				next_i = i + 1;
				if ( typeof menus[next_i] == "undefined" || parseInt($(menus[next_i]).width()) < 10 ) {
					me.addClass("app-item-last-in-line");
				}
				else {
					my_offset = me.offset();
					him = $(menus[next_i]);
					his_offset = him.offset();
					if ( Math.abs( his_offset.top - my_offset.top ) > 30 ) {
						me.addClass("app-item-last-in-line");
						has_2lines = true;
					}
					else {
						me.removeClass("app-item-last-in-line");
					}
				}							
			});
			// if (has_2lines && cnt){
				// menus.last().css('max-width', menus.first().width());
			// }
		}

		/* Close info panel */
		function close_updating() {
			$(".app-compact-book-wrapper, .appointments-wrapper").css("opacity",1);
			if (mobile){$.mobile.loading('hide');}
			$('div.app-updating-panel').find(".app-updating").text(_app_.done);
			$.unblockUI({ fadeOut: 700 });
		}
		$(document).ajaxStop(close_updating);

		/* Open info panel */
		var panel = $('div#app-updating-panel');
		_app_.updating = function(task){
			if (!task || 'first_load' == task) {
				$(".app-compact-book-wrapper, .appointments-wrapper").css("opacity",_app_.opacity);
				if (mobile){$.mobile.loading('show');}
				if ( 'first_load' == task ) {
					return false;
				}
			}
			
			var msg = task && _app_[task] ? _app_[task] : _app_.updating_text;
			var is_checkout = task =='booking' ? true : false
			panel.find('.app-updating').text(msg);
			var msg = panel;
			
			$.blockUI.defaults.css = {}; 
			$.blockUI.defaults.themedCSS = {
				width: 'auto',
				top: '50%',
				left: '50%',
				right: 'initial',
				overflow: 'initial',
				'-ms-transform': 'translate(-50%, -50%)',
				'-webkit-transform': 'translate(-50%, -50%)',
				'transform': 'translate(-50%, -50%)'
			};
			if(!$('.blockUI').length) {			
				$.blockUI({
					blockMsgClass: 'app-blockUI',
					message: msg,
					fadeIn: 200, 
					fadeOut: 700,
					theme: is_checkout,				
					showOverlay: is_checkout, 
					centerY: is_checkout,
					ignoreIfBlocked: true				
				}); 
			}
		}
		
		/* Redraw multiselect */
		function refresh_multiselect(){
			if ( typeof $.mobile != "object" && typeof $.fn.multiselect !=="undefined") {
				$.each($(".app_ms"), function(i,v){
					if ( $(this).data().hasOwnProperty( "echMultiselect" ) ) {
						$(this).multiselect("refresh");
					}
				});
			}
		}
		$(window).resize(function(){
			refresh_multiselect();
			adjust_menu_padding();
		});
		
		/* Select location */
		$(document).on('change', '.app_select_locations:not(.app_edit)',function(){
			selected_location=$('.app_select_locations option:selected').val();
			$('.app_location_excerpt').hide();
			$('#app_location_excerpt_'+selected_location).show();
			_app_.update_calendars();
			$(document).trigger("update-locations");
		});
		
		/* Select service */
		$(document).on('change', '.app_select_services:not(.app_edit)', function(){
			if (at_checkout){return false;}
			selected_service=$('.app_select_services option:selected').val();
			_app_.update_calendars();
			$(document).trigger("update-services");
		});
		
		/* Select duration */
		$(document).on('change','.app_select_durations',function(){
			if (at_checkout){return false;}
			selected_duration=$('.app_select_durations option:selected').val();
			_app_.update_calendars();
			$(document).trigger("update-durations");	
		});

		/* Select provider */
		$(document).on('change', '.app_select_workers:not(.app_edit)', function(){
			selected_worker=$('.app_select_workers option:selected').val();
			$('.app_worker_excerpt').hide();
			$('#app_worker_excerpt_'+selected_worker).show();
			_app_.update_calendars(); 
			$(document).trigger("update-workers");
		});
			
		/* Select timezone */
		$(document).on('change', '.app_timezone_string', function(){
			selected_timezone=$('.app_timezone_string option:selected').not(":disabled").val();
			$.post(_app_.ajax_url, {action:"update_timezone"}, function(r) {
				$(document).trigger("update-timezone");
				_app_.update_calendars();
			},"json");
		});
		
		/* Select timezone on user-provider page */
		$(document).on('change','.app_wh_timezone',function(){
			var me = $(this);
			var form_tz = me.parents("form").first();
			form_tz.find("input[name='app_timezone_string']").val(me.val());
			form_tz.submit();
		});
		
		/* Select recurring appt repeat/repeat unit */
		$(document).on("change", ".app_select_repeat,.app_select_repeat_unit", function(e){
			_app_.update_calendars();
		});

		/* Select seats */
		$(document).on("change", ".app_select_seats", function(e){
			_app_.update_calendars(0,0,0,"seats_changed");
		});

		/* Helper for Update calendars - Maintains ajax script run only once and fixes buttons before/after animations */
		function update_cal_h( ) {
			var cal_script = typeof $(document).data("calendar_scripts") !== "undefined" ? $(document).data("calendar_scripts") : null;
			var lock = typeof $(document).data("calendar_scripts_lock") !== "undefined" ? $(document).data("calendar_scripts_lock") : null;
			if ( cal_script && !lock ) {
				eval(cal_script);
				$(document).data("calendar_scripts",null);
			}

			$(".ui-button").addClass("ui-state-default").qtip();
			$(".app-book-now-button").button({
			  icons: { primary: "ui-icon-cart" }
			}).qtip();
		}

		/* Update calendars with pagination buttons or service change */
		var updating_calendars = false;
		_app_.update_calendars = function(prev_clicked,step,unit,task) {
			if ( updating_calendars ){return false;}
			updating_calendars = true;
			var menus = $("div.app-flex-menu div.app-flex-item:visible");
			$.each( menus, function(i,v){
				// $(this).removeClass("app-item-last-in-line");
			});

			_app_.updating(task);
			
			prev_clicked = typeof prev_clicked !== "undefined" ? prev_clicked : 0;
			var arr = typeof $(document).data("shift_register") =="undefined" ? new Array() : $(document).data("shift_register");
			var dur = typeof selected_duration !=="undefined" ? selected_duration : 0;
			var service_dur = typeof selected_service_dur !=="undefined" ? selected_service_dur : 0;
			
			selected_location=$(".app_select_locations option:selected").val();
			if (typeof selected_location==="undefined"){selected_location=_app_.def_location;}
			selected_service=$(".app_select_services option:selected").val();
			if (typeof selected_service==="undefined"){selected_service=_app_.def_service;}
			selected_worker=$(".app_select_workers option:selected").val();
			if (typeof selected_worker==="undefined"){selected_worker=_app_.def_worker;}
			selected_timezone=$(".app_timezone_string option:selected").val();
			if (typeof selected_timezone==="undefined"){selected_timezone="";}
			if (typeof _app_.used_widgets==="undefined"){_app_.used_widgets="";}
			var update_cal_dat = {
				wpb_ajax:true,
				action:"update_calendars",
				task:task,				
				prev_clicked:prev_clicked, 
				app_last_timestamps: JSON.stringify(arr), 
				tab:_app_.tab, 
				screen_base:_app_.screen_base, 
				page_id:parseInt(_app_.post_id),
				step:step,
				unit:unit,
				app_location_id:selected_location, 
				app_service_id:selected_service,
				app_worker_id:selected_worker,
				app_tz_string:selected_timezone,
				app_lang: $.urlParam("app_lang"),				
				app_timestamp:selected_timestamp,
				used_widgets:_app_.used_widgets,
				bp_displayed_user_id:_app_.bp_displayed_user_id,
				bp_tab:_app_.bp_tab,
				app_seats:$(".app_select_seats option:selected").val(),
				app_duration:$(".app_select_durations option:selected").val(),
				app_repeat:$(".app_select_repeat option:selected").val(),
				app_repeat_unit:$(".app_select_repeat_unit option:selected").val()
			};
			
			if (typeof update_cal_add_dat == "undefined"){ update_cal_add_dat={};}
			$.extend(update_cal_dat,update_cal_add_dat);
			
			$.post(_app_.ajax_url, update_cal_dat, function(r) {
				updating_calendars = false;
				$(document).data("calendar_scripts",r.j);
				$(document).data("calendar_scripts_lock",null);
				if ( r ) {
					if (r.html_first){
						$(".app_nested_menu").remove();
						var menu = $(".app_first_menu").replaceWith(r.html_first);
						menu.addClass("app_replaced");
					}
					if (r.html_single){
						$.each( r.html_single, function( k, v ) {
							if ($(this).hasClass("app_replaced")){return true;}
							var menu2 = $(".app_single_menu").eq(k).replaceWith(v);
							menu2.addClass("app_replaced");
						});
					}
					if ( r.html_hide ) {
						var req = $(r.html_hide);
						req.each( function( k, v ) {
							if ($(this).hasClass("app_replaced")){return true;}
							var divh = $(v);
							if ( true || $.trim(divh.text()) !="" ) {
								if (parseInt(_app_.use_effect) > 0 ) {
									$(document).data("calendar_scripts_lock",true);
									// http://stackoverflow.com/questions/5248721/$-replacewith-fade-animate
									$(".app-hide-wrapper").eq(k).hide("drop",1000, function(){
										div.hide();
										$(this).replaceWith(div).addClass("app_replaced");
										update_elements();
										div.show("drop",1000).promise().done(function(){
											$(document).data("calendar_scripts_lock",null);
											update_cal_h();
										});
									});
								}
								else {
									$(".app-hide-wrapper").eq(k).replaceWith(v).addClass("app_replaced");
									update_cal_h();
								}
							}
						});
					}
					else {
						update_cal_h();
					}

					if ( r.html ) {
						$.each( r.html, function( k, v ) {
							if ($(this).hasClass("app_replaced")){return true;}
							$(".appointments-wrapper").not(".app-book-child, .app-swipe-child").eq(k).replaceWith(v).addClass("app_replaced");
						});
					}
					if ( r.html_c ) {
						$.each( r.html_c, function( k, v ) {
							if ($(this).hasClass("app_replaced")){return true;}
						  $(".app-compact-book-wrapper-gr1").eq(k).replaceWith(v);
						});
					}
					if ( r.prev ) {
						var me = $(".app_previous a").parent(".app_previous");
						if ( typeof me != "undefined" && me.length ) {
							if ( "hide" == r.prev ) {
								me.css("visibility","hidden");
							}
							else {
								var cl = me.attr("class").replace(/\d+/g, r.prev);
								me.attr("class", cl ).css("visibility","visible");
							}
						}
					}
					if ( r.next ) {
						var me = $(".app_next a").parent(".app_next");
						if ( typeof me != "undefined" && me.length ) {
							if ( "hide" == r.next ) {
								me.css("visibility","hidden");
							}
							else {
								var cl = me.attr("class").replace(/\d+/g, r.next);
								me.attr("class", cl ).css("visibility","visible");
							}
						}
					}
					if ( r.widgets ) {
						$.each( r.widgets, function( k, v ) {
						  $("#appointments_shortcode-"+k).html(v);
						});
					}
					update_cal_h();
					
					if ( task == "seats_changed" && $(document).data("last_clicked_slot") ){
						handle_clicks( "click", $(document).data("last_clicked_slot") );
					}
					
					_app_.arrange_flex();
					
					/* Let qtip reload */
					$(document).trigger("calendars_updated");
				}
				else {
					alert(_app_.con_error);
				}
			},"json");
		};
		if ( parseInt( _app_.lazy_load ) > 0 ) {
			_app_.update_calendars(0,0,0,'first_load');
		}
		
		/* Pagination buttons */
		$(document).on("click", ".app_next a, .app_previous a", function(e) {
			e.preventDefault();
			if (at_checkout){return false;}
			var par = $(this).parent();
			var unit = par.find(".app_unit").val();
			var step = par.find(".app_step").val();
			prev_clicked = par.hasClass("app_previous") ? 1 : 0;
			$(document).data("prev_clicked",prev_clicked);
			var arr = typeof $(document).data("shift_register") =="undefined" ? new Array() : $(document).data("shift_register");
			var temp = typeof $(document).data("last_values") =="undefined" ? new Array() : $(document).data("last_values");
			if (!prev_clicked){
				arr.unshift(temp);
				$(document).data("shift_register",arr);
			}
			selected_timestamp = par.attr("class").split(" ")[1];
			_app_.update_calendars(prev_clicked,step,unit);
			if (prev_clicked){
				arr.shift();
				$(document).data("shift_register",arr);
			}
		});
		
		/* Bring book table and fill swipe slider */
		created_index_max = 3;
		_app_.bring_book_table = function( index ) {
			if ( typeof current_index == "undefined" ){current_index=-1;}	
			var target = index + 3;
			if ( target > created_index_max ) {
				// If no buffer left, show loading...
				if ( (created_index_max - current_index) < 1 ){
					if (mobile){$.mobile.loading('show');}
				}
				var target_el = $("div.appointments-wrapper[data-index='"+target+"']");
				$.post(_app_.ajax_url, 
					{	wpb_ajax:true,
						action:"bring_book_table",
						post_id:_app_.post_id,
						type:target_el.data("type"),
						start_ts:target_el.data("start-ts"),
						title:target_el.data("title"),
						logged:target_el.data("logged"),
						notlogged:target_el.data("notlogged")
					}, 
					function(r) {
						current_index = -1; // This allows first call
						if (mobile){$.mobile.loading('hide');}
						if ( r && r.html ){
							target_el.html(r.html);
							target_el.find(".ui-button").addClass("ui-shadow ui-btn-"+swatch).enhanceWithin();
							created_index_max = Math.max(created_index_max, target);
							return true;
						}
					},
				"json");
			}
		}
		
		/* Set height=1px to slides out of sight after transition complete */
		function adjust_height( index ) {
			if ( typeof current_index_h == "undefined" ){current_index_h=-1;}	// callback runs twice. This ignores the second call.
			if ( index == current_index_h ){return;}
			current_index_h = index;
			var me = $("div.appointments-wrapper[data-index='"+index+"']");
			if ( me && me.length ) {
				$("div.appointments-wrapper[data-index]").not(me).css("height","1px");
				me.css("height","auto");
			}
		}
		
		function run_swipe() {
			/* Swipe.js call */
			if ( typeof Swipe == "function" && mobile ) {
				slider = document.getElementById('app-slider');
				var startSlide = 0;			
				var wpbSwipe = new Swipe(slider, {
				  startSlide: startSlide,
				  speed: 400,
				  auto: 0,
				  continuous: false,
				  disableScroll: false,
				  stopPropagation: false,
				  callback: function(index, elem) { _app_.bring_book_table(index); },
				  transitionEnd: function(index, elem) { adjust_height(index); }
				});
				setTimeout(function() {
					if ( document.getElementById('app-slider') ) {
						wpbSwipe.setup();
						created_index_max = 3;
					}
				}, 10);
			}
		}
		
		/* Clicking monthly calendar day cell to bring timetable */
		$(document).on("click",".app_day.free:not(.daily),.app_day.waiting:not(.daily)", function(){
			if (at_checkout){return false;}
			var $this = $(this);
			var par = $this.parents(".appointments-list");
			if (par.find(".app_blink").length>0){return false;}
			var val = $this.find(".app_get_val").val();
			var start = val.split("_")[0].toString();
			var cl = ".app_timetable_"+start;
			var wrap = $this.parents(".appointments-list").find(".app_timetable_wrapper");
			var ttable = wrap.find(cl);
		
			if ( ttable.length ) {
				wrap.find(".app_timetable").hide();
				ttable.show("slow");
				goTo(wrap);
				return false;
			}
			
			_app_.updating('preparing_timetable');
			
			$.post(_app_.ajax_url,{
				wpb_ajax:true,
				action:"bring_timetable",
				display_mode:par.find(".app_monthly_schedule_wrapper").data("display_mode"),
				app_value:val,
				app_service_id:$(".app_select_services option:selected").val() ? $(".app_select_services option:selected").val() : _app_.def_service,
				app_duration:$(".app_select_durations option:selected").val(),
				app_repeat:$(".app_select_repeat option:selected").val(),
				app_repeat_unit:$(".app_select_repeat_unit option:selected").val(),
				app_repeat_unit_alt:$(".app_repeat_unit_alt").val(),
				app_seats:$(".app_select_seats option:selected").val()
				}, function(r){
					wrap.children().hide();
					if ( r && r.data ) {
						var data = $(r.data);
						wrap.append(data);
						data.show("slow");
						update_elements();
						goTo(wrap);
					}
				},"json");
			});
		
		/* Determine if confirmation form 1 or 2 columns */
		function conf_form_layout(){
			$.each($(document).find(".app-conf-wrapper"),function(i,v){
				var chld = $(this).find(".app-conf-fields-gr1");
				if (!chld.hasClass("app-conf-fields-gr-auto")) {return false;}
				var width = $(this).innerWidth();
				var edge_width = chld.data("edge_width");
				if ( width > edge_width ) {
					chld.addClass("app_2column");
					chld.next().addClass("app_2column");
				}
				if ( (width + 20) < edge_width ) {
					chld.removeClass("app_2column");
					chld.next().removeClass("app_2column");
				}
			});
			refresh_multiselect();
		}
		conf_form_layout();
		$(document).on("app-conf-wrapper-opened", conf_form_layout);
		/* http://stackoverflow.com/a/15170104 */
		$(window).resize(function(){
			clearTimeout($.data(this, 'resizeTimer'));
			$.data(this, 'resizeTimer', setTimeout(function() {
				conf_form_layout();
			}, 500));
		});

		/* Prevent disabled buttons fire */
		$(document).on("click", ".app-disabled-button", function(e){e.preventDefault();return false;});
		
		/* app_value is a global array of booked time slots */
		function get_app_value(){
			app_value = typeof app_value == "undefined" ? [] : app_value;
			return app_value;
		}
		
		/* Check if cart is activated by the shortcode */
		function has_cart(){
			return $(".has-cart").length && parseInt($(".has-cart").val()) > 0 ? 1 : 0;	
		}
		
		/* Enable confirmation checkout button */
		_app_.enable_button = function() {
			$(".app-conf-button").removeClass("app-disabled-button").css("opacity",1).blur().qtip({
				overwrite: true,
				content: {
					text: _app_.checkout_button_tip
				},hide:qtip_hide,position:qtip_pos,style:qtip_small_style
			});
		};

		/* Disable confirmation checkout button */
		_app_.disable_button = function() {
			app_value = get_app_value();
			var q_text = app_value.length === 0 ? _app_.too_less : _app_.no_gateway;
			$(".app-conf-button").addClass("app-disabled-button").css("opacity",_app_.opacity).blur().qtip({
				overwrite: true,
				content: {
					text: q_text
				},hide:qtip_hide,position:qtip_pos,style:qtip_small_style
			});
		};
		
		/* Scroll to the selected target */
		function goTo($t){
			if ($t){
				$.scrollTo($t,{duration:_app_.scroll_duration, axis:"y", offset:-1*parseInt(_app_.offset)});
			}
		}

		/* Start/pause Countdown */
		function start_countdown(r) {
			var rt = r.remaining_time || parseInt(_app_.remaining_time);
			if ( rt && $(".app-conf-countdown").length && typeof $.fn.countdown !=="undefined" ) {
				$(".app-conf-countdown").countdown({
					format:_app_.countdown_format,
					until:rt,
					onTick: function (p) { 
							if ($.countdown.periodsToSeconds(p) === parseInt(_app_.blink_starts)) {				
								$(this).addClass("app_blink app-error"); 
							}  
						},
					onExpiry:function(){
						var params = [
							"app_empty_cart=1",
							"_ajax_nonce="+_app_.nonce
						];
						window.location.href = window.location.protocol + "//" + window.location.host + window.location.pathname + '?' + params.join('&');
					}
				});
				$(".app_countdown_dropdown_title.app_title").css("visibility","visible").show();
			}
		}
		function pause_countdown(){
			if ( $(".app-conf-countdown").length && typeof $.fn.countdown !=="undefined" ) {
				$(".app-conf-countdown").countdown("pause");
			}
		}
		
		/* Cancel button - Refresh the page */
		$(document).on("click", ".app-conf-cancel-button", function(){
			var confirm_wrapper = $("<div class='app-cancel-dialog-content' data-dialog='true' >"+_app_.cancel_confirm_text+"</div>");
			var editing = false;
			if ( $(this).parents(".app-edit-wrapper").length ){editing=true;}
			var w = window.innerWidth;
			var dwidth = 0;
			if (w > 600) {dwidth = 600;}
			else{ dwidth= w-30;}
			confirm_wrapper.dialog({
				resizable: false,
				width:dwidth,
				modal: parseInt(_app_.modal)>0 ? true : false,
				dialogClass: "app-no-title app-cancel-dialog",
				hide: hide_effect,
				show: show_effect,
				buttons: [
					{	text: _app_.cancel_confirm_yes,
						click: function() {
							if ( !editing ) {
								var params = [
									"app_empty_cart=1",
									"_ajax_nonce="+_app_.nonce
								];
								_app_.updating('refreshing');
								window.location.href = window.location.protocol + "//" + window.location.host + window.location.pathname + '?' + params.join('&');
							}
							$(this).dialog("destroy");
							$(".ui-dialog").each(function(){
								$(this).dialog().dialog("destroy").remove();
							});
							$(document).trigger("app-edit-cancelled");							
						}
					},
					{	text: _app_.cancel_confirm_no,
						click: function() {
							$(this).dialog("destroy").remove();
						}
					}
				]
			});
		});

		/* Add required fields booking notes to the form */
		function add_sup_mark(){
			var cw = $(document).find(".app-conf-wrapper").first();
			if ( cw.find("sup").not(".app-next-day, .app-all-day").length && !cw.find(".app-required-note").length ) {
				$(".app-required-note").remove();
				cw.find("fieldset .app-conf-final-note").before("<div class='app-required-note'><sup> *</sup>"+_app_.required+"</div>");
			}
		}
		
		/* Fixed tooltips for prices */
		function tt_regular_price() {
			$('.app_old_price').qtip({
				content:{text:_app_.tt_regular_price},
				show:{
					when:false,
					ready:true,
					delay:2000
				},
				hide:{event:false, inactive:5000},
				style:qtip_small_style,
				position:{my:'right center',at:'left center',target:false}
			});			
		}
		function tt_discounted_price(coupon) {
			var tt_text = typeof coupon === "undefined" || !coupon ? _app_.tt_discounted_price : _app_.tt_coupon;
			$('.app_new_price').qtip({
				content:{text:tt_text},
				show:{
					when:false,
					ready:true,
					delay:3000
				},
				hide:{event:false, inactive:3000},
				style:qtip_small_style,
				position:{my:'left center',at:'right center',target:false}
			});
		}
		
		/* Build cart on first page load */
		function build_cart() {
			if ( typeof _app_.cart_values ==="undefined" || !_app_.cart_values.length ){return false;}
			var cw = $(document).find(".app-conf-wrapper").first();
			if ( !cw.length ){return false;}			
			var cart_data = read_form(cw);
			cart_data.value = _app_.cart_values;
			cart_data.action = "pre_confirmation_update";
			
			$.post(_app_.ajax_url, cart_data, function(r) {
				if ( r ) {
					if ( r.error ) {
						_app_.open_dialog({
								confirm_text:r.error,
								confirm_title:_app_.error_short,
								refresh:false, 
								price:999
						});
					}
					else {handle_pre_conf_reply( r );}
				}
				else {
					alert(_app_.con_error);
				}
			},"json");
		}
		
		/* Read confirmation form values for pre_conf, update and post_conf */
		function read_form(cw){
			var app_id = $(".app_gateway_form").find(".app_id") ? $(".app_gateway_form").find(".app_id").val() : 0;
			if (!app_id && cw.find(".app-edit-id")) {app_id=cw.find(".app-edit-id").val(); }
			var app_user_id = selected_user;
			var app_user_data = {};
			if( typeof user_fields=="undefined"){user_fields={};}
			var editing = parseInt(cw.find(".app_editing_value").val());
			if (editing==1){fields=user_fields_edit;}else{fields=user_fields;}
			$.each(fields, function( i, v ) {
				app_user_data[v] = cw.find(".app-"+v+"-field-entry").val();
			});						
			
			var app_cc_data = {};
			cw.find(".app_billing_line input, .app_billing_line select").each(function(){ 
				var input_name=$(this).prop("name"); 
				var input_val=$(this).val(); 
				app_cc_data[input_name]=input_val; 
			});
			var post_data = {
				wpb_ajax:true,
				action: "post_confirmation",
				post_id:cw.find(".app_post_id").val(),
				app_id:app_id,
				bp_tab:_app_.bp_tab,
				has_cart:has_cart(),
				value:get_app_value(),
				editing:editing,
				app_lang: $.urlParam("app_lang"),
				app_edit_cap:$.type($("body").data("app_edit_cap")) =="string" ? $("body").data("app_edit_cap") : 0,
				app_user_data:JSON.stringify(app_user_data),
				app_user_fields:JSON.stringify(fields),
				app_note:cw.find(".app-note-field-entry").val(),
				app_remember:cw.find(".app-remember-field-entry").is(":checked") ? 1 : "",  
				app_service:cw.find(".app_select_services") ? cw.find(".app_select_services").val() : 0,
				app_category:$(document).find(".app_select_services") ? $(document).find(".app_select_services option:selected").closest("optgroup").data("category_id") : 0,
				app_worker:cw.find(".app_select_workers") ? cw.find(".app_select_workers").val() : 0,
				app_date:cw.find(".app-edit-date") ? cw.find(".app-edit-date").val() : 0,
				app_time:cw.find(".app-edit-time") ? cw.find(".app-edit-time").val() : 0,
				app_duration:$(".app_select_durations option:selected").val(),
				app_repeat:$(".app_select_repeat option:selected").val(),
				app_repeat_unit:$(".app_select_repeat_unit option:selected").val(),
				app_repeat_unit_alt:$(".app_repeat_unit_alt").val(),
				app_seats:$(".app_select_seats option:selected").val(),
				app_coupon:$(".app-coupon-field-entry").val(),
				app_extras:$(".app_select_extras option:selected").map(function(){return this.value;}).get().join(","),
				app_disp_price:$(".app-disp-price").val(),
				app_payment_method:cw.find("input:radio[name='app_choose_gateway']").is(":checked") ? cw.find("input:radio[name='app_choose_gateway']:checked").val() : "",
				app_cc_submit:cw.find(".app-cc-submit") ? cw.find(".app-cc-submit").val() : 0,
				app_cc_data:JSON.stringify(app_cc_data),
				stripeToken:cw.find(".stripeToken") ? cw.find(".stripeToken").val() : "",
				paymillToken:cw.find(".paymillToken") ? cw.find(".paymillToken").val() : "",
				simplify_token:cw.find(".simplify_token") ? cw.find(".simplify_token").val() : "",
				_ajax_nonce:_app_.nonce
			};

			if (typeof post_udf_data == "undefined"){ post_udf_data={};}
			
			/* Add udf values to post data */
			cw.find("[class*=app-udf-field-entry]").each( function(i,v){
				var udf_name=$(this).attr("name");
				var udf_value='';
				if ($(this).hasClass("app-udf-checkbox")){
					udf_value=$(this).is(":checked")?1:0;
				}
				else{
					udf_value=$(this).val();
				}
				post_udf_data[udf_name]=udf_value;
			});
			$.extend(post_data,post_udf_data);
			
			if (typeof post_lop_data == "undefined"){ post_lop_data={};}
			
			/* Add lop values to post data */
			cw.find("input[class*=app-lop-text]").each( function(i,v){
				var lop_name=$(this).attr("name");
				var lop_value=$(this).val();
				post_lop_data[lop_name]=lop_value;
			});
			$.extend(post_data,post_lop_data);

			return post_data;
		}

		/* Clicking on any book button or cell */
		$(document).on( "click", 
			".free.daily,.app_schedule_wrapper table td.free:not(.app_select_wh),.app_schedule_wrapper table td.waiting,.app_timetable div.free, .app_timetable div.waiting,.app-book-now-button, .app-book-flex-button", 
			handle_clicks 
		);
		/* Pre confirmation helper */
		function handle_clicks(e,last_clicked){
			if (at_checkout || $(this).hasClass("app-disabled-button")){return false;}
			if ( parseInt(_app_.login_not_met)>0 ){return false;}
			if (pre_process){return false;}
			
			pre_process=true;
			$(document).trigger("pre-process");
			
			var cl_slot_prev = $(document).data("last_clicked_slot");

			var cl_slot = last_clicked ? last_clicked : $(this);
			if ( typeof cl_slot === "undefined" ) {
				cl_slot = $(e.currentTarget);
			}
			// Quit if clicked on admin
			if ( parseInt( cl_slot.parents(".appointments-wrapper-admin").length ) > 0 ){return false;}
			
			$(document).data("last_clicked_slot", cl_slot);
			if ( typeof selected_service === "undefined" ) {selected_service=_app_.def_service;}
			if ( parseInt(cl_slot.parents(".app_monthly_schedule_wrapper").length)>0 && !cl_slot.hasClass("daily") ){return false;}
			
			_app_.updating('preparing_form');
			
			app_value = get_app_value();
			if ( !has_cart() && !last_clicked ) {
				app_value = [];
			}
			var deleted_value = "";
			var api = cl_slot.qtip("api");
			
			var old_content = typeof api != "undefined" && typeof api.get == "function" ? api.get('content.text') : "";
			if ( typeof $(this).data("app_selected")==="undefined" || $(this).data("app_selected")==0 ){
				if (!has_cart()){
					cl_slot.siblings().css("opacity","1");
				}
				cl_slot.css("opacity",_app_.opacity).data("app_selected",1);
				if (old_content) {
					api.set('content.text', old_content.replace(_app_.click_hint,_app_.click_to_remove));
				}
				if ( cl_slot.hasClass("app-book-now-button") ){cl_slot.addClass("app-disabled-button");}
				app_value.push($(this).find(".app_get_val").val());
			}
			else {
				cl_slot.css("opacity","1").data("app_selected",0);
				if (old_content) {
					api.set('content.text', old_content.replace(_app_.click_to_remove,_app_.click_hint));
				}
				deleted_value = cl_slot.find(".app_get_val").val();
				var index_ = app_value.indexOf(deleted_value);
				if (index_ > -1 ) {
					var test = app_value.splice(index_,1);
				}
			}			
			if ( app_value.length === 0 ) {
				_app_.disable_button();
			}
			else {
				_app_.enable_button();
			}
			var cw = $(document).find(".app-conf-wrapper").first();
			var pre_data = read_form(cw);
			pre_data.action = "pre_confirmation";
			pre_data.value = app_value;
			pre_data.deleted_value = deleted_value;			
		
			$.post(_app_.ajax_url, pre_data, function(r) {
				pre_process=false;
				
				if ( r ) {
					if ( r.error ) {
						if (r.start) {
							$(".app-conf-start").html(r.start);
						}
						if (r.end) {
							$(".app-conf-end").html(r.end);
						}
						if (r.lasts) {
							$(".app-conf-lasts").html(r.lasts).show();
						}						
						if (r.price) {
							$(".app-conf-price").html(r.price).show();
						}
						if (r.remove_last && Array.isArray(app_value)){
							var removed = app_value.pop();
						}
						_app_.open_dialog({
								confirm_text:r.error,
								confirm_title:_app_.error_short,
								refresh:false, 
								price:999
						});
						// app_value = [];
						cl_slot.css("opacity","1");
						$(document).trigger("pre-conf-error");
					}
					else {
						if ( cl_slot_prev ) {
							cl_slot_prev.removeClass("busy").addClass("free").css("opacity","1");
						}
						handle_pre_conf_reply( r );
					}
				}
				else {
					alert(_app_.con_error);
				}
			},"json");
		}
		
		/* Evaluate pre confirmation reply if there is no error */
		function handle_pre_conf_reply( r ){
			start_countdown( r );
			app_value = r.new_value;
			
			if (r.blocked){
				$.each(r.blocked, function(i,v){
					var slots = $(".app_get_val[value="+v+"]").parents(".free");
					slots.removeClass("free").addClass("busy");
				});
			}
			
			var cl_slot = typeof $(document).data("last_clicked_slot") ? $(document).data("last_clicked_slot") : false;
			var cw = $(".app-conf-wrapper");
			if ( cl_slot ) {
				var el = cl_slot.parents(".appointments-wrapper").siblings(".app-conf-wrapper");
				if ( typeof el !="undefined" && el.length ) {
					cw = el;
				}
			}
			add_sup_mark();
			cw.show();
			
			$(document).trigger("app-conf-wrapper-opened");
			$(".app-conf-location").html(r.loc);
			if (r.loc_js){eval(r.loc_js);}
			$(".app-conf-category").html(r.category);
			$(".app-conf-service").html(r.service);
			if (r.worker){
				$(".app-conf-worker").html(r.worker).show();
				if (mobile){$(".app-conf-worker").enhanceWithin();}
			}
			else{
				$(".app-conf-worker").hide();
			}
			if (r.worker_js){eval(r.worker_js);}
			$(".app-conf-start").html(r.start);
			$(".app-conf-end").html(r.end);
			if(r.lasts){$(".app-conf-lasts").html(r.lasts).show();}
			$(".app-conf-details").html(r.cart_contents).show();
			if(r.price){$(".app-conf-price").html(r.price).show();}
			$(".app-disp-price").val(r.disp_price);
			
			tt_regular_price();
			tt_discounted_price();

			// $(".app-conf-deposit").html(r.deposit);
			if (r.deposit){$(".app-conf-deposit").html(r.deposit).show();}else{$(".app-conf-deposit").hide();}
			if (r.amount){$(".app-conf-amount").html(r.amount).show();}else{$(".app-conf-amount").hide();}
			if (r.seats){$(".app-conf-seats").html(r.seats).show().enhanceWithin();}
			if (r.coupon){$(".app-conf-coupon").html(r.coupon).show();}
			if (r.extra){
				$(".app-conf-extra").html(r.extra).show();
				$(".app_select_extras").multiselect({selectedList:3, classes:"app_extras"}).enhanceWithin();
			}
			if (r.extra_js){eval(r.extra_js);}
			
			if( typeof user_fields=="undefined"){user_fields={};}
			$.each(user_fields, function( i, v ) {
				if (r[v]=="ask"){$(".app-"+v+"-field").show().enhanceWithin();}
			});						
			if (r.note =="ask"){$(".app-note-field").show();}
			if (r.udf =="ask"){
				$.each( r.udf_values, function( key, value ) {
					$(".app-udf-field-"+value).show().enhanceWithin();
				});
			}
			if (r.remember =="ask"){$(".app-remember-field").show();}
			if (r.payment =="ask"){
				if ( _app_.active_gateways.length > 1 ) {
					$(".app-payment-field").show().enhanceWithin();
				}
				if ( !$("input:radio[name=app_choose_gateway]:checked").val() ) {_app_.disable_button();}
			}
			if (r.additional =="ask"){$(".app-additional-field").show().enhanceWithin();}
			
			if (r.lop){$(".app-lop").html(r.lop).show().enhanceWithin();}
			
			$.each( $(".app-conf-wrapper fieldset div"), function(){
				if ( $.trim($(this).html()) == "" ){$(this).addClass("app_mb0");}
				else{$(this).removeClass("app_mb0");}
			});
			
			update_elements("pre-conf");
		
			var els = cw.find("input,textarea,select").addClass("ui-shadow ui-btn-"+swatch).enhanceWithin();
			
			var target = false;
			var focus_el = false;
			if (has_cart()) {
				if( cl_slot ) {
					target = cw.find(".app-cont-btn");
				}
				else {
					target = $(".app-conf-fields-gr");
				}
				focus_el = cw.find("input:visible,select:visible").first();
			}
			else {
				target = cw.find("input:visible,select:visible").first();
				focus_el = target;
			}
			if (target && typeof app_value !=="undefined" && app_value.length !== 0) {
				$.scrollTo(target,{duration:1000, axis:"y", offset:-1*parseInt(_app_.offset), 
					onAfter:function(){
							if (parseInt(_app_.allow_focus)>0){
								focus_el.focus();
							}
						}
					}
				);
			}
		}

		/* Update price after seats field or workers or any select field change, or coupon entered */
		function update_price( $this ) {
			var cw = $this.parents(".app-conf-wrapper");
			var editing = parseInt(cw.find(".app_editing_value").val());
			if (editing){return false;}
			if ( $this.parents(".app_gateway_form").length > 0 ){return false;}
			$(document).trigger("update-price");
			_app_.updating('calculating');
			$this.attr("disabled",true);
			var worker_pulldown = $this.parents().find("select.app_select_workers_conf");
			app_value = get_app_value();
			if ( worker_pulldown.length ) {
				var sel_worker = worker_pulldown.val();
				// update app_value - This cannot be multiple apps, so we can take the 1st element
				if ( app_value.length > 0 ) {
					var temp = app_value[0].split("_");
					temp[4]=sel_worker;			
					app_value[0]=temp.join("_");
				}
			}
			var pre_data = read_form(cw);
			pre_data.action = "pre_confirmation_update"; 
			pre_data.value = app_value;

			$.post(_app_.ajax_url, pre_data, function(r) {
				$this.attr("disabled",false);
				if ( r ) {
					if ( r.error ) {
						alert(r.error);
					}
					else {
						var coupon = $this.hasClass("app-coupon-field-entry") ? true: false;
						$('.app_new_price').qtip("destroy");
						tt_discounted_price(coupon);
						$(".app-conf-price").html(r.price);
						$(".app-disp-price").val(r.disp_price);
						if (r.amount){
							$(".app-conf-amount").html(r.amount).show();
						}
						update_elements();						
					}
				}
				else {
					alert(_app_.con_error);
				}
			},"json");			
			
		}

		/* Update price after seats field or workers or any select field or radio button change */
		$(document).on("change", ".app-seats-field-entry, .app_select_workers_conf, .app-conf-wrapper select, .app-conf-wrapper input[type=radio]", function(){
			$(this).parents("label").removeClass("ui-state-error");
			update_price( $(this) );
		});
		
		/* Update price after coupon entered */
		$(document).on("blur", ".app-coupon-field-entry", function(){
			var $this = $(this);
			if ($this.val() || $(document).data("coupon_value_entered") ) {
				$(document).data("coupon_value_entered",true);
				update_price( $this );
			}
		});

		/* A field set as price entry will trigger update price */
		$(document).on("blur", ".app-price-field-entry", function(){
			$(this).parents("label").removeClass("ui-state-error");
			update_price( $(this) );
		});
		
		/* In multiple bookings, when continue button is clicked scroll up to last click point */
		$(document).on( "click", ".app-cont-btn", function(){
			if (!has_cart()){return false;}
			var lcs = $(document).data("last_clicked_slot");
			var target = lcs ? lcs : $(".free:first");
			goTo(target);
		});
		
		/* In multiple bookings, delete selected booking */
		/* It can also be deleted by the clicked slot - see handle_clicks */
		$(document).on( "click", ".app-remove-cart-item", function() {
			var $this = $(this);
			if ($this.hasClass("removed")){return false;}
			if (pre_process){return false;}
			
			_app_.updating();
			pre_process=true;
			app_value = get_app_value();
			var app_id = $this.data("app_id");
			var net_val = $this.data("value");
			var index_ = app_value.indexOf(net_val);			
			if ( index_ > -1 ) {app_value.splice(index_,1);}
			if ( app_value.length === 0 ) {_app_.disable_button();}
			
			var cw = $this.parents(".app-conf-wrapper");
			var pre_data = read_form(cw);
			pre_data.action = "pre_confirmation_update"; 			
			pre_data.value = app_value;
			pre_data.deleted_value = net_val;
			
			var qtip_text = $this.qtip( "option", "content.text" );
			var new_content = String(qtip_text).replace(_app_.click_to_remove,_app_.removed);
			$this.qtip( "option", "content.text", new_content );
			$this.parent().css("opacity",_app_.opacity).css("text-decoration","line-through");
			$(document).find( "input[value='"+net_val+"']" ).parent().css("opacity",1).parent("button").css("opacity",1);
			$.post(_app_.ajax_url, pre_data, function(r) {
				pre_process=false;
				if (r && !r.error) {
					if (r.disp_price){$(".app-disp-price").val(r.disp_price);}
					if (r.start){$(".app-conf-start").html(r.start);}
					if (r.end){$(".app-conf-end").html(r.end);}
					if (r.lasts){$(".app-conf-lasts").html(r.lasts).show();}
					if (r.amount){$(".app-conf-amount").html(r.amount);}
					if (r.price){$(".app-conf-price").html(r.price).show();}
					if (r.deposit){$(".app-conf-deposit").html(r.deposit);}
					$this.addClass("removed");
				}
				else if (r.error) {
					_app_.open_dialog({
						confirm_text:r.error,
						confirm_title:_app_.error_short,
						refresh:false, 
						price:999
					});
				}
				else {
					alert(_app_.con_error);
				}
			},"json");
		});

		/* Select gateways */
		/* By clicking on the image */
		$(document).on("click",".app-payment-gateway-item a", function(){
			$(this).parent().find("input:radio").attr("checked", "checked");
			if ( $("input:radio[name=app_choose_gateway]:checked").val() ) {
				_app_.enable_button();
			}
		});
		/* By checking the checkbox */
		$(document).on("change","input:radio[name='app_choose_gateway']", function(){
			if ( $(this).is(":checked") ) {
				$(this).parents().find(".app_gateway_form").hide();
				var sel_gateway = $(this).val();
				$(this).parents().find("."+sel_gateway).show();
				app_value = get_app_value();
				if ( app_value.length > 0 ) {
					_app_.enable_button();
				}
			}
		});	

		/* Give an alert if a required field is missing */
		function field_alert($this){
			if ($this.hasClass("app-udf-checkbox") || $this.hasClass("app-select") ){
				$this.parent().addClass("app-missing-field");
			}
			else {
				$this.addClass("app-missing-field");
			}
			$this.parents("label").addClass("ui-state-error");
			var h = window.innerHeight;
			var first_cl = $this.attr('class').split(" ")[0];
			first_cl = first_cl ? first_cl+'-warning' : '';
			// Special warning text for field, e.g. _app_.app-email-field-entry-warning
			var confirm_text = first_cl && typeof _app_[first_cl] !="undefined" ? _app_[first_cl] : _app_.warning_text;
			$.scrollTo($this,{duration:500, axis:"y", offset:-1*(parseInt(h/2))+100, 
				onAfter:function(){
						_app_.open_dialog({
							confirm_text:confirm_text,
							confirm_title:_app_.error_short,
							refresh:false, 
							price:999,
							f_alert: true
						});
				}
			});
			return false;
		}
		
		/* Remove # from url so that page can be refreshed */
		function app_location() {
			var loc = window.location.href;
			var index = loc.indexOf("#");
			if (index > 0) {
			loc = loc.substring(0, index);
			}
			return String(loc);
		}
		
		/* Refreshing page and if set, redirection after confirmation */
		refresh_after_confirm = function(r){
			rurl = _app_.refresh_url ? _app_.refresh_url : app_location(); 
			if ( r.refresh_url && parseInt(r.refresh_url) == -1 ){return false;}
			else {
				_app_.updating('refreshing');
				if ( r.refresh_url ) { window.location.href=r.refresh_url; }
				else { window.location.href=rurl; }
			}
		};
		
		/* Open popup for mobile */
		_app_.open_popup = function(msg,$this){
			$( "#app-msg-popup" ).find(".ui-content h3").text(msg);
			$( "#app-msg-popup" ).popup().popup("open");
		};
		
		/* Open confirmation dialog */
		_app_.open_dialog = function(r){
			if (mobile){
				_app_.open_popup(r.confirm_text);
				return;
			}
			var confirm_wrapper = $("<div class='app-conf-dialog-content' data-dialog='true' ></div>");
			var w = window.innerWidth;
			var dwidth = 0;
			if (w > 600) {dwidth = 600;}
			else{ dwidth= w-30;}
			confirm_wrapper.html(r.confirm_text).dialog({
				title:r.confirm_title,
				closeOnEscape: false,
				modal:parseInt(_app_.modal)>0 ? true : false,
				width: dwidth,
				buttons: [
					{	text: _app_.close,
						click: function() {
							$(this).dialog("close");
							$("body").removeClass("stop-scrolling");
							if( r.refresh || r.price==0 ) {
								refresh_after_confirm(r);
							}				
						}
					}
				],
				open: function(event, ui) {
					$("body").addClass("stop-scrolling");
				},				  
				close: function(event, ui) {
					$("body").removeClass("stop-scrolling");
					if( r.refresh || r.price==0 ) {
						refresh_after_confirm(r);
					}
				},				  
				position: { my: "center top-"+parseInt(_app_.dialog_offset), at: "center top", of: window },
				draggable:false,
				dialogClass: "app-conf-dialog",
				hide: hide_effect,
				show: show_effect
			}).parent(".ui-dialog").css({"border-radius": "10px 10px 10px 10px","box-shadow": "0 0 25px 5px #999"});
			confirm_wrapper.data( "uiDialog" )._title = function(title) {
				title.html( this.options.title );
			};
			var icon = r.icon ? r.icon : 'info';
			confirm_wrapper.dialog('option', 'title', '<span class="ui-icon ui-icon-'+icon+'"></span> '+r.confirm_title);
			$(document).trigger("app-conf-dialog-opened");			
		};
		
		/* Checkout - post confirmation */
		$(document).on("click",".app-conf-button", function(){
			if ($(this).hasClass("app-disabled-button")){return false;}
			var cw = $(this).parents(".app-conf-wrapper");
			$(document).trigger("app-checkout-pre");
			/* Check if udf active and required udf fields filled */
			var fields_filled = true;
			$.each(cw.find(".app-required"), function( i, v ) {
				var $this = $(this);
				$this.removeClass("app-missing-field");
				$this.parent().removeClass("app-missing-field");
				$this.parents("label").removeClass("ui-state-error");
				var type = $this.prop("type");
				if ( "text"==type || "textarea"==type ) {
					if ( ""==$.trim($this.val()) ) {
						field_alert($this);
						fields_filled = false;
						return false;
					}
				}
				else if ( "radio"==type || "checkbox"==type ) {
					if ( !$this.is(":checked") ) {
						field_alert($this);
						fields_filled = false;
						return false;
					}
				}
				else if ( $this.is("select") ) {
					if ( !$this.val() ) {
						field_alert($this);
						fields_filled = false;
						return false;
					}
				}
			});
			if ( !fields_filled ) {return false;}			
			
			var post_data = read_form(cw);
		
			/* Check if standard user fields are filled */
			var broken = false;
			$.each(fields, function( i, v ) {
				$(this).removeClass("app-missing-field");
				$(this).parents("label").removeClass("ui-state-error");
				var field = cw.find(".app-"+v+"-field");
				var field_i = cw.find(".app-"+v+"-field-entry");
				var field_v = field_i.val();
				if ( field.is(":visible") && $.trim(field_v)==""){
					field_i.parents("label").addClass("ui-state-error");
					field_i.bind("focus", function(){
						$(this).parents("label").removeClass("ui-state-error");
					});
					field_alert(field_i);
					broken = true;
					return false;
				}
			});
			if (broken){
				return false;
			}

			_app_.disable_button();
			
			$(".app-conf-button").after("<span class='app_blink'>"+_app_.please_wait+"</span>");
			$(".app-conf-cancel-button").attr("disabled",true);
			
			if (app_mp_active){marketpress.loadingOverlay('show');}
			else{_app_.updating('booking');}
			
			$.each(cw.find(".app-missing-field"), function(){
				$(this).removeClass("app-missing-field");
				$(this).parents("label").removeClass("ui-state-error");
			});
			 
			$.post(_app_.ajax_url, post_data, function(r) {
				var target = false;
				$(document).trigger("app-conf-dialog-before-open");
				$(".app-conf-cancel-button").attr("disabled",false);
			if (app_mp_active){marketpress.loadingOverlay('hide');}
				if ( r && r.error ) {
					$(".app_blink").remove();
					if (r.goTo){
						target = cw.find("."+r.goTo);
						if (target) {
							target.addClass("app-missing-field");
							target.parents("label").addClass("ui-state-error");
							goTo(target);
						}
					}
					_app_.open_dialog({
						confirm_text:r.error,
						confirm_title:r.confirm_title ?  r.confirm_title : _app_.error_short,
						refresh:false, 
						price:999,
						hide: hide_effect,
						show: show_effect
					});
					$(".app-disp-price").val(r.disp_price);
					if (r.price){
						$(".app-conf-price").html(r.price);
						$(".app-conf-price").fadeIn(500).fadeOut(500).fadeIn(500).fadeOut(500).fadeIn(500);
					}
					_app_.enable_button();
				}
				else if ( r && ( parseInt(r.refresh)==1 || ( parseInt(r.price)==0 && !r.wc && !r.mp ) ) ) {
					pause_countdown();
					
					$(".app_blink").remove();
					
					if ( !r.confirm_text ) {
						var received_msg = r.is_editing ? _app_.edited : _app_.received;
						if (mobile){
							_app_.open_popup(received_msg);
							$( "#app-msg-popup" ).on({
							   popupafterclose: function(event, ui) {
								   refresh_after_confirm(r);
							   }
							});
						}
						else {
							alert(received_msg);
							refresh_after_confirm(r);
						}
					}
					else {
						_app_.open_dialog(r);
					}
				}
				else if ( r ) {
					pause_countdown();
					if (r.form) {
						at_checkout = true;
						var cc_form = $(".app_gateway_form");
						cc_form.html(r.form);
						var cc_par = cc_form.parents(".app-conf-wrapper");
						cc_par.find("div").not(".app-conf-fields-gr,.app_gateway_form,.app_billing_line,.app_billing_line_inner,.app-conf-buttons").hide();
						cc_par.find("legend").text(_app_.cc_legend);
						cc_par.find(".app-conf-button .ui-button-text").html(r.f_amount);
						if ( r.method == "paypal-standard" || r.method == "paypal-express" ||r.method == "2checkout" ) {
							cc_par.before("<span class='app_blink'>"+_app_.please_wait+"</span>");
							cc_par.css("visibility","hidden");
							goTo($(".app_blink"));
						}
						else {
							$(".app_blink").remove();
							_app_.enable_button();
							if (mobile){cc_form.find("select").selectmenu().selectmenu("refresh",true);}
							cc_form.show().enhanceWithin();
							target = cc_form.find("input:visible:first");
							goTo(target);
						}
						add_sup_mark();
					}
					if (r.blocked){
						$.each(r.blocked, function(i,v){
							var slots = $(".app_get_val[value="+v+"]").parents(".free");
							slots.removeClass("free").addClass("busy");
						});
					}
					if ( r.mp > 0 ) {
						$(".app_blink").remove();
						$(".app-conf-wrapper").hide();
						if ( r.mp == 1 ) {
							$(".mp_buy_form").find("[name='variation']").val(r.variation);
							$(document).find(".mp_buy_form:has(input[name='action'])").trigger("submit");
						}
						else {
							var app_id = r.variation;
							var mp_form = $(document).find(".mp_form-buy-product");
							var var_select = mp_form.find( 'select[name^="product_attr_1"]' );
							if ( parseInt(var_select.length) == 1 ) {
								var_select.val(app_id);
								var $option = $("<option></option>").attr("value",app_id).attr("selected","selected");
								var_select.append($option);
								var_select.parents(".mp_form-buy-product").submit();
							}
							else {
								mp_form = $(document).find(".mp_form-buy-product").first();
								mp_form.find("[name='product_id']").val(app_id);
								mp_form.submit();
							}
						}
						if ( r.refresh_url ) {
							_app_.updating('checkout');
							setTimeout(function() {
								refresh_after_confirm(r);
							},2000);							
						}
						else {
							target = $(".mp_cart_widget").length > 0 ? $(".mp_cart_widget") : ($(".mp_widget_cart").length > 0 ? $(".mp_widget_cart") : $(".app-sc").first());
							goTo(target);
						}
					}
					else if ( r.wc > 0 ) {
						$(".app_blink").remove();
						$(".app-conf-wrapper").hide();
						var wc_form = $(document).find(".variations_form");
						wc_form.find('input[name="variation_id"], input.variation_id').val(r.variation);
						$(document).find(".single_add_to_cart_button").removeClass("disabled wc-variation-selection-needed").click();
						if ( r.refresh_url ) {
							_app_.updating('checkout');
							setTimeout(function() {
								refresh_after_confirm(r);
							},2000);							
						}
						else {
							target = $(".woocommerce.widget_shopping_cart").length > 0 ? $(".woocommerce.widget_shopping_cart") : $(".app-sc").first();
							goTo(target);
						}
					}
					else if ( r.method && (r.method == "stripe" || r.method == "paymill" || r.method == "simplify") ){
						if ( r.step && r.step == "generate-token"){
							_app_.updating('checkout');
							$(".app-conf-wrapper").trigger("app-execute-"+r.method);
						}
					}
					else if ( r.method && (r.method == "paypal-standard" || r.method == "paypal-express" || r.method == "2checkout")){
						_app_.updating('checkout');						
						$(".app-"+r.method+"-form").submit();
					}
				}
				else{alert(_app_.con_error);}
			},"json");

		});	
		
		/* Paypal Express - Move to confirmation */
		if ( $.urlParam('app_ask_confirm') && 2==parseInt($(".app_editing_value").val()) ) {
			$(".app-conf-fields-gr2").hide();
			goTo($(".app-conf-wrapper"));
		}
		
		/* Login */
		function create_app_login_interface ($me) {
			if ($("#app-login_links-wrapper").length) {
				$("#app-login_links-wrapper").remove();
			}
			$me.parents('.appointments-login').after('<div id="app-login_links-wrapper" />');
			var $root = $("#app-login_links-wrapper");
			var methods = _app_.login_methods;
			var fb_li =	($.inArray('Facebook',methods) > -1) ? '<li><a href="javascript:void(0)" class="app-login_link app-login_link-facebook">' + _app_.facebook + '</a></li>' : '';
			var tw_li =	($.inArray('Twitter',methods) > -1) ? '<li><a href="javascript:void(0)" class="app-login_link app-login_link-twitter">' + _app_.twitter + '</a></li>' : '';
			var google_li = ($.inArray('Google+',methods) > -1) ? ( 
				_app_.gg_client_id 
				? '<li><span id="signinButton"> <span class="g-signin" data-callback="app_google_plus_login_callback" data-clientid="' + _app_.gg_client_id + '" data-cookiepolicy="single_host_origin" data-scope="profile email"> </span> </span></li>'
				: '<li><a href="javascript:void(0)" class="app-login_link app-login_link-google">' + _app_.google + '</a></li>') : '';
			var wp_li =	($.inArray('WordPress',methods) > -1) ? '<li><a href="javascript:void(0)" class="app-login_link app-login_link-wordpress">' + _app_.wordpress + '</a></li>' : '';
			$root.html(
				'<ul class="app-login_links">' +
					fb_li + tw_li + google_li + wp_li +
					'<li class="app_login_submit"><input type="text" class="app_username" placeholder="'+_app_.username+'"/>' +
					'<input type="password" class="app_password" placeholder="'+_app_.password+'"/>' +
					'<button class="app-login_link app-login_link-submit ui-button ui-btn ui-state-default">' + _app_.submit + '</button></li>' +
					'<li><button class="app-login_link app-login_link-cancel app-cancel-button ui-button ui-btn ui-state-default">' + _app_.cancel + '</button></li>' +
				'</ul>'
			);
			_app_.style_buttons();
			$root.find(".app-login_link").each(function () {
				var $lnk = $(this);
				var callback = false;
				if ($lnk.is(".app-login_link-facebook")) {
					// Facebook login
					callback = function () {
						FB.login(function (resp) {
							if (resp.authResponse) {
								_app_.updating('logging_in');
								var user_id = resp.authResponse.userID;
								var token = resp.authResponse.accessToken;
								FB.api("/me", {fields: "name,email"}, function(response) {
									$.post(_app_.ajax_url, {
										"action": "app_facebook_login",
										"task": "front_end_login",
										"user_id": user_id,
										"token": token,
										"name": response.name,
										"email": response.email
									}, function (data) {
										var status = 0;
										try { status = parseInt(data.status); } catch (e) { status = 0; }
										if (!status) { // ... handle error
											$root.remove();
											return false;
										}
										if ( data.status && data.status==1 ) { 
											$(".appointments-login_inner").text(_app_.logged_in);
											window.location.href = window.location.href;
											window.location.reload();
										}
										else {
											alert(_app_.con_error);
										}
									}, "json");
								});
							}
						}, {scope: 'email'});
						return false;
					};
				} else if ($lnk.is(".app-login_link-twitter")) {
					callback = function () {
						var twLogin = window.open('', "twitter_login", "scrollbars=no,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,height=400,width=600");
						twLogin.document.write(_app_.please_wait || "Please wait...");
						$.post(_app_.ajax_url, {
							"action": "app_get_twitter_auth_url",
							"post_id": _app_.post_id,
							"url": window.location.toString()
						}, function (data) {
							var href = data.url,
								cback = function () {
									$(twLogin).off("unload", cback);
									var tTimer = setInterval(function () {
										try {
											if (twLogin.location.hostname == window.location.hostname) {
												clearInterval(tTimer);
												twLogin.close();
												var location = twLogin.location;
												var search = '';
												try { search = location.search; } catch (e) { search = ''; }
												clearInterval(tTimer);
												twLogin.close();
												_app_.updating('logging_in');
												$.post(_app_.ajax_url, {
													"action": "app_twitter_login",
													"task": "front_end_login",
													"secret": data.secret,
													"data": search
												}, function (data) {
													var status = 0;
													try { status = parseInt(data.status, 10); } catch (e) { status = 0; }
													if (!status) { // ... handle error
														$root.remove();
														return false;
													}
													if ( data.status && data.status==1 ) {
														$(".appointments-login_inner").text(_app_.logged_in);
														window.location.href = window.location.href;
														window.location.reload();
													}
													else {
														alert(_app_.error);
													}
												}, "json");
											}
										} catch (e) {}
									}, 300);
								}
							;
							$(twLogin).on("unload", cback);
							twLogin.location = href;
						}, "json");
						return false;
					};
				} 
				else if ($lnk.is(".app-login_link-google")) {
					callback = function () {
						var googleLogin = window.open('https://www.google.com/accounts', "google_login", "scrollbars=no,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no,copyhistory=no,height=400,width=800");
						$.post(_app_.ajax_url, {
							"action": "app_get_google_auth_url",
							"url": window.location.href
						}, function (data) {
							var href = data.url;
							googleLogin.location = href;
							var gTimer = setInterval(function () {
								try {
									if (googleLogin.location.hostname == window.location.hostname) {
										clearInterval(gTimer);
										googleLogin.close();
										_app_.updating('logging_in');
										$.post(_app_.ajax_url, {
											"action": "app_google_login",
											"task": "front_end_login"
										}, function (data) {
											var status = 0;
											try { status = parseInt(data.status); } catch (e) { status = 0; }
											if (!status) { // ... handle error
												$root.remove();
												$me.click();
												return false;
											}
											if ( data.status && data.status==1 ) { 
												$(".appointments-login_inner").text(_app_.logged_in);
												window.location.href = window.location.href;
												window.location.reload();
											}
											else {
												alert(_app_.con_error);
											}
										});
									}
								} catch (e) {}
							}, 300);
						}, "json");
						return false;
					};
				}
				else if ($lnk.is(".app-login_link-wordpress")) {
					// Pass on to wordpress login
					callback = function () {
						$(".app_login_submit").show();
						return false;
					};
				} else if ($lnk.is(".app-login_link-submit")) {
					callback = function () {
						$(".app-error").remove();
						_app_.updating('logging_in');
						$.post(_app_.ajax_url, {
								"action": "app_ajax_login",
								"task": "front_end_login",
								"log": $lnk.parents(".app_login_submit").find(".app_username").val(),
								"pwd": $lnk.parents(".app_login_submit").find(".app_password").val(),
								"rememberme": 1
							}, function (data) {
								var status = 0;
								try { status = parseInt(data.status); } catch (e) { status = 0; }
								if (!status) { // ... handle error
									$lnk.after('<div class="app-error">'+data.error+'</div>');
									return false;
								}
								if ( data.status && data.status==1 ) { 
										$(".appointments-login_inner").text(_app_.logged_in);
										window.location.href = window.location.href;
										window.location.reload();
									}
								else {
									alert(_app_.con_error);
								}
							}, "json"
						);
					};
				} else if ($lnk.is(".app-login_link-cancel")) {
					// Drop entire thing
					callback = function () {
						$root.remove();
						return false;
					};
				}
				if (callback) {
					$lnk
					.unbind('click')
					.bind('click', callback);}
			});
			if (_app_.gg_client_id && "undefined" !== typeof gapi && "undefined" !== typeof gapi.signin) {gapi.signin.go();}
		}

		function signinCallback(authResult) {
			if (authResult['status']['signed_in']) {
				$.post(_app_.ajax_url, {
					"action": "app_google_plus_login",
					"token": authResult['access_token']
				}, function (data) {
					window.location.href = window.location.href;
					window.location.reload();
				}, "json");
			}
		}

		// Init Login
		$(function () {
			$(document).on("click", ".appointments-login_show_login",function(e){
				if ( parseInt(_app_.login_methods.length) >0 ){
					e.preventDefault();
					create_app_login_interface($(this));
					if (_app_.gg_client_id) {
						window.app_google_plus_login_callback = signinCallback;
						(function() {
							var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
							po.src = 'https://apis.google.com/js/client:plusone.js';
							var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
						})();
					}
				}
				else{
					if (confirm(_app_.redirect)){window.location.href=_app_.login_url;}
					else{return false;}
				}
				return false;
			});

		});

		$.each($(".widget_appointments_shortcode"), function(i,v){
			if ( $.trim($(this).text) =="" ){
				$(this).css("display","none");
			}
		});

		$(document).trigger("app-common-loaded");

		$(function() {
			if ( typeof $.fn.countdown !== "undefined" ) {
				$.countdown.regionalOptions['wp-base'] = {
					labels: _app_.countdown_pl,
					labels1: _app_.countdown_sin,
					compactLabels: ['y', 'm', 'w', 'd'],
					whichLabels: null,
					timeSeparator: ':', isRTL:  _app_.is_rtl};
				$.countdown.setDefaults($.countdown.regionalOptions['wp-base']);
			}
		});

	});
/* _app_ properties and methods are public, rest is private */	
}( window._app_ = window._app_ || {}, jQuery ));
