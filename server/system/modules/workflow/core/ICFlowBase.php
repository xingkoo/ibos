<?php

abstract class ICFlowBase
{
    /**
     * 对象数组
     * @var array 
     */
    protected $_attributes = array();

    public function __get($name)
    {
        $getter = "get" . ucfirst($name);

        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        } elseif (isset($this->_attributes[$name])) {
            return $this->_attributes[$name];
        }

        throw new CException(Ibos::t("yii", "Property \"{class}.{property}\" is not defined.", array("{class}" => get_class($this), "{property}" => $name)));
    }

    public function __set($name, $value)
    {
        $setter = "set" . $name;

        if (method_exists($this, $setter)) {
            return $this->{$setter}($value);
        } elseif (isset($this->_attributes[$name])) {
            return $this->_attributes[$name] = $value;
        }

        if (method_exists($this, "get" . $name)) {
            throw new CException(Ibos::t("yii", "Property \"{class}.{property}\" is read only.", array("{class}" => get_class($this), "{property}" => $name)));
        } else {
            throw new CException(Ibos::t("yii", "Property \"{class}.{property}\" is not defined.", array("{class}" => get_class($this), "{property}" => $name)));
        }
    }

    public function __isset($name)
    {
        $getter = "get" . $name;

        if (method_exists($this, $getter)) {
            return $this->{$getter}() !== null;
        } elseif (isset($this->_attributes[$name])) {
            return true;
        } elseif (isset($this->$name)) {
            return true;
        }

        return false;
    }

    public function toArray()
    {
        return $this->_attributes;
    }

    protected function setAttributes($attributes)
    {
        $this->_attributes = $attributes;
    }

    abstract public function getID();
}
