<?php

class ICUpgrade
{
    /**
     * 更新xml文件信息来由网址
     * @var string 
     */
    private $upgradeurl = "http://update.ibos.com.cn/upgrade/";
    /**
     * 扩展名
     * @var string 
     */
    private $locale = "SC";
    /**
     * 字符编码
     * @var string 
     */
    private $charset = "UTF8";

    public function fetchUpdateFileList($upgradeInfo)
    {
        $version = "ibos" . $upgradeInfo["latestversion"] . " Release[" . $upgradeInfo["latestrelease"] . "]";
        $file = PATH_ROOT . "./data/update/" . $version . "/updatelist.tmp";
        $upgradeDataFlag = true;
        $upgradeData = @file_get_contents($file);

        if (!$upgradeData) {
            $_txtFile = $this->upgradeurl . substr($upgradeInfo["upgradelist"], 0, -4) . strtolower("_" . $this->locale) . ".txt";
            $upgradeData = FileUtil::fSockOpen($_txtFile);
            $upgradeDataFlag = false;
        }

        $return = array();
        $upgradeDataArr = explode("\r\n", $upgradeData);

        foreach ($upgradeDataArr as $k => $v) {
            if (!$v) {
                continue;
            }

            $return["file"][$k] = trim(substr($v, 34));
            $return["md5"][$k] = substr($v, 0, 32);

            if (trim(substr($v, 32, 2)) != "*") {
                @unlink($file);

                return array();
            }
        }

        if (!$upgradeDataFlag) {
            $this->mkdirs(dirname($file));
            $fp = fopen($file, "w");

            if (!$fp) {
                return array();
            }

            fwrite($fp, $upgradeData);
        }

        return $return;
    }

    public function compareBaseFile($upgradeInfo, $upgradeFileList)
    {
        if (!$ibosFiles = @file("./source/admincp/ibosfiles.md5")) {
            return array();
        }

        $newUpgradeFileList = array();

        foreach ($upgradeFileList as $v) {
            $newUpgradeFileList[$v] = md5_file(PATH_ROOT . "./" . $v);
        }

        $modifyList = $showList = $searchList = array();

        foreach ($ibosFiles as $line) {
            $file = trim(substr($line, 34));
            $md5DataNew[$file] = substr($line, 0, 32);

            if (isset($newUpgradeFileList[$file])) {
                if ($md5DataNew[$file] != $newUpgradeFileList[$file]) {
                    if (!$upgradeInfo["isupdatetemplate"] && preg_match("/\.htm$/i", $file)) {
                        $ignoreList[$file] = $file;
                        $searchList[] = "\r\n" . $file;
                        continue;
                    }

                    $modifyList[$file] = $file;
                } else {
                    $showList[$file] = $file;
                }
            }
        }

        if ($searchList) {
            $version = "ibos" . $upgradeInfo["latestversion"] . " Release[" . $upgradeInfo["latestrelease"] . "]";
            $file = PATH_ROOT . "./data/update/" . $version . "/updatelist.tmp";
            $upgradeData = file_get_contents($file);
            $upgradeData = str_replace($searchList, "", $upgradeData);
            $fp = fopen($file, "w");

            if ($fp) {
                fwrite($fp, $upgradeData);
            }
        }

        return array($modifyList, $showList, $ignoreList);
    }

    public function compareFileContent($file, $remoteFile)
    {
        if (!preg_match("/\.php$|\.htm$/i", $file)) {
            return false;
        }

        $content = preg_replace("/\s/", "", file_get_contents($file));
        $ctx = stream_context_create(array( "http" => array("timeout" => 60) ));
        $remoteContent = preg_replace("/\s/", "", file_get_contents($remoteFile, false, $ctx));

        if (strcmp($content, $remoteContent)) {
            return false;
        } else {
            return true;
        }
    }

    public function checkUpgrade()
    {
        $return = false;
        $upgradeFile = $this->upgradeurl . $this->versionPath() . "/" . IBOS_RELEASE . "/upgrade.xml";
        $xmlContents = FileUtil::fileSockOpen($upgradeFile);
        $response = XmlUtil::xmlToArray($xmlContents);
        if (isset($response["cross"]) || isset($response["patch"])) {
            Setting::model()->updateByPk("upgrade", array("value" => serialize($response)));
            $return = true;
        } else {
            Setting::model()->updateByPk("upgrade", array("value" => ""));
            $return = false;
        }

        $setting = Yii::app()->setting->get("setting");
        $setting["upgrade"] = (isset($response["cross"]) || isset($response["patch"]) ? $response : array());
        Yii::app()->setting->set("setting", $setting);
        return $return;
    }

    public function checkFolderPerm($updateFileList)
    {
        foreach ($updateFileList as $file) {
            if (!file_exists(PATH_ROOT . "/" . $file)) {
                if (!$this->testWritAble(dirname(PATH_ROOT . "/" . $file))) {
                    return false;
                }
            } elseif (!is_writable(PATH_ROOT . "/" . $file)) {
                return false;
            }
        }

        return true;
    }

    public function testWritAble($dir)
    {
        $writeable = 0;
        $this->mkdirs($dir);

        if (is_dir($dir)) {
            if ($fp = @fopen("$dir/test.txt", "w")) {
                @fclose($fp);
                @unlink("$dir/test.txt");
                $writeable = 1;
            } else {
                $writeable = 0;
            }
        }

        return $writeable;
    }

    public function downloadFile($upgradeInfo, $file, $folder = "upload", $md5 = "", $position = 0, $offset = 0)
    {
        $dir = PATH_ROOT . "./data/update/IBOS" . $upgradeInfo["latestversion"] . " Release[" . $upgradeInfo["latestrelease"] . "]/";
        $this->mkdirs(dirname($dir . $file));
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

        $_uploadFileUrl = $this->upgradeurl . $upgradeInfo["latestversion"] . "/" . $upgradeInfo["latestrelease"] . "/" . $this->locale . "/" . $folder . "/" . $file . ".sc";
        $response = FileUtil::fSockOpen($_uploadFileUrl, $offset, "", "", false, "", 15, true, "URLENCODE", false, $position);

        if ($response) {
            if ($offset && (strlen($response) == $offset)) {
                $downloadFileFlag = false;
            }

            fwrite($fp, $response);
        }

        fclose($fp);

        if ($downloadFileFlag) {
            if (md5_file($dir . $file) == $md5) {
                return 2;
            } else {
                return 0;
            }
        } else {
            return 1;
        }
    }

    public function mkdirs($dir)
    {
        if (!is_dir($dir)) {
            if (!self::mkdirs(dirname($dir))) {
                return false;
            }

            if (!@mkdir($dir, 511)) {
                return false;
            }

            @touch($dir . "/index.htm");
            @chmod($dir . "/index.htm", 511);
        }

        return true;
    }

    public function copyFile($srcFile, $desFile, $type)
    {
        $_G = Yii::app()->setting->toArray();

        if (!is_file($srcFile)) {
            return false;
        }

        if ($type == "file") {
            $this->mkdirs(dirname($desFile));
            copy($srcFile, $desFile);
        } else if ($type == "ftp") {
            $siteFtp = $_GET["siteftp"];
            $siteFtp["on"] = 1;
            $autoKey = md5($_G["config"]["security"]["authkey"]);
            $siteFtp["password"] = StringUtil::authCode($siteFtp["password"], "ENCODE", $autoKey);
            $ftp = &FtpUtil::instance($siteFtp);
            $ftp->connect();
            $ftp->upload($srcFile, $desFile);

            if ($ftp->error()) {
                return false;
            }
        }

        return true;
    }

    public function versionPath()
    {
        $versionPath = "";

        foreach (explode(" ", IBOS_VERSION) as $unit) {
            $versionPath = $unit;
            break;
        }

        return $versionPath;
    }

    public function copyDir($srcDir, $destDir)
    {
        $dir = @opendir($srcDir);

        while ($entry = @readdir($dir)) {
            $file = $srcDir . $entry;
            if (($entry != ".") && ($entry != "..")) {
                if (is_dir($file)) {
                    self::copyDir($file . "/", $destDir . $entry . "/");
                } else {
                    self::mkdirs(dirname($destDir . $entry));
                    copy($file, $destDir . $entry);
                }
            }
        }

        closedir($dir);
    }

    public function rmdirs($srcDir)
    {
        $dir = @opendir($srcDir);

        while ($entry = @readdir($dir)) {
            $file = $srcDir . $entry;
            if (($entry != ".") && ($entry != "..")) {
                if (is_dir($file)) {
                    self::rmdirs($file . "/");
                } else {
                    @unlink($file);
                }
            }
        }

        closedir($dir);
        rmdir($srcDir);
    }
}
