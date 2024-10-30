=== Cached l10n ===
Contributors: lavoiesl
Tags: performance l10n
Requires at least: 3.0.1
Tested up to: 3.5.1
Stable tag: 0.2.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Caches the global variable $l10n, which stores all translations for considerable speed improvement.

== Description ==

Upon inspection using xhProf, I realized that the PHP implementation loading the MO files is really slow.
From my tests, about 25% to 40% of the time Wordpress spends rendering the page is actually spent locating, parsing and merging all the translation files.

This plugin works by caching the whole $l10n variable holding all the text domains and overridding the `load_textdomain`.

Caching is done using serialize and written to `wp-content/uploads/l10n.pson`.
Be sure to regenerate the cache if a .mo changes.

= TODO =

 * Provide a better UI page

== Installation ==

`wp-content/uploads/l10n.pson` must be writable

 1. Activate plugin
 2. Go to plugin's settings page
 3. Hit the regenerate button
 4. `WP_DEBUG` must be false

== Changelog ==

= 0.2.2 =
 * Refactored code
 * Fixed small issues

= 0.2 =

 * Refactored code
 * Added some detection when translations change

= 0.1 =

 * Orignal submission

== Speed comparison ==
Comparing Wordpress Gettext loading vs this plugin.

Using the average response time of:
`siege -c 1 -r 100 -b http://localhost/`

Vanilla: Fresh install of Wordpress
Common: WPML, Types, Views, and Gravity Forms
Total Cache: Common + Total Cache (APC object/database cache, no page cache)
BuddyPress: BP-Registration-Options, BP Show Friends, BuddyPress, BuddyPress Activity Plus, Gravity Forms, Invite Anyone, U BuddyPress Forum Attachment, U BuddyPress Forum Editor, User Switching

                Vanilla         Common          Total Cache     BuddyPress
    Native      202 ms          565 ms          478 ms          567 ms
    Cached      193 ms (-5%)    322 ms (-43%)   333 ms (-30%)   431 ms (-24%)

