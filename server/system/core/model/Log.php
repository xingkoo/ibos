<?php

class Log
{
    public static function write($msg, $level = "action", $category = "module")
    {
        $message = CJSON::encode($msg);
        $logger = Ibos::getLogger();
        return $logger->log($message, $level, $category);
    }

    public static function fetchAllByList($tableId, $condition = "", $limit = 20, $offset = 0, $order = "logtime DESC")
    {
        $table = self::getTableName($tableId);
        $list = Ibos::app()->db->createCommand()->select("*")->from($table)->where($condition)->order($order)->limit($limit)->offset($offset)->queryAll();
        return $list;
    }

    public static function countByTableId($tableId = 0, $condition = "")
    {
        $table = self::getTableName($tableId);
        $count = Ibos::app()->db->createCommand()->select("count(id)")->from($table)->where($condition)->queryScalar();
        return intval($count);
    }

    public static function getLogTableId()
    {
        $tableId = CacheUtil::get("logtableid");

        if ($tableId === false) {
            $tableId = Ibos::app()->db->createCommand()->select("svalue")->from("{{setting}}")->where("skey = 'logtableid'")->queryScalar();
            CacheUtil::set("logtableid", intval($tableId));
        }

        return $tableId;
    }

    public static function getTableName($tableId = 0)
    {
        $tableId = intval($tableId);
        $year = date("Y");
        return 0 < $tableId ? "{{log_$tableId}}" : sprintf("{{log_%s}}", $year);
    }

    public static function getAllArchiveTableId()
    {
        $return = array();
        $db = Ibos::app()->db->createCommand();
        $prefix = $db->getConnection()->tablePrefix;
        $tables = $db->setText("SHOW TABLES LIKE '" . str_replace("_", "\_", $prefix . "log_%") . "'")->queryAll(false);

        if (!empty($tables)) {
            $tableArr = ConvertUtil::getSubByKey($tables, 0);
            $return = array_map(function ($archiveTable) {
                return substr($archiveTable, -4);
            }, $tableArr);
        }

        return $return;
    }
}
