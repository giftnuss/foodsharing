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
	 * @param string $photoVersion type of the photo, one of '', 'crop_', 'thumb_crop_', 'mini_q', '130_q_'
	 *
	 * @return array
	 */
	public static function normalizeFoodsaver($data, $prefix = '', $photoVersion = ''): array
	{
		$sleepStatus = $data[$prefix . 'sleep_status'];
		if (isNull($sleepStatus) || isEmpty($sleepStatus)) {
			$sleepStatus = $data['sleep_status'];
		}

		return [
			'id' => (int)$data[$prefix . 'id'],
			'name' => $data[$prefix . 'name'],
			'avatar' => $data[$prefix . 'photo'] ? ('/images/' . $photoVersion . $data[$prefix . 'photo']) : null,
			'sleepStatus' => $sleepStatus,
		];
	}
}
