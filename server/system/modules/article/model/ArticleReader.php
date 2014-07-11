<?php

class ArticleReader extends ICModel
{
    public static function model($className = "ArticleReader")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{article_reader}}";
    }

    public function fetchReaderByArticleId($articleid)
    {
        $condition = "articleid=:articleid";
        $params = array(":articleid" => $articleid);
        $reader = $this->fetch($condition, $params);
        return $reader;
    }

    public function checkIsRead($articleid, $uid)
    {
        $result = false;
        $condition = "articleid=:articleid AND uid=:uid";
        $params = array(":articleid" => $articleid, ":uid" => $uid);
        $count = $this->count($condition, $params);

        if ($count) {
            $result = true;
        }

        return $result;
    }

    public function fetchReadArtIdsByUid($uid)
    {
        $read = $this->fetchAll("uid = $uid");
        $readArtIds = ConvertUtil::getSubByKey($read, "articleid");
        return $readArtIds;
    }

    public function addReader($articleid, $uid)
    {
        if ($this->checkIsRead($articleid, $uid) == false) {
            $user = User::model()->fetchByUid($uid);
            $articleReader = array("articleid" => $articleid, "uid" => $uid, "addtime" => TIMESTAMP, "readername" => $user["realname"]);
            return $this->add($articleReader);
        }
    }

    public function fetchArticleidsByUid($uid)
    {
        $result = array();
        $readerList = $this->fetchAll("uid=:uid", array(":uid" => $uid));

        if (!empty($readerList)) {
            foreach ($readerList as $reader) {
                $result[$reader["readerid"]] = $reader["articleid"];
            }
        }

        return $result;
    }

    public function fetchArticleReaderByDeptid($articleId, $deptId)
    {
        $result = array();
        $data = $this->fetchAll("articleid=:articleid", array(":articleid" => $articleId));

        if (!empty($data)) {
            foreach ($data as $k => $reader) {
                $user = User::model()->fetchByUid($reader["uid"]);

                if (!empty($user)) {
                    $deptid = $user["deptid"];

                    if ($deptid == $deptId) {
                        $result[] = $reader["readername"];
                    }
                } else {
                    unset($data[$k]);
                }
            }

            $temp = implode(",", $result);
            $result = $temp;
        }

        return $result;
    }
}
