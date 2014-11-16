<?php
session_start();
session_write_close();

set_time_limit(0);
ini_set('user_agent','Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.56 Safari/537.17');

if (isset($_GET["getAnimeUltimaLink"])) {
	// part of a chain
	$link = $_GET["getAnimeUltimaLink"];
	$_GET["filename"] = urlencode($_GET["filename"]);
	
	$fgc = file_get_contents($link);
	$fgc = substr($fgc, stripos($fgc, "http://mp4upload.com"));
	$fgc = substr($fgc, 0, stripos($fgc, '"'));
	
	echo "<b>Got MP4Upload link, finding video URL.</b><br/>{$fgc}";
	echo "<script>$('#status').load('core.CloudDL?getMP4UploadVideo={$fgc}&filename={$_GET['filename']}');</script>";
}
elseif (isset($_GET["getMP4UploadVideo"])) {
	// part of a chain
	$link = $_GET["getMP4UploadVideo"];
	$_GET["filename"] = urlencode($_GET["filename"]);
	
	$fgc = file_get_contents($link);
	$fgc = substr($fgc, 0, stripos($fgc, "video.mp4") + 9);
	$fgc = substr($fgc, strripos($fgc, "http://"));
	
	echo "<b>Got MP4Upload video URL, starting download.</b><br/>{$fgc}";
	echo "<script>$('#status').load('core.CloudDL?asyncDownload={$fgc}&filename={$_GET['filename']}');</script>";
}
elseif (isset($_GET["asyncDownload"])) {
	$link = $_GET["asyncDownload"];
	file_put_contents($_sys["path_data"] . "temp/{$_GET['filename']}.filesize", "0");
	$_GET["filename"] = urlencode($_GET["filename"]);

	$ch = curl_init($link);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

	$data = curl_exec($ch);
	curl_close($ch);

	if (preg_match('/Content-Length: (\d+)/', $data, $matches)) {
		$contentLength = (int)$matches[1];
		echo "Starting download (with Content-Length)...";
		
		echo "<script>bgLoad('core.CloudDL?forceDL={$link}&filename={$_GET['filename']}&filesize={$contentLength}');</script>";
		echo "<script>$('#status').load('core.CloudDL?getAsyncStatus={$_GET['filename']}&filesize={$contentLength}'); timer = setInterval(\"$('#status').load('core.CloudDL?getAsyncStatus={$_GET['filename']}&filesize={$contentLength}');\", 1250);</script>";
		
	}
	else {
		echo "Starting download...";
		echo "<script>bgLoad('core.CloudDL?forceDL={$link}&filename={$_GET['filename']}&filesize=-1');</script>";
		echo "<script>$('#status').load('core.CloudDL?getAsyncStatus={$_GET['filename']}&filesize={$contentLength}'); timer = setInterval(\"$('#status').load('core.CloudDL?getAsyncStatus={$_GET['filename']}&filesize=-1');\", 2000);</script>";
	}
}
elseif (isset($_GET["getAsyncStatus"])) {
	clearstatcache();
	$filesize = $_GET["filesize"];
	$current = @filesize($_sys["path_data"] . "temp/{$_GET['getAsyncStatus']}");
	
	$start = filemtime($_sys["path_data"] . "temp/{$_GET['getAsyncStatus']}.filesize");
	$cur = time();
	$dur = $cur - $start;
	$speed = $current / $dur; // byte/s
	$left = $filesize - $current;
	$tleft = round($left / $speed);
		
	if ($filesize > 0 && $left > 0) {
		//echo "$start, $cur, $dur, $speed, $left, $tleft<br/>";
		$mb = formatBytes($speed) . "/s";
		
		$zleft = "";
		if ($tleft > 60) {
			$tleft = round($tleft / 60);
			$zleft = "{$tleft} minutes";
		}
		else {
			$zleft = "{$tleft} seconds";
		}
		
		echo "Downloaded " . formatBytes($current) . " of " . formatBytes($filesize) . " (about {$zleft} @ {$mb})";
	}
	elseif ($current >= $filesize || !file_exists($_sys["path_data"] . "temp/{$_GET['getAsyncStatus']}.filesize") || $left == 0) {
		echo "<script>clearTimeout(timer);</script>";
		echo "<b>Done downloading!</b><br/>";
		
		$file = $_GET["getAsyncStatus"];
		$dir = $_sys["path_data"] . "files/";

		$user = $_SESSION["username"] . "_" . getIPAddr();
		$target = time() . "_{$user}_" . $file;
		$target = strtr($target, array(":" => "_", " " => "_"));
		$target = preg_replace("([^\w\s\d\-_~,;:\[\]\(\].]|[\.]{2,})", '', $target);
		
		rename($_sys["path_data"] . "temp/{$_GET['getAsyncStatus']}", $dir . $target);
		
		$accessKey = sha1($_sys["salt"] . $target . uniqid());
		$filesize = filesize($dir . $target);
		$upl = readDB(myDir() . ".uploads");
		$upl["size"] += (float) $filesize;
		$upl["files"][] = array(
			"file" => $file,
			"accessKey" => $accessKey
		);
		writeDB(myDir() . ".uploads", $upl);
		
		$upl_ak = readDB($_sys["path_data"] . "db/.access-key");
		$upl_ak[$accessKey] = array(
			"uploader" => $_SESSION["username"],
			"password" => "",
			"downloads" => 0,
			"lastDownload" => 0,
			"uploadIP" => getIPAddr(),
			"uploadTime" => time(),
			"local" => $dir . $target,
			"file" => $file,
			"status" => 0,
			"filesize" => $filesize,
			"encrypted" => false
		);
		writeDB($_sys["path_data"] . "db/.access-key", $upl_ak);
		
		echo "Done! - <span onclick=\"modal('core.Modal?modal_viewer={$accessKey}');\" class='link'>Direct Link</span>";
		unlink($_sys["path_data"] . "temp/{$_GET['getAsyncStatus']}.filesize");
	}
	else {
		echo "Downloaded " . formatBytes($current) . ".";
	}
}
elseif (isset($_GET["forceDL"])) {
	$link = $_GET["forceDL"];
	$filename = $_GET["filename"];
	/*
	$handle = fopen($link, "rb");
	$zt = 0;
	$z = time();
	while (!feof($handle)) {
		$fr = fread($handle, 8192);
		file_put_contents($_sys["path_data"] . "temp/{$filename}", $fr, FILE_APPEND);
		$zt += strlen($fr);
		if (time() - $z > 4) {
			file_put_contents($_sys["path_data"] . "temp/{$filename}.filesize", $zt);
		}
	}
	fclose($handle);
	*/
	exec("wget -O \"{$_sys['path_data']}temp/{$filename}\" {$link}");
	unlink($_sys["path_data"] . "temp/{$filename}.filesize");
}
elseif (isset($_GET["name"]) && isset($_GET["url"])) {
	$file = $_GET["name"];
	$url = $_GET["url"];
	$dir = $_sys["path_data"] . "files/";

	$user = $_SESSION["username"] . "_" . getIPAddr();
	$target = time() . "_{$user}_" . $file;
	$target = strtr($target, array(":" => "_"));
	$target = preg_replace("([^\w\s\d\-_~,;:\[\]\(\].]|[\.]{2,})", '', $target);

	$opts = array(
	  'http'=>array(
		'method'=>"GET",
		'header'=>"Accept-language: en\r\n" .
				  "Cookie: {$_GET['cookie']}\r\n"
	  )
	);

	$context = stream_context_create($opts);

	if (file_put_contents($dir . $target, file_get_contents($url, false, $context))) {
		$accessKey = sha1($_sys["salt"] . $target . uniqid());
		$filesize = filesize($dir . $target);
		$upl = readDB(myDir() . ".uploads");
		$upl["size"] += (float) $filesize;
		$upl["files"][] = array(
			"file" => $file,
			"accessKey" => $accessKey
		);
		writeDB(myDir() . ".uploads", $upl);
		
		$upl_ak = readDB($_sys["path_data"] . "db/.access-key");
		$upl_ak[$accessKey] = array(
			"uploader" => $_SESSION["username"],
			"password" => "",
			"downloads" => 0,
			"lastDownload" => 0,
			"uploadIP" => getIPAddr(),
			"uploadTime" => time(),
			"local" => $dir . $target,
			"file" => $file,
			"status" => 0,
			"filesize" => $filesize,
			"encrypted" => false
		);
		writeDB($_sys["path_data"] . "db/.access-key", $upl_ak);
		echo "Download complete. <span onclick=\"modal('core.Modal?modal_viewer={$accessKey}');\" class='link'>View File</span>";
	}
	else {
		echo "Something failed. Please try again later.";
	}
}
?>