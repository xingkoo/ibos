<?php

class DiaryShare extends ICModel
{
    public static function model($className = "DiaryShare")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{diary_share}}";
    }

    public function fetchShareInfoByUid($uid)
    {
        $record = $this->fetch("uid=:uid", array(":uid" => $uid));

        if (!empty($record)) {
            $shareIdArrTemp = explode(",", $record["deftoid"]);
            $shareIdArr = array_filter($shareIdArrTemp, create_function("\$v", "return !empty(\$v);"));
            $result = array();

            foreach ($shareIdArr as $key => $shareId) {
                $result[$key]["department"] = Department::model()->fetchDeptNameByUid($shareId);
                $result[$key]["user"] = User::model()->fetchRealnameByUid($shareId);
                $result[$key]["userid"] = $shareId;
            }

            return array("shareInfo" => $result, "deftoid" => $record["deftoid"]);
        } else {
            return array(
                "shareInfo" => array(),
                "deftoid"   => ""
            );
        }
    }

    public function delDeftoidByUid($uid)
    {
        $record = $this->fetch("uid=:uid", array(":uid" => $uid));

        if ($record) {
            $this->deleteAllByAttributes(array("uid" => $uid));
        }
    }

    public function addOrUpdateDeftoidByUid($uid, $value)
    {
        $record = $this->fetch("uid=:uid", array(":uid" => $uid));
        $share = array("uid" => $uid, "deftoid" => implode(",", $value));

        if (empty($record)) {
            $this->add($share);
        } else {
            $this->modify($record["id"], array("deftoid" => $share["deftoid"]));
        }
    }
}
