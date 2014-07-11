<?php

class AppPersonal extends ICModel
{
    public static function model($className = "AppPersonal")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{app_personal}}";
    }

    public function fetchShortcutByUid($uid)
    {
        $shortcut = $this->fetch("uid = $uid");
        $app = array();

        if (!empty($shortcut)) {
            $app = App::model()->fetchAll("FIND_IN_SET(`appid`, '{$shortcut["shortcut"]}')");
        }

        return $app;
    }

    public function fetchWidgetByUid($uid)
    {
        $widget = $this->fetch("uid = $uid");
        $app = array();

        if (!empty($widget)) {
            $app = App::model()->fetchAll("FIND_IN_SET(`appid`, '{$widget["widget"]}')");
        }

        return $app;
    }
}
