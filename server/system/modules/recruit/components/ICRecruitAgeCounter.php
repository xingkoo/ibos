<?php

class ICRecruitAgeCounter extends ICRecruitTimeCounter
{
    public function getID()
    {
        return "age";
    }

    public function getCount()
    {
        static $return = array();

        if (empty($return)) {
            $time = $this->getTimeScope();
            $resumeids = Resume::model()->fetchAllByTime($time["start"], $time["end"]);
            $birthdays = ResumeDetail::model()->fetchFieldByRerumeids($resumeids, "birthday");
            $age23 = $age24 = $age27 = $age31 = $age41 = 0;

            foreach ($birthdays as $birthday) {
                $age = ICResumeDetail::handleAge($birthday);

                if ($age <= 23) {
                    $age23++;
                } else {
                    if ((24 <= $age) && ($age <= 26)) {
                        $age24++;
                    } else {
                        if ((27 <= $age) && ($age <= 30)) {
                            $age27++;
                        } else {
                            if ((31 <= $age) && ($age <= 40)) {
                                $age31++;
                            } elseif (41 <= $age) {
                                $age41++;
                            }
                        }
                    }
                }
            }

            $return["age23"] = array("count" => $age23, "name" => "23岁以下");
            $return["age24"] = array("count" => $age24, "name" => "24-26岁");
            $return["age27"] = array("count" => $age27, "name" => "27-30岁");
            $return["age31"] = array("count" => $age31, "name" => "31-40岁");
            $return["age41"] = array("count" => $age41, "name" => "41岁以上");
        }

        return $return;
    }
}
