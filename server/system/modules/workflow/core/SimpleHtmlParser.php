<?php

class SimpleHtmlParser extends ICFlowFormParser
{
    /**
     * 表单控件的缓存数组
     * @var array 
     */
    private $_cache;

    public function getID()
    {
        return $this->formid;
    }

    public function __construct(ICFlowForm $form)
    {
        $cache = CacheUtil::get("form_" . $form->getID());

        if (is_array($cache)) {
            $this->_cache = $cache;
        }

        parent::__construct($form);
    }

    public function parse($isUpdate = false)
    {
        Ibos::import("application.extensions.simple_html_dom", true);

        if ($isUpdate) {
            $model = preg_replace("/\s+data-id\s?=\s?\"?\d+\"?/i", "", $this->printmodel);
            $max = 0;
        } else {
            $model = $this->printmodel;
            $max = intval($this->itemmax);
        }

        $elements = array();
        $doc = new simple_html_dom();
        $doc->load($model, true, true, CHARSET);
        $items = $doc->find("ic");
        $config = $this->getItemConfig();
        if (!empty($items) && !empty($config)) {
            $this->refactor($items, $config, $max, $elements);
        }

        $html = $doc->save();
        $this->_cache = $elements;
        CacheUtil::set("form_" . $this->ID, $elements);
        $form["printmodelshort"] = $html;

        if ($max != $this->itemmax) {
            $form["itemmax"] = $max;
        }

        $doc->clear();
        FlowFormType::model()->modify($this->ID, $form);
    }

    public function getStructure()
    {
        if (is_null($this->_cache)) {
            $this->parse();
        }

        return $this->_cache;
    }

    private function refactor(&$items, $config, &$max, &$elements)
    {
        foreach ($items as $item) {
            if ($item->hasAttribute("data-type")) {
                $icType = strtolower($item->getAttribute("data-type"));

                if (isset($config[$icType])) {
                    $tag = $item->children(0);

                    if ($tag) {
                        $tagName = $tag->nodeName();
                        $id = intval($item->getAttribute("data-id"));

                        if (!$id) {
                            $itemId = $max + 1;
                            $eName = "data_" . $itemId;
                            $item->setAttribute("data-id", $itemId);

                            foreach ($item->children() as $child) {
                                if (strcasecmp($child->nodeName(), "img") !== 0) {
                                    $child->setAttribute("name", $eName);
                                }
                            }
                        } else {
                            $eName = "data_" . $id;
                            $itemId = $id;
                        }

                        $elements[$eName]["itemid"] = $itemId;
                        $elements[$eName]["tag"] = $tagName;
                        $elementAttrs = $item->getAllAttributes();
                        $content = $item->outertext();

                        foreach ($elementAttrs as $name => $val) {
                            $pattern = "/" . $name . "\s?=\s?(\\\"|\')?" . preg_quote($val, "/") . "(\\\"|\')?/i";
                            $replacement = $name . "=\"" . $val . "\"";
                            $string = ConvertUtil::iIconv($content, CHARSET, "UTF-8");
                            $content = ConvertUtil::iIconv(preg_replace($pattern, $replacement, $string), "UTF-8", CHARSET);
                            $attrName = strtolower($name);

                            if (in_array($attrName, $this->getSafeItemAttributes())) {
                                $elements[$eName][$attrName] = $val;
                            }
                        }

                        $elements[$eName]["content"] = $item->innertext();
                        $max = ($max < $itemId ? $itemId : $max);
                        $item->outertext = "{" . $eName . "}";
                    }
                }
            }
        }
    }
}
