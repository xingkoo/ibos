<?php

class ICOfficialdoc
{
    /**
     * 所有的字段属性数组
     * @var array 
     * @access private
     */
    private $attributes = array();

    public function __construct($docid)
    {
        $officialDoc = Officialdoc::model()->fetchByPk($docid);

        if (!empty($officialDoc)) {
            $this->attributes = $officialDoc;
            $this->attributes["issign"] = OfficialdocReader::model()->fetchSignByDocid($docid, Ibos::app()->user->uid);
        }
    }

    public function __set($name, $value)
    {
        isset($this->attributes[$name]) && ($this->attributes[$name] = $value);
    }

    public function __get($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setStatus($value)
    {
        $this->attributes["status"] = $value;
    }

    public function getStatus()
    {
        return $this->attributes["status"];
    }

    public function getPrint()
    {
    }

    public static function getShowData($data)
    {
        $data["subject"] = stripslashes($data["subject"]);
        $data["showVersion"] = OfficialdocUtil::changeVersion($data["version"]);
        $departments = DepartmentUtil::loadDepartment();
        $positions = PositionUtil::loadPosition();

        if ($data["approver"] != 0) {
            $data["approver"] = User::model()->fetchRealnameByUid($data["approver"]);
        } else {
            $data["approver"] = Ibos::lang("None");
        }

        $data["addtime"] = ConvertUtil::formatDate($data["addtime"], "u");

        if (!empty($data["uptime"])) {
            $data["uptime"] = ConvertUtil::formatDate($data["uptime"], "u");
        }

        $data["categoryName"] = OfficialdocCategory::model()->fetchCateNameByCatid($data["catid"]);
        if (empty($data["deptid"]) && empty($data["positionid"]) && empty($data["uid"])) {
            $data["departmentNames"] = Ibos::lang("All");
            $data["positionNames"] = $data["uidNames"] = "";
        } elseif ($data["deptid"] == "alldept") {
            $data["departmentNames"] = Ibos::lang("All");
            $data["positionNames"] = $data["uidNames"] = "";
        } else {
            $department = DepartmentUtil::loadDepartment();
            $data["departmentNames"] = OfficialdocUtil::joinStringByArray($data["deptid"], $department, "deptname", "、");
            $position = PositionUtil::loadPosition();
            $data["positionNames"] = OfficialdocUtil::joinStringByArray($data["positionid"], $position, "posname", "、");

            if (!empty($data["uid"])) {
                $users = User::model()->fetchAllByUids(explode(",", $data["uid"]));
                $data["uidNames"] = OfficialdocUtil::joinStringByArray($data["uid"], $users, "realname", "、");
            } else {
                $data["uidNames"] = "";
            }
        }

        if (empty($data["ccdeptid"]) && empty($data["ccpositionid"]) && empty($data["ccuid"])) {
            $data["ccDepartmentNames"] = Ibos::lang("All");
            $data["ccPositionNames"] = $data["ccUidNames"] = "";
        } elseif ($data["ccdeptid"] == "alldept") {
            $data["ccDepartmentNames"] = Ibos::lang("All");
            $data["ccPositionNames"] = $data["ccUidNames"] = "";
        } else {
            $department = DepartmentUtil::loadDepartment();
            $data["ccDepartmentNames"] = OfficialdocUtil::joinStringByArray($data["ccdeptid"], $department, "deptname", "、");
            $position = PositionUtil::loadPosition();
            $data["ccPositionNames"] = OfficialdocUtil::joinStringByArray($data["ccpositionid"], $position, "posname", "、");

            if (!empty($data["ccuid"])) {
                $users = User::model()->fetchAllByUids(explode(",", $data["ccuid"]));
                $data["ccUidNames"] = OfficialdocUtil::joinStringByArray($data["ccuid"], $users, "realname", "、");
            } else {
                $data["ccUidNames"] = "";
            }
        }

        return $data;
    }

    public static function getListDatas($datas)
    {
        $listDatas = array();
        $users = Ibos::app()->setting->get("cache/users");
        $uid = Ibos::app()->user->uid;
        $checkTime = 3 * 86400;
        $readDocIds = OfficialdocReader::model()->fetchReadArtIdsByUid($uid);
        $signedDocIds = OfficialdocReader::model()->fetchSignArtIdsByUid($uid);

        foreach ($datas as $data) {
            $data["subject"] = StringUtil::cutStr($data["subject"], 50);
            $data["readStatus"] = (in_array($data["docid"], $readDocIds) ? 1 : -1);
            if (($data["readStatus"] === -1) && ((TIMESTAMP - $checkTime) < $data["uptime"])) {
                $data["readStatus"] = 2;
            }

            $data["signNum"] = OfficialdocReader::model()->count("issign = 1 AND docid = {$data["docid"]}");
            $data["signStatus"] = (in_array($data["docid"], $signedDocIds) ? 1 : 0);
            $data["author"] = (isset($users[$data["author"]]) ? $users[$data["author"]]["realname"] : "");
            $data["uptime"] = (empty($data["uptime"]) ? $data["addtime"] : $data["uptime"]);
            $data["uptime"] = ConvertUtil::formatDate($data["uptime"], "u");
            $keyword = EnvUtil::getRequest("keyword");

            if (!empty($keyword)) {
                $data["subject"] = preg_replace("|($keyword)|i", "<span style='color:red'>\$1</span>", $data["subject"]);
            }

            if ($data["ishighlight"] == "1") {
                $highLightStyle = $data["highlightstyle"];
                $hiddenInput = "<input type='hidden' id='{$data["docid"]}_hlstyle' value='$highLightStyle'/>";
                $data["subject"] .= $hiddenInput;
                $highLightStyleArr = explode(",", $highLightStyle);
                $color = $highLightStyleArr[1];
                $isB = $highLightStyleArr[0];
                $isI = $highLightStyleArr[2];
                $isU = $highLightStyleArr[3];
                $isB && ($data["subject"] = "<b>{$data["subject"]}</b>");
                $isU && ($data["subject"] = "<u>{$data["subject"]}</u>");
                $fontStyle = "";
                ($color != "") && ($fontStyle .= "color:$color;");
                $isI && ($fontStyle .= "font-style:italic;");
                ($fontStyle != "") && ($data["subject"] = "<font style='$fontStyle'>{$data["subject"]}</font>");
            }

            $listDatas[] = $data;
        }

        return $listDatas;
    }

    public static function handleApproval($datas)
    {
        $allApprovals = Approval::model()->fetchAllSortByPk("id");
        $allCategorys = OfficialdocCategory::model()->fetchAllSortByPk("catid");
        $docApprovals = OfficialdocApproval::model()->fetchAllGroupByDocId();
        $backDocIds = OfficialdocBack::model()->fetchAllBackDocId();

        foreach ($datas as &$doc) {
            $doc["back"] = (in_array($doc["docid"], $backDocIds) ? 1 : 0);
            $doc["approval"] = $doc["approvalStep"] = array();
            $catid = $doc["catid"];

            if (!empty($allCategorys[$catid]["aid"])) {
                $aid = $allCategorys[$catid]["aid"];

                if (!empty($allApprovals[$aid])) {
                    $doc["approval"] = $allApprovals[$aid];
                }
            }

            if (!empty($doc["approval"])) {
                $doc["approvalName"] = (!empty($doc["approval"]) ? $doc["approval"]["name"] : "");
                $doc["docApproval"] = (isset($docApprovals[$doc["docid"]]) ? $docApprovals[$doc["docid"]] : array());
                $doc["stepNum"] = count($doc["docApproval"]);
                $step = array();

                foreach ($doc["docApproval"] as $docApproval) {
                    $step[$docApproval["step"]] = User::model()->fetchRealnameByUid($docApproval["uid"]);
                }

                for ($i = 1; $i <= $doc["approval"]["level"]; $i++) {
                    if ($i <= $doc["stepNum"]) {
                        $doc["approval"][$i]["approvaler"] = (isset($step[$i]) ? $step[$i] : "未知");
                    } else {
                        $levelName = Approval::model()->getLevelNameByStep($i);
                        $approvalUids = $doc["approval"][$levelName];
                        $doc["approval"][$i]["approvaler"] = User::model()->fetchRealnamesByUids($approvalUids, "、");
                    }
                }
            }
        }

        return $datas;
    }

    public static function setPages($content, $pageSize, $page)
    {
        $contentLength = strlen($content);
        $pageCount = ceil($contentLength / $pageSize);
        $pages = new CPagination($pageCount);
        $pages->setPageSize($pageSize);
        $pages->setCurrentPage(0);
        $data = array();

        for ($i = 0; $i < $pageCount; $i++) {
            $data[$i] = substr($content, $i * $pageSize, ($i + 1) * $pageSize);
        }

        return array("data" => $data[$page - 1], "pages" => $pages);
    }

    protected function handleShowData($data)
    {
        foreach ($data as $k => $approval) {
            for ($level = 1; $level <= $approval["level"]; $level++) {
                $field = "level$level";
                $data[$k]["levels"][$field] = $this->getShowNames($approval[$field]);
                $data[$k]["levels"][$field]["levelClass"] = $this->getShowLevelClass($field);
            }

            $data[$k]["free"] = $this->getShowNames($approval["free"]);
            $data[$k]["free"]["levelClass"] = $this->getShowLevelClass("free");
        }

        return $data;
    }
}
