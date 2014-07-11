<?php

class ICRecruitDegreeCounter extends ICRecruitTimeCounter
{
    public function getID()
    {
        return "degree";
    }

    public function getCount()
    {
        static $return = array();

        if (empty($return)) {
            $time = $this->getTimeScope();
            $resumeids = Resume::model()->fetchAllByTime($time["start"], $time["end"]);
            $educations = ResumeDetail::model()->fetchFieldByRerumeids($resumeids, "education");
            $ac = array_count_values($educations);
            $return["JUNIOR_HIGH"] = array("count" => isset($ac["JUNIOR_HIGH"]) ? $ac["JUNIOR_HIGH"] : 0, "name" => "初中");
            $return["SENIOR_HIGH"] = array("count" => isset($ac["SENIOR_HIGH"]) ? $ac["SENIOR_HIGH"] : 0, "name" => "高中");
            $return["TECHNICAL_SECONDARY"] = array("count" => isset($ac["TECHNICAL_SECONDARY"]) ? $ac["TECHNICAL_SECONDARY"] : 0, "name" => "中专");
            $return["COLLEGE"] = array("count" => isset($ac["COLLEGE"]) ? $ac["COLLEGE"] : 0, "name" => "大专");
            $return["BACHELOR_DEGREE"] = array("count" => isset($ac["BACHELOR_DEGREE"]) ? $ac["BACHELOR_DEGREE"] : 0, "name" => "本科");
            $return["MASTER"] = array("count" => isset($ac["MASTER"]) ? $ac["MASTER"] : 0, "name" => "硕士");
            $return["DOCTOR"] = array("count" => isset($ac["DOCTOR"]) ? $ac["DOCTOR"] : 0, "name" => "博士");
        }

        return $return;
    }
}
