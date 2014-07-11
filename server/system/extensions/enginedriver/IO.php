<?php

abstract class IO
{
	/**
     * 存放已初始化的接口对象数组
     * @var array 
     */
	private $object = array();

	protected function getObject($obj)
	{
		$key = strtolower($obj);

		if (isset($this->object[$key])) {
			return $this->object[$key];
		}
		else {
			if (1 < ($n = func_num_args())) {
				$args = func_get_args();

				if ($n === 2) {
					$object = new $obj($args[1]);
				}
				else if ($n === 3) {
					$object = new $obj($args[1], $args[2]);
				}
				else if ($n === 4) {
					$object = new $obj($args[1], $args[2], $args[3]);
				}
				else {
					unset($args[0]);
					$class = new ReflectionClass($obj);
					$object = call_user_func_array(array($class, "newInstance"), $args);
				}
			}
			else {
				$object = new $obj();
			}

			$this->object[$key] = $object;
			return $object;
		}
	}

	abstract public function upload($fileArea, $module);

	abstract public function file();
}


?>
