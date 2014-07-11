<?php

class UpgradeUtil
{
    const UPGRADE_URL = "http://update.ibos.com.cn/upgrade/";

    /**
     * 本地代号
     * @var string 
     */
    public static $locale = "SC";
    /**
     * 升级编码
     * @var string 
     */
    public static $charset = "UTF8";

    public static function fetchUpdateFileList($upgradeInfo)
    {
        $file = PATH_ROOT . "/data/update/IBOS" . $upgradeInfo["latestversion"] . " Release[" . $upgradeInfo["latestrelease"] . "]/updatelist.tmp";
        $upgradeDataFlag = true;
        $upgradeData = @trim(file_get_contents($file));

        if (!$upgradeData) {
            $url = self::UPGRADE_URL . substr($upgradeInfo["upgradelist"], 0, -4) . strtolower("_" . self::$locale) . ".txt";
            $upgradeData = FileUtil::fileSockOpen($url);
            $upgradeDataFlag = false;
        }

        $return = array();
        $upgradeData = str_replace(array("\r\n", "\n"), array(",,", ",,"), $upgradeData);
        $upgradeDataArr = explode(",,", $upgradeData);

        foreach ($upgradeDataArr as $key => $value) {
            if (!$value) {
                continue;
            }

            $return["file"][$key] = trim(substr($value, 34));
            $return["md5"][$key] = substr($value, 0, 32);

            if (trim(substr($value, 32, 2)) != "*") {
                @unlink($file);

                return array();
            }
        }

        if (!$upgradeDataFlag) {
            FileUtil::makeDirs(dirname($file));
            $fp = fopen($file, "w");

            if (!$fp) {
                return array();
            }

            fwrite($fp, $upgradeData);
        }

        return $return;
    }

    public static function compareBasefile($upgradeFileList)
    {
        $ibosFiles = @file(Yii::getPathOfAlias("application.ibosfiles") . ".md5");

        if (!$ibosFiles) {
            return array();
        }

        $newUpgradeFileList = array();

        foreach ($upgradeFileList as $hashFile) {
            if (file_exists(PATH_ROOT . "/" . $hashFile)) {
                $newUpgradeFileList[$hashFile] = md5_file(PATH_ROOT . "/" . $hashFile);
            }
        }

        $modifyList = $showList = $searchList = array();

        foreach ($ibosFiles as $line) {
            $file = trim(substr($line, 34));
            $md5DataNew[$file] = substr($line, 0, 32);

            if (isset($newUpgradeFileList[$file])) {
                if ($md5DataNew[$file] != $newUpgradeFileList[$file]) {
                    $modifyList[$file] = $file;
                } else {
                    $showList[$file] = $file;
                }
            }
        }

        return array($modifyList, $showList);
    }

    public static function checkUpgrade()
    {
        $return = false;
        $ibosRelease = VERSION_DATE;
        $upgradeFile = self::UPGRADE_URL . self::getVersionPath() . "/" . $ibosRelease . "/upgrade.xml";
        $remoteResponse = FileUtil::fileSockOpen($upgradeFile);
        $response = XmlUtil::xmlToArray($remoteResponse);
        if (isset($response["cross"]) || isset($response["patch"])) {
            Setting::model()->updateSettingValueByKey("upgrade", $response);
            CacheUtil::update("setting");
            $return = true;
        } else {
            Setting::model()->updateSettingValueByKey("upgrade", "");
            $return = false;
        }

        return $return;
    }

    public static function getVersionPath()
    {
        list($version) = explode(" ", VERSION);
        return $version;
    }

    public static function compareFileContent($file, $remoteFile)
    {
        if (!preg_match("/\.php$/i", $file)) {
            return false;
        }

        $content = preg_replace("/\s/", "", file_get_contents($file));
        $ctx = stream_context_create(array( "http" => array("timeout" => 60) ));
        $remotecontent = preg_replace("/\s/", "", file_get_contents($remoteFile, false, $ctx));

        if (strcmp($content, $remotecontent)) {
            return false;
        } else {
            return true;
        }
    }

    public static function downloadFile($upgradeInfo, $file, $folder = "upload", $md5 = "", $position = 0, $offset = 0)
    {
        $dir = PATH_ROOT . "/data/update/IBOS" . $upgradeInfo["latestversion"] . " Release[" . $upgradeInfo["latestrelease"] . "]/";
        FileUtil::makeDirs(dirname($dir . $file));
        $downloadFileFlag = true;

        if (!$position) {
            $mode = "wb";
        } else {
            $mode = "ab";
        }

        $fp = fopen($dir . $file, $mode);

        if (!$fp) {
            return false;
        }

        $tempUploadFileUrl = self::UPGRADE_URL . $upgradeInfo["latestversion"] . "/" . $upgradeInfo["latestrelease"] . "/" . self::$locale . "/" . $folder . "/" . $file . ".sc";
        $response = FileUtil::fileSockOpen($tempUploadFileUrl, $offset, "", "", false, "", 15, true, "URLENCODE", false, $position);

        if ($response) {
            if ($offset && (strlen($response) == $offset)) {
                $downloadFileFlag = false;
            }

            fwrite($fp, $response);
        }

        fclose($fp);

        if ($downloadFileFlag) {
            $compare = md5_file($dir . $file);

            if ($compare == $md5) {
                return 2;
            } else {
                return 0;
            }
        } else {
            return 1;
        }
    }

    public static function copyFile($srcFile, $desFile, $type)
    {
        if (!is_file($srcFile)) {
            return false;
        }

        if ($type == "file") {
            FileUtil::makeDirs(dirname($desFile));
            copy($srcFile, $desFile);
        } elseif ($type == "ftp") {
            $ftpConf = EnvUtil::getRequest("ftp");
            $ftpConf["on"] = 1;
            $ftpConf["password"] = StringUtil::authcode($ftpConf["password"], "ENCODE", md5(Yii::app()->setting->get("config/security/authkey")));
            $ftp = FtpUtil::getInstance($ftpConf);
            $ftp->connect();
            $ftp->upload($srcFile, $desFile);

            if ($ftp->error()) {
                return false;
            }
        }

        return true;
    }

    public static function getStepName($step)
    {
        $stepNameArr = array("1" => Ibos::lang("Upgrade get file"), "2" => Ibos::lang("Upgrade download"), "3" => Ibos::lang("Upgrade compare"), "4" => Ibos::lang("Upgradeing"), "dbupdate" => Ibos::lang("Upgrade db"));
        return $stepNameArr[$step];
    }

    public static function recordStep($step)
    {
        $upgradeStep = Cache::model()->fetchByPk("upgrade_step");
        if (!empty($upgradeStep["cachevalue"]) && !empty($upgradeStep["cachevalue"]["step"])) {
            $upgradeStep["cachevalue"] = unserialize($upgradeStep["cachevalue"]);
            $upgradeStep["cachevalue"]["step"] = $step;
            Cache::model()->add(array("cachekey" => "upgrade_step", "cachevalue" => serialize($upgradeStep["cachevalue"]), "dateline" => TIMESTAMP), false, true);
        }
    }
}
