<?php

class DashboardUpgradeController extends DashboardBaseController
{
    public function init()
    {
        parent::init();

        if (!LOCAL) {
            exit(Ibos::lang("Not compatible service", "message"));
        }
    }

    public function actionIndex()
    {
        if (EnvUtil::getRequest("op")) {
            $operation = EnvUtil::getRequest("op");
            $operations = array("checking", "patch", "showupgrade");

            if (!in_array($operation, $operations)) {
                exit();
            }

            switch ($operation) {
                case "checking":
                    $upgradeStep = Cache::model()->fetchByPk("upgrade_step");
                    $upgradeStep["cachevalue"] = unserialize($upgradeStep["cachevalue"]);
                    $isExistStep = !empty($upgradeStep["cachevalue"]) && !empty($upgradeStep["cachevalue"]["step"]);
                    if (!EnvUtil::getRequest("rechecking") && $isExistStep) {
                        $param = array("op" => $upgradeStep["cachevalue"]["operation"], "version" => $upgradeStep["cachevalue"]["version"], "locale" => $upgradeStep["cachevalue"]["locale"], "charset" => $upgradeStep["cachevalue"]["charset"], "release" => $upgradeStep["cachevalue"]["release"], "step" => $upgradeStep["cachevalue"]["step"]);
                        $data = array("url" => $this->createUrl("upgrade/index", $param), "stepName" => UpgradeUtil::getStepName($upgradeStep["cachevalue"]["step"]));
                        $this->render("upgradeContinue", array("data" => $data));
                    } else {
                        Cache::model()->deleteByPk("upgrade_step");
                        UpgradeUtil::checkUpgrade();
                        $url = $this->createUrl("upgrade/index", array("op" => "showupgrade"));
                        $this->redirect($url);
                    }

                    break;

                case "showupgrade":
                    $result = $this->processingUpgradeList();

                    if ($result["isHaveUpgrade"]) {
                        $this->render("upgradeShow", $result);
                    } else {
                        $this->render("upgradeNewest");
                    }

                    break;

                case "patch":
                    $step = EnvUtil::getRequest("step");
                    $step = (intval($step) ? $step : 1);
                    $version = trim($_GET["version"]);
                    $release = trim($_GET["release"]);
                    $locale = trim($_GET["locale"]);
                    $charset = trim($_GET["charset"]);
                    $upgradeInfo = $upgradeStep = array();
                    $upgradeStepRecord = Cache::model()->fetchByPk("upgrade_step");
                    $upgradeStep = unserialize($upgradeStepRecord["cachevalue"]);
                    $upgradeStep["step"] = (isset($upgradeStep["step"]) ? intval($upgradeStep["step"]) : $step);
                    $upgradeStep["operation"] = $operation;
                    $upgradeStep["version"] = $version;
                    $upgradeStep["release"] = $release;
                    $upgradeStep["charset"] = $charset;
                    $upgradeStep["locale"] = $locale;
                    $data = array("cachekey" => "upgrade_step", "cachevalue" => serialize($upgradeStep), "dateline" => TIMESTAMP);
                    Cache::model()->add($data, false, true);
                    $upgradeRun = Cache::model()->fetchByPk("upgrade_run");

                    if (!$upgradeRun) {
                        $upgrade = Ibos::app()->setting->get("setting/upgrade");
                        $data = array("cachekey" => "upgrade_run", "cachevalue" => serialize($upgrade), "dateline" => TIMESTAMP);
                        Cache::model()->add($data, false, true);
                        $upgradeRun = $upgrade;
                    } else {
                        $upgradeRun = unserialize($upgradeRun["cachevalue"]);
                    }

                    $param = array("op" => $operation, "version" => $version, "locale" => $locale, "charset" => $charset, "release" => $release);

                    if ($step != 5) {
                        $upgradeInfo = $this->filterRun($param, $upgradeRun);

                        if (empty($upgradeInfo)) {
                            Cache::model()->deleteByPk("upgrade_step");
                            Cache::model()->deleteByPk("upgrade_run");
                            $msg = Ibos::lang("upgrade_unknow_error", "", array("{url}" => $this->createUrl("upgrade/index", array("op" => "checking", "rechecking" => 1))));
                            $this->render("upgradeError", array("msg" => $msg));
                            exit();
                        }

                        $savePath = "/data/update/IBOS" . $upgradeInfo["latestversion"] . " Release[" . $upgradeInfo["latestrelease"] . "]";
                        $fileList = UpgradeUtil::fetchUpdateFileList($upgradeInfo);
                        $updateMd5FileList = $fileList["md5"];
                        $updateFileList = $fileList["file"];
                        $preStatus = $this->preProcessingStep($upgradeInfo, $actionUrl = $this->createUrl("upgrade/index", $param), !empty($updateFileList) ? true : false);

                        if ($preStatus["status"] < 0) {
                            $this->ajaxReturn($preStatus, "json");
                        }
                    }

                    switch ($step) {
                        case 1:
                            return $this->processingShowUpgrade($updateFileList, $param, $savePath);
                            break;

                        case 2:
                            return $this->processingDownloadFile($upgradeInfo, $updateMd5FileList, $updateFileList, $param);
                            break;

                        case 3:
                            return $this->processingCompareFile($updateFileList, $param, $savePath);
                            break;

                        case 4:
                            return $this->processingUpdateFile($upgradeInfo, $updateFileList, $upgradeStep, $param);
                            break;

                        case 5:
                            return $this->processingTempFile($param);
                            break;

                        default:
                            break;
                    }

                    break;

                default:
                    break;
            }
        } else {
            $this->render("upgradeCheckVersion");
        }
    }

    private function filterRun($param, $upgradeRun)
    {
        $upgradeInfo = array();

        if (!empty($upgradeRun)) {
            foreach ($upgradeRun as $type => $list) {
                if (($type == $param["op"]) && ($param["version"] == $list["latestversion"]) && ($param["release"] == $list["latestrelease"])) {
                    UpgradeUtil::$locale = $param["locale"];
                    UpgradeUtil::$charset = $param["charset"];
                    $upgradeInfo = $list;
                    break;
                }
            }
        }

        return $upgradeInfo;
    }

    private function preProcessingStep($upgradeInfo, $actionUrl, $fileListExists)
    {
        if (!$upgradeInfo) {
            return array("status" => -1, "msg" => Ibos::lang("Upgrade none"));
        }

        if (!$fileListExists) {
            return array("status" => -2, "msg" => Ibos::lang("Upgrade download upgradelist error"), "actionUrl" => $actionUrl);
        }

        return array("status" => 1);
    }

    private function processingUpgradeList()
    {
        $upgrades = Ibos::app()->setting->get("setting/upgrade");

        if (!$upgrades) {
            return array("isHaveUpgrade" => false, "msg" => Ibos::lang("Upgrade latest version"));
        } else {
            $upgradeStep = array("cachekey" => "upgrade_step", "cachevalue" => serialize(array("curversion" => UpgradeUtil::getVersionPath(), "currelease" => VERSION_DATE)), "dateline" => TIMESTAMP);
            Cache::model()->add($upgradeStep, false, true);
            $upgradeRow = array();
            $charset = str_replace("-", "", strtoupper(CHARSET));
            $dbVersion = Ibos::app()->db->getServerVersion();
            $locale = "";

            if ($charset == "BIG5") {
                $locale = "TC";
            } elseif ($charset == "GBK") {
                $locale = "SC";
            } elseif ($charset == "UTF8") {
                $language = Ibos::app()->getLanguage();

                if ($language == "zh_cn") {
                    $locale = "SC";
                } elseif ($language == "zh_tw") {
                    $locale = "TC";
                }
            }

            foreach ($upgrades as $type => $upgrade) {
                $unUpgrade = 0;
                if ((0 < version_compare($upgrade["phpversion"], PHP_VERSION)) || (0 < version_compare($upgrade["mysqlversion"], $dbVersion))) {
                    $unUpgrade = 1;
                }

                $baseDesc = "IBOS " . $upgrade["latestversion"] . "_" . $locale . "_" . $charset . " [" . $upgrade["latestrelease"] . "]";

                if ($unUpgrade) {
                    $this->render("upgradeError", array("msg" => Ibos::lang("Upgrade require config", "", array("phpVersion" => PHP_VERSION, "dbVersion" => $dbVersion))));
                    exit();
                } else {
                    $params = array("op" => $type, "version" => $upgrade["latestversion"], "locale" => $locale, "charset" => $charset, "release" => $upgrade["latestrelease"]);
                    $linkUrl = $this->createUrl("upgrade/index", $params);
                    $upgradeRow[] = array("desc" => $baseDesc, "upgrade" => true, "link" => $linkUrl, "upgradeDesc" => $upgrade["upgradeDesc"], "official" => $upgrade["official"]);
                }
            }

            return array("isHaveUpgrade" => true, "list" => $upgradeRow);
        }
    }

    private function processingShowUpgrade($updateFileList, $urlParam = array(), $savePath = "")
    {
        $urlParam["step"] = 2;
        $url = $this->createUrl("upgrade/index", $urlParam);
        $data = array_merge(array("actionUrl" => $url), array("list" => $updateFileList), array("savePath" => $savePath));
        $this->render("upgradeDownloadList", array("step" => 1, "data" => $data));
    }

    private function processingDownloadFile($upgradeInfo, $updateMd5FileList, $updateFileList, $urlParam)
    {
        if (EnvUtil::getRequest("downloadStart")) {
            $fileSeq = intval(EnvUtil::getRequest("fileseq"));
            $fileSeq = ($fileSeq ? $fileSeq : 1);
            $position = intval(EnvUtil::getRequest("position"));
            $position = ($position ? $position : 0);
            $offset = 100 * 1024;
            $data["step"] = 2;

            if (count($updateFileList) < $fileSeq) {
                if ($upgradeInfo["isupdatedb"]) {
                    UpgradeUtil::downloadFile($upgradeInfo, "update.php", "utils");
                }

                $data["data"] = array("IsSuccess" => true, "msg" => Ibos::lang("Upgrade download complete to compare"), "url" => $this->createUrl("upgrade/index", array_merge(array("step" => 3), $urlParam)));
                $data["step"] = 3;
                return $this->ajaxReturn($data, "json");
            } else {
                $curFile = $updateFileList[$fileSeq - 1];
                $curMd5File = $updateMd5FileList[$fileSeq - 1];
                $percent = sprintf("%2d", (100 * $fileSeq) / count($updateFileList)) . "%";
                $downloadStatus = UpgradeUtil::downloadFile($upgradeInfo, $curFile, "upload", $curMd5File, $position, $offset);

                if ($downloadStatus == 1) {
                    $data["data"] = array("IsSuccess" => true, "msg" => Ibos::lang("Upgrade downloading file", "", array("{file}" => $curFile, "{percent}" => $percent)), "url" => $this->createUrl("upgrade/index", array_merge(array("step" => 2, "fileseq" => $fileSeq, "position" => $position + $offset), $urlParam)));
                } elseif ($downloadStatus == 2) {
                    $data["data"] = array("IsSuccess" => true, "msg" => Ibos::lang("Upgrade downloading file", "", array("{file}" => $curFile, "{percent}" => $percent)), "url" => $this->createUrl("upgrade/index", array_merge(array("step" => 2, "fileseq" => $fileSeq + 1), $urlParam)));
                } else {
                    $data["data"] = array("IsSuccess" => false, "msg" => Ibos::lang("Upgrade redownload", "", array("{file}" => $curFile)), "url" => $this->createUrl("upgrade/index", array_merge(array("step" => 2, "fileseq" => $fileSeq), $urlParam)));
                }

                return $this->ajaxReturn($data, "json");
            }
        } else {
            UpgradeUtil::recordStep(2);
            $downloadUrl = $this->createUrl("upgrade/index", array_merge(array("step" => 2), $urlParam));
            $this->render("upgradeDownload", array("downloadUrl" => $downloadUrl));
        }
    }

    private function processingCompareFile($updateFileList, $urlParam, $savePath = "")
    {
        list($modifyList, $showList) = UpgradeUtil::compareBasefile($updateFileList);
        $data["step"] = 3;
        if (empty($modifyList) && empty($showList)) {
            $msg = Ibos::lang("Filecheck nofound md5file");
            $this->render("upgradeError", array("msg" => $msg));
            exit();
        } else {
            $list = array();

            foreach ($updateFileList as $file) {
                if (isset($modifyList[$file])) {
                    $list["diff"][] = $file;
                } elseif (isset($showList[$file])) {
                    $list["normal"][] = $file;
                } else {
                    $list["newfile"][] = $file;
                }
            }

            $backPath = "./data/back/IBOS" . VERSION . " Release[" . VERSION_DATE . "]";
            $data["data"]["param"] = $urlParam;
            $data["data"]["list"] = $list;
            $data["data"]["url"] = $this->createUrl("upgrade/index", array_merge(array("step" => 4), $urlParam));
            $data["data"]["forceUpgrade"] = !empty($modifyList);
            $data["data"]["msg"] = Ibos::lang("Upgrade comepare", "", array("{savePath}" => $savePath, "{backPath}" => $backPath));
        }

        UpgradeUtil::recordStep(3);
        $this->render("upgradeCompare", $data);
    }

    private function processingUpdateFile($upgradeInfo, $updateFileList, $upgradeStep, $urlParam)
    {
        if (EnvUtil::getRequest("coverStart")) {
            $data["step"] = 4;
            $confirm = EnvUtil::getRequest("confirm");
            $startUpgrade = EnvUtil::getRequest("startupgrade");

            if (!$confirm) {
                if (EnvUtil::getRequest("ftpsetting")) {
                    $param = array("step" => 4, "confirm" => "ftp");

                    if ($startUpgrade) {
                        $param["startupgrade"] = 1;
                    }

                    $data["data"]["status"] = "ftpsetup";
                    $data["data"]["url"] = $this->createUrl("upgrade/index", array_merge($param, $urlParam));
                    $this->ajaxReturn($data, "json");
                }

                if ($upgradeInfo["isupdatedb"]) {
                    $fileList = array("data/update.php");
                    $checkUpdateFileList = array_merge($fileList, $updateFileList);
                } else {
                    $checkUpdateFileList = $updateFileList;
                }

                if (FileUtil::checkFolderPerm($checkUpdateFileList)) {
                    $confirm = "file";
                } else {
                    $data["data"]["status"] = "no_access";
                    $data["data"]["msg"] = Ibos::lang("Upgrade cannot access file");
                    $data["data"]["retryUrl"] = $this->createUrl("upgrade/index", array_merge(array("step" => 4), $urlParam));
                    $data["data"]["ftpUrl"] = $this->createUrl("upgrade/index", array_merge(array("step" => 4, "ftpsetting" => 1), $urlParam));
                    $this->ajaxReturn($data, "json");
                }
            }

            $ftpParam = array();
            $ftpSetup = EnvUtil::getRequest("ftpsetup");

            if ($ftpSetup) {
                foreach ($ftpSetup as $key => $value) {
                    $ftpParam["ftp[$key]"] = $value;
                }
            }

            if (!$startUpgrade) {
                if (!EnvUtil::getRequest("backfile")) {
                    $param = array("step" => 4, "backfile" => 1, "confirm" => $confirm);
                    $data["data"]["status"] = "upgrade_backuping";
                    $data["data"]["msg"] = Ibos::lang("Upgrade backuping");
                    $data["data"]["url"] = $this->createUrl("upgrade/index", array_merge($ftpParam, $param, $urlParam));
                    $this->ajaxReturn($data, "json");
                }

                foreach ($updateFileList as $updateFile) {
                    $destFile = PATH_ROOT . "/" . $updateFile;
                    $backFile = PATH_ROOT . "/data/back/IBOS" . VERSION . " Release[" . VERSION_DATE . "]/" . $updateFile;

                    if (is_file($destFile)) {
                        if (!UpgradeUtil::copyFile($destFile, $backFile, "file")) {
                            $data["data"]["status"] = "upgrade_backup_error";
                            $data["data"]["msg"] = Ibos::lang("Upgrade backup error");
                            $this->ajaxReturn($data, "json");
                        }
                    }
                }

                $data["data"]["status"] = "upgrade_backup_complete";
                $data["data"]["msg"] = Ibos::lang("Upgrade backup complete");
                $data["data"]["url"] = $this->createUrl("upgrade/index", array_merge(array("step" => 4, "startupgrade" => 1, "confirm" => $confirm), $ftpParam, $urlParam));
                $this->ajaxReturn($data, "json");
            }

            $param = array("step" => 4, "startupgrade" => 1);
            $url = $this->createUrl("upgrade/index", array_merge($param, $urlParam, $ftpParam, array("confirm" => $confirm)));
            $ftpUrl = $this->createUrl("upgrade/index", array_merge($param, $urlParam, array("ftpsetting" => 1)));

            foreach ($updateFileList as $updateFile) {
                $srcFile = PATH_ROOT . "/data/update/IBOS" . $urlParam["version"] . " Release[" . $urlParam["release"] . "]/" . $updateFile;

                if ($confirm == "ftp") {
                    $destFile = $updateFile;
                } else {
                    $destFile = PATH_ROOT . "/" . $updateFile;
                }

                if (!UpgradeUtil::copyFile($srcFile, $destFile, $confirm)) {
                    Cache::model()->deleteByPk("upgrade_step");
                    Cache::model()->deleteByPk("upgrade_run");
                    $data["data"]["ftpUrl"] = $ftpUrl;
                    $data["data"]["retryUrl"] = $url;

                    if ($confirm == "ftp") {
                        $data["data"]["status"] = "upgrade_ftp_upload_error";
                        $data["data"]["msg"] = Ibos::lang("Upgrade ftp upload error", "", array("{file}" => $updateFile));
                    } else {
                        $data["data"]["status"] = "upgrade_copy_error";
                        $data["data"]["msg"] = Ibos::lang("Upgrade copy error", "", array("{file}" => $updateFile));
                    }

                    $this->ajaxReturn($data, "json");
                }
            }

            if ($upgradeInfo["isupdatedb"]) {
                $dbUpdateFileArr = array("update.php");

                foreach ($dbUpdateFileArr as $dbUpdateFile) {
                    $srcFile = PATH_ROOT . "/data/update/IBOS" . $urlParam["version"] . " Release[" . $urlParam["release"] . "]/" . $dbUpdateFile;
                    $dbUpdateFile = ($dbUpdateFile == "update.php" ? "data/update.php" : $dbUpdateFile);

                    if ($confirm == "ftp") {
                        $destFile = $dbUpdateFile;
                    } else {
                        $destFile = PATH_ROOT . "/" . $dbUpdateFile;
                    }

                    if (!UpgradeUtil::copyFile($srcFile, $destFile, $confirm)) {
                        $data["data"]["ftpUrl"] = $ftpUrl;
                        $data["data"]["retryUrl"] = $url;

                        if ($confirm == "ftp") {
                            $data["data"]["status"] = "upgrade_ftp_upload_error";
                            $data["data"]["msg"] = Ibos::lang("Upgrade ftp upload error", "", array("{file}" => $dbUpdateFile));
                        } else {
                            $data["data"]["status"] = "upgrade_copy_error";
                            $data["data"]["msg"] = Ibos::lang("Upgrade copy error", "", array("{file}" => $dbUpdateFile));
                        }

                        $this->ajaxReturn($data, "json");
                    }
                }

                $upgradeStep["step"] = 4;
                Cache::model()->add(array("cachekey" => "upgrade_step", "cachevalue" => serialize($upgradeStep), "dateline" => TIMESTAMP), false, true);
                $dbReturnUrl = $this->createUrl("upgrade/index", array_merge(array("step" => 5), $urlParam));
                $param = array("step" => "prepare", "from" => rawurlencode($dbReturnUrl), "frommd5" => md5(rawurlencode($dbReturnUrl) . Ibos::app()->setting->get("config/security/authkey")));
                $data["data"]["status"] = "upgrade_database";
                $data["data"]["url"] = "data/update.php?" . http_build_query($param);
                $data["data"]["msg"] = Ibos::lang("Upgrade file successful");
                $this->ajaxReturn($data, "json");
            }

            $data["data"]["status"] = "upgrade_file_successful";
            $data["data"]["url"] = $this->createUrl("upgrade/index", array_merge(array("step" => 5), $urlParam));
            $data["step"] = 5;
            $this->ajaxReturn($data, "json");
        } else {
            UpgradeUtil::recordStep(4);
            $coverUrl = $this->createUrl("upgrade/index", array_merge(array("step" => 4), $urlParam));
            $this->render("upgradeCover", array("coverUrl" => $coverUrl));
        }
    }

    private function processingTempFile($urlParam)
    {
        $file = PATH_ROOT . "/data/update/IBOS " . $urlParam["version"] . " Release[" . $urlParam["release"] . "]/updatelist.tmp";
        $authKey = Ibos::app()->setting->get("config/security/authkey");
        @unlink($file);
        @unlink(PATH_ROOT . "/data/update.php");
        Cache::model()->deleteByPk("upgrade_step");
        Cache::model()->deleteByPk("upgrade_run");
        Setting::model()->updateSettingValueByKey("upgrade", "");
        CacheUtil::update();
        $randomStr = StringUtil::random(6);
        $oldUpdateDir = "/data/update/";
        $newUpdateDir = "/data/update-" . $randomStr . "/";
        $oldBackDir = "/data/back/";
        $newBackDir = "/data/back-" . $randomStr . "/";
        FileUtil::copyDir(PATH_ROOT . $oldUpdateDir, PATH_ROOT . $newUpdateDir);
        FileUtil::copyDir(PATH_ROOT . $oldBackDir, PATH_ROOT . $newBackDir);
        FileUtil::clearDirs(PATH_ROOT . $oldUpdateDir);
        FileUtil::clearDirs(PATH_ROOT . $oldBackDir);
        $data["step"] = 5;
        $data["data"]["msg"] = Ibos::lang("Upgrade successful", "", array("{version}" => "IBOS" . VERSION . " " . VERSION_DATE, "{saveUpdateDir}" => $newUpdateDir, "{saveBackDir}" => $newBackDir));
        $this->render("upgradeSuccess", $data);
    }

    public function actionShowUpgradeErrorMsg()
    {
        $msg = EnvUtil::getRequest("msg");
        $this->render("upgradeError", array("msg" => $msg));
    }
}
