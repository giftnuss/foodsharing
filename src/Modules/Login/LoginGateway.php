<?php

namespace Foodsharing\Modules\Login;

use Foodsharing\Modules\Core\BaseGateway;

class LoginGateway extends BaseGateway
{
	public function login($email, $pass)
	{
		$email = trim($email);
		if ($this->db->exists('fs_email_blacklist', ['email' => $email])) {
			return null;
		}
		if ($fsid = $this->checkClient($email, $pass)) {
			$this->db->update(
				'fs_foodsaver',
				['last_login' => $this->db->now()],
				['id' => $fsid]
			);

			return $fsid;
		}

		return null;
	}

	/**
	 * Check given email and password combination,
	 * update password if old-style one is detected.
	 */
	public function checkClient($email, $pass = false)
	{
		$email = trim($email);
		if (strlen($email) < 2 || strlen($pass) < 1) {
			return false;
		}

		$user = $this->db->fetchByCriteria(
			'fs_foodsaver',
			['id', 'password', 'passwd', 'fs_password', 'bezirk_id', 'admin', 'orgateam', 'photo'],
			['email' => $email, 'deleted_at' => null]
		);

		// does the email exist?
		if (!$user) {
			return false;
		}

		// modern hashing algorithm
		if ($user['password']) {
			if (password_verify($pass, $user['password'])) {
				return $user['id'];
			}

			return false;

			// old hashing algorithm
		}

		if (
			($user['passwd'] && $user['passwd'] == $this->encryptMd5($email, $pass)) || // md5
			($user['fs_password'] && $user['fs_password'] == $this->fs_sha1hash($pass))  // sha1
		) {
			// update stored password to modern
			$this->db->update(
				'fs_foodsaver',
				['fs_password' => null, 'passwd' => null, 'password' => $this->password_hash($pass)],
				['id' => $user['id']]
			);

			return $user['id'];
		}

		return false;
	}

	/**
	 * hashes password with modern hashing algorithmn.
	 */
	public function password_hash($password)
	{
		return password_hash($password, PASSWORD_ARGON2I);
	}

	/**
	 * Generates md5 hash with email as salt. used before
	 * xx.02.2018.
	 */
	private function encryptMd5($email, $pass)
	{
		$email = strtolower($email);

		return md5($email . '-lz%&lk4-' . $pass);
	}

	/**
	 * Generate a foodsharing.de style hash before 12.12.2014
	 * fusion.
	 * Uses sha1 of concatenation of fixed salt and password.
	 */
	private function fs_sha1hash($pass)
	{
		$salt = 'DYZG93b04yJfIxfs2guV3Uub5wv7iR2G0FgaC9mi';

		return sha1($salt . $pass);
	}
}
