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
}

function reloadCachedFiles() {
  preloadQueue = [];
  var dirReader = fs.root.createReader();
  dirReader.readEntries(function(entries) {
	for(var i = 0; i < entries.length; i++) {
	  var entry = entries[i];
	  if (entry.isFile){
		var fileName = entry.name;
		var hash = fileName.substring(0, fileName.indexOf('-'));
		var fileName = fileName.substring(hash.length + 1);
		var vid = entry.toURL();
		preloadQueue[preloadQueue.length] = { dat_url: "", dat_file: fileName, dat_ready: 1, dat_src: vid,  dat_hash: hash };
	  }
	}
  }, errorHandler);
  setTimeout("cacheUsage()", 1000);
}

function cacheUsage() {
	window.webkitStorageInfo.queryUsageAndQuota(webkitStorageInfo.PERMANENT, function(used, remaining) {
		$('#netCache').html('Using: ' + Math.round(used / 1024 / 1024) + ' MB<br/>Remaining: ' + Math.round(remaining / 1024 / 1024) + ' MB');
	}, errorHandler);
}

var xhr = new XMLHttpRequest();
var preloadQueueBusy = false;
var preloadQueueIndex = 0;
var preloadCurrentHash;

var busyProcessing;

$(function() {
	setInterval("bgWorker()", 2000);
	setTimeout("initFS()", 1000);
	setTimeout("cacheUsage()", 2000);
});

function bgWorker() {
	if (!preloadQueueBusy) {
		var i = 0;
		for (; i < preloadQueue.length; i++) {
			var item = preloadQueue[i];
			if (item.dat_ready == 0) {
				busyProcessing = true;
				preloadQueueBusy = true;
				preloadQueueIndex = i;
				var str = item.dat_file;
				//$('#load_video_name').text(item.dat_file.substring(0, item.dat_file.lastIndexOf('.')));
				reqStart = Date.now();
				//$('#progressor').fadeIn(200);'
				var hash = item.dat_url.substr(item.dat_url.lastIndexOf('/') + 1);
				hash = hash.substring(0, hash.length - 4);
				preloadCurrentHash = hash;
				preloadNewMedia(str, hash);
				xhr.open('GET', item.dat_url, true);
				xhr.responseType = 'blob';
				xhr.send();
				console.log("xhr sent.");
				$("#preloadDLLabel_" + preloadCurrentHash).text("Downloading...");
				return;
			}
		}
	}
}

function loadVideo(url, file) {
	var i = 0;
	for (; i < preloadQueue.length; i++) {
		var item = preloadQueue[i];
		if (item.dat_file == file) {
			return;
		}
	}
	//if ($('#preload').is(':checked')) {
	preloadQueue[preloadQueue.length] = { dat_url: url, dat_file: file, dat_ready: 0, dat_src: '' };
	//}
	//else {
		//playState = 'loaded media (no preload)';
		//preloadQueue[preloadQueue.length] = { dat_url: url, dat_file: file, dat_ready: 1, dat_src: url };
		//$('#queue_content').append($('<span>').html(file).addClass('link').click(function() { emitVideo(url, file); })).append('<br/>');
		//var video = document.getElementById('video');
		//video.src = url;
	//}
}

xhr.onload = function(e) {
	//console.log(this.status);
	if (this.status == 200) {
		//console.log("it's done!");
		busyProcessing = false;
		$("#preloadDLLabel_" + preloadCurrentHash).text("Done!");
		//playState = 'loaded media (preloaded)';
		//$('#console').text('Ready to Play');
		var myBlob = this.response;
		var vid = (window.webkitURL ? webkitURL : URL).createObjectURL(myBlob);
		//var video = document.getElementById('video');
		//video.src = vid;
		var item = preloadQueue[preloadQueueIndex];
		item.dat_ready = 1;
		item.dat_src = vid;
		var hash = item.dat_url.substr(item.dat_url.lastIndexOf('/') + 1);
		hash = hash.substring(0, hash.length - 4);
		item.dat_hash = hash;
		writeFile(hash + '-' + item.dat_file, myBlob);
		//$('#queue_content').append($('<span>').html(item.dat_file).addClass('link').click(function() { emitVideo(item.dat_url, item.dat_file); })).append('<br/>');
		//preloadQueue[preloadQueueIndex] = item;
		preloadQueueBusy = false;
		//$('#netspeed').text('Network');
		//$('#progressor').fadeOut(200);
		//alert('Ready to play!');
		//socket.emit('chat message', { message: \"/b\" + item.dat_file + \" is ready.\", username: '{$_GET['u']}' } );
		//cacheUsage();
	}
}
xhr.onreadystatechange = function() {
	//console.log('readystate: ' + xhr.readyState);
	//console.log('status: ' + xhr.status);
}
xhr.onprogress = function(e) {
	// totalSize, position (cur), total (same?), loaded
	var percent = Math.round((e.loaded / e.total) * 100);
	//$('#console').text(percent + '% done');
	//$('#progress').attr('value', percent);
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
		if (eta > 60) {
			eta = Math.round(eta / 60, 2);
			//$('#progress_label').text(percent + '% done - ' + eta + ' minute(s) remaining.');
			//playState = '[' + (preloadQueueIndex + 1) + '/' + preloadQueue.length + '] (' + speed + ' ) preloading - ' + percent + '% done - ' + eta + ' minute(s) remaining.';
			$("#preloadDLLabel_" + preloadCurrentHash).text(percent + "% - " + eta + " min(s) - " + speed);
			myStatus = percent + "% - " + eta + " min(s) - " + speed;
		}
		else {
			//$('#progress_label').text(percent + '% done - ' + eta + ' second(s) remaining.');
			//playState = '[' + (preloadQueueIndex + 1) + '/' + preloadQueue.length + '] (' + speed + ' ) preloading - ' + percent + '% done - ' + eta + ' second(s) remaining.';
			$("#preloadDLLabel_" + preloadCurrentHash).text(percent + "% - " + eta + " sec(s) - " + speed);
			myStatus = percent + "% - " + eta + " sec(s) - " + speed;
		}
	}
}


function videoExistsInQueue(url) {
	console.log('f(x) videoExistInQueue: ' + url);
	//console.log(preloadQueue);
	//console.log('preloadQueue length: ' + preloadQueue.length);
	var i = 0;
	for (; i < preloadQueue.length; i++) {
		var item = preloadQueue[i];
		//console.log('checking: ' + item.dat_url + ' with ' + url + '...');
		if (item.dat_url == url) {
			return true;
		}
	}
	return false;
}
function getLocalVideoBlob(url) {
	console.log('f(x) getLocalVideoBlob: ' + url);
	var i = 0;
	for (; i < preloadQueue.length; i++) {
		var item = preloadQueue[i];
		//console.log('checking: ' + item.dat_url + ' with ' + url + '...');
		if (item.dat_url == url) {
			return item.dat_src;
		}
	}
	return false;
}
