<?php
/**
 * The shortcode of Pinboard Bookmarks plugin.
 *
 * @package PinboardBookmarks
 * @since 1.0
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
 * @since 1.0
 */
function pinboard_bookmarks_sc( $atts ) {
	$defaults = array(
		// 'title' is for widget only.
		'intro_text'       => '',
		'username'         => '',
		'tags'             => '',
		'source'           => '', // This is the source in Pinboard. Can be 'from:pocket' or 'from:instapaper'.
		'quantity'         => 5,
		'random'           => false,
		'display_desc'     => false,
		'truncate'         => 0,
		'display_date'     => false,
		'display_time'     => false,
		'date_text'        => esc_html__( 'Stored on:', 'pinboard-bookmarks' ),
		'display_tags'     => false,
		'tags_text'        => esc_html__( 'Tags:', 'pinboard-bookmarks' ),
		'display_hashtag'  => true,
		'use_comma'        => false,
		'display_source'   => false,
		'display_arrow'    => false,
		// 'time' is for widget only.
		'display_archive'  => true,
		'archive_text'     => esc_html__( 'See the bookmarks on Pinboard', 'pinboard-bookmarks' ),
		'list_type'        => 'bullet',
		'display_arch_arr' => true,
		'new_tab'          => false,
		'nofollow'         => true,
		'items_order'      => 'title description date tags',
		'admin_only'       => true,
		'debug_options'    => false,
		'debug_urls'       => false,
	);

	$atts = shortcode_atts( $defaults, $atts );

	return get_pinboard_bookmarks_fetch_feed( $atts );
}
if ( ! shortcode_exists( 'pbsc' ) ) {
	add_shortcode( 'pbsc', 'pinboard_bookmarks_sc' );
}
