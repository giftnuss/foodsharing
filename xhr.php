<?php

use Foodsharing\Lib\Cache\Caching;
use Foodsharing\Lib\Session\S;
use Foodsharing\Lib\Xhr\XhrMethods;
use Foodsharing\Modules\Core\Model;

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
require_once 'lib/xhr.view.inc.php';
require_once 'lib/view.inc.php';

$action = $_GET['f'];

$db = new Model();

$db->updateActivity();
if (isset($_GET['f'])) {
	$xhr = new XhrMethods($db);
	$func = 'xhr_' . $action;
	if (method_exists($xhr, $func)) {
		/*
		 * check for page caching
		*/
		if (isset($cache) && $cache->shouldCache()) {
			ob_start();
			echo $xhr->$func($_GET);
			$page = ob_get_contents();
			$cache->cache($page);

			ob_end_clean();

			echo $page;
		} else {
			echo $xhr->$func($_GET);
		}
	}
}
