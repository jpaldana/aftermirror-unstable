var chatHidden = false;
var xhr = new XMLHttpRequest();
var reqStart;
var userList = [];
var playState = 'loading UI...';

var preloadQueue = [];
var preloadQueueBusy = false;
var preloadQueueIndex = 0;
var preloadQueueHash;

var isAsync = false;
var hasPlayed = false;

var socket;
var previousChat = '';
var t;

function bgWorker() {
	if (!preloadQueueBusy) {
		//console.log('bgWorker not busy, looking for work...');
		var i = 0;
		for (; i < preloadQueue.length; i++) {
			var item = preloadQueue[i];
			if (item.dat_ready == 0) {
				busyProcessing = true;
				console.log('found work! - preloading queue at index = ' + i);
				console.log(item);
				preloadQueueBusy = true;
				preloadQueueIndex = i;
				var str = item.dat_file;
				preloadQueueHash = item.dat_url.substr(item.dat_url.lastIndexOf('/') + 1);
				
				preloadQueueHash = preloadQueueHash.substr(0, preloadQueueHash.length - 4);
				//$('#load_video_name').text(item.dat_file.substring(0, item.dat_file.lastIndexOf('.')));
				reqStart = Date.now();
				//$('#progressor').fadeIn(200);
				xhr.open('GET', item.dat_url, true);
				xhr.responseType = 'blob';
				xhr.send();
				return;
			}
		}
	}
}

function queueVideo(url, file) {
	var i = 0;
	for (; i < preloadQueue.length; i++) {
		var item = preloadQueue[i];
		if (item.dat_url == url && item.dat_file == file) {
			console.log('queuing video url=' + url + ' and file=' + file);
			var video = document.getElementById('video');
			video.src = item.dat_src;
			socket.emit('chat message', { message: "/iis ready to watch " + file, username: aniUsername } );
		}
	}
}

function emitVideo(d_url, d_file) {
	console.log('emitting video...');
	socket.emit('emit video', { url: d_url, file: d_file });
}

function loadVideo(url, file) {
	var i = 0;
	for (; i < preloadQueue.length; i++) {
		var item = preloadQueue[i];
		if (item.dat_url == url) {
			console.log('already found url in queue, exiting...');
			return;
		}
	}
	console.log('adding video to preload queue...');
	// preallocate
	var hash = url.substr(url.lastIndexOf('/') + 1);
	hash = hash.substr(0, hash.length - 4);
	$("#queue").append(
		$("<a>")
			.addClass("preventDefault buttonLink fxBackground")
			.attr("id", "mediaButton_" + hash)
			.attr("durl", hash)
			.attr("dfile", file)
			.click(function() {
				emitVideo(aniNodeRoot + 'core.DataGate?direct=' + $(this).attr('durl'), $(this).attr('dfile'));
			})
			.html("<img src='" + aniNodeCDN + "/img/icons/load.gif' style='width: 14px;' /> " + file + "<br/><span id='mediaButtonPercent_" + hash + "'>0</span>%<br/><span id='mediaButtonETA_" + hash + "'>--</span>")
	);
	
	preloadQueue[preloadQueue.length] = { dat_url: url, dat_file: file, dat_ready: 0, dat_src: '' };
	/*
	if ($('#preload').is(':checked')) {
		preloadQueue[preloadQueue.length] = { dat_url: url, dat_file: file, dat_ready: 0, dat_src: '' };
	}
	else {
		playState = 'loaded media (no preload)';
		preloadQueue[preloadQueue.length] = { dat_url: url, dat_file: file, dat_ready: 1, dat_src: url };
		$('#queue_content').append($('<span>').html(file).addClass('link').click(function() { emitVideo(url, file); })).append('<br/>');
		//var video = document.getElementById('video');
		//video.src = url;
	}
	*/
}

xhr.onload = function(e) {
	//console.log(this.status);
	if (this.status == 200) {
		busyProcessing = false;
		console.log('xhr done downloading video.');
		playState = 'loaded media (preloaded)';
		$('#console').text('Ready to Play');
		var myBlob = this.response;
		var vid = (window.webkitURL ? webkitURL : URL).createObjectURL(myBlob);
		//var video = document.getElementById('video');
		//video.src = vid;
		var item = preloadQueue[preloadQueueIndex];
		item.dat_ready = 1;
		item.dat_src = vid;
		var hash = item.dat_url.substr(item.dat_url.lastIndexOf('/') + 1);
		hash = hash.substr(0, hash.length - 4);
		writeFile(hash + '-' + item.dat_file, myBlob);
		$("#mediaButton_" + hash)
			.html(item.dat_file);
		//$('#queue_content').append($('<span>').html(item.dat_file).addClass('link').click(function() { emitVideo(item.dat_url, item.dat_file); })).append('<br/>');
		//preloadQueue[preloadQueueIndex] = item;
		preloadQueueBusy = false;
		//$('#netspeed').text('Network');
		//$('#progressor').fadeOut(200);
		//alert('Ready to play!');
		socket.emit('chat message', { message: "/i" + item.dat_file + " is ready.", username: aniUsername } );
		cacheUsage();
	}
};
xhr.onreadystatechange = function() {
	//console.log('xhr readystatechange!');
	//console.log('readystate: ' + xhr.readyState);
	//console.log('status: ' + xhr.status);
}
xhr.onprogress = function(e) {
	// totalSize, position (cur), total (same?), loaded
	var percent = Math.round((e.loaded / e.total) * 100);
	//$('#console').text(percent + '% done');
	$('#progress').attr('value', percent);
	var curTime = Date.now();
	var elapsed = Math.round((curTime - reqStart) / 1000);
	var diff = curTime - reqStart;
	var perc = e.loaded / e.total;
	//console.log('curtime: ' + curTime + ', reqstart: ' + reqStart + ', e.loaded: ' + e.loaded + ', e.total: ' + e.total + ', diff: ' + diff + ', perc: ' + perc);
	//console.log((curTime - reqStart) / (e.loaded / e.total) / 1000);
	if (elapsed > 0) {
		//var eta = Math.round((curTime - reqStart) / (e.loaded / e.total) / 1000);
		var speed = Math.round(e.loaded / 1024 / elapsed, 2);
		if (speed > (1024)) {
			speed = Math.round(speed / 1024, 2) + ' MB/s';
		}
		else {
			speed = speed + ' KB/s';
		}
		//console.log(speed);
		var mloaded = Math.round(e.loaded / 1024 / 1024);
		var mtotal = Math.round(e.total / 1024 / 1024);
		//$('#netspeed').text(speed + ' - ' + mloaded + ' MB of ' + mtotal + ' MB');
		var total = Math.round(diff / perc / 1000);
		var eta = total - elapsed;
		$('#netSpeed').text(speed);
		if (eta > 60) {
			eta = Math.round(eta / 60, 2);
			//$('#progress_label').text(percent + '% done - ' + eta + ' minute(s) remaining.');
			$("#mediaButtonPercent_" + preloadQueueHash).text(percent);
			$("#mediaButtonETA_" + preloadQueueHash).text(eta + " minute(s)");
			playState = '[' + (preloadQueueIndex + 1) + '/' + preloadQueue.length + '] (' + speed + ' ) preloading - ' + percent + '% done - ' + eta + ' minute(s) remaining.';
			$('#console').text(percent + '% done - ' + eta + ' minute(s) - ' + $('#mediaButton_' + preloadQueueHash).attr('dfile'));
		}
		else {
			//$('#progress_label').text(percent + '% done - ' + eta + ' second(s) remaining.');
			$("#mediaButtonPercent_" + preloadQueueHash).text(percent);
			$("#mediaButtonETA_" + preloadQueueHash).text(eta + " second(s)");
			playState = '[' + (preloadQueueIndex + 1) + '/' + preloadQueue.length + '] (' + speed + ' ) preloading - ' + percent + '% done - ' + eta + ' second(s) remaining.';
			$('#console').text(percent + '% done - ' + eta + ' second(s) - ' + $('#mediaButton_' + preloadQueueHash).attr('dfile'));
		}
	}
}

$(function() {
	$('#messages').slimScroll({ height: '175px' });
});

function toggleChat() {
	if (chatHidden) {
		console.log('chat now visible');
		chatHidden = false;
		$('#messages').fadeIn(200);
		$('#video').css('width', '700px');
		//$('#inputbox').css({ opacity: '1', bottom: '0px' });
		$('#chatbox').css('background-color', 'white');
	}
	else {
		console.log('chat now hidden');
		chatHidden = true;
		$('#messages').fadeOut(200);
		$('#video').css('width', '1000px');
		//$('#inputbox').css({ opacity: '0.25', bottom: '40px' });
		$('#chatbox').css('background-color', 'transparent');
	}
}
function setWall(url, tile) {
	socket.emit('background switcher', { file: url, is_tile: tile });
	$('#overlay').css('background-image', 'url(' + url + ')');
	if (tile) {
		$('#overlay').css('background-size', '100px');
	}
	else {
		$('#overlay').css('background-size', 'cover');
	}
}

function overlayShow(url) {
	$('#overlay').fadeIn(250);
	$('#overlay_content').load(url);
}
function overlayHide() {
	$('#overlay').fadeOut(250);
	//$('#overlay').css('background-image', 'none');
}

function formatAMPM(date) {
	var hours = date.getHours();
	var minutes = date.getMinutes();
	var ampm = hours >= 12 ? 'pm' : 'am';
	hours = hours % 12;
	hours = hours ? hours : 12; // the hour '0' should be '12'
	minutes = minutes < 10 ? '0'+minutes : minutes;
	var strTime = hours + ':' + minutes + ' ' + ampm;
	return strTime;
}
function popupFadeOut() {
	$('#popup').fadeOut(400);
}

function loadMedia(url, file) {
	socket.emit('chat message', { message: "/ihas queued " + file, username: aniUsername } );
	// url is the accessKey, change it to become a full URL.
	console.log('emitting media queue...');
	//socket.emit('media queue', { url: aniNodeRoot + 'core.DataGate?direct=' + url, file: file });
	socket.emit('media queue', { url: prismRoot + url, file: file });
	//socket.emit('media queue', url);
	//socket.emit('chat message', { message: \"<p style='text-align: center;'>Now Playing<br/><hr/>\" + file.substring(0, file.lastIndexOf('.')) + \"</p>\", username: '{$_GET[u]}', skipfilter: true } );
}

function clearPlayingMedia() {
	var video = document.getElementById('video');
	video.src = '';
}

window.requestFileSystem = window.requestFileSystem || window.webkitRequestFileSystem;
var fs = null;

function errorHandler(e) {
	var msg = '';
	switch (e.code) {
		case FileError.QUOTA_EXCEEDED_ERR:
			msg = 'QUOTA_EXCEEDED_ERR';
			break;
		case FileError.NOT_FOUND_ERR:
			msg = 'NOT_FOUND_ERR';
			break;
		case FileError.SECURITY_ERR:
			msg = 'SECURITY_ERR';
			break;
		case FileError.INVALID_MODIFICATION_ERR:
			msg = 'INVALID_MODIFICATION_ERR';
			break;
		case FileError.INVALID_STATE_ERR:
			msg = 'INVALID_STATE_ERR';
			break;
		default:
			msg = 'Unknown Error';
			break;
	};
	console.log('Error: ' + msg);
}

function initFS() {
	window.requestFileSystem(window.PERMANENT, 1024*1024*1024, function(filesystem) {
		fs = filesystem;
	}, errorHandler);
}

function writeFile(file, blob) {
	fs.root.getFile(file, { create: true }, function(fileEntry) {
		fileEntry.createWriter(function(fileWriter) {
			fileWriter.write(blob);
		}, errorHandler);
	}, errorHandler);
}

function getLocalFiles() {
  var dirReader = fs.root.createReader();
  dirReader.readEntries(function(entries) {
	for(var i = 0; i < entries.length; i++) {
	  var entry = entries[i];
	  if (entry.isDirectory){
		console.log('Directory: ' + entry.fullPath);
	  }
	  else if (entry.isFile){
		console.log('File: ' + entry.fullPath);
		entry.getMetadata(function(metadata) {
			console.log(metadata);
		});
	  }
	}
  }, errorHandler);
}

function removeAllFiles() {
  var dirReader = fs.root.createReader();
  dirReader.readEntries(function(entries) {
	for(var i = 0; i < entries.length; i++) {
	  var entry = entries[i];
	  if (entry.isDirectory){
		console.log('Directory: ' + entry.fullPath);
	  }
	  else if (entry.isFile){
		console.log('File: ' + entry.fullPath);
		entry.remove(function() { console.log('removed!'); }, errorHandler);
	  }
	}
  }, errorHandler);
  reloadCachedFiles();
}

function reloadCachedFiles() {
  preloadQueue = [];
  $('#queue').empty();
  var dirReader = fs.root.createReader();
  dirReader.readEntries(function(entries) {
	for(var i = 0; i < entries.length; i++) {
	  var entry = entries[i];
	  if (entry.isFile){
		var fileName = entry.name;
		var hash = fileName.substring(0, fileName.indexOf('-'));
		var fileName = fileName.substring(hash.length + 1);
		$("#queue").append(
			$("<a>")
				.addClass("preventDefault buttonLink fxBackground")
				.attr("durl", hash)
				.attr("dfile", fileName)
				.click(function() {
					emitVideo(prismRoot + 'core.DataGate?direct=' + $(this).attr('durl'), $(this).attr('dfile'));
				})
				.text(fileName)
		);
		//$('#queue_content').append($("<span style='color: blue;' durl='" + hash + "' dfile='" + fileName + "'>").click(function() { emitVideo(aniNodeRoot + 'core.File?raw=' + $(this).attr('durl'), $(this).attr('dfile')); }).html('*' + fileName).addClass('link')).append('<br/>');
		var vid = entry.toURL();
		preloadQueue[preloadQueue.length] = { dat_url: prismRoot + 'core.DataGate?direct=' + hash, dat_file: fileName, dat_ready: 1, dat_src: vid };
	  }
	}
  }, errorHandler);
  cacheUsage();
}

function cacheUsage() {
	window.webkitStorageInfo.queryUsageAndQuota(webkitStorageInfo.PERMANENT, function(used, remaining) {
		var percent_used = Math.round(Math.round(used / 1024 / 1024) * 100 / (Math.round(used / 1024 / 1024) + Math.round(remaining / 1024 / 1024)));
		$('#netCache').html('Using: ' + Math.round(used / 1024 / 1024) + ' MB (' + percent_used + '%)<br/>Remaining: ' + Math.round(remaining / 1024 / 1024) + ' MB');
	}, errorHandler);
}

$(function() {
	socket = io('https://entity.aftermirror.com:182/theatre', { 'transports': ['websocket', 'polling'] });
	//socket = io.connect("https://entity.aftermirror.com:182/");
	
	$('#chatter').submit(function(e) {
		socket.emit('chat message', { message: $('#userinput').val(), username: aniUsername } );
		$('#userinput').val('');
		e.preventDefault();
		return false;
	});
	
	socket.on('chat message', function(msg) {
		var d = new Date();
		if (msg.message.substring(0, 1) == '/') {
			var command = msg.message.substring(0, 2);
			if (msg.message.substring(2).trim().length > 0) {
				switch (command) {
					case '/b':
						msg.message = '<b>' + msg.message.substring(2) + '</b>';
					break;
					case '/i':
						msg.message = '<i>' + msg.message.substring(2) + '</i>';
					break;
					case '/u':
						msg.message = '<u>' + msg.message.substring(2) + '</u>';
					break;
					case '/s':
						msg.message = '<s>' + msg.message.substring(2) + '</s>';
					break;
					case '/h':
						msg.message = "<a href='" + msg.message.substring(2).trim() + "' target='_blank' class='link' style='color: blue; text-decoration: none;'>" + msg.message.substring(2).trim() + "</a>";
					break;
				}
			}
			else {
				msg.message = '';
			}
		}
		msg.message = msg.message.trim();
		if (msg.message.length > 0) {
			/*
			$("#messages").append(
				$("<span>")
					.addClass("noFlow chatMessage")
					.html("<b>" + msg.username + "</b>: " + msg.message + "<br/>")
					.css("background-image", "url(" + aniNodeRoot + "core.DataGate?profilePicture=" + msg.username + ")")
			);
			*/
			$("#messages").append(
				$("<div>")
					.addClass("chatBlock")
					.append(
						$("<div>")
							.addClass("chatProfilePicture")
							.css("background-image", "url(" + aniNodeRoot + "core.DataGate?profilePicture=" + msg.username + ")")
					)
					.append(
						$("<div>")
							.addClass("chatMessage")
							.html("<b>" + msg.username + "</b> " + msg.message + "<br/>")
					)
			);
			$("#messages").slimScroll({ scrollTo: $("#messages").prop("scrollHeight") + "px" });
			/*
			if (msg.username == previousChat) {
				$('#messages div.mail:first-child div').prepend("<p>" + msg.message + "</p>");
			}
			else if(msg.username == aniUsername) {
				$('#messages').prepend($('<div>').html("<div><p>" + msg.message + "</p></div><label><span class='name'>me</span> - <span class='date'>" + formatAMPM(d) + "</span></label>").addClass('send').addClass('mail'));
				previousChat = msg.username;
			}
			else {
				$('#messages').prepend($('<div>').html("<div><p>" + msg.message + "</p></div><label><span class='name'>" + msg.username + "</span> - <span class='date'>" + formatAMPM(d) + "</span></label>").addClass('recv').addClass('mail'));
				previousChat = msg.username;
			}
			
			clearTimeout(t);
			$('#popup').hide().html("<span class='pop_msg'>" + msg.message + "</span><br/><span class='pop_user'>" + msg.username + "</span>").fadeIn(200);
			t = setTimeout(popupFadeOut, 2000);
			*/
			$("#beeper")[0].play();
		}
	});
	
	socket.on('media queue', function(e) {
		overlayHide();
		loadVideo(e.url, e.file);
	});
	
	socket.on('emit video', function(e) {
		queueVideo(e.url, e.file);
	});
	
	socket.on('network', function(stat) {
		var ping = 0;
		if (stat.username in userList) {
			$('#net_'  + stat.username).text(stat.username + ": " + stat.status + " - " + stat.position);
		}
		else {
			userList[stat.username] = true;
			$('#network').append("<p id='net_" + stat.username + "'>" + stat.username + ": " + stat.status + " - " + stat.position + "</p>");
		}
	});
	
	socket.on('network position', function(stat) {
		isAsync = true;
		//$('#video').get(0).pause();
		$('#video').get(0).currentTime = stat;
		setTimeout('isAsync = false', 500);
	});

	socket.on('status', function(stat) {
		isAsync = true;
		setTimeout('isAsync = false', 500);
		if (stat == 'play') {
			if (!$('#video').playing) {
				$('#video').get(0).play();
			}
		}
		else if (stat == 'pause') {
			if (!$('#video').paused) {
				$('#video').get(0).pause();
			}
		}
	});
});

$(function() {
	$('#video').on('play', function() {
		playState = 'playing';
		if (!isAsync) {
			socket.emit('status', 'play');
			if (hasPlayed) {
				socket.emit('chat message', { message: aniUsername + ' resumed the movie.', username: 'system' } );
			}
			else {
				socket.emit('chat message', { message: aniUsername + ' resumed the movie.', username: 'system' } );
				hasPlayed = true;
			}
		}
		isAsync = false;
	});
	$('#video').on('pause', function() {
		playState = 'paused';
		if (!isAsync) {
			socket.emit('status', 'pause');
			socket.emit('chat message', { message: aniUsername + ' paused the movie.', username: 'system' } );
		}
		isAsync = false;
	});
	$('#video').on('canplay', function() {
		playState = 'buffering';
	});
	$('#video').on('canplaythrough', function() {
		playState = 'ready to play';
	});
	$('#video').on('waiting', function() {
		playState = 'waiting';
	});
	$('#video').on('ended', function() {
		playState = 'finished';
	});
	$('#video').on('seeking', function() {
		playState = 'seeking';
		if (!isAsync) {
			isAsync = true;
			socket.emit('status', 'pause');
		}
		$('#video').get(0).pause();
	});
	$('#video').on('seeked', function() {
		if (!isAsync) {
			isAsync = true;
			socket.emit('network position', $('#video').get(0).currentTime);
			setTimeout('isAsync = false', 1000);
		}
	});
});

function heartbeat() {
	socket.emit('network', {
		username: aniUsername,
		status: playState,
		position: $('#video').get(0).currentTime.toString().toHHMMSS(),
		time: Date.now()
	});
}

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

$(function() {
	$('#overlay').delay(1500).fadeOut(750);
	$('#console').text('Finished Loading');
	
	setInterval('heartbeat()', 625);
	playState = 'Ready!';
	setInterval('bgWorker()', 2000);
	
	setTimeout("$('#notifier').fadeOut(400); reloadCachedFiles();", 4000);
	
	if (window.requestFileSystem) {
		initFS();
	}
	socket.emit('chat message', { message: "/ihas joined the chat!", username: aniUsername } );
});

var chatSize = "big";
function toggleChatSize() {
	if (chatSize == "big") {
		chatSize = "small";
		$("#messages").css({ width: "200px" });
	}
	else {
		chatSize = "big";
		$("#messages").css({ width: "64px" });
	}
}
