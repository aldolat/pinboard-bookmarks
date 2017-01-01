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
		'quantity'         => 5,
		'random'           => false,
		'display_desc'     => false,
		'truncate'         => 0,
		'display_date'     => false,
		'date_text'        => esc_html__( 'Stored on:', 'pinboard-bookmarks' ),
		'display_tags'     => false,
		'tags_text'        => esc_html__( 'Tags:', 'pinboard-bookmarks' ),
		'display_hashtag'  => true,
		'display_arrow'    => false,
		'display_archive'  => true,
		'archive_text'     => esc_html__( 'See the bookmarks on Pinboard', 'pinboard-bookmarks' ),
		'display_arch_arr' => true,
		'new_tab'          => false,
		'nofollow'         => true,
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );

    if ( ! $username && ! $tags ) {
        echo '<p class="pinboard-bookmarks error">';
        esc_html_e( 'You have not properly configured the widget. Please, add a username or a tag at least.', 'pinboard-bookmarks' );
        echo '</p>';
        return;
    }

    if ( $display_arrow )    $arrow        = '&nbsp;&rarr;';             else $arrow        = '';
    if ( isset( $new_tab ) ) $new_tab_link = ' target="_blank"';         else $new_tab_link = '';
    if ( $nofollow )         $rel_txt      = ' rel="bookmark nofollow"'; else $rel_txt      = ' rel="bookmark"';
    if ( 400 < $quantity )  $quantity      = 400;
    if ( '' == $quantity )  $quantity      = 5;

    $pinboard_url          = 'https://pinboard.in';
    $pinboard_rss_url      = 'https://feeds.pinboard.in/rss';
    $pinboard_tag_url      = $pinboard_url . '/t:';

    $pinboard_user_url     = $pinboard_url . '/u:' . $username;
    $pinboard_rss_user_url = $pinboard_rss_url . '/u:' . $username;
    $pinboard_user_tag_url = $pinboard_user_url . '/t:';

    // Build the tags list
    if ( $tags ) {
        // Pinboard accepts maximum 3 tags for a query
        if ( 3 < count( $tags ) ) {
            $tags_slice = array_slice( $tags_slice, 0, 3 );
            $tags = implode( ' ', $tags_slice );
        }
        $tags_for_url = pinboard_bookmarks_get_tags_for_url( $tags );
    } else {
        $tags_for_url = '';
    }

    // Build the RSS url
    if ( $username ) {
        $feed_url = $pinboard_rss_user_url . $tags_for_url . '/?count=' . $quantity;
        $archive_url = $pinboard_user_url . $tags_for_url;
    } else {
        $feed_url = $feed_url = $pinboard_rss_url . $tags_for_url . '/?count=' . $quantity;
        $archive_url = $pinboard_url . $tags_for_url;
    }

    // Grab the feed from Pinboard.
    add_filter( 'wp_feed_cache_transient_lifetime', 'pinboard_bookmarks_cache_handler' );
	include_once( ABSPATH . WPINC . '/feed.php' );
    $rss = fetch_feed( $feed_url );
	remove_filter( 'wp_feed_cache_transient_lifetime', 'pinboard_bookmarks_cache_handler' );

	$output = '<ul class="pinboard-bookmarks-list">';

	if ( is_wp_error( $rss ) ) {
		$output .= '<li class="pinboard-bookmarks-list-li error">';
			$output .= sprintf( esc_html__( 'There was a problem with your feed! The error is %s', 'pinboard-bookmarks' ), '<code>' . $rss->get_error_message() . '</code>' );
		$output .= '</li>';
        $output .= '</ul>';
        return $output;
	}

	if ( $quantity > 400 ) $quantity = 400;
	$maxitems  = $rss->get_item_quantity( $quantity );
	$rss_items = $rss->get_items( 0, $maxitems );
	if ( $maxitems == 0 ) {
		$output .= '<li class="pinboard-bookmarks-list-li">';
			$output .= esc_html__( 'No items.', 'pinboard-bookmarks' );
		$output .= '</li>';
	} else {
		if ( $random ) shuffle( $rss_items );
		foreach ( $rss_items as $item ) {
			$output .= '<li class="pinboard-bookmarks-list-li">';

				// Title
				$title = sprintf( esc_html__( 'Read &laquo;%s&raquo;', 'pinboard-bookmarks' ), $item->get_title() );

				$output .= '<p class="pinboard-bookmarks-list-title">';
					$output .= '<a' . $rel_txt . ' href="' . $item->get_permalink() . '" title="' . $title . '"' . $new_tab_link . '>';
						$output .= $item->get_title() . $arrow;
					$output .= '</a>';
				$output .= '</p>';

				// Description
				if ( $display_desc ) {
					if ( $item->get_description() ) {
						if ( $truncate > 0 ) {
							$output .= '<p  class="pinboard-bookmarks-list-desc">';
								$output .= wp_trim_words( $item->get_description(), $truncate, '&hellip;' );
							$output .= '</p>';
						} else {
							$output .= '<p  class="pinboard-bookmarks-list-desc">' . $item->get_description() . '</p>';
						}
					}
				}

				// Date
				if ( $display_date ) {
					$bookmark_date = date_i18n( get_option( 'date_format' ), strtotime( $item->get_date() ), false );
					$output .= '<p class="pinboard-bookmarks-list-date">';
						if ( $date_text ) $output .= $date_text . ' ';
						$output .= '<a rel="bookmark" href="' . $item->get_id() . '" title="' . esc_attr__( 'Go to the bookmark stored on Pinboard.', 'pinboard-bookmarks' ) . '"' . $new_tab_link . '>';
							$output .= $bookmark_date;
						$output .= '</a>';
					$output .= '</p>';
				}

				// Tags
				if ( $display_tags ) {
					$tags_list = (array) $item->get_categories();
                    if ( $tags_list ) {
						$output .= '<p class="pinboard-bookmarks-list-tags">';
							if ( $tags_text ) $output .= $tags_text . ' ';
							if ( $display_hashtag ) $hashtag = '#';
							foreach( $tags_list as $tag ) {
                                $item_tags = $tag->get_label();
                                $item_tags = (array) explode( ' ', $item_tags );
                                if ( $username ) {
                                    $url = $pinboard_user_tag_url;
                                } else {
                                    $url = $pinboard_tag_url;
                                }
                                foreach ( $item_tags as $item_tag ) {
    								$output .= $hashtag . '<a rel="bookmark" href="' . $url . strtolower( $item_tag ) . '/" title="' . sprintf( esc_html__( 'View the tag %s on Pinboard', 'pinboard-bookmarks' ), $hashtag . $item_tag ) . '"' . $new_tab_link . '>' .  $item_tag . '</a> ';
                                }
                                // Removes the trailing space after the last tag.
                                $output = trim( $output );
							}
						$output .= '</p>';
                    }
				}

			$output .= '</li>';

		}
	}

	$output .= '</ul>';

	if ( ! is_wp_error( $rss ) && $display_archive ) {
		if ( $display_arch_arr ) $arrow = '&nbsp;&rarr;'; else $arrow = '';
		$output .= '<p class="pinboard-bookmarks-list-more">';
			$output .= '<a href="' . $archive_url . '"' .  $new_tab_link . '>';
				$output .= $archive_text . $arrow;
			$output .= '</a>';
		$output .= '</p>';
	}

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
