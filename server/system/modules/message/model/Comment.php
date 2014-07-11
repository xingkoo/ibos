<?php

class Comment extends ICModel
{
    public static function model($className = "Comment")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{comment}}";
    }

    public function fetchReplyIdByCid($cid)
    {
        $criteria = array(
            "select"    => "cid",
            "condition" => "`rowid` = :rowid",
            "params"    => array(":rowid" => $cid)
        );
        $result = $this->fetchAll($criteria);
        return ConvertUtil::getSubByKey($result, "cid");
    }

    public function addComment($data, $forApi = false, $notCount = false, $lessUids = null)
    {
        $add = $this->escapeData($data);

        if ($add["content"] === "") {
            $this->addError("comment", Ibos::lang("Required comment content", "message.default"));
            return false;
        }

        $add["isdel"] = 0;
        $res = $this->add($add, true);

        if ($res) {
            isset($data["touid"]) && !empty($data["touid"]) && ($lessUids[] = intval($data["touid"]));
            $scream = explode("//", $data["content"]);
            Atme::model()->addAtme("message", "comment", trim($scream[0]), $res, null, $lessUids);
            $table = ucfirst($add["table"]);
            $pk = $table::model()->getTableSchema()->primaryKey;
            $table::model()->updateCounters(array("commentcount" => 1), "`$pk` = {$add["rowid"]}");
            if ((Ibos::app()->user->uid != $add["moduleuid"]) && ($add["moduleuid"] != "")) {
                !$notCount && UserData::model()->updateKey("unread_comment", 1, true, $add["moduleuid"]);
            }

            if (!empty($add["touid"]) && ($add["touid"] != Ibos::app()->user->uid) && ($add["touid"] != $add["moduleuid"])) {
                !$notCount && UserData::model()->updateKey("unread_comment", 1, true, $add["touid"]);
            }

            if ($add["table"] == "feed") {
                if (Ibos::app()->user->uid != $add["uid"]) {
                    UserUtil::updateCreditByAction("addcomment", Ibos::app()->user->uid);
                    UserUtil::updateCreditByAction("getcomment", $data["moduleuid"]);
                }

                Feed::model()->cleanCache($add["rowid"]);
            }

            if (($add["touid"] != Ibos::app()->user->uid) || (($add["moduleuid"] != Ibos::app()->user->uid) && ($add["moduleuid"] != ""))) {
                $author = User::model()->fetchByUid(Ibos::app()->user->uid);
                $config["{name}"] = $author["realname"];
                $sourceInfo = Source::getCommentSource($add, $forApi);
                $config["{url}"] = $sourceInfo["source_url"];
                $config["{sourceContent}"] = StringUtil::parseHtml($sourceInfo["source_content"]);

                if (!empty($add["touid"])) {
                    $config["{commentType}"] = "回复了我的评论:";
                    Notify::model()->sendNotify($add["touid"], "comment", $config);
                } else {
                    $config["{commentType}"] = "评论了我的微博:";

                    if (!empty($add["moduleuid"])) {
                        Notify::model()->sendNotify($add["moduleuid"], "comment", $config);
                    }
                }
            }
        }

        return $res;
    }

    public function deleteComment($ids, $uid = null, $module = "")
    {
        $ids = (is_array($ids) ? $ids : explode(",", $ids));
        $map = array("and");
        $map[] = array("in", "cid", $ids);
        $comments = $this->getDbConnection()->createCommand()->select("cid,module,table,rowid,moduleuid,uid")->from($this->tableName())->where($map)->queryAll();

        if (empty($comments)) {
            return false;
        }

        foreach ($comments as $value) {
            Atme::model()->deleteAtme($value["table"], null, $value["cid"], null);
        }

        $_comments = array();

        foreach ($comments as $comment) {
            $_comments[$comment["table"]][$comment["rowid"]][] = $comment["cid"];
        }

        $cids = ConvertUtil::getSubByKey($comments, "cid");
        $res = $this->updateAll(array("isdel" => 1), "`cid` IN (" . implode(",", $cids) . ")");

        if ($res) {
            foreach ($_comments as $tableName => $rows) {
                foreach ($rows as $rowid => $cid) {
                    $_table = ucfirst($tableName);
                    $field = $_table::model()->getTableSchema()->primaryKey;

                    if (empty($field)) {
                        $field = $tableName . "id";
                    }

                    $_table::model()->updateCounters(array("commentcount" => -count($cid)), "`$field`=$rowid");
                    if (($module == "weibo") || ($module == "feed")) {
                        $_table::model()->cleanCache($rowid);
                    }
                }
            }

            if ($uid) {
                UserUtil::updateCreditByAction("delcomment", $uid);
            }
        }

        $this->addError("deletecomment", $res != false ? Ibos::lang("Operation succeed", "message") : Ibos::lang("Operation failure", "message"));
        return $res;
    }

    public function getCommentList($map = null, $order = "cid ASC", $limit = 10, $offset = 0, $isReply = false)
    {
        $list = $this->getDbConnection()->createCommand()->select("*")->from($this->tableName())->where($map)->order($order)->limit($limit)->offset($offset)->queryAll();
        $uid = Ibos::app()->user->uid;
        $isAdministrator = Ibos::app()->user->isadministrator;

        foreach ($list as $k => &$v) {
            if (!empty($v["tocid"]) && $isReply) {
                $replyInfo = $this->getCommentInfo($v["tocid"], false);
                $v["replyInfo"] = "//@<a class='anchor' data-toggle='usercard' data-param='uid={$replyInfo["user_info"]["uid"]}' href='{$replyInfo["user_info"]["space_url"]}' target='_blank'>" . $replyInfo["user_info"]["realname"] . "</a>：" . $replyInfo["content"];
            } else {
                $v["replyInfo"] = "";
            }

            $v["isCommentDel"] = $isAdministrator || ($uid === $v["uid"]);
            $v["user_info"] = User::model()->fetchByUid($v["uid"]);
            $v["content"] = StringUtil::parseHtml($v["content"] . $v["replyInfo"]);
            $v["sourceInfo"] = Source::getCommentSource($v);

            if (!empty($v["attachmentid"])) {
                $v["attach"] = AttachUtil::getAttach($v["attachmentid"]);
            }
        }

        return $list;
    }

    public function getCommentInfo($id, $source = true)
    {
        $id = intval($id);

        if (empty($id)) {
            $this->addError("get", Ibos::lang("Parameters error", "error"));
            return false;
        }

        $info = CacheUtil::get("comment_info_" . $id);

        if ($info) {
            return $info;
        } else {
            $info = $this->fetchByPk($id);
            $info["user_info"] = User::model()->fetchByUid($info["uid"]);
            $info["content"] = $info["content"];
            $source && ($info["sourceInfo"] = Source::getCommentSource($info));
            $source && CacheUtil::set("comment_info_" . $id, $info);
            return $info;
        }
    }

    public function countCommentByMap($map)
    {
        return $this->getDbConnection()->createCommand()->select("count(cid)")->from($this->tableName())->where($map)->queryScalar();
    }

    private function escapeData($data)
    {
        $add["module"] = $data["module"];
        $add["table"] = $data["table"];
        $add["rowid"] = intval($data["rowid"]);
        $add["uid"] = Ibos::app()->user->uid;
        $add["moduleuid"] = intval($data["moduleuid"]);
        $add["content"] = StringUtil::pregHtml($data["content"]);
        $add["tocid"] = (isset($data["tocid"]) ? intval($data["tocid"]) : 0);
        $add["touid"] = (isset($data["touid"]) ? intval($data["touid"]) : 0);
        $add["data"] = serialize(isset($data["data"]) ? $data["data"] : array());
        $add["ctime"] = TIMESTAMP;
        $add["from"] = (isset($data["from"]) ? intval($data["from"]) : EnvUtil::getVisitorClient());
        $add["attachmentid"] = (isset($data["attachmentid"]) ? $data["attachmentid"] : "");
        return $add;
    }

    public function doEditComment($id, $type)
    {
        $return = false;

        if (empty($id)) {
        } else {
            $cid = (is_array($id) ? implode(",", $id) : intval($id));
            $con = sprintf("cid = %d", $cid);

            if ($type == "deleteComment") {
                $res = $this->deleteAll($con);
            } elseif ($type == "commentRecover") {
                $res = $this->commentRecover($id);
            } else {
                $res = $this->deleteComment($id);
            }

            if ($res != false) {
                $return = true;
            }
        }

        return $return;
    }

    public function commentRecover($id)
    {
        if (empty($id)) {
            return false;
        }

        $con = "cid = " . $id;
        $criteria = array("select" => "cid,module,`table`,rowid,moduleuid,uid", "condition" => $con);
        $comment = $this->fetch($criteria);
        $save["isdel"] = 0;

        if ($this->updateAll($save, $con)) {
            $model = ucfirst($comment["table"]);
            $model::model()->updateCounters(array("commentcount" => 1), "`" . $comment["table"] . "id`=" . $comment["rowid"]);

            switch ($comment["table"]) {
                case "feed":
                    $feedIds = $this->fetch(array("select" => "rowid", "condition" => $con));
                    $feedId = array($feedIds["rowid"]);
                    Feed::model()->cleanCache($feedId);
                    break;
            }

            return true;
        }

        return false;
    }
}
