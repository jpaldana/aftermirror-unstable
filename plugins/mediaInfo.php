<?php
function genMediaInfo($data, $output) {
	global $_sys;
	include_once($_sys["path_mods"] . "/getid3/getid3.php");

	$getID3 = new getID3;
	$fileInfo = $getID3->analyze($data["local"]);
	getid3_lib::CopyTagsToComments($fileInfo);
	$mediaInfo = array(
		"bitrate" => $fileInfo["audio"]["bitrate"],
		"samplerate" => $fileInfo["audio"]["sample_rate"],
		"artist" => implode(", ", $fileInfo["tags"]["id3v2"]["artist"]),
		"title" => implode(", ", $fileInfo["tags"]["id3v2"]["title"]),
		"album" => implode(", ", $fileInfo["tags"]["id3v2"]["album"]),
		"genre" => implode(", ", $fileInfo["tags"]["id3v2"]["genre"]),
		"year" => implode(", ", $fileInfo["tags"]["id3v2"]["year"]),
		"albumArt" => false,
		"albumMimeType" => ""
	);
	if (!isSomething($mediaInfo["artist"])) {
		$mediaInfo["artist"] = "no artist";
	}
	if (!isSomething($mediaInfo["title"])) {
		$mediaInfo["title"] = $data["file"];
	}
	if (isSomething($fileInfo["comments"]["picture"][0]["data"])) {
		$mediaInfo["albumMimeType"] = $fileInfo["comments"]["picture"][0]["image_mime"];
		$mediaInfo["albumArt"] = true;
		file_put_contents($_sys["path_data"] . "/cache/albumArt_{$output}", $fileInfo["comments"]["picture"][0]["data"]);
	}
	writeDB($_sys["path_data"] . "/cache/mediaInfo_{$output}", $mediaInfo);
}
?>