<?php

class OfficialdocApi extends MessageApi
{
    public function renderIndex()
    {
        $data = array("docs" => $this->loadNewDoc(), "lang" => Ibos::getLangSource("officialdoc.default"), "assetUrl" => Yii::app()->assetManager->getAssetsUrl("officialdoc"));
        $viewAlias = "application.modules.officialdoc.views.indexapi.officialdoc";
        $return["officialdoc"] = Yii::app()->getController()->renderPartial($viewAlias, $data, true);
        return $return;
    }

    public function loadSetting()
    {
        return array("name" => "officialdoc", "title" => Ibos::lang("Officialdoc", "officialdoc.default"), "style" => "in-officialdoc");
    }

    public function loadNew()
    {
        $uid = Yii::app()->user->uid;
        $allDeptId = Yii::app()->user->alldeptid . "";
        $allPosId = Yii::app()->user->allposid . "";
        $condition = " ( ((deptid='alldept' OR FIND_IN_SET('$allDeptId',deptid) OR FIND_IN_SET('$allPosId',positionid) OR FIND_IN_SET('$uid',uid)) OR (deptid='' AND positionid='' AND uid='') OR (author='$uid') OR (approver='$uid')) ) AND `status`='1'";
        $sql = sprintf("SELECT COUNT(docid) FROM {{doc}} WHERE %s AND `docid` NOT IN (SELECT `docid` FROM `{{doc_reader}}` a WHERE a.uid = %d)", $condition, $uid);
        $count = Ibos::app()->db->createCommand()->setText($sql)->queryScalar();
        return intval($count);
    }

    private function loadNewDoc($num = 3)
    {
        $uid = Yii::app()->user->uid;
        $allDeptId = Yii::app()->user->alldeptid . "";
        $allPosId = Yii::app()->user->allposid . "";
        $condition = " ( ((deptid='alldept' OR FIND_IN_SET('$allDeptId',deptid) OR FIND_IN_SET('$allPosId',positionid) OR FIND_IN_SET('$uid',uid)) OR (deptid='' AND positionid='' AND uid='') OR (author='$uid') OR (approver='$uid')) ) AND `status`='1'";
        $criteria = array("select" => "docid,subject,author,addtime", "condition" => $condition, "order" => "`istop` DESC, `addtime` DESC", "offset" => 0);
        $docs = Officialdoc::model()->fetchAll($criteria);
        $unSign = array();
        $signed = array();

        if (!empty($docs)) {
            foreach ($docs as &$doc) {
                $doc["author"] = User::model()->fetchRealNameByUid($doc["author"]);
                $doc["sign"] = OfficialdocReader::model()->fetchByAttributes(array("docid" => $doc["docid"], "uid" => $uid));
                $doc["isSign"] = (empty($doc["sign"]) ? 0 : $doc["sign"]["issign"]);

                if ($doc["isSign"] == 0) {
                    $unSign[] = $doc;
                } elseif ($doc["isSign"] == 1) {
                    $signed[] = $doc;
                }
            }

            $docs = array_merge($unSign, $signed);
        }

        if ($num < count($docs)) {
            $docs = array_slice($docs, 0, $num);
        }

        return $docs;
    }
}
