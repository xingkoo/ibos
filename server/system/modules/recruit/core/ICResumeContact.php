<?php

class ICResumeContact
{
    public static function processListData($contactList)
    {
        foreach ($contactList as $k => $contact) {
            $contactList[$k]["realname"] = ResumeDetail::model()->fetchRealnameByResumeid($contact["resumeid"]);
            $contactList[$k]["inputtime"] = date("Y-m-d", $contact["inputtime"]);
            $contactList[$k]["detail"] = StringUtil::cutStr($contact["detail"], 12);

            if ($contactList[$k]["input"]) {
                $contactList[$k]["input"] = User::model()->fetchRealnameByUid($contact["input"]);
            } else {
                $contactList[$k]["input"] = "";
            }
        }

        return $contactList;
    }

    public static function processAddOrEditData($data)
    {
        $contactArr = array("upuid" => 0, "inputtime" => 0, "contact" => "", "purpose" => "", "detail" => "");

        foreach ($data as $k => $v) {
            if (in_array($k, array_keys($contactArr))) {
                $contactArr[$k] = $v;
            }
        }

        $input = implode(",", StringUtil::getId($contactArr["upuid"]));
        $contactArr["input"] = (empty($input) ? Ibos::app()->user->uid : $input);

        if ($contactArr["inputtime"] != 0) {
            $contactArr["inputtime"] = strtotime($contactArr["inputtime"]);
        } else {
            $contactArr["inputtime"] = TIMESTAMP;
        }

        unset($contactArr["upuid"]);
        return $contactArr;
    }
}
