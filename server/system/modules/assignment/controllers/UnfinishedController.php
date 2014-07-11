<?php

class AssignmentUnfinishedController extends AssignmentBaseController
{
    public function actionIndex()
    {
        $uid = Ibos::app()->user->uid;
        $params = $this->getUnfinishedDataByUid($uid);
        $params["uploadConfig"] = AttachUtil::getUploadConfig();
        $this->setPageTitle(Ibos::lang("Assignment"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Assignment"), "url" => $this->createUrl("unfinished/index")),
            array("name" => Ibos::lang("Unfinished list"))
        ));
        $this->render("list", $params);
    }

    public function actionSubList()
    {
        if (EnvUtil::getRequest("op") == "getsubordinates") {
            $this->getsubordinates();
            exit();
        }

        $getUid = intval(EnvUtil::getRequest("uid"));

        if (!$getUid) {
            $deptArr = UserUtil::getManagerDeptSubUserByUid(Ibos::app()->user->uid);

            if (!empty($deptArr)) {
                $firstDept = reset($deptArr);
                $uid = $firstDept["user"][0]["uid"];
            } else {
                $this->error(IBos::lang("You do not subordinate"), $this->createUrl("schedule/index"));
            }
        } else {
            $uid = $getUid;
        }

        if (!UserUtil::checkIsSub(Ibos::app()->user->uid, $uid)) {
            $this->error(Ibos::lang("No permission to view schedule"), $this->createUrl("schedule/index"));
        }

        $params = $this->getUnfinishedDataByUid($uid);
        $this->setPageTitle(Ibos::lang("Assignment"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Assignment"), "url" => $this->createUrl("unfinished/index")),
            array("name" => Ibos::lang("Unfinished list"))
        ));
        $this->render("sublist", $params);
    }

    protected function getsubordinates()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $uid = intval(EnvUtil::getRequest("uid"));
            $getItem = EnvUtil::getRequest("item");
            $item = (empty($getItem) ? 5 : $getItem);
            $users = UserUtil::getAllSubs($uid);
            $subAlias = "application.modules.assignment.views.unfinished.subview";
            $subView = $this->renderPartial($subAlias, array("users" => $users, "item" => $item, "uid" => $uid), true);
            echo $subView;
        }
    }

    public function actionAjaxEntrance()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $op = EnvUtil::getRequest("op");
            $allowOptions = array("push", "toFinished", "stamp", "restart", "applyDelay", "delay", "runApplyDelayResult", "applyCancel", "cancel", "runApplyCancelResult", "remind");

            if (!in_array($op, $allowOptions)) {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Parameters error", "error")));
            } else {
                $assignmentId = EnvUtil::getRequest("id");
                $paramCheck = $this->checkAvailableById($assignmentId);

                if (!$paramCheck["isSuccess"]) {
                    $this->ajaxReturn($paramCheck);
                }

                $this->{$op}($assignmentId);
            }
        }
    }

    protected function push($assignmentId)
    {
        $assignment = Assignment::model()->fetchByPk($assignmentId);

        if (!$this->checkIsDesigneeuid($assignment["designeeuid"])) {
            $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Only the sponsors have permission to remind")));
        }

        $this->sendNotify($assignmentId, $assignment["subject"], $assignment["chargeuid"], "assignment_push_message");
        $this->addStepComment($assignmentId, Ibos::lang("Push the assignment"));
        AssignmentLog::model()->addLog($assignmentId, "push", Ibos::lang("Push the assignment"));
        $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Operation succeed", "message")));
    }

    protected function toFinished($assignmentId)
    {
        $assignment = Assignment::model()->fetchByPk($assignmentId);
        $isDesigneeuid = $this->checkIsDesigneeuid($assignment["designeeuid"]);
        $isChargeuid = $this->checkIsChargeuid($assignment["chargeuid"]);
        if (!$isDesigneeuid && !$isChargeuid) {
            $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Only the sponsors or head have permission to complete")));
        }

        $updateSuccess = Assignment::model()->modify($assignmentId, array("status" => 2, "finishtime" => TIMESTAMP));

        if ($updateSuccess) {
            $this->sendNotify($assignmentId, $assignment["subject"], $assignment["designeeuid"], "assignment_finish_message");
            UserUtil::updateCreditByAction("finishassignment", Ibos::app()->user->uid);
            $this->addStepComment($assignmentId, Ibos::lang("Finish the assignment"));
            AssignmentLog::model()->addLog($assignmentId, "finish", Ibos::lang("Finish the assignment"));
            $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Operation succeed", "message")));
        } else {
            $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("The assignment has been completed")));
        }
    }

    protected function stamp($assignmentId)
    {
        $assignment = Assignment::model()->fetchByPk($assignmentId);

        if ($assignment["status"] == 2) {
            $stamp = intval(EnvUtil::getRequest("stamp"));
            Assignment::model()->modify($assignmentId, array("stamp" => $stamp, "status" => 3));
            $chargeuid = explode(",", $assignment["chargeuid"]);
            $participantuid = explode(",", $assignment["participantuid"]);
            $uidArr = array_merge($participantuid, $chargeuid);
            $this->sendNotify($assignmentId, $assignment["subject"], $uidArr, "assignment_appraisal_message");
            $stampInfo = Stamp::model()->fetchByPk($stamp);
            $this->addStepComment($assignmentId, Ibos::lang("Stamp the assignment") . "-" . $stampInfo["code"]);
            AssignmentLog::model()->addLog($assignmentId, "stamp", Ibos::lang("Stamp the assignment") . "-" . $stampInfo["code"]);
            $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Operation succeed", "message")));
        } else {
            $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Assignment has not been finished")));
        }
    }

    protected function restart($assignmentId)
    {
        $assignment = Assignment::model()->fetchByPk($assignmentId);
        $isDesigneeuid = $this->checkIsDesigneeuid($assignment["designeeuid"]);
        $isChargeuid = $this->checkIsChargeuid($assignment["chargeuid"]);
        if (!$isDesigneeuid && !$isChargeuid) {
            $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Only the sponsors or head have permission to restart")));
        }

        $updateSuccess = Assignment::model()->modify($assignmentId, array("status" => 1, "finishtime" => 0, "stamp" => 0));

        if ($updateSuccess) {
            $this->addStepComment($assignmentId, Ibos::lang("Restart the assignment"));
            AssignmentLog::model()->addLog($assignmentId, "restart", Ibos::lang("Restart the assignment"));
            $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Operation succeed", "message")));
        } else {
            $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Task is the initial state")));
        }
    }

    protected function applyDelay($assignmentId)
    {
        $assignment = Assignment::model()->fetchByPk($assignmentId);

        if ($this->checkIsChargeuid($assignment["chargeuid"])) {
            $postStattime = EnvUtil::getRequest("starttime");
            $postEndtime = EnvUtil::getRequest("endtime");

            if (empty($postEndtime)) {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("The end time cannot be empty")));
            }

            $delayReason = EnvUtil::getRequest("delayReason");
            $uid = Ibos::app()->user->uid;
            $starttime = (empty($postStattime) ? TIMESTAMP : strtotime($postStattime));
            $endtime = strtotime($postEndtime);
            AssignmentApply::model()->addDelay($uid, $assignmentId, $delayReason, $starttime, $endtime);
            $this->sendNotify($assignmentId, $assignment["subject"], $assignment["designeeuid"], "assignment_applydelay_message");
            $this->addStepComment($assignmentId, Ibos::lang("Apply delay the assignment"));
            AssignmentLog::model()->addLog($assignmentId, "applydelay", Ibos::lang("Apply delay the assignment"));
            $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Operation succeed", "message")));
        }
    }

    protected function delay($assignmentId)
    {
        $assignment = Assignment::model()->fetchByPk($assignmentId);

        if ($this->checkIsDesigneeuid($assignment["designeeuid"])) {
            $delayStattime = strtotime(EnvUtil::getRequest("starttime"));
            $delayEndtime = strtotime(EnvUtil::getRequest("endtime"));

            if (empty($delayEndtime)) {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("The end time cannot be empty")));
            }

            $this->handleDelay($assignmentId, $delayStattime, $delayEndtime);
            $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Operation succeed", "message")));
        }
    }

    protected function runApplyDelayResult($assignmentId)
    {
        $agree = intval(EnvUtil::getRequest("agree"));

        if ($agree) {
            $apply = AssignmentApply::model()->fetchByAttributes(array("assignmentid" => $assignmentId));

            if (!empty($apply)) {
                $this->handleDelay($assignmentId, $apply["delaystarttime"], $apply["delayendtime"]);
            }

            $this->addStepComment($assignmentId, Ibos::lang("Agree delay the assignment"));
            AssignmentLog::model()->addLog($assignmentId, "agreedelay", Ibos::lang("Agree delay the assignment"));
            $result = Ibos::lang("Agree");
        } else {
            AssignmentApply::model()->deleteAll("assignmentid = $assignmentId");
            $this->addStepComment($assignmentId, Ibos::lang("Refuse delay the assignment"));
            AssignmentLog::model()->addLog($assignmentId, "refusedelay", Ibos::lang("Refuse delay the assignment"));
            $result = Ibos::lang("Refuse");
        }

        $assignment = Assignment::model()->fetchByPk($assignmentId);
        $this->sendNotify($assignmentId, $assignment["subject"], $assignment["chargeuid"], "assignment_applydelayresult_message", $result);
        $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Operation succeed", "message")));
    }

    private function handleDelay($assignmentId, $delayStarttime, $delayEndtime)
    {
        Assignment::model()->modify($assignmentId, array("starttime" => $delayStarttime, "endtime" => $delayEndtime));
        AssignmentApply::model()->deleteAll("assignmentid = $assignmentId");
        $this->addStepComment($assignmentId, Ibos::lang("Delay the assignment"));
        AssignmentLog::model()->addLog($assignmentId, "delay", Ibos::lang("Delay the assignment"));
        return true;
    }

    protected function applyCancel($assignmentId)
    {
        $assignment = Assignment::model()->fetchByPk($assignmentId);

        if ($assignment["status"] == 2) {
            $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("The completed assignment cannot be cancelled, can restart")));
        }

        if ($this->checkIsChargeuid($assignment["chargeuid"])) {
            $cancelReason = EnvUtil::getRequest("cancelReason");
            AssignmentApply::model()->addCancel(Ibos::app()->user->uid, $assignmentId, $cancelReason);
            $this->sendNotify($assignmentId, $assignment["subject"], $assignment["designeeuid"], "assignment_applycancel_message");
            $this->addStepComment($assignmentId, Ibos::lang("Apply cancel the assignment"));
            AssignmentLog::model()->addLog($assignmentId, "applycancel", Ibos::lang("Apply cancel the assignment"));
            $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Operation succeed", "message")));
        }
    }

    protected function cancel($assignmentId)
    {
        $assignment = Assignment::model()->fetchByPk($assignmentId);

        if ($assignment["status"] == 2) {
            $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("The completed assignment cannot be cancelled, can restart")));
        }

        if ($this->checkIsDesigneeuid($assignment["designeeuid"])) {
            $this->handleCancel($assignmentId);
            $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Operation succeed", "message")));
        }
    }

    protected function runApplyCancelResult($assignmentId)
    {
        $agree = intval(EnvUtil::getRequest("agree"));

        if ($agree) {
            $this->addStepComment($assignmentId, Ibos::lang("Agree cancel the assignment"));
            AssignmentLog::model()->addLog($assignmentId, "agreecancel", Ibos::lang("Agree cancel the assignment"));
            $this->handleCancel($assignmentId);
            $result = Ibos::lang("Agree");
        } else {
            $result = Ibos::lang("Refuse");
            AssignmentApply::model()->deleteAll("assignmentid = $assignmentId");
            $this->addStepComment($assignmentId, Ibos::lang("Refuse cancel the assignment"));
            AssignmentLog::model()->addLog($assignmentId, "refusecancel", Ibos::lang("Refuse cancel the assignment"));
        }

        $assignment = Assignment::model()->fetchByPk($assignmentId);
        $this->sendNotify($assignmentId, $assignment["subject"], $assignment["chargeuid"], "assignment_applycancelresult_message", $result);
        $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Operation succeed", "message")));
    }

    private function handleCancel($assignmentId)
    {
        Assignment::model()->modify($assignmentId, array("status" => 4));
        AssignmentApply::model()->deleteAll("assignmentid = $assignmentId");
        $this->addStepComment($assignmentId, Ibos::lang("Cancel the assignment"));
        AssignmentLog::model()->addLog($assignmentId, "cancel", Ibos::lang("Cancel the assignment"));
        return true;
    }

    protected function remind($assignmentId)
    {
        if (EnvUtil::submitCheck("remindsubmit")) {
            if ($this->getIsInstallCalendar()) {
                $uid = Ibos::app()->user->uid;
                $remindTime = strtotime(EnvUtil::getRequest("remindTime"));
                $remindContent = EnvUtil::getRequest("remindContent");
                $calendar = array("subject" => $remindContent, "starttime" => $remindTime, "endtime" => $remindTime + 1800, "uid" => $uid, "upuid" => $uid, "lock" => 1, "category" => 5);
                $oldCalendarids = AssignmentRemind::model()->fetchCalendarids($assignmentId, $uid);
                Calendars::model()->deleteAll(sprintf("uid = %d AND FIND_IN_SET(`calendarid`, '%s')", $uid, implode(",", $oldCalendarids)));
                $cid = Calendars::model()->add($calendar, true);
                AssignmentRemind::model()->deleteAll("assignmentid = $assignmentId AND uid = $uid");
                AssignmentRemind::model()->add(array("assignmentid" => $assignmentId, "calendarid" => $cid, "remindtime" => $remindTime, "uid" => $uid, "content" => $remindContent));
                $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Operation succeed", "message")));
            } else {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Not installed calendar to support remind")));
            }
        } else {
            $remind = AssignmentRemind::model()->fetch(sprintf("uid = %d AND assignmentid = %d", Ibos::app()->user->uid, $assignmentId));
            $remindtime = (empty($remind) ? TIMESTAMP : $remind["remindtime"]);
            $params = array("reminddate" => date("Y-m-d", $remindtime), "remindtime" => date("H:i", $remindtime), "content" => empty($remind) ? "" : $remind["content"], "lang" => Ibos::getLangSource("assignment.default"));
            $remindAlias = "application.modules.assignment.views.default.remind";
            $editView = $this->renderPartial($remindAlias, $params, true);
            echo $editView;
        }
    }
}
