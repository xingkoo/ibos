<?php

class EditorUploader
{
    private $fileField;
    private $file;
    private $config;
    private $oriName;
    private $fileName;
    private $fullName;
    private $fileSize;
    private $fileType;
    private $stateInfo;
    private $stateMap = array(0 => "SUCCESS", 1 => "文件大小超出 upload_max_filesize 限制", 2 => "文件大小超出 MAX_FILE_SIZE 限制", 3 => "文件未被完整上传", 4 => "没有文件被上传", 5 => "上传文件为空", "POST" => "文件大小超出 post_max_size 限制", "SIZE" => "文件大小超出网站限制", "TYPE" => "不允许的文件类型", "DIR" => "目录创建失败", "IO" => "输入输出错误", "UNKNOWN" => "未知错误", "MOVE" => "文件保存时出错");

    public function __construct($fileField, $config, $base64 = false)
    {
        $this->fileField = $fileField;
        $this->config = $config;
        $this->stateInfo = $this->stateMap[0];
        $this->upFile($base64);
    }

    private function upFile($base64)
    {
        if ("base64" == $base64) {
            $content = $_POST[$this->fileField];
            $this->base64ToImage($content);
            return null;
        }

        $file = $this->file = $_FILES[$this->fileField];

        if (!$file) {
            $this->stateInfo = $this->getStateInfo("POST");
            return null;
        }

        if ($this->file["error"]) {
            $this->stateInfo = $this->getStateInfo($file["error"]);
            return null;
        }

        if (!is_uploaded_file($file["tmp_name"])) {
            $this->stateInfo = $this->getStateInfo("UNKNOWN");
            return null;
        }

        $this->oriName = $file["name"];
        $this->fileSize = $file["size"];
        $this->fileType = $this->getFileExt();

        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("SIZE");
            return null;
        }

        if (!$this->checkType()) {
            $this->stateInfo = $this->getStateInfo("TYPE");
            return null;
        }

        $this->fullName = $this->getFolder() . "/" . $this->getName();

        if ($this->stateInfo == $this->stateMap[0]) {
            if (!defined("SAE_TMP_PATH")) {
                if (!move_uploaded_file($file["tmp_name"], $this->fullName)) {
                    $this->stateInfo = $this->getStateInfo("MOVE");
                }
            } else {
                $st = new SaeStorage();
                $url = $st->upload("data", $this->fullName, $file["tmp_name"]);

                if (!$url) {
                    $this->stateInfo = $this->getStateInfo("MOVE");
                } else {
                    $this->fullName = $url;
                }
            }
        }
    }

    private function base64ToImage($base64Data)
    {
        $img = base64_decode($base64Data);
        $this->fileName = time() . rand(1, 10000) . ".png";
        $this->fullName = $this->getFolder() . "/" . $this->fileName;

        if (!file_put_contents($this->fullName, $img)) {
            $this->stateInfo = $this->getStateInfo("IO");
            return null;
        }

        $this->oriName = "";
        $this->fileSize = strlen($img);
        $this->fileType = ".png";
    }

    public function getFileInfo()
    {
        return array("originalName" => $this->oriName, "name" => $this->fileName, "url" => $this->fullName, "size" => $this->fileSize, "type" => $this->fileType, "state" => $this->stateInfo);
    }

    private function getStateInfo($errCode)
    {
        return !$this->stateMap[$errCode] ? $this->stateMap["UNKNOWN"] : $this->stateMap[$errCode];
    }

    private function getName()
    {
        return $this->fileName = time() . rand(1, 10000) . $this->getFileExt();
    }

    private function checkType()
    {
        return in_array($this->getFileExt(), $this->config["allowFiles"]);
    }

    private function checkSize()
    {
        return $this->fileSize <= $this->config["maxSize"] * 1024;
    }

    private function getFileExt()
    {
        return strtolower(strrchr($this->file["name"], "."));
    }

    private function getFolder()
    {
        $pathStr = $this->config["savePath"];

        if (strrchr($pathStr, "/") != "/") {
            $pathStr .= "/";
        }

        $pathStr .= date("Ymd");

        if (!defined("SAE_TMP_PATH")) {
            if (!file_exists($pathStr)) {
                if (!mkdir($pathStr, 511, true)) {
                    return false;
                }
            }
        }

        return $pathStr;
    }
}
