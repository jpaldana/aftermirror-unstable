<?php
// Home
function createPost($title, $author, $message) {
	
}
function retrievePosts($requestor, $author) {
}
// Friends
function areWeFriends($requestor, $person) {
}
function becomeFriends($requestor, $person) {
}
function removeFriends($requestor, $person) {
}
function getUsers() {
	global $_sys;
	$a = dir_get($_sys["path_data"] . "user/");
	$users = array();
	foreach ($a as $b) {
		$users[] = basename($b);
	}
	return $users;
}
// Files
function downloadFile($key) {
}
function getFileInformation($key) {
}
function shareFile($key, $person) {
}
function unshareFile($key, $person) {
}
// Points
function rewardPoints($person, $points) {
}
function spendPoints($person, $points) {
}
// Short Links
function sendifyMe($url, $req = false) {
}
?>