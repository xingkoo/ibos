<?php

class DashboardSplitController extends DashboardBaseController
{
    const DEFAULT_ARCHIVE_MOVE = 100;

    public function actionIndex()
    {
        $mod = EnvUtil::getRequest("mod");
        $modList = array("email", "diary");

        if (!in_array($mod, $modList)) {
            foreach ($modList as $module) {
                if (ModuleUtil::getIsEnabled($module)) {
                    $mod = $module;
                    break;
                }
            }
        }

        if (!ModuleUtil::getIsEnabled($mod)) {
            $this->error(Ibos::lang("Module not installed"));
        }

        $operation = EnvUtil::getRequest("op");

        if (!in_array($operation, array("manage", "move", "movechoose", "droptable", "addtable"))) {
            $operation = "manage";
        }

        $data = array();

        if ($mod == "email") {
            $tableDriver = array("tableId" => "emailtableids", "tableInfo" => "emailtable_info", "mainTable" => "Email", "bodyTable" => "EmailBody", "bodyIdField" => "bodyid");
        } else {
            $tableDriver = array("tableId" => "diarytableids", "tableInfo" => "diarytable_info", "mainTable" => "Diary", "bodyTable" => "DiaryRecord", "bodyIdField" => "diaryid");
        }

        $setting = Setting::model()->fetchSettingValueByKeys($tableDriver["tableId"] . "," . $tableDriver["tableInfo"], true);
        $tableIds = ($setting[$tableDriver["tableId"]] ? $setting[$tableDriver["tableId"]] : array());
        $tableInfo = ($setting[$tableDriver["tableInfo"]] ? $setting[$tableDriver["tableInfo"]] : array());
        $formSubmit = EnvUtil::submitCheck("archiveSubmit");

        if ($formSubmit) {
            if ($operation == "manage") {
                $info = array();
                $_POST["memo"] = (!empty($_POST["memo"]) ? $_POST["memo"] : array());
                $_POST["displayname"] = (!empty($_POST["displayname"]) ? $_POST["displayname"] : array());

                foreach (array_keys($_POST["memo"]) as $tableId) {
                    $info[$tableId]["memo"] = $_POST["memo"][$tableId];
                }

                foreach (array_keys($_POST["displayname"]) as $tableId) {
                    $info[$tableId]["displayname"] = $_POST["displayname"][$tableId];
                }

                Setting::model()->updateSettingValueByKey($tableDriver["tableInfo"], $info);
                CacheUtil::save($tableDriver["tableInfo"], $info);
                ArchiveSplitUtil::updateTableIds($tableDriver);
                CacheUtil::update(array("setting"));
                $this->success(Ibos::lang("Archivessplit manage update succeed"), $this->createUrl("split/index", array("op" => "manage", "mod" => $mod)));
            } elseif ($operation == "movechoose") {
                $conditions = array("sourcetableid" => EnvUtil::getRequest("sourcetableid"), "timerange" => intval(EnvUtil::getRequest("timerange")));
                $showDetail = intval($_POST["detail"]);
                $count = ArchiveSplitUtil::search($conditions, $tableDriver, true);
                $data["count"] = $count;
                $data["sourceTableId"] = $conditions["sourcetableid"];
                $data["tableInfo"] = $tableInfo;

                if ($showDetail) {
                    $list = ArchiveSplitUtil::search($conditions, $tableDriver);
                    !empty($list) && ($data["list"] = $list);
                    $data["detail"] = 1;
                } else {
                    $data["readyToMove"] = $count;
                    $data["detail"] = 0;
                }

                $data["conditions"] = serialize($conditions);
                $data = array_merge($data, ArchiveSplitUtil::getTableStatus($tableIds, $tableDriver));
                $this->render($mod . "MoveChoose", $data);
            } elseif ($operation == "moving") {
                $tableId = intval(EnvUtil::getRequest("tableid"));
                $step = intval(EnvUtil::getRequest("step"));
                $sourceTableId = intval(EnvUtil::getRequest("sourcetableid"));
                $detail = intval(EnvUtil::getRequest("detail"));

                if (!$tableId) {
                    $this->error(Ibos::lang("Archivessplit no target table"));
                }

                $continue = false;
                $readyToMove = intval(EnvUtil::getRequest("readytomve"));
                $bodyIdArr = (!empty($_POST["bodyidarray"]) ? $_POST["bodyidarray"] : array());
                if (empty($bodyIdArr) && !$detail && !empty($_POST["conditions"])) {
                    $conditions = unserialize($_POST["conditions"]);
                    $maxMove = (intval($_POST["pertime"]) ? intval($_POST["pertime"]) : self::DEFAULT_ARCHIVE_MOVE);
                    $list = ArchiveSplitUtil::search($conditions, $tableDriver, false, $maxMove);
                    $bodyIdArr = ConvertUtil::getSubByKey($list, $tableDriver["bodyIdField"]);
                } else {
                    $readyToMove = count($bodyIdArr);
                }

                if (!empty($bodyIdArr)) {
                    $continue = true;
                }

                if ($tableId == $sourceTableId) {
                    $this->error(Ibos::lang("Archivessplit source cannot be the target"), $this->createUrl("split/index", array("op" => "move", "mod" => $mod)));
                }

                if ($continue) {
                    $cronArchiveSetting = Setting::model()->fetchSettingValueByKeys("cronarchive", true);
                    $tableTarget = intval($tableId);
                    $tableSource = ($_POST["sourcetableid"] ? $_POST["sourcetableid"] : 0);
                    $tableDriver["mainTable"]::model()->moveByBodyId($bodyIdArr, $tableSource, $tableTarget);
                    $tableDriver["bodyTable"]::model()->moveByBodyid($bodyIdArr, $tableSource, $tableTarget);

                    if (!$step) {
                        if ($_POST["setcron"] == "1") {
                            $cronArchiveSetting[$mod] = array("sourcetableid" => $tableSource, "targettableid" => $tableTarget, "conditions" => unserialize($_POST["conditions"]));
                        } else {
                            unset($cronArchiveSetting[$mod]);
                        }

                        Setting::model()->updateSettingValueByKey("cronarchive", $cronArchiveSetting);
                    }

                    $completed = intval(EnvUtil::getRequest("completed")) + count($bodyIdArr);
                    $nextStep = $step + 1;
                }

                $param = array("op" => "moving", "tableid" => $tableId, "completed" => $completed, "sourcetableid" => $sourceTableId, "readytomove" => $readyToMove, "step" => $nextStep, "detail" => $detail, "mod" => $mod);
                $data["message"] = Ibos::lang(ucfirst($mod) . " moving", "", array("{count}" => $completed, "{total}" => $readyToMove, "{pertime}" => $_POST["pertime"], "{conditions}" => $_POST["conditions"]));
                $data["url"] = $this->createUrl("split/index", $param);
                $this->render("moving", $data);
            }
        } elseif ($operation == "droptable") {
            $tableId = intval(EnvUtil::getRequest("tableid"));
            $statusInfo = $tableDriver["mainTable"]::model()->getTableStatus($tableId);
            if (!$tableId || !$statusInfo) {
                $this->error(Ibos::lang("Archivessplit table no exists"));
            }

            if (0 < $statusInfo["Rows"]) {
                $this->error(Ibos::lang("Archivessplit drop table no empty error"));
            }

            $tableDriver["mainTable"]::model()->dropTable($tableId);
            $tableDriver["bodyTable"]::model()->dropTable($tableId);
            unset($tableInfo[$tableId]);
            ArchiveSplitUtil::updateTableIds($tableDriver);
            Setting::model()->updateSettingValueByKey($tableDriver["tableInfo"], $tableInfo);
            CacheUtil::save($tableDriver["tableInfo"], $tableInfo);
            CacheUtil::update(array("setting"));
            $this->success(Ibos::lang("Archivessplit drop table succeed"), $this->createUrl("split/index", array("op" => "manage", "mod" => $mod)));
        } elseif ($operation == "manage") {
            $data["tableInfo"] = $tableInfo;
            $data = array_merge($data, ArchiveSplitUtil::getTableStatus($tableIds, $tableDriver));
            $this->render($mod . "Manage", $data);
        } elseif ($operation == "addtable") {
            if (empty($tableIds)) {
                $maxTableId = 0;
            } else {
                $maxTableId = max($tableIds);
            }

            $tableDriver["mainTable"]::model()->createTable($maxTableId + 1);
            $tableDriver["bodyTable"]::model()->createTable($maxTableId + 1);
            ArchiveSplitUtil::updateTableIds($tableDriver);
            CacheUtil::update(array("setting"));
            $this->success(Ibos::lang("Archivessplit table create succeed"), $this->createUrl("split/index", array("op" => "manage", "mod" => $mod)));
        } elseif ($operation == "move") {
            if (Ibos::app()->setting->get("setting/appclosed") !== "1") {
                $this->error(Ibos::lang("Archivessplit must be closed"), $this->createUrl("split/index", array("op" => "manage", "mod" => $mod)));
            }

            $tableSelect = array();

            foreach ($tableIds as $tableId) {
                $tableSelect[$tableId] = $tableDriver["mainTable"]::model()->getTableName($tableId) . " & " . $tableDriver["bodyTable"]::model()->getTableName($tableId);
            }

            $data["tableSelect"] = $tableSelect;
            $this->render($mod . "Move", $data);
        }
    }
}
