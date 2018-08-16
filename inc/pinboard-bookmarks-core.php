<?php
/**
 * Pinboard Bookmarks plugin.
 *
 * @package WordPress
 * @subpackage Pinboard Bookmarks
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
 * The core function.
 * It retrieves the feed and displays the content.
 *
 * @since 1.0
 * @param array $args {
 *      The array containing the custom parameters.
 *
 *      @type string  $intro_text       The introductory text for the widget.
 *      @type string  $username         The username on Pinboard.
 *      @type string  $tags             The tags where to get bookmarks from.
 *      @type boolean $source           The Pinboard 'source' where to get bookmarks from.
 *                                      Default empty. Accepts 'pocket', 'instapaper'.
 *      @type integer $quantity         The number of bookmarks to fetch.
 *      @type boolean $random           If fetched bookmarks should be displayed in random order.
 *      @type boolean $display_desc     If the description of the bookmark should be displayed.
 *      @type integer $truncate         Truncate the description of the bookmark at this words number.
 *      @type boolean $display_date     If the date of the bookmarks should be displayed.
 *      @type boolean $display_time     If the time of the bookmarks should be displayed.
 *      @type string  $date_text        The text to be prepended before the date/time.
 *      @type boolean $display_tags     If the tags of the bookmarks should be displayed.
 *      @type boolean $tags_text        The text to be prepended before the tags.
 *      @type boolean $display_hashtag  If the hashtag `#` should be displayed.
 *      @type boolean $use_comma        If a comma should be displayed between tags.
 *      @type boolean $display_source   If the source of the bookmark should be displayed.
 *      @type boolean $display_arrow    If an HTML arrow should be displayed after the bookmark.
 *      @type boolean $display_archive  If the link to the archive on Pinboard should be displayed.
 *      @type boolean $archive_text     The text to be prepended before the archive link.
 *      @type boolean $list_type        The HTML list type. Default 'bullet' (ul). Accepts 'numbered' (ol).
 *      @type boolean $display_arch_arr If an HTML arrow should be displayed after the archive link.
 *      @type boolean $new_tab          If links should be opened ina new browser tab.
 *      @type boolean $nofollow         If a 'nofollow' attribute should be added in links.
 *      @type boolean $admin_only       If administrators only can view the debug.
 *      @type boolean $debug_options    If debug informations should be displayed.
 *      @type boolean $debug_urls       If URLs used by the plugin should be displayed for debug.
 * }
 * @return string $output The HTML structure to be displayed on the page
 */
function get_pinboard_bookmarks_fetch_feed( $args ) {
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
		'widget_id'        => '',
	);

	$args             = wp_parse_args( $args, $defaults );
	$intro_text       = $args['intro_text'];
	$username         = $args['username'];
	$tags             = $args['tags'];
	$source           = $args['source'];
	$quantity         = $args['quantity'];
	$random           = $args['random'];
	$display_desc     = $args['display_desc'];
	$truncate         = $args['truncate'];
	$display_date     = $args['display_date'];
	$display_time     = $args['display_time'];
	$date_text        = $args['date_text'];
	$display_tags     = $args['display_tags'];
	$tags_text        = $args['tags_text'];
	$display_hashtag  = $args['display_hashtag'];
	$use_comma        = $args['use_comma'];
	$display_source   = $args['display_source'];
	$display_arrow    = $args['display_arrow'];
	$display_archive  = $args['display_archive'];
	$archive_text     = $args['archive_text'];
	$list_type        = $args['list_type'];
	$display_arch_arr = $args['display_arch_arr'];
	$new_tab          = $args['new_tab'];
	$nofollow         = $args['nofollow'];
	$items_order      = $args['items_order'];
	$admin_only       = $args['admin_only'];
	$debug_options    = $args['debug_options'];
	$debug_urls       = $args['debug_urls'];
	$widget_id        = $args['widget_id'];

	// If $username is empty, stop the function and give an alert.
	if ( empty( $username ) ) {
		$output_error  = '<p class="pinboard-bookmarks pinboard-bookmarks-error">';
		$output_error .= esc_html__( 'You have not properly configured the widget. Please, add a username.', 'pinboard-bookmarks' );
		$output_error .= '</p>';
		return $output_error;
	}

	// Set up some variables.
	$display_arrow ? $arrow  = '&nbsp;<span class="pinboard-bookmarks-arrow">&rarr;</span>' : $arrow = '';
	$new_tab ? $new_tab_link = ' target="_blank"' : $new_tab_link = '';
	$nofollow ? $rel_txt     = ' rel="bookmark nofollow"' : $rel_txt = ' rel="bookmark"';
	if ( 400 < $quantity ) {
		$quantity = 400;
	}
	if ( '' === $quantity ) {
		$quantity = 5;
	}

	// Set up the Pinboard URLs.
	$pinboard_url = trailingslashit( 'https://pinboard.in' );

	// Set up the user URLs on Pinboard.
	$pinboard_user_url        = trailingslashit( $pinboard_url . 'u:' . $username );
	$pinboard_user_tag_url    = $pinboard_user_url . 't:';
	$pinboard_user_source_url = $pinboard_user_url . 'from:';

	// Set up the Pinboard RSS URLs.
	$pinboard_rss_url             = trailingslashit( 'https://feeds.pinboard.in/rss' );
	$pinboard_rss_user_url        = trailingslashit( $pinboard_rss_url . 'u:' . $username );
	$pinboard_rss_user_source_url = $pinboard_rss_user_url . 'from:';

	// Build the tags list.
	if ( $tags ) {
		$tags_for_url = pinboard_bookmarks_get_tags_for_url( $tags );
	} else {
		$tags_for_url = '';
	}

	// Build the RSS and archive URLs.
	if ( 400 < $quantity ) {
		$quantity = 400;
	}
	if ( $random ) {
		$feed_url = trailingslashit( $pinboard_rss_user_url . $tags_for_url ) . '?count=400';
	} else {
		$feed_url = trailingslashit( $pinboard_rss_user_url . $tags_for_url ) . '?count=' . $quantity;
	}
	$archive_url = trailingslashit( $pinboard_user_url . $tags_for_url );
	if ( $source ) {
		if ( $random ) {
			$feed_url = trailingslashit( $pinboard_rss_user_source_url . $source ) . '?count=400';
		} else {
			$feed_url = trailingslashit( $pinboard_rss_user_source_url . $source ) . '?count=' . $quantity;
		}
		$archive_url = trailingslashit( $pinboard_user_source_url . $source );
	}

	// Grab the feed from Pinboard.
	add_filter( 'wp_feed_cache_transient_lifetime', 'pinboard_bookmarks_cache_handler' );
	include_once ABSPATH . WPINC . '/feed.php';
	$rss = fetch_feed( esc_url( $feed_url ) );
	remove_filter( 'wp_feed_cache_transient_lifetime', 'pinboard_bookmarks_cache_handler' );

	/*
	 * Define the main variable that will concatenate all the output.
	 *
	 * @since 1.6.0
	 */
	$output = '';

	// The introductory text.
	if ( $intro_text ) {
		$output .= '<p class="pinboard-bookmarks-intro-text">' . wp_kses_post( $intro_text ) . '</p>';
	}

	// Start building the $output variable.
	switch ( $list_type ) {
		case 'bullet':
			$list_element = 'ul';
			break;

		case 'number':
			$list_element = 'ol';
			break;

		default:
			$list_element = 'ul';
	}
	$output .= '<' . $list_element . ' class="pinboard-bookmarks-list">';

	if ( is_wp_error( $rss ) ) {
		$output .= '<li class="pinboard-bookmarks-li pinboard-bookmarks-error">';
		// translators: %s is the feed error.
		$output .= sprintf( esc_html__( 'There was a problem with your feed! The error is %s', 'pinboard-bookmarks' ), '<code>' . $rss->get_error_message() . '</code>' );
		$output .= '</li>';
		$output .= '</' . $list_element . '>';
		return $output;
	}

	/*
	 * Define the maximum number of retrievable items.
	 * For example, I want 100 items but only 20 are available,
	 * so $maxitems will be 20).
	 */
	if ( $random ) {
		$maxitems = $rss->get_item_quantity( 400 );
	} else {
		$maxitems = $rss->get_item_quantity( $quantity );
	}
	// If the feed is empty.
	if ( 0 === $maxitems ) {
		$output .= '<li class="pinboard-bookmarks-li pinboard-bookmarks-no-items">';
		$output .= esc_html__( 'No items.', 'pinboard-bookmarks' );
		$output .= '</li>';
	} else {
		// Get the items from 0 to $maxitems.
		$rss_items = $rss->get_items( 0, $maxitems );
		// Shuffle items if required and slice the array according to the quantity defined by the user.
		if ( $random ) {
			shuffle( $rss_items );
			$rss_items = array_slice( $rss_items, 0, $quantity );
		}
		// The number of finally displayed items.
		$displayed_items = count( $rss_items );
		// Start the loop.
		foreach ( $rss_items as $item ) {
			$output .= '<li class="pinboard-bookmarks-li">';

			// Title part.
			$title_part  = '';
			$params      = array(
				'rel_txt'      => $rel_txt,
				'item'         => $item,
				'new_tab_link' => $new_tab_link,
				'arrow'        => $arrow,
			);
			$title_part .= pinboard_bookmarks_get_title( $params );

			// Description part.
			$description_part = '';
			if ( $display_desc ) {
				$params = array(
					'item'     => $item,
					'truncate' => $truncate,
				);

				$description_part .= pinboard_bookmarks_get_description( $params );
			}

			// Date part.
			$date_part = '';
			if ( $display_date ) {
				$params = array(
					'display_time' => $display_time,
					'item'         => $item,
					'date_text'    => $date_text,
					'rel_txt'      => $rel_txt,
					'new_tab_link' => $new_tab_link,
				);

				$date_part .= pinboard_bookmarks_get_date( $params );
			}

			// Tags part.
			$tags_part = '';
			if ( $display_tags ) {
				$params = array(
					'item'                     => $item,
					'tags_text'                => $tags_text,
					'display_hashtag'          => $display_hashtag,
					'pinboard_user_tag_url'    => $pinboard_user_tag_url,
					'use_comma'                => $use_comma,
					'rel_txt'                  => $rel_txt,
					'new_tab_link'             => $new_tab_link,
					'display_source'           => $display_source,
					'pinboard_user_source_url' => $pinboard_user_source_url,
				);

				$tags_part .= pinboard_bookmarks_get_tags( $params );
			}

			// Ordering item parts.
			if ( ! is_array( $items_order ) ) {
				$items_order = explode( ' ', $items_order );
			}
			foreach ( $items_order as $next ) {
				switch ( $next ) {
					case 'title':
						$output .= $title_part;
						break;
					case 'description':
						$output .= $description_part;
						break;
					case 'date':
						$output .= $date_part;
						break;
					case 'tags':
						$output .= $tags_part;
						break;
				}
			}

			$output .= '</li>';
		}
	}

	$output .= '</' . $list_element . '>';

	// The archive link.
	if ( ! is_wp_error( $rss ) && $display_archive ) {
		$params = array(
			'display_arch_arr'  => $display_arch_arr,
			'maxitems'          => $maxitems,
			'username'          => $username,
			'pinboard_user_url' => $pinboard_user_url,
			'pinboard_url'      => $pinboard_url,
			'archive_url'       => $archive_url,
			'rel_txt'           => $rel_txt,
			'new_tab_link'      => $new_tab_link,
			'archive_text'      => $archive_text,
		);

		$output .= pinboard_bookmarks_get_archive_link( $params );
	}

	// The debugging informations.
	if ( $debug_options || $debug_urls ) {
		$params = array(
			'admin_only'    => $admin_only,
			'debug_options' => $debug_options,
			'debug_urls'    => $debug_urls,
			'widget_id'     => $widget_id,
			'options'       => $args,
			'urls'          => array(
				'username_part'     => $username,
				'tags_part'         => $tags_for_url,
				'complete_feed_url' => $feed_url,
				'archive_url'       => $archive_url,
				'items_retrieved'   => $maxitems,
				'items_displayed'   => $displayed_items,
			),
		);

		$output .= pinboard_bookmarks_debug( $params );
	}

	// Add a HTML comment with plugin name and version.
	$output .= pinboard_bookmarks_get_generated_by();

	return $output;
}

/**
 * Echo the core function
 *
 * @param array $args The array containing the custom parameters.
 * @since 1.0
 */
function pinboard_bookmarks_fetch_feed( $args ) {
	echo get_pinboard_bookmarks_fetch_feed( $args );
}
