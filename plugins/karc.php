<?php
# Karc

class Karc {

	# Karc - two file DB, Maximum DB @ 2 GB.
	
	var $karc_data_file = false; # datafile + .karc
	var $karc_meta_file = false; # datafile +.karc.kdat
	var $karc_loaded = false;
	
	var $karc_size = 0;
	
	var $karc_tree = array();
	
	var $karc_log = array(); # we need logs!
	var $karc_buffer = 8192; #16384; #8192;
	
	var $karc_compress = false; # manually change this flag.
	# compress flag will automatically be enabled if fext of karc is xk/xkarc
	var $karc_compressLevel = 9; # 0 = none, 1 = min, 9 = max
	
	function __construct($karc) {
		# $this->karc_log[] = "constructing karc ({$karc})";
		# $this->karc_log[] = "karc_data_file={$karc}";
		# $this->karc_log[] = "karc_meta_file={$karc}.kdat";
		$this->karc_data_file = $karc;
		if (fext($karc) === "xk") {
			$this->karc_meta_file = $karc . ".d";
		}
		elseif (file_exists($this->karc_meta_file . ".kdat")) {
			# legacy
			$this->karc_meta_file = $karc . ".kdat";
		}
		else {
			# looks cleaner
			$this->karc_meta_file = $karc . ".d";
		}
		# compress-fext is detected in reload()
		$this->reload();
	}
	
	function reload() {
		# $this->karc_log[] = "calling reload();";
		if (fext($this->karc_data_file) === "xk" || fext($this->karc_data_file) === "xkarc") {
			$this->karc_compress = true;
		}
		if (file_exists($this->karc_data_file) && file_exists($this->karc_meta_file)) {
			# sync
			# $this->karc_log[] = "file(s) exist. reading meta into karc_tree";
			$this->karc_tree = readDB($this->karc_meta_file);
		}
		$this->karc_loaded = true;
		# $this->karc_log[] = "end reload();";
	}
	
	function sync() {
		# $this->karc_log[] = "syncing tree to meta_file";
		writeDB($this->karc_meta_file, $this->karc_tree);
	}
	
	function add($file, $info) {
		# $this->karc_log[] = "adding file";
		if ($this->karc_loaded) {
			# autoswitch.
			# $this->karc_log[] = "autoswitching file...";
			$switch = -1;
			$vcompressed = false;
			$vfilesize = 0;
			if ($this->karc_compress) {
				$vcompressed = true;
			}
			else {
				if (is_array($info)) {
					if (isValueInArray("compressed", $info["flags"], false)) {
						$vcompressed = true;
					}
				}
			}
			if (@file_exists($file)) {
				# $this->karc_log[] = "id-ed as file";
				if (filesize($file) >= 26214400 && !$vcompressed) { // 25 MB, no compressed flag
					# $this->karc_log[] = "using 25MB+ method";
					# use fwrite method to prevent over memory use
					$handle = fopen($file, "rb");
					$karc_handle = fopen($this->karc_data_file, "ab");
					while(!feof($handle)) {
						fwrite($karc_handle, fread($handle, $this->karc_buffer));
					}
					fclose($handle);
					fclose($karc_handle);
					$switch = 2;
				}
				else { // ~25MB AND/OR compressed file
					# $this->karc_log[] = "using ~25MB method";
					# use native fpc/fgc.
					if ($vcompressed) {
						# on the fly compression baby :)
						# we still need the filesize though, so we will use the difference between the file before and after.
						if (file_exists($this->karc_data_file)) {
							$vfilesize = filesize($this->karc_data_file);
						}
						else {
							$vfilesize = 0;
						}
						file_put_contents($this->karc_data_file, gzcompress(file_get_contents($file), $this->karc_compressLevel), FILE_APPEND);
						clearstatcache(); # make sure it's recent ;o
						$vfilesize = filesize($this->karc_data_file) - $vfilesize;
					}
					else {
						file_put_contents($this->karc_data_file, file_get_contents($file), FILE_APPEND);
					}
					$switch = 1;
				}
			}
			else {
				# $this->karc_log[] = "id-ed as raw file";
				# raw data
				# no memory switching here, auto assumes that if you're able to call this function, input is fine.				
				if ($vcompressed) {
					# because of the (hopefully) small size, we will use memory to find the filesize.
					$vfilesize = strlen(gzcompress($file, $this->karc_compressLevel));
					file_put_contents($this->karc_data_file, gzcompress($file, $this->karc_compressLevel), FILE_APPEND);
				}
				else {
					file_put_contents($this->karc_data_file, $file, FILE_APPEND);
				}
				$switch = 0;
			}
			if (is_array($info)) {
				# $this->karc_log[] = "info is sent as array, parsing...";
				# parse $info
				$n = $info;
				# $info = [filename, + extras] (others are auto-generated)
				if (!isset($n["flags"])) {
					$n["flags"] = array("active");
				}
				if ($vcompressed && !isValueInArray("compressed", $n["flags"], false)) {
					# auto add compressed flag
					$n["flags"][] = "compressed";
				}
			}
			else {
				# $this->karc_log[] = "info is sent as basename (filename).";
				# assume $info is the file name
				$n = array();
				$n["filename"] = $info;
				$n["flags"] = array("active");
				if ($vcompressed) {
					# auto add compressed flag
					$n["flags"][] = "compressed";
				}
			}
			if ($switch === 0) {
				# $this->karc_log[] = "adding auto-gen for raw data";
				# raw data
				$n["filesize"] = strlen($file);
				$n["_original_type"] = "raw";
				$n["hash"] = sha1($file);
			}
			else {
				# $this->karc_log[] = "adding auto-gen for file";
				# file
				$n["filesize"] = filesize($file);
				$n["_original_type"] = "file";
				$n["hash"] = sha1_file($file);
			}
			if ($vcompressed) {
				# fix around filesizes.
				$n["r_filesize"] = $n["filesize"];
				$n["filesize"] = $vfilesize;
			}
			$uuid = sha1(uniqid()) . ".uuid";
			# $this->karc_log[] = "uuid set={$uuid}";
			$n["uuid"] = $uuid; # .uuid is completely optional.
			$n["creation_time"] = time();
			$n["read_start"] = filesize($this->karc_data_file) - $n["filesize"]; # do not cache this.
			$this->karc_tree[$uuid] = $n;
			$this->sync();
			# $this->karc_log[] = "added.";
		}
		else {
			# not built yet.
			# $this->karc_log[] = "karc not loaded yet.";
		}
	}
	
	function isCompressed($uuid) {
		if ($this->karc_compress || isValueInArray("compressed", $this->karc_tree[$uuid]["flags"], false)) {
			return true;
		}
		return false;
	}
	
	function read($uuid) {
		# get pointer, length, repeat.
		# can we go 1 line?!
		# $this->karc_log[] = "reading uuid {$uuid}...";
			# compress mode
		if ($this->isCompressed($uuid)) {
			return gzuncompress(file_read_contents($this->karc_data_file, $this->karc_tree[$uuid]["read_start"], $this->karc_tree[$uuid]["filesize"]));
		}
		else {
			return file_read_contents($this->karc_data_file, $this->karc_tree[$uuid]["read_start"], $this->karc_tree[$uuid]["filesize"]);
		}
	}
	
	function stream($uuid) {
		# fun stuff.
		# i don't think we can stream with compression enabled. sorry
		if ($this->isCompressed($uuid)) {
			echo $this->read($uuid);
		}
		$handle = fopen($this->karc_data_file, "rb");
		fseek($handle, $this->karc_tree[$uuid]["read_start"]);
		$curbit = ftell($handle);
		$lastbit = $curbit + $this->karc_tree[$uuid]["filesize"];
		$finished = false;
		while (($curbit < $lastbit) && !$finished) {
			$dif = $lastbit - $curbit;
			if ($dif > 0) {
				if ($dif > $this->karc_buffer) {
					echo fread($handle, $this->karc_buffer);
					$curbit += $this->karc_buffer;
				}
				else {
					echo fread($handle, $dif);
					$curbit += $dif;
					$finished = true;
				}
			}
			else {
				$finished = true;
			}
		}
	}
	
	function rem($uuid, $perma_rem = false) {
		# $this->karc_log[] = "removing uuid...";
		if ($perma_rem) {
			# $this->karc_log[] = "using perma-rem method **";
			# permanently remove file, rebuild() afterwards
			# ridiculous resyncing.
			# get uuid's length & start first.
			$floor = $this->karc_tree[$uuid]["read_start"];
			$ceil = $this->karc_tree[$uuid]["filesize"];
			# $this->karc_log[] = "floor: {$floor}, ceil: {$ceil}";
			# then loop through tree, shift all post-uuid files by filesize.
			# $this->karc_log[] = "looping through tree";
			foreach ($this->karc_tree as $ref_uuid => $ref_data) {
				# $this->karc_log[] = "uuid: {$ref_uuid}, start@{$ref_data[read_start]}";
				if ($ref_data["read_start"] > $floor) {
					# affected file, shift read_start downwards.
					# $this->karc_log[] = "shifting read_start**";
					$this->karc_tree[$ref_uuid]["read_start"] = (int)($ref_data["read_start"] -= $ceil);
					# $this->karc_log[] = "new rs=" . $ref_data["read_start"];
				}
				else {
					# $this->karc_log[] = "shifting not required.";
				}
			}
			# okay, the pointers are fixed but now we have to remove the actual data
			# we'll have to do fopen/... for this to work fine
			# we shall add extension file to ... + .rw
			# $this->karc_log[] = "moving data to *.rw, starting file transfer";
			rename($this->karc_data_file, $this->karc_data_file . ".rw");
			$src_handle = fopen($this->karc_data_file . ".rw", "rb");
			$new_handle = fopen($this->karc_data_file, "wb"); # this should be an empty file
			$curbit = 0;
			$passed = false;
			while (!feof($src_handle)) {
				# $this->karc_log[] = "curbit@{$curbit}";
				if ($this->karc_tree[$uuid]["read_start"] < $this->karc_buffer && !$passed) {
					# $this->karc_log[] = "stepping is before first 8k bytes";
					# ridiculous. resize $stepping immediately to prevent overshooting.
					# check to make sure it's not the first byte (read_start === 0)
					if ($this->karc_tree[$uuid]["read_start"] == 0) { # using == instead of === because of int screwing
						# $this->karc_log[] = "0 byte match, insta-seek to filesize";
						# okay, 0 byte match
						# instant seek to next file (offset by filesize)
						fseek($src_handle, $this->karc_tree[$uuid]["filesize"]);
					}
					else {
						# somewhat harder.
						# copy to handle
						# $this->karc_log[] = "copying bytes 0 -> read_start";
						fwrite($new_handle, fread($src_handle, $this->karc_tree[$uuid]["read_start"]));
						# then move to next handle afterwards
						# $this->karc_log[] = "moving to next pointer (rs+fs)";
						fseek($src_handle, ($this->karc_tree[$uuid]["read_start"] + $this->karc_tree[$uuid]["filesize"])); # move to next pointer, shift of read_start + filesize
					}
					# $this->karc_log[] = "passed=true";
					$passed = true; # start shifting
				}
				# whether or not fseek is fixed or not, then 
				# we split this in order to prevent screw ups.
				if ($passed) {
					# just write to end
					# $this->karc_log[] = "already passed - writing 8k bytes.";
					fwrite($new_handle, fread($src_handle, $this->karc_buffer));
					$curbit += $this->karc_buffer;
				}
				else {
					# keep looping till we find handle.
					# make sure that next bit is not yet affected.
					# $curbit
					# $this->karc_log[] = "not yet passed.";
					if (($curbit + $this->karc_buffer) > $this->karc_tree[$uuid]["read_start"]) {
						# write match!! copy+flush
						# $this->karc_log[] = "rs is within 8k bytes. copy to start, then seek to after file";
						fwrite($new_handle, fread($src_handle, ($this->karc_tree[$uuid]["read_start"] - $curbit)));
						fseek($src_handle, ($this->karc_tree[$uuid]["read_start"] + $this->karc_tree[$uuid]["filesize"]));
						$passed = true;
						$curbit = ($this->karc_tree[$uuid]["read_start"] + $this->karc_tree[$uuid]["filesize"]);
						# $this->karc_log[] = "passed=true";
						$curbit = ($this->karc_tree[$uuid]["read_start"] + $this->karc_tree[$uuid]["filesize"]);
					}
					else {
						# $this->karc_log[] = "not passed - writing 8k bytes";
						fwrite($new_handle, fread($src_handle, $this->karc_buffer));
						$curbit += $this->karc_buffer;
					}
				}
			}
			fclose($new_handle);
			fclose($src_handle);
			unlink($this->karc_data_file . ".rw"); # the ***.rw file
			# $this->karc_log[] = "delete finished. removing {$uuid} from list";
			$this->karc_tree = pushValueFromArray($uuid, $this->karc_tree);
			# $this->karc_log[] = "closing handles, deleting reference *.rw file.";
		}
		else {
			# do not permanently remove file, remove active flag
			$this->karc_tree[$uuid]["flags"] = pushValueFromArray("active", $this->karc_tree[$uuid]["flags"], false);
			# $this->karc_log[] = "we are just removing the active flag.";
		}
		$this->sync();
		# $this->karc_log[] = "requesting sync...";
	}
	
	function find_uuid($filename) {
		# redudant, really.
		# This returns the first occurance only!!
		foreach ($this->karc_tree as $uuid => $data) {
			if ($data["filename"] === $filename) {
				# $this->karc_log[] = "uuid search for {$filename} returned {$uuid}";
				return $uuid;
			}
		}
		return false;
	}
	
	function rebuild() {
		# Only needed when perma-rem() is activated. Otherwise, "delete" merely changes file's flag to "archive".
		# $this->karc_log[] = "rebuild called. (not yet implemented)";
	}
	
}

function file_read_contents($file, $start, $length) {
	$handle = fopen($file, "rb");
	fseek($handle, $start);
	$contents = fread($handle, $length);
	fclose($handle);
	return $contents;
}
?>