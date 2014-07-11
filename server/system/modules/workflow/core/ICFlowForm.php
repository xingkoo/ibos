<?php

class ICFlowForm extends ICFlowBase
{
    /**
     * 当前表单解析
     * @var mixed 
     */
    private $_parser;

    public function __construct($formId, $parser = "simple_html")
    {
        $this->setForm($formId);
        $this->setParser($parser);
    }

    public function getID()
    {
        return $this->formid;
    }

    public function getStructure()
    {
        return $this->getParser()->getStructure();
    }

    public function getParser()
    {
        return $this->_parser;
    }

    protected function setForm($formId)
    {
        $attr = FlowFormType::model()->fetchByPk(intval($formId));
        $this->setAttributes($attr);
    }

    protected function setParser($parser)
    {
        switch ($parser) {
            case "simple_html":
                $this->_parser = new SimpleHtmlParser($this);
                break;

            default:
                break;
        }
    }
}
