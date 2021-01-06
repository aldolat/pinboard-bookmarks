<?php
/**
 * Pinboard Bookmarks items functions.
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
 * Return the title of the bookmark.
 *
 * @param array $args {
 *      The array containing the custom parameters.
 *
 *      @type string $rel_txt      The rel attribute of the link.
 *                                 Default to ' rel="bookmark"'.
 *      @type array  $item         The array containing the bookmark information.
 *      @type string $new_tab_link The HTML for open links ina new browser's tab.
 *      @type string $arrow        The HTML string for an arrow, for example:
 *                                '&nbsp;<span class="pinboard-bookmarks-arrow">&rarr;</span>'.
 * }
 * @since 1.7.2
 * @return string The title of the bookmark, in an HTML paragraph linked to Pinboard.
 */
function pinboard_bookmarks_get_title( $args ) {
	$defaults = array(
		'rel_txt'      => ' rel="bookmark"',
		'item'         => array(),
		'new_tab_link' => '',
		'arrow'        => '',
	);

	$args = wp_parse_args( $args, $defaults );

	$rel_txt      = $args['rel_txt'];
	$item         = $args['item'];
	$new_tab_link = $args['new_tab_link'];
	$arrow        = $args['arrow'];

	$output  = '<p class="pinboard-bookmarks-title">';
	$output .= '<a class="pinboard-bookmarks-title-link"' . $rel_txt . ' href="' . esc_url( $item->get_permalink() ) . '"' . $new_tab_link . '>';
	$output .= esc_html( $item->get_title() ) . $arrow;
	$output .= '</a>';
	$output .= '</p>';

	return $output;
}

/**
 * Return the description of the bookmark.
 *
 * @param array $args {
 *      The array containing the custom parameters.
 *
 *      @type array   $item     The array containing the bookmark information.
 *      @type integer $truncate The number of maximum words for the description.
 *                              Default to 0 that means no truncation.
 * }
 * @since 1.7.2
 * @return string The description of the bookmark, in an HTML paragraph.
 */
function pinboard_bookmarks_get_description( $args ) {
	$defaults = array(
		'item'     => array(),
		'truncate' => 0,
	);

	$args = wp_parse_args( $args, $defaults );

	$item     = $args['item'];
	$truncate = $args['truncate'];

	if ( ! $item->get_description() ) {
		return;
	} else {
		if ( $truncate > 0 ) {
			$output  = '<p class="pinboard-bookmarks-desc">';
			$output .= wp_trim_words( esc_html( $item->get_description() ), $truncate, '&hellip;' );
			$output .= '</p>';
		} else {
			$output = '<p class="pinboard-bookmarks-desc">' . esc_html( $item->get_description() ) . '</p>';
		}
		return $output;
	}
}

/**
 * Return the date of the bookmark.
 *
 * @param array $args {
 *      The array containing the custom parameters.
 *
 *      @type boolean $display_time If the time of the bookmark should be displayed.
 *                                  Default to false.
 *      @type array   $item         The array containing the bookmark information.
 *      @type string  $date_text    The leading text for the date.
 *                                  Default to 'Stored on:'.
 *      @type string $rel_txt       The rel attribute of the link.
 *                                  Default to ' rel="bookmark"'.
 *      @type string $new_tab_link  The HTML for open links ina new browser's tab.
 * }
 * @since 1.7.2
 * @return string The date of the bookmark, in an HTML paragraph linked to Pinboard.
 */
function pinboard_bookmarks_get_date( $args ) {
	$defaults = array(
		'display_time' => false,
		'item'         => array(),
		'date_text'    => esc_html__( 'Stored on:', 'pinboard-bookmarks' ),
		'rel_txt'      => ' rel="bookmark"',
		'new_tab_link' => '',
	);

	$args = wp_parse_args( $args, $defaults );

	$display_time = $args['display_time'];
	$item         = $args['item'];
	$date_text    = $args['date_text'];
	$rel_txt      = $args['rel_txt'];
	$new_tab_link = $args['new_tab_link'];

	// Get date format.
	$date_format = get_option( 'date_format' );
	// Get time format, if requested.
	if ( $display_time ) {
		$date_format .= ' ' . get_option( 'time_format' );
	}

	// Convert date and time of the bookmark into a UNIX timestamp.
	$item_timestamp = strtotime( esc_html( $item->get_date( $date_format ) ) );
	// Get local time offset.
	$local_offset = (int) get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
	// Since the bookmark on Pinboard is stored in UTC, convert item timestamp from UTC to local time.
	$item_local_timestamp = $item_timestamp + $local_offset;

	// Get the final date and time of the item.
	$bookmark_date = date( $date_format, $item_local_timestamp );

	// Build the final HTML.
	$output = '<p class="pinboard-bookmarks-date">';
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
 * Return the tags of the bookmark.
 *
 * @param array $args {
 *      The array containing the custom parameters.
 *
 *      @type array   $item                     The array containing the bookmark information.
 *      @type string  $tags_text                The leading text for the tags.
 *                                              Default to 'Tags:'
 *      @type boolean $display_hashtag          If an hashtag shoud be displayed before each tag.
 *                                              Default to true.
 *      @type string  $pinboard_user_tag_url    The URL structure to Pinboard tag for a user.
 *                                              For example: https://pinboard.in/u:nickname/t:
 *      @type boolean $use_comma                If a comma should be appended after each tag.
 *      @type string  $rel_txt                  The rel attribute of the link.
 *                                              Default to ' rel="bookmark"'.
 *      @type string  $new_tab_link             The HTML for open links ina new browser's tab.
 *      @type boolean $display_source           If the source of the bookmark should be displayed.
 *      @type string  $pinboard_user_source_url The URL structure to Pinboard source for a user.
 *                                              For example: https://pinboard.in/u:nickname/from:
 * }
 * @since 1.7.2
 * @since 1.12.0 Added Pinboard as source. Updated protocols.
 * @return string The tags of the bookmark, in an HTML paragraph, with each tag linked to Pinboard.
 */
function pinboard_bookmarks_get_tags( $args ) {
	$defaults = array(
		'display_tags'             => false,
		'item'                     => array(),
		'tags_text'                => esc_html__( 'Tags:', 'pinboard-bookmarks' ),
		'display_hashtag'          => true,
		'pinboard_user_tag_url'    => '',
		'use_comma'                => false,
		'rel_txt'                  => ' rel="bookmark"',
		'new_tab_link'             => '',
		'display_source'           => false,
		'pinboard_user_source_url' => '',
	);

	$args = wp_parse_args( $args, $defaults );

	$display_tags             = $args['display_tags'];
	$item                     = $args['item'];
	$tags_text                = $args['tags_text'];
	$display_hashtag          = $args['display_hashtag'];
	$pinboard_user_tag_url    = $args['pinboard_user_tag_url'];
	$use_comma                = $args['use_comma'];
	$rel_txt                  = $args['rel_txt'];
	$new_tab_link             = $args['new_tab_link'];
	$display_source           = $args['display_source'];
	$pinboard_user_source_url = $args['pinboard_user_source_url'];

	// Get list of tags and source of the bookmark.
	$tags_list      = (array) $item->get_categories();
	$source_service = $item->get_item_tags( SIMPLEPIE_NAMESPACE_DC_11, 'source' );

	/*
	 * If the list of tags is empty AND the source of the bookmark is empty
	 * stop the function and return an empty string.
	 *
	 * @since 1.9.0
	 */
	if ( empty( $tags_list ) && empty( $source_service ) ) {
		return '';
	}

	// Open the $output variable that will contain the text.
	$output = '';

	/*
	 * If we want to see tags AND there are tags
	 * OR
	 * I want to see the source AND there is the source
	 * continue executing the function.
	 */
	if (
		( $display_tags && $tags_list ) ||
		( $display_source && $source_service )
	) :

		$output .= '<p class="pinboard-bookmarks-tags">';

		$tags_text ? $output .= $tags_text . ' ' : $output .= '';

		$use_comma ? $comma = ', ' : $comma = ' ';

		/*
		* Display tags.
		*/
		if ( $display_tags ) {
			if ( $tags_list ) {
				foreach ( $tags_list as $tag ) {
					$item_tags = $tag->get_label();
					$item_tags = (array) explode( ' ', $item_tags );
					foreach ( $item_tags as $item_tag ) {
						$display_hashtag ? $hashtag = '<span class="pinboard-bookmarks-hashtag">#</span>' : $hashtag = '';
						$url                        = $pinboard_user_tag_url;
						$output                    .= $hashtag . '<a class="pinboard-bookmarks-tag"' . $rel_txt . ' href="' . esc_url( $url . strtolower( $item_tag ) . '/' ) . '"' . $new_tab_link . '>' . esc_attr( $item_tag ) . '</a>' . $comma;
					}
					// Removes the trailing comma and space in any quantity and any order after the last tag.
					$output = rtrim( $output, ', ' );
				}
			}
		}

		/*
		* Display the source of the bookmark, like Pocket or Instapaper or Pinboard.
		*
		* @since 1.4
		*/
		if ( $display_source ) {
			if ( $source_service ) {
				$source_service = $source_service[0]['data'];
				switch ( $source_service ) {
					case 'https://readitlater.com/':
						$source_name    = 'Pocket';
						$source_address = $pinboard_user_source_url . 'pocket';
						break;
					case 'https://instapaper.com/':
						$source_name    = 'Instapaper';
						$source_address = $pinboard_user_source_url . 'instapaper';
						break;

					/*
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
					case 'https://pinboard.in/':
						$source_name    = 'Pinboard';
						$source_address = $pinboard_user_source_url . 'pinboard';
						break;
				}
				$output .= $comma . '<a class="pinboard-bookmarks-source"' . $rel_txt . ' href="' . $source_address . '"' . $new_tab_link . '>from ' . $source_name . '</a>';
			}
		}

		$output .= '</p>';

	endif;

	return $output;
}

/**
 * Return the link to the archive of the bookmarks on Pinboard.
 *
 * @param array $args {
 *      The array containing the custom parameters.
 *
 *      @type boolean $display_arch_arr  If an HTML arrow should be displayed.
 *                                       Default to true.
 *      @type integer $maxitems          The number of items retrieved.
 *      @type string  $username          The username on Pinboard.
 *      @type string  $pinboard_user_url The URL structure to Pinboard page for a user.
 *                                       For example: https://pinboard.in/u:nickname
 *      @type string  $pinboard_url      The URL structure to Pinboard.
 *                                       For example: https://pinboard.in
 *      @type string  $archive_url       The URL structure to the archive on Pinboard.
 *                                       For example: https://pinboard.in/u:nickname/t:tag
 *      @type string  $rel_txt           The rel attribute of the link.
 *                                       Default to ' rel="bookmark"'.
 *      @type string  $new_tab_link      The HTML for open links ina new browser's tab.
 *      @type string  $archive_text      The leading text for the archive link.
 *                                       Default to 'See the bookmarks on Pinboard'.
 * }
 * @since 1.7.2
 * @return string The link to the archive on Pinboard.
 */
function pinboard_bookmarks_get_archive_link( $args ) {
	$defaults = array(
		'display_arch_arr'  => true,
		'maxitems'          => 0,
		'username'          => '',
		'pinboard_user_url' => '',
		'pinboard_url'      => '',
		'archive_url'       => '',
		'rel_txt'           => ' rel="bookmark"',
		'new_tab_link'      => '',
		'archive_text'      => esc_html__( 'See the bookmarks on Pinboard', 'pinboard-bookmarks' ),
	);

	$args = wp_parse_args( $args, $defaults );

	$display_arch_arr  = $args['display_arch_arr'];
	$maxitems          = $args['maxitems'];
	$username          = $args['username'];
	$pinboard_user_url = $args['pinboard_user_url'];
	$pinboard_url      = $args['pinboard_url'];
	$archive_url       = $args['archive_url'];
	$rel_txt           = $args['rel_txt'];
	$new_tab_link      = $args['new_tab_link'];
	$archive_text      = $args['archive_text'];

	$display_arch_arr ? $arrow = '&nbsp;<span class="pinboard-bookmarks-arrow">&rarr;</span>' : $arrow = '';

	$output = '<p class="pinboard-bookmarks-archive">' . "\n";

	if ( 0 === $maxitems ) {
		if ( $username ) {
			$url_to_archive = $pinboard_user_url;
		} else {
			$url_to_archive = $pinboard_url;
		}
	} else {
		$url_to_archive = $archive_url;
	}

	$output .= "\t" . '<a class="pinboard-bookmarks-archive-link"' . $rel_txt . ' href="' . esc_url( $url_to_archive ) . '"' . $new_tab_link . '>' . "\n";
	$output .= "\t\t" . esc_html( $archive_text ) . $arrow . "\n";
	$output .= "\t" . '</a>' . "\n";
	$output .= '</p>';

	return $output;
}

/**
 * Return the debugging informations.
 *
 * @param array $args {
 *      The array containing the custom parameters.
 *
 *      @type boolean $admin_only    If the administrators only can view the debugging informations.
 *      @type boolean $debug_options If display the parameters of the widget.
 *      @type boolean $debug_urls    If display the URLS and the parts used to build them.
 *      @type string  $widget_id     The ID of the widget.
 *      @type array   $options       The parameters of the widget.
 *      @type array   $urls          The set of URLS.
 * }
 * @since 1.0
 * @return string The HTML for displaying the debugging informations.
 */
function pinboard_bookmarks_debug( $args ) {
	$defaults = array(
		'admin_only'    => true,
		'debug_options' => false,
		'debug_urls'    => false,
		'widget_id'     => '',
		'options'       => '',
		'urls'          => array(),
	);

	$args = wp_parse_args( $args, $defaults );

	$admin_only    = $args['admin_only'];
	$debug_options = $args['debug_options'];
	$debug_urls    = $args['debug_urls'];
	$widget_id     = $args['widget_id'];
	$options       = $args['options'];
	$urls          = $args['urls'];

	$output = '';

	// Environment information.
	if ( $debug_options || $debug_urls ) {
		global $wp_version;
		$output .= "\n" . '<div class="pinboard-bookmarks-debug-group">' . "\n";
		// Title.
		$output .= "\t" . '<h3 class="pinboard-bookmarks-debug-title">' . sprintf(
			// translators: %s is the name of the plugin.
			esc_html__( '%s Debug', 'pinboard-bookmarks' ),
			'Pinboard Bookmarks'
		) . '</h3>' . "\n";
		// Subtitle.
		$output .= "\t" . '<h4 class="pinboard-bookmarks-debug-env">' . esc_html__(
			'Environment information:',
			'pinboard-bookmarks'
		) . '</h4>' . "\n";
		$output .= "\t" . '<ul class="pinboard-bookmarks-debug-ul">' . "\n";
		// Site URL.
		$output .= "\t\t" . '<li class="pinboard-bookmarks-debug-li">' . sprintf(
			// translators: %s is the URL of the site.
			esc_html__(
				'Site URL: %s',
				'pinboard-bookmarks'
			),
			esc_url(
				site_url()
			) . '</li>'
		) . "\n";
		// WordPress version.
		$output .= "\t\t" . '<li class="pinboard-bookmarks-debug-li">' . sprintf(
			// translators: %s is the WordPress version.
			esc_html__(
				'WordPress version: %s',
				'pinboard-bookmarks'
			),
			$wp_version . '</li>'
		) . "\n";
		// Pinboard Bookmarks version.
		$output .= "\t\t" . '<li class="pinboard-bookmarks-debug-li">' . sprintf(
			// translators: %s is the plugin version.
			esc_html__(
				'Plugin version: %s',
				'pinboard-bookmarks'
			),
			PINBOARD_BOOKMARKS_PLUGIN_VERSION . '</li>'
		) . "\n";
		// ID of the widget/shortcode.
		$output .= "\t\t" . '<li class="pinboard-bookmarks-debug-li">' . sprintf(
			// translators: %s is the ID of the widget.
			esc_html__(
				'ID of this widget: %s',
				'pinboard-bookmarks'
			),
			$widget_id . '</li>'
		) . "\n";

		// Cache information.
		$cache_info = pinboard_bookmarks_get_cache_info( $urls['complete_feed_url'] );

		$output .= "\t\t" . '<li class="pinboard-bookmarks-debug-li">' . esc_html__(
			'Cache information:',
			'pinboard-bookmarks'
		) . "\n";

		$output .= "\t\t\t" . '<ul class="pinboard-bookmarks-debug-ul">' . "\n";

		$output .= "\t\t\t\t" . '<li class="pinboard-bookmarks-debug-li">' . sprintf(
			// translators: %s is the time when the cache was created.
			esc_html__(
				'Created on: %s',
				'pinboard-bookmarks'
			),
			$cache_info['cache_created'] . '</li>'
		) . "\n";
		$output .= "\t\t\t\t" . '<li class="pinboard-bookmarks-debug-li">' . sprintf(
			// translators: %s is the duration of the cache.
			esc_html__(
				'Duration: %s',
				'pinboard-bookmarks'
			),
			$cache_info['cache_duration'] . '</li>'
		) . "\n";
		$output .= "\t\t\t\t" . '<li class="pinboard-bookmarks-debug-li">' . sprintf(
			// translators: %s is the time when the cache will expire.
			esc_html__(
				'Will expire on: %s',
				'pinboard-bookmarks'
			),
			$cache_info['cache_expires'] . '</li>'
		) . "\n";
		$output .= "\t\t\t\t" . '<li class="pinboard-bookmarks-debug-li">' . sprintf(
			// translators: %s is the remaining time.
			esc_html__(
				'Remaining time: %s',
				'pinboard-bookmarks'
			),
			$cache_info['cache_remaining_time'] . '</li>'
		) . "\n";
		$output .= "\t\t\t" . '</ul>' . "\n";
		$output .= "\t\t" . '</li>' . "\n";
		$output .= "\t" . '</ul>' . "\n";
	}

	// Debug section.
	if ( $debug_options ) {
		$output .= "\t" . '<h4 class="pinboard-bookmarks-debug-opts">' . esc_html__(
			'The options:',
			'pinboard-bookmarks'
		) . '</h4>' . "\n";
		$output .= "\t" . '<ul class="pinboard-bookmarks-debug-ul">' . "\n";

		foreach ( $options as $key => $value ) {
			/*
			 * If $value is boolean, echo "true" or "false", instead of boolean "1" or "0".
			 *
			 * If $value is empty, echo "(empty)".
			 * Here we don't use `if ( empty( $value ) )`
			 * because, if $value is a string and contains "0" (a string with 0 as content),
			 * PHP's `empty()` function returns "true", instead of "false".
			 */
			if ( is_bool( $value ) ) {
				$value = ( true === $value ) ? 'true' : 'false';
			} else {
				if ( '' === $value ) {
					$value = esc_html__( '(empty)', 'pinboard-bookmarks' );
				}
			}

			$output .= "\t\t" . '<li class="pinboard-bookmarks-debug-li">' . $key . ': <code>' . esc_html( $value ) . '</code></li>' . "\n";
		}

		$output .= "\t" . '</ul>' . "\n";
	}

	// URLs section.
	if ( $debug_urls ) {
		$output .= "\t" . '<h4 class="pinboard-bookmarks-debug-urls">' . esc_html__( 'URLs and components:', 'pinboard-bookmarks' ) . '</h4>' . "\n";
		$output .= "\t" . '<ul class="pinboard-bookmarks-debug-ul">' . "\n";
		foreach ( $urls as $key => $value ) {
			if ( empty( $value ) ) {
				$value = esc_html__( '(empty)', 'pinboard-bookmarks' );
			}
			$output .= "\t\t" . '<li class="pinboard-bookmarks-debug-li">' . $key . ': <code>' . $value . '</code></li>' . "\n";
		}
		$output .= "\t" . '</ul>' . "\n";
	}

	if ( $debug_options || $debug_urls ) {
		$output .= '</div>';
	}

	/**
	 * If display debugging informations to admins only.
	 *
	 * @since 1.3
	 */
	if ( $admin_only ) {
		if ( current_user_can( 'create_users' ) ) {
			return $output;
		} else {
			return '';
		}
	} else {
		return $output;
	}
}

/**
 * Get site URL from a complete address.
 *
 * Given an address like https://www.example.com/address/of/the/article
 * the function return the site address part, i.e. https://www.example.com.
 *
 * @param array $args {
 *      The array containing the various URL options.
 *
 *      @type string  $url           The complete URL where to extract the base URL.
 *      @type boolean $leave_domain  Whether to leave the domain only.
 *      @type string  $site_url_text The text to used before the base URL.
 * }
 * @return string $url The site address.
 * @since 1.10.0
 */
function pinboard_bookmarks_get_site( $args ) {
	$defaults = array(
		'url'           => '',
		'leave_domain'  => false,
		'site_url_text' => esc_html__( 'From:', 'pinboard-bookmarks' ),
	);

	wp_parse_args( $args, $defaults );

	if ( '' === $args['url'] || ! is_string( $args['url'] ) ) {
		return;
	}

	$args['url'] = esc_url( $args['url'] );

	$url = wp_parse_url( $args['url'] );

	if ( $args['leave_domain'] ) {
		$site_url = preg_replace( '/www./', '', $url['host'] );
	} else {
		$site_url = $url['scheme'] . '://' . $url['host'];
	}

	if ( $args['site_url_text'] ) {
		$text_site_url = '<span class="pinboard-bookmarks-site-url-text">' . $args['site_url_text'] . '</span> ';
	} else {
		$text_site_url = '';
	}

	$output = '<p class="pinboard-bookmarks-site-url">' . $text_site_url . $site_url . '</p>';

	return $output;
}

/**
 * Get the rel HTML attribute for links.
 *
 * @param  bool    $new_tab    Whether to open links in a new browser tab.
 * @param  bool    $nofollow   Whether to use nofollow in rel attribute.
 * @param  bool    $noreferrer Whether to use noreferrer in rel attribute.
 * @return string  $output     The HTML rel attribute.
 *
 * @since 1.13.0
 */
function pinboard_bookmarks_get_rel_link( $new_tab, $nofollow, $noreferrer ) {
	$new_tab ? $noopener_attr      = ' noopener' : $noopener_attr = '';
	$nofollow ? $nofollow_attr     = ' nofollow' : $nofollow_attr = '';
	$noreferrer ? $noreferrer_attr = ' noreferrer' : $noreferrer_attr = '';

	$output = ' rel="bookmark external' . $noopener_attr . $nofollow_attr . $noreferrer_attr . '"';

	return $output;
}
