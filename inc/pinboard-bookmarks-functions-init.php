<?php
/**
 * Pinboard Bookmarks general functions.
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
 * Returns the default options.
 *
 * $defaults contains the default parameters:
 *    string  $title            The title of the widget.
 *    string  $intro_text       The introductory text for the widget.
 *    string  $username         The username on Pinboard.
 *    string  $tags             The tags where to get bookmarks from.
 *    boolean $source           The Pinboard 'source' where to get bookmarks from.
 *                              Default empty. Accepts 'pocket', 'instapaper'.
 *    integer $quantity         The number of bookmarks to fetch.
 *    boolean $random           If fetched bookmarks should be displayed in random order.
 *    boolean $display_desc     If the description of the bookmark should be displayed.
 *    integer $truncate         Truncate the description of the bookmark at this words number.
 *    boolean $display_date     If the date of the bookmarks should be displayed.
 *    boolean $display_time     If the time of the bookmarks should be displayed.
 *    string  $date_text        The text to be displayed before the date/time.
 *    boolean $display_tags     If the tags of the bookmarks should be displayed.
 *    boolean $tags_text        The text to be displayed before the tags.
 *    boolean $display_hashtag  If the hashtag `#` should be displayed.
 *    boolean $use_comma        If a comma should be displayed between tags.
 *    boolean $display_source   If the source of the bookmark should be displayed.
 *    boolean $display_arrow    If an HTML arrow should be displayed after the bookmark.
 *    integer $time             Minimum time in seconds between two requests to Pinboard server.
 *    boolean $display_site_url If the original site base URL should be displayed.
 *    boolean $leave_domain     If the base URL of the original should be diaplayed only.
 *    string  $site_url_text    The text to be displayed before the original site URL.
 *    boolean $display_archive  If the link to the archive on Pinboard should be displayed.
 *    boolean $archive_text     The text to be used for the archive on Pinboard.
 *    boolean $list_type        The HTML list type.
 *                              Default 'bullet' (ul). Accepts 'numbered' (ol).
 *    boolean $display_arch_arr If an HTML arrow should be displayed after the archive link.
 *    boolean $new_tab          If links should be opened ina new browser tab.
 *    boolean $nofollow         If a 'nofollow' attribute should be added in links.
 *    string  $items_order      The order in which to display the items.
 *    boolean $admin_only       If administrators only can view the debug.
 *    boolean $debug_options    If debug informations should be displayed.
 *    boolean $debug_urls       If URLs used by the plugin should be displayed for debug.
 *    string  $widget_id        The ID of the widget.
 * }
 *
 * @since 1.7.5
 * @return array $defaults The default options.
 */
function pinboard_bookmarks_get_defaults() {
	$defaults = array(
		'title'            => esc_html__( 'My bookmarks on Pinboard', 'pinboard-bookmarks' ),
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
		'time'             => 1800,
		'display_site_url' => false,
		'leave_domain'     => false,
		'site_url_text'    => esc_html__( 'From:', 'pinboard-bookmarks' ),
		'display_archive'  => true,
		'archive_text'     => esc_html__( 'See the bookmarks on Pinboard', 'pinboard-bookmarks' ),
		'list_type'        => 'bullet',
		'display_arch_arr' => true,
		'new_tab'          => false,
		'nofollow'         => true,
		'noreferrer'       => true,
		'items_order'      => 'title site description date tags',
		'admin_only'       => true,
		'debug_options'    => false,
		'debug_urls'       => false,
		'widget_id'        => '',
	);

	return $defaults;
}

/**
 * Register the widget.
 *
 * @since 1.0
 */
function pinboard_bookmarks_load_widget() {
	register_widget( 'Pinboard_Bookmarks_Widget' );
}

/**
 * Load the CSS file.
 * The file will be loaded only in the widgets admin page.
 *
 * @param string $hook The hook where to load scripts.
 * @since 1.0
 * @since 1.15.0 Registered new javascript for sliding panels.
 */
function pinboard_bookmarks_load_scripts( $hook ) {
	if ( 'widgets.php' !== $hook ) {
		return;
	}

	// Register and enqueue the CSS file.
	wp_register_style(
		'pinboard_bookmarks_style',
		plugins_url( '../assets/pinboard-bookmarks-styles.css', __FILE__ ),
		array(),
		PINBOARD_BOOKMARKS_PLUGIN_VERSION,
		'all'
	);
	wp_enqueue_style( 'pinboard_bookmarks_style' );

	// Register and enqueue the JS file.
	wp_register_script(
		'pinboard_bookmarks_slide_panels_js',
		plugins_url( '../assets/pinboard-bookmarks-slide-panels.js', __FILE__ ),
		array( 'jquery' ),
		PINBOARD_BOOKMARKS_PLUGIN_VERSION,
		false
	);
	wp_enqueue_script( 'pinboard_bookmarks_slide_panels_js' );
}
