<?php

namespace Foodsharing\Controller;

/**
 * Utility class that can be user by all controllers to format objects for
 * uniform Rest responses.
 */
class RestNormalization
{
	/**
	 * Formats a timestamp to the DATE_ATOM format.
	 *
	 * @param int $timestamp a timestamp
	 *
	 * @return string
	 */
	public static function normalizeDate(int $timestamp): string
	{
		return date(DATE_ATOM, $timestamp);
	}

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
		$store = [
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
			'contactPerson' => $data['ansprechpartner'],
			'storeCategoryId' => (int)$data['betrieb_kategorie_id'],
			'cooperationStatus' => (int)$data['betrieb_status_id'],
			'updatedAt' => self::normalizeDate(strtotime($data['status_date'])),
			'teamStatus' => (int)$data['team_status'],
			'chain' => [],
			'responsibleUserIds' => [],
			'notes' => [],
		];

		if (isset($data['kette'])) {
			$store['chain'] = $data['kette'];
		}
		if (isset($data['verantwortlicher']) && is_array($data['verantwortlicher'])) {
			$store['responsibleUserIds'] = array_map(function ($u) {
				return (int)$u;
			}, $data['verantwortlicher']);
		}
		if (isset($data['notizen']) && is_array($data['notizen'])) {
			$store['notes'] = array_map(function ($n) {
				return self::normalizeStoreNote($n);
			}, $data['notizen']);
		}

		return $store;
	}

	/**
	 * Returns the response data for an address.
	 *
	 * @param array $data the address data from the database
	 *
	 * @return array
	 */
	public static function normalizeAddress(array $data): array
	{
		return [
			'street' => $data['str'],
			'houseNumber' => (int)$data['hsnr'],
			'city' => $data['stadt'],
			'postalCode' => (int)$data['plz']
		];
	}

	/**
	 * Returns the response data for a note on a store's wall (milestone).
	 *
	 * @param array $data the note data from the database
	 *
	 * @return array
	 */
	public static function normalizeStoreNote(array $data): array
	{
		return [
			'id' => (int)$data['id'],
			'foodsaverId' => (int)$data['foodsaver_id'],
			'text' => $data['text'],
			'createdAt' => self::normalizeDate($data['zeit_ts'])
		];
	}
}
