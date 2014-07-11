<?php

abstract class ICFlowFormParser extends ICFlowBase
{
    public function __construct(ICFlowForm $form)
    {
        $this->setAttributes($form->toArray());
    }

    protected function getSafeItemAttributes()
    {
        return array("data-id", "data-title", "data-checked", "data-type", "data-field", "data-hide", "data-height", "data-width", "data-rows", "data-select-type", "data-lv-title", "data-lv-size", "data-lv-sum", "data-value", "data-lv-coltype", "data-lv-colvalue", "data-lv-cal", "data-prec", "data-rich", "data-radio-field", "data-child", "data-control", "data-src", "data-query", "data-radio-check", "data-table", "data-field-name", "data-sign-type", "data-sign-color", "data-img-width", "data-img-height", "data-rich-width", "data-rich-height", "data-date-format", "data-single", "data-text", "data-font-size", "data-align", "data-bold", "data-italic", "data-underline", "data-color", "data-select-check", "data-size", "data-select-field", "data-qrcode-type", "data-qrcode-size", "data-step", "data-progress-style");
    }

    protected function getItemConfig()
    {
        $lang = Ibos::getLangSource("workflow.item");
        return array("calc" => $lang["Calculate control"], "text" => $lang["Single input box"], "auto" => $lang["Macro control"], "date" => $lang["Calendar control"], "checkbox" => $lang["Check box"], "user" => $lang["User control"], "textarea" => $lang["Multiline input box"], "select" => $lang["Dropdown menu"], "auto" => $lang["Macro control"], "fetch" => $lang["Data selection control"], "form-data" => $lang["Form data control"], "listview" => $lang["List control"], "qrcode" => $lang["Qr code control"], "sign" => $lang["Signature control"], "progressbar" => $lang["Progressbar control"], "imgupload" => $lang["Image upload control"], "fileupload" => $lang["Attach upload control"], "radio" => $lang["Radio buttons"], "label" => $lang["Label text"]);
    }

    abstract public function parse();

    abstract public function getStructure();
}
