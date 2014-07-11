<?php

class ICUpload
{
    /**
     * 附件信息数组
     * @var type 
     */
    private $_attach = array();
    /**
     * 错误代码
     * @var integer 
     */
    private $_errorCode = 0;

    final public function __construct($attach, $module = "temp")
    {
        if (!is_array($attach) || empty($attach) || !$this->isUploadFile($attach["tmp_name"]) || (trim($attach["name"]) == "") || ($attach["size"] == 0)) {
            $this->_attach = array();
            $this->_errorCode = -1;
            return false;
        } else {
            $attach["type"] = $this->checkDirType($module);
            $attach["size"] = intval($attach["size"]);
            $attach["name"] = trim($attach["name"]);
            $attach["thumb"] = "";
            $attach["ext"] = StringUtil::getFileExt($attach["name"]);
            $attach["name"] = StringUtil::ihtmlSpecialChars($attach["name"], ENT_QUOTES);

            if (90 < strlen($attach["name"])) {
                $attach["name"] = StringUtil::cutStr($attach["name"], 80, "") . "." . $attach["ext"];
            }

            $attach["isimage"] = $this->isImageExt($attach["ext"]);
            $attach["attachdir"] = $this->getTargetDir($attach["type"]);
            $attach["attachname"] = $this->getTargetFileName() . "." . $attach["ext"];
            $attach["attachment"] = $attach["attachdir"] . $attach["attachname"];
            $attach["target"] = FileUtil::getAttachUrl() . "/" . $attach["type"] . "/" . $attach["attachment"];
            $this->_attach = &$attach;
            $this->_errorCode = 0;
            return true;
        }
    }

    public function getAttach()
    {
        return $this->_attach;
    }

    public function getError()
    {
        return $this->_errorCode;
    }

    public function save()
    {
        if (!$this->saveToLocal($this->_attach["tmp_name"], $this->_attach["target"])) {
            $this->_errorCode = -103;
            return false;
        } else {
            $this->_errorCode = 0;
            return true;
        }
    }

    protected function saveToLocal($source, $target)
    {
        if (!$this->isUploadFile($source)) {
            $succeed = false;
        } elseif (@copy($source, $target)) {
            $succeed = true;
        } else {
            if (function_exists("move_uploaded_file") && @move_uploaded_file($source, $target)) {
                $succeed = true;
            } else {
                if (@is_readable($source) && @$fp_s = fopen($source, "rb") && @$fp_t = fopen($target, "wb")) {
                    while (!feof($fp_s)) {
                        $s = @fread($fp_s, 1024 * 512);
                        @fwrite($fp_t, $s);
                    }

                    fclose($fp_s);
                    fclose($fp_t);
                    $succeed = true;
                }
            }
        }

        if ($succeed) {
            $this->_errorCode = 0;
            @chmod($target, 420);
            @unlink($source);
        } else {
            $this->_errorCode = 0;
        }

        return $succeed;
    }

    protected function checkDirType($module)
    {
        $modules = Ibos::app()->getEnabledModule();
        return !array_key_exists($module, $modules) ? "temp" : $module;
    }

    protected function isUploadFile($source)
    {
        return $source && ($source != "none") && (is_uploaded_file($source) || is_uploaded_file(str_replace("\\\\", "\\", $source)));
    }

    protected function isImageExt($ext)
    {
        static $imgext = array("jpg", "jpeg", "gif", "png", "bmp");
        return in_array($ext, $imgext) ? 1 : 0;
    }

    protected function getTargetDir($module)
    {
        $subDir = $ymDir = $dayDir = "";
        $ymDir = date("Ym");
        $dayDir = date("d");
        $subDir = $ymDir . "/" . $dayDir . "/";
        LOCAL && $this->checkDirExists($module, $ymDir, $dayDir);
        return $subDir;
    }

    protected function checkDirExists($module, $ymDir, $dayDir)
    {
        $type = $this->checkDirType($module);
        $baseDir = FileUtil::getAttachUrl();
        $dirs = $baseDir . "/" . $type . "/" . $ymDir . "/" . $dayDir;
        $res = is_dir($dirs);

        if (!$res) {
            $res = FileUtil::makeDirs($dirs);
        }

        return $res;
    }

    protected function getTargetFileName()
    {
        return date("His") . strtolower(StringUtil::random(16));
    }
}
