<?php

class AttachUtil
{
    const ICON_PATH = "static/image/file/";

    private static $_imgext = array("jpg", "jpeg", "gif", "png");
    /**
     * 附件类型图标
     * @var array 
     */
    private static $attachIcons = array(1 => "i_unknown", 2 => "i_rar", 3 => "i_doc", 4 => "i_xls", 5 => "i_ppt", 6 => "i_txt", 7 => "i_html", 8 => "i_swf", 9 => "i_jpg", 10 => "i_pdf");
    /**
     *
     * @var type 
     */
    private static $dangerTags = array("php", "php4", "asp", "aspx", "jsp", "exe");

    public static function getCommonImgExt()
    {
        return self::$_imgext;
    }

    public static function getUploadConfig($uid = 0)
    {
        $config = array();
        $imageexts = self::getCommonImgExt();
        $config["limit"] = 0;
        $uid = (!empty($uid) ? intval($uid) : Ibos::app()->user->uid);
        $authKey = Ibos::app()->setting->get("config/security/authkey");
        $config["hash"] = md5(substr(md5($authKey), 8) . $uid);
        $config["max"] = 0;
        $max = Ibos::app()->setting->get("setting/attachsize");

        if ($max) {
            $max = $max * 1024 * 1024;
        }

        $config["max"] = $max / 1024;
        $config["imageexts"] = array("ext" => "", "depict" => "Image File");
        $config["imageexts"]["ext"] = (!empty($imageexts) ? "*." . implode(";*.", $imageexts) : "");
        $config["attachexts"] = array("ext" => "*.*", "depict" => "All Support Formats");
        $extensions = Ibos::app()->setting->get("setting/filetype");

        if (!empty($extensions)) {
            $extension = str_replace(" ", "", $extensions);
            $exts = explode(",", $extension);

            foreach ($exts as $index => $ext) {
                if (in_array(strtolower($ext), self::$dangerTags)) {
                    unset($exts[$index]);
                }
            }

            $config["attachexts"]["ext"] = "*." . implode(";*.", $exts);
        }

        return $config;
    }

    public static function getTableId($relatedId)
    {
        $id = (string) $relatedId;
        $tableId = StringUtil::iIntval($id[strlen($id) - 1]);
        return $tableId;
    }

    public static function updateAttach($aid, $relateId = 0)
    {
        $aid = (is_array($aid) ? $aid : explode(",", $aid));
        $relateId = (0 < $relateId ? $relateId : mt_rand(0, 9));
        $uid = Ibos::app()->user->uid;
        $records = Attachment::model()->findAllByPk($aid);
        $count = 0;

        foreach ($records as $record) {
            $id = $record["aid"];
            if ((strcasecmp($record["uid"], $uid) !== 0) || (strcasecmp($record["tableid"], 127) !== 0)) {
                continue;
            } else {
                $unused = AttachmentUnused::model()->fetchByPk($id);
                $tableId = self::getTableId($relateId);
                Attachment::model()->modify($id, array("tableid" => $tableId));
                AttachmentN::model()->add($tableId, $unused);
                AttachmentUnused::model()->deleteByPk($id);
                $count++;
            }
        }

        return !!$count;
    }

    public static function delAttach($aid)
    {
        $count = 0;
        $aid = (is_array($aid) ? implode(",", $aid) : trim($aid, ","));
        $attachUrl = FileUtil::getAttachUrl() . "/";
        $records = Attachment::model()->fetchAll(array(
                    "select"    => array("aid", "tableid"),
                    "condition" => "FIND_IN_SET(aid,'$aid')"
                    ));

        foreach ($records as $value) {
            $record = AttachmentN::model()->fetch($value["tableid"], $value["aid"]);

            if (!empty($record)) {
                if (FileUtil::fileExists($attachUrl . $record["attachment"])) {
                    FileUtil::deleteFile($attachUrl . $record["attachment"]);
                    $count++;
                }

                AttachmentN::model()->deleteByPk($value["tableid"], $value["aid"]);
            }
        }

        if ($count) {
            Attachment::model()->deleteAll("FIND_IN_SET(aid,'$aid')");
        }

        return $count;
    }

    public static function getAttachStr($aid, $tableID = "", $param = array())
    {
        if (!is_numeric($tableID)) {
            $tableID = Ibos::app()->db->createCommand()->select("tableid")->from("{{attachment}}")->where("aid = $aid")->queryScalar();
        }

        $str = $aid . "|" . $tableID . "|" . TIMESTAMP;

        if (!empty($param)) {
            $str .= "|" . serialize($param);
        }

        $encode = rawurlencode(base64_encode(StringUtil::authCode($str, "ENCODE", Ibos::app()->user->salt)));
        return $encode;
    }

    public static function getAttachData($aid, $filterUnused = true)
    {
        $attach = array();
        $aid = (is_array($aid) ? $aid : explode(",", trim($aid, ",")));
        $records = Attachment::model()->fetchAllByPk($aid, $filterUnused ? "tableid != 127" : "");

        foreach ($records as $record) {
            if (!empty($record)) {
                $data = AttachmentN::model()->fetch($record["tableid"], $record["aid"]);
                $data["tableid"] = $record["tableid"];
                $attach[$record["aid"]] = $data;
            }
        }

        return $attach;
    }

    public static function getAttach($aid, $down = true, $officeDown = true, $edit = false, $delete = false, $getRealAddress = false)
    {
        $attach = array();

        if (!empty($aid)) {
            $data = self::getAttachData($aid);
        }

        $urlManager = Ibos::app()->urlManager;

        foreach ($data as $id => &$val) {
            $val["date"] = ConvertUtil::formatDate($val["dateline"], "u");
            $val["filetype"] = StringUtil::getFileExt($val["filename"]);
            $val["filesize"] = ConvertUtil::sizeCount($val["filesize"]);

            if ($getRealAddress) {
                $val["attachment"] = FileUtil::getAttachUrl() . "/" . $val["attachment"];
            }

            $val["filename"] = trim($val["filename"]);
            $val["delete"] = $delete;
            $val["down"] = $down;
            $val["down_office"] = $officeDown;
            $val["edit"] = $edit;
            $val["iconsmall"] = self::attachType($val["filetype"], "smallicon");
            $val["iconbig"] = self::attachType($val["filetype"], "bigicon");
            $idString = self::getAttachStr($id, $val["tableid"]);
            $val["openurl"] = $urlManager->createUrl("main/attach/open", array("id" => $idString));

            if ($val["down"]) {
                $val["downurl"] = $urlManager->createUrl("main/attach/download", array("id" => $idString));
            }

            $inOfficeRange = in_array(self::attachType($val["filetype"], "id"), range(3, 5));
            if ($inOfficeRange && $val["down_office"]) {
                $val["officereadurl"] = "http://view.officeapps.live.com/op/view.aspx?src=" . urlencode(Ibos::app()->setting->get("siteurl") . FileUtil::getAttachUrl() . "/" . $val["attachment"]);
            }

            if ($inOfficeRange && $val["edit"]) {
                $val["officeediturl"] = $urlManager->createUrl("main/attach/office", array("id" => self::getAttachStr($aid, $val["tableid"], array("filetype" => $val["filetype"], "op" => "edit"))));
            }
        }

        return $data;
    }

    public static function attachType($type, $returnVal = "smallicon")
    {
        $type = strtolower($type);

        if (is_numeric($type)) {
            $typeId = $type;
        } elseif (in_array($type, array("pdf"))) {
            $typeId = 10;
        } elseif (in_array($type, array("jpg", "gif", "png", "bmp"))) {
            $typeId = 9;
        } elseif (in_array($type, array("swf", "fla", "flv", "swi"))) {
            $typeId = 8;
        } elseif (in_array($type, array("php", "js", "pl", "cgi", "asp", "html", "htm"))) {
            $typeId = 7;
        } elseif (in_array($type, array("txt", "rtf", "wri", "chm"))) {
            $typeId = 6;
        } elseif (in_array($type, array("pptx", "pptm", "ppt", "potx", "potm", "pot", "pps", "ppsx", "ppsm", "ppam", "ppa"))) {
            $typeId = 5;
        } elseif (in_array($type, array("xlsx", "xlsm", "xlsb", "xltx", "xltm", "xlt", "xls", "xml", "xlam", "xla", "xlw", "csv"))) {
            $typeId = 4;
        } elseif (in_array($type, array("doc", "docm", "docx", "dot", "dotm", "dotx"))) {
            $typeId = 3;
        } elseif (in_array($type, array("rar", "zip", "7z"))) {
            $typeId = 2;
        } elseif ($type) {
            $typeId = 1;
        } else {
            $typeId = 0;
        }

        if ($returnVal == "smallicon") {
            return self::ICON_PATH . self::$attachIcons[$typeId] . "_lt.png";
        } else if ($returnVal == "bigicon") {
            return self::ICON_PATH . self::$attachIcons[$typeId] . ".png";
        } else if ($returnVal == "id") {
            return $typeId;
        }
    }
}
