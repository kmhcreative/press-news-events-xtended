=== PNEX (Press-News-Events Xtended) ===  
Contributors: kmhcreative, mattdeclaire, briankwa
Tags: press releases, events, news, custom post type
Requires at least: 3.3.1 and PHP 5.4
Tested up to: 4.3.1
Stable tag: 1.2

Create custom post types for press releases, references to external news stories, and events organized and filtered by custom categories.

== Description ==

This plugin creates custom post types for Press Releases, Events and News Stories, three things a standard PR site needs.  This “Xtended” version adds the ability to organize each of these things by categories (ex: “print,” “television,” “radio,” “interview” etc.,) and new shortcodes that allow readers to filter the Press Releases, Events, and News Stories by those categories.

Note: The original “Press, News, Events” plugin in the WordPress directory has not been updated it in over two years. - kmhcreative

== Installation ==

1. Upload the `press-news-events-xtended` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

You may want to customize your theme for these three post types.  For instance, you can create [specific single and archive templates](http://codex.wordpress.org/Post_Types#Template_Files) files.

== Frequently Asked Questions ==

= What is the air speed velocity of an unladen swallow? =

A question you must answer the Bridge Keeper, assuming you’re not afraid.

= What do you mean? An African or European swallow? =

I don’t know that.  AAAAAAAAAAAAAARGH!

== Screenshots ==

1. Three new post types in your admin menu.
2. Special options box for each post type.
3. Global options for the plugin.

== Changelog ==

= 1.2 =
* Extended plugin to add categories and shortcodes for filtering results by those categories.
* Fixed multiple “non-static method should not be called statically” errors in custom post type initialization.
* Fixed numerous undefined variable errors when adding custom post type that doesn’t have post meta yet.
* Fixed “WP_Admin_Bar::add_node was called incorrectly” error on “Edit Boilerplate”
* Creates “PR News & Events” page on activation, preloaded with sort and loop shortcodes so all you have to do is add the page to your main menu.
* Updated Plugin Screenshots
* Added license declaration and file

= 1.1 =
* News archive sorts by external story publication date.
* Press Release boilerplate is optional.
* Misc. bug fixes.

= 1.0 =
* Hello world.

== Upgrade Notice ==

= 1.2 =
You can safely upgrade from the original “Press, News, Events” plugin to “PNEX.”  It doesn’t change any of the existing database options.