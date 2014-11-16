<?php

function stringWithinArray($str, $arr) {
}

function auEpisodeInPrism($url, $prism) {
	// $url = http://www.animeultima.tv/gundam-g-no-reconguista-episode-7/
	$auNameTag = substr($url, 0, strripos($url, "-episode-"));
	// $auNameTag = http://www.animeultima.tv/gundam-g-no-reconguista
	$auNameTag = substr($auNameTag, strripos($auNameTag, "/") + 1);
	// $auNameTag = gundam-g-no-reconguista
	
	if (!isSomething($prism["anime"])) return false;
	
	foreach ($prism["anime"] as $id => $block) {
		if (strc($block["epURL"], $auNameTag)) {
			// match?
			return $id;
		}
	}
	
	return false;
}

?>
