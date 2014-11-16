<?php
echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
				<h1 class='center'><span style='color: gray;'>my</span>|<span class='fxSlide'>settings</span></h1>
";
if (isset($_GET["profilePicture"])) {
	if (isset($_GET["refresh"])) {
		if (isset($_POST["url"])) {
			$image = file_get_contents($_POST["url"], NULL, NULL, 0, 1024*512);
			$local_fext = fext(basename($_POST["url"]));
			$local_temp = $_sys["path_data"] . "temp/{$_SESSION['username']}_temp_profilepicture.{$local_fext}";
			file_put_contents($local_temp, $image);
			if (getimagesize($local_temp)) {
				echo "<h2>Image successfully set!</h2>";
				if (file_exists(myDir() . ".profile_picture")) {
					unlink(myDir() . ".profile_picture");
				}
				rename($local_temp, myDir() . ".profile_picture");
				echo "<h2>Profile picture updated!</h2>";
			}
			else {
				echo "
					<h2>Invalid image</h2>
					<br />
					<p>Possible reasons include: large file size, non-image/invalid URL, private/non-hotlink URL</p>
					<br />
					<a href='app.SettingsDialog?profilePicture&method=url_upload' class='modalFriendly'>Try again?</a>
				";
			}
		}
		elseif (isset($_FILES["file"])) {
			if ($_FILES["file"]["error"] > 0) {
				echo "
					<h2>Error</h2>
					<br />
					<p>{$_FILES['file']['error']}</p>
					<br />
					<a href='app.SettingsDialog?profilePicture&method=upload' class='modalFriendly'>Try again?</a>
				";
			}
			else {
				switch ($_FILES["file"]["type"]) {
					case "image/gif":
					case "image/jpeg":
					case "image/jpg":
					case "image/pjpeg":
					case "image/x-png":
					case "image/png":
					case "image/bmp":
						if ($_FILES["file"]["size"] < 1024*512) {
							if (file_exists(myDir() . ".profile_picture")) {
								unlink(myDir() . ".profile_picture");
							}
							move_uploaded_file($_FILES["file"]["tmp_name"], myDir() . ".profile_picture");
							echo "<h2>Profile picture updated!</h2>";
						}
						else {
							echo "<h2>Error</h2><br /><p>File is too large.</p><br /><a href='app.SettingsDialog?profilePicture&method=upload' class='modalFriendly'>Try again?</a>";
						}
					break;
					default:
						echo "<h2>Error</h2><br /><p>File type '{$_FILES['file']['type']}' not recognized.</p><br /><a href='app.SettingsDialog?profilePicture&method=upload' class='modalFriendly'>Try again?</a>";
					break;
				}
			}
		}
	}
	else {
		echo "
						<h2>Update Profile Picture</h2>
						<br />
						<p>max size: 512 KB, square images (around 256x256) recommended</p>
						<br />
						<a href='app.SettingsDialog?profilePicture&method=upload' class='modalFriendly fxButton'>upload a file...</a> 
						<a href='app.SettingsDialog?profilePicture&method=url_upload' class='modalFriendly fxButton'>enter a URL...</a> 
						<a href='app.SettingsDialog?profilePicture&method=local_file' class='modalFriendly fxButton'>select from list...</a>
						<br />
		";
		if (isset($_GET["method"])) {
			switch ($_GET["method"]) {
				case "upload":
					echo "
						<br />
						<form action='app.SettingsDialog?profilePicture&refresh' method='post' enctype='multipart/form-data'>
							<label for='file'>Select Image</label>
							<input type='file' name='file' id='file' />
							<div class='center'>
								<input type='submit' value='Upload' />
							</div>
						</form>
					";
				break;
				case "url_upload":
					echo "
						<br />
						<form action='app.SettingsDialog?profilePicture&refresh' method='post'>
							<label for='url'>Image URL</label>
							<input type='text' name='url' id='url' autocomplete='off' />
							<div class='center'>
								<input type='submit' value='Upload' />
							</div>
						</form>
					";
				break;
				case "local_file":
				break;
			}
		}
	}
}
elseif (isset($_GET["profileBackground"])) {
	if (isset($_GET["set_color"])) {
		if (substr($_GET["set_color"], 0, 1) === "!") {
			$_GET["set_color"][0] = "#";
		}
		$cssDB = readDB(myDir() . ".css");
		$cssDB["backgroundType"] = "solid";
		$cssDB["background"] = $_GET["set_color"];
		writeDB(myDir() . ".css", $cssDB);
		echo "<h2>Background set!</h2><script>$('#underlay').css('background-color', '{$_GET['set_color']}');</script>";
	}
	elseif (isset($_GET["set_wallpaper"])) {
		$cssDB = readDB(myDir() . ".css");
		$cssDB["backgroundType"] = "image";
		$cssDB["background"] = "url({$_sys['path_cdn']}test-wallpaper.jpg)";
		writeDB(myDir() . ".css", $cssDB);
		echo "<h2>Background set!</h2><script>$('#underlay').css('background-image', '{$cssDB['background']}');</script>";
	}
	echo "
		<h2>Change Background</h2>
		<br />
		<hr />
		<br />
		<a href='app.SettingsDialog?profileBackground&set_color=!E1E1E1' class='modalFriendly fxButton' onclick=\"changeBackgroundColor('#E1E1E1');\">Classic gray</a> 
		<a href='app.SettingsDialog?profileBackground&set_color=black' class='modalFriendly fxButton' onclick=\"changeBackgroundColor('black');\">Black</a> 
		<a href='app.SettingsDialog?profileBackground&set_color=red' class='modalFriendly fxButton' onclick=\"changeBackgroundColor('red');\">Red</a>
		<br />
		<br />
		<small>For custom image backgrounds, use <a href='app.Cloud' class='ajaxFriendly'>Cloud</a> to set your background.</small>
	";
}
elseif (isset($_GET["sidebarBackground"])) {
	if (isset($_GET["set_color"])) {
		$cssDB = readDB(myDir() . ".css");
		switch($_GET["set_color"]) {
			case "pure_white":
				$cssDB["sidebar"] = "white";
				$cssDB["sidebar_text"] = "black";
			break;
			case "pure_black":
				$cssDB["sidebar"] = "black";
				$cssDB["sidebar_text"] = "white";
			break;
			case "full_transparent":
				$cssDB["sidebar"] = "rgba(0, 0, 0, 0)";
				$cssDB["sidebar_text"] = "black";
			break;
			case "full_transparent2":
				$cssDB["sidebar"] = "rgba(0, 0, 0, 0)";
				$cssDB["sidebar_text"] = "white";
			break;
			case "glassy_smoke":
				$cssDB["sidebar"] = "rgba(255, 255, 255, 0.5)";
				$cssDB["sidebar_text"] = "black";
			break;
			case "faded_shadow":
				$cssDB["sidebar"] = "rgba(0, 0, 0, 0.5)";
				$cssDB["sidebar_text"] = "white";

			break;
		}
		echo "<h2>Sidebar background set!</h2><script>$('#sidebar').css('background-color', '{$cssDB['sidebar']}'); $('#sidebar').css('color', '{$cssDB['sidebar_text']}');</script>";
		writeDB(myDir() . ".css", $cssDB);
	}
	echo "
		<h2>Change Sidebar Background</h2>
		<br />
		<hr />
		<br />
		<a href='app.SettingsDialog?sidebarBackground&set_color=pure_white' class='modalFriendly fxButton'>Pure White</a> 
		<a href='app.SettingsDialog?sidebarBackground&set_color=pure_black' class='modalFriendly fxButton'>Pure Black</a> 
		<a href='app.SettingsDialog?sidebarBackground&set_color=full_transparent' class='modalFriendly fxButton'>Full Transparent</a> 
		<a href='app.SettingsDialog?sidebarBackground&set_color=full_transparent2' class='modalFriendly fxButton'>Full Transparent (white text)</a> 
		<a href='app.SettingsDialog?sidebarBackground&set_color=glassy_smoke' class='modalFriendly fxButton'>Default (Glassy Smoke)</a> 
		<a href='app.SettingsDialog?sidebarBackground&set_color=faded_shadow' class='modalFriendly fxButton'>Faded Shadow</a> 
	";
}
echo "
			</div>
		</div>
	</div>
";
?>
