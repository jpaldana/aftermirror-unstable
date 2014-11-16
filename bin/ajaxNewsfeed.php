<?php
// Make this more personal soon.
/*
$feed[0] = array(
	"title" => "Updated Design",
	"subtitle" => "October 7, 2014 - 9:04 PM",
	"origin" => "jpaldana",
	"origin_type" => "user",
	"banner_available" => true,
	"banner_image" => "{$_sys['path_cdn']}test-spanner.jpg",
	"content" => "<strong>Woo hoo!</strong> The site finally looks a bit nicer again. Let's see how this one goes, yeah?"
);
*/
// feed sources:
// data/feed/.prism <- prism updates
// data/feed/.admin <- admin messages/notices
// data/feed/.public <- public messages
// data/user/[user]/.friends <- friends updates
//   \
//    data/user/[friend]/.posts

$feed = array();
if (file_exists("{$_sys['path_data']}feed/.prism")) {
	$feed = array_merge($feed, readDB("{$_sys['path_data']}feed/.prism"));
}
if (file_exists("{$_sys['path_data']}feed/.admin")) {
	$feed = array_merge($feed, readDB("{$_sys['path_data']}feed/.admin"));
}
if (file_exists("{$_sys['path_data']}feed/.public")) {
	$feed = array_merge($feed, readDB("{$_sys['path_data']}feed/.public"));
}
if (file_exists(myDir() . ".friend-list")) {
	$friends = readDB(myDir() . ".friend-list");
	if (isSomething($friends)) {
		foreach (array_keys($friends) as $friend) {
			if (file_exists("{$_sys['path_data']}user/{$friend}/.posts")) {
				$feed = array_merge($feed, readDB("{$_sys['path_data']}user/{$friend}/.posts"));
			}
		}
	}
	$feed = array_merge($feed, readDB("{$_sys['path_data']}feed/.prism"));
}
knatsort($feed);
$feed = array_reverse($feed);

/*
debug
*/
/*
for ($i = 1; $i <= 20; $i++) {
	$feed[$i] = $feed[0];
}
*/

$totalFeed = count($feed);
$currentFeed = $_GET["current"];
$maxFeedPush = 10;

$feedSlice = array_slice($feed, $currentFeed, $maxFeedPush);
foreach ($feedSlice as $feedGUID => $feedBlock) {
	$pdMonth = date("F", $feedBlock["time"]);
	$pdDay = date("j", $feedBlock["time"]);
	$pdYear = date("Y", $feedBlock["time"]);
	$pdTime = date("g:i:s A", $feedBlock["time"]);
	$pdFull = date("F j, Y - g:i:s A", $feedBlock["time"]);
	$timeSince = time_since(time() - $feedBlock["time"]);
	echo "
		<div class='block feedBlock'>
			<div class='feedBar'>
				<div style='background-image: url({$_sys['path_root']}profile/{$feedBlock['origin']}/picture.jpg);' class='feedProfileImage'></div>
				<div class='dateBlock'>
					<span class='dateBlock_month'>{$pdMonth}</span>
					<span class='dateBlock_day'>{$pdDay}</span>
					<span class='dateBlock_year'>{$pdYear}</span>
					<span class='dateBlock_time'>{$pdTime}</span>
				</div>
				<div class='vAlign titleContainer'>
					<h1 class='title center noFlow'>{$feedBlock['title']}</h1>
					<h2 class='subtitle center' style='font-size: 10px; color: gray;'><span style='color: gray; font-size: 14px;'>{$timeSince} ago</span><span class='dateBlock_fulltime'><br />{$pdFull}</span></h2>
				</div>
			</div>
	";
	if ($feedBlock["banner_available"]) {
		echo "
			<br /> 
			<div style='background-image: url({$feedBlock['banner_image']});' class='spanImage'></div>
			<br />
		";
	}
	echo "
			<blockquote class='text'>{$feedBlock['content']}<cite><div style='background-image: url({$_sys['path_root']}profile/{$feedBlock['origin']}/picture.jpg);' class='citeProfilePicture'></div> {$feedBlock['origin']}</cite></blockquote>
		</div>
	";
}

if ($currentFeed + $maxFeedPush < $totalFeed) {
	$nextCount = $currentFeed + $maxFeedPush;
	echo "
		<div id='moreLoad_{$currentFeed}' class='block moreLoad fxBackground' onclick='feedLoad({$nextCount}, {$currentFeed});'>
			<span class='link'>older posts...</span>
		</div>
		<br />
	";
}
?>
