<?php

class Apc extends CApcCache
{
	public function getMulti($keys)
	{
		return apc_fetch($keys);
	}

	public function inc($key, $step = 1)
	{
		return apc_inc($key, $step) !== false ? apc_fetch($key) : false;
	}

	public function dec($key, $step = 1)
	{
		return apc_dec($key, $step) !== false ? apc_fetch($key) : false;
	}
}
