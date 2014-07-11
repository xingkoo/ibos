<?php

class ICRecruitTalentFlowCounter extends ICRecruitTimeCounter
{
    public function getID()
    {
        return "talentFlow";
    }

    public function getCount()
    {
        static $return = array();

        if (empty($return)) {
            $time = $this->getTimeScope();
            $list = ResumeStats::model()->fetchAllByTime($time["start"], $time["end"]);
            $statsTemp = array();

            if (!empty($list)) {
                foreach ($list as $stat) {
                    $statsTemp[$stat["datetime"]] = $stat;
                }
            }

            $type = $this->getType();
            if (($type == "week") || ($type == "month")) {
                $return = $this->ReplenishingWeekOrMonth($statsTemp, $time);
            } else {
                $return = $this->ReplenishingDay($statsTemp, $time);
            }
        }

        return $return;
    }

    protected function ReplenishingDay($stats, $time)
    {
        if (empty($stats)) {
            return $stats;
        }

        $return = array();
        $startDateTime = strtotime(date("Y-m-d", $time["start"]));
        $endDateTime = strtotime(date("Y-m-d", $time["end"]));

        for ($i = $startDateTime; $i <= $endDateTime; $i += 86400) {
            if (in_array($i, array_keys($stats))) {
                $return["new"]["list"][$i] = intval($stats[$i]["new"]);
                $return["pending"]["list"][$i] = intval($stats[$i]["pending"]);
                $return["interview"]["list"][$i] = intval($stats[$i]["interview"]);
                $return["employ"]["list"][$i] = intval($stats[$i]["employ"]);
                $return["eliminate"]["list"][$i] = intval($stats[$i]["eliminate"]);
            } else {
                $return["new"]["list"][$i] = 0;
                $return["pending"]["list"][$i] = 0;
                $return["interview"]["list"][$i] = 0;
                $return["employ"]["list"][$i] = 0;
                $return["eliminate"]["list"][$i] = 0;
            }

            $return["new"]["name"] = "新增简历";
            $return["pending"]["name"] = "待安排";
            $return["interview"]["name"] = "面试";
            $return["employ"]["name"] = "录用";
            $return["eliminate"]["name"] = "淘汰";
        }

        return $return;
    }

    protected function ReplenishingWeekOrMonth($stats)
    {
        if (empty($stats)) {
            return $stats;
        }

        $dateScopeTmp = $this->getDateScope();
        $dateScope = array_flip($dateScopeTmp);
        $ret = $this->getLegal($dateScope, $stats);
        return $ret;
    }

    private function getLegal($dateScope, $stats)
    {
        $return = array();

        foreach ($dateScope as $k => $date) {
            $return["new"]["list"][$k] = 0;
            $return["pending"]["list"][$k] = 0;
            $return["interview"]["list"][$k] = 0;
            $return["employ"]["list"][$k] = 0;
            $return["eliminate"]["list"][$k] = 0;
            list($st, $et) = explode(":", $date);

            foreach ($stats as $datetime => $stat) {
                if ((strtotime($st) <= $stat["datetime"]) && ($stat["datetime"] <= strtotime($et))) {
                    $return["new"]["list"][$k] += $stat["new"];
                    $return["pending"]["list"][$k] += $stat["pending"];
                    $return["interview"]["list"][$k] += $stat["interview"];
                    $return["employ"]["list"][$k] += $stat["employ"];
                    $return["eliminate"]["list"][$k] += $stat["eliminate"];
                    unset($stats[$datetime]);
                }
            }
        }

        $return["new"]["name"] = "新增简历";
        $return["pending"]["name"] = "待安排";
        $return["interview"]["name"] = "面试";
        $return["employ"]["name"] = "录用";
        $return["eliminate"]["name"] = "淘汰";
        return $return;
    }
}
