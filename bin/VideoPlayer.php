<?php
echo "
	<div id='content'>
		<div id='container'>
			<div class='block' style='position: relative; padding: 0px;'>
";
if (isset($_GET["video"])) {
	$ak = readDB($_sys["path_data"] . "db/.access-key");
	$data = $ak[$_GET["video"]];
	$fs = formatBytes($data["filesize"]);
	if (fextIsVideo($data["local"])) {
		echo "
			<div class='hAlignContainer' style='padding: 5px 10px;'>
				<h3>{$data['file']}</h3>
				<h2>{$fs}</h2>
				<br />
				<hr />
				<br />
				<div class='hAlign'>
					<div id='videoNormalButtons'>
						<a href='app.VideoPlayer?watch_video={$_GET['video']}' style='font-weight: bold; font-variant: normal;' class='modalFriendly buttonLink fxBackground'>Watch</a>
						<br />
						<span style='font-weight: bold; font-variant: normal;' class='buttonLink fxBackground' onclick=\"loadVideo('core.DataGate?direct={$_GET['video']}', '{$data['file']}'); closeModal();\">Preload</span>
						<small>Watch the video later without having to buffer.</small>
						<br />
						<br />
						<a href='/download-file/{$_GET['video']}/{$data['file']}' style='font-weight: bold; font-variant: normal;' class='buttonLink fxBackground'>Download</a>
						<small>Download the video to your computer.</small>
					</div>
					<div id='videoAbnormalButtons' style='display: none;'>
						<a href='app.VideoPlayer?watch_video={$_GET['video']}' style='font-weight: bold; font-variant: normal;' class='modalFriendly buttonLink fxBackground' id='watchButtonPreloaded'>Watch</a>
						<small>This video has already been preloaded!</small>
						<br />
						<br />
						<a href='/download-file/{$_GET['video']}/{$data['file']}' style='font-weight: bold; font-variant: normal;' class='buttonLink fxBackground'>Download</a>
						<small>Download the video to your computer.</small>
					</div>
					<br />
					<br />
				</div>
			</div>
			<script>
				$(function() {
					//console.log('exec: reloading cached files...');
					//reloadCachedFiles();
					console.log('checking to see if core.DataGate?direct={$_GET['video']} is preloaded...');
					if (videoExistsInQueue('core.DataGate?direct={$_GET['video']}')) {
						console.log('...yes it is!');
						$('#videoNormalButtons').css('display', 'none');
						$('#videoAbnormalButtons').css('display', 'block');
						var localURL = getLocalVideoBlob('core.DataGate?direct={$_GET['video']}');
						$('#watchButtonPreloaded').attr('href', 'app.VideoPlayer?raw_video=' + localURL);
					}
					else {
						console.log('...nope.');
					}
				});
			</script>
		";
	}
}
elseif (isset($_GET["watch_video"])) {
	$ak = readDB($_sys["path_data"] . "db/.access-key");
	$data = $ak[$_GET["watch_video"]];
	if (fextIsVideo($data["local"])) {
		echo "
			<video style='width: 100%;' controls>
				<source src='core.DataGate?direct={$_GET['watch_video']}' />
			</video>
		";
	}
}
elseif (isset($_GET["raw_video"])) {
	echo "
		<video style='width: 100%;' controls>
			<source src='{$_GET['raw_video']}' />
		</video>
	";
}
echo "
			</div>
		</div>
	</div>
";
?>