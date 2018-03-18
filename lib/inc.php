<?php

use Foodsharing\DI;
use Foodsharing\Lib\Cache\Caching;
use Foodsharing\Lib\Db\ManualDb;
use Foodsharing\Lib\Func;
use Foodsharing\Lib\Session\S;
use Foodsharing\Lib\View\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once 'config.inc.php';
S::init();
if (isset($g_page_cache)) {
	$cache = new Caching($g_page_cache);
	$cache->lookup();
}

require_once 'lang/DE/de.php';
require_once 'lib/minify/JSMin.php';

error_reporting(E_ALL);

if (isset($_GET['logout'])) {
	$_SESSION['client'] = array();
	unset($_SESSION['client']);
}

$content_left_width = 5;
$content_right_width = 6;

$request = Request::createFromGlobals();
$response = new Response('--');

$func = DI::$shared->get(Func::class);
$viewUtils = DI::$shared->get(Utils::class);

$g_template = 'default';
$g_data = $func->getPostData();

$db = DI::$shared->get(ManualDb::class);

/*
$func->addHead('<link rel="stylesheet" href="/css/pure/pure.min.css">
    <!--[if lte IE 8]>
        <link rel="stylesheet" href="/css/pure/grids-responsive-old-ie-min.css">
    <![endif]-->
    <!--[if gt IE 8]><!-->
        <link rel="stylesheet" href="/css/pure/grids-responsive-min.css">
    <!--<![endif]-->');
*/
//$func->addHead('<link rel="stylesheet" href="/fonts/font-awesome-4.7.0/css/font-awesome.min.css">');

$func->addHidden('<a id="' . $func->id('fancylink') . '" href="#fancy">&nbsp;</a>');
$func->addHidden('<div id="' . $func->id('fancy') . '"></div>');

$func->addHidden('<div id="u-profile"></div>');
$func->addHidden('<ul id="hidden-info"></ul>');
$func->addHidden('<ul id="hidden-error"></ul>');
$func->addHidden('<div id="comment">' . $viewUtils->v_form_textarea('Kommentar') . '<input type="hidden" id="comment-id" name="comment-id" value="0" /><input type="hidden" id="comment-name" name="comment-name" value="0" /></div>');
$func->addHidden('<div id="dialog-confirm" title="Wirklich l&ouml;schen?"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><span id="dialog-confirm-msg"></span><input type="hidden" value="" id="dialog-confirm-url" /></p></div>');
$func->addHidden('<div id="uploadPhoto"><form method="post" enctype="multipart/form-data" target="upload" action="xhr.php?f=addPhoto"><input type="file" name="photo" onchange="uploadPhoto();" /> <input type="hidden" id="uploadPhoto-fs_id" name="fs_id" value="" /></form><div id="uploadPhoto-preview"></div><iframe name="upload" width="1" height="1" src=""></iframe></div>');
//addHidden('<audio id="xhr-chat-notify"><source src="img/notify.ogg" type="audio/ogg"><source src="img/notify.mp3" type="audio/mpeg"><source src="img/notify.wav" type="audio/wav"></audio>');

$func->addHidden('<div id="fs-profile"></div>');

$user = '';
$g_body_class = '';
$g_broadcast_message = $db->qOne('SELECT `body` FROM fs_content WHERE `id` = 51');
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

$userData = [
    'id' => $func->fsId(),
    'may' => S::may(),
];

if (S::may()) {
    $userData['token'] = S::user('token');
}

if ($pos = S::get('blocation')) {
    $func->jsData['location'] = [
        'lat' => floatval($pos['lat']),
        'lon' => floatval($pos['lon']),
    ];
} else {
    $func->jsData['location'] = null;
}

$func->jsData['user'] = $userData;
$func->jsData['page'] = $func->getPage();

/*
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
*/
$func->addHidden('<div id="fs-profile-rate-comment">' . $viewUtils->v_form_textarea('fs-profile-rate-msg', array('desc' => '...')) . '</div>');

/*
    // TODO: add back in
if (!S::may()) {
	$func->addJs('clearInterval(g.interval_newBasket);');
} else {
	$func->addJs('
		sock.connect();
		user.token = "' . S::user('token') . '";
		info.init();
	');
}
*/
/*
 * Browser location abfrage nur einmal dann in session speichern
 */
/*
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
*/