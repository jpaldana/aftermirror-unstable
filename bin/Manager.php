<?php
echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
				<h1 class='center'><span style='color: gray;'>my</span>|<span class='fxSlide'>cloud:manage</span></h1>
				<h2 class='center' style='font-size: 14px; color: gray;'>manage your files here</h2>
				<br />
				<hr />
				<br />
				<div class='center'>
					<a href='app.Upload' class='ajaxFriendly fxButton'>upload</a> <a href='app.Cloud?style=prism' class='ajaxFriendly fxButton btnFor_prism'>anime</a> <a href='app.Cloud?style=text' class='ajaxFriendly fxButton'>text</a> <a href='app.Cloud?style=grid' class='ajaxFriendly fxButton'>grid</a> <a href='app.PublicCloud' class='ajaxFriendly fxButton'>public files</a> <a href='app.Manager' class='ajaxFriendly fxButton' style='font-weight: bold;'>manage</a>
					<br />
					<br />
				</div>
";

if (file_exists(myDir() . ".uploads")) {
	$upl = readDB(myDir() . ".uploads");
	$ak = readDB($_sys["path_data"] . "db/.access-key");
	
	if (isset($_GET["delete"])) {
		// delete from my cloud only
		$newFiles = array();
		$upl["size"] = 0;
		foreach ($upl["files"] as $file) {
			if ($file["accessKey"] !== $_GET["delete"]) {
				$newFiles[] = $file;
				$upl["size"] += $ak[$file["accessKey"]]["filesize"];
			}
		}
		$upl["files"] = $newFiles;
		writeDB(myDir() . ".uploads", $upl);
	}
	
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
	
	foreach ($files as $key => $data) {
		echo "<h3>{$key}</h3>";
		foreach ($data as $upload) {
			echo "<a href='app.Manager?delete={$upload['accessKey']}' class='ajaxFriendly'><img src='{$_sys['path_cdn']}img/icons/error.png' class='btnDelete' /></a> <label for='input_{$upload['accessKey']}'>{$upload['file']}</label><br />"; 
		}
	}
	echo "
		<script>
			$('.btnDelete').css({ width: '16px', position: 'relative', top: '3px' });
		</script>
	";
}
echo "
			</div>
		</div>
	</div>
";
?>
