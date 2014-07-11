<?php

class ApiUtil extends SystemUtil
{
    /**
     * 默认的CURL选项
     * @var array 
     */
    protected $curlopt = array("CURLOPT_RETURNTRANSFER\000\030" => null, "CURLOPT_HEADER\000\030" => null, "CURLOPT_ENCODING\000\030" => null, "CURLOPT_USERAGENT\000\030" => null, "CURLOPT_AUTOREFERER\000\030" => null, "CURLOPT_CONNECTTIMEOUT\000\030" => null, "CURLOPT_TIMEOUT\000\030" => null, "CURLOPT_MAXREDIRS\000\030" => null, "CURLOPT_SSL_VERIFYHOST\000\030" => null, "CURLOPT_SSL_VERIFYPEER\000\030" => null, "CURLOPT_VERBOSE\000\030" => null);

    public static function getInstance($className = "ApiUtil")
    {
        return parent::getInstance($className);
    }

    public function setOpt($opt)
    {
        if (!empty($opt)) {
            $this->curlopt = $opt + $this->curlopt;
        }
    }

    public function getOpt()
    {
        return $this->curlopt;
    }

    public function buildUrl($url, $param = array())
    {
        $param = http_build_query($param);
        return $url . (strpos($url, "?") ? "&" : "?") . $param;
    }

    public function fetchResult($url, $param = array(), $type = "get")
    {
        if ($type == "post") {
            $this->setOpt(array(CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $param));
        } else {
            $url = $this->buildUrl($url, $param);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, $this->getOpt());
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            return "error:" . curl_error($ch);
        }

        return $result;
    }
}
