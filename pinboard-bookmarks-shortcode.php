<?php
/**
 * The shortcode
 *
 * @package DeliciousReadings
 * @since 2.0
 */

add_shortcode( 'dreadings', 'delicious_readings_sc' );

function delicious_readings_sc( $atts ) {
	extract( shortcode_atts( array(
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
	), $atts ) );

	return get_dr_fetch_feed( $atts );
}
