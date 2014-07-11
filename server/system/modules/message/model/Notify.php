<?php

class Notify extends ICModel
{
    public static function model($className = "Notify")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{notify_node}}";
    }

    public function sendNotify($toUid, $node, $config)
    {
        empty($config) && ($config = array());
        $nodeInfo = $this->getNode($node);

        if (!$nodeInfo) {
            return false;
        }

        !is_array($toUid) && ($toUid = explode(",", $toUid));
        $userInfo = User::model()->fetchAllByUids($toUid);
        $data["node"] = $node;
        $data["module"] = $nodeInfo["module"];
        $data["url"] = (isset($config["{url}"]) ? $config["{url}"] : "");
        $data["title"] = Ibos::lang($nodeInfo["titlekey"], "", $config);

        if (empty($nodeInfo["contentkey"])) {
            $data["body"] = $data["title"];
            $hasContent = false;
        } else {
            $data["body"] = Ibos::lang($nodeInfo["contentkey"], "", $config);
            $hasContent = true;
        }

        MessageUtil::push("notify", $toUid, array("message" => $data["title"], "url" => $data["url"]));

        foreach ($userInfo as $v) {
            $data["uid"] = $v["uid"];
            !empty($nodeInfo["sendmessage"]) && NotifyMessage::model()->sendMessage($data);
            $data["email"] = $v["email"];
            $remindSetting = (!empty($v["remindsetting"]) ? unserialize($v["remindsetting"]) : array());
            if (isset($remindSetting[$node]) && isset($remindSetting[$node]["app"]) && ($remindSetting[$node]["app"] == 1)) {
                MessageUtil::appPush($toUid, $data["title"]);
            }

            if (!empty($nodeInfo["sendemail"])) {
                if (isset($remindSetting[$node]) && isset($remindSetting[$node]["email"]) && ($remindSetting[$node]["email"] == 1)) {
                    NotifyEmail::model()->sendEmail($data, $hasContent);
                }
            }

            if (!empty($nodeInfo["sendsms"])) {
                if (isset($remindSetting[$node]) && isset($remindSetting[$node]["sms"]) && ($remindSetting[$node]["sms"] == 1)) {
                    MessageUtil::sendSms($v["mobile"], StringUtil::filterCleanHtml($data["title"]), $nodeInfo["module"], $v["uid"]);
                }
            }
        }
    }

    public function getNode($node)
    {
        $list = $this->getNodeList();
        return isset($list[$node]) ? $list[$node] : false;
    }

    public function getNodeList()
    {
        $list = CacheUtil::get("notifyNode");

        if (!$list) {
            $list = $this->fetchAllSortByPk("node", array("order" => "`module` DESC"));
            CacheUtil::set("notifyNode", $list);
        }

        return $list;
    }
}
