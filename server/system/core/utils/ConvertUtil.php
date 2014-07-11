<?php

class ConvertUtil
{
    public static function ConvertBytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);

        switch ($last) {
            case "g":
                $val *= 1024;
            case "m":
                $val *= 1024;
            case "k":
                $val *= 1024;
        }

        return $val;
    }

    public static function sizeCount($size)
    {
        if (1073741824 <= $size) {
            $size = (round(($size / 1073741824) * 100) / 100) . " GB";
        } elseif (1048576 <= $size) {
            $size = (round(($size / 1048576) * 100) / 100) . " MB";
        } elseif (1024 <= $size) {
            $size = (round(($size / 1024) * 100) / 100) . " KB";
        } else {
            $size = $size . " Bytes";
        }

        return $size;
    }

    public static function formatDate($timestamp, $format = "dt", $timeOffset = "9999", $uformat = "")
    {
        $setting = Yii::app()->setting->get("setting");
        $dateConvert = $setting["dateconvert"];
        if (($format == "u") && !$dateConvert) {
            $format = "dt";
        }

        $dateFormat = $setting["dateformat"];
        $timeFormat = $setting["timeformat"];
        $dayTimeFormat = $dateFormat . " " . $timeFormat;
        $offset = $setting["timeoffset"];
        $timeOffset = ($timeOffset == "9999" ? $offset : $timeOffset);
        $timestamp += $timeOffset * 3600;
        if (empty($format) || ($format == "dt")) {
            $format = $dayTimeFormat;
        } elseif ($format == "d") {
            $format = $dateFormat;
        } elseif ($format == "t") {
            $format = $timeFormat;
        }

        if ($format == "u") {
            $todayTimestamp = (TIMESTAMP - ((TIMESTAMP + ($timeOffset * 3600)) % 86400)) + ($timeOffset * 3600);
            $outputStr = gmdate(!$uformat ? $dayTimeFormat : $uformat, $timestamp);
            $time = (TIMESTAMP + ($timeOffset * 3600)) - $timestamp;

            if ($todayTimestamp <= $timestamp) {
                $replace = array("{outputStr}" => $outputStr);

                if (3600 < $time) {
                    $replace["{outputTime}"] = intval($time / 3600);
                    $returnTimeStr = Ibos::lang("Time greaterthan 3600", "date", $replace);
                } elseif (1800 < $time) {
                    $returnTimeStr = Ibos::lang("Time greaterthan 1800", "date", $replace);
                } elseif (60 < $time) {
                    $replace["{outputTime}"] = intval($time / 60);
                    $returnTimeStr = Ibos::lang("Time greaterthan 60", "date", $replace);
                } elseif (0 < $time) {
                    $replace["{outputTime}"] = $time;
                    $returnTimeStr = Ibos::lang("Time greaterthan 0", "date", $replace);
                } elseif ($time == 0) {
                    $returnTimeStr = Ibos::lang("Time equal 0", "date", $replace);
                } else {
                    return $outputStr;
                }

                return $returnTimeStr;
            } else {
                if ((0 <= $days = intval(($todayTimestamp - $timestamp) / 86400)) && ($days < 7)) {
                    $replace = array("{outputStr}" => $outputStr, "{outputDay}" => gmdate($timeFormat, $timestamp));

                    if ($days == 0) {
                        $returnTimeStr = Ibos::lang("Day equal 0", "date", $replace);
                    } elseif ($days == 1) {
                        $returnTimeStr = Ibos::lang("Day equal 1", "date", $replace);
                    } else {
                        $replace["{outputDay}"] = $days + 1;
                        $returnTimeStr = Ibos::lang("Day equal else", "date", $replace);
                    }

                    return $returnTimeStr;
                } else {
                    return $outputStr;
                }
            }
        } else {
            $returnTimeStr = gmdate($format, $timestamp);
            return $returnTimeStr;
        }
    }

    public static function RGBToHex($rgb)
    {
        $regexp = "/^rgb\(([0-9]{0,3})\,\s*([0-9]{0,3})\,\s*([0-9]{0,3})\)/";
        $re = preg_match($regexp, $rgb, $match);
        $re = array_shift($match);
        $hexColor = "#";
        $hex = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F");

        for ($i = 0; $i < 3; $i++) {
            $r = null;
            $c = $match[$i];
            $hexAr = array();

            while (16 < $c) {
                $r = $c % 16;
                $c = ($c / 16) >> 0;
                array_push($hexAr, $hex[$r]);
            }

            array_push($hexAr, $hex[$c]);
            $ret = array_reverse($hexAr);
            $item = implode("", $ret);
            $item = str_pad($item, 2, "0", STR_PAD_LEFT);
            $hexColor .= $item;
        }

        return $hexColor;
    }

    public static function hexColorToRGB($hexColor)
    {
        $color = str_replace("#", "", $hexColor);

        if (3 < strlen($color)) {
            $rgb = array("r" => hexdec(substr($color, 0, 2)), "g" => hexdec(substr($color, 2, 2)), "b" => hexdec(substr($color, 4, 2)));
        } else {
            $color = str_replace("#", "", $hexColor);
            $r = substr($color, 0, 1) . substr($color, 0, 1);
            $g = substr($color, 1, 1) . substr($color, 1, 1);
            $b = substr($color, 2, 1) . substr($color, 2, 1);
            $rgb = array("r" => hexdec($r), "g" => hexdec($g), "b" => hexdec($b));
        }

        return $rgb;
    }

    public static function convertIp($ip)
    {
        $return = "";

        if (preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) {
            $ipArr = explode(".", $ip);
            if (($ipArr[0] == 10) || ($ipArr[0] == 127) || (($ipArr[0] == 192) && ($ipArr[1] == 168)) || (($ipArr[0] == 172) && (16 <= $ipArr[1]) && ($ipArr[1] <= 31))) {
                $return = "- LAN";
            } else {
                if ((255 < $ipArr[0]) || (255 < $ipArr[1]) || (255 < $ipArr[2]) || (255 < $ipArr[3])) {
                    $return = "- Invalid IP Address";
                } else {
                    $tinyIpFile = "data/ipdata/tiny.dat";
                    $fullIpFile = "data/ipdata/full.dat";

                    if (@file_exists($tinyIpFile)) {
                        $return = self::convertTinyIp($ip, $tinyIpFile);
                    } elseif (@file_exists($fullIpFile)) {
                        $return = self::convertFullIp($ip, $fullIpFile);
                    }
                }
            }
        }

        return $return;
    }

    public static function convertTinyIp($ip, $ipDataFile)
    {
        static $fp;
        static $offset = array();
        static $index;
        $ipdot = explode(".", $ip);
        $ip = pack("N", ip2long($ip));
        $ipdot[0] = (int) $ipdot[0];
        $ipdot[1] = (int) $ipdot[1];
        if (($fp === null) && ($fp = @fopen($ipDataFile, "rb"))) {
            $offset = @unpack("Nlen", @fread($fp, 4));
            $index = @fread($fp, $offset["len"] - 4);
        } elseif ($fp == false) {
            return "- Invalid IP data file";
        }

        $length = $offset["len"] - 1028;
        $start = @unpack("Vlen", $index[$ipdot[0] * 4] . $index[($ipdot[0] * 4) + 1] . $index[($ipdot[0] * 4) + 2] . $index[($ipdot[0] * 4) + 3]);

        for ($start = ($start["len"] * 8) + 1024; $start < $length; $start += 8) {
            if ($ip <= $index[$start] . $index[$start + 1] . $index[$start + 2] . $index[$start + 3]) {
                $indexOffset = @unpack("Vlen", $index[$start + 4] . $index[$start + 5] . $index[$start + 6] . "\000");
                $indexLength = @unpack("Clen", $index[$start + 7]);
                break;
            }
        }

        @fseek($fp, ($offset["len"] + $indexOffset["len"]) - 1024);

        if ($indexLength["len"]) {
            return "- " . @fread($fp, $indexLength["len"]);
        } else {
            return "- Unknown";
        }
    }

    public static function convertFullIp($ip, $ipDataFile)
    {
        if (!$fd = @fopen($ipDataFile, "rb")) {
            return "- Invalid IP data file";
        }

        $ip = explode(".", $ip);
        $ipNum = ($ip[0] * 16777216) + ($ip[1] * 65536) + ($ip[2] * 256) + $ip[3];
        if (!$DataBegin = fread($fd, 4) || !$DataEnd = fread($fd, 4)) {
            return null;
        }

        @$ipbegin = implode("", unpack("L", $DataBegin));

        if ($ipbegin < 0) {
            $ipbegin += pow(2, 32);
        }

        @$ipend = implode("", unpack("L", $DataEnd));

        if ($ipend < 0) {
            $ipend += pow(2, 32);
        }

        $ipAllNum = (($ipend - $ipbegin) / 7) + 1;
        $BeginNum = $ip2num = $ip1num = 0;
        $ipAddr1 = $ipAddr2 = "";
        $EndNum = $ipAllNum;
        while (($ipNum < $ip1num) || ($ip2num < $ipNum)) {
            $Middle = intval(($EndNum + $BeginNum) / 2);
            fseek($fd, $ipbegin + (7 * $Middle));
            $ipData1 = fread($fd, 4);

            if (strlen($ipData1) < 4) {
                fclose($fd);
                return "- System Error";
            }

            $ip1num = implode("", unpack("L", $ipData1));

            if ($ip1num < 0) {
                $ip1num += pow(2, 32);
            }

            if ($ipNum < $ip1num) {
                $EndNum = $Middle;
                continue;
            }

            $DataSeek = fread($fd, 3);

            if (strlen($DataSeek) < 3) {
                fclose($fd);
                return "- System Error";
            }

            $DataSeek = implode("", unpack("L", $DataSeek . chr(0)));
            fseek($fd, $DataSeek);
            $ipData2 = fread($fd, 4);

            if (strlen($ipData2) < 4) {
                fclose($fd);
                return "- System Error";
            }

            $ip2num = implode("", unpack("L", $ipData2));

            if ($ip2num < 0) {
                $ip2num += pow(2, 32);
            }

            if ($ip2num < $ipNum) {
                if ($Middle == $BeginNum) {
                    fclose($fd);
                    return "- Unknown";
                }

                $BeginNum = $Middle;
            }
        }

        $ipFlag = fread($fd, 1);

        if ($ipFlag == chr(1)) {
            $ipSeek = fread($fd, 3);

            if (strlen($ipSeek) < 3) {
                fclose($fd);
                return "- System Error";
            }

            $ipSeek = implode("", unpack("L", $ipSeek . chr(0)));
            fseek($fd, $ipSeek);
            $ipFlag = fread($fd, 1);
        }

        if ($ipFlag == chr(2)) {
            $AddrSeek = fread($fd, 3);

            if (strlen($AddrSeek) < 3) {
                fclose($fd);
                return "- System Error";
            }

            $ipFlag = fread($fd, 1);

            if ($ipFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);

                if (strlen($AddrSeek2) < 3) {
                    fclose($fd);
                    return "- System Error";
                }

                $AddrSeek2 = implode("", unpack("L", $AddrSeek2 . chr(0)));
                fseek($fd, $AddrSeek2);
            } else {
                fseek($fd, -1, SEEK_CUR);
            }

            while (($char = fread($fd, 1)) != chr(0)) {
                $ipAddr2 .= $char;
            }

            $AddrSeek = implode("", unpack("L", $AddrSeek . chr(0)));
            fseek($fd, $AddrSeek);

            while (($char = fread($fd, 1)) != chr(0)) {
                $ipAddr1 .= $char;
            }
        } else {
            fseek($fd, -1, SEEK_CUR);

            while (($char = fread($fd, 1)) != chr(0)) {
                $ipAddr1 .= $char;
            }

            $ipFlag = fread($fd, 1);

            if ($ipFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);

                if (strlen($AddrSeek2) < 3) {
                    fclose($fd);
                    return "- System Error";
                }

                $AddrSeek2 = implode("", unpack("L", $AddrSeek2 . chr(0)));
                fseek($fd, $AddrSeek2);
            } else {
                fseek($fd, -1, SEEK_CUR);
            }

            while (($char = fread($fd, 1)) != chr(0)) {
                $ipAddr2 .= $char;
            }
        }

        fclose($fd);

        if (preg_match("/http/i", $ipAddr2)) {
            $ipAddr2 = "";
        }

        $ipaddr = "$ipAddr1 $ipAddr2";
        $ipaddr = preg_replace("/CZ88\.NET/is", "", $ipaddr);
        $ipaddr = preg_replace("/^\s*/is", "", $ipaddr);
        $ipaddr = preg_replace("/\s*$/is", "", $ipaddr);
        if (preg_match("/http/i", $ipaddr) || ($ipaddr == "")) {
            $ipaddr = "- Unknown";
        }

        return "- " . $ipaddr;
    }

    public static function implodeArray($array, $skip = array())
    {
        $return = "";
        if (is_array($array) && !empty($array)) {
            foreach ($array as $key => $value) {
                if (empty($skip) || !in_array($key, $skip, true)) {
                    if (is_array($value)) {
                        $return .= "$key={" . self::implodeArray($value, $skip) . "}; ";
                    } elseif (!empty($value)) {
                        $return .= "$key=$value; ";
                    } else {
                        $return .= "";
                    }
                }
            }
        }

        return $return;
    }

    public static function getSubByKey($pArray, $pKey = "", $pCondition = "")
    {
        $result = array();

        if (is_array($pArray)) {
            foreach ($pArray as $tempArray) {
                if (is_object($tempArray)) {
                    $tempArray = (array) $tempArray;
                }

                if ((("" != $pCondition) && ($tempArray[$pCondition[0]] == $pCondition[1])) || ("" == $pCondition)) {
                    $result[] = ("" == $pKey ? $tempArray : isset($tempArray[$pKey]) ? $tempArray[$pKey] : "");
                }
            }

            return $result;
        } else {
            return false;
        }
    }

    public static function ToChinaseNum($num)
    {
        $char = array("零", "一", "二", "三", "四", "五", "六", "七", "八", "九");
        $dw = array("", "十", "百", "千", "万", "亿", "兆");
        $retval = "";
        $proZero = false;

        for ($i = 0; $i < strlen($num); $i++) {
            if (0 < $i) {
                $temp = (int) ($num % pow(10, $i + 1)) / pow(10, $i);
            } else {
                $temp = (int) $num % pow(10, 1);
            }

            if (($proZero == true) && ($temp == 0)) {
                continue;
            }

            if ($temp == 0) {
                $proZero = true;
            } else {
                $proZero = false;
            }

            if ($proZero) {
                if ($retval == "") {
                    continue;
                }

                $retval = $char[$temp] . $retval;
            } else {
                $retval = $char[$temp] . $dw[$i] . $retval;
            }
        }

        if ($retval == "一十") {
            $retval = "十";
        }

        return $retval;
    }

    public static function iIconv($str, $inCharset, $outCharset = CHARSET, $forceTable = false)
    {
        $inCharset = strtoupper($inCharset);
        $outCharset = strtoupper($outCharset);
        if (empty($str) || ($inCharset == $outCharset)) {
            return $str;
        }

        $out = "";

        if (!$forceTable) {
            if (function_exists("iconv")) {
                $out = iconv($inCharset, $outCharset . "//IGNORE", $str);
            } elseif (function_exists("mb_convert_encoding")) {
                $out = mb_convert_encoding($str, $outCharset, $inCharset);
            }
        }

        if ($out == "") {
            Ibos::import("ext.chinese.Chinese", true);
            $chinese = new Chinese($inCharset, $outCharset, true);
            $out = $chinese->Convert($str);
        }

        return $out;
    }

    public static function getPY($string, $first = false, $phonetic = false)
    {
        $pdat = "data/pydata/py.dat";
        $fp = @fopen($pdat, "rb");

        if (!$fp) {
            return "*";
        }

        $in_code = strtoupper(CHARSET);
        $out_code = "GBK";
        $strlen = mb_strlen($string, $in_code);
        $ret = "";

        for ($i = 0; $i < $strlen; $i++) {
            $py = "";
            $izh = mb_substr($string, $i, 1, $in_code);

            if (preg_match("/^[a-zA-Z0-9]$/", $izh)) {
                $ret .= $izh;
            } elseif (preg_match("/^[\\x{4e00}-\\x{9fa5}]+$/u", $izh)) {
                $char = iconv($in_code, $out_code, $izh);
                $high = ord($char[0]) - 129;
                $low = ord($char[1]) - 64;
                $offset = (($high << 8) + $low) - ($high * 64);

                if (0 <= $offset) {
                    fseek($fp, $offset * 8, SEEK_SET);
                    $p_arr = unpack("a8py", fread($fp, 8));
                    $py = (isset($p_arr["py"]) ? ($phonetic ? $p_arr["py"] : substr($p_arr["py"], 0, -1)) : "");
                    $ret .= ($first ? $py[0] : "" . $py);
                }
            }
        }

        fclose($fp);
        return $ret;
    }

    public static function unescape($str)
    {
        $ret = "";
        $len = strlen($str);

        for ($i = 0; $i < $len; $i++) {
            if (($str[$i] == "%") && ($str[$i + 1] == "u")) {
                $val = hexdec(substr($str, $i + 2, 4));

                if ($val < 127) {
                    $ret .= chr($val);
                } elseif ($val < 2048) {
                    $ret .= chr(192 | ($val >> 6)) . chr(128 | ($val & 63));
                } else {
                    $ret .= chr(224 | ($val >> 12)) . chr(128 | (($val >> 6) & 63)) . chr(128 | ($val & 63));
                }

                $i += 5;
            } elseif ($str[$i] == "%") {
                $ret .= urldecode(substr($str, $i, 3));
                $i += 2;
            } else {
                $ret .= $str[$i];
            }
        }

        return $ret;
    }
}
