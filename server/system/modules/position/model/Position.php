<?php

class Position extends ICModel
{
    protected $allowCache = true;

    public static function model($className = "Position")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{position}}";
    }

    public function afterSave()
    {
        CacheUtil::update("position");
        CacheUtil::load("position");
        parent::afterSave();
    }

    public function afterDelete()
    {
        CacheUtil::update("position");
        CacheUtil::load("position");
        parent::afterDelete();
    }

    public function fetchAllByCatId($catId, $limit, $offset)
    {
        $criteria = array("order" => "sort DESC", "limit" => $limit, "offset" => $offset);

        if ($catId) {
            $criteria["condition"] = "`catid` = $catId";
        }

        return $this->fetchAll($criteria);
    }

    public function fetchPosNameByPosId($id, $glue = ",", $returnFirst = false)
    {
        $posArr = PositionUtil::loadPosition();
        $posIds = (is_array($id) ? $id : explode(",", StringUtil::filterStr($id)));
        $name = array();

        if ($returnFirst) {
            if (isset($posArr[$posIds[0]])) {
                $name[] = $posArr[$posIds[0]]["posname"];
            }
        } else {
            foreach ($posIds as $posId) {
                $name[] = (isset($posArr[$posId]) ? $posArr[$posId]["posname"] : null);
            }
        }

        return implode($glue, $name);
    }
}
