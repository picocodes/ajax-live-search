<?php
/**
 * Defines all the action hooks that we execute
 *
 *
 * @since             1.0
 * @package           Ajax Live Search Lite
 *
 */
 	/**
	 * Callback to the get_search_form filter, 
	 * allows us to edit search forms generated using get_search_form()
	 *
	 * @since 1.0
	 *
	 * @param $form string The generated markup for the search form
	 *
	 * @return string Markup for the search form
	 */
	function als_form_filter( $form ) {
	
		// WE dont want the browser breaking our form with lazy autocompletes
		$form = str_replace( 'name="s"', 'name="s" autocomplete="off" ', $form );
		
		if(isset($_GET["s"])){
			// Wp doesnt automatically set the value because we remoe it from the search parameter
			$form = str_replace( 'value name="s" autocomplete="off"', 'value="' . esc_attr($_GET["s"]) . '" name="s" autocomplete="off"', $form );
		}
		
		$form ='<div class="als">' . $form . '<div class="alsSuggestions"></div></div>';
		

	
		return $form;
				
	}
	
	 /**
	 * Filter to change the search page template
	 *
	 * @since 1.0
	 *
	 * @param $search_template string The path to the original template
	 *
	 * @return string The path to our custom template
	 */

	function als_search_template($search_template) {
		$search_template = ALS_LITE__PLUGIN_DIR . 'includes/search-template.php';
		return $search_template;
	}

	
	/**
	 * Filter to add extra search conditions
	 *
	 * @since 1.0
	 *
	 * @param $conditions array An array of existing conditions
	 *
	 * @return array an array of the modified conditions
	 */

	function als_search_conditions($conditions) {
		$conditions['posts'] .=' AND post_type NOT IN ("nav_menu_item", "attachment")';
		
		return $conditions;
	}


	/**
	 * Filter to allow or deny the use of our inbuilt search function
	 *
	 * @since 1.0
	 *
	 * @param $allow bool
	 *
	 * @return bool
	 */

	function als_use_custom_search($allow) {
			if (!isset($_GET['s'])) {
				$allow = false;						// Only works on search pages
			}
			
			return $allow;
	}
	
	/**
	 * Filters indexable post types
	 *
	 * @param $types an array of current post types
	 *
	 * @since 1.0
	 * @return array
	 */

	function als_index_post_types($types) {
		$post_types = get_option( 'als_post_types', als_post_types(false) ); //set true for array
		if(empty($post_types)){
			return $types;
		}
		
		$post_types = explode(' ', als_remove_punct($post_types));
		$new = array();
		foreach ($post_types as $type){
			$new[] = '"' . $type . '"';
		}
		
		foreach ($new as $type){
			if(($key = array_search($type, $types)) !==false) {
				unset($new[$key]);
			}
		}
		
		return (array_merge( $types, $new));
	}

