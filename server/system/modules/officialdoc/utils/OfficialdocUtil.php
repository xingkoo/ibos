<?php

class OfficialdocUtil
{
    public static function joinListCondition($type, $docidArr, $catid = 0, $condition = "")
    {
        $typeWhere = "";
        $uid = Ibos::app()->user->uid;
        if (($type == "nosign") || ($type == "sign")) {
            $docidsStr = implode(",", $docidArr);
            $docids = (empty($docidsStr) ? "-1" : $docidsStr);
            $flag = ($type == "nosign" ? "NOT" : "");
            $typeWhere = " docid " . $flag . " IN($docids) AND status=1";
        } elseif ($type == "notallow") {
            $docids = Officialdoc::model()->fetchUnApprovalDocIds($catid, $uid);
            $docidStr = implode(",", $docids);
            $typeWhere = "FIND_IN_SET(`docid`, '$docidStr')";
        } elseif ($type == "draft") {
            $typeWhere = "status='3' AND author='$uid'";
        } else {
            $typeWhere = "status ='1' AND approver!=0";
        }

        $condition = (!empty($condition) ? $condition .= " AND " . $typeWhere : $typeWhere);
        $allCcDeptId = Ibos::app()->user->alldeptid . "";
        $allDeptId = Ibos::app()->user->alldeptid . "";
        $allDeptId .= "," . Ibos::app()->user->allupdeptid . "";
        $allPosId = Ibos::app()->user->allposid . "";
        $deptCondition = "";
        $deptIdArr = explode(",", trim($allDeptId, ","));

        if (0 < count($deptIdArr)) {
            foreach ($deptIdArr as $deptId) {
                $deptCondition .= "FIND_IN_SET('$deptId',deptid) OR ";
            }

            $deptCondition = substr($deptCondition, 0, -4);
        } else {
            $deptCondition = "FIND_IN_SET('',deptid)";
        }

        $scopeCondition = " ( ((deptid='alldept' OR $deptCondition OR FIND_IN_SET('$allCcDeptId',ccdeptid) OR FIND_IN_SET('$allPosId',positionid) OR FIND_IN_SET('$allPosId',ccpositionid) OR FIND_IN_SET('$uid',uid ) OR FIND_IN_SET('$uid',ccuid )) OR (deptid='' AND positionid='' AND uid='') OR (author='$uid') OR (approver='$uid')) )";
        $condition .= " AND " . $scopeCondition;

        if (!empty($catid)) {
            $condition .= " AND catid IN ($catid)";
        }

        return $condition;
    }

    public static function joinSearchCondition(array $search, $condition)
    {
        $searchCondition = "";
        $keyword = $search["keyword"];
        $starttime = $search["starttime"];
        $endtime = $search["endtime"];

        if (!empty($keyword)) {
            $searchCondition .= " subject LIKE '%$keyword%' AND ";
        }

        if (!empty($starttime)) {
            $starttime = strtotime($starttime);
            $searchCondition .= " addtime>=$starttime AND";
        }

        if (!empty($endtime)) {
            $endtime = strtotime($endtime) + (24 * 60 * 60);
            $searchCondition .= " addtime<=$endtime AND";
        }

        $newCondition = (empty($searchCondition) ? "" : substr($searchCondition, 0, -4));
        return $condition . $newCondition;
    }

    public static function checkReadScope($uid, $data)
    {
        if ($data["deptid"] == "alldept") {
            return true;
        }

        if ($uid == $data["author"]) {
            return true;
        }

        if (empty($data["deptid"]) && empty($data["positionid"]) && empty($data["uid"])) {
            return true;
        }

        $user = User::model()->fetch(array(
            "select"    => array("deptid", "positionid"),
            "condition" => "uid=:uid",
            "params"    => array(":uid" => $uid)
        ));
        $childDeptid = Department::model()->fetchChildIdByDeptids($data["deptid"]);

        if (StringUtil::findIn($user["deptid"], $childDeptid . "," . $data["deptid"])) {
            return true;
        }

        $childCcDeptid = Department::model()->fetchChildIdByDeptids($data["ccdeptid"]);

        if (StringUtil::findIn($user["deptid"], $childCcDeptid . "," . $data["ccdeptid"])) {
            return true;
        }

        if (StringUtil::findIn($data["positionid"], $user["positionid"])) {
            return true;
        }

        if (StringUtil::findIn($data["uid"], $uid)) {
            return true;
        }

        if (StringUtil::findIn($data["ccpositionid"], $user["positionid"])) {
            return true;
        }

        if (StringUtil::findIn($data["ccuid"], $uid)) {
            return true;
        }

        return false;
    }

    public static function getScopeUidArr($data)
    {
        $uidArr = array();

        if ($data["deptid"] == "alldept") {
            $users = Yii::app()->setting->get("cache/users");

            foreach ($users as $value) {
                $uidArr[] = $value["uid"];
            }
        } elseif (!empty($data["deptid"])) {
            foreach (explode(",", $data["deptid"]) as $value) {
                $criteria = array("select" => "uid", "condition" => "`deptid`=$value");
                $records = User::model()->fetchAll($criteria);

                foreach ($records as $record) {
                    $uidArr[] = $record["uid"];
                }
            }
        }

        if (!empty($data["positionid"])) {
            foreach (explode(",", $data["positionid"]) as $value) {
                $criteria = array("select" => "uid", "condition" => "`positionid`=$value");
                $records = User::model()->fetchAll($criteria);

                foreach ($records as $record) {
                    $uidArr[] = $record["uid"];
                }
            }
        }

        if (!empty($data["uid"])) {
            foreach (explode(",", $data["uid"]) as $value) {
                $uidArr[] = $value;
            }
        }

        return array_unique($uidArr);
    }

    public static function joinStringByArray($str, $data, $field, $join)
    {
        if (empty($str)) {
            return "";
        }

        $result = array();
        $strArr = explode(",", $str);

        foreach ($strArr as $value) {
            if (array_key_exists($value, $data)) {
                $result[] = $data[$value][$field];
            }
        }

        $resultStr = implode($join, $result);
        return $resultStr;
    }

    public static function handleSelectBoxData($data, $flag = true)
    {
        $result = array("deptid" => "", "positionid" => "", "uid" => "");

        if (!empty($data)) {
            if (isset($data["c"])) {
                $result = array("deptid" => "alldept", "positionid" => "", "uid" => "");
                return $result;
            }

            if (isset($data["d"])) {
                $result["deptid"] = implode(",", $data["d"]);
            }

            if (isset($data["p"])) {
                $result["positionid"] = implode(",", $data["p"]);
            }

            if (isset($data["u"])) {
                $result["uid"] = implode(",", $data["u"]);
            }
        } elseif ($flag) {
            $result["deptid"] = "alldept";
        }

        return $result;
    }

    public static function joinSelectBoxValue($deptid, $positionid, $uid)
    {
        $tmp = array();

        if (!empty($deptid)) {
            if ($deptid == "alldept") {
                return "c_0";
            }

            $tmp[] = StringUtil::wrapId($deptid, "d");
        }

        if (!empty($positionid)) {
            $tmp[] = StringUtil::wrapId($positionid, "p");
        }

        if (!empty($uid)) {
            $tmp[] = StringUtil::wrapId($uid, "u");
        }

        return implode(",", $tmp);
    }

    public static function processHighLightRequestData($data)
    {
        $highLight = array();
        $highLight["highlightstyle"] = "";

        if (!empty($data["endTime"])) {
            $highLight["highlightendtime"] = (strtotime($data["endTime"]) + (24 * 60 * 60)) - 1;
        }

        if (empty($data["bold"])) {
            $data["bold"] = 0;
        }

        $highLight["highlightstyle"] = $highLight["highlightstyle"] . $data["bold"] . ",";

        if (empty($data["color"])) {
            $data["color"] = "";
        }

        $highLight["highlightstyle"] = $highLight["highlightstyle"] . $data["color"] . ",";

        if (empty($data["italic"])) {
            $data["italic"] = 0;
        }

        $highLight["highlightstyle"] = $highLight["highlightstyle"] . $data["italic"] . ",";

        if (empty($data["underline"])) {
            $data["underline"] = 0;
        }

        $highLight["highlightstyle"] = $highLight["highlightstyle"] . $data["underline"] . ",";
        $highLight["highlightstyle"] = substr($highLight["highlightstyle"], 0, strlen($highLight["highlightstyle"]) - 1);
        if (!empty($highLight["highlightendtime"]) || (3 < strlen($highLight["highlightstyle"]))) {
            $highLight["ishighlight"] = 1;
        } else {
            $highLight["ishighlight"] = 0;
        }

        return $highLight;
    }

    public static function getVerify()
    {
        $positionids = PositionPurview::model()->fetchArticleVerifyPositionIds();
        $userDatas = array();

        if (!empty($positionids)) {
            $datas = User::model()->fetchAllByStatusAndPositionIds($positionids);

            foreach ($datas as $data) {
                $userDatas[$data["uid"]] = $data["realname"];
            }
        }

        return $userDatas;
    }

    public static function changeVersion($version, $increment = 0.1, $minVersion = 1)
    {
        $newVersion = $minVersion + ($increment * ($version - 1));

        if (!strpos($newVersion, ".")) {
            $newVersion = $newVersion . ".0";
        }

        return $newVersion;
    }

    public static function getCharacterLength($html)
    {
        $len = 0;
        $contents = preg_split("~(<[^>]+?>)~si", $html, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        foreach ($contents as $tag) {
            if (trim($tag) == "") {
                continue;
            }

            if (preg_match("~<([a-z0-9]+)[^/>]*?/>~si", $tag)) {
                continue;
            } elseif (preg_match("~</([a-z0-9]+)[^/>]*?>~si", $tag, $match)) {
                continue;
            } elseif (preg_match("~<([a-z0-9]+)[^/>]*?>~si", $tag, $match)) {
                continue;
            } elseif (preg_match("~<!--.*?-->~si", $tag)) {
                continue;
            } else {
                $len += self::mstrlen($tag);
            }
        }

        return $len;
    }

    public static function subHtml($html, $start, $length)
    {
        $result = "";
        $tagStack = array("");
        $len = 0;
        $contents = preg_split("~(<[^>]+?>)~si", $html, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        foreach ($contents as $tag) {
            if (trim($tag) == "") {
                continue;
            }

            if (preg_match("~<([a-z0-9]+)[^/>]*?/>~si", $tag)) {
                if (($start <= $len) && ($len <= $length)) {
                    $result .= $tag;
                }
            } elseif (preg_match("~</([a-z0-9]+)[^/>]*?>~si", $tag, $match)) {
                if (($start <= $len) && ($len < $length)) {
                    if ($tagStack[count($tagStack) - 1] == $match[1]) {
                        array_pop($tagStack);
                        $result .= $tag;
                    }
                }
            } elseif (preg_match("~<([a-z0-9]+)[^/>]*?>~si", $tag, $match)) {
                if (($start <= $len) && ($len <= $length)) {
                    array_push($tagStack, $match[1]);
                    $result .= $tag;
                }
            } elseif (preg_match("~<!--.*?-->~si", $tag)) {
                if (($start <= $len) && ($len <= $length)) {
                    $result .= $tag;
                }
            } elseif (($len + self::mstrlen($tag)) < $length) {
                $len += self::mstrlen($tag);
                if (($start <= $len) && ($len <= $length)) {
                    $result .= $tag;
                }
            } else {
                $str = self::msubstr($tag, 0, ($length - $len) + 1);
                $result .= $str;
                break;
            }
        }

        while (!empty($tagStack)) {
            $result .= "</" . array_pop($tagStack) . ">";
        }

        return $result;
    }

    public static function msubstr($string, $start, $length, $dot = "", $charset = "UTF-8")
    {
        $string = str_replace(array("&amp;", "&quot;", "&lt;", "&gt;", "&nbsp;"), array("&", "\"", "<", ">", " "), $string);

        if (strlen($string) <= $length) {
            return $string;
        }

        if (strtolower($charset) == "utf-8") {
            $n = $tn = $noc = 0;

            while ($n < strlen($string)) {
                $t = ord($string[$n]);
                if (($t == 9) || ($t == 10) || ((32 <= $t) && ($t <= 126))) {
                    $tn = 1;
                    $n++;
                } else {
                    if ((194 <= $t) && ($t <= 223)) {
                        $tn = 2;
                        $n += 2;
                    } else {
                        if ((224 <= $t) && ($t <= 239)) {
                            $tn = 3;
                            $n += 3;
                        } else {
                            if ((240 <= $t) && ($t <= 247)) {
                                $tn = 4;
                                $n += 4;
                            } else {
                                if ((248 <= $t) && ($t <= 251)) {
                                    $tn = 5;
                                    $n += 5;
                                } else {
                                    if (($t == 252) || ($t == 253)) {
                                        $tn = 6;
                                        $n += 6;
                                    } else {
                                        $n++;
                                    }
                                }
                            }
                        }
                    }
                }

                $noc++;

                if ($length <= $noc) {
                    break;
                }
            }

            if ($length < $noc) {
                $n -= $tn;
            }

            $strcut = substr($string, 0, $n);
        } else {
            for ($i = 0; $i < $length; $i++) {
                $strcut .= (127 < ord($string[$i]) ? $string[$i] . $string[++$i] : $string[$i]);
            }
        }

        return $strcut . $dot;
    }

    public static function mstrlen($str, $charset = "UTF-8")
    {
        if (function_exists("mb_substr")) {
            $length = mb_strlen($str, $charset);
        } elseif (function_exists("iconv_substr")) {
            $length = iconv_strlen($str, $charset);
        } else {
            $arr = array();
            preg_match_all("/[x01-x7f]|[xc2-xdf][x80-xbf]|xe0[xa0-xbf][x80-xbf]|[xe1-xef][x80-xbf][x80-xbf]|xf0[x90-xbf][x80-xbf][x80-xbf]|[xf1-xf7][x80-xbf][x80-xbf][x80-xbf]/", $str, $arr);
            $length = count($arr[0]);
        }

        return $length;
    }
}
