<?php 
/**
 * Contains all the search related functions
 *
 * @ since 2.0.0
 * @package Ajax Live Search
 *
 */
 
	/**
	 * Fetches all published posts
	 * Returns an array of fetched posts ids
	 * @param $post_types an array of post types to fetch
	 * @param $limit maximum number of posts to fetch and an optional integer
	 * @since    1.0.0
	 */	
	 
	function als_fetch_posts($post_types, $limit=1000000){
		
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
			
		$q = "SELECT post.ID
			FROM $wpdb->posts as post
			LEFT JOIN $wpdb->posts as parent ON (post.post_parent=parent.ID)
			WHERE $restriction LIMIT $limit";

		return $wpdb->get_results($q);

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
			"SELECT modified FROM $table 
			WHERE LOWER(modified) LIKE LOWER('$q%') 
			LIMIT $count");
			
	if(is_array($response) AND count($response) >=1 ) {
		echo'<ul>'; 
		
		for($i=0; $i<count($response); $i++){
			$url = esc_url( home_url("/?s=") . urlencode($response[$i]->modified ));
			echo '<li>
				<a href="' . $url. '">'
				. $response[$i]->modified .
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
 * @return array|bool an array of post ids or false if no results  found
 */
function als_get_search_results($q) {
	require_once plugin_dir_path( __FILE__ ) . 'class-als-search.php';
	$results=new Als_Search($q);
	return $results->results;
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
	$content = wp_kses(do_shortcode(stripslashes($content)), $tags);
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
		//$full_word=false;
		//while(!$full_word && $end !== $text_len) {
		//	$sub = substr($content, $end, 1);
		//	if($sub == ' ' || $sub == '.' || $sub == '!' || $sub == '?' || $sub == '-'){
		//	 $full_word=true;
		//	 break;
		//	}
		//	$end++;
		//}
		
		//$full_word=false;
		//while(!$full_word && $start !== 0) {
		//	$start = substr($content, $start, 1);
		//	if( $start == ' ' or $start = '.'){
		//	 $full_word=true;
		//	 break;
		//	}
		//	$start--;
		//}
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
 * Checks if their is any user matching the current search term
 *
 * @param $s the search term defaults to $_GET['s'];
 *
 * @since  1.0.0
 *
 * @return array/false
 */
function als_has_authors($s=false) {
	global $wpdb;
	if($s===false){
		$s = $_GET['s'];
	}
	
	$terms = array_keys(als_get_terms($s));
	if(count($terms)<1 || get_option('als_content_options_authors', 'yes') != 'yes' || is_paged()){
		return false;
	}
	$conditions = false;
	foreach($terms as $term){
		$term = stripslashes($term);
		if(!$conditions) {
			$conditions .= 'display_name LIKE "' . $term . '%"';
		}else{
			$conditions .= 'OR display_name LIKE "' . $term . '%"';
		}
	}
	if(!$conditions){
		$conditions = '1=1';
	}
	
	$users = $wpdb->get_results("SELECT ID, user_email, display_name FROM $wpdb->users WHERE $conditions LIMIT 3");
	
	if(!is_array($users) or count($users)<1) {
		return false;
	}
	
	return $users;
}


/**
 * outputs the users provided by the $users param
 *
 * @param $users an array of user objects
 *
 * @since  1.0.0
 *
 * @return null/bool
 */
function als_show_authors($users= false) { 
	
	if($users === false or !is_array($users)){
		return false;
	}

	//We have users
	do_action('als_before_authors');
	foreach ($users as $user) {
	?>
		<article id="post-<?php the_ID(); ?>" <?php post_class('als-snippet'); ?>>
				<div class="alsthumbnail">
						<a class="als-post-thumbnail" href="<?php echo esc_url( get_author_posts_url($user->ID)); ?>" aria-hidden="true">
						<?php
						echo get_avatar($user->ID, '100px');
						?>
						</a>
				</div>
				<div class="alssnippet">
					<header class="entry-header als-header">
						<h2><a href="<?php echo esc_url( get_author_posts_url($user->ID)); ?>"><?php echo $user->display_name; ?></a></h2>

					</header><!-- .entry-header -->
				

					<div class="entry-summary">
						<?php echo get_user_meta($user->ID, 'description', true); ?>
					</div><!-- .entry-summary -->
				</div>
		</article><!-- #post-## -->
				
			<?php
	}
	do_action('als_after_authors');
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
$data = file_get_contents('D:\Flash\voc.txt');	
	

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
		
		$value = $wpdb->prepare("(%s,%s, %d, %d, %s)",
			$word, $word, $results, $searches, $date);

		array_push($insert_data, $value);

	}
	
	if (function_exists('wp_suspend_cache_addition')) 
		wp_suspend_cache_addition(true);
		
	if (!empty($insert_data)) {
		$insert_data = implode(', ',$insert_data);
		$query = "INSERT IGNORE INTO $table (query, modified, hits, searches, time)
			VALUES $insert_data";
		$wpdb->query($query);
	}

	
	if (function_exists('wp_suspend_cache_addition')) {
		wp_suspend_cache_addition(false);
	}
	

}
