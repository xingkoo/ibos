<?php

class WorkflowQueryController extends WorkflowBaseController
{
    public function actionIndex()
    {
        $flowList = WfQueryUtil::getFlowList($this->uid);
        $this->setPageTitle(Ibos::lang("Work query"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Workflow")),
            array("name" => Ibos::lang("Work query"), "url" => $this->createUrl("query/index")),
            array("name" => Ibos::lang("List"))
        ));
        $data = array_merge(array("flowlist" => $flowList), $this->getListFilterParam(), $this->getListData());
        $this->render("index", $data);
    }

    public function actionAdvanced()
    {
        $flowList = $sort = array();
        $enabledFlowIds = WfNewUtil::getEnabledFlowIdByUid($this->uid);

        foreach (FlowType::model()->fetchAll() as $flow) {
            $catId = $flow["catid"];
            $flowId = $flow["flowid"];

            if (!isset($flowList[$catId])) {
                $sort[$catId] = array();
                $cat = FlowCategory::model()->fetchByPk($catId);

                if ($cat) {
                    $sort[$catId] = $cat;
                }
            }

            if ($flow["usestatus"] == 3) {
                continue;
            }

            $enabled = in_array($flowId, $enabledFlowIds);
            if (!$enabled && ($flow["usestatus"] == 2)) {
                continue;
            }

            $per = FlowPermission::model()->fetchPermission($this->uid, $flowId);
            if (($flow["type"] == 1) && (Ibos::app()->user->isadministrator != "1") && !$per) {
                if (!WfNewUtil::checkProcessPermission($flowId, 0, $this->uid)) {
                    continue;
                }
            }

            $handle = $done = 0;

            foreach (FlowRun::model()->fetchAllEndByFlowID($flowId) as $run) {
                if ($run["endtime"] == 0) {
                    $handle++;
                } else {
                    $done++;
                }
            }

            $flow["handle"] = $handle;
            $flow["done"] = $done;
            $flow["enabled"] = $enabled;
            $flowList[$catId][$flowId] = $flow;
        }

        ksort($flowList, SORT_NUMERIC);
        $this->setPageTitle(Ibos::lang("Select process"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Workflow")),
            array("name" => Ibos::lang("Work query"), "url" => $this->createUrl("query/index")),
            array("name" => Ibos::lang("Advanced query"), "url" => $this->createUrl("query/advanced")),
            array("name" => Ibos::lang("Select process"))
        ));
        $data = array("flows" => $flowList, "sort" => $sort);
        $this->render("advanced", $data);
    }

    public function actionSearch()
    {
        $flowId = intval(EnvUtil::getRequest("flowid"));
        $seqId = intval(EnvUtil::getRequest("id"));
        if (!$flowId && !$seqId) {
            $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("query/advanced"));
        }

        $data = array();

        if ($seqId) {
            $edit = true;
            $tpl = FlowQueryTpl::model()->fetchByPk($seqId);

            if ($tpl) {
                $flow = new ICFlowType(intval($tpl["flowid"]));
                $tpl["flow"] = unserialize($tpl["flowconditions"]);

                if (!empty($tpl["flow"]["beginuser"])) {
                    $tpl["flow"]["beginuser"] = StringUtil::wrapId($tpl["flow"]["beginuser"]);
                }

                $tpl["group"] = unserialize($tpl["groupbyfields"]);
                $tpl["viewfields"] = (!empty($tpl["viewextfields"]) ? explode(",", $tpl["viewextfields"]) : array());

                if (!empty($tpl["condformula"])) {
                    $conArr = explode("\\n", $tpl["condformula"]);
                } else {
                    $conArr = array();
                }
            } else {
                $this->error(Ibos::lang("Record does not exists", "error"), $this->createUrl("query/advanced"));
            }
        } else {
            $flow = new ICFlowType($flowId);
            $edit = false;
            $tpl = $conArr = array();
        }

        $formStructure = $flow->form->parser->getStructure();
        $defTitleArr = array(
            array("key" => "runid", "title" => Ibos::lang("Flow no")),
            array("key" => "runname", "title" => Ibos::lang("Flow subject/num")),
            array("key" => "runstatus", "title" => Ibos::lang("Flow status")),
            array("key" => "rundate", "title" => Ibos::lang("Flow begin date")),
            array("key" => "runtime", "title" => Ibos::lang("Flow begin time"))
            );
        $titleArr = array();
        $table = "flow_data_" . $flow->getID();

        foreach ($formStructure as $structure) {
            if (($structure["data-type"] == "sign") || ($structure["data-type"] == "label")) {
                continue;
            }

            $titleIdentifier = sprintf("%s.%s", $table, "data_" . $structure["itemid"]);
            $structure["data-title"] = stripslashes(str_replace(array("<", ">"), array("&lt", "&gt"), $structure["data-title"]));
            $titleArr[] = array("key" => $titleIdentifier, "title" => $structure["data-title"]);
        }

        $data["deftitle"] = $defTitleArr;
        $data["title"] = $titleArr;
        $this->setPageTitle(Ibos::lang("specify query conditions"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Workflow")),
            array("name" => Ibos::lang("Work query"), "url" => $this->createUrl("query/index")),
            array("name" => Ibos::lang("Advanced query"), "url" => $this->createUrl("query/advanced")),
            array("name" => Ibos::lang("specify query conditions"))
        ));
        $tpls = FlowQueryTpl::model()->fetchAllBySearch($flow->getID(), $this->uid);
        $data["flow"] = $flow->toArray();
        $data["tpls"] = $tpls;
        $data["edit"] = $edit;
        $data["tpl"] = $tpl;
        $data["conArr"] = $conArr;
        $data["id"] = $seqId;
        $this->render("search", $data);
    }

    public function actionAdd()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $data = $this->getPostData();
            $data["flowconditions"] = serialize($data["flowconditions"]);
            $data["groupbyfields"] = serialize($data["groupbyfields"]);
            $data["createtime"] = TIMESTAMP;

            if (FlowQueryTpl::model()->checkTplNameExists($data["tplname"])) {
                $data["tplname"] .= StringUtil::random(3);
            }

            $newID = FlowQueryTpl::model()->add($data, true);
            $this->success(Ibos::lang("Save succeed", "message"), $this->createUrl("query/search", array("id" => $newID)));
        }
    }

    public function actionSearchResult()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $data = $this->getPostData();
            $param = array_merge((array) $data["flowconditions"], array("condition" => $data["condformula"]), array("flowid" => $data["flowid"]));
        } else {
            $searchParam = EnvUtil::getRequest("key");

            if (!$searchParam) {
                $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("query/advanced"));
            }

            $param = WfCommonUtil::param($searchParam, "DECODE");
        }

        $isManager = FlowPermission::model()->fetchPermission($this->uid, $param["flowid"], array(0, 1));
        $condition = $this->getSearchCondition($param);

        if (!empty($param["condition"])) {
            $count = Ibos::app()->db->createCommand()->select("count(*)")->from("{{flow_type}} ft")->leftJoin("{{flow_run}} fr", "ft.flowid = fr.flowid")->leftJoin(sprintf("{{flow_data_%d}} fd", $param["flowid"]), "fd.runid = fr.runid")->where($condition)->queryScalar();
            $pages = PageUtil::create($count, $this->getListPageSize());
            $list = Ibos::app()->db->createCommand()->select("fr.name as runName,ft.name as typeName,fr.*,ft.*")->from("{{flow_run}} fr")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->leftJoin(sprintf("{{flow_data_%d}} fd", $param["flowid"]), "fd.runid = fr.runid")->where($condition)->order("fr.runid")->limit($pages->getLimit())->offset($pages->getOffset())->queryAll();
        } else {
            $count = Ibos::app()->db->createCommand()->select("count(*)")->from("{{flow_type}} ft")->leftJoin("{{flow_run}} fr", "ft.flowid = fr.flowid")->where($condition)->queryScalar();
            $pages = PageUtil::create($count, $this->getListPageSize());
            $list = Ibos::app()->db->createCommand()->select("fr.name as runName,ft.name as typeName,fr.*,ft.*")->from("{{flow_run}} fr")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->where($condition)->order("fr.runid")->limit($pages->getLimit())->offset($pages->getOffset())->queryAll();
        }

        $pages->params = array("key" => WfCommonUtil::param($param));

        foreach ($list as &$rec) {
            $rp = FlowRunProcess::model()->fetch(array(
                "select"    => "processid,flag,flowprocess,opflag",
                "condition" => "runid = :runid AND uid = :uid AND flag<>'4'",
                "params"    => array(":runid" => $rec["runid"], ":uid" => $this->uid),
                "order"     => "flag",
                "limit"     => 1
            ));
            $keyParam = array("flowid" => $rec["flowid"], "runid" => $rec["runid"]);

            if ($rp) {
                $rec["flag"] = $rp["flag"];
                $rec["opflag"] = $rp["opflag"];
                $keyParam["processid"] = $rp["processid"];
                $keyParam["flowprocess"] = $rp["flowprocess"];
            } else {
                $rec["flag"] = "";
            }

            $rec["key"] = WfCommonUtil::param($keyParam);
            $editper = FlowPermission::model()->fetchPermission($this->uid, $rec["flowid"], array(4));
            if ($editper || (Ibos::app()->user->isadministrator == 1)) {
                $rec["editper"] = true;
            } else {
                $rec["editper"] = false;
            }

            $rec["isend"] = false;

            if ($param["flowstatus"] == "all") {
                if ($rec["endtime"] != 0) {
                    $rec["isend"] = true;
                }
            } elseif ($param["flowstatus"] == 0) {
                $rec["isend"] = true;
            }

            if (!empty($rec["attachmentid"])) {
                $rec["attachdata"] = AttachUtil::getAttachData($rec["attachmentid"]);
            }

            $rec["focus"] = StringUtil::findIn($this->uid, $rec["focususer"]);
            $rec["user"] = User::model()->fetchByUid($rec["beginuser"]);
            $rec["begin"] = ConvertUtil::formatDate($rec["begintime"], "n月j日 H:i");
        }

        $this->setPageTitle(Ibos::lang("Query results"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Workflow")),
            array("name" => Ibos::lang("Work query"), "url" => $this->createUrl("query/index")),
            array("name" => Ibos::lang("Advanced query"), "url" => $this->createUrl("query/advanced")),
            array("name" => Ibos::lang("specify query conditions"), "url" => $this->createUrl("query/search", array("flowid" => $param["flowid"]))),
            array("name" => Ibos::lang("Query results"))
        ));
        $data = array("pages" => $pages, "list" => $list, "flowid" => $param["flowid"], "advanceOpt" => $isManager || (Ibos::app()->user->isadministrator == 1));
        $this->render("result", $data);
    }

    public function actionExport()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $op = $_POST["op"];
            $data = $this->getPostData();
            $condition = $this->getSearchCondition(array_merge($data["flowconditions"], array("flowid" => $data["flowid"])));
            WfQueryUtil::export($condition, $data, $op);
        }
    }

    protected function getSearchCondition($param)
    {
        $temp = array("and", "fr.delflag = 0", "ft.flowid = " . $param["flowid"]);

        if (!empty($param["runname"])) {
            array_push($temp, "fr.name LIKE '%{$param["runname"]}%'");
        }

        $condition = array_merge($temp, WfQueryUtil::getBeginTimeSearch($param["time1"], $param["time2"]), WfQueryUtil::getAttachNameSearch($param["attachname"]), WfQueryUtil::getFlowSearch($param["flowid"], $param["flowquerytype"], $this->uid, $param["beginuser"]), WfQueryUtil::getFlowStatusSearch($param["flowstatus"]), WfQueryUtil::getEndTimeSearch($param["time1"], $param["time2"], $param["time3"], $param["time4"]), empty($param["condition"]) ? array() : WfQueryUtil::getFormConditionSearch($param["flowid"], $param["condition"]));
        return $condition;
    }

    protected function getPostData()
    {
        $viewExtFields = StringUtil::filterStr($_POST["viewextfields"]);
        $sumFields = StringUtil::filterStr($_POST["sumfields"]);
        $flowConditions = array("flowquerytype" => $_POST["flow_query_type"], "beginuser" => !empty($_POST["begin_user"]) ? implode(",", StringUtil::getId($_POST["begin_user"])) : "", "runname" => StringUtil::filterCleanHtml($_POST["run_name"]), "flowstatus" => $_POST["flow_status"], "time1" => $_POST["time1"], "time2" => $_POST["time2"], "time3" => $_POST["time3"], "time4" => $_POST["time4"], "attachname" => StringUtil::filterCleanHtml($_POST["attach_name"]));
        $groupbyFields = array("field" => $_POST["group_field"], "order" => $_POST["group_sort"]);
        $data = array("flowid" => intval($_POST["flowid"]), "uid" => $this->uid, "tplname" => StringUtil::filterCleanHtml($_POST["tplname"]), "viewextfields" => $viewExtFields, "sumfields" => $sumFields, "flowconditions" => $flowConditions, "groupbyfields" => $groupbyFields, "condformula" => $_POST["condformula"]);
        return $data;
    }

    protected function getListFilterParam()
    {
        static $params = array();

        if (empty($params)) {
            $type = EnvUtil::getRequest("type");

            if (!in_array($type, array("all", "perform", "end"))) {
                $type = "all";
            }

            $scope = EnvUtil::getRequest("scope");

            if (!in_array($scope, array("none", "start", "handle", "manage", "focus", "custom"))) {
                $scope = "none";
            }

            $params["beginuser"] = null;

            if ($scope == "custom") {
                $beginUser = EnvUtil::getRequest("beginuser");

                if (!empty($beginUser)) {
                    $params["beginuser"] = $beginUser;
                } else {
                    $scope = "none";
                }
            }

            $time = EnvUtil::getRequest("time");
            $timeScope = array("none", "today", "yesterday", "thisweek", "lastweek", "thismonth", "lastmonth", "custom");

            if (!in_array($time, $timeScope)) {
                $time = "none";
            }

            if ($time == "custom") {
                $start = EnvUtil::getRequest("start") . "";
                $end = EnvUtil::getRequest("end") . "";
                $params["start"] = (strtotime($start) !== false ? strtotime($start) : null);
                $params["end"] = (strtotime($end) !== false ? strtotime($end) : null);
            } elseif ($time !== "none") {
                $times = DateTimeUtil::getStrTimeScope($time);
                $params["start"] = $times["start"];
                $params["end"] = $times["end"];
            } else {
                $params["start"] = $params["end"] = null;
            }

            $params["flowid"] = intval(EnvUtil::getRequest("flowid"));
            $params["type"] = $type;
            $params["scope"] = $scope;
            $params["time"] = $time;
        }

        return $params;
    }

    protected function getListData()
    {
        $param = $this->getListFilterParam();
        $field = "fr.runid,fr.name as runName,fr.begintime,fr.endtime,ft.name as typeName,fr.attachmentid,fr.focususer,fr.beginuser,ft.flowid,ft.type,ft.freeother";
        $condition = array("and", "fr.delflag = 0");

        if ($param["flowid"]) {
            $condition[] = "ft.flowid = " . $param["flowid"];
            $isManager = FlowPermission::model()->fetchPermission($this->uid, $param["flowid"], array(0, 1));
        } else {
            $isManager = false;
        }

        if ($param["start"]) {
            $condition[] = "fr.begintime >= " . $param["start"];
        }

        if ($param["end"]) {
            $condition[] = "fr.endtime <= " . $param["end"];
        }

        $flowIds = WfQueryUtil::getMyFlowIDs($this->uid);
        $myRuns = FlowRun::model()->fetchAllMyRunID($this->uid, $param["flowid"]);
        if (($param["scope"] == "none") && (Ibos::app()->user->isadministrator != 1)) {
            $condition[] = sprintf("(FIND_IN_SET(fr.runid,'%s') OR FIND_IN_SET(ft.flowid,'%s'))", implode(",", $myRuns), implode(",", $flowIds));
        } elseif ($param["scope"] == "start") {
            $beginUser = $this->uid;
        } elseif ($param["scope"] == "handle") {
            $condition[] = array("in", "fr.runid", $myRuns);
        } else {
            if (($param["scope"] == "manage") && (Ibos::app()->user->isadministrator != 1)) {
                $condition[] = sprintf("FIND_IN_SET('%s',ft.flowid)", implode(",", $flowIds));
            } elseif ($param["scope"] == "focus") {
                $implodeStr = WfCommonUtil::implodeSql($this->uid, "fr.focususer");
                $condition[] = sprintf("fr.focususer = %d%s", $this->uid, $implodeStr);
            } elseif ($param["scope"] == "custom") {
                if (Ibos::app()->user->isadministrator != 1) {
                    $condition[] = sprintf("FIND_IN_SET(ft.flowid,'%s')", implode(",", $flowIds));
                }

                $beginUser = implode(",", StringUtil::getId($param["beginuser"]));
            } elseif (Ibos::app()->user->isadministrator != 1) {
                $this->error(Ibos::lang("Parameters error", "error"));
            }
        }

        if ($param["type"] !== "all") {
            if ($param["type"] == "perform") {
                $condition[] = "fr.endtime = 0";
            } else {
                $condition[] = "fr.endtime != 0";
            }
        }

        if (isset($beginUser)) {
            $condition[] = "fr.beginuser = " . $beginUser;
        }

        $count = Ibos::app()->db->createCommand()->select("count(fr.runid)")->from("{{flow_run}} fr")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->where($condition)->queryScalar();
        $pages = PageUtil::create($count, $this->getListPageSize());
        $list = Ibos::app()->db->createCommand()->select($field)->from("{{flow_run}} fr")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->where($condition)->order("fr.runid DESC")->limit($pages->getLimit())->offset($pages->getOffset())->queryAll();

        foreach ($list as &$rec) {
            if (!empty($rec["attachmentid"])) {
                $rec["attachdata"] = AttachUtil::getAttachData($rec["attachmentid"]);
            }

            $rec["focus"] = StringUtil::findIn($this->uid, $rec["focususer"]);
            $rec["user"] = User::model()->fetchByUid($rec["beginuser"]);
            $rec["key"] = WfCommonUtil::param(array("flowid" => $rec["flowid"], "runid" => $rec["runid"]));
            $rec["begin"] = ConvertUtil::formatDate($rec["begintime"], "n月j日 H:i");
        }

        return array("list" => $list, "pages" => $pages, "advanceOpt" => $isManager || (Ibos::app()->user->isadministrator == 1));
    }
}
