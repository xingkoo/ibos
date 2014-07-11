<?php

class ICRecruitSexCounter extends ICRecruitTimeCounter
{
    public function getID()
    {
        return "sex";
    }

    public function getCount()
    {
        static $return = array();

        if (empty($return)) {
            $time = $this->getTimeScope();
            $resumeids = Resume::model()->fetchAllByTime($time["start"], $time["end"]);
            $genders = ResumeDetail::model()->fetchFieldByRerumeids($resumeids, "gender");
            $ac = array_count_values($genders);
            $return["male"] = array("count" => isset($ac["1"]) ? $ac["1"] : 0, "sex" => "男");
            $return["female"] = array("count" => isset($ac["2"]) ? $ac["2"] : 0, "sex" => "女");
        }

        return $return;
    }
}
