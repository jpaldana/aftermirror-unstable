<?php
echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
				<span class='buttonLink fxBackground' onclick=\"loadMusic('{$_GET['media']}');\">Play</span>
				<a href='/download-file/{$_GET['media']}' class='buttonLink fxBackground' onclick=\"miniPopup('Starting download...', 'text', 'downloadDialog');\">Download</a>
				<a href='app.ShareDialog?file={$_GET['media']}&type=music' class='modalFriendly buttonLink fxBackground'>Share</a>
			</div>
		</div>
	</div>
";
?>