<?php
namespace routing;
use database\db;
class Route {
	public function __construct($route) {
		global $routes, $config, $user;
		$db = DB::instance();	
		$routing = explode("/", rtrim($route, "/"));
		$query = array_splice($routing, 1);
		$routing = array_filter($routing);
		$routing = strtolower(trim(isset($routing[0]) ? $routing[0] : ""));
		$query = array_filter($query);
		if($user->loggedIn())
			$db->update("user", ["id", "=", $user->data()->id], ["last_active" => date("Y-m-d H:i:s", time()), "last_visited" => (array_key_exists($routing, $routes) ? $routing : ($routing == 'index' ? 'index' : 'unknown'))]);
		if(empty($routing))
			req("index.php", $query);
		else {
			if(!array_key_exists($routing, $routes) && $routing != "index")
				die(include_once("../route/404.php"));
			elseif($routing == "index")
				req("index.php", $query);
			else {
				$f = $routes[$routing];			
				if(is_callable($f))
					call_user_func($f);
				else
					req($f, $query);
			}
		}
	}
}
function req($file, $query) {
	if(file_exists("../route/" . $file))
		include_once("../route/" . $file);
	else
		include_once("../route/404.php");
	die();
}