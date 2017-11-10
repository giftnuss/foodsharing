<?php

namespace Foodsharing\Lib\Session;

use Flourish\fAuthorization;
use Flourish\fSession;
use Foodsharing\Lib\Db\ManualDb;

class S
{
	public static function init()
	{
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

	public static function setAuthLevel($role)
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

	public static function logout()
	{
		self::set('user', false);
		fAuthorization::destroyUserInfo();
		self::setAuthLevel('guest');
	}

	public static function login($user)
	{
		if (isset($user['id']) && !empty($user['id']) && isset($user['rolle'])) {
			fAuthorization::setUserToken($user['id']);
			self::setAuthLevel(rolleWrapInt($user['rolle']));

			self::set('user', array(
				'name' => $user['name'],
				'nachname' => $user['nachname'],
				'photo' => $user['photo'],
				'bezirk_id' => $user['bezirk_id'],
				'email' => $user['email'],
				'rolle' => $user['rolle'],
				'type' => $user['type'],
				'token' => $user['token'],
				'mailbox_id' => $user['mailbox_id'],
				'gender' => $user['geschlecht']
			));

			self::set('buddy-ids', $user['buddys']);

			return true;
		}

		return false;
	}

	public static function user($index)
	{
		$user = self::get('user');

		return $user[$index];
	}

	public static function id()
	{
		return fAuthorization::getUserToken();
	}

	public static function may($role = 'user')
	{
		if (fAuthorization::checkAuthLevel($role)) {
			return true;
		}

		return false;
	}

	public static function getLocation()
	{
		$loc = fSession::get('g_location', false);
		if (!$loc) {
			$db = new ManualDb();
			$loc = $db->getValues(array('lat', 'lon'), 'foodsaver', fsId());
			self::set('g_location', $loc);
		}

		return $loc;
	}

	public static function setLocation($lat, $lng)
	{
		self::set('g_location', array(
			'lat' => $lat,
			'lon' => $lng
		));
	}

	public static function destroy()
	{
		fSession::destroy();
	}

	public static function set($key, $value)
	{
		fSession::set($key, $value);
	}

	public static function get($var)
	{
		return fSession::get($var, false);
	}

	/**
	 * gets a user specific option and will be available after next login.
	 *
	 * @param $name
	 */
	public static function option($key)
	{
		return self::get('useroption_' . $key);
	}

	public static function setOption($key, $val, $db = false)
	{
		if (!$db) {
			$db = new ManualDb();
		}

		$db->setOption($key, $val);
		self::set('useroption_' . $key, $val);
	}

	public static function addMsg($message, $type, $title = null)
	{
		$msg = fSession::get('g_message', array());

		if (!isset($msg[$type])) {
			$msg[$type] = array();
		}

		if (!$title) {
			$title = ' ' . s($type);
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
	public static function noWrite()
	{
		session_write_close();
	}
}
