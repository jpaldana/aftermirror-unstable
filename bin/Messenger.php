<?php
$messengerDB = readDB(dir_fix(myDir() . ".messenger"));
echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
";

if (isset($_GET["statusChange"])) {
	echo "
		<a href='#' class='preventDefault buttonLink fxBackground' onclick=\"miniPopup('core.ajaxMessenger?set_status=available', 'url', 'messengerStatusChange');\"><span class='messengerAvailable'>&bull;</span> Available</a>
		<a href='#' class='preventDefault buttonLink fxBackground' onclick=\"miniPopup('core.ajaxMessenger?set_status=busy', 'url', 'messengerStatusChange');\"><span class='messengerBusy'>&bull;</span> Busy</a>
		<a href='#' class='preventDefault buttonLink fxBackground' onclick=\"miniPopup('core.ajaxMessenger?set_status=away', 'url', 'messengerStatusChange');\"><span class='messengerAway'>&bull;</span> Away</a>
		<a href='#' class='preventDefault buttonLink fxBackground' onclick=\"miniPopup('core.ajaxMessenger?set_status=invisible', 'url', 'messengerStatusChange');\"><span class='messengerInvisible'>&bull;</span> Invisible</a>
	";
}
elseif (isset($_GET["manageList"])) {
	$friendList = readDB(myDir() . ".friend-list");
	$users = getUsers();
	if (isset($_GET["addFriend"])) {
		$friendList[$_GET["addFriend"]] = false;
		echo "<h3>Successfully sent request to {$_GET['addFriend']}.</h3><br />";
		$requestList = readDB("{$_sys['path_data']}user/{$_GET['addFriend']}/.friend-list");
		$requestList[$_SESSION["username"]] = -2;
		writeDB("{$_sys['path_data']}user/{$_GET['addFriend']}/.friend-list", $requestList);
		writeDB(myDir() . ".friend-list", $friendList);
	}
	if (isset($_GET["confirmFriend"])) {
		$friendList[$_GET["confirmFriend"]] = true;
		echo "<h3>You are now friends with {$_GET['confirmFriend']}.</h3><br />";
		$requestList = readDB("{$_sys['path_data']}user/{$_GET['confirmFriend']}/.friend-list");
		$requestList[$_SESSION["username"]] = true;
		writeDB("{$_sys['path_data']}user/{$_GET['confirmFriend']}/.friend-list", $requestList);
		writeDB(myDir() . ".friend-list", $friendList);
	}
	foreach ($users as $user) {
		if ($user === "guest") {
			continue;
		}
		if (isSomething($friendList) && isset($friendList[$user])) {
			switch ($friendList[$user]) {
				case 1:
					// friends
					echo "<span class='buttonLink fxBackground'><img src='{$_sys['path_cdn']}img/icons/contact.png' style='width: 12px;' /> <b>{$user}</b></span>";
				break;
				case 0:
					// sent request
					echo "<span class='buttonLink fxBackground'><img src='{$_sys['path_cdn']}img/icons/load.gif' style='width: 12px;' /> {$user} <b>request sent.</b></span>";
				break;
				case -1:
					// request declined
					echo "<a href='app.Messenger?manageList&addFriend={$user}' class='modalFriendly buttonLink fxBackground'><img src='{$_sys['path_cdn']}img/icons/add.png' style='width: 12px;' /> {$user}</a>";
				break;
				case -2:
					// request sent
					echo "<a href='app.Messenger?manageList&confirmFriend={$user}' class='modalFriendly buttonLink fxBackground'><img src='{$_sys['path_cdn']}img/icons/load.gif' style='width: 12px;' /> {$user} <b>wants to be friends, confirm?</b></a>";
				break;
			}
		}
		else {
			// not friends.
			echo "<a href='app.Messenger?manageList&addFriend={$user}' class='modalFriendly buttonLink fxBackground'><img src='{$_sys['path_cdn']}img/icons/add.png' style='width: 12px;' /> {$user}</a>";
		}
	}
}

echo "
			</div>
		</div>
	</div>
";
?>
