<?php

class IWMainOffice extends CWidget
{
    const VIEW = "application.modules.main.views.attach.office";
    const OFFICE_PATH = "system/modules/main/office/";
    const DOC_WORD = 3;
    const DOC_EXCEL = 4;
    const DOC_PPT = 5;
    const LOCK_SEC = 180;

    /**
     * 待处理的参数
     * @var array 
     */
    private $_param = array();
    /**
     * 该文档指向的附件数组
     * @var array 
     */
    private $_attach = array();
    /**
     * run方法处理的数组
     * @var array 
     */
    private $_var = array();

    public function setParam($param)
    {
        $this->_param = $this->formatParam($param);
    }

    public function getParam()
    {
        return $this->_param;
    }

    public function setAttach($attach)
    {
        $this->_attach = $attach;
    }

    public function getAttach()
    {
        return $this->_attach;
    }

    public function init()
    {
        $attach = $this->getAttach();
        $var = array("assetUrl" => $this->getController()->getAssetUrl(), "lang" => Ibos::getLangSources(), "attach" => $attach, "param" => $this->getParam(), "isNew" => empty($attach));
        $var = array_merge($this->getDocFile($var), $var);
        $this->_var = $var;
    }

    public function run()
    {
        $var = $this->_var;
        $licence = $this->getLicence();
        $correct = $this->chkLicence($licence);

        if ($correct) {
            $var["licence"] = $licence["officelicence"];
            $var["officePath"] = self::OFFICE_PATH;
            $var["assetUrl"] = $this->getController()->getAssetUrl();
            $this->render(self::VIEW, $var);
        } else {
            $this->getController()->error(Ibos::lang("Illegal office license", "main.default"), "", array("autoJump" => 0));
        }
    }

    public function handleRequest()
    {
        $allowedOps = array("lock", "save");
        $op = filter_input(INPUT_GET, "op", FILTER_SANITIZE_STRING);
    }

    private function getDocFile($var)
    {
        if ($var["isNew"]) {
            $typeId = AttachUtil::attachType($var["param"]["filetype"], "id");
            $map = array(
                self::DOC_WORD  => array("fileName" => $var["lang"]["New doc"] . ".doc", "fileUrl" => self::OFFICE_PATH . "new.doc", "typeId" => self::DOC_WORD),
                self::DOC_EXCEL => array("fileName" => $var["lang"]["New excel"] . ".xls", "fileUrl" => self::OFFICE_PATH . "new.doc", "typeId" => self::DOC_WORD),
                self::DOC_PPT   => array("fileName" => $var["lang"]["New ppt"] . ".ppt", "fileUrl" => self::OFFICE_PATH . "new.ppt", "typeId" => self::DOC_WORD)
                );
            return $map[$typeId];
        } else {
            return array("typeId" => AttachUtil::attachType(StringUtil::getFileExt($var["attach"]["attachment"]), "id"), "fileName" => $var["attach"]["filename"], "fileUrl" => FileUtil::fileName(FileUtil::getAttachUrl() . "/" . $var["attach"]["attachment"]));
        }
    }

    private function formatParam($param)
    {
        $return = array();

        if (isset($param[0])) {
            $return["aid"] = intval($param[0]);
        }

        if (isset($param[1])) {
            $return["tableid"] = intval($param[1]);
        }

        if (isset($param[2])) {
            $return["timestamp"] = intval($param[2]);
        }

        if (isset($param[3])) {
            $ext = unserialize($param[3]);
            $return = array_merge($return, $ext);
        }

        return $return;
    }

    private function getLicence()
    {
        $file = self::OFFICE_PATH . "licence.xml";

        if (file_exists($file)) {
            $content = file_get_contents($file);

            if (is_string($content)) {
                $licence = XmlUtil::xmlToArray($content);
                return $licence;
            }
        } else {
            return false;
        }
    }

    private function chkLicence($licence)
    {
        if (is_array($licence)) {
            if (isset($licence["officelicence"])) {
                $data = $licence["officelicence"];
                return !empty($data["ProductCaption"]) && !empty($data["ProductKey"]);
            }
        }

        return false;
    }
}
