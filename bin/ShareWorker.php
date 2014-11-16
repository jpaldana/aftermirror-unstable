<?php
$ak = readDB($_sys["path_data"] . "db/.access-key");
if (isSomething($_POST["share"]) && isSomething($_POST["file"])) {
	$reqFile = $ak[$_POST["file"]];
	foreach ($_POST["share"] as $share) {
		$theirFiles = $_sys["path_data"] . "user/{$share}/.uploads";
		$fileDB = readDB($theirFiles);
		$fileExists = false;
		if (isSomething($fileDB["files"])) {
			foreach ($fileDB["files"] as $file) {
				if ($file["accessKey"] == $_POST["file"]) {
					$fileExists = true;
					break;
				}
			}
		}
		if (!$fileExists) {
			$fileDB["files"][] = array("file" => $reqFile["file"], "accessKey" => $_POST["file"]);
			$totalSize = 0;
			foreach ($fileDB["files"] as $file) {
				$totalSize += $ak[$file["accessKey"]]["filesize"];
			}
			$fileDB["size"] = $totalSize;
			writeDB($theirFiles, $fileDB);
		}
	}
}
header("Location: app.Cloud?share_success");
?>