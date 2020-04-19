<?php

use Foodsharing\Lib\Cache\Caching;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\Xhr\XhrMethods;
use Foodsharing\Lib\Xhr\XhrResponses;
use Symfony\Component\DependencyInjection\Container;

require __DIR__ . '/includes/setup.php';
require_once 'config.inc.php';

/*
	methods wich are excluded from the CSRF Protection.
	We start with every method and remove one by another
	NEVER ADD SOMETING TO THIS LIST!
*/
$csrf_whitelist = [
	// 'getPinPost',
	// 'activeSwitch',
	// 'grabInfo',
	// 'childBezirke',
	// 'bBubble',
	// 'loadMarker',
	// 'uploadPictureRefactorMeSoon',
	'uploadPicture',
	// 'cropagain',
	'pictureCrop',
	// 'out',
	// 'getRecip',
	'addPhoto',
	// 'continueMail',
	'uploadPhoto',
	// 'update_newbezirk',
	// 'update_abholen',
	// 'bezirkTree',
	// 'bteamstatus',
	// 'getBezirk',
	// 'acceptBezirkRequest',
	// 'denyBezirkRequest',
	// 'denyRequest',
	// 'acceptRequest',
	// 'warteRequest',
	// 'betriebRequest',
	// 'saveBezirk',
	// 'delDate',
	// 'fetchDeny',
	// 'fetchConfirm',
	// 'delBPost',
	// 'delPost',
	// 'abortEmail',
	// 'bcontext'
];

/* @var Container $container */
global $container;
$container = initializeContainer();

/* @var Session $session */
$session = $container->get(Session::class);
$session->initIfCookieExists();

/* @var Mem $mem */
$mem = $container->get(Mem::class);

/* @var \Foodsharing\Modules\Core\InfluxMetrics $influxdb */
$influxdb = $container->get(\Foodsharing\Modules\Core\InfluxMetrics::class);

if (isset($g_page_cache)) {
	$cache = new Caching($g_page_cache, $session, $mem, $influxdb);
	$cache->lookup();
}

require_once 'lang/DE/de.php';

$action = $_GET['f'];

if (isset($_GET['f'])) {
	if (!in_array($action, $csrf_whitelist)) {
		if (!$session->isValidCsrfHeader()) {
			header('HTTP/1.1 403 Forbidden');
			die('CSRF Failed: CSRF token missing or incorrect.');
		}
	}

	/* @var XhrMethods $xhr */
	$xhr = $container->get(XhrMethods::class);
	$func = 'xhr_' . $action;
	if (method_exists($xhr, $func)) {
		$metrics = $container->get(\Foodsharing\Modules\Core\InfluxMetrics::class);
		$metrics->addPageStatData(['controller' => $func]);

		ob_start();
		echo $xhr->$func($_GET);
		$page = ob_get_contents();
		ob_end_clean();

		if ($page === XhrResponses::PERMISSION_DENIED) {
			header('HTTP/1.1 403 Forbidden');
			die('Permission denied');
		}

		if (is_string($page) && (!trim($page) || $page[0] == '{' || $page[0] == '[')) {
			// just assume it's an JSON, to prevent the browser from interpreting it as
			// HTML, which could result in XSS possibilities
			header('Content-Type: application/json');
		}
		/*
		 * check for page caching
		*/
		if (isset($cache) && $cache->shouldCache()) {
			$cache->cache($page);
		}
		echo $page;
	}
}
