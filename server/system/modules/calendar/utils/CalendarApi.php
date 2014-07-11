<?php

class CalendarApi
{
    public function renderIndex()
    {
        $data = array("schedules" => $this->loadNewSchedules(), "lant" => Ibos::getLangSource("calendar.default"), "assetUrl" => Yii::app()->assetManager->getAssetsUrl("calendar"));
        $viewAlias = "application.modules.calendar.views.indexapi.schedule";
        $return["calendar"] = Yii::app()->getController()->renderPartial($viewAlias, $data, true);
        return $return;
    }

    public function loadSetting()
    {
        return array("name" => "calendar", "title" => Ibos::lang("Calendar arrangement", "calendar.default"), "style" => "in-calendar");
    }

    public function loadNew()
    {
        return intval(0);
    }

    private function loadNewSchedules()
    {
        $uid = Yii::app()->user->uid;
        $st = time();
        $schedules = Calendars::model()->fetchNewSchedule($uid, $st);

        if (!empty($schedules)) {
            foreach ($schedules as $k => $schedule) {
                $schedules[$k]["dateAndWeekDay"] = CalendarUtil::getDateAndWeekDay(date("Y-m-d", $schedule["starttime"]));
                $schedules[$k]["category"] = Calendars::model()->handleColor($schedule["category"]);
                $schedules[$k]["cutSubject"] = StringUtil::cutStr($schedule["subject"], 30);
            }
        }

        return $schedules;
    }
}
