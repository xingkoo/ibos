<?php

class DepartmentUtil
{
    public static function loadDepartment()
    {
        return Ibos::app()->setting->get("cache/department");
    }

    public static function isDeptParent($deptId, $pid)
    {
        $depts = self::loadDepartment();
        $_pid = $depts[$deptId]["pid"];

        if ($_pid == 0) {
            return false;
        }

        if ($_pid == $pid) {
            return true;
        }

        return self::isDeptParent($_pid, $pid);
    }
}
