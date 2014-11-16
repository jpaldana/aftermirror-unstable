<?php
$cdn = $_sys["node_cdn"];
if (!isset($_GET["u"])) { header("Location: {$_sys['node_root']}"); die(); }
$valid = readDB($_sys["path_data"] . "db/.valid-id");
if (!isSomething($_GET["u"]) || ($_GET["u"] === "undefined" && $_GET["pwh"] === "undefined")) { echo "<script>window.location='{$_sys['node_root']}app.Lounge'</script>"; die(); }

$id = $valid[$_GET["u"]];
if (!($id["pwh"] === $_GET["pwh"] && ($id["time"] + $_sys["node_expiry"]) > time())) { echo "<script>window.location='{$_sys['node_root']}app.Lounge'</script>"; die(); }



echo "
	<html>
		<head>
			<title>after|mirror: theatre</title>
			<meta name='viewport' content='minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no'>
			<meta http-equiv='cleartype' content='on'>
			<meta name='keywords' content='aftermirror, after, mirror, after|mirror' />
			<meta name='description' content='You found love in a hopeless place.' />
			<meta name='viewport' content='minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no'>
			<link rel='shortcut icon' href='{$_sys['node_cdn']}/favicon.ico' />
			<link href='{$_sys['node_cdn']}/css/style.css' rel='stylesheet' />
			<link href='{$_sys['node_cdn']}/css/style-page.css' rel='stylesheet' />
			<link href='{$_sys['node_cdn']}/css/style-anistream.css' rel='stylesheet' />
			<script src='/socket.io/socket.io.js'></script>
			<script src='{$_sys['node_cdn']}/js/jquery.js'></script>
			<script src='{$_sys['node_cdn']}/js/flo.js'></script>
			<script src='{$_sys['node_cdn']}/js/sidebar-slider.js'></script>
			<script src='{$_sys['node_cdn']}/js/jquery.slimscroll.min.js'></script>
			<script src='{$_sys['node_cdn']}/js/jquery.unveil.js'></script>
			<script>
				var aniUsername = '{$_GET['u']}';
				var aniNodeRoot = '{$_sys['node_root']}';
				var aniNodeCDN = '{$_sys['node_cdn']}';
				var prismRoot = '{$_sys['prism_root']}';
			</script>
			<script src='{$_sys['node_cdn']}/js/anistream.js'></script>
			<script src='{$_sys['node_cdn']}/js/fancyEffects.js'></script>
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
				#underlay { background-image: url({$_sys['node_root']}profile/{$_SESSION['username']}/background.jpg); }
				#content { background-color: transparent; }
				#sidebar { background-color: {$sidebarBG}; color: {$sidebarText}; }
			";
		}
	}
	echo "</style>\n";
}
$rev = getRevisionStatus();
$revDate = date("m.d", $rev["date"]);
echo "
		</head>
		<body>
			<div id='underlay'></div>
				<div id='sidebarContainer' class='sidebarInvisible'>
				<div id='sidebar'>
					<a href='{$_sys['node_root']}app.Home' style='text-decoration: none; position: relative;'>
						<h1 class='title'><span style='color: gray;'>after</span>|<span class='fxSlide'>mirror</span></h1>
						<span style='position: absolute; left: 95px; top: -22px; font-size: 10px;'>r{$rev['hash']}.{$revDate}&nbsp;#{$rev['num']}</span>
					</a>
					<hr />
					<br />
					<a target='_blank' href='{$_sys['node_root']}app.Home' class='buttonLink fxBackground'>What's new</a>
					<a href='#' class='buttonLink fxBackground'>Companion</a>
					<a target='_blank' href='{$_sys['node_root']}app.Cloud' class='buttonLink fxBackground'>Cloud</a>
					<a target='_blank' href='{$_sys['node_root']}app.Lounge' class='buttonLink fxBackground'>Lounge</a>
					<br />
					<hr />
					<br />
					<div style='background-image: url({$_sys['node_root']}core.DataGate?profilePicture={$_SESSION['username']});' class='profilePicture'></div>
					<span class='profileUsername'>{$_SESSION['username']}</span>
					<a href='#' class='buttonLink'>Wallet: <span id='yenCount'>&yen;{$wpDB[$_SESSION['username']]['points']}</span></a>
					<a href='#' class='buttonLink'>Notifications: <span id='notCount'>0</span></a>
					<br />
					<div class='hAlignContainer'>
						<div class='hAlign'>
							<a href='{$_sys['node_root']}core.LoginWorker?do=logout' style='float: left;' class='buttonLink fxBackground'><img src='{$_sys['node_cdn']}/img/icons/power.png' style='width: 24px;' /><br/>logout</a>
							<a target='_blank' href='{$_sys['node_root']}app.Settings' style='float: right;' class='buttonLink fxBackground'><img src='{$_sys['node_cdn']}/img/icons/settings.png' style='width: 24px;' /><br/>settings</a>
						</div>
					</div>
					<br />
					<hr />
					<br />
					<div id='worker'>
						<a class='buttonLink fxBackground'>Network: <span id='netSpeed'>0 B/s</span></a>
						<a class='buttonLink fxBackground'><span id='netCache'>Using: 0 B<br/>Remaining: 0 B</span></a>
						<div id='queue'>
						</div>
						<a class='preventDefault buttonLink fxBackground' style='color: cyan; background-color: rgba(0, 0, 0, 0.5);' onclick=\"reloadCachedFiles();\">Refresh</a>
						<a class='preventDefault buttonLink fxBackground' style='color: red; background-color: rgba(0, 0, 0, 0.5);' onclick=\"removeAllFiles(); setTimeout('removeAllFiles();', 1000);\">Remove All</a>
					</div>
					<audio id='beeper' style='display: none;'><source src='{$_sys['node_cdn']}beep.mp3' type='audio/mp3' /></audio>
				</div>
			</div>
			<div id='content'>
				<div id='container'>
					<div class='block' style='background-color: black;'>
						<video id='video' controls></video>
					</div>
					<div class='block'>
						<div id='messages'></div>
						<div id='inputbox'>
							<form id='chatter'>
								<input type='text' placeholder='say something...' id='userinput' autocomplete='off' />
							</form>
						</div>
						<span id='console' style='color: gray; font-family: Monospace;'>--</span><br/>
						Message alert volume: 
						<small class='fxButton' onclick=\"$('#beeper')[0].volume = 0.3;\">low</small> 
						<small class='fxButton' onclick=\"$('#beeper')[0].volume = 0.55;\">medium</small> 
						<small class='fxButton' onclick=\"$('#beeper')[0].volume = 1.0;\">high</small>
					</div>
					<div class='block'>
";

$w = readDB($_sys["path_data"] . "db/.access-key");
$d = readDB($_sys["path_data"] . "user/{$_GET['u']}/.uploads");
$va = false;

$z = array();
/*
// private
if (isSomething($d)) {
	foreach ($d["files"] as $file) {
		if (fextIsVideo($file["file"])) {
			if (!$va) {
				echo "<h2>My files</h2>";
			}
			//echo "<span onclick=\"loadMedia('{$file['accessKey']}', '{$file['file']}');\" class='link'>{$file['file']}</span><br/>";
			$file["freshness"] = time_since(time() - $w[$file["accessKey"]]["uploadTime"], true);
			$file["uploadtime"] = $w[$file["accessKey"]]["uploadTime"];
			$file = array_reverse($file);
			$z[$w[$file["accessKey"]]["uploadTime"] . uniqid()] = $file;
			$va = true;
		}
	}
}
if ($va) {
	knatsort($z);
	$z = array_reverse($z);
	echo "<div class='hAlignContainer'><div class='grid hAlign'>";
	foreach ($z as $file) {
		$btitle = $file["file"];
		$btitle = substr($btitle, 0, strripos($btitle, "."));
		echo "<div class='fxBackground cell' psize='cell2x1' style='background-image: url({$_sys['node_root']}core.DataGate?video_thumbnail={$file['accessKey']});' onclick=\"loadMedia('{$file['accessKey']}', '{$file['file']}');\"><label class='noFlow playIcon'>{$btitle} ({$file['freshness']} ago)</label></div>";
	}
	echo "</div></div>";
	$z = array();
}
*/
// prism
$prismDB = readDB($_sys["path_data"] . "db/.prism");
foreach ($prismDB["anime"] as $id => $details) {
	$altNames = "English: " . $details["english"] . " | Synonyms: " . $details["synonym"];
	echo "
		<div style='background-image: url({$details['cover']}); background-repeat: no-repeat; background-position: left center; background-size: 100px; padding-left: 120px; height: 150px;'>
			<h3>{$details['name']}</h3>
			<h2 class='noFlow'>{$altNames}</h2>
			<br />
	";
	if (isSomething($prismDB["files"][$id])) {
		$eps = array_reverse($prismDB["files"][$id], true);
		$latestEp = array_pop(array_keys($eps));

		$latest = array_pop($eps);
		
		echo "<b>Watch latest episode ({$latestEp}):</b><br/>";
		
		if (isSomething($latest["subbed"])) {
			if (isSomething($latest["subbed"]["low"])) {
				$latest["subbed"]["low"] = basename($latest["subbed"]["low"]);
				echo "<span class='fxButton' onclick=\"loadMedia('{$latest['subbed']['low']}', '{$details['name']} - {$latestEp} (low)');\">subbed - low</span> ";
			}
			if (isSomething($latest["subbed"]["high"])) {
				$latest["subbed"]["high"] = basename($latest["subbed"]["high"]);
				echo "<span class='fxButton' onclick=\"loadMedia('{$latest['subbed']['high']}', '{$details['name']} - {$latestEp} (high)');\">subbed - high</span> ";
			}
		}
		if (isSomething($latest["dubbed"])) {
			if (isSomething($latest["dubbed"]["low"])) {
				$latest["dubbed"]["low"] = basename($latest["dubbed"]["low"]);
				echo "<span class='fxButton' onclick=\"loadMedia('{$latest['dubbed']['low']}', '{$details['name']} - {$latestEp} (low)');\">dubbed - low</span> ";
			}
			if (isSomething($latest["dubbed"]["high"])) {
				$latest["dubbed"]["high"] = basename($latest["dubbed"]["high"]);
				echo "<span class='fxButton' onclick=\"loadMedia('{$latest['dubbed']['high']}', '{$details['name']} - {$latestEp} (high)');\">dubbed - high</span> ";
			}
		}
		
		if (isSomething($eps)) {
			echo "<br/><small>or</small><br/>";
			$rest = array_reverse($eps);
			echo "<select onchange=\"loadMedia($(this).val(), '{$details['name']} - {$num} ({$qual})');\">";
			echo "<option>select other episode...</option>";
			foreach ($eps as $num => $epdat) {
				foreach ($epdat as $type => $local) {
					foreach ($local as $qual => $file) {
						$file = basename($file);
						echo "<option value='{$file}'>{$type}: {$num} - {$qual} quality</option>";
					}
				}
			}
			echo "</select>";
		}
	}
	echo "</div>";
}

/*
// public
foreach (dir_get($_sys["path_data"] . "user") as $user) {
	$sn = false;
	$d = readDB($user . ".public-files");
	if (isSomething($d)) {
		$r = readDB($user . ".uploads");
		foreach ($r["files"] as $file) {
			if (fextIsVideo($file["file"]) && isset($d[$file["accessKey"]])) {
				if (!$sn) {
					echo "<hr/><h2>" . basename($user) . "'s public files</h2>";
					$sn = true;
				}
				//echo "<span onclick=\"loadMedia('{$file['accessKey']}', '{$file['file']}');\" class='link'>{$file['file']}</span><br/>";
				$file["freshness"] = time_since(time() - $w[$file["accessKey"]]["uploadTime"], true);
				$file["uploadtime"] = $w[$file["accessKey"]]["uploadTime"];
				$file = array_reverse($file);
				$z[$w[$file["accessKey"]]["uploadTime"] . uniqid()] = $file;
				$va = true;
			}
		}
	}
	if (isSomething($z)) {
		knatsort($z);
		$z = array_reverse($z);
		$bnUser = basename($user);
		echo "
			<div class='center'>
				<a class='standardLink preventDefault publicContainerUseText' block='{$bnUser}'>text</a> | <a class='standardLink preventDefault publicContainerUseGrid' block='{$bnUser}'>grid</a>
				<script>
					$(function() {
						$('.publicContainerUseText').on('click', function() {
							var usr = $(this).attr('block');
							$('#publicContainerText_' + usr).css('display', 'block');
							$('#publicContainerGrid_' + usr).css('display', 'none');
						});
						$('.publicContainerUseGrid').on('click', function() {
							var usr = $(this).attr('block');
							$('#publicContainerGrid_' + usr).css('display', 'block');
							$('#publicContainerText_' + usr).css('display', 'none');
							fixGrid();
						});
					});
				</script>
			</div>
			<br />
			<br />
		";
		
		echo "<div id='publicContainerText_{$bnUser}'>";
		$pDates = array();
		foreach ($z as $file) {
			$date = date("F j, Y", $file["uploadtime"]);
			if (!isset($pDates[$date])) {
				echo "<h3>{$date}</h3>";
				$pDates[$date] = true;
			}
			$btitle = $file["file"];
			$btitle = substr($btitle, 0, strripos($btitle, "."));
			echo "<a class='preventDefault buttonLink fxBackground' onclick=\"loadMedia('{$file['accessKey']}', '{$file['file']}');\">{$btitle} ({$file['freshness']} ago)</a>";
		}
		echo "</div>";
		
		echo "<div class='hAlignContainer' id='publicContainerGrid_{$bnUser}' style='display: none;'><div class='grid hAlign'>";
		foreach ($z as $file) {
			$btitle = $file["file"];
			$btitle = substr($btitle, 0, strripos($btitle, "."));
			echo "<div class='fxBackground cell' psize='cell2x1' style='background-image: url({$_sys['node_root']}core.DataGate?video_thumbnail={$file['accessKey']});' onclick=\"loadMedia('{$file['accessKey']}', '{$file['file']}');\"><label class='noFlow playIcon'>{$btitle} ({$file['freshness']} ago)</label></div>";
		}
		echo "</div></div>";
		
		
		$z = array();
	}
}
if (!$va) {
	echo "You don't have any videos!";
}
*/

echo "
					<script>
						fixGrid();
						$(window).on('resize', function() { fixGrid(); });
					</script>
					</div>
				</div>
			</div>
			<a href='#' id='toggleMenu'>&#9776; menu</a>
			<div id='modal' class='hAlignContainer'>
				<div id='modal_container' style='width: 100%; height: 100%;'>
					<div id='modal_close' style='position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px;' onclick='closeModal()'></div>
					<div id='modal_content' class='vAlign hAlign'>
					</div>
				</div>
			</div>
		</body>
	</html>
";
?>
