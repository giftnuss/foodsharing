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
use Foodsharing\Lib\Session\S;

require __DIR__ . '/includes/setup.php';

require_once 'lib/inc.php';
global $g_view_utils;
addCss('/css/gen/style.css?v=' . VERSION);
addScript('/js/gen/script.js?v=' . VERSION);

getCurrent();
$menu = getMenu();

getMessages();
makeHead();

if (DebugBar::isEnabled()) {
	addHead(DebugBar::renderHead());
}

if (isset($_POST['form_submit'])) {
	if (handleForm($_POST['form_submit'])) {
		go('/?page=' . getPage());
	}
}
$msgbar = '';
$logolink = '/';
if (S::may()) {
	$msgbar = $g_view_utils->v_msgBar();
	$logolink = '/?page=dashboard';
} else {
	$msgbar = $g_view_utils->v_login();
}

if (DebugBar::isEnabled()) {
	addContent(DebugBar::renderContent(), CNT_BOTTOM);
}

/*
 * check for page caching
 */
if (isset($cache) && $cache->shouldCache()) {
	ob_start();
	include 'tpl/' . $g_template . '.php';
	$page = ob_get_contents();
	$cache->cache($page);
	ob_end_clean();

	echo $page;
} else {
	include 'tpl/' . $g_template . '.php';
}
