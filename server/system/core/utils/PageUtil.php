<?php

class PageUtil extends CPagination
{
    /**
     * 静态pagination实例
     * @var mixed 
     */
    private static $instance;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function create($count, $pageSize = self::DEFAULT_PAGE_SIZE, $usingDb = true)
    {
        self::getInstance()->setPageSize($pageSize);
        self::getInstance()->setItemCount($count);

        if ($usingDb) {
            $criteria = new CDbCriteria(array("limit" => self::getInstance()->getLimit(), "offset" => self::getInstance()->getOffset()));
            self::getInstance()->applyLimit($criteria);
        }

        return self::$instance;
    }
}
