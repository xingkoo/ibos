<?php

class Ibos extends Yii
{
    /**
     * 当前平台引擎
     * @var mixed 
     */
    private static $_engine;
    /**
     * 默认加载的全局语言包
     * @var array 
     */
    private static $globalLangSource = array("default", "message", "error");

    public static function lang($message, $category = "", $params = array(), $source = null, $language = null)
    {
        if (empty($category)) {
            $messagePart = explode("/", $message);
            $currentModule = (string) self::getCurrentModuleName() . ".";

            switch (count($messagePart)) {
                case 1:
                    $message = $messagePart[0];
                    $category = $currentModule . "default";
                    break;
    
                case 2:
                    $message = $messagePart[1];
                    $file = $messagePart[0];
                    $category = $currentModule . $file;
                    break;
    
                case 3:
                    $message = $messagePart[2];
                    $file = $messagePart[1];
                    $module = $messagePart[0];
                    $category = $module . "." . $file;
                    break;
    
                default:
                    $category = "default";
                    break;
            }
        }

        $translation = parent::t(trim($category, "."), $message, $params, $source, $language);
        return $translation;
    }

    public static function getCurrentModuleName()
    {
        return Ibos::app()->setting->get("module");
    }

    public static function getLangSources($langSource = array())
    {
        if (self::getCurrentModuleName()) {
            $langSource[] = self::getCurrentModuleName() . ".default";
        }

        $lang = array();

        foreach (array_unique(array_merge(self::$globalLangSource, $langSource)) as $source) {
            $sourceLang = self::getLangSource($source);
            $lang = array_merge($lang, (array) $sourceLang);
        }

        return $lang;
    }

    public static function getLangSource($file)
    {
        static $langs = array();

        if (!isset($langs[$file])) {
            $langs[$file] = Ibos::app()->getMessages()->loadMessages($file, Ibos::app()->getLanguage());
        }

        return (array) $langs[$file];
    }

    public static function engine()
    {
        return self::$_engine;
    }

    public static function setEngine($engine)
    {
        if ((self::$_engine === null) || ($engine === null)) {
            self::$_engine = $engine;
        } else {
            throw new CException(self::lang("Ibos engine can only be created once.", "error"));
        }
    }
}
