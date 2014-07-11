<?php

class ArticleApi extends MessageApi
{
    public $iconNormalStyle = array("o-art-normal", "o-art-pic", "o-art-vote");
    public $iconReadStyle = array("o-art-normal-gray", "o-art-pic-gray", "o-art-vote-gray");

    public function renderIndex()
    {
        $data = array("articles" => $this->loadNewArticle(), "lang" => Ibos::getLangSource("article.default"), "assetUrl" => Ibos::app()->assetManager->getAssetsUrl("article"));
        $viewAlias = "application.modules.article.views.indexapi.article";
        $return["article"] = Ibos::app()->getController()->renderPartial($viewAlias, $data, true);
        return $return;
    }

    public function loadNew()
    {
        $uid = Ibos::app()->user->uid;
        $allDeptId = Ibos::app()->user->alldeptid . "";
        $allPosId = Ibos::app()->user->allposid . "";
        $deptCondition = "";
        $deptIdArr = explode(",", $allDeptId);

        if (0 < count($deptIdArr)) {
            foreach ($deptIdArr as $deptId) {
                $deptCondition .= "FIND_IN_SET('$deptId',deptid) OR ";
            }

            $deptCondition = substr($deptCondition, 0, -4);
        } else {
            $deptCondition = "FIND_IN_SET('',deptid)";
        }

        $condition = " ( ((deptid='alldept' OR $deptCondition OR FIND_IN_SET('$allPosId',positionid) OR FIND_IN_SET('$uid',uid)) OR (deptid='' AND positionid='' AND uid='') OR (author='$uid') OR (approver='$uid')) ) AND `status`='1'";
        $sql = sprintf("SELECT COUNT(articleid) FROM {{article}} WHERE %s AND `articleid` NOT IN (SELECT `articleid` FROM `{{article_reader}}` a WHERE a.uid = %d)", $condition, $uid);
        $count = Ibos::app()->db->createCommand()->setText($sql)->queryScalar();
        return intval($count);
    }

    public function loadSetting()
    {
        return array("name" => "article", "title" => Ibos::lang("Information center", "article.default"), "style" => "in-article");
    }

    private function loadNewArticle($num = 3)
    {
        $uid = Ibos::app()->user->uid;
        $allDeptId = Ibos::app()->user->alldeptid . "";
        $allPosId = Ibos::app()->user->allposid . "";
        $deptCondition = "";
        $deptIdArr = explode(",", $allDeptId);

        if (0 < count($deptIdArr)) {
            foreach ($deptIdArr as $deptId) {
                $deptCondition .= "FIND_IN_SET('$deptId',deptid) OR ";
            }

            $deptCondition = substr($deptCondition, 0, -4);
        } else {
            $deptCondition = "FIND_IN_SET('',deptid)";
        }

        $condition = " ( ((deptid='alldept' OR $deptCondition OR FIND_IN_SET('$allPosId',positionid) OR FIND_IN_SET('$uid',uid)) OR (deptid='' AND positionid='' AND uid='') OR (author='$uid') OR (approver='$uid')) ) AND `status`='1'";
        $criteria = array("select" => "articleid,subject,content,addtime,type", "condition" => $condition, "order" => "`istop` DESC, `addtime` DESC", "offset" => 0, "limit" => $num);
        $articles = Article::model()->fetchAll($criteria);

        if (!empty($articles)) {
            foreach ($articles as &$article) {
                $read = ArticleReader::model()->fetchByAttributes(array("articleid" => $article["articleid"], "uid" => $uid));
                $readStatus = ($read ? 1 : 0);

                if ($readStatus) {
                    $article["iconStyle"] = $this->iconReadStyle[$article["type"]];
                } else {
                    $article["iconStyle"] = $this->iconNormalStyle[$article["type"]];
                }
            }
        }

        return $articles;
    }
}
