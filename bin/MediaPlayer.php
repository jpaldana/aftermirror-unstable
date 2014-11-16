<?php
echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
";
if (isset($_GET["music"])) {
	$mediaInfo = readDB($_sys["path_data"] . "cache/mediaInfo_{$_GET['music']}");
	$miTitle = htmlentities($mediaInfo["title"]);
	$miArtist = htmlentities($mediaInfo["artist"]);
	echo "
				<script>
					spawnMediaPlayer();
					$('#sbMediaPlayerTitle').text(\"{$miTitle}\");
					$('#sbMediaPlayerArtist').text(\"{$miArtist}\");
				</script>
	";
}
echo "
			</div>
		</div>
	</div>
";
?>