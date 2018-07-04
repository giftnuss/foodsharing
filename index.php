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
use Foodsharing\DI;
use Foodsharing\Lib\Func;
use Foodsharing\Lib\Routing;
use Foodsharing\Lib\Session\S;
use Foodsharing\Lib\View\Utils;

require __DIR__ . '/includes/setup.php';

require_once 'lib/inc.php';

/* @var $view_utils Utils */
$view_utils = DI::$shared->get(Utils::class);

/* @var $func Func */
$func = DI::$shared->get(Func::class);

/* @var $session S */
$session = DI::$shared->get(S::class);

$func->addStylesheet('/css/gen/style.css?v=' . VERSION);
$func->addScript('/js/gen/script.js?v=' . VERSION);

$func->addStylesheet('/css/pure/pure.min.css');
$func->addStylesheet('/css/pure/grids-responsive-min.css');
$func->addStylesheet('/fonts/font-awesome-4.7.0/css/font-awesome.min.css');

$g_body_class = '';
$g_broadcast_message = $db->qOne('SELECT `body` FROM fs_content WHERE `id` = 51');

if (DebugBar::isEnabled()) {
	$func->addHead(DebugBar::renderHead());
}

if (DebugBar::isEnabled()) {
	$func->addContent(DebugBar::renderContent(), CNT_BOTTOM);
}

if ($session->may()) {
	if (isset($_GET['uc'])) {
		if ($func->fsId() != $_GET['uc']) {
			$db->logout();
			$func->goLogin();
		}
	}

	$g_body_class = ' class="loggedin"';
}

$app = $func->getPage();

$usesWebpack = false;
if (($class = $session->getRouteOverride()) === null) {
	$class = Routing::getClassName($app, 'Control');
}

if ($class) {
	$obj = DI::$shared->get(ltrim($class, '\\'));

	$usesWebpack = $obj->getUsesWebpack();

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
}

if (!$usesWebpack) {
	require_once 'lib/global-js.php';
}

$page = $response->getContent();
$isUsingResponse = $page !== '--';
if ($isUsingResponse) {
	$response->send();
} else {
	/* @var $twig \Twig\Environment */
	$twig = DI::$shared->get(\Twig\Environment::class);
	$page = $twig->render('layouts/' . $g_template . '.twig', $func->generateAndGetGlobalViewData($usesWebpack));
}

if (isset($cache) && $cache->shouldCache()) {
	$cache->cache($page);
}

if (!$isUsingResponse) {
	echo $page;
}
