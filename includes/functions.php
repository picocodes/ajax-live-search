<?php

/**
 * Contains all the core functions
 *
 * @ since 1.0.0
 * @package Ajax Live Search
 *
 */
	 
	 require_once( ALS__PLUGIN_DIR . 'includes/porter-stemmer.php'    );
	 require_once( ALS__PLUGIN_DIR . 'includes/functions.search.php'    );
	  require_once( ALS__PLUGIN_DIR . 'includes/functions.format.php'    );
	  require_once( ALS__PLUGIN_DIR . 'includes/functions.utilities.php'    );
	 
	 //The version below happens to be a little bit slow though more accurate
	 //require_once( ALS__PLUGIN_DIR . 'includes/PorterStemmer2.php'    );

	 

 function als_dummy_posts(){
	 global $wpdb;
	 $data = explode(' ', als_remove_punct(file_get_contents('D:\Flash\voc.txt')));	
	 
		$args = array('public' => true);
		$post_types = get_post_types($args);
		$users = 21;
		$sentences = array(); //10 word sentences
		
		for($i=0; $i<count($data); $i++){
			
			$sentence = ' ';
			for($j=0; $j<10; $j++){
				
				$sentence .= $data[mt_rand(0, count($data)-1)] . ' ';
				
			}
			
			$sentence .= '.';
			$sentences[]=$sentence;
			
		}
		
		$posts = array();//20-400 sentence posts (200-4000 words)
		$titles = array();
		for($i=0; $i<100; $i++){  //100 posts
			$post = '';
			$sentece_count = mt_rand(20, 400);
			for($j=0; $j<$sentece_count; $j++){
				
				$post .=$sentences[mt_rand(0, count($sentences)-1)];
				
			}
			
			$posts[] = $post;
			$titles[] = $sentences[mt_rand(0, count($sentences)-1)];
			
		}
		
	$insert_data = array();
	$dateinitial = strtotime('5 months ago');
	$date_now = time();
	
	for($i=0; $i<count($titles); $i++){
		$post_title = $titles[$i];
		$post_name = str_replace(' ', '-', str_replace('.','',$post_title));
		$post_status = 'publish';
		$post_author = mt_rand(1, $users);
		$post_content = $posts[$i];
		$time = mt_rand($dateinitial, $date_now);
		$post_date = date('Y-m-d H:i:s', $time);
		
		$value = $wpdb->prepare("(%s, %s,%s, %d, %s, %s)",
			$post_title, $post_name, $post_status, $post_author, $post_content, $post_date);

		array_push($insert_data, $value);
		usleep(100);

	}
	
	if (function_exists('wp_suspend_cache_addition')) 
		wp_suspend_cache_addition(true);
		
	if (!empty($insert_data)) {
		$insert_data = implode(', ',$insert_data);
	$query = "INSERT IGNORE INTO {$wpdb->posts} (post_title, post_name, post_status, post_author, post_content, post_date)
			VALUES $insert_data";
		$wpdb->query($query);
	}

	
	if (function_exists('wp_suspend_cache_addition')) {
		wp_suspend_cache_addition(false);
	}
	 
 }