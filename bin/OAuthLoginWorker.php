<?php
$state = md5($_sys["salt"] . uniqid());
$client_id = "257942873480-s8m68uf07fotunn51cpcsheoo7r35qss.apps.googleusercontent.com";
$client_secret = "gIRh4K_QzEhUI2blkCr69GwD";
$redirect_uri = "https://aftermirror.com/core.OAuthLoginWorker";

if(isset($_GET['code'])) {
    // try to get an access token
    $code = $_GET['code'];
    $url = 'https://accounts.google.com/o/oauth2/token';
    $params = array(
        "code" => $code,
        "client_id" => $client_id,
        "client_secret" => $client_secret,
        "redirect_uri" => $redirect_uri,
        "grant_type" => "authorization_code"
    );
 
    $opts = array("http" => array(
		"method" => "POST",
		"header" => "Content-Type: application/x-www-form-urlencoded",
		"content" => http_build_query($params)
    ));
    $context = stream_context_create($opts);
    $responseObj = json_decode(file_get_contents($url, false, $context));
    
    $accessToken = $responseObj->access_token;
	header("Location: {$redirect_uri}?access_token={$accessToken}");
}
elseif (isset($_GET["access_token"])) {
    $accessToken = $_GET["access_token"];
	$userDetails = file_get_contents('https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $accessToken);
	$userData = json_decode($userDetails, true);
	//print_a($userData);
	$oauthDB = readDB($_sys["path_data"] . "db/.oauth");
	if (isset($oauthDB[$userData["id"]])) {
		$user = $oauthDB[$userData["id"]];
		$userDB = readDB($_sys["path_data"] . "user/{$user}/.account");
		session_start();
		$_SESSION["username"] = $user;
		$_SESSION["key"] = $userDB["sessionKey"];
		header("Location: app.Home");
	}
	else {
		// Not yet registered (or at least, tied in to an account)
		
		$rev = getRevisionStatus();
		$revDate = date("m.d", $rev["date"]);
		
		echo "
		<html>
			<head>
				<title>after|mirror: oauth</title>
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
						<h3>New OAuth ID</h3>
						<br />
		";
		if (isset($_GET["QuickRegister"])) {
			if (isset($_POST["username"]) && isSomething($_POST["username"])) {
				
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
					"access" => 2, // OAuth automatically confirms email.
					"sessionKey" => $username . sha1($_sys["salt"] . uniqid() . $username . microtime())
				));
				copy("{$myDir}.account", "{$myDir}.account.backup");
								
				$oauthDB[$userData["id"]] = $username;
				writeDB($_sys["path_data"] . "db/.oauth", $oauthDB);
				
				file_put_contents("{$myDir}.profile_picture", file_get_contents($userData["picture"]));
				
				header("Location: app.Boot?error=91"); // registration success!
				die();
				
			}
			echo "
				<form action='core.OAuthLoginWorker?access_token={$accessToken}&QuickRegister' method='post'>
					<label for='usernameReg' class='textboxLabel'>username</label>
					<input type='text' id='usernameReg' name='username' value='' autocomplete='off' class='fxBackground' />
					<label for='passwordReg' class='textboxLabel'>password</label>
					<input type='password' id='passwordReg' name='password' value='' class='fxBackground' />
					<label for='nameReg' class='textboxLabel'>name (full name, nickname, or whatever)</label>
					<input type='text' id='nameReg' name='name' value='{$userData['name']}' class='fxBackground' />
					<label for='emailReg' class='textboxLabel'>e-mail (for password retrieval)</label>
					<input type='text' id='emailReg' name='email' value='{$userData['email']}' class='fxBackground' disabled />
					<div class='hAlignContainer'>
						<div class='hAlign'>
							<input type='submit' value='register' />
						</div>
					</div>
				</form>
			";
		}
		elseif (isset($_GET["QuickLink"])) {
			if (isset($_POST["username"]) && isset($_POST["password"])) {
				$user = strtolower(cleanANString($_POST["username"]));
				$reqAcc = readDB($_sys["path_data"] . "user/{$user}/.account");
				if ($reqAcc["password"] === sha1($_sys["salt"] . $_POST["password"])) {
					$oauthDB[$userData["id"]] = $user;
					writeDB($_sys["path_data"] . "db/.oauth", $oauthDB);
					
					$userDB = readDB($_sys["path_data"] . "user/{$user}/.account");
					session_start();
					$_SESSION["username"] = $user;
					$_SESSION["key"] = $userDB["sessionKey"];
					header("Location: app.Home");
					die();
				}
				else {
					header("Location: core.OAuthLoginWorker?access_token={$accessToken}&QuickLink&errorLogin");
					die();
				}
			}
			
			if (isset($_GET["errorLogin"])) {
				echo "<h3>Invalid Login -- please try again.</h3>";
			}
			echo "
				<form action='core.OAuthLoginWorker?access_token={$accessToken}&QuickLink' method='post'>
					<label for='username' class='textboxLabel'>Existing Username</label>
					<input type='text' name='username' id='username' />
					<label for='password' class='textboxLabel'>Password</label>
					<input type='password' name='password' id='password' />
					<input type='submit' value='Link Account' class='fullSize' />
				</form>
			";
		}
		else {
			echo "
				<div style='height: 100px; margin-left: 100px; position: relative;'>
					<img src='{$userData['picture']}' style='position: absolute; left: -90px; top: 0px; width: 80px;' />
					<b>Hello, {$userData['name']}</b><br />
					<p>It seems that this is the first time you have logged in using this OAuth.<br/>Please choose an option:</p>
				</div>
				<br />
				<a href='core.OAuthLoginWorker?access_token={$accessToken}&QuickRegister' class='fxButton'>Register New after|mirror Account</a>
				<br />
				<br />
				<a href='core.OAuthLoginWorker?access_token={$accessToken}&QuickLink' class='fxButton'>Link OAuth to an existing after|mirror account.</a>
			";
		}
		echo "
					</div>
				</div>
				<div class='popup alert'>after|mirror: Register New OAuth</div>
			</body>
		</html>
		";
		
		/*
		$url = "https://accounts.google.com/o/oauth2/revoke?token={$accessToken}";
		$opts = array("http" => array(
			"method" => "POST",
			"header" => "Content-Type: application/x-www-form-urlencoded"
		));
		$context = stream_context_create($opts);
		file_get_contents($url, false, $context);
		header("Location: app.Boot?logout-oauth");
		*/
	}
}
else {
	// This branch shouldn't be triggered anymore...
	$url = "https://accounts.google.com/o/oauth2/auth";
	 
	$params = array(
		"response_type" => "code",
		"client_id" => $client_id,
		"redirect_uri" => $redirect_uri,
		"scope" => "https://www.googleapis.com/auth/userinfo.email"
		);
	 
	$request_to = $url . '?' . http_build_query($params);
	 
	header("Location: " . $request_to);
}
?>
