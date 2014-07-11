<?php

class ICDiary
{
    public static function processDefaultListData($data)
    {
        $dashboardConfig = Yii::app()->setting->get("setting/diaryconfig");
        $lockday = ($dashboardConfig["lockday"] ? intval($dashboardConfig["lockday"]) : 0);
        $return = array();

        foreach ($data as $value) {
            $readeruid = $value["readeruid"];

            if (empty($readeruid)) {
                $value["readercount"] = 0;
            } else {
                $value["readercount"] = count(explode(",", trim($readeruid, ",")));
            }

            $todayTime = (int) strtotime(date("Y-m-d", time()));
            $diaryTime = (int) $value["diarytime"];
            $diffDay = ($todayTime - $diaryTime) / (24 * 60 * 60);
            if ((0 < $lockday) && ($lockday < $diffDay)) {
                $value["editIsLock"] = 1;
            } else {
                $value["editIsLock"] = 0;
            }

            $value["content"] = StringUtil::cutStr(strip_tags($value["content"]), 255);
            $value["diarytime"] = DiaryUtil::getDateAndWeekDay(date("Y-m-d", $value["diarytime"]));
            $value["addtime"] = ConvertUtil::formatDate($value["addtime"], "u");

            if ($value["stamp"] != 0) {
                $path = Stamp::model()->fetchIconById($value["stamp"]);
                $value["stampPath"] = FileUtil::fileName(Stamp::STAMP_PATH . $path);
            }

            $return[] = $value;
        }

        return $return;
    }

    public static function processDefaultShowData($diary)
    {
        $dashboardConfig = Yii::app()->setting->get("setting/diaryconfig");
        $lockday = ($dashboardConfig["lockday"] ? intval($dashboardConfig["lockday"]) : 0);
        $todayTime = (int) strtotime(date("Y-m-d", time()));
        $diaryTime = (int) $diary["diarytime"];
        $diffDay = ($todayTime - $diaryTime) / (24 * 60 * 60);
        if ((0 < $lockday) && ($lockday < $diffDay)) {
            $diary["editIsLock"] = 1;
        } else {
            $diary["editIsLock"] = 0;
        }

        $diary["addtime"] = date("Y-m-d H:i:s", $diary["addtime"]);
        $diary["originalDiarytime"] = $diary["diarytime"];
        $diary["diarytime"] = DiaryUtil::getDateAndWeekDay(date("Y-m-d", $diary["diarytime"]));
        $diary["nextDiarytime"] = DiaryUtil::getDateAndWeekDay(date("Y-m-d", $diary["nextdiarytime"]));
        $diary["realname"] = User::model()->fetchRealnameByUid($diary["uid"]);
        $diary["departmentName"] = Department::model()->fetchDeptNameByUid($diary["uid"]);
        $diary["shareuid"] = StringUtil::wrapId($diary["shareuid"]);
        return $diary;
    }

    public static function processReviewListData($uid, $data)
    {
        $result = array();
        $attentions = DiaryAttention::model()->fetchAllByAttributes(array("uid" => $uid));
        $auidArr = ConvertUtil::getSubByKey($attentions, "auid");

        foreach ($data as $diary) {
            $diary["content"] = StringUtil::cutStr(strip_tags($diary["content"]), 255);
            $diary["realname"] = User::model()->fetchRealnameByUid($diary["uid"]);
            $diary["addtime"] = ConvertUtil::formatDate($diary["addtime"], "u");
            $isattention = in_array($diary["uid"], $auidArr);
            $diary["isattention"] = ($isattention ? 1 : 0);

            if (empty($diary["readeruid"])) {
                $diary["readercount"] = 0;
            } else {
                $diary["readercount"] = count(explode(",", trim($diary["readeruid"], ",")));
            }

            $result[] = $diary;
        }

        return $result;
    }

    public static function processShareListData($uid, $data)
    {
        $result = array();
        $attentions = DiaryAttention::model()->fetchAllByAttributes(array("uid" => $uid));
        $auidArr = ConvertUtil::getSubByKey($attentions, "auid");

        foreach ($data as $diary) {
            $diary["content"] = StringUtil::cutStr(strip_tags($diary["content"]), 255);
            $diary["realname"] = User::model()->fetchRealnameByUid($diary["uid"]);
            $diary["addtime"] = ConvertUtil::formatDate($diary["addtime"], "u");
            $isattention = in_array($diary["uid"], $auidArr);
            $diary["isattention"] = ($isattention ? 1 : 0);
            $diary["user"] = User::model()->fetchByUid($diary["uid"]);
            $result[] = $diary;
        }

        return $result;
    }

    public static function checkReadScope($uid, $diary)
    {
        if (isset($diary["uid"]) && ($uid == $diary["uid"])) {
            return true;
        } else {
            return false;
        }
    }

    public static function checkReviewScope($uid, $diary)
    {
        if (isset($diary["uid"]) && UserUtil::checkIsSub($uid, $diary["uid"])) {
            return true;
        } else {
            return false;
        }
    }

    public static function checkScope($uid, $diary)
    {
        if (!isset($diary["uid"])) {
            return false;
        }

        if (isset($diary["shareuid"]) && in_array($uid, explode(",", $diary["shareuid"]))) {
            return true;
        } elseif ($uid == $diary["uid"]) {
            return true;
        } elseif (self::checkReviewScope($uid, $diary)) {
            return true;
        } else {
            return false;
        }
    }
}
