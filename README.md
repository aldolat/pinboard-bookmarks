# Pinboard Bookmarks #
**Contributors:** aldolat  
**Donate link:**   http://dev.aldolat.it/projects/pinboard-bookmarks/  
**Tags:** pinboard, readings, bookmarks, widget  
**Requires at least:** 3.0  
**Tested up to:** 4.7  
**Stable tag:** 1.0  
**License:** GPLv3 or later  
**License URI:** http://www.gnu.org/licenses/gpl-3.0.html  

Publish Pinboard bookmarks on your WordPress blog.

## Description ##

Pinboard Bookmarks allows you to publish bookmarks from Pinboard on your blog. The plugin lets you:

* retrieve the bookmarks from your account (or any account) on Pinboard;
* retrieve the bookmarks from one or more tags of your account (ar any account);
* retrieve the latest bookmarks from Pinboard using one or more tags.

This plugin has also a shortcode, so you can publish the bookmarks in a post or a page.

The plugin may display for each tag (you choose what to display):

* The title with link;
* The description if any;
* The date of the bookmark;
* The tags assigned to the bookmark;
* The link to the entire archive of that tag on Pinboard;
* Display items in random order.

After the plugin's activation, you will have a new widget in Appearance / Widgets.

### Usage as shortcode ###

You can also use the plugin's shortcode to display your list on a static page or on a single post. Example usage:

`[pbsc username="johndoe"]`

Change `username` as required.

In the widget you can use the full set of options. So, for example, if you want to fetch the feed from bookmarks that have `books` and `comics` tags and display the tags, use:

`[pbsc username="johndoe" tags="books comics" display_tags=true]`

Note that the the plugin will fetch bookmarks that have both the tags `books` and `comics`.

This is the list of the options for the shortcode:

* `username` (string) - A username on Pinboard.
* `tags` (string) - A space separated list of tags or a single tag.
* `quantity` (integer) - The number of bookmarks you want to display (Pinboard accepts 400 at most).
* `random` (boolean, true/false) - If a random order should be used.
* `display_desc` (boolean, true/false) - If the tag description should be displayed.
* `truncate` (integer) - The maximum number of words the description should have.
* `display_date` (boolean, true/false) - If the date of the bookmark (when it was archived on Pinboard) should be displayed.
* `date_text` (string) - The text to be prepended to the date of the bookmark.
* `display_tags` (boolean, true/false) - If the tags should be displayed.
* `tags_text` (string) - The text to be prepended to the tags of the bookmark.
* `display_hashtag` (boolean, true/false) - If the tags should be prefixed with an hashtag (`#`),
* `display_arrow` (boolean, true/false) - If an HTML arrow shound be appended to the title of the bookmarks.
* `display_archive` (boolean, true/false) - If the link to the archive on Pinboard should be displayed.
* `archive_text` (string) - The text to be used for the archive on Pinboard.
* `display_arch_arr` (boolean, true/false) - If an HTML arrow shound be appended to the archive text.
* `new_tab` (boolean, true/false) - If the links should be open in a new browser tab.
* `nofollow` (boolean, true/false) - If a `nofollow` attribute should be added the the links of the bookmark title.

### Usage as PHP function ###

You can also use the main PHP function directly in your theme. Add these lines where you want it be displayed (the function echoes the result):

````
if ( function_exists( 'pinboard_bookmarks_fetch_feed' ) ) {
	$args = array(
	'username'         => '',
	'tags'             => '',
	'quantity'         => 5,
	'random'           => false,
	'display_desc'     => false,
	'truncate'         => 0,
	'display_date'     => false,
	'date_text'        => 'Stored on:'
	'display_tags'     => false,
	'tags_text'        => 'Tags:'
	'display_hashtag'  => true,
	'display_arrow'    => false,
	'display_archive'  => true,
	'archive_text'     => 'See the bookmarks on Pinboard'
	'display_arch_arr' => true,
	'new_tab'          => false,
	'nofollow'         => true,
	);
	pinboard_bookmarks_fetch_feed( $args );
}
````

Make sure to properly use the opening and closing tags `<?php` and `?>` respectively.

The only mandatory options are `username` and `tags`; you have to use one of them at least. The other options are the default options which you can change according your needs. It isn't necessary to insert all of them.

## Installation ##

This section describes how to install the plugin and get it working.

1. From your WordPress dashboard search the plugin Pinboard Bookmarks, install and activate it.
1. Add the new widget on your sidebar.
1. The only necessary thing to do is to add a username or one or more tags.

## Screenshots ##

### 1. The dashboard panel to set up the widget ###
![1. The dashboard panel to set up the widget](http://ps.w.org/pinboard-bookmarks/assets/screenshot-1.png)

### 2. An example of rendered widget ###
![2. An example of rendered widget](http://ps.w.org/pinboard-bookmarks/assets/screenshot-2.png)

## Changelog ##

### 1.0 ###
First release of the plugin.

## Upgrade Notice ##

No upgrade notice.
