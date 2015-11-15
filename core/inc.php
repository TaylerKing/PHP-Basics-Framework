<?php
//error_reporting(0);
//ini_set('display_errors', 1);
use database\db;
use user\user;
include_once("func.php");
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ["module", "configuration", "config.chunk.php"]);
$config = new configuration\Config;
session_start();
spl_autoload_register(function($a) {
	$file;
	if(file_exists($file = dirname(__DIR__) . DIRECTORY_SEPARATOR . "module" . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, strtolower($a)) . ".chunk.php"))
		require_once $file;
});
$db = DB::instance();
$user = new User();
$URL = $config->get("app/data/url");
function kill($reason = "You don't have access to the specified resource.", $success = true) {
	die('{"STATUS":"' . ($success ? "Successful" : "Error") . '", "SUCCESS":' . ($success ? "true" : "false") . ', "MSG":"' . htmlentities($reason) . '"}');
}
