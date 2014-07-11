<?php

class EmailBody extends ICModel
{
    public static function model($className = "EmailBody")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{email_body}}";
    }

    public function delBody($bodyIds, $archiveId = 0)
    {
        $table = sprintf("{{%s}}", $this->getTableName($archiveId));
        $bodys = Yii::app()->db->createCommand()->select("attachmentid")->from($table)->where("FIND_IN_SET(bodyid,'$bodyIds')")->queryAll();
        $attachIds = ConvertUtil::getSubByKey($bodys, "attachmentid");
        $attachId = StringUtil::filterStr(implode(",", $attachIds));

        if (!empty($attachId)) {
            AttachUtil::delAttach($attachId);
        }

        return Yii::app()->db->createCommand()->delete($table, "FIND_IN_SET(bodyid,'$bodyIds')");
    }

    public function handleEmailBody($data)
    {
        $data["toids"] = implode(",", StringUtil::getId($data["toids"]));
        $data["sendtime"] = TIMESTAMP;
        $data["isneedreceipt"] = (isset($data["isneedreceipt"]) ? 1 : 0);

        if (empty($data["isOtherRec"])) {
            $data["copytoids"] = $data["secrettoids"] = "";
        } else {
            $data["copytoids"] = implode(",", StringUtil::getId($data["copytoids"]));
            $data["secrettoids"] = implode(",", StringUtil::getId($data["secrettoids"]));
        }

        if (empty($data["isWebRec"])) {
            $data["towebmail"] = "";
        }

        if (!isset($data["fromwebmail"])) {
            $data["fromwebmail"] = "";
        }

        !empty($data["attachmentid"]) && ($data["attachmentid"] = StringUtil::filterStr($data["attachmentid"]));
        $data["size"] = EmailUtil::getEmailSize($data["content"], $data["attachmentid"]);
        return $data;
    }

    public function moveByBodyId($bodyIds, $source, $target)
    {
        $source = intval($source);
        $target = intval($target);

        if ($source != $target) {
            $db = Yii::app()->db->createCommand();
            $text = sprintf("REPLACE INTO {{%s}} SELECT * FROM {{%s}} WHERE bodyid IN ('%s')", $this->getTableName($target), $this->getTableName($source), implode(",", $bodyIds));
            $db->setText($text)->execute();
            return $db->delete(sprintf("{{\$s}}", $this->getTableName($source)), "FIND_IN_SET(bodyid,'" . implode(",", $bodyIds) . ")");
        } else {
            return false;
        }
    }

    public function getTableName($tableId = 0)
    {
        $tableId = intval($tableId);
        return $tableId ? "email_body_$tableId" : "email_body";
    }

    public function getTableStatus($tableId = 0)
    {
        return DatabaseUtil::getTableStatus($this->getTableName($tableId));
    }

    public function dropTable($tableId, $force = false)
    {
        $tableId = intval($tableId);

        if ($tableId) {
            $rel = DatabaseUtil::dropTable($this->getTableName($tableId), $force);

            if ($rel === 1) {
                return true;
            }
        }

        return false;
    }

    public function createTable($maxTableId)
    {
        if ($maxTableId) {
            return DatabaseUtil::cloneTable($this->getTableName(), $this->getTableName($maxTableId));
        } else {
            return false;
        }
    }
}
