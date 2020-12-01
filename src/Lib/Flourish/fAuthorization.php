<?php

namespace Flourish;

/**
 * Allows defining and checking user authentication via ACLs, authorization levels or a simple logged in/not logged in scheme.
 *
 * @copyright  Copyright (c) 2007-2011 Will Bond
 * @author     Will Bond [wb] <will@flourishlib.com>
 * @license    http://flourishlib.com/license
 *
 * @see       http://flourishlib.com/fAuthorization
 *
 * @version    1.0.0b6
 * @changes    1.0.0b6  Fixed ::checkIP() to not trigger a notice when `$_SERVER['REMOTE_ADDR']` is not set [wb, 2011-05-10]
 * @changes    1.0.0b5  Added ::getLoginPage() [wb, 2010-03-09]
 * @changes    1.0.0b4  Updated class to use new fSession API [wb, 2009-10-23]
 * @changes    1.0.0b3  Updated class to use new fSession API [wb, 2009-05-08]
 * @changes    1.0.0b2  Fixed a bug with using named IP ranges in ::checkIP() [wb, 2009-01-10]
 * @changes    1.0.0b   The initial implementation [wb, 2007-06-14]
 */
class fAuthorization
{
	// The following constants allow for nice looking callbacks to static methods
	const checkACL = 'fAuthorization::checkACL';
	const checkAuthLevel = 'fAuthorization::checkAuthLevel';
	const destroyUserInfo = 'fAuthorization::destroyUserInfo';
	const getUserACLs = 'fAuthorization::getUserACLs';
	const getUserAuthLevel = 'fAuthorization::getUserAuthLevel';
	const getUserToken = 'fAuthorization::getUserToken';
	const setAuthLevels = 'fAuthorization::setAuthLevels';
	const setLoginPage = 'fAuthorization::setLoginPage';
	const setUserACLs = 'fAuthorization::setUserACLs';
	const setUserAuthLevel = 'fAuthorization::setUserAuthLevel';
	const setUserToken = 'fAuthorization::setUserToken';

	/**
	 * The valid auth levels.
	 *
	 * @var array
	 */
	private static $levels = null;

	/**
	 * Checks to see if the logged in user meets the requirements of the ACL specified.
	 *
	 * @param  string $resource    The resource we are checking permissions for
	 * @param  string $permission  The permission to require from the user
	 *
	 * @return bool  If the user has the required permissions
	 */
	public static function checkACL($resource, $permission)
	{
		if (self::getUserACLs() === null) {
			return false;
		}

		$acls = self::getUserACLs();

		if (!isset($acls[$resource]) && !isset($acls['*'])) {
			return false;
		}

		if (isset($acls[$resource])) {
			if (in_array($permission, $acls[$resource]) || in_array('*', $acls[$resource])) {
				return true;
			}
		}

		if (isset($acls['*'])) {
			if (in_array($permission, $acls['*']) || in_array('*', $acls['*'])) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks to see if the logged in user has the specified auth level.
	 *
	 * @param  string $level  The level to check against the logged in user's level
	 *
	 * @return bool  If the user has the required auth level
	 */
	public static function checkAuthLevel($level)
	{
		if (self::getUserAuthLevel()) {
			self::validateAuthLevel(self::getUserAuthLevel());
			self::validateAuthLevel($level);

			$user_number = self::$levels[self::getUserAuthLevel()];
			$required_number = self::$levels[$level];

			if ($user_number >= $required_number) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Destroys the user's auth level and/or ACLs.
	 */
	public static function destroyUserInfo()
	{
		fSession::delete(__CLASS__ . '::user_auth_level');
		fSession::delete(__CLASS__ . '::user_acls');
		fSession::delete(__CLASS__ . '::user_token');
		fSession::delete(__CLASS__ . '::requested_url');
	}

	/**
	 * Gets the ACLs for the logged in user.
	 *
	 * @return array  The logged in user's ACLs
	 */
	public static function getUserACLs()
	{
		return fSession::get(__CLASS__ . '::user_acls', null);
	}

	/**
	 * Gets the authorization level for the logged in user.
	 *
	 * @return string  The logged in user's auth level
	 */
	public static function getUserAuthLevel()
	{
		return fSession::get(__CLASS__ . '::user_auth_level', null);
	}

	/**
	 * Gets the value that was set as the user token, `NULL` if no token has been set.
	 *
	 * @return mixed  The user token that had been set, `NULL` if none
	 */
	public static function getUserToken()
	{
		return fSession::get(__CLASS__ . '::user_token', null);
	}

	/**
	 * Sets the authorization levels to use for level checking.
	 *
	 * @param  array $levels  An associative array of `(string) {level} => (integer) {value}`, for each level
	 */
	public static function setAuthLevels($levels)
	{
		self::$levels = $levels;
	}

	/**
	 * Sets the ACLs for the logged in user.
	 *
	 * Array should be formatted like:
	 *
	 * {{{
	 * array (
	 *     (string) {resource name} => array(
	 *         (mixed) {permission}, ...
	 *     ), ...
	 * )
	 * }}}
	 *
	 * The resource name or the permission may be the single character `'*'`
	 * which acts as a wildcard.
	 *
	 * @param  array $acls  The logged in user's ACLs - see method description for format
	 */
	public static function setUserACLs($acls)
	{
		fSession::set(__CLASS__ . '::user_acls', $acls);
		fSession::regenerateID();
	}

	/**
	 * Sets the authorization level for the logged in user.
	 *
	 * @param  string $level  The logged in user's auth level
	 */
	public static function setUserAuthLevel($level)
	{
		self::validateAuthLevel($level);
		fSession::set(__CLASS__ . '::user_auth_level', $level);
		fSession::regenerateID();
	}

	/**
	 * Sets some piece of information to use to identify the current user.
	 *
	 * @param  mixed $token  The user's token. This could be a user id, an email address, a user object, etc.
	 */
	public static function setUserToken($token)
	{
		fSession::set(__CLASS__ . '::user_token', $token);
		fSession::regenerateID();
	}

	/**
	 * Makes sure auth levels have been set, and that the specified auth level is valid.
	 *
	 * @param  string $level  The level to validate
	 */
	private static function validateAuthLevel($level = null)
	{
		if (self::$levels === null) {
			throw new fProgrammerException(
				'No authorization levels have been set, please call %s',
				__CLASS__ . '::setAuthLevels()'
			);
		}
		if ($level !== null && !isset(self::$levels[$level])) {
			throw new fProgrammerException(
				'The authorization level specified, %1$s, is invalid. Must be one of: %2$s.',
				$level,
				join(', ', array_keys(self::$levels))
			);
		}
	}

	/**
	 * Forces use as a static class.
	 *
	 * @return fAuthorization
	 */
	private function __construct()
	{
	}
}

/*
 * Copyright (c) 2007-2011 Will Bond <will@flourishlib.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
