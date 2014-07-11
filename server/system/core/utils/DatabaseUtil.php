<?php

class DatabaseUtil
{
    const BACKUP_DIR = "data/backup";
    const OFFSET = 300;

    /**
     * 备份时要排除的表
     * @var array 
     */
    private static $exceptTables = array("session");
    /**
     * 开始处理行数。这个值在备份时会被重复赋值
     * @var integer 
     */
    private static $startRow = 0;
    /**
     * 完成状态标识
     * @var boolean 
     */
    private static $complete = true;

    public static function getdbPrefix()
    {
        return Ibos::app()->setting->get("config/db/tableprefix");
    }

    public static function arrayKeysTo($array, $key)
    {
        $return = array();

        foreach ($array as $val) {
            $return[] = $val[$key];
        }

        return $return;
    }

    public static function getDatabaseSize()
    {
        $tableList = self::getTablelist((string) self::getdbPrefix());
        $count = 0;

        foreach ($tableList as $table) {
            $count += $table["Data_length"];
        }

        $size = ConvertUtil::sizeCount($count);
        return $size;
    }

    public static function getTablelist($tablePrefix = "")
    {
        $arr = explode(".", $tablePrefix);
        $dbName = (isset($arr[1]) ? $arr[0] : "");
        $prefix = str_replace("_", "\_", $tablePrefix);
        $sqlAdd = ($dbName ? " FROM $dbName LIKE '$arr[1]%'" : "LIKE '$prefix%'");
        $tables = $table = array();
        $command = Ibos::app()->db->createCommand("SHOW TABLE STATUS $sqlAdd");
        $command->execute();
        $query = $command->query();

        foreach ($query as $table) {
            $table["Name"] = ($dbName ? "$dbName." : "") . $table["Name"];
            $tables[] = $table;
        }

        return $tables;
    }

    public static function getTableStatus($tableName, $formatSize = true)
    {
        $status = Ibos::app()->db->createCommand()->setText("SHOW TABLE STATUS LIKE '{{" . str_replace("_", "\_", $tableName) . "}}'")->queryRow();

        if ($formatSize) {
            $status["Data_length"] = ConvertUtil::sizeCount($status["Data_length"]);
            $status["Index_length"] = ConvertUtil::sizeCount($status["Index_length"]);
        }

        return $status;
    }

    public static function dropTable($tableName, $force = false)
    {
        $quoteTableName = "{{$tableName}}";

        if ($force) {
            Ibos::app()->db->createCommand()->dropTable($quoteTableName);
            return 1;
        } else {
            $tableInfo = self::getTableStatus($tableName);

            if ($tableInfo["Rows"] == 0) {
                Ibos::app()->db->createCommand()->dropTable($quoteTableName);
                return 1;
            } else {
                return -1;
            }
        }
    }

    public static function cloneTable($prototype, $target)
    {
        $db = Ibos::app()->db->createCommand();
        $prefix = Ibos::app()->db->tablePrefix;
        $prototype = $prefix . $prototype;
        $target = $prefix . $target;
        $db->setText("SET SQL_QUOTE_SHOW_CREATE = 0")->execute();
        $create = $db->setText("SHOW CREATE TABLE $prototype")->queryRow();
        $createSql = $create["Create Table"];
        $createSql = preg_replace("/^([^\(]*)" . $prototype . "/", "\$1" . $target, $createSql);
        return $db->setText($createSql)->execute();
    }

    public static function getSqlDumpTableStruct($table, $compat, $dumpCharset, $charset = "")
    {
        $command = Ibos::app()->db->createCommand();
        $rows = $command->setText("SHOW CREATE TABLE $table")->queryRow();

        if ($rows) {
            $tableDump = "DROP TABLE IF EXISTS $table;\n";
        } else {
            return "";
        }

        if (strpos($table, ".") !== false) {
            $tableName = substr($table, strpos($table, ".") + 1);
            $rows["Create Table"] = str_replace("CREATE TABLE $tableName", "CREATE TABLE " . $table, $rows["Create Table"]);
        }

        $tableDump .= $rows["Create Table"];
        $dbVersion = Ibos::app()->db->getServerVersion();
        if (($compat == "MYSQL41") && ($dbVersion < "4.1")) {
            $tableDump = preg_replace("/TYPE\=(.+)/", "ENGINE=\1 DEFAULT CHARSET=" . $dumpCharset, $tableDump);
        }

        if (("4.1" < $dbVersion) && $charset) {
            $tableDump = preg_replace("/(DEFAULT)*\s*CHARSET=.+/", "DEFAULT CHARSET=" . $charset, $tableDump);
        }

        $tableStatus = $command->setText("SHOW TABLE STATUS LIKE '$table'")->queryRow();
        $tableDump .= ($tableStatus["Auto_increment"] ? " AUTO_INCREMENT={$tableStatus["Auto_increment"]}" : "") . ";\n\n";
        if (($compat == "MYSQL40") && ("4.1" <= $dbVersion) && ($dbVersion < "5.1")) {
            if ($tableStatus["Auto_increment"] != "") {
                $temppos = strpos($tableDump, ",");
                $tableDump = substr($tableDump, 0, $temppos) . " auto_increment" . substr($tableDump, $temppos);
            }

            if ($tableStatus["Engine"] == "MEMORY") {
                $tableDump = str_replace("TYPE=MEMORY", "TYPE=HEAP", $tableDump);
            }
        }

        return $tableDump;
    }

    public static function databaseBackup()
    {
        $config = Ibos::app()->setting->toArray();
        $command = Ibos::app()->db->createCommand("SET SQL_QUOTE_SHOW_CREATE=0");
        $command->execute();
        $fileName = EnvUtil::getRequest("filename");
        $hasDangerFileName = preg_match("/(\.)(exe|jsp|asp|aspx|cgi|fcgi|pl)(\.|$)/i", $fileName);
        if (!$fileName || (bool) $hasDangerFileName) {
            return array("type" => "error", "msg" => Ibos::lang("Database export filename invalid", "dashboard.default"));
        }

        $tablePrefix = $config["config"]["db"]["tableprefix"];
        $dbCharset = $config["config"]["db"]["charset"];
        $type = EnvUtil::getRequest("backuptype");

        if ($type == "all") {
            $tableList = self::getTablelist($tablePrefix);
            $tables = self::arrayKeysTo($tableList, "Name");
        } elseif ($type == "custom") {
            $tables = array();

            if (is_null(EnvUtil::getRequest("dbSubmit"))) {
                $tables = Setting::model()->fetchSettingValueByKey("custombackup");
                $tables = unserialize($tables);
            } else {
                $customTables = EnvUtil::getRequest("customtables");
                Setting::model()->updateSettingValueByKey("custombackup", is_null($customTables) ? "" : $customTables);
                $tables = &$customTables;
            }

            if (!is_array($tables) || empty($tables)) {
                return array("type" => "error", "msg" => Ibos::lang("Database export custom invalid", "dashboard.default"));
            }
        }

        $time = date("Y-m-d H:i:s", TIMESTAMP);
        $volume = intval(EnvUtil::getRequest("volume")) + 1;
        $method = EnvUtil::getRequest("method");
        $encode = base64_encode("{$config["timestamp"]}," . VERSION . ",$type,$method,$volume,$tablePrefix,$dbCharset");
        $idString = "# Identify: " . $encode . "\n";
        $sqlCharset = EnvUtil::getRequest("sqlcharset");
        $sqlCompat = EnvUtil::getRequest("sqlcompat");
        $dbVersion = Ibos::app()->db->getServerVersion();
        $useZip = EnvUtil::getRequest("usezip");
        $useHex = EnvUtil::getRequest("usehex");
        $extendIns = EnvUtil::getRequest("extendins");
        $sizeLimit = EnvUtil::getRequest("sizelimit");
        $dumpCharset = (!empty($sqlCharset) ? $sqlCharset : str_replace("-", "", CHARSET));
        $isNewSqlVersion = ("4.1" < $dbVersion) && (!is_null($sqlCompat) || ($sqlCompat == "MYSQL41"));
        $setNames = (!empty($sqlCharset) && $isNewSqlVersion ? "SET NAMES '$dumpCharset';\n\n" : "");

        if ("4.1" < $dbVersion) {
            if ($sqlCharset) {
                $command->setText("SET NAMES `$sqlCharset`")->execute();
            }

            if ($sqlCompat == "MYSQL40") {
                $command->setText("SET SQL_MODE='MYSQL40'")->execute();
            } elseif ($sqlCompat == "MYSQL41") {
                $command->setText("SET SQL_MODE=''")->execute();
            }
        }

        if (!is_dir(self::BACKUP_DIR)) {
            FileUtil::makeDir(self::BACKUP_DIR, 511);
        }

        $backupFileName = self::BACKUP_DIR . "/" . str_replace(array("/", "\\", ".", "'"), "", $fileName);

        if ($method == "multivol") {
            $sqlDump = "";
            $tableId = intval(EnvUtil::getRequest("tableid"));
            $startFrom = intval(EnvUtil::getRequest("startfrom"));
            if (!$tableId && ($volume == 1)) {
                foreach ($tables as $table) {
                    $sqlDump .= self::getSqlDumpTableStruct($table, $sqlCompat, $sqlCharset, $dumpCharset);
                }
            }

            for (self::$complete = true; (strlen($sqlDump) + 500) < ($sizeLimit * 1000); $tableId++) {
                $sqlDump .= self::sqlDumpTable($tables[$tableId], $extendIns, $sizeLimit, $useHex, $startFrom, strlen($sqlDump));

                if (self::$complete) {
                    $startFrom = 0;
                }
            }

            $dumpFile = $backupFileName . "-%s.sql";
            !self::$complete && $tableId--;

            if (trim($sqlDump)) {
                $sqlDump = "$idString# <?php exit();?>\n# IBOS Multi-Volume Data Dump Vol.{$volume}\n# Version: IBOS {$config["version"]}\n# Time: {$time}\n# Type: {$type}\n# Table Prefix: {$tablePrefix}\n#\n# IBOS Home: http://www.ibos.com.cn\n# Please visit our website for newest infomation about IBOS\n# --------------------------------------------------------\n\n\n$setNames" . $sqlDump;
                $dumpFileName = sprintf($dumpFile, $volume);
                @$fp = fopen($dumpFileName, "wb");
                @flock($fp, 2);

                if (@!fwrite($fp, $sqlDump)) {
                    @fclose($fp);
                    return array("type" => "error", "msg" => Ibos::lang("Database export file invalid", "dashboard.default"), "url" => "");
                } else {
                    fclose($fp);

                    if ($useZip == 2) {
                        $fp = fopen($dumpFileName, "r");
                        $content = @fread($fp, filesize($dumpFileName));
                        fclose($fp);
                        $zip = new Zip();
                        $zip->addFile($content, basename($dumpFileName));
                        $fp = fopen(sprintf($backupFileName . "-%s.zip", $volume), "w");

                        if (@fwrite($fp, $zip->file()) !== false) {
                            @unlink($dumpFileName);
                        }

                        fclose($fp);
                    }

                    unset($sqlDump);
                    unset($zip);
                    unset($content);
                    $param = array("setup" => 1, "backuptype" => rawurlencode($type), "filename" => rawurlencode($fileName), "method" => "multivol", "sizelimit" => rawurlencode($sizeLimit), "volume" => rawurlencode($volume), "tableid" => rawurlencode($tableId), "startfrom" => rawurlencode(self::$startRow), "extendins" => rawurlencode($fileName), "sqlcharset" => rawurlencode($sqlCharset), "sqlcompat" => rawurlencode($sqlCompat), "usehex" => $useHex, "usezip" => $useZip);
                    $url = Ibos::app()->urlManager->createUrl("dashboard/database/backup", $param);
                    return array("type" => "success", "msg" => Ibos::lang("Database export multivol redirect", "dashboard.default", array("volume" => $volume)), "url" => $url);
                }
            } else {
                $volume--;

                if ($useZip == 1) {
                    $zip = new Zip();
                    $zipFileName = $backupFileName . ".zip";
                    $unlinks = array();

                    for ($i = 1; $i <= $volume; $i++) {
                        $filename = sprintf($dumpFile, $i);
                        $fp = fopen($filename, "r");
                        $content = @fread($fp, filesize($filename));
                        fclose($fp);
                        $zip->addFile($content, basename($filename));
                        $unlinks[] = $filename;
                    }

                    $fp = fopen($zipFileName, "w");

                    if (@fwrite($fp, $zip->file()) !== false) {
                        foreach ($unlinks as $link) {
                            @unlink($link);
                        }
                    } else {
                        return array("type" => "success", "msg" => Ibos::lang("Database export multivol succeed", "dashboard.default", array("volume" => $volume)), "url" => Ibos::app()->urlManager->createUrl("dashboard/database/restore"));
                    }

                    unset($sqlDump);
                    unset($zip);
                    unset($content);
                    fclose($fp);
                    $filename = $zipFileName;
                    return array(
                    "type"  => "success",
                    "msg"   => Ibos::lang("Database export zip succeed", "dashboard.default"),
                    "param" => array("autoJump" => false)
                    );
                } else {
                    return array("type" => "success", "msg" => Ibos::lang("Database export multivol succeed", "dashboard.default", array("volume" => $volume)), "url" => Ibos::app()->urlManager->createUrl("dashboard/database/restore"));
                }
            }
        } else {
            $tablesstr = "";

            foreach ($tables as $table) {
                $tablesstr .= "\"" . $table . "\" ";
            }

            $db = $config["config"]["db"];
            $query = $command->setText("SHOW VARIABLES LIKE 'basedir'")->queryRow();
            $mysqlBase = $query["Value"];
            $dumpFile = addslashes(dirname(dirname(__FILE__))) . "/" . $backupFileName . ".sql";
            @unlink($dumpFile);
            $mysqlBin = ($mysqlBase == "/" ? "" : addslashes($mysqlBase) . "bin/");
            shell_exec($mysqlBin . "mysqldump --force --quick " . ("4.1" < $dbVersion ? "--skip-opt --create-options" : "-all") . " --add-drop-table" . (EnvUtil::getRequest("extendins") == 1 ? " --extended-insert" : "") . "" . (("4.1" < $dbVersion) && ($sqlCompat == "MYSQL40") ? " --compatible=mysql40" : "") . " --host=\"" . $db["host"] . ($db["port"] ? (is_numeric($db["port"]) ? " --port=" . $db["port"] : " --socket=\"" . $db["port"] . "\"") : "") . "\" --user=\"" . $db["username"] . "\" --password=\"" . $db["password"] . "\" \"" . $db["dbname"] . "\" " . $tablesstr . " > " . $dumpFile);

            if (@file_exists($dumpFile)) {
                if ($useZip) {
                    $zip = new Zip();
                    $zipfilename = $backupFileName . ".zip";
                    $fp = fopen($dumpFile, "r");
                    $content = @fread($fp, filesize($dumpFile));
                    fclose($fp);
                    $zip->addFile($idString . "# <?php exit();?>\n " . $setNames . "\n #" . $content, basename($dumpFile));
                    $fp = fopen($zipfilename, "w");
                    @fwrite($fp, $zip->file());
                    fclose($fp);
                    @unlink($dumpFile);
                    $filename = $backupFileName . ".zip";
                    unset($sqlDump);
                    unset($zip);
                    unset($content);
                    return array("type" => "success", "msg" => Ibos::lang("Database export zip succeed", "dashboard.default"), "url" => Ibos::app()->urlManager->createUrl("dashboard/database/restore"));
                } else {
                    if (@is_writeable($dumpFile)) {
                        $fp = fopen($dumpFile, "rb+");
                        @fwrite($fp, $idString . "# <?php exit();?>\n " . $setNames . "\n #");
                        fclose($fp);
                    }

                    $filename = $backupFileName . ".sql";
                    return array("type" => "success", "msg" => Ibos::lang("Database export succeed", "dashboard.default"), "param" => Ibos::app()->urlManager->createUrl("dashboard/database/restore"));
                }
            } else {
                return array("type" => "error", "msg" => Ibos::lang("Database shell fail", "dashboard.default"));
            }
        }
    }

    public static function sqlDumpTable($table, $extendIns, $sizeLimit, $useHex = true, $startFrom = 0, $currentSize = 0)
    {
        $offset = self::OFFSET;
        $tableDump = "";
        $command = Ibos::app()->db->createCommand();
        $tableFields = $command->setText("SHOW FULL COLUMNS FROM `$table`")->queryAll();

        if (!$tableFields) {
            $useHex = false;
        }

        if (!in_array($table, self::getExceptTables())) {
            $tableDumped = 0;
            $numRows = $offset;
            $firstField = $tableFields[0];

            if ($extendIns == "0") {
                while ((($currentSize + strlen($tableDump) + 500) < ($sizeLimit * 1000)) && ($numRows == $offset)) {
                    if ($firstField["Extra"] == "auto_increment") {
                        $selectSql = "SELECT * FROM `$table` WHERE `{$firstField["Field"]}` > $startFrom ORDER BY `{$firstField["Field"]}` LIMIT $offset";
                    } else {
                        $selectSql = "SELECT * FROM `$table` LIMIT $startFrom, $offset";
                    }

                    $tableDumped = 1;
                    $numRows = $command->setText($selectSql)->execute();
                    $rows = $command->queryAll();

                    foreach ($rows as $row) {
                        $comma = $t = "";
                        $index = 0;

                        foreach ($row as $value) {
                            $t .= $comma . ($useHex && !empty($value) && (StringUtil::strExists($tableFields[$index]["Type"], "char") || StringUtil::strExists($tableFields[$index]["Type"], "text")) ? "0x" . bin2hex($value) : "'" . addslashes($value) . "'");
                            $comma = ",";
                            $index++;
                        }

                        if ((strlen($t) + $currentSize + strlen($tableDump) + 500) < ($sizeLimit * 1000)) {
                            if ($firstField["Extra"] == "auto_increment") {
                                $startFrom = array_shift($row);
                            } else {
                                $startFrom++;
                            }

                            $tableDump .= "INSERT INTO `$table` VALUES ( $t);\n";
                        } else {
                            self::$complete = false;
                            break 2;
                        }
                    }
                }
            } else {
                while ((($currentSize + strlen($tableDump) + 500) < ($sizeLimit * 1000)) && ($numRows == $offset)) {
                    if ($firstField["Extra"] == "auto_increment") {
                        $selectSql = "SELECT * FROM `$table` WHERE {$firstField["Field"]} > $startFrom LIMIT $offset";
                    } else {
                        $selectSql = "SELECT * FROM `$table` LIMIT $startFrom, $offset";
                    }

                    $tableDumped = 1;
                    $numRows = $command->setText($selectSql)->execute();
                    $rows = $command->queryAll();

                    if ($numRows) {
                        $t1 = $comma1 = "";

                        foreach ($rows as $row) {
                            $t2 = $comma2 = "";
                            $index = 0;

                            foreach ($row as $value) {
                                $t2 .= $comma2 . ($useHex && !empty($value) && (StringUtil::strExists($tableFields[$index]["Type"], "char") || StringUtil::strExists($tableFields[$index]["Type"], "text")) ? "0x" . bin2hex($value) : "'" . addslashes($value) . "'");
                                $comma2 = ",";
                                $index++;
                            }

                            if ((strlen($t1) + $currentSize + strlen($tableDump) + 500) < ($sizeLimit * 1000)) {
                                if ($firstField["Extra"] == "auto_increment") {
                                    $startFrom = array_shift($row);
                                } else {
                                    $startFrom++;
                                }

                                $t1 .= "$comma1 ($t2)";
                                $comma1 = ",";
                            } else {
                                $tableDump .= "INSERT INTO `$table` VALUES $t1;\n";
                                self::$complete = false;
                                break 2;
                            }
                        }

                        $tableDump .= "INSERT INTO `$table` VALUES $t1;\n";
                    }
                }
            }

            self::$startRow = $startFrom;
            $tableDump .= "\n";
        }

        return $tableDump;
    }

    public static function getBackupList()
    {
        $exportLog = $exportSize = $exportZipLog = array();

        if (is_dir(self::BACKUP_DIR)) {
            $dir = dir(self::BACKUP_DIR);

            while ($entry = $dir->read()) {
                $entry = self::BACKUP_DIR . "/" . $entry;

                if (is_file($entry)) {
                    if (preg_match("/\.sql$/i", $entry)) {
                        $fileSize = filesize($entry);
                        $fp = fopen($entry, "rb");
                        $identify = explode(",", base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\1", fgets($fp, 256))));
                        fclose($fp);
                        $key = preg_replace("/^(.+?)(\-\d+)\.sql$/i", "\1", basename($entry));
                        $exportLog[$key][$identify[4]] = array("version" => $identify[1], "type" => $identify[2], "method" => $identify[3], "volume" => $identify[4], "filename" => $entry, "dateline" => filemtime($entry), "size" => $fileSize);

                        if (isset($exportSize[$key])) {
                            $exportSize[$key] += $fileSize;
                        } else {
                            $exportSize[$key] = $fileSize;
                        }
                    } else if (preg_match("/\.zip$/i", $entry)) {
                        $fileSize = filesize($entry);
                        $exportZipLog[] = array("type" => "zip", "filename" => $entry, "size" => filesize($entry), "dateline" => filemtime($entry));
                    }
                }
            }

            $dir->close();
        }

        return array("exportLog" => $exportLog, "exportSize" => $exportSize, "exportZipLog" => $exportZipLog);
    }

    public static function getOptimizeTable()
    {
        $tableType = ("4.1" < Ibos::app()->db->getServerVersion() ? "Engine" : "Type");
        $lists = self::getTablelist(self::getdbPrefix());
        $tables = array();

        foreach ($lists as $list) {
            if ($list["Data_free"] && ($list[$tableType] == "MyISAM")) {
                $list["checked"] = ($list[$tableType] == "MyISAM" ? "checked" : "disabled");
                $list["tableType"] = $tableType;
                $tables[] = $list;
            }
        }

        return $tables;
    }

    public static function optimize($tables)
    {
        $command = Ibos::app()->db->createCommand();

        foreach ($tables as $table) {
            $command->setText("OPTIMIZE TABLE $table")->execute();
        }

        return true;
    }

    public static function getBackupDir()
    {
        return self::BACKUP_DIR;
    }

    private static function getExceptTables()
    {
        $tables = array();
        $prefix = self::getdbPrefix();

        foreach (self::$exceptTables as $table) {
            $tables[] = $prefix . $table;
        }

        return $tables;
    }

    public static function syncTableStruct($sql, $version, $dbCharset)
    {
        if (strpos(trim(substr($sql, 0, 18)), "CREATE TABLE") === false) {
            return $sql;
        }

        $sqlVersion = (strpos($sql, "ENGINE=") === false ? false : true);

        if ($sqlVersion === $version) {
            $pattern = array("/ character set \w+/i", "/ collate \w+/i", "/DEFAULT CHARSET=\w+/is");
            $replacement = array("", "", "DEFAULT CHARSET=$dbCharset");
            return $sqlVersion && $dbCharset ? preg_replace($pattern, $replacement, $sql) : $sql;
        }

        if ($version) {
            $pattern = array("/TYPE=HEAP/i", "/TYPE=(\w+)/is");
            $replacement = array("ENGINE=MEMORY DEFAULT CHARSET=$dbCharset", "ENGINE=\1 DEFAULT CHARSET=$dbCharset");
            return preg_replace($pattern, $replacement, $sql);
        } else {
            $pattern = array("/character set \w+/i", "/collate \w+/i", "/ENGINE=MEMORY/i", "/\s*DEFAULT CHARSET=\w+/is", "/\s*COLLATE=\w+/is", "/ENGINE=(\w+)(.*)/is");
            $replacement = array("", "", "ENGINE=HEAP", "", "", "TYPE=\1\2");
            return preg_replace($pattern, $replacement, $sql);
        }
    }
}
