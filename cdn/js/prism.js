function prismRefreshData(id, page_url) {
	$.ajax({
		url: page_url,
		async: true,
		dataType: "json",
		success: function(data) {
			if (data.processing == true) {
				//$("#" + id + "_progress").attr("value", data.current);
				$("#" + id + "_progress").animate({ value: data.current });
				$("#" + id + "_status").html("Downloaded " + data.formatCurrent + " of " + data.filesize + " (about " + data.timeLeft + " at " + data.speed + ")");
			}
			else {
				setTimeout("clearInterval(timer_" + id + ");", 100);
				$("#" + id + "_progress").attr("value", $("#" + id + "_progress").attr("max"));
				$("#" + id + "_status").html("Done!");
			}
		}
	});
}
