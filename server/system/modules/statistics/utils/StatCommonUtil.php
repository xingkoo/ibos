<?php

class StatCommonUtil
{
    public static function getStatisticsModules()
    {
        static $statModules = array();

        if (empty($statModules)) {
            foreach (Ibos::app()->getEnabledModule() as $module => $configs) {
                $config = CJSON::decode($configs["config"], true);

                if (isset($config["statistics"])) {
                    $statModules[] = array("module" => $module, "name" => $configs["name"]);
                }
            }
        }

        return $statModules;
    }

    public static function getWidget($module)
    {
        $modules = Ibos::app()->getEnabledModule();
        $widgets = array();

        if (isset($modules[$module])) {
            $configs = $modules[$module]["config"];
            $config = CJSON::decode($configs, true);

            if (isset($config["statistics"])) {
                $widgets = $config["statistics"];
            }
        }

        return $widgets;
    }

    public static function getWidgetName($module, $name = "")
    {
        $widgets = self::getWidget($module);
        return isset($widgets[$name]) ? $widgets[$name] : "";
    }

    public static function getCommonTimeScope()
    {
        static $timeScope = array();

        if (empty($timeScope)) {
            $time = EnvUtil::getRequest("time");
            $start = EnvUtil::getRequest("start");
            $end = EnvUtil::getRequest("end");

            if (!empty($time)) {
                if (!in_array($time, array("thisweek", "lastweek", "thismonth", "lastmonth"))) {
                    $time = "thisweek";
                }
            } else {
                if (!empty($start) && !empty($end)) {
                    $start = strtotime($start);
                    $end = strtotime($end);
                    if ($start && $end) {
                        $timeScope = array("timestr" => "custom", "start" => $start, "end" => $end);
                    }
                } else {
                    $time = "thisweek";
                }
            }

            if (empty($timeScope)) {
                $timeScope = DateTimeUtil::getStrTimeScope($time);
                $timeScope["timestr"] = $time;
            }
        }

        return $timeScope;
    }
}
