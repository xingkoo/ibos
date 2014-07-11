<?php

class Assignment extends ICModel
{
    public static function model($className = "Assignment")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{assignment}}";
    }

    public function fetchUnfinishedByDesigneeuid($uid)
    {
        $record = $this->fetchAll(array("condition" => sprintf("`designeeuid` = %d AND `status` != 2 AND `status` != 3", $uid), "order" => "addtime DESC"));
        return $record;
    }

    public function fetchUnfinishedByChargeuid($uid)
    {
        $record = $this->fetchAll(array("condition" => sprintf("`chargeuid` = %d AND `status` != 2 AND `status` != 3", $uid), "order" => "addtime DESC"));
        return $record;
    }

    public function fetchUnfinishedByParticipantuid($uid)
    {
        $record = $this->fetchAll(array("condition" => sprintf("FIND_IN_SET(%d, `participantuid`) AND `status` != 2 AND `status` != 3", $uid), "order" => "addtime DESC"));
        return $record;
    }

    public function getUnfinishedByUid($uid)
    {
        $datas = array("designeeData" => $this->fetchUnfinishedByDesigneeuid($uid), "chargeData" => $this->fetchUnfinishedByChargeuid($uid), "participantData" => $this->fetchUnfinishedByParticipantuid($uid));
        return $datas;
    }

    public function fetchAllAndPage($conditions = "", $pageSize = null)
    {
        $conditionArray = array("condition" => $conditions, "order" => "finishtime DESC");
        $criteria = new CDbCriteria();

        foreach ($conditionArray as $key => $value) {
            $criteria->$key = $value;
        }

        $count = $this->count($criteria);
        $pages = new CPagination($count);
        $everyPage = (is_null($pageSize) ? Ibos::app()->params["basePerPage"] : $pageSize);
        $pages->setPageSize(intval($everyPage));
        $pages->applyLimit($criteria);
        $datas = $this->fetchAll($criteria);
        return array("pages" => $pages, "datas" => $datas, "count" => $count);
    }

    public function getUnfinishCountByUid($uid)
    {
        $count = $this->count("`status` != 2 AND `status` != 3 AND (`designeeuid` = $uid OR `chargeuid` = $uid OR FIND_IN_SET($uid, `participantuid`) )");
        return intval($count);
    }
}
