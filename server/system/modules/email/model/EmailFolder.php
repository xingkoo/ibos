<?php

class EmailFolder extends ICModel
{
    public static function model($className = "EmailFolder")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{email_folder}}";
    }

    public function fetchAllUserFolderByUid($uid, $search = "all")
    {
        $cond = "1";

        if ($search == "all") {
        } elseif ($search == "web") {
            $cond = "webid!=0";
        } elseif ($search == "folder") {
            $cond = "webid=0";
        }

        $records = $this->fetchAll("uid=$uid AND `system`='0' AND $cond ORDER BY sort DESC");
        return $records;
    }

    public function fetchFolderNameByWebId($id)
    {
        return Yii::app()->db->createCommand()->select("name")->from("{{email_folder}}")->where("webid = " . intval($id))->queryScalar();
    }

    public function getUsedSize($uid)
    {
        $where = array("and", "toid=$uid", "isdel=0", "issend=1");
        $count = Yii::app()->db->createCommand()->select("SUM(eb.size) AS sum")->from("{{email}} e")->leftJoin("{{email_body}} eb", "e.bodyid = eb.bodyid")->where($where)->queryScalar();
        return intval($count);
    }

    public function getFolderSize($uid, $fid)
    {
        $where = array("and", "toid=$uid", "fid=$fid", "isdel=0", "issend=1");
        $count = Yii::app()->db->createCommand()->select("SUM(eb.size) AS sum")->from("{{email}} e")->leftJoin("{{email_body}} eb", "e.bodyid = eb.bodyid")->where($where)->queryScalar();
        return intval($count);
    }

    public function getSysFolderSize($uid, $alias)
    {
        $param = Email::model()->getListParam($alias, $uid);
        $count = Yii::app()->db->createCommand()->select("SUM(eb.size) AS sum")->from("{{email}} e")->leftJoin("{{email_body}} eb", "e.bodyid = eb.bodyid")->where($param["condition"])->queryScalar();
        return intval($count);
    }
}
