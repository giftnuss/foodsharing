<?php

use Foodsharing\Lib\Cache\Caching;
use Foodsharing\Lib\Session\S;

require __DIR__ . '/includes/setup.php';

require_once 'config.inc.php';
require_once 'lib/func.inc.php';

//session_init();
S::init();
if (isset($g_page_cache)) {
	$cache = new Caching($g_page_cache);
	$cache->lookup();
}

require_once 'lang/DE/de.php';
require_once 'lib/xhr.inc.php';

$action = $_GET['f'];

$db->updateActivity();
if (isset($_GET['f'])) {
	$func = 'xhr_' . $action;
	if (function_exists($func)) {
		/*
		 * check for page caching
		*/
		if (isset($cache) && $cache->shouldCache()) {
			ob_start();
			echo $func($_GET);
			$page = ob_get_contents();
			$cache->cache($page);

			ob_end_clean();

			echo $page;
			//echo 'check';die();
		} else {
			echo $func($_GET);
		}
	}
}
