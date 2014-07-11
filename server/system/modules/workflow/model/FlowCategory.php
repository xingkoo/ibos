<?php

class FlowCategory extends ICModel
{
    protected $allowCache = true;

    public static function model($className = "FlowCategory")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{flow_category}}";
    }

    public function fetchNameByPk($catId)
    {
        $cat = $this->fetchByPk($catId);
        return $cat ? $cat["name"] : "";
    }

    public function fetchAllByUserPurv($uid)
    {
        $list = $this->fetchAllSortByPk("catid", array("order" => "sort ASC"));

        foreach ($list as $index => &$category) {
            if (WfCommonUtil::checkDeptPurv($uid, $category["deptid"])) {
                continue;
            } else {
                unset($list[$index]);
            }
        }

        return $list;
    }

    public function del($ids)
    {
        $id = (is_array($ids) ? implode(",", $ids) : $ids);
        $con = sprintf("FIND_IN_SET(catid,'%s')", $id);
        $flowCount = FlowType::model()->count($con);
        $formCount = FlowFormType::model()->count($con);
        if ($flowCount || $formCount) {
            return false;
        } else {
            return $this->deleteAll($con);
        }
    }
}
