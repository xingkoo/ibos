<?php

class LoginTemplate extends ICModel
{
    const BG_PATH = "data/login/";

    public static function model($className = "LoginTemplate")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{login_template}}";
    }

    public function fetchByPk($pk)
    {
        static $tpls = array();

        if (!isset($tpls[$pk])) {
            $tpls[$pk] = parent::fetchByPk($pk);
        }

        return $tpls[$pk];
    }

    public function delImg($id)
    {
        $tpl = $this->fetchByPk($id);

        if (FileUtil::fileExists(self::BG_PATH . $tpl["image"])) {
            FileUtil::deleteFile(self::BG_PATH . $tpl["image"]);
        }
    }

    public function deleteByIds($ids)
    {
        $id = $files = array();

        foreach ($ids as $removeId) {
            $record = $this->fetchByPk($removeId);

            if (!empty($record["image"])) {
                $files[] = $record["image"];
            }

            $id[] = $record["id"];
        }

        $this->deleteByPk($id);

        foreach ($files as $file) {
            if (FileUtil::fileExists(self::BG_PATH . $file)) {
                FileUtil::deleteFile(self::BG_PATH . $file);
            }
        }
    }
}
