=== Ajax Live Search Lite 
Contributors: picocodes
Tags: search, live search, ajax search, real time search, autocompletes, sponsored results, caching, searching, google, google search, search query logging
Requires at least: 4.1
Tested up to: 4.5
Stable tag: 1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Ajax Live Search Lite is a powerful, extendable search plugin that adds a modern touch to the default search sytem with features such as query logging, live searching, autocompletes, relevance based search and sponsored results.
== Description 

Ajax Live Search Lite is a powerful, extendable search plugin that adds a modern touch to the default search sytem with features such as query logging, live searching, autocompletes, relevance based search and sponsored results.
It's a lightweight search plugin that adds a modern feel to your site, giving both users and developers complete control.


= Provide relevance based searching 
The default search system shows results ordered by data while Ajax Live Search shows the results ordered by relevance. We make use of ngrams, markov chains, inverted indexes and tf/idfs to compute relevant results.

It comes with the porter's stemming algorithym that stems words so that when a user searches for a certain word/ phrase.. they get results containing the phrase in any tense. For example, a user searching for players would also get results that include either the phrase play, playing and played. Which is exactly the way google does things.

A team has been asigned the task of implementing a system that also uses a thesaurus to match synonyms. Research is still underway and it should be implemented in the next major version.

= It all begins with ajax 
We have implemented a basic form of ajax that let's you provide live search, autocompletes and ajax loading of results. All three modules can be disabled in the settings dashboard.
 
Live search allows you to display results as the user continues to search, hence a very good way to speed up your site, though it is resource consuming.

Auto completes work the same way as google, where by the user is shown query suggestions while typing. The pro version let's you load them from Google/YouTube, while the basic version only has the option of loading them from your local databse. Though you have the option of selecting whether the suggestions should be fetched from the posts table or the previous searches table.

Ajax search on the other hand allows you to show results without reloading the page. It saves both time and resources for both the user and you. It is therefore highly recommended that you enable it.

= Speed up results by caching 
Ajax Live Search Lite can be configured to cache search results. It can and should be used alongside another caching system. 

However, unlike other systems, the cache is only purged once an item is added to the index, which is helpful since it's highly unlikely that the search results will change without something being added to the index.

= Awesome Statistics 
Get ideas for new content by taking a look at the list of queries with least results but most searches, or just checking out queries that have most searches and focusing on fulfilling that intent.

The pro version offers the ability to view the statistics in form of graphs instead of plain tables. It also offers the ability to import queries in csv format for offline manipulation in your favourite spreadsheet.

= Earn some money by displaying sponsored resuts (Pro version) 

Ajax Live Search let's you add your affiliate links and their descriptions + title so that when a user searches for something related to them, they appear at the top of the search results.

This increases your affilite clickthroughs since the ads are very target. For example, showing someone an elance link when they search for a phrase like 'how to hire freelancers' is guaranteed to produce a good amount of purchases.

= Built with developers in mind 

Ajax Live Search has over 200 hooks that can be used to change the way it performs, whether it's something as easy as dictating the number of returned posts or something as complex as defining your own search algorithym.

Designers can also change it's appearance by registering custom search templates.

== Installation 

= Minimum Requirements 

* WordPress 4.1 or greater
* PHP version 5.3 or greater
* MySQL version 5.0 or greater

NOTE: If you are installing the pro version, please make sure to uninstall the lite version first.

= Automatic installation 

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of Ajax Live Search Lite, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type “Ajax Live Search Lite” and click Search Plugins. Once you’ve found our search plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.

= Manual installation 

The manual installation method involves downloading our search plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating 

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

= Dummy data 

To view how statistics page work, just click on the generate dummy data button in the main statistics window and Als will create app. 1000 dummy search queries for you to play around with.

== Frequently Asked Questions 

= Can I use another search engine such as relevance alongside Ajax Search Lite? =

At the moment, it's not possible, but in the future we will try to implement that option. In any way, Als is just as good as other engines and even better than most of them.

= How does caching work? 

Basically, when a user submits a search query, we check if there is a search file containing that query and display it. If not, we make a new cache file and display that one.

Unlike other systems, our caching does not depend on time, since search results don't change with time but with a change in the index. We therefore keep the cache files for as long as the index is intact.

= Will Ajax Live Search Lite work with my theme? 

Yes; Ajax Live Search Lite will work with any theme, but may require some styling to make it match nicely. It uses the themes default styles though it loads its own search template. If you discover that it's not working properly, then just open the plugin editor and select Ajax search lite then open the styles.css file and style it until it matches your theme.


= Where can I report bugs or contribute to the project? 

Bugs can be reported either in our support forum or preferably on the [Ajax Live Search Lite GitHub repository](https://github.com/picocodes/als/issues).

= Ajax Live Search Lite is awesome! Can I contribute? 

Yes you can! Join in on our [GitHub repository](http://github.com/picocodes/als/) :)

== Screenshots 

1. The slick Ajax Live Search Lite settings panel.
2. Ajax Live Search Lite autocompletes.
3. Sponsored Results.
4. Ajax Live Search Lite search reports.


== Changelog 

= 1.00 - 14/06/2016 =
* Initial release

