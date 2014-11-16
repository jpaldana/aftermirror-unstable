<?php
echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
				<h1 class='center'><span style='color: gray;'>after</span>|<span class='fxSlide'>admin</span></h1>
				<h2 class='center' style='font-size: 14px; color: gray;'>who messed up now?</h2>
				
				<br />
				
				<div class='center'>
					<a href='app.Admin?fix=users' class='fxButton ajaxFriendly'>users</a> 
					<a href='app.Admin?fix=sendify' class='fxButton ajaxFriendly'>sendify.me</a> 
					<a href='app.Admin?fix=files' class='fxButton'>files</a> 
					<a href='app.Prism' class='fxButton ajaxFriendly'>prism</a> 
					<a href='app.Admin?fix=system' class='fxButton'>system</a> 
					<a href='app.Admin?fix=cloudflare' class='fxButton'>cloudflare</a>
				</div>
				
				<br />
";

if (isset($_GET["fix"]) && isSomething($_GET["fix"])) switch ($_GET["fix"]) {
	case "users":
		if (isset($_GET["psu"])) {
			$r = readDB($_sys["path_data"] . "user/{$_GET['psu']}/.account");
			$_SESSION["username"] = $_GET["psu"];
			$_SESSION["key"] = $r["sessionKey"];
			echo "<h1>Session set.</h1>";
		}
		if (isset($_GET["rec"])) {
			$key = $_GET["rec"] . sha1($_GET["rec"] . uniqid());
			$rec = array("time" => time(), "key" => $key);
			writeDB($_sys["path_data"] . "user/{$_GET['rec']}/.recovery", $rec);
			echo "<a href='core.PasswordResetToken?user={$_GET['rec']}&key={$key}' class='fxButton'>Recovery Link for {$_GET['rec']} (valid for 1 hour)</a><br/>";
		}
		$users = dir_get($_sys["path_data"] . "user");
		
		if (isset($_GET["setUser"])) {
			$account = $_sys["path_data"] . "user/{$_GET['setUser']}/.account";
			$userDB = readDB($account);
			if (isset($_GET["setLevel"])) {
				$userDB["access"] = $_GET["setLevel"];
				writeDB($account, $userDB);
			}
		}
		
		foreach ($users as $user) {
			$user = basename($user);
			$account = $_sys["path_data"] . "user/{$user}/.account";
			
			if (file_exists($account)) {
				echo "<h3>{$user}</h3>";
				$userDB = readDB($account);
				echo "<b>Name:</b> {$userDB['name']}<br />";
				echo "<b>Email:</b> {$userDB['email']}<br />";
				$userRegDate = date("M d Y - h:i:s A", $userDB["registrationDate"]);
				echo "<b>Registration Date:</b> {$userRegDate}<br />";
				echo "<b>IP(s):</b> {$userDB['ip']}, {$userDB['prev_ip']}<br />";
				echo "<b>Access Level:</b> {$userDB['access']} ";
				echo "<select id='newLevel' class='noSelectStyle' onchange=\"ajaxContentLoad('app.Admin?fix=users&setUser={$user}&setLevel=' + $(this).val());\"><option>";
				switch ($userDB["access"]) {
						case 9:
							echo "administrator";
						break;
						case 8:
							echo "moderator";
						break;
						case 7:
							echo "priority user";
						break;
						case 6:
							echo "priority user";
						break;
						case 5:
							echo "priority user";
						break;
						case 4:
							echo "priority user";
						break;
						case 3:
							echo "elevated user"; // + AnimeDL rights
						break;
						case 2:
							echo "registered user";
						break;
						case 1:
							echo "unverified user";
						break;
						case 0:
							echo "silenced user";
						break;
						case -1:
							echo "banned user";
						break;
				}
				echo "*</option>";
				echo "
					<option value='9'>administrator</option>
					<option value='8'>moderator</option>
					<option value='7'>priority user</option>
					<option value='6'>priority user</option>
					<option value='5'>priority user</option>
					<option value='4'>priority user</option>
					<option value='3'>elevated user</option>
					<option value='2'>registered user</option>
					<option value='1'>unverified user</option>
					<option value='0'>silenced user</option>
					<option value='-1'>banned user</option>
				";
				echo "</select>";
				echo "<br />";
				if (file_exists($account . ".backup")) {
					echo "<b>.account Backup: Yes</b><br/>";
				}
				else {
					echo "<b>.account Backup:</b> No<br/>";
				}
				echo "<a href='app.Admin?fix=users&psu={$user}' class='ajaxFriendly fxButton'>pseudologin</a> ";
				echo "<a href='app.Admin?fix=users&rec={$user}' class='ajaxFriendly fxButton'>generate password recovery key</a> ";
				echo "<br /><br />";
			}
			else {
				// pseudoaccount
			}
		}
	break;
	case "sendify":
		$sendifyDB = readDB($_sys["path_data"] . "db/.short-links");
		
		if (isset($_GET["removeKey"])) {
			$sendifyDB = pushValueFromArray($_GET["removeKey"], $sendifyDB);
			writeDB($_sys["path_data"] . "db/.short-links", $sendifyDB);
			echo "<b>Changes saved.</b><br/><br/>";
		}
		
		foreach ($sendifyDB as $key => $url) {
			echo "
				<a href='app.Admin?fix=sendify&removeKey={$key}' class='ajaxFriendly fxButton'>remove</a> <span style='color: gray;'>sendify.me</span>/<b class='fxSlide'>{$key}</b><br />
				<a href='{$url}' target='_blank' class='preventDefault buttonLink fxBackground noFlow'>{$url}</a>
				<hr /><br />
			";
		}
		
	break;
	case "files":
		$files = dir_get($_sys["path_data"] . "files");
		$akDB = readDB($_sys["path_data"] . "db/.access-key");
		print_a($files);
		print_a($akDB);
	break;
	case "prism":
	
	break;
	case "system":
		$sysDB = readDB($_sys["path_data"] . "db/.site-config");
		if (isset($_GET["access"])) {
			$sysDB["access"] = $_GET["access"];
			writeDB($_sys["path_data"] . "db/.site-config", $sysDB);
		}
		if (isset($_GET["regAccess"])) {
			$sysDB["regAccess"] = $_GET["regAccess"];
			writeDB($_sys["path_data"] . "db/.site-config", $sysDB);
		}

		echo "
			<h3>Site Access ({$sysDB['access']})</h3>
			<a href='app.Admin?fix=system&access=online' class='fxButton ajaxFriendly'>Online</a> 
			<a href='app.Admin?fix=system&access=offline' class='fxButton ajaxFriendly'>Offline (No access to non-admin)</a>
			<br />
			<br />
			<h3>Registration ({$sysDB['regAccess']})</h3>
			<a href='app.Admin?fix=system&regAccess=online' class='fxButton ajaxFriendly'>Online</a> 
			<a href='app.Admin?fix=system&regAccess=offline' class='fxButton ajaxFriendly'>Offline</a>
			<br />
			<br />
		";
	break;
}

echo "
			</div>
		</div>
	</div>
";
?>
