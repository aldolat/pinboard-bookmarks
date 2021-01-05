<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link    https://dev.aldolat.it/projects/pinboard-bookmarks/
 * @since   1.0.0
 * @package PinboardBookmarks
 * @license GPLv3 or later
 *
 * @wordpress-plugin
 * Plugin Name: Pinboard Bookmarks
 * Description:  Publish Pinboard bookmarks on your WordPress blog.
 * Plugin URI: https://dev.aldolat.it/projects/pinboard-bookmarks/
 * Author: Aldo Latino
 * Author URI: https://www.aldolat.it/
 * Version: 1.15.0
 * License: GPLv3 or later
 * Text Domain: pinboard-bookmarks
 * Domain Path: /languages/
 */

/*
 * Copyright (C) 2016-2021  Aldo Latino  (email : aldolat@gmail.com)
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
 * Prevent direct access to this file.
 *
 * @since 1.0
 */
if ( ! defined( 'WPINC' ) ) {
	exit( 'No script kiddies please!' );
}

/**
 * Launch Pinboard Bookmarks.
 *
 * @since 1.0
 */
add_action( 'plugins_loaded', 'pinboard_bookmarks_setup' );

/**
 * Setup the plugin and fire the necessary files.
 */
function pinboard_bookmarks_setup() {
	/*
	 * Define the version of the plugin.
	 */
	define( 'PINBOARD_BOOKMARKS_PLUGIN_VERSION', '1.15.0' );

	/*
	 * Load the translation.
	 *
	 * @since 1.0
	 */
	load_plugin_textdomain( 'pinboard-bookmarks', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	/*
	 * Include all necessary PHP files.
	 *
	 * @since 1.0
	 */
	// Load the core functions.
	require_once plugin_dir_path( __FILE__ ) . 'inc/pinboard-bookmarks-core.php';
	// Load the functions.
	require_once plugin_dir_path( __FILE__ ) . 'inc/pinboard-bookmarks-functions-init.php';
	// Load the functions.
	require_once plugin_dir_path( __FILE__ ) . 'inc/pinboard-bookmarks-functions-items.php';
	// Load the functions.
	require_once plugin_dir_path( __FILE__ ) . 'inc/pinboard-bookmarks-functions-tools.php';
	// Load the shortcode.
	require_once plugin_dir_path( __FILE__ ) . 'inc/pinboard-bookmarks-shortcode.php';
	// Load the widget's form functions.
	require_once plugin_dir_path( __FILE__ ) . 'inc/pinboard-bookmarks-widget-form-functions.php';
	// Load the widget's PHP file.
	require_once plugin_dir_path( __FILE__ ) . 'inc/class-pinboard-bookmarks-widget.php';

	/*
	 * Load Pinboard Bookmarks' widgets.
	 *
	 * @since 1.0
	 */
	add_action( 'widgets_init', 'pinboard_bookmarks_load_widget' );

	/*
	 * Load the script.
	 *
	 * @since 1.0
	 */
	add_action( 'admin_enqueue_scripts', 'pinboard_bookmarks_load_scripts' );

	/*
	 * Add links to plugins list line.
	 *
	 * @since 1.0
	 */
	add_filter( 'plugin_row_meta', 'pinboard_bookmarks_add_links', 10, 2 );
}

/**
 * Add links to plugins list line.
 *
 * @param array  $links The array containing links.
 * @param string $file The path to the current file.
 * @since 1.0
 */
function pinboard_bookmarks_add_links( $links, $file ) {
	if ( plugin_basename( __FILE__ ) === $file ) {
		// Changelog.
		$changelog_url = 'https://github.com/aldolat/pinboard-bookmarks/blob/master/CHANGELOG.md';
		$links[]       = '<a target="_blank" href="' . $changelog_url . '">' . esc_html__( 'Changelog', 'pinboard-bookmarks' ) . '</a>';

		// Reviews.
		$rate_url = 'https://wordpress.org/support/plugin/' . basename( dirname( __FILE__ ) ) . '/reviews/#new-post';
		$links[]  = '<a target="_blank" href="' . $rate_url . '">' . esc_html__( 'Rate this plugin', 'pinboard-bookmarks' ) . '</a>';
	}
	return $links;
}

/*
 * CODE IS POETRY
 */
