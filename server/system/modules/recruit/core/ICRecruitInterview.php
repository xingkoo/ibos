<?php

class ICRecruitInterview
{
    public static function processListData($interviewList)
    {
        foreach ($interviewList as $k => $interview) {
            $interviewList[$k]["interviewtime"] = date("Y-m-d", $interview["interviewtime"]);
            $interviewList[$k]["interviewer"] = User::model()->fetchRealnameByUid($interview["interviewer"]);
            $interviewList[$k]["process"] = StringUtil::cutStr($interview["process"], 12);
            $interviewList[$k]["realname"] = ResumeDetail::model()->fetchRealnameByResumeId($interview["resumeid"]);
        }

        return $interviewList;
    }

    public static function processAddOrEditData($data)
    {
        $inverviewArr = array("interviewtime" => 0, "interviewer" => 0, "method" => "", "type" => "", "process" => "");

        foreach ($data as $k => $v) {
            if (in_array($k, array_keys($inverviewArr))) {
                $inverviewArr[$k] = $v;
            }
        }

        $interviewer = implode(",", StringUtil::getId($inverviewArr["interviewer"]));
        $inverviewArr["interviewer"] = (empty($interviewer) ? Ibos::app()->user->uid : $interviewer);

        if ($inverviewArr["interviewtime"] != 0) {
            $inverviewArr["interviewtime"] = strtotime($inverviewArr["interviewtime"]);
        } else {
            $inverviewArr["interviewtime"] = TIMESTAMP;
        }

        return $inverviewArr;
    }
}
