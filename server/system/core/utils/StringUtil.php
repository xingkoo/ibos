<?php

class StringUtil
{
    public static function istrpos($string, $arr, $returnValue = false)
    {
        if (empty($string)) {
            return false;
        }

        foreach ((array) $arr as $v) {
            if (strpos($string, $v) !== false) {
                $return = ($returnValue ? $v : true);

                return $return;
            }
        }

        return false;
    }

    public static function iaddSlashes($string, $force = 1)
    {
        if (is_array($string)) {
            $keys = array_keys($string);

            foreach ($keys as $key) {
                $val = $string[$key];
                unset($string[$key]);
                $string[addslashes($key)] = self::iaddSlashes($val, $force);
            }
        } else {
            $string = addslashes($string);
        }

        return $string;
    }

    public static function authCode($string, $operation = "DECODE", $key = "", $expiry = 0)
    {
        $ckeyLength = 4;
        $authKey = Ibos::app()->setting->get("authkey");
        $key = md5($key != "" ? $key : $authKey);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = ($ckeyLength ? ($operation == "DECODE" ? substr($string, 0, $ckeyLength) : substr(md5(microtime()), -$ckeyLength)) : "");
        $cryptkey = $keya . md5($keya . $keyc);
        $keyLength = strlen($cryptkey);
        $string = ($operation == "DECODE" ? base64_decode(substr($string, $ckeyLength)) : sprintf("%010d", $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string);
        $stringLength = strlen($string);
        $result = "";
        $box = range(0, 255);
        $rndkey = array();

        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $keyLength]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $stringLength; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ $box[($box[$a] + $box[$j]) % 256]);
        }

        if ($operation == "DECODE") {
            if (((substr($result, 0, 10) == 0) || (0 < (substr($result, 0, 10) - time()))) && (substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16))) {
                return substr($result, 26);
            } else {
                return "";
            }
        } else {
            return $keyc . str_replace("=", "", base64_encode($result));
        }
    }

    public static function random($length, $numeric = 0)
    {
        $seed = base_convert(md5(microtime() . $_SERVER["DOCUMENT_ROOT"]), 16, $numeric ? 10 : 35);
        $seed = ($numeric ? str_replace("0", "", $seed) . "012340567890" : $seed . "zZ" . strtoupper($seed));
        $hash = "";
        $max = strlen($seed) - 1;

        for ($index = 0; $index < $length; $index++) {
            $hash .= $seed[mt_rand(0, $max)];
        }

        return $hash;
    }

    public static function ihtmlSpecialChars($string, $flags = null)
    {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = self::ihtmlSpecialChars($val, $flags);
            }
        } elseif ($flags === null) {
            $string = str_replace(array("&", "\"", "<", ">"), array("&amp;", "&quot;", "&lt;", "&gt;"), $string);

            if (strpos($string, "&amp;#") !== false) {
                $string = preg_replace("/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/", "&\1", $string);
            }
        } elseif (PHP_VERSION < "5.4.0") {
            $string = htmlspecialchars($string, $flags);
        } else {
            if (strtolower(CHARSET) == "utf-8") {
                $charset = "UTF-8";
            } else {
                $charset = "ISO-8859-1";
            }

            $string = htmlspecialchars($string, $flags, $charset);
        }

        return $string;
    }

    public static function cutStr($string, $length, $dot = " ...")
    {
        $strlen = self::iStrLen($string);

        if ($strlen <= $length) {
            return $string;
        }

        $pre = chr(1);
        $end = chr(1);
        $string = str_replace(array("&amp;", "&quot;", "&lt;", "&gt;"), array($pre . "&" . $end, $pre . "\"" . $end, $pre . "<" . $end, $pre . ">" . $end), $string);
        $strCut = "";

        if (strtolower(CHARSET) == "utf-8") {
            $n = $tn = $noc = 0;

            while ($n < $strlen) {
                $t = ord($string[$n]);
                if (($t == 9) || ($t == 10) || ((32 <= $t) && ($t <= 126))) {
                    $tn = 1;
                    $n++;
                    $noc++;
                } else {
                    if ((194 <= $t) && ($t <= 223)) {
                        $tn = 2;
                        $n += 2;
                        $noc += 2;
                    } else {
                        if ((224 <= $t) && ($t <= 239)) {
                            $tn = 3;
                            $n += 3;
                            $noc += 2;
                        } else {
                            if ((240 <= $t) && ($t <= 247)) {
                                $tn = 4;
                                $n += 4;
                                $noc += 2;
                            } else {
                                if ((248 <= $t) && ($t <= 251)) {
                                    $tn = 5;
                                    $n += 5;
                                    $noc += 2;
                                } else {
                                    if (($t == 252) || ($t == 253)) {
                                        $tn = 6;
                                        $n += 6;
                                        $noc += 2;
                                    } else {
                                        $n++;
                                    }
                                }
                            }
                        }
                    }
                }

                if ($length <= $noc) {
                    break;
                }
            }

            if ($length < $noc) {
                $n -= $tn;
            }

            $strCut = substr($string, 0, $n);
        } else {
            for ($i = 0; $i < $length; $i++) {
                $strCut .= (127 < ord($string[$i]) ? $string[$i] . $string[++$i] : $string[$i]);
            }
        }

        $strCut = str_replace(array($pre . "&" . $end, $pre . "\"" . $end, $pre . "<" . $end, $pre . ">" . $end), array("&amp;", "&quot;", "&lt;", "&gt;"), $strCut);
        $pos = strrpos($strCut, chr(1));

        if ($pos !== false) {
            $strCut = substr($strCut, 0, $pos);
        }

        return $strCut . $dot;
    }

    public static function iStrLen($str)
    {
        if (strtolower(CHARSET) != "utf-8") {
            return strlen($str);
        }

        $count = 0;

        for ($index = 0; $index < strlen($str); $index++) {
            $value = ord($str[$index]);

            if (127 < $value) {
                $count++;
                if ((192 <= $value) && ($value <= 223)) {
                    $index++;
                } else {
                    if ((224 <= $value) && ($value <= 239)) {
                        $index = $index + 2;
                    } else {
                        if ((240 <= $value) && ($value <= 247)) {
                            $index = $index + 3;
                        }
                    }
                }
            }

            $count++;
        }

        return $count;
    }

    public static function findIn($string, $id)
    {
        $string = trim($string, ",");
        $newId = trim($id, ",");
        if (($newId == "") || ($newId == ",")) {
            return false;
        }

        $idArr = explode(",", $newId);
        $strArr = explode(",", $string);

        if (array_intersect($strArr, $idArr)) {
            return true;
        }

        return false;
    }

    public static function isIp($ip)
    {
        if (!strcmp(long2ip(sprintf("%u", ip2long($ip))), $ip)) {
            return true;
        }

        return false;
    }

    public static function strExists($string, $find)
    {
        return !strpos($string, $find) === false;
    }

    public static function getSubIp($ip = "")
    {
        if (empty($ip)) {
            $ip = $clientIp = Ibos::app()->setting->get("clientip");
        }

        $reg = "/(\d+\.)(\d+\.)(\d+)\.(\d+)/";
        return preg_replace($reg, "\$1\$2*.*", $ip);
    }

    public static function displayIp($str)
    {
        if (self::isIp($str)) {
            return self::getSubIp($str);
        }

        return $str;
    }

    public static function iImplode($array)
    {
        if (!empty($array)) {
            $array = array_map("addslashes", $array);
            return "'" . implode("','", is_array($array) ? $array : array($array)) . "'";
        } else {
            return "";
        }
    }

    public static function splitParam($param)
    {
        $return = array();

        if (!empty($param)) {
            $params = explode("&", trim($param));

            foreach ($params as $data) {
                list($key, $value) = explode("=", $data);
                $return[$key] = $value;
            }
        }

        return $return;
    }

    public static function splitSql($sql)
    {
        $sql = str_replace("\r", "\n", $sql);
        $ret = array();
        $num = 0;
        $queriesArr = explode(";\n", trim($sql));
        unset($sql);

        foreach ($queriesArr as $querys) {
            $queries = explode("\n", trim($querys));

            foreach ($queries as $query) {
                $val = (substr(trim($query), 0, 1) == "#" ? null : $query);

                if (isset($ret[$num])) {
                    $ret[$num] .= $val;
                } else {
                    $ret[$num] = $val;
                }
            }

            $num++;
        }

        return $ret;
    }

    public static function passwordMask($password)
    {
        return !empty($password) ? $password[0] . "********" . substr($password, -2) : "";
    }

    public static function clearLogString($str)
    {
        if (!empty($str)) {
            if (!is_array($str)) {
                $str = self::ihtmlSpecialChars(trim($str));
                $str = str_replace(array("\t", "\r\n", "\n", "   ", "  "), " ", $str);
            } else {
                foreach ($str as $key => $val) {
                    $str[$key] = self::clearLogString($val);
                }
            }
        }

        return $str;
    }

    public static function getTree($data, $format = "<option value='\$catid' \$selected>\$spacer\$name</option>", $id = 0, $nbsp = "&nbsp;&nbsp;&nbsp;&nbsp;", $icon = array("&nbsp;&nbsp;", "&nbsp;&nbsp;", "&nbsp;&nbsp;"))
    {
        Ibos::import("ext.Tree", true);
        $tree = new tree();
        $tree->init($data);
        $tree->icon = $icon;
        $tree->nbsp = $nbsp;
        $trees = $tree->get_tree(0, $format, $id);
        return $trees;
    }

    public static function getId($ids, $index = false)
    {
        $newIds = array();
        $idList = (is_array($ids) ? $ids : explode(",", $ids));

        foreach ($idList as $idstr) {
            if (!empty($idstr)) {
                if ($index) {
                    $prefix = substr($idstr, 0, 1);
                    $newIds[$prefix][] = substr($idstr, 2);
                } else {
                    $newIds[] = substr($idstr, 2);
                }
            }
        }

        return $newIds;
    }

    public static function getUid($ids)
    {
        $uids = array();
        $idList = (is_array($ids) ? $ids : array($ids));

        foreach ($idList as $idstr) {
            if (!empty($idstr)) {
                $identifier = $idstr[0];
                $uid = self::getUidByIdentifier($identifier, $idstr);
                $uids = array_merge($uids, $uid);
            }
        }

        return array_unique($uids);
    }

    public static function wrapId($ids, $identifier = "u", $glue = ",")
    {
        if (empty($ids)) {
            return "";
        }

        $id = (is_array($ids) ? $ids : explode(",", $ids));
        $wrapId = array();

        foreach ($id as $tempId) {
            if (!empty($tempId)) {
                $wrapId[] = $identifier . "_" . $tempId;
            }
        }

        return implode($glue, $wrapId);
    }

    public static function getUidByIdentifier($identifier, $str)
    {
        $id = substr($str, 2);

        if (strcmp($identifier, "u") == 0) {
            return array($id);
        } elseif (strcmp($identifier, "d") == 0) {
            $main = User::model()->fetchAllUidByDeptid($id);
            $auxiliary = DepartmentRelated::model()->fetchAllUidByDeptId($id);
            return array_merge($main, $auxiliary);
        } elseif (strcmp($identifier, "p") == 0) {
            return User::model()->fetchUidByPosId($id);
        }
    }

    public static function iIntval($int, $allowArray = false)
    {
        $ret = intval($int);
        if (($int == $ret) || (!$allowArray && is_array($int))) {
            return $ret;
        }

        if ($allowArray && is_array($int)) {
            foreach ($int as &$v) {
                $v = self::iIntval($v, true);
            }

            return $int;
        } elseif ($int <= 4294967295) {
            $l = strlen($int);
            $m = (substr($int, 0, 1) == "-" ? 1 : 0);

            if (($l - $m) === strspn($int, "0987654321", $m)) {
                return $int;
            }
        }

        return $ret;
    }

    public static function getFileExt($fileName)
    {
        return addslashes(strtolower(substr(strrchr($fileName, "."), 1, 10))) . "";
    }

    public static function pregHtml($html)
    {
        $p = array("/<[a|A][^>]+(topic=\"true\")+[^>]*+>#([^<]+)#<\/[a|A]>/", "/<[a|A][^>]+(data=\")+([^\"]+)\"[^>]*+>[^<]*+<\/[a|A]>/", "/<[img|IMG][^>]+(src=\")+([^\"]+)\"[^>]*+>/");
        $t = array("topic{data=\$2}", "\$2", "img{data=\$2}");
        $html = preg_replace($p, $t, $html);
        $html = strip_tags($html, "<br/>");
        return $html;
    }

    public static function getStrLength($str, $filter = false)
    {
        if ($filter) {
            $str = html_entity_decode($str, ENT_QUOTES);
            $str = strip_tags($str);
        }

        return (strlen($str) + mb_strlen($str, "UTF8")) / 4;
    }

    public static function filterCleanHtml($text)
    {
        $text = nl2br($text);
        $text = self::realStripTags($text);
        $text = addslashes($text);
        $text = trim($text);
        return $text;
    }

    public static function realStripTags($str, $allowableTags = "")
    {
        $str = stripslashes(htmlspecialchars_decode($str));
        return strip_tags($str, $allowableTags);
    }

    public static function filterDangerTag($text, $type = "html")
    {
        $textTags = "";
        $linkTags = "<a>";
        $imageTags = "<img>";
        $fontTags = "<i><b><u><s><em><strong><font><big><small><sup><sub><bdo><h1><h2><h3><h4><h5><h6>";
        $baseTags = $fontTags . "<p><br><hr><a><img><map><area><pre><code><q><blockquote><acronym><cite><ins><del><center><strike>";
        $formTags = $baseTags . "<form><input><textarea><button><select><optgroup><option><label><fieldset><legend>";
        $htmlTags = $baseTags . "<meta><ul><ol><li><dl><dd><dt><table><caption><td><th><tr><thead><tbody><tfoot><col><colgroup><div><span><object><embed><param>";
        $allTags = $formTags . $htmlTags . "<!DOCTYPE><html><head><title><body><base><basefont><script><noscript><applet><object><param><style><frame><frameset><noframes><iframe>";
        $text = self::realStripTags($text, $type . "Tags");

        if ($type != "all") {
            while (preg_match("/(<[^><]+)(ondblclick|onclick|onload|onerror|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|action|background|codebase|dynsrc|lowsrc)([^><]*)/i", $text, $mat)) {
                $text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
            }

            while (preg_match("/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i", $text, $mat)) {
                $text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
            }
        }

        return $text;
    }

    public static function filterStr($string, $delimiter = ",", $unique = true)
    {
        $filterArr = array();
        $strArr = explode($delimiter, $string);

        foreach ($strArr as $str) {
            if (!empty($str)) {
                $filterArr[] = trim($str);
            }
        }

        return implode($delimiter, $unique ? array_unique($filterArr) : $filterArr);
    }

    public static function unicodeToUtf8($str)
    {
        if (!$str) {
            return $str;
        }

        $decode = json_decode($str);

        if ($decode) {
            return $decode;
        }

        $str = "[\"" . $str . "\"]";
        $decode = json_decode($str);

        if (count($decode) == 1) {
            return $decode[0];
        }

        return $str;
    }

    public static function parseForApi($html)
    {
        $html = self::filterDangerTag($html);
        $html = str_replace(array("[SITE_URL]", "&nbsp;"), array(Ibos::app()->setting->get("siteurl"), " "), $html);
        $html = preg_replace_callback("/@([\w\\x{2e80}-\\x{9fff}\-]+)/u", "self::parseWapAtByUname", $html);
        return $html;
    }

    public static function parseWapAtByUname($name)
    {
        $info = static_cache("user_info_uname_" . $name[1]);

        if (!$info) {
            $info = model("User")->getUserInfoByName($name[1]);

            if (!$info) {
                $info = 1;
            }

            static_cache("user_info_uname_" . $name[1], $info);
        }

        if ($info && $info["is_active"] && $info["is_audit"] && $info["is_init"]) {
            return "<a href=\"" . u("wap/Index/weibo", array("uid" => $info["uid"])) . "\" >" . $name[0] . "</a>";
        } else {
            return $name[0];
        }
    }

    public static function parseHtml($html)
    {
        $html = htmlspecialchars_decode($html);
        $html = str_replace("[SITE_URL]", Ibos::app()->setting->get("siteurl"), $html);
        $html = preg_replace_callback("/((?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*\.)?[a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z0-9]+)+(?:\:[0-9]*)?(?:\/[^\\x{2e80}-\\x{9fff}\s<'\\\"“”‘’,，。]*)?)/u", "self::parseUrl", $html);
        $html = preg_replace_callback("/(\[.+?\])/is", "self::parseExpression", $html);
        $html = str_replace("＃", "#", $html);
        $html = preg_replace_callback("/#([^#]*[^#^\s][^#]*)#/is", "self::parseTheme", $html);
        $html = preg_replace_callback("/@([\w\\x{2e80}-\\x{9fff}\-]+)/u", "self::parseAtByUserName", $html);
        return $html;
    }

    private static function parseExpression($data)
    {
        if (preg_match("/#.+#/i", $data[0])) {
            return $data[0];
        }

        $allExpression = ExpressionUtil::getAllExpression();
        $info = (isset($allExpression[$data[0]]) ? $allExpression[$data[0]] : false);

        if ($info) {
            return preg_replace("/\[.+?\]/i", "<img class='exp-img' src='" . STATICURL . "/image/expression/" . $info["icon"] . "' />", $data[0]);
        } else {
            return $data[0];
        }
    }

    private static function parseAtByUserName($name)
    {
        $info = CacheUtil::get("userInfoRealName_" . md5($name[1]));

        if (!$info) {
            $info = User::model()->fetchByRealname($name[1]);
            CacheUtil::set("userInfoRealName_" . md5($name[1]), $info);
        }

        if ($info) {
            return "<a class=\"anchor\" data-toggle=\"usercard\" data-param=\"uid=" . $info["uid"] . "\" href=\"" . $info["space_url"] . "\" target=\"_blank\">" . $name[0] . "</a>";
        } else {
            return $name[0];
        }
    }

    private static function parseTheme($data)
    {
        $lock = Ibos::app()->db->createCommand()->select("lock")->from("{{feed_topic}}")->where(sprintf("topicname = '%s'", $data[1]))->queryScalar();

        if (!$lock) {
            return "<a class='wb-source' href=" . Ibos::app()->urlManager->createUrl("weibo/topic/detail", array("k" => urlencode($data[1]))) . ">" . $data[0] . "</a>";
        } else {
            return $data[0];
        }
    }

    public static function parseUrl($url)
    {
        $str = "<div class=\"url\">";

        if (preg_match("/(youku.com|youtube.com|ku6.com|sohu.com|mofile.com|sina.com.cn|tudou.com|yinyuetai.com)/i", $url[0], $hosts)) {
            $str .= "<a href=\"" . $url[0] . "\" target=\"_blank\" data-node-type=\"wbUrl\" class=\"o-url-video\">视频</a>";
        } elseif (strpos($url[0], "taobao.com")) {
            $str .= "<a href=\"" . $url[0] . "\" target=\"_blank\" data-node-type=\"wbUrl\" class=\"o-url-taobao\">淘宝</a>";
        } else {
            $str .= "<a href=\"" . $url[0] . "\" target=\"_blank\" data-node-type=\"wbUrl\" class=\"o-url-web\">网页</a>";
        }

        $str .= "</div>";
        return $str;
    }

    public static function formatFeedContentUrlLength($match)
    {
        static $i = 97;
        $result = "{iurl==" . chr($i) . "}";
        $i++;
        $GLOBALS["replaceHash"][$result] = $match[0];
        return $result;
    }

    public static function replaceUrl($content)
    {
        $content = str_replace("[SITE_URL]", Ibos::app()->setting->get("siteurl"), $content);
        $content = preg_replace_callback("/((?:https?|mailto|ftp):\/\/([^\\x{2e80}-\\x{9fff}\s<'\\\"“”‘’，。}]*)?)/u", "self::parseUrl", $content);
        return $content;
    }

    public static function createGuid()
    {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45);
        $uuid = substr($charid, 0, 8) . $hyphen . substr($charid, 8, 4) . $hyphen . substr($charid, 12, 4) . $hyphen . substr($charid, 16, 4) . $hyphen . substr($charid, 20, 12);
        return $uuid;
    }
}
