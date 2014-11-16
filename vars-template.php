<?php
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
	$_sys = array(
		"os" => "linux",
		"path_root" => "/",
		"path_bin" => "bin/",
		"path_cdn" => "cdn/",
		"path_data" => "data/",
		"path_mods" => "mods/",
		"path_res" => "res/",
		"path_drive" => "/",
		"salt" => "",
		"apache_xsendfile" => false,
		"lighty_sendfile" => true,
		"direct_file" => false,
		"node_expiry" => 30,
		"node_cdn" => "https://aftermirror.com/cdn/",
		"node_root" => "https://aftermirror.com/",
		"node_url" => "http://haiku.aftermirror.com:182",
		"storage_root" => "http://horizon.aftermirror.com/",
		"prism_root" => "http://horizon.aftermirror.com/prism/",
		"file_head" => strtr(__DIR__, array("\\" => "/")) . "/_head.php",
		"file_foot" => strtr(__DIR__, array("\\" => "/")) . "/_foot.php"
	);
	if ($_SERVER["REQUEST_SCHEME"] !== "https") {
		//$_sys["path_cdn"] = "http://cdn.aftermirror.com/cdn/"; // only use this in production, not dev.
	}
}
else {
	$_sys = array(
		"os" => "windows",
		"path_root" => "/",
		"path_bin" => "bin/",
		"path_cdn" => "cdn/",
		"path_data" => "data/",
		"path_mods" => "mods/",
		"path_res" => "res/",
		"path_drive" => "C:",
		"salt" => "",
		"apache_xsendfile" => false,
		"lighty_sendfile" => false,
		"direct_file" => true,
		"node_expiry" => 3000,
		"node_cdn" => "http://local.aftermirror.com/cdn/",
		"node_root" => "http://local.aftermirror.com/",
		"node_url" => "http://local.aftermirror.com:182",
		"storage_root" => "http://local.aftermirror.com/",
		"prism_root" => "data/files/",
		"file_head" => strtr(__DIR__, array("\\" => "/")) . "/_head.php",
		"file_foot" => strtr(__DIR__, array("\\" => "/")) . "/_foot.php"
	);
}

?>
