<?php

class EmailUtil
{
    public static function getUserSize($uid)
    {
        $user = User::model()->fetchByUid($uid);
        $userSize = Yii::app()->setting->get("setting/emaildefsize");

        if (!empty($user["allposid"])) {
            $role = Yii::app()->setting->get("setting/emailroleallocation");

            if (!empty($role)) {
                $sizes = array();

                foreach (explode(",", $user["allposid"]) as $posId) {
                    if (isset($role[$posId])) {
                        $sizes[] = $role[$posId];
                    }
                }

                if (!empty($sizes)) {
                    rsort($sizes, SORT_NUMERIC);
                    isset($sizes[0]) && ($userSize = $sizes[0]);
                }
            }
        }

        return (int) $userSize;
    }

    public static function mergeSearchCondition($search, $uid)
    {
        $condition = "(eb.fromid = $uid OR e.toid = $uid)";
        $keyword = stripcslashes($search["keyword"]);
        $pos = (isset($search["pos"]) ? $search["pos"] : "all");
        $folder = (isset($search["folder"]) ? $search["folder"] : 0);
        $setAttach = isset($search["attachment"]) && ($search["attachment"] !== "-1");

        if ($folder == "allbynoarchive") {
            $queryArchiveId = 0;
            $folder = 0;
        } elseif ($folder == "all") {
            $ids = Yii::app()->setting->get("setting/emailtableids");
            $queryArchiveId = $ids;
            $folder = 0;
        } elseif (strpos($folder, "archive_") !== false) {
            $queryArchiveId = intval(preg_replace("/^archive_(\d+)/", "\1", $folder));
            $folder = 0;
        } else {
            $queryArchiveId = 0;
            $folder = intval($folder);
        }

        if (!empty($keyword)) {
            $allPos = $pos == "all";
            $posWhereJoin = ($allPos ? " OR " : " AND ");
            $posWhere = "";
            if (($pos == "content") || !empty($pos)) {
                if (($pos == "subject") || $allPos) {
                    $posWhere .= $posWhereJoin . "eb.subject LIKE '%$keyword%'";
                }

                if (($pos == "content") || $allPos) {
                    $posWhere .= $posWhereJoin . "eb.content LIKE '%$keyword%'";
                }

                if (($pos == "attachment") || $allPos) {
                    $containAttach = isset($search["attachment"]) && ($search["attachment"] !== "0");

                    if ($containAttach) {
                        $kwBodyIds = Email::model()->fetchAllBodyIdByKeywordFromAttach($keyword, $condition, $queryArchiveId);

                        if (0 < count($kwBodyIds)) {
                            $posWhere .= $posWhereJoin . "FIND_IN_SET(eb.bodyid,'" . implode(",", $kwBodyIds) . "')";
                        } else {
                            return array("condition" => "1=0", "archiveId" => $queryArchiveId);
                        }
                    } else {
                        return array("condition" => "1=0", "archiveId" => $queryArchiveId);
                    }
                }

                if ($allPos) {
                    $condition .= " AND (" . preg_replace("/^" . $posWhereJoin . "/", "", $posWhere) . ")";
                } else {
                    $condition .= $posWhere;
                }
            }

            if ($folder) {
                if ($folder == 1) {
                    $condition .= " AND (e.fid = 1 AND e.isdel = 0)";
                } elseif ($folder == 3) {
                    $condition .= " AND (eb.issend = 1 AND eb.issenderdel != 1 AND e.isweb=0)";
                } else {
                    $condition .= " AND (e.fid = $folder AND e.isdel = 0)";
                }
            }

            if (isset($search["dateRange"]) && ($search["dateRange"] !== "-1")) {
                $dateRange = intval($search["dateRange"]);
                $endTime = TIMESTAMP;
                $startTime = strtotime("- {$dateRange}day", $endTime);
                $condition .= " AND (eb.sendtime BETWEEN $startTime AND $endTime)";
            }

            if (isset($search["readStatus"]) && ($search["readStatus"] !== "-1")) {
                $readStatus = intval($search["readStatus"]);
                $condition .= " AND e.isread = $readStatus";
            }

            if ($setAttach) {
                if ($search["attachment"] == "0") {
                    $condition .= " AND eb.attachmentid = ''";
                } elseif ($search["attachment"] == "1") {
                    $condition .= " AND eb.attachmentid != ''";
                }
            }

            if (isset($search["sender"]) && !empty($search["sender"])) {
                $sender = StringUtil::getUid($search["sender"]);
                $condition .= " AND eb.fromid = " . implode(",", $sender);
            }

            if (isset($search["recipient"]) && !empty($search["recipient"])) {
                $recipient = StringUtil::getUid($search["recipient"]);
                $condition .= " AND e.toid = " . implode(",", $recipient);
            }

            return array("condition" => $condition, "archiveId" => $queryArchiveId);
        }
    }

    public static function joinStringByArray($str, $data, $field, $join)
    {
        if (empty($str)) {
            return "";
        }

        $result = array();
        $strArr = explode(",", $str);

        foreach ($strArr as $value) {
            if (array_key_exists($value, $data)) {
                $result[] = $data[$value][$field];
            }
        }

        $resultStr = implode($join, $result);
        return $resultStr;
    }

    public static function exportEml($id)
    {
        $data = Email::model()->fetchById($id);

        if ($data) {
            $users = UserUtil::loadUser();
            $data["copytoname"] = self::joinStringByArray($data["copytoids"], $users, "realname", ";");
            $filecontent = "Date: " . ConvertUtil::formatDate($data["sendtime"]) . "\n";
            $data["fromname"] = (isset($users[$data["fromid"]]) ? $users[$data["fromid"]]["realname"] : "");
            $filecontent .= "From: \"" . $data["fromname"] . "\"\n";
            $filecontent .= "MIME-Version: 1.0\n";
            $data["toname"] = self::joinStringByArray($data["toids"], $users, "realname", ";");
            $filecontent .= "To: \"" . $data["toname"] . "\"\n";

            if ($data["copytoids"] != "") {
                $filecontent .= "Cc: \"" . $data["copytoname"] . "\" <" . $data["copytoids"] . ">\n";
            }

            $filecontent .= "subject: " . $data["subject"] . "\n";
            $filecontent .= "Content-Type: multipart/mixed; boundary=\"==========myOA==========\"\n\n";
            $filecontent .= "This is a multi-part message in MIME format.\n";
            $filecontent .= "--==========myOA==========\n";
            $filecontent .= "Content-Type: text/html;\tcharset=\"utf-8\"\n";
            $filecontent .= "Content-Transfer-Encoding: base64\n\n";
            $filecontent .= chunk_split(base64_encode($data["content"])) . "\n";

            if ($data["attachmentid"] !== "") {
                $tempdata = AttachUtil::getAttach($data["attachmentid"], true, true, false, false, true);

                foreach ($tempdata as $value) {
                    $filecontent .= "--==========myOA==========\n";
                    $filecontent .= "Content-Type: application/octet-stream; name=\"" . $value["filename"] . "\"\n";
                    $filecontent .= "Content-Transfer-Encoding: base64\n";
                    $filecontent .= "Content-Disposition: attachment; filename=\"" . $value["filename"] . "\"\n\n";
                    $filecontent .= chunk_split(base64_encode(FileUtil::readFile($value["attachment"]))) . "\n";
                }
            }

            $filecontent .= "--==========myOA==========--";

            if (ob_get_length()) {
                ob_end_clean();
            }

            header("Cache-control: private");
            header("Content-type: message/rfc822");
            header("Accept-Ranges: bytes");
            header("Content-Disposition: attachment; filename=" . $data["subject"] . "(" . date("Y-m-d") . ").eml");
            echo $filecontent;
        }
    }

    public static function exportExcel($id)
    {
        $data = Email::model()->fetchById($id);

        if ($data) {
            $users = UserUtil::loadUser();
            header("Cache-control: private");
            header("Content-type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=" . ConvertUtil::iIconv($data["subject"], CHARSET, "GBK") . "(" . date("Y-m-d") . ").xls");
            //$html = "            <html xmlns:o=\"urn:schemas-microsoft-com:office:office\"\r\n\t\txmlns:x=\"urn:schemas-microsoft-com:office:excel\"\r\n\t\txmlns=\"http://www.w3.org/TR/REC-html40\">\r\n\t\t<head>\r\n\t\t<title></title>\r\n\t\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\r\n\t\t</head>\r\n\t\t<body topmargin=\"5\">\r\n\t\t <table border=\"1\" cellspacing=\"1\" width=\"95%\" class=\"small\" cellpadding=\"3\">\r\n\t\t\t<tr style=\"BACKGROUND: #D3E5FA; color: #000000; font-weight: bold;\">\r\n\t\t\t  <td align=\"center\">收件人：</td>\r\n\t\t\t  <td align=\"center\">发件人：</td>\r\n\t\t\t  <td align=\"center\">抄送：</td>\r\n\t\t\t  <td align=\"center\">重要性：</td>\r\n\t\t\t  <td align=\"center\">标题：</td>\r\n\t\t\t  <td align=\"center\">发送时间：</td>\r\n\t\t\t  <td align=\"center\">内容：</td>\r\n\t\t\t  <td align=\"center\">附件名称：</td>\r\n\t\t\t</tr>      ";
            $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office"
        xmlns:x="urn:schemas-microsoft-com:office:excel"
        xmlns="http://www.w3.org/TR/REC-html40">
        <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        </head>
        <body topmargin="5">
         <table border="1" cellspacing="1" width="95%" class="small" cellpadding="3">
            <tr style="BACKGROUND: #D3E5FA; color: #000000; font-weight: bold;">
              <td align="center">收件人：</td>
              <td align="center">发件人：</td>
              <td align="center">抄送：</td>
              <td align="center">重要性：</td>
              <td align="center">标题：</td>
              <td align="center">发送时间：</td>
              <td align="center">内容：</td>
              <td align="center">附件名称：</td>
            </tr>';
            $data["toname"] = self::joinStringByArray($data["toids"], $users, "realname", ";");
            $data["content"] = str_replace("  ", "&nbsp;&nbsp;", $data["content"]);
            $data["content"] = str_replace("\n", "<br>", $data["content"]);
            $data["fromname"] = (isset($users[$data["fromid"]]) ? $users[$data["fromid"]]["realname"] : "");
            $data["copytoname"] = self::joinStringByArray($data["copytoids"], $users, "realname", ";");
            $important_desc = "";

            if ($data["important"] == "0") {
                $important_desc = "";
            } elseif ($data["important"] == "1") {
                $important_desc = "<font color=\"#ff6600\">一般邮件</font>";
            } elseif ($data["important"] == "2") {
                $important_desc = "<font color=\"#FF0000\">重要邮件</font>";
            }

            $attachmentname = "";

            if ($data["attachmentid"] !== "") {
                $tempdata = AttachUtil::getAttach($data["attachmentid"]);

                foreach ($tempdata as $value) {
                    $attachmentname .= $value["filename"] . "; ";
                }
            }

            $data["sendtime"] = ConvertUtil::formatDate($data["sendtime"]);
            //$html .= "\r\n                <tr>\r\n                    <td nowrap align=\"center\">" . $data["toname"] . "</td>\r\n                    <td nowrap align=\"center\">" . $data["fromname"] . "</td>\r\n                    <td>" . $data["copytoname"] . "</td>\r\n                    <td nowrap align=\"center\">" . $important_desc . "</td>\r\n                    <td nowrap>" . $data["subject"] . "</td>\r\n                    <td nowrap align=\"center\" x:str=\"" . $data["sendtime"] . "\">" . $data["sendtime"] . "</td>\r\n                    <td>" . $data["content"] . "</td>\r\n                    <td>" . $attachmentname . "</td>\r\n                </tr>\r\n            </table>";
            $html .= '<tr>
                        <td nowrap align="center">' . $data["toname"] . '</td>
                        <td nowrap align="center">' . $data["fromname"] . '</td>
                        <td>' . $data["copytoname"] . '</td>
                        <td nowrap align="center">' . $important_desc . '</td>
                        <td nowrap>' . $data["subject"] . '</td>
                        <td nowrap align="center" x:str="' . $data["sendtime"] . '">' . $data["sendtime"] . '</td>
                        <td>' . $data["content"] . '</td>
                        <td>' . $attachmentname . '</td>
                    </tr></table>';
            echo $html;
        }
    }

    public static function getEmailSize($content, $attachmentId = "")
    {
        $tmpfile = "data/emailsize.temp";
        FileUtil::createFile($tmpfile, $content);
        $emailContentSize = FileUtil::fileSize($tmpfile);
        FileUtil::deleteFile($tmpfile);
        $attFileSize = 0;

        if (!empty($attachmentId)) {
            $attach = AttachUtil::getAttachData($attachmentId, false);

            foreach ($attach as $value) {
                $attFileSize += intval($value["filesize"]);
            }
        }

        return intval($emailContentSize + $attFileSize);
    }
}
