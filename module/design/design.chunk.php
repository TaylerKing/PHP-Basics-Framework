<?php
namespace design;
global $user, $config;
class Design {
	public static function build($design, $ext = null) {
		global $user, $config, $db;
		$URL = $config->get("app/data/url");
		switch ($design) {
			case "footer":
			echo '</body>'
            break;
			case "head":
			echo '<head></head><body>';
			break;
		}
	}
}