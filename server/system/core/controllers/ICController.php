<?php

class ICController extends CController
{
    const DEFAULT_JSONP_HANDLER = "jsonpReturn";

    /**
     * 布局类型
     * @var string 
     */
    public $layout = "";
    /**
     * 默认不进行权限验证的模块
     * @var type 
     */
    private $_notAuthModule = array("main", "user", "dashboard", "message", "weibo");
    /**
     * 当前模块可访问的静态资源文件路径
     * @var string 
     */
    private $_assetUrl = "";

    public function __construct($id, $module = null)
    {
        Ibos::app()->setting->set("module", $module->getId());
        parent::__construct($id, $module);
    }

    public function init()
    {
        parent::init();
        if (!Ibos::app()->user->isGuest && Ibos::app()->user->isNeedReset && !Ibos::app()->request->isAjaxRequest) {
            Ibos::app()->request->redirect(Ibos::app()->createUrl("user/default/reset"));
        }
    }

    public function actionError()
    {
        $error = Ibos::app()->errorHandler->error;

        if ($error) {
            $isAjaxRequest = Ibos::app()->request->getIsAjaxRequest();
            $this->error($error["message"], "", array(), $isAjaxRequest);
        }
    }

    public function render($view, $data = null, $return = false, $langSources = array())
    {
        if (is_null($data)) {
            $data = array();
        }

        Ibos::app()->setting->set("pageTitle", $this->getPageTitle());
        Ibos::app()->setting->set("breadCrumbs", $this->getPageState("breadCrumbs", array()));
        $this->setPageState("breadCrumbs", null);
        !isset($data["assetUrl"]) && ($data["assetUrl"] = $this->getAssetUrl());
        $data["lang"] = Ibos::getLangSources($langSources);
        return parent::render($view, $data, $return);
    }

    public function ajaxReturn($data, $type = "")
    {
        if (empty($type)) {
            $type = "json";
        }

        switch (strtoupper($type)) {
            case "JSON":
                header("Content-Type:application/json; charset=" . CHARSET);
                exit(CJSON::encode($data));
                break;

            case "XML":
                header("Content-Type:text/xml; charset=" . CHARSET);
                exit(xml_encode($data));
                break;

            case "JSONP":
                header("Content-Type:text/html; charset=" . CHARSET);
                $handler = (isset($_GET["callback"]) ? $_GET["callback"] : self::DEFAULT_JSONP_HANDLER);
                exit($handler . "(" . (!empty($data) ? CJSON::encode($data) : "") . ");");
                break;

            case "EVAL":
                header("Content-Type:text/html; charset=" . CHARSET);
                exit($data);
                break;

            default:
                exit($data);
                break;
        }
    }

    public function error($message = "", $jumpUrl = "", $params = array(), $ajax = false)
    {
        $this->showMessage($message, $jumpUrl, $params, 0, $ajax);
    }

    public function success($message = "", $jumpUrl = "", $params = array(), $ajax = false)
    {
        $this->showMessage($message, $jumpUrl, $params, 1, $ajax);
    }

    public function showMessage($message, $jumpUrl = "", $params = array(), $status = 1, $ajax = false)
    {
        if (($ajax === true) || Ibos::app()->request->getIsAjaxRequest()) {
            $data = (is_array($ajax) ? $ajax : array());
            $data["msg"] = $message;
            $data["isSuccess"] = $status;
            $data["url"] = $jumpUrl;
            $this->ajaxReturn($data);
        }

        $params["message"] = $message;
        $params["autoJump"] = (isset($params["autoJump"]) ? $params["autoJump"] : true);

        if (!$params["autoJump"]) {
            $params["jumpLinksOptions"] = (isset($params["jumpLinksOptions"]) && is_array($params["jumpLinksOptions"]) ? $params["jumpLinksOptions"] : array());
        } else {
            $params["jumpLinksOptions"] = array();
        }

        if (!empty($jumpUrl)) {
            $params["jumpUrl"] = $jumpUrl;
        } else {
            $params["jumpUrl"] = (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "");
        }

        if (!isset($params["timeout"])) {
            if ($status) {
                $params["timeout"] = 1;
            } else {
                $params["timeout"] = 5;
            }
        }

        $params["msgTitle"] = ($status ? Ibos::lang("Operation successful", "message") : Ibos::lang("Operation failure", "message"));

        if (isset($params["closeWin"])) {
            $params["jumpUrl"] = "javascript:window.close();";
        }

        $params["script"] = (isset($params["script"]) ? trim($params["script"]) : null);

        if (!isset($params["messageType"])) {
            $params["messageType"] = ($status ? "success" : "error");
        }

        if ($status) {
            MainUtil::setCookie("globalRemind", urlencode($params["message"]), 30);
            MainUtil::setCookie("globalRemindType", $params["messageType"], 30);
            $this->redirect($params["jumpUrl"]);
        } else {
            $viewPath = $basePath = Ibos::app()->getViewPath();
            $viewFile = $this->resolveViewFile("showMessage", $viewPath, $basePath);
            $output = $this->renderFile($viewFile, $params, true);
            echo $output;
        }

        exit();
    }

    public function getAssetUrl($module = "")
    {
        if (empty($this->_assetUrl)) {
            if (empty($module)) {
                $module = Ibos::getCurrentModuleName();
            }

            $this->_assetUrl = Ibos::app()->assetManager->getAssetsUrl($module);
        }

        return $this->_assetUrl;
    }

    public function setTitle($title)
    {
        Ibos::app()->setting->set("title", $title);
    }

    final public function filterNotAuthModule($module)
    {
        return in_array($module, $this->_notAuthModule);
    }

    public function filterRoutes($routes)
    {
        return false;
    }
}
