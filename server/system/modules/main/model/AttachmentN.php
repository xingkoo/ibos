<?php

class AttachmentN
{
    /**
     * 实例
     * @var mixed 
     */
    private static $_instance;

    public static function model()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getTable($tableId)
    {
        if (!is_numeric($tableId)) {
            list($idType, $id) = explode(":", $tableId);

            if ($idType == "aid") {
                $aid = StringUtil::iIntval($id);
                $tableId = Ibos::app()->db->createCommand()->select("tableid")->from("{{attachment}}")->where("aid='$aid'")->queryScalar();
            } elseif ($idType == "rid") {
                $rid = (string) $id;
                $tableId = StringUtil::iIntval($rid[strlen($rid) - 1]);
            }
        }

        if ((0 <= $tableId) && ($tableId < 10)) {
            return sprintf("{{attachment_%d}}", intval($tableId));
        } elseif ($tableId == 127) {
            return "{{attachment_unused}}";
        } else {
            throw new CException("Table attachment_" . $tableId . " has not exists");
        }
    }

    public function fetch($tableId, $aid, $isImage = false)
    {
        $isImage = ($isImage === false ? "" : " AND isimage = 1");
        $sqlText = sprintf("SELECT * FROM %s WHERE aid = %d %s", $this->getTable($tableId), $aid, $isImage);
        return !empty($aid) ? Ibos::app()->db->createCommand()->setText($sqlText)->queryRow() : array();
    }

    public function add($tableId, $attrs, $returnID = false)
    {
        $rs = Ibos::app()->db->createCommand()->insert($this->getTable($tableId), $attrs);

        if ($returnID) {
            return Ibos::app()->db->getLastInsertID();
        } else {
            return $rs;
        }
    }

    public function deleteByPk($tableID, $aid)
    {
        return Ibos::app()->db->createCommand()->delete($this->getTable($tableID), "aid = $aid");
    }
}
