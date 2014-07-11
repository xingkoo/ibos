<?php

class DepartmentRelated extends ICModel
{
    public static function model($className = "DepartmentRelated")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{department_related}}";
    }

    public function fetchAllDeptIdByUid($uid)
    {
        static $uids = array();

        if (!isset($uids[$uid])) {
            $deptids = $this->fetchAll(array(
                "select"    => "deptid",
                "condition" => "`uid` = :uid",
                "params"    => array(":uid" => $uid)
            ));
            $uids[$uid] = ConvertUtil::getSubByKey($deptids, "deptid");
        }

        return $uids[$uid];
    }

    public function fetchAllUidByDeptId($deptId)
    {
        $criteria = array("select" => "uid", "condition" => "`deptid`=$deptId");
        $auxiliary = ConvertUtil::getSubByKey($this->fetchAll($criteria), "uid");
        return $auxiliary;
    }
}
