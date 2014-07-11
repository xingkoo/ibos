<?php

class ReportUtil
{
    public static function getSeason()
    {
        $season = array("season1" => "-01-01", "season2" => "-03-31", "season3" => "-04-01", "season4" => "-06-30", "season5" => "-07-01", "season6" => "-09-31", "season7" => "-10-01", "season8" => "-12-31");
        return $season;
    }

    public static function getDateByIntervalType($intervalType, $intervals)
    {
        $season = self::getSeason();
        $year = date("Y");
        $month = date("m");
        $today = date("Y-m-d");

        switch ($intervalType) {
            case "0":
                $oneday = 60 * 60 * 24;
                $time = strtotime("sunday");
                $begin = $time - ($oneday * 7);
                $end = $begin + ($oneday * 6);
                $return = array("summaryBegin" => date("Y-m-d", $begin), "summaryEnd" => date("Y-m-d", $end), "planBegin" => date("Y-m-d", $end + $oneday), "planEnd" => date("Y-m-d", $end + $oneday + ($oneday * 6)));
                break;

            case "1":
                $return = array("summaryBegin" => date("Y-m-01"), "summaryEnd" => date("Y-m-t"), "planBegin" => date("Y-m-01", strtotime("+1 month")), "planEnd" => date("Y-m-t", strtotime("+1 month")));
                break;

            case "2":
                switch ($month) {
                    case "01":
                    case "02":
                    case "03":
                        $return = array("summaryBegin" => $year . $season["season1"], "summaryEnd" => $year . $season["season2"], "planBegin" => $year . $season["season3"], "planEnd" => $year . $season["season4"]);
                        break;

                    case "04":
                    case "05":
                    case "06":
                        $return = array("summaryBegin" => $year . $season["season3"], "summaryEnd" => $year . $season["season4"], "planBegin" => $year . $season["season5"], "planEnd" => $year . $season["season6"]);
                        break;

                    case "07":
                    case "08":
                    case "09":
                        $return = array("summaryBegin" => $year . $season["season5"], "summaryEnd" => $year . $season["season6"], "planBegin" => $year . $season["season7"], "planEnd" => $year . $season["season8"]);
                        break;

                    case "10":
                    case "11":
                    case "12":
                        $return = array("summaryBegin" => $year . $season["season7"], "summaryEnd" => $year . $season["season8"], "planBegin" => ($year + 1) . $season["season1"], "planEnd" => ($year + 1) . $season["season2"]);
                        break;
                }

                break;

            case "3":
                if (in_array($month, array("01", "02", "03", "04", "05", "06"))) {
                    $return = array("summaryBegin" => $year . $season["season1"], "summaryEnd" => $year . $season["season4"], "planBegin" => $year . $season["season5"], "planEnd" => $year . $season["season8"]);
                } else {
                    $return = array("summaryBegin" => $year . $season["season5"], "summaryEnd" => $year . $season["season8"], "planBegin" => ($year + 1) . $season["season1"], "planEnd" => ($year + 1) . $season["season4"]);
                }

                break;

            case "4":
                $return = array("summaryBegin" => date("Y-01-01"), "summaryEnd" => date("Y-12-31"), "planBegin" => date("Y-01-01", strtotime("+1 year")), "planEnd" => date("Y-12-31", strtotime("+1 year")));
                break;

            case "5":
                $oneday = 60 * 60 * 24;
                $dateTime1 = strtotime($today);
                $dateTime2 = $dateTime1 + ($oneday * $intervals);
                $dateTime3 = $dateTime2 + $oneday;
                $dateTime4 = $dateTime3 + ($oneday * $intervals);
                $return = array("summaryBegin" => $today, "summaryEnd" => date("Y-m-d", $dateTime2), "planBegin" => date("Y-m-d", $dateTime3), "planEnd" => date("Y-m-d", $dateTime4));
                break;

            default:
                break;
        }

        return $return;
    }

    public static function joinCondition($condition1, $condition2)
    {
        if (empty($condition1)) {
            return $condition2;
        } else {
            return $condition1 . " AND " . $condition2;
        }
    }

    public static function joinSearchCondition($search)
    {
        $searchCondition = "";
        $keyword = $search["keyword"];
        $starttime = $search["starttime"];
        $endtime = $search["endtime"];

        if (!empty($keyword)) {
            $searchCondition .= " ( subject LIKE '%$keyword%' OR content LIKE '%$keyword%' ) AND ";
        }

        if (!empty($starttime)) {
            $starttime = strtotime($starttime);
            $searchCondition .= " begindate>=$starttime AND ";
        }

        if (!empty($endtime)) {
            $endtime = strtotime($endtime);
            $searchCondition .= " enddate<=$endtime AND ";
        }

        $condition = (!empty($searchCondition) ? substr($searchCondition, 0, -4) : "");
        return $condition;
    }

    public static function getSetting()
    {
        return Ibos::app()->setting->get("setting/reportconfig");
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
}
