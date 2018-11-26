<?php

use Foodsharing\Lib\Cache\Caching;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\Xhr\XhrMethods;
use Symfony\Component\DependencyInjection\Container;

require __DIR__ . '/includes/setup.php';
require_once 'config.inc.php';

/* @var $container Container */
global $container;
$container = initializeContainer();

/* @var $session Session */
$session = $container->get(Session::class);
$session->initIfCookieExists();

/* @var $mem Mem */
$mem = $container->get(Mem::class);

if (isset($g_page_cache)) {
	$cache = new Caching($g_page_cache, $session, $mem);
	$cache->lookup();
}

require_once 'lang/DE/de.php';

$action = $_GET['f'];

$mem->updateActivity($session->id());
if (isset($_GET['f'])) {
	/* @var $xhr XhrMethods */
	$xhr = $container->get(XhrMethods::class);
	$func = 'xhr_' . $action;
	if (method_exists($xhr, $func)) {
		$metrics = $container->get(\Foodsharing\Modules\Core\InfluxMetrics::class);
		$metrics->addPageStatData(['controller' => $func]);
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
