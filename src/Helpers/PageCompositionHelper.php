<?php

namespace Foodsharing\Helpers;

use Foodsharing\Lib\Func;
use Foodsharing\Lib\Session;
use Foodsharing\Services\SanitizerService;

final class PageCompositionHelper
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

	public function __construct(Func $func, Session $session, SanitizerService $sanitizerService)
	{
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
		$this->title = array('foodsharing');
		$this->func = $func;
		$this->session = $session;
		$this->sanitizerService = $sanitizerService;
	}

	private function getContent($place = CNT_MAIN)
	{
		switch ($place) {
			case CNT_MAIN:
				return $this->content_main;
				break;
			case CNT_RIGHT:
				return $this->content_right;
				break;
			case CNT_TOP:
				return $this->content_top;
				break;
			case CNT_BOTTOM:
				return $this->content_bottom;
				break;
			case CNT_LEFT:
				return $this->content_left;
				break;
			case CNT_OVERTOP:
				return $this->content_overtop;
				break;
			default:
				return '';
				break;
		}
	}

	public function addContent($new_content, $place = CNT_MAIN)
	{
		switch ($place) {
			case CNT_MAIN:

				$this->content_main .= $new_content;
				break;
			case CNT_RIGHT:

				$this->content_right .= $new_content;
				break;

			case CNT_TOP:
				$this->content_top .= $new_content;
				break;

			case CNT_BOTTOM:

				$this->content_bottom .= $new_content;
				break;

			case CNT_LEFT:

				$this->content_left .= $new_content;
				break;

			case CNT_OVERTOP:

				$this->content_overtop .= $new_content;
				break;

			default:
				break;
		}
	}

	public function addBread($name, $href = '')
	{
		$this->bread[] = array('name' => $name, 'href' => $href);
	}

	public function generateAndGetGlobalViewData()
	{
		global $g_broadcast_message;
		global $content_left_width;
		global $content_right_width;

		$menu = $this->func->getMenu();

		$this->getMessages();

		$mainwidth = 24;

		$content_left = $this->getContent(CNT_LEFT);
		$content_right = $this->getContent(CNT_RIGHT);

		if (!empty($content_left)) {
			$mainwidth -= $content_left_width;
		}

		if (!empty($content_right)) {
			$mainwidth -= $content_right_width;
		}

		$bodyClasses = [];

		if ($this->session->may()) {
			$bodyClasses[] = 'loggedin';
		}

		$bodyClasses[] = 'page-' . $this->func->getPage();

		return [
			'head' => $this->getHeadData(),
			'bread' => $this->bread,
			'bodyClasses' => $bodyClasses,
			'serverDataJSON' => json_encode($this->func->getServerData()),
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

	private function getHeadData()
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

	private function getMessages()
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

	public function addHidden($html)
	{
		$this->hidden .= $html;
	}

	public function addWebpackScript($src)
	{
		$this->webpackScripts[] = $src;
	}

	public function addJsFunc($nfunc)
	{
		$this->js_func .= $nfunc;
	}

	public function addJs($njs)
	{
		$this->js .= $njs;
	}

	public function addWebpackStylesheet($src)
	{
		$this->webpackStylesheets[] = $src;
	}

	public function addHead($str)
	{
		$this->head .= "\n" . $str;
	}

	public function addStyle($css)
	{
		$this->add_css .= trim($css);
	}

	public function addTitle($name)
	{
		$this->title[] = $name;
	}

	public function hiddenDialog($table, $fields, $title = '', $option = array())
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
