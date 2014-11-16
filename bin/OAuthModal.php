<?php
echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
				<h1 class='center'><span style='color: gray;'>after</span>|<span class='fxSlide'>OAuth</span></h1>
				<h2 class='center' style='font-size: 14px; color: gray;'>cross-site login</h2>
";
$oauthDB = readDB($_sys["path_data"] . "db/.oauth");

if (isset($_GET["access_token"])) {
	$accessToken = $_GET["access_token"];
	$userDetails = file_get_contents('https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $accessToken);
	$userData = json_decode($userDetails, true);
	//print_a($userData);
	if ($userData["id"]) {
		$oauthDB[$userData["id"]] = $_SESSION["username"];
		writeDB($_sys["path_data"] . "db/.oauth", $oauthDB);
		echo "<h3>OAuth set!</h3>";
	}
}
else {
	if (isValueInArray($_SESSION["username"], $oauthDB, false)) {
		echo "
			<b>Your OAuth ID has been set.</b>
		";
	}
	else {
		echo "
			<script>
			function signinCallback(authResult) {
			  if (authResult['status']['signed_in']) {
				//document.getElementById('signinButton').setAttribute('style', 'display: none');
				//console.log(authResult);
				modalContentLoad('app.OAuthModal?access_token=' + authResult['access_token']);
			  } else {
				// Update the app to reflect a signed out user
				// Possible error values:
				//   'user_signed_out' - User is signed-out
				//   'access_denied' - User denied access to your app
				//   'immediate_failed' - Could not automatically log in the user
				console.log('Sign-in state: ' + authResult['error']);
			  }
			}
			</script>
			<br />
			<span id='signinButton'>
			  <span
				class='g-signin'
				data-callback='signinCallback'
				data-clientid='257942873480-s8m68uf07fotunn51cpcsheoo7r35qss.apps.googleusercontent.com'
				data-cookiepolicy='single_host_origin'
				data-scope='profile'>
			  </span>
			</span>
		";
	}
}
echo "
			</div>
		</div>
	</div>
";
?>
