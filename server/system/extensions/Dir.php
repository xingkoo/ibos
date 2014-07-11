<?php

class Dir implements IteratorAggregate
{
	private $_values = array();

	public function __construct($path, $pattern = "*")
	{
		if (substr($path, -1) != "/") {
			$path .= "/";
		}

		$this->listFile($path, $pattern);
	}

	public function listFile($pathname, $pattern = "*")
	{
		static $_listDirs = array();
		$guid = md5($pathname . $pattern);

		if (!isset($_listDirs[$guid])) {
			$dir = array();
			$list = glob($pathname . $pattern);

			foreach ($list as $i => $file ) {
				$dir[$i]["filename"] = preg_replace("/^.+[\\\\\/]/", "", $file);
				$dir[$i]["pathname"] = realpath($file);
				$dir[$i]["owner"] = fileowner($file);
				$dir[$i]["perms"] = fileperms($file);
				$dir[$i]["inode"] = fileinode($file);
				$dir[$i]["group"] = filegroup($file);
				$dir[$i]["path"] = dirname($file);
				$dir[$i]["atime"] = fileatime($file);
				$dir[$i]["ctime"] = filectime($file);
				$dir[$i]["size"] = filesize($file);
				$dir[$i]["type"] = filetype($file);
				$dir[$i]["ext"] = (is_file($file) ? strtolower(substr(strrchr(basename($file), "."), 1)) : "");
				$dir[$i]["mtime"] = filemtime($file);
				$dir[$i]["isDir"] = is_dir($file);
				$dir[$i]["isFile"] = is_file($file);
				$dir[$i]["isLink"] = is_link($file);
				$dir[$i]["isReadable"] = is_readable($file);
				$dir[$i]["isWritable"] = is_writable($file);
			}

			$cmp_func = create_function("\$a,\$b", "\r\n\t\t\t\$k  =  \"isDir\";\r\n\t\t\tif(\$a[\$k]  ==  \$b[\$k])  return  0;\r\n\t\t\treturn  \$a[\$k]>\$b[\$k]?-1:1;\r\n\t\t\t");
			usort($dir, $cmp_func);
			$this->_values = $dir;
			$_listDirs[$guid] = $dir;
		}
		else {
			$this->_values = $_listDirs[$guid];
		}
	}

	public function getATime()
	{
		$current = $this->current($this->_values);
		return $current["atime"];
	}

	public function getCTime()
	{
		$current = $this->current($this->_values);
		return $current["ctime"];
	}

	public function getChildren()
	{
		$current = $this->current($this->_values);

		if ($current["isDir"]) {
			return new Dir($current["pathname"]);
		}

		return false;
	}

	public function getFilename()
	{
		$current = $this->current($this->_values);
		return $current["filename"];
	}

	public function getGroup()
	{
		$current = $this->current($this->_values);
		return $current["group"];
	}

	public function getInode()
	{
		$current = $this->current($this->_values);
		return $current["inode"];
	}

	public function getMTime()
	{
		$current = $this->current($this->_values);
		return $current["mtime"];
	}

	public function getOwner()
	{
		$current = $this->current($this->_values);
		return $current["owner"];
	}

	public function getPath()
	{
		$current = $this->current($this->_values);
		return $current["path"];
	}

	public function getPathname()
	{
		$current = $this->current($this->_values);
		return $current["pathname"];
	}

	public function getPerms()
	{
		$current = $this->current($this->_values);
		return $current["perms"];
	}

	public function getSize()
	{
		$current = $this->current($this->_values);
		return $current["size"];
	}

	public function getType()
	{
		$current = $this->current($this->_values);
		return $current["type"];
	}

	public function isDir()
	{
		$current = $this->current($this->_values);
		return $current["isDir"];
	}

	public function isFile()
	{
		$current = $this->current($this->_values);
		return $current["isFile"];
	}

	public function isLink()
	{
		$current = $this->current($this->_values);
		return $current["isLink"];
	}

	public function isExecutable()
	{
		$current = $this->current($this->_values);
		return $current["isExecutable"];
	}

	public function isReadable()
	{
		$current = $this->current($this->_values);
		return $current["isReadable"];
	}

	public function getIterator()
	{
		return new ArrayObject($this->_values);
	}

	public function toArray()
	{
		return $this->_values;
	}

	public function isEmpty($directory)
	{
		$handle = opendir($directory);

		while (($file = readdir($handle)) !== false) {
			if (($file != ".") && ($file != "..")) {
				closedir($handle);
				return false;
			}
		}

		closedir($handle);
		return true;
	}

	public function getList($directory)
	{
		return scandir($directory);
	}

	public function delDir($directory, $subdir = true)
	{
		if (is_dir($directory) == false) {
			exit("The Directory Is Not Exist!");
		}

		$handle = opendir($directory);

		while (($file = readdir($handle)) !== false) {
			if (($file != ".") && ($file != "..")) {
				is_dir("$directory/$file") ? Dir::delDir("$directory/$file") : unlink("$directory/$file");
			}
		}

		if (readdir($handle) == false) {
			closedir($handle);
			rmdir($directory);
		}
	}

	public function del($directory)
	{
		if (is_dir($directory) == false) {
			exit("The Directory Is Not Exist!");
		}

		$handle = opendir($directory);

		while (($file = readdir($handle)) !== false) {
			if (($file != ".") && ($file != "..") && is_file("$directory/$file")) {
				unlink("$directory/$file");
			}
		}

		closedir($handle);
	}

	public function copyDir($source, $destination)
	{
		if (is_dir($source) == false) {
			exit("The Source Directory Is Not Exist!");
		}

		if (is_dir($destination) == false) {
			mkdir($destination, 448);
		}

		$handle = opendir($source);

		while (false !== $file = readdir($handle)) {
			if (($file != ".") && ($file != "..")) {
				is_dir("$source/$file") ? Dir::copyDir("$source/$file", "$destination/$file") : copy("$source/$file", "$destination/$file");
			}
		}

		closedir($handle);
	}
}

if (!class_exists("DirectoryIterator")) {
	class DirectoryIterator extends Dir
	{
		
	}

}
