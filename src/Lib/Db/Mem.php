<?php

namespace Foodsharing\Lib\Db;

use Foodsharing\Lib\Func;
use Redis;

class Mem
{
	/**
	 * @var Redis
	 */
	public $cache;
	public $connected;
	/**
	 * @var Func
	 */
	private $func;

	public function __construct(Func $func)
	{
		$this->func = $func;
	}

	public function connect()
	{
		if (MEM_ENABLED) {
			$this->connected = true;
			$this->cache = new Redis();
			$this->cache->connect(REDIS_HOST, REDIS_PORT);
		}
	}

	// Set a key to a value, ttl in seconds
	public function set($key, $data, $ttl = 0)
	{
		if (MEM_ENABLED) {
			$this->ensureConnected();
			$options = array();
			if ($ttl > 0) {
				$options['ex'] = $ttl;
			}
			if ($options) {
				return $this->cache->set($key, $data, $options);
			} else {
				return $this->cache->set($key, $data);
			}
		}

		return false;
	}

	/* enqueue work of specified type.
	   counterpart of asynchronous queue runner in mails.control
	 */
	public function queueWork($type, $data)
	{
		if (MEM_ENABLED) {
			$e = serialize(array('type' => $type, 'data' => $data));
			$this->ensureConnected();

			return $this->cache->lPush('workqueue', $e);
		}
	}

	public function get($key)
	{
		if (MEM_ENABLED) {
			$this->ensureConnected();

			return $this->cache->get($key);
		}

		return false;
	}

	public function del($key)
	{
		if (MEM_ENABLED) {
			$this->ensureConnected();

			return $this->cache->delete($key);
		}

		return false;
	}

	public function user($id, $key)
	{
		return $this->get('user-' . $key . '-' . $id);
	}

	public function userSet($id, $key, $value)
	{
		return $this->set('user-' . $key . '-' . $id, $value);
	}

	public function userAppend($id, $key, $value)
	{
		$out = array();
		if ($val = $this->user($id, $key)) {
			if (is_array($val)) {
				$out = $val;
			}
		}
		$out[] = $value;

		return $this->set('user-' . $key . '-' . $id, $out);
	}

	public function userDel($id, $key)
	{
		return $this->del('user-' . $key . '-' . $id);
	}

	/*
	 * Add entry to the redis set that stores user -> session mappings.
	 * e.g. for user=20 and sessionid=mysessionid it would run the redis command:
	 *   > SADD php:user:20:sessions mysessionid
	 *
	 * This then provides a way to get all the active sessions for a user and expire old ones.
	 * See `chat/session-ids.lua` for a redis lua script that does this.
	 */
	public function userAddSession($fs_id, $session_id)
	{
		$this->ensureConnected();

		return $this->cache->sAdd(join(':', array('php', 'user', $fs_id, 'sessions')), $session_id);
	}

	public function userRemoveSession($fs_id, $session_id)
	{
		$this->ensureConnected();

		return $this->cache->sRem(join(':', array('php', 'user', $fs_id, 'sessions')), $session_id);
	}

	public function getPageCache()
	{
		return $this->get('pc-' . $_SERVER['REQUEST_URI'] . ':' . $this->func->fsId());
	}

	public function setPageCache($page, $ttl)
	{
		return $this->set('pc-' . $_SERVER['REQUEST_URI'] . ':' . $this->func->fsId(), $page, $ttl);
	}

	public function delPageCache($page)
	{
		return $this->del('pc-' . $page . ':' . $this->func->fsId());
	}

	/**
	 * Method to check users online status by checking timestamp from memcahce.
	 *
	 * @param int $fs_id
	 *
	 * @return bool
	 */
	public function userOnline($fs_id)
	{
		if ($time = $this->user($fs_id, 'active')) {
			if ((time() - $time) < 600) {
				return true;
			}
		}
		/*
		 * free memcache from userdata
		 */
		$this->userDel($fs_id, 'lastMailMessage');
		$this->userDel($fs_id, 'active');

		return false;
	}

	/**
	 * Method to check users online status by checking timestamp from memcache.
	 *
	 * @param int $fs_id
	 *
	 * @return bool
	 */
	public function userIsActive($fs_id)
	{
		if ($time = $this->user($fs_id, 'active')) {
			return !((time() - $time) > 600);
		}

		return false;
	}

	public function updateActivity($fs_id = null)
	{
		if ($fs_id) {
			$this->userSet($fs_id, 'active', time());
		}
	}

	public function logout($fs_id)
	{
		$this->userDel($fs_id, 'active');
		$this->userDel($fs_id, 'lastMailMessage');
		$this->userRemoveSession($fs_id, session_id());
	}

	public function ensureConnected()
	{
		if (!$this->connected) {
			$this->connect();
		}
	}
}
