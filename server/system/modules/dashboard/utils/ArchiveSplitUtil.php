<?php

class ArchiveSplitUtil
{
    public static function updateTableIds($tableDriver)
    {
        $tableIds = $tableDriver["mainTable"]::model()->fetchTableIds();
        Setting::model()->updateSettingValueByKey($tableDriver["tableId"], $tableIds);
        CacheUtil::save($tableDriver["tableId"], $tableIds);
    }

    public static function getTableStatus($tableIds, $tableDriver)
    {
        $data = array();
        $data["main"] = $tableDriver["mainTable"]::model()->getTableStatus();
        $data["body"] = $tableDriver["bodyTable"]::model()->getTableStatus();
        $tables = array();

        foreach ($tableIds as $tableId) {
            if (!$tableId) {
                continue;
            }

            $tables[$tableId]["main"] = $tableDriver["mainTable"]::model()->getTableStatus($tableId);
            $tables[$tableId]["body"] = $tableDriver["bodyTable"]::model()->getTableStatus($tableId);
        }

        $data["tables"] = $tables;
        return $data;
    }

    public static function search($conditions, $tableDriver, $countOnly = false, $length = 20)
    {
        global $page;
        $list = array();
        $tableId = ($conditions["sourcetableid"] ? $conditions["sourcetableid"] : 0);
        $sql = $tableDriver["mainTable"]::model()->getSplitSearchContdition($conditions);
        $count = $tableDriver["mainTable"]::model()->countBySplitCondition($tableId, $sql);

        if ($countOnly) {
            return $count;
        } else {
            $page = PageUtil::create($count, $length);
            $list = $tableDriver["mainTable"]::model()->fetchAllBySplitCondition($tableId, $sql, $page->getOffset(), $page->getLimit());
        }

        return $list;
    }
}
