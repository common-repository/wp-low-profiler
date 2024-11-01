=== WP low Profiler ===
Contributors: rmahfoud
Donate link: http://anappleaday.konceptus.net/donate
Tags: SEO,privacy,customization,hide,show,publish,sitemap,filter
Requires at least: 2.7.1
Tested up to: 2.8.2
Stable tag: 2.0.3

*** THIS PLUGIN IS DEPRECATED. PLEASE REPLACE BY <a href="http://wordpress.org/extend/plugins/wp-hide-post/">WP Hide Post</a> *** - Enables you to write "low profile" posts/pages that are hidden on some parts of your blog, while still visible in other parts as well as to search engines.

== Description ==

*THIS PLUGIN IS DEPRECATED. PLEASE REPLACE BY [WP Hide Post](http://wordpress.org/extend/plugins/wp-hide-post/)*

This plugin excels in giving you full control on where you want a post to appear. By default, any post you add to your WordPress blog will become the topmost post, and will show up immediately on the front page in the first position, and similarly in category/tag/archive pages. Sometimes, you want to create a "low-profile" addition to your blog that doesn't belong on on the front page, or maybe you don't want it to show up anywhere else in your blog except when you explicitly link to it. This plugin allows you to create such "hidden" gems.

In particular, this plugin allows you to control the visibility of a **post** in various different views:

* The Front Page (Homepage, depending on your theme, this may not be relevant)
* The Category Page (listing the posts belonging to a category)
* The Tag Page (listing the posts tagged with a given tag)
* The Authors Page (listing the posts belonging to an author)
* The Archive Pages (listing the posts belonging to time period: month, week, day, etc..)
* The Search Results
* Feeds

The posts will disappear from the places you choose them to disappear. Everywhere else they will show up as regular posts. In particular, permalinks of the posts still work, and if you generate a sitemap, with something like the [Google XML Sitemaps](http://wordpress.org/extend/plugins/google-sitemap-generator/) the post will be there as well. This means that the content of your post will be indexed and searchable by search engines.

For a WordPress **page**, this plugin also allows you to control the visibility with two options:

* Hide a page on the front page (homepage) only.
* Hide a page everywhere in the blog (hiding the page in the search results is optional).

This means, technically, whenever pages are listed somewhere using the `get_pages` filter, this plugin will kick in and either filter it out or not according to the options you choose. The same rules apply regarding permalinks and sitemaps as they do for regular posts.

"WP low Profiler" plugin is a great tool in your arsenal for SEO optimization. It allows you to add plenty of content to your blog, without forcing you to change the nature and presentation of your front page, for example. You can now create content that you otherwise would be reluctant to add to your blog because it would show immediately on the front page, or somewhere else where it would not belong. It's a must-have feature of WordPress.

Please enjoy this plugin freely, comment and rate it profusely, and send me feedback and any ideas for new features. 

== Installation ==

1. Upload the `wp-low-profiler` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress
1. That's it!! Now whenever you edit a post/page or create a new one, you will see a small panel on the bottom right of the screen that shows the applicable *low profile* options.

== Frequently Asked Questions ==

= What does this plugin do? =

It enables you to create *low profile* posts/pages that can be hidden (temporarily or permanently) from the homepage, feeds and/or other places. The post/page will remain accessible normally through other means, such as permalinks, archives, search, etc... and thus will remain visible to search engines.

= How can I make a post or a page private so that no one can see it? =

If you want to make a post/page completely private you don't need this plugin. WordPress supports options such as private and/or password-protected posts/pages out of the box.

= Can I make a post or a page *low profile* for a while, but then make it normal again? =

Yes. The *low profile* flags are just another attribute of the post/page. It can be added or removed at any time, just like editing anything else about the post.

= I have an idea to improve this feature further, what can I do? =

Please contact me on my blog [An Apple a Day](http://anappleaday.konceptus.net/posts/wp-low-profiler/). I'm looking forward to hearing any suggestions.

= I just found something that doesn't look right, do I just sit on it? =

By all means no! Please report any bugs on my blog [An Apple a Day](http://anappleaday.konceptus.net/posts/wp-low-profiler/). I'd really appreciate it. This is free software and I rely on the help of people like you to maintain it.

= I'm worried this could reduce my search engine ranking. Is it gonna? =

Not at all. On the contrary. All the content you include on your blog, even though it's not directly accessible from the homepage for example, it's still to be available when search engines crawl your site, and will remain linkable for those individuals that are interested in it. Furthermore, if you use some sitemap generation plugin (like the [Google XML Sitemaps](http://wordpress.org/extend/plugins/google-sitemap-generator/) plugin I use on my own [blog](http://anappleaday.konceptus.net/)) all the content will be published to web crawlers and will be picked up by search engines. In fact, this plugin will make your SEO more effective by allowing you to add content that you wouldn't otherwise want to show on your homepage.

= Why is 'WP low Profiler'? =

'WP low Profiler' is being deprecated. You should upgrade to the new plugin [WP Hide Post](http://wordpress.org/extend/plugins/wp-hide-post/), which is equivalent in terms of functionality, and the switch will preserve your existing settings.
The reason it was deprecated is that its name wasn't descriptive enough of the functionality of the plugin. Being 'low profile' could mean many things to many people. It was hard to find and many people who needed it didn't know it exists because of that.

== Screenshots ==

1. A small panel will appear whenever you are editing or creating a **post**. You can check one or more of the *low profile* options as is needed. [See Larger Version](http://anappleaday.konceptus.net/wp-content/uploads/screenshot-1.png)
2. Closup showing the "low Profile Attributes" for posts. [See Larger Version](http://anappleaday.konceptus.net/wp-content/uploads/screenshot-2.png)
3. Another panel will appear whenever you are editing or creating a new **page**. You can check one or more of the *low profile* options as is needed. Note that options for pages are different from those of posts. [See Larger Version](http://anappleaday.konceptus.net/wp-content/uploads/screenshot-3.png)
4. Closup showing the "low Profile Attributes" for pages. [See Larger Version](http://anappleaday.konceptus.net/wp-content/uploads/screenshot-4.png)

== Revision History ==

* 07/25/2009: v2.0.3  - Plugin deprecated and replaced by [WP Hide Post](http://wordpress.org/extend/plugins/wp-hide-post/)
* 07/18/2009: v2.0.2  - Bug fix affecting multiple blogs on the same database with different table prefix.
* 07/13/2009: v2.0.1  - Major revamp, with more compatibility and new features inclusing more low profile options for posts, and more control over search results for pages.
* 06/18/2009: v1.0.11 - Added support for multiple instances coexisting in a single DB.
* 06/15/2009: v1.0.10 - Bug fix to improve compatibility with PHP4 and MySQL 5.0.
* 05/15/2009: v1.0.9  - Minor bug fix - parent page drop down disappears when the plugin is activated (thanks to [dragonsys](http://anappleaday.konceptus.net/posts/wp-low-profiler/comment-page-1/#comment-106) from [dragonsys.org](http://blog.dragonsys.org/) for reporting it).
* 05/04/2009: v1.0.8  - Minor bug fix.
* 05/03/2009: v1.0.7  - Fixed compatibility issues with versions of MySQL prior to 5.1, and added support for themes that filter by categories on the home page (thanks to [Elizabeth & Jackie](http://anappleaday.konceptus.net/posts/wp-low-profiler/#comment-39) from [edaycafe.com](http://edaycafe.com/) for reporting the issues)
* 05/01/2009: v1.0.6  - Included fix for bug causing the number of posts displayed per page to be reduced (thanks to [Rashid](http://anappleaday.konceptus.net/posts/wp-low-profiler/#comment-34) from [mp3wala.com](http://www.mp3wala.com/) for reporting the issue)
* 04/30/2009: v1.0.5  - Added support for low-profiling pages
* 04/27/2009: v1.0.4  - Added i18n support
* 04/26/2009: v1.0.3  - Minor fixes
* 04/25/2009: v1.0.2  - Initial public release

== Development Blog ==

Please visit the plugin page at [An Apple a Day](http://anappleaday.konceptus.net/posts/wp-low-profiler/), and feel free to leave feedback, bug reports and comments.
