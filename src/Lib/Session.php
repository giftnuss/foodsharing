<?php

namespace Foodsharing\Lib;

use Exception;
use Flourish\fAuthorization;
use Flourish\fSession;
use Foodsharing\Lib\Db\Db;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Modules\Buddy\BuddyGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Legal\LegalControl;
use Foodsharing\Modules\Legal\LegalGateway;
use Foodsharing\Modules\Quiz\QuizHelper;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;

class Session
{
	private $func;
	private $legalGateway;
	private $foodsaverGateway;
	private $quizHelper;
	private $regionGateway;
	private $buddyGateway;
	private $storeGateway;
	private $db;
	private $initialized = false;

	public function __construct(
		Func $func,
		LegalGateway $legalGateway,
		FoodsaverGateway $foodsaverGateway,
		QuizHelper $quizHelper,
		RegionGateway $regionGateway,
		BuddyGateway $buddyGateway,
		StoreGateway $storeGateway,
		Db $db
	) {
		$this->func = $func;
		$this->legalGateway = $legalGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->quizHelper = $quizHelper;
		$this->regionGateway = $regionGateway;
		$this->buddyGateway = $buddyGateway;
		$this->storeGateway = $storeGateway;
		$this->db = $db;
	}

	public function init()
	{
		if ($this->initialized) {
			throw new Exception('Session is already initialized');
		}
		$this->initialized = true;

		ini_set('session.save_handler', 'redis');
		ini_set('session.save_path', 'tcp://' . REDIS_HOST . ':' . REDIS_PORT);

		fSession::setLength('24 hours', '1 week');
		//fSession::enablePersistence();

		fAuthorization::setAuthLevels(
			array(
				'admin' => 100,
				'orga' => 70,
				'bot' => 60,
				'bieb' => 45,
				'fs' => 40,
				'user' => 30,
				'user_unauth' => 20,
				'presse' => 15,
				'guest' => 10
			)
		);

		fSession::open();
	}

	public function setAuthLevel($role)
	{
		fAuthorization::setLoginPage('/?page=login');
		fAuthorization::setUserAuthLevel($role);
		fAuthorization::setUserACLs(
			array(
				'posts' => array('*'),
				'users' => array('add', 'edit', 'delete'),
				'groups' => array('add'),
				'*' => array('list')
			)
		);
	}

	public function logout()
	{
		$this->set('user', false);
		fAuthorization::destroyUserInfo();
		$this->setAuthLevel('guest');
	}

	public function user($index)
	{
		$user = $this->get('user');

		return $user[$index];
	}

	public function getRouteOverride()
	{
		$ppVersion = $this->legalGateway->getPpVersion();
		$pnVersion = $this->legalGateway->getPnVersion();
		if ($this->id() &&
			(($ppVersion && $ppVersion != $this->user('privacy_policy_accepted_date')) ||
				($pnVersion && $this->user('rolle') >= 2 && $this->user('privacy_notice_accepted_date') != $pnVersion))) {
			/* Allow Settings page, otherwise redirect to legal page */
			if (in_array($this->func->getPage(), ['settings', 'logout'])) {
				return null;
			}

			return LegalControl::class;
		}

		return null;
	}

	public function id()
	{
		return fAuthorization::getUserToken();
	}

	public function may($role = 'user')
	{
		if (fAuthorization::checkAuthLevel($role)) {
			return true;
		}

		return false;
	}

	public function getLocation()
	{
		$loc = fSession::get('g_location', false);
		if (!$loc) {
			$loc = $this->db->getValues(array('lat', 'lon'), 'foodsaver', $this->func->fsId());
			$this->set('g_location', $loc);
		}

		return $loc;
	}

	public function setLocation($lat, $lng)
	{
		$this->set('g_location', array(
			'lat' => $lat,
			'lon' => $lng
		));
	}

	public function destroy()
	{
		fSession::destroy();
	}

	public function set($key, $value)
	{
		fSession::set($key, $value);
	}

	public function get($var)
	{
		return fSession::get($var, false);
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
		$msg = fSession::get('g_message', array());

		if (!isset($msg[$type])) {
			$msg[$type] = array();
		}

		if (!$title) {
			$title = ' ' . $this->func->s($type);
		} else {
			$title = ' ';
		}

		$msg[$type][] = array('msg' => $message, 'title' => $title);
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

	public function getBotBezirkIds()
	{
		$out = array();
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

	public function getMyBetriebIds()
	{
		$out = array();
		if (isset($_SESSION['client']['betriebe']) && is_array($_SESSION['client']['betriebe'])) {
			foreach ($_SESSION['client']['betriebe'] as $b) {
				$out[] = $b['id'];
			}
		}

		if (!empty($out)) {
			return $out;
		}

		return false;
	}

	public function getBezirkIds()
	{
		$out = array();
		if (isset($_SESSION['client']['bezirke']) && is_array($_SESSION['client']['bezirke'])) {
			foreach ($_SESSION['client']['bezirke'] as $b) {
				$out[] = $b['id'];
			}
		}

		if (!empty($out)) {
			return $out;
		}

		return false;
	}

	public function getCurrentBezirkId()
	{
		if (isset($_SESSION['client']['bezirk_id'])) {
			return $_SESSION['client']['bezirk_id'];
		}
	}

	public function setPhoto($file)
	{
		$_SESSION['client']['photo'] = $file;
	}

	public function mayGroup($group)
	{
		if (isset($_SESSION) && isset($_SESSION['client']['group'][$group])) {
			return true;
		}

		return false;
	}

	public function isOrgaTeam()
	{
		return $this->mayGroup('orgateam');
	}

	public function isBotschafter()
	{
		if (isset($_SESSION['client']['botschafter'])) {
			return true;
		}

		return false;
	}

	public function refreshFromDatabase($fs_id = null)
	{
		if ($fs_id === null) {
			$fs_id = $this->id();
		}

		Mem::updateActivity($fs_id);
		$fs = $this->foodsaverGateway->getFoodsaverDetails($fs_id);
		if (!$fs) {
			$this->func->goPage('logout');
		}
		$this->set('g_location', array(
			'lat' => $fs['lat'],
			'lon' => $fs['lon']
		));

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
		$this->setAuthLevel($this->func->rolleWrapInt($fs['rolle']));

		$this->set('user', array(
			'name' => $fs['name'],
			'nachname' => $fs['nachname'],
			'photo' => $fs['photo'],
			'bezirk_id' => $fs['bezirk_id'],
			'email' => $fs['email'],
			'rolle' => $fs['rolle'],
			'type' => $fs['type'],
			'token' => $fs['token'],
			'mailbox_id' => $fs['mailbox_id'],
			'gender' => $fs['geschlecht'],
			'privacy_policy_accepted_date' => $fs['privacy_policy_accepted_date'],
			'privacy_notice_accepted_date' => $fs['privacy_notice_accepted_date']
		));
		$this->set('buddy-ids', $fs['buddys']);

		/*
		 * Add entry into user -> session set
		 */
		Mem::userAddSession($fs_id, session_id());

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
		$_SESSION['client'] = array(
			'id' => $fs['id'],
			'bezirk_id' => $fs['bezirk_id'],
			'group' => array('member' => true),
			'photo' => $fs['photo'],
			'rolle' => (int)$fs['rolle'],
			'verified' => (int)$fs['verified']
		);
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

			if ($r = $this->regionGateway->listRegionsForFoodsaver($fs['id'])) {
				$_SESSION['client']['bezirke'] = array();
				foreach ($r as $rr) {
					$_SESSION['client']['bezirke'][$rr['id']] = array(
						'id' => $rr['id'],
						'name' => $rr['name'],
						'type' => $rr['type']
					);
				}
			}
		}
		$_SESSION['client']['betriebe'] = false;
		if ($r = $this->storeGateway->listStoresForFoodsaver($fs['id'])) {
			$_SESSION['client']['betriebe'] = array();
			foreach ($r as $rr) {
				$_SESSION['client']['betriebe'][$rr['id']] = $rr;
			}
		}

		if ($r = $this->storeGateway->listStoreIdsForBieb($fs['id'])) {
			$_SESSION['client']['verantwortlich'] = $r;
			$_SESSION['client']['group']['verantwortlich'] = true;
			$mailbox = true;
		}
		$this->set('mailbox', $mailbox);
	}
}
