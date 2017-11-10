<?php

namespace Foodsharing\Lib\Db;

use Redis;

class Mem
{
	public static $cache;
	public static $connected;

	public static function connect()
	{
		if (MEM_ENABLED) {
			self::$connected = true;
			self::$cache = new Redis();
			self::$cache->connect(REDIS_HOST, REDIS_PORT);
		}
	}

	// Set a key to a value, ttl in seconds
	public static function set($key, $data, $ttl = 0)
	{
		if (MEM_ENABLED) {
			$options = array();
			if ($ttl > 0) {
				$options['ex'] = $ttl;
			}
			if ($options) {
				return self::$cache->set($key, $data, $options);
			} else {
				return self::$cache->set($key, $data);
			}
		}

		return false;
	}

	/* enqueue work of specified type.
	   counterpart of asynchronous queue runner in mails.control
	 */
	public static function queueWork($type, $data)
	{
		if (MEM_ENABLED) {
			$e = serialize(array('type' => $type, 'data' => $data));

			return self::$cache->lPush('workqueue', $e);
		}
	}

	public static function get($key)
	{
		if (MEM_ENABLED) {
			return self::$cache->get($key);
		}

		return false;
	}

	public static function del($key)
	{
		if (MEM_ENABLED) {
			return self::$cache->delete($key);
		}

		return false;
	}

	public static function user($id, $key)
	{
		return self::get('user-' . $key . '-' . $id);
	}

	public static function userSet($id, $key, $value)
	{
		return self::set('user-' . $key . '-' . $id, $value);
	}

	public static function userAppend($id, $key, $value)
	{
		$out = array();
		if ($val = self::user($id, $key)) {
			if (is_array($val)) {
				$out = $val;
			}
		}
		$out[] = $value;

		return self::set('user-' . $key . '-' . $id, $out);
	}

	public static function userDel($id, $key)
	{
		return self::del('user-' . $key . '-' . $id);
	}

	/*
	 * Add entry to the redis set that stores user -> session mappings.
	 * e.g. for user=20 and sessionid=mysessionid it would run the redis command:
	 *   > SADD php:user:20:sessions mysessionid
	 *
	 * This then provides a way to get all the active sessions for a user and expire old ones.
	 * See `chat/session-ids.lua` for a redis lua script that does this.
	 */
	public static function userAddSession($fs_id, $session_id)
	{
		return self::$cache->sAdd(join(':', array('php', 'user', $fs_id, 'sessions')), $session_id);
	}

	public static function userRemoveSession($fs_id, $session_id)
	{
		return self::$cache->sRem(join(':', array('php', 'user', $fs_id, 'sessions')), $session_id);
	}

	public static function getPageCache()
	{
		global $g_page_cache_suffix;

		return self::get('pc-' . $_SERVER['REQUEST_URI'] . ':' . fsId());
	}

	public static function setPageCache($page, $ttl)
	{
		return self::set('pc-' . $_SERVER['REQUEST_URI'] . ':' . fsId(), $page, $ttl);
	}

	public static function delPageCache($page)
	{
		return self::del('pc-' . $page . ':' . fsId());
	}

	/**
	 * Method to check users online status by checking timestamp from memcahce.
	 *
	 * @param int $fs_id
	 *
	 * @return bool
	 */
	public static function userOnline($fs_id)
	{
		if ($time = self::user($fs_id, 'active')) {
			if ((time() - $time) < 600) {
				return true;
			}
		}
		/*
		 * free memcache from userdata
		 */
		self::userDel($fs_id, 'lastMailMessage');
		self::userDel($fs_id, 'active');

		return false;
	}
}

/* this initializes the static class - can be refactored when we have DI, should be fine for now */
Mem::connect();
