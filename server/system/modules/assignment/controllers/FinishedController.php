<?php

class AssignmentFinishedController extends AssignmentBaseController
{
    private $_condition;

    public function actionIndex()
    {
        $uid = Ibos::app()->user->uid;

        if (EnvUtil::getRequest("param") == "search") {
            $this->search();
        }

        $this->_condition = AssignmentUtil::joinCondition($this->_condition, "(`status` = 2 OR `status` = 3) AND (`designeeuid` = $uid OR `chargeuid` = $uid OR FIND_IN_SET($uid, `participantuid`))");
        $data = Assignment::model()->fetchAllAndPage($this->_condition);
        $data["datas"] = AssignmentUtil::handleListData($data["datas"]);
        $data["datas"] = $this->groupByFinishtime($data["datas"]);
        $this->setPageTitle(Ibos::lang("Assignment"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Assignment"), "url" => $this->createUrl("unfinished/index")),
            array("name" => Ibos::lang("Unfinished list"))
        ));
        $this->render("list", $data);
    }

    private function groupByFinishtime($datas)
    {
        $res = array();

        foreach ($datas as $k => $v) {
            $finishDate = strtotime(date("Y-m-d", $v["finishtime"]));
            if ((0 < $k) && (strtotime(date("Y-m-d", $datas[$k - 1]["finishtime"])) == $finishDate)) {
                $preFinishDate = strtotime(date("Y-m-d", $datas[$k - 1]["finishtime"]));
                $res[$preFinishDate][] = $v;
            } else {
                $res[$finishDate][] = $v;
            }
        }

        return $res;
    }

    private function search()
    {
        $conditionCookie = MainUtil::getCookie("condition");

        if (empty($conditionCookie)) {
            MainUtil::setCookie("condition", $this->_condition, 10 * 60);
        }

        if (EnvUtil::getRequest("search")) {
            $keyword = EnvUtil::getRequest("keyword");

            if (!empty($keyword)) {
                $this->_condition = " (`subject` LIKE '%$keyword%' ";
                $users = User::model()->fetchAll("`realname` LIKE '%$keyword%'");

                if (!empty($users)) {
                    $uids = ConvertUtil::getSubByKey($users, "uid");
                    $uidStr = implode(",", $uids);
                    $this->_condition .= " OR FIND_IN_SET(`designeeuid`, '$uidStr') OR FIND_IN_SET( `chargeuid`, '$uidStr' ) ";

                    foreach ($uids as $uid) {
                        $this->_condition .= " OR FIND_IN_SET($uid, `participantuid`)";
                    }
                }

                $this->_condition .= ")";
            }

            $daterange = EnvUtil::getRequest("daterange");

            if (!empty($daterange)) {
                $time = explode(" - ", $daterange);
                $starttime = $time[0];
                $endtime = $time[1];
                $st = strtotime($starttime);
                $et = strtotime($endtime);
                $this->_condition = AssignmentUtil::joinCondition($this->_condition, "`starttime` >= $st AND `endtime` <= $et");
            }

            MainUtil::setCookie("keyword", $keyword, 10 * 60);
            MainUtil::setCookie("daterange", $daterange, 10 * 60);
        } else {
            $this->_condition = $conditionCookie;
        }

        if ($this->_condition != MainUtil::getCookie("condition")) {
            MainUtil::setCookie("condition", $this->_condition, 10 * 60);
        }
    }
}
