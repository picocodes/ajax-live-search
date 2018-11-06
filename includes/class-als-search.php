<?php

/**
 * Used to search the database for results
 *
 * @since      1.0.0
 *
 * @package    @package Ajax Live Search
 * @subpackage Als/includes
 */
 require_once( ALS__PLUGIN_DIR . 'includes/functions.php'); 
/**
 * The main search class
 * @since      1.0.0
 *
 * @package    Als
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
	 * @param    string               $q            The phrase to search for
	 */
	public function search( $q ){
		global $wpdb;
		
		$s = $wpdb->prepare('%s', $q);
		$post_ids =  apply_filters( 'als_found_post_ids', $this->posts_with( $s, $q ), $s);

		if (!is_array($post_ids) OR count($post_ids)==0){
			return false;
		}
		
		$this->results_count = count($post_ids);
		$this->log_query( $s, $this->results_count);
		
		for($i=0; $i<$this->results_count; $i++) {
			
			$post_ids[$i]=intval($post_ids[$i]);
			
		}

		return $post_ids;
		
	}
	
	/**
	 * Uses MySQL's fulltext to search for posts containing the search string
	 *
	 *
	 * @since    1.0.0
	 * @param    string               $s           sanitised string to search for
	 * @param    string               $q           unsanitised string to search for
	 * @return array with ids of posts arranged according to relevance
	 */
	private function posts_with( $s ){
		global $wpdb;			
		$posts = array();
		
		$post_types = apply_filters('als_index_post_types', array('post','page','product'));
		array_walk($post_types, function(&$value){$value=trim($value, '"');});
		$post_types = array_unique($post_types); //John do something
		
		$order_by = get_option('als_rank_by','post__in');
		$order = '';
		$score_field = 'post_date';
			if($order_by == 'post__in'){
				
				$title_weight = intval(get_option('als_title_weight',15));
				$content_weight = intval(get_option('als_content_weight',1));
				$q = $wpdb->prepare('%s', als_remove_punct(als_remove_modifiers($s)));
				/**
					* After 35 tries and no success I was tempted to copy relevanssi
					* (He claims to have done magic in his plugin. Which is true by the way)
					* Then an idea came to me 
					* And I created my own magic that uses around 20 factors
					* To determine relevance. And hey, it so happens that my version is way faster
					*
					* Here is what happens...
					* First of all we calculate the sum of the title and content weight for the query 
				*/
				$score_field = "(((MATCH(post_title) AGAINST($q) * $title_weight) + 
					(MATCH(post_title,post_content) AGAINST($q) * $content_weight))";
				
				/*
				 * After that we check if posts with comments should be weighted more
				 * If yes, we use linear regression based on comment count to calculate extra weight
				**/
				if (get_option('als_favor_popular','no') == 'yes'){
					
					$score_field .= '+ (IF(comment_count = 0, 0, log(comment_count)+0.5))'; //Log 1=0, so we add 0.5 to atleast give more weight to posts containing 1 comment
					
				}
				
				/**
				 * Not forgetting about newer posts
				 * Pretty simple... just calculate the no of days from now to post publish date and get it's log	 
				*/
				
				if (get_option('als_favor_new','no') == 'yes'){
					$field = get_option('als_favor_new_field','post_modified');
					$score_field .= "+ (log(DATEDIFF(NOW(), $field)+1))";
					
				}
				
				/**
				 * Finally, Not all post types are important
				 * If I'm running a forum the al rather have a topic show up at the top instead of page
				*/
				
				
				$score_field .= '+(';
				
				foreach($post_types as $type){
					$type = strtolower($type);
					$weight = "als_{$type}_weight";
					$weight = get_option($weight, 1);
					$score_field .= "((post_type IN('$type'))*$weight)+";
					
					
				}
				
				$score_field .= '0)) as score'; //Simple way to take care of final +
				
				$order ='ORDER BY score DESC';
				
				
			}
			
		array_walk($post_types, function(&$value){$value="'$value'";});
		$post_types = implode(',', $post_types);
		$restrictions = "WHERE MATCH(post_title, post_content) AGAINST ($s IN BOOLEAN MODE)";
		$restrictions .= "AND post_type in($post_types) AND post_status='publish'";
		
		//Rember, score field is set to either date or calculated score depending on admin setting
		
        $sql = "SELECT ID, $score_field FROM {$wpdb->posts} $restrictions $order LIMIT 100";	
		$results = $wpdb->query($sql); // For some reason $results is evaluating to the no of found rows
		$results = $wpdb->last_result; //Here is a workaround(for the time being)
		
		foreach($results as $single){
			$posts[] = $single->ID;
		}
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
		$modified = $wpdb->prepare("(%s)", als_remove_punct(als_remove_modifiers($s)));
		$original = $wpdb->prepare("(%s)", $s);

		$exists = "SELECT searches as searches FROM $searches_log_table WHERE LOWER(query)=LOWER($original)";	
		$exists = $wpdb->get_results($exists);
		
		if(count($exists) < 1) {
			$sql = "INSERT IGNORE INTO $searches_log_table (query, modified, hits)
			VALUES ($original, $modified,$count)";
			
			return $wpdb->query($sql);
		} else {
			$searches = intval($exists[0]->searches) + 1;
			$sql = "UPDATE $searches_log_table SET searches=$searches, hits=$count WHERE LOWER(query)=LOWER($original)";
			
			return $wpdb->query($sql);
		}
	}

}
