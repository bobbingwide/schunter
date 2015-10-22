=== schunter ===
Contributors: bobbingwide
Donate link: http://www.oik-plugins.com/oik/oik-donate/
Tags: shortcodes, smart, lazy
Requires at least: 4.3
Tested up to: 4.3.1
Stable tag: 0.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Short code hunter.

1. Searches for shortcodes in your WordPress website

Planned features

1. Allows selection of shortcodes to be registered
1. Helps keep track of all known shortcodes 

== Installation ==
1. Upload the contents of the schunter plugin to the `/wp-content/plugins/schunter' directory
1. Activate the schunter plugin through the 'Plugins' menu in WordPress
1. Use oik-batch to run the schunter logic

== Frequently Asked Questions ==

= What are the plugin dependencies? =

schunter v0.0.1 is dependent upon oik or oik-lib
It's designed to be run in batch mode using oik-batch
which is something like WP-CLI.
These plugins are available from github.


== Screenshots ==
1. schunter in action

== Upgrade Notice ==
= 0.0.2 = 
Now supports scanning of posts, postmeta and options, including serialized data

= 0.0.1=
New plugin, available from github.

== Changelog == 
= 0.0.2 =
* Added: Fixes #1 - schunter should cater for script and CDATA sections
* Added: Fixes #2 - schunter should cater for style tags
* Added: Fixes #3 - schunter should cater for shortcodes which don't expand $content
* Added: Fixes #4 - schunter should cater for postmeta and options, including serialized data

= 0.0.1 =
* Added: New plugin. Dependent upon oik / oik-lib. Scans for shortcodes in posts.

== Further reading ==
If you want to read more about the oik plugins then please visit the
[oik plugin](http://www.oik-plugins.com/oik) 
**"the oik plugin - for often included key-information"**



