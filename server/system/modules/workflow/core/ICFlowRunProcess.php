<?php

class ICFlowRunProcess extends ICFlowBase
{
    public function __construct()
    {
        $args = func_get_args();
        $nums = func_num_args();

        if ($nums == 1) {
            call_user_func_array(array($this, "setById"), $args);
        } elseif ($nums == 4) {
            call_user_func_array(array($this, "setByAllID"), $args);
        } else {
            throw new CException(Ibos::lang("Parameters error", "error"));
        }
    }

    public function getID()
    {
        $attr = $this->toArray();
        return $attr["id"];
    }

    protected function setById($id)
    {
        $attr = FlowRunProcess::model()->fetchByPk(intval($id));
        $this->setAttributes($attr);
    }

    protected function setByAllID($runId, $processId, $flowProcess, $uid)
    {
        $attr = FlowRunProcess::model()->fetchRunProcess($runId, $processId, $flowProcess, $uid);
        $this->setAttributes($attr);
    }
}
