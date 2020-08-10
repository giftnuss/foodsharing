<?php

namespace Foodsharing\Modules\Register;

use Exception;
use Foodsharing\Modules\Login\LoginGateway;
use Foodsharing\Modules\Register\DTO\RegisterData;
use Foodsharing\Utility\EmailHelper;
use Foodsharing\Utility\TranslationHelper;

class RegisterTransactions
{
	private $loginGateway;
	private $emailHelper;
	private $translationHelper;

	public function __construct(
		LoginGateway $loginGateway,
		EmailHelper $emailHelper,
		TranslationHelper $translationHelper
	) {
		$this->loginGateway = $loginGateway;
		$this->emailHelper = $emailHelper;
		$this->translationHelper = $translationHelper;
	}

	/**
	 * Registers a user, sends out the registration email, and returns the user's Id.
	 *
	 * @return int the user Id
	 *
	 * @throws Exception if the database insert fails
	 */
	public function registerUser(RegisterData $data): int
	{
		$token = bin2hex(random_bytes(12));
		$id = $this->loginGateway->insertNewUser($data, $token);
		if (!$id) {
			throw new Exception('could not register user');
		}

		// send activation email
		$this->emailHelper->tplMail('user/join', $data->email, [
			'name' => $data->firstName,
			'link' => BASE_URL . '/?page=login&sub=activate&e=' . urlencode($data->email) . '&t=' . urlencode($token),
			'anrede' => $this->translationHelper->s('anrede_' . $data->gender)
		], false, true);

		return $id;
	}
}
