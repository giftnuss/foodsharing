<?php

namespace Foodsharing\Services;

class LoginService
{
	public const ACTIVATION_MAIL_LIMIT_PER_DAY = 3;

	/**
	 * @param int $count
	 *
	 * @return string
	 */
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
	 *
	 * @return array
	 */
	public function validateTokenLimit($token, $limit = self::ACTIVATION_MAIL_LIMIT_PER_DAY): array
	{
		$tokenData = $this->extractTokenData($token);

		if ($tokenData['count'] > $limit && $tokenData['date'] === date('Ymd')) {
			$isValid = false;
		} else {
			$isValid = true;
		}

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
}
