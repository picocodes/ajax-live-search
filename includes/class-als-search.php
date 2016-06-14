<?php

/**
 * Used to search the database for results
 *
 * @since      1.0.0
 *
 * @package    Ajax Live Search Lite
 * @subpackage Als/includes
 */
 require_once( ALS_LITE__PLUGIN_DIR . 'includes/functions.php'); 
/**
 * The main search class
 * @since      1.0.0
 *
 * @package    Ajax Live Search Lite
 * @subpackage Als/includes
 */
 

class Als_Search {

	/**
	 * Whether or not we should go ahead with our search
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      bool
	 */
	public $can_search = true;


	/**
	 * Class constructor
	 *
	 * @param $s the query string to search for
	 * @since    1.0.0
	 */
	public function __construct($s) {
		

		$this->can_search = apply_filters('als_can_search', true);					
		
		if($this->can_search) {
			$this->results = apply_filters('als_search_results', $this->search($s));
		}

		return $this->results;
	}

	/**
	 * Search for the user query
	 *
	 * 1. Sanitize the string
	 * 2. Strip stop words
	 * 3. Fetch the posts using the new string
	 *
	 * @since    1.0.0
	 * @param    string               $s            The phrase to search for
	 */
	public function search( $s ){
		global $wpdb;
	
		
		$q = als_remove_punct($s);
		$q = als_get_terms($q,1); //Array of search terms
		$terms_per_page = 10;
		$current_page = 1;
		$offset = $current_page * $terms_per_page - $terms_per_page;

		$post_ids =  apply_filters( 'als_found_post_ids', $this->posts_with( $q ));

		$this->results = count($post_ids);

		if (count($post_ids) <1 ){
			return false;
		}
		$post_ids = implode(',' , array_keys($post_ids)); 
		
		$query_restrictions = "ID IN($post_ids) AND 1=1";
		$query_restrictions = apply_filters( 'als_query_restrictions', $query_restrictions);

		$results = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE $query_restrictions ORDER BY FIELD( ID, $post_ids )");
		
		$this->log_query( $s, count($results));

		return $results;
		
	}
	
	/**
	 * fetch post ids containing each term
	 * merge posts that contain more than one term
	 * df = no of documents / no. of documents with the term
	 *
	 *
	 * @since    1.0.0
	 * @param    string               $s            array of words
	 * @return array with ids of posts arranged according to relevance
	 */
	private function posts_with( $s ){
		global $wpdb;
		$index_table = $wpdb->prefix . "als_index";	
			
			$posts = array();
			$post_types = apply_filters('als_index_post_types', array('"post"','"page", "product"'));
			$post_types = implode(',', $post_types);
			
			$total = "SELECT COUNT(ID) as total FROM {$wpdb->posts} WHERE post_type IN($post_types) AND post_status='publish'";
			$total = $wpdb->get_results($total);
			$total = intval($total[0]->total);
			
			foreach ($s as $single => $data) {

				$df = "SELECT COUNT(ID) as df FROM $index_table WHERE term LIKE('$single%')";
				
				$df = $wpdb->get_results($df);
				$df = $df[0]->df;
				
				if(!$df){
					$df = 1; //Prevent division by zero
				}
				
				$df = log($total / $df) * $data['count']; //$data['count'] = No of times current word appears in the string

				$term_posts = "SELECT id, (tf * $df) as idf, post_id FROM $index_table WHERE term LIKE('$single%') ORDER BY idf DESC";
				$term_posts = $wpdb->get_results($term_posts);
				
				if($term_posts) {
					foreach($term_posts as $single_post)
						{	$rele = floatval($single_post->idf);
							if(in_array($single_post->post_id, $posts)) {
								$posts[$single_post->post_id] += $rele;
							} else {
								$posts[$single_post->post_id] = $rele;
							}
						}
				}
			}
			arsort($posts);
			
			return $posts;
			

	}
	
	/**
	 * Save the search term with its results
	 *
	 *
	 * @since    1.0.0
	 * @param    string               $s            array of words
	 * @param    int               $count            number of results
	 */
	private function log_query( $s, $count = 0 ){
		global $wpdb;
		$searches_log_table = $wpdb->prefix . "als_log";
		$count = intval($count);
		$s = als_remove_punct($s);
		$s = $wpdb->prepare("(%s)", $s);

		$exists = "SELECT searches as searches FROM $searches_log_table WHERE LOWER(query)=LOWER($s)";	
		$exists = $wpdb->get_results($exists);
		
		if(count($exists) < 1) {
			$sql = "INSERT IGNORE INTO $searches_log_table (query, hits)
			VALUES ($s,$count)";
			
			return $wpdb->query($sql);
		} else {
			$searches = intval($exists[0]->searches) + 1;
			$sql = "UPDATE $searches_log_table SET searches=$searches, hits=$count WHERE LOWER(query)=LOWER($s)";
			
			return $wpdb->query($sql);
		}
	}

}
