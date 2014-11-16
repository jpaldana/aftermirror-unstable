// progress steps:
/*
	0 = initial
	1 = get episode links
	2 = pick which links
		2.0 = store into prism[animeID][links]
		2.1 = recur through and get URL of each prism[animeID][links]
			2.1.0 = store into prism[animeID][ep_link]
		2.2 = get size of prism[animeID][
	3 = link download (LQ)
	4 = link download (HQ)
	5 = add to database
	6 = done
	
	html dom
	eID = (initially) blank div
	eID_progress = progress bar for status
*/
var prism = [];
function prismify(req_url, eID, animeID, episode, type) {
	// initialize elements
	$("#" + eID)
		.html('')
		.css({ padding: '6px' })
		.append($("<progress>")
			.css({ width: '100%' })
			.attr({ id: eID + "_progress", max: 6, value: 0 }))
		.append($("<br>"))
		.append($("<span>")
			.attr({ id: eID + "_message" })
			.html(prismLoadText("starting prism...")));
	
	prism[animeID] = [];
	prism[animeID][type] = [];
	prism[animeID][type]["video_links"] = [];
	prism[animeID][type]["reqURL"] = req_url;
	// network ajax stack
	var segment = "&animeID=" + animeID + "&episode=" + episode + "&type=" + type;
	prismNetworkStack(0, eID, animeID, episode, type);
}
function prismNetworkStack(step, eID, animeID, episode, type) {
	var segment = "&animeID=" + animeID + "&episode=" + episode + "&type=" + type;
	switch (step) {
		case 0:
			$("#" + eID + "_message").html(prismLoadText("finding low and high quality links..."));
			$("#" + eID + "_progress").attr("value", 1);
			$.ajax({
				url: "core.ajaxPrism?getEpLink=" + prism[animeID][type]["reqURL"] + segment,
				async: true,
				dataType: "json",
				success: function(data) {
					$("#" + eID + "_message").html(prismLoadText("link(s) found. starting download..."));
					$("#" + eID + "_progress").attr("value", 2);
					prism[animeID][type]["video_links"] = data;
					prismNetworkStack(1, eID, animeID, episode, type);
				}
			});
		break;
		case 1:
			if (prism[animeID][type]["video_links"]["low"]["size"]) {
				$("#" + eID + "_message").html(prismLoadText("downloading low quality video..."));
				$.ajax({
					url: "core.ajaxPrism?bgDownload=" + prism[animeID][type]["video_links"]["low"]["link"] + "&size=low" + segment,
					async: true,
					type: "POST",
					data: prism[animeID]["links"],
					success: function(data) {
						$("#" + eID + "_message").html(prismLoadText("low quality video downloaded!"));	
						prismNetworkStack(2, eID, animeID, episode, type);
						$("#" + eID + "_progress").attr("value", 3);
						clearInterval(prism[animeID][type]["video_links"]["low"]["timeout"]);
					}
				});
				prism[animeID][type]["video_links"]["low"]["timeout"] = setInterval("prismRefresh('" + eID + "', '?statusCheck&quality=low&esize=" + prism[animeID][type]["video_links"]["low"]["size"] + segment + "')", 2250);
			}
			else {
				prismNetworkStack(2, eID, animeID, episode, type);
				$("#" + eID + "_progress").attr("value", 3);
			}
		break;
		case 2:
			if (prism[animeID][type]["video_links"]["high"]["size"]) {
				$("#" + eID + "_message").html(prismLoadText("downloading high quality video..."));
				$.ajax({
					url: "core.ajaxPrism?bgDownload=" + prism[animeID][type]["video_links"]["high"]["link"] + "&size=high" + segment,
					async: true,
					type: "POST",
					data: prism[animeID]["links"],
					success: function(data) {
						$("#" + eID + "_message").html(prismLoadText("high quality video downloaded!"));
						prismNetworkStack(3, eID, animeID, episode, type);
						$("#" + eID + "_progress").attr("value", 4);
						clearInterval(prism[animeID][type]["video_links"]["high"]["timeout"]);
					}
				});
				prism[animeID][type]["video_links"]["high"]["timeout"] = setInterval("prismRefresh('" + eID + "', '?statusCheck&quality=high&esize=" + prism[animeID][type]["video_links"]["high"]["size"] + segment + "')", 2250);
			}
			else {
				prismNetworkStack(3, eID, animeID, episode, type);
				$("#" + eID + "_progress").attr("value", 4);
			}
		break;
		case 3:
			// ste
		break;
	}
}
function prismLoadText(text) {
	return "<img src='cdn/img/icons/load.gif' style='width: 24px; position: relative; top: 12px;' /> " + text;
}
function prismRefresh(eID, req) {
	$.ajax({
		url: "core.AjaxPrism" + req,
		async: true,
		success: function(data) {
			$("#" + eID + "_message").html(prismLoadText(data));
		}
	});
}