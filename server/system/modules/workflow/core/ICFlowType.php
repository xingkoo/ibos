<?php

class ICFlowType extends ICFlowBase
{
    /**
     * 表单对象
     * @var mixed 
     */
    private $_form;

    public function __construct($flowId, $setForm = true)
    {
        $this->setFlow($flowId);
        if (isset($this->formid) && $setForm) {
            $this->setForm($this->formid);
        }
    }

    public function getID()
    {
        return $this->flowid;
    }

    public function setForm($formId, $parser = "simple_html")
    {
        $this->_form = new ICFlowForm($formId, $parser);
    }

    public function getForm()
    {
        return $this->_form;
    }

    public function isFixed()
    {
        return $this->type == "1";
    }

    public function isFree()
    {
        return $this->type == "2";
    }

    protected function setFlow($flowId)
    {
        $attr = FlowType::model()->fetchByPk(intval($flowId));
        $this->setAttributes($attr);
    }
}
