jQuery(document).ready(function($) {
	alert("!");
	jQuery("#submitpost").on("click", "#publish, #save-post", function(e){
		alert("prevent");
		e.preventDefault();
	});
});


(function(document, $) {
	"use strict";
	var pageWidgets;

	pageWidgets = {

		sidebarWidgets: {},
		sidebar: '',
		originalSidebar: '',

		/**
		* Handle all of the events
		* @method init
		*/
		init : function init() {
			var the_id, rem, $first;

			//pageWidgets.sidebarWidgets = $.parseJSON(widgetsAdmin.sidebars_widgets.replace(/&quot;/g, '"'));

			// @todo: update height as widgets are added / expanded
			//$('.sidebar').css('height', $('.column-1').height());

			if($('.voce-post-meta-widget-drop.column-2 .description').size() === 0) { 
				//$('.voce-post-meta-widget-drop.column-2 .sidebar').html('<p class="description">Widgets in this area will be shown in the sidebar on the ' + widgetsAdmin.post_name + ' page.</p>');
			}

			$first = $('.voce-post-meta-widget-drop.column-2 .sidebar-list :first');
			pageWidgets.sidebar = $first.attr('id');
			pageWidgets.originalSidebar = $first.attr('data-sidebar');

			$('#sidebar_admin #widget-list').children('.widget').draggable({
				connectToSortable: '.sidebar',
				handle: '> .widget-top > .widget-title',
				helper: function() {
					return $(this).clone().css( 'width', $(this).css('width') );
				},
				zIndex: 5,
				start : function(e, ui) {
					ui.helper.find('.widget-description').hide();
					ui.helper.find('a.widget-action').removeAttr('href');
					the_id = this.id;
				},

				stop : function(e, ui) {
					if ( rem ){
						$(rem).hide();
					}
					rem = '';
				}
			});

			$('.voce-post-meta-widget-drop.column-2 .sidebar').droppable({
				accept: '.widget',
				drop: function(e, ui) {
				}
			});

			$('.voce-post-meta-widget-drop.column-2 .sidebar').sortable({
				placeholder: 'placeholder',
				items: '.widget',
				stop: function(e, ui) {

					var add = ui.item.find('input.add_new').val(),
					n = ui.item.find('input.multi_number').val(),
					id = the_id;

					ui.item.css({
						margin:'', 
						'width':''
					});
					the_id = '';

					if ( add ) {
						if ( 'multi' === add ) {
							ui.item.html( ui.item.html().replace(/<[^<>]+>/g, function(m){
								return m.replace(/__i__|%i%/g, n);
							}) );
							ui.item.attr( 'id', id.replace('__i__', n) );
							n++;
							$('div#' + id).find('input.multi_number').val(n);
						} else if ( 'single' === add ) {
							ui.item.attr( 'id', 'new-' + id );
							rem = 'div#' + id;
						}
						alert (ui.item);
						pageWidgets.save( ui.item, 0, 0, 1 );
						ui.item.find('input.add_new').val('');
						ui.item.find('a.widget-action').click();
						return;
					}

					pageWidgets.saveOrder();
				}
			});

			/**
			* When the close link is clicked on a widget or a sidebar
			* hide the widget-inside element.
			*/
			$('a.widget-control-close').live('click', function(e){
				e.preventDefault();
				pageWidgets.close($(this).closest('.widget'));
			});

			/**
			* When an avilable widget is clicked, show the description
			*/
			$('.voce-post-meta-widget .column-1 .widget-action').live('click', function(e) {
				e.preventDefault();
				$('.widget-description', $(this).closest('.widget')).slideToggle();
			});

			/**
			* When the arrown on an active widget is clicked, show the form options
			*/
			$('.voce-post-meta-widget-drop.column-2 .widget-action').live('click', function(e) {
				e.preventDefault();
				$('.widget-inside', $(this).closest('.widget')).slideToggle();
			});

			/**
			* When the delete link is clicked on a widget, remove it from
			* the DOM and save the sidebar's state.
			*/
			$('.voce-post-meta-widget-drop.column-2 .widget-control-remove').live('click', function(e) {
				e.preventDefault();
				// removing save action from when any action but save is clicked ...
				 pageWidgets.save($(this).closest('.widget'), 1, 1, 1);
				$(this).closest('.widget').remove();
			});

			/**
			* When the save button of an active widget is clicked, make an
			* AJAX request and save the form data.
			*/
			$('.voce-post-meta-widget-drop.column-2 .widget-control-save').live('click', function(e) {
				e.preventDefault();
			
				pageWidgets.save($(this).closest('.widget'), 0, 0, 0);
			});

			/**
			* When a sidebar is clicked set this objects sidebar and originalSidebar
			* attributes, and make an AJAX request to get the active widgets for that 
			* sidebar.
			*/
			$('.voce-post-meta-widget-drop.column-2 .sidebar-list').live('change', function(e) {
				e.preventDefault();
				var a;

				// Remove any widgets from the previous sidebar.
				$('.voce-post-meta-widget-drop.column-2 .widget').remove();

				// Set the object attributes
				pageWidgets.sidebar = $(this).find(':selected').attr('id');
				pageWidgets.originalSidebar = $(this).find(':selected').attr('data-sidebar');

				// Get the active widgets for this sidebar
				a = {
					action: 'get-active-widgets',
					sidebar: pageWidgets.sidebar
				};

				$.post(
					ajaxurl,
					a,
					function(data) {
						$('.voce-post-meta-widget-drop.column-2 .sidebar').html(data);
						if($('.voce-post-meta-widget-drop.column-2 .description').size() === 0) { 
							//$('.voce-post-meta-widget-drop.column-2 .sidebar').html('<p class="description">Widgets in this area will be shown in the sidebar on the ' + widgetsAdmin.post_name + ' page.</p>');
						}
					}
					);
			});


		},

		/**
		 *@method save
		 */
		save : function save(widget, del, animate, order) {
			alert ("saving");
			var data, a;
			data = widget.find('.widget-inside :input').serialize();

			$('.ajax-feedback', widget).css('visibility', 'visible');

			
			a = {
				action: 'save-widget',
				savewidgets: $('#_wpnonce_widgets').val(),
				sidebar: pageWidgets.sidebar
			};

			if(del){
				a.delete_widget = 1;
			}
			data += '&' + $.param(a);

			$.post(
				ajaxurl,
				data,
				function(data){
					$('.ajax-feedback').css('visibility', 'hidden');
				}
				);

			if(order){
				pageWidgets.saveOrder();
			}
		},

		/**
		* When calling the widgets-order AJAX action, you must post
		* ALL of the sidebars and widgets, not just the one(s) you are
		* updating.
		* @method saveOrder
		*/
		saveOrder : function saveOrder() {
			var a, sidebar;

			a = {
				action: 'widgets-order',
				savewidgets: $('#_wpnonce_widgets').val()
			};


			for(sidebar in pageWidgets.sidebarWidgets) {
				if( typeof pageWidgets.sidebarWidgets[sidebar] === "object" && pageWidgets.sidebarWidgets[sidebar] !== null) {
					a['sidebars[' + sidebar + ']'] = pageWidgets.sidebarWidgets[sidebar].join(',');
				}
			}

			pageWidgets.sidebarWidgets[pageWidgets.sidebar] = $($('.voce-post-meta-widget-drop.column-2 div.ui-sortable')).sortable('toArray');
			a['sidebars[' + pageWidgets.sidebar + ']'] = $($('.voce-post-meta-widget-drop.column-2 div.ui-sortable')).sortable('toArray').join(',');

			$.post(
				ajaxurl, 
				a, 
				function(data) {
				}
				);
		},

		/**
		 * @method close
		 */
		close : function close(widget) {
			widget.children('.widget-inside').slideUp('fast', function(){
				widget.css({
					'width':'', 
					margin:''
				});
			});
		}
	};

	$(document).ready(function(){
		pageWidgets.init();
	});

}(document, jQuery));
