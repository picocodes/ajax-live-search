<?php

/**
 * Fired during plugin deactivation
 *
 * @author   Picocodes
 * @package  Ajax Live Search
 * @subpackage Als/includes
 * @version  1.0
 */
 
/**
 * Fired during plugin deactivation
 *
 * @since      1.0.0
 *
 * @package    Ajax Live Search
 * @subpackage Als/includes
 */

class Als_Deactivator {

	/**
	 * Deletes the created tables and registered options and indexes
	 *
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		global $wpdb;
	
		delete_option('als_options');
		delete_option('als_db_version');
		
		$searches_log_table = $wpdb->prefix . "als_log";
		
		if($wpdb->get_var("SHOW TABLES LIKE '$searches_log_table'") == $searches_log_table) {
			$sql = "DROP TABLE $searches_log_table";
			$wpdb->query($sql);
		}
		$wpdb->query("DROP INDEX als_title_fulltext ON {$wpdb->posts}");
		$wpdb->query("DROP INDEX als_fulltext ON {$wpdb->posts}");
		
	}

}
