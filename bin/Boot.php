<?php
if (isset($_SESSION["username"]) && !isset($_GET["error"])) {
	echo "<script>window.location = 'app.Home';</script>";
	die();
}

$rev = getRevisionStatus();
$revDate = date("m.d", $rev["date"]);

if (isset($_GET["error"]) && $_GET["error"] === "B00") {
	echo "
	<html>
		<head>
			<title>after|mirror</title>
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
					<form action='#' method='post'>
						<input type='hidden' name='qs' value='{$_GET['qs']}' />
						<label for='username' class='textboxLabel'>username</label>
						<input type='text' id='username' name='username' value='{$_GET['username']}' autocomplete='off' class='fxBackground' disabled />
						<label for='password' class='textboxLabel'>password</label>
						<input type='password' id='password' name='password' value='' class='fxBackground' disabled />
						<input type='checkbox' id='remember' name='remember' disabled /> <label for='remember' class='checkboxLabel'>remember me</label>
						<br />
						<br />
						<div class='hAlignContainer'>
							<div class='hAlign'>
								<input type='submit' value='login' disabled />
								<input type='button' value='register / forgot password' disabled /> 
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class='popup error'>Your account is banned.</div>
		</body>
	</html>
	";
}
else {
	echo "
	<html>
		<head>
			<title>after|mirror</title>
			<link rel='shortcut icon' href='{$_sys['path_cdn']}favicon.ico' />
			<link rel='stylesheet' href='{$_sys['path_cdn']}css/style.css' />
			<link rel='stylesheet' href='{$_sys['path_cdn']}css/style-boot.css' />
			<script type='text/javascript' src='{$_sys['path_cdn']}js/jquery.js'></script>
			<meta name='keywords' content='aftermirror, after, mirror, after|mirror' />
			<meta name='description' content='You found love in a hopeless place.' />
			<meta name='viewport' content='minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no'>
			<meta http-equiv='cleartype' content='on'>
			<script src='https://apis.google.com/js/client:platform.js'></script>
			<script src='https://apis.google.com/js/client:plusone.js'></script>
			<script>
";
if (isset($_GET["logout-oauth"]) || isset($_GET["error"])) {
	echo "
		gapi.auth.signOut();
		function signinCallback(authResult) {
		  disconnectUser(authResult['access_token']);
		}
		function disconnectUser(access_token) {
		  var revokeUrl = 'https://accounts.google.com/o/oauth2/revoke?token=' +
			  access_token;

		  // Perform an asynchronous GET request.
		  $.ajax({
			type: 'GET',
			url: revokeUrl,
			async: false,
			contentType: 'application/json',
			dataType: 'jsonp',
			success: function(nullResponse) {
			  // Do something now that user is disconnected
			  // The response is always undefined.
			  window.location = 'app.Boot';
			},
			error: function(e) {
			  // Handle the error
			  // console.log(e);
			  // You could point users to manually disconnect if unsuccessful
			  // https://plus.google.com/apps
			}
		  });
		}
	";
}
else {
	echo "
		function signinCallback(authResult) {
		  if (authResult['status']['signed_in']) {
			console.log(authResult);
			window.location = 'core.OAuthLoginWorker?access_token=' + authResult['access_token'];
			//document.getElementById('signinButton').setAttribute('style', 'display: none');
			//window.location = 'core.OAuthLoginWorker';
		  } else {
			// Update the app to reflect a signed out user
			// Possible error values:
			//   'user_signed_out' - User is signed-out
			//   'access_denied' - User denied access to your app
			//   'immediate_failed' - Could not automatically log in the user
			console.log('Sign-in state: ' + authResult['error']);
		  }
		}
	";
}
echo "
			</script>
		</head>
		<body>
			<div class='backgroundScroller' style='background-image: url({$_sys['path_cdn']}img/scroll-bg/clouds.png);'>
			</div>
			<div class='dialog vAlign loginDialog'>
				<div id='mainDialog' class='boxDialog'>
					<span id='signinButton' style='position: absolute; top: 25px; right: 35px;'>
					  <span
						class='g-signin'
						data-callback='signinCallback'
						data-clientid='257942873480-s8m68uf07fotunn51cpcsheoo7r35qss.apps.googleusercontent.com'
						data-cookiepolicy='single_host_origin'
						data-scope='profile'>
					  </span>
					</span>
					<h1 class='title'>after|mirror</h1>
					<h2 class='subtitle'>r{$rev['hash']}.{$revDate} #{$rev['num']}</h2>
					<br />
					<hr />
					<br />
					<form action='core.LoginWorker?do=login' method='post'>
						<input type='hidden' name='qs' value='";
if (isset($_GET["qs"])) echo $_GET["qs"];
echo "' />
						<label for='username' class='textboxLabel'>username</label>
						<input type='text' id='username' name='username' value='";
if (isset($_GET["username"])) echo $_GET["username"];
echo "' autocomplete='off' class='fxBackground' />
						<label for='password' class='textboxLabel'>password</label>
						<input type='password' id='password' name='password' value='' class='fxBackground' />
						<input type='checkbox' id='remember' name='remember' /> <label for='remember' class='checkboxLabel'>remember me</label>
						<br />
						<br />
						<div class='hAlignContainer'>
							<div class='hAlign'>
								<input type='submit' value='login' />
								<input type='button' value='register / forgot password' onclick=\"focusDialog('registerForgotDialog');\" />
							</div>
						</div>
					</form>
				</div>
				<div id='registerForgotDialog' style='display: none;' class='boxDialog'>
					<h1 class='title'>are you here to...</h1>
					<br />
					<a href='#' onclick=\"focusDialog('registerDialog');\" class='fxButton'>register?</a>
					<br />
					<br />
					<a href='#' onclick=\"alert('Sorry, you must contact an administrator to recover your password.');\" class='fxButton'>forgot password?</a>
					<br />
					<br />
					<a href='#' onclick=\"focusDialog('mainDialog');\" class='fxButton'>nevermind, get me back to the login screen</a>
				</div>
				<div id='registerDialog' style='display: none;' class='boxDialog'>
					<h1 class='title'>registration</h1>
					<h2 class='subtitle'><a href='#' onclick=\"focusDialog('mainDialog');\">cancel registration</a></h2>
					<br />
					<hr />
					<br />
					<form action='core.RegisterWorker?do=signup' method='post'>
						<label for='usernameReg' class='textboxLabel'>username</label>
						<input type='text' id='usernameReg' name='username' value='";
if (isset($_GET["username"])) echo $_GET['username'];
echo "' autocomplete='off' class='fxBackground limitText' />
						<label for='passwordReg' class='textboxLabel'>password</label>
						<input type='password' id='passwordReg' name='password' value='' class='fxBackground' />
						<label for='nameReg' class='textboxLabel'>name (full name, nickname, or whatever)</label>
						<input type='text' id='nameReg' name='name' value='' class='fxBackground' />
						<label for='emailReg' class='textboxLabel'>e-mail (for password retrieval)</label>
						<input type='text' id='emailReg' name='email' value='' class='fxBackground' />
						<div class='hAlignContainer'>
							<div class='hAlign'>
								<input type='submit' value='register' />
							</div>
						</div>
					</form>
				</div>
				<div id='forgotDialog' style='display: none;' class='boxDialog'>
				</div>
				<script>
					function focusDialog(id) {
						$('.boxDialog').fadeOut(200);
						$('#' + id).delay(200).fadeIn(200);
					}
					$(function() {
						$('.limitText').keyup(function() {
							if (this.value.match(/[^a-zA-Z0-9 ]/g)) {
								this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '');
							}
							this.value = this.value.toLowerCase();
						});
					});
				</script>
			</div>
	";
	if (isset($_GET["error"])) {
		switch ($_GET["error"]) {
			case "0":
				$errorMessage = "Good bye!";
			break;
			case "1":
				$errorMessage = "Password does not match.";
			break;
			case "2":
				$errorMessage = "Username &quot;{$_GET['username']}&quot; does not exist.";
			break;
			case "3":
				$errorMessage = "Your session is invalid. Please login again.";
			break;
			case "4":
				$errorMessage = "You must be logged in order to do that.";
			break;
			case "91":
				$errorMessage = "Thank you for registering. You may now login."; // OAuth
			break;
			case "99":
				$errorMessage = "Thank you for registering. You may now login.";
			break;
			case "100":
				$errorMessage = "Failed to register - Cannot leave any field(s) blank.";
			break;
			case "101":
				$errorMessage = "Failed to register - Cannot use this username.";
			break;
			case "102":
				$errorMessage = "Failed to register - Username exists.";
			break;
			case "103":
				$errorMessage = "Failed to register - Cannot register your account. If this problem persists, please contact an administrator.";
			break;
			case "F10":
				$errorMessage = "Failed to register - Registration is disabled.";
			break;
			case "F99":
				$errorMessage = "Site is currently offline. Please try again later.";
			break;
			case "B00":
				$errorMessage = "Your account is banned.";
			break;
			case "R90":
				$errorMessage = "Expired recovery key. Please request for a new key.";
			break;
			case "R91":
				$errorMessage = "Invalid recovery key.";
			break;
			case "R92":
				$errorMessage = "Invalid recovery key.";
			break;
			default:
				$errorMessage = "Unknown error. Please try again or contact an administrator.";
			break;
		}
		echo "
			<div class='popup error'>{$errorMessage}</div>
			<script>
				$(function() {
					$('.popup').delay(4000).fadeOut(400);
				});
			</script>
		";
	}
	if (isset($_GET["username"])) {
		echo "
			<script>
				$('#password').focus();
			</script>
		";
	}
	echo "
		</body>
	</html>
	";
}
?>
