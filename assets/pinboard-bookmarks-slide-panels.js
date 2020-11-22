/**
 * Pinboard Bookmarks javascript for sliding panels in the admin UI.
 * This file is a modified version of Category Posts Widget's js file from @kometschuh
 * released under GPLv2 or later.
 *
 * @package PinboardBookmarks
 * @since 1.15
 */

// The namespace.
var pinboard_bookmarks_namespace = {
	// Holds an array of open panels per wiget id.
	open_panels : {},
	// Generic click handler on the panel title.
	clickHandler: function( element ) {
		// Open the div "below" the h4 title.
		jQuery( element ).toggleClass( 'open' ).next().stop().slideToggle();
		// Get the data-panel attribute, for example "pinboard-bookmarks-retrieving".
		var panel = element.getAttribute( 'data-panel' );
		// 1st LEVEL PANELS: Get the id of the widget, for example "widget-44_pinboard_bookmarks_widget-2".
		var id = jQuery( element ).parent().parent().parent().parent().parent().parent().attr( 'id' );
		// 2nd LEVEL (CHILD) PANELS: Get the id of the widget, for example "widget-44_pinboard_bookmarks_widget-2".
		/* if ( id === undefined ) {
			var id = jQuery( element ).parent().parent().parent().parent().parent().parent().parent().attr( 'id' );
		} */
		// 3rd LEVEL (CHILD) PANELS: Get the id of the widget, for example "widget-44_pinboard_bookmarks_widget-2".
		/* if ( id === undefined ) {
			var id = jQuery( element ).parent().parent().parent().parent().parent().parent().parent().parent().attr( 'id' );
		} */
		var o = {};
		if ( this.open_panels.hasOwnProperty( id ) ) {
			o = this.open_panels[id];
		}
		if ( o.hasOwnProperty( panel ) ) {
			delete o[panel];
		} else {
			o[panel] = true;
		}
		this.open_panels[id] = o;
	}
}

jQuery( document ).ready( function() {
	// Open/close the widget panel.
	jQuery( '.pinboard-bookmarks-widget-title' ).click( function() {
		pinboard_bookmarks_namespace.clickHandler( this );
	});

	// After saving the widget, we need to reassign click handlers.
	jQuery( document ).on( 'widget-added widget-updated', function( root, element ) {
		jQuery( '.pinboard-bookmarks-widget-title' ).off( 'click' ).on( 'click', function() {
			pinboard_bookmarks_namespace.clickHandler( this );
		});
		// Refresh panels to the state before saving.
		var id = jQuery( element ).attr( 'id' );
		if ( pinboard_bookmarks_namespace.open_panels.hasOwnProperty( id ) ) {
			var o = pinboard_bookmarks_namespace.open_panels[id];
			for ( var panel in o ) {
				jQuery( element ).find( '[data-panel=' + panel + ']' ).toggleClass( 'open' ).next().stop().show();
			}
		}
	});
});
