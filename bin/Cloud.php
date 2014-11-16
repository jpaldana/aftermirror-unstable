<?php
echo "
	<div id='content'>
		<div id='container'>
";

if (file_exists(myDir() . ".uploads")) {
	$upl = readDB(myDir() . ".uploads");
	$ak = readDB($_sys["path_data"] . "db/.access-key");
	
	$files = array();
	foreach ($upl["files"] as $upload) {
		if (fextIsImage($upload["file"])) $upload["type"] = "image";
		elseif (fextIsVideo($upload["file"])) $upload["type"] = "video";
		elseif (fextIsMusic($upload["file"])) $upload["type"] = "music";
		elseif (fextIsText($upload["file"])) $upload["type"] = "text";
		else $upload["type"] = "other";
		$files[] = $upload;
	}
	
	$totalFiles = count($upl["files"]);
	$cloudSize = $upl["size"];
	$cloudHumanSize = formatBytes($upl["size"]);
}
else {
	$totalFiles = 0;
	$cloudSize = 0;
	$cloudHumanSize = "0 Bytes";
}
echo "
			<div class='block'>
				<h1 class='center'><span style='color: gray;'>my</span>|<span class='fxSlide'>cloud</span></h1>
				<h2 class='center' style='font-size: 14px; color: gray;'>consists of {$totalFiles} file(s) using {$cloudHumanSize} of space</h2>
				<br />
				<hr />
				<br />
				<div class='hAlignContainer'>
					<div class='grid hAlign'>
";
if ($totalFiles > 0) {
	$listStyle = "text";
	if (isset($_GET["style"])) $listStyle = $_GET["style"];
	
	echo "
		<a href='app.Upload' class='ajaxFriendly fxButton'>upload</a> <a href='app.Cloud?style=prism' class='ajaxFriendly fxButton btnFor_prism'>anime</a> <a href='app.Cloud?style=text' class='ajaxFriendly fxButton btnFor_text'>text</a> <a href='app.Cloud?style=grid' class='ajaxFriendly fxButton btnFor_grid'>grid</a> <a href='app.PublicCloud' class='ajaxFriendly fxButton'>public files</a> <a href='app.Manager' class='ajaxFriendly fxButton'>manage</a>
		<script>
			$(function() {
				$('.btnFor_{$listStyle}').css('font-weight', 'bold');
			});
		</script>
		<br />
		<br />
	";
	
	switch ($listStyle) {
		case "grid":
			foreach ($files as $file) {
				if ($file["type"] == "image") {
					echo "<a href='app.ImageViewer?image={$file['accessKey']}' class='modalFriendly'><div class='fxBackground cell' psize='cell1x1' style='background-image: url(core.DataGate?image_thumbnail={$file['accessKey']});'><label class='noFlow'>{$file['file']}</label></div></a>";
				}
				elseif ($file["type"] == "video") {
					echo "<a href='app.VideoPlayer?video={$file['accessKey']}' class='modalFriendly'><div class='fxBackground cell' psize='cell2x1' style='background-image: url(core.DataGate?video_thumbnail={$file['accessKey']});'><label class='noFlow playIcon'>{$file['file']}</label></div></a>";
				}
				elseif ($file["type"] == "music") {
					$imageURL = "{$_sys['path_cdn']}img/default_album_art.png"; // generic album art
					if (!file_exists($_sys["path_data"] . "cache/mediaInfo_{$file['accessKey']}")) {
						$data = $ak[$file["accessKey"]];
						genMediaInfo($data, $file["accessKey"]);
					}
					$mediaInfo = readDB($_sys["path_data"] . "cache/mediaInfo_{$file['accessKey']}");
					if ($mediaInfo["albumArt"]) {
						$imageURL = "core.DataGate?music_thumbnail={$file['accessKey']}";
					}
					
					if (isset($mediaInfo["artist"]) && isset($mediaInfo["title"])) {
						$fileLabel = $mediaInfo["artist"] . " - " . $mediaInfo["title"];
					}
					elseif (isset($mediaInfo["title"])) {
						$fileLabel = $mediaInfo["title"];
					}
					else {
						$fileLabel = $file["file"];
					}
					echo "<a href='#' onclick=\"loadMusic('{$file['accessKey']}');\" class='preventDefault'><div class='fxBackground cell' psize='cell1x1' style='background-image: url({$imageURL});'><label class='noFlow playIcon' id='cloudLabel_{$file['accessKey']}'>{$fileLabel}</label></div></a>";
				}
			}
		break;
		case "prism":
			$prismDB = readDB($_sys["path_data"] . "db/.prism");
			echo "<div style='text-align: left;'>";
			foreach ($prismDB["anime"] as $id => $anime) {
				if (!$anime["prism_active"]) continue;
				echo "<b>{$anime['name']} ({$anime['type']} - {$anime['episodes']} episodes, {$anime['status']})</b><br/>";
				$altNames = "English: " . $anime["english"] . " | Synonyms: " . $anime["synonym"];
				echo "<small>{$altNames}</small><br/>";
				
				echo "<p style='color: gray; font-size: 12px;'>{$anime['synopsis']}</p><br/>";
				if (isSomething($prismDB["files"][$id])) {
					
					$src = array_slice($prismDB["files"][$id], 0, 5, true);
					foreach ($src as $episode => $files) {
						echo "<b style='font-size: 18px;'>Episode {$episode}</b><br/>";
						foreach ($files as $type => $tree) {
							echo "<b>{$type}:</b> ";
							foreach ($tree as $quality => $local) {
								echo "<a href='app.PrismStream?animeID={$id}&episode={$episode}&type={$type}&quality={$quality}' class='ajaxFriendly'>{$quality} quality</a> ";
							}
							echo "<br/>";
						}
					}
				}
				else {
					echo "<b>No episodes available :(</b><br/>";
				}
				echo "<br/><hr/><br/>";
			}
			echo "</div>";
		break;
		case "text":
		default:
			$files = array_reverse($files);
			$fileSorted = array();
			foreach ($files as $file) {
				$fileSorted[$file["type"]][] = $file;
			}

			echo "<div style='text-align: left;'>";
			foreach ($fileSorted as $fileType => $filesOfType) {
				echo "<h3>{$fileType}</h3>";
				$bDates = array();
				foreach ($filesOfType as $file) {
					$fileBlob = $ak[$file["accessKey"]];
					$date = date("F j, Y", $fileBlob["uploadTime"]);
					if (!isset($bDates[$date])) {
						echo "<h2 style='text-decoration: underline;'>{$date}</h2>";	
						$bDates[$date] = true;
					}
					switch($file["type"]) {
						case "image":
							echo "<a href='app.ImageViewer?image={$file['accessKey']}' id='fileID_{$file['accessKey']}' class='modalFriendly buttonLink fxBackground'>{$file['file']}</a>";
						break;
						case "video":
							echo "<a href='app.VideoPlayer?video={$file['accessKey']}' id='fileID_{$file['accessKey']}' class='modalFriendly buttonLink fxBackground'>{$file['file']}</a>";
						break;
						case "music":
							//echo "<a href='core.DataGate?direct={$file['accessKey']}' id='fileID_{$file['accessKey']}' class='preventDefault buttonLink fxBackground' onclick=\"loadMusic('{$file['accessKey']}');\">{$file['file']}</a>";
							echo "<a href='core.DataGate?direct={$file['accessKey']}' id='fileID_{$file['accessKey']}' class='preventDefault buttonLink fxBackground' onclick=\"miniWindow('app.ajaxMediaPlayerDialog?media={$file['accessKey']}', 'mediaDialog_{$file['accessKey']}');\">{$file['file']}</a>";
						break;
						default:
							echo "<a href='core.DataGate?direct={$file['accessKey']}' id='fileID_{$file['accessKey']}' class='buttonLink fxBackground' target='_blank'>{$file['file']}</a>";
						break;
					}
				}	
			}
			echo "
				</div>
				<script>
					$(function() {
						setTimeout('notifyPreloaded()', 2200);
					});
					function notifyPreloaded() {
						var i;
						for (i = 0; i < preloadQueue.length; i++) {
							var dat = preloadQueue[i];
							$('#fileID_' + dat['dat_hash']).prepend('<b>[preloaded]</b> ');
						}
					}
				</script>
			";
		break;
	}
}
else {
	echo "<p>No files here :(</p>";
}
echo "
					<script>
						fixGrid();
						$(window).on('resize', function() { fixGrid(); });
					</script>
					</div>
				</div>
			</div>
";

echo "
		</div>
	</div>
";
?>
