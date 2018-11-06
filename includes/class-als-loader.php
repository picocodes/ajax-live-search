<?php

/**
 * Register all actions and filters for the plugin
 *
 * @since      1.0.0
 *
 * @package Ajax Live Search
 * @subpackage Als/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Ajax Live Search
 * @subpackage als/includes
 * @author     Picocodes <picocodes@gmail.com>
 */
 

class Als_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		require_once( ALS__PLUGIN_DIR . 'includes/filters.php'    ); //Filters
		require_once( ALS__PLUGIN_DIR . 'includes/actions.php'    ); //Actions
		require_once( ALS__PLUGIN_DIR . 'includes/admin/settings.php'    ); //Settings api

		$this->actions = array();
		$this->filters = array();
		
		if(get_option('als_enable_search', 'yes') != 'no'){
			
			$this->add_action( 'pre_get_posts', 'als_pre_get_posts', 10000 );
			
			$this->add_filter( 'als_index_post_types', 'als_index_post_types' );
			$this->add_filter( 'als_use_custom_search', 'als_use_custom_search' );
			$this->add_filter( 'als_author_in', 'als_author_in', 10, 2 );
			$this->add_filter( 'als_author_not_in', 'als_author_not_in', 10, 2 );
			$this->add_filter( 'als_author_name', 'als_author_name', 10, 2 );
			$this->add_filter( 'als_cat', 'als_cat', 10, 2 );
			$this->add_filter( 'als_category_name', 'als_category_name', 10, 2 );
			$this->add_filter( 'als_category_in', 'als_category_in', 10, 2 );
			$this->add_filter( 'als_category_not_in', 'als_category_not_in', 10, 2 );
			$this->add_filter( 'als_tag', 'als_tag', 10, 2 );
			$this->add_filter( 'als_posts_per_page', 'als_all_posts_on_a_single_page', 10, 2 );
			$this->add_filter( 'als_date_query', 'als_date_query', 10, 2 );
			$this->add_filter( 'als_wp_query_post_types', 'als_wp_query_post_types', 10, 2 );
			$this->add_filter( 'als_searching_settings', 'als_searching_settings');
			$this->add_filter( 'als_search_conditions', 'als_search_conditions' );

			
		}
		
		if(get_option('als_enable_render', 'yes') != 'no'){
			$this->add_filter( 'search_template', 'als_search_template' );

			
			
		}
		$this->add_action( 'wp_ajax_alsgetresults', 'als_ajax_results' );
		$this->add_action( 'wp_ajax_nopriv_alsgetresults', 'als_ajax_results' );
		$this->add_action( 'wp_ajax_alsgetsuggestions', 'als_get_suggestions' );
		$this->add_action( 'wp_ajax_nopriv_alsgetsuggestions', 'als_get_suggestions' );
		$this->add_action( 'wp_enqueue_scripts', 'als_frontend_scripts' );		
		$this->add_action( 'admin_menu', 'Als_Admin_Settings::add_menu_page' );
		$this->add_action( 'admin_init', 'als_admin_init' );
		$this->add_action( 'admin_head', 'als_remove_menus' );
		$this->add_action( 'wp_head', 'als_wp_head' );
		
		add_filter( 'body_class', 'als_body_classes' );
		$this->add_filter( 'get_search_form', 'als_form_filter' );
	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress action that is being registered.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. he priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. he priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
	 */
	public function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $callback, $priority, $accepted_args );
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         The priority at which the function should be fired.
	 * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $callback, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;

	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], $hook['callback'], $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], $hook['callback'], $hook['priority'], $hook['accepted_args'] );
		}

	}

}
