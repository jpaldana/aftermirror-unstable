function miniWindow(url, id) {
	if ($("#" + id)[0] !== undefined) {
		console.log("id " + id + " already exists.");
		return;
	}
	var docWidth = $(document).width();
	var docHeight = $(document).height();
	var sector;
	var floX, floY;
	var floWidth = 300;
	var floHeight = 200;
	/*
	if (currentMousePos.x < docWidth / 2) {
		if (currentMousePos.y < docHeight / 2) {
			sector = 2; // top left
			floX = currentMousePos.x + 10;
			floY = currentMousePos.y + 10;
		}
		else {
			sector = 3; // bottom left
			floX = currentMousePos.x + 10;
			floY = currentMousePos.y - 10 - floHeight;
		}
	}
	else {
		if (currentMousePos.y < docHeight / 2) {
			sector = 1; // top right
			floX = currentMousePos.x - 10 - floWidth;
			floY = currentMousePos.y + 10;
		}
		else {
			sector = 4; // bottom right
			floX = currentMousePos.x - 10 - floWidth;
			floY = currentMousePos.y - 10 - floHeight;
		}
	}
	*/
	floX = currentMousePos.x - document.body.scrollLeft;
	floY = currentMousePos.y - document.body.scrollTop;
	var $floating = $("<div>");
	$floating.addClass("floatingWindow");
	$floating.css({ left: floX, top: floY, maxWidth: floWidth, maxHeight: floHeight });
	$floating.attr("id", id);
	$floating.on("click", function(e) {
		$(this).remove();
	});
	$("#floatSpace").append($floating);
	$("#" + id).html("<div class='block center'><img src='cdn/img/icons/load.gif' /><br /><span>loading page...</span></div>");
	$.get(url, function(result) {
		$result = $(result);
		$("#" + id).html($result.find("#container"));
		$("#" + id).append($result.find("script"));
		makeFriendly();
	}, "html");
	//$("#" + id).slimscroll({ height: $("#" + id).height() }); // way too buggy. :(
	//console.log("sector: " + sector);
	//console.log("mouse: " + currentMousePos.x + ", " + currentMousePos.y);
	//console.log("w: " + docWidth + " h: " + docHeight);
}
var currentMousePos = { x: -1, y: -1 };
$(document).mousemove(function(e) {
	currentMousePos.x = e.pageX;
	currentMousePos.y = e.pageY;
});
$(document).on('scroll', function(e) {
	$('.floatingWindow').remove();
});

function miniPopup(url, type, id) { // type=text/url
	if ($("#" + id)[0] !== undefined) {
		console.log("id " + id + " already exists.");
		return;
	}
	var $popup = $("<div>");
	$popup.css({ display: "none", position: "fixed", bottom: "10px", right: "10px", minWidth: "200px", maxWidth: "400px", overflow: "auto", maxHeight: "75px", backgroundColor: "white", boxShadow: "0px 0px 4px black", border: "solid 2px black", padding: "2px 6px" });
	$popup.attr("id", id);
	$popup.on("click", function(e) {
		$(this).remove();
	});
	if (type == 'url') {
		$.get(url, function(result) {
			$result = $(result);
			$popup.html($result.find("#container"));
			$("#" + id).append($result.find("script"));
			$("#floatSpace").append($popup);
			$("#" + id).fadeIn(200).delay(2000).fadeOut(200);
			setTimeout("jqRemove('" + id + "');", 2500);
		});
	}
	else {
		$popup.text(url);
		$("#floatSpace").append($popup);
		$("#" + id).fadeIn(200).delay(2000).fadeOut(200);
		setTimeout("jqRemove('" + id + "');", 2500);
	}
}
function jqRemove(id) {
	$("#" + id).remove();
}