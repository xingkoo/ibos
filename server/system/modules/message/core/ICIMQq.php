<?php

class ICIMQq extends ICIM
{
    /**
     * 同步标记，是增加还是删除
     * @var type 
     */
    protected $syncFlag;

    public function check()
    {
        if ($this->isEnabled("open")) {
            $config = $this->getConfig();
            if (isset($config["checkpass"]) && ($config["checkpass"] == "1")) {
                return true;
            } else {
                if (!empty($config["id"]) && !empty($config["token"])) {
                    $info = $this->getApi()->getCorBase();
                    if (isset($info["ret"]) && ($info["ret"] == 0)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function push()
    {
        $type = $this->getPushType();
        if (($type == "notify") && $this->isEnabled("push/note")) {
            $this->pushNotify();
        } else {
            if (($type == "pm") && $this->isEnabled("push/msg")) {
            }
        }
    }

    public function syncOrg()
    {
    }

    public function syncUser()
    {
        $oplist = array("confirm", "sync");
        $op = EnvUtil::getRequest("op");

        if (!in_array($op, $oplist)) {
            $op = "confirm";
        }

        $syncUsers = User::model()->fetchAllByUids($this->getUid());
        $flag = $this->getSyncFlag();

        if ($op == "confirm") {
            if ($flag != 1) {
                $bindingUser = $this->getBindingUser($this->getUid());

                if (!empty($bindingUser)) {
                    $userNames = explode(",", User::model()->fetchRealnamesByUids($bindingUser));
                    $uids = $bindingUser;
                } else {
                    $exit = "<script>parent.Ui.tip('无需同步','success');parent.Ui.closeDialog();</script>";
                    EnvUtil::iExit($exit);
                }
            } else {
                $userNames = ConvertUtil::getSubByKey($syncUsers, "realname");
                $uids = $this->getUid();
            }

            $properties = array("usernames" => $userNames, "uid" => implode(",", $uids), "flag" => $flag);
            Ibos::app()->getController()->renderPartial("application.modules.organization.views.im.qqsync", $properties);
        } elseif ($op == "sync") {
            $count = count($syncUsers);

            if ($flag == 1) {
                $res = $this->addUser($syncUsers);
            } else {
                $res = $this->setUserStatus($syncUsers, $flag);
            }

            if ((1 <= $count) && ($count == $res)) {
                $exit = "\t\t\t<script>parent.Ui.tip('同步完成','success');parent.Ui.closeDialog();</script>";
            } else {
                $errors = $this->getError(self::ERROR_SYNC);
                $exit = implode(",", array_unique($errors));
            }

            EnvUtil::iExit($exit);
        }
    }

    private function addUser($users)
    {
        $count = 0;

        try {
            foreach ($users as $user) {
                $account = ConvertUtil::getPY($user["username"]);
                $data = array("account" => $account, "name" => $user["realname"], "gender" => $user["gender"] == 0 ? 2 : 1, "mobile" => $user["mobile"]);
                $res = $this->getApi()->addAccount($data);

                if (isset($res["ret"])) {
                    if ($res["ret"] == 0) {
                        $this->setBinding($user["uid"], implode(",", $res["data"]));
                        $count++;
                    } else {
                        $this->setError($res["msg"], self::ERROR_SYNC);
                    }
                }
            }

            return $count;
        } catch (Exception $exc) {
            $this->setError($exc->getMessage(), self::ERROR_SYNC);
            return 0;
        }
    }

    private function setUserStatus($users, $flag)
    {
        if ($flag == 0) {
            $flag = 2;
        } else {
            $flag = 1;
        }

        $count = 0;

        try {
            foreach ($users as $user) {
                $openId = UserBinding::model()->fetchBindValue($user["uid"], "bqq");
                $res = $this->getApi()->setStatus($openId, $flag);

                if (isset($res["ret"])) {
                    if ($res["ret"] == 0) {
                        $count++;
                    } else {
                        $this->setError($res["msg"], self::ERROR_SYNC);
                    }
                }
            }

            return $count;
        } catch (Exception $exc) {
            $this->setError($exc->getMessage(), self::ERROR_SYNC);
            return 0;
        }
    }

    protected function pushMsg()
    {
    }

    protected function pushNotify()
    {
        $openIds = UserBinding::model()->fetchValuesByUids($this->getUid(), "bqq");

        if (!empty($openIds)) {
            try {
                $unit = Ibos::app()->setting->get("setting/unit/shortname");
                $content = strip_tags($this->getMessage(), "<a>");
                $data = array("window_title" => Ibos::lang("From unit", "default", array("{unit}" => $unit)), "tips_title" => Ibos::lang("System notify", "default"), "tips_content" => $content, "receivers" => implode(",", $openIds), "to_all" => 0, "receive_type" => 0, "display_time" => 0, "need_verify" => $this->isEnabled("sso") ? 1 : 0);

                if (!empty($this->url)) {
                    $data["tips_url"] = $this->getUrl();
                }

                $this->getApi()->sendNotify($data);
            } catch (Exception $exc) {
                return null;
            }
        }
    }

    public function getApi()
    {
        static $api;

        if (empty($api)) {
            $config = $this->getConfig();
            $properties = array("company_id" => $config["id"], "company_token" => $config["token"], "app_id" => $config["appid"], "client_ip" => Ibos::app()->setting->get("clientip"));
            $api = new BQQApi($properties);
        }

        return $api;
    }

    protected function setBinding($uid, $openId)
    {
        if (!UserBinding::model()->getIsBinding($uid, "bqq")) {
            UserBinding::model()->add(array("uid" => $uid, "bindvalue" => $openId, "app" => "bqq"));
        } else {
            UserBinding::model()->updateAll(array("bindvalue" => $openId), sprintf("uid = %d AND app = 'bqq'", $uid));
        }
    }

    protected function getBindingUser($uids)
    {
        $result = array();

        foreach ($uids as $uid) {
            if (UserBinding::model()->getIsBinding($uid, "bqq")) {
                $result[] = $uid;
            }
        }

        return $result;
    }
}
