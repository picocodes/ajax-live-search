<?php

/**
 * Fired during plugin activation
 *
 * @since 1.0.0
 *
 * @package    Ajax Live Search
 * @subpackage Als/includes
 */
 
 require_once( ALS_LITE__PLUGIN_DIR . 'includes/functions.php'    );
	 
/**
 * Fired during plugin activation.
 *
 * This class automagically creates a new index of the existing posts 
 *
 * @since 1.0.0
 * @package    Ajax Live Search
 * @subpackage Als/includes
 * @author     picocodes <picocodes@gmail.com>
 */
class Als_Activator {

	/**
	 * Calls the functions need to build an index
	 *
	 * And save the index to the database
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
	
	self::als_db_options();
	self::als_create_index();
	
	}
	
	/**
	 * Defines user editable database options.
	 *
	 * Then saves them to the database.
	 *
	 * @since    1.0.0
	 */
	 
	public static function als_db_options() {

	add_option('als_db_version', 1);
	add_option('als_indexed_posts', 0);
	add_option('als_last_indexed', 0);
	add_option('als_highest_indexed', 0);

	}
	/**
	 * Creates the initial index from the existing posts
	 *
	 * uses the indexing class
	 *
	 * @since    1.0.0
	 */
	 
	public static function als_create_index() {
	global $wpdb;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$charset_collate_bin_column = '';
	$charset_collate = '';

	if (!empty($wpdb->charset)) {
        $charset_collate_bin_column = "CHARACTER SET $wpdb->charset";
		$charset_collate = "DEFAULT $charset_collate_bin_column";
	}
	if (strpos($wpdb->collate, "_") > 0) {
        $charset_collate_bin_column .= " COLLATE " . substr($wpdb->collate, 0, strpos($wpdb->collate, '_')) . "_bin";
        $charset_collate .= " COLLATE $wpdb->collate";
    } else {
    	if ($wpdb->collate == '' && $wpdb->charset == "utf8") {
	        $charset_collate_bin_column .= " COLLATE utf8_bin";
	    }
    }
    
	$index_table = $wpdb->prefix . "als_index";	
	$searches_log_table = $wpdb->prefix . "als_log";
	
		$sql = "CREATE TABLE " . $index_table . " (id int(11) NOT NULL AUTO_INCREMENT,
		post_id bigint(20) NOT NULL DEFAULT '0', 
		term varchar(60) NOT NULL DEFAULT '0', 
		tf FLOAT(11) NOT NULL DEFAULT '0', 
		relevance FLOAT(11) NOT NULL DEFAULT '0',
		count int(11) NOT NULL DEFAULT '0',
		UNIQUE KEY id (id)) $charset_collate";
		
		dbDelta($sql);

		//$sql = "ALTER TABLE $index_table ADD PRIMARY KEY(`id`)";
		//$wpdb->get_results($sql);
		
		//$sql = "ALTER TABLE $index_table ALTER TABLE `wp_als_index` ADD INDEX(`term`)";
		//$wpdb->get_results($sql);

		$sql = "CREATE TABLE " . $searches_log_table . " (id bigint(9) NOT NULL AUTO_INCREMENT, 
		query varchar(200) NOT NULL,
		hits mediumint(9) NOT NULL DEFAULT '0',
		searches mediumint(9) NOT NULL DEFAULT '1',
		time timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	    UNIQUE KEY id (id)) $charset_collate;";

		dbDelta($sql);
		

	if (function_exists('wp_suspend_cache_addition')) 
		wp_suspend_cache_addition(true);	

	if (!ini_get('safe_mode')) 	{
		set_time_limit(0);
	}
	
	$post_types = apply_filters('als_index_post_types', array('"post"','"page"'));

	$content = als_fetch_posts($post_types, 100);
	
	update_option('als_indexed_posts', count($content));
	
	foreach ($content as $post) {
		als_single_index($post->ID, false);
	}
	
	$wpdb->query("ANALYZE TABLE $index_table");
	// To prevent empty indices

	if (function_exists('wp_suspend_cache_addition')) {
		wp_suspend_cache_addition(false);
		}

}

}
