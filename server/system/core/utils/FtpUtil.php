<?php

class FtpUtil
{
    const FTP_ERR_SERVER_DISABLED = -100;
    const FTP_ERR_CONFIG_OFF = -101;
    const FTP_ERR_CONNECT_TO_SERVER = -102;
    const FTP_ERR_USER_NO_LOGGIN = -103;
    const FTP_ERR_CHDIR = -104;
    const FTP_ERR_MKDIR = -105;
    const FTP_ERR_SOURCE_READ = -106;
    const FTP_ERR_TARGET_WRITE = -107;

    /**
     * 是否使用
     * @var boolean 
     */
    private $_enabled = false;
    /**
     * 配置
     * @var array 
     */
    private $_config = array();
    /**
     * 方法
     * @var function 
     */
    private $_func;
    /**
     *
     * @var int 
     */
    private $_connectId;
    /**
     * 错误标识
     * @var int
     */
    private $_error;

    public static function getInstance($config = array())
    {
        static $object;

        if (empty($object)) {
            $object = new self($config);
        }

        return $object;
    }

    public function __construct($config = array())
    {
        $ftp = Yii::app()->setting->get("setting/ftp");
        $this->setError(0);
        $this->_config = (!$config ? $ftp : $config);
        $this->_enabled = false;
        if (empty($this->_config["on"]) || empty($this->_config["host"])) {
            $this->setError(self::FTP_ERR_CONFIG_OFF);
        } else {
            $this->_func = (isset($this->_config["ftpssl"]) && function_exists("ftp_ssl_connect") ? "ftp_ssl_connect" : "ftpConnect");
            if (($this->_func == "ftpConnect") && !function_exists("ftpConnect")) {
                $this->setError(self::FTP_ERR_SERVER_DISABLED);
            } else {
                $this->_config["host"] = $this->clear($this->_config["host"]);
                $this->_config["port"] = intval($this->_config["port"]);
                $this->_config["ssl"] = intval($this->_config["ssl"]);
                $this->_config["host"] = $this->clear($this->_config["host"]);
                $autoKey = md5(Yii::app()->setting->get("config/security/authkey"));
                $this->_config["password"] = StringUtil::authCode($this->_config["password"], "DECODE", $autoKey);
                $this->_config["timeout"] = intval($this->_config["timeout"]);
                $this->_enabled = true;
            }
        }
    }

    public function upload($source, $target)
    {
        if ($this->error()) {
            return 0;
        }

        $oldDir = $this->ftpPwd();
        $dirName = dirname($target);
        $fileName = basename($target);

        if (!$this->ftpChdir($dirName)) {
            if ($this->ftpMkdir($dirName)) {
                $this->ftpChmod($dirName);

                if (!$this->ftpChdir($dirName)) {
                    $this->setError(self::FTP_ERR_CHDIR);
                }

                $attachDir = Yii::app()->setting->get("setting/attachdir");
                $this->ftpPut("index.htm", $attachDir . "/index.htm", FTP_BINARY);
            } else {
                $this->setError(self::FTP_ERR_MKDIR);
            }
        }

        $res = 0;

        if (!$this->error()) {
            $fp = @fopen($source, "rb");

            if ($fp) {
                $res = $this->ftpFput($fileName, $fp, FTP_BINARY);
                @fclose($fp);
                !$res && $this->setError(self::FTP_ERR_TARGET_WRITE);
            } else {
                $this->setError(self::FTP_ERR_SOURCE_READ);
            }
        }

        $this->ftpChdir($oldDir);
        return $res ? 1 : 0;
    }

    public function connect()
    {
        if (!$this->_enabled || empty($this->_config)) {
            return 0;
        } else {
            return $this->ftpConnect($this->config["host"], $this->config["username"], $this->config["password"], $this->config["attachdir"], $this->config["port"], $this->config["timeout"], $this->config["ssl"], $this->config["pasv"]);
        }
    }

    public function ftpConnect($ftpHost, $userName, $password, $ftpPath, $ftpPort = 21, $timeout = 30, $ftpssl = 0, $ftpPasv = 0)
    {
        $res = 0;
        $fun = $this->func;

        if ($this->_connectId = $fun($ftpHost, $ftpPort, 20)) {
            $timeout && $this->setOption(FTP_TIMEOUT_SEC, $timeout);

            if ($this->ftpLogin($userName, $password)) {
                $this->ftpPasv($ftpPasv);

                if ($this->ftpChdir($ftpPath)) {
                    $res = $this->_connectId;
                } else {
                    $this->setError(self::FTP_ERR_CHDIR);
                }
            } else {
                $this->setError(self::FTP_ERR_USER_NO_LOGGIN);
            }
        } else {
            $this->setError(self::FTP_ERR_CONNECT_TO_SERVER);
        }

        if (0 < $res) {
            $this->setError();
            $this->_enabled = 1;
        } else {
            $this->_enabled = 0;
            $this->ftpClose();
        }

        return $res;
    }

    public function error()
    {
        return $this->_error;
    }

    private function clear($str)
    {
        return str_replace(array("\n", "\r", ".."), "", $str);
    }

    private function setOption($cmd, $value)
    {
        if (function_exists("ftp_set_option")) {
            return @ftp_set_option($this->_connectId, $cmd, $value);
        }
    }

    private function ftpMkdir($directory)
    {
        $directory = $this->clear($directory);
        $ePath = explode("/", $directory);
        $dir = "";
        $comma = "";

        foreach ($ePath as $path) {
            $dir .= $comma . $path;
            $comma = "/";
            $return = @ftp_mkdir($this->_connectId, $dir);
            $this->ftpChmod($dir);
        }

        return $return;
    }

    private function ftpRmdir($directory)
    {
        $directory = $this->clear($directory);
        return @ftp_rmdir($this->_connectId, $directory);
    }

    private function ftpPut($remoteFile, $localFile, $mode = FTP_BINARY)
    {
        $remoteFile = $this->clear($remoteFile);
        $localFile = $this->clear($localFile);
        $mode = intval($mode);
        return @ftp_put($this->_connectId, $remoteFile, $localFile, $mode);
    }

    private function ftpFput($remoteFile, $sourcefp, $mode = FTP_BINARY)
    {
        $remoteFile = $this->clear($remoteFile);
        $mode = intval($mode);
        return @ftp_fput($this->_connectId, $remoteFile, $sourcefp, $mode);
    }

    private function ftpSize($remoteFile)
    {
        $remoteFile = $this->clear($remoteFile);
        return @ftp_size($this->_connectId, $remoteFile);
    }

    private function ftpClose()
    {
        return @ftp_close($this->_connectId);
    }

    private function ftpDelete($path)
    {
        $path = $this->clear($path);
        return @ftp_delete($this->_connectId, $path);
    }

    private function ftpGet($localFile, $remoteFile, $mode, $resumePos = 0)
    {
        $remoteFile = $this->clear($remoteFile);
        $localFile = $this->clear($localFile);
        $mode = intval($mode);
        $resumePos = intval($resumePos);
        return @ftp_get($this->_connectId, $localFile, $remoteFile, $mode, $resumePos);
    }

    private function ftpLogin($userName, $password)
    {
        $userName = $this->clear($userName);
        $password = str_replace(array("\n", "\r"), array("", ""), $password);
        return @ftp_login($this->_connectId, $userName, $password);
    }

    private function ftpPasv($pasv)
    {
        return @ftp_pasv($this->_connectId, $pasv ? true : false);
    }

    private function ftpChdir($directory)
    {
        $directory = $this->clear($directory);
        return @ftp_chdir($this->_connectId, $directory);
    }

    private function ftpSite($cmd)
    {
        $cmd = $this->clear($cmd);
        return @ftp_site($this->_connectId, $cmd);
    }

    private function ftpChmod($fileName, $mod = 511)
    {
        $fileName = $this->clear($fileName);

        if (function_exists("ftp_chmod")) {
            return @ftp_chmod($this->_connectId, $mod, $fileName);
        } else {
            return @ftp_site($this->_connectId, "CHMOD " . $mod . " " . $fileName);
        }
    }

    private function ftpPwd()
    {
        return @ftp_pwd($this->_connectId);
    }

    private function setError($code = 0)
    {
        $this->_error = $code;
    }
}
