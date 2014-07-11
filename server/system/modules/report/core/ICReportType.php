<?php

class ICReportType
{
    public static function handleSaveData($data)
    {
        $type = array("sort" => intval($data["sort"]), "typename" => StringUtil::filterCleanHtml($data["typename"]), "uid" => Ibos::app()->user->uid, "intervaltype" => intval($data["intervaltype"]), "intervals" => intval($data["intervals"]), "issystype" => 0);
        return $type;
    }

    public static function handleShowInterval($intervalType)
    {
        $allInterval = array("周", "月", "季", "半年", "年", "其他");
        return $allInterval[$intervalType];
    }
}
