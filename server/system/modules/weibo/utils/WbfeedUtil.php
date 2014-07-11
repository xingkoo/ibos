<?php

class WbfeedUtil
{
    public static function pushFeed($uid, $module, $table, $rowid, $data, $type = "post")
    {
        if (empty($data["userid"]) && empty($data["deptid"]) && empty($data["positionid"])) {
            $data["view"] = 0;
        } else {
            $data["view"] = 3;
        }

        if (Feed::model()->put($uid, $module, $type, $data, $rowid, $table)) {
            return true;
        } else {
            return false;
        }
    }

    public static function getViewCondition($uid, $tableprefix = "")
    {
        $user = User::model()->fetchByUid($uid);
        $deptids = StringUtil::filterStr($user["alldeptid"] . "," . $user["alldowndeptid"]);
        $custom = sprintf("(FIND_IN_SET('%d',{$tableprefix}userid) OR FIND_IN_SET('$deptids',{$tableprefix}deptid) OR FIND_IN_SET('%s',{$tableprefix}positionid))", $uid, $user["allposid"]);
        $condition = "({$tableprefix}view = 0 OR ({$tableprefix}view = 1 AND {$tableprefix}uid = $uid) OR FIND_IN_SET('$deptids',{$tableprefix}deptid) OR {$tableprefix}deptid = 'alldept' OR $custom)";
        return $condition;
    }

    public static function hasView($feedid, $uid)
    {
        $feed = Feed::model()->get($feedid);
        $feedUser = User::model()->fetchByUid($feed["uid"]);
        $user = User::model()->fetchByUid($uid);
        if ($feed && ($feed["view"] !== WbConst::SELF_VIEW_SCOPE)) {
            $fuDeptIds = StringUtil::filterStr($feedUser["alldeptid"] . "," . $feedUser["alldowndeptid"]);
            $deptIds = StringUtil::filterStr($user["alldeptid"] . "," . $user["allupdeptid"]);

            if ($feed["view"] == WbConst::ALL_VIEW_SCOPE) {
                return true;
            } elseif ($feed["view"] == WbConst::SELFDEPT_VIEW_SCOPE) {
                if (StringUtil::findIn($fuDeptIds, $deptIds)) {
                    return true;
                }
            } else {
                if (StringUtil::findIn($feed["userid"], $uid)) {
                    return true;
                }

                if (StringUtil::findIn($feed["positionid"], $user["allposid"])) {
                    return true;
                }

                if (StringUtil::findIn($fuDeptIds, $deptIds)) {
                    return true;
                }
            }
        }

        return false;
    }
}
