<?php
/**
 * Contains the internationalization class
 *
 *
 * @since             1.0
 * @package           Ajax Live Search Lite
 *
 */
 
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 *
 * @package    Ajax Live Search Lite
 * @subpackage als/includes
 */

class Als_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'als-lite',
			false,
			ALS__PLUGIN_DIR . '/languages/'
		);

	}



}
