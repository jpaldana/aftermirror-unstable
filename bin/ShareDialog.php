<?php
echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
";

$file = $_GET["file"];
$type = $_GET["type"];

switch ($type) {
	case "image":
		$thumb = "core.DataGate?image_thumbnail={$file}";
	break;
	default:
		$thumb = "{$_sys['path_cdn']}img/default_file.png";
	break;
}
echo "
	<div class='center'>
		<h3>Share with...</h3>
	</div>
	<br />
	<hr />
	<br />
	<div class='column_container'>
		<div class='column_one center'>
			<div style='width: 100%; height: 140px; box-shadow: 0px 0px 4px black; background-size: cover; background-image: url({$thumb}); background-repeat: no-repeat; background-position: center center;' class='vAlign'></div>
		</div>
		<div class='column_two' style='text-align: left; padding: 10px;'>
		<form action='core.ShareWorker' method='post'>
			<input type='hidden' name='file' value='{$file}' />
";
$a = getUsers();
$a = array_diff($a, array($_SESSION["username"]));
foreach ($a as $user) {
	$theirFiles = $_sys["path_data"] . "user/{$user}/.uploads";
	$fileExists = false;
	if (file_exists($theirFiles)) {
		$fileDB = readDB($theirFiles);
		if (isSomething($fileDB["files"])) {
			foreach ($fileDB["files"] as $cloudFile) {
				if ($cloudFile["accessKey"] == $file) {
					$fileExists = true;
					break;
				}
			}
		}
	}
	if (!$fileExists) {
		echo "<input type='checkbox' name='share[]' value='{$user}' /> {$user}<br />";
	}
}

echo "
			<br />
			<input type='submit' value='Share' class='fullSize' />
			</form>
		</div>
	</div>
";



echo "
			</div>
		</div>
	</div>
";
?>