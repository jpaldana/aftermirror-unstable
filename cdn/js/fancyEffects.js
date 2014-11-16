function changeBackgroundColor(color) {
	$("#underlay").css('background-image', '').animate({ backgroundColor: color });
}
function changeBackgroundImage(image) {
	$('#underlay').css('background-image', 'url(' + image + ')');
}
function setStatus(status) {
	switch (status) {
		case "available":
			$("#messengerStatus").html("Status: <b><span class='messengerAvailable'>&bull;</span> available</b>");
		break;
		case "away":
			$("#messengerStatus").html("Status: <b><span class='messengerAway'>&bull;</span> away</b>");
		break;
		case "busy":
			$("#messengerStatus").html("Status: <b><span class='messengerBusy'>&bull;</span> busy</b>");
		break;
		case "invisible":
			$("#messengerStatus").html("Status: <b><span class='messengerInvisible'>&bull;</span> invisible</b>");
		break;
	}
}

function hideAll(divs) {
	for (div in divs) {
		$("#" + divs[div]).hide();
	}
}
function switchTab(div, hideDivs) {
	hideAll(hideDivs);
	$("#" + div).fadeIn(200);
	$(".tab").removeClass("active");
	$("#" + div + "_tab").addClass("active");
}

var busyProcessing = false;
window.onbeforeunload = function() {
	if (busyProcessing) {
		return "Wait! Something is still processing in the background. If you leave now unsaved work may be lost.";
	}
}
