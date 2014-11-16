<?php
if (isset($_GET["profilePicture"])) {
	if (file_exists($_sys["path_cdn"] . "img/static-profile-pictures/{$_GET['profilePicture']}.png")) {
		$reqFile = $_sys["path_cdn"] . "img/static-profile-pictures/{$_GET['profilePicture']}.png";
	}
	elseif (file_exists($_sys["path_cdn"] . "img/static-profile-pictures/{$_GET['profilePicture']}.jpg")) {
		$reqFile = $_sys["path_cdn"] . "img/static-profile-pictures/{$_GET['profilePicture']}.jpg";
	}
	elseif (file_exists($_sys["path_data"] . "user/{$_GET['profilePicture']}/.profile_picture")) {
		$reqFile = $_sys["path_data"] . "user/{$_GET['profilePicture']}/.profile_picture";
	}
	else {
		$reqFile = $_sys["path_cdn"] . "img/default_avatar.png";
	}
	
	if (fextIsImage($reqFile)) {
		header("Content-Type: " . @mimetype($reqFile));
	}
	else {
		header("Content-Type: image/png");
	}
	readfile($reqFile);
}
elseif (isset($_GET["background"])) {
	$ak = readDB($_sys["path_data"] . "db/.access-key");
	$cssDB = readDB($_sys["path_data"] . "user/{$_GET['background']}/.css");
	$data = $ak[$cssDB["background"]];
	header("Content-Type: image/jpg");
	readfile($data["local"]);
}
elseif (isset($_GET["raw"])) {
	$ak = readDB($_sys["path_data"] . "db/.access-key");
	$data = $ak[$_GET["raw"]];
	// max filesize through this method is 10MB!
	if ($data["filesize"] < 1024*1024*10) {
		headMimetype($data["local"]);
		readfile($data["local"]);
	}
	else {
		die("filesize too big, cannot read.");
	}
}
elseif (isset($_GET["image_thumbnail"])) {
	$ak = readDB($_sys["path_data"] . "db/.access-key");
	$data = $ak[$_GET["image_thumbnail"]];
	if (fextIsImage($data["file"])) {
		$thumb = $_sys["path_data"] . "cache/thumbnail_{$_GET['image_thumbnail']}.jpg";
		if (!file_exists($thumb) || filesize($thumb) == 0) {
			iTF($data["local"], $thumb, 240, 91);
		}
		header("Content-Type: image/jpeg"); // it's always jpg.
		readfile($thumb);
	}
	else {
		die("invalid parameter.");
	}
}
elseif (isset($_GET["video_thumbnail"])) {
	$ak = readDB($_sys["path_data"] . "db/.access-key");
	$data = $ak[$_GET["video_thumbnail"]];
	if (fextIsVideo($data["file"])) {
		$thumb = $_sys["path_data"] . "cache/thumbnail_{$_GET['video_thumbnail']}.jpg";
		if (!file_exists($thumb) || filesize($thumb) == 0) {
			if ($_sys["os"] == "linux") {
				exec("ffmpeg -itsoffset -3 -i \"{$data['local']}\" -vcodec mjpeg -vframes 1 -an -f rawvideo -s 640x480 {$thumb}");
			}
			else {
				exec("\"" . strtr(__DIR__, array("\\" => "/")) . "/../exe/ffmpeg.exe\" -itsoffset -3 -i \"{$data['local']}\" -vcodec mjpeg -vframes 1 -an -f rawvideo -s 640x480 {$thumb}");
			}
		}
		header("Content-Type: image/jpeg"); // it's always jpg.
		readfile($thumb);
	}
	else {
		die("invalid parameter.");
	}
}
elseif (isset($_GET["music_thumbnail"])) {
	$file = $_sys["path_data"] . "cache/albumArt_{$_GET['music_thumbnail']}";
	if (file_exists($file)) {
		header("Content-Type: image/jpeg"); // it's always jpg.
		readfile($file);
	}
}
elseif (isset($_GET["direct"])) {
	$ak = readDB($_sys["path_data"] . "db/.access-key");
	$data = $ak[$_GET["direct"]];
	if (isset($data)) {
		header("Location: " . $data["local"]);
	}
}
elseif (isset($_GET["download"])) {
	$ak = readDB($_sys["path_data"] . "db/.access-key");
	$hash = $_GET["download"];
	if (isSomething($ak[$hash])) {
		// add security here
		$file = $ak[$hash]["local"];
		if ($_sys["xsendfile"]) {
		//if ($_sys["path_drive"] === "/") {
			header("X-Sendfile: /home/aftermirror/public_html/{$ak[$hash]['local']}");
			header("Content-Type: " . mimetype($ak[$hash]["file"]));
			header("Content-Disposition: attachment; filename=\"{$ak[$hash]['file']}\"");
		}
		elseif (function_exists("http_send_content_disposition")) {
			http_send_content_disposition($ak[$hash]["file"], true);
			http_send_content_type(mimetype($ak[$hash]["file"]));
			#http_throttle(0.1, 2048);
			http_send_file($ak[$hash]["local"]);
		}
		else {
			header("Content-length: " . filesize($file));
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . $ak[$hash]["file"] . '"');
			readfile($file);
		}
	}
}
elseif (isset($_GET["proxy"])) {
	$f = $_sys["path_data"] . "temp/" . cleanANString($_GET["proxy"]) . "." . fext(basenamex($_GET["proxy"]));
	if (!file_exists($f)) {
		file_put_contents($f, file_get_contents($_GET["proxy"]));
	}
	header("Content-Type: " . @mimetype($f));
	readfile($f);
}
?>
