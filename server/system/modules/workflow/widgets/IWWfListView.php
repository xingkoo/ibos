<?php

class IWWfListView extends IWWfListContent
{
    /**
     * 分页条数
     * @var integer 
     */
    protected $limit = 10;
    /**
     * 分页偏移
     * @var integer 
     */
    protected $offset = 0;
    /**
     * 列表显示多少条
     * @var integer 
     */
    protected $pageSize = 0;
    /**
     * 视图变量
     * @var array 
     */
    private $_var = array();

    public function run()
    {
        $this->setPages();
        $var["lang"] = Ibos::getLangSources();
        $var["list"] = $this->handleList($this->getRunProcess());
        $var["type"] = $this->getType();
        $var["sort"] = $this->getSort();
        $var["op"] = $this->getOp();
        $view = "wfwidget.list" . $this->getType();
        $this->render($view, array_merge($this->_var, $var));
    }

    protected function getOffset()
    {
        return $this->offset;
    }

    protected function getLimit()
    {
        return $this->limit;
    }

    protected function setLimit($limit)
    {
        $this->limit = intval($limit);
    }

    protected function setOffset($offset)
    {
        $this->offset = intval($offset);
    }

    public function getPageSize()
    {
        return $this->pageSize;
    }

    public function setPageSize($size)
    {
        $this->pageSize = intval($size);
    }

    protected function setPages()
    {
        $dataReader = Ibos::app()->db->createCommand()->select("count(*)")->from("{{flow_run_process}} frp")->leftJoin("{{flow_run}} fr", "frp.runid = fr.runid")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->where($this->getCondition())->group($this->getQueryGroup())->query();
        $count = $dataReader->count();
        $pages = PageUtil::create($count, $this->getPageSize());
        if (($this->getKeyword() != "") && $count) {
            $pages->params = array("keyword" => $this->getKeyword());
        }

        $this->setOffset($pages->getOffset());
        $this->setLimit($pages->getLimit());
        $this->_var["pages"] = $pages;
    }

    private function handleList($list)
    {
        $allProcess = FlowProcess::model()->fetchAllProcessSortByFlowId();

        foreach ($list as &$run) {
            $run["user"] = User::model()->fetchByUid($run["beginuser"]);

            if ($this->getIsOver()) {
                $this->refreshRun($run);
            }

            $this->setStepName($run, $allProcess);
            $this->setOtherInfo($run);
            $this->setOpt($run);
        }

        return $list;
    }

    private function refreshRun(&$run)
    {
        $rp = FlowRunProcess::model()->fetchCurrentNextRun($run["runid"], $this->uid, $this->flag);

        if (!empty($rp)) {
            $run["processid"] = $rp["processid"];
            $run["flowprocess"] = $rp["flowprocess"];
            $run["opflag"] = $rp["opflag"];
            $run["flag"] = $rp["flag"];
        }
    }

    private function setStepName(&$run, $allProcess)
    {
        if ($run["type"] == 1) {
            if (isset($allProcess[$run["flowid"]][$run["flowprocess"]]["name"])) {
                $run["stepname"] = $allProcess[$run["flowid"]][$run["flowprocess"]]["name"];
            } else {
                $run["stepname"] = Ibos::lang("Process steps already deleted", "workflow.default");
            }
        } else {
            $run["stepname"] = Ibos::lang("No.step", "workflow.default", array("{step}" => $run["processid"]));
        }
    }

    private function setOtherInfo(&$run)
    {
        if ($this->type !== "done") {
            $run["focus"] = StringUtil::findIn($this->uid, $run["focususer"]);
        } elseif (!empty($run["endtime"])) {
            $usedTime = $run["endtime"] - $run["begintime"];
            $run["usedtime"] = WfCommonUtil::getTime($usedTime);
        }

        $param = array("runid" => $run["runid"], "flowid" => $run["flowid"], "processid" => $run["processid"], "flowprocess" => $run["flowprocess"]);
        $run["key"] = WfCommonUtil::param($param);
    }

    private function setOpt(&$run)
    {
        $this->getHandleOpt($run);
        $this->getEntrustOpt($run);
        $this->getRollbackOpt($run);
        $this->getTurnOpt($run);
        $this->getEndOpt($run);
        $this->getDelayOpt($run);
        $this->getRestoreOpt($run);
        $this->getDelOpt($run);
    }

    private function getHandleOpt(&$run)
    {
        if ($this->getIsTodo()) {
            if ($run["opflag"] == "1") {
                $run["host"] = true;
            } else {
                $run["sign"] = true;
            }
        }
    }

    private function getEntrustOpt(&$run)
    {
        if ($this->getIsTodo()) {
            if ((($run["freeother"] == "1") && ($run["opflag"] == "1")) || in_array($run["freeother"], array(2, 3))) {
                $run["entrust"] = true;
            }
        }
    }

    private function getRollbackOpt(&$run)
    {
        if ($run["opflag"] && ($run["flag"] == FlowConst::PRCS_TRANS) && ($run["endtime"] == 0) && $this->getIsOver()) {
            $run["rollback"] = true;
        }
    }

    private function getTurnOpt(&$run)
    {
        if (($run["flag"] == FlowConst::PRCS_HANDLE) && !$this->getIsOver()) {
            if ($run["opflag"] == "1") {
                $run["turn"] = true;
            }
        }
    }

    private function getEndOpt(&$run)
    {
        if (($run["flag"] == FlowConst::PRCS_HANDLE) && !$this->getIsOver()) {
            if (($run["type"] != 1) && ($run["opflag"] == "1")) {
                $run["end"] = true;
            }
        }
    }

    private function getDelayOpt(&$run)
    {
        if ($this->getIsTodo()) {
            $run["delay"] = true;
        }
    }

    private function getRestoreOpt(&$run)
    {
        if ($this->getIsDelay()) {
            $run["restore"] = true;
        }
    }

    private function getDelOpt(&$run)
    {
        if ((($run["processid"] == "1") && ($run["flag"] < FlowConst::PRCS_TRANS)) || Ibos::app()->user->isadministrator) {
            $run["del"] = true;
        }
    }
}
