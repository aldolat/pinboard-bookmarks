=== Pinboard Bookmarks ===
Contributors: aldolat
Donate link: http://dev.aldolat.it/projects/pinboard-bookmarks/
Tags: pinboard, bookmarks, sidebar, widget, shortcode
Requires at least: 3.0
Tested up to: 5.2.0
Stable tag: 1.8.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Publish Pinboard bookmarks on your WordPress blog.

== Description ==

Pinboard Bookmarks allows you to publish bookmarks from Pinboard on your blog. The plugin lets you:

* retrieve the bookmarks from your account (or any account) on Pinboard;
* retrieve the bookmarks from one or more tags of your account (or any account).

This plugin has also a shortcode, so you can publish the bookmarks in a post or a page.

The plugin may display for each tag (you choose what to display):

* The title with link;
* The description if any;
* The date of the bookmark;
* The tags assigned to the bookmark;
* The link to the entire archive of that tag on Pinboard;
* Display items in random order.

After the plugin's activation, you will have a new widget in Appearance / Widgets.

= Usage as widget =

The plugin provides a widget to be used in your sidebar. After activating the plugin, you will have a new widget in Appearance > Widgets. Simply drag & drop the "Pinboard Bookmarks" widget in the sidebar, adjust the options, and click Save.

= Usage as shortcode =

You can also use the plugin's shortcode to display your list on a static page or on a single post. Example usage:

`[pbsc username="johndoe"]`

Change `username` as required.

In the widget you can use the full set of options. So, for example, if you want to fetch the feed from bookmarks that have `books` and `comics` tags and display the tags, use:

`[pbsc username="johndoe" tags="books comics" display_tags=1]`

Note that the plugin will fetch bookmarks that have both the tags `books` and `comics`.

This is the list of the options for the shortcode:

* `intro_text` (string) - An introductory text for the shortcode.
* `username` (string) - A username on Pinboard.
* `tags` (string) - A space separated list of tags or a single tag.
* `source` (string) - The source in Pinboard, like `from:pocket`, `from:instapaper`, or `from:twitter`.
* `quantity` (integer) - The number of bookmarks you want to display (Pinboard accepts 400 at most).
* `random` (boolean, 1/0) - If a random order should be used.
* `display_desc` (boolean, 1/0) - If the tag description should be displayed.
* `truncate` (integer) - The maximum number of words the description should have.
* `display_date` (boolean, 1/0) - If the date of the bookmark (when it was archived on Pinboard) should be displayed.
* `display_time` (boolean, 1/0) - If the time of the bookmark (when it was archived on Pinboard) should be displayed.
* `date_text` (string) - The text to be prepended to the date of the bookmark.
* `display_tags` (boolean, 1/0) - If the tags should be displayed.
* `tags_text` (string) - The text to be prepended to the tags of the bookmark.
* `display_hashtag` (boolean, 1/0) - If the tags should be prefixed with a hashtag (`#`).
* `use_comma` (boolean, 1/0) - If a comma for separate tags should be used.
* `display_source` (boolean, 1/0) - If the source should be displayed.
* `display_arrow` (boolean, 1/0) - If an HTML arrow should be appended to the title of the bookmarks.
* `display_archive` (boolean, 1/0) - If the link to the archive on Pinboard should be displayed.
* `archive_text` (string) - The text to be used for the archive on Pinboard.
* `list_type` (string) - The type of list to be used.
* `display_arch_arr` (boolean, 1/0) - If an HTML arrow should be appended to the archive text.
* `new_tab` (boolean, 1/0) - If the links should be open in a new browser tab.
* `nofollow` (boolean, 1/0) - If a `nofollow` attribute should be added to the external links.
* `items_order` (string) - The order in which the elements of each item should be displayed.
* `admin_only` (boolean, 1/0) - If the debug should be displayed to Administrators only.
* `debug_options` (boolean, 1/0) - If the complete set of options of the widget should be displayed.
* `debug_urls` (boolean, 1/0) - If the URLs and the single parts, used to build them, should be displayed.

= Usage as PHP function =

You can also use the main PHP function directly in your theme. Add these lines where you want them to be displayed (the function echoes the result):

`if ( function_exists( 'pinboard_bookmarks_fetch_feed' ) ) {
	$args = array(
		'intro_text'       => '',
		'username'         => '',
		'tags'             => '',
		'source'           => '',
		'quantity'         => 5,
		'random'           => false,
		'display_desc'     => false,
		'truncate'         => 0,
		'display_date'     => false,
		'display_time'     => false,
		'date_text'        => 'Stored on:',
		'display_tags'     => false,
		'tags_text'        => 'Tags:',
		'display_hashtag'  => true,
		'use_comma'        => false,
		'display_source'   => false,
		'display_arrow'    => false,
		'display_archive'  => true,
		'archive_text'     => 'See the bookmarks on Pinboard',
		'list_type'        => 'bullet',
		'display_arch_arr' => true,
		'new_tab'          => false,
		'nofollow'         => true,
		'items_order'      => 'title description date tags',
		'admin_only'       => true,
		'debug_options'    => false,
		'debug_urls'       => false
	);
	pinboard_bookmarks_fetch_feed( $args );
}`

If you want to return the result, use `get_pinboard_bookmarks_fetch_feed( $args )`.

Make sure to properly use the opening and closing tags `<?php` and `?>` respectively.

The only mandatory option is `username`. The other options are the default options which you can change according to your needs. It isn't necessary to insert all of them.

= Help, Bugs, and Contributing =

If you need help, please use [WordPress forum](http://wordpress.org/support/plugin/pinboard-bookmarks). Do not send private email unless it is really necessary.

If you have found a bug, please report it on [GitHub](https://github.com/aldolat/pinboard-bookmarks/issues).

This plugin is developed using [GitHub](https://github.com/aldolat/pinboard-bookmarks). If you wrote an enhancement and would share it with the world, please send me a [Pull request](https://github.com/aldolat/pinboard-bookmarks/pulls).

= Privacy Policy =

This plugin does not collect any user data.

== Installation ==

This section describes how to install the plugin and get it working.

1. From your WordPress dashboard search the plugin Pinboard Bookmarks, install and activate it.
1. Add the new widget on your sidebar.
1. The only necessary option is the username on Pinboard. Add it and save the widget.

== Screenshots ==

1. The dashboard panel to set up the widget
2. An example of rendered widget

== Upgrade Notice ==

No upgrade notice.
