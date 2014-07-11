<?php

class NotifySms extends ICModel
{
    public static function model($className = "NotifySms")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{notify_sms}}";
    }

    public function sendSms($data)
    {
        $s["uid"] = intval($data["uid"]);
        $s["touid"] = intval($data["touid"]);
        $s["mobile"] = StringUtil::filterCleanHtml($data["mobile"]);
        $s["posturl"] = StringUtil::filterCleanHtml($data["posturl"]);
        $s["node"] = StringUtil::filterCleanHtml($data["node"]);
        $s["module"] = StringUtil::filterCleanHtml($data["module"]);
        $s["return"] = StringUtil::filterCleanHtml($data["return"]);
        $s["content"] = StringUtil::filterDangerTag($data["content"]);
        $s["ctime"] = time();
        return $this->add($s, true);
    }
}
