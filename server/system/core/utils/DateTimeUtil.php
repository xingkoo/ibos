<?php

class DateTimeUtil
{
    private static $_SMDay = array(1 => 31, 2 => 28, 3 => 31, 4 => 30, 5 => 31, 6 => 30, 7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31);
    private static $_LStart = 1950;
    private static $_LMDay = array(
        array(47, 29, 30, 30, 29, 30, 30, 29, 29, 30, 29, 30, 29),
        array(36, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30, 29, 30),
        array(6, 29, 30, 29, 30, 59, 29, 30, 30, 29, 30, 29, 30, 29),
        array(44, 29, 30, 29, 29, 30, 30, 29, 30, 30, 29, 30, 29),
        array(33, 30, 29, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30),
        array(23, 29, 30, 59, 29, 29, 30, 29, 30, 29, 30, 30, 30, 29),
        array(42, 29, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30),
        array(30, 30, 29, 30, 29, 30, 29, 29, 59, 30, 29, 30, 29, 30),
        array(48, 30, 30, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30),
        array(38, 29, 30, 30, 29, 30, 29, 30, 29, 30, 29, 30, 29),
        array(27, 30, 29, 30, 29, 30, 59, 30, 29, 30, 29, 30, 29, 30),
        array(45, 30, 29, 30, 29, 30, 29, 30, 30, 29, 30, 29, 30),
        array(35, 29, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30, 29),
        array(24, 30, 29, 30, 58, 30, 29, 30, 29, 30, 30, 30, 29, 29),
        array(43, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30, 30),
        array(32, 29, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29),
        array(20, 30, 30, 59, 30, 29, 29, 30, 29, 29, 30, 30, 29, 30),
        array(39, 30, 30, 29, 30, 30, 29, 29, 30, 29, 30, 29, 30),
        array(29, 29, 30, 29, 30, 30, 29, 59, 30, 29, 30, 29, 30, 30),
        array(47, 29, 30, 29, 30, 29, 30, 30, 29, 30, 29, 30, 29),
        array(36, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30, 29, 30),
        array(26, 29, 30, 29, 29, 59, 30, 29, 30, 30, 30, 29, 30, 30),
        array(45, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30, 29, 30),
        array(33, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29, 30),
        array(22, 30, 30, 29, 59, 29, 30, 29, 29, 30, 30, 29, 30, 30),
        array(41, 30, 30, 29, 30, 29, 29, 30, 29, 29, 30, 29, 30),
        array(30, 30, 30, 29, 30, 29, 30, 29, 59, 29, 30, 29, 30, 30),
        array(48, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30, 29, 29),
        array(37, 30, 29, 30, 30, 29, 30, 30, 29, 30, 29, 30, 29),
        array(27, 30, 29, 29, 30, 29, 60, 29, 30, 30, 29, 30, 29, 30),
        array(46, 30, 29, 29, 30, 29, 30, 29, 30, 30, 29, 30, 30),
        array(35, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29, 30, 30),
        array(24, 30, 29, 30, 58, 30, 29, 29, 30, 29, 30, 30, 30, 29),
        array(43, 30, 29, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30),
        array(32, 30, 29, 30, 30, 29, 29, 30, 29, 29, 59, 30, 30, 30),
        array(50, 29, 30, 30, 29, 30, 29, 30, 29, 29, 30, 29, 30),
        array(39, 29, 30, 30, 29, 30, 30, 29, 30, 29, 30, 29, 29),
        array(28, 30, 29, 30, 29, 30, 59, 30, 30, 29, 30, 29, 29, 30),
        array(47, 30, 29, 30, 29, 30, 29, 30, 30, 29, 30, 30, 29),
        array(36, 30, 29, 29, 30, 29, 30, 29, 30, 29, 30, 30, 30),
        array(26, 29, 30, 29, 29, 59, 29, 30, 29, 30, 30, 30, 30, 30),
        array(45, 29, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30, 30),
        array(34, 29, 30, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30),
        array(22, 29, 30, 59, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30),
        array(40, 30, 30, 30, 29, 30, 29, 30, 29, 29, 30, 29, 30),
        array(30, 29, 30, 30, 29, 30, 29, 30, 59, 29, 30, 29, 30, 30),
        array(49, 29, 30, 29, 30, 30, 29, 30, 29, 30, 30, 29, 29),
        array(37, 30, 29, 30, 29, 30, 29, 30, 30, 29, 30, 30, 29),
        array(27, 30, 29, 29, 30, 58, 30, 30, 29, 30, 30, 29, 30, 29),
        array(46, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30, 30, 29),
        array(35, 30, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30, 29),
        array(23, 30, 30, 29, 59, 30, 29, 29, 30, 29, 30, 29, 30, 30),
        array(42, 30, 30, 29, 30, 29, 30, 29, 29, 30, 29, 30, 29),
        array(31, 30, 30, 29, 30, 30, 29, 30, 29, 29, 30, 29, 30),
        array(21, 29, 59, 30, 30, 29, 30, 29, 30, 29, 30, 29, 30, 30),
        array(39, 29, 30, 29, 30, 29, 30, 30, 29, 30, 29, 30, 29),
        array(28, 30, 29, 30, 29, 30, 29, 59, 30, 30, 29, 30, 30, 30),
        array(48, 29, 29, 30, 29, 29, 30, 29, 30, 30, 30, 29, 30),
        array(37, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30, 29, 30),
        array(25, 30, 30, 29, 29, 59, 29, 30, 29, 30, 29, 30, 30, 30),
        array(44, 30, 29, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30),
        array(33, 30, 29, 30, 30, 29, 30, 29, 29, 30, 29, 30, 29),
        array(22, 30, 29, 30, 59, 30, 29, 30, 29, 30, 29, 30, 29, 30),
        array(40, 30, 29, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30),
        array(30, 29, 30, 29, 30, 29, 30, 29, 30, 59, 30, 29, 30, 30),
        array(49, 29, 30, 29, 29, 30, 29, 30, 30, 30, 29, 30, 29),
        array(38, 30, 29, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30),
        array(27, 29, 30, 29, 30, 29, 59, 29, 30, 29, 30, 30, 30, 29),
        array(46, 29, 30, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30),
        array(35, 30, 29, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30),
        array(24, 29, 30, 30, 59, 30, 29, 29, 30, 29, 30, 29, 30, 30),
        array(42, 29, 30, 30, 29, 30, 29, 30, 29, 30, 29, 30, 29),
        array(31, 30, 29, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30),
        array(21, 29, 59, 29, 30, 30, 29, 30, 30, 29, 30, 29, 30, 30),
        array(40, 29, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30, 29),
        array(28, 30, 29, 30, 29, 29, 59, 30, 29, 30, 30, 30, 29, 30),
        array(47, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30, 30, 29),
        array(36, 30, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29),
        array(25, 30, 30, 30, 29, 59, 29, 30, 29, 29, 30, 30, 29, 30),
        array(43, 30, 30, 29, 30, 29, 30, 29, 30, 29, 29, 30, 30),
        array(33, 29, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30, 29),
        array(22, 29, 30, 59, 30, 29, 30, 30, 29, 30, 29, 30, 29, 30),
        array(41, 30, 29, 29, 30, 29, 30, 30, 29, 30, 30, 29, 30),
        array(30, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30, 59, 30, 30),
        array(49, 29, 30, 29, 29, 30, 29, 30, 29, 30, 30, 29, 30),
        array(38, 30, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29, 30),
        array(27, 30, 30, 29, 30, 29, 59, 29, 29, 30, 29, 30, 30, 29),
        array(45, 30, 30, 29, 30, 29, 29, 30, 29, 29, 30, 29, 30),
        array(34, 30, 30, 29, 30, 29, 30, 29, 30, 29, 29, 30, 29),
        array(23, 30, 30, 29, 30, 59, 30, 29, 30, 29, 30, 29, 29, 30),
        array(42, 30, 29, 30, 30, 29, 30, 29, 30, 30, 29, 30, 29),
        array(31, 29, 30, 29, 30, 29, 30, 30, 29, 30, 30, 29, 30),
        array(21, 29, 59, 29, 30, 29, 30, 29, 30, 30, 29, 30, 30, 30),
        array(40, 29, 30, 29, 29, 30, 29, 29, 30, 30, 29, 30, 30),
        array(29, 30, 29, 30, 29, 29, 30, 58, 30, 29, 30, 30, 30, 29),
        array(47, 30, 29, 30, 29, 29, 30, 29, 29, 30, 29, 30, 30),
        array(36, 30, 29, 30, 29, 30, 29, 30, 29, 29, 30, 29, 30),
        array(25, 30, 29, 30, 30, 59, 29, 30, 29, 29, 30, 29, 30, 29),
        array(44, 29, 30, 30, 29, 30, 30, 29, 30, 29, 29, 30, 29),
        array(32, 30, 29, 30, 29, 30, 30, 29, 30, 30, 29, 30, 29),
        array(22, 29, 30, 59, 29, 30, 29, 30, 30, 29, 30, 30, 29, 29)
        );

    private static function LYearName($year)
    {
        $Name = array("零", "一", "二", "三", "四", "五", "六", "七", "八", "九");
        $tmp = "";

        for ($i = 0; $i < 4; $i++) {
            for ($k = 0; $k < 10; $k++) {
                if ($year[$i] == $k) {
                    $tmp .= $Name[$k];
                }
            }
        }

        return $tmp;
    }

    private static function LMonName($month)
    {
        if ((1 <= $month) && ($month <= 12)) {
            $Name = array(1 => "正", 0 => "二", 1 => "三", 2 => "四", 3 => "五", 4 => "六", 5 => "七", 6 => "八", 7 => "九", 8 => "十", 9 => "十一", 10 => "十二");
            return $Name[$month];
        }

        return $month;
    }

    private static function LDayName($day)
    {
        if ((1 <= $day) && ($day <= 30)) {
            $Name = array(1 => "初一", 0 => "初二", 1 => "初三", 2 => "初四", 3 => "初五", 4 => "初六", 5 => "初七", 6 => "初八", 7 => "初九", 8 => "初十", 9 => "十一", 10 => "十二", 11 => "十三", 12 => "十四", 13 => "十五", 14 => "十六", 15 => "十七", 16 => "十八", 17 => "十九", 18 => "二十", 19 => "廿一", 20 => "廿二", 21 => "廿三", 22 => "廿四", 23 => "廿五", 24 => "廿六", 25 => "廿七", 26 => "廿八", 27 => "廿九", 28 => "三十");
            return $Name[$day];
        }

        return $day;
    }

    public static function S2L($date)
    {
        list($year, $month, $day) = explode("-", $date);
        if (($year <= 1951) || ($month <= 0) || ($day <= 0) || (2051 <= $year)) {
            return false;
        }

        $date1 = strtotime($year . "-01-01");
        $date2 = strtotime($year . "-" . $month . "-" . $day);
        $days = round(($date2 - $date1) / 3600 / 24);
        $days += 1;
        $Larray = self::$_LMDay[$year - self::$_LStart];

        if ($days <= $Larray[0]) {
            $Lyear = $year - 1;
            $days = $Larray[0] - $days;
            $Larray = self::$_LMDay[$Lyear - self::$_LStart];

            if ($days < $Larray[12]) {
                $Lmonth = 12;
                $Lday = $Larray[12] - $days;
            } else {
                $Lmonth = 11;
                $days = $days - $Larray[12];
                $Lday = $Larray[11] - $days;
            }
        } else {
            $Lyear = $year;
            $days = $days - $Larray[0];

            for ($i = 1; $i <= 12; $i++) {
                if ($Larray[$i] < $days) {
                    $days = $days - $Larray[$i];
                } else {
                    if (30 < $days) {
                        $days = $days - $Larray[13];
                        $Ltype = 1;
                    }

                    $Lmonth = $i;
                    $Lday = $days;
                    break;
                }
            }
        }

        return array("Lyear" => $Lyear, "Lmonth" => $Lmonth, "Lday" => $Lday);
    }

    public static function getWeekDay($timestamp = TIMESTAMP)
    {
        $weekArr = array("日", "一", "二", "三", "四", "五", "六");
        $weekDay = $weekArr[date("w", $timestamp)];
        return $weekDay;
    }

    public static function getlunarCalendar()
    {
        $dateStr = date("Y-m-d-");
        $dateArr = explode("-", $dateStr);
        $day = $dateArr[2];
        $month = $dateArr[1];
        $year = $dateArr[0];

        if (strpos($month, "0") === 0) {
            $month = substr($month, 1);
        }

        if (strpos($day, "0") === 0) {
            $day = substr($day, 1);
        }

        $LunarCalendar = self::S2L(date("Y-m-d"));
        $LunarCalendarStr = self::LMonName($LunarCalendar["Lmonth"]) . "月" . self::LDayName($LunarCalendar["Lday"]);
        return $year . "年" . $month . "月" . $day . "日，星期" . self::getWeekDay() . "，农历" . $LunarCalendarStr;
    }

    public static function getStrTimeScope($strTime, $time = TIMESTAMP)
    {
        switch ($strTime) {
            case "today":
                $start = strtotime(date("today 00:00:00", $time));
                $end = strtotime(date("today 23:59:59", $time));
                break;

            case "yesterday":
                $start = strtotime("yesterday 00:00:00", $time);
                $end = strtotime("yesterday 23:59:59", $time);
                break;

            case "thisweek":
                $start = strtotime("Monday 00:00:00 this week", $time);
                $end = strtotime("Sunday 23:59:59 this week", $time);
                break;

            case "lastweek":
                $start = strtotime("Monday 00:00:00 last week", $time);
                $end = strtotime("Sunday 23:59:59 last week", $time);
                break;

            case "thismonth":
                $start = strtotime("first day of this month 00:00:00", $time);
                $end = strtotime("last day of this month 23:59:59", $time);
                break;

            case "lastmonth":
                $start = strtotime("first day of last month 00:00:00", $time);
                $end = strtotime("last day of last month 23:59:59", $time);
                break;

            default:
                $start = $end = null;
                break;
        }

        return array("start" => $start, "end" => $end);
    }

    public static function getTime($secs, $format = "dhis")
    {
        $day = floor($secs / 86400);
        $hour = floor(($secs % 86400) / 3600);
        $min = floor(($secs % 3600) / 60);
        $sec = floor($secs % 60);
        $lang = Ibos::getLangSource("date");
        $timestr = "";
        if ((0 < $day) && stristr($format, "d")) {
            $timestr .= $day . $lang["Day"];
        }

        if ((0 < $hour) && stristr($format, "h")) {
            $timestr .= $hour . $lang["Hour"];
        }

        if ((0 < $min) && stristr($format, "i")) {
            $timestr .= $min . $lang["Min"];
        }

        if ((0 < $sec) && stristr($format, "s")) {
            $timestr .= $sec . $lang["Sec"];
        }

        return $timestr;
    }

    public static function getDays($start, $end)
    {
        $days = ($end - $start) / 86400;
        return intval($days);
    }

    public static function getDiffDate($date1, $date2)
    {
        if (strtotime($date2) < strtotime($date1)) {
            $tmp = $date2;
            $date2 = $date1;
            $date1 = $tmp;
        }

        list($Y1, $m1, $d1) = explode("-", $date1);
        list($Y2, $m2, $d2) = explode("-", $date2);
        $y = $Y2 - $Y1;
        $m = $m2 - $m1;
        $d = $d2 - $d1;

        if ($d < 0) {
            $d += (int) date("t", strtotime("-1 month $date2"));
            $m--;
        }

        if ($m < 0) {
            $m += 12;
            $y--;
        }

        return array("y" => $y, "m" => $m, "d" => $d);
    }

    public static function getSeasonByMonty($month)
    {
        switch ($month) {
            case 1:
            case 2:
            case 3:
                $season = 1;
                break;

            case 4:
            case 5:
            case 6:
                $season = 2;
                break;

            case 7:
            case 8:
            case 9:
                $season = 3;
                break;

            case 10:
            case 11:
            case 12:
                $season = 4;
                break;
        }

        return $season;
    }

    public static function getFormatDate($start, $end, $type = "Y-m-d")
    {
        $return = array();

        switch ($type) {
            case "Y-m-d":
                $days = self::getDays($start, $end);
                $return = self::formatByYMD($days, $start);
                break;

            case "weekend":
                break;

            default:
                break;
        }

        return $return;
    }

    private static function formatByYMD($days, $start)
    {
        $return = array();

        for ($i = 0; $i <= $days; $i++) {
            $return[] = date("Y-m-d", strtotime("+$i day", $start));
        }

        return $return;
    }
}
