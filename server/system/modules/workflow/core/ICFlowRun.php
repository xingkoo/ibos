<?php

class ICFlowRun extends ICFlowBase
{
    public function __construct($runId)
    {
        $this->setRun($runId);
    }

    public function getID()
    {
        return $this->runid;
    }

    protected function setRun($runId)
    {
        $attr = FlowRun::model()->fetchByPk(intval($runId));
        $this->setAttributes($attr);
    }
}
