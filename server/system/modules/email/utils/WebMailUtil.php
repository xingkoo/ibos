<?php

class WebMailUtil
{
    const SERVER_CONF_WEB = "http://www.ibos.com.cn/resources/email/serverConf.xml";
    const SERVER_CONF_LOCAL = "system/modules/email/extensions/serverConf.xml";

    /**
     * 默认的服务器配置数组
     * @var array 
     */
    private static $defaultConfig = array("POP3NAME" => "", "POP3EntireAddress" => 0, "SMTPNAME" => "", "IMAPNAME" => "", "POP3PORT" => 110, "SMTPPORT" => 25, "IMAPPORT" => 0, "POP3SSL" => 0, "SMTPSSL" => 0, "IMAPSSL" => 0, "IMAPEntireAddress" => 0, "DefaultUseIMAP" => 0);
    private static $_web = array();

    public static function checkAccount($address, $password, $postConfig = array(), $configParse = "LOCAL")
    {
        $accountCorrect = false;
        $server = array();

        if (empty($postConfig)) {
            $server = self::getEmailConfig($address, $password, $configParse);
        } else {
            $server = self::mergePostConfig($address, $password, $postConfig);
        }

        if (!is_string($server)) {
            $accountCorrect = self::connectServer($server);
        }

        return $accountCorrect;
    }

    private static function connectServer($conf = array())
    {
        $connected = false;

        if (!empty($conf)) {
            if ($conf["type"] == "imap") {
                $obj = new WebMailImap();
            } else {
                $obj = new WebMailPop();
            }

            if ($obj->connect($conf["server"], $conf["username"], $conf["password"], $conf["ssl"], $conf["port"])) {
                $connected = true;
            }
        }

        return $connected;
    }

    private static function fetchBody($obj, $conn, $folder, $id, $structure, $part)
    {
        $body = $obj->fetchPartBody($conn, $folder, $id, $part);
        $encoding = EmailMimeUtil::getPartEncodingCode($structure, $part);

        if ($encoding == 3) {
            $body = base64_decode($body);
        } elseif ($encoding == 4) {
            $body = quoted_printable_decode($body);
        }

        $charset = EmailMimeUtil::getPartCharset($structure, $part);

        if (empty($charset)) {
            $part_header = $obj->fetchPartHeader($conn, $folder, $id, $part);
            $pattern = "/charset=[\"]?([a-zA-Z0-9_-]+)[\"]?/";
            preg_match($pattern, $part_header, $matches);

            if (count($matches) == 2) {
                $charset = $matches[1];
            }
        }

        if (strcasecmp($charset, "utf-8") == 0) {
            $is_unicode = true;
        } elseif (preg_match("/#[0-9]{5};/", $body)) {
            $is_unicode = false;
        } else {
            $is_unicode = false;
        }

        if (!$is_unicode) {
            $body = ConvertUtil::iIconv($body, "gb2312");
        }

        $url = Yii::app()->urlManager->createUrl("email/web/show", array("webid" => self::$_web["webid"], "folder" => $folder, "id" => $id, "cid" => ""));
        $body = preg_replace("/src=(\")?cid:/i", "src=\"$url", $body);
        return $body;
    }

    public static function getBody($id, &$conn, &$obj, $header)
    {
        $structure_str = $obj->fetchStructureString($conn, "INBOX", $id);
        $structure = EmailMimeUtil::getRawStructureArray($structure_str);
        $num_parts = EmailMimeUtil::getNumParts($structure);
        $parent_type = EmailMimeUtil::getPartTypeCode($structure);
        if (($parent_type == 1) && ($num_parts == 1)) {
            $part = 1;
            $num_parts = EmailMimeUtil::getNumParts($structure, $part);
            $parent_type = EmailMimeUtil::getPartTypeCode($structure, $part);
        } else {
            $part = null;
        }

        $body = array();
        $attach = "";

        if (0 < $num_parts) {
            $attach .= "<table width=100%>\n";

            for ($i = 1; $i <= $num_parts; $i++) {
                if ($parent_type == 1) {
                    $code = $part . (empty($part) ? "" : ".") . $i;
                } elseif ($parent_type == 2) {
                    $code = $part . (empty($part) ? "" : ".") . $i;
                }

                $type = EmailMimeUtil::getPartTypeCode($structure, $code);
                $name = EmailMimeUtil::getPartName($structure, $code);
                if (is_string($name) && !empty($name)) {
                    $name = htmlspecialchars(EmailLangUtil::langDecodeSubject($name, CHARSET));
                    $fileExt = StringUtil::getFileExt($name);
                    $fileType = AttachUtil::attachType($fileExt);
                } else {
                    $fileType = AttachUtil::attachType(1);
                }

                $typestring = EmailMimeUtil::getPartTypeString($structure, $code);
                list($dummy, $subtype) = explode("/", $typestring);
                $bytes = EmailMimeUtil::getPartSize($structure, $code);
                $disposition = EmailMimeUtil::getPartDisposition($structure, $code);
                if (($type == 1) || ($type == 2) || (($type == 3) && (strcasecmp($subtype, "ms-tnef") == 0))) {
                    continue;
                } else {
                    $href = Yii::app()->urlManager->createUrl("email/web/show", array("webid" => self::$_web["webid"], "folder" => "INBOX", "id" => $id, "part" => $code));
                }

                $attach .= "<tr><td align=\"center\"><img src=\"$fileType\" border=0></td>";
                $attach .= "<td><a href=\"" . $href . "\" " . (($type == 1) || ($type == 2) || (($type == 3) && (strcasecmp($subtype, "ms-tnef") == 0)) ? "" : "target=_blank") . ">";
                $attach .= "<span class=\"small\">" . $name . "</span></a>";

                if (0 < $bytes) {
                    $attach .= "<td>[" . ConvertUtil::sizeCount($bytes) . "]</td>\n";
                }

                if (is_string($typestring)) {
                    $attach .= "<td>" . htmlspecialchars($typestring) . "</td>\n";
                }

                $attach .= "\n</tr>\n";
            }

            $attach .= "</table>\n";
        }

        $typeCode = EmailMimeUtil::getPartTypeCode($structure, $part);
        list($dummy, $subType) = explode("/", EmailMimeUtil::getPartTypeString($structure, $part));
        if (($typeCode == 3) && (strcasecmp($subType, "ms-tnef") == 0)) {
            $type = $dummy;
        } elseif ($typeCode == 0) {
            $typeString = EmailMimeUtil::getPartTypeString($structure, $part);
            if (empty($part) && !empty($header->ctype) && (strcmp($typeString, $header->ctype) != 0)) {
                $typeString = $header->ctype;
            }

            list($type, $subType) = explode("/", $typeString);
            $body[] = self::fetchBody($obj, $conn, "INBOX", $id, $structure, $part);
        } else {
            if (($typeCode == 1) && empty($part) && ($structure[0][0] == "message")) {
                $part = "1.1";
                $typeString = EmailMimeUtil::getPartTypeString($structure, $part);
                list($type, $subType) = explode("/", $typeString);
                $typeCode = EmailMimeUtil::getPartTypeCode($structure, $part);
                $disposition = EmailMimeUtil::getPartDisposition($structure, $part);
                $body[] = self::fetchBody($obj, $conn, "INBOX", $id, $structure, $part);
            } else {
                if (($typeCode == 1) || ($typeCode == 2)) {
                    $typeString = EmailMimeUtil::getPartTypeString($structure, $part);
                    list($type, $subType) = explode("/", $typeString);
                    $mode = 0;
                    $subtypes = array("mixed" => 1, "signed" => 1, "related" => 1, "array" => 2, "alternative" => 2);
                    $subType = strtolower($subType);

                    if (0 < $subtypes[$subType]) {
                        $mode = $subtypes[$subType];
                    } elseif (strcasecmp($subType, "rfc822") == 0) {
                        $temp_num = EmailMimeUtil::getNumParts($structure, $part);

                        if (0 < $temp_num) {
                            $mode = 2;
                        }
                    } elseif (strcasecmp($subType, "encrypted") == 0) {
                        $encrypted_type = EmailMimeUtil::getPartTypeString($structure, $part . ".1");

                        if (stristr($encrypted_type, "pgp-encrypted") !== false) {
                            $mode = -1;
                        }
                    }

                    if ($mode == -1) {
                        $part = $part . (empty($part) ? "" : ".") . "2";
                        $typeString = EmailMimeUtil::getPartTypeString($structure, $part);
                        list($type, $subType) = explode("/", $typeString);
                        $typeCode = EmailMimeUtil::getPartTypeCode($structure, $part);
                        $disposition = EmailMimeUtil::getPartDisposition($structure, $part);
                        $body[] = self::fetchBody($obj, $conn, "INBOX", $id, $structure, $part);
                    } elseif (0 < $mode) {
                        $originalPart = $part;

                        for ($i = 1; $i <= $num_parts; $i++) {
                            $part = $originalPart . (empty($originalPart) ? "" : ".") . $i;
                            $typeString = EmailMimeUtil::getPartTypeString($structure, $part);
                            list($type, $subType) = explode("/", $typeString);
                            $typeCode = EmailMimeUtil::getPartTypeCode($structure, $part);
                            $disposition = EmailMimeUtil::getPartDisposition($structure, $part);

                            if (strcasecmp($disposition, "attachment") != 0) {
                                if (($mode == 1) && ($typeCode == 0)) {
                                    $body[] = self::fetchBody($obj, $conn, "INBOX", $id, $structure, $part);
                                } elseif ($mode == 2) {
                                    $body[] = self::fetchBody($obj, $conn, "INBOX", $id, $structure, $part);
                                } else {
                                    if (($typeCode == 5) && (strcasecmp($disposition, "inline") == 0)) {
                                        $href = Yii::app()->urlManager->createUrl("email/web/show", array("webid" => self::$_web["webid"], "folder" => "INBOX", "id" => $id, "part" => $part));
                                        $body[] = "<img src='$href'>";
                                    } elseif ($typeCode == 1) {
                                        $part = EmailMimeUtil::getFirstTextPart($structure, $part);
                                        $next_part = EmailMimeUtil::getNextPart($part);
                                        $next_type = EmailMimeUtil::getPartTypeString($structure, $next_part);

                                        if (stristr($next_type, "html") !== false) {
                                            $part = $next_part;
                                        }

                                        $i++;
                                        $body[] = self::fetchBody($obj, $conn, "INBOX", $id, $structure, $part);
                                    }
                                }
                            } elseif ($typeCode == 5) {
                                $href = Yii::app()->urlManager->createUrl("email/web/show", array("webid" => self::$_web["webid"], "folder" => "INBOX", "id" => $id, "part" => $part));
                                $body[] = "<img src='$href'>";
                            }
                        }
                    } else {
                        if (strcasecmp($subType, "rfc822") != 0) {
                            $part = EmailMimeUtil::getFirstTextPart($structure, "");
                            $next_part = EmailMimeUtil::getNextPart($part);
                            $next_type = EmailMimeUtil::getPartTypeString($structure, $next_part);

                            if (stristr($next_type, "html") !== false) {
                                $typeString = "text/html";
                                $type = "text";
                                $subType = "html";
                                $part = $next_part;
                            }
                        }

                        $body[] = self::fetchBody($obj, $conn, "INBOX", $id, $structure, $part);
                    }
                } else {
                    $type = EmailMimeUtil::getPartTypeCode($structure, $part);
                    $partName = EmailMimeUtil::getPartName($structure, $part);
                    $typeString = EmailMimeUtil::getPartTypeString($structure, $part);
                    $bytes = EmailMimeUtil::getPartSize($structure, $part);
                    $disposition = EmailMimeUtil::getPartDisposition($structure, $part);
                    $name = EmailLangUtil::langDecodeSubject($partName, CHARSET);
                    $fileExt = StringUtil::getFileExt($name);
                    $fileType = AttachUtil::attachType($fileExt);
                    $size = ConvertUtil::sizeCount($bytes);
                    $href = Yii::app()->urlManager->createUrl("email/web/show", array("webid" => self::$_web["webid"], "folder" => "INBOX", "id" => $id, "part" => $part));
                    //$body[] = "\t\t\t\t\t<table>\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td align=\"center\">\r\n\t\t\t\t\t\t\t\t<a href=\"$href\" target=\"_blank\"><img src=\"$fileType\" border=0 /><br/>$name<br/>[$size]<br/></a>\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t</table><br/>";
                    $body[] = '<table>
                        <tr>
                            <td align="center">
                                <a href="$href" target="_blank"><img src="'. $fileType .'" border=0 /><br/>'. $name .'<br/>['. $size .']<br/></a>
                            </td>
                        </tr>
                    </table><br/>';
                }
            }
        }

        $body[] = $attach;
        return $body;
    }

    public static function getDomin($string)
    {
        $parts = explode(".", $string);
        $count = count($parts);

        if (2 < $count) {
            $suffix = array_pop($parts);
            $domain = array_pop($parts);
            return $domain . "." . $suffix;
        } else {
            return $string;
        }
    }

    public static function getEmailConfig($address, $password, $configParse = "LOCAL")
    {
        $server = array();
        $config = self::getServerConfig($configParse);

        if (!empty($config)) {
            list(, $server) = explode("@", $address);

            if (isset($config[$server])) {
                $server = self::mergeServerConfig($address, $password, $config[$server]);
            } else {
                $host = self::getMailAddress($server);

                if ($host) {
                    if (isset($config[$host])) {
                        $server = self::mergeServerConfig($address, $password, $config[$host]);
                    }
                }
            }
        }

        return $server;
    }

    public static function getMailAddress($domain)
    {
        $host = $ip = false;
        $records = @dns_get_record($domain, DNS_MX);

        if (!$records) {
            return false;
        }

        $priority = null;

        foreach ($records as $record) {
            if (($priority == null) || ($record["pri"] < $priority)) {
                $myip = gethostbyname($record["target"]);

                if ($myip != $record["target"]) {
                    $ip = $myip;
                    $host = self::getDomin($record["target"]);
                    $priority = $record["pri"];
                }
            }
        }

        if (!$ip) {
            $ip = gethostbyname($domain);

            if ($ip == $domain) {
                $ip = false;
            } else {
                $info = gethostbyaddr($ip);
                $info && ($host = self::getDomin($info));
            }
        }

        return $host;
    }

    public static function getServerConfig($method)
    {
        static $config = array();

        if (empty($config)) {
            switch ($method) {
                case "LOCAL":
                    $config = self::parseLocalConfig(self::SERVER_CONF_LOCAL);
                    break;

                case "WEB":
                    $config = self::parseWebConfig(self::SERVER_CONF_WEB);
                    break;

                default:
                    $config = array();
                    break;
            }
        }

        return $config;
    }

    public static function mergePostConfig($address, $password, $config)
    {
        $data = array("SMTPNAME" => $config["smtpserver"], "SMTPPORT" => $config["smtpport"], "SMTPSSL" => isset($config["smtpssl"]) ? 1 : 0);

        if ($config["agreement"] == "1") {
            $data["POP3NAME"] = $config["server"];
            $data["POP3PORT"] = $config["port"];
            $data["POP3SSL"] = (isset($config["ssl"]) ? 1 : 0);
        } else {
            $data["IMAPNAME"] = $config["server"];
            $data["IMAPPORT"] = $config["port"];
            $data["IMAPSSL"] = (isset($config["ssl"]) ? 1 : 0);
            $data["DefaultUseIMAP"] = 1;
        }

        return self::mergeServerConfig($address, $password, $data);
    }

    private static function mergeServerConfig($address, $password, $config)
    {
        $config = array_merge(self::$defaultConfig, $config);
        $return = array();
        if ($config["POP3EntireAddress"] || $config["IMAPEntireAddress"]) {
            $return["username"] = $address;
        } else {
            list($domain) = explode("@", $address);
            $return["username"] = $domain;
        }

        $return["password"] = $password;
        $return["address"] = $address;
        $usingImap = ($config["DefaultUseIMAP"] ? true : false);
        $return["type"] = ($usingImap ? "imap" : "pop");
        $return["server"] = ($usingImap ? $config["IMAPNAME"] : $config["POP3NAME"]);
        $return["port"] = ($usingImap ? $config["IMAPPORT"] : $config["POP3PORT"]);
        $return["ssl"] = ($usingImap ? $config["IMAPSSL"] : $config["POP3SSL"]);
        $return["smtpserver"] = (isset($config["SMTPNAME"]) ? $config["SMTPNAME"] : "");
        $return["smtpport"] = (isset($config["SMTPPORT"]) ? $config["SMTPPORT"] : "");
        $return["smtpssl"] = (isset($config["SMTPSSL"]) ? $config["SMTPSSL"] : "");
        return $return;
    }

    private static function parseLocalConfig($address)
    {
        $config = array();

        if (is_file($address)) {
            $fileContent = file_get_contents($address);
            $config = XmlUtil::xmlToArray($fileContent);
        }

        return $config;
    }

    private static function parseWebConfig($address)
    {
    }

    public static function receiveMail($web)
    {
        self::$_web = $web;
        @set_time_limit(0);
        ignore_user_abort(true);
        list($prefix) = explode(".", $web["server"]);
        $user = User::model()->fetchByUid($web["uid"]);
        $pwd = StringUtil::authCode($web["password"], "DECODE", $user["salt"]);

        if ($prefix == "imap") {
            $obj = new WebMailImap();
        } else {
            $obj = new WebMailPop();
        }

        $conn = $obj->connect($web["server"], $web["username"], $pwd, $web["ssl"], $web["port"], "plain");

        if (!$conn) {
            return implode(",", $obj->getError());
        } else {
            $totalNum = $obj->countMessages($conn, "INBOX");

            if (0 < $totalNum) {
                $messagesStr = "1:" . $totalNum;
            } else {
                $messagesStr = "";
            }

            if ($messagesStr != "") {
                $headers = $obj->fetchHeaders($conn, "INBOX", $messagesStr);
                $headers = $obj->sortHeaders($headers, "DATE", "DESC");
            } else {
                $headers = false;
            }

            if ($headers == false) {
                $headers = array();
            }

            $count = 0;

            if (0 < count($headers)) {
                while (list($key, $val) = each($headers)) {
                    $header = $headers[$key];
                    $time = $header->timestamp + 28800;
                    if (($web["lastrectime"] == 0) || ($web["lastrectime"] < $time)) {
                        $count++;
                        $data = array();
                        $data["subject"] = str_replace(array("<", ">"), array("&lt;", "&gt;"), EmailLangUtil::langDecodeSubject($header->subject, CHARSET));
                        $data["sendtime"] = $time;
                        $data["towebmail"] = $web["address"];
                        $data["issend"] = 1;
                        $data["fromid"] = $data["secrettoids"] = "";
                        $data["fromwebmail"] = EmailLangUtil::langGetParseAddressList($header->from);
                        if (isset($header->to) && !empty($header->to)) {
                            $data["toids"] = EmailLangUtil::langGetParseAddressList($header->to, ",");
                        } else {
                            $data["toids"] = "";
                        }

                        if (isset($header->cc) && !empty($header->cc)) {
                            $data["copytoids"] = EmailLangUtil::langGetParseAddressList($header->cc, ",");
                        } else {
                            $data["copytoids"] = "";
                        }

                        $body = self::getBody($header->id, $conn, $obj, $header);
                        $data["content"] = implode("", $body);
                        $data["size"] = EmailUtil::getEmailSize($data["content"]);
                        $bodyId = EmailBody::model()->add($data, true);

                        if ($bodyId) {
                            $email = array("toid" => $web["uid"], "isread" => 0, "fid" => $web["fid"], "isweb" => 1, "bodyid" => $bodyId);
                            Email::model()->add($email);
                        }
                    }
                }

                EmailWeb::model()->updateByPk($web["webid"], array("lastrectime" => TIMESTAMP));
            }

            return $count;
        }
    }

    public static function sendWebMail($toUser, $body, $web)
    {
        $user = User::model()->fetchByUid($web["uid"]);
        $password = StringUtil::authCode($web["password"], "DECODE", $user["salt"]);
        $mailer = Yii::createComponent("application.modules.email.extensions.mailer.EMailer");
        $mailer->IsSMTP();
        $mailer->SMTPDebug = 0;
        $mailer->Host = $web["smtpserver"];
        $mailer->Port = $web["smtpport"];
        $mailer->CharSet = "UTF-8";

        if ($web["smtpssl"]) {
            $mailer->SMTPSecure = "ssl";
        }

        $mailer->SMTPAuth = true;
        $mailer->Username = $web["username"];
        $mailer->Password = $password;
        $mailer->setFrom($web["address"], $web["nickname"]);

        foreach (explode(";", $toUser) as $address) {
            $mailer->addAddress($address);
        }

        $mailer->Subject = $body["subject"];
        $mailer->msgHTML($body["content"]);
        $mailer->AltBody = "This is a plain-text message body";

        if (!empty($body["attachmentid"])) {
            $attachs = AttachUtil::getAttachData($body["attachmentid"]);
            $attachUrl = FileUtil::getAttachUrl();

            foreach ($attachs as $attachment) {
                $url = $attachUrl . "/" . $attachment["attachment"];

                if (LOCAL) {
                    $mailer->addAttachment($url, $attachment["filename"]);
                } else {
                    $temp = Ibos::engine()->IO()->file()->fetchTemp($url);
                    $mailer->addAttachment($temp, $attachment["filename"]);
                }
            }
        }

        $status = $mailer->send();

        if ($status) {
            return true;
        } else {
            return $mailer->ErrorInfo;
        }
    }
}
