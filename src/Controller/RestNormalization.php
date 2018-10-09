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
	 * @param string $photoVersion type of the photo, one of '', 'crop_', 'thumb_crop_', 'mini_q', '130_q_'
	 *
	 * @return array
	 */
	public static function normalizeFoodsaver($data, $photoVersion = ''): array
	{
		return [
			'id' => (int)$data['fs_id'],
			'name' => $data['fs_name'],
			'avatar' => $data['fs_photo'] ? ('/images/' . $photoVersion . $data['fs_photo']) : null,
			'sleepStatus' => $data['sleep_status'],
		];
	}
}
