<?php
namespace cryptography;
use configuration\config;
class Hash {
	public static function make($string, $salt = "", $rp = '/') {
		$config = new Config();
		return str_replace('/', $rp, password_hash($string . $salt, $config->get("hashing/algo"), ['cost' => $config->get("hashing/cost")]));
	}
	public static function salt($length) {
		return \mcrypt_create_iv($length);
	}
	public static function unique() {
		return self::make(uniqid(), "", "");
	}
}