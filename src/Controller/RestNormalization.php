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

	/**
	 * Returns the response data for a store.
	 *
	 * @param array $data the store data from the database
	 *
	 * @return array
	 */
	public static function normalizeStore(array $data): array
	{
		return [
			'id' => (int)$data['id'],
			'name' => $data['name'],
			'address' => self::normalizeAddress($data),
			'group' => [
				'id' => $data['bezirk_id'],
				'name' => $data['bezirk'],
			],
			'lat' => (float)$data['lat'],
			'lon' => (float)$data['lon'],
			'phone' => $data['telefon'],
			'fax' => $data['fax'],
			'email' => $data['email'],
			'contactPerson' => $data['verantwortlicher'],
			'chainId' => (int)$data['kette_id'],
			'storeCategoryId' => (int)$data['betrieb_kategorie_id'],
			'cooperationStatus' => (int)$data['betrieb_status_id'],
			'updatedAt' => $data['status_date'],
			'teamStatus' => (int)$data['team_status'],
			'notes' => $data['notizen']
		];
	}

	public static function normalizeAddress(array $data): array
	{
		return [
			'street' => $data['str'],
			'houseNumber' => (int)$data['hsnr'],
			'city' => $data['stadt'],
			'postalCode' => (int)$data['plz']
		];
	}
}
