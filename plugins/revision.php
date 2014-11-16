<?php
function getRevisionStatus() {
	global $_sys;
	$file = ".git/ORIG_HEAD";
	if (!file_exists($file)) {
		return array("hash" => "00000000", "status" => "not available");
	}
	
	$local = readDB($_sys["path_data"] . "db/.revision");
	$fmt = filemtime($file);
	
	if (isset($local[$fmt])) {
		$local[$fmt]["num"] = count($local);
		return $local[$fmt];
	}
	
	$local[$fmt] = array(
		"hash" => substr(file_get_contents(".git/ORIG_HEAD"), 0, 8),
		"status" => file_get_contents($file),
		"date" => time(),
		"num" => count($local)
	);
	$local[$fmt]["hash"] = substr($local[$fmt]["hash"], 0, 8);
	writeDB($_sys["path_data"] . "db/.revision", $local);
	return $local[$fmt];
}
?>
