<?php

class MessageUtil
{
    public static function sendSms($mobile, $content = "", $module = "", $touid = 0, $uid = 0)
    {
        $content = StringUtil::filterCleanHtml($content);
        $data = array("uid" => $uid, "touid" => $touid, "node" => "", "module" => $module, "mobile" => $mobile, "content" => $content);
        if (CloudApi::getInstance()->isOpen() && CloudApi::getInstance()->exists("sms_send")) {
            $route = "Api/Sms/Send";
            $rs = CloudApi::getInstance()->fetch($route, array("mobile" => $mobile, "content" => $content));
            $data["posturl"] = $route;
            $data["return"] = $rs;
            NotifySms::model()->sendSms($data);
            return true;
        }

        return false;
    }

    public static function exportSms($id)
    {
        $ids = (is_array($id) ? $id : explode(",", $id));
        header("Content-Type:application/vnd.ms-excel");
        $fileName = ConvertUtil::iIconv(Ibos::lang("SMS export name", "dashboard.default", array("{date}" => date("Ymd"))), CHARSET, "gbk");
        header("Content-Disposition: attachment;filename=$fileName.csv");
        header("Cache-Control: max-age = 0");
        $head = array("ID", Ibos::lang("Sender", "dashboard.default"), Ibos::lang("Recipient", "dashboard.default"), Ibos::lang("Membership module", "dashboard.default"), Ibos::lang("Recipient phone number", "dashboard.default"), Ibos::lang("Content", "dashboard.default"), Ibos::lang("Result", "dashboard.default"), Ibos::lang("Send time", "dashboard.default"));

        foreach ($head as &$header) {
            $header = ConvertUtil::iIconv($header, CHARSET, "gbk");
        }

        $fp = fopen("php://output", "a");
        fputcsv($fp, $head);
        $cnt = 0;
        $limit = 100;
        $system = Ibos::lang("System", "dashboard.default");

        foreach (NotifySms::model()->fetchAll(sprintf("FIND_IN_SET(id,'%s')", implode(",", $ids))) as $row) {
            if ($limit == $cnt) {
                ob_flush();
                flush();
                $cnt = 0;
            }

            $data = array($row["id"], ConvertUtil::iIconv($row["uid"] == 0 ? $system : User::model()->fetchRealnameByUid($row["uid"]), CHARSET, "gbk"), ConvertUtil::iIconv(User::model()->fetchRealnameByUid($row["touid"]), CHARSET, "gbk"), $row["module"], $row["mobile"], ConvertUtil::iIconv($row["content"], CHARSET, "gbk"), ConvertUtil::iIconv($row["return"], CHARSET, "gbk"), date("Y-m-d H:i:s", $row["ctime"]));
            fputcsv($fp, $data);
        }

        exit();
    }

    public static function getIsImOpen($type)
    {
        $setting = Setting::model()->fetchSettingValueByKey("im");
        $arrays = unserialize($setting);
        if (is_array($arrays) && isset($arrays[$type])) {
            if (isset($arrays[$type]["open"]) && ($arrays[$type]["open"] == "1")) {
                return true;
            }
        }

        return false;
    }

    public static function getIsImBinding($type, $im)
    {
        $className = "ICIM" . ucfirst($type);

        if (class_exists($className)) {
            $adapter = new $className($im);
            return $adapter->check() ? true : $adapter->getError();
        }

        return false;
    }

    public static function push($type, $toUid, $push)
    {
        !is_array($toUid) && ($toUid = explode(",", $toUid));
        $imCfg = array();

        foreach (Ibos::app()->setting->get("setting/im") as $imType => $config) {
            if ($config["open"] == "1") {
                $className = "ICIM" . ucfirst($imType);
                $imCfg = $config;
                break;
            }
        }

        if (!empty($imCfg)) {
            $factory = new ICIMFactory();
            $properties = array_merge($push, array("uid" => $toUid, "pushType" => $type));
            $adapter = $factory->createAdapter($className, $imCfg, $properties);
            return $adapter !== false ? $adapter->push() : "";
        }
    }

    public static function appPush($toUid, $message)
    {
        is_array($toUid) && ($toUid = implode(",", $toUid));

        if (!empty($toUid)) {
            $message = str_replace(" ", "", StringUtil::filterCleanHtml($message));
            CloudApi::getInstance()->fetch("Api/Push/Notify", array("uid" => $toUid, "msg" => $message));
        }
    }
}
