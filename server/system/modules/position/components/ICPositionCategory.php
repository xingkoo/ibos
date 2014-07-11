<?php

class ICPositionCategory extends ICCategory
{
    public function getAjaxCategory($data = array())
    {
        foreach ($data as &$row) {
            $row["id"] = $row["catid"];
            $row["pId"] = $row["pid"];
            $row["name"] = $row["name"];
            $row["target"] = "_self";
            $row["url"] = Yii::app()->urlManager->createUrl("organization/position/index") . "&catid=" . $row["catid"];
            $row["open"] = true;
        }

        return array_merge((array) $data, array());
    }

    public function getData($condition = "")
    {
        return PositionUtil::loadPositionCategory();
    }

    public function afterAdd()
    {
        OrgUtil::update();
    }

    public function afterEdit()
    {
        OrgUtil::update();
    }

    public function afterDelete()
    {
        CacheUtil::update();
        OrgUtil::update();
    }
}
