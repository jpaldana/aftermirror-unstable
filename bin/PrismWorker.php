<?php
set_time_limit(120);
ini_set('user_agent','Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.56 Safari/537.17');

$animeID = $_GET["animeID"];
$episode = $_GET["episode"];
$type = $_GET["type"];

echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
";

if (isset($_GET["getEpLinks"])) {
	$html = substr(file_get_contents($_GET["url"]), 0, 1024 * 128);
	//$html = file_get_contents($_GET["url"]);
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
				"link" => $_GET["url"],
				"uploader" => $rel->find("a", 0)->plaintext,
				"host" => $host
			);
		}
	}
	
	unset($video);
	unset($html);
	foreach ($links as $link) {
		
		switch($link["host"]) {
			case "mp4upload":
				$minHTML = substr(file_get_contents($link["link"]), 0, 1024 * 128);
				$html = str_get_html($minHTML);
				$html = $html->find("iframe[width=650]", 0)->src;
				$html = file_get_contents($html);
				$html = substr($html, 0, strripos($html, "video.mp4") + 9);
				$video = substr($html, strripos($html, "http://"));
			break;
			case "auengine":
				$minHTML = substr(file_get_contents($link["link"]), 0, 1024 * 128);
				$html = str_get_html($minHTML);
				$html = $html->find("iframe[width=650]", 0)->src;
				$html = file_get_contents($html);
				$html = substr($html, strripos($html, "url:") + 6);
				$html = substr($html, 0, stripos($html, ",") - 1);
				$video = urldecode($html);
			break;
			case "auengine.io":
				$minHTML = substr(file_get_contents($link["link"]), 0, 1024 * 128);
				$html = str_get_html($minHTML);
				$html = $html->find("iframe[width=650]", 0)->src;
				$html = file_get_contents($html);
				$html = substr($html, strripos($html, "file: '") + 7);
				$video = substr($html, 0, stripos($html, ",") - 1);
			break;
			case "yourupload":
				$minHTML = substr(file_get_contents($link["link"]), 0, 1024 * 128);
				$html = str_get_html($minHTML);
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
	
	echo "
		<h2>{$animeID}: episode {$episode} | type: {$type}</h2>
		<h3>Low</h3>
		<span style='display: block; width: 100%;' class='noFlow'>{$videoLQ['link']}</span><br/>
		<b>Size: </b> " . formatBytes($videoLQ["size"]) . " - {$videoLQ['size']}<br/>
		<div id='ajaxStatus_{$animeID}_{$episode}_{$type}_low'>
			<span onclick=\"aLoad('ajaxStatus_{$animeID}_{$episode}_{$type}_low', 'core.PrismAjaxDL?initializeDL={$videoLQ['link']}&animeID={$animeID}&episode={$episode}&type={$type}&quality=low&size={$videoLQ['size']}');\">Start Download</span>
		</div>
		<br />
		<hr />
		<br />
		<h3>High</h3>
		<span style='display: block; width: 100%;' class='noFlow'>{$videoHQ['link']}</span><br/>
		<b>Size: </b> " . formatBytes($videoHQ["size"]) . " - {$videoHQ['size']}<br/>
		<div id='ajaxStatus_{$animeID}_{$episode}_{$type}_high'>
			<span onclick=\"aLoad('ajaxStatus_{$animeID}_{$episode}_{$type}_high', 'core.PrismAjaxDL?initializeDL={$videoHQ['link']}&animeID={$animeID}&episode={$episode}&type={$type}&quality=high&size={$videoHQ['size']}');\">Start Download</span>
		</div>
	";
}

echo "
			</div>
		</div>
	</div>
";
?>
