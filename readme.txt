=== Ajax Live Search Plugin For WordPress===
Contributors: picocodes
Tags: search, ajax search, live search, search logs, search suggestions, sponsored results, caching, searching, google search
Requires at least: 4.4
Tested up to: 5.0
Version: 2.3.0
Stable tag: 2.3.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A very fast search plugin that sorts search results by relevance and saves user searches.

== Description ==

A very fast and lightweight search plugin that sorts search results by relevance and saves user searches.

As a result:-

* Your best content is shown to your users for relevant searches.
* You can get new content ideas by looking at what your users are searching for.
* Download your users' searches and analyse them with your favorite SEO tool.

= Other Features =

* Earn money - Promote your affiliate links as sponsored results above normal search results.
* Live Search - live search loads search results while the user types.
* Ajax search - speeds up searches by fetching search results without reloading the search page.
* Autocompletes - Show search suggestions in the search box like Google does.

Ajax Live Search was born to solve the problems people faced with other WordPress search plugins.

Most search plugins were either too slow, bloated, returned irrelevant search results or all three.

Ajax live search is lightweight and uses indexes the same way Google does to ensure that searches are both fast and relevant.

The pro version takes things a step further by using linear regression (artificial intelligence) to calculate the relevancy of a post or page to a search term. It takes into account factors such as the age of a post and the number of comments that a given post has when calculating it's relevancy.

The pro version is also x10 faster than the free version since it caches search results. 

We tested several plugins on a database containing 1 million wikipedia articles running on an old computer with 512 mb ram. The pro version took an average of 135 milliseconds to search while the lite version took an average of 1.5 seconds. Most plugins were taking up to 2 days to index the data and 30 mins to fetch results.


**Note:** Only the pro version supports sponsored results and downloading previous searches.

= About Ajax Live Search Pro =

I built Ajax Live Search as a way to give back to the community, and therefore intended to include all features in a single free version.

However, I also decided to release a pro version and use the proceeds to support a local charity run by my mum.

I reduced the price by 300% to ensure that everyone can afford it. It's a win win since you get a great plugin at a *very* affordable price and at the same time help educate a couple of needy children.

So please consider  [upgrading to the pro version](https://ajaxlivesearch.xyz/download#pro). It costs a cup of coffee, and in doing so you will help send needy children to school and also get the following extra benefits:

1. Earn money by displaying sponsored results at the top of regular search results.

1. Download past searches and import them into your favorite keyword research tool.

1. Keep your visitors happy by speeding search results up to x10.

1. Use basic machine learning to show more relevant results to your users' searches.

1. Fetch more relevant search suggestions from Google or YouTube while reducing the load on your server.



= How to search =

You can search using the following search modifiers on your WordPress blog :-

1. (+) A leading plus sign indicates that this word must be present.

1. (-) A leading minus sign indicates that this word must not be present in any of the returned rows.

1. (>) Indicates that this word should be given more weight.

1. (~) Indicates that this word's weight should be negated but not removed. See 2 above.

1. (*) When you append an asterisk to a word; we match all words beginning with the value  before the asterisk.

1. (author: or @) Limit search results to posts by the author with the given username.

1. (category:) Comma, "-" or "+" separated list of category slugs to search in. Use comma  to get posts from any of the categories. Use "+" to fetch posts that appear in all the categories. Use "-" to exclude posts from the given category.

1. (tagged:) Comma or "+" separated list of tag slugs to search in. Use comma to get posts with any of those tags. Use "+" to fetch posts that  have all those tags.

1. (post_types:) comma separated list of post types to search.


Take a look at the [getting started guide](https://ajaxlivesearch.xyz/getting-started) to learn more.

== Installation ==

= Minimum Requirements =

* WordPress 4.4 or greater
* PHP version 5.4 or greater
* MySQL version 5.5 or greater

*NOTE:* If you are installing the pro version, please make sure to **uninstall** the lite version first.

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. 

To do an automatic install of Ajax Live Search :-

* Log in to your WordPress dashboard.
* Click on `Plugins` the menu bar.
* Click on the `Add New` link.
* In the search field that appears; type `Ajax Live Search Plugin` and press enter.
* Find the result that has Picocodes as the author and then install it by clicking `Install Now`!

= Manual installation =

The manual installation method involves downloading our search plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

== Frequently Asked Questions ==

= Can I use another search engine such as relevanssi or wpsolr alongside Ajax Live Search? =

Technically; yes. But you'll will have to disable the searching engine of Ajax Live Search first. That way you can use another engine for searching and still enjoy other features such as ajax search and live search.

= How can I get in touch? =

* For general feedback or personal messages, please use our [contact page](https://picocodes.typeform.com/to/W5wM2d).
* You can report bugs or request new features on our [GitHub repository.](https://github.com/picocodes/ajax-live-search/issues)

= How can I contribute to Ajax Live Search? =

There are a lot of ways to contribute the project:-

* Star our project on [GitHub.](https://github.com/picocodes/ajax-live-search/)
* [Clone the plugin,](https://github.com/picocodes/ajax-live-search) then make improvements to the code and send us a [pull request](https://github.com/picocodes/ajax-live-search/pulls) so that we can share your improvements with the world.
*  Buy the [premium version](https://ajaxlivesearch.xyz/download#pro) of the plugin. The proceeds will be used to support my mum's charity which pays tuition fees for needy students.
* Give us a [5* rating](https://wordpress.org/support/plugin/ajax-live-search/reviews/?filter=5) on WordPress.

= Will Ajax Live Search work with my theme? =

Most likely.

Ajax Live Search will work with any theme, but may require some styling to make it match nicely. It uses the themes default styles though it loads its own search template. If you discover that it's not working properly, open the plugin editor and select Ajax Live search  then open the styles.css file and style it until it matches your theme.

== Screenshots ==

1. First
2. Second
3. Third
4. Fourth


== Changelog ==

= 1.0.0 - 14/06/2016 =
* Hello World

= 2.0.0 - 20/06/2016 =
* Bug fixes
* Migrated from custom indexes to MySQL fulltext search
* Enhanced the ranking system