<?php
function enforceLogin() {
	if (!isset($_SESSION)) session_start();
	if (!isset($_SESSION["username"]) && isset($_COOKIE["username"]) && isset($_COOKIE["key"])) {
		$_SESSION["username"] = $_COOKIE["username"];
		$_SESSION["key"] = $_COOKIE["key"];
	}
	if (!isSomething($_SESSION["username"])) {
		$qs = base64_encode($_SERVER["QUERY_STRING"]);
		header("Location: app.Boot?error=4&qs={$qs}");
		die();
	}
	verifyLogin();
}
function verifyLogin() {
	if (!isset($_SESSION)) session_start();
	global $_sys;
	$dat = readDB($_sys["path_data"] . "user/{$_SESSION['username']}/.account");
	if ($dat["access"] < 0 && !isset($_GET["error"])) {
		header("Location: app.Boot?error=B00");
		die();
	}
	if ($dat["sessionKey"] !== $_SESSION["key"]) {
		setcookie("username", "", time() - 3600);
		setcookie("key", "", time() - 3600);
		session_destroy();
		$qs = base64_encode($_SERVER["QUERY_STRING"]);
		header("Location: app.Boot?error=3&qs={$qs}");
		die();
	}
}
function reqAccess($level, $redirect = false) {
	if (!isset($_SESSION)) session_start();
	global $_sys;
	$dat = readDB($_sys["path_data"] . "user/{$_SESSION['username']}/.account");
	if ($dat["access"] < $level) {
		if ($redirect) {
			header("Location: {$redirect}");
		}
		else {
			header("Location: app.Home?noAccess");
		}
		die();
	}
}
function lockLogin() {
	if (!isset($_SESSION)) session_start();
	session_write_close();
}
function myDir() {
	global $_sys;
	if (!isset($_SESSION)) enforceLogin();
	return $_sys["path_data"] . "user/{$_SESSION['username']}/";
}
function myDat() {
	return myDir() . ".account";
}

/* OAuth */

?>
