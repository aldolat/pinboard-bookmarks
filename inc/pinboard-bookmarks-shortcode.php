<?php
/**
 * The shortcode of Pinboard Bookmarks plugin.
 *
 * @since 1.0.0
 * @package PinboardBookmarks
 */

/**
 * Prevent direct access to this file.
 *
 * @since 1.0
 */
if ( ! defined( 'WPINC' ) ) {
	exit( 'No script kiddies please!' );
}

/**
 * The function to be used for the shortcode.
 *
 * @param array $atts The array containing the custom parameters.
 * @example [pbsc username="johndoe" display_desc=1 display_archive=0]
 * @since 1.0
 */
function pinboard_bookmarks_sc( $atts ) {
	$atts = shortcode_atts( pinboard_bookmarks_get_defaults(), $atts );

	return get_pinboard_bookmarks_fetch_feed( $atts );
}
if ( ! shortcode_exists( 'pbsc' ) ) {
	add_shortcode( 'pbsc', 'pinboard_bookmarks_sc' );
}
