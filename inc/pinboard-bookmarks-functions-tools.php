<?php
/**
 * Pinboard Bookmarks plugin.
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
 * Build the tags string for RSS URL.
 *
 * @since 1.0
 * @param string $tags The comma separated list of tags.
 * @return string The tags part to be used in RSS URL.
 * @example /t:books/t:comics
 */
function pinboard_bookmarks_get_tags_for_url( $tags ) {
	$tags_for_url = '';

	// Sanitize $tags.
	$tags = sanitize_text_field( $tags );

	// Replace all the occurrences of comma and space in any mix and quantity with a single space.
	$tags = trim( preg_replace( '([\s,]+)', ' ', $tags ) );

	$tags           = strtolower( $tags );
	$tags           = explode( ' ', $tags );
	$number_of_tags = count( $tags );

	// Figure out how many tags we have.
	if ( 1 === $number_of_tags ) {
		// We have 1 tag only.
		$tags         = implode( ' ', $tags );
		$tags_for_url = 't:' . $tags;
	} elseif ( 4 >= $number_of_tags ) {
		// We have 2, 3, or 4 tags.
		foreach ( $tags as $tag ) {
			$tags_for_url .= 't:' . $tag . '/';
		}
	} else {
		// We have more than 4 tags.
		// In this case we have to reduce them to 4, since Pinboard accepts maximum 4 tags for a single query.
		$tags_slice = array_slice( $tags, 0, 4 );
		foreach ( $tags_slice as $tag ) {
			$tags_for_url .= 't:' . $tag . '/';
		}
	}

	return $tags_for_url;
}

/**
 * Return an HTML comment with the version of the plugin.
 *
 * @since 1.0
 * @return string $output The HTML comment.
 */
function pinboard_bookmarks_get_generated_by() {
	$output = "\n" . '<!-- Generated by Pinboard Bookmarks ' . PINBOARD_BOOKMARKS_PLUGIN_VERSION . ' -->' . "\n";
	return $output;
}

/**
 * Return the date and time when the feed cache will expire.
 *
 * This function receives a feed URL in input
 * and gets the human readable date and time of the transient expiration.
 *
 * @param string $feed_url   The complete feed URL.
 * @return array $cache_info An associative array containing cache information.
 *
 * @since 1.8.2
 */
function pinboard_bookmarks_get_cache_info( $feed_url = '' ) {
	if ( empty( $feed_url ) ) {
		return;
	}

	$cache_info = array();

	// Calculate MD5 of the feed URL, as stored by WordPress in the database.
	$md5 = md5( $feed_url );

	// Get transient values.
	$cache_created_timestamp = get_transient( 'feed_mod_' . $md5 );
	$cache_expires_timestamp = get_transient( 'timeout_feed_mod_' . $md5 );

	// Get the local GMT offset and date/time formats.
	$local_offset    = (int) get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
	$datetime_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

	// Add local GMT offset to transients timeout.
	$cache_created_timestamp_localized = $cache_created_timestamp + $local_offset;
	$cache_expires_timestamp_localized = $cache_expires_timestamp + $local_offset;

	// Get date and time of cache creation.
	$cache_created = date( $datetime_format, $cache_created_timestamp_localized );

	// Get cache duration time.
	$cache_duration = human_time_diff( $cache_created_timestamp, $cache_expires_timestamp );

	// Get date and time of cache expiration.
	$cache_expires = date( $datetime_format, $cache_expires_timestamp_localized );

	// Get remaining time to next cache update.
	$cache_remaining_time = human_time_diff( $cache_expires_timestamp, time() );

	$cache_info = array(
		'cache_created'        => $cache_created,
		'cache_duration'       => $cache_duration,
		'cache_expires'        => $cache_expires,
		'cache_remaining_time' => $cache_remaining_time,
	);

	return $cache_info;
}

/**
 * Check if the items entered by the user are according to the standard.
 *
 * The function sanitizes the user input, removes any non-standard item,
 * makes sure that all the standard items are present, removes any duplicate,
 * and makes sure that the items are 4.
 * If the final string is empty, it is filled with the standard value.
 *
 * @param string $items The string containing the items to be checked.
 * @return string The items in the order to be displayed.
 *
 * @since 1.7.0 As a series of commands.
 * @since 1.8.0 As a standalone function.
 * @since 1.8.1 Added check if $items is a string.
 */
function pinboard_bookmarks_check_items( $items = '' ) {
	// Check if $items is a string.
	if ( ! is_string( $items ) ) {
		return;
	}

	// Sanitize user input and make it lowercase.
	$items = strtolower( sanitize_text_field( $items ) );
	// Remove any space and comma from user input and remove leading/trailing spaces.
	$items = trim( preg_replace( '([\s,]+)', ' ', $items ) );

	// Make the user input an array for some checks.
	$items = explode( ' ', $items );

	// Define the standard items.
	$standard_values = array( 'title', 'description', 'date', 'tags' );

	// Check if the user entered items that aren't in the four standard values.
	foreach ( $items as $key => $value ) {
		if ( ! in_array( $value, $standard_values, true ) ) {
			unset( $items[ $key ] );
		}
	}

	// Make sure that all the standard items are present in the array.
	$items = array_merge( $items, $standard_values );

	// Check for possible duplicates and remove them.
	$items = array_unique( $items );

	// Make sure that the items are only four.
	if ( 4 < count( $items ) ) {
		$items = array_slice( $items, 0, 4 );
	}

	// Restore the $items array into a string.
	$items = implode( ' ', $items );

	// If the input is empty, fill it with standard values.
	if ( empty( $items ) ) {
		$items = implode( ' ', $standard_values );
	}

	return $items;
}
