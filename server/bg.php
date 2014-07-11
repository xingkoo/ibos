<?php

function getBg($uid, $size = "small")
{
    $size = (in_array($size, array("big", "small")) ? $size : "small");
    $uid = sprintf("%09d", abs(intval($uid)));
    $level1 = substr($uid, 0, 3);
    $level2 = substr($uid, 3, 2);
    $level3 = substr($uid, 5, 2);
    return $level1 . "/" . $level2 . "/" . $level3 . "/" . substr($uid, -2) . "_bg_$size.jpg";
}

error_reporting(0);
$uid = (isset($_GET["uid"]) ? $_GET["uid"] : 0);
$size = (isset($_GET["size"]) ? $_GET["size"] : "");
$random = (isset($_GET["random"]) ? $_GET["random"] : "");
$engine = (isset($_GET["engine"]) ? strtolower($_GET["engine"]) : "");

if (!in_array($engine, array("local", "sae"))) {
    $engine = "local";
}

$bg = getbg($uid, $size);

if ($engine == "local") {
    $path = "./data/home/";
    $fileExists = file_exists($path . $bg);
} else {
    require_once ("./system/extensions/enginedriver/sae/SAEFile.php");
    $file = new SAEFile();
    $path = $file->fileName("data/home/");
    $fileExists = $file->fileExists($path . $bg);
}

if ($fileExists) {
    $random = (!empty($random) ? rand(1000, 9999) : "");
    $bgUrl = (empty($random) ? $path . $bg : $path . $bg . "?random=" . $random);
} else {
    $size = (in_array($size, array("big", "middle", "small")) ? $size : "small");
    $bgUrl = $path . "nobg_" . $size . ".jpg";
}

if (empty($random)) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Last-Modified:" . date("r"));
    header("Expires: " . date("r", time() + 86400));
}

header("Location: " . $bgUrl);
exit();
