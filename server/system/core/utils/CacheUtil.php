<?php

class CacheUtil
{
    const CACHE_ALIAS = "ext.cacheprovider";

    public static function check()
    {
        return Ibos::app()->cache->enable ? strtolower(Ibos::app()->cache->type) : "";
    }

    public static function set($key, $value, $expire = 0, $prefix = "")
    {
        return Ibos::app()->cache->set($key, $value, $expire, $prefix);
    }

    public static function get($key, $prefix = "")
    {
        return Ibos::app()->cache->get($key, $prefix);
    }

    public static function rm($id)
    {
        return Ibos::app()->cache->rm($id);
    }

    public static function inc($key, $step = 1)
    {
        return Ibos::app()->cache->inc($key, $step);
    }

    public static function dec($key, $step = 1)
    {
        return Ibos::app()->cache->dec($key, $step);
    }

    public static function clear()
    {
        return Ibos::app()->cache->clear();
    }

    public static function load($cacheNames, $force = false)
    {
        static $loadedCache = array();
        $cacheNames = (is_array($cacheNames) ? $cacheNames : array($cacheNames));
        $caches = array();

        foreach ($cacheNames as $key) {
            if (!isset($loadedCache[$key]) || $force) {
                $caches[] = $key;
                $loadedCache[$key] = true;
            }
        }

        if (!empty($caches)) {
            $cacheData = Syscache::model()->fetchAllCache($caches);

            foreach ($cacheData as $cacheName => $data) {
                if ($cacheName == "setting") {
                    Ibos::app()->setting->set("setting", $data);
                } else {
                    Ibos::app()->setting->set("cache/" . $cacheName, $data);
                }
            }
        }

        return true;
    }

    public static function save($cacheName, $value)
    {
        Syscache::model()->addCache($cacheName, $value);
    }

    public static function update($cacheName = "")
    {
        $updateList = (empty($cacheName) ? array() : (is_array($cacheName) ? $cacheName : array($cacheName)));

        if (!$updateList) {
            $cacheDir = Ibos::getPathOfAlias(self::CACHE_ALIAS);
            $cacheDirHandle = dir($cacheDir);

            while ($entry = $cacheDirHandle->read()) {
                $isProviderFile = preg_match("/^([\_\w]+)CacheProvider\.php$/", $entry, $matches) && (substr($entry, -4) == ".php") && is_file($cacheDir . "/" . $entry);
                if (!in_array($entry, array(".", "..")) && $isProviderFile) {
                    $class = basename($matches[0], ".php");

                    if (class_exists($class)) {
                        Ibos::app()->attachBehavior("onUpdateCache", array("class" => self::CACHE_ALIAS . "." . $class));
                    }
                }
            }
        } else {
            foreach ($updateList as $entry) {
                $owner = ucfirst($entry) . "CacheProvider";

                if (class_exists($owner)) {
                    Ibos::app()->attachBehavior("onUpdateCache", array("class" => self::CACHE_ALIAS . "." . $owner));
                }
            }
        }

        if (Ibos::app()->hasEventHandler("onUpdateCache")) {
            Ibos::app()->raiseEvent("onUpdateCache", new CEvent(Ibos::app()));
        }
    }
}
