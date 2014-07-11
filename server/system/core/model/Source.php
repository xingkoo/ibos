<?php

class Source
{
    public static function getSourceInfo($table, $rowId, $forApi = false, $moduleName = "weibo")
    {
        static $_forApi = "0";
        ($_forApi == "0") && ($_forApi = intval($forApi));
        $key = ($_forApi ? $table . $rowId . "_api" : $table . $rowId);
        $info = CacheUtil::get("source_info_" . $key);

        if ($info) {
            return $info;
        }

        switch ($table) {
            case "feed":
                $info = self::getInfoFromFeed($table, $rowId, $_forApi);
                break;

            case "comment":
                $info = self::getInfoFromComment($table, $rowId, $_forApi);
                break;

            default:
                $table = ucfirst($table);
                $model = $table::model();

                if (method_exists($model, "getSourceInfo")) {
                    $info = $model->getSourceInfo($rowId, $_forApi);
                }

                unset($model);
                break;
        }

        $info["source_table"] = $table;
        $info["source_id"] = $rowId;
        CacheUtil::set("source_info_" . $key, $info);
        return $info;
    }

    private static function getInfoFromFeed($table, $rowId, $forApi)
    {
        $info = Feed::model()->getFeedInfo($rowId, $forApi);
        $info["source_user_info"] = User::model()->fetchByUid($info["uid"]);
        $info["source_user"] = ($info["uid"] == Ibos::app()->user->uid ? Ibos::lang("Me", "message.default") : "<a href=\"" . $info["source_user_info"]["space_url"] . "\" class=\"anchor\" target=\"_blank\">" . $info["source_user_info"]["realname"] . "</a>");
        $info["source_type"] = "微博";
        $info["source_title"] = "";
        $info["source_url"] = Ibos::app()->urlManager->createUrl("weibo/personal/feed", array("feedid" => $rowId, "uid" => $info["uid"]));
        $info["source_content"] = $info["content"];
        $info["ctime"] = $info["ctime"];
        return $info;
    }

    private static function getInfoFromComment($table, $rowId, $forApi)
    {
        $_info = Comment::model()->getCommentInfo($rowId, true);
        $info["uid"] = $_info["moduleuid"];
        $info["rowid"] = $_info["rowid"];
        $info["source_user"] = ($info["uid"] == Ibos::app()->user->uid ? Ibos::lang("Me", "message.default") : $_info["user_info"]["space_url"]);
        $info["comment_user_info"] = User::model()->fetchByUid($_info["user_info"]["uid"]);
        $forApi && ($info["source_user"] = StringUtil::parseForApi($info["source_user"]));
        $info["source_user_info"] = User::model()->fetchByUid($info["uid"]);
        $info["source_type"] = Ibos::lang("Comment", "message.default");
        $info["source_content"] = ($forApi ? parseforapi($_info["content"]) : $_info["content"]);
        $info["source_url"] = $_info["sourceInfo"]["source_url"];
        $info["ctime"] = $_info["ctime"];
        $info["module"] = $_info["module"];
        $info["sourceInfo"] = $_info["sourceInfo"];
        $info["source_title"] = ($forApi ? StringUtil::parseForApi($_info["user_info"]["space_url"]) : $_info["user_info"]["space_url"]);
        return $info;
    }

    public static function getCommentSource($data, $forApi = false)
    {
        if (($data["table"] == "feed") || ($data["table"] == "comment") || $forApi) {
            $info = self::getSourceInfo($data["table"], $data["rowid"], $forApi, $data["module"]);
            return $info;
        }

        $info["source_user_info"] = User::model()->fetchByUid($data["moduleuid"]);
        $info["source_url"] = "";
        $info["source_content"] = (isset($data["content"]) ? $data["content"] : "");
        return $info;
    }
}
