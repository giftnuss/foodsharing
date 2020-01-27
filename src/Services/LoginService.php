<?php

namespace Foodsharing\Services;

class LoginService
{
	private const ACTIVATION_MAIL_LIMIT_PER_DAY = 3;

	/**
	 * @param string $oldToken
	 *
	 * @return string
	 */
	public function generateMailActivationToken(string $oldToken): string
	{
		$tokenData = $this->extractTokenData($oldToken);
		$date = date('Ymd');

		if ($tokenData['date'] === $date) {
			$count = $tokenData['count'] + 1;
		}

		$token = bin2hex(random_bytes(12));

		return base64_encode($token . '+' . $date . '-' .  $count);
	}

	/**
	 * @param string $token
	 * @param int $limit
	 *
	 * @return bool
	 */
	public function validateTokenLimit($token, $limit = self::ACTIVATION_MAIL_LIMIT_PER_DAY): bool
	{
		$tokenData = $this->extractTokenData($token);

		if ($tokenData['count'] > $limit && $tokenData['date'] === date('Ymd')) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $token Base64 encoded token
	 *
	 * @return array Array containing both date and count encoded in token
	 */
	private function extractTokenData(string $token): array
	{
		$string = base64_decode($token);
		preg_match("/\+(\d*)-/", $string, $matches);
		$date = $matches[1];

		// Old style tokens should return valid data
		if ($matches[1] === null) {
			return [
				"date" => date("Ymd"),
				"count" => 1,
			];
		}

		$count = substr($string, strpos($string, "-") + 1);

		return [
			"date" => $date,
			"count" => $count,
		];
	}
}
