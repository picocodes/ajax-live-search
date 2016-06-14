<?php

/**
 * Fired during plugin deactivation
 *
 * @author   Picocodes
 * @package  Ajax Live Search Lite
 * @subpackage Als/includes
 * @version  1.0
 */
 
/**
 * Fired during plugin deactivation
 *
 * @since      1.0.0
 *
 * @package    Ajax Live Search Lite
 * @subpackage Als/includes
 */

class Als_Deactivator {

	/**
	 * Deletes the created tables and registered options
	 *
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		global $wpdb;
	
		delete_option('als_options');
		
		$index_table = $wpdb->prefix . "als_index";
		$searches_log_table = $wpdb->prefix . "als_log";
		
		if($wpdb->get_var("SHOW TABLES LIKE '$index_table'") == $index_table) {
			$sql = "DROP TABLE $index_table";
			$wpdb->query($sql);
		}

		if($wpdb->get_var("SHOW TABLES LIKE '$searches_log_table'") == $searches_log_table) {
			$sql = "DROP TABLE $searches_log_table";
			$wpdb->query($sql);
		}
	}

}
