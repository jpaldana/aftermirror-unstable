<?php
ini_set('user_agent','Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.56 Safari/537.17');

echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
				<h1 class='center'><span style='color: gray;'>my</span>|<span class='fxSlide'>settings</span></h1>
				<h2 class='center' style='font-size: 14px; color: gray;'>make it personal!</h2>
				<br />
				<hr />
				<br />
				<h3>Account</h3>
";
if (isset($_GET["requestPasswordReset"])) {
	$key = $_SESSION["username"] . sha1($_SESSION["username"] . uniqid());
	$rec = array("time" => time(), "key" => $key);
	writeDB(myDir() . ".recovery", $rec);
	echo "<a href='core.PasswordResetToken?user={$_SESSION['username']}&key={$key}' class='fxButton'>Password Reset Link (valid for 1 hour)</a><br /><br />";
}

$myDat = readDB(myDat());

echo "<b>Name </b> {$myDat['name']}<br />";
echo "<b>Email </b> {$myDat['email']}<br />";
echo "<b>Password </b> <a href='app.Settings?requestPasswordReset' class='ajaxButton fxButton'>Change Password</a> <a href='app.OAuthModal' class='fxButton'>OAuth Setup</a><br />";

echo "		
				<br />
				<hr />
				<br />
				<h3>Profile</h3>
				<br />
				<div class='column_container'>
					<div class='column_one'>
						<div style='background-image:url(core.DataGate?profilePicture={$_SESSION['username']}); position: relative; display: inline-block;' class='profilePicture'>
							<a href='app.SettingsDialog?profilePicture' class='modalFriendly' style='display: block; position: absolute; bottom: 0px; left: 0px; right: 0px;padding: 4px; background-color: rgba(0, 0, 0, 0.7); color: white; text-align: center;'>change picture</a>
						</div>
						<br />
						<br />
						<br />
					</div>
					<div class='column_two'>
";
if (file_exists(myDir() . ".css")) {
	$cssDB = readDB(myDir() . ".css");
	if (isset($cssDB["backgroundType"])) {
		if ($cssDB["backgroundType"] == "solid") {
			echo "<div style='background-color: {$cssDB['background']}; position: relative; display: inline-block; width: 256px;' class='profilePicture backgroundPicture'>";
		}
		elseif ($cssDB["backgroundType"] == "image") {
			echo "<div style='background-image: url(core.DataGate?background={$_SESSION['username']}); position: relative; display: inline-block; width: 256px;' class='profilePicture backgroundPicture'>";
		}
	}
}
else {
	echo "<div style='background-color: #E1E1E1; position: relative; display: inline-block; width: 256px;' class='profilePicture backgroundPicture'>";
}
echo "
						<a href='app.SettingsDialog?profileBackground' class='modalFriendly' style='display: block; position: absolute; bottom: 0px; left: 0px; right: 0px;padding: 4px; background-color: rgba(0, 0, 0, 0.7); color: white; text-align: center;'>change background</a>
						</div>
						<br />
						<br />
						<a href='app.SettingsDialog?sidebarBackground' class='modalFriendly fxButton'>change sidebar color</a>
					</div>
				</div>
				<br />
				<hr />
				<br />
				<h3>MyAnimeList</h3>
				<small>Link your MyAnimeList profile here!</small>
				<br />
				<p style='font-size: 9px;'><img src='{$_sys['path_cdn']}img/icons/lock.png' style='width: 10px;' /> We try our best to secure your account. This is an experimental project and is not backed by any industrial-grade security whatsoever.</p>
				<br />
";
$malDB = readDB(myDir() . ".myAnimeList");
echo "
				<form action='core.MALWorker' method='post'>
					<label for='mal_username' class='textboxLabel'>Username</label>
					<input type='text' name='mal_username' id='mal_username' placeholder='MyAnimeList Username' value='{$malDB['username']}' />
					<label for='mal_password' class='textboxLabel'>Password</label>
					<input type='password' name='mal_password' id='mal_password' placeholder='MyAnimeList Password' onfocus=\"$('#saveAccountDetails').fadeIn(200);\" />
					<br />
					<input type='submit' value='Save Account Details' class='fullSize' id='saveAccountDetails' style='display: none;' />
				</form>
";
if (isSomething($malDB) && (isset($malDB["username"]) && isset($malDB["password"]))) {
	if (!$malDB["valid"]) {
		echo "<p>Error: Incorrect login.</p>";
	}
	elseif ($malDB["valid"] === true) {
		$list = "http://{$malDB['username']}:{$malDB['password']}@myanimelist.net/malappinfo.php?u={$malDB['username']}&status=all&type=anime";
		if ($malDB["sync"] == 1) {
			$result = @simplexml_load_file($list);
			$result = json_decode(json_encode($result), true);
		}
		else {
			$result = array();
		}
		$syncAccountTrue = "";
		$syncAccountFalse = "";
		if ($malDB["sync"] == 1) {
			$syncAccountTrue = "checked ";
		}
		else {
			$syncAccountFalse = "checked ";
		}
		echo "
			<form action='core.MALWorker' method='post'>
				<input type='radio' name='syncAccount' value='1' id='syncAccountTrue' onchange=\"$('#saveSyncDetails').fadeIn(200);\" {$syncAccountTrue}/><label for='syncAccountTrue'> Sync Account</a><br />
				<input type='radio' name='syncAccount' value='0' id='syncAccountFalse' onchange=\"$('#saveSyncDetails').fadeIn(200);\" {$syncAccountFalse}/><label for='syncAccountFalse'> Don't Sync Account</a><br /><br />
				<input type='submit' value='Save Sync Details' class='fullSize' id='saveSyncDetails' style='display: none;' />
			</form>
			<br />
		";
		if (isSomething($result)) {
			echo "
				<p>
					<h3>Statistics:</h3>
					<b>User ID: </b>{$result['myinfo']['user_id']}<br />
					<b>Watching: </b>{$result['myinfo']['user_watching']}</br />
					<b>Completed: </b>{$result['myinfo']['user_completed']}</br />
					<b>On Hold: </b>{$result['myinfo']['user_onhold']}</br />
					<b>Dropped: </b>{$result['myinfo']['user_dropped']}</br />
					<b>Plan To Watch: </b>{$result['myinfo']['user_plantowatch']}</br />
					<b>Days Spent Watching: </b>{$result['myinfo']['user_days_spent_watching']}</br />
				</p>
			";
		}
		else {
			echo "
				<p>Failed to load list. Please try again later.</p>
			";
		}
	}
	else {
		$credentials = "http://{$malDB['username']}:{$malDB['password']}@myanimelist.net/api/account/verify_credentials.xml";
		$result = simplexml_load_file($credentials);
		if (!$result->id) {
			$malDB["valid"] = false;
			writeDB(myDir() . ".myAnimeList", $malDB);
			echo "<p>Error: Incorrect login.</p>";
		}
		else {
			$malDB["valid"] = true;
			writeDB(myDir() . ".myAnimeList", $malDB);
			echo "<p>MyAnimeList Profile ID: " . $result->id . "<br />Refresh to view your current stats.</p>";
		}
	}
}
echo "
			</div>
		</div>
	</div>
";
?>
