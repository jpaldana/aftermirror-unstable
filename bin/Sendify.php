<?php
$shortDB = readDB($_sys["path_data"] . "db/.short-links");

function generateRandomString($length) {
	$characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$randomString = "";
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $randomString;
}

if (isset($_GET["url"])) {
	$url = $_GET["url"];
	$error = false;
	if (in_array($url, $shortDB)) {
		$rev = array_flip($shortDB);
		$bump = $rev[$url];
	}
	else {
		$shortHash = generateRandomString(5);
		$newShort = false;
		if (isSomething($_GET["request"])) {
			$len = 0;
			$bump = $_GET["request"];
			if (!isset($shortDB[$bump])) {
				$newShort = true;
			}
		}
		else {
			$len = 1;
			$bump = "";
		}
		while (!$newShort) {
			$bump .= substr($shortHash, $len - 1, 1);
			if (!isset($shortDB[$bump])) {
				$newShort = true;
			}
			elseif ($len >= strlen($shortHash)) {
				$shortHash = generateRandomString(5);
				$len = 1;
				if (!isSomething($_GET["request"])) {
					$bump = "";
				}
			}
			else {
				$len++;
			}
		}
			
		if (substr($url, 0, 4) == "app." || substr($url, 0, 5) == "core.") {
			// am links.
			$shortDB[$bump] = $url;
		}
		elseif (substr($url, 0, 4) == "http" || substr($url, 0, 3) == "ftp") {
			// regular links.
			$shortDB[$bump] = $url;
		}
		else {
			$error = 1;
		}
		writeDB($_sys["path_data"] . "db/.short-links", $shortDB);
	}
	
	echo "
		<div id='content'>
			<div id='container'>
				<div class='block'>
				<div class='center'>
					<h1 class='center'><span style='color: gray;'>sendify</span>|<span class='fxSlide'>me</span></h1>
					<h2 class='center' style='font-size: 14px; color: gray;'><a href='app.Sendify' class='ajaxFriendly'>generate another link...</a></h2>
				</div>
				<h3>Original Link</h3>
				<a href='{$url}' class='buttonLink fxBackground noFlow' style='font-variant: normal;'>{$url}</a>
				<br />
				<hr />
				<br />
				<h3>sendify.me Link</h3>
	";
	if (!$error) {
		echo "
			<a href='http://sendify.me/{$bump}' class='preventDefault buttonLink fxBackground noFlow' style='background-color: #428BEB; font-size: 24px; font-variant: normal;'>http://sendify.me/{$bump}</a>
		";
	}
	else {
		echo "
			<p>Error: Invalid URL. Make sure it has http(s):// or a different proper scheme is in the URL.</p>
		";
	}
	echo "
				</div>
			</div>
		</div>
	";
}
else {
	echo "
		<div id='content'>
			<div id='container'>
				<div class='block'>
					<div class='center'>
						<h1 class='center'><span style='color: gray;'>sendify</span>|<span class='fxSlide'>me</span></h1>
						<h2 class='center' style='font-size: 14px; color: gray;'>shorten your links!<br/><br/><a href=\"javascript: document.location = 'https://aftermirror.com/app.Sendify?url=' + window.location;\" class='preventDefault fxButton'>Drag me to your bookmarks to quickly shorten links</a></h2>
					</div>
					<br />
					<hr />
					<br />
					<h3>Quick Link</h3>
					<small>Use this to shorten a URL with a quick, randomly generated sendify.me access key</small>
					<br />
					<br />
					<form action='app.Sendify' method='get'>
						<label for='url' class='textboxLabel'>URL</label>
						<input type='text' name='url' placeholder='URL' id='url' />
						<br />
						<input type='submit' value=\"Transform URL\" class='fullSize preventDefault' onclick=\"generateURL($('#url').val()); return false;\" />
					</form>
					<br />
					<hr />
					<br />
					<h3>Custom Link</h3>
					<small>Use this to shorten a URL with a custom sendify.me access key (if available)</small>
					<br />
					<br />
					<form action='app.Sendify' method='get'>
						<label for='url2' class='textboxLabel'>URL</label>
						<input type='text' name='url' placeholder='URL' id='url2' />
						<label for='request' class='textboxLabel'>Custom access key</label>
						<input type='text' name='request' placeholder='sendify.me/______' id='request' />
						<br />
						<input type='submit' value='Transform URL' class='fullSize preventDefault' onclick=\"generateURL($('#url2').val(), $('#request').val()); return false;\" />
					</form>
					<script>
						function generateURL(url, req) {
							if (req !== undefined) {
								ajaxContentLoad('app.Sendify?url=' + url + '&request=' + req);
							}
							else {
								ajaxContentLoad('app.Sendify?url=' + url);
							}
						}
					</script>
				</div>
			</div>
		</div>
	";
}

?>
