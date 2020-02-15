<?php

namespace Foodsharing\Utility;

use Foodsharing\Modules\Login\LoginGateway;

class LoginService
{
	public const ACTIVATION_MAIL_LIMIT_PER_DAY = 3;

	private $loginGateway;
	private $emailHelper;
	private $translationHelper;

	public function __construct(LoginGateway $loginGateway, EmailHelper $emailHelper, TranslationHelper $translationHelper)
	{
		$this->loginGateway = $loginGateway;
		$this->emailHelper = $emailHelper;
		$this->translationHelper = $translationHelper;
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
	 * @param string $token
	 * @param int $limit
	 */
	public function validateTokenLimit($token, $limit = self::ACTIVATION_MAIL_LIMIT_PER_DAY): array
	{
		$tokenData = $this->extractTokenData($token);

		$isValid = ($tokenData['count'] <= $limit || $tokenData['date'] !== date('Ymd'));

		if ($tokenData['date'] === date('Ymd')) {
			$tokenData['count'] = $tokenData['count'] + 1;
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
		if (strlen($token) <= 24) {
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

		// Don't send a mail if mail adress is already confirmed
		if ($data['active'] === 1) {
			return false;
		}

		$tokenData = $this->validateTokenLimit($data['token']);
		if ($tokenData['isValid']) {
			$token = $this->generateMailActivationToken($tokenData['count']);
			$this->loginGateway->updateMailActivationToken($fsId, $token);
		} else {
			return false;
		}

		$activationUrl = BASE_URL . '/?page=login&a=activate&e=' . urlencode($data['email']) . '&t=' . urlencode($token);

		$this->emailHelper->tplMail('user/join', $data['email'], [
			'name' => $data['name'],
			'link' => $activationUrl,
			'anrede' => $this->translationHelper->s('anrede_' . $data['geschlecht'])
		]);

		return true;
	}
}
