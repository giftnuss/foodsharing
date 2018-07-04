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

/* @var $func Func */
$func = DI::$shared->get(Func::class);

/* @var $viewUtils Utils */
$viewUtils = DI::$shared->get(Utils::class);

$g_template = 'default';
$g_data = $func->getPostData();

/* @var $db ManualDb */
$db = DI::$shared->get(ManualDb::class);

$func->addHidden('<a id="' . $func->id('fancylink') . '" href="#fancy">&nbsp;</a>');
$func->addHidden('<div id="' . $func->id('fancy') . '"></div>');

$func->addHidden('<div id="u-profile"></div>');
$func->addHidden('<ul id="hidden-info"></ul>');
$func->addHidden('<ul id="hidden-error"></ul>');
$func->addHidden('<div id="dialog-confirm" title="Wirklich l&ouml;schen?"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><span id="dialog-confirm-msg"></span><input type="hidden" value="" id="dialog-confirm-url" /></p></div>');
$func->addHidden('<div id="uploadPhoto"><form method="post" enctype="multipart/form-data" target="upload" action="/xhr.php?f=addPhoto"><input type="file" name="photo" onchange="uploadPhoto();" /> <input type="hidden" id="uploadPhoto-fs_id" name="fs_id" value="" /></form><div id="uploadPhoto-preview"></div><iframe name="upload" width="1" height="1" src=""></iframe></div>');

$func->addHidden('<div id="fs-profile"></div>');

$func->addHidden('<div id="fs-profile-rate-comment">' . $viewUtils->v_form_textarea('fs-profile-rate-msg', array('desc' => '...')) . '</div>');
