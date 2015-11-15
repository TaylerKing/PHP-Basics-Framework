<?php
use strings\bbcode;
use design\design;
use user\user;
require_once("../core/inc.php");
global $user, $db, $config, $URL;
if(!$user->loggedIn() || !$user->hasPermission("admin.page.page")) {
	header("Location: " . $URL . "/auth/uac");
	exit();
}
?>
Hello World!