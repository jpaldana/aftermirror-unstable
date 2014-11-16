<?php
$valid = readDB($_sys["path_data"] . "db/.valid-id");
$pwh = substr(sha1(uniqid() . $_sys["salt"]), 0, 5);
$valid[$_SESSION["username"]] = array("pwh" => $pwh, "time" => time());
writeDB($_sys["path_data"] . "db/.valid-id", $valid);

echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
				<h1 class='center'><span style='color: gray;'>after</span>|<span class='fxSlide'>lounge</span></h1>
				<h2 class='center' style='font-size: 14px; color: gray;'>feeling stressed? take a break!</h2>
				<br />
				<hr />
				<br />
				<h3>Anime</h3>
				<a href='app.Theatre' class='buttonLink fxBackground'><b>Theatre (Unified)</b><br/>Let's do this for real.</a>
";
$myDat = readDB(myDat());
if ($myDat["access"] > 3) {
	echo "
				<a href='app.AnimeDownloader' class='ajaxFriendly buttonLink fxBackground'><b>Anime Downloader</b><br/>Download episodes straight onto after|mirror.</a>
	";
}
echo "
				<h3>Games</h3>
					<a href='app.Gamepad' class='ajaxFriendly buttonLink fxBackground'><b>Game Pad</b><br/>Browse the collection of <i>super awesome</i> games!</a>
				<h3>Utilities</h3>
				<a href='app.Sendify' class='ajaxFriendly buttonLink fxBackground'><b>sendify.me</b><br/>Shorten links using after|mirror's own URL shortening service.</a>
			</div>
		</div>
	</div>
";
?>
