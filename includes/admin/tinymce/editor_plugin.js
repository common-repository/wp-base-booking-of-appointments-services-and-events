(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('wpbaseshortcodes');
	var wpb_adminpage = typeof adminpage != "undefined" ? adminpage : ""; 

	tinymce.create('tinymce.plugins.WPBasePlugin', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceChat');
			var selection = '';
			ed.addCommand('mceAppointmentBookings', function() {
				if ( typeof ed.selection.getContent() != 'undefined' ) {
					selection = ed.selection.getContent();
				}

				ed.windowManager.open({
					file : url + "../../../../../../../wp-admin/admin-ajax.php?action=appTinymceOptions&selection="+encodeURIComponent(selection)+"&adminpage="+wpb_adminpage,
					width : jQuery( window ).width() * 0.7,
					height : (jQuery( window ).height() - 36 - 50) * 0.7,
					inline : 1,
					title: ed.getLang('wpbaseshortcodes.tooltip')
				}, {
					plugin_url : url // Plugin absolute URL
				});
			});

			// Register button
			ed.addButton('wpbaseshortcodes', {
				title : ed.getLang('wpbaseshortcodes.title'),
				tooltip: ed.getLang('wpbaseshortcodes.tooltip'),
				cmd : 'mceAppointmentBookings',
				icon: 'icon dashicons-calendar'
				/* image : url + '/app-pro.png' */
			});

			// Add a node change handler, selects the button in the UI when a image is selected
			// ed.onNodeChange.add(function(ed, cm, n) {
				// cm.setActive('wpbaseshortcodes', n.nodeName == 'IMG');
			// });
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'WP BASE Booking of Appointments, Services and Events',
				author : 'Hakan Ozevin',
				authorurl : '',
				infourl : '',
				version : "3.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('wpbaseshortcodes', tinymce.plugins.WPBasePlugin);
})();
