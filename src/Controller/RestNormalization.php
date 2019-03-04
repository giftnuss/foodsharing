<?php

namespace Foodsharing\Controller;

/**
 * Utility class that can be user by all controllers to format objects for
 * uniform Rest responses.
 */
class RestNormalization
{
	/**
	 * Returns the response data for a foodsaver including id, name, photo url,
	 * and sleep-status.
	 *
	 * @param array $data the foodsaver data from the database
	 * @param string $prefix a prefix for the entries in the data array
	 *
	 * @return array
	 */
	public static function normalizeFoodsaver(array $data, string $prefix = ''): array
	{
		//sleep_status is used with and without prefix
		$sleepStatus = self::getSleepStatus($data, $prefix);

		return [
			'id' => (int)$data[$prefix . 'id'],
			'name' => $data[$prefix . 'name'],
			'avatar' => $data[$prefix . 'photo'] ?? null,
			'sleepStatus' => $sleepStatus,
		];
	}

	private static function getSleepStatus(array $data, string $prefix)
	{
		if (isset($data[$prefix . 'sleep_status'])) {
			$sleepStatus = $data[$prefix . 'sleep_status'];
		} elseif (isset($data['sleep_status'])) {
			$sleepStatus = $data['sleep_status'];
		} else {
			$sleepStatus = null;
		}

		return $sleepStatus;
	}
}
