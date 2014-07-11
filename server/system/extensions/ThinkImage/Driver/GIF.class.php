<?php
 
class GIF
{
	/**
	 * GIF帧列表
	 * @var array
	 */
	private $frames = array();
	/**
	 * 每帧等待时间列表
	 * @var array
	 */
	private $delays = array();
 
	public function __construct($src = NULL, $mod = "url")
	{
		if (!is_null($src)) {
			if (("url" == $mod) && is_file($src)) {
				$src = file_get_contents($src);
			}
 
			try {
				$de = new GIFDecoder($src);
				$this->frames = $de->GIFGetFrames();
				$this->delays = $de->GIFGetDelays();
			}
			catch (Exception $e) {
				throw new Exception("½âÂëGIFÍ¼Æ¬³ö´í");
			}
		}
	}
 
	public function image($stream = NULL)
	{
		if (is_null($stream)) {
			$current = current($this->frames);
			return false === $current ? reset($this->frames) : $current;
		}
		else {
			$this->frames[key($this->frames)] = $stream;
		}
	}
 
	public function nextImage()
	{
		return next($this->frames);
	}
 
	public function save($gifname)
	{
		$gif = new GIFEncoder($this->frames, $this->delays, 0, 2, 0, 0, 0, "bin");
		file_put_contents($gifname, $gif->GetAnimation());
	}
}
 
class GIFEncoder
{
	private $GIF = "GIF89a";
	private $VER = "GIFEncoder V2.05";
	private $BUF = array();
	private $LOP = 0;
	private $DIS = 2;
	private $COL = -1;
	private $IMG = -1;
	private $ERR = array("ERR00" => "Does not supported function for only one image!", "ERR01" => "Source is not a GIF image!", "ERR02" => "Unintelligible flag ", "ERR03" => "Does not make animation from animated GIF source");
 
	public function __construct($GIF_src, $GIF_dly, $GIF_lop, $GIF_dis, $GIF_red, $GIF_grn, $GIF_blu, $GIF_mod)
	{
		if (!is_array($GIF_src) && !is_array($GIF_tim)) {
			printf("%s: %s", $this->VER, $this->ERR["ERR00"]);
			exit(0);
		}
 
		$this->LOP = (-1 < $GIF_lop ? $GIF_lop : 0);
		$this->DIS = (-1 < $GIF_dis ? ($GIF_dis < 3 ? $GIF_dis : 3) : 2);
		$this->COL = ((-1 < $GIF_red) && (-1 < $GIF_grn) && (-1 < $GIF_blu) ? $GIF_red | ($GIF_grn << 8) | ($GIF_blu << 16) : -1);
 
		for ($i = 0; $i < count($GIF_src); $i++) {
			if (strtolower($GIF_mod) == "url") {
				$this->BUF[] = fread(fopen($GIF_src[$i], "rb"), filesize($GIF_src[$i]));
			}
			else if (strtolower($GIF_mod) == "bin") {
				$this->BUF[] = $GIF_src[$i];
			}
			else {
				printf("%s: %s ( %s )!", $this->VER, $this->ERR["ERR02"], $GIF_mod);
				exit(0);
			}
 
			if ((substr($this->BUF[$i], 0, 6) != "GIF87a") && (substr($this->BUF[$i], 0, 6) != "GIF89a")) {
				printf("%s: %d %s", $this->VER, $i, $this->ERR["ERR01"]);
				exit(0);
			}
 
			$j = 13 + (3 * (2 << (ord($this->BUF[$i][10]) & 7)));
 
			for ($k = true; $k; $j++) {
				switch ($this->BUF[$i][$j]) {
				case "!":
					if (substr($this->BUF[$i], $j + 3, 8) == "NETSCAPE") {
						printf("%s: %s ( %s source )!", $this->VER, $this->ERR["ERR03"], $i + 1);
						exit(0);
					}
 
					break;
 
				case ";":
					$k = false;
					break;
				}
			}
		}
 
		GIFEncoder::GIFAddHeader();
 
		for ($i = 0; $i < count($this->BUF); $i++) {
			GIFEncoder::GIFAddFrames($i, $GIF_dly[$i]);
		}
 
		GIFEncoder::GIFAddFooter();
	}
 
	private function GIFAddHeader()
	{
		$cmap = 0;
 
		if (ord($this->BUF[0][10]) & 128) {
			$cmap = 3 * (2 << (ord($this->BUF[0][10]) & 7));
			$this->GIF .= substr($this->BUF[0], 6, 7);
			$this->GIF .= substr($this->BUF[0], 13, $cmap);
			$this->GIF .= "!\vNETSCAPE2.0\003\001" . GIFEncoder::GIFWord($this->LOP) . "\000";
		}
	}
 
	private function GIFAddFrames($i, $d)
	{
		$Locals_str = 13 + (3 * (2 << (ord($this->BUF[$i][10]) & 7)));
		$Locals_end = strlen($this->BUF[$i]) - $Locals_str - 1;
		$Locals_tmp = substr($this->BUF[$i], $Locals_str, $Locals_end);
		$Global_len = 2 << (ord($this->BUF[0][10]) & 7);
		$Locals_len = 2 << (ord($this->BUF[$i][10]) & 7);
		$Global_rgb = substr($this->BUF[0], 13, 3 * (2 << (ord($this->BUF[0][10]) & 7)));
		$Locals_rgb = substr($this->BUF[$i], 13, 3 * (2 << (ord($this->BUF[$i][10]) & 7)));
		$Locals_ext = "!\004" . chr(($this->DIS << 2) + 0) . chr(($d >> 0) & 255) . chr(($d >> 8) & 255) . "\000\000";
		if ((-1 < $this->COL) && (ord($this->BUF[$i][10]) & 128)) {
			for ($j = 0; $j < (2 << (ord($this->BUF[$i][10]) & 7)); $j++) {
				if ((ord($Locals_rgb[(3 * $j) + 0]) == ($this->COL >> 16) & 255) && (ord($Locals_rgb[(3 * $j) + 1]) == ($this->COL >> 8) & 255) && (ord($Locals_rgb[(3 * $j) + 2]) == ($this->COL >> 0) & 255)) {
					$Locals_ext = "!\004" . chr(($this->DIS << 2) + 1) . chr(($d >> 0) & 255) . chr(($d >> 8) & 255) . chr($j) . "\000";
					break;
				}
			}
		}
 
		switch ($Locals_tmp[0]) {
		case "!":
			$Locals_img = substr($Locals_tmp, 8, 10);
			$Locals_tmp = substr($Locals_tmp, 18, strlen($Locals_tmp) - 18);
			break;
 
		case ",":
			$Locals_img = substr($Locals_tmp, 0, 10);
			$Locals_tmp = substr($Locals_tmp, 10, strlen($Locals_tmp) - 10);
			break;
		}
 
		if ((ord($this->BUF[$i][10]) & 128) && (-1 < $this->IMG)) {
			if ($Global_len == $Locals_len) {
				if (GIFEncoder::GIFBlockCompare($Global_rgb, $Locals_rgb, $Global_len)) {
					$this->GIF .= $Locals_ext . $Locals_img . $Locals_tmp;
				}
				else {
					$byte = ord($Locals_img[9]);
					$byte |= 128;
					$byte &= 248;
					$byte |= ord($this->BUF[0][10]) & 7;
					$Locals_img[9] = chr($byte);
					$this->GIF .= $Locals_ext . $Locals_img . $Locals_rgb . $Locals_tmp;
				}
			}
			else {
				$byte = ord($Locals_img[9]);
				$byte |= 128;
				$byte &= 248;
				$byte |= ord($this->BUF[$i][10]) & 7;
				$Locals_img[9] = chr($byte);
				$this->GIF .= $Locals_ext . $Locals_img . $Locals_rgb . $Locals_tmp;
			}
		}
		else {
			$this->GIF .= $Locals_ext . $Locals_img . $Locals_tmp;
		}
 
		$this->IMG = 1;
	}
 
	private function GIFAddFooter()
	{
		$this->GIF .= ";";
	}
 
	private function GIFBlockCompare($GlobalBlock, $LocalBlock, $Len)
	{
		for ($i = 0; $i < $Len; $i++) {
			if (($GlobalBlock[(3 * $i) + 0] != $LocalBlock[(3 * $i) + 0]) || ($GlobalBlock[(3 * $i) + 1] != $LocalBlock[(3 * $i) + 1]) || ($GlobalBlock[(3 * $i) + 2] != $LocalBlock[(3 * $i) + 2])) {
				return 0;
			}
		}
 
		return 1;
	}
 
	private function GIFWord($int)
	{
		return chr($int & 255) . chr(($int >> 8) & 255);
	}
 
	public function GetAnimation()
	{
		return $this->GIF;
	}
}
 
class GIFDecoder
{
	private $GIF_buffer = array();
	private $GIF_arrays = array();
	private $GIF_delays = array();
	private $GIF_stream = "";
	private $GIF_string = "";
	private $GIF_bfseek = 0;
	private $GIF_screen = array();
	private $GIF_global = array();
	private $GIF_sorted;
	private $GIF_colorS;
	private $GIF_colorC;
	private $GIF_colorF;
 
	public function __construct($GIF_pointer)
	{
		$this->GIF_stream = $GIF_pointer;
		$this->GIFGetByte(6);
		$this->GIFGetByte(7);
		$this->GIF_screen = $this->GIF_buffer;
		$this->GIF_colorF = ($this->GIF_buffer[4] & 128 ? 1 : 0);
		$this->GIF_sorted = ($this->GIF_buffer[4] & 8 ? 1 : 0);
		$this->GIF_colorC = $this->GIF_buffer[4] & 7;
		$this->GIF_colorS = 2 << $this->GIF_colorC;
 
		if ($this->GIF_colorF == 1) {
			$this->GIFGetByte(3 * $this->GIF_colorS);
			$this->GIF_global = $this->GIF_buffer;
		}
 
		for ($cycle = 1; $cycle; ) {
			if ($this->GIFGetByte(1)) {
				switch ($this->GIF_buffer[0]) {
				case 33:
					$this->GIFReadExtensions();
					break;
 
				case 44:
					$this->GIFReadDescriptor();
					break;
 
				case 59:
					$cycle = 0;
					break;
				}
			}
			else {
				$cycle = 0;
			}
		}
	}
 
	private function GIFReadExtensions()
	{
		for ($this->GIFGetByte(1); ; ) {
			$this->GIFGetByte(1);
 
			if (($u = $this->GIF_buffer[0]) == 0) {
				break;
			}
 
			$this->GIFGetByte($u);
 
			if ($u == 4) {
				$this->GIF_delays[] = $this->GIF_buffer[1] | ($this->GIF_buffer[2] << 8);
			}
		}
	}
 
	private function GIFReadDescriptor()
	{
		$GIF_screen = array();
		$this->GIFGetByte(9);
		$GIF_screen = $this->GIF_buffer;
		$GIF_colorF = ($this->GIF_buffer[8] & 128 ? 1 : 0);
 
		if ($GIF_colorF) {
			$GIF_code = $this->GIF_buffer[8] & 7;
			$GIF_sort = ($this->GIF_buffer[8] & 32 ? 1 : 0);
		}
		else {
			$GIF_code = $this->GIF_colorC;
			$GIF_sort = $this->GIF_sorted;
		}
 
		$GIF_size = 2 << $GIF_code;
		$this->GIF_screen[4] &= 112;
		$this->GIF_screen[4] |= 128;
		$this->GIF_screen[4] |= $GIF_code;
 
		if ($GIF_sort) {
			$this->GIF_screen[4] |= 8;
		}
 
		$this->GIF_string = "GIF87a";
		$this->GIFPutByte($this->GIF_screen);
 
		if ($GIF_colorF == 1) {
			$this->GIFGetByte(3 * $GIF_size);
			$this->GIFPutByte($this->GIF_buffer);
		}
		else {
			$this->GIFPutByte($this->GIF_global);
		}
 
		$this->GIF_string .= chr(44);
		$GIF_screen[8] &= 64;
		$this->GIFPutByte($GIF_screen);
		$this->GIFGetByte(1);
 
		for ($this->GIFPutByte($this->GIF_buffer); ; ) {
			$this->GIFGetByte(1);
			$this->GIFPutByte($this->GIF_buffer);
 
			if (($u = $this->GIF_buffer[0]) == 0) {
				break;
			}
 
			$this->GIFGetByte($u);
			$this->GIFPutByte($this->GIF_buffer);
		}
 
		$this->GIF_string .= chr(59);
		$this->GIF_arrays[] = $this->GIF_string;
	}
 
	private function GIFGetByte($len)
	{
		$this->GIF_buffer = array();
 
		for ($i = 0; $i < $len; $i++) {
			if (strlen($this->GIF_stream) < $this->GIF_bfseek) {
				return 0;
			}
 
			$this->GIF_buffer[] = ord($this->GIF_stream[$this->GIF_bfseek++]);
		}
 
		return 1;
	}
 
	private function GIFPutByte($bytes)
	{
		for ($i = 0; $i < count($bytes); $i++) {
			$this->GIF_string .= chr($bytes[$i]);
		}
	}
 
	public function GIFGetFrames()
	{
		return $this->GIF_arrays;
	}
 
	public function GIFGetDelays()
	{
		return $this->GIF_delays;
	}
}
