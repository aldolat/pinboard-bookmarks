<?php
/*
 * Plugin Name: Delicious Readings
 * Description:  Publish a reading list using your Delicious bookmarks
 * Plugin URI: http://www.aldolat.it/wordpress/wordpress-plugins/delicious-readings/
 * Author: Aldo Latino
 * Author URI: http://www.aldolat.it/
 * Version: 2.4.2
 * License: GPLv3 or later
 * Text Domain: delicious-readings
 * Domain Path: /languages/
 */

/*
 * Copyright (C) 2012-2013  Aldo Latino  (email : aldolat@gmail.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */


/**
 * Define the version of the plugin.
 */
define( 'DRPLUGIN_VERSION', '2.4.2' );

/**
 * Check for the cache lifetime in the database and set it to 3600 seconds minimum.
 *
 * @since 1.0
 * @param int $seconds The number of seconds of feed lifetime
 * @return int
 * @link http://codex.wordpress.org/Plugin_API/Filter_Reference/wp_feed_cache_transient_lifetime Codex Documentation
 */
function dr_cache_handler( $seconds ) {
	$options = (array) get_option( 'widget_dr-widget' );
	$seconds = isset( $options['time'] ) ? $options['time'] : 3600;
	return $seconds;
}


/**
 * The core function.
 * It retrieves the feed and display the content.
 *
 * @since 2.1
 * @param mixed $args Variables to customize the output of the function
 * @return mixed
 */
function get_dr_fetch_feed( $args ) {
	$defaults = array(
		'feed_url'         => '',
		'quantity'         => 5,
		'random'           => false,
		'display_desc'     => false,
		'truncate'         => 0,
		'display_date'     => false,
		'date_text'        => __( 'Stored on:', 'delicious-readings' ),
		'display_tags'     => false,
		'tags_text'        => __( 'Tags:', 'delicious-readings' ),
		'display_hashtag'  => true,
		'display_arrow'    => false,
		'display_archive'  => true,
		'archive_text'     => __( 'More posts', 'delicious-readings' ),
		'display_arch_arr' => true,
		'new_tab'          => false,
		'nofollow'         => true,
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );

	add_filter( 'wp_feed_cache_transient_lifetime', 'dr_cache_handler' );

	include_once( ABSPATH . WPINC . '/feed.php' );

	/*
	Delicious offers 10 items by default.
	If the user wants more than 10 items, we have to add '?count=XX' at the end of the URL.
	Also, let's figure out if the user has already added the "?count=XX" parameter in the URL.
	*/
	$count_in_url = strpos( $feed_url, '?count=' );
	if ( ! $count_in_url && $quantity > 10 ) {
		$feed_url = $feed_url . '?count=' . $quantity;
	}

	$rss = fetch_feed( $feed_url );

	remove_filter( 'wp_feed_cache_transient_lifetime', 'dr_cache_handler' );

	$output = '<ul class="reading-list">';

	if( is_wp_error( $rss ) ) {
		$output .= '<li class="reading-list-li">';
			$output .= sprintf( __( 'There was a problem with your feed! The error is %s', 'delicious-readings' ), '<code>' . $rss->get_error_message() . '</code>' );
		$output .= '</li>';
	} else {
		if( $quantity > 100 ) $quantity = 100;
		$maxitems  = $rss->get_item_quantity( $quantity );
		$rss_items = $rss->get_items( 1, $maxitems );
		if( $maxitems == 0 ) {
			$output .= '<li class="reading-list-li">';
				$output .= __( 'No items.', 'delicious-readings' );
			$output .= '</li>';
		} else {
			if ( $random ) shuffle( $rss_items );
			foreach ( $rss_items as $item ) {
				$output .= '<li class="reading-list-li">';

					// Title
					if( $display_arrow )    $arrow        = '&nbsp;&rarr;';            else $arrow = '';
					if( isset( $new_tab ) ) $new_tab_link = ' target="_blank"';
					if( $nofollow )         $rel_txt      = ' rel="bookmark nofollow"'; else $rel_txt = ' rel="bookmark"';

					$title = sprintf( __( 'Read &laquo;%s&raquo;', 'delicious-readings' ), $item->get_title() );

					$output .= '<p class="reading-list-title">';
						$output .= '<a' . $rel_txt . ' href="' . $item->get_permalink() . '" title="' . $title . '"' . $new_tab_link . '>';
							$output .= $item->get_title() . $arrow;
						$output .= '</a>';
					$output .= '</p>';

					// Description
					if( $display_desc ) {
						if( $item->get_description() ) {
							if( $truncate > 0 ) {
								$output .= '<p  class="reading-list-desc">';
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
						$output .= '<p class="reading-list-date">';
							if( $date_text ) $output .= $date_text . ' ';
							$output .= '<a rel="bookmark" href="' . $item->get_id() . '" title="' . __( 'Go to the bookmark stored on Delicious.', 'delicious-readings' ) . '"' . $new_tab_link . '>';
								$output .= $bookmark_date;
							$output .= '</a>';
						$output .= '</p>';
					}

					// Tag
					if( $display_tags ) {
						$tags = (array) $item->get_item_tags( '', 'category' );
						$output .= '<p class="reading-list-tags">';
							if( $tags_text ) $output .= $tags_text . ' ';
							if( $display_hashtag ) $hashtag = '#';
							foreach( $tags as $tag ) {
								$the_domain = isset( $tag['attribs']['']['domain'] ) ? $tag['attribs']['']['domain'] : '';
								$the_tag    = isset( $tag['data'] ) ? $tag['data'] : '';
								$output .= $hashtag . '<a rel="bookmark" href="' . $the_domain . $tag['data'] . '" title="' . sprintf( __( 'Go to the tag %s su Delicious', 'delicious-readings' ), $hashtag . $the_tag ) . '"' . $new_tab_link . '>' .  $the_tag . '</a> ';
							}
						$output .= '</p>';
					}

				$output .= '</li>';

			}
		}
	}

	$output .= '</ul>';

	if( ! is_wp_error( $rss ) && $display_archive ) {
		if( $display_arch_arr ) $arrow = '&nbsp;&rarr;'; else $arrow = '';
		$output .= '<p class="reading-list-more">';
			$output .= '<a href="' .  $rss->get_link() . '"' .  $new_tab_link . '>';
				$output .= $archive_text . $arrow;
			$output .= '</a>';
		$output .= '</p>';
	}

	$output .= dr_get_generated_by();

	return $output;
}


/**
 * Echo the core function
 *
 * @since 1.0
 */
function dr_fetch_feed( $args ) {
	echo get_dr_fetch_feed( $args );
}


/**
 * Return an HTML comment with the version of the plugin.
 *
 * @since 2.4
 * @return string $output The HTML comment.
 */
function dr_get_generated_by() {
	$output = "\n" . '<!-- Generated by Delicious Readings ' . DRPLUGIN_VERSION . ' -->' . "\n";
	return $output;
}


/**
 * Include the widget
 *
 * @since 1.1
 */
include_once( plugin_dir_path( __FILE__ ) . 'delicious-readings-widget.php' );


/**
 * Include the shortcode
 *
 * @since 2.0
 */
include_once( plugin_dir_path( __FILE__ ) . 'delicious-readings-shortcode.php' );


/**
 * Load the translation.
 *
 * @since 1.0
 */
function dr_load_languages() {
	load_plugin_textdomain( 'delicious-readings', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
}
add_action( 'plugins_loaded', 'dr_load_languages' );


/***********************************************************************
 *                            CODE IS POETRY
 ***********************************************************************/
