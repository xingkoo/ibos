<?php

class Syscache extends ICModel
{
    public static function model($className = "Syscache")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{syscache}}";
    }

    public function addCache($cacheName, $data)
    {
        $this->add(array("name" => $cacheName, "type" => is_array($data) ? 1 : 0, "dateline" => TIMESTAMP, "value" => is_array($data) ? serialize($data) : $data), false, true);

        if (CacheUtil::get($cacheName) !== false) {
            CacheUtil::set($cacheName, $data);
        }
    }

    public function modify($pk, $attributes = null)
    {
        $data = $this->handleData($attributes);
        CacheUtil::set($pk, $data["value"]);
        return $this->updateAll($data, "name = :name", array(":name" => $pk));
    }

    public function fetchAllCache($cacheNames)
    {
        $cacheNames = (is_array($cacheNames) ? $cacheNames : array($cacheNames));
        $data = CacheUtil::get($cacheNames);
        if ((is_array($data) && in_array(false, $data, true)) || !$data) {
            $data = false;
        }

        $newArray = ($data !== false ? array_diff($cacheNames, array_keys($data)) : $cacheNames);

        if (empty($newArray)) {
            foreach ($data as &$cache) {
                $isSerialized = ($cache == serialize(false)) || (@unserialize($cache) !== false);
                $cache = ($isSerialized ? unserialize($cache) : $cache);
            }

            return $data;
        } else {
            $cacheNames = $newArray;
        }

        $caches = $this->fetchAll(sprintf("FIND_IN_SET(name,'%s')", implode(",", $cacheNames)));

        if ($caches) {
            foreach ($caches as $sysCache) {
                $data[$sysCache["name"]] = ($sysCache["type"] ? unserialize($sysCache["value"]) : $sysCache["value"]);
                CacheUtil::set($sysCache["name"], $data[$sysCache["name"]]);
            }

            foreach ($cacheNames as $name) {
                if ($data[$name] === null) {
                    $data[$name] = null;
                    CacheUtil::rm($name);
                }
            }
        }

        return $data;
    }

    private function handleData($attributes)
    {
        $value = (is_array($attributes) ? serialize($attributes) : $attributes);
        $data = array("type" => is_array($attributes) ? 1 : 0, "dateline" => time(), "value" => $value);
        return $data;
    }
}
