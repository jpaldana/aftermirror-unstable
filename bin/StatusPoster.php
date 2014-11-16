<?php
enforceLogin();

if (isset($_POST["post_title"]) && isset($_POST["post_content"]) && isset($_POST["visibility"])) {
	$title = $_POST["post_title"];
	$content = $_POST["post_content"];
	$visibility = $_POST["visibility"];
	if ($visibility == "admin") {
		reqAccess(9, "app.Home");
	}
	
	$postGUID = time() . "_" . $_SESSION["username"] . uniqid();
	$post = array(
		"title" => $title,
		"time" => time(),
		"origin" => $_SESSION["username"],
		"origin_type" => "user",
		"banner_available" => isset($_POST["banner_available"]),
		"banner_image" => isset($_POST["banner_available"]) ? $_POST["banner_image"] : "",
		"content" => $content,
		"postGUID" => $postGUID
	);
	
	switch ($visibility) {
		case "friends":
			$myPosts = readDB(myDir() . ".posts");
			$myPosts[$postGUID] = $post;
			writeDB(myDir() . ".posts", $myPosts);
		break;
		case "public":
			$pubPosts = readDB("{$_sys['path_data']}feed/.public");
			$pubPosts[$postGUID] = $post;
			writeDB("{$_sys['path_data']}feed/.public", $pubPosts);
		break;
		case "admin":
			$admPosts = readDB("{$_sys['path_data']}feed/.admin");
			$admPosts[$postGUID] = $post;
			writeDB("{$_sys['path_data']}feed/.admin", $admPosts);
		break;
	}
	
}
header("Location: app.Home");
?>
