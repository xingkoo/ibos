<?php

function getAvatar($uid, $size = "middle")
{
    $size = (in_array($size, array("big", "middle", "small")) ? $size : "middle");
    $uid = sprintf("%09d", abs(intval($uid)));
    $level1 = substr($uid, 0, 3);
    $level2 = substr($uid, 3, 2);
    $level3 = substr($uid, 5, 2);
    return $level1 . "/" . $level2 . "/" . $level3 . "/" . substr($uid, -2) . "_avatar_$size.jpg";
}

error_reporting(0);
$uid = (isset($_GET["uid"]) ? $_GET["uid"] : 0);
$size = (isset($_GET["size"]) ? $_GET["size"] : "");
$random = (isset($_GET["random"]) ? $_GET["random"] : "");
$engine = (isset($_GET["engine"]) ? strtolower($_GET["engine"]) : "");

if (!in_array($engine, array("local", "sae"))) {
    $engine = "local";
}

$avatar = getavatar($uid, $size);

if ($engine == "local") {
    $path = "./data/avatar/";
    $fileExists = file_exists($path . $avatar);
} else {
    require_once ("./system/extensions/enginedriver/sae/SAEFile.php");
    $file = new SAEFile();
    $path = $file->fileName("data/avatar/");
    $fileExists = $file->fileExists($path . $avatar);
}

if ($fileExists) {
    $random = (!empty($random) ? rand(1000, 9999) : "");
    $avatarUrl = (empty($random) ? $path . $avatar : $path . $avatar . "?random=" . $random);
} else {
    $size = (in_array($size, array("big", "middle", "small")) ? $size : "middle");
    $avatarUrl = $path . "noavatar_" . $size . ".png";
}

if (empty($random)) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Last-Modified:" . date("r"));
    header("Expires: " . date("r", time() + 86400));
}

header("Location: " . $avatarUrl);
exit();
