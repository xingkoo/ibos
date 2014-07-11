<?php

class WorkflowManagerController extends ICController
{
    public function actionIndex()
    {
        $data = array("lang" => Ibos::getLangSources());
        $flowId = intval(EnvUtil::getRequest("flowid"));
        $list = FlowPermission::model()->fetchAllListByFlowId($flowId);

        if (!empty($list)) {
            $data["list"] = $list;
        }

        $this->renderPartial("index", $data);
    }

    public function actionAdd()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $this->beforeSave();
            $status = FlowPermission::model()->add($_POST);
            $this->ajaxReturn(array("isSuccess" => !!$status));
        } else {
            $data = array("lang" => Ibos::getLangSources(), "flowId" => EnvUtil::getRequest("flowid"));
            $this->renderPartial("add", $data);
        }
    }

    public function actionEdit()
    {
        $id = intval(EnvUtil::getRequest("id"));

        if ($id) {
            if (EnvUtil::submitCheck("formhash")) {
                $this->beforeSave();
                unset($_POST["id"]);
                $data = FlowPermission::model()->create();
                $status = FlowPermission::model()->modify($id, $data);
                $this->ajaxReturn(array("isSuccess" => !!$status));
            } else {
                $per = FlowPermission::model()->fetchByPk($id);

                if (!empty($per)) {
                    if ($per["deptid"] == "alldept") {
                        $users = "c_0";
                    } else {
                        $users = StringUtil::wrapId($per["uid"], "u") . "," . StringUtil::wrapId($per["deptid"], "d") . "," . StringUtil::wrapId($per["positionid"], "p");
                    }

                    $isCustom = !in_array($per["scope"], array("selforg", "alldept", "selfdeptall", "selfdept"));
                    $data = array("per" => $per, "lang" => Ibos::getLangSources(), "custom" => $isCustom, "users" => StringUtil::filterStr($users));
                    $this->renderPartial("edit", $data);
                } else {
                    $this->ajaxReturn(Ibos::lang("Parameters error", "error"), "eval");
                }
            }
        }
    }

    public function actionDel()
    {
        $id = intval(EnvUtil::getRequest("id"));
        if ($id && EnvUtil::submitCheck("formhash")) {
            $flowId = intval(EnvUtil::getRequest("flowid"));
            $status = FlowPermission::model()->deleteAllByAttributes(array("id" => $id, "flowid" => $flowId));
            $this->ajaxReturn(array("isSuccess" => !!$status));
        }
    }

    protected function beforeSave()
    {
        $users = $_POST["users"];
        $_POST["uid"] = $_POST["deptid"] = $_POST["positionid"] = "";
        $allIds = StringUtil::getId($users, true);

        foreach ($allIds as $prefix => $ids) {
            $id = implode(",", $ids);

            if ($prefix == "c") {
                $_POST["deptid"] = "alldept";
            }

            if ($prefix == "d") {
                $_POST["deptid"] = $id;
            }

            if ($prefix == "p") {
                $_POST["positionid"] = $id;
            }

            if ($prefix == "u") {
                $_POST["uid"] = $id;
            }
        }

        if ($_POST["scope"] === "custom") {
            $_POST["scope"] = implode(",", StringUtil::getId($_POST["scopedept"]));
        }
    }
}
