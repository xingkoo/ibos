<?php
 
class ICApplication extends CWebApplication
{
    /**
     * 已安装的模块
     * @var array 
     */
    private $_enabledModule = array();
    /**
     * 授权数组信息
     * @var array 
     */
    private $_licenceArr = array();
 
    protected function init()
    {
        $this->setLicence();
        $this->_enabledModule = Module::model()->fetchAllEnabledModule();
 
        foreach ($this->getEnabledModule() as $module) {
            $config = CJSON::decode($module["config"], true);
            if (isset($config["configure"]) && array_key_exists("modules", $config["configure"])) {
                if (isset($config["behaviors"])) {
                    $this->setBehaviors($config["behaviors"]);
                }
 
                parent::configure($config["configure"]);
            }
        }
 
        parent::init();
    }
 
    public function getLicence()
    {
        return $this->_licenceArr;
    }
 
    private function setLicence()
    {
        $filename = PATH_ROOT . "/data/licence.key";
        if (file_exists($filename) && is_readable($filename)) {
            $licencekey = file_get_contents($filename);
        } else {
            $licencekey = "";
        }
 
        if ($licencekey) {
            $licenceArr = $this->readLicence($licencekey);
 
            if (is_array($licenceArr)) {
                switch ($licenceArr["ver"]) {
                    case "Standard":
                        $licenceArr["vername"] = "普及版";
                        break;
 
                    case "Enterprise":
                        $licenceArr["vername"] = "企业版";
                        break;
 
                    case "Group":
                        $licenceArr["vername"] = "旗舰版";
                        break;
 
                    case "Project":
                        $licenceArr["vername"] = "项目版";
                        break;
 
                    case "DIY":
                        $licenceArr["vername"] = $licenceArr["vername"];
                        break;
 
                    default:
                        $licenceArr["vername"] = "试用版";
                }
 
                if (isset($licenceArr["limit"])) {
                    $limit = $licenceArr["limit"];
                } else {
                    $limit = 10;
                }
 
                if (isset($licenceArr["added"])) {
                    $limit = $limit + $licenceArr["added"];
                }
 
                if (isset($licenceArr["disable"])) {
                    define("LICENCE_DISABLE", $licenceArr["disable"]);
                }
 
                $licenceArr["limit"] = $limit;
                $this->_licenceArr = $licenceArr;
            }
        }
    }
 
    private function readLicence($licencekey)
    {
        $c = "";
 
        if (strpos($licencekey, "|") == false) {
            return false;
        }
 
        list($pre, $c) = explode("|", $licencekey);
 
        if (empty($c)) {
            return false;
        }
 
        $modulus = "247951816413205085921106286398120136896788014055199338629780778472204077308053767006218018324142651909195596003106594609159002643031774387211432583166542583483099049359378164797170552666392349957500492002826361302903529659499530039.0000000000";
        $public = "65537";
        $keylength = "768";
        Ibos::import("ext.auth.RSA", true);
        $RSA = new RSA();
        $pre = base64_decode($pre);
        $key = $RSA->verify($pre, $public, $modulus, $keylength);
        $key = trim($key, "\000\001\002\000");
        Ibos::import("ext.auth.AES", true);
        $AES = new AES(true);
        $keys = $AES->makeKey($key);
        $s = $AES->decryptString($c, $keys);
        $s = json_decode($s, true);
        return $s;
    }
 
    public function configure($config)
    {
        defined("ENGINE") || define("ENGINE", "LOCAL");
        $this->setImport($config["import"]);
        unset($config["import"]);
        $engineClass = ucfirst(strtolower(ENGINE)) . "Engine";
        $engine = new $engineClass($config);
        Ibos::setEngine($engine);
        parent::configure(Ibos::engine()->getEngineConfig());
    }
 
    public function beforeControllerAction($controller, $action)
    {
        $module = $controller->getModule()->getId();
 
        if (!$controller->filterNotAuthModule($module)) {
            $routes = strtolower($controller->getUniqueId() . "/" . $action->getId());
 
            if (!$controller->filterRoutes($routes)) {
                if (!Ibos::app()->user->checkAccess($routes, AuthUtil::getParams($routes))) {
                    if (isset($this->rbacErrorPage)) {
                        $controller->redirect($this->rbacErrorPage);
                    } else {
                        $controller->error(Ibos::lang("Valid access", "error"), "", array("autoJump" => 0));
                    }
                }
            }
        }
 
        return true;
    }
 
    public function onInitModule($event)
    {
        $this->raiseEvent("onInitModule", $event);
    }
 
    public function onUpdateCache($event)
    {
        $this->raiseEvent("onUpdateCache", $event);
    }
 
    public function createController($route, $owner = null)
    {
        if ($owner === null) {
            $owner = $this;
        }
 
        if (($route = trim($route, "/")) === "") {
            $route = $owner->defaultController;
        }
 
        $caseSensitive = parent::getUrlManager()->caseSensitive;
        $route .= "/";
 
        while (($pos = strpos($route, "/")) !== false) {
            $id = substr($route, 0, $pos);
 
            if (!preg_match("/^\w+$/", $id)) {
                return null;
            }
 
            if (!$caseSensitive) {
                $id = strtolower($id);
            }
 
            $route = (string) substr($route, $pos + 1);
 
            if (!isset($basePath)) {
                if (isset($owner->controllerMap[$id])) {
                    return array(Ibos::createComponent($owner->controllerMap[$id], $id, $this->resolveWhatToPassAsParameterForOwner($owner)), parent::parseActionParams($route));
                }
 
                if (($module = $owner->getModule($id)) !== null) {
                    if (parent::hasEventHandler("onInitModule")) {
                        $this->onInitModule(new CEvent($this));
                    }
 
                    return $this->createController($route, $module);
                }
 
                $basePath = $owner->getControllerPath();
                $controllerID = "";
            } else {
                $controllerID .= "/";
            }
 
            $baseClassName = ucfirst($id) . "Controller";
 
            if ($this->isOwnerTheController($owner)) {
                $className = $baseClassName;
            } else {
                $className = $owner::getPluralCamelCasedName() . $baseClassName;
            }
 
            $classFile = $basePath . DIRECTORY_SEPARATOR . $baseClassName . ".php";
 
            if (is_file($classFile)) {
                if (!class_exists($className, false)) {
                    require ($classFile);
                }
 
                if (class_exists($className, false) && is_subclass_of($className, "CController")) {
                    $id[0] = strtolower($id[0]);
                    return array(new $className($controllerID . $id, $this->resolveWhatToPassAsParameterForOwner($owner)), parent::parseActionParams($route));
                }
 
                return null;
            }
 
            $controllerID .= $id;
            $basePath .= DIRECTORY_SEPARATOR . $id;
        }
    }
 
    public function isApplicationInstalled()
    {
        $params = $this->getParams();
        return $params["installed"];
    }
 
    public function getEnabledModule()
    {
        return (array) $this->_enabledModule;
    }
 
    protected function setBehaviors($behaviors)
    {
        parent::attachBehaviors($behaviors);
    }
 
    protected function isOwnerTheController($owner)
    {
        if ($owner === $this) {
            return true;
        }
 
        return false;
    }
 
    protected function resolveWhatToPassAsParameterForOwner($owner)
    {
        if ($owner === $this) {
            return null;
        }
 
        return $owner;
    }
}
