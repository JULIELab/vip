////////////////////////////////////////
////////// GLOBALS /////////////////////
////////////////////////////////////////

var globTimeouts = Array;
var globDelay = 400;

////////////////////////////////////////
////////// FUNKTIONEN //////////////////
////////////////////////////////////////

/**
 * Delay calling a function.
 */
function delayRequest (functionName, params) {
	if(globTimeouts[functionName] != null){
		clearTimeout(globTimeouts[functionName]);
		globTimeouts[functionName] = null;
	}

	var call = functionName+'(';
	if(params != null){
		for(var i = 0; i <= params.length - 1; i++){
			if(i != 0)
				call += ', ';

			call += '"'+params[i]+'"';
		}
	}
	call += ')';

	globTimeouts[functionName] = setTimeout(call, globDelay);
}

/**
 * Set the HTML of an element or group of elements to "loading".
 */
function setLoading (selector, path, text) {
	if(path == null)
		path = '';

	$(selector).html('<img src="'+path+'_resources/images/pending.gif" alt="pending" />');
	if(text != null)
		$(selector).append(' '+text);
}

function addTab (id, data, content) {
	$('#resultContainer').append('<div id="tabContent_'+(id)+'" class="tabContent">'+content+'</div>');
	$('#tabContent_'+id).data(data);

	$('#resultList').append('<li id="tab_'+id+'"><span onclick="showTab('+id+')">Result #'+id+'</span><a href="javascript:;" onclick="closeTab('+id+')"><span class="silk-icon silk-icon-cross" title="Close result"></span></a></li>');
	showTab(id);
}

function showTab (id) {
	/**
	 * Hide the currently selected tab.
	 */
	$('.tabContent:visible').hide();
	$('#resultList li.active').removeClass('active');
	/**
	 * Show the requested tab.
	 */
	$('#tabContent_'+id).show('fast');
	$('#tab_'+id).addClass('active');
}

function closeTab (id) {
	/**
	 * Close the tab and remove its HTML from the DOM.
	 */
	$('#tabContent_'+id).remove();
	$('#tab_'+id).remove();

	/**
	 * Show the first tab of the set.
	 */
	showTab(parseInt($('#resultContainer div:first').attr('id').split('_')[1]));
}

function closeActiveTab () {
	/**
	 * Get the id of the active tab.
	 */
	var id = parseInt($('.tabContent:visible').attr('id').split('_')[1]);
	closeTab(id);
}

function findSimilarTab (object) {
	var winner = false;
	$('.tabContent').each(function () {
		var similar = true;
		var tab = this;

		$.each(object, function (key, value) {
			if($(tab).data(key) != value)
				similar = false;
		})

		if(similar){
			winner = true;
			$.jGrowl('Your request matches an existing tab. This tab has been opened for your convenience.');
			showTab(parseInt($(tab).attr('id').split('_')[1]));
		}
	});
	return winner;
}

window.onload = function () {
	$('a').each(function () {
		if($(this).$(this).attr('href').match(/^https?\:/i) || $(this).attr('href').match(/\.pdf$/i))
			$(this).attr('target', '_blank');
	});
}

$(document).ajaxError(function(e, req, set){
	$('#body').prepend('<div class="error"><a href="javascript:;" onclick="$(this).parent().hide(\'slow\');" style="float: right;">Close this!</a><strong>There was an error with your request.</strong><br /><strong>'+set.type+' '+set.url+'</strong><br /><div class="notice" style="font-size: 0.7em;">'+req.responseText+'</div></div>');
});
