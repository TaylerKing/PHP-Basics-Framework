<?php
namespace user;
use database\db;
use cryptography\hash;
use configuration\config;
class User {
	private $_db,
	$_data,
	$_config,
	$_loggedIn = false;
	public function __construct($user = null) {
		$this->_db = DB::instance();
		$this->_config = new Config;		
		if(!$user) {
			if(isset($_SESSION['uid'])) {
				$user = $_SESSION['uid'];
				if($this->find($user) && $this->validate()) {
					$hash = Hash::unique();
					if(isset($_COOKIE['uniqid'])) {
						$qr = $this->_db->get("session", ["hash", "=", $_COOKIE['uniqid']]);
						if(!$qr->count()) {
							setcookie("uniqid", $hash, 2147483647);
							$this->_db->insert("session", ["uid" => $_SESSION['uid'], "hash" => $hash, "ip" => $_SERVER['REMOTE_ADDR']]);
						}
					} else {
						setcookie("uniqid", $hash, 2147483647);
						$this->_db->insert("session", ["uid" => $_SESSION['uid'], "hash" => $hash, "ip" => $_SERVER['REMOTE_ADDR']]);
					}
					$this->_loggedIn = true;
				} else {
					self::logout();
				}
			} elseif(isset($_COOKIE['uniqid'])) {
				$qr = $this->_db->get("session", ["hash", "=", $_COOKIE['uniqid']]);
				if($qr->count() && $qr->first()->ip == $_SERVER['REMOTE_ADDR']) {
					self::login($qr->first()->uid);
				}
			}
		} else {
			$this->find($user);
		}
	}
	public function create($fields = array()) {
		$uql = $this->_db->insert('user', $fields) ? true : false;
		return $uql;
	}
	public function find($user = null) {
		if($user) {
			$data = $this->_db->get('user', array('id', '=', $user));
			if($data->count()) {
				$this->_data = $data->first();
				return true;
			}
		}
		return false;
	}
	public function validate() {
		if(isset($_SESSION['uid']) || isset($_COOKIE['uniqid'])) {
			if(isset($_SESSION['uid'])) {
				if($this->find($_SESSION['uid'])) {
					return true;
				}
			}
			if(isset($_COOKIE['uniqid'])) {
				$cookie = $_COOKIE['uniqid'];
				$qr = $this->_db->get("session", ["hash", "=", $cookie]);
				if($qr->count()) {
					if($qr->first()->ip == $_SERVER['REMOTE_ADDR'])
						return true;
				} else setcookie("uniqid", "", time()-3000);
			}
		}
		return false;
	}
	public function login($ident = null, $password = null) {
		$uname = $this->_db->query("SELECT * from user WHERE ident = ?", [$ident]);
		if($uname->count()) {
			$uid = $uname->first()->id;
			$user = $this->find($uid);
			if($user) {
				$success = false;
				if($this->data()->password == password_verify($password, $this->data()->password) || $password === null) {
					$success = true;
					$_SESSION['uid'] = $uname->first()->id;
					$hash = Hash::unique();
					if($this->data()->last_ip != $_SERVER['REMOTE_ADDR'])
						$this->_db->update("user", ["id", "=", $this->data()->id], ["last_ip" => $_SERVER['REMOTE_ADDR'], "last_ip_update" => date("Y-m-d H:i:s")]);
					$hashCheck = $this->_db->get('session', ['uid', '=', $_SESSION['uid']]);
					if(!$hashCheck->count()) {
						$this->_db->insert("session", ["uid" => $_SESSION['uid'], "hash" => $hash, "ip" => $_SERVER['REMOTE_ADDR']]);
					} else {
						$x = 1;
						$max = $hashCheck->count();
						foreach ($hashCheck->results() as $res) {
							if($res->ip == $_SERVER['REMOTE_ADDR'])
								$hash = $res->hash;
							else {
								if($x == $max)
									$this->_db->insert("session", ["uid" => $_SESSION['uid'], "hash" => $hash, "ip" => $_SERVER['REMOTE_ADDR']]);
							}
						}													
					}					
					setcookie("uniqid", $hash, 2147483647);
					$this->_loggedIn = true;
				}
				$this->_db->insert("logs", ["type" => 2, "data" => json_encode(["UID" => $uid, "password" => base64_encode($success ? "HIDDEN" : $password), "ip" => $_SERVER['REMOTE_ADDR']]), "success" => $success ? 1 : 0]);
				if($success) {
					return true;
				}
			}
		}
		$this->_loggedIn = false;
		$this->_data = [];
		return false;
	}
	public function exists() {
		return (!empty($this->_data));
	}
	public function data() {
		return $this->_data;
	}
	public function loggedIn() {
		return $this->_loggedIn;
	}
	public function logout() {
		if($this->exists()) {
			$this->_db->delete("session", array("uid", "=", $this->data()->id));
			/*
			 * 1 - Account Login
			 * 2 - Account Registration
			 * 3 - Account Logout
			 * 4 - Account Change Password
			 * 5 - Account Change Username
			 */
			$this->_db->insert("logs", ["type" => 3, "data" => json_encode(["UID" => $this->data()->id, "ip" => $_SERVER['REMOTE_ADDR']])]);
		}
		$_SESSION = array();
		session_destroy();
		setcookie("uniqid", "", time()-3000);
	}
	public function expiry() {
		$expiry = $this->data()->expiry;
		if($expiry <= time()) {
			$this->_db->update('user', ['id', '=', $this->data()->id], ['plan' => 0, 'purchased' => 0, 'expiry' => 0]);
			$this->data()->plan = 0;
			return 0;
		} else return (int)$expiry;
	}
	public function plan() {
		$expiry = $this->expiry();
		$plan = $this->data()->plan;
		if($plan == 0) {
			return [0, 0, 0];
		} else {
			$plan = $this->_db->query("SELECT * from plans WHERE id = ?", [$plan]);
			if($plan->count() && $expiry > time()) {
				return [$plan->first()->id, htmlentities($plan->first()->name), $expiry, $plan->first()];
			} else {
				$this->_db->query("UPDATE user WHERE expiry <= ? AND plan > 0", [time()]);
				$this->_db->update('user', ['id', '=', $this->data()->id], ['plan' => 0, 'purchased' => 0, 'expiry' => 0]);
				$this->data()->plan = 0;				
				return [0, 0, 0];
			}
		}
	}
	public function status() {
		return $this->_data->status;
	}
	public function hasPermission($permission) {
		return (strpos($this->_data->permissions, $permission) !== false);
	}
	public static function permissions($permList) {
		$segments = explode('|', $permList);
		$md_array = array();
		foreach($segments as $segment) {
			$current = &$md_array;
			$tokens = explode('.', $segment);
			$x = 0;
			foreach($tokens as $token) {				
				if(!array_key_exists($token, $current) ) {
					if(count($tokens) > $x+1)
						$current[$token] = array();
					else
						$current[] = $token;
				}
				if(count($tokens) > $x+1)
					$current = &$current[$token];
				$x++;
			}
		}
		return $md_array;
	}	
}