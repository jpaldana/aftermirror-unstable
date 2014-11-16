<?php
	$cdn = $_sys["node_cdn"];
	if (isset($_GET["u"])) {
		$valid = readDB($_sys["path_data"] . "db/.valid-id");
		if (isSomething($_GET["u"]) && !($_GET["u"] === "undefined" && $_GET["pwh"] === "undefined")) {
			$id = $valid[$_GET["u"]];
			//if (true) {
			if ($id["pwh"] === $_GET["pwh"] && ($id["time"] + $_sys["node_expiry"]) > time()) {
				echo "
					<html>
						<head>
							<title>after|mirror: theatre</title>
							<meta name='viewport' content='minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no'>
							<meta http-equiv='cleartype' content='on'>
							<link href='{$_sys['node_cdn']}/css/style.css' rel='stylesheet' />
							<link href='{$_sys['node_cdn']}/css/style-page.css' rel='stylesheet' />
							<link href='{$_sys['node_cdn']}/css/style-anistream.css' rel='stylesheet' />
							<script src='/socket.io/socket.io.js'></script>
							<script src='{$_sys['node_cdn']}/js/jquery.js'></script>
							<script src='{$_sys['node_cdn']}/js/flo.js'></script>
							<script src='{$_sys['node_cdn']}/js/jquery.slimscroll.min.js'></script>
							<script>
								var aniUsername = '{$_GET['u']}';
								var aniNodeRoot = '{$_sys['node_root']}';
							</script>
							<script src='{$_sys['node_cdn']}/js/anistream.js'></script>
						</head>
						<body>
							<div id='content'>
								<div id='block' class='valign'>
									<div id='top_block'>
										<div id='videobox' class='valign'>
											<div id='popup' style='display: none;'></div>
											<video id='video' controls preload='auto'></video>
											<div id='progressor' style='display: none;'>
												<h1>Loading...</h1><br/>
												<small onclick=\"$('#progressor').fadeOut(200);\" class='link'>Click me to dismiss.</small><br/><br/>
												<hr/><br/>
												<p>&quot;<span id='load_video_name'></span>&quot; is now being pre-loaded.<br/>You will receive a popup when the process is complete.</p><br/><br/>
												<progress id='progress' max='100' value='0'></progress><br/><br/><span id='progress_label'>0% done - 0 minute(s) remaining</span>
											</div>
										</div>
										<div id='chatbox'>
											<div id='messages'>
												<div class='recv mail' style='background-image: url({$_sys['node_cdn']}kirisuna.png); background-size: 200px; background-position: right bottom; background-repeat: no-repeat;'>
													<div style='background-color: rgba(255, 255, 255, 0.4)'>
														<p>anistream.theatre v1.0.2 &quot;kirisuna&quot;</p>
														<p><br/><hr/><br/><small>chat commands:</small></p>
														<p>/b - <b>bold text</b></p>
														<p>/i - <i>italic text</i></p>
														<p>/u - <u>underline text</u></p>
														<p>/s - <s>strikethrough text</s></p>
														<p>/h http://example.com - shows link</p>
														<label class='name'>system</label>
													</div>
												</div>
											</div>
											<div id='inputbox'>
												<form id='chatter'><input type='text' placeholder='Say something...' id='userinput' autocomplete='off' /></form>
											</div>
										</div>
										<div id='bottom_block'>
										<fieldset>
											<legend onclick=\"overlayShow('{$_sys['node_root']}core.ajaxAnistream?u={$_GET['u']}&media_selection');\">Open...</legend>
										</fieldset>
										<fieldset>
											<legend onclick=\"toggle('opt_player');\">Player</legend>
											<div id='opt_player' style='display: none;'>
												<input type='checkbox' id='preload' checked /> <label class='link' onclick=\"$('#preload').click();\">Preload</label><br/>
												<label class='link' onclick=\"clearPlayingMedia();\">Clear Media</label><br/>
											</div>
										</fieldset>
										<fieldset>
											<legend onclick=\"toggle('opt_interface');\">Interface</legend>
											<div id='opt_interface' style='display: none;'>
												<label class='link' onclick='toggleChat();'>Toggle Chat</label><br/>
												<label class='link' onclick=\"overlayShow('{$_sys['node_root']}core.ajaxAnistream?wallpaper_dialog');\">Set Wallpaper...</label>
											</div>
										</fieldset>
										<fieldset>
											<legend onclick=\"toggle('opt_network');\"><span id='netspeed'>Network</span></legend>
											<div id='opt_network' style='display: none;'>
												Status: <label id='console'>--</label><br/>
												<div id='network'></div>
											</div>
										</fieldset>
										<fieldset>
											<legend onclick=\"toggle('opt_local');\" id='local_label'>Local</legend>
											<div id='opt_local' style='display: none;'>
												<label id='localstat'>0 MB of 0 MB</label><br/>
												<label class='link' onclick='removeAllFiles();'>Delete All Cached Files</label><br/>
											</div>
										</fieldset>
										</div>
									</div>
								</div>
							</div>
							<div id='overlay' style='display: none;'>
								<div id='overlay_content'>
								</div>
								<label class='link' onclick='overlayHide();' style='position: absolute; top: 116px; right: 132px;'><img src='{$cdn}/img/icons/exit.png' style='width: 32px;' /></label>
							</div>
							<div id='notifier'>
								<span><b>Permanently Storing Large Data</b><br/><hr/>This allows you to save your preloaded videos locally, allowing you to resume playback even after you close the tab. <span style='color: gray;'>Maximum size used is limited to 1 GB.</span></span>
							</div>
							<div id='rightfloat'>
								<p onclick=\"$('#queue_content').slideToggle(200);\" class='title'>Queue</p>
								<div id='queue_content'>
								</div>
							</div>
						</body>
					</html>
				";
			}
			else {
				echo "
					<html>
						<head>
							<title>Error</title>
						</head>
						<body>
							<h1>Expired or invalid link.</h1>
							<h2><a href='{$_sys['node_root']}app.Anistream?gm' class='link'>Retrieving new access key, please wait...</a></h2>
							<script>
								function goToTheatre() { window.location = '{$_sys['node_root']}app.Anistream?gm'; }
								setTimeout(goToTheatre, 500);
							</script>
						</body>
					</html>
				";
			}
		}
		else {
			echo "
				<html>
					<head>
						<title>Error</title>
					</head>
					<body>
						<h1>You must be logged in to view this content.</h1>
					</body>
				</html>
			";
		}
	}
	elseif (isset($_GET["gm"])) {
		if (isset($_SESSION["username"])) {
			$valid = readDB($_sys["path_data"] . "db/.valid-id");
			$pwh = substr(sha1(uniqid() . $_sys["salt"]), 0, 5);
			$valid[$_SESSION["username"]] = array("pwh" => $pwh, "time" => time());
			writeDB($_sys["path_data"] . "db/.valid-id", $valid);
			echo "
				<div id='content'>
					<div class='block warning'>
						<h2>You will automatically be sent to the theatre within a few seconds.</h2>
					</div>
					<div class='block'>
						<h2><a href='{$_sys['node_url']}/?u={$_SESSION['username']}&pwh={$pwh}' class='link'>Click me to enter the theatre, if you are not automatically redirected.</a></h2>
						<script>
							function goToTheatre() { window.location = '{$_sys['node_url']}/?u={$_SESSION['username']}&pwh={$pwh}'; }
							setTimeout(goToTheatre, 500);
						</script>
						<p>You have 30 seconds to click enter the theatre, otherwise you must refresh this page for a new link.</p>
						<small>Ticket ID: {$pwh}</small>
					</div>
				</div>
			";
		}
		else {
			echo "
				<div id='content'>
					<div class='block warning'>
						<h2>Please login again.</h2>
					</div>
				</div>
			";
		}
	}
?>