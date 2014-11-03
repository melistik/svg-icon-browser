<?php
/*
 * author: 	mpriess
 * date:	2013-08-26
 * purpose:	recolores and resizes the selected icons and return the url to the download-zip
 */
session_start();
require_once ("functions.php");

$json = null;

if (!empty($_POST["data"]))
	$json = $_POST["data"];

// only for testing purpose..
$json = '{"color":"#000000","sizes":["svg","16","64","24","128","32","256","56","512"],"data":[{"dir":"Picons Basic 1 (2013)","name":"battery_1.svg"},{"dir":"Picons Basic 1 (2013)","name":"alert_error1.svg"},{"dir":"Picons Basic 1 (2013)","name":"alert_error2.svg"}]}';

if ($json == null)
	die("no data were passed");

$jsonArray = json_decode($json);

$color = $jsonArray -> color;
$sizes = $jsonArray -> sizes;
$icons = $jsonArray -> data;

resetTmp($sizes);

foreach ($icons as $icon) {

	set_time_limit(5);

	if (in_array("svg", $sizes)) {
		$fh = fopen("tmp/" . session_id() . "/svg/" . ($icon -> name), 'w') or die("can't open file");
		fwrite($fh, RecolorImage("./icons/" . ($icon -> dir) . "/" . ($icon -> name), $color));
		fclose($fh);
	}
	foreach ($sizes as $size) {
		if ($size != "svg") {
			ConvertSvg2Png(RecolorImage("./icons/" . ($icon -> dir) . "/" . ($icon -> name), $color, $size >= 64 ?  $size : 56), $icon -> name, $size);
		}
	}
}

// zipping takes a bit more time
set_time_limit(30);
$zipName = "tmp/Icons-" . date("YmdHis") . ".zip";

//Zip("./tmp/".session_id(), $zipName);
// faster then php-way only works on installed zip-command-tool
exec('cd ./tmp/' . session_id() . '/ && zip -r ../../' . $zipName . ' ./');

echo "{\"url\": \"$zipName\", \"sessionId\": \"" . session_id() . "\"}";

deleteDirectory("./tmp/" . session_id());

/*
 * when sent directly via this script but jquery wants the response a bit different

 * header('Content-Type: "application/octet-stream"');
 * header('Content-Disposition: attachment; filename="' . basename($zipName) . '"');
 * header("Content-Transfer-Encoding: binary");
 * header('Expires: 0');
 * header('Pragma: no-cache');
 * header("Content-Length: " . filesize($zipName));
 * $data = readfile($zipName);
 * exit($data);
 */
?>