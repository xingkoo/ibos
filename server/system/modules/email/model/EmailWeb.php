<?php

class EmailWeb extends ICModel
{
    public static function model($className = "EmailWeb")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{email_web}}";
    }

    public function fetchAllByUid($uid)
    {
        $data = array("condition" => "uid = $uid", "order" => "isdefault DESC");
        return $this->fetchAllSortByPk("webid", $data);
    }

    public function delClear($id, $uid)
    {
        $fidArr = Yii::app()->db->createCommand()->select("fid")->from($this->tableName())->where("FIND_IN_SET(webid,'$id') AND uid = $uid")->queryAll();
        $fids = ConvertUtil::getSubByKey($fidArr, "fid");
        $fid = implode(",", $fids);
        Yii::app()->db->createCommand()->delete("{{email_folder}}", "FIND_IN_SET(fid,'$fid') AND uid = $uid");
        Yii::app()->db->createCommand()->update("{{email}}", array("fid" => 1), "FIND_IN_SET(fid,'$fid') AND toid = $uid");
        return $this->deleteAll("FIND_IN_SET(webid,'$id')");
    }

    public function fetchByList($uid, $offset, $limit)
    {
        $list = $this->fetchAll(array("condition" => "uid = " . intval($uid), "offset" => intval($offset), "limit" => intval($limit)));
        return (array) $list;
    }
}
