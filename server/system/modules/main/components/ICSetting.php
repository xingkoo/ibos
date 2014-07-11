<?php

class ICSetting extends CApplicationComponent
{
    private $_G = array();

    public function toArray()
    {
        return $this->_G;
    }

    public function copyFrom($setting)
    {
        $this->_G = $setting;
    }

    public function mergeWith($value)
    {
        $this->_G = CMap::mergeArray($this->_G, $value);
    }

    public function get($key)
    {
        $keyArr = explode("/", $key);
        $setting = $this->toArray();

        foreach ($keyArr as $keyPart) {
            if (!isset($setting[$keyPart])) {
                return null;
            }

            $setting = &$setting[$keyPart];
        }

        return $setting;
    }

    public function set($key, $value)
    {
        $setting = $this->toArray();
        $key = explode("/", $key);
        $p = &$setting;

        foreach ($key as $k) {
            if (!isset($p[$k]) || !is_array($p[$k])) {
                $p[$k] = array();
            }

            $p = &$p[$k];
        }

        $p = $value;
        $this->copyFrom($setting);
        return true;
    }
}
