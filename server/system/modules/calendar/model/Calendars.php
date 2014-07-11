<?php

class Calendars extends ICModel
{
    public static function model($className = "Calendars")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{calendars}}";
    }

    public function listCalendarByRange($sd, $ed, $uid = "", $num = null)
    {
        $ret = array();
        $ret["events"] = array();
        $ret["issort"] = true;
        $ret["start"] = "/Date(" . $sd . "000)/";
        $ret["end"] = "/Date(" . $ed . "000)/";
        $ret["error"] = null;
        $whereuid = (empty($uid) ? "1" : "`uid`=" . $uid);
        $select = "`calendarid`, `subject`, `starttime`, `endtime`, `mastertime`, `masterid`, `isalldayevent`, `category`, `instancetype`, `recurringtime`, `recurringtype`, `status`, `recurringbegin`, `recurringend`, `upuid`, `uid`, `lock`, `isfromdiary` ";
        $handle = $this->fetchAll(array("select" => $select, "condition" => "instancetype!=1 AND status!=3 AND $whereuid AND endtime BETWEEN $sd AND $ed", "order" => "starttime ASC"));

        if (!empty($handle)) {
            foreach ($handle as $timestask) {
                $ret["events"][] = $timestask;
            }
        }

        $loops = $this->fetchAll(array(
            "select"    => $select,
            "condition" => "`instancetype`=1 AND recurringbegin<=" . $ed . " AND (`recurringend`>=" . $sd . " OR `recurringend`=0) AND $whereuid",
            "params"    => array(":uid" => $uid)
        ));

        if (!empty($loops)) {
            foreach ($loops as $loop) {
                $examples = $this->fetchAll(array("condition" => "`instancetype`=2 AND `masterid`=" . $loop["calendarid"]));
                $mastertimearr = array();

                if (!empty($examples)) {
                    foreach ($examples as $example) {
                        $mastertimearr[] = $example["mastertime"];
                    }
                }

                switch ($loop["recurringtype"]) {
                    case "week":
                        $weekarr = explode(",", $loop["recurringtime"]);
                        $dayarr = array();
                        $rstart = strtotime(date("Y-m-d", $sd));
                        $rend = strtotime(date("Y-m-d ", $ed));
                        $validitydays = ceil($rend - $rstart) / (60 * 60 * 24);

                        for ($i = 0; $i < ($validitydays + 1); $i++) {
                            $dayarr[] = mktime(0, 0, 0, date("m", $sd), date("d", $sd) + $i, date("Y", $sd));
                        }

                        $cloneid = $loop["calendarid"];

                        foreach ($dayarr as $key => $value) {
                            $weekday = date("N", $value);

                            if (in_array($weekday, $weekarr)) {
                                $loop["starttime"] = strtotime(date("Y-m-d", $value) . " " . date("H:i:s", $loop["starttime"]));
                                $loop["endtime"] = strtotime(date("Y-m-d", $value) . " " . date("H:i:s", $loop["endtime"]));
                                $issub = in_array(date("Y-m-d", $loop["starttime"]), $mastertimearr);
                                if (($sd < $loop["endtime"]) && ($loop["recurringbegin"] <= $loop["starttime"]) && (($loop["endtime"] <= ($loop["recurringend"] + (24 * 60 * 60)) - 1) || ($loop["recurringend"] == 0)) && !$issub) {
                                    $loop["calendarid"] = "-" . $loop["starttime"] . $cloneid;
                                    $ret["events"][] = $loop;
                                }
                            }
                        }

                        break;

                    case "month":
                        $day = date("d", $sd);

                        if ($day < $loop["recurringtime"]) {
                            $date = date("Y-m-", $sd) . $loop["recurringtime"] . " ";
                        } else {
                            $date = date("Y-m-", $ed) . $loop["recurringtime"] . " ";
                        }

                        $stime = date("H:i:s", $loop["starttime"]);
                        $etime = date("H:i:s", $loop["endtime"]);
                        $loop["starttime"] = strtotime($date . $stime);
                        $loop["endtime"] = strtotime($date . $etime);
                        $issub = in_array(date("Y-m-d", $loop["starttime"]), $mastertimearr);
                        if (($sd <= $loop["starttime"]) && ($loop["endtime"] <= $ed) && ($loop["recurringbegin"] <= $loop["starttime"]) && (($loop["endtime"] <= ($loop["recurringend"] + (24 * 60 * 60)) - 1) || ($loop["recurringend"] == 0)) && !$issub) {
                            $loop["calendarid"] = "-" . $loop["starttime"] . $loop["calendarid"];
                            $ret["events"][] = $loop;
                        }

                        break;

                    case "year":
                        $recurringtime = $loop["recurringtime"];
                        $date = date("Y-", $sd) . $recurringtime . " ";
                        $stime = date("H:i:s", $loop["starttime"]);
                        $etime = date("H:i:s", $loop["endtime"]);
                        $loop["starttime"] = strtotime($date . $stime);
                        $loop["endtime"] = strtotime($date . $etime);
                        $issub = in_array(date("Y-m-d", $loop["starttime"]), $mastertimearr);
                        if (($sd <= $loop["starttime"]) && ($loop["endtime"] <= $ed) && ($loop["recurringbegin"] <= $loop["starttime"]) && (($loop["endtime"] <= ($loop["recurringend"] + (24 * 60 * 60)) - 1) || ($loop["recurringend"] == 0)) && !$issub) {
                            $loop["calendarid"] = "-" . $loop["starttime"] . $loop["calendarid"];
                            $ret["events"][] = $loop;
                        }

                        break;
                }
            }

            foreach ($ret["events"] as $key => $row) {
                $starttimearr[$key] = $row["starttime"];
            }

            if (!empty($starttimearr)) {
                array_multisort($starttimearr, SORT_ASC, $ret["events"]);
            }

            if (!is_null($num)) {
                $ret["events"] = array_slice($ret["events"], 0, $num);
            }
        }

        return $ret;
    }

    public function updateSchedule($calendarid, $st, $et, $sj, $cg, $su = null)
    {
        $modifyData = array("starttime" => CalendarUtil::js2PhpTime($st), "endtime" => CalendarUtil::js2PhpTime($et), "subject" => $sj, "category" => $cg, "status" => $su);

        if (is_null($su)) {
            unset($modifyData["status"]);
        }

        $modifyResult = $this->modify($calendarid, $modifyData);

        if ($modifyResult) {
            $ret["isSuccess"] = true;
            $ret["msg"] = "操作成功";
        } else {
            $ret["isSuccess"] = false;
            $ret["msg"] = "操作失败";
        }

        return $ret;
    }

    public function removeSchedule($calendarid)
    {
        $removeSuccess = $this->remove($calendarid);

        if ($removeSuccess) {
            $ret["isSuccess"] = true;
            $ret["msg"] = "success";
        } else {
            $ret["isSuccess"] = false;
            $ret["msg"] = "fail";
        }

        return $ret;
    }

    public function fetchLoopsAndPage($conditions = "", $pageSize = null)
    {
        $pages = new CPagination($this->countByCondition($conditions));
        $pageSize = (is_null($pageSize) ? Yii::app()->params["basePerPage"] : $pageSize);
        $pages->setPageSize(intval($pageSize));
        $offset = $pages->getOffset();
        $limit = $pages->getLimit();
        $criteria = new CDbCriteria(array("limit" => $limit, "offset" => $offset));
        $pages->applyLimit($criteria);
        $fields = "`calendarid`, `subject`, `starttime`, `endtime`, `mastertime`, `masterid`, `isalldayevent`, `category`, `instancetype`, `recurringtime`, `recurringtype`, `status`, `recurringbegin`, `recurringend`, `uptime`, `upuid`, `uid`, `lock` ";
        $sql = "SELECT $fields FROM {{calendars}}";

        if (!empty($conditions)) {
            $sql .= " WHERE " . $conditions;
        }

        $sql .= " ORDER BY uptime DESC LIMIT $offset,$limit";
        $records = $this->getDbConnection()->createCommand($sql)->queryAll();
        return array("pages" => $pages, "datas" => $records);
    }

    public function countByCondition($condition = "")
    {
        if (!empty($condition)) {
            $whereCondition = " WHERE " . $condition;
            $sql = "SELECT COUNT(*) AS number FROM {{calendars}} $whereCondition";
            $record = $this->getDbConnection()->createCommand($sql)->queryAll();
            return $record[0]["number"];
        } else {
            return $this->count();
        }
    }

    public function addSchedule($uid)
    {
        $calendar = array("upuid" => Yii::app()->user->uid, "uid" => $uid, "subject" => EnvUtil::getRequest("CalendarTitle"), "starttime" => strtotime(EnvUtil::getRequest("CalendarStartTime")), "endtime" => strtotime(EnvUtil::getRequest("CalendarEndTime")), "isalldayevent" => EnvUtil::getRequest("IsAllDayEvent"));
        $isSuccess = $this->add($this->create($calendar));
        return $isSuccess;
    }

    public function fetchEditLoop($editCalendarid)
    {
        $editData = $this->fetchByPk($editCalendarid);
        $editData["starttime"] = date("H:i", $editData["starttime"]);
        $editData["endtime"] = date("H:i", $editData["endtime"]);
        $editData["recurringbegin"] = date("Y-m-d", $editData["recurringbegin"]);

        if ($editData["recurringend"] == 0) {
            $editData["recurringend"] = "";
        } else {
            $editData["recurringend"] = date("Y-m-d", $editData["recurringend"]);
        }

        return $editData;
    }

    public function fetchNewSchedule($uid, $st)
    {
        $todaystart = strtotime(date("Y-m-d"));
        $schedules = $this->fetchAll(array(
            "select"    => "calendarid,subject,mastertime,starttime,endtime,isalldayevent,category",
            "condition" => "uid = :uid AND (endtime > :time OR (starttime >= :todaystart && isalldayevent = 1)) AND status = 0 AND instancetype != 1",
            "params"    => array(":uid" => $uid, ":time" => $st, ":todaystart" => $todaystart),
            "order"     => "`starttime` ASC",
            "limit"     => 5
        ));
        return $schedules;
    }

    public function handleColor($cagory)
    {
        $colorArr = array("-1" => "3497DB", "0" => "3497DB", "1" => "A6C82F", "2" => "F4C73B", "3" => "EE8C0C", "4" => "E76F6F", "5" => "AD85CC", "6" => "98B2D1", "7" => "82939E");

        if (isset($colorArr[$cagory])) {
            return $colorArr[$cagory];
        } else {
            return $colorArr["-1"];
        }
    }

    public function handleTime($time)
    {
        $timeArr = explode(",", $time);
        $st = $timeArr[0];
        $et = $timeArr[1];
        $arr = array("0" => "00:00", "0.5" => "00:30", "1" => "01:00", "1.5" => "01:30", "2" => "02:00", "2.5" => "02:30", "3" => "03:00", "3.5" => "03:30", "4" => "04:00", "4.5" => "04:30", "5" => "05:00", "5.5" => "05:30", "6" => "06:00", "6.5" => "06:30", "7" => "07:00", "7.5" => "07:30", "8" => "08:00", "8.5" => "08:30", "9" => "09:00", "9.5" => "09:30", "10" => "10:00", "10.5" => "10:30", "11" => "11:00", "11.5" => "11:30", "12" => "12:00", "12.5" => "12:30", "13" => "13:00", "13.5" => "13:30", "14" => "14:00", "14.5" => "14:30", "15" => "15:00", "15.5" => "15:30", "16" => "16:00", "16.5" => "16:30", "17" => "17:00", "17.5" => "17:30", "18" => "18:00", "18.5" => "18:30", "19" => "19:00", "19.5" => "19:30", "20" => "20:00", "20.5" => "20:30", "21" => "21:00", "21.5" => "21:30", "22" => "22:00", "22.5" => "22:30", "23" => "23:00", "23.5" => "23:30");
        $ret["st"] = $arr[$st];
        $ret["et"] = $arr[$et];
        return $ret;
    }
}
