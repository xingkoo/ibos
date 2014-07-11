<?php

class IWWfListContent extends IWWfBase
{
    /**
     * 工作流查询范围标识
     * @var mixed 
     */
    protected $flag;
    /**
     * 流程过滤类型
     * @var string 
     */
    protected $type;
    /**
     * 排序条件
     * @var string 
     */
    protected $sort;
    /**
     * 点击后的流程ID
     * @var integer 
     */
    protected $flowId;
    /**
     * 当前所在的视图
     * @var string 
     */
    protected $op;
    /**
     * 查询关键字
     * @var string 
     */
    protected $keyword;
    /**
     * 查询字段 
     * @var array 
     */
    protected $fields = array("frp.runid", "frp.processid", "frp.flowprocess", "frp.flag", "frp.opflag", "frp.processtime", "ft.freeother", "ft.flowid", "ft.name as typeName", "ft.type", "ft.listfieldstr", "fr.name as runName", "fr.beginuser", "fr.begintime", "fr.endtime", "fr.focususer");

    public function setFlag($flag)
    {
        $this->flag = $flag;
    }

    public function getFlag()
    {
        return $this->flag;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getFlowId()
    {
        return $this->flowId;
    }

    public function setFlowId($flowId)
    {
        $this->flowId = intval($flowId);
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setOp($op)
    {
        $this->op = $op;
    }

    public function getOp()
    {
        return $this->op;
    }

    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    }

    public function getKeyword()
    {
        return $this->keyword;
    }

    protected function getRunProcess()
    {
        return Ibos::app()->db->createCommand()->select($this->fields)->from("{{flow_run_process}} frp")->leftJoin("{{flow_run}} fr", "frp.runid = fr.runid")->leftJoin("{{flow_type}} ft", "fr.flowid = ft.flowid")->where($this->getCondition())->order($this->getQuerySort())->group($this->getQueryGroup())->offset($this->getOffset())->limit($this->getLimit())->queryAll();
    }

    protected function getOffset()
    {
        return false;
    }

    protected function getLimit()
    {
        return false;
    }

    protected function getIsOver()
    {
        return in_array($this->getType(), array("trans", "done"));
    }

    protected function getIsTodo()
    {
        return $this->getType() == "todo";
    }

    protected function getIsDelay()
    {
        return $this->getType() == "delay";
    }

    protected function getQuerySort()
    {
        $sort = "frp.runid DESC";

        if ($this->getIsOver()) {
            if ($this->getType() == "trans") {
                $sort = "frp.processtime DESC";
            } else {
                $sort = "fr.endtime DESC";
            }
        } elseif ($this->getIsTodo()) {
            $sort = "frp.createtime DESC";
        } elseif ($this->getIsDelay()) {
            $sort = "frp.flag DESC";
        }

        return $sort;
    }

    protected function getQueryGroup()
    {
        $group = "";

        if ($this->getIsOver()) {
            $group = "frp.runid";
        } elseif ($this->getIsTodo()) {
            $group = "frp.id";
        } elseif ($this->getIsDelay()) {
            $group = "frp.id";
        }

        return $group;
    }

    protected function getCondition()
    {
        $flag = $this->getFlag();
        $condition = array("and", "fr.delflag = 0", "frp.childrun = 0", sprintf("frp.uid = %d", $this->getUid()));

        if ($flag == FlowConst::PRCS_DONE) {
            $condition[] = "fr.endtime != '0'";
        } else {
            $condition[] = array("in", "frp.flag", explode(",", $flag));
            $condition[] = "fr.endtime = 0";
        }

        if ($this->sort == "host") {
            $condition[] = "frp.opflag = 1";
        } elseif ($this->sort == "sign") {
            $condition[] = "frp.opflag = 0";
        } elseif ($this->sort == "rollback") {
            $condition[] = "frp.processid != frp.flowprocess";
        }

        if ($this->flowid != 0) {
            $condition[] = "fr.flowid = " . $this->flowid;
        }

        $key = $this->getKeyword();

        if (!empty($key)) {
            $condition[] = array("or", "fr.runid LIKE '%$key%'", "fr.name LIKE '%$key%'");
        }

        return $condition;
    }
}
