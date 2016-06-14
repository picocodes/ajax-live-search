<?php

/**
 * Contains all the core functions
 *
 * @ since 1.0.0
 * @package Ajax Live Search Lite
 *
 */
	 
	 require_once( ALS_LITE__PLUGIN_DIR . 'includes/porter-stemmer.php'    );
	 //The version below happens to be a little bit slow
	 //require_once( ALS_LITE__PLUGIN_DIR . 'includes/PorterStemmer2.php'    );
	 
	/**
	 * Removes all punctuations from a string
	 * 
	 * @param a punctuated string
	 * @param $preserve_basics whether or not to preserve basic punctuations like fullstops and comas
	 * @return modified string
	 * @since    1.0.0
	 */
	 function als_remove_punct($a, $preserve_basics=false) {

		$a = preg_replace ('/<[^>]*>/', ' ', $a); 
    
	    $a = str_replace("\r", ' ', $a);    // --- replace with empty space
	    $a = str_replace("\n", ' ', $a);   // --- replace with space
	    $a = str_replace("\t", ' ', $a);   // --- replace with space		
		
		$a = stripslashes($a);

		$a = str_replace('ß', 'ss', $a);
		
		if(!$preserve_basics){
			$a = str_replace("·", '', $a);
			$a = str_replace("'", ' ', $a);
			$a = str_replace("’", ' ', $a);
			$a = str_replace("‘", ' ', $a);
			$a = str_replace("”", ' ', $a);
			$a = str_replace("“", ' ', $a);
			$a = str_replace("„", ' ', $a);
			$a = str_replace("´", ' ', $a);
			$a = str_replace("—", ' ', $a);
			$a = str_replace("–", ' ', $a);
			$a = str_replace("×", ' ', $a);
			$a = preg_replace('/[[:punct:]]+/u', ' ', $a);
		}
		$a = str_replace("…", '', $a);
		$a = str_replace("€", '', $a);
		$a = str_replace("&shy;", '', $a);

		$a = str_replace(chr(194) . chr(160), ' ', $a);
		$a = str_replace("&nbsp;", ' ', $a);
		$a = str_replace('&#8217;', ' ', $a);

        $a = preg_replace('/[[:space:]]+/', ' ', $a);
		$a = trim($a);

        return $a;
}

	/**
	 * Converts a string into an array of words
	 *
	 * @param $content string to be converted
	 * @param $weight the weight to assign each word
	 * @param $tokens an optional array of existing tokens that should be added to the extracted terms
	 * @param $count the total count of words used when calculating tf, df and idf
	 *
	 * @return array of words ... array['example']=array('tf','relevance');
	 * where tf = term frequence = no. of appearence / total no of terms
	 * relevance is calculated using the option weight parameter, tf
	 * and appearance position in the document
	 
	 * relevance = log(zoneX * weight * tf)
	 *
	 * @since    1.0.0
	 */
	function als_get_terms($content, $weight=1.5, $tokens=array(), $count = false){
		$content=strtolower(als_remove_punct($content));
		$single = explode(' ', $content);
		
		if(!$count) {
			$count= count($single);
		}
		
		$stop_words = file_get_contents(ALS_LITE__PLUGIN_DIR . 'includes/stopwords.txt');
		$stop_words = $content=strtolower(als_remove_punct($stop_words));
		$stop_words = explode(' ', $stop_words);
		
		foreach($single as $t) {
			$t = strval($t); // Convert all variables to strings
			
			if(in_array($t, $stop_words)) {
				continue;
			}
			
			if (strlen($t) < 3) {
				continue;
			}

			$t = PorterStemmer::Stem($t); 
			
			
			if (!isset($tokens[$t])) {
				$tokens[$t]['count'] = 1;
				$tokens[$t]['weight'] = $tokens[$t]['count'] * $weight;
				$tokens[$t]['tf'] = $tokens[$t]['count'];
			}
			else {
				$tokens[$t]['count']++;
				$tokens[$t]['weight'] = $tokens[$t]['count'] * $weight;
				$tokens[$t]['tf'] = $tokens[$t]['count'];
			}
		
		}
		
		
		
	return $tokens;

	}
	
	/**
	 * Removes a single post from the index
	 *
	 * @param $id int id of the post to eliminate
	 * @return $id int id of removed post
	 * @since    1.0.0
	 */	
	 
	function als_remove_post($id){
		global $wpdb;
		$index_table = $wpdb->prefix . "als_index";
		$id = intval($id);
		$sql = "DELETE FROM $index_table WHERE post_id=$id";
		$wpdb->query($sql);
		return $id;
	}
	
	/**
	 * Indexes 100 non indexed posts
	 *
	 * @since    1.0.0
	 */	
	 
	function als_index_new(){
	global $wpdb;
	if (function_exists('wp_suspend_cache_addition')) 
		wp_suspend_cache_addition(true);	

	if (!ini_get('safe_mode')) 	{
		set_time_limit(0);
	}
	
	$post_types = apply_filters('als_index_post_types', array('"post"','"page"'));

	$content = als_fetch_posts($post_types, 100, true);
	
	$in_index = intval(get_option('als_indexed_posts', 1));
	
	update_option('als_indexed_posts', count($content) + $in_index);
	
	foreach ($content as $post) {
		als_single_index($post->ID, false);
	}
	
	$index_table = $wpdb->prefix . "als_index";
	$wpdb->query("ANALYZE TABLE $index_table");
	// To prevent empty indices

	if (function_exists('wp_suspend_cache_addition')) {
		wp_suspend_cache_addition(false);
		}
	}
	
	/**
	 * returns the number of total indexable posts
	 *
	 * @since    1.0.0
	 */	
	 
	function  als_total_indexable(){

	global $wpdb;
	$post_types = apply_filters('als_index_post_types', array('"post"','"page"'));

			
	$total = $wpdb->get_results("SELECT count(post.ID) as count
			FROM $wpdb->posts as post
			WHERE post.post_type IN (" . implode(', ', $post_types) . ") AND post_status='publish'");
	
	return ($total[0]->count);
	
	}
/**
 * Clean variables using sanitize_text_field.
 * @param string|array $var text to be cleaned
 * @return string|array
 */

function als_clean( $var ) {
	return is_array( $var ) ? array_map( 'wc_clean', $var ) : sanitize_text_field( $var );
}


	
	/**
	 * returns the number of total indexable posts
	 *
	 * @since    1.0.0
	 */	
	 
	function  als_indexed_count(){

	global $wpdb;
	$table = $wpdb->prefix . 'als_index';

	$total = $wpdb->get_results("SELECT DISTINCT post_id from $table");
	
	return (count($total));
	
	}
	
	/**
	 * Displays the index more details
	 *
	 * @since    1.0.0
	 */	
	 
	function  als_general_index_more(){?>
		
		<button name ='als_index_more' value = '<?php esc_attr_e( 'Index More', 'als-lite' ); ?>' class ='button-primary'><?php esc_attr_e( 'Index More', 'als-lite' ); ?></button>
		

	<?php 
	}
	
	/**
	 * Fetches all published posts
	 * Returns an array of fetched posts ids
	 * @param $post_types an array of post types to fetch
	 * @param $limit maximum number of posts to fetch and an optional integer
	 * @param $none_indexed Whether or not to limit the posts to non-indexed posts
	 * @since    1.0.0
	 */	
	 
	function als_fetch_posts($post_types, $limit=1000000, $none_indexed = false){
		
		if (!ini_get('safe_mode')) 	{
			set_time_limit(0);
		}
		
		global $wpdb;
		
		$restriction = " (post.post_status='publish' OR
			(post.post_status='inherit'
				AND(
					(parent.ID is not null AND (parent.post_status='publish'))
					OR (post.post_parent=0)
				)
			)) ";
			
		if (count($post_types) > 0) {
			$restriction .= " AND post.post_type IN (" . implode(', ', $post_types) . ') ';
		}
		
		if($none_indexed){
			$table = $wpdb->prefix . 'als_index';
			$ids = array();
			$indexed = $wpdb->get_results("SELECT DISTINCT post_id from $table");
			foreach($indexed as $id) {
				$ids[] = $id->post_id;
			}

			$restriction .= " AND post.ID NOT IN (" . implode(', ', $ids) . ') ';
		}
		
		$q = "SELECT post.ID
			FROM $wpdb->posts as post
			LEFT JOIN $wpdb->posts as parent ON (post.post_parent=parent.ID)
			WHERE $restriction LIMIT $limit";

		return $wpdb->get_results($q);

	}

	/**
	 * Indexes the post containing the published id
	 * 
	 * @param $id id of the post to index
	 * @param $delete whether or not to remove it from the index first before indexing it again
	 * 
	 * @since    1.0.0
	 */	
	 
function als_single_index($id, $delete=true) {

	global $wpdb, $post;	
	$index_table = $wpdb->prefix . "als_index";	
	
	if($delete) {
	//Let's avoid duplicates
	als_remove_post($id);
	}
	
	// Fetch the post
	$post = get_post($id);

	$body=apply_filters('the_content', $post->post_content);
	$title=apply_filters('the_title', $post->post_title);
	$url=apply_filters('the_name', $post->post_name);
	$excerpt=apply_filters('the_excerpt', $post->post_excerpt);
	
	if (function_exists("do_shortcode")) {
		
		$body = do_shortcode($body);
		$title = do_shortcode($title);
		$url = do_shortcode($url);
		$excerpt = do_shortcode($excerpt);
	}
	
	$content_weight = floatval(get_option('als_content_weight', 1));
	$title_weight = floatval(get_option('als_content_weight', 1.5));
	$url_weight = floatval(get_option('als_content_weight', 2.5));
	$excerpt_weight = floatval(get_option('als_content_weight', 1.25));
	
	$insert_data = als_get_terms($body, $content_weight, array(), 150);
	$insert_data = als_get_terms($body, $title_weight, $insert_data);
	$insert_data = als_get_terms($body, $url_weight, $insert_data);
	$insert_data = als_get_terms($body, $excerpt_weight, $insert_data);
	$values = array();
	
	foreach ($insert_data as $term => $data) {

		$term = trim($term);

		$value = $wpdb->prepare("(%d, %s, %f, %f, %d)",
			$id, $term, $data['tf'], $data['weight'], $data['count']);

		array_push($values, $value);
	}
	
	if (function_exists('wp_suspend_cache_addition')) 
		wp_suspend_cache_addition(true);
		
	if (!empty($values)) {
		$values = implode(', ', $values);
		$query = "INSERT IGNORE INTO $index_table (post_id, term, tf, relevance, count)
			VALUES $values";
		$wpdb->query($query);
	}

	update_option('als_last_indexed', $id);
	
	if (function_exists('wp_suspend_cache_addition')) {
		wp_suspend_cache_addition(false);
	}
}

/**
 * Fetch query suggestions from the database
 *
 *
 * @param $q the user query that should be used to fetch suggestions
 * @param $count the number of suggestions to fetch
 *
 * @since  1.0.0
 *
 * @return array
 */
function als_database_suggest($q='',$count =5) {
	if(is_null($count)){
		$count =5;
	}
	global $wpdb;
	
	$table = get_option('als_autocomplete_table', 'posts');

	if($table == 'posts'){
		$response= $wpdb->get_results(
			"SELECT post_title FROM $wpdb->posts 
			WHERE LOWER(post_title) LIKE LOWER('$q%') AND post_status='publish' 
			LIMIT $count");
		
	if(is_array($response) AND count($response) >=1 ) {
		echo'<ul>'; 
		
		for($i=0; $i<count($response); $i++){
			$url = home_url("/?s=") . $response[$i]->post_title;
			echo '<li>
				<a href="' . $url . '">'
				. $response[$i]->post_title .
				'</a></li>';
		}
		
		echo'</ul>';
		}
		
	else {
		echo '';
		}
	
	
	wp_reset_postdata();
	} else {
	
	$table = $wpdb->prefix . 'als_log';

	$response= $wpdb->get_results(
			"SELECT query FROM $table 
			WHERE LOWER(query) LIKE LOWER('$q%') 
			LIMIT $count");
			
	if(is_array($response) AND count($response) >=1 ) {
		echo'<ul>'; 
		
		for($i=0; $i<count($response); $i++){
			$url = esc_url( home_url("/?s=") . $response[$i]->query );
			echo '<li>
				<a href="' . $url. '">'
				. $response[$i]->query .
				'</a></li>';
		}
		
		echo'</ul>';
		}
		
	else {
		echo '';
		}
	
	}

}


/**
 * Searches for posts matching the current request
 *
 *
 * @param $q the user query that should be used to fetch results
 *
 * @since  1.0.0
 *
 * @return array
 */
function als_get_search_results($q) {
	require_once plugin_dir_path( __FILE__ ) . 'class-als-search.php';
	$results=new Als_Search($q);
	$results=$results->results;
	$ids = array();

	if(is_array($results)) {
		foreach ($results as $result) {
			$ids[] = intval($result->ID);
		}
	}
	return $ids;
}

/**
 * Excutes a query
 * 
 * @param $ids an array of id's to fetch
 *
 *
 * @since  1.0.0
 *
 * @return $wpdb object
 */
function als_wp_query($ids) { 
	global $wpdb;
	if (count($ids) <1 ){
		return false;
	}
	$ids = implode(',' , $ids);
	$post_types = apply_filters('als_index_post_types', array('"post"','"page"'));
	return $wpdb->get_results("SELECT post_title FROM $wpdb->posts WHERE ID IN($ids) AND post_type IN (" . implode(', ', $post_types) . ")");
	

}

/**
 * Returns how many seconds/mins/hours/days/weeks/months ago
 *
 *
 * @param $past an earlier date
 * @param $current the later date, defaults to now()
 *
 * @since  1.0.0
 *
 * @return array
 */
function als_date_diff($past, $current=false) { 
	if(!$current){
		$current=date('Y-m-d h:i:s');
	}
	$past = strtotime($past);
	$current = strtotime($current);
	$diff = abs($past - $current);
	
	if ($diff<60) {
		Return ($diff . __(' Seconds ago.', 'als'));
	}
	
	if ($diff>=60 && $diff<(60*60)) {
		$diff = ceil($diff / 60);
		Return ($diff . __(' Minutes ago.', 'als'));
	}
	
	if ($diff>=(60*60) && $diff<(60*60*24)) {
		$diff = ceil($diff / (60*60));
		Return ($diff . __(' Hours ago.', 'als'));
	}
	
	if ($diff>=(60*60*24) && $diff<(60*60*24*7)) {
		$diff = ceil($diff / (60*60*24));
		Return ($diff . __(' Days ago.', 'als'));
	}
	
	if ($diff>=(60*60*24*7) && $diff<(60*60*24*7*4)) {
		$diff = ceil($diff / (60*60*24*7));
		Return ($diff . __(' Weeks ago.', 'als'));
	}
	
	
	if ($diff>=(60*60*24*7*4) && $diff<(60*60*24*7*4*12)) {
		$diff = ceil($diff / (60*60*24*7*4));
		Return ($diff . __(' Months ago.', 'als'));
	}

	$diff = ceil($diff / (60*60*24*7*4*12));
	Return ($diff . __(' Years ago.', 'als'));
}

/**
 * Fetches the registered post types with the exclusion of sponsored_results
 *
 * @param $array whether to return an array or string. Set true to return an array
 * @since  1.0.0
 *
 * @return array/string
 */
function als_post_types($array=false) { 
	$args = array('public' => true);
	$post_types = get_post_types($args);
	
	if(($key = array_search('sponsored_result', $post_types)) !==false) {
		unset($post_types[$key]);
	}
	
	if(!$array) {
		return (implode(', ', $post_types));
	}
	return $post_types;

}

/**
 * Returns the snippet that is shown in the search results page with optional highliting
 *
 *
 * @param $content the text to use for generating the snippet
 * @param $s the search string to highlight
 *
 * @since  1.0.0
 *
 * @return array
 */
function als_snippet($content, $s=false) { 
	if($s===false){
		$s = $_GET['s'];
	}
	$tags = explode(',', trim(get_option('als_excerpt_tags', '<strong>,<b>')));
	$content = do_shortcode(wp_kses(stripslashes($content), $tags));
	$s = als_remove_punct($s);
	
	$radius = intval(get_option('als_excerpt_length', 200) / 2);
	$s_len = strlen($s);
	$text_len = strlen($content);
	
	if($radius < $s_len) {
		$radius = $s_len;
	}

	$phrases = array_keys(als_get_terms($s));
	$excerpt = false;
	foreach ($phrases as $phrase) {
		$pos = strpos(strtolower($content), strtolower($phrase));
		
		//If current term missing from content move to next term
		if($pos === false) {
			continue;
		}
		
		$start = 0;
		$end_adavantage = 0;
		if ($pos > $radius) {
			$start = $pos - $radius;
		}else{
		$end_adavantage = $radius - $pos;
		}
		
		$end = $pos + $s_len + $radius + $end_adavantage;
		if ($end >= $text_len) {
			$end = $text_len;
		}
		
		//We don't need half words at the beginning and end of the snippet
		$full_word=false;
		while(!$full_word && $end !== $text_len) {
			$sub = substr($content, $end, 1);
			if($sub == ' ' || $sub == '.'){
			 $full_word=true;
			 break;
			}
			$end++;
		}
		
		$full_word=false;
		while(!$full_word && $start !== 0) {
			$start = substr($content, $start, 1);
			if( $start == ' ' or $start = '.'){
			 $full_word=true;
			 break;
			}
			$start--;
		}
		$start = intval($start);
		$end = intval($end);

		
		$excerpt = substr($content,$start , $end - $start) . ' ...';
		break;
		
	}
	
	if(!$excerpt){
		$excerpt = substr($content, 0, $radius * 2);
	
	}
	
	if(!strlen($excerpt) > $text_len){
		$excerpt = $excerpt . '...';
	}
	
	if ( get_option('als_highlight_term', 'yes') != 'yes'){
		return $excerpt;
	}
	
	for($i=0; $i<sizeOf($phrases); $i++) {
		$excerpt=preg_replace("/($phrases[$i])(?![^<]*>)/i", "<strong>\${1}</strong>", $excerpt);
	}

	return $excerpt;
	
}

/**
 * Crawls the homepage for results
 *
 * @param $q The query to crawl for
 *
 * @since  1.0.0
 *
 * @return null
 */
 
function als_crawl_results($q=false) { 
	if($q===false){
		return;
	}
	$url = home_url('?s=' . $q);
	$id = '#als-results';
	
	$response = wp_remote_get($url);
	if( is_array($response) AND $response['response']['code']==200) {
		$dom = new DOMDocument();
		$dom->validateOnParse = true;
		@$dom->loadHTML($response['body']);
		$results = $dom->saveHTML($dom->getElementById($id));
		
		if(!$results){
			_e( "We could not fetch results at the moment. Please try again.", "als");
			return;
		}
		
		echo $results ;
	}
	
	else {
		_e( "We could not fetch results at the moment. Please try again.", "als");
	}
}


/**
 * Fills the searches log table with dummy data
 *
 * @since  1.0.0
 *
 * @return null
 */
 
function als_dummy_data(){
global $wpdb;
	
	

	$table = $wpdb->prefix . 'als_log';

	$queries = array('Brian','Pappito','Adele','Creative','Create','write','google','bot','crawl','java','javascript','themeforest','codecanyon','namecheap','hostgator','account','password','email','join','president','music','videos','images','photos','view','delete','random','number','generator','online');
	
	$db_words = array();
	for ($i = 0; $i<200; $i++) {
		$first = mt_rand(0, count($queries)-1);
		$second = mt_rand(0, count($queries)-1);
		$word = $queries[$first] . ' ' . $queries[$second];
		$word2 = $queries[$second] . ' ' . $queries[$first];
		
		if(!in_array($word, $db_words)) 
		$db_words[] = $word;
		
		if(!in_array($word2, $db_words)) 
		$db_words[] = $word2;
		
	}
	
	$tri_words = array();
	for ($i = 0; $i<100; $i++) {
		$first = mt_rand(0, count($queries)-1);
		$second = mt_rand(0, count($queries)-1);
		$third = mt_rand(0, count($queries)-1);
		$word = array();
		$word[] = $queries[$first] . ' ' . $queries[$second] . ' ' . $queries[$third];
		$word[] = $queries[$second] . ' ' . $queries[$third] . ' ' . $queries[$first];
		$word[] = $queries[$third] . ' ' . $queries[$first] . ' ' . $queries[$second];
		$word[] = $queries[$first] . ' ' . $queries[$third] . ' ' . $queries[$second];
		$word[] = $queries[$second] . ' ' . $queries[$first] . ' ' . $queries[$third];
		$word[] = $queries[$third] . ' ' . $queries[$second] . ' ' . $queries[$first];
		
		foreach($word as $w){
		if(!in_array($w, $tri_words)) 
		$tri_words[] = $w;
		}
		
	}
	
	$words = array_merge($queries, $db_words, $tri_words);
	$insert_data = array();
	$dateinitial = strtotime('5 months ago');
	$date_now = time();
	foreach($words as $word){
		$searches = mt_rand(1, 100);
		$results = mt_rand(0, 100);
		$time = mt_rand($dateinitial, $date_now);
		$date = date('Y-m-d H:i:s', $time);
		
		$value = $wpdb->prepare("(%s, %d, %d, %s)",
			$word, $results, $searches, $date);

		array_push($insert_data, $value);

	}
	
	if (function_exists('wp_suspend_cache_addition')) 
		wp_suspend_cache_addition(true);
		
	if (!empty($insert_data)) {
		$insert_data = implode(', ',$insert_data);
		$query = "INSERT IGNORE INTO $table (query, hits, searches, time)
			VALUES $insert_data";
		$wpdb->query($query);
	}

	
	if (function_exists('wp_suspend_cache_addition')) {
		wp_suspend_cache_addition(false);
	}
	

}


/**
 * Display an als help tip.
 *
 * @since  1.0.0
 *
 * @param  string $tip        Help tip text
 * @param  bool   $allow_html Allow sanitized HTML if true or escape
 * @return string
 */
function als_help_tip( $tip, $allow_html = false ) {
	if ( $allow_html ) {
		$tip = wc_sanitize_tooltip( $tip );
	} else {
		$tip = esc_attr( $tip );
	}

	return '<span class="als-help-tip" data-tip="' . $tip . '"></span>';
}