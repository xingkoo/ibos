<?php

class ICResumeDetail
{
    public static function processAddRequestData()
    {
        $fieldArr = array("avatarid" => "", "realname" => "", "gender" => 0, "birthday" => 0, "birthplace" => "", "workyears" => "", "education" => "", "residecity" => "", "zipcode" => "", "idcard" => "", "height" => "", "weight" => "", "maritalstatus" => 0, "mobile" => "", "email" => "", "telephone" => "", "qq" => "", "msn" => "", "beginworkday" => "", "positionid" => 0, "expectsalary" => "", "workplace" => "", "recchannel" => "", "workexperience" => "", "projectexperience" => "", "eduexperience" => "", "langskill" => "", "computerskill" => "", "professionskill" => "", "trainexperience" => "", "selfevaluation" => "", "relevantcertificates" => "", "socialpractice" => "", "status" => 0, "attachmentid" => "");

        foreach ($_POST as $key => $value) {
            if (in_array($key, array_keys($fieldArr))) {
                $fieldArr[$key] = $value;
            }
        }

        $fieldArr["positionid"] = implode(",", StringUtil::getId($fieldArr["positionid"]));
        return $fieldArr;
    }

    public static function processListData($resumeList)
    {
        $position = PositionUtil::loadPosition();

        foreach ($resumeList as $k => $resume) {
            $resumeList[$k]["age"] = self::handleAge($resume["birthday"]);
            $resumeList[$k]["gender"] = self::handleGender($resume["gender"]);
            $resumeList[$k]["workyears"] = self::handleWorkyears($resume["workyears"]);
            $resumeList[$k]["status"] = self::handleResumeStatus($resume["status"]);
            $resumeList[$k]["education"] = self::handleEdu($resume["education"]);
            $resumeList[$k]["targetposition"] = (isset($position[$resume["positionid"]]) ? $position[$resume["positionid"]]["posname"] : "");
        }

        return $resumeList;
    }

    public static function processShowData($resumeDetail)
    {
        $position = PositionUtil::loadPosition();
        $resumeDetail["targetposition"] = (isset($position[$resumeDetail["positionid"]]) ? $position[$resumeDetail["positionid"]]["posname"] : "");
        $resumeDetail["age"] = self::handleAge($resumeDetail["birthday"]);
        $resumeDetail["gender"] = self::handleGender($resumeDetail["gender"]);
        $resumeDetail["workyears"] = self::handleWorkyears($resumeDetail["workyears"]);
        $resumeDetail["education"] = self::handleEdu($resumeDetail["education"]);
        $resumeDetail["maritalstatus"] = self::handleMaritalstatus($resumeDetail["maritalstatus"]);
        $resumeDetail["status"] = Resume::model()->fetchStatusByResumeid($resumeDetail["resumeid"]);
        return $resumeDetail;
    }

    public static function handleAge($birthday)
    {
        if ($birthday == 0) {
            $age = Ibos::lang("Unknown");
        } else {
            $age = intval(date("Y", time())) - intval(date("Y", $birthday));
        }

        return $age;
    }

    public static function handleGender($gender)
    {
        $sex = Ibos::lang("Unknown");

        if ($gender == 1) {
            $sex = Ibos::lang("Male");
        } elseif ($gender == 2) {
            $sex = Ibos::lang("Female");
        }

        return $sex;
    }

    public static function handleWorkyears($workyears)
    {
        $workyearsArr = array("empty" => Ibos::lang("Unknown"), "0" => Ibos::lang("Graduates"), "1" => Ibos::lang("More than one year"), "2" => Ibos::lang("More than two years"), "3" => Ibos::lang("More than three years"), "5" => Ibos::lang("More than five years"), "10" => Ibos::lang("More than a decade"));

        if (in_array($workyears, array_keys($workyearsArr))) {
            $year = $workyearsArr[$workyears];
        } else {
            $year = Ibos::lang("Unknown");
        }

        return $year;
    }

    public static function handleEdu($education)
    {
        $eduArr = array("EMPTY" => Ibos::lang("Unknown"), "JUNIOR_HIGH" => Ibos::lang("Junior high school"), "SENIOR_HIGH" => Ibos::lang("Senior middle school"), "TECHNICAL_SECONDARY" => Ibos::lang("Secondary"), "COLLEGE" => Ibos::lang("College"), "BACHELOR_DEGREE" => Ibos::lang("Undergraduate course"), "MASTER" => Ibos::lang("Master"), "DOCTOR" => Ibos::lang("Doctor"));

        if (in_array($education, array_keys($eduArr))) {
            $edu = $eduArr[$education];
        } else {
            $edu = Ibos::lang("Unknown");
        }

        return $edu;
    }

    public static function handleMaritalstatus($marriage)
    {
        $marry = Ibos::lang("Unknown");

        if ($marriage == 0) {
            $marry = Ibos::lang("Unmarried");
        } elseif ($marriage == 1) {
            $marry = Ibos::lang("Married");
        }

        return $marry;
    }

    public static function handleResumeStatus($status)
    {
        $statusArr = array("-", Ibos::lang("Interview center"), Ibos::lang("Hire"), Ibos::lang("Entry"), Ibos::lang("To be arranged"), Ibos::lang("Eliminate"));
        return $statusArr[$status];
    }
}
