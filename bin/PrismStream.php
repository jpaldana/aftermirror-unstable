<?php
$animeID = $_GET["animeID"];
$episode = $_GET["episode"];
$type = $_GET["type"]; // subbed/dubbed
$quality = $_GET["quality"]; // low/high
$prismDB = readDB($_sys["path_data"] . "db/.prism");

echo "
	<div id='content'>
		<div id='container'>
			<div class='block center'>
";

$vmp4 = $prismDB["files"][$animeID][$episode][$type][$quality];
if (isSomething($vmp4)) {
	$anime = $prismDB["anime"][$animeID];
	echo "
		<h3>{$anime['name']}</h3>
		<b>Episode: 
		<select id='episodePicker' class='noSelectStyle'>
		<option value='{$episode}'>{$episode} (current)</option>
	";
	for ($i = 1; $i <= $anime["episodes"]; $i++) {
		if (isSomething($prismDB["files"][$animeID][$i][$type][$quality])) {
			echo "<option value='{$i}'>{$i}</option>";
		}
	}
	$vmp4 = basename($vmp4); // pre-horizon fix.
	echo "
		</select> / {$anime['episodes']}</b><br/><br/>
		<video controls style='width: 100%;'><source src='{$_sys['prism_root']}{$vmp4}' type='video/mp4' /></video>
		
	";
}

echo "
			</div>
		</div>
	</div>
";
?>
