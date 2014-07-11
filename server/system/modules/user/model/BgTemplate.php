<?php

class BgTemplate extends ICModel
{
    public static function model($className = "BgTemplate")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{bg_template}}";
    }

    public function fetchAllBg()
    {
        $bgs = $this->fetchAll(array("order" => "id ASC"));

        foreach ($bgs as $k => $bg) {
            $bgs[$k]["imgUrl"] = UserUtil::getTempBg($bg["image"], "big");
        }

        return $bgs;
    }
}
