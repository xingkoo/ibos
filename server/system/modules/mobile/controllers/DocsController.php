<?php

class MobileDocsController extends MobileBaseController
{
    private $_condition = "";

    public function actionIndex()
    {
        $type = EnvUtil::getRequest("type");
        $catid = EnvUtil::getRequest("catid");
        $childCatIds = "";

        if (!empty($catid)) {
            if ($catid == "-1") {
                $type = "nosign";
            } elseif ($catid == "-2") {
                $type = "sign";
            } else {
                $childCatIds = OfficialdocCategory::model()->fetchCatidByPid($catid, true);
            }
        }

        if (EnvUtil::getRequest("search")) {
            $this->search();
        }

        $uid = Ibos::app()->user->uid;
        $docIdArr = OfficialdocReader::model()->fetchDocidsByUid($uid);
        $this->_condition = OfficialdocUtil::joinListCondition($type, $docIdArr, $childCatIds, $this->_condition);
        $datas = Officialdoc::model()->fetchAllAndPage($this->_condition);

        if (isset($datas["datas"])) {
            foreach ($datas["datas"] as $k => $v) {
                $datas["datas"][$k]["content"] = mb_substr(strip_tags($v["content"]), 0, 15, 'utf-8');
            }
        }
        $officialDocList = ICOfficialdoc::getListDatas($datas["datas"]);
        $dashboardConfig = Yii::app()->setting->get("setting/docconfig");
        $params = array("pages" => $datas["pages"], "datas" => $officialDocList, "isApprover" => StringUtil::findIn($uid, $dashboardConfig["docapprover"]));
        $this->ajaxReturn($params, "JSONP");
    }

    public function actionCategory()
    {
        $category = OfficialdocCategory::model()->fetchAll();
        $tmp = array(
            array()
            );
        $data = array_merge($tmp, $category);
        unset($data[0]);
        $params = StringUtil::getTree($data, "<li class='\$selected'><a href='#docs' onclick='docs.loadList(\$catid)'>\$spacer<i class='ao-file'></i>\$name</a></li>");
        $this->ajaxReturn($params, "JSONP");
    }

    public function actionShow()
    {
        $uid = Ibos::app()->user->uid;
        $docid = EnvUtil::getRequest("id");
        $version = EnvUtil::getRequest("version");

        if (empty($docid)) {
            $this->ajaxReturn("", "JSONP");
        }

        $officialDocEntity = new ICOfficialdoc($docid);
        $officialDoc = $officialDocEntity->getAttributes();

        if ($version) {
            $versionData = OfficialdocVersion::model()->fetchByAttributes(array("docid" => $docid, "version" => $version));
            $officialDoc = array_merge($officialDoc, $versionData);
        }

        if (!empty($officialDoc)) {
            if (!OfficialdocUtil::checkReadScope($uid, $officialDoc)) {
                $this->error(Ibos::lang("You do not have permission to read the officialdoc"), $this->createUrl("default/index"));
            }

            $data = ICOfficialdoc::getShowData($officialDoc);
            OfficialdocReader::model()->addReader($docid, $uid);
            Officialdoc::model()->updateClickCount($docid, $data["clickcount"]);
            $page = EnvUtil::getRequest("page");
            $criteria = new CDbCriteria();
            $pages = new CPagination(OfficialdocUtil::getCharacterLength($data["content"]));
            $pages->pageSize = 2000;
            $pages->applyLimit($criteria);
            $tmpContent = OfficialdocUtil::subHtml($data["content"], $pages->getCurrentPage() * $pages->getPageSize(), ($pages->getCurrentPage() + 1) * $pages->getPageSize());
            $data["content"] = $tmpContent;
            if (!empty($page) && ($page != 1)) {
                $data["content"] = "<div><div style=\"border-bottom:4px solid #e26f50;margin-top:60px;\"></div><div style=\"border-top:1px solid #e26f50;margin-top:4px;\"><div><p style=\"text-align:center;\"></p><div id=\"original-content\" style=\"min-height:400px;font:16px/2 fangsong,simsun;color:#666;\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"95%\" align=\"center\"><tbody><tr><td class=\"p1\"><span><p>" . $tmpContent . "</p>";
                $data["content"] = OfficialdocUtil::subHtml($data["content"], 0, $pages->pageSize * 2);
            }

            $params = array("data" => $data, "pages" => $pages, "dashboardConfig" => Yii::app()->setting->get("setting/docconfig"));

            if ($data["rcid"]) {
                $params["rcType"] = RcType::model()->fetchByPk($data["rcid"]);
            }
        } else {
            $params = "";
        }

        $this->ajaxReturn($params, "JSONP");
    }

    private function search()
    {
        $keyword = EnvUtil::getRequest("search");
        $this->_condition = " subject LIKE '%$keyword%' ";
    }
}
