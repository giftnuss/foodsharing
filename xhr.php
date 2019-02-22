<?php

use Foodsharing\Lib\Cache\Caching;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\Xhr\XhrMethods;
use Symfony\Component\DependencyInjection\Container;
use Foodsharing\Lib\Xhr\XhrResponses;

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
	// 'addPinPost',
	// 'childBezirke',
	'bBubble',
	// 'fsBubble',
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
	// 'addFetcher',
	// 'delDate',
	// 'fetchDeny',
	// 'fetchConfirm',
	// 'delBPost',
	// 'delPost',
	// 'abortEmail',
	// 'bcontext'
];

/* @var $container Container */
global $container;
$container = initializeContainer();

/* @var $session Session */
$session = $container->get(Session::class);
$session->initIfCookieExists();

/* @var $mem Mem */
$mem = $container->get(Mem::class);

/* @var $influxdb \Foodsharing\Modules\Core\InfluxMetrics */
$influxdb = $container->get(\Foodsharing\Modules\Core\InfluxMetrics::class);

if (isset($g_page_cache)) {
	$cache = new Caching($g_page_cache, $session, $mem, $influxdb);
	$cache->lookup();
}

require_once 'lang/DE/de.php';

$action = $_GET['f'];

$mem->updateActivity($session->id());
if (isset($_GET['f'])) {
	if (!in_array($action, $csrf_whitelist)) {
		if (!$session->isValidCsrfHeader()) {
			header('HTTP/1.1 403 Forbidden');
			die('CSRF Failed: CSRF token missing or incorrect.');
		}
	}

	/* @var $xhr XhrMethods */
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

		if (is_string($page) && ($page[0] == '{' || $page[0] == '[')) {
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
