<?php
if (isset($_POST["blob"])) {
	file_put_contents("file.blob", $_POST["blob"]);
}
?>