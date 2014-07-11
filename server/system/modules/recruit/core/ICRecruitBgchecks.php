<?php

class ICRecruitBgchecks
{
    public static function processListData($bgcheckList)
    {
        foreach ($bgcheckList as $k => $bgcheck) {
            $bgcheckList[$k]["realname"] = ResumeDetail::model()->fetchRealnameByResumeid($bgcheck["resumeid"]);
            $bgcheckList[$k]["entrytime"] = ($bgcheck["entrytime"] == 0 ? "-" : date("Y-m-d", $bgcheck["entrytime"]));
            $bgcheckList[$k]["quittime"] = ($bgcheck["quittime"] == 0 ? "-" : date("Y-m-d", $bgcheck["quittime"]));
        }

        return $bgcheckList;
    }

    public static function processAddOrEditData($data)
    {
        $bgcheckArr = array("company" => "", "address" => "", "phone" => "", "fax" => "", "contact" => "", "position" => "", "entrytime" => 0, "quittime" => 0, "detail" => "", "uid" => 0);

        foreach ($data as $k => $v) {
            if (in_array($k, array_keys($bgcheckArr))) {
                $bgcheckArr[$k] = $v;
            }
        }

        $bgcheckArr["entrytime"] = strtotime($bgcheckArr["entrytime"]);
        $bgcheckArr["quittime"] = strtotime($bgcheckArr["quittime"]);
        $bgcheckArr["uid"] = Ibos::app()->user->uid;
        return $bgcheckArr;
    }
}
