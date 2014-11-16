<?php
ini_set('user_agent','Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.56 Safari/537.17');
$prismDB = readDB($_sys["path_data"] . "db/.prism");

echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
				<div class='center'>
					<h1><a href='app.Prism' class='ajaxFriendly'>Prism</a></h1>
					<h2>Anime Manager</h2>
				</div>
				<a href='app.Prism?add_dialog' class='ajaxFriendly buttonLink fxBackground'>Add Anime</a>
				<a href='app.Prism?manage_dialog' class='ajaxFriendly buttonLink fxBackground'>Manage Anime</a>
				<br />
				<hr />
				<br />
";

if (isset($_GET["add_dialog"])) {
	echo "
		<form action='app.Prism?verify_dialog' method='post'>
			<label for='animeName' class='textboxLabel'>Name</label>
			<input type='text' name='animeName' id='animeName' />
			<label for='animeID' class='textboxLabel'>MyAnimeList ID</label>
			<input type='text' name='animeID' id='animeID' />
			<label for='animeEpURL' class='textboxLabel'>animeultima.tv Episode Guide URL</label>
			<input type='text' name='animeEpURL' id='animeEpURL' />
			<br />
			<input type='submit' value='Verify' class='fullSize' />
		</form>
	";
}
elseif (isset($_GET["verify_dialog"]) && isSomething($_POST)) {
	$list = "http://wafflerain:waffle2626@myanimelist.net/api/anime/search.xml?q=" . urlencode($_POST["animeName"]);
	$result = simplexml_load_file($list);
	if (!$result) {
		$html = file_get_contents($list);
		$result = simplexml_load_string($html);
		var_dump($html);
	}
	$result = json_decode(json_encode($result), true);
	var_dump($list);
	print_a($result);
	$found = false;
	if ($result["entry"]["id"] == $_POST["animeID"]) {
		// only one result
		$found = $result["entry"];
	}
	else {
		foreach ($result["entry"] as $detail) {
			if ($detail["id"] == $_POST["animeID"]) {
				$found = $detail;
				break;
			}
		}
	}
	if (!$found) {
		echo "<p>Could not find anime ID.</p>";
	}
	else {
		$animeEnglishName = is_array($found["english"]) ? "" : $found["english"];
		$animeSynonymName = is_array($found["synonyms"]) ? "" : $found["synonyms"];
		$animeSynopsis = substr($found["synopsis"], 0, stripos($found["synopsis"], "<br />"));
		echo "
			<form action='app.Prism?confirm_dialog' method='post'>
				<label for='animeName' class='textboxLabel'>Name</label>
				<input type='text' name='animeName' id='animeName' value='{$found['title']}' />
				
				<label for='animeEnglishName' class='textboxLabel'>English Name(s) - optional</label>
				<input type='text' name='animeEnglishName' id='animeEnglishName' value='{$animeEnglishName}' />
				
				<label for='animeSynonymName' class='textboxLabel'>Synonym(s) - optional</label>
				<input type='text' name='animeSynonymName' id='animeSynonymName' value='{$animeSynonymName}' />
				
				<label for='animeID' class='textboxLabel'>MyAnimeList ID</label>
				<input type='text' name='animeID' id='animeID' value='{$found['id']}' />
				
				<label for='animeEpisodes' class='textboxLabel'>Episode Count</label>
				<input type='text' name='animeEpisodes' id='animeEpisodes' value='{$found['episodes']}' />
				
				<label for='animeScore' class='textboxLabel'>Current Score</label>
				<input type='text' name='animeScore' id='animeScore' value='{$found['score']}' />
				
				<label for='animeType' class='textboxLabel'>Airing Type</label>
				<input type='text' name='animeType' id='animeType' value='{$found['type']}' />
				
				<label for='animeStatus' class='textboxLabel'>Airing Status</label>
				<input type='text' name='animeStatus' id='animeStatus' value='{$found['status']}' />
				
				<label for='animeStartDate' class='textboxLabel'>Airing Start</label>
				<input type='text' name='animeStartDate' id='animeStartDate' value='{$found['start_date']}' />
				
				<label for='animeEndDate' class='textboxLabel'>Airing End</label>
				<input type='text' name='animeEndDate' id='animeEndDate' value='{$found['end_date']}' />
				
				<label for='animeSynopsis' class='textboxLabel'>Synopsis</label>
				<textarea name='animeSynopsis' id='animeSynopsis'>{$animeSynopsis}</textarea>
				<script>
					$(function() {
						$('textarea').autogrow();
					});
				</script>
				
				<label for='animeCoverArt' class='textboxLabel'>Cover Art</label>
				<input type='text' name='animeCoverArt' id='animeCoverArt' value='{$found['image']}' />
				
				<label for='animeEpURL' class='textboxLabel'>animeultima.tv Episode Guide URL</label>
				<input type='text' name='animeEpURL' id='animeEpURL' value='{$_POST['animeEpURL']}' />
				<br />
				<input type='submit' value='Confirm' class='fullSize' />
			</form>
		";
	}
}
elseif (isset($_GET["confirm_dialog"]) && isSomething($_POST)) {
	
	$block = array(
		"name" => $_POST["animeName"],
		"english" => $_POST["animeEnglishName"],
		"synonym" => $_POST["animeSynonymName"],
		"id" => $_POST["animeID"],
		"episodes" => $_POST["animeEpisodes"],
		"score" => $_POST["animeScore"],
		"type" => $_POST["animeType"],
		"status" => $_POST["animeStatus"],
		"start" => $_POST["animeStartDate"],
		"end" => $_POST["animeEndDate"],
		"synopsis" => $_POST["animeSynopsis"],
		"cover" => $_POST["animeCoverArt"],
		"epURL" => $_POST["animeEpURL"]
	);
	if ($_POST["animeStatus"] === "Finished Airing" || isSomething($_POST["animeEndDate"])) {
		$block["prism_active"] = false;
	}
	else {
		$block["prism_active"] = true;
		$prismDB["active"][$_POST["animeID"]] = true;
	}
	$prismDB["anime"][$_POST["animeID"]] = $block;
	writeDB($_sys["path_data"] . "db/.prism", $prismDB);
	echo "<p>{$_POST['animeName']} successfully added.</p>";
}
elseif (isset($_GET["manage_dialog"])) {
	echo "
		<form action='app.Prism?manage_submit' method='post'>
			<h3>Set active:</h3>
			<small>Anime must be active in order to download episodes and more.</small>
			<br />
			<br />
	";
	foreach ($prismDB["anime"] as $id => $anime) {
		if ($anime["prism_active"] || $prismDB["active"][$id]) {
			echo "<input type='checkbox' name='active[]' value='{$id}' id='anime_{$id}' checked /><label for='anime_{$id}'> {$anime['name']}</label><br />";
		}
		else {
			echo "<input type='checkbox' name='active[]' value='{$id}' id='anime_{$id}' /><label for='anime_{$id}'> {$anime['name']}</label><br />";
		}
	}
	echo "
			<br />
			<input type='submit' value='Save' class='fullSize' />
		</form>
	";
}
elseif (isset($_GET["manage_submit"]) && isset($_POST)) {
	$newActive = array();
	foreach ($prismDB["anime"] as $id => $data) {
		$prismDB["anime"][$id]["prism_active"] = false;
	}
	foreach ($_POST["active"] as $id) {
		$newActive[$id] = true;
		$prismDB["anime"][$id]["prism_active"] = true;
	}
	$prismDB["active"] = $newActive;
	writeDB($_sys["path_data"] . "db/.prism", $prismDB);
	echo "<p>Active anime saved.</p>";
}
elseif (isset($_GET["reload_dialog"])) {
	echo "
		<h3>{$prismDB['anime'][$_GET['reload_dialog']]['name']}</h3>
		<a href='app.Prism?download_dialog={$_GET['reload_dialog']}' class='ajaxFriendly buttonLink fxBackground'>Download New Episode(s)</a>
		<a href='app.Prism?update_dialog={$_GET['reload_dialog']}' class='ajaxFriendly buttonLink fxBackground'>Update Information</a>
	";
}
elseif (isset($_GET["download_dialog"])) {
	if (isset($_GET["refreshDownloadedContent"])) {
		//$dir = dir_get($_sys["path_data"] . "files");
		$dir = json_decode(file_get_contents("{$_sys['storage_root']}do.php?getPrismDir"));
		$dir = array_reverse($dir);
		$prismDB["files"] = array();
		foreach ($dir as $file) {
			$bn = basename($file);
			if (substr($bn, 0, 5) === "anime") {
				$part = explode("_", substr($bn, 0, -4)); // get rid of .mp4
				$prismDB["files"][$part[1]][$part[2]][$part[3]][$part[4]] = $file;
			}
		}
		writeDB($_sys["path_data"] . "db/.prism", $prismDB);
	}
	echo "<h3>{$prismDB['anime'][$_GET['download_dialog']]['name']}</h3>";
	echo "<a href='app.Prism?download_dialog={$_GET['download_dialog']}&refreshDownloadedContent' class='ajaxFriendly buttonLink fxBackground'>refresh downloaded content</a>";
	echo "<script src='{$_sys['path_cdn']}js/prism.js'></script>";
	$link = $prismDB["anime"][$_GET["download_dialog"]]["epURL"];
	$html = str_get_html(file_get_contents($link));
	$rows = $html->find("table[id=animetable]", 0)->find("tr");
	$result = array();
	foreach ($rows as $row) {
		if ($row->find("td[class=epnum]")) {
			$block = array(
				"episode" => $row->find("td[class=epnum]", 0)->plaintext,
				"episodeTitle" => $row->find("td[class=title]", 0)->plaintext,
				"airDate" => $row->find("td[class=airdate]", 0)->plaintext,
				"subbed" => $row->find("a", 1)->href,
				"dubbed" => $row->find("a", 2)->href
			);
			$result[] = $block;
		}
	}
	foreach ($result as $block) {
		echo "<h2>Episode {$block['episode']}</h2>";
		if (isSomething($block["airDate"])) {
			echo "<small>Aired: {$block['airDate']}</small><br /><br />";
		}

		if (isSomething($prismDB["files"][$_GET["download_dialog"]][$block["episode"]]["subbed"]["low"])) {
			// downloaded
			echo "<b>Subbed/low: Downloaded</b><br />";
		}
		else {
			// not downloaded
			echo "Subbed/low: Not Downloaded<br />";
		}
		if (isSomething($prismDB["files"][$_GET["download_dialog"]][$block["episode"]]["subbed"]["high"])) {
			// downloaded
			echo "<b>Subbed/high: Downloaded</b><br />";
		}
		else {
			// not downloaded
			echo "Subbed/high: Not Downloaded<br />";
		}
		if (isSomething($prismDB["files"][$_GET["download_dialog"]][$block["episode"]]["dubbed"]["low"])) {
			// downloaded
			echo "<b>Dubbed/low: Downloaded</b><br />";
		}
		else {
			// not downloaded
			echo "Dubbed/low: Not Downloaded<br />";
		}
		if (isSomething($prismDB["files"][$_GET["download_dialog"]][$block["episode"]]["dubbed"]["high"])) {
			// downloaded
			echo "<b>Dubbed/high: Downloaded</b><br />";
		}
		else {
			// not downloaded
			echo "Dubbed/high: Not Downloaded<br />";
		}
		echo "<br/>";

		$uniqid = 'prism' . crc32(mt_rand());
		if (isSomething($block["subbed"])) {
			echo "<div id='{$uniqid}_sub'>";
				//echo "<a href='core.PrismWorker?getEpLinks&animeID={$_GET['download_dialog']}&url={$block['subbed']}&episode={$block['episode']}&type=subbed' class='modalFriendly buttonLink fxBackground'>Download - Subbed</a>";
				//echo "<span onclick=\"ajaxDivLoad('{$uniqid}_sub', 'core.PrismWorker?getEpLinks&animeID={$_GET['download_dialog']}&url={$block['subbed']}&episode={$block['episode']}&type=subbed');\" class='buttonLink fxBackground'>Download - Subbed</a>";
				echo "<span onclick=\"ajaxDivLoad('{$uniqid}_sub', '{$_sys['storage_root_auto']}prism.php?getEpLinks&animeID={$_GET['download_dialog']}&url={$block['subbed']}&episode={$block['episode']}&type=subbed');\" class='buttonLink fxBackground'>Download - Subbed</a>";
			echo "</div>";
		}
		if (isSomething($block["dubbed"])) {
			echo "<div id='{$uniqid}_dub'>";
				//echo "<a onclick=\"$('#{$uniqid}_dubbed').load('app.PrismWorker?animeID={$_GET['download_dialog']}&url={$block['dubbed']}&episode={$block['episode']}&type=dubbed');\" class='preventDefault buttonLink fxBackground'>Download - Dubbed</a>";
				echo "<span onclick=\"ajaxDivLoad('{$uniqid}_dub', '{$_sys['storage_root_auto']}prism.php?getEpLinks&animeID={$_GET['download_dialog']}&url={$block['dubbed']}&episode={$block['episode']}&type=dubbed');\" class='buttonLink fxBackground'>Download - Dubbed</a>";
			echo "</div>";
		}
		echo "<br /><hr /><br />";
	}
}
else {
	// default
	if (isSomething($prismDB["active"])) {
		foreach ($prismDB["active"] as $id => $null) {
			echo "<a href='app.Prism?reload_dialog={$id}' class='ajaxFriendly buttonLink fxBackground'>{$prismDB['anime'][$id]['name']}</a>";
		}
	}
	else {
		echo "<h3>No active anime found.</h3>";
	}
}

echo "
			</div>
		</div>
	</div>
";
?>
