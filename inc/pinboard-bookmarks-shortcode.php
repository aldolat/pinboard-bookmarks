<?php
/**
 * The shortcode
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
 * @since 1.0
 */
function pinboard_bookmarks_sc( $atts ) {
	extract( shortcode_atts( array(
        'username'         => '',
        'tags'             => '',
        'source'           => '', // This is the source in Pinboard, like 'from:pocket', 'from:instapaper', or 'from:twitter'.
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
		'display_arrow'    => false,
		'display_archive'  => true,
		'archive_text'     => esc_html__( 'See the bookmarks on Pinboard', 'pinboard-bookmarks' ),
        'list_type'        => 'bullet',
		'display_arch_arr' => true,
		'new_tab'          => false,
		'nofollow'         => true,
        'admin_only'       => true,
        'debug_options'    => false,
        'debug_urls'       => false
	), $atts ) );

	return get_pinboard_bookmarks_fetch_feed( $atts );
}
if ( ! shortcode_exists( 'pbsc' ) ) {
	add_shortcode( 'pbsc', 'pinboard_bookmarks_sc' );
}
