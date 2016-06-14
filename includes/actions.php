<?php

/**
 * Defines all the action hooks that we execute
 *
 *
 * @since             1.0
 * @package           Ajax Live Search Lite
 *
 */

require_once plugin_dir_path( __FILE__ ) . 'functions.php';

/**
	* Handles the ajax requests for search suggestions
**/

function als_get_suggestions() {

	$s=trim($_GET["s"]);
	
	//Check nonce
	$nonce = $_GET['nextNonce'];

	
	if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' )) {
			die ('Get a life please');
		}
		
		if($s == ''){
			return;
		}

	echo als_database_suggest($s, $count);
	
	exit; //This is important
}
/**
	* Handles ajax requests for live search and ajax search
	* First checks if there is a cached version before fetching the results /** Caching not provided in the lite version 
**/
function als_ajax_results() {

	
	
	//Check nonce
	$nonce = $_GET['nextNonce'];
	
	if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' )) {
			die ('Get a life please');
		}
	
	return als_crawl_results($_GET['s']); 

	
	exit; //This is important
}

/**
	* Registers js and css that is used on the admin page
	* And localises our js with user options for ajax and live search
**/

function als_frontend_scripts() {

		// load jquery and our main script
		wp_enqueue_script( 'jquery' );
		wp_register_script( 'als_functions', plugins_url( '/js/functions.js' , __FILE__ ), array( 'jquery' ), null, true);
		
		// Pass variables to our js file, e.g url etc

	$als_ajax_results = get_option('als_ajax_results', 'no');
	$als_live_search = get_option('als_live_search', 'no');
	$als_display_autocomplete = get_option('als_display_autocomplete', 'yes');
	
		$params = array(
			'ajaxurl'               => admin_url( 'admin-ajax.php' ),
			'home_url'                => home_url(),
			'nextNonce'				=>wp_create_nonce('myajax-next-nonce'),
			'load_suggestions'	=> $als_display_autocomplete,
			'ajax_results' => $als_ajax_results,
			'live_results' => $als_live_search
		);

		// localize and enqueue the script with all of the variable inserted
		wp_localize_script( 'als_functions', 'als', $params );
		wp_enqueue_script( 'als_functions' );
		
		//Load our theme stylesheet
		wp_enqueue_style( 'als_style', plugins_url( '/styles/style.css', __FILE__));

}

/**
	* Runs before the WP_Query class fetches posts
	* First we check if its the main query of a search page
	* Then fetch the posts containing the query
	* And pass their ids to WP_Query
	*
	* @param $query the query that is currently being accessed
**/
function als_pre_get_posts($query){
	if(!is_admin() && $query->is_main_query() && $query->is_search() || $query->als_active()) {
		$results =als_get_search_results($_GET['s']);
		define( 'ALS_SEARCH_RESULTS', count($results) );
		if(count($results) < 1) //no results
		{
			return $query;
		}
		$query->set('post__in', $results);
		$query->set('s', '');
		$query->set('ignore_sticky_posts', 1);
		$post_types = apply_filters('als_index_post_types', array('post','page'));
		$query->set('post_type', $post_types);
		
		$query->set('orderby', 'post__in');
	}
}

/**
	* Runs whenever a post is published
	* If it's an aautosave request or the post is not published,
	* Or it's not in one of the indexable post types, we do nothing
	* Otherwise, we add it to the index and then clear the cache
	*
	* @param $id The id of the post being saved
	* @param $post The global post object of the current post
**/

function als_publish_post($id, $post){
	if ( defined( 'DOUNG_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $id;
	}

	$post_types = apply_filters('als_index_post_types', array('"post"','"page"'));
	
	if(!in_array(trim('"'.$post->post_type. '"'), $post_types) || $post->post_type != 'publish'){
		return $id;
	}
	als_single_index($id, true); //True states that we should delete this post from the index first
	als_delete_dir_files( ALS__CACHE_DIR ); // Let's delete our cache
	return;
}