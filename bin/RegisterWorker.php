<?php
if (isSomething(cleanANString($_POST["username"])) && isSomething($_POST["password"]) && isSomething($_POST["name"]) && isSomething($_POST["email"])) {
	$sysDB = readDB($_sys["path_data"] . "db/.site-config");
	if ($sysDB["regAccess"] === "offline") {
		header("Location: app.Boot?error=F10"); // failed to register -- registration offline
		die();
	}
	$reservedUsernames = array(
		"system",
		"guest"
	);
	$username = cleanANString(strtolower($_POST["username"]));
	if (array_diff($reservedUsernames, array($username)) !== $reservedUsernames) {
		header("Location: app.Boot?error=101"); // failed to register -- reserved username
		die();
	}
	if (is_dir($_sys["path_data"] . "user/{$username}")) {
		header("Location: app.Boot?error=102"); // failed to register -- user exists
		die();
	}
	$pass = sha1($_sys["salt"] . $_POST["password"]);
	$name = $_POST["name"];
	$email = $_POST["email"];
	$myDir = $_sys["path_data"] . "user/{$username}/";
	if (!mkdir($myDir)) {
		header("Location: app.Boot?error=103"); // failed to register -- cannot make user directory (server-side error?)
		die();
	}
	writeDB("{$myDir}.account", array(
		"username" => $username,
		"password" => $pass,
		"name" => $name,
		"email" => $email,
		"registrationDate" => time(),
		"ip" => getIPAddr(),
		"access" => 1,
		"sessionKey" => $username . sha1($_sys["salt"] . uniqid() . $username . microtime())
	));
	copy("{$myDir}.account", "{$myDir}.account.backup");
	header("Location: app.boot?error=99"); // registration success!
	die();
}
else {
	header("Location: app.Boot?error=100"); // failed to register -- incomplete field(s)
	die();
}
?>
