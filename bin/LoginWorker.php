<?php
switch ($_GET["do"]) {
case "login":
	$username = strtolower(cleanANString($_POST["username"]));
	$password = $_POST["password"];
	if (is_dir($_sys["path_data"] . "user/{$username}")) {
		$dat = readDB($_sys["path_data"] . "user/{$username}/.account");
		if ($dat["password"] === sha1($_sys["salt"] . $password)) {
			session_start();
			if ($_POST["remember"]) {
				setcookie("username", $username, time() + 60 * 60 * 24 * 365);
				setcookie("key", $dat["sessionKey"], time() + 60 * 60 * 24 * 365);
			}
			$_SESSION["username"] = $username;
			$_SESSION["key"] = $dat["sessionKey"];
			$dat["prev_ip"] = $dat["ip"];
			$dat["ip"] = getIPAddr();
			writeDB(myDat(), $dat);
			if (isSomething($_POST["qs"])) {
				$b64d = base64_decode($_POST["qs"]);
				$b64d = strtr($b64d, array("app=" => "app.", "core=" => "core."));
				header("Location: {$b64d}");
			}
			else {
				header("Location: app.Home");
			}
		}
		else {
			if (isSomething($_POST["qs"])) {
				header("Location: app.Boot?username={$username}&error=2&qs={$_POST['qs']}");
			}
			else {
				header("Location: app.Boot?username={$username}&error=1");
			}
		}
	}
	else {
		if (isSomething($_POST["qs"])) {
			header("Location: app.Boot?username={$username}&error=2&qs={$_POST['qs']}");
		}
		else {
			header("Location: app.Boot?username={$username}&error=2");
		}
	}
break;
case "logout":
	setcookie("username", "", time() - 3600);
	setcookie("key", "", time() - 3600);
	if (isset($_SERVER['HTTP_COOKIE'])) {
		$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
		foreach($cookies as $cookie) {
			$parts = explode('=', $cookie);
			$name = trim($parts[0]);
			setcookie($name, '', time()-1000);
			setcookie($name, '', time()-1000, '/');
		}
	}
	session_start();
	session_destroy();
	header("Location: app.Boot?error=0");
break;
case "logout_all":
	session_start();
	enforceLogin();
	setcookie("username", "", 0);
	setcookie("key", "", 0);
	if (isSomething($_SESSION["username"])) {
		$dat = readDB(myDat());
		$dat["sessionKey"] = $_SESSION["username"] . sha1($_sys["salt"] . uniqid() . $_SESSION["username"]  . microtime());
		writeDB(myDat(), $dat);
	}
	session_destroy();
	header("Location: app.Boot?error=0");
break;
}
?>
