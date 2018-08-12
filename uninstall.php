<?php
/**
 * Pinboard Bookmarks Uninstall
 *
 * @package WordPress
 * @subpackage Pinboard Bookmarks
 * @since 1.0
 */

// Check for the 'WP_UNINSTALL_PLUGIN' constant, before executing.
if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// Delete options from the database.
delete_option( 'widget_pinboard_bookmarks_widget' );
