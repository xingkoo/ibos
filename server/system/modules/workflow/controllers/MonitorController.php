<?php

class WorkflowMonitorController extends WorkflowBaseController
{
    public function actionIndex()
    {
        $flowList = WfQueryUtil::getFlowList($this->uid);
        $this->setPageTitle(Ibos::lang("Work monitor"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Workflow")),
            array("name" => Ibos::lang(Ibos::lang("Work monitor")), "url" => $this->createUrl("monitor/index")),
            array("name" => Ibos::lang("List"))
        ));
        $data = array_merge(array("flowlist" => $flowList), $this->getListData());
        $this->render("index", $data);
    }

    protected function getListData()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $runid = intval(EnvUtil::getRequest("runid"));
            $flowid = intval(EnvUtil::getRequest("flowid"));
            $userType = EnvUtil::getRequest("usertype");
            $runName = StringUtil::filterCleanHtml(EnvUtil::getRequest("runname"));
            $toid = (!empty($_POST["toid"]) ? implode(",", StringUtil::getId($_POST["toid"])) : "");
        } else {
            $runid = 0;
            $userType = $runName = $toid = "";
            $flowid = "all";
        }

        $flowIds = WfQueryUtil::getMyFlowIDs($this->uid);

        if (empty($flowIds)) {
            $flowIds = array(0);
        }

        $condition = array(
            "and",
            "fr.delflag = 0",
            array("in", "fr.flowid", $flowIds),
            array(
                "in",
                "frp.flag",
                array(1, 2)
                ),
            "(frp.opflag = 1 OR frp.topflag = 2)"
            );
        $field = "frp.runid,frp.processid,frp.uid,frp.flag,frp.processtime,frp.flowprocess,fr.attachmentid,fr.focususer,ft.freeother";

        if ($flowid !== "all") {
            $condition[] = "ft.flowid = " . $flowid;
        }

        if (!empty($runid)) {
            $condition[] = "fr.runid = " . $runid;
        }

        if (!empty($runName)) {
            $condition[] = " fr.name LIKE '%$runName%'";
        }

        if ($toid != "") {
            if ($userType == "opuser") {
                $condition[] = "frp.uid = $toid";
            } else {
                $condition[] = "fr.beginuser = $toid";
            }
        }

        $lang = Ibos::getLangSource("workflow.default");
        $count = Ibos::app()->db->createCommand()->select("count(fr.runid)")->from("{{flow_run}} fr")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->leftJoin("{{flow_run_process}} frp", "fr.runid = frp.runid")->where($condition)->queryScalar();
        $pages = PageUtil::create($count, $this->getListPageSize());
        $list = Ibos::app()->db->createCommand()->select($field)->from("{{flow_run}} fr")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->leftJoin("{{flow_run_process}} frp", "fr.runid = frp.runid")->where($condition)->group("frp.runid")->order("frp.runid DESC")->limit($pages->getLimit())->offset($pages->getOffset())->queryAll();

        foreach ($list as $k => &$rec) {
            $temp = Ibos::app()->db->createCommand()->select("ft.flowid,ft.freeother,fr.name as runName,ft.name as typeName,ft.type,ft.sort")->from("{{flow_type}} ft")->leftJoin("{{flow_run}} fr", "fr.flowid = ft.flowid")->where("fr.runid = " . $rec["runid"])->queryRow();

            if ($temp) {
                $rec = array_merge($rec, $temp);
            } else {
                continue;
            }

            if ($temp["type"] == 1) {
                $fp = FlowProcess::model()->fetchProcess($temp["flowid"], $rec["flowprocess"]);

                if ($fp) {
                    $rec["stepname"] = $fp["name"];
                } else {
                    $rec["stepname"] = $lang["Process steps already deleted"];
                }
            } else {
                $rec["stepname"] = Ibos::lang("Step", "", array("{step}" => $rec["processid"]));
            }

            if ($rec["flag"] == FlowConst::PRCS_UN_RECEIVE) {
                $deliverTime = FlowRunProcess::model()->fetchDeliverTime($rec["runid"], $rec["flowprocess"]);

                if ($deliverTime) {
                    $prcsBeginTime = $deliverTime;
                }
            } else {
                $prcsBeginTime = $rec["processtime"];
            }

            if (!isset($prcsBeginTime) || ($prcsBeginTime == 0)) {
                $prcsBeginTime = TIMESTAMP;
            }

            $usedTime = TIMESTAMP - $prcsBeginTime;
            $rec["timestr"] = WfCommonUtil::getTime($usedTime, "dhi");

            if (!empty($rec["attachmentid"])) {
                $rec["attachdata"] = AttachUtil::getAttachData($rec["attachmentid"]);
            }

            $rec["focus"] = StringUtil::findIn($this->uid, $rec["focususer"]);
            $rec["user"] = User::model()->fetchByUid($rec["uid"]);
            $rec["key"] = WfCommonUtil::param(array("flowid" => $rec["flowid"], "runid" => $rec["runid"], "processid" => $rec["processid"], "flowprocess" => $rec["flowprocess"]));

            if (empty($rec["user"])) {
                unset($list[$k]);
            }
        }

        return array("list" => $list, "pages" => $pages);
    }
}
