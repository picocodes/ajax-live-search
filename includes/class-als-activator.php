<?php

/**
 * Fired during plugin activation
 *
 * @since 1.0.0
 *
 * @package    Ajax Live Search
 * @subpackage Als/includes
 */
 
 require_once( ALS__PLUGIN_DIR . 'includes/functions.php'    );
	 
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
	 * Calls the functions needed to create the initial database calls
	 *
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
	if (function_exists('wp_suspend_cache_addition')) 
		wp_suspend_cache_addition(true);	

	if (!ini_get('safe_mode')) 	{
		set_time_limit(0);
	}
	
	self::als_db_options();
	self::als_create_tables();
	
	
	if (function_exists('wp_suspend_cache_addition')) {
		wp_suspend_cache_addition(false);
		}
		
	set_transient( '_welcome_screen_activation_redirect', true, 60 );	
	}
	
	/**
	 * Defines user editable database options.
	 *
	 * Then saves them to the database.
	 *
	 * @since    1.0.0
	 */
	 
	public static function als_db_options() {

	$version = explode('.', ALS_VERSION);
	add_option('als_db_version', $version[0] . '.' . $version[1]);

	}
	/**
	 * Creates the searches log table
	 *
	 *
	 * @since    1.0.0
	 */
	 
	public static function als_create_tables() {
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
    

	$searches_log_table = $wpdb->prefix . "als_log";
	$wpdb->query("DROP TABLE IF EXISTS $searches_log_table;");

	
		$sql = "CREATE TABLE " . $searches_log_table . " (id bigint(9) NOT NULL AUTO_INCREMENT, 
		query varchar(200) NOT NULL,
		modified varchar(200) NOT NULL,
		hits mediumint(9) NOT NULL DEFAULT '0',
		searches mediumint(9) NOT NULL DEFAULT '1',
		time timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	    UNIQUE KEY id (id)) $charset_collate;";

		dbDelta($sql);
		
		
	// We will be using MySQL's inbuilt fulltext engine for faster searches
	// Please not that it doesnt support Inoodb until version 5.5.7
	
	$wpdb->query("ALTER TABLE {$wpdb->posts} ENGINE=MyISAM");
	$wpdb->query("CREATE FULLTEXT INDEX als_title_fulltext ON {$wpdb->posts} (post_title)");
	$wpdb->query("CREATE FULLTEXT INDEX als_fulltext ON {$wpdb->posts} (post_title, post_content)");	
	$wpdb->query("ANALYZE TABLE {$wpdb->posts}");
		
}

}
