=== WP-WikiBox ===
Contributors: bsurprised
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=FDGUSD7M825M6
Tags: wikipedia, wiki, summary, article, extract, api, seo, keyword, shortcode, multilingual
Requires at least: 2.9.2
Tested up to: 3.0.1
Stable tag: 0.1.3

Get Wikipedia article summary for a keyword in any language, inline with your posts and pages, with a simple shortcode and/or function.

== Description ==

WP-WikiBox is a simple yet very useful plugin. It uses Wikipedia API to retrieve the beginning summary section of a Wikipedia article based on the keyword title provided. You can simply insert Wikipedia information inline with your posts or provide wiki explanation on your tags and boost your ranks with rich wiki content.

The wiki box supports styling, customization and caching of retrieved data to optimize your experience.

Check out [Tag: WordPress](http://bsurprised.com/tag/wordpress/) on my website to see a demo.

= Features =

New from v0.1.2

* Added file caching method as an option
* Enabled cURL to retrieve data for better performance
* Added option to strip links from content

And

* Supports shortcodes anywhere in your posts, pages and sidebar
* Template function to automate inserting articles
* Long list of supported Wikipedia languages
* Customizable cache control to reduce server load
* Multilingual interface
* Customizable features
* Plugin uninstall support

== Installation ==

Installation is the same routine as most WP plugins:

1. Upload `wp-wikibox` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the `Plugins` menu in WordPress
3. Find the WP-WikiBox submenu under `Options` menu in your Wordpress to change plugin settings.

**Notice:** Please enable gzip compression in plugin options page if you have the support on your server. Wikipedia servers have heavy bandwidth usage and they appreciate less packet data very much. This is during data request on your page from Wikipedia servers and has nothing to do with how you provide your page content to visitors.

= Using WP-WikiBox =

* Place `[wikibox lang="{Wiki Language}"]{Your Keyword}[/wikibox]` in your posts, pages, and/or as text widget in your sidebar. 
   The `lang` attribute is optional and if not set, the default language from plugin settings will be used.
   Example: `[wikibox lang="en"]Pink Floyd[/wikibox]`
* You can add `wikibox_summary( {$keyword}, {$lang = 'en'}, {$echo = true} );` inside your template to add article box anywhere. Optional arguments `$lang` and `$echo` can be provided for customization. 
Examples: 

1. `<?php if ( function_exists( 'wikibox_summary' ) )  wikibox_summary( 'Pink Floyd' ); ?>` 
2. `<?php if ( function_exists( 'wikibox_summary' ) )  wikibox_summary( single_tag_title('', false) ); ?>` 
3. `<?php if ( function_exists( 'wikibox_summary' ) )  wikibox_summary( single_cat_title('', false) ); ?>` 

= Requirements =

* PHP Version > 5.0 required
* PHP cURL recommended
* Wordpress > 3.0 recommended
* PHP zLib support recommended

== Frequently Asked Questions ==

None

== Screenshots ==

1. WP-WikiBox in action
2. Settings page

== Upgrade Notice ==

= 0.1.3 =
Minor update fixing and suppressing the die on error if Wikipedia returned invalid XML.

== Changelog ==

= 0.1.3 =
2010-10-05

* Minor bug fix

= 0.1.2 =
2010-10-05

* Added file caching method as an option
* Enabled cURL to retrieve data for better performance
* Added option to strip links from content

= 0.1.1 =
2010-09-07

* Minor bug fixes

= 0.1 =
2010-08-30

* First released version
* Supports shortcodes and template functions
* Multilingual Wiki support