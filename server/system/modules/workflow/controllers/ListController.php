<?php

class WorkflowListController extends WorkflowBaseController
{
    /**
     * 流程ID
     * @var integer 
     */
    protected $flowId = 0;
    /**
     * 列表显示视图
     * @var string 
     */
    protected $op;
    /**
     * 列表排序方式
     * @var string 
     */
    protected $sort;
    /**
     * 列表过滤类型
     * @var string 
     */
    protected $type;
    /**
     * 排序描述
     * @var string 
     */
    protected $sortText;
    /**
     * 查询关键字
     * @var string 
     */
    protected $keyword = "";
    /**
     * 检索类型 - 数据库标识 映射数组
     * @var array 
     */
    protected $typeMapping = array("todo" => FlowConst::TODO_SCOPE, "trans" => flowConst::TRANS_SCOPE, "done" => FlowConst::PRCS_DONE, "delay" => FlowConst::PRCS_DELAY);

    public function init()
    {
        parent::init();
        $this->handleOP();
        $this->handleSort();
        $this->handleType();
        $this->handleFlowId();
        $this->handleKeyword();
    }

    public function actionIndex()
    {
        $param = array("op" => $this->OP, "type" => $this->Type, "sort" => $this->Sort, "sortText" => $this->SortText, "keyword" => $this->Keyword, "flowId" => $this->FlowId, "flag" => $this->typeMapping[$this->Type]);
        $this->setPageTitle(Ibos::lang("My work"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Workflow")),
            array("name" => Ibos::lang("My work"), "url" => $this->createUrl("list/index")),
            array("name" => Ibos::lang("List"))
        ));
        $this->render($this->OP, $param);
    }

    public function actionCount()
    {
        $data = array();
        $data["new"] = intval($this->countUnReceive());
        $data["focus"] = intval($this->countFocus());
        $data["recycle"] = intval($this->countRecycle());
        $this->ajaxReturn($data);
    }

    protected function setFlowId($flowId)
    {
        $this->flowId = intval($flowId);
    }

    protected function getFlowId()
    {
        return $this->flowId;
    }

    protected function setOP($op)
    {
        $this->op = $op;
    }

    protected function getOP()
    {
        return $this->op;
    }

    protected function setSort($sort)
    {
        $this->sort = $sort;
    }

    protected function getSort()
    {
        return $this->sort;
    }

    protected function setType($type)
    {
        $this->type = $type;
    }

    protected function getType()
    {
        return $this->type;
    }

    protected function setSortText($text)
    {
        $this->sortText = $text;
    }

    protected function getSortText()
    {
        return $this->sortText;
    }

    protected function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    }

    protected function getKeyword()
    {
        return $this->keyword;
    }

    protected function handleOP()
    {
        $op = filter_input(INPUT_GET, "op");

        if (!in_array($op, array("category", "list"))) {
            $op = "category";
        }

        $this->setOP($op);
    }

    protected function handleSort()
    {
        $sort = filter_input(INPUT_GET, "sort");

        if (!in_array($sort, array("all", "host", "sign", "rollback"))) {
            $sort = "all";
        }

        $this->setSort($sort);
        $this->handleSortText();
    }

    protected function handleType()
    {
        $type = filter_input(INPUT_GET, "type");

        if (!isset($this->typeMapping[$type])) {
            $type = "todo";
        }

        $this->setType($type);
    }

    protected function handleFlowId()
    {
        $flowId = filter_has_var(INPUT_GET, "flowid");

        if ($flowId) {
            $this->setFlowId(filter_input(INPUT_GET, "flowid", FILTER_SANITIZE_NUMBER_INT));
        }
    }

    protected function handleSortText()
    {
        $sortMap = array("all" => Ibos::lang("All of it"), "host" => Ibos::lang("Host"), "sign" => Ibos::lang("Sign"), "rollback" => Ibos::lang("Rollback"));

        if (isset($sortMap[$this->getSort()])) {
            $this->setSortText($sortMap[$this->getSort()]);
        }
    }

    protected function handleKeyword()
    {
        $keyword = filter_has_var(INPUT_POST, "keyword");

        if ($keyword) {
            $this->setKeyword(filter_input(INPUT_POST, "keyword"));
        }
    }
}
