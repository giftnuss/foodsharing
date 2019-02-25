<?php

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Helpers\PageHelper;
use Foodsharing\Lib\Cache\Caching;
use Foodsharing\Lib\Db\Db;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/* @var $container Container */
global $container;

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

error_reporting(E_ALL);

if (isset($_GET['logout'])) {
	$_SESSION['client'] = array();
	unset($_SESSION['client']);
}

$content_left_width = 5;
$content_right_width = 6;

/* @var $request Request */
$request = Request::createFromGlobals();
$response = new Response('--');

/* @var $dataHelper DataHelper */
$dataHelper = $container->get(DataHelper::class);

/* @var $pageHelper PageHelper */
$pageHelper = $container->get(PageHelper::class);

/* @var $identificationHelper IdentificationHelper */
$identificationHelper = $container->get(IdentificationHelper::class);

/* @var $viewUtils Utils */
$viewUtils = $container->get(Utils::class);

$g_template = 'default';
$g_data = $dataHelper->getPostData();

/* @var $db Db */
$db = $container->get(Db::class);

$pageHelper->addHidden('<a id="' . $identificationHelper->id('fancylink') . '" href="#fancy">&nbsp;</a>');
$pageHelper->addHidden('<div id="' . $identificationHelper->id('fancy') . '"></div>');

$pageHelper->addHidden('<div id="u-profile"></div>');
$pageHelper->addHidden('<ul id="hidden-info"></ul>');
$pageHelper->addHidden('<ul id="hidden-error"></ul>');
$pageHelper->addHidden('<div id="dialog-confirm" title="Wirklich l&ouml;schen?"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><span id="dialog-confirm-msg"></span><input type="hidden" value="" id="dialog-confirm-url" /></p></div>');
$pageHelper->addHidden('<div id="uploadPhoto"><form method="post" enctype="multipart/form-data" target="upload" action="/xhr.php?f=addPhoto"><input type="file" name="photo" onchange="uploadPhoto();" /> <input type="hidden" id="uploadPhoto-fs_id" name="fs_id" value="" /></form><div id="uploadPhoto-preview"></div><iframe name="upload" width="1" height="1" src=""></iframe></div>');

$pageHelper->addHidden('<div id="fs-profile"></div>');

$pageHelper->addHidden('<div id="fs-profile-rate-comment">' . $viewUtils->v_form_textarea('fs-profile-rate-msg', array('desc' => '...')) . '</div>');
