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

$func->addStylesheet('/css/pure/pure.min.css');
$func->addStylesheet('/css/pure/grids-responsive-min.css');
$func->addStylesheet('/fonts/font-awesome-4.7.0/css/font-awesome.min.css');

$user = '';
$g_body_class = '';
$g_broadcast_message = $db->qOne('SELECT `body` FROM fs_content WHERE `id` = 51');

if (DebugBar::isEnabled()) {
	$func->addHead(DebugBar::renderHead());
}

if (DebugBar::isEnabled()) {
	$func->addContent(DebugBar::renderContent(), CNT_BOTTOM);
}

$app = $func->getPage();

$usesWebpack = false;

$class = Routing::getClassName($app, 'Control');

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
	if (S::may()) {
		if (isset($_GET['uc'])) {
			if ($func->fsId() != $_GET['uc']) {
				$db->logout();
				$func->goLogin();
			}
		}

		$g_body_class = ' class="loggedin"';
		$user = 'user = {id:' . $func->fsId() . '};';
	}

	$func->addJs('
	' . $user . '
	$("#mainMenu > li > a").each(function(){
		if(parseInt(this.href.length) > 2 && this.href.indexOf("' . $func->getPage() . '") > 0)
		{
			$(this).parent().addClass("active").click(function(ev){
				//ev.preventDefault();
			});
		}
	});
		
	$("#fs-profile-rate-comment").dialog({
		modal: true,
		title: "",
		autoOpen: false,
		buttons: 
		[
			{
				text: "Abbrechen",
				click: function(){
					$("#fs-profile-rate-comment").dialog("close");
				}
			},
			{
				text: "Absenden",
				click: function(){
					ajreq("rate",{app:"profile",type:2,id:$("#profile-rate-id").val(),message:$("#fsprofileratemsg").val()});
				}
			}
		]
	}).siblings(".ui-dialog-titlebar").remove();
');

	if (!S::may()) {
		$func->addJs('clearInterval(g_interval_newBasket);');
	} else {
		$func->addJs('
		sock.connect();
		user.token = "' . S::user('token') . '";
		info.init();
	');
	}
	/*
	 * Browser location abfrage nur einmal dann in session speichern
	 */
	if ($pos = S::get('blocation')) {
		$func->addJsFunc('
		function getBrowserLocation(success)
		{
			success({
				lat:' . floatval($pos['lat']) . ',
				lon:' . floatval($pos['lon']) . '
			});
		}
	');
	} else {
		$func->addJsFunc('
		function getBrowserLocation(success)
		{
			if(navigator.geolocation)
			{
				navigator.geolocation.getCurrentPosition(function(pos){
					ajreq("savebpos",{app:"map",lat:pos.coords.latitude,lon:pos.coords.longitude});
					success({
						lat: pos.coords.latitude,
						lon: pos.coords.longitude
					});
				});
			}
		}
	');
	}
}

$page = $response->getContent();
$isUsingResponse = $page !== '--';
if ($isUsingResponse) {
	$response->send();
} else {
	$twig = DI::$shared->get(\Foodsharing\Lib\Twig::class);
	$page = $twig->render('layouts/' . $g_template . '.twig', $func->generateAndGetGlobalViewData($usesWebpack));
}

if (isset($cache) && $cache->shouldCache()) {
	$cache->cache($page);
}

if (!$isUsingResponse) {
	echo $page;
}
