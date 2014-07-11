<?php

class DiaryUtil
{
    public static function getDateAndWeekDay($dateStr)
    {
        list($year, $month, $day) = explode("-", $dateStr);
        $weekArray = array(Ibos::lang("Day", "date"), Ibos::lang("One", "date"), Ibos::lang("Two", "date"), Ibos::lang("Three", "date"), Ibos::lang("Four", "date"), Ibos::lang("Five", "date"), Ibos::lang("Six", "date"));
        $weekday = $weekArray[date("w", strtotime($dateStr))];
        return array("year" => $year, "month" => $month, "day" => $day, "weekday" => Ibos::lang("Weekday", "date") . $weekday);
    }

    public static function joinSearchCondition($search)
    {
        $searchCondition = "";
        $keyword = $search["keyword"];
        $starttime = $search["starttime"];
        $endtime = $search["endtime"];

        if (!empty($keyword)) {
            $searchCondition .= " content LIKE '%$keyword%' AND ";
        }

        if (!empty($starttime)) {
            $starttime = strtotime($starttime);
            $searchCondition .= " diarytime>=$starttime AND ";
        }

        if (!empty($endtime)) {
            $endtime = strtotime($endtime);
            $searchCondition .= " diarytime<=$endtime AND ";
        }

        $condition = (!empty($searchCondition) ? substr($searchCondition, 0, -4) : "");
        return $condition;
    }

    public static function joinCondition($condition1, $condition2)
    {
        if (empty($condition1)) {
            return $condition2;
        } else {
            return $condition1 . " AND " . $condition2;
        }
    }

    public static function processReaderList($readerList)
    {
        $result = array();

        foreach ($readerList as $reader) {
            if (array_key_exists($reader["departmentName"], $result)) {
                $result[$reader["departmentName"]] = $result[$reader["departmentName"]] . "," . $reader["realname"];
            } else {
                $result[$reader["departmentName"]] = $reader["realname"];
            }
        }

        return $result;
    }

    public static function getCalendar($ym, $diaryList, $currentDay)
    {
        if ($ym) {
            $year = substr($ym, 0, 4);
            $month = substr($ym, 4, strlen($ym) - 4);

            if (12 < $month) {
                $year += floor($month / 12);
                $month = $month % 12;
            }

            if (2030 < $year) {
                $year = 2030;
            }

            if ($year < 1980) {
                $year = 1980;
            }
        }

        $nowtime = mktime(0, 0, 0, $month, 1, $year);
        $daysofmonth = date("t", $nowtime);
        $weekofbeginday = date("w", $nowtime);
        $weekofendday = date("w", mktime(0, 0, 0, $month + 1, 0, $year));
        $daysofprevmonth = date("t", mktime(0, 0, 0, $month, 0, $year));
        $result = array();
        $count = 1;

        for ($i = 1; $i <= $weekofbeginday; $i++) {
            $result[] = array("day" => ($daysofprevmonth - $weekofbeginday) + $i, "className" => "old", "diaryid" => "");
            $count++;
        }

        for ($i = 1; $i <= $daysofmonth; $i++) {
            $css = "";

            if ($i == $currentDay) {
                $css .= "current";
            } else {
                if (($diaryList[$i]["isLog"] == true) && ($diaryList[$i]["isComment"] == false)) {
                    $css .= "log";
                } else {
                    if (($diaryList[$i]["isLog"] == true) && ($diaryList[$i]["isComment"] == true)) {
                        $css .= "log comment";
                    }
                }
            }

            $result[] = array("day" => $i, "className" => $css, "diaryid" => $diaryList[$i]["diaryid"]);
            $count++;
        }

        for ($i = 1; $i <= 6 - $weekofendday; $i++) {
            $result[] = array("day" => $i, "className" => "new", "diaryid" => "");
        }

        return $result;
    }

    public static function checkShowPurview($uid, $author)
    {
        $flag = false;

        if ($uid == $author) {
            return true;
        }

        $subUidArr = UserUtil::getAllSubs($uid, "", true);

        if (StringUtil::findIn($author, implode(",", $subUidArr))) {
            $flag = true;
        }

        return $flag;
    }

    public static function removeNullVal($arr)
    {
        $ret = array_filter($arr, create_function("\$v", "return !empty(\$v);"));
        return $ret;
    }

    public static function getSetting()
    {
        return Ibos::app()->setting->get("setting/diaryconfig");
    }

    public static function getIsAttention($attentionUid)
    {
        $aUids = DiaryAttention::model()->fetchAuidByUid(Ibos::app()->user->uid);
        return in_array($attentionUid, $aUids);
    }

    public static function getScoreByStamp($stamp)
    {
        $stamps = self::getEnableStamp();

        if (isset($stamps[$stamp])) {
            return $stamps[$stamp];
        } else {
            return 0;
        }
    }

    public static function getEnableStamp()
    {
        $config = self::getSetting();
        $stampDetails = $config["stampdetails"];
        $stamps = array();

        if (!empty($stampDetails)) {
            $stampidArr = explode(",", trim($stampDetails));

            if (0 < count($stampidArr)) {
                foreach ($stampidArr as $stampidStr) {
                    list($stampId, $score) = explode(":", $stampidStr);

                    if ($stampId != 0) {
                        $stamps[$stampId] = intval($score);
                    }
                }
            }
        }

        return $stamps;
    }

    public static function checkIsHasSub()
    {
        static $hasSub;

        if ($hasSub === null) {
            $subUidArr = User::model()->fetchSubUidByUid(Ibos::app()->user->uid);

            if (!empty($subUidArr)) {
                $hasSub = true;
            } else {
                $hasSub = false;
            }
        }

        return $hasSub;
    }

    public static function getOffTime()
    {
        if (ModuleUtil::getIsEnabled("calendar")) {
            $workTime = explode(",", Ibos::app()->setting->get("setting/calendarworkingtime"));
            $offTime = $workTime[1];
            $ret = self::handleOffTime($offTime);
        } else {
            $ret = "18.00";
        }

        return $ret;
    }

    public static function handleOffTime($offTime)
    {
        $times = array("0" => "00.00", "0.5" => "00.30", "1" => "01.00", "1.5" => "01.30", "2" => "02.00", "2.5" => "02.30", "3" => "03.00", "3.5" => "03.30", "4" => "04.00", "4.5" => "04.30", "5" => "05.00", "5.5" => "05.30", "6" => "06.00", "6.5" => "06.30", "7" => "07.00", "7.5" => "07.30", "8" => "08.00", "8.5" => "08.30", "9" => "09.00", "9.5" => "09.30", "10" => "10.00", "10.5" => "10.30", "11" => "11.00", "11.5" => "11.30", "12" => "12.00", "12.5" => "12.30", "13" => "13.00", "13.5" => "13.30", "14" => "14.00", "14.5" => "14.30", "15" => "15.00", "15.5" => "15.30", "16" => "16.00", "16.5" => "16.30", "17" => "17.00", "17.5" => "17.30", "18" => "18.00", "18.5" => "18.30", "19" => "19.00", "19.5" => "19.30", "20" => "20.00", "20.5" => "20.30", "21" => "21.00", "21.5" => "21.30", "22" => "22.00", "22.5" => "22.30", "23" => "23.00", "23.5" => "23.30", "24" => "24.00");

        if (isset($times[$offTime])) {
            return $times[$offTime];
        } else {
            return "18.00";
        }
    }
}
