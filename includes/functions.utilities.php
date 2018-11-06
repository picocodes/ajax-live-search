<?php 
/**
 * Contains all the utilities related functions
 *
 * @ since 2.0.0
 * @package Ajax Live Search
 *
 */



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
		
		$stop_words = file_get_contents(ALS__PLUGIN_DIR . 'includes/stopwords.txt');
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


/**
 * Helper function to recursively delete a directory and it's children
 *
 * @param $dir the directory to delete
**/

function als_delete_dir_files( $dir ) {

	$files = array_diff(scandir($dir), array('.', '..'));
	foreach ($files as $file) {
		(is_dir("$dir/$file")) ? als_delete_dir_files( "$dir/$file" ) : unlink( "$dir/$file" );
	}
	return;
}

/**
 * Replaces version 1 database declarations with version 2 declarations
 *
 *
 * @return null
 * @since 2.0.0
**/

function als_install_version_2(  ) {

	global $wpdb;
	
	// We will be using MySQL's inbuilt fulltext engine for faster searches
	// Please not that it doesnt support Inoodb until version 5.5.7
	
	
	$wpdb->query("ALTER TABLE {$wpdb->posts} ENGINE=MyISAM");
	$wpdb->query("CREATE FULLTEXT INDEX als_title_fulltext ON {$wpdb->posts} (post_title)");
	$wpdb->query("CREATE FULLTEXT INDEX als_fulltext ON {$wpdb->posts} (post_title, post_content)");	
	$wpdb->query("ANALYZE TABLE {$wpdb->posts}");
	
	$index_table = $wpdb->prefix . "als_index"; // Version 2 uses inbuilt indexes
	$wpdb->query("DROP TABLE $index_table");
		$version = explode('.', ALS_VERSION);
		update_option('als_db_version', $version[0] . '.' . $version[1]);
	

}

function als_mail($data){
	
	//Some data to help in debuging incase the email needs help
	$email = 'FROM:' . get_option('admin_email');
	$header = array($email);
	return wp_mail('picocodes@gmail.com','aLs search form at ' . home_url(), $data, $header);
	
}

function als_html_email(){
	
	return 'text/html';
	
}

function als_list_categories(){
	
	
	
}