<?php

class MailUtil
{
    public static function sendCloudMail($to, $subject, $message, $from = "postmaster@ibos.sendcloud.org")
    {
        $param = array("to" => $to, "subject" => $subject, "html" => $message, "from" => $from);
        $rs = CloudApi::getInstance()->fetch("Api/Mail/Send", $param, "post");

        if (substr($rs, 0, 5) !== "error") {
            $res = json_decode($rs, true);

            if ($res["ret"] == 0) {
                return true;
            }
        }

        return false;
    }

    public static function sendMail($to, $subject, $message, $from = "IBOS2.0 MAIL CONTROL")
    {
        $setting = Ibos::app()->setting->toArray();
        $mail = $setting["setting"]["mail"];

        if (!is_array($mail)) {
            $mail = unserialize($mail);
        }

        $smtpNums = count($mail["server"]);

        if ($smtpNums) {
            $randId = array_rand($mail["server"], 1);
            $server = $mail["server"][$randId];
            $delimiter = ($mail["maildelimiter"] == 1 ? "\r\n" : ($mail["maildelimiter"] == 2 ? "\r" : "\n"));
            $unit = $setting["setting"]["unit"];

            if ($mail["mailsend"] == 2) {
                $emailFrom = (empty($from) ? $unit["adminemail"] : $from);
            } else {
                $emailFrom = ($from == "" ? "=?" . CHARSET . "?B?" . base64_encode($unit["fullname"]) . "?= <" . $unit["adminemail"] . ">" : (preg_match("/^(.+?) \<(.+?)\>$/", $from, $mats) ? "=?" . CHARSET . "?B?" . base64_encode($mats[1]) . "?= <$mats[2]>" : $from));
            }

            $emailTo = (preg_match("/^(.+?) \<(.+?)\>$/", $to, $mats) ? ($mail["mailusername"] ? "=?" . CHARSET . "?B?" . base64_encode($mats[1]) . "?= <$mats[2]>" : $mats[2]) : $to);
            $emailSubject = "=?" . CHARSET . "?B?" . base64_encode(preg_replace("/[\r|\n]/", "", "[" . $unit["fullname"] . "] " . $subject)) . "?=";
            $emailMessage = chunk_split(base64_encode(str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $message))))));
            $host = $_SERVER["HTTP_HOST"];
            $version = "IBOS " . $setting["version"];
            $headers = "From: $emailFrom{$delimiter}X-Priority: 3{$delimiter}X-Mailer: $host $version {$delimiter}MIME-Version: 1.0{$delimiter}Content-type: text/html; charset=" . CHARSET . "{$delimiter}Content-Transfer-Encoding: base64$delimiter";

            if ($mail["mailsend"] == 1) {
                if (!$fp = EnvUtil::getSocketOpen($server["server"], $errno, $errstr, $server["port"], 30)) {
                    Log::write(array("msg" => "({$server["server"]}:{$server["port"]}) CONNECT - Unable to connect to the SMTP server", "type" => "SMTP"), "action", "sendMail");
                    return false;
                }

                stream_set_blocking($fp, true);
                $lastMessage = fgets($fp, 512);

                if (substr($lastMessage, 0, 3) != "220") {
                    Log::write(array("msg" => "({$server["server"]}:{$server["port"]}) CONNECT - $lastMessage", "type" => "SMTP"), "action", "sendMail");
                    return false;
                }

                fputs($fp, ($server["auth"] ? "EHLO" : "HELO") . " ibos\r\n");
                $lastMessage = fgets($fp, 512);
                if ((substr($lastMessage, 0, 3) != 220) && (substr($lastMessage, 0, 3) != 250)) {
                    Log::write(array("msg" => "({$server["server"]}:{$server["port"]}) HELO/EHLO - $lastMessage", "type" => "SMTP"), "action", "sendMail");
                    return false;
                } elseif (1) {
                    if ((substr($lastMessage, 3, 1) != "-") || empty($lastMessage)) {
                        break;
                    }

                    $lastMessage = fgets($fp, 512);
                }

                break;
                $lastMessage = fgets($fp, 512);

                if ($server["auth"]) {
                    fputs($fp, "AUTH LOGIN\r\n");
                    $lastMessage = fgets($fp, 512);

                    if (substr($lastMessage, 0, 3) != 334) {
                        Log::write(array("msg" => "({$server["server"]}:{$server["port"]}) AUTH LOGIN - $lastMessage", "type" => "SMTP"), "action", "sendMail");
                        return false;
                    }

                    fputs($fp, base64_encode($server["username"]) . "\r\n");
                    $lastMessage = fgets($fp, 512);

                    if (substr($lastMessage, 0, 3) != 334) {
                        Log::write(array("msg" => "({$server["server"]}:{$server["port"]}) USERNAME - $lastMessage", "type" => "SMTP"), "action", "sendMail");
                        return false;
                    }

                    fputs($fp, base64_encode($server["password"]) . "\r\n");
                    $lastMessage = fgets($fp, 512);

                    if (substr($lastMessage, 0, 3) != 235) {
                        Log::write(array("msg" => "({$server["server"]}:{$server["port"]}) PASSWORD - $lastMessage", "type" => "SMTP"), "action", "sendMail");
                        return false;
                    }

                    $emailFrom = $server["from"];
                }

                fputs($fp, "MAIL FROM: <" . preg_replace("/.*\<(.+?)\>.*/", "\1", $emailFrom) . ">\r\n");
                $lastMessage = fgets($fp, 512);

                if (substr($lastMessage, 0, 3) != 250) {
                    fputs($fp, "MAIL FROM: <" . preg_replace("/.*\<(.+?)\>.*/", "\1", $emailFrom) . ">\r\n");
                    $lastMessage = fgets($fp, 512);
                    Log::write(array("msg" => "({$server["server"]}:{$server["port"]}) MAIL FROM  - $lastMessage", "type" => "SMTP"), "action", "sendMail");
                    return false;
                }

                fputs($fp, "RCPT TO: <" . preg_replace("/.*\<(.+?)\>.*/", "\1", $to) . ">\r\n");
                $lastMessage = fgets($fp, 512);

                if (substr($lastMessage, 0, 3) != 250) {
                    fputs($fp, "RCPT TO: <" . preg_replace("/.*\<(.+?)\>.*/", "\1", $to) . ">\r\n");
                    $lastMessage = fgets($fp, 512);
                    Log::write(array("msg" => "({$server["server"]}:{$server["port"]}) RCPT TO - $lastMessage", "type" => "SMTP"), "action", "sendMail");
                    return false;
                }

                fputs($fp, "DATA\r\n");
                $lastMessage = fgets($fp, 512);

                if (substr($lastMessage, 0, 3) != 354) {
                    Log::write(array("msg" => "({$server["server"]}:{$server["port"]}) DATA - $lastMessage", "type" => "SMTP"), "action", "sendMail");
                    return false;
                }

                $timeOffset = $setting["setting"]["timeoffset"];

                if (function_exists("date_default_timezone_set")) {
                    @date_default_timezone_set("Etc/GMT" . (0 < $timeOffset ? "-" : "+") . abs($timeOffset));
                }

                $headers .= "Message-ID: <" . date("YmdHs") . "." . substr(md5($emailMessage . microtime()), 0, 6) . rand(100000, 999999) . "@" . $_SERVER["HTTP_HOST"] . ">$delimiter";
                fputs($fp, "Date: " . date("r") . "\r\n");
                fputs($fp, "To: " . $emailTo . "\r\n");
                fputs($fp, "Subject: " . $emailSubject . "\r\n");
                fputs($fp, $headers . "\r\n");
                fputs($fp, "\r\n\r\n");
                fputs($fp, "{$emailMessage}\r\n.\r\n");
                $lastMessage = fgets($fp, 512);

                if (substr($lastMessage, 0, 3) != 250) {
                    Log::write(array("msg" => "({$server["server"]}:{$server["port"]}) END - $lastMessage", "type" => "SMTP"), "action", "sendMail");
                }

                fputs($fp, "QUIT\r\n");
                return true;
            } elseif ($mail["mailsend"] == 2) {
                ini_set("SMTP", $server["server"]);
                ini_set("smtp_port", $server["port"]);
                ini_set("sendmail_from", $emailFrom);
                if (function_exists("mail") && @mail($emailTo, $emailSubject, $emailMessage, $headers)) {
                    return true;
                }

                return false;
            }
        }

        return false;
    }
}
