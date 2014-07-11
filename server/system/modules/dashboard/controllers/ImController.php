<?php

class DashboardImController extends DashboardBaseController
{
    public function actionIndex()
    {
        $type = EnvUtil::getRequest("type");
        $allowType = array("rtx", "qq");

        if (!in_array($type, $allowType)) {
            $type = "rtx";
        }

        $diff = array_diff($allowType, array($type));
        $value = Setting::model()->fetchSettingValueByKey("im");
        $im = unserialize($value);
        $formSubmit = EnvUtil::submitCheck("imSubmit");

        if ($formSubmit) {
            $type = $_POST["type"];

            if ($type == "rtx") {
                $keys = array("open", "server", "appport", "sdkport", "push", "sso", "reverselanding", "syncuser");
            } elseif ($type == "qq") {
                $keys = array("open", "id", "token", "appid", "appsecret", "push", "sso", "syncuser", "syncorg", "showunread", "refresh_token", "time", "expires_in");
            }

            $updateList = array();

            foreach ($keys as $key) {
                if (isset($_POST[$key])) {
                    $updateList[$key] = $_POST[$key];
                } else {
                    $updateList[$key] = 0;
                }
            }

            if ($updateList["open"] == "1") {
                $this->checkImUnique($diff);
                $correct = MessageUtil::getIsImBinding($type, $updateList);

                if ($correct !== true) {
                    $updateList["open"] = 0;
                } elseif ($type == "qq") {
                    $updateList["checkpass"] = 1;
                }
            } else {
                if ($type == "qq") {
                    $updateList["checkpass"] = 0;
                }

                $correct = true;
            }

            $im[$type] = $updateList;
            Setting::model()->updateSettingValueByKey("im", $im);
            CacheUtil::update(array("setting"));

            if ($correct === true) {
                $this->success(Ibos::lang("Save succeed", "message"));
            } else {
                $updateList["open"] = 0;

                if (is_array($correct)) {
                    $msg = (isset($correct[ICIM::ERROR_INIT]) ? implode(",", $correct[ICIM::ERROR_INIT]) : Ibos::lang("Unknown error", "error"));
                } else {
                    $msg = Ibos::lang("Unknown error", "error");
                }

                $this->error(Ibos::lang("Binding error", "", array("{err}" => $msg)));
            }
        } else {
            $data = array("type" => $type, "im" => $im[$type]);
            $this->render($type, $data);
        }
    }

    public function actionSyncOrg()
    {
        $type = EnvUtil::getRequest("type");
        $allowType = array("rtx", "qq");

        if (!in_array($type, $allowType)) {
            $type = "rtx";
        }

        $pwd = EnvUtil::getRequest("pwd");

        if (MessageUtil::getIsImOpen($type)) {
            $imCfg = Ibos::app()->setting->get("setting/im/" . $type);
            $className = "ICIM" . ucfirst($type);
            $factory = new ICIMFactory();
            $adapter = $factory->createAdapter($className, $imCfg, array("pwd" => $pwd));
            $res = $adapter->syncOrg();

            if (!$res) {
                $msg = implode(",", $adapter->getError(ICIM::ERROR_SYNC));
            } else {
                $msg = "";
            }

            $this->ajaxReturn(array("isSuccess" => !!$res, "msg" => $msg));
        }
    }

    public function actionBindingUser()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $map = filter_input(INPUT_POST, "map", FILTER_SANITIZE_STRING);

            if (!empty($map)) {
                UserBinding::model()->deleteAllByAttributes(array("app" => "bqq"));
                $maps = explode(",", $map);

                foreach ($maps as $relation) {
                    list($uid, $openId) = explode("=", $relation);
                    UserBinding::model()->add(array("uid" => $uid, "bindvalue" => $openId, "app" => "bqq"));
                }

                $this->ajaxReturn(array("isSuccess" => true));
            }

            $this->ajaxReturn(array("isSuccess" => false));
        } elseif (MessageUtil::getIsImOpen("qq")) {
            $imCfg = Ibos::app()->setting->get("setting/im/qq");
            $factory = new ICIMFactory();
            $adapter = $factory->createAdapter("ICIMQq", $imCfg);
            $api = $adapter->getApi();
            $rs = $api->getUserList(array("timestamp" => 0));
            $bqqUsers = array();

            if (substr($rs, 0, 5) !== "error") {
                $rsArr = json_decode($rs, true);
                if (isset($rsArr["ret"]) && ($rsArr["ret"] == 0)) {
                    $bqqUsers = $rsArr["data"]["items"];
                }
            }

            $data = array("ibosUsers" => UserUtil::loadUser(), "binds" => UserBinding::model()->fetchAllSortByPk("uid", "app = 'bqq'"), "bqqUsers" => $bqqUsers);
            $this->renderPartial("qqbinding", $data);
        }
    }

    private function checkImUnique($arr)
    {
        foreach ($arr as $type) {
            if (MessageUtil::getIsImOpen($type)) {
                $this->error(Ibos::lang("Binding unique error"));
            }
        }
    }
}
