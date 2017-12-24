<?php
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
 *
 * @param  array $args {
 *      The array containing the custom parameters.
 *
 * @type string $intro_text The introductory text for the widget.
 * @type string $username The username on Pinboard.
 * @type string $tags The tags where to get bookmarks from.
 * @type boolean $source The Pinboard 'source' where to get bookmarks from.
 *                                      Default empty. Accepts 'pocket', 'instapaper'.
 * @type integer $quantity The number of bookmarks to fetch.
 * @type boolean $random If fetched bookmarks should be displayed in random order.
 * @type boolean $display_desc If the description of the bookmark should be displayed.
 * @type integer $truncate Truncate the description of the bookmark at this words number.
 * @type boolean $display_date If the date of the bookmarks should be displayed.
 * @type boolean $display_time If the time of the bookmarks should be displayed.
 * @type string $date_text The text to be prepended before the date/time.
 * @type boolean $display_tags If the tags of the bookmarks should be displayed.
 * @type boolean $tags_text The text to be prepended before the tags.
 * @type boolean $display_hashtag If the hashtag `#` should be displayed.
 * @type boolean $use_comma If a comma should be displayed between tags.
 * @type boolean $display_source If the source of the bookrmark should be displayed.
 * @type boolean $display_arrow If an HTML arrow should be displayed after the bookmark.
 * @type boolean $display_archive If the link to the archive on Pinboard should be displayed.
 * @type boolean $archive_text The text to be prepended before the archive link.
 * @type boolean $list_type The HTML list type. Default 'bullet' (ul). Accepts 'numbered' (ol).
 * @type boolean $display_arch_arr If an HTML arrow should be displayed after the archive link.
 * @type boolean $new_tab If links should be opened ina new browser tab.
 * @type boolean $nofollow If a 'nofollow' attribute should be added in links.
 * @type boolean $admin_only If administrators only can view the debug.
 * @type boolean $debug_options If debug informations should be displayed.
 * @type boolean $debug_urls If URLs used by the plugin should be displayed for debug.
 * }
 * @return string $output The HTML structure to be displayed on the page
 */
function get_pinboard_bookmarks_fetch_feed( $args ) {
	$defaults = array(
		// 'title'            => esc_html__( 'My bookmarks on Pinboard', 'pinboard-bookmarks' ), /* FOR WIDGET ONLY */
		'intro_text'       => '',
		'username'         => '',
		'tags'             => '',
		'source'           => '',
		// This is the source in Pinboard. Can be 'from:pocket' or 'from:instapaper'.
		'quantity'         => 5,
		'random'           => false,
		'display_desc'     => false,
		'truncate'         => 0,
		'display_date'     => false,
		'display_time'     => false,
		'date_text'        => esc_html__( 'Stored on:', 'pinboard-bookmarks' ),
		'display_tags'     => false,
		'item_parts_order' => "",
		'tags_text'        => esc_html__( 'Tags:', 'pinboard-bookmarks' ),
		'display_hashtag'  => true,
		'use_comma'        => false,
		'display_source'   => false,
		'display_arrow'    => false,
		// 'time'             => 1800, /* FOR WIDGET ONLY */
		'display_archive'  => true,
		'archive_text'     => esc_html__( 'See the bookmarks on Pinboard', 'pinboard-bookmarks' ),
		'list_type'        => 'bullet',
		'display_arch_arr' => true,
		'new_tab'          => false,
		'nofollow'         => true,
		'admin_only'       => true,
		'debug_options'    => false,
		'debug_urls'       => false
	);
	$args     = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );

	// If $username is empty, stop the function and give an alert.
	if ( empty( $username ) ) {
		$output_error = '<p class="pinboard-bookmarks pinboard-bookmarks-error">';
		$output_error .= esc_html__( 'You have not properly configured the widget. Please, add a username.', 'pinboard-bookmarks' );
		$output_error .= '</p>';

		return $output_error;
	}

	// Set up some variables.
	if ( $display_arrow ) {
		$arrow = '&nbsp;<span class="pinboard-bookmarks-arrow">&rarr;</span>';
	} else {
		$arrow = '';
	}
	if ( $new_tab ) {
		$new_tab_link = ' target="_blank"';
	} else {
		$new_tab_link = '';
	}
	if ( $nofollow ) {
		$rel_txt = ' rel="bookmark nofollow"';
	} else {
		$rel_txt = ' rel="bookmark"';
	}
	if ( 400 < $quantity ) {
		$quantity = 400;
	}
	if ( '' == $quantity ) {
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

	// Set up item parts order
	$item_parts_manual_ordering = false;
	if ( strlen( $item_parts_order ) > 0 ) {
		$item_parts_manual_ordering = true;
		$item_parts_order = strtoupper( $item_parts_order );
		$item_parts_order = str_replace( " ", "", $item_parts_order );
		$order = array_unique( explode( ",", $item_parts_order ) );
	} else {
		$order = [ "TITLE", "DESC", "DATE", "TAGS" ];
	}


	// Build the tags list.
	if ( $tags ) {
		$tags_for_url = pinboard_bookmarks_get_tags_for_url( $tags );
	} else {
		$tags_for_url = '';
	}

	// Build the RSS and archive URLs.
	$feed_url    = trailingslashit( $pinboard_rss_user_url . $tags_for_url ) . '?count=' . $quantity;
	$archive_url = trailingslashit( $pinboard_user_url . $tags_for_url );
	if ( $source ) {
		$feed_url    = trailingslashit( $pinboard_rss_user_source_url . $source ) . '?count=' . $quantity;
		$archive_url = trailingslashit( $pinboard_user_source_url . $source );
	}

	// Grab the feed from Pinboard.
	add_filter( 'wp_feed_cache_transient_lifetime', 'pinboard_bookmarks_cache_handler' );
	include_once( ABSPATH . WPINC . '/feed.php' );
	$rss = fetch_feed( esc_url( $feed_url ) );
	remove_filter( 'wp_feed_cache_transient_lifetime', 'pinboard_bookmarks_cache_handler' );

	/*
	 * Define the main variable that will concatenate all the output.
	 *
	 * @since 1.6.0
	 */
	$output = '';

	// The introductory text
	if ( $intro_text ) {
		$output .= '<p class="pinboard-bookmarks-intro-text">' . $intro_text . '</p>';
	}

	// Start building the $output variable.
	switch ( $list_type ) {
		case 'bullet' :
			$list_element = 'ul';
			break;

		case 'number' :
			$list_element = 'ol';
			break;

		default :
			$list_element = 'ul';
			break;
	}
	$output .= '<' . $list_element . ' class="pinboard-bookmarks-list">';

	if ( is_wp_error( $rss ) ) {
		$output .= '<li class="pinboard-bookmarks-li pinboard-bookmarks-error">';
		$output .= sprintf( esc_html__( 'There was a problem with your feed! The error is %s', 'pinboard-bookmarks' ), '<code>' . $rss->get_error_message() . '</code>' );
		$output .= '</li>';
		$output .= '</ul>';

		return $output;
	}

	if ( $quantity > 400 ) {
		$quantity = 400;
	}
	// Define the maximum number of retrievable items (for example, I want 100 items but only 20 are available, so $maxitems will be 20).
	$maxitems = $rss->get_item_quantity( $quantity );
	// If the feed is empty
	if ( $maxitems == 0 ) {
		$output .= '<li class="pinboard-bookmarks-li pinboard-bookmarks-no-items">';
		$output .= esc_html__( 'No items.', 'pinboard-bookmarks' );
		$output .= '</li>';
	} else {
		// Get the items from 0 to $maxitems.
		$rss_items = $rss->get_items( 0, $maxitems );
		// Shuffle items if required.
		if ( $random ) {
			shuffle( $rss_items );
		}
		// Start the loop
		foreach ( $rss_items as $item ) {
			$output .= '<li class="pinboard-bookmarks-li">';

			foreach ( $order as $next ) {
				switch ( $next ) {
					case "TITLE":
						// Title
						$output .= get_title_output( $rel_txt, $item, $new_tab_link, $arrow );
						break;
					case "DESC":
						// Description
						if ( $item_parts_manual_ordering || $display_desc ) {
							$output .= get_description_output( $item, $truncate );
						}
						break;
					case "DATE":
						// Date
						if ( $item_parts_manual_ordering || $display_date ) {
							$output .= get_date_output( $display_time, $item, $date_text, $rel_txt, $new_tab_link );
						}
						break;
					case "TAGS":
						// Tags
						if ( $item_parts_manual_ordering || $display_tags ) {
							$output .= get_tags_output( $item, $tags_text, $display_hashtag, $pinboard_user_tag_url, $use_comma, $rel_txt, $new_tab_link, $display_source, $pinboard_user_source_url );
						}
						break;
					default:
						// User supplied an undefined item part. Ignore.
						// Log?
				}
			}


			$output .= '</li>';

		}
	}

	$output .= '</' . $list_element . '>';

	// The archive link.
	if ( ! is_wp_error( $rss ) && $display_archive ) {
		if ( $display_arch_arr ) {
			$arrow = '&nbsp;<span class="pinboard-bookmarks-arrow">&rarr;</span>';
		} else {
			$arrow = '';
		}
		$output .= '<p class="pinboard-bookmarks-archive">';
		if ( $maxitems == 0 ) {
			if ( $username ) {
				$url_to_archive = $pinboard_user_url;
			} else {
				$url_to_archive = $pinboard_url;
			}
		} else {
			$url_to_archive = $archive_url;
		}
		$output .= '<a class="pinboard-bookmarks-archive-link"' . $rel_txt . ' href="' . esc_url( $url_to_archive ) . '"' . $new_tab_link . '>';
		$output .= esc_html( $archive_text ) . $arrow;
		$output .= '</a>';
		$output .= '</p>';
	}

	// The debugging informations.
	if ( $debug_options || $debug_urls ) {
		$params = array(
			'debug_options' => $debug_options,
			'debug_urls'    => $debug_urls,
			'options'       => $args,
			'urls'          => array(
				'username_part'     => $username,
				'tags_part'         => $tags_for_url,
				'complete_feed_url' => $feed_url,
				'archive_url'       => $archive_url,
				'items_retrieved'   => $maxitems,
			),
		);
		$output .= pinboard_bookmarks_debug( $params );
	}

	// Add a HTML comment with plugin name and version.
	$output .= pinboard_bookmarks_get_generated_by();

	return $output;
}

/**
 * @param $item
 * @param $tags_text
 * @param $display_hashtag
 * @param $pinboard_user_tag_url
 * @param $use_comma
 * @param $rel_txt
 * @param $new_tab_link
 * @param $display_source
 * @param $pinboard_user_source_url
 *
 * @return string
 */
function get_tags_output( $item, $tags_text, $display_hashtag, $pinboard_user_tag_url, $use_comma, $rel_txt, $new_tab_link, $display_source, $pinboard_user_source_url ): string {
	$tempoutput = '';
	if ( true ) {
		$tags_list = (array) $item->get_categories();
		if ( $tags_list ) {
			$tempoutput .= '<p class="pinboard-bookmarks-tags">';

			if ( $tags_text ) {
				$tempoutput .= $tags_text . ' ';
			}
			if ( $display_hashtag ) {
				$hashtag = '<span class="pinboard-bookmarks-hashtag">#</span>';
			} else {
				$hashtag = '';
			}
			$url = $pinboard_user_tag_url;
			if ( $use_comma ) {
				$comma = ', ';
			} else {
				$comma = ' ';
			}

			foreach ( $tags_list as $tag ) {
				$item_tags = $tag->get_label();
				$item_tags = (array) explode( ' ', $item_tags );
				foreach ( $item_tags as $item_tag ) {
					$tempoutput .= $hashtag . '<a class="pinboard-bookmarks-tag"' . $rel_txt . ' href="' . esc_url( $url . strtolower( $item_tag ) . '/' ) . '"' . $new_tab_link . '>' . esc_attr( $item_tag ) . '</a>' . $comma;
				}
				// Removes the trailing comma and space in any quantity and any order after the last tag.
				$tempoutput = rtrim( $tempoutput, ', ' );
			}

			/*
			 * Append the source of the bookmark, like Pocket, Instapaper, Twitter.
			 *
			 * @since 1.4
			 */
			if ( $display_source ) {
				if ( $source_service = $item->get_item_tags( SIMPLEPIE_NAMESPACE_DC_11, 'source' ) ) {
					$source_service = $source_service[0]['data'];
					switch ( $source_service ) {
						case 'http://readitlater.com/':
							$source_name    = 'Pocket';
							$source_address = $pinboard_user_source_url . 'pocket';
							break;
						case 'http://instapaper.com/':
							$source_name    = 'Instapaper';
							$source_address = $pinboard_user_source_url . 'instapaper';
							break;
						/**
						 * Remove support for Twitter.
						 * Pinboard lets you fetch your tweets that:
						 * - have a link inside;
						 * - you liked and have a link inside.
						 * Pinboard then adds a "tag" depending on the type of tweet:
						 * `from twitter` (the first case) or `from twitter_favs` (the second one).
						 * So in Pinboard you have two separate pages for these bookmarks:
						 * - https://pinboard.in/u:username/from:twitter
						 * - https://pinboard.in/u:username/from:twitter_favs
						 * The problem is that, when Pinboard creates the RSS feed,
						 * there is no way to distinguish the first tweets from the second ones.
						 * In the feed you have only `<dc:source>http://twitter.com/</dc:source>`.
						 * In this situation we cannot link to the correct page.
						 *
						 * Code removed:
						 * case 'http://twitter.com/':
						 *    $source_name = 'Twitter';
						 *    $source_address = $pinboard_user_source_url . 'twitter';
						 *    break;
						 *
						 * @since 1.6.0
						 */
						// In some cases the source is Pinboard itself, so do not display it (also see some lines below).
						case 'http://pinboard.in/':
							$source_name    = 'Pinboard';
							$source_address = $pinboard_user_source_url . 'pinboard';
							break;
					}
					if ( 'Pinboard' != $source_name ) {
						$tempoutput .= $comma . '<a class="pinboard-bookmarks-source"' . $rel_txt . ' href="' . $source_address . '">from ' . $source_name . '</a>';
					}
				}
			}

			$tempoutput .= '</p>';
		}
	}

	return $tempoutput;
}

/**
 * @param $display_time
 * @param $item
 * @param $date_text
 * @param $rel_txt
 * @param $new_tab_link
 *
 * @return string
 */
function get_date_output( $display_time, $item, $date_text, $rel_txt, $new_tab_link ): string {
	$output = "";
	// Get date format
	$date_format = get_option( 'date_format' );
	// Get time format, if requested
	if ( $display_time ) {
		$time_format = ' ' . get_option( 'time_format' );
		$date_format .= $time_format;
	}
	// Convert date and time of the bookmark into a UNIX timestamp
	$item_timestamp = strtotime( esc_html( $item->get_date( $date_format ) ) );
	// Get local time offset
	$local_offset = get_option( 'gmt_offset' ) * 3600;
	// Since the bookmark on Pinboard is stored in UTC, convert item timestamp from UTC to local time
	$item_local_timestamp = $item_timestamp + $local_offset;
	// Get the final date and time of the item
	$bookmark_date = date_i18n( $date_format, $item_local_timestamp );
	// Build the final HTML
	$output .= '<p class="pinboard-bookmarks-date">';
	if ( $date_text ) {
		$output .= $date_text . ' ';
	}
	$output .= '<a class="pinboard-bookmarks-date-link"' . $rel_txt . ' href="' . esc_url( $item->get_id() ) . '"' . $new_tab_link . '>';
	$output .= $bookmark_date;
	$output .= '</a>';
	$output .= '</p>';

	return $output;
}

/**
 * @param $item
 * @param $truncate
 *
 * @return string
 */
function get_description_output( $item, $truncate ): string {
	$tempoutput = "";
	if ( $item->get_description() ) {
		if ( $truncate > 0 ) {
			$tempoutput .= '<p class="pinboard-bookmarks-desc">';
			$tempoutput .= wp_trim_words( esc_html( $item->get_description() ), $truncate, '&hellip;' );
			$tempoutput .= '</p>';
		} else {
			$tempoutput .= '<p class="pinboard-bookmarks-desc">' . esc_html( $item->get_description() ) . '</p>';
		}
	}

	return $tempoutput;
}

/**
 * @param $rel_txt
 * @param $item
 * @param $new_tab_link
 * @param $arrow
 *
 * @return string
 */
function get_title_output( $rel_txt, $item, $new_tab_link, $arrow ): string {
	$title_output = "";
	$title_output .= '<p class="pinboard-bookmarks-title">';
	$title_output .= '<a class="pinboard-bookmarks-title-link"' . $rel_txt . ' href="' . esc_url( $item->get_permalink() ) . '"' . $new_tab_link . '>';
	$title_output .= esc_html( $item->get_title() ) . $arrow;
	$title_output .= '</a>';
	$title_output .= '</p>';

	return $title_output;
}

/**
 * Echo the core function
 *
 * @since 1.0
 */
function pinboard_bookmarks_fetch_feed( $args ) {
	echo get_pinboard_bookmarks_fetch_feed( $args );
}
