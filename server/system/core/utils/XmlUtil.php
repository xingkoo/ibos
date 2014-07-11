<?php

class XmlUtil
{
    public static function xmlToArray($xml, $isNormal = false)
    {
        $xmlParser = new XMLParse($isNormal);
        $data = $xmlParser->parse($xml);
        $xmlParser->destruct();
        return $data;
    }

    public static function arrayToXml($arr, $htmlOn = true, $level = 1)
    {
        $string = ($level == 1 ? "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n<root>\r\n" : "");
        $space = str_repeat("\t", $level);

        foreach ($arr as $key => $value) {
            if (!is_array($value)) {
                $string .= $space . "<item id=\"$key\">" . ($htmlOn ? "<![CDATA[" : "") . $value . ($htmlOn ? "]]>" : "") . "</item>\r\n";
            } else {
                $string .= $space . "<item id=\"$key\">\r\n" . self::arrayToXml($value, $htmlOn, $level + 1) . $space . "</item>\r\n";
            }
        }

        $string = preg_replace("/([\001-\010\v-\f\016-\037])+/", " ", $string);
        return $level == 1 ? $string . "</root>" : $string;
    }
}

class XMLParse
{
    /**
     * xml解析对象
     * @var mixed 
     */
    private $_parser;
    /**
     *
     * @var type 
     */
    private $_document;
    /**
     *
     * @var type 
     */
    private $_stack;
    /**
     *
     * @var type 
     */
    private $_data;
    /**
     *
     * @var type 
     */
    private $_lastOpenedTag;
    /**
     *
     * @var type 
     */
    private $_isNormal;
    /**
     *
     * @var type 
     */
    private $_attrs = array();
    /**
     *
     * @var type 
     */
    private $_failed = false;

    public function __construct($isNormal)
    {
        $this->_isNormal = $isNormal;
        $this->_parser = xml_parser_create("ISO-8859-1");
        xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_object($this->_parser, $this);
        xml_set_element_handler($this->_parser, "open", "close");
        xml_set_character_data_handler($this->_parser, "data");
    }

    public function destruct()
    {
        xml_parser_free($this->_parser);
    }

    public function parse(&$data)
    {
        $this->_document = array();
        $this->_stack = array();
        $flag = xml_parse($this->_parser, $data, true);
        $failedFlag = $this->_failed;
        if ($flag && !$failedFlag) {
            return $this->_document;
        } else {
            return "";
        }
    }

    public function open(&$parser, $tag, $attributes)
    {
        $this->_data = "";
        $this->_failed = false;

        if (!$this->_isNormal) {
            if (isset($attributes["id"])) {
                $this->_document = &$this->_document[$attributes["id"]];
            } else {
                $this->_failed = true;
            }
        } elseif (!isset($this->_document[$tag])) {
            $this->_document = &$this->_document[$tag];
        } else {
            $this->_failed = true;
        }

        $this->_stack[] = &$this->_document;
        $this->_lastOpenedTag = $tag;
        $this->_attrs = $attributes;
    }

    public function data(&$parser, $data)
    {
        if ($this->_lastOpenedTag != null) {
            $this->_data .= $data;
        }
    }

    public function close(&$parser, $tag)
    {
        if ($this->_lastOpenedTag == $tag) {
            $this->_document = $this->_data;
            $this->_lastOpenedTag = null;
        }

        array_pop($this->_stack);

        if ($this->_stack) {
            $this->_document = &$this->_stack[count($this->_stack) - 1];
        }
    }
}
