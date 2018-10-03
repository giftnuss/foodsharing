<?php

namespace Foodsharing\Controller;

/**
 * Utility class that can be user by all controllers to format objects for 
 * uniform Rest responses.
 */
class RestNormalization {

	/**
	 * Returns the response data for a foodsaver including id, name, photo url,
	 * and sleep-status.
	 * 
	 * @param $data the foodsaver data
	 * @param $photoVersion type of the photo, one of '', 'crop_', 'thumb_crop_', 'mini_q', '130_q_'
	 */
	public static function normalizeFoodsaver($data, $photoVersion = '130_q_') {
		return [
			'id' => (int) $data['fs_id'],
			'name' => $data['fs_name'],
			//TODO:
			//'nachname' => (in_array('fs_nachname', $data) ? $data['fs_nachname'] : ''),
			'avatar' => $data['fs_photo'] ? ('/images/' . $photoVersion . $data['fs_photo']) : null,
			'sleepStatus' => $data['sleep_status'],
		];
	}
}
