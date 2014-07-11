<?php

class ThinkImage
{
	/**
     * 图片资源
     * @var resource
     */
	private $img;

	public function __construct($type = THINKIMAGE_GD, $imgname = NULL)
	{
		switch ($type) {
		case THINKIMAGE_GD:
			$class = "ImageGd";
			break;

		case THINKIMAGE_IMAGICK:
			$class = "ImageImagick";
			break;

		default:
			throw new Exception("不支持的图片处理库类型");
		}

		require_once ("Driver/$class.class.php");
		$this->img = new $class($imgname);
	}

	public function width()
	{
		return $this->img->width();
	}

	public function height()
	{
		return $this->img->height();
	}

	public function type()
	{
		return $this->img->type();
	}

	public function mime()
	{
		return $this->img->mime();
	}

	public function size()
	{
		return $this->img->size();
	}

	public function __call($method, $args)
	{
		call_user_func_array(array($this->img, $method), $args);
		return $this;
	}
}

define("THINKIMAGE_GD", 1);
define("THINKIMAGE_IMAGICK", 2);
define("THINKIMAGE_THUMB_SCALE", 1);
define("THINKIMAGE_THUMB_FILLED", 2);
define("THINKIMAGE_THUMB_CENTER", 3);
define("THINKIMAGE_THUMB_NORTHWEST", 4);
define("THINKIMAGE_THUMB_SOUTHEAST", 5);
define("THINKIMAGE_THUMB_FIXED", 6);
define("THINKIMAGE_WATER_NORTHWEST", 1);
define("THINKIMAGE_WATER_NORTH", 2);
define("THINKIMAGE_WATER_NORTHEAST", 3);
define("THINKIMAGE_WATER_WEST", 4);
define("THINKIMAGE_WATER_CENTER", 5);
define("THINKIMAGE_WATER_EAST", 6);
define("THINKIMAGE_WATER_SOUTHWEST", 7);
define("THINKIMAGE_WATER_SOUTH", 8);
define("THINKIMAGE_WATER_SOUTHEAST", 9);
