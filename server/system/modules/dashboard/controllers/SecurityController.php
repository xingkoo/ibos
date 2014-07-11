<?php

class DashboardSecurityController extends DashboardBaseController
{
    public function actionSetup()
    {
        $formSubmit = EnvUtil::submitCheck("securitySubmit");

        if ($formSubmit) {
            $fields = array("expiration", "minlength", "mixed", "errorlimit", "errorrepeat", "errortime", "autologin", "allowshare", "timeout");
            $updateList = array();

            foreach ($fields as $field) {
                if (!isset($_POST[$field])) {
                    $_POST[$field] = 0;
                }

                $updateList[$field] = $_POST[$field];
            }

            if (intval($updateList["timeout"]) == 0) {
                $this->error("请填写一个正确的大于0的超时时间值");
            }

            Setting::model()->updateSettingValueByKey("account", $updateList);
            CacheUtil::update(array("setting"));
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $data = array();
            $account = Setting::model()->fetchSettingValueByKey("account");
            $data["account"] = unserialize($account);
            $this->render("setup", $data);
        }
    }

    public function actionLog()
    {
        $formSubmit = EnvUtil::submitCheck("securitySubmit");

        if ($formSubmit) {
            CacheUtil::update(array("setting"));
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $data = array();
            $levels = array("admincp", "banned", "illegal", "login");
            $level = EnvUtil::getRequest("level");
            $filterAct = EnvUtil::getRequest("filteract");
            $timeScope = EnvUtil::getRequest("timescope");

            if (!in_array($level, $levels)) {
                $level = "admincp";
            }

            $conArr = array("level" => $level);
            $condition = "level = '$level'";

            if (!empty($filterAct)) {
                $condition .= sprintf(" AND category = 'module.dashboard.%s'", $filterAct);
                $conArr["filteract"] = $filterAct;
            } else {
                $condition .= " AND 1";
            }

            if (!empty($timeScope)) {
                $start = EnvUtil::getRequest("start");
                $end = EnvUtil::getRequest("end");
                $tableId = intval($timeScope);
                $conArr["timescope"] = $tableId;
                if (!empty($start) && !empty($end)) {
                    $conArr["start"] = $start;
                    $conArr["end"] = $end;
                    $start = strtotime($tableId . "-" . $start);
                    $end = strtotime($tableId . "-" . $end);
                    $condition .= sprintf(" AND `logtime` BETWEEN %d AND %d", $start, $end);
                } elseif (!empty($start)) {
                    $conArr["start"] = $start;
                    $start = strtotime($tableId . "-" . $start);
                    $condition .= sprintf(" AND `logtime` > %d", $start);
                } elseif (!empty($end)) {
                    $conArr["end"] = $end;
                    $end = strtotime($tableId . "-" . $end);
                    $condition .= sprintf(" AND `logtime` < %d", $end);
                }
            } else {
                $tableId = 0;
                $lastMonth = strtotime("last month");
                $condition .= sprintf(" AND `logtime` BETWEEN %d AND %d", $lastMonth, TIMESTAMP);
            }

            $count = Log::countByTableId($tableId, $condition);
            $pages = PageUtil::create($count, 20);
            $log = Log::fetchAllByList($tableId, $condition, $pages->getLimit(), $pages->getOffset());
            $data["log"] = $log;
            $data["pages"] = $pages;

            if ($level == "admincp") {
                $data["actions"] = Ibos::getLangSource("dashboard.actions");
            }

            $data["filterAct"] = $filterAct;
            $data["level"] = $level;
            $data["archive"] = Log::getAllArchiveTableId();
            $data["con"] = $conArr;
            $this->render("log", $data);
        }
    }

    public function actionIp()
    {
        $formSubmit = EnvUtil::submitCheck("securitySubmit");

        if ($formSubmit) {
            if ($_POST["act"] == "") {
                if (isset($_POST["ip"])) {
                    foreach ($_POST["ip"] as $new) {
                        if (($new["ip1"] != "") && ($new["ip2"] != "") && ($new["ip3"] != "") && ($new["ip4"] != "")) {
                            $own = 0;
                            $ip = explode(".", Ibos::app()->setting->get("clientip"));

                            for ($i = 1; $i <= 4; $i++) {
                                if (!is_numeric($new["ip" . $i]) || ($new["ip" . $i] < 0)) {
                                    $new["ip" . $i] = -1;
                                    $own++;
                                } elseif ($new["ip" . $i] == $ip[$i - 1]) {
                                    $own++;
                                }

                                $new["ip" . $i] = intval($new["ip" . $i]);
                            }

                            if ($own == 4) {
                                $this->error(Ibos::lang("Ipban illegal"));
                            }

                            $expiration = TIMESTAMP + ($new["validitynew"] * 86400);
                            $new["admin"] = Ibos::app()->user->username;
                            $new["dateline"] = TIMESTAMP;
                            $new["expiration"] = $expiration;
                            IpBanned::model()->add($new);
                        }
                    }
                }

                if (isset($_POST["expiration"])) {
                    $userName = Ibos::app()->user->username;

                    foreach ($_POST["expiration"] as $id => $expiration) {
                        IpBanned::model()->updateExpirationById($id, strtotime($expiration), $userName);
                    }
                }
            } elseif ($_POST["act"] == "del") {
                if (is_array($_POST["id"])) {
                    IpBanned::model()->deleteByPk($_POST["id"]);
                }
            } elseif ($_POST["act"] == "clear") {
                $command = Ibos::app()->db->createCommand();
                $command->delete("{{ipbanned}}");
            }

            CacheUtil::update(array("setting", "ipbanned"));
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $data = array();
            $lists = IpBanned::model()->fetchAllOrderDateline();
            $list = array();

            foreach ($lists as $banned) {
                for ($i = 1; $i <= 4; $i++) {
                    if ($banned["ip$i"] == -1) {
                        $banned["ip$i"] = "*";
                    }
                }

                $banned["dateline"] = date("Y-m-d", $banned["dateline"]);
                $banned["expiration"] = date("Y-m-d", $banned["expiration"]);
                $displayIp = "{$banned["ip1"]}.{$banned["ip2"]}.{$banned["ip3"]}.{$banned["ip4"]}";
                $banned["display"] = $displayIp;
                $banned["scope"] = ConvertUtil::convertIp($displayIp);
                $list[] = $banned;
            }

            $data["list"] = $list;
            $this->render("ip", $data);
        }
    }
}
