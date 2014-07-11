<?php

class Xcache extends CXCache
{
	public function inc($key, $step = 1)
	{
		return xcache_inc($key, $step);
	}

	public function dec($key, $step = 1)
	{
		return xcache_dec($key, $step);
	}
}
