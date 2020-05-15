<?php

namespace Foodsharing\Lib;

use Exception;
use Flourish\fAuthorization;
use Flourish\fImage;
use Flourish\fSession;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Modules\Buddy\BuddyGateway;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Quiz\QuizHelper;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Services\StoreService;

class Session
{
	private $mem;
	private $foodsaverGateway;
	private $quizHelper;
	private $regionGateway;
	private $buddyGateway;
	private $storeGateway;
	private $storeService;
	private $initialized = false;
	private $translationHelper;

	public function __construct(
		Mem $mem,
		FoodsaverGateway $foodsaverGateway,
		QuizHelper $quizHelper,
		RegionGateway $regionGateway,
		BuddyGateway $buddyGateway,
		StoreGateway $storeGateway,
		StoreService $storeService,
		TranslationHelper $translationHelper
	) {
		$this->mem = $mem;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->quizHelper = $quizHelper;
		$this->regionGateway = $regionGateway;
		$this->buddyGateway = $buddyGateway;
		$this->storeGateway = $storeGateway;
		$this->storeService = $storeService;
		$this->translationHelper = $translationHelper;
	}

	public function initIfCookieExists()
	{
		if (isset($_COOKIE[session_name()]) && !$this->initialized) {
			$this->init();
		}
	}

	public function checkInitialized()
	{
		if (!$this->initialized) {
			throw new Exception('Session not initialized');
		}
	}

	public function init($rememberMe = false)
	{
		if ($this->initialized) {
			throw new Exception('Session is already initialized');
		}

		$this->initialized = true;

		ini_set('session.save_handler', 'redis');
		ini_set('session.save_path', 'tcp://' . REDIS_HOST . ':' . REDIS_PORT);

		fSession::setLength('24 hours', '2 weeks');

		if ($rememberMe) {
			// This regenerates the session id even if it's already persistent, we want to only set it when logging in
			fSession::enablePersistence();
		}

		fAuthorization::setAuthLevels(
			[
				'admin' => 100,
				'orga' => 70,
				'bot' => 60,
				'bieb' => 45,
				'fs' => 40,
				'user' => 30,
				'user_unauth' => 20,
				'presse' => 15,
				'guest' => 10
			]
		);

		fSession::open();

		$cookieExpires = $this->isPersistent() ? strtotime('2 weeks') : 0;
		if (!isset($_COOKIE['CSRF_TOKEN']) || !$_COOKIE['CSRF_TOKEN'] || !$this->isValidCsrfToken('cookie', $_COOKIE['CSRF_TOKEN'])) {
			setcookie('CSRF_TOKEN', $this->generateCrsfToken('cookie'), $cookieExpires, '/');
		} elseif ($this->isPersistent() && isset($_COOKIE['CSRF_TOKEN']) && isset($_COOKIE['PHPSESSID'])) {
			// Extend the duration of the cookies in every request
			setcookie('CSRF_TOKEN', $_COOKIE['CSRF_TOKEN'], $cookieExpires, '/');
			setcookie('PHPSESSID', $_COOKIE['PHPSESSID'], $cookieExpires, '/');
		}
	}

	private function isPersistent(): bool
	{
		return $_SESSION['fSession::type'] === 'persistent';
	}

	public function setAuthLevel($role)
	{
		fAuthorization::setLoginPage('/?page=login');
		fAuthorization::setUserAuthLevel($role);
		fAuthorization::setUserACLs(
			[
				'posts' => ['*'],
				'users' => ['add', 'edit', 'delete'],
				'groups' => ['add'],
				'*' => ['list']
			]
		);
	}

	public function logout()
	{
		if ($this->initialized) {
			$this->mem->logout($this->id());
			$this->set('user', false);
			fAuthorization::destroyUserInfo();
			$this->setAuthLevel('guest');
			$this->destroy();
		}
	}

	public function user($index)
	{
		$user = $this->get('user');

		return $user[$index];
	}

	public function id()
	{
		if (!$this->initialized) {
			return null;
		}

		return fAuthorization::getUserToken();
	}

	public function may($role = 'user')
	{
		if (!$this->initialized) {
			return false;
		}
		if (fAuthorization::checkAuthLevel($role)) {
			return true;
		}

		return false;
	}

	public function getLocation()
	{
		if (!$this->initialized) {
			return ['lat' => null, 'lon' => null];
		}

		$loc = fSession::get('g_location', false);
		if (!$loc) {
			$loc = $this->foodsaverGateway->getFoodsaverAddress($this->id());
			$this->set('g_location', ['lat' => $loc['lat'], 'lon' => $loc['lon']]);
		}

		return $loc;
	}

	public function setLocation($lat, $lng)
	{
		$this->set('g_location', [
			'lat' => $lat,
			'lon' => $lng
		]);
	}

	public function destroy()
	{
		$this->checkInitialized();
		fSession::destroy();
	}

	public function set($key, $value)
	{
		$this->checkInitialized();
		fSession::set($key, $value);
	}

	public function get($var)
	{
		if (!$this->initialized) {
			return false;
		}

		return fSession::get($var, false);
	}

	public function getLocale()
	{
		if (!$this->initialized) {
			return 'de';
		}

		return fSession::get('locale', 'de');
	}

	/**
	 * gets a user specific option and will be available after next login.
	 *
	 * @param $name
	 */
	public function option($key)
	{
		return $this->get('useroption_' . $key);
	}

	public function setOption($key, $val)
	{
		$this->foodsaverGateway->setOption($this->id(), $key, $val);
		$this->set('useroption_' . $key, $val);
	}

	public function addMsg($message, $type, $title = null)
	{
		$this->checkInitialized();
		$msg = fSession::get('g_message', []);

		if (!isset($msg[$type])) {
			$msg[$type] = [];
		}

		if (!$title) {
			$title = ' ' . $this->translationHelper->s($type);
		} else {
			$title = ' ';
		}

		$msg[$type][] = ['msg' => $message, 'title' => $title];
		fSession::set('g_message', $msg);
	}

	/**
	 * static method for disable session writing
	 * this is important if more than one ajax request is sended to the server, if session writing is enabled php is waiting for finish and the requests cant live together.
	 */
	public function noWrite()
	{
		session_write_close();
	}

	public function getRegions(): array
	{
		if (isset($_SESSION['client']['bezirke']) && is_array($_SESSION['client']['bezirke'])) {
			return $_SESSION['client']['bezirke'];
		}
		// TODO enable to receive Sentry messages
		// trigger_error('$this->session->getRegions(): accessed but not initialized yet', E_USER_NOTICE);
		return [];
	}

	public function getMyAmbassadorRegionIds()
	{
		$out = [];
		if (isset($_SESSION['client']['botschafter']) && is_array($_SESSION['client']['botschafter'])) {
			foreach ($_SESSION['client']['botschafter'] as $b) {
				$out[] = $b['bezirk_id'];
			}
		}

		if (!empty($out)) {
			return $out;
		}

		return false;
	}

	public function isAdminFor(int $regionId): bool
	{
		if ($this->isAmbassador()) {
			foreach ($_SESSION['client']['botschafter'] as $b) {
				if ($b['bezirk_id'] == $regionId) {
					return true;
				}
			}
		}

		return false;
	}

	public function listRegionIDs(): array
	{
		$out = [];
		if (isset($_SESSION['client']['bezirke']) && is_array($_SESSION['client']['bezirke'])) {
			foreach ($_SESSION['client']['bezirke'] as $region) {
				$out[] = $region['id'];
			}
		}

		return $out;
	}

	public function getCurrentRegionId()
	{
		if (isset($_SESSION['client']['bezirk_id'])) {
			return $_SESSION['client']['bezirk_id'];
		}
	}

	public function setPhoto($file)
	{
		$_SESSION['client']['photo'] = $file;
	}

	private function isInUserGroup(string $group): bool
	{
		if (isset($_SESSION['client']['group'][$group])) {
			return true;
		}

		return false;
	}

	public function isSiteAdmin()
	{
		return $this->isInUserGroup('admin');
	}

	public function isOrgaTeam()
	{
		return $this->isInUserGroup('orgateam');
	}

	public function isAmbassador(): bool
	{
		if (isset($_SESSION['client']['botschafter'])) {
			return true;
		}

		return false;
	}

	public function login($fs_id = null, $rememberMe = false)
	{
		if (!$this->initialized) {
			$this->init($rememberMe);
		}

		$this->refreshFromDatabase($fs_id);
	}

	public function refreshFromDatabase($fs_id = null): void
	{
		$this->checkInitialized();

		if ($fs_id === null) {
			$fs_id = $this->id();
		}

		$fs = $this->foodsaverGateway->getFoodsaverDetails($fs_id);
		if (!$fs) {
			throw new \Exception('Foodsaver details not found in database.');
		}
		$this->set('g_location', [
			'lat' => $fs['lat'],
			'lon' => $fs['lon']
		]);

		$hastodo_id = $this->quizHelper->refreshQuizData($fs_id, $fs['rolle']);
		$hastodo = $hastodo_id > 0;
		$this->set('hastodoquiz', $hastodo);
		$this->set('hastodoquiz-id', $hastodo_id);

		$mailbox = false;
		if ((int)$fs['mailbox_id'] > 0) {
			$mailbox = true;
		}

		if ((int)$fs['bezirk_id'] > 0 && $fs['rolle'] > 0) {
			$this->regionGateway->addMember($fs_id, $fs['bezirk_id']);
		}

		if ($master = $this->regionGateway->getMasterId($fs['bezirk_id'])) {
			$this->regionGateway->addMember($fs_id, $master);
		}

		if ($fs['photo'] != '' && file_exists('images/mini_q_' . $fs['photo'])) {
			$image1 = new fImage('images/mini_q_' . $fs['photo']);
			if ($image1->getWidth() > 36) {
				$image1->cropToRatio(1, 1);
				$image1->resize(35, 35);
				$image1->saveChanges();
			}
		}

		$fs['buddys'] = $this->buddyGateway->listBuddyIds($fs_id);

		fAuthorization::setUserToken($fs['id']);
		$this->setAuthLevel($this->rolleWrapInt($fs['rolle']));

		$this->set('user', [
			'name' => $fs['name'],
			'nachname' => $fs['nachname'],
			'photo' => $fs['photo'],
			'bezirk_id' => $fs['bezirk_id'],
			'email' => $fs['email'],
			'rolle' => $fs['rolle'],
			'type' => $fs['type'],
			'verified' => $fs['verified'],
			'token' => $fs['token'],
			'mailbox_id' => $fs['mailbox_id'],
			'gender' => $fs['geschlecht'],
			'privacy_policy_accepted_date' => $fs['privacy_policy_accepted_date'],
			'privacy_notice_accepted_date' => $fs['privacy_notice_accepted_date']
		]);
		$this->set('buddy-ids', $fs['buddys']);

		/*
		 * Add entry into user -> session set
		 */
		$this->mem->userAddSession($fs_id, session_id());

		/*
		 * store all options in the session
		*/
		if (!empty($fs['option'])) {
			$options = unserialize($fs['option']);
			foreach ($options as $key => $val) {
				$this->setOption($key, $val);
			}
		}

		$_SESSION['login'] = true;
		$_SESSION['client'] = [
			'id' => $fs['id'],
			'bezirk_id' => $fs['bezirk_id'],
			'group' => ['member' => true],
			'photo' => $fs['photo'],
			'rolle' => (int)$fs['rolle'],
			'verified' => (int)$fs['verified']
		];
		if ($fs['admin'] == 1) {
			$_SESSION['client']['group']['admin'] = true;
		}
		if ($fs['orgateam'] == 1) {
			$_SESSION['client']['group']['orgateam'] = true;
		}
		if ((int)$fs['rolle'] > 0) {
			if ($r = $this->regionGateway->listRegionsForBotschafter($fs['id'])
			) {
				$_SESSION['client']['botschafter'] = $r;
				$_SESSION['client']['group']['botschafter'] = true;
				$mailbox = true;
				foreach ($r as $rr) {
					$this->regionGateway->addOrUpdateMember($fs['id'], $rr['id']);
				}
			}

			if ($r = $this->regionGateway->listForFoodsaver($fs['id'])) {
				$_SESSION['client']['bezirke'] = [];
				foreach ($r as $rr) {
					$_SESSION['client']['bezirke'][$rr['id']] = [
						'id' => $rr['id'],
						'name' => $rr['name'],
						'type' => $rr['type']
					];
				}
			}
		}

		if ($r = $this->storeGateway->listStoreIdsForBieb($fs['id'])) {
			$_SESSION['client']['verantwortlich'] = $r;
			$_SESSION['client']['group']['verantwortlich'] = true;
			$mailbox = true;
		}
		$this->set('mailbox', $mailbox);
	}

	private function rolleWrapInt($roleInt)
	{
		$roles = [
			0 => 'user',
			1 => 'fs',
			2 => 'bieb',
			3 => 'bot',
			4 => 'orga',
			5 => 'admin'
		];

		return $roles[$roleInt];
	}

	public function mayBezirk($regionId): bool
	{
		return isset($_SESSION['client']['bezirke'][$regionId]) || $this->isAdminFor($regionId) || $this->isOrgaTeam();
	}

	public function isAdminForAWorkGroup()
	{
		if ($all_group_admins = $this->mem->get('all_global_group_admins')) {
			return in_array($this->id(), unserialize($all_group_admins));
		}

		return false;
	}

	public function isVerified()
	{
		if ($this->isOrgaTeam()) {
			return true;
		}

		if (isset($_SESSION['client']['verified']) && $_SESSION['client']['verified'] == 1) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the current user is an ambassador for one of the regions in the list of region IDs.
	 *
	 * @param array $regionIds list of region IDs
	 * @param bool $include_groups if working group should be included in the check
	 * @param bool $include_parent_regions if the parent regions should be included in the check
	 */
	public function isAmbassadorForRegion($regionIds, $include_groups = true, $include_parent_regions = false): bool
	{
		if (is_array($regionIds) && count($regionIds) && $this->isAmbassador()) {
			if ($include_parent_regions) {
				$regionIds = $this->regionGateway->listRegionsIncludingParents($regionIds);
			}
			foreach ($_SESSION['client']['botschafter'] as $b) {
				foreach ($regionIds as $regId) {
					if ($b['bezirk_id'] == $regId && ($include_groups || $b['type'] != Type::WORKING_GROUP)) {
						return true;
					}
				}
			}
		}

		return false;
	}

	public function generateCrsfToken(string $key)
	{
		$token = bin2hex(random_bytes(16));
		$this->set("csrf[$key][$token]", true);

		return $token;
	}

	public function isValidCsrfToken(string $key, string $token): bool
	{
		if (defined('CSRF_TEST_TOKEN') && $token === CSRF_TEST_TOKEN) {
			return true;
		}

		return $this->get("csrf[$key][$token]");
	}

	public function isValidCsrfHeader(): bool
	{
		// enable CSRF Protection only for loggedin users
		if (!$this->id()) {
			return true;
		}

		if (!isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
			return false;
		}

		return $this->isValidCsrfToken('cookie', $_SERVER['HTTP_X_CSRF_TOKEN']);
	}

	public function isMob(): bool
	{
		return isset($_SESSION['mob']) && $_SESSION['mob'] == 1;
	}
}
