<?php

class Session extends ICModel
{
    public static function model($className = "Session")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{session}}";
    }

    public function fetchBySid($sid, $ip = false, $uid = false)
    {
        if (empty($sid)) {
            return array();
        }

        $result = $this->findByAttributes(array("sid" => $sid));
        $session = (is_null($result) ? array() : $result->attributes);

        if (!empty($session)) {
            $ipConcat = "{$session["ip1"]}.{$session["ip2"]}.{$session["ip3"]}.{$session["ip4"]}";
        } else {
            $ipConcat = "";
        }

        if ($session && ($ip !== false) && ($ip != $ipConcat)) {
            $session = array();
        }

        if ($session && ($uid !== false) && ($uid != $session["uid"])) {
            $session = array();
        }

        return $session;
    }

    public function fetchByUid($uid)
    {
        return $this->fetchByAttributes(array("uid" => $uid));
    }

    public function deleteBySession($session)
    {
        if (!empty($session) && is_array($session)) {
            $session = StringUtil::iaddSlashes($session);
            $condition = "uid='{$session["uid"]}'";
            $this->deleteAll($condition);
        }
    }
}
