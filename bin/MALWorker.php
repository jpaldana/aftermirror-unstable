<?php
	if (isset($_POST["mal_username"]) && isset($_POST["mal_password"])) {
		$malDBp = myDir() . ".myAnimeList";
		$malDB = readDB($malDBp);
		$malDB["username"] = $_POST["mal_username"];
		$malDB["password"] = $_POST["mal_password"];
		$malDB["sync"] = 0;
		$malDB["valid"] = "unknown";
		writeDB($malDBp, $malDB);
	}
	elseif (isset($_POST["syncAccount"])) {
		$malDBp = myDir() . ".myAnimeList";
		$malDB = readDB($malDBp);
		$malDB["sync"] = $_POST["syncAccount"];
		writeDB($malDBp, $malDB);
	}
	
	header("Location: app.Settings?mal_updated");
?>