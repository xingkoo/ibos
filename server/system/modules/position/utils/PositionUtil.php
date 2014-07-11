<?php

class PositionUtil
{
    public static function combineRelated($related)
    {
        $return = array();

        foreach ($related as $value) {
            $return[$value["module"]][$value["key"]][$value["node"]] = $value["val"];
        }

        return $return;
    }

    public static function loadPosition()
    {
        return Ibos::app()->setting->get("cache/position");
    }

    public static function loadPositionCategory()
    {
        return Ibos::app()->setting->get("cache/positioncategory");
    }

    public static function cleanPurvCache($posId)
    {
        CacheUtil::rm("purv_" . $posId);
    }

    public static function getPurv($posId)
    {
        $access = CacheUtil::get("purv_" . $posId);

        if (!$access) {
            $access = Ibos::app()->getAuthManager()->getItemChildren($posId);
            CacheUtil::set("purv_" . $posId, array_flip(array_map("strtolower", array_keys($access))));
        }

        return $access;
    }
}
