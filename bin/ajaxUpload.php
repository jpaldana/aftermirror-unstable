<?php
session_start();
session_write_close(); // pesky little problem

if (!empty($_FILES)) {
	$fileCount = count($_FILES["file"]["name"]);
	for ($i = 0; $i < $fileCount; $i++) {
		$tmp = $_FILES["file"]["tmp_name"][$i];
		$dir = $_sys["path_data"] . "files/";
		if (isSomething($_SESSION["username"])) {
			$user = $_SESSION["username"] . "_" . getIPAddr();
			$isGuest = false;
		}
		else {
			$user = "guest_" . getIPAddr();
			$isGuest = true;
		}
		$target = time() . "_{$user}_" . $_FILES["file"]["name"][$i];
		$target = strtr($target, array(":" => "_", " " => "_"));
		$target = preg_replace("([^\w\s\d\-_~,;:\[\]\(\].]|[\.]{2,})", '', $target);
		
		if (move_uploaded_file($tmp, $dir . $target)) {
			// store into user database
			$accessKey = sha1($_sys["salt"] . $target . uniqid());
			$filesize = filesize($dir . $target);
			if ($isGuest) {
				$upl = readDB($_sys["path_data"] . "user/guest/.uploads");
				$upl["files"][] = array(
					"file" => $_FILES["file"]["name"][$i],
					"accessKey" => $accessKey
				);
				writeDB($_sys["path_data"] . "user/guest/.uploads", $upl);
				$whoami = "guest";
			}
			else {
				$upl = readDB(myDir() . ".uploads");
				$upl["size"] += (float) $filesize;
				$upl["files"][] = array(
					"file" => $_FILES["file"]["name"][$i],
					"accessKey" => $accessKey
				);
				writeDB(myDir() . ".uploads", $upl);
				$whoami = $_SESSION["username"];
			}
			$upl_ak = readDB($_sys["path_data"] . "db/.access-key");
			$upl_ak[$accessKey] = array(
				"uploader" => $whoami,
				"password" => "",
				"downloads" => 0,
				"lastDownload" => 0,
				"uploadIP" => getIPAddr(),
				"uploadTime" => time(),
				"local" => $dir . $target,
				"file" => $_FILES["file"]["name"][$i],
				"status" => 0,
				"filesize" => $filesize,
				"encrypted" => false
			);
			writeDB($_sys["path_data"] . "db/.access-key", $upl_ak);
			echo "Successfully uploaded: {$_FILES['file']['name'][$i]}<br/>";
		}
	}
}
?>