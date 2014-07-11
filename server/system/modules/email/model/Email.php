<?php

class Email extends ICModel
{
    public static function model($className = "Email")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{email}}";
    }

    public function fetchPrev($id, $uid, $fid, $archiveId = 0)
    {
        $condition = sprintf("e.fid = %d AND toid = %d AND eb.issend = 1 AND e.isdel = 0 AND e.emailid > %d", $fid, $uid, $id);
        $order = "emailid ASC";
        return $this->getSiblingsByCondition($condition, $order, $archiveId);
    }

    public function fetchNext($id, $uid, $fid, $archiveId = 0)
    {
        $condition = sprintf("e.fid = %d AND toid = %d AND eb.issend = 1 AND e.isdel = 0 AND e.emailid < %d", $fid, $uid, $id);
        $order = "emailid DESC";
        return $this->getSiblingsByCondition($condition, $order, $archiveId);
    }

    public function fetchById($id, $archiveId = 0)
    {
        $mainTable = $this->getTableName($archiveId);
        $bodyTable = EmailBody::model()->getTableName($archiveId);
        $email = Ibos::app()->db->createCommand()->select("*")->from("{{" . $mainTable . "}} e")->leftJoin("{{" . $bodyTable . "}} eb", "e.bodyid = eb.bodyid")->where("emailid = " . intval($id))->queryRow();
        return is_array($email) ? $email : array();
    }

    public function fetchAllBodyIdByKeywordFromAttach($keyword, $whereAdd = "1", $queryArchiveId = 0)
    {
        $kwBodyIds = array();
        $queryParam = "uid = " . Ibos::app()->user->uid;
        $kwAttachments = Attachment::model()->fetchAllByKeywordFileName($keyword, $queryParam);

        if (!empty($kwAttachments)) {
            $kwAids = array_keys($kwAttachments);
            $emailData = $this->fetchAllByArchiveIds("e.*,eb.*,", "$whereAdd AND attachmentid!=''", $queryArchiveId);

            foreach ($emailData as $email) {
                if (array_intersect($kwAids, explode(",", $email["attachmentid"]))) {
                    $kwBodyIds[] = $email["bodyid"];
                }
            }
        }

        return $kwBodyIds;
    }

    public function setAllRead($uid)
    {
        return $this->setField("isread", 1, "toid = " . intval($uid));
    }

    public function setRead($id)
    {
        return $this->setField("isread", 1, "emailid = " . intval($id));
    }

    public function setField($field, $value, $conditions = "")
    {
        return $this->updateAll(array($field => $value), $conditions);
    }

    public function send($bodyId, $bodyData, $inboxId = EmailBaseController::INBOX_ID)
    {
        $toids = $bodyData["toids"] . "," . $bodyData["copytoids"] . "," . $bodyData["secrettoids"];
        $toid = StringUtil::filterStr($toids);

        foreach (explode(",", $toid) as $uid) {
            $email = array("toid" => $uid, "fid" => $inboxId, "bodyid" => $bodyId);
            $newId = $this->add($email, true);
            $config = array("{sender}" => Ibos::app()->user->realname, "{subject}" => $bodyData["subject"], "{url}" => Ibos::app()->urlManager->createUrl("email/content/show", array("id" => $newId)), "{content}" => Ibos::app()->getController()->renderPartial("application.modules.email.views.remindcontent", array("body" => $bodyData), true));
            Notify::model()->sendNotify($uid, "email_message", $config);
        }
    }

    public function recall($emailIds, $uid)
    {
        $emails = $this->fetchAllByPk(explode(",", $emailIds));
        $ids = array();

        foreach ($emails as $email) {
            if (!$email["isread"]) {
                $ids[] = $email["emailid"];
            }
        }

        if (!empty($ids)) {
            return $this->completelyDelete($ids, $uid);
        }

        return false;
    }

    public function completelyDelete($emailIds, $uid, $archiveId = 0)
    {
        $isSuccess = 0;
        $emailIds = (is_array($emailIds) ? $emailIds : array($emailIds));
        $mainTable = sprintf("{{%s}}", $this->getTableName($archiveId));
        $bodyTable = sprintf("{{%s}}", EmailBody::model()->getTableName($archiveId));
        $bodyIds = Ibos::app()->db->createCommand()->select("bodyid")->from($mainTable)->where("FIND_IN_SET(emailid,'" . implode(",", $emailIds) . "')")->queryAll();

        if ($bodyIds) {
            $bodyIds = ConvertUtil::getSubByKey($bodyIds, "bodyid");
        }

        foreach ($bodyIds as $i => $bodyId) {
            $body = Ibos::app()->db->createCommand()->select("fromid,attachmentid")->from($bodyTable)->where("bodyid = $bodyId AND fromid = $uid")->queryRow();
            if ($body || !isset($emailIds[$i])) {
                if (isset($emailIds[$i])) {
                    $readerRows = Ibos::app()->db->createCommand()->select("bodyid")->from($mainTable)->where("emailid = $emailIds[$i] AND isread != 0 AND toid != $uid")->queryRow();
                } else {
                    $readerRows = false;
                }

                if ($readerRows) {
                    if (Ibos::app()->db->createCommand()->update($bodyTable, array("issenderdel" => 1), "bodyid = " . $bodyId)) {
                        $isSuccess = 1;
                    }
                } else {
                    if (isset($emailIds[$i])) {
                        $nextStep = Ibos::app()->db->createCommand()->delete($mainTable, "emailid = " . $emailIds[$i]);
                    } else {
                        Ibos::app()->db->createCommand()->delete($bodyTable, "bodyid = " . $bodyId);
                        $nextStep = true;
                    }

                    if ($nextStep) {
                        if ($body["attachmentid"] !== "") {
                            AttachUtil::delAttach($body["attachmentid"]);
                        }

                        $isSuccess = 1;
                    }
                }
            } else {
                $lastRows = Ibos::app()->db->createCommand()->select("toid")->from($mainTable)->where("bodyid = $bodyId AND toid != $uid")->queryRow();

                if (!$lastRows) {
                    Ibos::app()->db->createCommand()->delete($mainTable, "emailid = " . $emailIds[$i]);
                    $attachmentId = Ibos::app()->db->createCommand()->select("attachmentid")->from($bodyTable)->where("bodyid = " . $bodyId)->queryScalar();
                    if ($attachmentId && ($attachmentId !== "")) {
                        AttachUtil::delAttach($attachmentId);
                    }

                    $isSuccess++;
                } else {
                    Ibos::app()->db->createCommand()->delete($mainTable, "emailid = $emailIds[$i] AND toid = $uid");
                    $isSuccess++;
                }
            }
        }

        return $isSuccess;
    }

    public function fetchAllEmailIdsByFolderId($fid, $uid)
    {
        $record = $this->fetchAllByAttributes(array("fid" => $fid, "toid" => $uid), array("select" => "emailid"));
        $emailIds = ConvertUtil::getSubByKey($record, "emailid");
        return $emailIds;
    }

    public function fetchAllByArchiveIds($field = "*", $conditions = "", $archiveId = 0, $tableAlias = array("e", "eb"), $offset = null, $length = null, $order = SORT_DESC, $sort = "sendtime")
    {
        $aidList = (is_array($archiveId) ? $archiveId : array($archiveId));
        $emailData = array();
        $queryTable = array();

        foreach ($aidList as $aid) {
            $emailTableName = $this->getTableName($aid);
            $emailbodyTableName = EmailBody::model()->getTableName($aid);

            if (in_array($emailTableName, $queryTable)) {
                continue;
            }

            $list = Ibos::app()->db->createCommand()->select($field)->from(sprintf("{{%s}} %s", $emailTableName, $tableAlias[0]))->leftJoin(sprintf("{{%s}} %s", $emailbodyTableName, $tableAlias[1]), "$tableAlias[0].bodyid = $tableAlias[1].bodyid")->where($conditions)->queryAll();
            $sortRefer = array();
            $emailFetchData = array();

            foreach ($list as $email) {
                $email["aid"] = $aid;
                $sortRefer[$email["emailid"]] = $email[$sort];
                $emailFetchData[] = $email;
            }

            $queryTable[] = $emailTableName;
        }

        foreach ($emailFetchData as $emailInfo) {
            $emailData[$emailInfo["emailid"]] = $emailInfo;
        }

        array_multisort($sortRefer, $order, $emailData);
        if (!is_null($offset) && !is_null($length)) {
            $emailData = array_slice($emailData, $offset, $length, false);
        }

        return $emailData;
    }

    public function fetchAllByListParam($operation, $uid = 0, $fid = 0, $archiveId = 0, $limit = 10, $offset = 0, $subOp = "")
    {
        $param = $this->getListParam($operation, $uid, $fid, $archiveId, false, $subOp);

        if (empty($param["field"])) {
            $param["field"] = "e.emailid, e.isread, eb.fromid, eb.subject, eb.sendtime, eb.fromwebmail,eb.important, e.ismark, eb.attachmentid";
        }

        if (empty($param["order"])) {
            $param["order"] = "eb.sendtime DESC";
        }

        $sql = "SELECT %s FROM %s WHERE %s";

        if (!empty($param["group"])) {
            $sql .= " GROUP BY " . $param["group"];
        }

        $sql .= " ORDER BY {$param["order"]} LIMIT $offset,$limit";
        $db = Ibos::app()->db->createCommand();
        $list = $db->setText(sprintf($sql, $param["field"], $param["table"], $param["condition"]))->queryAll();

        foreach ($list as &$value) {
            if (!empty($value["fromid"])) {
                $value["fromuser"] = User::model()->fetchRealnameByUid($value["fromid"]);
            } else {
                $value["fromuser"] = $value["fromwebmail"];
            }
        }

        return (array) $list;
    }

    public function countUnreadByListParam($operation, $uid = 0, $fid = 0, $archiveId = 0, $subOp = "")
    {
        $param = $this->getListParam($operation, $uid, $fid, $archiveId, true, $subOp);
        return $this->countListParam($param);
    }

    public function countByListParam($operation, $uid = 0, $fid = 0, $archiveId = 0, $subOp = "")
    {
        $param = $this->getListParam($operation, $uid, $fid, $archiveId, false, $subOp);
        return $this->countListParam($param);
    }

    private function countListParam($param)
    {
        if (empty($param["field"])) {
            $param["field"] = "emailid";
        }

        if (empty($param["order"])) {
            $param["order"] = "eb.sendtime DESC";
        }

        $sql = "SELECT COUNT(%s) as count FROM %s WHERE %s";

        if (!empty($param["group"])) {
            $sql .= " GROUP BY " . $param["group"];
        }

        $result = Ibos::app()->db->createCommand()->setText(sprintf($sql, $param["field"], $param["table"], $param["condition"]))->queryAll();
        $count = (count($result) == 1 ? $result[0]["count"] : count($result));
        return intval($count);
    }

    public function getListParam($operation, $uid = 0, $fid = 0, $archiveId = 0, $getUnread = false, $subOp = "")
    {
        if (!$uid) {
            $uid = Ibos::app()->user->uid;
        }

        $mainTable = $this->getTableName($archiveId);
        $bodyTable = EmailBody::model()->getTableName($archiveId);
        $param = array("field" => "", "table" => "{{{$mainTable}}} e LEFT JOIN {{{$bodyTable}}} eb on e.bodyid = eb.bodyid", "condition" => $getUnread ? "e.isread = 0 AND " : "", "order" => "", "group" => "");

        switch ($operation) {
            case "inbox":
                $param["condition"] .= "e.toid ='$uid' AND e.fid ='1' AND e.isdel ='0' AND e.isweb = '0'";
                break;

            case "todo":
                $param["condition"] .= "e.toid ='$uid' AND e.isdel = 0 AND e.ismark = 1";
                break;

            case "draft":
                $param["field"] = "*";
                $param["table"] = "{{{$bodyTable}}} eb";
                $param["condition"] = "eb.fromid = '$uid' AND eb.issend != 1";
                break;

            case "send":
                $param["condition"] = "eb.fromid = '$uid' AND eb.issend = 1 AND e.fid = 1 AND eb.issenderdel != 1";
                $param["group"] = "eb.bodyid";
                break;

            case "archive":
                if ($archiveId && $subOp) {
                    if ($subOp == "in") {
                        $param["condition"] .= "e.toid ='$uid' AND e.fid = 1 AND e.isdel = 0";
                    } elseif ($subOp == "send") {
                        $param["field"] = "*";
                        $param["group"] = "eb.bodyid";
                        $param["condition"] .= "eb.fromid = '$uid' AND eb.issend = 1 AND e.fid = 1 AND eb.issenderdel != 1";
                    }

                    break;
                }
            case "del":
                $param["condition"] .= "e.toid ='$uid' AND (e.isdel = 3 OR e.isdel = 4 OR e.isdel = 1)";
                break;

            case "folder":
                if ($fid) {
                    $param["condition"] .= "(e.toid='$uid' OR eb.fromid='$uid') AND e.fid = $fid AND e.isdel !=3";
                    break;
                }
            case "web":
                $param["condition"] .= "e.toid ='$uid' AND e.isdel =0 AND eb.issend = 1 AND e.isweb = 1";
                break;

            default:
                $param["condition"] .= "1=2";
                break;
        }

        return $param;
    }

    public function moveByBodyId($emailids, $source, $target)
    {
        $source = intval($source);
        $target = intval($target);

        if ($source != $target) {
            $db = Ibos::app()->db->createCommand();
            $text = sprintf("REPLACE INTO {{%s}} SELECT * FROM {{%s}} WHERE bodyid IN ('%s')", $this->getTableName($target), $this->getTableName($source), implode(",", $emailids));
            $db->setText($text)->execute();
            return $db->delete($this->getTableName($source), "FIND_IN_SET(bodyid,'" . implode(",", $emailids) . ")");
        } else {
            return false;
        }
    }

    public function fetchTableIds()
    {
        $tableIds = array("0" => 0);
        $name = $this->getTableSchema()->name;
        $tables = Ibos::app()->db->createCommand()->setText("SHOW TABLES LIKE '" . str_replace("_", "\_", $this->tableName() . "_%") . "'")->queryAll(false);

        foreach ($tables as $table) {
            $tableName = $table[0];
            preg_match("/^" . $name . "_([\d])+$/", $tableName, $match);

            if (empty($match[1])) {
                continue;
            } else {
                $tableId = intval($match[1]);
            }

            $tableIds[$tableId] = $tableId;
        }

        return $tableIds;
    }

    public function getSplitSearchContdition($conditions)
    {
        $whereArr = array();

        if (!empty($conditions["emailidmin"])) {
            $whereArr[] = "e.emailid >= " . $conditions["emailidmin"];
        }

        if (!empty($conditions["emailidmax"])) {
            $whereArr[] = "e.emailid <= " . $conditions["emailidmax"];
        }

        if (!empty($conditions["timerange"])) {
            $timeRange = TIMESTAMP - (intval($conditions["timerange"]) * 86400 * 30);
            $whereArr[] = "b.sendtime <= " . $timeRange;
        }

        $whereSql = (!empty($whereArr) && is_array($whereArr) ? implode(" AND ", $whereArr) : "");
        return $whereSql;
    }

    public function countBySplitCondition($tableId, $conditions = "")
    {
        $condition = $this->mergeSplitCondition($conditions);
        $db = Ibos::app()->db->createCommand();
        $count = $db->select("COUNT(*)")->from("{{" . $this->getTableName($tableId) . "}} e")->rightJoin("{{" . EmailBody::model()->getTableName($tableId) . "}} b", "e.`bodyid` = b.`bodyid`")->where($condition)->queryScalar();
        return intval($count);
    }

    public function fetchAllBySplitCondition($tableId, $conditions = "", $offset = null, $limit = null)
    {
        $condition = $this->mergeSplitCondition($conditions);
        $db = Ibos::app()->db->createCommand();
        $list = $db->select("e.emailid,b.fromid,b.subject,b.sendtime,b.bodyid")->from("{{" . $this->getTableName($tableId) . "}} e")->rightJoin("{{" . EmailBody::model()->getTableName($tableId) . "}} b", "e.`bodyid` = b.`bodyid`")->where($condition)->order("sendtime ASC")->offset($offset)->limit($limit)->queryAll();
        return $list;
    }

    public function getTableName($tableId = 0)
    {
        $tableId = intval($tableId);
        return 0 < $tableId ? "email_$tableId" : "email";
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

    private function mergeSplitCondition($conditions = "")
    {
        $conditions .= (stripos("WHERE", $conditions) != -1 ? " AND" : "");
        $conditions .= " e.`ismark`=0 AND e.`isread`=1 AND b.`bodyid` IS NOT null";
        $addition = array();
        $addition[] = "e.`boxid` = 1 AND e.`isdel` = 0";
        $addition[] = "e.`boxid` = 1 AND e.`issend` = 1 AND b.`issenderdel` != 1";
        $addition[] = "e.`issend` = 1 AND b.`issenderdel` != 1 AND b.`towebmail`!=''";
        $conditions .= " AND ((" . implode(") OR (", $addition) . "))";
        return $conditions;
    }

    private function getSiblingsByCondition($condition, $order, $archiveId = 0)
    {
        $siblings = Ibos::app()->db->createCommand()->select("e.emailid,eb.subject")->from(sprintf("{{%s}} e", $this->getTableName($archiveId)))->leftJoin(sprintf("{{%s}} eb", EmailBody::model()->getTableName($archiveId)), "e.bodyid = eb.bodyid")->where($condition)->order($order)->limit(1)->queryRow();
        return $siblings;
    }
}
