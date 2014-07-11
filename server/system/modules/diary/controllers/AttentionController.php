<?php

class DiaryAttentionController extends DiaryBaseController
{
    public function init()
    {
        if (!$this->issetAttention()) {
            $this->error(Ibos::lang("Attention not open"), $this->createUrl("default/index"));
        }

        parent::init();
    }

    protected function getSidebar()
    {
        $sidebarAlias = "application.modules.diary.views.attention.sidebar";
        $aUids = DiaryAttention::model()->fetchAuidByUid(Ibos::app()->user->uid);
        $aUsers = array();

        if (!empty($aUids)) {
            $aUsers = User::model()->fetchAllByUids($aUids);
        }

        $params = array("aUsers" => $aUsers, "statModule" => Ibos::app()->setting->get("setting/statmodules"));
        $sidebarView = $this->renderPartial($sidebarAlias, $params, true);
        return $sidebarView;
    }

    public function actionIndex()
    {
        $op = EnvUtil::getRequest("op");
        if (empty($op) || !in_array($op, array("default", "personal"))) {
            $op = "default";
        }

        if ($op == "default") {
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
            $paginationData = Diary::model()->fetchAllByPage($condition);
            $params = array("dateWeekDay" => DiaryUtil::getDateAndWeekDay(date("Y-m-d", strtotime($date))), "pagination" => $paginationData["pagination"], "data" => ICDiary::processShareListData($uid, $paginationData["data"]), "shareCommentSwitch" => $this->issetSharecomment(), "attentionSwitch" => $this->issetAttention());
            $params["prevAndNextDate"] = array("prev" => date("Y-m-d", strtotime($date) - (24 * 60 * 60)), "next" => date("Y-m-d", strtotime($date) + (24 * 60 * 60)), "prevTime" => strtotime($date) - (24 * 60 * 60), "nextTime" => strtotime($date) + (24 * 60 * 60));
            $this->setPageTitle(Ibos::lang("Attention diary"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Personal Office")),
                array("name" => Ibos::lang("Work diary"), "url" => $this->createUrl("default/index")),
                array("name" => Ibos::lang("Attention diary"))
            ));
            $this->render("index", $params);
        } else {
            $this->{$op}();
        }
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

    private function personal()
    {
        $uid = Ibos::app()->user->uid;
        $getUid = intval(EnvUtil::getRequest("uid"));
        $condition = "uid = '$getUid'";

        if (!UserUtil::checkIsSub($uid, $getUid)) {
            $condition .= " AND FIND_IN_SET('$uid', shareuid )";
        }

        if (EnvUtil::getRequest("param") == "search") {
            $this->search();
        }

        $this->_condition = DiaryUtil::joinCondition($this->_condition, $condition);
        $paginationData = Diary::model()->fetchAllByPage($this->_condition);
        $data = array("pagination" => $paginationData["pagination"], "data" => ICDiary::processDefaultListData($paginationData["data"]), "diaryCount" => Diary::model()->count($this->_condition), "commentCount" => Diary::model()->countCommentByReview($getUid), "user" => User::model()->fetchByUid($getUid), "dashboardConfig" => Yii::app()->setting->get("setting/diaryconfig"));
        $this->setPageTitle(Ibos::lang("Attention diary"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Work diary"), "url" => $this->createUrl("default/index")),
            array("name" => Ibos::lang("Attention diary"))
        ));
        $this->render("personal", $data);
    }

    public function actionEdit()
    {
        $op = EnvUtil::getRequest("op");
        $option = (empty($op) ? "default" : $op);
        $routes = array("default", "attention", "unattention");

        if (!in_array($option, $routes)) {
            $this->error(Ibos::lang("Can not find the path"), $this->createUrl("default/index"));
        }

        if ($option == "default") {
        } else {
            $this->{$option}();
        }
    }

    private function attention()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $auid = EnvUtil::getRequest("auid");
            $uid = Ibos::app()->user->uid;
            DiaryAttention::model()->addAttention($uid, $auid);
            $this->ajaxReturn(array("isSuccess" => true, "info" => Ibos::lang("Attention succeed")));
        }
    }

    private function unattention()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $auid = EnvUtil::getRequest("auid");
            $uid = Ibos::app()->user->uid;
            DiaryAttention::model()->removeAttention($uid, $auid);
            $this->ajaxReturn(array("isSuccess" => true, "info" => Ibos::lang("Unattention succeed")));
        }
    }

    public function actionShow()
    {
        $diaryid = intval(EnvUtil::getRequest("diaryid"));
        $uid = Ibos::app()->user->uid;

        if (empty($diaryid)) {
            $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("attention/index"));
        }

        $diary = Diary::model()->fetchByPk($diaryid);

        if (empty($diary)) {
            $this->error(Ibos::lang("No data found"), $this->createUrl("attention/index"));
        }

        if (!ICDiary::checkScope($uid, $diary)) {
            $this->error(Ibos::lang("You do not have permission to view the log"), $this->createUrl("attention/index"));
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

        $this->setPageTitle(Ibos::lang("Show Attention diary"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Work diary"), "url" => $this->createUrl("default/index")),
            array("name" => Ibos::lang("Show Attention diary"))
        ));
        $this->render("show", $params);
    }
}
