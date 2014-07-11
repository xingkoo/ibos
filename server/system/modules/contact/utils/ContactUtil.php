<?php

class ContactUtil
{
    public static $deptList = array();

    public static function handleDeptData($depts, $pid = 0)
    {
        if (empty($depts)) {
            return null;
        }

        foreach ($depts as $k => $dept) {
            if ($dept["pid"] == $pid) {
                self::$deptList[] = $dept;
                unset($depts[$k]);
                self::handleDeptData($depts, $dept["deptid"]);
            }
        }

        return self::$deptList;
    }

    public static function handleLetterGroup($data)
    {
        $group = $data["group"];

        foreach ($group as $letter => $value) {
            foreach ($value as $index => $uid) {
                $group[$letter][$index] = $data["datas"][$uid];
            }
        }

        return $group;
    }
}
