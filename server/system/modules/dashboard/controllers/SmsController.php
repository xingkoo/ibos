<?php

class DashboardSmsController extends DashboardBaseController
{
    public function actionSetup()
    {
        $formSubmit = EnvUtil::submitCheck("smsSubmit");

        if ($formSubmit) {
            if (isset($_POST["enabled"])) {
                $enabled = 1;
            } else {
                $enabled = 0;
            }

            $interface = $_POST["interface"];
            $setup = $_POST["interface" . $interface];
            Setting::model()->updateSettingValueByKey("smsenabled", (int) $enabled);
            Setting::model()->updateSettingValueByKey("smsinterface", (int) $interface);
            Setting::model()->updateSettingValueByKey("smssetup", $setup);
            CacheUtil::update(array("setting"));
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $data = array();
            $smsLeft = 0;
            $arr = Setting::model()->fetchSettingValueByKeys("smsenabled,smsinterface,smssetup");
            $arr["smssetup"] = unserialize($arr["smssetup"]);

            if (is_array($arr["smssetup"])) {
                if ($arr["smsinterface"] == "1") {
                    $accessKey = $arr["smssetup"]["accesskey"];
                    $secretKey = $arr["smssetup"]["secretkey"];
                    $url = "http://sms.bechtech.cn/Api/getLeft/data/json?accesskey=$accessKey&secretkey=$secretKey";
                    $return = FileUtil::fileSockOpen($url);

                    if ($return) {
                        $return = json_decode($return, true);

                        if (isset($return["result"])) {
                            $smsLeft = $return["result"];
                        }
                    }
                }
            }

            $temp = Setting::model()->fetchSettingValueByKey("");
            $arr["setup"] = unserialize($temp);
            $data["setup"] = $arr;
            $data["smsLeft"] = $smsLeft;
            $this->render("setup", $data);
        }
    }

    public function actionManager()
    {
        $data = array();
        $type = EnvUtil::getRequest("type");
        $inSearch = false;

        if ($type == "search") {
            $inSearch = true;
            $condition = "1";
            $keyword = EnvUtil::getRequest("keyword");

            if (!empty($keyword)) {
                $keyword = StringUtil::filterCleanHtml($keyword);
                $condition .= " AND content LIKE '%$keyword%'";
            }

            $searchType = EnvUtil::getRequest("searchtype");

            if (!empty($searchType)) {
                $returnStatus = array();

                if (StringUtil::findIn($searchType, 1)) {
                    $returnStatus[] = 1;
                }

                if (StringUtil::findIn($searchType, 0)) {
                    $returnStatus[] = 0;
                }

                $condition .= sprintf(" AND return IN ('%s')", implode(",", $returnStatus));
            }

            $begin = EnvUtil::getRequest("begin");
            $end = EnvUtil::getRequest("end");
            if (!empty($begin) && !empty($end)) {
                $condition .= sprintf(" AND ctime BETWEEN %d AND %d", strtotime($begin), strtotime($end));
            } elseif (!empty($begin)) {
                $condition .= sprintf(" AND ctime > %d", strtotime($begin));
            } elseif (!empty($end)) {
                $condition .= sprintf(" AND ctime < %d", strtotime($end));
            }

            $sender = EnvUtil::getRequest("sender");

            if (!empty($sender)) {
                $realSender = implode(",", StringUtil::getId($sender));
                $condition .= sprintf(" AND uid = %d", intval($realSender));
            }

            $recNumber = EnvUtil::getRequest("recnumber");

            if (!empty($recNumber)) {
                $condition .= sprintf(" AND mobile = %d", sprintf("%d", $recNumber));
            }

            $content = EnvUtil::getRequest("content");
            if (!empty($content) && empty($keyword)) {
                $content = StringUtil::filterCleanHtml($content);
                $condition .= " AND content LIKE '%$content%'";
            }

            $type = "manager";
        } else {
            $condition = "";
        }

        $count = NotifySms::model()->count($condition);
        $pages = PageUtil::create($count, 20);

        if ($inSearch) {
            $pages->params = array("keyword" => $keyword, "searchtype" => $searchType, "begin" => $begin, "end" => $end, "sender" => $sender, "recnumber" => $recNumber, "content" => $content);
        }

        $data["list"] = NotifySms::model()->fetchAll(array("condition" => $condition, "order" => "ctime DESC"));
        $data["count"] = $count;
        $data["pages"] = $pages;
        $data["search"] = $inSearch;
        $this->render("manager", $data);
    }

    public function actionAccess()
    {
        $formSubmit = EnvUtil::submitCheck("smsSubmit");

        if ($formSubmit) {
            $enabledModule = (!empty($_POST["enabled"]) ? explode(",", $_POST["enabled"]) : array());
            Setting::model()->updateSettingValueByKey("smsmodule", $enabledModule);
            CacheUtil::update(array("setting"));
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $data = array("smsModule" => Ibos::app()->setting->get("setting/smsmodule"), "enableModule" => Module::model()->fetchAllNotCoreModule());
            $this->render("access", $data);
        }
    }

    public function actionDel()
    {
        $id = EnvUtil::getRequest("id");
        $id = StringUtil::filterStr($id);
        NotifySms::model()->deleteAll("FIND_IN_SET(id,'$id')");
        $this->ajaxReturn(array("isSuccess" => true));
    }

    public function actionExport()
    {
        $id = EnvUtil::getRequest("id");
        $id = StringUtil::filterStr($id);
        MessageUtil::exportSms($id);
    }
}
