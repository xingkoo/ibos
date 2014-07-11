<?php

class DiaryShareController extends DiaryBaseController
{
    public function init()
    {
        if (!$this->issetShare()) {
            $this->error(Ibos::lang("Share not open"), $this->createUrl("default/index"));
        }

        parent::init();
    }

    protected function getSidebar()
    {
        $sidebarAlias = "application.modules.diary.views.share.sidebar";
        $records = Diary::model()->fetchAllByShareCondition(Ibos::app()->user->uid, 5);
        $result = array();

        foreach ($records as $record) {
            $record["diarytime"] = date("m-d", $record["diarytime"]);
            $record["user"] = User::model()->fetchByUid($record["uid"]);
            $result[] = $record;
        }

        $sidebarView = $this->renderPartial($sidebarAlias, array("data" => $result, "statModule" => Ibos::app()->setting->get("setting/statmodules")), true);
        return $sidebarView;
    }

    public function actionIndex()
    {
        $op = EnvUtil::getRequest("op");

        if (!in_array($op, array("personal"))) {
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
            $condition = "FIND_IN_SET('$uid',shareuid) AND uid NOT IN($uid) AND diarytime=$time";
            $paginationData = Diary::model()->fetchAllByPage($condition);
            $params = array("dateWeekDay" => DiaryUtil::getDateAndWeekDay(date("Y-m-d", strtotime($date))), "pagination" => $paginationData["pagination"], "data" => ICDiary::processShareListData($uid, $paginationData["data"]), "dashboardConfig" => $this->getDiaryConfig(), "attentionSwitch" => $this->issetAttention());
            $params["prevAndNextDate"] = array("prev" => date("Y-m-d", strtotime($date) - (24 * 60 * 60)), "next" => date("Y-m-d", strtotime($date) + (24 * 60 * 60)), "prevTime" => strtotime($date) - (24 * 60 * 60), "nextTime" => strtotime($date) + (24 * 60 * 60));
            $this->setPageTitle(Ibos::lang("Share diary"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Personal Office")),
                array("name" => Ibos::lang("Work diary"), "url" => $this->createUrl("default/index")),
                array("name" => Ibos::lang("Share diary"))
            ));
            $this->render("index", $params);
        } else {
            $this->{$op}();
        }
    }

    public function actionShow()
    {
        $diaryid = intval(EnvUtil::getRequest("diaryid"));
        $uid = Ibos::app()->user->uid;

        if (empty($diaryid)) {
            $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("share/index"));
        }

        $diary = Diary::model()->fetchByPk($diaryid);

        if (empty($diary)) {
            $this->error(Ibos::lang("No data found"), $this->createUrl("share/index"));
        }

        if (!ICDiary::checkScope($uid, $diary)) {
            $this->error(Ibos::lang("You do not have permission to view the log"), $this->createUrl("share/index"));
        }

        Diary::model()->addReaderuidByPK($diary, $uid);
        $data = Diary::model()->fetchDiaryRecord($diary);
        $params = array("diary" => ICDiary::processDefaultShowData($diary), "prevAndNextPK" => Diary::model()->fetchPrevAndNextPKByPK($diary["diaryid"]), "data" => $data);

        if (!empty($diary["attachmentid"])) {
            $params["attach"] = AttachUtil::getAttach($diary["attachmentid"], true, true, false, false, true);
            $params["count"] = 0;
        }

        $params["allowComment"] = ($this->issetSharecomment() || UserUtil::checkIsSub($uid, $diary["uid"]) ? 1 : 0);

        if (!empty($diary["readeruid"])) {
            $readerArr = explode(",", $diary["readeruid"]);
            $params["readers"] = User::model()->fetchAllByPk($readerArr);
        } else {
            $params["readers"] = "";
        }

        if (!empty($diary["stamp"])) {
            $params["stampUrl"] = Stamp::model()->fetchStampById($diary["stamp"]);
        }

        $params["sharecomment"] = $this->issetSharecomment();
        $this->setPageTitle(Ibos::lang("Show share diary"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Work diary"), "url" => $this->createUrl("default/index")),
            array("name" => Ibos::lang("Show share diary"))
        ));
        $this->render("show", $params);
    }

    private function personal()
    {
        $getUid = intval(EnvUtil::getRequest("uid"));
        $uid = Ibos::app()->user->uid;

        if (EnvUtil::getRequest("param") == "search") {
            $this->search();
        }

        $condition = "uid='$getUid' AND FIND_IN_SET('$uid',shareuid) AND uid NOT IN($uid)";
        $this->_condition = DiaryUtil::joinCondition($this->_condition, $condition);
        $paginationData = Diary::model()->fetchAllByPage($this->_condition);
        $attention = DiaryAttention::model()->fetchAllByAttributes(array("uid" => $uid, "auid" => $getUid));
        $data = array("pagination" => $paginationData["pagination"], "data" => ICDiary::processDefaultListData($paginationData["data"]), "diaryCount" => Diary::model()->count($this->_condition), "commentCount" => Diary::model()->countCommentByUid($getUid), "user" => User::model()->fetchByUid($getUid), "dashboardConfig" => $this->getDiaryConfig(), "isattention" => empty($attention) ? 0 : 1);
        $this->setPageTitle(Ibos::lang("Share diary"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Work diary"), "url" => $this->createUrl("default/index")),
            array("name" => Ibos::lang("Share diary"))
        ));
        $this->render("personal", $data);
    }
}
