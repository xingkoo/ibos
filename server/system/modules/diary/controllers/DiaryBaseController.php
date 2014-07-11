<?php

class DiaryBaseController extends ICController
{
    const COMPLETE_FALG = 10;
    const UNSTART_FALG = 0;

    /**
     * 查询条件
     * @var string 
     * @access protected 
     */
    protected $_condition;

    public function getDiaryConfig()
    {
        return DiaryUtil::getSetting();
    }

    public function getUnreviews()
    {
        $uidArr = User::model()->fetchSubUidByUid(Ibos::app()->user->uid);
        $count = 0;

        foreach ($uidArr as $subUid) {
            $diarys = Diary::model()->fetchAll("uid=:uid AND isreview=:isreview", array(":uid" => $subUid, ":isreview" => 0));
            $count += count($diarys);
        }

        if ($count == 0) {
            $count = "";
        }

        return $count;
    }

    protected function getAjaxSidebar()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $sidebarView = $this->getSidebarData();
            $this->ajaxReturn($sidebarView);
        }
    }

    protected function getSidebarData()
    {
        $uid = Ibos::app()->user->uid;
        $ym = date("Ym");

        if (array_key_exists("ym", $_GET)) {
            $ym = $_GET["ym"];
        }

        if (array_key_exists("diaryDate", $_GET)) {
            list($year, $month) = explode("-", $_GET["diaryDate"]);
            $ym = $year . $month;
        }

        $currentDay = 0;

        if (date("m") == substr($ym, 4)) {
            $currentDay = date("j");
        }

        if (array_key_exists("currentDay", $_GET)) {
            $currentDay = $_GET["currentDay"];
        }

        $diaryList = Diary::model()->fetchAllByUidAndDiarytime($ym, $uid);
        $calendarStr = DiaryUtil::getCalendar($ym, $diaryList, $currentDay);
        return $calendarStr;
    }

    protected function getSidebar()
    {
        $sidebarAlias = "application.modules.diary.views.sidebar";
        $month = date("m");

        if (array_key_exists("diaryDate", $_GET)) {
            list(, $m) = explode("-", $_GET["diaryDate"]);
            $month = $m;
        }

        $monthName = array("一", "二", "三", "四", "五", "六", "七", "八", "九", "十", "十一", "十二");
        $monthStr = $monthName[$month - 1];
        $params = array(
            "statModule"      => Ibos::app()->setting->get("setting/statmodules"),
            "calendar"        => $this->getSidebarData(),
            "currentDateInfo" => array("year" => date("Y"), "month" => $month, "monthStr" => $monthStr),
            "dashboardConfig" => Ibos::app()->setting->get("setting/diaryconfig")
        );
        $sidebarView = $this->renderPartial($sidebarAlias, $params, true);
        return $sidebarView;
    }

    protected function getStamp()
    {
        $config = $this->getDiaryConfig();

        if ($config["stampenable"]) {
            $stampDetails = $config["stampdetails"];
            $stamps = array();

            if (!empty($stampDetails)) {
                $stampidArr = explode(",", trim($stampDetails));

                if (0 < count($stampidArr)) {
                    foreach ($stampidArr as $stampidStr) {
                        list($stampId, $score) = explode(":", $stampidStr);

                        if ($stampId != 0) {
                            $stamps[$score] = intval($stampId);
                        }
                    }
                }
            }

            $stampList = Stamp::model()->fetchAll();
            $temp = array();

            foreach ($stampList as $stamp) {
                $stampid = $stamp["id"];
                $temp[$stampid]["title"] = $stamp["code"];
                $temp[$stampid]["stamp"] = $stamp["stamp"];
                $temp[$stampid]["value"] = $stamp["id"];
                $temp[$stampid]["path"] = FileUtil::fileName(Stamp::STAMP_PATH . $stamp["icon"]);
            }

            $result = array();

            if (!empty($stamps)) {
                foreach ($stamps as $score => $stampid) {
                    $result[$score] = $temp[$stampid];
                    $result[$score]["point"] = $score;
                }
            }

            $ret = CJSON::encode(array_values($result));
        } else {
            $ret = CJSON::encode("");
        }

        return $ret;
    }

    protected function issetStamp()
    {
        $config = $this->getDiaryConfig();
        return !!$config["stampenable"];
    }

    protected function issetAutoReview()
    {
        $config = $this->getDiaryConfig();
        return !!$config["autoreview"];
    }

    protected function issetAttention()
    {
        $config = $this->getDiaryConfig();
        return !!$config["attention"];
    }

    protected function issetShare()
    {
        $config = $this->getDiaryConfig();
        return !!$config["sharepersonnel"];
    }

    protected function issetSharecomment()
    {
        $config = $this->getDiaryConfig();
        return !!$config["sharecomment"];
    }

    protected function getAutoReviewStamp()
    {
        $config = $this->getDiaryConfig();
        return intval($config["autoreviewstamp"]);
    }

    protected function getIsAllowComment($controller, $uid, $diary)
    {
        $ret = 0;

        if ($controller == "review") {
            $ret = 1;
        } else {
            if (($controller == "share") || ($controller == "attention")) {
                $ret = ($this->issetSharecomment() || UserUtil::checkIsSub($uid, $diary["uid"]) ? 1 : 0);
            }
        }

        return $ret;
    }

    protected function search()
    {
        $type = EnvUtil::getRequest("type");
        $conditionCookie = MainUtil::getCookie("condition");

        if (empty($conditionCookie)) {
            MainUtil::setCookie("condition", $this->_condition, 10 * 60);
        }

        if ($type == "advanced_search") {
            $search = $_POST["search"];
            $this->_condition = DiaryUtil::joinSearchCondition($search);
        } elseif ($type == "normal_search") {
            $keyword = $_POST["keyword"];
            MainUtil::setCookie("keyword", $keyword, 10 * 60);
            $this->_condition = " content LIKE '%$keyword%' ";
        } else {
            $this->_condition = $conditionCookie;
        }

        if ($this->_condition != MainUtil::getCookie("condition")) {
            MainUtil::setCookie("condition", $this->_condition, 10 * 60);
        }
    }

    protected function checkIsHasSub()
    {
        return DiaryUtil::checkIsHasSub();
    }
}
