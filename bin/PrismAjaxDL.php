<?php
set_time_limit(360);
ini_set('user_agent','Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.56 Safari/537.17');

$animeID = $_GET["animeID"];
$episode = $_GET["episode"];
$type = $_GET["type"];

if (isset($_GET["initializeDL"])) {
	$id = "ablock_" . uniqid();
	echo "
		<progress id='{$id}_progress' value='0' max='{$_GET['size']}' style='width: 100%;'></progress>
		<br />
		<span id='{$id}_status'>loading...</span>
		<script>
			var timer_{$id} = setInterval(\"prismRefreshData('{$id}', 'core.PrismAjaxDL?getStatus&animeID={$_GET['animeID']}&episode={$_GET['episode']}&type={$_GET['type']}&quality={$_GET['quality']}&filesize={$_GET['size']}');\", 2000);
			bgLoad('core.PrismAjaxDL?downloadURL&url={$_GET['initializeDL']}&animeID={$_GET['animeID']}&episode={$_GET['episode']}&type={$_GET['type']}&quality={$_GET['quality']}');
		</script>
	";
}
elseif (isset($_GET["downloadURL"])) {
	$link = $_GET["url"];
	$filename = "anime_{$_GET['animeID']}_{$_GET['episode']}_{$_GET['type']}_{$_GET['quality']}.mp4";
	file_put_contents($_sys["path_data"] . "temp/{$filename}.downloading", "0");
	system("wget -O \"{$_sys['path_data']}temp/{$filename}\" {$link}");
	unlink($_sys["path_data"] . "temp/{$filename}.downloading");
}
elseif (isset($_GET["getStatus"])) {
	clearstatcache();
	$filename = "anime_{$_GET['animeID']}_{$_GET['episode']}_{$_GET['type']}_{$_GET['quality']}.mp4";
	$current = filesize("{$_sys['path_data']}temp/{$filename}");
	$filesize = $_GET["filesize"];
	
	$start = filemtime($_sys["path_data"] . "temp/{$filename}.downloading");
	$cur = time();
	$dur = $cur - $start;
	$speed = $current / $dur; // byte/s
	$left = $filesize - $current;
	$tleft = round($left / $speed);
	
	if ($left > 0 && file_exists($_sys["path_data"] . "temp/{$filename}.downloading")) {
		$mb = formatBytes($speed) . "/s";
		$zleft = "";
		if ($tleft > 60) {
			$tleft = round($tleft / 60);
			$zleft = "{$tleft} minutes";
		}
		else {
			$zleft = "{$tleft} seconds";
		}
		
		echo json_encode(array("current" => $current, "formatCurrent" => formatBytes($current), "filesize" => formatBytes($filesize), "timeLeft" => $zleft, "speed" => $mb, "processing" => true));
		
		/*
		echo "Downloaded " . formatBytes($current) . " of " . formatBytes($filesize) . " (about {$zleft} @ {$mb})";
		echo "
			<script>
				$('#ajaxProgress_{$_GET['episode']}_{$_GET['type']}_{$_GET['quality']}').attr('value', {$current});
			</script>
		";
		*/
	}
	elseif ($current >= $filesize || !file_exists($_sys["path_data"] . "temp/{$filename}.downloading") || $left == 0) {
	
		echo json_encode(array("processing" => false));
		/*
		echo "
			<b>Done downloading</b>
			<script>
				clearTimeout('timer_{$_GET['episode']}_{$_GET['type']}_{$_GET['quality']}');
			</script>
		";
		*/
		
		rename("{$_sys['path_data']}temp/{$filename}", "{$_sys['path_data']}files/{$filename}");
	}
}
?>
