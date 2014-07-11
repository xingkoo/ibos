<?php

class Stamp extends ICModel
{
    const STAMP_PATH = "data/stamp/";

    /**
     * 允许缓存
     * @var boolean 
     */
    protected $allowCache = true;

    public static function model($className = "Stamp")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{stamp}}";
    }

    public function getMaxSort()
    {
        $record = $this->fetch(array("order" => "sort DESC", "select" => "sort"));
        return !empty($record) ? intval($record["sort"]) : 0;
    }

    public function fetchStampById($id)
    {
        $stamp = $this->fetchByPk($id);
        return $stamp["stamp"];
    }

    public function fetchIconById($id)
    {
        $stamp = $this->fetchByPk($id);
        return $stamp["icon"];
    }

    public function fetchByPk($pk)
    {
        static $stamps = array();

        if (!isset($stamps[$pk])) {
            $stamps[$pk] = parent::fetchByPk($pk);
        }

        return $stamps[$pk];
    }

    public function delImg($id, $index = "")
    {
        $stamp = $this->fetchByPk($id);

        if (!empty($stamp[$index])) {
            if (FileUtil::fileExists(self::STAMP_PATH . $stamp[$index])) {
                FileUtil::deleteFile(self::STAMP_PATH . $stamp[$index]);
            }
        }
    }

    public function deleteByIds($ids)
    {
        $id = $files = array();

        foreach ($ids as $removeId) {
            $record = $this->fetchByPk($removeId);

            if (!empty($record["stamp"])) {
                $files[] = $record["stamp"];
            }

            if (!empty($record["icon"])) {
                $files[] = $record["icon"];
            }

            $id[] = $record["id"];
        }

        $this->deleteByPk($id);

        foreach ($files as $file) {
            if (FileUtil::fileExists(self::STAMP_PATH . $file)) {
                FileUtil::deleteFile(self::STAMP_PATH . $file);
            }
        }
    }

    public function fetchAllIds()
    {
        $stamps = $this->fetchAll();
        $ids = ConvertUtil::getSubByKey($stamps, "id");
        return $ids;
    }
}
