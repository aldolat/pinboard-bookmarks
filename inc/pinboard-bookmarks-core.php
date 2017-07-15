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
 * It retrieves the feed and display the content.
 *
 * @since 1.0
 * @param mixed $args Variables to customize the output of the function
 * @return mixed
 */
function get_pinboard_bookmarks_fetch_feed( $args ) {
	$defaults = array(
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
        'display_source'   => false,
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
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );

    // If a username or a tag has not been entered, stop the function and give an alert.
    if ( ! $username && ! $tags ) {
        echo '<p class="pinboard-bookmarks error">';
        esc_html_e( 'You have not properly configured the widget. Please, add a username or a tag at least.', 'pinboard-bookmarks' );
        echo '</p>';
        return;
    }

    // Set up some variables.
    if ( $display_arrow )   $arrow         = '&nbsp;<span class="pinboard-bookmarks-arrow">&rarr;</span>'; else $arrow        = '';
    if ( $new_tab )         $new_tab_link  = ' target="_blank"';                                           else $new_tab_link = '';
    if ( $nofollow )        $rel_txt       = ' rel="bookmark nofollow"';                                   else $rel_txt      = ' rel="bookmark"';
    if ( 400 < $quantity )  $quantity      = 400;
    if ( '' == $quantity )  $quantity      = 5;

    // Set up the Pinboard URLs.
    $pinboard_url                 = trailingslashit( 'https://pinboard.in' );
    $pinboard_tag_url             = $pinboard_url . 't:';

    // Set up the user URLs on Pinboard.
    $pinboard_user_url            = trailingslashit( $pinboard_url . 'u:' . $username );
    $pinboard_user_tag_url        = $pinboard_user_url . 't:';
    $pinboard_user_source_url     = $pinboard_user_url . 'from:';

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
    if ( $username ) {
        $feed_url = trailingslashit( $pinboard_rss_user_url . $tags_for_url ) . '?count=' . $quantity;
        $archive_url = trailingslashit( $pinboard_user_url . $tags_for_url );
        if ( $source ) {
            $feed_url = trailingslashit( $pinboard_rss_user_source_url . $source ) . '?count=' . $quantity;
            $archive_url = trailingslashit( $pinboard_user_source_url . $source );
        }
    } else {
        $feed_url = trailingslashit( $pinboard_rss_url . $tags_for_url ) . '?count=' . $quantity;
        $archive_url = trailingslashit( $pinboard_url . $tags_for_url );
    }

    // Grab the feed from Pinboard.
    add_filter( 'wp_feed_cache_transient_lifetime', 'pinboard_bookmarks_cache_handler' );
	include_once( ABSPATH . WPINC . '/feed.php' );
    $rss = fetch_feed( esc_url( $feed_url ) );
	remove_filter( 'wp_feed_cache_transient_lifetime', 'pinboard_bookmarks_cache_handler' );

    // Start building the $output variable.
    switch  ( $list_type ) {
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
	$output = '<' . $list_element . ' class="pinboard-bookmarks-list">';

	if ( is_wp_error( $rss ) ) {
		$output .= '<li class="pinboard-bookmarks-li pinboard-bookmarks-error">';
			$output .= sprintf( esc_html__( 'There was a problem with your feed! The error is %s', 'pinboard-bookmarks' ), '<code>' . $rss->get_error_message() . '</code>' );
		$output .= '</li>';
        $output .= '</ul>';
        return $output;
	}

	if ( $quantity > 400 ) $quantity = 400;
    // Define the maximum number of retrievable items (for example, I want 100 items but only 20 are available, so $maxitems will be 20).
	$maxitems = $rss->get_item_quantity( $quantity );
    // Get the items from 0 to $maxitems.
	$rss_items = $rss->get_items( 0, $maxitems );
	if ( $maxitems == 0 ) {
		$output .= '<li class="pinboard-bookmarks-li pinboard-bookmarks-no-items">';
			$output .= esc_html__( 'No items.', 'pinboard-bookmarks' );
		$output .= '</li>';
	} else {
		if ( $random ) shuffle( $rss_items );
		foreach ( $rss_items as $item ) {
			$output .= '<li class="pinboard-bookmarks-li">';

				// Title
				$output .= '<p class="pinboard-bookmarks-title">';
					$output .= '<a class="pinboard-bookmarks-title-link"' . $rel_txt . ' href="' . esc_url( $item->get_permalink() ) . '"' . $new_tab_link . '>';
						$output .= esc_html( $item->get_title() ) . $arrow;
					$output .= '</a>';
				$output .= '</p>';

				// Description
				if ( $display_desc ) {
					if ( $item->get_description() ) {
						if ( $truncate > 0 ) {
							$output .= '<p class="pinboard-bookmarks-desc">';
								$output .= wp_trim_words( esc_html( $item->get_description() ), $truncate, '&hellip;' );
							$output .= '</p>';
						} else {
							$output .= '<p class="pinboard-bookmarks-desc">' . esc_html( $item->get_description() ) . '</p>';
						}
					}
				}

				// Date
				if ( $display_date ) {
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
						if ( $date_text ) $output .= $date_text . ' ';
						$output .= '<a class="pinboard-bookmarks-date-link"' . $rel_txt . ' href="' . esc_url( $item->get_id() ) . '"' . $new_tab_link . '>';
							$output .= $bookmark_date;
						$output .= '</a>';
					$output .= '</p>';
				}

                // Tags
				if ( $display_tags ) {
					$tags_list = (array) $item->get_categories();
                    if ( $tags_list ) {
						$output .= '<p class="pinboard-bookmarks-tags">';

						if ( $tags_text ) $output .= $tags_text . ' ';
						if ( $display_hashtag ) $hashtag = '<span class="pinboard-bookmarks-hashtag">#</span>'; else $hashtag = '';
                        if ( $username ) $url = $pinboard_user_tag_url; else $url = $pinboard_tag_url;
                        if ( $use_comma ) $comma = ', '; else $comma = ' ';

						foreach( $tags_list as $tag ) {
                            $item_tags = $tag->get_label();
                            $item_tags = (array) explode( ' ', $item_tags );
                            foreach ( $item_tags as $item_tag ) {
								$output .= $hashtag . '<a class="pinboard-bookmarks-tag"' . $rel_txt . ' href="' . esc_url( $url . strtolower( $item_tag ) . '/' ) . '"' . $new_tab_link . '>' .  esc_attr( $item_tag ) . '</a>' . $comma;
                            }
                            // Removes the trailing comma and space in any quantity and any order after the last tag.
                            $output = rtrim( $output, ', ' );
						}

                        /*
                         * Append the source of the bookmark, like Pocket, Instapaper, Twitter.
                         *
                         * @since 1.4
                         */
                        if ( $display_source && ! empty( $source ) ) {
                            $output .= $comma . '<a class="pinboard-bookmarks-source"' . $rel_txt . ' href="' . $archive_url . '">from ' . ucfirst( $source ) . '</a>';
                        }

						$output .= '</p>';
                    }
				}

			$output .= '</li>';

		}
	}

	$output .= '</' . $list_element . '>';

    // The archive link.
	if ( ! is_wp_error( $rss ) && $display_archive ) {
		if ( $display_arch_arr ) $arrow = '&nbsp;<span class="pinboard-bookmarks-arrow">&rarr;</span>'; else $arrow = '';
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
			$output .= '<a class="pinboard-bookmarks-archive-link"' . $rel_txt . ' href="' . esc_url( $url_to_archive ) . '"' .  $new_tab_link . '>';
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
 * Echo the core function
 *
 * @since 1.0
 */
function pinboard_bookmarks_fetch_feed( $args ) {
	echo get_pinboard_bookmarks_fetch_feed( $args );
}
