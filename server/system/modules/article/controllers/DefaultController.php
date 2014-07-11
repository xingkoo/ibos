<?php

class ArticleDefaultController extends ArticleBaseController
{
    public function actionIndex()
    {
        $op = EnvUtil::getRequest("op");
        $option = (empty($op) ? "default" : $op);
        $routes = array("default", "show", "search", "getReader", "getReaderByDeptId", "getVoteCount", "preview");

        if (!in_array($option, $routes)) {
            $this->error(Ibos::lang("Can not find the path"), $this->createUrl("default/index"));
        }

        $uid = Ibos::app()->user->uid;

        if ($option == "default") {
            $catid = intval(EnvUtil::getRequest("catid"));
            $childCatIds = "";

            if (!empty($catid)) {
                $this->catid = $catid;
                $childCatIds = ArticleCategory::model()->fetchCatidByPid($this->catid, true);
            }

            if (EnvUtil::getRequest("param") == "search") {
                $this->search();
            }

            Article::model()->cancelTop();
            Article::model()->updateIsOverHighLight();
            $type = EnvUtil::getRequest("type");
            $articleidArr = ArticleReader::model()->fetchArticleidsByUid($uid);
            $this->condition = ArticleUtil::joinListCondition($type, $articleidArr, $childCatIds, $this->condition, $this->catid);
            $datas = Article::model()->fetchAllAndPage($this->condition);
            $articleList = ICArticle::getListData($datas["datas"], $uid);
            $params = array("pages" => $datas["pages"], "datas" => $articleList, "categoryOption" => $this->getCategoryOption());

            if ($type == "notallow") {
                $view = "approval";
                $params["datas"] = ICArticle::handleApproval($params["datas"]);
            } else {
                $view = "list";
            }

            $this->setPageTitle(Ibos::lang("Article"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Information center")),
                array("name" => Ibos::lang("Article"), "url" => $this->createUrl("default/index")),
                array("name" => Ibos::lang("Article list"))
            ));
            $this->render($view, $params);
        } else {
            $this->{$option}();
        }
    }

    private function search()
    {
        $type = EnvUtil::getRequest("type");
        $conditionCookie = MainUtil::getCookie("condition");

        if (empty($conditionCookie)) {
            MainUtil::setCookie("condition", $this->condition, 10 * 60);
        }

        if ($type == "advanced_search") {
            $this->condition = ArticleUtil::joinSearchCondition($_POST["search"], $this->condition);
        } elseif ($type == "normal_search") {
            $keyword = $_POST["keyword"];
            $this->condition = " subject LIKE '%$keyword%' ";
            MainUtil::setCookie("keyword", $keyword, 10 * 60);
        } else {
            $this->condition = $conditionCookie;
        }

        if ($this->condition != MainUtil::getCookie("condition")) {
            MainUtil::setCookie("condition", $this->condition, 10 * 60);
        }
    }

    private function show()
    {
        $articleId = intval($_GET["articleid"]);

        if (empty($articleId)) {
            $this->error(Ibos::lang("Parameters error", "error"));
        }

        $article = Article::model()->fetchByPk($articleId);

        if (empty($article)) {
            $this->error(Ibos::lang("No permission or article not exists"), $this->createUrl("default/index"));
        }

        $uid = Yii::app()->user->uid;

        if (!ArticleUtil::checkReadScope($uid, $article)) {
            $this->error(Ibos::lang("You do not have permission to read the article"), $this->createUrl("default/index"));
        }

        $data = ICArticle::getShowData($article);
        ArticleReader::model()->addReader($articleId, $uid);
        Article::model()->updateClickCount($articleId, $data["clickcount"]);
        $dashboardConfig = $this->getDashboardConfig();

        if ($data["type"] == parent::ARTICLE_TYPE_LINK) {
            $urlArr = parse_url($data["url"]);
            $url = (isset($urlArr["scheme"]) ? $data["url"] : "http://" . $data["url"]);
            header("Location: " . $url);
            exit();
        }

        $params = array("data" => $data, "dashboardConfig" => $dashboardConfig, "isInstallEmail" => $this->getEmailInstalled());

        if (!empty($data["attachmentid"])) {
            $params["attach"] = AttachUtil::getAttach($data["attachmentid"]);
        }

        if ($data["type"] == parent::ARTICLE_TYPE_PICTURE) {
            $params["pictureData"] = ArticlePicture::model()->fetchPictureByArticleId($articleId);
        }

        if ($article["status"] == 2) {
            $temp[0] = $params["data"];
            $temp = ICArticle::handleApproval($temp);
            $params["data"] = $temp[0];
            $params["isApprovaler"] = $this->checkIsApprovaler($article, $uid);
        }

        $this->setPageTitle(Ibos::lang("Show Article"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Information center")),
            array("name" => Ibos::lang("Article"), "url" => $this->createUrl("default/index")),
            array("name" => Ibos::lang("Show Article"))
        ));
        $this->render("show", $params);
    }

    public function actionAdd()
    {
        $op = EnvUtil::getRequest("op");
        $option = (empty($op) ? "default" : $op);
        $routes = array("submit", "default", "checkIsAllowPublish");

        if (!in_array($option, $routes)) {
            $this->error(Ibos::lang("Can not find the path"), $this->createUrl("default/index"));
        }

        if ($option == "default") {
            if (!empty($_GET["catid"])) {
                $this->catid = $_GET["catid"];
            }

            $allowPublish = ArticleCategory::model()->checkIsAllowPublish($this->catid, Ibos::app()->user->uid);
            $params = array("categoryOption" => $this->getCategoryOption(), "uploadConfig" => AttachUtil::getUploadConfig(), "dashboardConfig" => $this->getDashboardConfig(), "allowPublish" => $allowPublish);
            $this->setPageTitle(Ibos::lang("Add Article"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Information center")),
                array("name" => Ibos::lang("Article"), "url" => $this->createUrl("default/index")),
                array("name" => Ibos::lang("Add Article"))
            ));
            $this->render("add", $params);
        } elseif ($option == "submit") {
            if (ICArticle::formCheck($_POST) == false) {
                $this->error(Ibos::lang("Title do not empty"), $this->createUrl("default/add"));
            }

            $uid = Ibos::app()->user->uid;
            $this->beforeSaveData($_POST);
            $articleId = $this->addOrUpdateArticle("add", $_POST, $uid);

            if ($_POST["type"] == parent::ARTICLE_TYPE_PICTURE) {
                $pidids = $_POST["picids"];

                if (!empty($pidids)) {
                    AttachUtil::updateAttach($pidids);
                    $attach = AttachUtil::getAttach($pidids, true, true, false, false, true);
                    $this->addPicture($attach, $articleId);
                }
            }

            $attachmentid = trim($_POST["attachmentid"], ",");

            if (!empty($attachmentid)) {
                AttachUtil::updateAttach($attachmentid);
                Article::model()->modify($articleId, array("attachmentid" => $attachmentid));
            }

            $dashboardConfig = $this->getDashboardConfig();
            if (isset($_POST["votestatus"]) && $this->getVoteInstalled() && $dashboardConfig["articlevoteenable"]) {
                $voteItemType = $_POST["voteItemType"];
                $type = ($voteItemType == 1 ? "vote" : "imageVote");
                if (!empty($voteItemType) && (trim($_POST[$type]["subject"]) != "")) {
                    $voteId = $this->addOrUpdateVote($_POST[$type], $articleId, $uid, "add");
                    $this->addVoteItem($_POST[$type], $voteId, $voteItemType);
                } else {
                    Article::model()->modify($articleId, array("votestatus" => 0));
                }
            }

            $user = User::model()->fetchByUid($uid);
            $article = Article::model()->fetchByPk($articleId);
            $categoryName = ArticleCategory::model()->fetchCateNameByCatid($article["catid"]);

            if ($article["status"] == "1") {
                $publishScope = array("deptid" => $article["deptid"], "positionid" => $article["positionid"], "uid" => $article["uid"]);
                $uidArr = ArticleUtil::getScopeUidArr($publishScope);
                $config = array("{sender}" => $user["realname"], "{category}" => $categoryName, "{subject}" => $article["subject"], "{content}" => $this->renderPartial("remindcontent", array("article" => $article, "author" => $user["realname"]), true), "{url}" => Ibos::app()->urlManager->createUrl("article/default/index", array("op" => "show", "articleid" => $articleId)));

                if (0 < count($uidArr)) {
                    Notify::model()->sendNotify($uidArr, "article_message", $config, $uid);
                }

                $wbconf = WbCommonUtil::getSetting(true);
                if (isset($wbconf["wbmovement"]["article"]) && ($wbconf["wbmovement"]["article"] == 1)) {
                    $publishScope = array("deptid" => $article["deptid"], "positionid" => $article["positionid"], "uid" => $article["uid"]);
                    $data = array("title" => Ibos::lang("Feed title", "", array("{subject}" => $article["subject"], "{url}" => Ibos::app()->urlManager->createUrl("article/default/index", array("op" => "show", "articleid" => $articleId)))), "body" => $article["subject"], "actdesc" => Ibos::lang("Post news"), "userid" => $publishScope["uid"], "deptid" => $publishScope["deptid"], "positionid" => $publishScope["positionid"]);

                    if ($_POST["type"] == self::ARTICLE_TYPE_PICTURE) {
                        $type = "postimage";
                        $picids = explode(",", $pidids);
                        $data["attach_id"] = array_shift($picids);
                    } else {
                        $type = "post";
                    }

                    WbfeedUtil::pushFeed(Ibos::app()->user->uid, "article", "article", $articleId, $data, $type);
                }

                UserUtil::updateCreditByAction("addarticle", $uid);
            } elseif ($article["status"] == "2") {
                $this->SendPending($article, $uid);
            }

            $this->success(Ibos::lang("Save succeed", "message"), $this->createUrl("default/index"));
        } else {
            $this->{$option}();
        }
    }

    private function sendPending($article, $uid)
    {
        $category = ArticleCategory::model()->fetchByPk($article["catid"]);
        $approval = Approval::model()->fetchNextApprovalUids($category["aid"], 0);

        if (!empty($approval)) {
            if ($approval["step"] == "publish") {
                $this->verifyComplete($article["articleid"], $uid);
            } else {
                ArticleApproval::model()->deleteAll("articleid={$article["articleid"]}");
                ArticleApproval::model()->recordStep($article["articleid"], $uid);
                $sender = User::model()->fetchRealnameByUid($uid);
                $config = array("{sender}" => $sender, "{subject}" => $article["subject"], "{category}" => $category["name"], "{url}" => $this->createUrl("default/index", array("type" => "notallow")), "{content}" => $this->renderPartial("remindcontent", array("article" => $article, "author" => $sender), true));

                foreach ($approval["uids"] as $k => $approvalUid) {
                    if (!ArticleUtil::checkReadScope($approvalUid, $article)) {
                        unset($approval["uids"][$k]);
                    }
                }

                Notify::model()->sendNotify($approval["uids"], "article_verify_message", $config, $uid);
            }
        }
    }

    protected function checkIsAllowPublish()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $catid = intval(EnvUtil::getRequest("catid"));
            $uid = intval(EnvUtil::getRequest("uid"));
            $isAllow = ArticleCategory::model()->checkIsAllowPublish($catid, $uid);
            $this->ajaxReturn(array("isSuccess" => !!$isAllow));
        }
    }

    public function actionEdit()
    {
        $op = EnvUtil::getRequest("op");
        $option = (empty($op) ? "default" : $op);
        $routes = array("default", "update", "verify", "move", "top", "highLight", "clickVote", "back");

        if (!in_array($option, $routes)) {
            $this->error(Ibos::lang("Can not find the path"));
        }

        if ($option == "default") {
            $articleId = EnvUtil::getRequest("articleid");

            if (empty($articleId)) {
                $this->error(Ibos::lang("Parameters error", "error"));
            }

            $data = Article::model()->fetchByPk($articleId);

            if (empty($data)) {
                $this->error(Ibos::lang("No permission or article not exists"));
            }

            $data["publishScope"] = ArticleUtil::joinSelectBoxValue($data["deptid"], $data["positionid"], $data["uid"]);
            $allowPublish = ArticleCategory::model()->checkIsAllowPublish($data["catid"], Ibos::app()->user->uid);
            $params = array("data" => $data, "categoryOption" => $this->getCategoryOption(), "uploadConfig" => AttachUtil::getUploadConfig(), "dashboardConfig" => $this->getDashboardConfig(), "allowPublish" => $allowPublish);

            if (!empty($data["attachmentid"])) {
                $params["attach"] = AttachUtil::getAttach($data["attachmentid"]);
            }

            if ($data["type"] == parent::ARTICLE_TYPE_PICTURE) {
                $params["pictureData"] = ArticlePicture::model()->fetchPictureByArticleId($articleId);
                $params["picids"] = "";

                foreach ($params["pictureData"] as $k => $value) {
                    $params["pictureData"][$k]["filepath"] = FileUtil::fileName($value["filepath"]);
                    $params["picids"] .= $value["aid"] . ",";
                }

                $params["picids"] = substr($params["picids"], 0, -1);
            }

            $this->setPageTitle(Ibos::lang("Edit Article"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Information center")),
                array("name" => Ibos::lang("Article"), "url" => $this->createUrl("default/index")),
                array("name" => Ibos::lang("Edit Article"))
            ));
            $this->render("edit", $params);
        } else {
            $this->{$option}();
        }
    }

    private function update()
    {
        $uid = Ibos::app()->user->uid;
        $articleId = $_POST["articleid"];
        $this->beforeSaveData($_POST);
        $this->addOrUpdateArticle("update", $_POST, $uid);

        if ($_POST["type"] == parent::ARTICLE_TYPE_PICTURE) {
            $pidids = $_POST["picids"];

            if (!empty($pidids)) {
                ArticlePicture::model()->deleteAll("articleid=:articleid", array(":articleid" => $articleId));
                AttachUtil::updateAttach($pidids);
                $attach = AttachUtil::getAttach($pidids, true, true, false, false, true);
                $this->addPicture($attach, $articleId);
            }
        }

        $attachmentid = trim($_POST["attachmentid"], ",");

        if (!empty($attachmentid)) {
            AttachUtil::updateAttach($attachmentid);
            Article::model()->modify($articleId, array("attachmentid" => $attachmentid));
        }

        $dashboardConfig = $this->getDashboardConfig();
        if (isset($_POST["votestatus"]) && $this->getVoteInstalled() && $dashboardConfig["articlevoteenable"]) {
            $voteItemType = $_POST["voteItemType"];
            $type = ($voteItemType == 1 ? "vote" : "imageVote");
            if (!empty($voteItemType) && (trim($_POST[$type]["subject"]) != "")) {
                $this->updateVote($voteItemType, $type, $articleId, $uid);
                $rcord = Article::model()->fetch(array(
                    "select"    => array("votestatus"),
                    "condition" => "articleid=:articleid",
                    "params"    => array(":articleid" => $articleId)
                ));

                if ($rcord["votestatus"] == 0) {
                    Article::model()->modify($articleId, array("votestatus" => 1));
                }
            } else {
                Article::model()->modify($articleId, array("votestatus" => 0));
            }
        }

        $user = User::model()->fetchByUid($uid);
        $article = Article::model()->fetchByPk($articleId);
        $categoryName = ArticleCategory::model()->fetchCateNameByCatid($article["catid"]);
        if (!empty($_POST["msgRemind"]) && ($article["status"] == 1)) {
            $publishScope = array("deptid" => $article["deptid"], "positionid" => $article["positionid"], "uid" => $article["uid"]);
            $uidArr = ArticleUtil::getScopeUidArr($publishScope);
            $config = array("{sender}" => $user["realname"], "{category}" => $categoryName, "{subject}" => $article["subject"], "{content}" => $this->renderPartial("remindcontent", array("article" => $article, "author" => $user["realname"]), true), "{url}" => Ibos::app()->urlManager->createUrl("article/default/index", array("op" => "show", "articleid" => $article["articleid"])));

            if (0 < count($uidArr)) {
                Notify::model()->sendNotify($uidArr, "article_message", $config, $uid);
            }
        }

        if ($article["status"] == 2) {
            $this->sendPending($article, $uid);
        }

        ArticleBack::model()->deleteAll("articleid = $articleId");
        $this->success(Ibos::lang("Update succeed"), $this->createUrl("default/index"));
    }

    private function beforeSaveData($postData)
    {
        if (isset($postData["type"])) {
            if ($postData["type"] == parent::ARTICLE_TYPE_PICTURE) {
                if (empty($postData["picids"])) {
                    $this->error(Ibos::lang("Picture empty tip"), $this->createUrl("default/add"));
                }
            } elseif ($postData["type"] == parent::ARTICLE_TYPE_DEFAULT) {
                if (empty($postData["content"])) {
                    $this->error(Ibos::lang("Content empty tip"), $this->createUrl("default/add"));
                }
            } elseif ($postData["type"] == parent::ARTICLE_TYPE_LINK) {
                if (empty($postData["url"])) {
                    $this->error(Ibos::lang("Url empty tip"), $this->createUrl("default/add"));
                }
            }
        }
    }

    private function addOrUpdateArticle($type, $data, $uid)
    {
        $attributes = Article::model()->create();
        $attributes["approver"] = $uid;
        $attributes["author"] = $uid;
        $publishScope = StringUtil::getId($data["publishScope"], true);
        $publishScope = ArticleUtil::handleSelectBoxData($publishScope);
        $attributes["deptid"] = $publishScope["deptid"];
        $attributes["positionid"] = $publishScope["positionid"];
        $attributes["uid"] = $publishScope["uid"];
        $attributes["votestatus"] = (isset($data["votestatus"]) ? $data["votestatus"] : 0);
        $attributes["commentstatus"] = (isset($data["commentstatus"]) ? $data["commentstatus"] : 0);

        if ($attributes["status"] == 2) {
            $catid = intval($attributes["catid"]);
            $category = ArticleCategory::model()->fetchByPk($catid);
            $attributes["status"] = (empty($category["aid"]) ? 1 : 2);
            $attributes["approver"] = (!empty($category["aid"]) ? 0 : $uid);
        }

        if ($type == "add") {
            $attributes["addtime"] = TIMESTAMP;
            return Article::model()->add($attributes, true);
        } elseif ($type == "update") {
            $attributes["uptime"] = TIMESTAMP;
            return Article::model()->updateByPk($attributes["articleid"], $attributes);
        }
    }

    private function addPicture($attach, $articleId)
    {
        $sort = 0;

        foreach ($attach as $value) {
            $picture = array("articleid" => $articleId, "aid" => $value["aid"], "sort" => $sort, "addtime" => TIMESTAMP, "postip" => StringUtil::getSubIp(), "filename" => $value["filename"], "title" => "", "type" => $value["filetype"], "size" => $value["filesize"], "filepath" => $value["attachment"]);

            if (Yii::app()->setting->get("setting/articlethumbenable")) {
                list($thumbWidth, $thumbHeight) = explode(",", Yii::app()->setting->get("setting/articlethumbwh"));
                $imageInfo = ImageUtil::getImageInfo(FileUtil::fileName($picture["filepath"]));
                if (($imageInfo["width"] < $thumbWidth) && ($imageInfo["height"] < $thumbHeight)) {
                    $picture["thumb"] = 0;
                } else {
                    $sourceFileName = explode("/", $picture["filepath"]);
                    $sourceFileName[count($sourceFileName) - 1] = "thumb_" . $sourceFileName[count($sourceFileName) - 1];
                    $thumbName = implode("/", $sourceFileName);

                    if (LOCAL) {
                        ImageUtil::thumb($picture["filepath"], $thumbName, $thumbWidth, $thumbHeight);
                    } else {
                        $tempFile = FileUtil::getTempPath() . "tmp." . $picture["type"];
                        $orgImgname = Ibos::engine()->IO()->file()->fetchTemp(FileUtil::fileName($picture["filepath"]), $picture["type"]);
                        ImageUtil::thumb($orgImgname, $tempFile, $thumbWidth, $thumbHeight);
                        FileUtil::createFile($thumbName, file_get_contents($tempFile));
                    }

                    $picture["thumb"] = 1;
                }
            }

            ArticlePicture::model()->add($picture);
            $sort++;
        }
    }

    private function addOrUpdateVote($data, $articleId, $uid, $type, $voteId = "")
    {
        $vote = array("subject" => $data["subject"], "starttime" => TIMESTAMP, "endtime" => strtotime($data["endtime"]), "ismulti" => $data["ismulti"], "maxselectnum" => $data["maxselectnum"], "isvisible" => $data["isvisible"], "status" => 1, "uid" => $uid, "relatedmodule" => "article", "relatedid" => $articleId, "deadlinetype" => $data["deadlineType"]);

        if ($type == "add") {
            return Vote::model()->add($vote, true);
        } else {
            return Vote::model()->modify($voteId, $vote);
        }
    }

    private function addVoteItem($data, $voteId, $type)
    {
        foreach ($data["voteItem"] as $key => $value) {
            $voteItem = array("voteid" => $voteId, "type" => $type, "content" => $value);
            if (($type == 1) && !empty($value)) {
                VoteItem::model()->add($voteItem);
            } elseif ($type == 2) {
                if (!empty($data["picpath"][$key]) || !empty($value)) {
                    $voteItem["picpath"] = $data["picpath"][$key];
                    VoteItem::model()->add($voteItem);
                }
            }
        }
    }

    private function updateVote($voteItemType, $type, $articleId, $uid)
    {
        if (empty($_POST["voteid"])) {
            $voteId = $this->addOrUpdateVote($_POST[$type], $articleId, $uid, "add");
            $this->addVoteItem($_POST[$type], $voteId, $voteItemType);
        } else {
            $newVoteItemArr = $oldVoteItemArr = $delFlagItemId = array();

            foreach ($_POST[$type]["voteItem"] as $key => $value) {
                if (substr($key, 0, 3) == "new") {
                    $newVoteItemArr[$key] = $value;
                } else {
                    $oldVoteItemArr[$key] = $value;
                }
            }

            $voteData = Vote::model()->fetchVote("article", $articleId);
            $itemData = $voteData["voteItemList"];

            foreach ($itemData as $value) {
                if (!array_key_exists($value["itemid"], $oldVoteItemArr)) {
                    $delFlagItemId[] = $value["itemid"];
                }
            }

            $this->addOrUpdateVote($_POST[$type], $articleId, $uid, "update", $_POST["voteid"]);
            $data = array("voteItem" => $newVoteItemArr);

            if ($type == "imageVote") {
                $data["picpath"] = $_POST[$type]["picpath"];
            }

            $this->addVoteItem($data, $_POST["voteid"], $voteItemType);

            foreach ($oldVoteItemArr as $key => $value) {
                $voteItem = array("content" => $value);

                if ($type == "imageVote") {
                    $voteItem["picpath"] = $_POST[$type]["picpath"][$key];
                }

                VoteItem::model()->modify($key, $voteItem);
            }

            VoteItem::model()->deleteByPk($delFlagItemId);
        }

        return true;
    }

    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $articleids = trim(EnvUtil::getRequest("articleids"), ",");
            $attachmentids = "";
            $attachmentIdArr = Article::model()->fetchAllFieldValueByArticleids("attachmentid", $articleids);

            foreach ($attachmentIdArr as $attachmentid) {
                if (!empty($attachmentid)) {
                    $attachmentids .= $attachmentid . ",";
                }
            }

            $count = 0;

            if (!empty($attachmentids)) {
                $splitArray = explode(",", trim($attachmentids, ","));
                $attachmentidArray = array_unique($splitArray);
                $attachmentids = implode(",", $attachmentidArray);
                $count = AttachUtil::delAttach($attachmentids);
            }

            if ($this->getVoteInstalled()) {
                Vote::model()->deleteAllByRelationIdsAndModule($articleids, "article");
            }

            ArticlePicture::model()->deleteAllByArticleIds($articleids);
            Article::model()->deleteAllByArticleIds($articleids);
            ArticleApproval::model()->deleteByArtIds($articleids);
            $this->ajaxReturn(array("isSuccess" => true, "count" => $count, "msg" => Ibos::lang("Del succeed", "message")));
        }
    }

    private function checkIsApprovaler($article, $uid)
    {
        $res = false;
        $artApproval = ArticleApproval::model()->fetchLastStep($article["articleid"]);
        $category = ArticleCategory::model()->fetchByPk($article["catid"]);

        if (!empty($category["aid"])) {
            $approval = Approval::model()->fetchByPk($category["aid"]);
            $nextApproval = Approval::model()->fetchNextApprovalUids($approval["id"], $artApproval["step"]);

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
            $artIds = trim(EnvUtil::getRequest("articleids"), ",");
            $ids = explode(",", $artIds);

            if (empty($ids)) {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Parameters error", "error")));
            }

            $sender = User::model()->fetchRealnameByUid($uid);

            foreach ($ids as $artId) {
                $artApproval = ArticleApproval::model()->fetchLastStep($artId);

                if (empty($artApproval)) {
                    $this->verifyComplete($artId, $uid);
                } else {
                    $art = Article::model()->fetchByPk($artId);
                    $category = ArticleCategory::model()->fetchByPk($art["catid"]);
                    $approval = Approval::model()->fetch("id={$category["aid"]}");
                    $curApproval = Approval::model()->fetchNextApprovalUids($approval["id"], $artApproval["step"]);
                    $nextApproval = Approval::model()->fetchNextApprovalUids($approval["id"], $artApproval["step"] + 1);

                    if (!in_array($uid, $curApproval["uids"])) {
                        $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("You do not have permission to verify the article")));
                    }

                    if (!empty($nextApproval)) {
                        if ($nextApproval["step"] == "publish") {
                            $this->verifyComplete($artId, $uid);
                        } else {
                            ArticleApproval::model()->recordStep($artId, $uid);
                            $config = array("{sender}" => $sender, "{subject}" => $art["subject"], "{category}" => $category["name"], "{content}" => $this->renderPartial("remindcontent", array("article" => $art, "author" => $sender), true), "{url}" => $this->createUrl("default/index", array("type" => "notallow")));

                            foreach ($nextApproval["uids"] as $k => $approvalUid) {
                                if (!ArticleUtil::checkReadScope($approvalUid, $art)) {
                                    unset($nextApproval["uids"][$k]);
                                }
                            }

                            Notify::model()->sendNotify($nextApproval["uids"], "article_verify_message", $config, $uid);
                            Article::model()->updateAllStatusAndApproverByPks($artId, $uid, 2);
                        }
                    }
                }
            }

            $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Verify succeed", "message")));
        }
    }

    private function verifyComplete($artId, $uid)
    {
        Article::model()->updateAllStatusAndApproverByPks($artId, $uid, 1);
        ArticleApproval::model()->deleteAll("articleid=$artId");
        $article = Article::model()->fetchByPk($artId);

        if (!empty($article)) {
            $wbconf = WbCommonUtil::getSetting(true);
            if (isset($wbconf["wbmovement"]["article"]) && ($wbconf["wbmovement"]["article"] == 1)) {
                $publishScope = array("deptid" => $article["deptid"], "positionid" => $article["positionid"], "uid" => $article["uid"]);
                $data = array("title" => Ibos::lang("Feed title", "", array("{subject}" => $article["subject"], "{url}" => Ibos::app()->urlManager->createUrl("article/default/index", array("op" => "show", "articleid" => $article["articleid"])))), "body" => $article["content"], "actdesc" => Ibos::lang("Post news"), "userid" => $publishScope["uid"], "deptid" => $publishScope["deptid"], "positionid" => $publishScope["positionid"]);

                if ($article["type"] == self::ARTICLE_TYPE_PICTURE) {
                    $type = "postimage";
                    $picture = ArticlePicture::model()->fetchByAttributes(array("articleid" => $article["articleid"]));
                    $data["attach_id"] = $picture["aid"];
                } else {
                    $type = "post";
                }

                WbfeedUtil::pushFeed($article["author"], "article", "article", $article["articleid"], $data, $type);
            }

            UserUtil::updateCreditByAction("addarticle", $article["author"]);
        }
    }

    private function back()
    {
        $uid = Ibos::app()->user->uid;
        $artIds = trim(EnvUtil::getRequest("articleids"), ",");
        $reason = StringUtil::filterCleanHtml(EnvUtil::getRequest("reason"));
        $ids = explode(",", $artIds);

        if (empty($ids)) {
            $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Parameters error", "error")));
        }

        $sender = User::model()->fetchRealnameByUid($uid);

        foreach ($ids as $artId) {
            $art = Article::model()->fetchByPk($artId);
            $categoryName = ArticleCategory::model()->fetchCateNameByCatid($art["catid"]);

            if (!$this->checkIsApprovaler($art, $uid)) {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("You do not have permission to verify the article")));
            }

            $config = array("{sender}" => $sender, "{subject}" => $art["subject"], "{category}" => $categoryName, "{content}" => $reason, "{url}" => Ibos::app()->urlManager->createUrl("article/default/index", array("type" => "notallow")));
            Notify::model()->sendNotify($art["author"], "article_back_message", $config, $uid);
            ArticleBack::model()->addBack($artId, $uid, $reason, TIMESTAMP);
        }

        $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Operation succeed", "message")));
    }

    private function move()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $articleids = EnvUtil::getRequest("articleids");
            $catid = EnvUtil::getRequest("catid");
            if (!empty($articleids) && !empty($catid)) {
                Article::model()->updateAllCatidByArticleIds(ltrim($articleids, ","), $catid);
                $this->ajaxReturn(array("isSuccess" => true));
            } else {
                $this->ajaxReturn(array("isSuccess" => false));
            }
        }
    }

    private function top()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $articleids = EnvUtil::getRequest("articleids");
            $topEndTime = EnvUtil::getRequest("topEndTime");

            if (!empty($topEndTime)) {
                $topEndTime = (strtotime($topEndTime) + (24 * 60 * 60)) - 1;
                Article::model()->updateTopStatus($articleids, 1, TIMESTAMP, $topEndTime);
                $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Top succeed")));
            } else {
                Article::model()->updateTopStatus($articleids, 0, "", "");
                $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Unstuck success")));
            }
        }
    }

    private function highLight()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $articleids = trim(EnvUtil::getRequest("articleids"), ",");
            $highLight = array();
            $highLight["endTime"] = EnvUtil::getRequest("highlightEndTime");
            $highLight["bold"] = EnvUtil::getRequest("highlight_bold");
            $highLight["color"] = EnvUtil::getRequest("highlight_color");
            $highLight["italic"] = EnvUtil::getRequest("highlight_italic");
            $highLight["underline"] = EnvUtil::getRequest("highlight_underline");
            $data = ArticleUtil::processHighLightRequestData($highLight);

            if (empty($data["highlightendtime"])) {
                Article::model()->updateHighlightStatus($articleids, 0, "", "");
                $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Unhighlighting success")));
            } else {
                Article::model()->updateHighlightStatus($articleids, 1, $data["highlightstyle"], $data["highlightendtime"]);
                $this->ajaxReturn(array("isSuccess" => true, "msg" => Ibos::lang("Highlight succeed")));
            }
        }
    }

    private function clickVote()
    {
        if ($this->getVoteInstalled()) {
            $relatedId = EnvUtil::getRequest("relatedid");
            $voteItemids = EnvUtil::getRequest("voteItemids");
            $result = ICVotePlugManager::getArticleVote()->clickVote("article", $relatedId, $voteItemids);

            if (is_numeric($result)) {
                echo $result;
            } else {
                $this->ajaxReturn($result);
            }
        }
    }

    private function getVoteCount()
    {
        if ($this->getVoteInstalled()) {
            $relatedId = EnvUtil::getRequest("relatedid");
            $count = Vote::model()->fetchUserVoteCount("article", $relatedId);
            echo $count;
            exit();
        }
    }

    private function getReader()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $articleid = EnvUtil::getRequest("articleid");
            $readerData = ArticleReader::model()->fetchAll("articleid=:articleid", array(":articleid" => $articleid));
            $departments = DepartmentUtil::loadDepartment();
            $res = $tempDeptids = $users = array();

            foreach ($readerData as $reader) {
                $user = User::model()->fetchByUid($reader["uid"]);
                $users[] = $user;
                $deptid = $user["deptid"];
                $tempDeptids[] = $user["deptid"];
            }

            $deptids = array_unique($tempDeptids);

            foreach ($deptids as $deptid) {
                $deptName = (isset($departments[$deptid]["deptname"]) ? $departments[$deptid]["deptname"] : "--");

                foreach ($users as $k => $user) {
                    if ($user["deptid"] == $deptid) {
                        $res[$deptName][] = $user;
                        unset($users[$k]);
                    }
                }
            }

            $this->ajaxReturn($res);
        }
    }

    private function getReaderByDeptId()
    {
        if (Yii::app()->request->isAjaxRequest) {
            $articleId = $_POST["articleid"];
            $deptId = $_POST["deptid"];
            $readerData = ArticleReader::model()->fetchArticleReaderByDeptid($articleId, $deptId);
            $this->ajaxReturn($readerData);
        }
    }

    private function preview()
    {
        $this->setPageTitle(Ibos::lang("Preview Acticle"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Information center")),
            array("name" => Ibos::lang("Article"), "url" => $this->createUrl("default/index")),
            array("name" => Ibos::lang("Preview Acticle"))
        ));
        $this->render("preview", array("subject" => $_POST["subject"], "content" => $_POST["content"]));
    }
}
