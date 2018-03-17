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
use Foodsharing\Lib\View\Utils;

require __DIR__ . '/includes/setup.php';

require_once 'lib/inc.php';
$view_utils = DI::$shared->get(Utils::class);

/**
 * @return Func
 */
function getFunc()
{
	return DI::$shared->get(Func::class);
}

$func = getFunc();

$func->addStylesheet('/css/gen/style.css?v=' . VERSION);
//$func->addScript('/js/gen/script.js?v=' . VERSION);
// $func->addScript('/js/gen/webpack/js/main.js');

if (DebugBar::isEnabled()) {
	$func->addHead(DebugBar::renderHead());
}

if (DebugBar::isEnabled()) {
	$func->addContent(DebugBar::renderContent(), CNT_BOTTOM);
}

$app = $func->getPage();

$class = Routing::getClassName($app, 'Control');
if ($class) {
	$obj = DI::$shared->get(ltrim($class, '\\'));

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

$page = $response->getContent();
$isUsingResponse = $page !== '--';
if ($isUsingResponse) {
	$response->send();
} else {
	$twig = DI::$shared->get(\Foodsharing\Lib\Twig::class);
	$page = $twig->render('layouts/' . $g_template . '.twig', $func->generateAndGetGlobalViewData());
}

if (isset($cache) && $cache->shouldCache()) {
	$cache->cache($page);
}

if (!$isUsingResponse) {
	echo $page;
}
