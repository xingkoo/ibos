<?php

class EmailListController extends EmailBaseController
{
    public function init()
    {
        parent::init();
        $this->fid = intval(EnvUtil::getRequest("fid"));
        $this->webId = intval(EnvUtil::getRequest("webid"));
        $this->archiveId = intval(EnvUtil::getRequest("archiveid"));
        $this->subOp = EnvUtil::getRequest("subop") . "";

        if (isset($_GET["pagesize"])) {
            $this->setListPageSize($_GET["pagesize"]);
        }
    }

    public function actionIndex()
    {
        $op = EnvUtil::getRequest("op");
        $opList = array("inbox", "todo", "draft", "send", "folder", "archive", "del");

        if ($this->allowWebMail) {
            $opList[] = "web";
        }

        if (!in_array($op, $opList)) {
            $op = "inbox";
        }

        $data = $this->getListData($op);
        $this->setPageTitle(Ibos::lang("Email center"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Email center"), "url" => $this->createUrl("list/index"))
        ));
        $this->render("index", $data);
    }

    public function actionSearch()
    {
        $condition = array();

        if (Yii::app()->request->getIsPostRequest()) {
            $search = $_POST["search"];
            $condition = EmailUtil::mergeSearchCondition($search, $this->uid);
            $conditionStr = base64_encode(serialize($condition));
        } else {
            $conditionStr = EnvUtil::getRequest("condition");
            $condition = unserialize(base64_decode($conditionStr));
        }

        if (empty($condition)) {
            $this->error(Ibos::lang("Request tainting", "error"), $this->createUrl("list/index"));
        }

        $emailData = Email::model()->fetchAllByArchiveIds("*", $condition["condition"], $condition["archiveId"], array("e", "eb"), null, null, SORT_DESC, "emailid");
        $count = count($emailData);
        $pages = PageUtil::create($count, $this->getListPageSize(), false);
        $pages->params = array("condition" => $conditionStr);
        $list = array_slice($emailData, $pages->getOffset(), $pages->getLimit(), false);

        foreach ($list as $index => &$mail) {
            $mail["fromuser"] = ($mail["fromid"] ? User::model()->fetchRealnameByUid($mail["fromid"]) : "");
        }

        $data = array("list" => $list, "pages" => $pages, "condition" => $conditionStr, "folders" => $this->folders);
        $this->setPageTitle(Ibos::lang("Search result"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Email center"), "url" => $this->createUrl("list/index")),
            array("name" => Ibos::lang("Search result"))
        ));
        $this->render("search", $data);
    }

    private function getListData($operation)
    {
        $data["op"] = $operation;
        $data["fid"] = $this->fid;
        $data["webId"] = $this->webId;
        $data["folders"] = $this->folders;
        $data["archiveId"] = $this->archiveId;
        $data["allowRecall"] = Yii::app()->setting->get("setting/emailrecall");
        $uid = $this->uid;

        if ($operation == "archive") {
            if (!in_array($this->subOp, array("in", "send"))) {
                $this->subOp = "in";
            }
        }

        $data["subOp"] = $this->subOp;
        $count = Email::model()->countByListParam($operation, $uid, $data["fid"], $data["archiveId"], $data["subOp"]);
        $pages = PageUtil::create($count, $this->getListPageSize());
        $data["pages"] = $pages;
        $data["unreadCount"] = Email::model()->countUnreadByListParam($operation, $uid, $data["fid"], $data["archiveId"], $data["subOp"]);
        $data["list"] = Email::model()->fetchAllByListParam($operation, $uid, $data["fid"], $data["archiveId"], $pages->getLimit(), $pages->getOffset(), $data["subOp"]);
        return $data;
    }
}
