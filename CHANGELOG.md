# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.15.0] - 2021-01-06
### Added
* Added collapsible panels.
### Changed
* Minimum height for text fields is 80 pixels now.
* Cleaned and beautified HTML code.

## [1.14.1] - 2020-11-21
### Added
* Added new link to plugin line in the WordPress plugins management page.
### Changed
* Updated compatibility to WordPress 5.6.

## [1.14.0] - 2020-05-30
### Fixed
* Avoid printing the HTML tag when the text before the base URL of the original site is empty.

## [1.13.1] - 2020-05-29
### Fixed
* Fixed domain for a string in the widget admin panel.

## [1.13.0] - 2020-05-28
### Added
* Added options for links relationship.

## [1.12.0] - 2020-05-27
### Changed
* Updated protocols from http to https
### Added
* Added Pinboard as source

## [1.11.0] - 2020-05-24
### Added
* Display feed errors to Administrator only.

## [1.10.0] - 2019-11-10
### Added
* Added option to display original site base URL.
* Added control if user entered 0 as items quantity.
### Fixed
* Fixed displaying comments under checkboxes.

## [1.9.0] - 2019-11-03
### Fixed
* Display real true/false in debug section. Now the debug section displays real true/false if an option is a boolean value, instead of displaying respectively 1 or (empty). Also, if the value is a string with 0 as content, displays the real content 0, not (empty).
* Now the source can be displayed independently from tags.
* Corrected the text of widget introduction.
### Changed
* Moved CSS into assets directory.

## [1.8.2] - 2019-11-02
### Added
* Totally revamped cache section in debug.
* Added an ID when the shortcode is used.
### Fixed
* Fixed cache time duration.
### Changed
* Used PHP `date()` instead of WordPress `date_i18n()`.
* Required PHP 5.3 for use of anonymous functions.

## [1.8.1] - 2019-10-26
### Fixed
* Removed typehint for argument in pinboard_bookmarks_check_items().

## [1.8.0] - 2019-04-28
### Changed
* The default options are in a separate function now to simplify the plugin.
* Code optimization.

## [1.7.4] - 2019-02-24
### Fixed
* Make sure that item timestamp and local offset are an integer.

## [1.7.3] - 2018-10-21
### Added
* Added widget ID in debug display.
* Checked all files with [WordPress Coding Standard for PHPCS](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards).
### Fixed
* Fixed filtering the widget title using widget id base.

## [1.7.2] - 2018-06-02
### Changed
* Created some functions in a separate file.
### Fixed
* Minor bug fix and enhancements.

## [1.7.1] - 2018-03-25
### Changed
* Updated shortcode.

## [1.7.0] - 2018-03-25
### Changed
* Added an option to rearrange the elements of each items.
### Fixed
* Fixed shuffling of items.
* Fixed open link in new tabs for source.

## [1.6.0] - 2017-09-24
### Removed
* Removed support for Twitter.
* Removed option to get bookmarks from tags without setting a username.
### Added
* Added introductory text.
### Fixed
* Fixed option name for uninstall.php.
* Minor fixes.

## [1.5.0] - 2017-07-15
### Changed
* Display the source of the bookmark, even if a source has not been defined in the widget admin.
### Fixed
* Minor fixes.

## [1.4.0] - 2017-07-14
### Added
* Display the source of the bookmarks, if activated.
### Changed
* Display a different URL to the archive in different cases.
* Improve control for username.
* Changed some class names.
### Fixed
* Fix time display: now the plugin displays correctly the seconds.
* Fix URL when using more than 1 tag.
* Minor fixes.

## [1.3] - 2017-04-29
### Changed
* Updated shortcode options.

## [1.2] - 2017-04-29
### Added
* Added "no-follow" option to all external links.
* Added the number of retrieved items in debug section.
* Added option to select the type of list (bullet or numeric list).
* Added option to get bookmarks labeled with the source, like `from:pocket`.
* Added option to display debugging informations to admins only.
* Added option to display the time of the bookmarks.
### Changed
* Changed capabilities for viewing debug informations.
### Security
* Hardening security.

## [1.1] - 2017-01-04
### Changed
* Various small improvements.
### Security
* Hardening security.

## 1.0.0 - 2017-01-02
### Added
* First release of the plugin.

[Unreleased]: https://github.com/aldolat/pinboard-bookmarks/commits/develop
[1.15.0]: https://github.com/aldolat/pinboard-bookmarks/compare/1.14.1...1.15.0
[1.14.1]: https://github.com/aldolat/pinboard-bookmarks/compare/1.14.0...1.14.1
[1.14.0]: https://github.com/aldolat/pinboard-bookmarks/compare/1.13.1...1.14.0
[1.13.1]: https://github.com/aldolat/pinboard-bookmarks/compare/1.13.0...1.13.1
[1.13.0]: https://github.com/aldolat/pinboard-bookmarks/compare/1.12.0...1.13.0
[1.12.0]: https://github.com/aldolat/pinboard-bookmarks/compare/1.11.0...1.12.0
[1.11.0]: https://github.com/aldolat/pinboard-bookmarks/compare/1.10.0...1.11.0
[1.10.0]: https://github.com/aldolat/pinboard-bookmarks/compare/1.9.0...1.10.0
[1.9.0]: https://github.com/aldolat/pinboard-bookmarks/compare/1.8.2...1.9.0
[1.8.2]: https://github.com/aldolat/pinboard-bookmarks/compare/1.8.1...1.8.2
[1.8.1]: https://github.com/aldolat/pinboard-bookmarks/compare/1.8.0...1.8.1
[1.8.0]: https://github.com/aldolat/pinboard-bookmarks/compare/1.7.4...1.8.0
[1.7.4]: https://github.com/aldolat/pinboard-bookmarks/compare/1.7.3...1.7.4
[1.7.3]: https://github.com/aldolat/pinboard-bookmarks/compare/1.7.2...1.7.3
[1.7.2]: https://github.com/aldolat/pinboard-bookmarks/compare/1.7.1...1.7.2
[1.7.1]: https://github.com/aldolat/pinboard-bookmarks/compare/1.7.0...1.7.1
[1.7.0]: https://github.com/aldolat/pinboard-bookmarks/compare/1.6.0...1.7.1
[1.6.0]: https://github.com/aldolat/pinboard-bookmarks/compare/1.5.0...1.6.0
[1.5.0]: https://github.com/aldolat/pinboard-bookmarks/compare/1.4.0...1.5.0
[1.4.0]: https://github.com/aldolat/pinboard-bookmarks/compare/1.3...1.4.0
[1.3]: https://github.com/aldolat/pinboard-bookmarks/compare/1.2...1.3
[1.2]: https://github.com/aldolat/pinboard-bookmarks/compare/1.1...1.2
[1.1]: https://github.com/aldolat/pinboard-bookmarks/compare/1.0...1.1
