<?php
// Unified Theatre.
// Let's make this awesome.

echo "
	<script>
		var aniUsername = '{$_SESSION['username']}';
		var theatreSocketServer = '{$_sys['node_url']}theatre';
		var sysPathRoot = '{$_sys['path_root']}';
	</script>
	<script src='{$_sys['node_url']}socket.io/socket.io.js'></script>
	<script src='{$_sys['path_cdn']}js/theatre.js'></script>
	
	<div id='content'>
		<div id='container'>
			
			<div class='block' style='background-color: black;'>
				<video id='video' controls></video>
			</div>
			<div class='block'>
				<div id='messages'></div>
				<div id='inputbox'>
					<form id='chatter'>
						<input type='text' placeholder='say something...' id='userinput' autocomplete='off' />
					</form>
				</div>
				<span id='console' style='color: gray; font-family: Monospace;'>--</span>
			</div>
			<div class='blockTab'>
				<span class='tab active fxBackground' id='tabStatus_tab' onclick=\"switchTab('tabStatus', ['tabOptions', 'tabBrowse', 'tabPreloads']);\">Status</span>
				<span class='tab fxBackground' id='tabOptions_tab' onclick=\"switchTab('tabOptions', ['tabStatus', 'tabBrowse', 'tabPreloads']);\">Options</span>
				<span class='tab fxBackground' id='tabPreloads_tab' onclick=\"switchTab('tabPreloads', ['tabStatus', 'tabBrowse', 'tabOptions']); listPreloads();\">Preloads</span>
				<span class='tab fxBackground' id='tabBrowse_tab' onclick=\"switchTab('tabBrowse', ['tabOptions', 'tabStatus', 'tabPreloads']); ajaxDivLoad('tabBrowse', 'app.ajaxTheatre?getLibrary');\">Browse...</span>
			</div>
			<div class='block'>
				<div id='tabStatus' class='tabContent active'>
					<h3>Users Online</h3>
					<div id='userlist'>--</div>
				</div>
				<div id='tabOptions' class='tabContent'>
					<audio id='beeper' style='display: none;'><source src='{$_sys['node_cdn']}beep.mp3' type='audio/mp3' /></audio>
					<h3>Message alert volume</h3>
					<input type='range' id='beeperVolume' min='0.0' max='1.0' value='1.0' step='0.05' onChange=\"controlBeeperVolume()\" style='width: 100%;' />
					<br />
					<h3>Preferred Quality</h3>
					<label for='preferQ_high' class='fxButton'><input type='radio' id='preferQ_high' name='preferQ' value='high' /> High</label>
					<label for='preferQ_low' class='fxButton'><input type='radio' id='preferQ_low' name='preferQ' value='low' checked /> Low</label>
					<br />
					<a class='preventDefault buttonLink fxBackground' style='color: cyan; background-color: rgba(0, 0, 0, 0.5);' onclick=\"reloadCachedFiles();\">Refresh</a>
					<a class='preventDefault buttonLink fxBackground' style='color: red; background-color: rgba(0, 0, 0, 0.5);' onclick=\"removeAllFiles(); setTimeout('removeAllFiles();', 1000);\">Remove All</a>
				</div>
				<div id='tabPreloads' class='tabContent'>
				</div>
				<div id='tabBrowse' class='tabContent'>
				</div>
			</div>
		</div>
	</div>
";
?>
