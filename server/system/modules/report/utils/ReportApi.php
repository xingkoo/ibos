<?php

class ReportApi extends MessageApi
{
    private $_indexTab = array("reportPersonal", "reportAppraise");

    public function loadSetting()
    {
        $uid = Ibos::app()->user->uid;
        $subUidArr = User::model()->fetchSubUidByUid($uid);
        $subReports = Report::model()->fetchAll("FIND_IN_SET($uid, `toid`)");
        if ((0 < count($subUidArr)) || !empty($subReports)) {
            return array(
                "name"  => "report",
                "title" => "工作总结",
                "style" => "in-report",
                "tab"   => array(
                    array("name" => "reportPersonal", "title" => "个人", "icon" => "o-rp-personal"),
                    array("name" => "reportAppraise", "title" => "评阅", "icon" => "o-rp-appraise")
                )
            );
        } else {
            return array(
                "name"  => "report",
                "title" => "工作总结",
                "style" => "in-report",
                "tab"   => array(
                    array("name" => "reportPersonal", "title" => "个人", "icon" => "o-rp-personal")
                )
            );
        }
    }

    public function renderIndex()
    {
        $return = array();
        $viewAlias = "application.modules.report.views.indexapi.report";
        $uid = Ibos::app()->user->uid;
        $reports = Report::model()->fetchAllRepByUids($uid);

        if (!empty($reports)) {
            $reports = $this->handleIconUrl($reports);
        }

        $subUidArr = User::model()->fetchSubUidByUid($uid);
        $subUidStr = implode(",", $subUidArr);
        $subReports = Report::model()->fetchAll("FIND_IN_SET(`uid`, '$subUidStr') OR FIND_IN_SET($uid, `toid`)");

        if (!empty($subReports)) {
            $subReports = $this->handleIconUrl($subReports, true);
        }

        $data = array("reports" => $reports, "subReports" => $subReports, "lang" => Ibos::getLangSource("report.default"), "assetUrl" => Yii::app()->assetManager->getAssetsUrl("report"));

        foreach ($this->_indexTab as $tab) {
            $data["tab"] = $tab;

            if ($tab == "reportPersonal") {
                $return[$tab] = Yii::app()->getController()->renderPartial($viewAlias, $data, true);
            } else {
                if (($tab == "reportAppraise") && ((0 < count($subUidArr)) || !empty($subReports))) {
                    $return[$tab] = Yii::app()->getController()->renderPartial($viewAlias, $data, true);
                }
            }
        }

        return $return;
    }

    public function loadNew()
    {
        $uid = Yii::app()->user->uid;
        $uidArr = User::model()->fetchSubUidByUid($uid);

        if (!empty($uidArr)) {
            $uidStr = implode(",", $uidArr);
            $sql = "SELECT COUNT(repid) AS number FROM {{report}} WHERE FIND_IN_SET( `uid`, '$uidStr' ) AND isreview = 0";
            $record = Report::model()->getDbConnection()->createCommand($sql)->queryAll();
            return intval($record[0]["number"]);
        } else {
            return 0;
        }
    }

    private function handleIconUrl($reports, $returnUserInfo = false)
    {
        $stampPath = FileUtil::fileName(Stamp::STAMP_PATH);

        foreach ($reports as $k => $report) {
            if ($returnUserInfo) {
                $reports[$k]["userInfo"] = User::model()->fetchByUid($report["uid"]);
            }

            if ($report["stamp"] != 0) {
                $stamp = Stamp::model()->fetchIconById($report["stamp"]);
                $reports[$k]["iconUrl"] = $stampPath . $stamp;
            } else {
                $reports[$k]["iconUrl"] = "";
            }
        }

        return $reports;
    }
}
