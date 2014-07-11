<?php

class SystemUtil
{
    /**
     * 单例应用对象
     * @var object 
     */
    private static $_instances = array();

    public static function getInstance($className = "SystemUtil")
    {
        if (isset(self::$_instances[$className])) {
            return self::$_instances[$className];
        } else {
            $instance = self::$_instances[$className] = new $className();
            return $instance;
        }
    }

    public function __get($name)
    {
        $getter = "get" . $name;

        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        }

        throw new CException(Yii::t("yii", "Property \"{class}.{property}\" is not defined.", array("{class}" => get_class($this), "{property}" => $name)));
    }

    public function __set($name, $value)
    {
        $setter = "set" . $name;

        if (method_exists($this, $setter)) {
            return $this->{$setter}($value);
        }

        if (method_exists($this, "get" . $name)) {
            throw new CException(Yii::t("yii", "Property \"{class}.{property}\" is read only.", array("{class}" => get_class($this), "{property}" => $name)));
        } else {
            throw new CException(Yii::t("yii", "Property \"{class}.{property}\" is not defined.", array("{class}" => get_class($this), "{property}" => $name)));
        }
    }

    public function __isset($name)
    {
        $getter = "get" . $name;

        if (method_exists($this, $getter)) {
            return $this->{$getter}() !== null;
        }

        return false;
    }

    public function hasProperty($name)
    {
        return method_exists($this, "get" . $name) || method_exists($this, "set" . $name);
    }

    public function canGetProperty($name)
    {
        return method_exists($this, "get" . $name);
    }

    public function canSetProperty($name)
    {
        return method_exists($this, "set" . $name);
    }
}
