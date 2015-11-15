<?php
namespace configuration;
use database\db;
class Config {
	private $data;
	public function __construct() {
		$this->data = array(
			"database" => [
				"ip" => "127.0.0.1",
				"un" => "username",
				"pw" => "password",
				"db" => "database"
			],
			"hashing" => [
				"algo" => PASSWORD_BCRYPT,
				"cost" => 8
			]
		);
	}
	public function get($key, $b = true) {
		if($key == "url")
			return $this->get("app/data/url");
		$parts = explode("/", $key);
		$data = $this->data;
		foreach ($parts as $part) {
			if(isset($data[$part]))
				$data = $data[$part];
			else {
				$db = DB::instance();
				$value = $db->get("configuration", ["ckey", "=", $key]);
				if($value->count()) {
					if($b)
						$v = preg_replace("/\[b\](.*?)\[\/b\]/s", "<strong>$1</strong>", $value->first()->value);
					else
						$v = $value->first()->value;
					return $v;
				}
				else
					return false;
			}
		}
		return is_string($data) ? preg_replace("/\[b\](.*?)\[\/b\]/s", "<strong>$1</strong>", htmlentities($data)) : $data;
	}
	public function set($key, $data) {
		if(isset($this->data[$key]))
			$this->data[$key] = $data;
		else {
			$db = DB::instance();
			return $db->query("INSERT INTO configuration (ckey, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?", [$key, $data, $data], 2);
		}
	}
}