<?php
set_time_limit(120);

$animeID = $_GET["animeID"];
$episode = $_GET["episode"];
$type = $_GET["type"];

if (isset($_GET["getEpLink"])) {
	$html = file_get_contents($_GET["getEpLink"]);
	$html = strtr($html, array("generic-video-item" => "generic_video_item"));
	$html = str_get_html($html);
	$links = array();
	$videoLQ = array();
	$videoHQ = array();
	foreach ($html->find("div[class=generic_video_item]") as $rel) {
		$host = trim(strtr(strtolower($rel->plaintext), array("subbed" => "", "dubbed" => "")));
		$host = substr($host, 0, stripos($host, " "));
		if ($rel->find("a", 1)) {
			// normal
			$links[] = array(
				"thumb" => $rel->find("img", 0)->src,
				"link" => "http://www.animeultima.tv" . $rel->find("a", 0)->href,
				"uploader" => $rel->find("a", 1)->plaintext,
				"host" => $host
			);
		}
		else {
			// "now playing"
			$links[] = array(
				"thumb" => $rel->find("img", 0)->src,
				"link" => $_GET["getEpLink"],
				"uploader" => $rel->find("a", 0)->plaintext,
				"host" => $host
			);
		}
	}
	
	$video = "";
	$html = "";
	foreach ($links as $link) {
		switch($link["host"]) {
			case "mp4upload":
				$html = file_get_html($link["link"]);
				$html = $html->find("iframe[width=650]", 0)->src;
				$html = file_get_contents($html);
				$html = substr($html, 0, strripos($html, "video.mp4") + 9);
				$video = substr($html, strripos($html, "http://"));
			break;
			case "auengine":
				$html = file_get_html($link["link"]);
				$html = $html->find("iframe[width=650]", 0)->src;
				$html = file_get_contents($html);
				$html = substr($html, strripos($html, "url:") + 6);
				$html = substr($html, 0, stripos($html, ",") - 1);
				$video = urldecode($html);
			break;
			case "auengine.io":
				$html = file_get_html($link["link"]);
				$html = $html->find("iframe[width=650]", 0)->src;
				$html = file_get_contents($html);
				$html = substr($html, strripos($html, "file: '") + 7);
				$video = substr($html, 0, stripos($html, ",") - 1);
			break;
			case "yourupload":
				$html = file_get_html($link["link"]);
				$html = $html->find("iframe[width=650]", 0)->src;
				$html = file_get_contents($html);
				$html = substr($html, stripos($html, "file:") + 7);
				$video = substr($html, 0, stripos($html, '"') - 1);
			break;
		}
		if (isSomething($video)) {
			$ch = curl_init($video);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

			$data = curl_exec($ch);
			curl_close($ch);
			
			if (preg_match('/Content-Length: (\d+)/', $data, $matches)) {
				$contentLength = (int)$matches[1];
				if (!isSomething($videoHQ)) {
					$videoHQ = array("link" => $video, "size" => $contentLength);
					$videoLQ = array("link" => $video, "size" => $contentLength);
				}
				else {
					if ($videoHQ["size"] < $contentLength) {
						// this is more HQ than listed.
						$videoHQ = array("link" => $video, "size" => $contentLength);
					}
					else {
						if (!isSomething($videoLQ)) {
							$videoLQ = array("link" => $video, "size" => $contentLength);
						}
						else {
							if ($videoLQ["size"] > $contentLength) {
								// this is more LQ than listed.
								$videoLQ = array("link" => $video, "size" => $contentLength);
							}
						}
					}
				}
			}
			else {
				// ignore...
				//echo "{$video} - ??? B<br/>";
			}
		}
	}
	echo json_encode(array("low" => $videoLQ, "high" => $videoHQ));
}
elseif (isset($_GET["bgDownload"])) {
	sleep(10);
	$link = $_GET["bgDownload"];
	$size = $_GET["size"];
	$localFile = "{$_sys['path_data']}data/files/{$animeID}_{$episode}_{$type}_{$size}.mp4";
	
	if (file_exists($localFile)) {
		if (filesize($localFile) < 1024 * 1024) { // should be greater than 1MB...
			unlink($localFile);
		}
		else {
			exit;
			// already a file here. don't override.
		}
	}
	file_put_contents($localFile . ".filesize", "");
	exec("wget -O \"{$localFile}\" {$link}");
	unlink($localFile . ".filesize");
}
elseif (isset($_GET["statusCheck"])) {
	clearstatcache();
	$filesize = $_GET["esize"];
	$current = @filesize("{$_sys['path_data']}data/files/{$animeID}_{$episode}_{$type}_{$size}.mp4");
	
	$start = filemtime("{$_sys['path_data']}data/files/{$animeID}_{$episode}_{$type}_{$size}.mp4.filesize");
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
}
?>