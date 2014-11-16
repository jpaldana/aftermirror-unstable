<?php
echo "
<html>
	<head>
		<title>after|mirror</title>
		<link rel='shortcut icon' href='{$_sys['path_cdn']}favicon.ico' />
		<link rel='stylesheet' href='{$_sys['path_cdn']}css/style.css' />
		<link rel='stylesheet' href='{$_sys['path_cdn']}css/style-page.css' />
		<link rel='stylesheet' href='{$_sys['path_cdn']}css/theatre.css' />
		<meta name='keywords' content='aftermirror, after, mirror, after|mirror' />
		<meta name='description' content='You found love in a hopeless place.' />
		<meta name='viewport' content='minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no'>
		<meta http-equiv='cleartype' content='on'>
		<script type='text/javascript' src='{$_sys['path_cdn']}js/jquery.js'></script>
		<script type='text/javascript' src='{$_sys['path_cdn']}js/flo-extended.js'></script>
		<script type='text/javascript' src='{$_sys['path_cdn']}js/flo.js'></script>
		<script type='text/javascript' src='{$_sys['path_cdn']}js/fancyEffects.js'></script>
		<script type='text/javascript' src='{$_sys['path_cdn']}js/mediaPreloader.js'></script>
		<script type='text/javascript' src='{$_sys['path_cdn']}js/mini-win.js'></script>
		<script type='text/javascript' src='{$_sys['path_cdn']}js/sidebar-slider.js'></script>
		<script type='text/javascript' src='{$_sys['path_cdn']}js/upload.js'></script>
		<script type='text/javascript' src='{$_sys['path_cdn']}js/jquery.flowtype.js'></script>
		<script type='text/javascript' src='{$_sys['path_cdn']}js/jquery.panzoom.min.js'></script>
		<script type='text/javascript' src='{$_sys['path_cdn']}js/jquery.unveil.js'></script>
		<script type='text/javascript' src='{$_sys['path_cdn']}js/jquery.autogrow.min.js'></script>
		<script type='text/javascript' src='{$_sys['path_cdn']}js/jquery.slimscroll.min.js'></script>
		<script type='text/javascript' src='{$_sys['path_cdn']}js/jquery.color.plus-names.min.js'></script>
		<script src='https://apis.google.com/js/client:platform.js'></script>
		<script src='https://apis.google.com/js/client:plusone.js'></script>
";
if (file_exists(myDir() . ".css")) {
	$cssDB = readDB(myDir() . ".css");
	echo "<style type='text/css'>\n";
	$sidebarBG = "rgba(255, 255, 255, 0.5)";
	$sidebarText = "black";
	if (isset($cssDB["sidebar"])) {
		$sidebarBG = $cssDB["sidebar"];
		$sidebarText = $cssDB["sidebar_text"];
	}
	if (isset($cssDB["backgroundType"])) {
		if ($cssDB["backgroundType"] == "solid") {
			echo "
				#underlay { background-color: {$cssDB['background']}; }
				#content { background-color: transparent; }
				#sidebar { background-color: {$sidebarBG}; color: {$sidebarText}; }
			";
		}
		elseif ($cssDB["backgroundType"] == "image") {
			echo "
				#underlay { background-image: url({$_sys['path_root']}profile/{$_SESSION['username']}/background.jpg); }
				#content { background-color: transparent; }
				#sidebar { background-color: {$sidebarBG}; color: {$sidebarText}; }
			";
		}
	}
	echo "</style>\n";
}
if (!isset($wpDB[$_SESSION["username"]]["points"])) {
	$wpDB[$_SESSION["username"]]["points"] = 0;
}

$rev = getRevisionStatus();
$revDate = date("m.d", $rev["date"]);

echo "
	</head>
	<body>
		<div id='underlay'></div>
		<div id='sidebarContainer' class='sidebarInvisible'>
			<div id='sidebar'>
				<a href='app.Home' style='text-decoration: none; position: relative;'>
					<h1 class='title'><span style='color: gray;'>after</span>|<span class='fxSlide'>mirror</span></h1>
					<span style='position: absolute; left: 95px; top: -22px; font-size: 10px;'>r{$rev['hash']}.{$revDate}&nbsp;#{$rev['num']}</span>
				</a>
				<div style='text-align: right; margin: 6px;'>
";

if (isset($_SERVER["HTTPS"]) || (isset($https) && $https)) {
	// on
	echo "
		<small style='color: green; text-decoration: none;'>SSL</small>
	";
}
else {
	// off
	echo "
		<a href='https://{$_SERVER['HTTP_HOST']}' style='text-decoration: none;'><small style='color: red;'>SSL</small></a>
	";
}

echo "
				</div>
				<hr />
				<br />
				<a href='app.Home' class='ajaxFriendly buttonLink fxBackground'>What's new</a>
				<a href='app.Companion' class='ajaxFriendly buttonLink fxBackground'>Companion</a>
				<a href='app.Cloud' class='ajaxFriendly buttonLink fxBackground'>Cloud</a>
				<a href='app.Lounge' class='ajaxFriendly buttonLink fxBackground'>Lounge</a>
";
$myDat = readDB(myDir() . ".account");
if ($myDat["access"] > 8) {
	echo "
				<a href='app.Admin' class='ajaxFriendly buttonLink fxBackground'>Administration</a>
	";
}
echo "
				<br />
				<hr />
				<br />
				<div style='background-image: url({$_sys['path_root']}profile/{$_SESSION['username']}/picture.jpg);' class='profilePicture'></div>
				<span class='profileUsername'>{$_SESSION['username']}</span>
				<a href='#' class='buttonLink'>Wallet: <span id='yenCount'>&yen;{$wpDB[$_SESSION['username']]['points']}</span></a>
				<a href='#' class='buttonLink'>Notifications: <span id='notCount'>0</span></a>
				<br />
				<div class='hAlignContainer'>
					<div class='hAlign'>
						<a href='core.LoginWorker?do=logout' style='float: left;' class='buttonLink fxBackground' onclick=\"gapi.auth.signOut();\"><img src='{$_sys['path_cdn']}img/icons/power.png' style='width: 24px;' /><br/>logout</a>
						<a href='app.Settings' style='float: right;' class='ajaxFriendly buttonLink fxBackground'><img src='{$_sys['path_cdn']}img/icons/settings.png' style='width: 24px;' /><br/>settings</a>
					</div>
				</div>
				<br />
				<hr />
				<br />
				<div id='messenger'>
					<a href='app.Messenger?manageList' class='modalFriendly buttonLink fxBackground'>Messenger</a>
";
$messengerDB = readDB(dir_fix(myDir() . ".messenger"));
if (!isSomething($messengerDB)) {
	// corrupted or non-existent, load default variables
	$messengerDB = array(
		"status" => "available",
		"activeFriends" => array()
	);
	writeDB(dir_fix(myDir() . ".messenger"), $messengerDB);
}
switch ($messengerDB["status"]) {
	case "busy":
		echo "<a href='#' class='preventDefault buttonLink fxBackground' onclick=\"miniWindow('app.Messenger?statusChange', 'statusChanger');\" id='messengerStatus'>Status: <b><span class='messengerBusy'>&bull;</span> busy</b></a>";
	break;
	case "away":
		echo "<a href='#' class='preventDefault buttonLink fxBackground' onclick=\"miniWindow('app.Messenger?statusChange', 'statusChanger');\" id='messengerStatus'>Status: <b><span class='messengerAway'>&bull;</span> away</b></a>";
	break;
	case "invisible":
		echo "<a href='#' class='preventDefault buttonLink fxBackground' onclick=\"miniWindow('app.Messenger?statusChange', 'statusChanger');\" id='messengerStatus'>Status: <b><span class='messengerInvisible'>&bull;</span> invisible</b></a>";
	break;
	case "available":
	default:
		echo "<a href='#' class='preventDefault buttonLink fxBackground' onclick=\"miniWindow('app.Messenger?statusChange', 'statusChanger');\" id='messengerStatus'>Status: <b><span class='messengerAvailable'>&bull;</span> available</b></a>";
	break;
}
if (isSomething($messengerDB["activeFriends"])) {
	foreach ($messengerDB["activeFriends"] as $messengerFriend) {
		echo "<a class='modalFriendly buttonLink fxBackground'><span class='messengerAvailable'>&bull;</span> {$messengerFriend}</a>";
	}
}
echo "
				</div>
				<br />
				<hr />
				<br />
				<div id='worker'>
					<a class='buttonLink fxBackground'>Network: <span id='netSpeed'>0 B/s</span></a>
					<a class='buttonLink fxBackground'><span id='netCache'>Using: 0 B<br/>Remaining: 0 B</span></a>
					<div id='queue'>
					</div>
					<!--
					<a class='preventDefault buttonLink fxBackground' style='color: cyan; background-color: rgba(0, 0, 0, 0.5);' onclick=\"reloadCachedFiles();\">Refresh</a>
					<a class='preventDefault buttonLink fxBackground' style='color: red; background-color: rgba(0, 0, 0, 0.5);' onclick=\"removeAllFiles(); setTimeout('removeAllFiles();', 1000);\">Remove All</a>
					-->
				</div>
			</div>
		</div>
";
?>
