<?php
/*
if(isset($_GET['g_path']))
{
	$path = explode('/', $_GET['g_path']);
	//print_r($path);


	switch ($path[0])
	{
		case 'group' :
			$_GET['page'] = 'bezirk';
			$_GET['bid'] = $path[1];
			$_GET['sub'] = $path[2];
			break;

		default:
			break;
	}

}
*/

use Foodsharing\Debug\DebugBar;
use Foodsharing\Helpers\RouteHelper;
use Foodsharing\Helpers\PageHelper;
use Foodsharing\Lib\ContentSecurityPolicy;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Routing;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

require __DIR__ . '/includes/setup.php';
require_once 'config.inc.php';

/* @var $container Container */
global $container;
$container = initializeContainer();

/* @var $csp ContentSecurityPolicy */
$csp = $container->get(ContentSecurityPolicy::class);

// Security headers :)

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

if (defined('CSP_REPORT_URI')) {
	header($csp->generate(CSP_REPORT_URI, CSP_REPORT_ONLY));
}

require_once 'lib/inc.php';

/* @var $mem Mem */
$mem = $container->get(Mem::class);

/* @var $view_utils Utils */
$view_utils = $container->get(Utils::class);

/* @var $func RouteHelper */
$routeHelper = $container->get(RouteHelper::class);

/* @var $func PageHelper */
$pageHelper = $container->get(PageHelper::class);

/* @var $session Session */
$session = $container->get(Session::class);

$g_broadcast_message = $db->qOne('SELECT `body` FROM fs_content WHERE `id` = 51');

if (DebugBar::isEnabled()) {
	$pageHelper->addHead(DebugBar::renderHead());
}

if (DebugBar::isEnabled()) {
	$pageHelper->addContent(DebugBar::renderContent(), CNT_BOTTOM);
}

if ($session->may()) {
	if (isset($_GET['uc'])) {
		if ($session->id() != $_GET['uc']) {
			$mem->logout($session->id());
			$routeHelper->goLogin();
		}
	}
}

$app = $routeHelper->getPage();

if (($class = $session->getRouteOverride()) === null) {
	$class = Routing::getClassName($app, 'Control');
	try {
		$obj = $container->get(ltrim($class, '\\'));
	} catch (ServiceNotFoundException $e) {
	}
} else {
	$obj = $container->get(ltrim($class, '\\'));
}

if (isset($obj)) {
	if (isset($_GET['a']) && is_callable(array($obj, $_GET['a']))) {
		$meth = $_GET['a'];
		$obj->$meth($request, $response);
	} else {
		$obj->index($request, $response);
	}
	$sub = $sub = $obj->getSubFunc();
	if ($sub !== false && is_callable(array($obj, $sub))) {
		$obj->$sub($request, $response);
	}
} else {
	$response->setStatusCode(404);
	$response->setContent('');
}

$page = $response->getContent();
$isUsingResponse = $page !== '--';
if ($isUsingResponse) {
	$response->send();
} else {
	/* @var $twig \Twig\Environment */
	$twig = $container->get(\Twig\Environment::class);
	$page = $twig->render('layouts/' . $g_template . '.twig', $pageHelper->generateAndGetGlobalViewData());
}

if (isset($cache) && $cache->shouldCache()) {
	$cache->cache($page);
}

if (!$isUsingResponse) {
	echo $page;
}
