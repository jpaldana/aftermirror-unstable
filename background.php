<?php
// This is a standalone worker.
// Of course it's in beta phase.

echo <<<eof
<html>
	<head>
		<title>after|mirror - background client</title>
		<script>
			function testNotification() {
				if (!Notification) {
					alert("Your browser does not support notifications.");
					return;
				}
				if (Notification.permission !== "granted") {
					Notification.requestPermission();
				}
				
				var notification = new Notification("Title", {
					icon: "cdn/test-avatar.png",
					body: "This is a sample message!"
				});
				notification.onclick = function() {
					alert("You clicked me!");
				}
			}
		</script>
	</head>
	<body>
		<button onclick="testNotification();">test!</button>
	</body>
</html>
eof;
?>
