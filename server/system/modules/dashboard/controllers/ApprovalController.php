<?php

class DashboardApprovalController extends DashboardBaseController
{
    public function actionIndex()
    {
        $approvals = Approval::model()->fetchAllApproval();
        $params = array("approvals" => $this->handleShowData($approvals));
        $this->render("index", $params);
    }

    public function actionAdd()
    {
        $formSubmit = EnvUtil::submitCheck("approvalSubmit");

        if ($formSubmit) {
            $data = $this->handleSaveData($_POST);
            $data["addtime"] = TIMESTAMP;
            Approval::model()->add($data);
            $this->success(Ibos::lang("Save succeed", "message"), $this->createUrl("approval/index"));
        } else {
            $this->render("add");
        }
    }

    public function actionEdit()
    {
        $formSubmit = EnvUtil::submitCheck("approvalSubmit");

        if ($formSubmit) {
            $id = intval(EnvUtil::getRequest("id"));
            $data = $this->handleSaveData($_POST);
            Approval::model()->modify($id, $data);
            $this->success(Ibos::lang("Update succeed", "message"), $this->createUrl("approval/index"));
        } else {
            $id = EnvUtil::getRequest("id");
            $approval = Approval::model()->fetchByPk($id);
            $approval["level1"] = StringUtil::wrapId($approval["level1"]);
            $approval["level2"] = StringUtil::wrapId($approval["level2"]);
            $approval["level3"] = StringUtil::wrapId($approval["level3"]);
            $approval["level4"] = StringUtil::wrapId($approval["level4"]);
            $approval["level5"] = StringUtil::wrapId($approval["level5"]);
            $approval["free"] = StringUtil::wrapId($approval["free"]);
            $params = array("approval" => $approval);
            $this->render("edit", $params);
        }
    }

    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $id = EnvUtil::getRequest("id");
            $delRet = Approval::model()->deleteApproval($id);

            if ($delRet) {
                $ret["isSuccess"] = true;
                $ret["msg"] = Ibos::lang("Del succeed", "message");
            } else {
                $ret["isSuccess"] = false;
                $ret["msg"] = Ibos::lang("Del failed", "message");
            }

            $this->ajaxReturn($ret);
        }
    }

    protected function handleShowData($data)
    {
        foreach ($data as $k => $approval) {
            for ($level = 1; $level <= $approval["level"]; $level++) {
                $field = "level$level";
                $data[$k]["levels"][$field] = $this->getShowNames($approval[$field]);
                $data[$k]["levels"][$field]["levelClass"] = $this->getShowLevelClass($field);
            }

            $data[$k]["free"] = $this->getShowNames($approval["free"]);
            $data[$k]["free"]["levelClass"] = $this->getShowLevelClass("free");
        }

        return $data;
    }

    protected function getShowNames($uids)
    {
        $uids = (is_array($uids) ? $uids : explode(",", $uids));
        $names = User::model()->fetchRealnamesByUids($uids);
        $nums = count($uids);

        if (4 <= $nums) {
            $show = StringUtil::cutStr($names, 30) . " 等$nums人";
        } else {
            $show = $names;
        }

        $ret = array("show" => $show, "title" => $names);
        return $ret;
    }

    protected function getShowLevelClass($level)
    {
        $allLevel = array("level1" => "o-step-1", "level2" => "o-step-2", "level3" => "o-step-3", "level4" => "o-step-4", "level5" => "o-step-5", "free" => "o-step-escape");
        return $allLevel[$level];
    }

    protected function handleSaveData($post)
    {
        $ret = array("name" => $post["name"], "level" => $post["level"], "level1" => implode(",", StringUtil::getId($post["level1"])), "level2" => implode(",", StringUtil::getId($post["level2"])), "level3" => implode(",", StringUtil::getId($post["level3"])), "level4" => implode(",", StringUtil::getId($post["level4"])), "level5" => implode(",", StringUtil::getId($post["level5"])), "free" => implode(",", StringUtil::getId($post["free"])), "desc" => $post["desc"]);
        return $ret;
    }
}
