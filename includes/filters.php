<?php
/**
 * Defines all the action hooks that we execute
 *
 *
 * @since             1.0
 * @package           Ajax Live Search
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
		
		$form ='<div class="als-form">' . $form . '<div class="alsSuggestions"></div></div>';
		

	
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
		$search_template = ALS__PLUGIN_DIR . 'includes/search-template.php';
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
	 * Filter to add more columns on listings overview page
	 *
	 * @since 1.0
	 *
	 * @param $allow bool
	 *
	 * @return bool
	 */

	function als_add_listings_columns($columns) {
		return array('cb' => '<input type="checkbox" />',
			'title' => __('Title', 'als'),
			'url' => __('Link', 'als'),
			'tags' => __('Tags', 'als'),
			'date' => __('Date', 'als'));
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
	
	/**
	 * Filters search query to excecute the author_in modifier
	 *
	 * @param $array array of author ids currently being searched
	 * @param $q search term
	 *
	 * @since 1.1.0
	 * @return array
	 */

	function als_author_in($array, $q) {
		$authors = als_query_modifier( 'author_in:', $q );
		
		if(!$authors ){
			
			return $array;
			
		}
		return (array_merge( $array, explode(',', $authors)));
	}
	
	/**
	 * Filters search query to excecute the author_not_in modifier
	 *
	 * @param $array array of author ids currently being excluded
	 * @param $q search term
	 *
	 * @since 1.1.0
	 * @return array
	 */

	function als_author_not_in($array, $q) {
		$authors = als_query_modifier( 'author_not_in:', $q );
		
		if(!$authors ){
			
			return $array;
			
		}
		return (array_merge( $array, explode(',', $authors)));
	}
	
	/**
	 * Filters search query to excecute the author_name modifier
	 *
	 * @param $string name of author being searched for
	 * @param $q search term
	 *
	 * @since 1.1.0
	 * @return string
	 */

	function als_author_name($string, $q) {
		$author = als_query_modifier( 'author:', $q );
		
		if(!$author ){
			
			$author = als_query_modifier( '@', $q );
			
		}
		
		if(!$author ){
			
			return $string;
			
		}
		return $author;
	}
	
	/**
	 * Filters search query to excecute the cat modifier
	 *
	 * @param $int id of category being searched on
	 * @param $q search term
	 *
	 * @since 1.1.0
	 * @return int
	 */

	function als_cat($int, $q) {
		$category = als_query_modifier( 'cat:', $q );
		
		if(!$category ){
			
			return $int;
			
		}
		return $category;
	}
	
	/**
	 * Filters search query to excecute the category modifier
	 *
	 * @param $string name of category being searched on
	 * @param $q search term
	 *
	 * @since 1.1.0
	 * @return string
	 */

	function als_category_name($string, $q) {
		$category = als_query_modifier( 'category:', $q );
		
		if(!$category ){
			
			return $string;
			
		}
		return category;
	}
	
	/**
	 * Filters search query to excecute the tagged modifier
	 *
	 * @param $string name of tag being searched on
	 * @param $q search term
	 *
	 * @since 1.1.0
	 * @return string
	 */

	function als_tag($string, $q) {
		$category = als_query_modifier( 'tagged:', $q );
		
		if(!$category ){
			
			return $string;
			
		}
		return category;
	}
	
	/**
	 * Filters search query to excecute the category_in modifier
	 *
	 * @param $array array of category ids currently being searched for
	 * @param $q search term
	 *
	 * @since 1.1.0
	 * @return array
	 */

	function als_category_in($array, $q) {
		$cats = als_query_modifier( 'category_in:', $q );
		
		if(!$cats ){
			
			return $array;
			
		}
		return (array_merge( $array, explode(',', $cats)));
	}
	
	/**
	 * Filters search query to excecute the category_not_in modifier
	 *
	 * @param $array array of category ids currently being excluded
	 * @param $q search term
	 *
	 * @since 1.1.0
	 * @return array
	 */

	function als_category_not_in($array, $q) {
		$cats = als_query_modifier( 'category_not_in:', $q );
		
		if(!$cats ){
			
			return $array;
			
		}
		return (array_merge( $array, explode(',', $cats)));
	}
	
	
	/**
	 * Filters search query to excecute the before: and after: modifier
	 *
	 * @param $array array of date params currently being used
	 * @param $q search term
	 *
	 * @since 1.1.0
	 * @return array
	 */

	function als_date_query($array, $q) {
		$before = als_query_modifier( 'before:', $q );
		$after = als_query_modifier( 'after:', $q );
		$date = array();
		
		if($before ){
			
			$date['before'] = $before; 
			
		}
		
		if($after ){
			
			$date['after'] = $after; 
			
		}
		$full = array($date);
		return (array_merge( $array, $full));
	}

	/**
	 * Filters search query to excecute the post_types modifier
	 *
	 * @param $array array of searchable post_types
	 * @param $q search term
	 *
	 * @since 1.1.0
	 * @return array
	 */

	function als_wp_query_post_types($array, $q) {
		$types = als_query_modifier( 'post_types:', $q );
		
		array_walk($array, function(&$value){$value=trim($value, '"');});
		$array = array_unique($array); //John really messed things up here
		if(!$types ){
			
			return $array;
			
		} 

		$types = explode(',', $types);
		$to_show = array();
		
		for($i=0; $i<count($types); $i++){
			
			if(in_array($types[$i], $array)){
				
				$to_show[] = $types[$i];
				
			}
			
		}

		return $to_show;
	}
	
	/**
	 * Filters search query to show all results on a single page
	 *
	 * @param $int integer of posts per page
	 * @param $q search term
	 *
	 * @since 1.1.0
	 * @return array
	 */

	function als_all_posts_on_a_single_page($int, $q) {
		$all = als_query_modifier( 'all_results:', $q );
		
		if(!$all ){
			
			return $int;
			
		}
		
		return -1;
	}
	
	/**
	 * Adds more setting fields to the searching tab
	 *
	 * @param $int integer of posts per page
	 * @param $q search term
	 *
	 * @since 1.1.0
	 * @return array
	 */

	function als_searching_settings($fields) {
		$last = array_pop($fields);
		$post_types = apply_filters('als_index_post_types', array('post','page','product'));
		
		array_walk($post_types, function(&$value){$value=trim($value, '"');});
		$post_types = array_values(array_unique($post_types)); //Seriously john!
		
		$extra_fields=array();
		for($i=0; $i<count($post_types); $i++){
			if(strlen($post_types[$i]) != 0) {
			$extra_fields[] = array(
				'title'    => $post_types[$i] . ' weight',
				'desc'     =>__('What weight should this post type be given in search results?', 'als'),
				'id'       => "als_{$post_types[$i]}_weight",
				'css'      => 'width: 48px;',
				'default'  => '1',
				'type'     => 'text',
				'desc_tip' =>  true,
			);
			}
			
		}
		
		$extra_fields[] = $last;
		return array_merge($fields, $extra_fields);
	}

/**
 * Adds custom classes to the array of body classes.
 *
 * @since 2.1.0
 *
 * @param array $classes Classes for the body element.
 * @return array (Maybe) filtered body classes.
 */
function als_body_classes( $classes ) {
	$classes[] = 'aLs-' . ALS_VERSION;
	

	return $classes;
}