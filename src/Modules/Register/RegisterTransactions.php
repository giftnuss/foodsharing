<?php

namespace Foodsharing\Modules\Register;

use Exception;
use Foodsharing\Modules\Login\LoginGateway;
use Foodsharing\Modules\Register\DTO\RegisterData;
use Foodsharing\Utility\EmailHelper;
use Foodsharing\Utility\LoginService;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegisterTransactions
{
	private LoginGateway $loginGateway;
	private EmailHelper $emailHelper;
	private TranslatorInterface $translator;
	private $loginService;

	public function __construct(
		LoginGateway $loginGateway,
		EmailHelper $emailHelper,
		TranslatorInterface $translator,
		LoginService $loginService
	) {
		$this->loginGateway = $loginGateway;
		$this->emailHelper = $emailHelper;
		$this->translator = $translator;
		$this->loginService = $loginService;
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
		$token = $this->loginService->generateMailActivationToken(1);
		$activationUrl = BASE_URL . '/?page=login&a=activate&e=' . urlencode($data->email) . '&t=' . urlencode($token);
		$id = $this->loginGateway->insertNewUser($data, $token);
		if (!$id) {
			throw new Exception('could not register user');
		}

		// send activation email
		$this->emailHelper->tplMail('user/join', $data->email, [
			'name' => $data->firstName,
			'link' => $activationUrl,
			'anrede' => $this->translator->trans('salutation.' . $data->gender),
		], false, true);

		return $id;
	}
}
