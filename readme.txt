=== Delicious Readings ===
Contributors: aldolat
Donate link: http://dev.aldolat.it/projects/delicious-readings/
Tags: delicious, readings, bookmarks, widget
Requires at least: 3.0
Tested up to: 4.6
Stable tag: 2.4.2
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Publish your readings on your WordPress blog using your Delicious Bookmarks.

== Description ==

This plugin allows you to publish some of your Delicious bookmarks on your blog:
it retrieves the bookmarks from a specific tag and publishes them on your sidebar.

It could be useful, for example, to publish your readings on the Web.
Let's say that you read a webpage and bookmark it as "readings".
This plugin can get the bookmarks from the tag "readings" (or whatever it is) and display them on a widget in your sidebar. You can also use a shortcode if you want to display your reading list on a static page or on a single post.

The plugin may display for each tag:

* The title with link
* The description if any
* The date of the bookmark
* The tags assigned to the bookmark
* The link to the entire archive of that tag on Delicious

After the plugin's activation, you will have a new widget in Appearance / Widgets.

**Usage as shortcode**

You can also use the plugin's shortcode to display your list on a static page or on a single post. Use:

`[dreadings feed_url="http://delicious.com/v2/rss/USERNAME/TAG-NAME"]`

Change `USERNAME` and `TAG-NAME` as required.

In the widget you can use the full set of options. So, for example, if you want to display the tags, use:

`[dreadings feed_url="http://delicious.com/v2/rss/USERNAME/TAG-NAME" display_tags=true]`

**Usage as PHP function**

You can also use the main PHP function directly in your theme. Add these lines where you want it be displayed:

`if ( function_exists( 'dr_fetch_feed' ) ) {
	$args = array(
		'feed_url'         => '',
		'quantity'         => 5,
		'display_desc'     => false,
		'truncate'         => 0,
		'display_date'     => false,
		'date_text'        => 'Stored on:',
		'display_tags'     => false,
		'tags_text'        => 'Tags:',
		'display_hashtag'  => true,
		'display_arrow'    => false,
		'display_archive'  => true,
		'archive_text'     => 'More posts',
		'display_arch_arr' => true,
		'new_tab'          => false,
		'nofollow'         => true,
	);
	dr_fetch_feed( $args );
}`

Make sure to properly use the opening and closing tags `<?php` and `?>` respectively.

The only mandatory option is `feed_url`. Also change `USERNAME` and `TAG-NAME` as required. The other options are the default options which you can change according your needs. It isn't necessary to insert all of them.

== Installation ==

This section describes how to install the plugin and get it working.

1. From your WordPress dashboard search the plugin Delicious Readings, install and activate it.
1. Add the new widget on your sidebar.
1. The only necessary thing to do is to add the feed of the tag on Delicious to retrieve.

== Screenshots ==

1. The dashboard panel to set up the widget
2. An example of rendered widget

== Frequently Asked Questions ==

= The rendered text on my blog is not similar to the screenshot =

You have to modify the style.css of yout theme to fit your needs.

= What link have I to insert in the widget? =

The link for the feed of a specific Delicious tag is like this: `http://delicious.com/v2/rss/USERNAME/TAG-NAME`
where `USERNAME` is your username on Delicious and `TAG-NAME` is the tag that collects all your bookmarks to be published.
So, for example, a link could be: `http://delicious.com/v2/rss/myusername/mytag`. Obviously adjust it to your real username ad tag.

== Changelog ==

= 2.4.2 =

* Removed sponsored link in the feed.

= 2.4.1 =

* Switched to PHP5 __contruct() in creating the widget.

= 2.4 =

* NEW: The user can fetch up to 100 items (props by alassafin.f).

= 2.3 =

* NEW: The items can now be displayed in random order (props by whaus).

= 2.2 =

* Fixed a typo that breaks hyperlink if "nofollow" is inactive (thanks to whaus).

= 2.1 =

* Fixed a bug where the shortcode displayed the output before a custom introductory text (thanks to eggepegge).

= 2.0 =

* NEW: Added the shortcode to display your reading list on a static page or on a single post (thanks to @redhatgal for the tip).
* Minor bug fixings.

= 1.1 =

* Moved the widget into a separate file.
* Fixed a typo in the widget panel.
* Fixed a bug in the "nofollow" value for rel attribute.
* Security focusing.

= 1.0 =
First release of the plugin.

== Upgrade Notice ==

No upgrade notice.
