<?php

namespace Foodsharing\Modules\Login;

use Foodsharing\Helpers\EmailHelper;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Legal\LegalGateway;

class LoginGateway extends BaseGateway
{
	private $emailHelper;
	private $translationHelper;
	private $legalGateway;

	public function __construct(EmailHelper $emailHelper, TranslationHelper $translationHelper, Database $db, LegalGateway $legalGateway)
	{
		$this->emailHelper = $emailHelper;
		$this->translationHelper = $translationHelper;
		$this->legalGateway = $legalGateway;

		parent::__construct($db);
	}

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
			['id', 'password', 'bezirk_id', 'admin', 'orgateam', 'photo'],
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

	public function activate(string $email, string $token): bool
	{
		return $this->db->update('fs_foodsaver', ['active' => 1], ['email' => strip_tags($email), 'token' => strip_tags($token)]) > 0;
	}

	public function insertNewUser(array $data, string $token): int
	{
		/*
				[name] => Peter
				[email] => peter@pan.de
				[pw] => 12345
				[avatar] => 5427fb55f3a5d.jpg
				[phone] => 02261889971
				[lat] => 48.0649838
				[lon] => 7.885475300000053
				[str] => Bauerngasse
				[nr] => 6
				[plz] => 79211
				[country] => DE
		*/

		return $this->db->insert(
			'fs_foodsaver',
			[
				'rolle' => 0,
				'active' => 0,
				'email' => strip_tags($data['email']),
				'password' => strip_tags($this->password_hash($data['pw'])),
				'name' => strip_tags($data['name']),
				'nachname' => strip_tags($data['surname']),
				'geb_datum' => strip_tags($data['birthdate']),
				'handy' => strip_tags($data['mobile_phone']),
				'newsletter' => (int)$data['newsletter'],
				'geschlecht' => (int)$data['gender'],
				'anmeldedatum' => $this->db->now(),
				'privacy_notice_accepted_date' => $this->legalGateway->getPnVersion(),
				'privacy_policy_accepted_date' => $this->legalGateway->getPpVersion(),
				'token' => strip_tags($token),
				'beta' => 1
			]
		);
	}

	public function checkResetKey(string $key)
	{
		return $this->db->fetchValueByCriteria('fs_pass_request', 'foodsaver_id', ['name' => strip_tags($key)]);
	}

	public function newPassword(array $data)
	{
		if (strlen($data['pass1']) > 4) {
			if ($fsid = $this->db->fetchValueByCriteria(
				'fs_pass_request',
				'foodsaver_id',
				['name' => strip_tags($data['k'])]
			)) {
				$this->db->delete('fs_pass_request', ['foodsaver_id' => (int)$fsid]);

				return $this->db->update(
					'fs_foodsaver',
					[
						'password' => strip_tags($this->password_hash($data['pass1']))
					],
					['id' => (int)$fsid]
				);
			}
		}

		return false;
	}

	public function addPassRequest(string $email, $mail = true)
	{
		if ($fs = $this->db->fetchByCriteria(
			'fs_foodsaver',
			['id', 'email', 'name', 'geschlecht'],
			['deleted_at' => null, 'email' => strip_tags($email)]
		)) {
			$key = bin2hex(random_bytes(16));

			$this->db->insertOrUpdate('fs_pass_request', [
				'foodsaver_id' => $fs['id'],
				'name' => $key,
				'time' => $this->db->now()
			]);

			if ($mail) {
				$vars = [
					'link' => BASE_URL . '/?page=login&sub=passwordReset&k=' . $key,
					'name' => $fs['name'],
					'anrede' => $this->translationHelper->genderWord($fs['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r')
				];

				$this->emailHelper->tplMail('user/reset_password', $fs['email'], $vars);

				return true;
			}

			return $key;
		}

		return false;
	}
}
