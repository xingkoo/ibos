<?php

class OfficialdocOfficialdocController extends OfficialdocBaseController
{
    /**
     * 分类id
     * @var type 
     */
    protected $catId = 0;
    /**
     * 条件
     * @var type 
     */
    private $_condition = "";

    public function actionIndex()
    {
        $op = EnvUtil::getRequest("op");
        $option = (empty($op) ? "default" : $op);
        $routes = array("default", "getSign", "search", "getUnSign", "getVersion", "getRcType", "prewiew", "remind");

        if (!in_array($option, $routes)) {
            $this->error(Ibos::lang("Can not find the path"), $this->createUrl("officialdoc/index"));
        }

        if ($option == "default") {
            $catid = intval(EnvUtil::getRequest("catid"));
            $childCatIds = "";

            if (!empty($catid)) {
                $this->catId = $catid;
                $childCatIds = OfficialdocCategory::model()->fetchCatidByPid($this->catId, true);
            }

            if (EnvUtil::getRequest("param") == "search") {
                $this->search();
            }

            Officialdoc::model()->cancelTop();
            Officialdoc::model()->updateIsOverHighLight();
            $type = EnvUtil::getRequest("type");
            $uid = Ibos::app()->user->uid;
            $docIdArr = OfficialdocReader::model()->fetchDocidsByUid($uid);
            //old code $this->_condition = OfficialdocUtil::joinListCondition($type, $docIdArr, $childCatIds, $this->_condition, $this->catId);
            $this->_condition = OfficialdocUtil::joinListCondition($type, $docIdArr, $childCatIds, $this->_condition);
            $datas = Officialdoc::model()->fetchAllAndPage($this->_condition);
            $officialDocList = ICOfficialdoc::getListDatas($datas["datas"]);
            $aids = OfficialdocCategory::model()->fetchAids();
            $isApprover = in_array($uid, Approval::model()->fetchApprovalUidsByIds($aids));
            $params = array("pages" => $datas["pages"], "officialDocList" => $officialDocList, "categorySelectOptions" => $this->getCategoryOption(), "isApprover" => $isApprover);
            $this->setPageTitle(Ibos::lang("Officialdoc"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Information center")),
                array("name" => Ibos::lang("Officialdoc"), "url" => $this->createUrl("officialdoc/index")),
                array("name" => Ibos::lang("Officialdoc list"))
            ));

            if ($type == "notallow") {
                $view = "approval";
                $params["officialDocList"] = ICOfficialdoc::handleApproval($params["officialDocList"]);
            } else {
                $view = "list";
            }

            $this->render($view, $params);
        } else {
            $this->{$option}();
        }
    }

    public function actionAdd()
    {
        $op = EnvUtil::getRequest("op");
        $option = (empty($op) ? "default" : $op);
        $routes = array("default", "save", "checkIsAllowPublish");

        if (!in_array($option, $routes)) {
            $this->error(Ibos::lang("Can not find the path"), $this->createUrl("officialdoc/index"));
        }

        if ($option == "default") {
            if (!empty($_GET["catid"])) {
                $this->catId = $_GET["catid"];
            }

            $allowPublish = OfficialdocCategory::model()->checkIsAllowPublish($this->catId, Ibos::app()->user->uid);
            $params = array("categoryOption" => $this->getCategoryOption(), "dashboardConfig" => Ibos::app()->setting->get("setting/docconfig"), "uploadConfig" => AttachUtil::getUploadConfig(), "RCData" => RcType::model()->fetchAll(), "allowPublish" => $allowPublish);
            $this->setPageTitle(Ibos::lang("Add officialdoc"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Information center")),
                array("name" => Ibos::lang("Officialdoc"), "url" => $this->createUrl("officialdoc/index")),
                array("name" => Ibos::lang("Add officialdoc"))
            ));
            $this->render("add", $params);
        } else {
            $this->{$option}();
        }
    }

    protected function checkIsAllowPublish()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $catid = intval(EnvUtil::getRequest("catid"));
            $uid = intval(EnvUtil::getRequest("uid"));
            $isAllow = OfficialdocCategory::model()->checkIsAllowPublish($catid, $uid);
            $this->ajaxReturn(array("isSuccess" => !!$isAllow));
        }
    }

    public function actionEdit()
    {
        $op = EnvUtil::getRequest("op");
        $option = (empty($op) ? "default" : $op);
        $routes = array("default", "update", "top", "highLight", "move", "verify", "back");

        if (!in_array($option, $routes)) {
            $this->error(Ibos::lang("Can not find the path"), $this->createUrl("officialdoc/index"));
        }

        if ($option == "default") {
            $docid = EnvUtil::getRequest("docid");

            if (empty($docid)) {
                $this->error(Ibos::lang("Parameters error", "error"));
            }

            $data = Officialdoc::model()->fetch("docid=:docid", array(":docid" => $docid));

            if (!empty($data)) {
                $data["publishScope"] = OfficialdocUtil::joinSelectBoxValue($data["deptid"], $data["positionid"], $data["uid"]);
                $data["ccScope"] = OfficialdocUtil::joinSelectBoxValue($data["ccdeptid"], $data["ccpositionid"], $data["ccuid"]);
                $allowPublish = OfficialdocCategory::model()->checkIsAllowPublish($data["catid"], Ibos::app()->user->uid);
                $params = array("data" => $data, "categoryOption" => $this->getCategoryOption(), "dashboard" => Ibos::app()->setting->get("setting/docconfig"), "uploadConfig" => AttachUtil::getUploadConfig(), "RCData" => RcType::model()->fetchAll(), "allowPublish" => $allowPublish);

                if (!empty($data["attachmentid"])) {
                    $params["attach"] = AttachUtil::getAttach($data["attachmentid"]);
                }

                $this->setPageTitle(Ibos::lang("Edit officialdoc"));
                $this->setPageState("breadCrumbs", array(
                    array("name" => Ibos::lang("Information center")),
                    array("name" => Ibos::lang("Officialdoc"), "url" => $this->createUrl("officialdoc/index")),
                    array("name" => Ibos::lang("Edit officialdoc"))
                ));
                $this->render("edit", $params);
            }
        } else {
            $this->{$option}();
        }
    }

    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $docids = trim(EnvUtil::getRequest("docids"), ",");
            $attachmentIdArr = Officialdoc::model()->fetchAidsByDocids($docids);
            AttachUtil::delAttach($attachmentIdArr);

            if (!empty($docids)) {
                Officialdoc::model()->deleteAllByDocIds($docids);
                OfficialdocVersion::model()->deleteAllByDocids($docids);
                OfficialdocReader::model()->deleteReaderByDocIds($docids);
                OfficialdocApproval::model()->deleteByDocIds($docids);
                OfficialdocBack::model()->deleteByDocIds($docids);
                $this->ajaxReturn(array("isSuccess" => true, "info" => Ibos::lang("Del succeed", "message")));
            } else {
                $this->ajaxReturn(array("isSuccess" => false, "info" => Ibos::lang("Parameters error", "error")));
            }
        }
    }

    private function save()
    {
        $uid = Ibos::app()->user->uid;
        $data = $_POST;
        $publicScope = OfficialdocUtil::handleSelectBoxData(StringUtil::getId($data["publishScope"], true));
        $data["uid"] = $publicScope["uid"];
        $data["positionid"] = $publicScope["positionid"];
        $data["deptid"] = $publicScope["deptid"];
        $ccScope = OfficialdocUtil::handleSelectBoxData(StringUtil::getId($data["ccScope"], true), false);
        $data["ccuid"] = $ccScope["uid"];
        $data["ccpositionid"] = $ccScope["positionid"];
        $data["ccdeptid"] = $ccScope["deptid"];
        $data["author"] = $uid;
        $data["docno"] = $_POST["docNo"];
        $data["approver"] = $uid;
        $data["addtime"] = TIMESTAMP;
        $data["uptime"] = TIMESTAMP;

        if ($data["status"] == 2) {
            $catid = intval($data["catid"]);
            $category = OfficialdocCategory::model()->fetchByPk($catid);
            $data["status"] = (empty($category["aid"]) ? 1 : 2);
            $data["approver"] = (!empty($category["aid"]) ? 0 : $uid);
        }

        $attachmentid = trim($_POST["attachmentid"], ",");

        if (!empty($attachmentid)) {
            AttachUtil::updateAttach($attachmentid);
        }

        $docId = Officialdoc::model()->add($data, true);
        $user = User::model()->fetchByUid($uid);
        $officialdoc = Officialdoc::model()->fetchByPk($docId);
        $categoryName = OfficialdocCategory::model()->fetchCateNameByCatid($officialdoc["catid"]);

        if ($data["status"] == "1") {
            $publishScope = array("deptid" => $officialdoc["deptid"], "positionid" => $officialdoc["positionid"], "uid" => $officialdoc["uid"]);
            $uidArr = OfficialdocUtil::getScopeUidArr($publishScope);
            $config = array("{sender}" => $user["realname"], "{category}" => $categoryName, "{subject}" => $officialdoc["subject"], "{content}" => $this->renderPartial("remindcontent", array("doc" => $officialdoc, "author" => $user["realname"]), true), "{url}" => Ibos::app()->urlManager->createUrl("officialdoc/officialdoc/show", array("docid" => $docId)));

            if (0 < count($uidArr)) {
                Notify::model()->sendNotify($uidArr, "officialdoc_message", $config, $uid);
            }

            $wbconf = WbCommonUtil::getSetting(true);
            if (isset($wbconf["wbmovement"]["article"]) && ($wbconf["wbmovement"]["article"] == 1)) {
                $publishScope = array("deptid" => $officialdoc["deptid"], "positionid" => $officialdoc["positionid"], "uid" => $officialdoc["uid"]);
                $data = array("title" => Ibos::lang("Feed title", "", array("{subject}" => $officialdoc["subject"], "{url}" => Ibos::app()->urlManager->createUrl("officialdoc/officialdoc/show", array("docid" => $docId)))), "body" => $officialdoc["subject"], "actdesc" => Ibos::lang("Post officialdoc"), "userid" => $publishScope["uid"], "deptid" => $publishScope["deptid"], "positionid" => $publishScope["positionid"]);
                WbfeedUtil::pushFeed($uid, "officialdoc", "officialdoc", $docId, $data);
            }

            UserUtil::updateCreditByAction("addofficialdoc", $uid);
        } elseif ($data["status"] == "2") {
            $this->SendPending($officialdoc, $uid);
        }

        $this->success(Ibos::lang("Save succeed", "message"), $this->createUrl("officialdoc/index"));
    }

    private function sendPending($doc, $uid)
    {
        $category = OfficialdocCategory::model()->fetchByPk($doc["catid"]);
        $approval = Approval::model()->fetchNextApprovalUids($category["aid"], 0);

        if (!empty($approval)) {
            if ($approval["step"] == "publish") {
                $this->verifyComplete($doc["docid"], $uid);
            } else {
                OfficialdocApproval::model()->deleteAll("docid={$doc["docid"]}");
                OfficialdocApproval::model()->recordStep($doc["docid"], $uid);
                $sender = User::model()->fetchRealnameByUid($uid);
                $config = array("{sender}" => $sender, "{subject}" => $doc["subject"], "{category}" => $category["name"], "{url}" => $this->createUrl("officialdoc/index", array("type" => "notallow")), "{content}" => $this->renderPartial("remindcontent", array("doc" => $doc, "author" => $sender), true));

                foreach ($approval["uids"] as $k => $approvalUid) {
                    if (!OfficialdocUtil::checkReadScope($approvalUid, $doc)) {
                        unset($approval["uids"][$k]);
                    }
                }

                Notify::model()->sendNotify($approval["uids"], "officialdoc_verify_message", $config, $uid);
            }
        }
    }

    private function update()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $docid = $_POST["docid"];
            $uid = Ibos::app()->user->uid;
            $data = $_POST;
            $publicScope = OfficialdocUtil::handleSelectBoxData(StringUtil::getId($data["publishScope"], true));
            $data["uid"] = $publicScope["uid"];
            $data["positionid"] = $publicScope["positionid"];
            $data["deptid"] = $publicScope["deptid"];
            $ccScope = OfficialdocUtil::handleSelectBoxData(StringUtil::getId($data["ccScope"], true), false);
            $data["ccuid"] = $ccScope["uid"];
            $data["ccpositionid"] = $ccScope["positionid"];
            $data["ccdeptid"] = $ccScope["deptid"];
            $data["approver"] = $uid;
            $data["docno"] = $_POST["docNo"];
            $data["commentstatus"] = (isset($data["commentstatus"]) ? $data["commentstatus"] : 0);
            $data["uptime"] = TIMESTAMP;
            $data["version"] = $data["version"] + 1;
            $version = Officialdoc::model()->fetchByPk($_POST["docid"]);
            $version["editor"] = $uid;
            $version["reason"] = $data["reason"];
            $version["uptime"] = TIMESTAMP;
            OfficialdocVersion::model()->add($version);

            if ($data["status"] == 2) {
                $catid = intval($data["catid"]);
                $category = OfficialdocCategory::model()->fetchByPk($catid);
                $data["status"] = (empty($category["aid"]) ? 1 : 2);
                $data["approver"] = (!empty($category["aid"]) ? 0 : $uid);
            }

            $attachmentid = trim($_POST["attachmentid"], ",");

            if (!empty($attachmentid)) {
                AttachUtil::updateAttach($attachmentid);
                Officialdoc::model()->modify($docid, array("attachmentid" => $attachmentid));
            }

            $attributes = Officialdoc::model()->create($data);
            Officialdoc::model()->updateByPk($data["docid"], $attributes);
            $doc = Officialdoc::model()->fetchByPk($data["docid"]);
            $this->sendPending($doc, $uid);
            OfficialdocBack::model()->deleteAll("docid = $docid");
            $this->success(Ibos::lang("Update succeed", "message"), $this->createUrl("officialdoc/index"));
        }
    }

    private function search()
    {
        $type = EnvUtil::getRequest("type");
        $conditionCookie = MainUtil::getCookie("condition");

        if (empty($conditionCookie)) {
            MainUtil::setCookie("condition", $this->_condition, 10 * 60);
        }

        if ($type == "advanced_search") {
            $this->_condition = OfficialdocUtil::joinSearchCondition($_POST["search"], $this->_condition);
        } elseif ($type == "normal_search") {
            $keyword = $_POST["keyword"];
            $this->_condition = " subject LIKE '%$keyword%' ";
            MainUtil::setCookie("keyword", $keyword, 10 * 60);
        } else {
            $this->_condition = $conditionCookie;
        }

        if ($this->_condition != MainUtil::getCookie("condition")) {
            MainUtil::setCookie("condition", $this->_condition, 10 * 60);
        }
    }

    public function actionShow()
    {
        if (EnvUtil::getRequest("op") == "sign") {
            $this->sign();
            exit();
        }

        $uid = Ibos::app()->user->uid;
        $docid = intval(EnvUtil::getRequest("docid"));
        $version = EnvUtil::getRequest("version");

        if (empty($docid)) {
            $this->error(Ibos::lang("Parameters error", "error"));
        }

        $officialDoc = Officialdoc::model()->fetchByPk($docid);

        if ($version) {
            $versionData = OfficialdocVersion::model()->fetchByAttributes(array("docid" => $docid, "version" => $version));
            $officialDoc = array_merge($officialDoc, $versionData);
        }

        if (!empty($officialDoc)) {
            if (!OfficialdocUtil::checkReadScope($uid, $officialDoc)) {
                $this->error(Ibos::lang("You do not have permission to read the officialdoc"), $this->createUrl("officialdoc/index"));
            }

            $data = ICOfficialdoc::getShowData($officialDoc);
            $signInfo = OfficialdocReader::model()->fetchSignInfo($docid, $uid);
            OfficialdocReader::model()->addReader($docid, $uid);
            Officialdoc::model()->updateClickCount($docid, $data["clickcount"]);
            $needSignUids = Officialdoc::model()->fetchAllUidsByDocId($docid);
            $needSign = in_array($uid, $needSignUids);
            $params = array("data" => $data, "signInfo" => $signInfo, "dashboardConfig" => Ibos::app()->setting->get("setting/docconfig"), "needSign" => $needSign);

            if ($data["rcid"]) {
                $params["rcType"] = RcType::model()->fetchByPk($data["rcid"]);
            }

            if ($officialDoc["status"] == 2) {
                $temp[0] = $params["data"];
                $temp = ICOfficialdoc::handleApproval($temp);
                $params["data"] = $temp[0];
                $params["isApprovaler"] = $this->checkIsApprovaler($officialDoc, $uid);
            }

            if (!empty($data["attachmentid"])) {
                $params["attach"] = AttachUtil::getAttach($data["attachmentid"]);
            }

            $this->setPageTitle(Ibos::lang("Show officialdoc"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Information center")),
                array("name" => Ibos::lang("Officialdoc"), "url" => $this->createUrl("officialdoc/index")),
                array("name" => Ibos::lang("Show officialdoc"))
            ));
            $this->render("show", $params);
        } else {
            $this->error(Ibos::lang("No permission or officialdoc not exists"), $this->createUrl("officialdoc/index"));
        }
    }

    private function getSign()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $docid = EnvUtil::getRequest("docid");
            $signedInfos = OfficialdocReader::model()->fetchSignedByDocId($docid);
            $signedUsersTemp = array();

            foreach ($signedInfos as $sign) {
                $uid = $sign["uid"];
                $signedUsersTemp[$uid] = User::model()->fetchByUid($uid);
                $signedUsersTemp[$uid]["signInfo"] = $sign;
            }

            $signedUsers = UserUtil::handleUserGroupByDept($signedUsersTemp);
            $params = array("signUsers" => $this->handleShowData($signedUsers), "signedCount" => count($signedInfos));
            $signAlias = "application.modules.officialdoc.views.officialdoc.signDetail";
            $signView = $this->renderPartial($signAlias, $params, true);
            $this->ajaxReturn(array("isSuccess" => true, "signView" => $signView));
        }
    }

    private function getUnSign()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $docid = EnvUtil::getRequest("docid");
            $uids = Officialdoc::model()->fetchAllUidsByDocId($docid);
            $signedUids = OfficialdocReader::model()->fetchSignedUidsByDocId($docid);
            $unSignedUids = array_diff($uids, $signedUids);
            $unSignedUsersTemp = User::model()->fetchAllByUids($unSignedUids);
            $unSignedUsers = UserUtil::handleUserGroupByDept($unSignedUsersTemp);
            $params = array("unsignUids" => CJSON::encode($unSignedUids), "unsignUsers" => $this->handleShowData($unSignedUsers), "unsignedCount" => count($unSignedUids));
            $unsignAlias = "application.modules.officialdoc.views.officialdoc.unsignDetail";
            $unsignView = $this->renderPartial($unsignAlias, $params, true);
            $this->ajaxReturn(array("isSuccess" => true, "unsignView" => $unsignView));
        }
    }

    private function remind()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $docid = EnvUtil::getRequest("docid");
            $docTitle = EnvUtil::getRequest("docTitle");
            $getUids = EnvUtil::getRequest("uids");
            $uid = Ibos::app()->user->uid;

            if (empty($getUids)) {
                $this->ajaxReturn(array("isSuccess" => false, "info" => Ibos::lang("No user to remind")));
            }

            $config = array("{name}" => User::model()->fetchRealnameByUid($uid), "{url}" => $this->createUrl("officialdoc/show", array("docid" => $docid)), "{title}" => $docTitle);

            if (0 < count($getUids)) {
                Notify::model()->sendNotify($getUids, "officialdoc_sign_remind", $config, $uid);
            }

            $this->ajaxReturn(array("isSuccess" => true, "info" => Ibos::lang("Remind succeed")));
        }
    }

    private function handleShowData($datas)
    {
        $user = User::model()->fetchByUid(Ibos::app()->user->uid);
        $self = array();

        foreach ($datas as $deptid => $data) {
            if ($deptid == $user["deptid"]) {
                $self[$deptid] = $data;
                unset($datas[$deptid]);
                break;
            }
        }

        if (!empty($self)) {
            $datas = array_merge($self, $datas);
        }

        return $datas;
    }

    private function getVersion()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $docid = EnvUtil::getRequest("docid");
            $versionData = OfficialdocVersion::model()->fetchAllByDocid($docid);
            $this->ajaxReturn($versionData);
        }
    }

    private function sign()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $docid = EnvUtil::getRequest("docid");
            $uid = Ibos::app()->user->uid;
            OfficialdocReader::model()->updateSignByDocid($docid, $uid);
            $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Sign for success"), "signtime" => date("Y年m月d日 H:i", TIMESTAMP)));
        }
    }

    private function checkIsApprovaler($doc, $uid)
    {
        $res = false;
        $docApproval = OfficialdocApproval::model()->fetchLastStep($doc["docid"]);
        $category = OfficialdocCategory::model()->fetchByPk($doc["catid"]);

        if (!empty($category["aid"])) {
            $approval = Approval::model()->fetchByPk($category["aid"]);
            $nextApproval = Approval::model()->fetchNextApprovalUids($approval["id"], $docApproval["step"]);

            if (in_array($uid, $nextApproval["uids"])) {
                $res = true;
            }
        }

        return $res;
    }

    private function verify()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $uid = Ibos::app()->user->uid;
            $docids = trim(EnvUtil::getRequest("docids"), ",");
            $ids = explode(",", $docids);

            if (empty($ids)) {
                $this->ajaxReturn(array("isSuccess" => false, "info" => Ibos::lang("Parameters error", "error")));
            }

            $sender = User::model()->fetchRealnameByUid($uid);

            foreach ($ids as $docid) {
                $docApproval = OfficialdocApproval::model()->fetchLastStep($docid);

                if (empty($docApproval)) {
                    $this->verifyComplete($docid, $uid);
                } else {
                    $doc = Officialdoc::model()->fetchByPk($docApproval["docid"]);
                    $category = OfficialdocCategory::model()->fetchByPk($doc["catid"]);
                    $approval = Approval::model()->fetch("id={$category["aid"]}");
                    $curApproval = Approval::model()->fetchNextApprovalUids($approval["id"], $docApproval["step"]);
                    $nextApproval = Approval::model()->fetchNextApprovalUids($approval["id"], $docApproval["step"] + 1);

                    if (!in_array($uid, $curApproval["uids"])) {
                        $this->ajaxReturn(array("isSuccess" => false, "info" => Ibos::lang("You do not have permission to verify the official")));
                    }

                    if (!empty($nextApproval)) {
                        if ($nextApproval["step"] == "publish") {
                            $this->verifyComplete($docid, $uid);
                        } else {
                            OfficialdocApproval::model()->recordStep($docid, $uid);
                            $config = array("{sender}" => $sender, "{subject}" => $doc["subject"], "{category}" => $category["name"], "{content}" => $this->renderPartial("remindcontent", array("doc" => $doc, "author" => $sender), true), "{url}" => $this->createUrl("officialdoc/index", array("type" => "notallow")));

                            foreach ($nextApproval["uids"] as $k => $approvalUid) {
                                if (!OfficialdocUtil::checkReadScope($approvalUid, $doc)) {
                                    unset($nextApproval["uids"][$k]);
                                }
                            }

                            Notify::model()->sendNotify($nextApproval["uids"], "officialdoc_verify_message", $config, $uid);
                            Officialdoc::model()->updateAllStatusByDocids($docid, 2, $uid);
                        }
                    }
                }
            }

            $this->ajaxReturn(array("isSuccess" => true, "info" => Ibos::lang("Verify succeed", "message")));
        }
    }

    private function verifyComplete($docid, $uid)
    {
        Officialdoc::model()->updateAllStatusByDocids($docid, 1, $uid);
        OfficialdocApproval::model()->deleteAll("docid=$docid");
        $doc = Officialdoc::model()->fetchByPk($docid);

        if (!empty($doc)) {
            $wbconf = WbCommonUtil::getSetting(true);
            if (isset($wbconf["wbmovement"]["article"]) && ($wbconf["wbmovement"]["article"] == 1)) {
                $publishScope = array("deptid" => $doc["deptid"], "positionid" => $doc["positionid"], "uid" => $doc["uid"]);
                $data = array("title" => Ibos::lang("Feed title", "", array("{subject}" => $doc["subject"], "{url}" => Ibos::app()->urlManager->createUrl("officialdoc/officialdoc/show", array("docid" => $doc["docid"])))), "body" => $doc["content"], "actdesc" => Ibos::lang("Post officialdoc"), "userid" => $publishScope["uid"], "deptid" => $publishScope["deptid"], "positionid" => $publishScope["positionid"]);
                WbfeedUtil::pushFeed($doc["author"], "officialdoc", "officialdoc", $doc["docid"], $data);
            }

            UserUtil::updateCreditByAction("addofficialdoc", $doc["author"]);
        }
    }

    private function back()
    {
        $uid = Ibos::app()->user->uid;
        $docIds = trim(EnvUtil::getRequest("docids"), ",");
        $reason = StringUtil::filterCleanHtml(EnvUtil::getRequest("reason"));
        $ids = explode(",", $docIds);

        if (empty($ids)) {
            $this->ajaxReturn(array("isSuccess" => false, "info" => Ibos::lang("Parameters error", "error")));
        }

        $sender = User::model()->fetchRealnameByUid($uid);

        foreach ($ids as $docId) {
            $doc = Officialdoc::model()->fetchByPk($docId);
            $categoryName = OfficialdocCategory::model()->fetchCateNameByCatid($doc["catid"]);

            if (!$this->checkIsApprovaler($doc, $uid)) {
                $this->ajaxReturn(array("isSuccess" => false, "info" => Ibos::lang("You do not have permission to verify the official")));
            }

            $config = array("{sender}" => $sender, "{subject}" => $doc["subject"], "{category}" => $categoryName, "{content}" => $reason, "{url}" => $this->createUrl("officialdoc/index", array("type" => "notallow")));
            Notify::model()->sendNotify($doc["author"], "official_back_message", $config, $uid);
            OfficialdocBack::model()->addBack($docId, $uid, $reason, TIMESTAMP);
        }

        $this->ajaxReturn(array("isSuccess" => true, "info" => Ibos::lang("Operation succeed", "message")));
    }

    private function move()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $docids = EnvUtil::getRequest("docids");
            $catid = EnvUtil::getRequest("catid");
            if (!empty($docids) && !empty($catid)) {
                Officialdoc::model()->updateAllCatidByDocids(ltrim($docids, ","), $catid);
                $this->ajaxReturn(array("isSuccess" => true));
            } else {
                $this->ajaxReturn(array("isSuccess" => false));
            }
        }
    }

    private function top()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $docids = EnvUtil::getRequest("docids");
            $topEndTime = EnvUtil::getRequest("topEndTime");

            if (!empty($topEndTime)) {
                $topEndTime = (strtotime($topEndTime) + (24 * 60 * 60)) - 1;
                Officialdoc::model()->updateTopStatus($docids, 1, TIMESTAMP, $topEndTime);
                $this->ajaxReturn(array("isSuccess" => true, "info" => Ibos::lang("Top succeed")));
            } else {
                Officialdoc::model()->updateTopStatus($docids, 0, "", "");
                $this->ajaxReturn(array("isSuccess" => true, "info" => Ibos::lang("Unstuck success")));
            }
        }
    }

    private function highLight()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $docids = trim(EnvUtil::getRequest("docids"), ",");
            $highLight = array();
            $highLight["endTime"] = EnvUtil::getRequest("highlightEndTime");
            $highLight["bold"] = EnvUtil::getRequest("bold");
            $highLight["color"] = EnvUtil::getRequest("color");
            $highLight["italic"] = EnvUtil::getRequest("italic");
            $highLight["underline"] = EnvUtil::getRequest("underline");
            $data = OfficialdocUtil::processHighLightRequestData($highLight);

            if (empty($data["highlightendtime"])) {
                Officialdoc::model()->updateHighlightStatus($docids, 0, "", "");
                $this->ajaxReturn(array("isSuccess" => true, "info" => Ibos::lang("Unhighlighting success")));
            } else {
                Officialdoc::model()->updateHighlightStatus($docids, 1, $data["highlightstyle"], $data["highlightendtime"]);
                $this->ajaxReturn(array("isSuccess" => true, "info" => Ibos::lang("Highlight succeed")));
            }
        }
    }

    private function getRcType()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $typeid = EnvUtil::getRequest("typeid");
            $rcType = RcType::model()->fetchByPk($typeid);
            $this->ajaxReturn($rcType);
        }
    }

    private function prewiew()
    {
        $this->setPageTitle(Ibos::lang("Preview officialdoc"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Information center")),
            array("name" => Ibos::lang("Officialdoc"), "url" => $this->createUrl("officialdoc/index")),
            array("name" => Ibos::lang("Preview officialdoc"))
        ));
        $this->render("prewiew", array("content" => $_POST["content"]));
    }
}
