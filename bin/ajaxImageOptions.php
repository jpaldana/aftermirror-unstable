<?php
if (isset($_GET["setWallpaper"]) && isset($_GET["image"])) {
	enforceLogin();
	$cssDB = readDB(myDir() . ".css");
	$cssDB["backgroundType"] = "image";
	$cssDB["background"] = $_GET["image"];
	writeDB(myDir() . ".css", $cssDB);
	$cf = new cloudflare_api("xjpaldana@gmail.com", "a6cbcc8f9e8da1737150bb030578d12fe3119");
	$cf->zone_file_purge("aftermirror.com", "https://aftermirror.com/profile/{$_SESSION['username']}/background.jpg");
	$rand = uniqid();
	echo "
		<div id='content'>
			<div id='container'>
				<div class='block'>
					Wallpaper set!
				</div>
				<script>
					changeBackgroundImage('profile/{$_SESSION['username']}/background.jpg?{$rand}');
				</script>
			</div>
		</div>
	";
}
else {
echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
				<span class='buttonLink fxBackground' onclick=\"miniPopup('core.ajaxImageOptions?image={$_GET['image']}&setWallpaper', 'url', 'wallpaperSetDialog');\">Set as Wallpaper</span>
				<a href='/download-file/{$_GET['image']}/download.jpg' class='buttonLink fxBackground' onclick=\"miniPopup('Starting download...', 'text', 'downloadDialog');\">Download</a>
				<a href='app.ShareDialog?file={$_GET['image']}&type=image' class='modalFriendly buttonLink fxBackground'>Share</a>
				<span class='buttonLink fxBackground'>Delete</span>
			</div>
		</div>
	</div>
";
}
?>