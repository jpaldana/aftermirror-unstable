// theatre.js (client)
// socket.io based asynchronous video stream plugin

// HTML DOM
var video = "#video"; // main video object
var chatForm = "#chatter";
var chatTextbox = "#userinput";
var chatBox = "#messages";
var userlist = "#userlist";
var logger = "#console";
var preloadBox = "#tabPreloads";

// Background
var debug = 2; // 0 = off, 1 = errors, 2 = all
var t_users = [];
var a_users = [];
var p_users = [];
var myStatus = "Loading UI...";
var playState;
var isAsync = false;
var hasPlayed = false;

var socket;

// Theatre
$(function() {
	socket = io(theatreSocketServer, { "transports": ["websocket", "polling"] });
	
	// Chat
	$(chatForm).submit(function(e) {
		socket.emit("chat message", { message: $(chatTextbox).val(), username: aniUsername });
		$(chatTextbox).val("");
		e.preventDefault();
		return false;
	});
	socket.on("chat message", function(msg) {
		spawnMessage(msg.message, msg.username, {});
	});
	
	// Announce
	console.log("announcing connection...");
	socket.emit("user connect", { username: aniUsername });
	
	// Users
	socket.on("new user", function(e) {
		spawnMessage("has joined the chat!", e.username, {});
	});
	socket.on("heartbeat response", function(e) {
		t_users[e.username] = new Date().getTime();
		a_users[e.username] = e.status;
		p_users[e.username] = e.ping;
	});
	
	// Preload
	socket.on("preload queue", function(e) {
		if ($("#preferQ_high:checked")[0]) {
			console.log("pref: high");
			var rqURL = "core.ajaxTheatreParser?animeID=" + e.id + "&episode=" + e.episode + "&prefQ=high&prefT=subbed";
		}
		else {
			console.log("pref: low");
			var rqURL = "core.ajaxTheatreParser?animeID=" + e.id + "&episode=" + e.episode + "&prefQ=low&prefT=subbed";
		}
		$.ajax({
			url: rqURL,
			async: true,
			success: function(data) {
				if (data == "false") {
					myStatus = "Failed to parse data";
				}
				else {
					loadVideo(data, e.name + " - " + e.episode + ".mp4");
					myStatus = "Starting preload sequence...";
				}
			}
		});
	});
	
	// Player
	socket.on("enqueue", function(e) {
		var i;
		for (i in preloadQueue) {
			if (preloadQueue[i].dat_file == e.file) {
				$(video)[0].src = preloadQueue[i].dat_src;
				socket.emit("chat message", { message: "is ready to watch " + e.file, username: aniUsername });
				myStatus = "Ready to watch " + e.file;
			}
		}
	});
	
	// Set UI
	$(chatBox).slimscroll({ height: "175px" });
	
	// Ping
	socket.on("ping", function(e) {
		socket.emit("pong", { username: aniUsername });
	});
	
	// Timers
	setInterval("broadcastHeartbeat()", 1000);
	myStatus = "Done loading UI.";
});

// Player Events
$(function() {
	socket.on('network position', function(stat) {
		isAsync = true;
		//$('#video').get(0).pause();
		$(video)[0].currentTime = stat;
		setTimeout('isAsync = false', 200);
	});

	socket.on('status', function(stat) {
		isAsync = true;
		setTimeout('isAsync = false', 200);
		if (stat == 'play') {
			if (!$(video).playing) {
				$(video).get(0).play();
			}
		}
		else if (stat == 'pause') {
			if (!$(video).paused) {
				$(video).get(0).pause();
			}
		}
	});
	$(video).on('play', function() {
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
	$(video).on('pause', function() {
		playState = 'paused';
		if (!isAsync) {
			socket.emit('status', 'pause');
			socket.emit('chat message', { message: aniUsername + ' paused the movie.', username: 'system' } );
		}
		isAsync = false;
	});
	$(video).on('canplay', function() {
		playState = 'buffering';
	});
	$(video).on('canplaythrough', function() {
		playState = 'ready to play';
	});
	$(video).on('waiting', function() {
		playState = 'waiting';
	});
	$(video).on('ended', function() {
		playState = 'finished';
	});
	$(video).on('seeking', function() {
		playState = 'seeking';
		if (!isAsync) {
			isAsync = true;
			socket.emit('status', 'pause');
		}
		$(video).get(0).pause();
	});
	$(video).on('seeked', function() {
		if (!isAsync) {
			console.log('sent pos');
			isAsync = true;
			socket.emit('network position', $(video)[0].currentTime);
			setTimeout('isAsync = false', 200);
		}
	});
});

// Helper
function spawnMessage(message, username, options) {
	$(chatBox).append(
		$("<div>")
			.addClass("chatBlock")
			.append(
				$("<div>")
					.addClass("chatProfilePicture")
					.css("background-image", "url(" + sysPathRoot + "profile/" + username + "/picture.jpg)")
			)
			.append(
				$("<div>")
					.addClass("chatMessage")
					.html("<b>" + username + "</b> " + message + "<br/>")
			)
	);
	$(chatBox).slimScroll({ scrollTo: $(chatBox).prop("scrollHeight") + "px" });
	controlBeep();
}
function spawnUserBlock(username) {
	$(userlist).append(
		$("<div id='userBlock_" + username + "'>")
			.addClass("chatBlock")
			.append(
				$("<div>")
					.addClass("chatProfilePicture")
					.css("background-image", "url(" + sysPathRoot + "profile/" + username + "/picture.jpg)")
			)
			.append(
				$("<div>")
					.addClass("chatMessage")
					.html("<b>" + username + "</b> <span style='color: gray; font-variant: small-caps;'>" + Math.abs(p_users[username]) + "ms</span> " + a_users[username] + "<br/>")
			)
	);
}
function broadcastHeartbeat() {
	$(logger).text(myStatus);
	socket.emit("heartbeat", { username: aniUsername, status: myStatus, position: $(video)[0].currentTime.toString().toHHMMSS() });
	$(userlist).html("");
	var user;
	var cTime = new Date().getTime();
	for (user in t_users) {
		if (t_users[user] == -1) {
		}
		else if (cTime - t_users[user] < 2000) {
			spawnUserBlock(user);
		}
		else {
			spawnMessage("disconnected.", user, {});
			t_users[user] = -1;
		}
	}
}
function controlBeeperVolume() {
	$('#beeper')[0].volume = $("#beeperVolume").val();
	controlBeep();
}
function controlBeep() {
	$('#beeper')[0].pause();
	$('#beeper')[0].currentTime = 0;
	$('#beeper')[0].play();
}
function preloadMedia(name, id, episode) {
	socket.emit("add preload queue", { name: name, id: id, episode: episode });
}
function listPreloads() {
	var i;
	$(preloadBox).html("");
	for (i in preloadQueue) {
		$(preloadBox).append(
			$("<a>")
				.addClass("buttonLink fxBackground")
				.html(preloadQueue[i].dat_file)
				.attr("onclick", "enqueueVideo('" + preloadQueue[i].dat_file + "');")
		);
	}
}
function enqueueVideo(file) {
	socket.emit("enqueue", { username: aniUsername, file: file });
}
