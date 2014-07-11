<?php

class Department extends ICModel
{
    protected $allowCache = true;

    public static function model($className = "Department")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{department}}";
    }

    public function queryDept($deptid, $connect = false)
    {
        $deptid = StringUtil::filterStr($deptid);
        $splitArray = explode(",", $deptid);
        $deptidStr = "";

        foreach ($splitArray as $data) {
            $deptidStr .= $this->getDeptParent($data);
        }

        $result = StringUtil::filterStr($deptidStr . ($connect ? "," . $deptid : ""));
        return $result;
    }

    private function getDeptParent($deptid)
    {
        static $depts = array();

        if (empty($depts)) {
            $depts = DepartmentUtil::loadDepartment();
        }

        $pid = (isset($depts[$deptid]) ? $depts[$deptid]["pid"] : 0);

        if (0 < $pid) {
            $pidStr = $pid . "," . $this->getDeptParent($pid);
            return $pidStr;
        } else {
            return "";
        }
    }

    public function fetchChildIdByDeptids($deptids, $connect = false)
    {
        $departArr = DepartmentUtil::loadDepartment();
        $deptidArr = explode(",", $deptids);
        $childDepartment = array();
        $childDeptIds = "";

        foreach ($deptidArr as $deptid) {
            $childDepartment = array_merge($childDepartment, $this->fetchChildDeptByDeptid($deptid, $departArr));
        }

        foreach ($childDepartment as $department) {
            $childDeptIds .= $department["deptid"] . ",";
        }

        if ($connect) {
            $childDeptIds = $deptids . "," . $childDeptIds;
        }

        return StringUtil::filterStr($childDeptIds);
    }

    public function fetchChildDeptByDeptid($deptid, $departArr)
    {
        static $result = array();

        foreach ($departArr as $department) {
            if ($department["pid"] == $deptid) {
                $result[] = $department;
                array_merge($result, $this->fetchChildDeptByDeptid($department["deptid"], $departArr));
            }
        }

        return $result;
    }

    public function fetchManagerByDeptid($deptid)
    {
        $departArr = $this->fetchByPk($deptid);
        return $departArr["manager"];
    }

    public function fetchDeptNameByDeptId($id, $glue = ",", $returnFirst = false)
    {
        $deptArr = DepartmentUtil::loadDepartment();
        $deptIds = (is_array($id) ? $id : explode(",", StringUtil::filterStr($id)));
        $name = array();

        if ($returnFirst) {
            if (isset($deptArr[$deptIds[0]])) {
                $name[] = $deptArr[$deptIds[0]]["deptname"];
            }
        } else {
            foreach ($deptIds as $deptId) {
                $name[] = (isset($deptArr[$deptId]) ? $deptArr[$deptId]["deptname"] : null);
            }
        }

        return implode($glue, $name);
    }

    public function fetchDeptNameByUid($uid, $glue = ",", $returnFirst = false)
    {
        $user = User::model()->fetchByUid($uid);
        $deptName = "";
        if (!empty($user) && !empty($user["alldeptid"])) {
            $deptName = $this->fetchDeptNameByDeptId($user["alldeptid"], $glue, $returnFirst);
        }

        return $deptName;
    }

    public function getIsBranch($id)
    {
        $record = $this->fetchByPk($id);
        return $record["isbranch"];
    }

    public function countChildByDeptId($id)
    {
        $count = $this->count("pid = :deptid", array(":deptid" => $id));
        return $count;
    }

    public function afterSave()
    {
        CacheUtil::update("department");
        CacheUtil::load("department");
        parent::afterSave();
    }

    public function afterDelete()
    {
        CacheUtil::update("department");
        CacheUtil::load("department");
        parent::afterDelete();
    }

    public function getBranchParent($deptid)
    {
        static $depts = array();

        if (empty($depts)) {
            $depts = DepartmentUtil::loadDepartment();
        }

        if (isset($depts[$deptid]) && ($depts[$deptid]["isbranch"] == 1)) {
            return $depts[$deptid];
        }

        $pid = (isset($depts[$deptid]) ? $depts[$deptid]["pid"] : 0);

        if (0 < $pid) {
            return $this->getBranchParent($pid);
        } else {
            return array();
        }
    }
}
