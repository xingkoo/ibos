<?php

class DashboardCronController extends DashboardBaseController
{
    public function actionIndex()
    {
        $op = EnvUtil::getRequest("op");
        $id = intval(EnvUtil::getRequest("id"));

        if (EnvUtil::submitCheck("formhash")) {
            if ($op == "edit") {
                $dayNew = ($_POST["weekdaynew"] != -1 ? -1 : $_POST["daynew"]);

                if (strpos($_POST["minutenew"], ",") !== false) {
                    $minuteNew = explode(",", $_POST["minutenew"]);

                    foreach ($minuteNew as $key => $val) {
                        $minuteNew[$key] = $val = intval($val);
                        if (($val < 0) || (59 < $val)) {
                            unset($minuteNew[$key]);
                        }
                    }

                    $minuteNew = array_slice(array_unique($minuteNew), 0, 12);
                    $minuteNew = implode("\t", $minuteNew);
                } else {
                    $minuteNew = intval($_POST["minutenew"]);
                    $minuteNew = ((0 <= $minuteNew) && ($minuteNew < 60) ? $minuteNew : "");
                }

                $cronfile = $this->getRealCronFile($_POST["type"], $_POST["filenamenew"], $_POST["module"]);

                if (preg_match("/[\\\\\/\:\*\?\"\<\>\|]+/", $_POST["filenamenew"])) {
                    $this->error(Ibos::lang("Crons filename illegal"));
                } elseif (!is_readable($cronfile)) {
                    $this->error(Ibos::lang("Crons filename invalid", "", array("{cronfile}" => $cronfile)));
                } else {
                    if (($_POST["weekdaynew"] == -1) && ($dayNew == -1) && ($_POST["hournew"] == -1) && ($minuteNew === "")) {
                        $this->error(Ibos::lang("Crons time invalid"));
                    }
                }

                $data = array("weekday" => $_POST["weekdaynew"], "day" => $dayNew, "hour" => $_POST["hournew"], "minute" => $minuteNew, "filename" => trim($_POST["filenamenew"]));
                $id && Cron::model()->modify($id, $data);
                Ibos::app()->cron->run($id);
            } elseif ($op == "delete") {
                if (!empty($_POST["delete"])) {
                    $ids = StringUtil::iImplode($_POST["delete"]);
                    Cron::model()->deleteAll(sprintf("cronid IN (%s) AND type='user'", $ids));
                }
            } else {
                if (isset($_POST["namenew"]) && !empty($_POST["namenew"])) {
                    foreach ($_POST["namenew"] as $id => $name) {
                        $newCron = array("name" => StringUtil::filterCleanHtml($_POST["namenew"][$id]), "available" => isset($_POST["availablenew"][$id]) ? 1 : 0);
                        if (isset($_POST["availablenew"][$id]) && empty($_POST["availablenew"][$id])) {
                            $newCron["nextrun"] = "0";
                        }

                        Cron::model()->modify($id, $newCron);
                    }
                }

                if (!empty($_POST["newname"])) {
                    $data = array("name" => StringUtil::ihtmlSpecialChars($_POST["newname"]), "type" => "user", "available" => "0", "weekday" => "-1", "day" => "-1", "hour" => "-1", "minute" => "", "nextrun" => TIMESTAMP);
                    Cron::model()->add($data);
                }

                $list = Cron::model()->fetchAll(array("select" => "cronid,filename,type,module"));

                foreach ($list as $cron) {
                    $cronFile = $this->getRealCronFile($cron["type"], $cron["filename"], $cron["module"]);

                    if (!file_exists($cronFile)) {
                        Cron::model()->modify($cron["cronid"], array("available" => 0, "nextrun" => 0));
                    }
                }

                CacheUtil::update("setting");
            }

            $this->success(Ibos::lang("Crons succeed"), $this->createUrl("cron/index"));
        } else {
            if ($op && in_array($op, array("edit", "run"))) {
                $cron = Cron::model()->fetchByPk($id);

                if (!$cron) {
                    $this->error("Cron not found");
                }

                $cron["filename"] = str_replace(array("..", "/", "\\"), array("", "", ""), $cron["filename"]);

                if ($op == "edit") {
                    $this->render("edit", array("cron" => $cron));
                } elseif ($op == "run") {
                    $file = $this->getRealCronFile($cron["type"], $cron["filename"], $cron["module"]);

                    if (!file_exists($file)) {
                        $this->error(Ibos::lang("Crons run invalid", "", array("{cronfile}" => $file)));
                    } else {
                        Ibos::app()->cron->run($cron["cronid"]);
                        $this->success(Ibos::lang("Crons run succeed"), $this->createUrl("cron/index"));
                    }
                }
            } else {
                $list = Cron::model()->fetchAll(array("order" => "type desc"));
                $this->handleCronList($list);
                $this->render("index", array("list" => $list));
            }
        }
    }

    private function getRealCronFile($type, $fileName, $module = "")
    {
        if ($type == "user") {
            $cronFile = "./system/extensions/cron/" . $fileName;
        } else {
            $cronFile = sprintf("./system/modules/%s/cron/%s", $module, $fileName);
        }

        return $cronFile;
    }

    private function handleCronList(&$list)
    {
        foreach ($list as &$cron) {
            $cron["disabled"] = (($cron["weekday"] == -1) && ($cron["day"] == -1) && ($cron["hour"] == -1) && ($cron["minute"] == "") ? true : false);
            if ((0 < $cron["day"]) && ($cron["day"] < 32)) {
                $cron["time"] = Ibos::lang("Per mensem") . $cron["day"] . Ibos::lang("Cron day");
            } else {
                if ((0 <= $cron["weekday"]) && ($cron["weekday"] < 7)) {
                    $cron["time"] = Ibos::lang("Weekly") . Ibos::lang("Cron week day " . $cron["weekday"]);
                } else {
                    if ((0 <= $cron["hour"]) && ($cron["hour"] < 24)) {
                        $cron["time"] = Ibos::lang("Cron perday");
                    } else {
                        $cron["time"] = Ibos::lang("Per hour");
                    }
                }
            }

            $cron["time"] .= ((0 <= $cron["hour"]) && ($cron["hour"] < 24) ? sprintf("%02d", $cron["hour"]) . Ibos::lang("Cron hour") : "");

            if (!in_array($cron["minute"], array(-1, ""))) {
                foreach ($cron["minute"] = explode("\t", $cron["minute"]) as $k => $v) {
                    $cron["minute"][$k] = sprintf("%02d", $v);
                }

                $cron["minute"] = implode(",", $cron["minute"]);
                $cron["time"] .= $cron["minute"] . Ibos::lang("Cron minute");
            } else {
                $cron["time"] .= "00" . Ibos::lang("Cron minute");
            }

            $cron["lastrun"] = ($cron["lastrun"] ? ConvertUtil::formatDate($cron["lastrun"], Ibos::app()->setting->get("setting/dateformat") . "<\b\\r />" . Ibos::app()->setting->get("setting/timeformat")) : "<b>N/A</b>");
            $cron["nextrun"] = ($cron["nextrun"] ? ConvertUtil::formatDate($cron["nextrun"], Ibos::app()->setting->get("setting/dateformat") . "<\b\\r />" . Ibos::app()->setting->get("setting/timeformat")) : "<b>N/A</b>");
        }
    }
}
