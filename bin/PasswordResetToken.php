<?php
	$recovery = "{$_sys['path_data']}user/{$_GET['user']}/.recovery";
	$account = "{$_sys['path_data']}user/{$_GET['user']}/.account";
	if (file_exists($recovery)) {
		$userRecDB = readDB($recovery);
		if (time() - $userRecDB["time"] > 60 * 60) {
			header("Location: app.Boot?error=R90");
			die();
		}
		if ($_GET["key"] !== $userRecDB["key"]) {
			header("Location: app.Boot?error=R91");
			die();
		}
		
		$rev = getRevisionStatus();
		$revDate = date("m.d", $rev["date"]);

		echo "
		<html>
			<head>
				<title>after|mirror: password reset</title>
				<link rel='shortcut icon' href='{$_sys['path_cdn']}favicon.ico' />
				<link rel='stylesheet' href='{$_sys['path_cdn']}css/style.css' />
				<link rel='stylesheet' href='{$_sys['path_cdn']}css/style-boot.css' />
				<meta name='keywords' content='aftermirror, after, mirror, after|mirror' />
				<meta name='description' content='You found love in a hopeless place.' />
				<meta name='viewport' content='minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no'>
				<meta http-equiv='cleartype' content='on'>
			</head>
			<body>
				<div class='backgroundScroller' style='background-image: url({$_sys['path_cdn']}img/scroll-bg/clouds.png);'>
				</div>
				<div class='dialog vAlign loginDialog'>
					<div id='mainDialog' class='boxDialog'>
						<h1 class='title'>after|mirror</h1>
						<h2 class='subtitle'>r{$rev['hash']}.{$revDate} #{$rev['num']}</h2>
						<br />
						<hr />
						<br />
		";
		
		if (isset($_POST["password"])) {
			$readDB = readDB($account);
			$readDB["password"] = sha1($_sys["salt"] . $_POST["password"]);
			writeDB($account, $readDB);
			unlink($recovery);
			echo "<p>Password set. <a href='app.Boot'>Click me to return.</a></p>";
		}
		else {
			echo "
				<form action='core.PasswordResetToken?user={$_GET['user']}&key={$_GET['key']}' method='post'>
					<h3>Enter a new password for '{$_GET['user']}'.</h3>
					<input type='password' name='password' />
					<br />
					<input type='submit' value='Submit' />
				</form>
			";
		}
		
		echo "
					</div>
				</div>
				<div class='popup alert'>after|mirror: Password Reset</div>
			</body>
		</html>
		";
		
	}
	else {
		header("Location: app.Boot?error=R92");
	}
?>
