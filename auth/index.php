<?php
use routing\route;
use database\db;
require_once("../core/inc.php");
global $routes, $user, $config;
$routes["dashboard"] = "test.php";
$routes["admin"] = "admin_dashboard.php";
$routes["logout"] = function(){
	global $user, $config;
	$user->logout();
	header("Location: " . $config->get("app/data/url"));
};
new Route(isset($_GET['params']) ? (string)$_GET['params'] : "");
die();