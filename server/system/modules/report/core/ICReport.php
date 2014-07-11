<?php

class ICReport
{
    public static function handelListData($reports)
    {
        $return = array();

        foreach ($reports as $report) {
            $report["cutSubject"] = StringUtil::cutStr(strip_tags($report["subject"]), 60);
            $report["user"] = User::model()->fetchByUid($report["uid"]);
            $readeruid = $report["readeruid"];
            $report["readercount"] = (empty($readeruid) ? 0 : count(explode(",", trim($readeruid, ","))));
            $report["content"] = StringUtil::cutStr(strip_tags($report["content"]), 255);
            $report["addtime"] = ConvertUtil::formatDate($report["addtime"], "u");

            if ($report["stamp"] != 0) {
                $path = Stamp::model()->fetchIconById($report["stamp"]);
                $report["stampPath"] = FileUtil::fileName(Stamp::STAMP_PATH . $path);
            }

            $return[] = $report;
        }

        return $return;
    }

    public static function handleSaveData($data)
    {
        $fieldDefault = array("uid" => 0, "begindate" => 0, "enddate" => 0, "addtime" => TIMESTAMP, "typeid" => 0, "subject" => "", "content" => "", "attachmentid" => "", "toid" => "", "readeruid" => "", "status" => 0, "remark" => "", "stamp" => 0, "lastcommenttime" => 0, "comment" => "", "commentline" => 0, "replyer" => 0, "reminddate" => 0, "commentcount" => 0);

        foreach ($data as $field => $val) {
            if (array_key_exists($field, $fieldDefault)) {
                $fieldDefault[$field] = $val;
            }
        }

        return $fieldDefault;
    }

    public static function handleShowSubject($reportType, $begin, $end, $connection = 0)
    {
        if ($reportType["intervaltype"] == 5) {
            $connectTitle = $reportType["typename"];
        } else {
            $interval = ICReportType::handleShowInterval($reportType["intervaltype"]);
            $connectTitle = ($connection == 0 ? $interval . "报" : $interval . "计划");
        }

        $subject = date("m.d", $begin) . " - " . date("m.d", $end) . " " . $connectTitle;
        return $subject;
    }

    public static function checkPermission($report, $uid)
    {
        $toid = explode(",", $report["toid"]);
        if (($report["uid"] == $uid) || in_array($uid, $toid) || UserUtil::checkIsSub($uid, $report["uid"])) {
            return true;
        } else {
            return false;
        }
    }
}
