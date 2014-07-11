<?php

class ICCron extends CApplicationComponent
{
    public function run($cronId = 0)
    {
        if ($cronId) {
            $cron = Cron::model()->fetchByPk($cronId);
        } else {
            $cron = Cron::model()->fetchByNextRun(TIMESTAMP);
        }

        $processName = "MAIN_CRON_" . (empty($cron) ? "CHECKER" : $cron["cronid"]);
        if ($cronId && !empty($cron)) {
            Ibos::app()->process->unLock($processName);
        }

        if (Ibos::app()->process->isLocked($processName, 600)) {
            return false;
        }

        if ($cron) {
            $cron["filename"] = str_replace(array("..", "/", "\\"), "", $cron["filename"]);
            $cron["minute"] = explode("\t", $cron["minute"]);
            $this->setNextTime($cron);
            @set_time_limit(1000);
            @ignore_user_abort(true);
            $cronFile = $this->getRealCronFile($cron["type"], $cron["filename"], $cron["module"]);

            if (!@include ($cronFile)) {
                return false;
            }
        }

        $this->nextCron();
        Ibos::app()->process->unLock($processName);
        return true;
    }

    private function nextCron()
    {
        $cron = Cron::model()->fetchByNextCron();
        if ($cron && isset($cron["nextrun"])) {
            $data = $cron["nextrun"];
        } else {
            $data = TIMESTAMP + (86400 * 365);
        }

        Syscache::model()->modify("cronnextrun", $data);
        return true;
    }

    private function setNextTime($cron)
    {
        if (empty($cron)) {
            return false;
        }

        $timeoffSet = Ibos::app()->setting->get("setting/timeoffset");
        list($yearNow, $monthNow, $dayNow, $weekdayNow, $hourNow, $minuteNow) = explode("-", gmdate("Y-m-d-w-H-i", TIMESTAMP + ($timeoffSet * 3600)));

        if ($cron["weekday"] == -1) {
            if ($cron["day"] == -1) {
                $firstDay = $dayNow;
                $secondDay = $dayNow + 1;
            } else {
                $firstDay = $cron["day"];
                $secondDay = $cron["day"] + gmdate("t", TIMESTAMP + ($timeoffSet * 3600));
            }
        } else {
            $firstDay = $dayNow + ($cron["weekday"] - $weekdayNow);
            $secondDay = $firstDay + 7;
        }

        if ($firstDay < $dayNow) {
            $firstDay = $secondDay;
        }

        if ($firstDay == $dayNow) {
            $todayTime = $this->todayNextRun($cron);
            if (($todayTime["hour"] == -1) && ($todayTime["minute"] == -1)) {
                $cron["day"] = $secondDay;
                $nextTime = $this->todayNextRun($cron, 0, -1);
                $cron["hour"] = $nextTime["hour"];
                $cron["minute"] = $nextTime["minute"];
            } else {
                $cron["day"] = $firstDay;
                $cron["hour"] = $todayTime["hour"];
                $cron["minute"] = $todayTime["minute"];
            }
        } else {
            $cron["day"] = $firstDay;
            $nextTime = $this->todayNextRun($cron, 0, -1);
            $cron["hour"] = $nextTime["hour"];
            $cron["minute"] = $nextTime["minute"];
        }

        $nextRun = @gmmktime($cron["hour"], 0 < $cron["minute"] ? $cron["minute"] : 0, 0, $monthNow, $cron["day"], $yearNow) - ($timeoffSet * 3600);
        $data = array("lastrun" => TIMESTAMP, "nextrun" => $nextRun);

        if (!TIMESTAMP < $nextRun) {
            $data["available"] = "0";
        }

        Cron::model()->modify($cron["cronid"], $data);
        return true;
    }

    private function todayNextRun($cron, $hour = -2, $minute = -2)
    {
        $timeoffSet = Ibos::app()->setting->get("setting/timeoffset");
        $hour = ($hour == -2 ? gmdate("H", TIMESTAMP + ($timeoffSet * 3600)) : $hour);
        $minute = ($minute == -2 ? gmdate("i", TIMESTAMP + ($timeoffSet * 3600)) : $minute);
        $nextTime = array();
        if (($cron["hour"] == -1) && !$cron["minute"]) {
            $nextTime["hour"] = $hour;
            $nextTime["minute"] = $minute + 1;
        } else {
            if (($cron["hour"] == -1) && ($cron["minute"] != "")) {
                $nextTime["hour"] = $hour;

                if (($nextMinute = $this->nextMinute($cron["minute"], $minute)) === false) {
                    ++$nextTime["hour"];
                    $nextMinute = $cron["minute"][0];
                }

                $nextTime["minute"] = $nextMinute;
            } else {
                if (($cron["hour"] != -1) && ($cron["minute"] == "")) {
                    if ($cron["hour"] < $hour) {
                        $nextTime["hour"] = $nextTime["minute"] = -1;
                    } elseif ($cron["hour"] == $hour) {
                        $nextTime["hour"] = $cron["hour"];
                        $nextTime["minute"] = $minute + 1;
                    } else {
                        $nextTime["hour"] = $cron["hour"];
                        $nextTime["minute"] = 0;
                    }
                } else {
                    if (($cron["hour"] != -1) && ($cron["minute"] != "")) {
                        $nextMinute = $this->nextMinute($cron["minute"], $minute);
                        if (($cron["hour"] < $hour) || (($cron["hour"] == $hour) && ($nextMinute === false))) {
                            $nextTime["hour"] = -1;
                            $nextTime["minute"] = -1;
                        } else {
                            $nextTime["hour"] = $cron["hour"];
                            $nextTime["minute"] = $nextMinute;
                        }
                    }
                }
            }
        }

        return $nextTime;
    }

    private function nextMinute($nextMinutes, $minuteNow)
    {
        foreach ($nextMinutes as $nextMinute) {
            if ($minuteNow < $nextMinute) {
                return $nextMinute;
            }
        }

        return false;
    }

    private function getRealCronFile($type, $fileName, $module = "")
    {
        if ($type == "user") {
            $cronFile = "./system/extensions/cron/" . $fileName;
        } else {
            $cronFile = sprintf("./system/modules/%s/cron/%s", $module, $fileName);
        }

        return $cronFile;
    }
}
