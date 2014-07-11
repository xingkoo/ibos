<?php

class AssignmentBaseController extends ICController
{
    public function getSidebar()
    {
        $sidebarAlias = "application.modules.assignment.views.sidebar";
        $uid = Ibos::app()->user->uid;
        $params = array("hasSubUid" => UserUtil::hasSubUid($uid), "unfinishCount" => Assignment::model()->getUnfinishCountByUid($uid));
        return $this->renderPartial($sidebarAlias, $params, true);
    }

    protected function getSubSidebar()
    {
        $uid = Ibos::app()->user->uid;
        $deptArr = UserUtil::getManagerDeptSubUserByUid($uid);
        $params = array("deptArr" => $deptArr, "unfinishCount" => Assignment::model()->getUnfinishCountByUid($uid));
        $sidebarAlias = "application.modules.assignment.views.subsidebar";
        $sidebarView = $this->renderPartial($sidebarAlias, $params, true);
        return $sidebarView;
    }

    protected function checkIsDesigneeuid($designeeuid)
    {
        if ($designeeuid == Ibos::app()->user->uid) {
            return true;
        } else {
            return false;
        }
    }

    protected function checkIsChargeuid($chargeuid)
    {
        if ($chargeuid == Ibos::app()->user->uid) {
            return true;
        } else {
            return false;
        }
    }

    protected function checkIsParticipantuid($participantuid)
    {
        $uids = (is_array($participantuid) ? $participantuid : explode(",", $participantuid));

        if (in_array(Ibos::app()->user->uid, $uids)) {
            return true;
        } else {
            return false;
        }
    }

    protected function checkShowPermissions($assignment)
    {
        $uid = Ibos::app()->user->uid;
        $participantuid = explode(",", $assignment["participantuid"]);
        if (($uid != $assignment["designeeuid"]) && ($uid != $assignment["chargeuid"]) && !in_array($uid, $participantuid)) {
            return false;
        }

        return true;
    }

    protected function checkIsSup($assignment)
    {
        $uid = Ibos::app()->user->uid;
        $participantuid = explode(",", $assignment["participantuid"]);

        if (UserUtil::checkIsSub($uid, $assignment["designeeuid"])) {
            return true;
        }

        if (UserUtil::checkIsSub($uid, $assignment["chargeuid"])) {
            return true;
        }

        foreach ($participantuid as $puid) {
            if (UserUtil::checkIsSub($uid, $puid)) {
                return true;
            }
        }

        return false;
    }

    protected function checkAvailableById($assignmentId)
    {
        $ret = array("isSuccess" => true, "msg" => Ibos::lang("检查通过"));

        if (empty($assignmentId)) {
            $ret = array("isSuccess" => false, "msg" => Ibos::lang("Parameters error", "error"));
        }

        $assignment = Assignment::model()->fetchByPk($assignmentId);

        if (empty($assignment)) {
            $ret = array("isSuccess" => false, "msg" => Ibos::lang("抱歉，该任务已被删除"));
        }

        return $ret;
    }

    protected function getUnfinishedDataByUid($uid)
    {
        $datas = Assignment::model()->getUnfinishedByUid($uid);
        $designeeData = AssignmentUtil::handleListData($datas["designeeData"]);
        $params = array("user" => User::model()->fetchByUid($uid), "designeeData" => AssignmentUtil::handleDesigneeData($designeeData), "chargeData" => AssignmentUtil::handleListData($datas["chargeData"]), "participantData" => AssignmentUtil::handleListData($datas["participantData"]));
        return $params;
    }

    protected function getIsInstallCalendar()
    {
        return ModuleUtil::getIsEnabled("calendar");
    }

    protected function addStepComment($assignmentId, $content)
    {
        $assignment = Assignment::model()->fetchByPk($assignmentId);
        $data = array("module" => $this->getModule()->getId(), "table" => "assignment", "rowid" => $assignmentId, "moduleuid" => $assignment["designeeuid"], "uid" => Ibos::app()->user->uid, "content" => $content, "touid" => 0, "ctime" => TIMESTAMP);
        Comment::model()->add($data);
        Assignment::model()->updateCounters(array("commentcount" => 1), "`assignmentid` = $assignmentId");
    }

    protected function sendNotify($assignmentId, $subject, $toUid, $node, $result = null)
    {
        $uid = Ibos::app()->user->uid;
        $config = array("{sender}" => User::model()->fetchRealnameByUid($uid), "{subject}" => $subject, "{url}" => Ibos::app()->urlManager->createUrl("assignment/default/show", array("assignmentId" => $assignmentId)));

        if (isset($result)) {
            $config["{result}"] = $result;
        }

        if (!empty($toUid)) {
            $toUid = $this->removeSelf($toUid);
            Notify::model()->sendNotify($toUid, $node, $config, $uid);
        }
    }

    private function removeSelf($uids)
    {
        $uids = (is_array($uids) ? $uids : explode(",", $uids));
        $curUid = Ibos::app()->user->uid;

        foreach ($uids as $k => $uid) {
            if ($uid == $curUid) {
                unset($uids[$k]);
            }
        }

        return $uids;
    }
}
