<?php

class AssignmentDefaultController extends AssignmentBaseController
{
    /**
     * 图章id(暂定3个，4干得不错，2有进步，3继续努力)
     * @var array
     */
    private $_stamps = array(4, 2, 3);

    public function actionAdd()
    {
        if (EnvUtil::submitCheck("addsubmit")) {
            $this->beforeSave($_POST);
            $uid = Ibos::app()->user->uid;
            $assignment = $this->handlePostData();
            $assignment["designeeuid"] = $uid;
            $assignment["addtime"] = TIMESTAMP;
            $assignmentId = Assignment::model()->add($assignment, true);

            if (!empty($assignment["attachmentid"])) {
                AttachUtil::updateAttach($assignment["attachmentid"]);
            }

            $chargeuid = StringUtil::getId($_POST["chargeuid"]);
            $participantuid = StringUtil::getId($_POST["participantuid"]);
            $uidArr = array_merge($participantuid, $chargeuid);
            $this->sendNotify($assignmentId, $assignment["subject"], $uidArr, "assignment_new_message");
            $wbconf = WbCommonUtil::getSetting(true);
            if (isset($wbconf["wbmovement"]["assignment"]) && ($wbconf["wbmovement"]["assignment"] == 1)) {
                $data = array("title" => Ibos::lang("Feed title", "", array("{subject}" => $assignment["subject"], "{url}" => Ibos::app()->urlManager->createUrl("assignment/default/show", array("assignmentId" => $assignmentId)))), "body" => $assignment["subject"], "actdesc" => Ibos::lang("Post assignment"), "userid" => implode(",", $uidArr), "deptid" => "", "positionid" => "");
                WbfeedUtil::pushFeed($uid, "assignment", "assignment", $assignmentId, $data, "post");
            }

            $this->addStepComment($assignmentId, Ibos::lang("Add the assignment"));
            AssignmentLog::model()->addLog($assignmentId, "add", Ibos::lang("Add the assignment"));
            $returnData = array("charge" => User::model()->fetchByUid($assignment["chargeuid"]), "id" => $assignmentId, "subject" => $assignment["subject"], "time" => date("m月d日 H:i", $assignment["starttime"]) . "--" . date("m月d日 H:i", $assignment["endtime"]));
            $this->ajaxReturn(array("isSuccess" => true, "data" => $returnData));
        }
    }

    public function actionEdit()
    {
        if (!EnvUtil::submitCheck("updatesubmit")) {
            $assignmentId = intval(EnvUtil::getRequest("id"));
            $checkRes = $this->checkAvailableById($assignmentId);

            if (!$checkRes["isSuccess"]) {
                $this->ajaxReturn($checkRes);
            }

            $assignment = Assignment::model()->fetchByPk($assignmentId);
            $uid = Ibos::app()->user->uid;

            if ($uid != $assignment["designeeuid"]) {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("You donot have permission to edit")));
            }

            if (!empty($assignment["attachmentid"])) {
                $assignment["attachs"] = AttachUtil::getAttach($assignment["attachmentid"]);
            }

            $assignment["starttime"] = (empty($assignment["starttime"]) ? "" : date("Y-m-d H:i", $assignment["starttime"]));
            $assignment["endtime"] = (empty($assignment["endtime"]) ? "" : date("Y-m-d H:i", $assignment["endtime"]));
            $assignment["chargeuid"] = StringUtil::wrapId($assignment["chargeuid"]);
            $assignment["participantuid"] = StringUtil::wrapId($assignment["participantuid"]);
            $assignment["lang"] = Ibos::getLangSource("assignment.default");
            $assignment["assetUrl"] = Ibos::app()->assetManager->getAssetsUrl("assignment");
            $editAlias = "application.modules.assignment.views.default.edit";
            $editView = $this->renderPartial($editAlias, $assignment, true);
            echo $editView;
        } else {
            $assignmentId = intval(EnvUtil::getRequest("id"));
            $assignment = Assignment::model()->fetchByPk($assignmentId);
            $this->beforeSave($_POST);
            $uid = Ibos::app()->user->uid;
            $data = $this->handlePostData();
            $data["updatetime"] = TIMESTAMP;
            $updateSuccess = Assignment::model()->updateByPk($assignmentId, $data);

            if ($updateSuccess) {
                AttachUtil::updateAttach($data["attachmentid"]);

                if ($data["chargeuid"] != $assignment["chargeuid"]) {
                    $chargeuid = StringUtil::getId($_POST["chargeuid"]);
                    $participantuid = StringUtil::getId($_POST["participantuid"]);
                    $uidArr = array_merge($participantuid, $chargeuid);
                    $this->sendNotify($assignmentId, $data["subject"], $uidArr, "assignment_new_message");
                }

                $this->addStepComment($assignmentId, Ibos::lang("Eidt the assignment"));
                AssignmentLog::model()->addLog($assignmentId, "edit", Ibos::lang("Eidt the assignment"));
                $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Update succeed", "message")));
            } else {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Update failed", "message")));
            }
        }
    }

    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $assignmentId = intval(EnvUtil::getRequest("id"));
            $checkRes = $this->checkAvailableById($assignmentId);

            if (!$checkRes["isSuccess"]) {
                $this->ajaxReturn($checkRes);
            }

            $assignment = Assignment::model()->fetchByPk($assignmentId);
            $uid = Ibos::app()->user->uid;

            if ($uid != $assignment["designeeuid"]) {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("You donot have permission to delete")));
            }

            if (!empty($assignment["attachmentid"])) {
                AttachUtil::delAttach($assignment["attachmentid"]);
            }

            if ($this->getIsInstallCalendar() && !empty($assignment["remindtime"])) {
                Calendars::model()->deleteALL("`calendarid` IN(select `calendarid` from {{assignment_remind}} where assignmentid = $assignmentId) ");
                AssignmentRemind::model()->deleteAll("assignmentid = $assignmentId");
            }

            AssignmentLog::model()->addLog($assignmentId, "del", Ibos::lang("Delete the assignment"));
            Assignment::model()->deleteByPk($assignmentId);
            AssignmentApply::model()->deleteAll("assignmentid = $assignmentId");
            $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Del succeed", "message")));
        }
    }

    public function actionShow()
    {
        $op = EnvUtil::getRequest("op");

        if (empty($op)) {
            $assignmentId = intval(EnvUtil::getRequest("assignmentId"));
            $checkRes = $this->checkAvailableById($assignmentId);

            if (!$checkRes["isSuccess"]) {
                $this->error($checkRes["msg"], $this->createUrl("unfinished/index"));
            }

            $assignment = Assignment::model()->fetchByPk($assignmentId);
            if (!$this->checkShowPermissions($assignment) && !$this->checkIsSup($assignment)) {
                $this->error(Ibos::lang("You donot have permission to view"), $this->createUrl("unfinished/index"));
            }

            if (!empty($assignment["attachmentid"])) {
                $assignment["attach"] = AttachUtil::getAttach($assignment["attachmentid"]);
            }

            if (!empty($assignment["stamp"])) {
                $assignment["stampUrl"] = Stamp::model()->fetchStampById($assignment["stamp"]);
            }

            $apply = AssignmentApply::model()->fetchByAttributes(array("assignmentid" => $assignmentId));
            $applyData = $this->handleApplyData($assignmentId, $apply);
            $isDesigneeuid = $this->checkIsDesigneeuid($assignment["designeeuid"]);
            $isChargeuid = $this->checkIsChargeuid($assignment["chargeuid"]);
            if ($isChargeuid && ($assignment["status"] == 0)) {
                Assignment::model()->modify($assignmentId, array("status" => 1));
                $assignment["status"] = 1;
            }

            AssignmentLog::model()->addLog($assignmentId, "view", Ibos::lang("View the assignment"));
            $participantuidArr = explode(",", $assignment["participantuid"]);
            $participantuid = array_filter($participantuidArr, create_function("\$v", "return !empty(\$v);"));
            $reminds = AssignmentRemind::model()->fetchAllByUid(Ibos::app()->user->uid);
            $assignment["remindtime"] = (in_array($assignmentId, array_keys($reminds)) ? $reminds[$assignmentId] : 0);
            $params = array("isDesigneeuid" => $isDesigneeuid, "isChargeuid" => $isChargeuid, "designee" => User::model()->fetchByUid($assignment["designeeuid"]), "charge" => User::model()->fetchByUid($assignment["chargeuid"]), "participantCount" => count($participantuid), "participant" => User::model()->fetchRealnamesByUids($participantuid, "、"), "assignment" => AssignmentUtil::handleShowData($assignment), "applyData" => CJSON::encode($applyData));
            $this->setPageTitle(Ibos::lang("See the assignment details"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Assignment"), "url" => $this->createUrl("unfinished/index")),
                array("name" => Ibos::lang("Assignment details"))
            ));
            $this->render("show", $params);
        } else {
            $this->{$op}();
        }
    }

    protected function beforeSave($postData)
    {
        if (empty($postData["chargeuid"])) {
            $this->error(Ibos::lang("Head cannot be empty"), $this->createUrl("unfinished/index"));
        }

        if (empty($postData["subject"])) {
            $this->error(Ibos::lang("Content cannot be empty"), $this->createUrl("unfinished/index"));
        }

        if (empty($postData["endtime"])) {
            $this->error(Ibos::lang("The end time cannot be empty"), $this->createUrl("unfinished/index"));
        }
    }

    private function handlePostData()
    {
        $chargeuid = StringUtil::getId($_POST["chargeuid"]);
        $participantuid = StringUtil::getId($_POST["participantuid"]);
        $data = array("subject" => StringUtil::filterStr($_POST["subject"]), "description" => StringUtil::filterStr($_POST["description"]), "chargeuid" => implode(",", $chargeuid), "participantuid" => implode(",", $participantuid), "attachmentid" => trim($_POST["attachmentid"], ","), "starttime" => empty($_POST["starttime"]) ? TIMESTAMP : strtotime($_POST["starttime"]), "endtime" => strtotime($_POST["endtime"]));
        return $data;
    }

    private function handleApplyData($assignmentId, $apply)
    {
        $applyData = array();

        if (!empty($apply)) {
            if ($apply["isdelay"]) {
                $applyData = array("id" => $assignmentId, "uid" => $apply["uid"], "reason" => $apply["delayreason"], "startTime" => date("m月d日 H:i", $apply["delaystarttime"]), "endTime" => date("m月d日 H:i", $apply["delayendtime"]));
            } else {
                $applyData = array("id" => $assignmentId, "uid" => $apply["uid"], "reason" => $apply["cancelreason"]);
            }
        }

        return $applyData;
    }

    public function getStamps()
    {
        $stamps = array();

        foreach ($this->_stamps as $id) {
            $stamp = Stamp::model()->fetchByPk($id);
            $stamps[] = array("path" => FileUtil::fileName(stamp::STAMP_PATH . $stamp["icon"]), "stamp" => $stamp["stamp"], "title" => $stamp["code"], "value" => $id);
        }

        return $stamps;
    }
}
