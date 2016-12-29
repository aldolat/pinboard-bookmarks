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
		'nickname'         => '',
		'quantity'         => 5,
		'random'           => false,
		'display_desc'     => false,
		'truncate'         => 0,
		'display_date'     => false,
		'date_text'        => __( 'Stored on:', 'pinboard-bookmarks' ),
		'display_tags'     => false,
		'tags_text'        => __( 'Tags:', 'pinboard-bookmarks' ),
		'display_hashtag'  => true,
		'display_arrow'    => false,
		'display_archive'  => true,
		'archive_text'     => __( 'More posts', 'pinboard-bookmarks' ),
		'display_arch_arr' => true,
		'new_tab'          => false,
		'nofollow'         => true,
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );

	add_filter( 'wp_feed_cache_transient_lifetime', 'pinboard_bookmarks_cache_handler' );

	include_once( ABSPATH . WPINC . '/feed.php' );

    if( $display_arrow )    $arrow        = '&nbsp;&rarr;';             else $arrow        = '';
    if( isset( $new_tab ) ) $new_tab_link = ' target="_blank"';         else $new_tab_link = '';
    if( $nofollow )         $rel_txt      = ' rel="bookmark nofollow"'; else $rel_txt      = ' rel="bookmark"';

    $pinboard_url     = 'https://pinboard.in/u:' . $nickname;
    $pinboard_tag_url = $pinboard_url . $nickname . '/t:';
    $pinboard_rss_url = 'https://feeds.pinboard.in/rss/u:' . $nickname . '/';

    if ( 400 < $quantity ) $quantity = 400;
	$feed_url = $pinboard_rss_url . '?count=' . $quantity;

	$rss = fetch_feed( $feed_url );

	remove_filter( 'wp_feed_cache_transient_lifetime', 'pinboard_bookmarks_cache_handler' );

	$output = '<ul class="pinboard-bookmarks-list">';

	if( is_wp_error( $rss ) ) {
		$output .= '<li class="pinboard-bookmarks-list-li">';
			$output .= sprintf( __( 'There was a problem with your feed! The error is %s', 'pinboard-bookmarks' ), '<code>' . $rss->get_error_message() . '</code>' );
		$output .= '</li>';
	} else {
		if( $quantity > 400 ) $quantity = 400;
		$maxitems  = $rss->get_item_quantity( $quantity );
		$rss_items = $rss->get_items( 0, $maxitems );
		if( $maxitems == 0 ) {
			$output .= '<li class="pinboard-bookmarks-list-li">';
				$output .= __( 'No items.', 'pinboard-bookmarks' );
			$output .= '</li>';
		} else {
			if ( $random ) shuffle( $rss_items );
			foreach ( $rss_items as $item ) {
				$output .= '<li class="pinboard-bookmarks-list-li">';

					// Title
					$title = sprintf( __( 'Read &laquo;%s&raquo;', 'pinboard-bookmarks' ), $item->get_title() );

					$output .= '<p class="pinboard-bookmarks-list-title">';
						$output .= '<a' . $rel_txt . ' href="' . $item->get_permalink() . '" title="' . $title . '"' . $new_tab_link . '>';
							$output .= $item->get_title() . $arrow;
						$output .= '</a>';
					$output .= '</p>';

					// Description
					if( $display_desc ) {
						if( $item->get_description() ) {
							if( $truncate > 0 ) {
								$output .= '<p  class="pinboard-bookmarks-list-desc">';
									$output .= wp_trim_words( $item->get_description(), $truncate, '&hellip;' );
								$output .= '</p>';
							} else {
								$output .= '<p  class="reading-list-desc">' . $item->get_description() . '</p>';
							}
						}
					}

					// Date
					if( $display_date ) {
						$bookmark_date = date_i18n( get_option( 'date_format' ), strtotime( $item->get_date() ), false );
						$output .= '<p class="pinboard-bookmarks-list-date">';
							if( $date_text ) $output .= $date_text . ' ';
							$output .= '<a rel="bookmark" href="' . $item->get_id() . '" title="' . __( 'Go to the bookmark stored on Delicious.', 'pinboard-bookmarks' ) . '"' . $new_tab_link . '>';
								$output .= $bookmark_date;
							$output .= '</a>';
						$output .= '</p>';
					}

					// Tag
					if( $display_tags ) {
						$tags = (array) $item->get_categories();
                        if ( $tags ) {
    						$output .= '<p class="pinboard-bookmarks-list-tags">';
    							if( $tags_text ) $output .= $tags_text . ' ';
    							if( $display_hashtag ) $hashtag = '#';
    							foreach( $tags as $tag ) {
                                    $item_tags = $tag->get_label();
                                    $item_tags = (array) explode( ' ', $item_tags );
                                    foreach ( $item_tags as $item_tag ) {
        								$output .= $hashtag . '<a rel="bookmark" href="' . $pinboard_tag_url . strtolower( $item_tag ) . '/" title="' . sprintf( __( 'View the tag %s on Pinboard', 'pinboard-bookmarks' ), $hashtag . $item_tag ) . '"' . $new_tab_link . '>' .  $item_tag . '</a> ';
                                    }
    							}
    						$output .= '</p>';
                        }
					}

				$output .= '</li>';

			}
		}
	}

	$output .= '</ul>';

	if( ! is_wp_error( $rss ) && $display_archive ) {
		if( $display_arch_arr ) $arrow = '&nbsp;&rarr;'; else $arrow = '';
		$output .= '<p class="pinboard-bookmarks-list-more">';
			$output .= '<a href="' . $rss->get_link() . '"' .  $new_tab_link . '>';
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

/**
 * Check for the cache lifetime in the database and set it to 3600 seconds minimum.
 *
 * @since 1.0
 * @param int $seconds The number of seconds of feed lifetime
 * @return int
 * @link http://codex.wordpress.org/Plugin_API/Filter_Reference/wp_feed_cache_transient_lifetime Codex Documentation
 */
function pinboard_bookmarks_cache_handler( $seconds ) {
	$options = (array) get_option( 'widget_dr-widget' );
	$seconds = isset( $options['time'] ) ? $options['time'] : 3600;
	return $seconds;
}

/**
 * Return an HTML comment with the version of the plugin.
 *
 * @since 1.0
 * @return string $output The HTML comment.
 */
function pinboard_bookmarks_get_generated_by() {
	$output = "\n" . '<!-- Generated by Pinboard Bookmarks ' . PB_PLUGIN_VERSION . ' -->' . "\n";
	return $output;
}

/**
 * Add links to plugins list line.
 *
 * @since 1.0
 */
function pinboard_bookmarks_add_links( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$rate_url = 'https://wordpress.org/support/plugin/' . basename( dirname( __FILE__ ) ) . '/reviews/#new-post';
		$links[] = '<a target="_blank" href="' . $rate_url . '" title="' . __( 'Click here to rate and review this plugin on WordPress.org', 'pinboard-bookmarks' ) . '">' . __( 'Rate this plugin', 'pinboard-bookmarks' ) . '</a>';
	}
	return $links;
}
