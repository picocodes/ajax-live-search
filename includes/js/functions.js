/**
 * Plugin functions file
 *
 * Contains handlers for all the js functions that we will
 * be executing
 */
( function( $ ) {

$(document).on('click', function(e) {
	if(!$(e.target).closest('.alsSuggestions').length) {
		$('.alsSuggestions').hide();
		}
});

/**
 * this variable is used to set the markup so that the 
 * the ajax request is only made 1/2 seconds after the last keypress
 * This reduces server load
 */

var timeoutID = null;

/**
 * This function queries the database for several suggestions for the user
 * @param string holds the value of the input box
 * @param div holds the div that displays the suggestions
 */

function getSuggestions(str, div) {

	if(str.length==0) {
		div.hide();
		return false;
	}

	$.get(als.ajaxurl, 
		 { 
		 s:str,
		 action: 'alsgetsuggestions',
		 nextNonce: als.nextNonce
		 }, 
		 function(data,status,xhr) {
		div.show().html(data);
	});
}

/**
 * This function loads search results via ajax
 * @param string holds the value of the input box
 * @param div holds the div that displays the results
 */

function loadResults(str, div) {

	if(str.length==0) {
		return false;
	}
	div.html('<div class="als-loading">Loading...</div>');
	$('.results-count').hide();
	$.get(als.ajaxurl, 
		 { 
		 s:str,
		 action: 'alsgetresults',
		 nextNonce: als.nextNonce
		 }, 
		 function(data,status,xhr) {
		
		div.html(data);
	});
}

/**
 * in normal wordpress search forms, the main input field has the name s
 * so we will select it
 * and add a keyup event handler to execute anytime someone starts typing
 */

$(document).on('keyup', 'input[name="s"]', function() {
	if(als.load_suggestions != 'yes'){
		return;
	}
	/**
	 * this variable contains the form element,
	 * which contains our active search form
	 */
 
	var form = $($($(this).parent()).parent()); 
 
	/**
	 * this variable contains the suggestions div,
	 * it houses our autosuggest options
	 * we also set it's width to the same width
	 * as that of the serch box
	 * Finally, we will be hiding it every time the form loses focus
	 */
 
	 var suggestDiv = form.siblings('div .alsSuggestions'); 
	 
	 suggestDiv.css({
			"width" : $(this).css("width")
		});
	/**
	 * this variable our typed query,
	 * which is then send to the getsuggestions function
	 */
 
	 var query = $(this).val();
	
	clearTimeout(timeoutID); //Reset the 1/2 second timer
	
	timeoutID = setTimeout( function() {
		getSuggestions( query, suggestDiv);}, 500);
});

/**
 * Loads results via ajax when the form is submited
 */

$('.als-mainform form').submit(function(e){
	if(als.ajax_results != 'yes'){
		return;
	}
		e.preventDefault();
		
		var term = $('.als-mainform input[name="s"]').val();
		var div = $('.als-results');
		
		loadResults(term, div);
	
});

/**
 * Excecutes live search functionality
 * Only when backspace or spacebar has been pressed to save resources
 */

$(document).on('keyup', '.als-mainform input[name="s"]', function(e){
	if(als.live_results != 'yes'){
		return;
	}
		if(e.keyCode != 8 && e.keyCode != 32){
			return;
		}
		
		var term = $('.als-mainform input[name="s"]').val();
		var div = $('.als-results');
		
		loadResults(term, div);
	
});

} )( jQuery );