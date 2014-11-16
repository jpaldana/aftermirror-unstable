<?php
	header("strict-transport-security: max-age=0");
	header("Location: {$_GET['url']}");
?>
