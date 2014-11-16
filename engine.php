<?php
// semi standalone engine

// 0. core
function print_a($a) {
	echo "<pre>"; print_r($a); echo "</pre>";
}
function isSomething(&$obj) {
	if (!isset($obj)) return false;
	if ($obj === NULL) return false;
	if ($obj === "") return false;
	if (is_array($obj) && count($obj) === 0) return false;
	if (!$obj) return false;
	return true;
}

// 1. i/o
function dir_fix($file) {
	return strtr($file, array("http://" => "http://", "https://" => "https://", "//" => "/"));
}
function dir_get($directory, $filter = false) {
	$r = array_values(array_diff(scandir($directory), array(".", "..", "Thumbs.db")));
	for ($i = 0; $i < count($r); $i++) {
		if (isSomething($filter) && strc($r[$i], $filter)) {
			$r[$i] = null;
			continue;
		}
		$r[$i] = dir_fix($directory . "/" . $r[$i]);
		if (is_dir($r[$i])) {
			$r[$i] .= "/";
		}
	}
	natsort($r);
	$r = array_values($r);
	$r = array_filter($r, "isSomething");
	return $r;
}
function dir_get_recursive($directory, $filter = false) {
	$r = dir_get($directory, $filter);
	foreach ($r as $file) {
		if (is_dir($file)) {
			$r = array_merge($r, dir_get_recursive($file));
		}
	}
	natsort($r);
	$r = array_values($r);
	return $r;
}
function fext($file) {
	$a = strtolower(substr($file, strripos($file, ".") + 1));
	if (strc($a, "?")) {
		$a = substr($a, 0, stripos($a, "?"));
	}
	if (strc($a, "#")) {
		$a = substr($a, 0, stripos($a, "#"));
	}
	return $a;
}
function fextIsVideo($fext) {
	if (strc($fext, ".")) {
		$fext = fext($fext);
	}
	switch($fext) {
		case "mkv":
		case "avi":
		case "mp4":
		case "m4v":
		case "mov":
		case "3gp":
		case "flv":
		case "wmv":
		case "mpg":
		case "webm":
			return true;
		break;
	}
	return false;
}
function fextIsImage($fext) {
	if (strc($fext, ".")) {
		$fext = fext($fext);
	}
	switch($fext) {
		case "jpg":
		case "jpeg":
		case "png":
		case "bmp":
		case "gif":
			return true;
		break;
	}
	return false;
}
function fextIsHTML($fext) {
	if (strc($fext, ".")) {
		$fext = fext($fext);
	}
	switch($fext) {
		case "part":
		case "htm":
		case "html":
		case "php":
		case "css":
		case "js":
			return true;
		break;
	}
	return false;
}
function fextIsMusic($fext) {
	if (strc($fext, ".")) {
		$fext = fext($fext);
	}
	switch($fext) {
		case "mp3":
		case "wav":
		case "m4a":
		case "ogg":
			return true;
		break;
	}
	return false;
}
function fextIsText($fext) {
	if (strc($fext, ".")) {
		$fext = fext($fext);
	}
	if (fextIsHTML($fext)) {
		return true;
	}
	switch($fext) {
		case "txt":
		case "log":
			return true;
		break;
	}
	return false;
}
function basenamex($str) {
	$DL = basename($str);
	if (strripos($DL, "?") !== false) {
		$DL = substr($DL, 0, strripos($DL, "?"));
	}
	if (strripos($DL, "#") !== false) {
		$DL = substr($DL, 0, strripos($DL, "#"));
	}
	return $DL;
}
function unlinkd($dir) {
	$a = dir_get($dir);
	foreach ($a as $k) {
		unlink($k);
	}
	rmdir($dir);
}
function dir_firstImage($dir) {
	$d = dir_get($dir);
	foreach ($d as $i) {
		if (fextIsImage(fext($i)) || fextIsVideo(fext($i))) { return $i; }
	}
	foreach ($d as $f) {
		if (is_dir($f)) {
			$z = dir_firstImage($f);
			if ($z) { return $z; }
		}
	}
	return false;
}
function is_file_filled($file) {
	if (file_exists($file) && !is_dir($file) && filesize($file) > 0) {
		return true;
	}
	return false;
}

// 2. strings
function strc($haystack, $needle) {
	if (is_array($needle)) {
		foreach ($needle as $searchFor) {
			if (strc($haystack, $searchFor)) {
				return true;
			}
		}
		return false;
	}
	if (strpos($haystack, $needle) === false) { return false; }
	return true;
}
function randomStr() {
	return base_convert(mt_rand(0x19A100, 0x39AA3FF), 10, 36);
}
function cleanString($str) {
	return preg_replace("/[^(\x20-\x7F)]*/", "", $str);
}
function cleanANString($str) {
	return preg_replace("/[^a-zA-Z0-9]+/", "", $str);
}
function cleanAString($str) {
	return preg_replace("/[^a-zA-Z]+/", "", $str);
}
function gz_a2es($plainArray) {
	return gzcompress(serialize($plainArray), 5);
}
function gz_es2a($gzString) {
	return unserialize(gzuncompress($gzString));
}
function gz_s2es($plainString) {
	return gzcompress($plainString, 5);
}
function gz_es2s($gzString) {
	return gzuncompress($gzString);
}

// 3. math
function isOdd($int) {
	return ($int % 2) ? true : false;
}
function formatBytes($size) {
	if ($size === 0) {
		return "0 Bytes";
	}
	$sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
	return (round($size/pow(1024, ($i = floor(log($size, 1024)))), $i > 1 ? 2 : 0) . $sizes[$i]);
}
function res2mp($x, $y) {
	return round((($x * $y) / 1000000), 1) . " mp";
}

// 4. array
function getValueFromArray($needle, $haystack, $useKey = true) {
	if (!is_array($haystack)) { return false; }
	foreach ($haystack as $k => $v) {
		if ($useKey) {
			if ($k === $needle) { return $v; }
		}
		else {
		if ($v === $needle) { return $k; }
		}
	}
	return false;
}
function isValueInArray($needle, $haystack, $useKey = true) {
	if (!is_array($haystack)) { return false; }
	foreach ($haystack as $k => $v) {
		if ($useKey) {
			if ($k === $needle) { return true; }
		}
		else {
			if ($v === $needle) { return true; }
		}
	}
	return false;
}
function pushValueFromArray($needle, $haystack, $useKey = true) {
	if (!is_array($haystack)) { return false; }
	$r = array();
	foreach ($haystack as $k => $v) {
		if ($useKey) {
			if ($k !== $needle) { $r[$k] = $v; }
		}
		else {
			if ($v !== $needle) { $r[$k] = $v; }
		}
	}
	return $r;
}
/*
function knatsort($array) {
	if (!is_array($array)) { return false; }
	$r = array_flip($array);
	natsort($r);
	return array_flip($r);
}
*/
function knatsort(&$array){
	$array_keys = array_keys($array);
	natsort($array_keys);
	$new_natsorted_array = array();
	foreach($array_keys as $array_keys_2) {
		$new_natsorted_array[$array_keys_2] = $array[$array_keys_2];
	}
	$array = $new_natsorted_array;
	return true;
}

// 5. date
function todate($date = false, $nicer = false) {
	if ($nicer) {
		if (!$date) return date("M j, Y g:i:s A");
		return date("M j, Y g:i:s A", $date);
	}
	else {
		if (!$date) return date("m-d-y_h-i-s-A");
		return date("m-d-y_h-i-s-A", $date);
	}
}
function time_since($since, $short = false) {
	if ($short) {
		$chunks = array(
			array(60 * 60 * 24 * 365 , 'y'),
			array(60 * 60 * 24 * 30 , 'm'),
			array(60 * 60 * 24 * 7, 'w'),
			array(60 * 60 * 24 , 'd'),
			array(60 * 60 , 'h'),
			array(60 , 'm'),
			array(1 , 's')
		);
	}
	else {
		$chunks = array(
			array(60 * 60 * 24 * 365 , 'year'),
			array(60 * 60 * 24 * 30 , 'month'),
			array(60 * 60 * 24 * 7, 'week'),
			array(60 * 60 * 24 , 'day'),
			array(60 * 60 , 'hour'),
			array(60 , 'minute'),
			array(1 , 'second')
		);
	}

    for ($i = 0, $j = count($chunks); $i < $j; $i++) {
        $seconds = $chunks[$i][0];
        $name = $chunks[$i][1];
        if (($count = floor($since / $seconds)) != 0) {
            break;
        }
    }

	if ($short) {
		$print = ($count == 1) ? '1'.$name : "{$count}{$name}";
	}
	else {
		$print = ($count == 1) ? '1 '.$name : "{$count} {$name}s";
	}
    return $print;
}

// 6. http
function isValidURL($url) {
	return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}
function isValidEmail($email) {
	return preg_match("~([a-zA-Z0-9!#$%&amp;'*+-/=?^_`{|}~])@([a-zA-Z0-9-]).([a-zA-Z0-9]{2,4})~", $email);
}
function getIPAddr() {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}
function forceDL($file) {
	if (isset($file) && file_exists($file)) {
		header("Content-length: " . filesize($file));
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $file . '"');
		readfile($file);
		return true;
	}
	return false;
}

// 11. db
function readDB($db) {
	if (is_file_filled($db)) { return gz_es2a(file_get_contents($db)); }
	else { return array(); }
}
function writeDB($db, $array) {
	return file_put_contents($db, gz_a2es($array)) ? true : false;
}
function addDB($dbu, $str) {
	if (!is_file_filled($dbu)) { writeDB($dbu, array()); }
	$db = readDB($dbu);
	$db[] = $str;
	writeDB($dbu, $db);
}
function clearDB($db) {
	writeDB($db, array());
}
function appendDB($db, $key, $value) {
	$a = readDB($db);
	$a[$key] = $value;
	writeDB($db, $a);
}
function appendsubDB($db, $key, $key2, $value) {
	$a = readDB($db);
	$a[$key][$key2] = $value;
	writeDB($db, $a);
}
function countDB($db) {
	return count(readDB($db));
}

// 12. gd
function iTF($inputFileName, $fileName, $maxSize, $quality = 100) {
	$info = getimagesize($inputFileName);
	$type = isset($info['type']) ? $info['type'] : $info[2];
	$ext = strtolower(substr($fileName, strrpos($fileName, '.')));
	if (!(imagetypes() & $type)) {
		 return false;
	}
	$width = isset($info['width']) ? $info['width'] : $info[0];
	$height = isset($info['height']) ? $info['height'] : $info[1];
	$wRatio = $maxSize / $width;
	$hRatio = $maxSize / $height;
	$sourceImage = imagecreatefromstring(file_get_contents($inputFileName));
	if (($width <= $maxSize) && ($height <= $maxSize)) {
		$tHeight = $height;
		$tWidth = $width;
	}
	elseif (($wRatio * $height) < $maxSize) {
		$tHeight = ceil($wRatio * $height);
		$tWidth = $maxSize;
	}
	else {
		$tWidth = ceil($hRatio * $width);
		$tHeight = $maxSize;
	}
	$thumb = imagecreatetruecolor($tWidth, $tHeight);
	if ($sourceImage === false) {
		 return false;
	}
	imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $tWidth, $tHeight, $width, $height);
	imagedestroy($sourceImage);
	$im = $thumb;
	if (!$im || file_exists($fileName)) {
		 return false;
	}
	switch ($ext) {
		 case '.gif':
			imagegif($im, $fileName);
		break;
		case '.jpg':
		case '.jpeg':
			imagejpeg($im, $fileName, $quality);
		break;
		case '.png':
			imagepng($im, $fileName);
		break;
		case '.bmp':
			imagewbmp($im, $fileName);
		break;
		default:
			imagepng($im, $fileName);
	}
	return true;
}
function colorPalette($imageFile, $numColors = 3, $granularity = 5) {
   $granularity = max(1, abs((int)$granularity));
   $colors = array();
   $size = @getimagesize($imageFile);
   if($size === false)
   {
      user_error("Unable to get image size data");
      return false;
   }
   $img = @imagecreatefromstring(file_get_contents($imageFile));

   if(!$img)
   {
      user_error("Unable to open image file");
      return false;
   }
   for($x = 0; $x < $size[0]; $x += $granularity)
   {
      for($y = 0; $y < $size[1]; $y += $granularity)
      {
         $thisColor = imagecolorat($img, $x, $y);
         $rgb = imagecolorsforindex($img, $thisColor);
         $red = round(round(($rgb['red'] / 0x33)) * 0x33);
         $green = round(round(($rgb['green'] / 0x33)) * 0x33);
         $blue = round(round(($rgb['blue'] / 0x33)) * 0x33);
         $thisRGB = sprintf('%02X%02X%02X', $red, $green, $blue);
         if(array_key_exists($thisRGB, $colors))
         {
            $colors[$thisRGB]++;
         }
         else
         {
            $colors[$thisRGB] = 1;
         }
      }
   }
   arsort($colors);
   return array_slice(array_keys($colors), 0, $numColors);
}
function colorDominant($image) {
	$i = imagecreatefromstring($image);

	for ($x=0;$x<imagesx($i);$x++) {
		for ($y=0;$y<imagesy($i);$y++) {
			$rgb = imagecolorat($i,$x,$y);
			$r   = ($rgb >> 16) & 0xFF;
			$g   = ($rgb >> 16) & 0xFF;
			$b   = $rgb & 0xFF;

			$rTotal += $r;
			$gTotal += $g;
			$bTotal += $b;
			$total++;
		}
	}

	$rAverage = round($rTotal/$total);
	$gAverage = round($gTotal/$total);
	$bAverage = round($bTotal/$total);
	return rgb2html($rAverage,$gAverage,$bAverage);
}
function rgb2html($r, $g=-1, $b=-1) {
    if (is_array($r) && sizeof($r) == 3)
        list($r, $g, $b) = $r;

    $r = intval($r); $g = intval($g);
    $b = intval($b);

    $r = dechex($r<0?0:($r>255?255:$r));
    $g = dechex($g<0?0:($g>255?255:$g));
    $b = dechex($b<0?0:($b>255?255:$b));

    $color = (strlen($r) < 2?'0':'').$r;
    $color .= (strlen($g) < 2?'0':'').$g;
    $color .= (strlen($b) < 2?'0':'').$b;
    return '#'.$color;
}
function html2rgb($color) {
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
}

// 13. gd-imagingfunctions-extended
function imgWatermarkText($image, $text) {
	# works with either image file, or raw image data (e.g. from karc)
	if (strlen($image) > 1024 || !file_exists($image)) {
		# okay, not a file
		$im = @imagecreatefromstring($image);
		if (!$im) {
			imagecreatefromjpg($image);
		}
	}
	else {
		$im = @imagecreatefromstring(file_get_contents($image));
	}
	imagestring($im, 4, 5, 5, $text, imagecolorallocate($im, 255, 255, 255));
	imagestring($im, 4, 6, 6, $text, imagecolorallocate($im, 0, 0, 0));
	return imagepng($im);
}

// 30. io-extended
function cleanFilename($str) {
	return preg_replace("[^\w\s\d\.\-_~,;:\[\]\(\]]", '_', $str);
}

// 31. strings-extended_html
function strip_html_tags($text) {
	$text = preg_replace(
		array(
			'@<head[^>]*.*?</head>@siu',
			'@<style[^>]*.*?</style>@siu',
			'@<script[^>]*?.*?</script>@siu',
			'@<object[^>]*?.*?</object>@siu',
			'@<embed[^>]*?.*?</embed>@siu',
			'@<applet[^>]*?.*?</applet>@siu',
			'@<noframes[^>]*?.*?</noframes>@siu',
			'@<noscript[^>]*?.*?</noscript>@siu',
			'@<noembed[^>]*?.*?</noembed>@siu',
			'@</?((address)|(blockquote)|(center)|(del))@iu',
			'@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
			'@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
			'@</?((table)|(th)|(td)|(caption))@iu',
			'@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
			'@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
			'@</?((frameset)|(frame)|(iframe))@iu',
		),
		array(
			' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
			"\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
			"\n\$0", "\n\$0",
		),
		$text);
	return strip_tags($text);
}
function uniquehtmlwords($text) {
	$a = strip_html_tags($text);
	$a = strtr($a, array("\n" => " "));
	$a = explode(" ", $a);
	$b = array();
	$g = "";
	foreach ($a as $c) {
	$c = strtolower(cleanAString($c));
		if (isSomething($c) && strlen($c) > 3 && !strc($c, "http")) {
			if (!strc($g, $c)) {
				$b[] = trim($c);
				$g .= $c;
			}
		}
	}
	$b = array_unique($b);
	return implode(" ", $b);
}
function makeClickableLinks($text) {  
	$text = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_+.~#?&//=]+)', '<a href="\1">\1</a>', $text);  
	$text = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_+.~#?&//=]+)', '\1<a href="http://\2">\2</a>', $text);  
	$text = eregi_replace('([_.0-9a-z-]+@([0-9a-z][0-9a-z-]+.)+[a-z]{2,3})', '<a href="mailto:\1">\1</a>', $text);  
	return $text;  
}

if (is_dir("plugins")) {
	foreach (dir_get("plugins") as $file) {
		if (strc($file, ".php")) include($file);
	}
}
?>
