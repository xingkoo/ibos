<?php

class Redis extends CCache
{
	public $enable;
	public $obj;

	public function init($config)
	{
		if (!empty($config["server"])) {
			try {
				$this->obj = new Redis();

				if ($config["pconnect"]) {
					$connect = @$this->obj->pconnect($config["server"], $config["port"]);
				}
				else {
					$connect = @$this->obj->connect($config["server"], $config["port"]);
				}
			}
			catch (RedisException $e) {
			}

			$this->enable = ($connect ? true : false);

			if ($this->enable) {
				@$this->obj->setOption(Redis::OPT_SERIALIZER, $config["serializer"]);
			}
		}

		parent::init();
	}

	protected function getValue($key)
	{
		if (is_array($key)) {
			return $this->getMulti($key);
		}

		return $this->obj->get($key);
	}

	protected function getMulti($keys)
	{
		$result = $this->obj->getMultiple($keys);
		$newresult = array();
		$index = 0;

		foreach ($keys as $key ) {
			if ($result[$index] !== false) {
				$newresult[$key] = $result[$index];
			}

			$index++;
		}

		unset($result);
		return $newresult;
	}

	protected function select($db = 0)
	{
		return $this->obj->select($db);
	}

	protected function setValue($key, $value, $ttl = 0)
	{
		if ($ttl) {
			return $this->obj->setex($key, $ttl, $value);
		}
		else {
			return $this->obj->set($key, $value);
		}
	}

	protected function deleteValue($key)
	{
		return $this->obj->delete($key);
	}

	protected function setMulti($arr, $ttl = 0)
	{
		if (!is_array($arr)) {
			return false;
		}

		foreach ($arr as $key => $v ) {
			$this->set($key, $v, $ttl);
		}

		return true;
	}

	protected function inc($key, $step = 1)
	{
		return $this->obj->incr($key, $step);
	}

	protected function dec($key, $step = 1)
	{
		return $this->obj->decr($key, $step);
	}

	protected function getSet($key, $value)
	{
		return $this->obj->getSet($key, $value);
	}

	protected function sADD($key, $value)
	{
		return $this->obj->sADD($key, $value);
	}

	protected function sRemove($key, $value)
	{
		return $this->obj->sRemove($key, $value);
	}

	protected function sMembers($key)
	{
		return $this->obj->sMembers($key);
	}

	protected function sIsMember($key, $member)
	{
		return $this->obj->sismember($key, $member);
	}

	protected function keys($key)
	{
		return $this->obj->keys($key);
	}

	protected function expire($key, $second)
	{
		return $this->obj->expire($key, $second);
	}

	protected function sCard($key)
	{
		return $this->obj->sCard($key);
	}

	protected function hSet($key, $field, $value)
	{
		return $this->obj->hSet($key, $field, $value);
	}

	protected function hDel($key, $field)
	{
		return $this->obj->hDel($key, $field);
	}

	protected function hLen($key)
	{
		return $this->obj->hLen($key);
	}

	protected function hVals($key)
	{
		return $this->obj->hVals($key);
	}

	protected function hIncrBy($key, $field, $incr)
	{
		return $this->obj->hIncrBy($key, $field, $incr);
	}

	protected function hGetAll($key)
	{
		return $this->obj->hGetAll($key);
	}

	protected function sort($key, $opt)
	{
		return $this->obj->sort($key, $opt);
	}

	protected function exists($key)
	{
		return $this->obj->exists($key);
	}

	protected function flushValues()
	{
		return $this->obj->flushAll();
	}
}
