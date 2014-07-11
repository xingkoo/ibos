<?php

class UserCount extends ICModel
{
    public static function model($className = "UserCount")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{user_count}}";
    }

    public function increase($uids, $creditArr)
    {
        $uids = StringUtil::iIntval((array) $uids, true);
        $sql = array();
        $allowKey = array("extcredits1", "extcredits2", "extcredits3", "extcredits4", "extcredits5", "oltime", "attachsize");

        foreach ($creditArr as $key => $value) {
            if (($value = intval($value)) && $value && in_array($key, $allowKey)) {
                $sql[] = "`$key`=`$key`+'$value'";
            }
        }

        if (!empty($sql)) {
            $sqlText = "UPDATE %s SET %s WHERE uid IN (%s)";
            return Yii::app()->db->createCommand()->setText(sprintf($sqlText, $this->tableName(), implode(",", $sql), StringUtil::iImplode($uids)))->execute();
        }
    }
}
