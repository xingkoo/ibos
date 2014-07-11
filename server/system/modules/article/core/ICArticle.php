<?php

class ICArticle
{
    public static function getShowData($data)
    {
        $data["subject"] = stripslashes($data["subject"]);

        if (!empty($data["author"])) {
            $data["authorDeptName"] = Department::model()->fetchDeptNameByUid($data["author"]);
        }

        if ($data["approver"] != 0) {
            $data["approver"] = User::model()->fetchRealNameByUid($data["approver"]);
        } else {
            $data["approver"] = Ibos::lang("None");
        }

        $data["addtime"] = ConvertUtil::formatDate($data["addtime"], "u");
        $data["uptime"] = (empty($data["uptime"]) ? "" : ConvertUtil::formatDate($data["uptime"], "u"));
        $data["categoryName"] = ArticleCategory::model()->fetchCateNameByCatid($data["catid"]);
        if (empty($data["deptid"]) && empty($data["positionid"]) && empty($data["uid"])) {
            $data["departmentNames"] = Ibos::lang("All");
            $data["positionNames"] = $data["uidNames"] = "";
        } elseif ($data["deptid"] == "alldept") {
            $data["departmentNames"] = Ibos::lang("All");
            $data["positionNames"] = $data["uidNames"] = "";
        } else {
            $department = DepartmentUtil::loadDepartment();
            $data["departmentNames"] = ArticleUtil::joinStringByArray($data["deptid"], $department, "deptname", "、");
            $position = PositionUtil::loadPosition();
            $data["positionNames"] = ArticleUtil::joinStringByArray($data["positionid"], $position, "posname", "、");

            if (!empty($data["uid"])) {
                $users = User::model()->fetchAllByUids(explode(",", $data["uid"]));
                $data["uidNames"] = ArticleUtil::joinStringByArray($data["uid"], $users, "realname", "、");
            } else {
                $data["uidNames"] = "";
            }
        }

        return $data;
    }

    public static function getListData($datas, $uid)
    {
        $listDatas = array();
        $checkTime = 3 * 86400;
        $readArtIds = ArticleReader::model()->fetchReadArtIdsByUid($uid);

        foreach ($datas as $data) {
            $data["subject"] = StringUtil::cutStr($data["subject"], 50);
            $data["readStatus"] = (in_array($data["articleid"], $readArtIds) ? 1 : -1);
            if (($data["readStatus"] === -1) && ((TIMESTAMP - $checkTime) < $data["uptime"])) {
                $data["readStatus"] = 2;
            }

            $data["author"] = User::model()->fetchRealnameByUid($data["author"]);

            if (empty($data["uptime"])) {
                $data["uptime"] = $data["addtime"];
            }

            $data["uptime"] = ConvertUtil::formatDate($data["uptime"], "u");
            $keyword = EnvUtil::getRequest("keyword");

            if (!empty($keyword)) {
                $data["subject"] = preg_replace("|($keyword)|i", "<span style='color:red'>\$1</span>", $data["subject"]);
            }

            if ($data["ishighlight"] == "1") {
                $highLightStyle = $data["highlightstyle"];
                $hiddenInput = "<input type='hidden' id='{$data["articleid"]}_hlstyle' value='$highLightStyle'/>";
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
        $allCategorys = ArticleCategory::model()->fetchAllSortByPk("catid");
        $artApprovals = ArticleApproval::model()->fetchAllGroupByArtId();
        $backArtIds = ArticleBack::model()->fetchAllBackArtId();

        foreach ($datas as &$art) {
            $art["back"] = (in_array($art["articleid"], $backArtIds) ? 1 : 0);
            $art["approval"] = $art["approvalStep"] = array();
            $catid = $art["catid"];

            if (!empty($allCategorys[$catid]["aid"])) {
                $aid = $allCategorys[$catid]["aid"];

                if (!empty($allApprovals[$aid])) {
                    $art["approval"] = $allApprovals[$aid];
                }
            }

            if (!empty($art["approval"])) {
                $art["approvalName"] = (!empty($art["approval"]) ? $art["approval"]["name"] : "");
                $art["artApproval"] = (isset($artApprovals[$art["articleid"]]) ? $artApprovals[$art["articleid"]] : array());
                $art["stepNum"] = count($art["artApproval"]);
                $step = array();

                foreach ($art["artApproval"] as $artApproval) {
                    $step[$artApproval["step"]] = User::model()->fetchRealnameByUid($artApproval["uid"]);
                }

                for ($i = 1; $i <= $art["approval"]["level"]; $i++) {
                    if ($i <= $art["stepNum"]) {
                        $art["approval"][$i]["approvaler"] = (isset($step[$i]) ? $step[$i] : "未知");
                    } else {
                        $levelName = Approval::model()->getLevelNameByStep($i);
                        $approvalUids = $art["approval"][$levelName];
                        $art["approval"][$i]["approvaler"] = User::model()->fetchRealnamesByUids($approvalUids, "、");
                    }
                }
            }
        }

        return $datas;
    }

    public static function setReadStatus($data, $uid)
    {
        if (is_array($data) && (0 < count($data))) {
            for ($i = 0; $i < count($data); $i++) {
                $articleid = $data[$i]["articleid"];

                if (ArticleReader::model()->checkIsRead($articleid, $uid)) {
                    $data[$i]["readStatus"] = 1;
                } else {
                    $data[$i]["readStatus"] = 0;
                }
            }
        }

        return $data;
    }

    public static function formCheck($data)
    {
        if (empty($data["subject"])) {
            return false;
        }

        return true;
    }
}
