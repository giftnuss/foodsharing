<?php

namespace Foodsharing\Lib;

use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Region\RegionGateway;

final class Func
{
	private $ids;
	private $regionGateway;

	public $jsData = [];

	/**
	 * @var \Twig\Environment
	 */
	private $twig;

	/**
	 * @var Session
	 */
	private $session;

	public function __construct(
		RegionGateway $regionGateway
	) {
		$this->regionGateway = $regionGateway;
		$this->ids = array();
	}

	/**
	 * @required
	 */
	public function setTwig(\Twig\Environment $twig)
	{
		$this->twig = $twig;
	}

	/**
	 * @required
	 */
	public function setSession(Session $session)
	{
		$this->session = $session;
	}

	public function s($id)
	{
		global $g_lang;

		if (isset($g_lang[$id])) {
			return $g_lang[$id];
		}

		return $id;
	}

	public function sv($id, $var)
	{
		global $g_lang;
		if (is_array($var)) {
			$search = array();
			$replace = array();
			foreach ($var as $key => $value) {
				$search[] = '{' . $key . '}';
				$replace[] = $value;
			}

			return str_replace($search, $replace, $g_lang[$id]);
		}

		return str_replace('{var}', $var, $g_lang[$id]);
	}

	public function setEditData($data)
	{
		global $g_data;
		$g_data = $data;
	}

	public function getAction($a): bool
	{
		return isset($_GET['a']) && $_GET['a'] == $a;
	}

	public function pageLink($page, $id, $action = '')
	{
		if (!empty($action)) {
			$action = '&a=' . $action;
		}

		return array('href' => '/?page=' . $page . $action, 'name' => $this->s($id));
	}

	public function getActionId($a)
	{
		if (isset($_GET['a'], $_GET['id']) && $_GET['a'] == $a && (int)$_GET['id'] > 0) {
			return (int)$_GET['id'];
		}

		return false;
	}

	public function getMenu()
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

		return $this->getMenuFn(
			$loggedIn,
			$regions,
			$this->session->may('fs'),
			$this->session->isOrgaTeam(),
			$this->session->mayEditBlog(),
			$this->session->mayEditQuiz(),
			$this->session->mayHandleReports(),
			$stores,
			$workingGroups,
			$this->session->get('mailbox'),
			(int)$this->session->id(),
			$loggedIn ? $this->img() : '',
			$this->session->may('bieb')
		);
	}

	private function getMenuFn(
		bool $loggedIn, array $regions, bool $hasFsRole,
		bool $isOrgaTeam, bool $mayEditBlog, bool $mayEditQuiz, bool $mayHandleReports,
		array $stores, array $workingGroups,
		$sessionMailbox, int $fsId, string $image, bool $mayAddStore)
	{
		$params = array_merge([
			'loggedIn' => $loggedIn,
			'fsId' => $fsId,
			'image' => $image,
			'mailbox' => $sessionMailbox,
			'hasFsRole' => $hasFsRole,
			'isOrgaTeam' => $isOrgaTeam,
			'may' => [
				'editBlog' => $mayEditBlog,
				'editQuiz' => $mayEditQuiz,
				'handleReports' => $mayHandleReports,
				'addStore' => $mayAddStore
			],
			'stores' => array_values($stores),
			'regions' => $regions,
			'workingGroups' => $workingGroups
		]);

		return $this->twig->render('partials/vue-wrapper.twig', [
			'id' => 'vue-topbar',
			'component' => 'topbar',
			'props' => $params
		]);
	}

	public function preZero($i)
	{
		if ($i < 10) {
			return '0' . $i;
		}

		return $i;
	}

	public function autolink($str, $attributes = array())
	{
		$attributes['target'] = '_blank';
		$attrs = '';
		foreach ($attributes as $attribute => $value) {
			$attrs .= " {$attribute}=\"{$value}\"";
		}
		$str = ' ' . $str;
		$str = preg_replace(
			'`([^"=\'>])(((http|https|ftp)://|www.)[^\s<]+[^\s<\.)])`i',
			'$1<a href="$2"' . $attrs . '>$2</a>',
			$str
		);
		$str = substr($str, 1);
		$str = preg_replace('`href=\"www`', 'href="http://www', $str);
		// fügt http:// hinzu, wenn nicht vorhanden
		return $str;
	}

	public function isMob(): bool
	{
		return isset($_SESSION['mob']) && $_SESSION['mob'] == 1;
	}

	public function id($name)
	{
		$id = $this->makeId($name, $this->ids);

		$this->ids[$id] = true;

		return $id;
	}

	public function getPostData()
	{
		if (isset($_POST)) {
			return $_POST;
		}

		return array();
	}

	public function getValue($id)
	{
		global $g_data;

		if (isset($g_data[$id])) {
			return $g_data[$id];
		}

		return '';
	}

	public function goPage($page = false)
	{
		if (!$page) {
			$page = $this->getPage();
			if (isset($_GET['bid'])) {
				$page .= '&bid=' . (int)$_GET['bid'];
			}
		}
		$this->go('/?page=' . $page);
	}

	public function go($url)
	{
		header('Location: ' . $url);
		exit();
	}

	public function getPage()
	{
		$page = $this->getGet('page');
		if (!$page) {
			$page = 'index';
		}

		return $page;
	}

	private function getSubPage()
	{
		$sub_page = $this->getGet('sub');
		if (!$sub_page) {
			$sub_page = 'index';
		}

		return $sub_page;
	}

	public function getGetId($name)
	{
		if (isset($_GET[$name]) && (int)$_GET[$name] > 0) {
			return (int)$_GET[$name];
		}

		return false;
	}

	public function getGet($name)
	{
		if (isset($_GET[$name])) {
			return $_GET[$name];
		}

		return false;
	}

	public function makeId($text, $ids = false)
	{
		$text = strtolower($text);
		str_replace(
			array('ä', 'ö', 'ü', 'ß', ' '),
			array('ae', 'oe', 'ue', 'ss', '_'),
			$text
		);
		$out = preg_replace('/[^a-z0-9_]/', '', $text);

		if ($ids !== false && isset($ids[$out])) {
			$id = $out;
			$i = 0;
			while (isset($ids[$id])) {
				++$i;
				$id = $out . '-' . $i;
			}
			$out = $id;
		}

		return $out;
	}

	public function submitted(): bool
	{
		return isset($_POST) && !empty($_POST);
	}

	public function info($msg, $title = false)
	{
		$t = '';
		if ($title !== false) {
			$t = '<strong>' . $title . '</strong> ';
		}
		$_SESSION['msg']['info'][] = $msg;
	}

	public function error($msg, $title = false)
	{
		$t = '';
		if ($title !== false) {
			$t = '<strong>' . $title . '</strong> ';
		}
		$_SESSION['msg']['error'][] = $t . $msg;
	}

	private function getTranslations()
	{
		global $g_lang;

		return $g_lang;
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
				'mini' => $this->img($user['photo'], 'mini'),
				'50' => $this->img($user['photo'], '50'),
				'130' => $this->img($user['photo'], '130')
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
			'page' => $this->getPage(),
			'subPage' => $this->getSubPage(),
			'location' => $location,
			'ravenConfig' => $ravenConfig,
			'translations' => $this->getTranslations(),
			'isDev' => getenv('FS_ENV') === 'dev'
		]);
	}

	public function getBezirk()
	{
		return $this->regionGateway->getBezirk($this->session->getCurrentBezirkId());
	}

	public function genderWord($gender, $m, $w, $other)
	{
		$out = $other;
		if ($gender == 1) {
			$out = $m;
		} elseif ($gender == 2) {
			$out = $w;
		}

		return $out;
	}

	public function goSelf()
	{
		$this->go($this->getSelf());
	}

	public function getSelf()
	{
		return $_SERVER['REQUEST_URI'];
	}

	public function unsetAll($array, $fields)
	{
		$out = array();
		foreach ($fields as $f) {
			if (isset($array[$f])) {
				$out[$f] = $array[$f];
			}
		}

		return $out;
	}

	public function goLogin()
	{
		$this->go('/?page=login&ref=' . urlencode($_SERVER['REQUEST_URI']));
	}
}
