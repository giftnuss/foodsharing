<?php

namespace Foodsharing\Lib\Session;

use Flourish\fAuthorization;
use Flourish\fSession;
use Foodsharing\Lib\Db\Db;
use Foodsharing\Lib\Func;
use Foodsharing\Modules\Core\Model;
use Foodsharing\Modules\Legal\LegalControl;
use Foodsharing\Modules\Legal\LegalGateway;

class S
{
	private $func;
	private $legalGateway;
	private $db;
	private $initialized = false;

	public function __construct(Func $func, LegalGateway $legalGateway, Db $db)
	{
		$this->func = $func;
		$this->legalGateway = $legalGateway;
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

	public function login($user)
	{
		if (isset($user['id']) && !empty($user['id']) && isset($user['rolle'])) {
			fAuthorization::setUserToken($user['id']);
			$this->setAuthLevel($this->func->rolleWrapInt($user['rolle']));

			$this->set('user', array(
				'name' => $user['name'],
				'nachname' => $user['nachname'],
				'photo' => $user['photo'],
				'bezirk_id' => $user['bezirk_id'],
				'email' => $user['email'],
				'rolle' => $user['rolle'],
				'type' => $user['type'],
				'token' => $user['token'],
				'mailbox_id' => $user['mailbox_id'],
				'gender' => $user['geschlecht'],
				'privacy_policy_accepted_date' => $user['privacy_policy_accepted_date'],
				'privacy_notice_accepted_date' => $user['privacy_notice_accepted_date']
			));

			$this->set('buddy-ids', $user['buddys']);

			return true;
		}

		return false;
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

	public function setOption($key, $val, Model $model)
	{
		$model->setOption($key, $val);
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
}
