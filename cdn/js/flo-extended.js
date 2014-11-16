var debugEnabled = false;
function debugMsg(message) {
	if (debugEnabled) {
		console.log(message);
	}
}

function ajaxDivLoad(id, url) {
	$("#" + id).animate({ opacity: 0.9 }, 400).animate({ opacity: 1.0 }, 400);
	$("#" + id).html("<div class='block center'><img src='cdn/img/icons/load.gif' /><br /><span>loading page...</span></div>");
	setTimeout("ajaxDivContentLoad('" + id + "', '" + url + "')", 400);
}
function ajaxDivContentLoad(id, url) {
	// do not call this directly.
	$.get(url, function(result) {
		$result = $(result);
		$("#" + id).html($result.find("#container"));
		$("#" + id).append($result.find("script"));
		makeFriendly();
	}, "html");
}
