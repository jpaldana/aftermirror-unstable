<?php
echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
			<h1 class='center'><span style='color: gray;'>after</span>|<span class='fxSlide'>randomify</span></h1>
			<h2 class='center' style='font-size: 14px; color: gray;'>what do you really want to do?</h2>
";

echo "
	<br />
	<form action='app.Randomify' method='get'>
		<input type='text' name='options' placeholder='options separated by ;' value='";
if (isset($_GET["options"])) echo $_GET["options"];
echo "' />
		<input type='submit' value='randomize' class='fullSize' />
	</form>
	<br />
	<br />
";

if (isset($_GET["options"]) && isSomething($_GET["options"])) {
	$opts = explode(";", $_GET["options"]);
	//shuffle($opts);
	//$do = array_pop($opts);
	$opts = implode("','", $opts);
	echo "
		<span>You should...</span>
		<br />
		<div class='center'>
			<b style='font-size: 48px;' id='output'>--</b>
		</div>
		<br />
		<span class='fxButton' id='respin'>spin again?</span>
		<script>
		
		var active = false;
		var opts = ['{$opts}'];
		
		$('#respin').on('click', function() {
			if (!active) {
				var i;
				$('#output').css('font-weight', 'normal');
				for (i = 0; i < 10; i++) {
					setTimeout('ref()', i * 500);
				}
				setTimeout('finalize()', i * 500);
			}
		});
		
		function ref() {
			var r = Math.floor(Math.random() * opts.length);
			$('#output').fadeOut(0).text(opts[r]).fadeIn(250);
		}
		function finalize() {
			$('#output').css('font-weight', 'bold');
			active = false;
		}
		
		</script>";
}

echo "
			</div>
		</div>
	</div>
";
?>
