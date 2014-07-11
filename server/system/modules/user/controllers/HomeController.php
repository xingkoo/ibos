<?php

class UserHomeController extends UserHomeBaseController
{
    public function actionIndex()
    {
        $data = $this->getIndexData();
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Home"), "url" => $this->createUrl("home/index")),
            array("name" => Ibos::lang("Home page"))
        ));
        $this->render("index", $data);
    }

    public function actionPersonal()
    {
        $op = EnvUtil::getRequest("op");

        if (!in_array($op, array("profile", "avatar", "history", "password", "skin", "remind"))) {
            $op = "profile";
        }

        if (EnvUtil::submitCheck("userSubmit")) {
            if (!$this->getIsMe()) {
                throw new EnvException(Ibos::lang("Parameters error", "error"));
            }

            $data = $_POST;

            if ($op == "profile") {
                $profileField = array("birthday", "bio", "telephone", "address", "qq");
                $userField = array("mobile", "email");
                $model = array();

                foreach ($_POST as $key => $value) {
                    if (in_array($key, $profileField)) {
                        if (($key == "birthday") && !empty($value)) {
                            $value = strtotime($value);
                        }

                        $model["UserProfile"][$key] = StringUtil::filterCleanHtml($value);
                    } elseif (in_array($key, $userField)) {
                        $model["User"][$key] = StringUtil::filterCleanHtml($value);
                    }
                }

                foreach ($model as $modelObject => $value) {
                    $modelObject::model()->modify($this->getUid(), $value);
                }
            } elseif ($op == "password") {
                $user = $this->getUser();
                $update = false;

                if ($data["originalpass"] == "") {
                    $this->error(Ibos::lang("Original password require"));
                } elseif (strcasecmp(md5(md5($data["originalpass"]) . $user["salt"]), $user["password"]) !== 0) {
                    $this->error(Ibos::lang("Password is not correct"));
                } else {
                    if (!empty($data["newpass"]) && (strcasecmp($data["newpass"], $data["newpass_confirm"]) !== 0)) {
                        $this->error(Ibos::lang("Confirm password is not correct"));
                    } else {
                        $password = md5(md5($data["newpass"]) . $user["salt"]);
                        $update = User::model()->updateByUid($this->getUid(), array("password" => $password, "lastchangepass" => TIMESTAMP));
                    }
                }
            } elseif ($op == "remind") {
                $remindSetting = array();

                foreach (array("email", "sms", "app") as $field) {
                    if (!empty($data[$field])) {
                        foreach ($data[$field] as $id => $value) {
                            $remindSetting[$id][$field] = $value;
                        }
                    }
                }

                if (!empty($remindSetting)) {
                    $remindSetting = serialize($remindSetting);
                } else {
                    $remindSetting = "";
                }

                UserProfile::model()->updateByPk($this->getUid(), array("remindsetting" => $remindSetting));
            }

            UserUtil::cleanCache($this->getUid());
            $this->success(Ibos::lang("Save succeed", "message"), $this->createUrl("home/personal", array("op" => $op)));
        } else {
            if (in_array($op, array("avatar", "history", "password", "remind"))) {
                if (!$this->getIsMe()) {
                    $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("home/index"));
                }
            }

            $dataProvider = "get" . ucfirst($op);
            $data = array("user" => $this->getUser());

            if (method_exists($this, $dataProvider)) {
                $data = array_merge($data, $this->{$dataProvider}());
            }

            $data["op"] = $op;
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Home"), "url" => $this->createUrl("home/index")),
                array("name" => Ibos::lang("Profile"))
            ));
            $this->render($op, $data);
        }
    }

    public function actionCredit()
    {
        if (!$this->getIsMe()) {
            $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("home/index"));
        }

        $op = EnvUtil::getRequest("op");

        if (!in_array($op, array("log", "level", "rule"))) {
            $op = "log";
        }

        $dataProvider = "getCredit" . ucfirst($op);
        $data = $this->{$dataProvider}();
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Home"), "url" => $this->createUrl("home/index")),
            array("name" => Ibos::lang("Credit"))
        ));
        $this->render("credit" . ucfirst($op), $data);
    }

    public function actionCheckSecurityRating()
    {
        if (Ibos::app()->request->getIsAjaxRequest()) {
            $rating = $this->getSecurityRating();
            $this->ajaxReturn(array("IsSuccess" => true, "rating" => $rating));
        }
    }

    public function actionCheckRepeat()
    {
        if (!$this->getIsMe()) {
            exit(Ibos::lang("Parameters error", "error"));
        }

        $op = EnvUtil::getRequest("op");

        if (!in_array($op, array("email", "mobile"))) {
            $op = "email";
        }

        $data = urldecode(EnvUtil::getRequest("data"));
        $record = User::model()->countByAttributes(array($op => $data));

        if (1 < $record) {
            $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Repeat " . $op)));
        } else {
            $res = $this->sendVerify($op, $data);
            $msg = ($res ? Ibos::lang("Operation succeed", "message") : Ibos::lang("Error " . $op));
            $this->ajaxReturn(array("isSuccess" => $res, "msg" => $msg));
        }
    }

    public function actionBind()
    {
        if (!$this->getIsMe()) {
            exit(Ibos::lang("Parameters error", "error"));
        }

        $op = EnvUtil::getRequest("op");

        if (!in_array($op, array("mobile", "email"))) {
            $op = "email";
        }

        $user = $this->getUser();
        $this->renderPartial("bind" . ucfirst($op), array("user" => $user, "lang" => Ibos::getLangSources()));
    }

    public function actionCheckVerify()
    {
        if (!$this->getIsMe()) {
            exit(Ibos::lang("Parameters error", "error"));
        }

        $op = EnvUtil::getRequest("op");

        if (!in_array($op, array("email", "mobile"))) {
            $op = "email";
        }

        $data = urldecode(EnvUtil::getRequest("data"));
        $session = new CHttpSession();
        $session->open();
        $verifyVal = md5($data);
        $verifyName = $op;
        if (isset($session[$verifyName]) && ($session[$verifyName] == $verifyVal)) {
            $check = true;
            $this->updateVerify($op);
        } else {
            $check = false;
        }

        $this->ajaxReturn(array("isSuccess" => $check));
    }

    protected function sendVerify($operation, $data)
    {
        $session = new CHttpSession();
        $session->open();

        if ($operation == "email") {
            $val = StringUtil::random(8);
        } elseif ($operation == "mobile") {
            $val = StringUtil::random(5, 1);
        }

        $verifyVal = md5($val);
        $verifyName = $operation;
        $session[$verifyName] = $verifyVal;
        $session["verifyData"] = $data;
        $res = $this->makeVerify($operation, $data, $val);
        $session->close();
        return $res;
    }

    private function makeVerify($op, $data, $val)
    {
        if ($op == "email") {
            $message = Ibos::lang("Verify email content", "", array("{code}" => $val, "{date}" => ConvertUtil::formatDate(TIMESTAMP, "d")));
            if (CloudApi::getInstance()->isOpen() && CloudApi::getInstance()->exists("mail_send")) {
                $res = MailUtil::sendCloudMail($data, Ibos::lang("Verify email title"), $message);
            } else {
                $res = MailUtil::sendMail($data, Ibos::lang("Verify email title"), $message);
            }
        } elseif ($op == "mobile") {
            $message = Ibos::lang("Verify mobile content", "", array("{code}" => $val));
            $res = MessageUtil::sendSms($data, $message, "user", $this->getUid());
        }

        return $res;
    }

    private function updateVerify($op)
    {
        $uid = $this->getUid();
        $session = new CHttpSession();
        $session->open();
        $data = $session["verifyData"];

        if ($op == "email") {
            User::model()->updateByUid($uid, array("validationemail" => 1, "email" => $data));
        } elseif ($op == "mobile") {
            User::model()->updateByUid($uid, array("validationmobile" => 1, "mobile" => $data));
        }

        UserUtil::updateCreditByAction("verify" . $op, $this->getUid());
    }

    protected function getIndexData()
    {
        $allCreditRankList = User::model()->fetchAllCredit();
        $curRanking = array_search($this->getUid(), $allCreditRankList);
        $totalRanking = count($allCreditRankList);
        $rankPercent = (double) 100 - (round(($curRanking + 1) / $totalRanking, 2) * 100);
        $ranklist = array();
        $top6 = array_slice($allCreditRankList, 0, 6);

        foreach ($top6 as $uid) {
            $ranklist[] = User::model()->fetchByUid($uid);
        }

        if (!empty($ranklist) && ($ranklist[0]["uid"] == $this->getUid())) {
            $isTop = true;
        } else {
            $isTop = false;
        }

        $extcredits = Ibos::app()->setting->get("setting/extcredits");
        $userCount = UserCount::model()->fetchByPk($this->getUid());
        $user = $this->getUser();
        $data = array("curRanking" => $curRanking + 1, "totalRanking" => $totalRanking, "rankPercent" => $rankPercent, "ranklist" => $ranklist, "isTop" => $isTop, "user" => $user, "extcredits" => $extcredits, "userCount" => $userCount, "contacts" => $this->getColleagues($user));

        if ($this->getIsMe()) {
            $data["securityRating"] = $this->getSecurityRating();
            $logTableId = Log::getLogTableId();
            $con = sprintf("`level` = 'login' AND `category` = 'module.user.%d'", $this->getUid());
            $data["history"] = Log::fetchAllByList($logTableId, $con, 4, 0);
        }

        return $data;
    }

    protected function getAvatar()
    {
        return array("user" => $this->getUser(), "swfConfig" => AttachUtil::getUploadConfig($this->getUid()));
    }

    protected function getHistory()
    {
        $lastMonth = strtotime("last month");
        $logTableId = Log::getLogTableId();
        $con = sprintf("`logtime` BETWEEN %d AND %d AND `level` = 'login' AND `category` = 'module.user.%d'", $lastMonth, TIMESTAMP, $this->getUid());
        $count = Log::countByTableId($logTableId, $con);
        $pages = PageUtil::create($count, 20);
        $logHistory = Log::fetchAllByList($logTableId, $con, 20, $pages->getOffset());
        return array("history" => $logHistory, "pages" => $pages);
    }

    protected function getRemind()
    {
        $user = $this->getUser();
        $user["remindsetting"] = (!empty($user["remindsetting"]) ? unserialize($user["remindsetting"]) : array());
        $nodeList = Notify::model()->getNodeList();
        $apiOpen = CloudApi::getInstance()->isOpen();

        foreach ($nodeList as $id => &$node) {
            $node["moduleName"] = Module::model()->fetchNameByModule($node["module"]);
            $node["appdisabled"] = (!$apiOpen ? 1 : 0);
            $node["maildisabled"] = (($user["validationemail"] == 0) || !$node["sendemail"] ? 1 : 0);
            $node["smsdisabled"] = (($user["validationmobile"] == 0) || !$node["sendsms"] ? 1 : 0);

            if (empty($user["remindsetting"])) {
                $node["emailcheck"] = $node["smscheck"] = $node["appcheck"] = 1;
            } elseif (isset($user["remindsetting"][$id])) {
                $node["appcheck"] = (isset($user["remindsetting"][$id]["app"]) ? $user["remindsetting"][$id]["app"] : 0);
                $node["emailcheck"] = (isset($user["remindsetting"][$id]["email"]) ? $user["remindsetting"][$id]["email"] : 0);
                $node["smscheck"] = (isset($user["remindsetting"][$id]["sms"]) ? $user["remindsetting"][$id]["sms"] : 0);
            } else {
                $node["emailcheck"] = $node["smscheck"] = $node["appcheck"] = 0;
            }
        }

        return array("nodeList" => $nodeList);
    }

    protected function getCreditLog()
    {
        CacheUtil::load(array("creditrule"));
        $creditRule = CreditRule::model()->fetchAllSortByPk("rid");
        $credits = Ibos::app()->setting->get("setting/extcredits");
        $relateRules = CreditRuleLog::model()->fetchAllByAttributes(array("uid" => $this->getUid()));
        $criteria = array(
            "condition" => "`uid` = :uid",
            "params"    => array(":uid" => $this->getUid()),
            "order"     => "dateline DESC"
        );
        $count = CreditLog::model()->count($criteria);
        $pages = PageUtil::create($count, 20);
        $criteria["limit"] = 20;
        $criteria["offset"] = $pages->getOffset();
        $creditLog = CreditLog::model()->fetchAll($criteria);
        return array("creditLog" => $creditLog, "relateRules" => $relateRules, "credits" => $credits, "creditRule" => $creditRule, "pages" => $pages);
    }

    protected function getCreditLevel()
    {
        $usergroup = Ibos::app()->setting->get("cache/usergroup");
        return array("level" => $usergroup, "user" => $this->getUser());
    }

    protected function getCreditRule()
    {
        $count = CreditRule::model()->count();
        $pages = PageUtil::create($count);
        $creditRule = CreditRule::model()->fetchAllSortByPk("rid", array("offset" => $pages->getOffset(), "limit" => $pages->getLimit()));
        $credits = Ibos::app()->setting->get("setting/extcredits");
        return array("credits" => $credits, "creditRule" => $creditRule, "pages" => $pages);
    }

    protected function getSecurityRating()
    {
        $score = 0;
        $user = $this->getUser();

        if (!empty($user["email"])) {
            $score += 25;
        }

        if (!empty($user["mobile"])) {
            $score += 25;
        }

        if ($user["validationemail"] == 1) {
            $score += 25;
        }

        if ($user["validationmobile"] == 1) {
            $score += 25;
        }

        return $score;
    }
}
