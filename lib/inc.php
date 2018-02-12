<?php

use Foodsharing\Lib\Cache\Caching;
use Foodsharing\Lib\Db\ManualDb;
use Foodsharing\Lib\Session\S;

require_once 'config.inc.php';
global $g_view_utils;
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

$g_template = 'default';
$g_data = $g_func->getPostData();

$db = new ManualDb();

$g_func->addHead('<link rel="stylesheet" href="/css/pure/pure.min.css">
    <!--[if lte IE 8]>
        <link rel="stylesheet" href="/css/pure/grids-responsive-old-ie-min.css">
    <![endif]-->
    <!--[if gt IE 8]><!-->
        <link rel="stylesheet" href="/css/pure/grids-responsive-min.css">
    <!--<![endif]-->');
$g_func->addHead('<link rel="stylesheet" href="/fonts/font-awesome-4.7.0/css/font-awesome.min.css">');

$g_func->addHidden('<a id="' . $g_func->id('fancylink') . '" href="#fancy">&nbsp;</a>');
$g_func->addHidden('<div id="' . $g_func->id('fancy') . '"></div>');

$g_func->addHidden('<div id="u-profile"></div>');
$g_func->addHidden('<ul id="hidden-info"></ul>');
$g_func->addHidden('<ul id="hidden-error"></ul>');
$g_func->addHidden('<div id="comment">' . $g_view_utils->v_form_textarea('Kommentar') . '<input type="hidden" id="comment-id" name="comment-id" value="0" /><input type="hidden" id="comment-name" name="comment-name" value="0" /></div>');
$g_func->addHidden('<div id="dialog-confirm" title="Wirklich l&ouml;schen?"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><span id="dialog-confirm-msg"></span><input type="hidden" value="" id="dialog-confirm-url" /></p></div>');
$g_func->addHidden('<div id="uploadPhoto"><form method="post" enctype="multipart/form-data" target="upload" action="xhr.php?f=addPhoto"><input type="file" name="photo" onchange="uploadPhoto();" /> <input type="hidden" id="uploadPhoto-fs_id" name="fs_id" value="" /></form><div id="uploadPhoto-preview"></div><iframe name="upload" width="1" height="1" src=""></iframe></div>');
//addHidden('<audio id="xhr-chat-notify"><source src="img/notify.ogg" type="audio/ogg"><source src="img/notify.mp3" type="audio/mpeg"><source src="img/notify.wav" type="audio/wav"></audio>');

$g_func->addHidden('<div id="fs-profile"></div>');

$user = '';
$g_body_class = '';
$g_broadcast_message = $db->qOne('SELECT `body` FROM ' . PREFIX . 'content WHERE `id` = 51');
if (S::may()) {
	if (isset($_GET['uc'])) {
		if ($g_func->fsId() != $_GET['uc']) {
			$db->logout();
			$g_func->goLogin();
		}
	}

	$g_body_class = ' class="loggedin"';
	$user = 'user = {id:' . $g_func->fsId() . '};';
}

$g_func->addJs('
	' . $user . '
	$("#mainMenu > li > a").each(function(){
		if(parseInt(this.href.length) > 2 && this.href.indexOf("' . $g_func->getPage() . '") > 0)
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
$g_func->addHidden('<div id="fs-profile-rate-comment">' . $g_view_utils->v_form_textarea('fs-profile-rate-msg', array('desc' => '...')) . '</div>');

if (!S::may()) {
	$g_func->addJs('clearInterval(g_interval_newBasket);');
} else {
	$g_func->addJs('
		sock.connect();
		user.token = "' . S::user('token') . '";
		info.init();
	');
}
/*
 * Browser location abfrage nur einmal dann in session speichern
 */
if ($pos = S::get('blocation')) {
	$g_func->addJsFunc('
		function getBrowserLocation(success)
		{
			success({
				lat:' . floatval($pos['lat']) . ',
				lon:' . floatval($pos['lon']) . '
			});
		}
	');
} else {
	$g_func->addJsFunc('
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
