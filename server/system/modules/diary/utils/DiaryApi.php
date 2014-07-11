<?php

class DiaryApi extends MessageApi
{
    private $_indexTab = array("diaryPersonal", "diaryAppraise");

    public function loadSetting()
    {
        $subUidArr = UserUtil::getAllSubs(Ibos::app()->user->uid, "", true);

        if (0 < count($subUidArr)) {
            return array(
                "name"  => "diary",
                "title" => "工作日志",
                "style" => "in-diary",
                "tab"   => array(
                    array("name" => "diaryPersonal", "title" => "个人", "icon" => "o-da-personal"),
                    array("name" => "diaryAppraise", "title" => "评阅", "icon" => "o-da-appraise")
                )
            );
        } else {
            return array(
                "name"  => "diary",
                "title" => "工作日志",
                "style" => "in-diary",
                "tab"   => array(
                    array("name" => "diaryPersonal", "title" => "个人", "icon" => "o-da-personal")
                )
            );
        }
    }

    public function renderIndex()
    {
        $return = array();
        $viewAlias = "application.modules.diary.views.indexapi.diary";
        $today = date("Y-m-d");
        $uid = Ibos::app()->user->uid;
        $data = array("diary" => Diary::model()->fetch("diarytime = :diarytime AND uid = :uid", array(":diarytime" => strtotime($today), ":uid" => $uid)), "calendar" => $this->loadCalendar(), "dateWeekDay" => DiaryUtil::getDateAndWeekDay($today), "lang" => Ibos::getLangSource("diary.default"), "assetUrl" => Ibos::app()->assetManager->getAssetsUrl("diary"));
        if (!empty($data["diary"]) && ($data["diary"]["stamp"] != 0)) {
            $data["stampUrl"] = Stamp::model()->fetchStampById($data["diary"]["stamp"]);
        }

        $data["preDiary"] = Diary::model()->fetchPreDiary(strtotime($today), $uid);
        if (!empty($data["preDiary"]) && ($data["preDiary"]["stamp"] != 0)) {
            $stampPath = FileUtil::fileName(Stamp::STAMP_PATH);
            $iconUrl = Stamp::model()->fetchIconById($data["preDiary"]["stamp"]);
            $data["preStampIcon"] = $stampPath . $iconUrl;
        }

        $subUidArr = User::model()->fetchSubUidByUid($uid);
        $data["subUids"] = implode(",", $subUidArr);

        if (!empty($subUidArr)) {
            $uids = implode(",", $subUidArr);
            $yesterday = strtotime(date("Y-m-d", strtotime("-1 day")));
            $yestUnReviewCount = Diary::model()->count("uid IN($uids) AND diarytime=$yesterday AND isreview='0'");

            if (0 < $yestUnReviewCount) {
                $time = $yesterday;
            } else {
                $time = strtotime(date("Y-m-d"));
            }

            $data["reviewInfo"] = array("reviewedCount" => Diary::model()->count("uid IN($uids) AND diarytime=$time AND isreview='1'"), "count" => Diary::model()->count("uid IN($uids) AND diarytime=$time"));
            $paginationData = Diary::model()->fetchAllByPage("uid IN($uids) AND diarytime=$time");
            $recordUidArr = $noRecordUidArr = $noRecordUserList = array();

            foreach ($paginationData["data"] as $diary) {
                $recordUidArr[] = $diary["uid"];
            }

            if (0 < count($recordUidArr)) {
                foreach ($subUidArr as $subUid) {
                    if (!in_array($subUid, $recordUidArr)) {
                        $noRecordUidArr[] = $subUid;
                    }
                }
            } else {
                $noRecordUidArr = $subUidArr;
            }

            if (0 < count($noRecordUidArr)) {
                $newUidArr = array_slice($noRecordUidArr, 0, 3);
                $noRecordUserList = User::model()->fetchAllByUids($newUidArr);
            }

            $data["noRecordUserList"] = $noRecordUserList;
            $reviewData = array();
            $noReviewData = array();

            foreach ($paginationData["data"] as $record) {
                $record["user"] = User::model()->fetchByUid($record["uid"]);
                $record["diarytime"] = ConvertUtil::formatDate($record["diarytime"], "d");

                if ($record["isreview"] == "1") {
                    $reviewData[] = $record;
                } else {
                    $noReviewData[] = $record;
                }
            }

            $data["reviewRecordList"] = $reviewData;
            $data["noReviewRecordList"] = $noReviewData;
        }

        foreach ($this->_indexTab as $tab) {
            $data["tab"] = $tab;

            if ($tab == "diaryPersonal") {
                $return[$tab] = Ibos::app()->getController()->renderPartial($viewAlias, $data, true);
            } else {
                if (($tab == "diaryAppraise") && (0 < count($subUidArr))) {
                    $return[$tab] = Ibos::app()->getController()->renderPartial($viewAlias, $data, true);
                }
            }
        }

        return $return;
    }

    public function loadNew()
    {
        $uid = Ibos::app()->user->uid;
        $uidArr = User::model()->fetchSubUidByUid($uid);

        if (!empty($uidArr)) {
            $uidStr = implode(",", $uidArr);
            $sql = "SELECT COUNT(diaryid) AS number FROM {{diary}} WHERE FIND_IN_SET( `uid`, '$uidStr' ) AND isreview = 0";
            $record = Diary::model()->getDbConnection()->createCommand($sql)->queryAll();
            return intval($record[0]["number"]);
        } else {
            return 0;
        }
    }

    private function loadCalendar()
    {
        list($year, $month, $day) = explode("-", date("Y-m-d"));
        $diaryList = Diary::model()->fetchAllByUidAndDiarytime($year . $month, Ibos::app()->user->uid);
        return DiaryUtil::getCalendar($year . $month, $diaryList, $day);
    }
}
