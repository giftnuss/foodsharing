<?php

namespace Foodsharing\Modules\Login;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Legal\LegalGateway;
use Foodsharing\Modules\Register\DTO\RegisterData;
use Foodsharing\Utility\EmailHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginGateway extends BaseGateway
{
	private LegalGateway $legalGateway;
	private EmailHelper $emailHelper;
	private TranslatorInterface $translator;

	public function __construct(
		Database $db,
		LegalGateway $legalGateway,
		EmailHelper $emailHelper,
		TranslatorInterface $translator
	) {
		$this->legalGateway = $legalGateway;
		$this->emailHelper = $emailHelper;
		$this->translator = $translator;

		parent::__construct($db);
	}

	public function login(string $email, string $pass)
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

	public function isActivated($fsId): bool
	{
		$isActivated = $this->db->fetchValueByCriteria('fs_foodsaver', 'active', ['id' => $fsId]);

		return $isActivated === 1;
	}

	/**
	 * Check given email and password combination,
	 * update password if old-style one is detected.
	 */
	public function checkClient(string $email, $pass = false)
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
	 * hashes password with modern hashing algorithm.
	 */
	public function password_hash($password)
	{
		return password_hash($password, PASSWORD_ARGON2I);
	}

	public function activate(string $email, string $token): bool
	{
		return $this->db->update('fs_foodsaver', ['active' => 1], ['email' => strip_tags($email), 'token' => strip_tags($token)]) > 0;
	}

	public function insertNewUser(RegisterData $data, string $token): int
	{
		return $this->db->insert(
			'fs_foodsaver',
			[
				'rolle' => 0,
				'active' => 0,
				'email' => strip_tags($data->email),
				'password' => strip_tags($this->password_hash($data->password)),
				'name' => strip_tags($data->firstName),
				'nachname' => strip_tags($data->lastName),
				'geb_datum' => $data->birthday,
				'handy' => strip_tags($data->mobilePhone),
				'newsletter' => (int)$data->subscribeNewsletter,
				'geschlecht' => (int)$data->gender,
				'anmeldedatum' => $this->db->now(),
				'privacy_policy_accepted_date' => $this->legalGateway->getPpVersion(),
				'token' => strip_tags($token),
			]
		);
	}

	public function checkResetKey(string $key)
	{
		return $this->db->fetchValueByCriteria('fs_pass_request', 'foodsaver_id', ['name' => strip_tags($key)]);
	}

	public function newPassword(array $data)
	{
		if (strlen($data['pass1']) <= 4) {
			return false;
		}

		$fsid = $this->db->fetchValueByCriteria(
			'fs_pass_request',
			'foodsaver_id',
			['name' => strip_tags($data['k'])]
		);
		if (!$fsid) {
			return false;
		}

		$this->db->delete('fs_pass_request', ['foodsaver_id' => (int)$fsid]);

		return $this->db->update(
			'fs_foodsaver',
			[
				'password' => strip_tags($this->password_hash($data['pass1']))
			],
			['id' => (int)$fsid]
		);
	}

	public function getMailActivationData(int $fsId): array
	{
		return $this->db->fetchByCriteria(
			'fs_foodsaver',
			['email', 'token', 'name', 'geschlecht', 'active'],
			['id' => $fsId]
		);
	}

	public function updateMailActivationToken(int $fsId, string $token): int
	{
		return $this->db->update('fs_foodsaver', ['token' => $token], ['id' => $fsId]);
	}

	public function addPassRequest(string $email, bool $mail = true)
	{
		$fs = $this->db->fetchByCriteria(
			'fs_foodsaver',
			['id', 'email', 'name', 'geschlecht'],
			['deleted_at' => null, 'email' => strip_tags($email)]
		);
		if (!$fs) {
			return false;
		}

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
				'anrede' => $this->translator->trans('salutation.' . $fs['geschlecht']),
			];

			$this->emailHelper->tplMail('user/reset_password', $fs['email'], $vars, false, true);

			return true;
		}

		return $key;
	}
}
