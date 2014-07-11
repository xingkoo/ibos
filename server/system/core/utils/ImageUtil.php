<?php

class ImageUtil
{
    public static function getImageInfo($img)
    {
        $imageInfo = FileUtil::imageSize($img);

        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = FileUtil::fileSize($img);
            $info = array("width" => $imageInfo[0], "height" => $imageInfo[1], "type" => $imageType, "size" => $imageSize, "mime" => $imageInfo["mime"]);
            return $info;
        } else {
            return false;
        }
    }

    public static function water($source, $water, $saveName = null, $pos = 0, $alpha = 80, $quality = 100)
    {
        if (!FileUtil::fileExists($source) || !FileUtil::fileExists($water)) {
            return false;
        }

        $sInfo = self::getImageInfo($source);
        $wInfo = self::getImageInfo($water);
        if (($sInfo["width"] < $wInfo["width"]) || ($sInfo["height"] < $wInfo["height"])) {
            return false;
        }

        $sCreateFunction = "imagecreatefrom" . $sInfo["type"];
        $sImage = $sCreateFunction($source);
        $wCreateFunction = "imagecreatefrom" . $wInfo["type"];
        $wImage = $wCreateFunction($water);
        imagealphablending($wImage, true);
        list($posX, $posY) = self::getPos($sInfo, $wInfo, $pos);

        if ($wInfo["type"] == "png") {
            imagecopy($sImage, $wImage, $posX, $posY, 0, 0, $wInfo["width"], $wInfo["height"]);
        } else {
            imagealphablending($wImage, true);
            imagecopymerge($sImage, $wImage, $posX, $posY, 0, 0, $wInfo["width"], $wInfo["height"], $alpha);
        }

        $imageFun = "image" . $sInfo["type"];

        if (!$saveName) {
            $saveName = $source;
            @unlink($source);
        }

        if ($sInfo["mime"] == "image/jpeg") {
            $imageFun($sImage, $saveName, $quality);
        } else {
            $imageFun($sImage, $saveName);
        }

        imagedestroy($sImage);
        return true;
    }

    public static function thumb($image, $thumbName, $maxWidth = 200, $maxHeight = 50, $quality = 100, $type = "", $interlace = true)
    {
        $info = self::getImageInfo($image);

        if ($info !== false) {
            $srcWidth = $info["width"];
            $srcHeight = $info["height"];
            $mime = $info["mime"];
            $type = (empty($type) ? $info["type"] : $type);
            $type = strtolower($type);
            $interlace = ($interlace ? 1 : 0);
            unset($info);
            $scale = min($maxWidth / $srcWidth, $maxHeight / $srcHeight);

            if (1 <= $scale) {
                $width = $srcWidth;
                $height = $srcHeight;
            } else {
                $width = (int) $srcWidth * $scale;
                $height = (int) $srcHeight * $scale;
            }

            $createFun = "ImageCreateFrom" . ($type == "jpg" ? "jpeg" : $type);

            if (!function_exists($createFun)) {
                return false;
            }

            $srcImg = $createFun($image);
            if (($type != "gif") && function_exists("imagecreatetruecolor")) {
                $thumbImg = imagecreatetruecolor($width, $height);
            } else {
                $thumbImg = imagecreate($width, $height);
            }

            if ("png" == $type) {
                imagealphablending($thumbImg, false);
                imagesavealpha($thumbImg, true);
            } elseif ("gif" == $type) {
                $trnprt_indx = imagecolortransparent($srcImg);

                if (0 <= $trnprt_indx) {
                    $trnprt_color = imagecolorsforindex($srcImg, $trnprt_indx);
                    $trnprt_indx = imagecolorallocate($thumbImg, $trnprt_color["red"], $trnprt_color["green"], $trnprt_color["blue"]);
                    imagefill($thumbImg, 0, 0, $trnprt_indx);
                    imagecolortransparent($thumbImg, $trnprt_indx);
                }
            }

            if (function_exists("ImageCopyResampled")) {
                imagecopyresampled($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
            } else {
                imagecopyresized($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
            }

            if (("jpg" == $type) || ("jpeg" == $type)) {
                imageinterlace($thumbImg, $interlace);
            }

            $imageFunc = "image" . ($type == "jpg" ? "jpeg" : $type);

            if ($mime == "image/jpeg") {
                $imageFunc($thumbImg, $thumbName, $quality);
            } else {
                $imageFunc($thumbImg, $thumbName);
            }

            imagedestroy($thumbImg);
            imagedestroy($srcImg);
            return $thumbName;
        }

        return false;
    }

    public static function thumb2($image, $thumbname, $type = "", $maxWidth = 200, $maxHeight = 50, $interlace = true)
    {
        $info = self::getImageInfo($image);

        if ($info !== false) {
            $srcWidth = $info["width"];
            $srcHeight = $info["height"];
            $type = (empty($type) ? $info["type"] : $type);
            $type = strtolower($type);
            $interlace = ($interlace ? 1 : 0);
            unset($info);
            $scale = max($maxWidth / $srcWidth, $maxHeight / $srcHeight);

            if (($maxHeight / $srcHeight) < ($maxWidth / $srcWidth)) {
                $srcX = 0;
                $srcY = ($srcHeight - ($maxHeight / $scale)) / 2;
                $cutWidth = $srcWidth;
                $cutHeight = $maxHeight / $scale;
            } else {
                $srcX = ($srcWidth - ($maxWidth / $scale)) / 2;
                $srcY = 0;
                $cutWidth = $maxWidth / $scale;
                $cutHeight = $srcHeight;
            }

            $createFun = "ImageCreateFrom" . ($type == "jpg" ? "jpeg" : $type);
            $srcImg = $createFun($image);
            if (($type != "gif") && function_exists("imagecreatetruecolor")) {
                $thumbImg = imagecreatetruecolor($maxWidth, $maxHeight);
            } else {
                $thumbImg = imagecreate($maxWidth, $maxHeight);
            }

            if (function_exists("ImageCopyResampled")) {
                imagecopyresampled($thumbImg, $srcImg, 0, 0, $srcX, $srcY, $maxWidth, $maxHeight, $cutWidth, $cutHeight);
            } else {
                imagecopyresized($thumbImg, $srcImg, 0, 0, $srcX, $srcY, $maxWidth, $maxHeight, $cutWidth, $cutHeight);
            }

            if (("gif" == $type) || ("png" == $type)) {
                $background_color = imagecolorallocate($thumbImg, 0, 255, 0);
                imagecolortransparent($thumbImg, $background_color);
            }

            if (("jpg" == $type) || ("jpeg" == $type)) {
                imageinterlace($thumbImg, $interlace);
            }

            $imageFun = "image" . ($type == "jpg" ? "jpeg" : $type);
            $imageFun($thumbImg, $thumbname);
            imagedestroy($thumbImg);
            imagedestroy($srcImg);
            return $thumbname;
        }

        return false;
    }

    public static function waterMarkString($string, $size, $source, $saveName = null, $pos = 0, $quality = 100, $rgb = array(), $fontPath = "")
    {
        $sInfo = self::getImageInfo($source);

        switch ($sInfo["type"]) {
            case "jpg":
            case "jpeg":
                $createFun = (function_exists("imagecreatefromjpeg") ? "imagecreatefromjpeg" : "");
                $imageFunc = (function_exists("imagejpeg") ? "imagejpeg" : "");
                break;

            case "gif":
                $createFun = (function_exists("imagecreatefromgif") ? "imagecreatefromgif" : "");
                $imageFunc = (function_exists("imagegif") ? "imagegif" : "");
                break;

            case "png":
                $createFun = (function_exists("imagecreatefrompng") ? "imagecreatefrompng" : "");
                $imageFunc = (function_exists("imagepng") ? "imagepng" : "");
                break;
        }

        $im = $createFun($source);
        $angle = 0;
        $box = imagettfbbox($size, $angle, $fontPath, $string);
        $wInfo["height"] = max($box[1], $box[3]) - min($box[5], $box[7]);
        $wInfo["width"] = max($box[2], $box[4]) - min($box[0], $box[6]);
        $ax = min($box[0], $box[6]) * -1;
        $ay = min($box[5], $box[7]) * -1;
        list($posX, $posY) = self::getPos($sInfo, $wInfo, $pos);

        if ($sInfo["mime"] != "image/png") {
            $colorPhoto = imagecreatetruecolor($sInfo["width"], $sInfo["height"]);
        }

        imagealphablending($im, true);
        imagesavealpha($im, true);

        if ($sInfo["mime"] != "image/png") {
            imagecopy($colorPhoto, $im, 0, 0, 0, 0, $sInfo["width"], $sInfo["height"]);
            $im = $colorPhoto;
        }

        $color = imagecolorallocate($im, $rgb["r"], $rgb["g"], $rgb["b"]);
        imagettftext($im, $size, 0, $posX + $ax, $posY + $ay, $color, $fontPath, $string);
        clearstatcache();

        if (!$saveName) {
            $saveName = $source;
            @unlink($source);
        }

        if ($sInfo["mime"] == "image/jpeg") {
            $imageFunc($im, $saveName, $quality);
        } else {
            $imageFunc($im, $saveName);
        }
    }

    private static function getPos($sInfo, $wInfo, $pos = 9)
    {
        switch ($pos) {
            case 1:
                $posX = 5;
                $posY = 5;
                break;

            case 2:
                $posX = ($sInfo["width"] - $wInfo["width"]) / 2;
                $posY = 5;
                break;

            case 3:
                $posX = $sInfo["width"] - $wInfo["width"] - 5;
                $posY = 5;
                break;

            case 4:
                $posX = 5;
                $posY = ($sInfo["height"] - $wInfo["height"]) / 2;
                break;

            case 5:
                $posX = ($sInfo["width"] - $wInfo["width"]) / 2;
                $posY = ($sInfo["height"] - $wInfo["height"]) / 2;
                break;

            case 6:
                $posX = $sInfo["width"] - $wInfo["width"];
                $posY = ($sInfo["height"] - $wInfo["height"]) / 2;
                break;

            case 7:
                $posX = 5;
                $posY = $sInfo["height"] - $wInfo["height"] - 5;
                break;

            case 8:
                $posX = ($sInfo["width"] - $wInfo["width"]) / 2;
                $posY = $sInfo["height"] - $wInfo["height"] - 5;
                break;

            case 9:
            default:
                $posX = $sInfo["width"] - $wInfo["width"] - 5;
                $posY = $sInfo["height"] - $wInfo["height"] - 5;
                break;
        }

        return array($posX, $posY);
    }
}
