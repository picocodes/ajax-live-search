<?php

/**
 * Defines all the action hooks that we execute
 *
 *
 * @since             1.0
 * @package           Ajax Live Search
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
		
	$engine = get_option('als_autocomplete_engine', 'google');
	$count = intval(get_option('als_autocomplete_count', '5'));
		als_database_suggest($s, $count);
		
	exit; //This is important
}
/**
	* Handles ajax requests for live search and ajax search
	* First checks if there is a cached version before fetching the results
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
	* Handles the display of welcome page
	* 
**/
function als_admin_init() {

  // Bail if no activation redirect
    if ( ! get_transient( '_welcome_screen_activation_redirect' ) ) {
    return;
  }

  // Delete the redirect transient
  delete_transient( '_welcome_screen_activation_redirect' );

  // Bail if activating from network, or bulk
  if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
    return;
  }

  // Redirect to als about page
  wp_safe_redirect( add_query_arg( array( 'page' => 'als-welcome-screen-about' ), admin_url( 'index.php' ) ) );


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
	 
	if(!is_admin() && $query->is_main_query() && $query->is_search() ) {
		$search_term = apply_filters('als_search_string', sanitize_text_field($_GET['s']));
		$results =als_get_search_results($search_term);
		
		global $als_search_results_count;
		$als_search_results_count = apply_filters('als_search_results_count', count($results));
		$post_types = apply_filters('als_index_post_types', array('post','page','product'));
		
		if(is_array($results)) //Found results so we don't need wordpress to help us with the searching
		{
			$query->set('s', '');
			$query->set('post__in', apply_filters('als_search_post_in', $results, $search_term));
			 
		}
		
		$args = array('author__in'=>array('sanitize'=>'als_is_array',
											'value'=>apply_filters('als_author_in', array(), $search_term)),
											
						'author__not_in'=>array('sanitize'=>'als_is_array',
											'value'=>apply_filters('als_author_not_in', array(), $search_term)),
						
						'author_name'=>array('sanitize'=>'als_is_string',
											'value'=>apply_filters('als_author_name', '', $search_term)),
											
						'cat'=>array('sanitize'=>'als_is_string',
											'value'=>apply_filters('als_cat', '', $search_term)),
						
						'category_name'=>array('sanitize'=>'als_is_string',
											'value'=>apply_filters('als_category_name', '', $search_term)),
											
						'category__in'=>array('sanitize'=>'als_is_array',
											'value'=>apply_filters('als_category_in', array(), $search_term)),

						'category__not_in'=>array('sanitize'=>'als_is_array',
											'value'=>apply_filters('als_category_not_in', array(), $search_term)),	

						'tag'=>array('sanitize'=>'als_is_string',
											'value'=>apply_filters('als_tag', '', $search_term)),	

						'tag__in'=>array('sanitize'=>'als_is_array',
											'value'=>apply_filters('als_tag_in', array(), $search_term)),	

						'tag__not_in'=>array('sanitize'=>'als_is_array',
											'value'=>apply_filters('als_tag_not_in', array(), $search_term)),	

						'post_type'=>array('sanitize'=>'als_is_array',
											'value'=> apply_filters('als_wp_query_post_types', $post_types, $search_term)),

						'date_query'=>array('sanitize'=>'als_is_array',
											'value'=>apply_filters('als_date_query', array(), $search_term)),
						
						'posts_per_page'=>array('sanitize'=>'als_is_string',
											'value'=>apply_filters('als_posts_per_page', get_option('als_posts_per_page', 10), $search_term))
					);
					
		foreach($args as $arg=>$data){
			
			if(!call_user_func($data['sanitize'], $data['value'])){

				continue;
				
			}
			
			$query->set($arg, $data['value']);
		}
		$query->set('orderby', get_option('als_rank_by', 'post__in')); //author, title, type, date, rand, post_in, comment_count

		
		

	}
	

}

function als_remove_menus() {
    remove_submenu_page( 'index.php', 'als-welcome-screen-about' );
}

function als_wp_head() {
    echo '<meta name="generator" content="Ajax Live Search aLs ' . ALS_VERSION .'"/>';
}