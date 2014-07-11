<?php

class ICOfficialdocCategory extends ICCategory
{
    public function delete($catid)
    {
        $clear = false;
        $ids = $this->fetchAllSubId($catid);
        $idStr = implode(",", array_unique(explode(",", trim($ids, ","))));

        if (empty($idStr)) {
            $idStr = $catid;
        } else {
            $idStr .= "," . $catid;
        }

        $count = Officialdoc::model()->count("catid IN ($idStr)");

        if ($count) {
            return -1;
        }

        if (!is_null($this->_related)) {
            $count = $this->_related->count("`$this->index` IN ($idStr)");
            !$count && ($clear = true);
        } else {
            $clear = true;
        }

        if ($clear) {
            $status = $this->_category->deleteAll("FIND_IN_SET($this->index,'$idStr')");
            $this->afterDelete();
            return $status;
        } else {
            return false;
        }
    }

    public function getAjaxCategory($data = array())
    {
        $return = array();

        foreach ($data as $row) {
            $row["id"] = $row["catid"];
            $row["pId"] = $row["pid"];
            $row["name"] = $row["name"];
            $row["target"] = "_self";
            $row["url"] = Yii::app()->urlManager->createUrl("officialdoc/officialdoc/index") . "&catid=" . $row["catid"];
            $row["open"] = true;
            $return[] = $row;
        }

        return $return;
    }

    public function getData($condition = "")
    {
        $categoryData = OfficialdocCategory::model()->fetchAll($condition);
        return $categoryData;
    }
}
