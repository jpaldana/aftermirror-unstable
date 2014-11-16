<?php
include("vars.php");
include("engine.php");

$default_app = "Boot";
$app = $default_app;
$noInterfaceFlag = false;

if (isset($_GET["app"])) $app = $_GET["app"];
if (isset($_GET["core"])) { $app = $_GET["core"]; $noInterfaceFlag = true; }
if (!file_exists($_sys["path_bin"] . $app . ".php")) $app = $default_app;

// for Anistream!
if ($app === "Anistream" && isset($_GET["u"]) && isset($_GET["ses"])) {
	session_start();
	$_SESSION["username"] = $_GET["u"];
	$_SESSION["key"] = $_GET["ses"];
}

if ($app === $default_app) {
	// recover session identifier
	if (!isset($_SESSION)) session_start();
	if (!isset($_SESSION["username"]) && isset($_COOKIE["username"]) && isset($_COOKIE["key"])) {
		$_SESSION["username"] = $_COOKIE["username"];
		$_SESSION["key"] = $_COOKIE["key"];
	}
}

// List apps that do not use the standard UI.
$noInterface = array("Boot", "DataGate", "Anistream");
// List apps that require user to be logged in.
$reqLogin = array(
	"Admin",
	"AnimeDownloader",
	"Anistream",
	"Cloud",
	"CloudDL",
	"Home",
	"Lounge",
	"Manager",
	"Messenger",
	"Prism",
	"PublicCloud",
	"Settings",
	"StatusPoster",
	"Upload"
);

if ($app == "Admin") {
	reqAccess(9);
}

// Darn it OAuth.
if ($app == "Boot" && (isset($_GET["error"]) && $_GET["error"] == "B00")) {
	include($_sys["path_bin"] . $app . ".php");
	die();
}

if (file_exists($_sys["path_data"] . "db/.site-config")) {
	$sysDB = readDB($_sys["path_data"] . "db/.site-config");
	if ($sysDB["access"] === "offline" && $app !== "Boot" && !isset($_GET["error"]) && $app !== "LoginWorker") {
		reqAccess(9, "app.Boot?error=F99");
	}
}


if (array_diff($reqLogin, array($app)) !== $reqLogin) {
	enforceLogin();
	$wpDB = readDB($_sys["path_data"] . "/db/.points");
}

if (array_diff($noInterface, array($app)) !== $noInterface) {
	$noInterfaceFlag = true;
}

if (!$noInterfaceFlag) {
	include($_sys["file_head"]);
}

include($_sys["path_bin"] . $app . ".php");

if (!$noInterfaceFlag) {
	include($_sys["file_foot"]);
}
?>
