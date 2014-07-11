<?php

class FileUtil
{
    public static function getUpload($fileArea, $module = "temp")
    {
        return Ibos::engine()->IO()->upload($fileArea, $module);
    }

    public static function getAttachUrl()
    {
        return rtrim(Ibos::app()->setting->get("setting/attachurl"), DIRECTORY_SEPARATOR);
    }

    public static function download($attach, $downloadInfo = array())
    {
        return Ibos::engine()->IO()->file()->download($attach, $downloadInfo);
    }

    public static function fileName($fileName)
    {
        return Ibos::engine()->IO()->file()->fileName($fileName);
    }

    public static function imageSize($image)
    {
        return Ibos::engine()->IO()->file()->imageSize($image);
    }

    public static function fileSize($file)
    {
        return Ibos::engine()->IO()->file()->fileSize($file);
    }

    public static function fileExists($file)
    {
        return Ibos::engine()->IO()->file()->fileExists($file);
    }

    public static function createFile($fileName, $content)
    {
        return Ibos::engine()->IO()->file()->createFile($fileName, $content);
    }

    public static function readFile($fileName)
    {
        return Ibos::engine()->IO()->file()->readFile($fileName);
    }

    public static function getTempPath()
    {
        return Ibos::engine()->IO()->file()->getTempPath();
    }

    public static function clearDir($dir)
    {
        return Ibos::engine()->IO()->file()->clearDir($dir);
    }

    public static function deleteFile($fileName)
    {
        return Ibos::engine()->IO()->file()->deleteFile($fileName);
    }

    public static function clearDirs($srcDir, $except = array())
    {
        return Ibos::engine()->IO()->file()->clearDirs($srcDir, $except);
    }

    public static function fileSockOpen($url, $limit = 0, $post = "", $cookie = "", $bysocket = false, $ip = "", $timeout = 15, $block = true, $encodeType = "URLENCODE", $allowcurl = true, $position = 0)
    {
        $return = "";
        $matches = parse_url($url);
        $scheme = $matches["scheme"];
        $host = $matches["host"];
        $path = ($matches["path"] ? $matches["path"] . (isset($matches["query"]) ? "?" . $matches["query"] : "") : "/");
        $port = (!empty($matches["port"]) ? $matches["port"] : 80);
        if (function_exists("curl_init") && function_exists("curl_exec") && $allowcurl) {
            $ch = curl_init();
            $ip && curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: " . $host));
            curl_setopt($ch, CURLOPT_URL, $scheme . "://" . ($ip ? $ip : $host) . ":" . $port . $path);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            if ($post) {
                curl_setopt($ch, CURLOPT_POST, 1);

                if ($encodeType == "URLENCODE") {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                } else {
                    parse_str($post, $postArray);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postArray);
                }
            }

            if ($cookie) {
                curl_setopt($ch, CURLOPT_COOKIE, $cookie);
            }

            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            $data = curl_exec($ch);
            $status = curl_getinfo($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            if ($errno || ($status["http_code"] != 200)) {
                return null;
            } else {
                return !$limit ? $data : substr($data, 0, $limit);
            }
        }

        if ($post) {
            $out = "POST $path HTTP/1.0\r\n";
            $header = "Accept: */*\r\n";
            $header .= "Accept-Language: zh-cn\r\n";
            $boundary = ($encodeType == "URLENCODE" ? "" : "; boundary=" . trim(substr(trim($post), 2, strpos(trim($post), "\n") - 2)));
            $header .= ($encodeType == "URLENCODE" ? "Content-Type: application/x-www-form-urlencoded\r\n" : "Content-Type: multipart/form-data{$boundary}\r\n");
            $header .= "User-Agent: {$_SERVER["HTTP_USER_AGENT"]}\r\n";
            $header .= "Host: $host:{$port}\r\n";
            $header .= "Content-Length: " . strlen($post) . "\r\n";
            $header .= "Connection: Close\r\n";
            $header .= "Cache-Control: no-cache\r\n";
            $header .= "Cookie: {$cookie}\r\n\r\n";
            $out .= $header . $post;
        } else {
            $out = "GET $path HTTP/1.0\r\n";
            $header = "Accept: */*\r\n";
            $header .= "Accept-Language: zh-cn\r\n";
            $header .= "User-Agent: {$_SERVER["HTTP_USER_AGENT"]}\r\n";
            $header .= "Host: $host:{$port}\r\n";
            $header .= "Connection: Close\r\n";
            $header .= "Cookie: {$cookie}\r\n\r\n";
            $out .= $header;
        }

        $fpflag = 0;
        if (!$fp = @fsockopen($ip ? $ip : $host, $port, $errno, $errstr, $timeout)) {
            $context = array(
                "http" => array("method" => $post ? "POST" : "GET", "header" => $header, "content" => $post, "timeout" => $timeout)
                );
            $context = stream_context_create($context);
            $fp = @fopen($scheme . "://" . ($ip ? $ip : $host) . ":" . $port . $path, "b", false, $context);
            $fpflag = 1;
        }

        if (!$fp) {
            return "";
        } else {
            stream_set_blocking($fp, $block);
            stream_set_timeout($fp, $timeout);
            @fwrite($fp, $out);
            $status = stream_get_meta_data($fp);

            if (!$status["timed_out"]) {
                while (!feof($fp) && !$fpflag) {
                    if (($header = @fgets($fp)) && (($header == "\r\n") || ($header == "\n"))) {
                        break;
                    }
                }

                if ($position) {
                    fseek($fp, $position, SEEK_CUR);
                }

                if ($limit) {
                    $return = stream_get_contents($fp, $limit);
                } else {
                    $return = stream_get_contents($fp);
                }
            }

            @fclose($fp);
            return $return;
        }
    }

    public static function checkFolderPerm($fileList)
    {
        foreach ($fileList as $file) {
            if (!file_exists(PATH_ROOT . "/" . $file)) {
                if (!self::testDirWriteable(dirname(PATH_ROOT . "/" . $file))) {
                    return false;
                }
            } elseif (!is_writable(PATH_ROOT . "/" . $file)) {
                return false;
            }
        }

        return true;
    }

    public static function testDirWriteable($dir)
    {
        $writeable = false;

        if (!is_dir($dir)) {
            self::makeDirs($dir, 511);
        }

        if (is_dir($dir)) {
            $fp = @fopen("$dir/test.txt", "w");

            if ($fp) {
                @fclose($fp);
                @unlink("$dir/test.txt");
                $writeable = true;
            } else {
                $writeable = false;
            }
        }

        return $writeable;
    }

    public static function makeDir($dir, $mode = 511, $makeIndex = true)
    {
        $res = true;

        if (!is_dir($dir)) {
            $res = @mkdir($dir, $mode);

            if ($makeIndex) {
                @touch($dir . "/index.html");
            }
        }

        return $res;
    }

    public static function makeDirs($dir, $mode = 511, $makeIndex = true)
    {
        if (!is_dir($dir)) {
            if (!self::makeDirs(dirname($dir))) {
                return false;
            }

            if (!@mkdir($dir, $mode)) {
                return false;
            }

            if ($makeIndex) {
                @touch($dir . "/index.html");
                @chmod($dir . "/index.html", $mode);
            }
        }

        return true;
    }

    public static function copyToDir($file, $copyToPath)
    {
        if (FileUtil::fileExists($file)) {
            $name = basename($file);

            if (LOCAL) {
                $state = @copy($file, $copyToPath . $name);
            } else {
                $state = Ibos::engine()->IO()->file()->moveFile($file, $copyToPath . $name);
            }

            return $state;
        }
    }

    public static function copyDir($srcDir, $destDir)
    {
        $dir = @opendir($srcDir);

        while ($entry = @readdir($dir)) {
            $file = $srcDir . $entry;
            if (($entry != ".") && ($entry != "..")) {
                if (is_dir($file)) {
                    self::copyDir($file . "/", $destDir . $entry . "/");
                } else {
                    self::makeDirs(dirname($destDir . $entry));
                    copy($file, $destDir . $entry);
                }
            }
        }

        closedir($dir);
    }

    public static function exportCsv($filename, $data)
    {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
        header("Expires:0");
        header("Pragma:public");
        echo $data;
    }
}
