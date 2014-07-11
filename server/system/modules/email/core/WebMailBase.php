<?php

class WebMailBase
{
    private $_error = array();
    private $_log = array();

    public function clearError()
    {
        $this->_error = array();
    }

    public function getError()
    {
        return $this->_error;
    }

    public function setError($errorMsg = "")
    {
        $this->_error[] = $errorMsg;
    }

    public function log($log = "")
    {
        $this->_log[] = $log;
    }

    public function _XOR($string, $string2)
    {
        $result = "";
        $size = strlen($string);

        for ($i = 0; $i < $size; $i++) {
            $result .= chr(ord($string[$i]) ^ ord($string2[$i]));
        }

        return $result;
    }

    public function readLine($fp, $size = 1024)
    {
        $line = "";
        if (is_resource($fp) && !feof($fp)) {
            do {
                $buffer = fgets($fp, $size);
                $endID = strlen($buffer) - 1;
                $end = ($buffer[$endID] == "\n") || feof($fp);
                $line .= $buffer;
            } while (!$end);
        }

        return $line;
    }

    public function splitHeaderLine($string)
    {
        $pos = strpos($string, ":");

        if (0 < $pos) {
            $res[0] = substr($string, 0, $pos);
            $res[1] = substr($string, $pos + 2);
            return $res;
        } else {
            return array(null, null);
        }
    }

    public function explodeQuotedString($delimiter, $string)
    {
        $quotes = explode("\"", $string);

        while (list($key, $val) = each($quotes)) {
            if (($key % 2) == 1) {
                $quotes[$key] = str_replace($delimiter, "_!@!_", $quotes[$key]);
            }
        }

        $string = implode("\"", $quotes);
        $result = explode($delimiter, $string);

        while (list($key, $val) = each($result)) {
            $result[$key] = str_replace("_!@!_", $delimiter, $result[$key]);
        }

        return $result;
    }

    public function strToTime($str)
    {
        $str = trim($str);
        $str = str_replace("  ", " ", $str);
        $pos = strpos($str, " ");
        $word = substr($str, 0, $pos);

        if (!is_numeric($word)) {
            $str = substr($str, $pos + 1);
        }

        $a = explode(" ", $str);
        $month_a = array("Jan" => 1, "Feb" => 2, "Mar" => 3, "Apr" => 4, "May" => 5, "Jun" => 6, "Jul" => 7, "Aug" => 8, "Sep" => 9, "Oct" => 10, "Nov" => 11, "Dec" => 12);
        $month_str = $a[1];
        $month = $month_a[$month_str];
        $day = $a[0];
        $year = $a[2];
        $time = $a[3];
        $tz_str = $a[4];
        $tz = substr($tz_str, 0, 3);
        $ta = explode(":", $time);
        $hour = (int) $ta[0] - (int) $tz;
        $minute = $ta[1];
        $second = $ta[2];
        return mktime($hour, $minute, $second, $month, $day, $year);
    }

    public function sortHeaders($a, $field, $flag)
    {
        if (empty($field)) {
            $field = "uid";
        }

        $field = strtolower($field);

        if ($field == "date") {
            $field = "timestamp";
        }

        if (empty($flag)) {
            $flag = "ASC";
        }

        $flag = strtoupper($flag);
        $c = count($a);

        if (0 < $c) {
            $index = array();
            reset($a);

            while (list($key, $val) = each($a)) {
                $data = $a[$key]->$field;

                if (is_string($data)) {
                    $data = strtoupper(str_replace("\"", "", $data));
                }

                $index[$key] = $data;
            }

            $i = 0;

            if ($flag == "ASC") {
                asort($index);
            } else {
                arsort($index);
            }

            $result = array();
            reset($index);

            while (list($key, $val) = each($index)) {
                $result[$i] = $a[$key];
                $i++;
            }
        }

        return $result;
    }

    public function readReply($fp)
    {
        do {
            $line = chop(trim($this->readLine($fp, 1024)));
        } while ($line[0] == "*");

        return $line;
    }
}

class ICWebMailConnection
{
    public $fp;
    public $login;
    public $password;
    public $host;
    public $error;
    public $errorNum;
    public $selected;
    public $cacheFP;
    public $cacheMode;
}

class ICWebMailBasicHeader
{
    public $id;
    public $uid;
    public $subject;
    public $from;
    public $to;
    public $cc;
    public $replyto;
    public $date;
    public $messageID;
    public $size;
    public $encoding;
    public $ctype;
    public $flags;
    public $timestamp;
    public $deleted;
    public $recent;
    public $answered;
}
