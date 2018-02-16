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
$func->addScript('/js/gen/script.js?v=' . VERSION);

$app = $func->getPage();

$class = Routing::getClassName($app, 'Control');
if ($class) {
	$obj = DI::$shared->get(ltrim($class, '\\'));

	if (isset($_GET['a']) && is_callable(array($obj, $_GET['a']))) {
		$meth = $_GET['a'];
		$obj->$meth($request);
	} else {
		$obj->index($request);
	}
	$sub = $sub = $obj->getSubFunc();
	if ($sub !== false && is_callable(array($obj, $sub))) {
		$obj->$sub($request);
	}
}

$menu = $func->getMenu();

$func->getMessages();

if (DebugBar::isEnabled()) {
	$func->addHead(DebugBar::renderHead());
}

$msgbar = '';
$logolink = '/';
if (S::may()) {
	$msgbar = $view_utils->v_msgBar();
	$logolink = '/?page=dashboard';
} else {
	$msgbar = $view_utils->v_login();
}

if (DebugBar::isEnabled()) {
	$func->addContent(DebugBar::renderContent(), CNT_BOTTOM);
}

$twig = DI::$shared->get(\Foodsharing\Lib\Twig::class);

$mainwidth = 24;

$content_left = $func->getContent(CNT_LEFT);
$content_right = $func->getContent(CNT_RIGHT);

if (!empty($content_left)) {
	$mainwidth -= $content_left_width;
}

if (!empty($content_right)) {
	$mainwidth -= $content_right_width;
}

global $g_broadcast_message;

$page = $twig->render('layouts/' . $g_template . '.twig', [
	'head' => $func->getHeadData(),
	'bread' => $func->getBread(),
	'bodyClass' => $g_body_class,
	'msgbar' => $msgbar,
	'menu' => $menu,
	'hidden' => $func->getHidden(),
	'isMob' => $func->isMob(),
	'logolink' => $logolink,
	'broadcast_message' => $g_broadcast_message,
	'SRC_REVISION' => defined('SRC_REVISION') ? SRC_REVISION : null,
	'HTTP_HOST' => $_SERVER['HTTP_HOST'],
	'is_foodsharing_dot_at' => strpos($_SERVER['HTTP_HOST'], 'foodsharing.at') !== false,
	'content' => [
		'main' => [
			'html' => $func->getContent(CNT_MAIN),
			'width' => $mainwidth
		],
		'left' => [
			'html' => $content_left,
			'width' => $content_left_width,
			'id' => 'left'
		],
		'right' => [
			'html' => $content_right,
			'width' => $content_right_width,
			'id' => 'right'
		],
		'top' => [
			'html' => $func->getContent(CNT_TOP),
			'id' => 'content_top'
		],
		'bottom' => [
			'html' => $func->getContent(CNT_BOTTOM),
			'id' => 'content_bottom'
		],
		'overtop' => [
			'html' => $func->getContent(CNT_OVERTOP)
		]
	]
]);

if (isset($cache) && $cache->shouldCache()) {
	$cache->cache($page);
}

echo $page;
