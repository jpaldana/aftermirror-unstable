<?php
include("engine.php");
$i = "data/user/subseven/.account";
writeDB($i, array(
	"username" => "subseven",
	"password" => sha1("3x_pepper4!2" . "food"),
	"name" => "Dane Hernandez",
	"registrationDate" => time(),
	"ip" => "127.0.0.1",
	"access" => 2,
	"sessionKey" => "subseven" . sha1("3x_pepper4!2" . uniqid() . "subseven"  . microtime()),
	"prev_up" => "127.0.0.1"
));
?>