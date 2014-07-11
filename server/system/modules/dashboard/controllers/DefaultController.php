<?php

class DashboardDefaultController extends DashboardBaseController
{
    /**
     * 控制器-动作映射数组，用于生成url
     * @var array 
     */
    private $_controllerMap = array(
        "index"     => array("index/index", "status/index"),
        "global"    => array("unit/index", "date/index", "sysCode/index", "userGroup/index", "credit/setup", "optimize/cache", "upload/index", "security/setup", "sms/manager", "im/index", "email/setup", "sysStamp/index", "approval/index", "notify/setup"),
        "interface" => array("nav/index", "login/index", "page/index", "quicknav/index"),
        "module"    => array("module/manager"),
        "manager"   => array("update/index", "announcement/setup", "task/index", "database/backup", "upgrade/index", "fileperms/index", "cron/index", "split/index"),
        "service"   => array("service/index")
    );

    public function actionLogin()
    {
        $access = $this->getAccess();
        $defaultUrl = $this->createUrl("default/index");

        if (0 < $access) {
            $this->success(Ibos::lang("Login succeed"), $defaultUrl);
        }

        if (!EnvUtil::submitCheck("formhash")) {
            $data = array("userName" => !empty($this->user) ? $this->user["username"] : "");
            $this->render("login", $data);
        } else {
            $userName = EnvUtil::getRequest("username");
            $passWord = EnvUtil::getRequest("password");
            if (!$passWord || ($passWord != addslashes($passWord))) {
                $this->error(Ibos::lang("Passwd illegal"));
            }

            $identity = new ICUserIdentity($userName, $passWord);
            $result = $identity->authenticate(true);

            if (0 < $result) {
                Ibos::app()->user->login($identity);

                if (Ibos::app()->user->uid != 1) {
                    MainUtil::checkLicenseLimit(true);
                }

                $this->success(Ibos::lang("Login succeed"), $defaultUrl);
            } else {
                $passWord = preg_replace("/^(.{" . round(strlen($passWord) / 4) . "})(.+?)(.{" . round(strlen($passWord) / 6) . "})$/s", "\1***\3", $passWord);
                $log = array("user" => $userName, "password" => $passWord, "ip" => Ibos::app()->setting->get("clientip"));
                Log::write($log, "illegal", "module.dashboard.login");
                $this->error(Ibos::lang("Login failed"));
            }
        }
    }

    public function actionIndex()
    {
        $data = array();
        $data["moduleMenu"] = Menu::model()->fetchAllRootMenu();

        foreach ($this->getControllerMap() as $category => $routes) {
            while (list($index, $route) = each($routes)) {
                list($controller) = explode("/", $route);
                $data[$category][$controller] = $this->createUrl(strtolower($route));
            }
        }

        $this->render("index", $data);
    }

    public function actionSearch()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $data = array();
            $keywords = trim($_POST["keyword"]);
            $kws = array_map("trim", explode(" ", $keywords));
            $keywords = implode(" ", $kws);

            if ($keywords) {
                $searchIndex = Ibos::getLangSource("dashboard.searchIndex");
                $result = $html = array();

                foreach ($searchIndex as $skey => $items) {
                    foreach ($kws as $kw) {
                        foreach ($items["text"] as $k => $text) {
                            if (strpos(strtolower($text), strtolower($kw)) !== false) {
                                $result[$skey][] = $k;
                            }
                        }
                    }
                }

                $data["kws"] = array_map(function ($item) {
                    return sprintf("\"%s\"", $item);
                }, $kws);

                if ($result) {
                    $totalCount = 0;
                    $item = Ibos::lang("Item");

                    foreach ($result as $skey => $tkeys) {
                        $tmp = array();

                        foreach ($searchIndex[$skey]["index"] as $title => $url) {
                            $tmp[] = "<a href=\"" . $url . "\" target=\"_self\">" . $title . "</a>";
                        }

                        $links = implode(" &raquo; ", $tmp);
                        $texts = array();
                        $tkeys = array_unique($tkeys);

                        foreach ($tkeys as $tkey) {
                            $texts[] = "<li><span data-class=\"highlight\">" . $searchIndex[$skey]["text"][$tkey] . "</span></li>";
                        }

                        $texts = implode("", array_unique($texts));
                        $totalCount += $count = count($tkeys);
                        //$html[] = "\t\t\t\t\t\t\t\t<div class=\"ctb\">\r\n\t\t\t\t\t\t\t\t\t<h2 class=\"st\">$count $item</h2>\r\n\t\t\t\t\t\t\t\t\t<div>\r\n\t\t\t\t\t\t\t\t\t\t<strong>$links</strong>\r\n\t\t\t\t\t\t\t\t\t\t<ul class=\"tipsblock\">$texts</ul>\r\n\t\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t</div>";
                        $html[] = '
                                <div class="ctb">
                                    <h2 class="st">'. $count .' '. $item . '</h2>
                                    <div>
                                        <strong>'. $links .'</strong>
                                        <ul class="tipsblock">'. $texts .'</ul>
                                    </div>
                                </div>';
                    }

                    if ($totalCount) {
                        $data["total"] = $totalCount;
                        $data["html"] = $html;
                    } else {
                        $data["msg"] = Ibos::lang("Search result noexists");
                    }
                } else {
                    $data["msg"] = Ibos::lang("Search result noexists");
                }
            } else {
                $data["msg"] = Ibos::lang("Search keyword noexists");
            }

            $this->render("search", $data);
        }
    }

    protected function getControllerMap()
    {
        return $this->_controllerMap;
    }

    public function actionLogout()
    {
        Ibos::app()->user->logout();
        $this->showMessage(Ibos::lang("Logout succeed"), Ibos::app()->urlManager->createUrl($this->loginUrl));
    }
}
