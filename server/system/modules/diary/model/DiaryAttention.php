<?php

class DiaryAttention extends ICModel
{
    public static function model($className = "DiaryAttention")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{diary_attention}}";
    }

    public function addAttention($uid, $auid)
    {
        $this->add(array("uid" => $uid, "auid" => $auid));
    }

    public function removeAttention($uid, $auid)
    {
        $this->deleteAllByAttributes(array("uid" => $uid, "auid" => $auid));
    }

    public function fetchAuidByUid($uid)
    {
        $attentions = $this->fetchAll("uid = :uid", array(":uid" => $uid));
        $aUids = array();

        if (!empty($attentions)) {
            $aUids = ConvertUtil::getSubByKey($attentions, "auid");
        }

        return $aUids;
    }
}
