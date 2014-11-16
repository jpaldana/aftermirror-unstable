<?php
echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
				<h1>Anime</h1>
				<h2>Fresh from animeultima.tv to you!</h2>
				<br />
				<hr />
";

if (isset($_GET["spider"])) {
	$html = file_get_contents($_GET["spider"]);
	if (strc($_GET["spider"], "episode")) {
		$html = strtr($html, array("generic-video-item" => "generic_video_item"));
		$html = str_get_html($html);
		$links = array();
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
					"link" => $_GET["spider"],
					"uploader" => $rel->find("a", 0)->plaintext,
					"host" => $host
				);
			}
		}
		echo "<h3>{$_GET['ep_title']} - Episode {$_GET['ep_num']}</h3>";
		foreach ($links as $link) {
			$title = urlencode("{$_GET['ep_title']}");
			switch ($link["host"]) {
				case "mp4upload":
				case "auengine":
				case "auengine.io":
				case "yourupload":
					echo "
						<a href='app.AnimeDownloader?parse={$link['link']}&host={$link['host']}&ep_title={$title}&ep_num={$_GET['ep_num']}' class='modalFriendly buttonLink fxBackground' style='font-weight: bold;'>{$link['host']} - {$link['uploader']}</a>
					";
				break;
				default:
					echo "
						<a href='app.AnimeDownloader?parse={$link['link']}&host={$link['host']}&ep_title={$title}&ep_num={$_GET['ep_num']}' class='modalFriendly buttonLink fxBackground'>{$link['host']} - {$link['uploader']}</a>
					";
				break;
			}
		}
	}
	else {
		$html = str_get_html($html);
		echo "<h1>Select episode</h1>";
		if (substr($_GET["spider"], -1) === "/") {
			$title = substr($_GET["spider"], 0, strripos($_GET["spider"], "/"));
		}
		else {
			$title = $_GET["spider"];
		}
		$title = substr($title, strripos($title, "/") + 1);
		$title = strtr($title, array("-english-subbed-dubbed-online" => "", "-" => " "));
		$title = explode(" ", $title);
		$ep_title = "";
		foreach ($title as $t) {
			$t = strtoupper(substr($t, 0, 1)) . substr($t, 1);
			$ep_title[] = $t; 
		}
		$ep_title = implode(" ", $ep_title);
		$ep_title_safe = urlencode($ep_title);
		echo "<h2>Anime: {$ep_title}</h2>";
		foreach ($html->find("tr") as $block) {
			if (isSomething($block->find("td[class=epnum]", 0)->plaintext)) {
				$url = $block->find("a", 0)->href;
				$epnum = $block->find("td[class=epnum]", 0)->plaintext;
				//echo "<h2><a href='app.Anime?spider=" . $block->find("a", 0)->href . "'>Episode " . $block->find("td[class=epnum]", 0)->plaintext . "</a></h2>";
				if (!isSomething($block->find("td[colspan=2]", 0)->plaintext)) {
					echo "<h2 onclick=\"$('#source').html('Loading...'); aLoad('source', 'app.Anime?spider={$url}&ep_title={$ep_title_safe}&ep_num={$epnum}&fix #source'); $(this).css('font-weight', 'bold');\" style='font-weight: normal;' class='link'>Episode {$epnum}</h2>";
				}
				else {
					echo "<h2 style='font-weight: normal;'>Episode {$epnum} (not yet aired)</h2>";
				}
			}
		}
	}
}
elseif (isset($_GET["parse"])) {
	switch ($_GET["host"]) {
		case "mp4upload":
			$html = file_get_html($_GET["parse"]);
			$html = $html->find("iframe[width=650]", 0)->src;
			$html = file_get_contents($html);
			// reverse the effing algorithm to find the *.mp4 instead.
			// assume they keep the filename to "video.mp4"
			$html = substr($html, 0, strripos($html, "video.mp4") + 9);
			$html = substr($html, strripos($html, "http://"));
			//$html = substr($html, stripos($html, "mp4:") + 6);
			//$html = substr($html, 0, stripos($html, '"'));
		break;
		case "auengine":
			$html = file_get_html($_GET["parse"]);
			$html = $html->find("iframe[width=650]", 0)->src;
			$html = file_get_contents($html);
			$html = substr($html, strripos($html, "url:") + 6);
			$html = substr($html, 0, stripos($html, ",") - 1);
			$html = urldecode($html);
		break;
		case "auengine.io": // default to last stream (720p)
			$html = file_get_html($_GET["parse"]);
			$html = $html->find("iframe[width=650]", 0)->src;
			$html = file_get_contents($html);
			//$html = substr($html, stripos($html, "file: '") + 7); // first stream
			$html = substr($html, strripos($html, "file: '") + 7); // last stream
			$html = substr($html, 0, stripos($html, ",") - 1);
		break;
		case "yourupload":
			$html = file_get_html($_GET["parse"]);
			$html = $html->find("iframe[width=650]", 0)->src;
			$html = file_get_contents($html);
			$html = substr($html, stripos($html, "file:") + 7);
			$html = substr($html, 0, stripos($html, '"') - 1);
		break;
		default:
		break;
	}
	if (isSomething($html)) {
		echo "<h2>Video URL</h2><a href='{$html}' rel='nofollow' class='noFlow buttonLink fxBackground'>{$html}</a><br/>";
		
		$ch = curl_init($html);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		$data = curl_exec($ch);
		curl_close($ch);

		if (preg_match('/Content-Length: (\d+)/', $data, $matches)) {
			$contentLength = (int)$matches[1];
			echo "<h2>Video Size: " . formatBytes($contentLength) . "</h2>";
		}
		else {
			echo "<h2>Video Size: Unknown</h2>";
		}
		$fext = fext($html);
		if (!fextIsVideo($fext)) {
			$fext = "mp4";
		}
		if ($_GET["ep_num"] < 10) {
			$_GET["ep_num"] = "0{$_GET['ep_num']}";
		}
		$id = uniqid() . 'animedl';
		echo "
			<form>
				<input type='text' id='filename' value='{$_GET['ep_title']} - {$_GET['ep_num']}.{$fext}'/>
			</form>
			<br />
			<span id='status'>
				<span onclick=\"aLoad('status', 'core.CloudDL?asyncDownload={$html}&filename=' + encodeURIComponent($('#filename').val())); $('#status').html('Please wait...');\">Download</span>
			</span>";
	}
	else {
		echo "<h2>Unsupported host / no video found: {$_GET['host']}</h2>";
	}
}
else {
	$recent = array();
	$html = file_get_html("http://www.animeultima.tv/ajax.php?method=newrelease_calendarview&standalone=1");
	foreach ($html->find("td[class=item], td[class=itemtoday]") as $e) {
		$mod = $e->find("div[class=dnum]", 0)->plaintext;
		foreach ($e->find("a") as $a) {
			$exp = explode(" Episode ", $a->plaintext);
			$recent[$mod][] = array("href" => "http://www.animeultima.tv" . $a->href, "title" => $exp[0], "episode" => $exp[1]);
		}
	}
	$month = date("F");
	echo "<h3>Anime releases for the month of {$month}.</h3>";
	$myAnimeList = readDB(myDir() . ".anime-list");

	if (isset($_GET["add"])) {
		$myAnimeList[$_GET["add"]] = true;
		writeDB(myDir() . ".anime-list", $myAnimeList);
	}
	if (isset($_GET["remove"])) {
		$myAnimeList = pushValueFromArray($_GET["remove"], $myAnimeList);
		writeDB(myDir() . ".anime-list", $myAnimeList);
	}
	foreach ($recent as $k => $v) {
		echo "<h2><small>{$month}</small> {$k}</h2>";
		foreach ($v as $ep) {
			if (isset($myAnimeList[$ep["title"]])) {
				echo "<a href='app.AnimeDownloader?remove={$ep['title']}' class='ajaxFriendly'><img src='{$_sys['path_cdn']}img/icons/error.png' style='width: 12px;' /></a> ";
				echo "<a href='app.AnimeDownloader?spider={$ep['href']}&ep_title={$ep['title']}&ep_num={$ep['episode']}' class='ajaxFriendly' style='font-weight: bold;'>{$ep['title']} - Episode {$ep['episode']}</a><br/>";
			}
			else {
				echo "<a href='app.AnimeDownloader?add={$ep['title']}' class='ajaxFriendly'><img src='{$_sys['path_cdn']}img/icons/add.png' style='width: 12px;' /></a> ";
				echo "<a href='app.AnimeDownloader?spider={$ep['href']}&ep_title={$ep['title']}&ep_num={$ep['episode']}' class='ajaxFriendly'>{$ep['title']} - Episode {$ep['episode']}</a><br/>";
			}
		}
		echo "<br /><br />";
	}
}

echo "
			</div>
		</div>
	</div>
";
?>