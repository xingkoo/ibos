<?php

class AssignmentUtil
{
    const CLASS_UNREAD = "unread";
    const CLASS_ONGOING = "ongoing";
    const CLASS_CANCEL = "cancel";

    public static function handleListData($data)
    {
        $reminds = AssignmentRemind::model()->fetchAllByUid(Ibos::app()->user->uid);

        foreach ($data as $k => $assignment) {
            $data[$k] = self::handleShowData($assignment);
            $aid = $assignment["assignmentid"];
            $data[$k]["remindtime"] = (in_array($aid, array_keys($reminds)) ? $reminds[$aid] : 0);

            if ($assignment["stamp"] != 0) {
                $path = Stamp::model()->fetchIconById($assignment["stamp"]);
                $data[$k]["stampPath"] = FileUtil::fileName(Stamp::STAMP_PATH . $path);
            }
        }

        return $data;
    }

    public static function handleDesigneeData($designeeData)
    {
        if (is_array($designeeData)) {
            foreach ($designeeData as $k => $des) {
                if ($des["designeeuid"] == $des["chargeuid"]) {
                    unset($designeeData[$k]);
                }
            }
        }

        return $designeeData;
    }

    public static function handleShowData($assignment)
    {
        $assignment["designee"] = User::model()->fetchByUid($assignment["designeeuid"]);
        $assignment["charge"] = User::model()->fetchByUid($assignment["chargeuid"]);
        $assignment["st"] = date("m月d日 H:i", $assignment["starttime"]);
        $assignment["et"] = (!$assignment["endtime"] ? "时间待定" : date("m月d日 H:i", $assignment["endtime"]));
        return $assignment;
    }

    public static function joinCondition($condition1, $condition2)
    {
        if (empty($condition1)) {
            return $condition2;
        } else {
            return $condition1 . " AND " . $condition2;
        }
    }

    public static function getCssClassByStatus($status)
    {
        switch ($status) {
            case 0:
                $res = self::CLASS_UNREAD;
                break;

            case 1:
                $res = self::CLASS_ONGOING;
                break;

            case 4:
                $res = self::CLASS_CANCEL;
                break;

            default:
                $res = self::CLASS_UNREAD;
                break;
        }

        return $res;
    }
}
