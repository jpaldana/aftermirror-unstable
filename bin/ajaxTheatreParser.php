<?php
if (isset($_GET["animeID"]) && isset($_GET["episode"])) {
	$prismDB = readDB($_sys["path_data"] . "db/.prism");
	
	//print_a($prismDB["files"]);
	if (!isset($prismDB["files"][$_GET["animeID"]][$_GET["episode"]])) {
		echo "false";
		return;
	}
	$branch = $prismDB["files"][$_GET["animeID"]][$_GET["episode"]];
	
	$targetType = $_GET["prefT"];
	$targetQuality = $_GET["prefQ"];
	
	if ($targetType == "dubbed" && !isset($branch["dubbed"])) {
		$targetType = "subbed";
	}
	if (isset($branch[$targetType][$targetQuality])) {
		echo $_sys["prism_root"] . basename($branch[$targetType][$targetQuality]);
	}
	else {
		echo $_sys["prism_root"] . basename(array_pop($branch[$targetType]));
	}
}
?>
