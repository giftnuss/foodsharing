<?php

namespace Foodsharing\Helpers;

use Foodsharing\Lib\Func;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Services\ImageService;
use Foodsharing\Services\SanitizerService;
use Twig\Environment;

final class PageHelper
{
	private $add_css;
	private $bread;
	private $hidden;
	private $content_main;
	private $content_right;
	private $content_left;
	private $content_bottom;
	private $content_top;
	private $content_overtop;
	private $js_func;
	private $js;
	private $head;
	private $title;
	private $webpackScripts;
	private $webpackStylesheets;
	private $func;
	private $session;
	private $sanitizerService;
	private $imageService;
	private $routeHelper;
	private $translationHelper;
	public $jsData = [];
	private $twig;

	public function __construct(
		Func $func,
		Session $session,
		SanitizerService $sanitizerService,
		ImageService $imageService,
		Environment $twig,
		RouteHelper $routeHelper,
		TranslationHelper $translationHelper
	) {
		$this->content_main = '';
		$this->content_right = '';
		$this->content_left = '';
		$this->content_bottom = '';
		$this->content_top = '';
		$this->content_overtop = '';
		$this->add_css = '';
		$this->bread = array();
		$this->hidden = '';
		$this->js_func = '';
		$this->js = '';
		$this->head = '';
		$this->title = ['foodsharing'];
		$this->func = $func;
		$this->session = $session;
		$this->sanitizerService = $sanitizerService;
		$this->imageService = $imageService;
		$this->twig = $twig;
		$this->routeHelper = $routeHelper;
		$this->translationHelper = $translationHelper;
	}

	public function generateAndGetGlobalViewData(): array
	{
		global $g_broadcast_message;
		global $content_left_width;
		global $content_right_width;

		$menu = $this->getMenu();

		$this->getMessages();

		$mainWidth = 24;

		$contentLeft = $this->getContent(CNT_LEFT);
		$contentRight = $this->getContent(CNT_RIGHT);

		if (!empty($contentLeft)) {
			$mainWidth -= $content_left_width;
		}

		if (!empty($contentRight)) {
			$mainWidth -= $content_right_width;
		}

		$bodyClasses = [];

		if ($this->session->may()) {
			$bodyClasses[] = 'loggedin';
		}

		$bodyClasses[] = 'page-' . $this->routeHelper->getPage();

		return [
			'head' => $this->getHeadData(),
			'bread' => $this->bread,
			'bodyClasses' => $bodyClasses,
			'serverDataJSON' => json_encode($this->getServerData()),
			'menu' => $menu,
			'dev' => FS_ENV == 'dev',
			'hidden' => $this->hidden,
			'isMob' => $this->func->isMob(),
			'broadcast_message' => $g_broadcast_message,
			'SRC_REVISION' => defined('SRC_REVISION') ? SRC_REVISION : null,
			'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? BASE_URL,
			'is_foodsharing_dot_at' => strpos($_SERVER['HTTP_HOST'] ?? BASE_URL, 'foodsharing.at') !== false,
			'content' => [
				'main' => [
					'html' => $this->getContent(CNT_MAIN),
					'width' => $mainWidth
				],
				'left' => [
					'html' => $contentLeft,
					'width' => $content_left_width,
					'id' => 'left'
				],
				'right' => [
					'html' => $contentRight,
					'width' => $content_right_width,
					'id' => 'right'
				],
				'top' => [
					'html' => $this->getContent(CNT_TOP),
					'id' => 'content_top'
				],
				'bottom' => [
					'html' => $this->getContent(CNT_BOTTOM),
					'id' => 'content_bottom'
				],
				'overtop' => [
					'html' => $this->getContent(CNT_OVERTOP)
				]
			]
		];
	}

	/**
	 * This is used to set window.serverData on in the frontend.
	 */
	public function getServerData()
	{
		$user = $this->session->get('user');

		$userData = [
			'id' => $this->session->id(),
			'firstname' => $user['name'],
			'lastname' => $user['nachname'],
			'may' => $this->session->may(),
			'verified' => $this->session->isVerified(),
			'avatar' => [
				'mini' => $this->imageService->img($user['photo'], 'mini'),
				'50' => $this->imageService->img($user['photo'], '50'),
				'130' => $this->imageService->img($user['photo'], '130')
			]
		];

		if ($this->session->may()) {
			$userData['token'] = $this->session->user('token');
		}

		$location = null;

		if ($pos = $this->session->get('blocation')) {
			$location = [
				'lat' => (float)$pos['lat'],
				'lon' => (float)$pos['lon'],
			];
		}

		$ravenConfig = null;

		if (defined('RAVEN_JAVASCRIPT_CONFIG')) {
			$ravenConfig = RAVEN_JAVASCRIPT_CONFIG;
		}

		return array_merge($this->jsData, [
			'user' => $userData,
			'page' => $this->routeHelper->getPage(),
			'subPage' => $this->routeHelper->getSubPage(),
			'location' => $location,
			'ravenConfig' => $ravenConfig,
			'translations' => $this->translationHelper->getTranslations(),
			'isDev' => getenv('FS_ENV') === 'dev'
		]);
	}

	private function getMenu(): string
	{
		$regions = [];
		$stores = [];
		$workingGroups = [];
		if (isset($_SESSION['client']['bezirke']) && is_array($_SESSION['client']['bezirke'])) {
			foreach ($_SESSION['client']['bezirke'] as $region) {
				$region = array_merge($region, ['isBot' => $this->session->isAdminFor($region['id'])]);
				if ($region['type'] == Type::WORKING_GROUP) {
					$workingGroups[] = $region;
				} else {
					$regions[] = $region;
				}
			}
		}
		if (isset($_SESSION['client']['betriebe']) && is_array($_SESSION['client']['betriebe'])) {
			$stores = $_SESSION['client']['betriebe'];
		}

		$loggedIn = $this->session->may();

		$params = array_merge(
			[
				'loggedIn' => $loggedIn,
				'fsId' => $this->session->id(),
				'image' => $loggedIn ? $this->imageService->img() : '',
				'mailbox' => $this->session->get('mailbox'),
				'hasFsRole' => $this->session->may('fs'),
				'isOrgaTeam' => $this->session->isOrgaTeam(),
				'may' => [
					'editBlog' => $this->session->mayEditBlog(),
					'editQuiz' => $this->session->mayEditQuiz(),
					'handleReports' => $this->session->mayHandleReports(),
					'addStore' => $this->session->may('bieb'),
				],
				'stores' => array_values($stores),
				'regions' => $regions,
				'workingGroups' => $workingGroups,
			]
		);

		return $this->twig->render(
			'partials/vue-wrapper.twig',
			[
				'id' => 'vue-topbar',
				'component' => 'topbar',
				'props' => $params,
			]
		);
	}

	private function getHeadData(): array
	{
		return [
			'title' => implode(' | ', $this->title),
			'extra' => $this->head,
			'css' => str_replace(["\r", "\n"], '', $this->add_css),
			'jsFunc' => $this->js_func,
			'js' => $this->js,
			'ravenConfig' => null,
			'stylesheets' => $this->webpackStylesheets,
			'scripts' => $this->webpackScripts
		];
	}

	private function getMessages(): void
	{
		if (!isset($_SESSION['msg'])) {
			$_SESSION['msg'] = array();
		}
		if (isset($_SESSION['msg']['error']) && !empty($_SESSION['msg']['error'])) {
			$msg = '';
			foreach ($_SESSION['msg']['error'] as $e) {
				$msg .= '<div class="item">' . $e . '</div>';
			}
			$this->addJs('pulseError("' . $this->sanitizerService->jsSafe($msg, '"') . '");');
		}
		if (isset($_SESSION['msg']['info']) && !empty($_SESSION['msg']['info'])) {
			$msg = '';
			foreach ($_SESSION['msg']['info'] as $i) {
				$msg .= '<p>' . $i . '</p>';
			}
			$this->addJs('pulseInfo("' . $this->sanitizerService->jsSafe($msg, '"') . '");');
		}
		if (isset($_SESSION['msg']['info']) && !empty($_SESSION['msg']['info'])) {
			$msg = '';
			foreach ($_SESSION['msg']['info'] as $i) {
				$msg .= '<p>' . $i . '</p>';
			}
			$this->addJs('pulseSuccess("' . $this->sanitizerService->jsSafe($msg, '"') . '");');
		}
		$_SESSION['msg']['info'] = array();
		$_SESSION['msg']['success'] = array();
		$_SESSION['msg']['error'] = array();
	}

	private function getContent(int $positionCode = CNT_MAIN): string
	{
		switch ($positionCode) {
			case CNT_MAIN:
				$content = $this->content_main;
				break;
			case CNT_RIGHT:
				$content = $this->content_right;
				break;
			case CNT_TOP:
				$content = $this->content_top;
				break;
			case CNT_BOTTOM:
				$content = $this->content_bottom;
				break;
			case CNT_LEFT:
				$content = $this->content_left;
				break;
			case CNT_OVERTOP:
				$content = $this->content_overtop;
				break;
			default:
				$content = '';
				break;
		}

		return $content;
	}

	public function addContent(string $newContent, int $positionCode = CNT_MAIN): void
	{
		switch ($positionCode) {
			case CNT_MAIN:
				$this->content_main .= $newContent;
				break;
			case CNT_RIGHT:
				$this->content_right .= $newContent;
				break;
			case CNT_TOP:
				$this->content_top .= $newContent;
				break;
			case CNT_BOTTOM:
				$this->content_bottom .= $newContent;
				break;
			case CNT_LEFT:
				$this->content_left .= $newContent;
				break;
			case CNT_OVERTOP:
				$this->content_overtop .= $newContent;
				break;
			default:
				break;
		}
	}

	public function addBread(string $name, string $href = ''): void
	{
		$this->bread[] = ['name' => $name, 'href' => $href];
	}

	public function addWebpackScript(string $src): void
	{
		$this->webpackScripts[] = $src;
	}

	public function addWebpackStylesheet(string $src): void
	{
		$this->webpackStylesheets[] = $src;
	}

	public function addStyle(string $css): void
	{
		$this->add_css .= trim($css);
	}

	public function addJs(string $njs): void
	{
		$this->js .= $njs;
	}

	public function addJsFunc(string $nfunc): void
	{
		$this->js_func .= $nfunc;
	}

	public function addHead(string $str): void
	{
		$this->head .= "\n" . $str;
	}

	public function addTitle(string $name): void
	{
		$this->title[] = $name;
	}

	public function addHidden(string $html): void
	{
		$this->hidden .= $html;
	}

	public function hiddenDialog(string $table, array $fields, string $title = '', array $option = array()): void
	{
		$width = '';
		if (isset($option['width'])) {
			$width = 'width:' . $option['width'] . ',';
		}
		$id = $this->func->id('dialog_' . $table);

		$form = '';
		foreach ($fields as $f) {
			$form .= $f;
		}

		$get = '';
		if (isset($_GET['id'])) {
			$get = '<input type="hidden" name="id" value="' . (int)$_GET['id'] . '" />';
		}

		$this->addHidden('<div id="' . $id . '"><form>' . $form . $get . '</form></div>');

		$success = '';
		if (isset($option['success'])) {
			$success = $option['success'];
		}

		if (isset($option['reload'])) {
			$success .= 'reload();';
		}

		$this->addJs('
		$("#' . $id . '").dialog({
		' . $width . '
		autoOpen:false,
		modal:true,
		title:"' . $title . '",
		buttons:
		{
			"Speichern":function()
			{
				showLoader();
				$.ajax({

					dataType:"json",
					url:"/xhr.php?f=update_' . $table . '&" + $("#' . $id . ' form").serialize(),
					success : function(data){
						$("#' . $id . '").dialog(\'close\');
						' . $success . '
						if(data.script != undefined)
						{
							$.globalEval(data.script);
						}
					},
					complete : function(){
						hideLoader();
					}
				});
			}
		}
	});
	');
	}
}
