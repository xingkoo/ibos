<?php

class ICAssetManager extends CAssetManager
{
    /**
     * 静态资源文件夹的所在路径
     * @var string 
     */
    private $_basePath;
    /**
     * 静态资源文件夹的访问地址
     * @var string 
     */
    private $_baseUrl;
    /**
     * @var array published assets
     */
    private $_published = array();

    public function getBasePath()
    {
        if ($this->_basePath === null) {
            $request = Yii::app()->getRequest();
            $this->setBasePath(dirname($request->getScriptFile()) . DIRECTORY_SEPARATOR . "static");
        }

        return $this->_basePath;
    }

    public function setBasePath($value)
    {
        if ((($basePath = realpath($value)) !== false) && is_dir($basePath) && is_writable($basePath)) {
            $this->_basePath = $basePath;
        } else {
            throw new CException(Yii::t("yii", "CAssetManager.basePath \"{path}\" is invalid. Please make sure the directory exists and is writable by the Web server process.", array("{path}" => $value)));
        }
    }

    public function setBaseUrl($value)
    {
        $this->_baseUrl = rtrim($value, "/");
    }

    public function getBaseUrl()
    {
        if ($this->_baseUrl === null) {
            $request = Yii::app()->getRequest();
            $this->setBaseUrl($request->getBaseUrl() . "/static");
        }

        return $this->_baseUrl;
    }

    public function hash($path)
    {
        return sprintf("%x", crc32($path . Yii::getVersion() . VERSION));
    }

    public function getAssetsUrl($module = "")
    {
        $path = Yii::getPathOfAlias("application.modules.$module.static");

        if (LOCAL) {
            $assetUrl = $this->publish($path);
        } else {
            $assetUrl = str_replace("\\", "/", stristr($path, "system"));
        }

        return $assetUrl;
    }

    public function republicAll()
    {
        if (LOCAL) {
            $except = array("image", "css", "font", "js", ".", "..");
            $basePath = $this->getBasePath();
            $dir = @opendir($basePath);

            while ($entry = @readdir($dir)) {
                $file = $basePath . DIRECTORY_SEPARATOR . $entry;

                if (!in_array($entry, array_merge($this->excludeFiles, $except))) {
                    if (is_dir($file)) {
                        FileUtil::clearDirs($file . "/");
                    }
                }
            }

            closedir($dir);
            $modules = Ibos::app()->getEnabledModule();

            foreach ($modules as $module) {
                $path = Yii::getPathOfAlias("application.modules.{$module["module"]}.static");

                if (is_dir($path)) {
                    $this->publish($path, false, -1, true);
                }
            }
        }

        return true;
    }
}
