<?php
echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
				<h1 class='center'><span style='color: gray;'>my</span>|<span class='fxSlide'>cloud:public</span></h1>
				<h2 class='center' style='font-size: 14px; color: gray;'>is currently sharing these files</h2>
				<br />
				<hr />
				<br />
				<div class='center'>
					<a href='app.Upload' class='ajaxFriendly fxButton'>upload</a> <a href='app.Cloud?style=prism' class='ajaxFriendly fxButton btnFor_prism'>anime</a> <a href='app.Cloud?style=text' class='ajaxFriendly fxButton'>text</a> <a href='app.Cloud?style=grid' class='ajaxFriendly fxButton'>grid</a> <a href='app.PublicCloud' class='ajaxFriendly fxButton' style='font-weight: bold;'>public files</a> <a href='app.Manager' class='ajaxFriendly fxButton'>manage</a>
					<br />
					<br />
				</div>
";
if (isset($_GET["update"]) && isset($_POST["mark"]) && isSomething($_POST["mark"])) {
	writeDB(myDir() . ".public-files", $_POST["mark"]);
}
elseif (isset($_GET["clear"])) {
	writeDB(myDir() . ".public-files", array());
}
$myPub = readDB(myDir() . ".public-files");

if (file_exists(myDir() . ".uploads")) {
	$upl = readDB(myDir() . ".uploads");
	$ak = readDB($_sys["path_data"] . "db/.access-key");
	$files = array("images" => array(), "videos" => array(), "music" => array(), "text" => array(), "others" => array());
	
	$size = formatBytes($upl["size"]);
	echo "<p>Total Size: {$size}</p>";
	
	foreach ($upl["files"] as $upload) {
		if (fextIsImage($upload["file"])) {
			$files["images"][] = $upload;
		}
		elseif (fextIsVideo($upload["file"])) {
			$files["videos"][] = $upload;
		}
		elseif (fextIsMusic($upload["file"])) {
			$files["music"][] = $upload;
		}
		elseif (fextIsText($upload["file"])) {
			$files["text"][] = $upload;
		}
		else {
			$files["others"][] = $upload;
		}
	}
	
	echo "<form action='app.PublicCloud?update' method='post'>";
	
	if (isSomething($files["images"])) {
		echo "<h3>Images</h3>";
		foreach ($files["images"] as $upload) {
			$checked = "";
			if (isset($myPub[$upload["accessKey"]])) {
				$checked = "checked";
			}
			echo "<input type='checkbox' name='mark[{$upload['accessKey']}]' {$checked} class='noevent' id='input_{$upload['accessKey']}' /> <label for='input_{$upload['accessKey']}'>{$upload['file']}</label><br />"; 
		}
	}
	
	if (isSomething($files["videos"])) {
		echo "<h3>Videos</h3>";
		foreach ($files["videos"] as $upload) {
			$checked = "";
			if (isset($myPub[$upload["accessKey"]])) {
				$checked = "checked";
			}
			echo "<input type='checkbox' name='mark[{$upload['accessKey']}]' {$checked} class='noevent' id='input_{$upload['accessKey']}' /> <label for='input_{$upload['accessKey']}'>{$upload['file']}</label><br />"; 
		}
	}
	
	if (isSomething($files["music"])) {
		echo "<h3>Music</h3>";
		foreach ($files["music"] as $upload) {
			$checked = "";
			if (isset($myPub[$upload["accessKey"]])) {
				$checked = "checked";
			}
			$imageURL = "{$_sys['path_cdn']}stripe.png";
			if (file_exists($_sys["path_data"] . "cache/mediaInfo_{$upload['accessKey']}")) {
				$mediaInfo = readDB($_sys["path_data"] . "cache/mediaInfo_{$upload['accessKey']}");
				if ($mediaInfo["albumArt"]) {
					$imageURL = "core.File?albumArt={$upload['accessKey']}";
				}
			}
			echo "<input type='checkbox' name='mark[{$upload['accessKey']}]' {$checked} class='noevent' id='input_{$upload['accessKey']}' /> <label for='input_{$upload['accessKey']}'>{$upload['file']}</label><br />"; 
		}
	}
	
	if (isSomething($files["text"])) {
		echo "<h3>Documents</h3>";
		foreach ($files["text"] as $upload) {
			echo "<input type='checkbox' name='mark[{$upload['accessKey']}]' {$checked} class='noevent' id='input_{$upload['accessKey']}' /> <label for='input_{$upload['accessKey']}'>{$upload['file']}</label><br />"; 
		}
	}
	
	if (isSomething($files["others"])) {
		echo "<h3>Other Files</h3>";
		foreach ($files["others"] as $upload) {
			echo "<input type='checkbox' name='mark[{$upload['accessKey']}]' {$checked} class='noevent' id='input_{$upload['accessKey']}' /> <label for='input_{$upload['accessKey']}'>{$upload['file']}</label><br />"; 
		}
	}
	
	echo "
			<br />
			<input type='submit' value='Mark as Public Files' style='width: 100%;' />
			<br />
			<a href='app.PublicCloud?clear' class='ajaxFriendly buttonLink fxBackground'>clear all</a>
		</form>
	";
}
echo "
			</div>
		</div>
	</div>
";
?>
