<?php

abstract class ICAttach
{
    /**
     * 上传对象
     * @var object 
     */
    protected $upload;

    public function __construct($fileArea = "Filedata", $module = "temp")
    {
        $file = $_FILES[$fileArea];

        if ($file["error"]) {
            throw new FileException(Ibos::lang("File is too big", "error"));
        } else {
            $upload = FileUtil::getUpload($file, $module);
            $this->upload = $upload;
        }
    }

    abstract public function upload();
}
