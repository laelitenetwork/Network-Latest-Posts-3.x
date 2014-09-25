=== Network Latest Posts ===
Contributors: L'Elite
Donate link: https://laelite.info
Tags: recent posts, widget, network, latest posts
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 1.2

This plugin allows you to get the latest posts from the blogs in your network and display them in your site using shortcodes or a widget.

== Description ==

This plugin allows you to get the latest posts from the blogs in your network and display them in your site using shortcodes or a widget. 
Based in the WPMU Recent Posts Widget by Angelo (http://bitfreedom.com/). For further details please visit: http://en.8elite.com/2012/02/27/network-latest-posts-wordpress-3-plugin/ [English] and http://es.8elite.com/2012/02/27/network-latest-posts-wordpress-3-plugin/ [Español]

This plugin works with Wordpress 3 Network (multisites)

== Installation ==

1. Upload `network-latest-posts folder` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. If you want to use the Widget, you can add the Network Latest Posts widget under 'Appearance->Widgets'
4. If you want to use the Shortcode, go to a page or post then write [nlposts] (that's it, seriously!)

== Options ==
* Number of posts to show - list this many posts.
* Number of days to limit - only go back this number of days to get posts.  Set to 0 for no limit (default).
* Title Only - if false it will show the excerpt for each post. True by default.
* Display latest posts by Blog ID
* Display posts by custom type (post, page, etc)
* Ignore Blog IDs

You can style the output using CSS, the list of classes used below:

* .network-latest-posts (content wrapper)
* .network-latest-posts-sectitle (if a title for the shortcode has been set)
* .network-posts-title (post's title)
* .network-posts-source (where the post comes from)
* .network-posts-excerpt (the post's excerpt)

There's also a second class named added to the content wrapper "blog-X" where X is the ID of each blog so you can add a different 
style for each block of posts inside a blog.

= Shortcode Options =
`[nlposts title='Latest Posts' number='2' days='30' titleonly=false wrapo='<div>' wrapc='</div>' blogid=null thumbnail=false cpt=post ignore_blog=null]`

* title = the section's title null by default
* number = number of posts to display by blog 10 by default
* days = time frame to choose recent posts from (in days) 0 by default
* titleonly = if false it will display the title and the excerpt for each post true by default
* wrapo = html opening tag to wrap the output (for styling purposes) null by default
* wrapc = html closing tag to wrap the output (for styling purposes) null by default
* blogid = the id of the blog for which you want to display the latest posts null by default
* thumbnail = allows you to display the thumbnail (featured image) for each post, it can be true or false (false by default)
* cpt = allows you to display a custom post type (post, page, etc)
* ignore_blog = allows you to ignore one or various blogs using their ID numbers

== Changelog ==

= 1.2 =
* Fixed the repeated `<ul></ul>` tags for the widget list
* NEW feature added `cpt` which allows you to display a specific post's type (post, page, etc) - Proposed by John Hawkins (9seeds.com)
* NEW feature added `ignore_blog` which allows you to ignore one or various blogs' ids - Proposed by John Hawkins (9seeds.com)
* Added the Domain name with the IDs to the list of blog ids in the Widget
* Some other minor bugs fixed

= 1.1 =
* Fixed the missing `<ul></ul>` tags for the widget list
* NEW feature added `blogid` which allows you to display the latest posts for a specific blog
* NEW feature added `thumbnail` to display the thumbnail of each post
* The widget includes now a list where you can select the blog's id for which you want to display the latest posts

= 1.0 =
* Added Widget option to display excerpt
* Markup improved to make CSS Styling easier
* Added Uninstall hook
* Added Shortcode functionality
* Plugin based in Multisite Recent Posts Widget

== Screenshots ==
1. Post Shortcode
2. Widget Options
3. Output Example
4. Latest Posts with Thumbnails

== Frequently Asked Questions ==

= Why did you do this plugin? =
Because I have 3 blogs and I needed a way to display the latest posts from them in the main blog of my Network.

= If I want you to add a new feature, will you do it? =
I like new ideas, but I'm kind of busy finishing the second year of my master's degree so I can't promise I will add everything people proposes but
I'll do my best to add the best ideas as soon as I have the time and as long as they don't break the actual state of the plugin.