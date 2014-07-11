<?php

class RSA
{
	public function encrypt($message, $public_key, $modulus, $keylength = 1024)
	{
		$padded = $this->add_PKCS1_padding($message, true, $keylength / 8);
		$number = $this->binary_to_number($padded);
		$encrypted = $this->pow_mod($number, $public_key, $modulus);
		$result = $this->number_to_binary($encrypted, $keylength / 8);
		return $result;
	}

	public function decrypt($message, $private_key, $modulus, $keylength = 1024)
	{
		$number = $this->binary_to_number($message);
		$decrypted = $this->pow_mod($number, $private_key, $modulus);
		$result = $this->number_to_binary($decrypted, $keylength / 8);
		return $this->remove_PKCS1_padding($result, $keylength / 8);
	}

	public function sign($message, $private_key, $modulus, $keylength)
	{
		$padded = $this->add_PKCS1_padding($message, false, $keylength / 8);
		$number = $this->binary_to_number($padded);
		$signed = $this->pow_mod($number, $private_key, $modulus);
		$result = $this->number_to_binary($signed, $keylength / 8);
		return $result;
	}

	public function verify($message, $public_key, $modulus, $keylength)
	{
		return $this->decrypt($message, $public_key, $modulus, $keylength);
	}

	private function pow_mod($p, $q, $r)
	{
		$factors = array();
		$div = $q;
		$power_of_two = 0;

		while (bccomp($div, "0") == 1) {
			$rem = bcmod($div, 2);
			$div = bcdiv($div, 2);

			if ($rem) {
				array_push($factors, $power_of_two);
			}

			$power_of_two++;
		}

		$partial_results = array();
		$part_res = $p;
		$idx = 0;

		foreach ($factors as $factor ) {
			while ($idx < $factor) {
				$part_res = bcpow($part_res, "2");
				$part_res = bcmod($part_res, $r);
				$idx++;
			}

			array_push($partial_results, $part_res);
		}

		$result = "1";

		foreach ($partial_results as $part_res ) {
			$result = bcmul($result, $part_res);
			$result = bcmod($result, $r);
		}

		return $result;
	}

	private function add_PKCS1_padding($data, $isPublicKey, $blocksize)
	{
		$pad_length = $blocksize - 3 - strlen($data);

		if ($isPublicKey) {
			$block_type = "\002";
			$padding = "";

			for ($i = 0; $i < $pad_length; $i++) {
				$rnd = mt_rand(1, 255);
				$padding .= chr($rnd);
			}
		}
		else {
			$block_type = "\001";
			$padding = str_repeat("ÿ", $pad_length);
		}

		return "\000" . $block_type . $padding . "\000" . $data;
	}

	private function remove_PKCS1_padding($data, $blocksize)
	{
		assert(strlen($data) == $blocksize);
		$data = substr($data, 1);

		if ($data[0] == "\\0") {
			exit("Block type 0 not implemented.");
		}

		assert(($data[0] == "\001") || ($data[0] == "\002"));
		$offset = strpos($data, "\000", 1);
		return substr($data, $offset + 1);
	}

	private function binary_to_number($data)
	{
		$base = "256";
		$radix = "1";
		$result = "0";

		for ($i = strlen($data) - 1; 0 <= $i; $i--) {
			$digit = ord($data[$i]);
			$part_res = bcmul($digit, $radix);
			$result = bcadd($result, $part_res);
			$radix = bcmul($radix, $base);
		}

		return $result;
	}

	private function number_to_binary($number, $blocksize)
	{
		$base = "256";
		$result = "";
		$div = $number;

		while (0 < $div) {
			$mod = bcmod($div, $base);
			$div = bcdiv($div, $base);
			$result = chr($mod) . $result;
		}

		return str_pad($result, $blocksize, "\000", STR_PAD_LEFT);
	}
}
