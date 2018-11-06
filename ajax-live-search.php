<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             2.0.0
 * @package           Ajax Live Search Lite
 *
 * @wordpress-plugin
 * Plugin Name:       Ajax Live Search
 * Plugin URI:        http://ajaxlivesearch.xyz
 * Description:       Supercharge your WordPress search functionality by adding autosuggest, live search, relevance-based search and tons of other cool features.
 * Version:           2.3.0
 * Author:            Picocodes
 * Author URI:        https://github.com/picocodes
 * License:           Extended License
 * License URI:      
 * Text Domain:       als
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ALS__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ALS__CACHE_DIR', plugin_dir_path( __FILE__ ) . 'cache/' );
define( 'ALS_VERSION', '2.1.0-lite' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-als-activator.php
 */
function activate_als() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-als-activator.php';
	Als_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-als-deactivator.php
 */
function deactivate_als() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-als-deactivator.php';
	Als_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_als' );
register_deactivation_hook( __FILE__, 'deactivate_als' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-als.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_als() {

	$plugin = new Als();
	$plugin->run();

}
run_als();
