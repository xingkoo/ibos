<?php

class WbCommonUtil
{
    public static function getThumbImageUrl($attach, $width, $height)
    {
        $attachUrl = FileUtil::getAttachUrl();
        $thumbName = self::getThumbName($attach, $width, $height);
        $thumbUrl = FileUtil::fileName($thumbName);

        if (FileUtil::fileExists($thumbUrl)) {
            return $thumbName;
        } else {
            $attachment = $attach["attachment"];
            $file = $attachUrl . "/" . $attachment;
            $imgext = AttachUtil::getCommonImgExt();

            if (FileUtil::fileExists($file)) {
                $info = ImageUtil::getImageInfo(FileUtil::fileName($file));
                $infoCorrect = is_array($info) && in_array($info["type"], $imgext);
                $sizeCorrect = $infoCorrect && (($width < $info["width"]) || ($height < $info["height"]));
                if ($infoCorrect && $sizeCorrect) {
                    $returnUrl = self::makeThumb($attach, $width, $height);
                } else {
                    $returnUrl = $attachUrl . "/" . $attachment;
                }
            } else {
                $returnUrl = FileUtil::fileName($file);
            }

            return $returnUrl;
        }
    }

    public static function getThumbName($attach, $width, $height)
    {
        $attachUrl = FileUtil::getAttachUrl();
        list($module, $year, $day, $name) = explode("/", $attach["attachment"]);
        $thumbName = sprintf("%s/%s/%s/%s/%dX%d.%s", $attachUrl, $module, $year, $day, $width, $height, $name);
        return $thumbName;
    }

    public static function makeThumb($attach, $width, $height)
    {
        $attachUrl = FileUtil::getAttachUrl();
        $file = sprintf("%s/%s", $attachUrl, $attach["attachment"]);
        $fileext = StringUtil::getFileExt($file);
        $thumbName = self::getThumbName($attach, $width, $height);

        if (LOCAL) {
            $res = ImageUtil::thumb2($file, $thumbName, "", $width, $height);
        } else {
            $tempFile = FileUtil::getTempPath() . "tmp." . $fileext;
            $orgImgname = Ibos::engine()->IO()->file()->fetchTemp(FileUtil::fileName($file), $fileext);
            ImageUtil::thumb2($orgImgname, $tempFile, "", $width, $height);
            FileUtil::createFile($thumbName, file_get_contents($tempFile));
        }

        return $thumbName;
    }

    public static function isResize($imageName)
    {
        if (preg_match("/(\d*X\d*)/", $imageName)) {
            return true;
        } else {
            return false;
        }
    }

    public static function getSetting($loadcache = false)
    {
        $keys = array("wbmovement", "wbnums", "wbpostfrequency", "wbposttype", "wbwatermark", "wbwcenabled");
        $serializeKeys = array("wbmovement", "wbposttype");

        if ($loadcache) {
            $allkeys = array_merge($keys, $serializeKeys);
            $setting = Ibos::app()->setting->toArray();
            $values = array();

            foreach ($allkeys as $key) {
                $values[$key] = $setting["setting"][$key];
            }
        } else {
            $values = Setting::model()->fetchSettingValueByKeys(implode(",", $keys));

            foreach ($values as $key => &$value) {
                if (in_array($key, $serializeKeys)) {
                    $value = unserialize($value);
                }
            }
        }

        return $values;
    }

    public static function getMovementModules()
    {
        $modules = Ibos::app()->getEnabledModule();
        $movementModules = array();

        foreach ($modules as $module => $configs) {
            $config = CJSON::decode($configs["config"], true);

            if (isset($config["param"]["pushMovement"])) {
                $movementModules[] = array("module" => $module, "name" => $configs["name"]);
            }
        }

        return $movementModules;
    }
}
