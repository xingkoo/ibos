<?php

class WorkflowTimerController extends WfsetupBaseController
{
    public function init()
    {
        $this->flowid = intval(EnvUtil::getRequest("flowid"));
        parent::init();
    }

    public function actionIndex()
    {
        if (EnvUtil::getRequest("inajax") == 1) {
            $list = FlowTimer::model()->fetchAllByFlowId($this->flowid);
            $count = count($list);
            $this->ajaxReturn(array("count" => $count, "list" => $list));
        } else {
            $this->renderPartial("index", array("lang" => Ibos::getLangSources()));
        }
    }

    public function actionSave()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $data = &$_POST;

            if (isset($data["type"])) {
                foreach ($data["type"] as $id => $type) {
                    if (empty($data["uid"][$id]) || empty($data["remindtime"][$id])) {
                        continue;
                    }

                    $type = intval($type);
                    $attr = array("type" => intval($type));

                    if (!in_array($type, array(1, 5))) {
                        $attr["reminddate"] = $data["reminddate"][$id];
                    }

                    $attr["remindtime"] = $data["remindtime"][$id];

                    if (substr($id, 0, 1) == "n") {
                        $uid = StringUtil::getId($data["uid"][$id]);
                        $attr["uid"] = implode(",", $uid);
                        $attr["flowid"] = $this->flowid;
                        FlowTimer::model()->add($attr);
                    } else {
                        FlowTimer::model()->modify($id, $attr);
                    }
                }
            }

            if (!empty($data["delid"])) {
                $id = StringUtil::filterStr($data["delid"]);
                FlowTimer::model()->deleteAll("FIND_IN_SET(tid,'$id')");
            }

            $this->ajaxReturn(array("isSuccess" => true));
        }

        exit();
    }
}
