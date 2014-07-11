<?php

class SAEFile
{
	protected $filesInfo = array();
	/**
	 * sae 所用的 domain,默认为 data
	 * @var string 
	 */
	private $_domain;
	/**
	 * sae 封装的 storage 对象
	 * @var object 
	 */
	private $_storage;
	/**
	 * 基本文件mime类型
	 * @var array 
	 */
	private $_mimeTypes = array("ai" => "application/postscript", "eps" => "application/postscript", "exe" => "application/octet-stream", "doc" => "application/vnd.ms-word", "xls" => "application/vnd.ms-excel", "ppt" => "application/vnd.ms-powerpoint", "pps" => "application/vnd.ms-powerpoint", "pdf" => "application/pdf", "xml" => "application/xml", "odt" => "application/vnd.oasis.opendocument.text", "swf" => "application/x-shockwave-flash", "gz" => "application/x-gzip", "tgz" => "application/x-gzip", "bz" => "application/x-bzip2", "bz2" => "application/x-bzip2", "tbz" => "application/x-bzip2", "zip" => "application/zip", "rar" => "application/x-rar", "tar" => "application/x-tar", "7z" => "application/x-7z-compressed", "txt" => "text/plain", "php" => "text/x-php", "html" => "text/html", "htm" => "text/html", "js" => "text/javascript", "css" => "text/css", "rtf" => "text/rtf", "rtfd" => "text/rtfd", "py" => "text/x-python", "java" => "text/x-java-source", "rb" => "text/x-ruby", "sh" => "text/x-shellscript", "pl" => "text/x-perl", "sql" => "text/x-sql", "bmp" => "image/x-ms-bmp", "jpg" => "image/jpeg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png", "tif" => "image/tiff", "tiff" => "image/tiff", "tga" => "image/x-targa", "psd" => "image/vnd.adobe.photoshop", "mp3" => "audio/mpeg", "mid" => "audio/midi", "ogg" => "audio/ogg", "mp4a" => "audio/mp4", "wav" => "audio/wav", "wma" => "audio/x-ms-wma", "avi" => "video/x-msvideo", "dv" => "video/x-dv", "mp4" => "video/mp4", "mpeg" => "video/mpeg", "mpg" => "video/mpeg", "mov" => "video/quicktime", "wm" => "video/x-ms-wmv", "flv" => "video/x-flv", "mkv" => "video/x-matroska");

	public function __construct($domain = "data", $ak = "", $sk = "")
	{
		$this->_domain = $domain;
		$this->_storage = new SaeStorage($ak, $sk);
	}

	public function getList($dirName, $showAll = false, $showDir = false)
	{
		if (substr($dirName, 0, 1) == DIRECTORY_SEPARATOR) {
			$dirName = substr($dirName, 1, strlen($dirName));
		}

		$prefix = $dirName;
		$prefix = str_replace(" ", "", $prefix);

		if (empty($prefix)) {
			$prefix = "*";
		}

		$domain = $this->getAssetsDomain();
		$s = $this->getDiskStorage();
		$ls = $s->getList($domain, $prefix);

		if (!empty($ls)) {
			if (!$showAll) {
				foreach ($ls as $key => $one ) {
					$tmp = str_replace($ls[0], "", $one);
					$bo = strpos($tmp, "/");
					$lenth = strlen($tmp);
					if ((1 < $bo) && (($bo + 1) < $lenth)) {
						unset($ls[$key]);
					}
				}
			}

			unset($ls[0]);

			if ($showDir == false) {
				foreach ($ls as $key => $one ) {
					if (substr($one, -1) == DIRECTORY_SEPARATOR) {
						unset($ls[$key]);
						continue;
					}
				}
			}
		}

		return $ls;
	}

	public function getFiles($dirName, $fold = true)
	{
		$dirName = $this->formatDir($dirName);
		$domain = $this->getAssetsDomain();
		$s = $this->getDiskStorage();
		$ls = $s->getListByPath($domain, $dirName, 1000, 0, $fold);
		$rs = $ls["files"];

		if (!empty($rs)) {
			$arr = explode("/", $dirName);
			$autoSinaName = $dirName . "/" . end($arr);

			foreach ($rs as $key => $tmp ) {
				$fullName = $tmp["fullName"];
				if ((substr($fullName, -1) == DIRECTORY_SEPARATOR) || ($fullName == $autoSinaName)) {
					unset($rs[$key]);
					continue;
				}

				$mine = $this->getMimeType($tmp["Name"]);
				if (($tmp["length"] == 26) && empty($mine)) {
					unset($rs[$key]);
					continue;
				}

				$rs[$key]["fileName"] = $tmp["fullName"];
			}
		}

		return $rs;
	}

	public function getDirs($dirName, $fold = true)
	{
		$dirName = $this->formatDir($dirName);
		$domain = $this->getAssetsDomain();
		$s = $this->getDiskStorage();
		$ls = $s->getListByPath($domain, $dirName, 1000, 0, $fold);
		$rs = $ls["dirs"];

		if (!empty($rs)) {
			foreach ($rs as $key => $tmp ) {
				$rs[$key]["fileName"] = $tmp["fullName"];
			}
		}

		return $rs;
	}

	public function uploadFile($destFileName, $srcFileName, $attr)
	{
		if (empty($srcFileName)) {
			return false;
		}

		if (empty($attr["type"])) {
			$attr["type"] = $this->getMimeType($destFileName);
		}

		$domain = $this->getAssetsDomain();
		$s = $this->getDiskStorage();
		$rs = $s->upload($domain, $destFileName, $srcFileName, $attr);
		return $rs;
	}

	public function getFileInfo($fileName)
	{
		$info = $this->filesInfo[$fileName];

		if (empty($info)) {
			$domain = $this->getAssetsDomain();
			$s = $this->getDiskStorage();
			$info = $s->getAttr($domain, $fileName);
			if (!empty($info) && empty($info["type"])) {
				$info["type"] = $this->getMimeType($fileName);
			}

			$this->filesInfo[$fileName] = $info;
		}

		return $info;
	}

	public function isDir($dirName)
	{
		if (($dirName == "") || ($dirName == "/")) {
			return true;
		}

		if (substr($dirName, -1) == DIRECTORY_SEPARATOR) {
			return true;
		}

		return false;
	}

	public function createDir($dirName)
	{
		if (empty($dirName)) {
			return false;
		}

		$attr = array("type" => "good");
		$fileName = $dirName . DIRECTORY_SEPARATOR;
		$content = "this is a sae dir and automatic create by ibos2";
		$rs = $this->writeFile($fileName, $content, $attr);
		return $rs;
	}

	public function createFile($fileName, $content = " ")
	{
		$rs = $this->writeFile($fileName, $content);
		return $rs;
	}

	public function writeFile($file, $content = " ", $attr = array())
	{
		if (empty($file)) {
			return false;
		}

		if (substr($file, 0, 1) == DIRECTORY_SEPARATOR) {
			$file = substr($file, 1, strlen($file));
		}

		$file = str_replace("//", "/", $file);
		$domain = $this->getAssetsDomain();
		$s = $this->getDiskStorage();

		if (!isset($attr["type"])) {
			$attr["type"] = $this->getMimeType($file);
		}

		$rs = $s->write($domain, $file, $content, -1, $attr);
		return $rs;
	}

	public function copyFile($old, $new)
	{
		$domain = $this->getAssetsDomain();
		$s = $this->getDiskStorage();
		$attr = $s->getAttr($domain, $old);
		$content = $this->readFile($old);
		$exists = $this->writeFile($new, $content, $attr);
		return $exists;
	}

	public function renameFile($old, $new)
	{
		$domain = $this->getAssetsDomain();
		$s = $this->getDiskStorage();
		$attr = $s->getAttr($domain, $old);
		$content = $this->readFile($old);
		$exists = $this->writeFile($new, $content, $attr);

		if ($exists) {
			$exists = $this->deleteFile($old);
		}

		return $exists;
	}

	public function renameDir($old, $new)
	{
		$old = $this->formatDir($old);
		$new = $this->formatDir($new);
		$domain = $this->getAssetsDomain();
		$s = $this->getDiskStorage();
		$list = $s->getList($domain, $old);

		if (!empty($list)) {
			$length = strlen($old);

			foreach ($list as $oldName ) {
				$relName = substr($oldName, $length);
				$newName = $new . $relName;
				$exists = $this->renameFile($oldName, $newName);
			}
		}

		return $exists;
	}

	public function moveFile($old, $new)
	{
		return $this->renameFile($old, $new);
	}

	public function readFile($file)
	{
		$domain = $this->getAssetsDomain();
		$s = $this->getDiskStorage();
		$content = $s->read($domain, $file);
		return $content;
	}

	public function fileExists($file)
	{
		$domain = $this->getAssetsDomain();
		$s = $this->getDiskStorage();
		$exists = $s->fileExists($domain, $file);
		return $exists;
	}

	public function dirExists($dir = "")
	{
		$dirName = $this->formatDir($dir);
		$dir = $dirName . "/";
		$exists = $this->fileExists($dir);
		return $exists;
	}

	public function clearDir($dir)
	{
		$domain = $this->getAssetsDomain();
		$s = $this->getDiskStorage();
		$dirName = $this->formatDir($dir);
		$list = $s->getList($domain, $dirName);
		$exists = true;

		if (!empty($list)) {
			foreach ($list as $one ) {
				$exists = $this->deleteFile($one);
			}
		}

		return $exists;
	}

	public function deleteFile($file)
	{
		$domain = $this->getAssetsDomain();
		$s = $this->getDiskStorage();
		$exists = $s->delete($domain, $file);
		return $exists;
	}

	public function hasChildren($dirName)
	{
		$dirName = $this->formatDir($dirName);
		$s = $this->getDiskStorage();
		$domain = $this->getAssetsDomain();
		$ls = $s->getListByPath($domain, $dirName);
		return 0 < $ls["dirNum"];
	}

	public function getMimeType($file)
	{
		$hx = "";
		$extend = explode(".", $file);

		if (!empty($extend)) {
			$va = count($extend) - 1;
			$hx = $extend[$va];
		}

		$type = "";

		if (!empty($this->_mimeTypes[$hx])) {
			$type = $this->_mimeTypes[$hx];
		}

		return $type;
	}

	public function fileName($path = "")
	{
		$domain = $this->getAssetsDomain();
		$s = $this->getDiskStorage();
		$url = $s->getUrl($domain, $path);
		$urls = parse_url($url);

		if (!isset($urls["scheme"])) {
			$url = "http://" . $url;
		}

		return $url;
	}

	public function errmsg()
	{
		return $this->getDiskStorage()->errmsg();
	}

	public function formatDir($dirName)
	{
		$dirName = trim($dirName, DIRECTORY_SEPARATOR);
		return $dirName;
	}

	public function imageSize($image)
	{
		if (!is_readable($image)) {
			$sufffix = StringUtil::getFileExt($image);
			$image = $this->fetchTemp($image, $sufffix);
		}

		return getimagesize($image);
	}

	public function getTempPath()
	{
		return SAE_TMP_PATH;
	}

	public function fileSize($file)
	{
		if (!is_readable($file)) {
			$file = $this->fetchTemp($file);
		}

		return sprintf("%u", filesize($file));
	}

	public function fetchTemp($file, $suffix = "")
	{
		if (empty($suffix)) {
			$suffix = pathinfo($file, PATHINFO_EXTENSION);
		}

		$tmp = SAE_TMP_PATH . "tmp." . $suffix;
		$fetch = new SaeFetchurl();
		$fileContent = $fetch->fetch($file);
		file_put_contents($tmp, $fileContent);
		return $tmp;
	}

	public function download($attach, $downloadInfo)
	{
		$attachUrl = FileUtil::getAttachUrl();
		$attachment = FileUtil::fileName($attachUrl . "/" . $attach["attachment"]);
		Attachment::model()->updateDownload($attach["aid"]);
		header("Location:$attachment");
	}

	private function getAssetsDomain()
	{
		return $this->_domain;
	}

	private function getDiskStorage()
	{
		return $this->_storage;
	}
}


?>
