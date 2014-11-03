<?php
/*
 * author: 	mpriess
 * date:	2013-08-26
 * purpose:	collection of different functions found and modified...
 */

// creates as zip of a hole directory with all its content
function Zip($source, $destination) {
	if (!extension_loaded('zip') || !file_exists($source)) {
		return false;
	}

	$zip = new ZipArchive();
	if (!$zip -> open($destination, ZIPARCHIVE::CREATE)) {
		return false;
	}

	$source = str_replace('\\', '/', realpath($source));

	if (is_dir($source) === true) {
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

		foreach ($files as $file) {
			set_time_limit(30);
			$file = str_replace('\\', '/', $file);

			// Ignore "." and ".." folders
			if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
				continue;

			$file = realpath($file);

			if (is_dir($file) === true) {
				$zip -> addEmptyDir(str_replace($source . '/', '', $file . '/'));
			} else if (is_file($file) === true) {
				$zip -> addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
			}
		}
	} else if (is_file($source) === true) {
		$zip -> addFromString(basename($source), file_get_contents($source));
	}

	return $zip -> close();
}

// recolors an svg and reset's dimensions when
function RecolorImage($ImageSvgFile, $ImageColor, $dimension = false) {
	$FileContents = file_get_contents($ImageSvgFile);
	$doc = new DOMDocument();
	$doc -> loadXML($FileContents) or die('Failed to load SVG file ' . $ImageSvgFile . ' as XML.  It probably contains malformed data.');
	$SvgTags = $doc -> getElementsByTagName("svg");
	if ($dimension) {
		foreach ($SvgTags as $svg) {
			$svg -> setAttribute("width", $dimension . "px");
			$svg -> setAttribute("height", $dimension . "px");
		}
	}

	if ($ImageColor) {
		//Look at each element in the XML and add or replace it's Fill attribute to change the color.
		$PathTags = $doc -> getElementsByTagName("path");
		foreach ($PathTags as $PTag) {
			$PTag -> setAttribute('fill', $ImageColor);
			$FileContents = $doc -> saveXML($doc);
		}
		$RecTags = $doc -> getElementsByTagName("rect");
		foreach ($RecTags as $RTag) {
			$RTag -> setAttribute('fill', $ImageColor);
			$FileContents = $doc -> saveXML($doc);
		}
		$CircleTags = $doc -> getElementsByTagName("circle");
		foreach ($CircleTags as $CTag) {
			$CTag -> setAttribute('fill', $ImageColor);
			$FileContents = $doc -> saveXML($doc);
		}
		$PolygonTags = $doc -> getElementsByTagName("polygon");
		foreach ($PolygonTags as $PolTag) {
			$PolTag -> setAttribute('fill', $ImageColor);
			$FileContents = $doc -> saveXML($doc);
		}
	}
	Return $FileContents;
}

// stores the converted png in the tmp file
function ConvertSvg2Png($SvgFile, $filename, $dimension) {
	$im = new Imagick();
	$im -> setBackgroundColor(new ImagickPixel('transparent'));
	$im -> readImageBlob($SvgFile);
	/*png settings*/
	$im -> setImageFormat("png");
	$im -> resizeImage($dimension, $dimension, imagick::FILTER_LANCZOS, 1, true);
	$im -> writeImage('./tmp/' . session_id() . '/' . $dimension . '/' . str_replace(".svg", ".png", $filename));
	$im -> clear();
	$im -> destroy();
}

// used to delete the tmp-folder recursivly
function deleteDirectory($dir) {
	if (!file_exists($dir))
		return true;
	if (!is_dir($dir))
		return unlink($dir);
	foreach (scandir($dir) as $item) {
		if ($item == '.' || $item == '..')
			continue;
		if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item))
			return false;
	}
	return rmdir($dir);
}

// deletes all existing files and create plain folder structure
function resetTmp($sizeArray) {
	deleteDirectory("tmp/" . session_id());
	mkdir("tmp/" . session_id());
	foreach ($sizeArray as $size) {
		mkdir("tmp/" . session_id() . "/" . $size);
	}

	// cleans up outdated folders and files
	foreach (scandir("tmp") as $item) {
		if ($item == '.' || $item == '..') {
			continue;
		}
		$itemStats = stat("tmp/" . $item);
		if ($itemStats['mtime'] < (time() - 3600)) {
			if (is_dir("tmp/" . $item)) {
				deleteDirectory($item);
			} else {
				unlink("tmp/" . $item);
			}
		}
	}
}
?>