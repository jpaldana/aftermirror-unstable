<?php
	enforceLogin();
	if (isset($_GET["set_status"])) {
		$messengerDB = readDB(dir_fix(myDir() . ".messenger"));
		$messengerDB["status"] = $_GET["set_status"];
		writeDB(dir_fix(myDir() . ".messenger"), $messengerDB);
		echo "<h2>Status is now: {$_GET['set_status']}.</h2>";
	}
	echo "
		<div id='content'>
			<div id='container'>
				<div class='block'>
					Status updated!
				</div>
				<script>
					// change status...
					setStatus('{$_GET['set_status']}');
				</script>
			</div>
		</div>
	";
?>