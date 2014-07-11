<?php

class MobileDiaryController extends MobileBaseController
{
    public function actionIndex()
    {
        $uid = EnvUtil::getRequest("uid");

        if (!$uid) {
            $uid = Ibos::app()->user->uid;
        }

        $datas = Diary::model()->fetchAllByPage("uid=" . $uid);

        if (isset($datas["data"])) {
            foreach ($datas["data"] as $k => $v) {
                $datas["data"][$k]["content"] = strip_tags($v["content"]);
            }
        }

        $return = array();
        $return["datas"] = $datas["data"];
        $return["pages"] = array("pageCount" => $datas["pagination"]->getPageCount(), "page" => $datas["pagination"]->getCurrentPage(), "pageSize" => $datas["pagination"]->getPageSize());
        $this->ajaxReturn($return, "JSONP");
    }

    public function actionReview()
    {
        $op = EnvUtil::getRequest("op");
        $option = (empty($op) ? "default" : $op);
        $routes = array("default", "show", "showdiary", "getsubordinates", "personal", "getStampIcon");

        if (!in_array($option, $routes)) {
            $this->error(Ibos::lang("Can not find the path"), $this->createUrl("default/index"));
        }

        $date = "today";

        if (array_key_exists("date", $_GET)) {
            $date = $_GET["date"];
        }

        if ($date == "today") {
            $time = strtotime(date("Y-m-d"));
            $date = date("Y-m-d");
        } elseif ($date == "yesterday") {
            $time = strtotime(date("Y-m-d")) - (24 * 60 * 60);
            $date = date("Y-m-d", $time);
        } else {
            $time = strtotime($date);
        }

        $uid = Ibos::app()->user->uid;
        $getSubUidArr = EnvUtil::getRequest("subUidArr");
        $user = EnvUtil::getRequest("user");

        if (!empty($getSubUidArr)) {
            $subUidArr = $getSubUidArr;
        } elseif (!empty($user)) {
            $subUidArr = array();

            foreach ($user as $v) {
                $subUidArr[] = $v["uid"];
            }
        } else {
            $subUidArr = User::model()->fetchSubUidByUid($uid);
        }

        $params = array();

        if (0 < count($subUidArr)) {
            $uids = implode(",", $subUidArr);
            $condition = "uid IN($uids) AND diarytime=$time";
            $paginationData = Diary::model()->fetchAllByPage($condition, 100);
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
                $noRecordUserList = User::model()->fetchAllByPk($noRecordUidArr);
            }

            $params = array("pagination" => $paginationData["pagination"], "data" => ICDiary::processReviewListData($uid, $paginationData["data"]), "noRecordUserList" => $noRecordUserList);
        } else {
            $params = array(
                "pagination"       => new CPagination(0),
                "data"             => array(),
                "noRecordUserList" => array()
                );
        }

        $params["dateWeekDay"] = DiaryUtil::getDateAndWeekDay($date);
        $params["dashboardConfig"] = Yii::app()->setting->get("setting/diaryconfig");
        $params["subUidArr"] = $subUidArr;
        $params["prevAndNextDate"] = array("prev" => date("Y-m-d", strtotime($date) - (24 * 60 * 60)), "next" => date("Y-m-d", strtotime($date) + (24 * 60 * 60)), "prevTime" => strtotime($date) - (24 * 60 * 60), "nextTime" => strtotime($date) + (24 * 60 * 60));
        $this->ajaxReturn($params, "JSONP");
    }

    public function actionAttention()
    {
        $date = "yesterday";

        if (array_key_exists("date", $_GET)) {
            $date = $_GET["date"];
        }

        if ($date == "today") {
            $time = strtotime(date("Y-m-d"));
            $date = date("Y-m-d");
        } elseif ($date == "yesterday") {
            $time = strtotime(date("Y-m-d")) - (24 * 60 * 60);
            $date = date("Y-m-d", $time);
        } else {
            $time = strtotime($date);
            $date = date("Y-m-d", $time);
        }

        $uid = Ibos::app()->user->uid;
        $attentions = DiaryAttention::model()->fetchAllByAttributes(array("uid" => $uid));
        $auidArr = ConvertUtil::getSubByKey($attentions, "auid");
        $hanAuidArr = $this->handleAuid($uid, $auidArr);
        $subUidStr = implode(",", $hanAuidArr["subUid"]);
        $auidStr = implode(",", $hanAuidArr["aUid"]);
        $condition = "(FIND_IN_SET(uid, '$subUidStr') OR (FIND_IN_SET('$uid', shareuid) AND FIND_IN_SET(uid, '$auidStr') ) ) AND diarytime=$time";
        $paginationData = Diary::model()->fetchAllByPage($condition, 100);
        $params = array("dateWeekDay" => DiaryUtil::getDateAndWeekDay(date("Y-m-d", strtotime($date))), "pagination" => $paginationData["pagination"], "data" => ICDiary::processShareListData($uid, $paginationData["data"]), "shareCommentSwitch" => 0, "attentionSwitch" => 1);
        $params["prevAndNextDate"] = array("prev" => date("Y-m-d", strtotime($date) - (24 * 60 * 60)), "next" => date("Y-m-d", strtotime($date) + (24 * 60 * 60)), "prevTime" => strtotime($date) - (24 * 60 * 60), "nextTime" => strtotime($date) + (24 * 60 * 60));
        $this->ajaxReturn($params, "JSONP");
    }

    private function handleAuid($uid, $attentionUids)
    {
        $aUids = (is_array($attentionUids) ? $attentionUids : implode(",", $attentionUids));
        $ret["subUid"] = array();
        $ret["aUid"] = array();

        if (!empty($aUids)) {
            foreach ($aUids as $aUid) {
                if (UserUtil::checkIsSub($uid, $aUid)) {
                    $ret["subUid"][] = $aUid;
                } else {
                    $ret["aUid"][] = $aUid;
                }
            }
        }

        return $ret;
    }

    public function actionCategory()
    {
        $this->ajaxReturn(array(), "JSONP");
    }

    public function actionShow()
    {
        $diaryid = EnvUtil::getRequest("id");
        $diaryDate = EnvUtil::getRequest("diarydate");
        if (empty($diaryid) && empty($diaryDate)) {
            $this->ajaxReturn(array(), "JSONP");
        }

        $diary = array();
        $uid = Ibos::app()->user->uid;

        if (!empty($diaryid)) {
            $diary = Diary::model()->fetchByPk($diaryid);
        } else {
            $diary = Diary::model()->fetch("diarytime=:diarytime AND uid=:uid", array(":diarytime" => strtotime($diaryDate), ":uid" => $uid));
        }

        if (empty($diary)) {
            $this->ajaxReturn(array(), "JSONP");
        }

        Diary::model()->addReaderuidByPK($diary, $uid);
        $data = Diary::model()->fetchDiaryRecord($diary);
        $params = array("diary" => ICDiary::processDefaultShowData($diary), "prevAndNextPK" => Diary::model()->fetchPrevAndNextPKByPK($diary["diaryid"]), "data" => $data);

        if (!empty($diary["attachmentid"])) {
            $params["attach"] = AttachUtil::getAttach($diary["attachmentid"], true, true, false, false, true);
            $params["count"] = 0;
        }

        if (!empty($diary["readeruid"])) {
            $readerArr = explode(",", $diary["readeruid"]);
            $params["readers"] = User::model()->fetchAllByPk($readerArr);
        } else {
            $params["readers"] = "";
        }

        if (!empty($diary["stamp"])) {
            $params["stampUrl"] = Stamp::model()->fetchStampById($diary["stamp"]);
        }

        $this->ajaxReturn($params, "JSONP");
    }

    public function actionAdd()
    {
        $todayDate = date("Y-m-d");

        if (array_key_exists("diaryDate", $_GET)) {
            $todayDate = $_GET["diaryDate"];

            if (strtotime(date("Y-m-d")) < strtotime($todayDate)) {
                $this->error(Ibos::lang("No new permissions"), $this->createUrl("default/index"));
            }
        }

        $todayTime = strtotime($todayDate);
        $uid = Ibos::app()->user->uid;

        if (Diary::model()->checkDiaryisAdd($todayTime, $uid)) {
            $this->ajaxReturn(array("msg" => "今天已经提交过日志！"), "JSONP");
        }

        $diaryRecordList = DiaryRecord::model()->fetchAllByPlantime($todayTime);
        $originalPlanList = $outsidePlanList = array();

        foreach ($diaryRecordList as $diaryRecord) {
            if ($diaryRecord["planflag"] == 1) {
                $originalPlanList[] = $diaryRecord;
            } else {
                $outsidePlanList[] = $diaryRecord;
            }
        }

        $dashboardConfig = Yii::app()->setting->get("setting/diaryconfig");
        $params = array(
            "diary"           => array("diaryid" => 0, "uid" => $uid, "diarytime" => DiaryUtil::getDateAndWeekDay($todayDate), "nextDiarytime" => DiaryUtil::getDateAndWeekDay(date("Y-m-d", strtotime("+1 day", $todayTime))), "content" => ""),
            "data"            => array("originalPlanList" => $originalPlanList, "outsidePlanList" => $outsidePlanList, "tomorrowPlanList" => ""),
            "dashboardConfig" => $dashboardConfig
            );
        $this->ajaxReturn($params, "JSONP");
    }

    public function actionSave()
    {
        $uid = Ibos::app()->user->uid;
        $originalPlan = $planOutside = "";

        if (array_key_exists("originalPlan", $_POST)) {
            $originalPlan = $_POST["originalPlan"];
        }

        if (array_key_exists("planOutside", $_POST)) {
            $planOutside = array_filter($_POST["planOutside"], create_function("\$v", "return !empty(\$v[\"content\"]);"));
        }

        if (!empty($originalPlan)) {
            foreach ($originalPlan as $key => $value) {
                DiaryRecord::model()->modify($key, array("schedule" => $value));
            }
        }

        $shareUidArr = (isset($_POST["shareuid"]) ? StringUtil::getId($_POST["shareuid"]) : array());
        $diary = array("uid" => $uid, "diarytime" => strtotime($_POST["todayDate"]), "nextdiarytime" => strtotime($_POST["plantime"]), "addtime" => TIMESTAMP, "content" => $_POST["diaryContent"], "shareuid" => implode(",", $shareUidArr), "readeruid" => "", "remark" => "", "attention" => "");
        $diaryId = Diary::model()->add($diary, true);

        if (!empty($planOutside)) {
            DiaryRecord::model()->addRecord($planOutside, $diaryId, strtotime($_POST["todayDate"]), $uid, "outside");
        }

        $plan = array_filter($_POST["plan"], create_function("\$v", "return !empty(\$v[\"content\"]);"));
        DiaryRecord::model()->addRecord($plan, $diaryId, strtotime($_POST["plantime"]), $uid, "new");
        UserUtil::updateCreditByAction("adddiary", $uid);
        $this->ajaxReturn($diaryId, "JSONP");
    }
}
