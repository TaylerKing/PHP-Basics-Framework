<?php
global $URL, $db, $user, $config;
	if(!$user->loggedIn() || !$user->hasPermission("admin.view"))
		die(header("Location: " . $config->get("app/data/url") . "/auth"));
	if(count($query) > 0)
		switch($query[0]) {
			case "page":
				die(include_once("admin/page.php"));
			break;
			case "page2":
				die(include_once("admin/page2.php"));
			break;
		}
	else
		die(include_once("admin/dashboard.php"));
?>