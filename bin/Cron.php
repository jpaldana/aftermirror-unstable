<?php
set_time_limit(720);
ini_set('user_agent','Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.56 Safari/537.17');

$xmlContent = simplexml_load_file("http://feeds.feedburner.com/AnimeUltimaTV?format=xml");

$prismDB = readDB("{$_sys['path_data']}db/.prism");

//print_a($prismDB);

$upcount = 0;
foreach ($xmlContent->channel->item as $block) {
	$shouldWeDL = auEpisodeInPrism($block->link, $prismDB);
	if ($shouldWeDL) {
		$prismBlock = $prismDB["anime"][$shouldWeDL];
		if ($prismBlock["prism_active"]) {
			$epNum = substr($block->link, strripos($block->link, "-") + 1);
			if (substr($epNum, -1) == "/") $epNum = substr($epNum, 0, -1);
			$subbed = true;
			if (strc($block->link, "dubbed")) {
				$subbed = false;
			}
			echo "<b>Download: {$prismBlock['name']} (episode {$epNum})</b><br />";
			
			$type = $subbed ? "subbed" : "dubbed";
			
			$resp = file_get_contents("{$_sys['storage_root']}fetch-url.php?animeID={$shouldWeDL}&episode={$epNum}&type={$type}&url={$block->link}");
			
			//echo "getting resp: {$_sys['storage_root']}fetch-url.php?animeID={$shouldWeDL}&episode={$epNum}&type={$type}&url={$block->link}...<br/>";
			
			$resp = json_decode($resp, true);
			
			if (isSomething($resp["hq"])) {
				$dlURL = "{$_sys['storage_root']}prismDL.php?downloadURL&animeID={$shouldWeDL}&episode={$epNum}&type={$type}&quality=high&url={$resp['hq']['link']}";
				
				echo "dl: {$dlURL}...<br />";
				
				file_get_contents($dlURL);
				$upcount++;
			}
			if (isSomething($resp["lq"])) {
				$dlURL = "{$_sys['storage_root']}prismDL.php?downloadURL&animeID={$shouldWeDL}&episode={$epNum}&type={$type}&quality=low&url={$resp['lq']['link']}";
				
				echo "dl: {$dlURL}...<br />";
				
				file_get_contents($dlURL);
				$upcount++;
			}
			
		}
		else {
			echo "Not active: {$prismBlock['name']}, ignoring...<br />";
		}
	}
	else {
		echo "Ignoring '{$block->link}'...<br />";
	}
}

if ($upcount > 0) {
	$dir = json_decode(file_get_contents("{$_sys['storage_root']}do.php?getPrismDir"));
	$dir = array_reverse($dir);
	$oldPrismFiles = $prismDB["files"];
	$newPrism = array();
	$prismDB["files"] = array();
	foreach ($dir as $file) {
		$bn = basename($file);
		if (substr($bn, 0, 5) === "anime") {
			$part = explode("_", substr($bn, 0, -4)); // get rid of .mp4
			$prismDB["files"][$part[1]][$part[2]][$part[3]][$part[4]] = $file;
			if (!isset($oldPrismFiles[$part[1]][$part[2]][$part[3]][$part[4]])) {
				$newPrism[] = array(
					"anime" => $prismDB["anime"][$part[1]],
					"episode" => $part[2]
				);
			}
		}
	}
	$blob = array();
	$newMsg = "<h3>New episodes are available in <a href='app.Theatre' class='fxButton'>Theatre</a>!</h3><br/>";
	
	foreach ($newPrism as $newPart) {
		$uid = $newPart["anime"]["name"] . $newPart["episode"];
		if (!isset($blob[$uid])) {
			$blob[$uid] = true;
			$newMsg .= "<b>{$newPart['anime']['name']}</b> Episode {$newPart['episode']}<br/>";
		}
	}
	writeDB($_sys["path_data"] . "db/.prism", $prismDB);
	
	if (isSomething($blob) && count($blob) > 0) {
		$postGUID = time() . "_system" . uniqid();
		$post = array(
			"title" => "[Prism] New Episodes",
			"time" => time(),
			"origin" => "prism_bot",
			"origin_type" => "system",
			"banner_available" => false,
			"banner_image" => "",
			"content" => $newMsg,
			"postGUID" => $postGUID
		);
		$pubPosts = readDB("{$_sys['path_data']}feed/.public");
		$pubPosts[$postGUID] = $post;
		writeDB("{$_sys['path_data']}feed/.public", $pubPosts);
	}
}
?>
