<?php

class ICIMFactory extends CApplicationComponent
{
    private $_error = array();

    public function createAdapter($className, $config = array(), $properties = array())
    {
        $className = Ibos::import($className, true);
        $adapter = new $className($config);
        $this->chkInstance($adapter);

        if ($adapter->check()) {
            foreach ($properties as $name => $value) {
                $adapter->$name = $value;
            }

            return $adapter;
        } else {
            $this->setError($className, $adapter->getError());
            return false;
        }
    }

    protected function setError($className, $error = array())
    {
        $this->_error[$className] = $error;
    }

    public function getError($className)
    {
        if (isset($this->_error[$className])) {
            return $this->_error[$className];
        } else {
            return array();
        }
    }

    private function chkInstance($adapter)
    {
        if (!$adapter instanceof ICIM) {
            throw new CException(Ibos::t("error", "Class \"{class}\" is illegal.", array("{class}" => get_class($adapter))));
        }
    }
}
