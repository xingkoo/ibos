<?php

class EmailApi
{
    private $_indexTab = array("inbox", "unread", "todo");

    public function renderIndex()
    {
        $return = array();
        $viewAlias = "application.modules.email.views.indexapi.email";
        $data["lang"] = Ibos::getLangSource("email.default");
        $data["assetUrl"] = Ibos::app()->assetManager->getAssetsUrl("email");

        foreach ($this->_indexTab as $tab) {
            $data["emails"] = $this->loadEmail($tab);
            $data["tab"] = $tab;
            $return[$tab] = Ibos::app()->getController()->renderPartial($viewAlias, $data, true);
        }

        return $return;
    }

    public function loadSetting()
    {
        return array(
            "name"  => "email",
            "title" => Ibos::lang("My email", "email.default"),
            "style" => "in-email",
            "tab"   => array(
                array("name" => "inbox", "title" => Ibos::lang("Inbox", "email.default"), "icon" => "o-mal-inbox"),
                array("name" => "unread", "title" => Ibos::lang("Unread", "email.default"), "icon" => "o-mal-unread"),
                array("name" => "todo", "title" => Ibos::lang("Todo", "email.default"), "icon" => "o-mal-todo")
            )
        );
    }

    public function loadNew()
    {
        $uid = Ibos::app()->user->uid;
        $command = Ibos::app()->db->createCommand();
        $count = $command->select("count(emailid)")->from("{{email}}")->where("`toid`='$uid' AND `fid`= 1 AND `isdel` = 0 AND `isread` = 0")->queryScalar();
        return intval($count);
    }

    private function loadEmail($type = "inbox", $num = 4)
    {
        $uid = Ibos::app()->user->uid;
        $command = Ibos::app()->db->createCommand();
        $command->select("emailid,b.bodyid,toid,isread,ismark,fromid,subject,content,sendtime,attachmentid,important,u.realname")->from("{{email}} e")->leftJoin("{{email_body}} b", "e.bodyid = b.bodyid")->leftJoin("{{user}} u", "b.fromid = u.uid");

        switch ($type) {
            case "inbox":
                $command->where("`toid`='$uid' AND `fid`= 1 AND `isdel` = 0");
                break;

            case "unread":
                $command->where("`toid`='$uid' AND (`isread`='' OR `isread` = 0) AND `isdel`= 0 AND `fid` = 1");
                break;

            case "todo":
                $command->where("`toid` ='$uid' AND `ismark` = 1 AND `isdel` = 0");
                break;

            default:
                return false;
        }

        $records = $command->order("e.emailid DESC")->offset(0)->limit($num)->queryAll();
        return $records;
    }
}
