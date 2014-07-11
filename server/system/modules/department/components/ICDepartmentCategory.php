<?php

class ICDepartmentCategory extends ICCategory
{
    public function getAjaxCategory($data = array())
    {
        foreach ($data as &$row) {
            $row["id"] = $row["deptid"];
            $row["pId"] = $row["pid"];
            $row["name"] = $row["deptname"];
            $row["target"] = "_self";
            $row["url"] = Yii::app()->urlManager->createUrl("organization/user/index") . "&deptid=" . $row["deptid"];
            $row["open"] = true;
        }

        return array_merge((array) $data, array());
    }

    public function getData($condition = "")
    {
        return Yii::app()->setting->get("cache/department");
    }
}
