<?php

namespace Foodsharing\Modules\Login;

use Foodsharing\Utility\EmailHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginService
{
	public const ACTIVATION_MAIL_LIMIT_PER_DAY = 3;
	public const MAIL_TOKEN_LENGTH = 24;

	private LoginGateway $loginGateway;
	private EmailHelper $emailHelper;
	private TranslatorInterface $translator;

	public function __construct(LoginGateway $loginGateway, EmailHelper $emailHelper, TranslatorInterface $translator)
	{
		$this->loginGateway = $loginGateway;
		$this->emailHelper = $emailHelper;
		$this->translator = $translator;
	}

	public function generateMailActivationToken(int $count = 1): string
	{
		$token = bin2hex(random_bytes(12));
		$data = [
			't' => $token,
			'd' => date('Ymd'),
			'c' => $count,
		];

		return base64_encode(json_encode($data));
	}

	/**
	 * @param string $token Base64 encoded token
	 * @param int $limit How many days is the activation token valid
	 *
	 * @return array Array containing state isValid and count from token
	 */
	public function validateTokenLimit(string $token, int $limit = self::ACTIVATION_MAIL_LIMIT_PER_DAY): array
	{
		$tokenData = $this->extractTokenData($token);

		$isValid = ($tokenData['count'] <= $limit || $tokenData['date'] !== date('Ymd'));

		if ($tokenData['date'] === date('Ymd')) {
			++$tokenData['count'];
		} else {
			$tokenData['count'] = 1;
		}

		return [
			'isValid' => $isValid,
			'count' => $tokenData['count'],
		];
	}

	/**
	 * @param string $token Base64 encoded token
	 *
	 * @return array Array containing both date and count encoded in token
	 */
	private function extractTokenData(string $token): array
	{
		// Old style tokens should return valid data
		if (strlen($token) <= self::MAIL_TOKEN_LENGTH) {
			return [
				'date' => date('Ymd'),
				'count' => 0,
			];
		}

		$data = json_decode(base64_decode($token), true);

		return [
			'date' => $data['d'],
			'count' => $data['c'],
		];
	}

	/**
	 * Triggers a new verification mail to provided FS. Mails will only be sent
	 * if token is valid and FS mail is not yet verified.
	 */
	public function newMailActivation(int $fsId): bool
	{
		$data = $this->loginGateway->getMailActivationData($fsId);

		// Don't send a mail if mail address is already confirmed
		if ($data['active'] === 1) {
			return false;
		}

		$tokenData = $this->validateTokenLimit($data['token']);
		if (!$tokenData['isValid']) {
			return false;
		}
		$token = $this->generateMailActivationToken($tokenData['count']);
		$this->loginGateway->updateMailActivationToken($fsId, $token);

		$activationUrl = BASE_URL . '/?page=login&a=activate&e=' . urlencode($data['email']) . '&t=' . urlencode($token);

		$this->emailHelper->tplMail('user/join', $data['email'], [
			'name' => $data['name'],
			'link' => $activationUrl,
			'anrede' => $this->translator->trans('salutation.' . $data['geschlecht'])
		]);

		return true;
	}
}
