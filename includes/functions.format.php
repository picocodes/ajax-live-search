<?php 
/**
 * Contains all the formating/validation related functions
 *
 * @ since 2.0.0
 * @package Ajax Live Search
 *
 */
 
/**
 * Clean variables using sanitize_text_field.
 * @param string|array $var text to be cleaned
 * @return string|array
 * @ since 1.0.0
 */

function als_clean( $var ) {
	return is_array( $var ) ? array_map( 'wc_clean', $var ) : sanitize_text_field( $var );
}


/**
 * Validates the array type search filters
 *
 * @param $array the array to validate 
 *
 * @return bool true on validate or false on invalid value
 * @ since 2.0.0
**/

function als_is_array( $array ) {
	if(is_array($array) AND count($array)!=0) {
		
		return true;
		
	}
	return false;

}

/**
 * Validates the string filters for searches
 *
 * @param $array the array to validate 
 *
 * @return bool true on validate or false on invalid value
 * @ since 2.0.0
**/

function als_is_string( $string ) {
	if(!is_null($string) AND strlen($string) !=0) {
		
		return true;
		
	}
	return false;

}

 	/**
	 * Removes all punctuations from a string
	 * 
	 * @param a punctuated string
	 * @param $preserve_basics whether or not to preserve basic punctuations like fullstops and comas
	 * @return modified string
	 * @since    1.0.0
	 */
	 function als_remove_punct($a, $preserve_basics=false) {

		$a = preg_replace ('/<[^>]*>/', ' ', $a); 
    
	    $a = str_replace("\r", ' ', $a);    // --- replace with empty space
	    $a = str_replace("\n", ' ', $a);   // --- replace with space
	    $a = str_replace("\t", ' ', $a);   // --- replace with space		
		
		$a = stripslashes($a);

		$a = str_replace('ß', 'ss', $a);
		
		if(!$preserve_basics){
			$a = str_replace("·", '', $a);
			$a = str_replace("'", ' ', $a);
			$a = str_replace("’", ' ', $a);
			$a = str_replace("‘", ' ', $a);
			$a = str_replace("”", ' ', $a);
			$a = str_replace("“", ' ', $a);
			$a = str_replace("„", ' ', $a);
			$a = str_replace("´", ' ', $a);
			$a = str_replace("—", ' ', $a);
			$a = str_replace("–", ' ', $a);
			$a = str_replace("×", ' ', $a);
			$a = preg_replace('/[[:punct:]]+/u', ' ', $a);
		}
		$a = str_replace("…", '', $a);
		$a = str_replace("€", '', $a);
		$a = str_replace("&shy;", '', $a);

		$a = str_replace(chr(194) . chr(160), ' ', $a);
		$a = str_replace("&nbsp;", ' ', $a);
		$a = str_replace('&#8217;', ' ', $a);

        $a = preg_replace('/[[:space:]]+/', ' ', $a);
		$a = trim($a);

        return $a;
}

/**
 * Get's a given search modifier from a search term
 *
 * @param $modifier the modifier eg intittle:
 * @param $q the query
 *
 * @return bool true on validate or false on invalid value
 * @ since 2.0.0
**/

function als_query_modifier( $modifier, $q ) {
	// the value to be modified starts with a quote, we use it as our delimeter
	// Otherwise, we use spaces as delimiters
	
	$is_quoted = false;
	$start = stripos($q, $modifier);
	$regex = $modifier . '([\'"]{1})';
	$q = stripslashes($q); //Took me 4 hours to realise that the query has been escaped with slashes
	if($start === false){
		
		return false; // end early to save resources
		
	} else if(preg_match("/$regex/i", $q, $matches)){ 
		
		$is_quoted = true;
		$quote=$matches[1];
		
	}
	
	$regex = '/\b' . $modifier . '(.+?)\s/i'; //captures the value assigned to a modifier
	
	if($is_quoted){
		
		$regex = '/\b' . $modifier . $quote . '(.+?)' . $quote .'/i'; //captures a quoted value assigned to a modifier
		
	}
	
	$q .= ' ';//To accomodate the space (\s) in the first regex
	if(preg_match($regex , $q, $matches))
		return $matches[1];
	
	return false;
}

/**
 * Removes modifiers from a search query
 *
 * @param $q the query
 *
 * @return bool true on validate or false on invalid value
 * @ since 2.0.0
**/

function als_remove_modifiers( $q ) {
	$modifiers = apply_filters('als_remove_modifiers', array("+", "-", "~", "<", ">", "*",
			"author_in:", "author:", "author_not_in:", "cat:", "category:", "tagged:",
			"post_types:", "before:", "after:"));
	
	
	return str_ireplace($modifiers, '', $q);
}