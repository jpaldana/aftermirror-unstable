// hash url redirection.
if (location.hash.length > 0) {
	var testStr = location.hash.substring(0, 5);
	if (testStr == "#core" || testStr == "#app.") {
		window.location = location.hash.substring(1, location.hash.length);
	}
}

$(function() {
	// make .ajaxFriendly and .modalFriendly links awesome.
	makeFriendly();
	
	// unveil/lazy load images
	$("img").unveil();
});

function ajaxContentLoad(url) {
	location.hash = url;
	$("#content").html("<div class='block center'><img src='cdn/img/icons/load.gif' /><br /><span>loading page...</span></div>");
	$.get(url, function(result) {
		$result = $(result);
		$("#content").html($result.find("#container"));
		$("#content").append($result.find("script"));
		makeFriendly();
	}, "html");
}
function modalContentLoad(url) {
	$("#modal").fadeIn(400);
	ajaxDivLoad("modal_content", url);
}
function makeFriendly() {
	$(".fxSlide").fadeIn(2000);
	$(".ajaxFriendly").off("click").on("click", function(e) {
		e.preventDefault();
		location.hash = $(this).attr("href");
		ajaxDivLoad("content", $(this).attr("href"));
	});
	$(".modalFriendly").off("click").on("click", function(e) {
		e.preventDefault();
		$("#modal").fadeIn(400);
		ajaxDivLoad("modal_content", $(this).attr("href"));
	});
	$(".preventDefault").on("click", function(e) {
		e.preventDefault();
	});
}

// app.Home feed loader
function feedLoad(current, previous) {
	$.ajax({
		url: 'core.ajaxNewsfeed?current=' + current,
		async: true,
		success: function(data) {
			$('#moreLoad_' + previous).before(data).fadeIn(400);
			$('#moreLoad_' + previous).remove();
		}
	});
}

// grid stuff
function fixGrid() {
	var gridWidth = $(".grid").width();
	var gridStep = Math.floor(gridWidth / 4) - 3;
	var gridMinimum = 128;
	var gridIsSmall = false;
	if (gridStep < gridMinimum) {
		gridStep = Math.floor(gridWidth / 3) - 3;
		if (gridStep < gridMinimum) {
			gridStep = Math.floor(gridWidth / 2) - 3;
			if (gridStep < gridMinimum) {
				gridIsSmall = true;
				gridStep = gridWidth;
			}
		}
	}
	if (gridIsSmall) {
		// everything is unilinear.
		var gridS1 = gridStep + "px";
		var gridS2 = gridStep + "px";
	}
	else {
		var gridS1 = gridStep + "px";
		var gridS2 = (gridStep * 2) + "px";
	}
	
	var tilesPerRow = Math.floor(gridWidth / gridStep);
	var currentRowTile = 0;
	$(".grid .cell").each(function(i) {
		var preferredTileSize = $(this).attr("psize");
		if (preferredTileSize == "cell2x1") {
			if (tilesPerRow >= (currentRowTile + 2)) {
				$(this).removeClass("cell1x1 cell2x1");
				$(this).addClass("cell2x1");
				currentRowTile += 2;
			}
			else {
				$(this).removeClass("cell1x1 cell2x1");
				$(this).addClass("cell1x1");
				currentRowTile++;
			}
		}
		else if (preferredTileSize == "cell1x1") {
			$(this).removeClass("cell1x1 cell2x1");
			$(this).addClass("cell1x1");
			currentRowTile++;
		}
		if (currentRowTile >= tilesPerRow) {
			currentRowTile = 0;
		}
	});
	
	$(".grid .cell1x1").css({ width: gridS1, height: gridS1 });
	$(".grid .cell2x1").css({ width: gridS2, height: gridS1 });
}

function closeModal() {
	$("#modal").fadeOut(400);
}

var spawnedMediaPlayer = false;
var currentlyPlaying;
var mediaPlayer;
var mediaPlayerDuration;
var mediaPlayerCurrent;
// music only.
function spawnMediaPlayer() {
	if (spawnedMediaPlayer) { return; }
	spawnedMediaPlayer = true;
	$("#worker").append(
		$("<audio>")
			.attr("id", "sbMediaPlayer")
			.addClass("sbMediaPlayer")
	);
	$("#worker").append(
		$("<div>")
			.addClass("hAlignContainer")
			.append($("<div>")
				.addClass("hAlign")
				.attr("id", "sbMediaPlayerControls")
				.html("<small>Now Playing...</small><br /><a id='sbMediaPlayerTitleArtistButton' class='preventDefault buttonLink fxBackground'><span class='noFlow' id='sbMediaPlayerTitle' style='font-size: 16px;'>title</span><br/><span class='noFlow' id='sbMediaPlayerArtist' style='font-size: 14px;'>artist</span><br/><span id='sbMediaPlayerSeekStatus' class='noFlow' style='font-size: 12px;'>00:00:00 / 00:00:00</span></a>")
				.on("click", function() { showPlayer(); })
			)
	);
	$("#sbMediaPlayerControls").append(
		$("<a>")
			.attr("id", "sbMediaPlayerPlayPauseButton")
			.addClass("preventDefault buttonLink fxBackground")
			.html("<img src='cdn/img/icons/pause.png' style='width: 24px;' id='sbMediaPlayerPlayPauseButtonImage' /><br/><span id='sbMediaPlayerPlayPauseButtonText'>pause</span>")
			.on("click", function() { playPausePlayer(); return false; })
	);
	//$("#sbMediaPlayerControls").append($("<a>").addClass("buttonLink fxBackground").html("<img src='cdn/img/icons/speaker.png' style='width: 24px;' /><br/>controls").css("float", "right").on("click", function() { showPlayer(); }));
	mediaPlayer = document.getElementById("sbMediaPlayer");
	
	// attach events
	$("#sbMediaPlayer").on("ended", function() {
		pausePlayer();
	});
	
	setInterval(mediaPlayerTick, 1000);
}
function playPlayer() {
	if (!spawnedMediaPlayer) { return; }
	
	mediaPlayer.play();
	$("#sbMediaPlayerPlayPauseButtonImage").attr("src", "cdn/img/icons/pause.png");
	$("#sbMediaPlayerPlayPauseButtonText").text("pause");		
	$('#auxPlayPauseButtonImage').attr('src', 'cdn/img/icons/pause.png');
	$('#auxPlayPauseButtonText').text('pause');
}
function pausePlayer() {
	if (!spawnedMediaPlayer) { return; }

	mediaPlayer.pause();
	$("#sbMediaPlayerPlayPauseButtonImage").attr("src", "cdn/img/icons/play.png");
	$("#sbMediaPlayerPlayPauseButtonText").text("play");
	$('#auxPlayPauseButtonImage').attr('src', 'cdn/img/icons/play.png');
	$('#auxPlayPauseButtonText').text('play');
}
function playPausePlayer() {
	if (!spawnedMediaPlayer) { return; }
	
	if (mediaPlayer.paused) {
		playPlayer();
	}
	else {
		pausePlayer();
	}
	return;
}
function showPlayer() {
	if (!spawnedMediaPlayer) { return; }
	$("#modal").fadeIn(400);
	$("#modal_content").html("<div class='block center'><img src='cdn/img/icons/load.gif' /><br /><span>loading page...</span></div>");
	
	$.get("core.ajaxMediaPlayer?playing=" + currentlyPlaying, function(result) {
		$result = $(result);
		$("#modal_content").html($result.find("#container"));
		$("#modal_content").append($result.find("script"));
		makeFriendly();
	}, "html");
}
function loadMusic(accKey) {
	spawnMediaPlayer();
	currentlyPlaying = accKey;
	
	$.get("core.MediaPlayer?music=" + accKey, function(result) {
		$result = $(result);
		$("#modal_content").append($result.find("script"));
		
		mediaPlayer.src = "core.DataGate?direct=" + accKey;
		playPlayer();
	}, "html");
	
	return false;
}
function mediaPlayerTick() {
	$("#sbMediaPlayerSeekStatus").text(mediaPlayer.currentTime.toString().toHHMMSS() + " / " + mediaPlayer.duration.toString().toHHMMSS());
	mediaPlayerDuration = Math.round(mediaPlayer.duration);
	mediaPlayerCurrent = Math.round(mediaPlayer.currentTime);
	var auxSeekerBar = document.getElementById("auxSeekerBar");
	if (auxSeekerBar) {
		auxSeekerBar.max = mediaPlayerDuration;
		auxSeekerBar.value = mediaPlayerCurrent;
		$("#auxCurrentText").text(mediaPlayer.currentTime.toString().toHHMMSS());
		$("#auxCurrentDuration").text(mediaPlayer.duration.toString().toHHMMSS());
	}
}

// preloader
var preloadQueue = [];
function preloadNewMedia(filename, key) {
	console.log('appending new worker...');
	$("#queue").append(
		$("<div>")
			.addClass("hAlignContainer")
			.append($("<div>")
				.addClass("hAlign")
				.css("width", "100%")
				.attr("id", "preloadDL_" + key)
				.html("<a class='preventDefault buttonLink fxBackground noFlow'><span class='noFlow' style='font-size: 14px;'>" + filename + "</span><br/><span class='noFlow' id='preloadDLLabel_" + key + "'>Queued...</span></a>")
				//.on("click", function() { showPreloadStatus(key); })
			)
	);
}
function showPreloadStatus(key) {
	if ($("#preloadDLLabel_" + key).text() == "Done!") {
		$("#preloadDL_" + key).remove();
	}
	else {
		$("#modal").fadeIn(400);
		$("#modal_content").html("<div class='block center'><img src='cdn/img/icons/load.gif' /><br /><span>loading page...</span></div>");
		
		$.get("core.ajaxVideoPlayer?preload_status=" + key, function(result) {
			$result = $(result);
			$("#modal_content").html($result.find("#container"));
			$("#modal_content").append($result.find("script"));
			makeFriendly();
		}, "html");
	}
}
// repopulate preloaded media
$(function() {
	//initFS();
	setTimeout("reloadCachedFiles()", 3000);
});

// standardized stuff.
String.prototype.toHHMMSS = function () {
	var sec_num = parseInt(this, 10); // don't forget the second param
	var hours   = Math.floor(sec_num / 3600);
	var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
	var seconds = sec_num - (hours * 3600) - (minutes * 60);

	if (hours   < 10) {hours   = '0'+hours;}
	if (minutes < 10) {minutes = '0'+minutes;}
	if (seconds < 10) {seconds = '0'+seconds;}
	var time    = hours+':'+minutes+':'+seconds;
	return time;
}

// improved stuff
function ajaxWorker(id, title, url) {
	$("#worker").append(
		$("<div>")
			.addClass("hAlignContainer")
			.append($("<div>")
				.addClass("hAlign")
				.attr("id", "worker_" + id)
				.css("width", "100%")
				.html("<span class='buttonLink fxBackground'>" + title + "</span><span id='status_" + id + "'>--</span>")
				.on("click", function() { $(this).remove(); })
			)
	);
}
$(function() {
	$("#sidebar").slimscroll({ height: 'auto' });
	$(window).resize(function() {
		$("#sidebar").slimscroll({ height: 'auto' });
	});
});

// from older flo.js
function toggle(id) {
	$("#" + id).slideToggle();
}
function aLoad(id, url) {
	$('#' + id).load(url);
}
function bgLoad(page_url) {
	$.ajax({
		url: page_url,
		async: true,
		success: function(data) {
		}
	});
}
