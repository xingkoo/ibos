<?php

class WorkflowRecycleController extends WorkflowBaseController
{
    public function actionIndex()
    {
        $fields = "ft.name as typeName,fr.name as runName,fr.*";
        $sort = "fr.runid DESC";
        $sql = sprintf(" AND beginuser = %d", $this->uid);
        $condition = array("and", sprintf("delflag = 1%s", $sql));
        $count = Ibos::app()->db->createCommand()->select("count(*) as count")->from("{{flow_run}} fr")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->where($condition)->queryScalar();
        $pages = PageUtil::create($count, $this->getListPageSize());
        $offset = $pages->getOffset();
        $limit = $pages->getLimit();
        $list = Ibos::app()->db->createCommand()->select($fields)->from("{{flow_run}} fr")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->where($condition)->order($sort)->offset($offset)->limit($limit)->queryAll();
        $data = array_merge(array("pages" => $pages), $this->handleList($list));
        $this->setPageTitle(Ibos::lang("Work recycle"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Workflow")),
            array("name" => Ibos::lang(Ibos::lang("Work recycle")), "url" => $this->createUrl("recycle/index")),
            array("name" => Ibos::lang("List"))
        ));
        $this->render("index", $data);
    }

    public function actionRestore()
    {
        $id = EnvUtil::getRequest("id");
        $runID = StringUtil::filterStr(StringUtil::filterCleanHtml($id));
        FlowRun::model()->updateAll(array("delflag" => 0), sprintf("FIND_IN_SET(runid,'%s')", $runID));
        $this->ajaxReturn(array("isSuccess" => true));
    }

    public function actionDestroy()
    {
        $id = EnvUtil::getRequest("id");
        $runId = StringUtil::filterStr(StringUtil::filterCleanHtml($id));
        WfHandleUtil::destroy($runId);
        $this->ajaxReturn(array("isSuccess" => true));
    }

    protected function handleList($runProcess)
    {
        foreach ($runProcess as &$run) {
            $run["user"] = User::model()->fetchByUid($run["beginuser"]);
            $run["begintime"] = ConvertUtil::formatDate($run["begintime"], "u");
            $param = array("runid" => $run["runid"], "flowid" => $run["flowid"]);
            $run["key"] = WfCommonUtil::param($param);
        }

        return array("list" => $runProcess);
    }
}
