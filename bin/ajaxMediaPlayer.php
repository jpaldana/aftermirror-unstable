<?php
// only accessible through ajax (js)

$mediaInfo = readDB($_sys["path_data"] . "cache/mediaInfo_{$_GET['playing']}");
$miTitle = htmlentities($mediaInfo["title"]);
$miArtist = htmlentities($mediaInfo["artist"]);
echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
				<div class='center'>
					<h1>{$miTitle}</h1>
					<h2>{$miArtist}</h2>
				</div>
				<br />
				<div class='hAlignContainer'>
					<div class='hAlign' style='width: 80%;'>
						<span id='auxCurrentText' style='float: left;'>00:00:00</span>
						<span id='auxCurrentDuration' style='float: right;'>00:00:00</span>
						<input id='auxSeekerBar' type='range' min='0' max='1' step='1' value='0' style='width: 100%;' disabled />
					</div>
				</div>
				<br />
				<div class='column_container'>
					<div class='column_one'>
						<p><img src='{$_sys['path_cdn']}img/icons/prev.png' style='width: 64px;' /><br/>skip</p>
					</div>
					<div class='column_one'>
						<p class='fxBackground buttonLink' onclick='playPausePlayer();'><img src='{$_sys['path_cdn']}img/icons/play.png' style='width: 64px;' id='auxPlayPauseButtonImage' /><br/><span id='auxPlayPauseButtonText'>play/pause</p>
					</div>
					<div class='column_one'>
						<p><img src='{$_sys['path_cdn']}img/icons/skip.png' style='width: 64px;' /><br/>next</p>
					</div>
				</div>
				<script>
					$(function() {
						if (!mediaPlayer.paused) {
							$('#auxPlayPauseButtonImage').attr('src', 'cdn/img/icons/pause.png');
							$('#auxPlayPauseButtonText').text('pause');
						}
						else {
							$('#auxPlayPauseButtonImage').attr('src', 'cdn/img/icons/play.png');
							$('#auxPlayPauseButtonText').text('play');
						}
					});
				</script>
			</div>
		</div>
	</div>
";
?>