<?php

namespace Foodsharing\Controller;

/**
 * Utility class that can be user by all controllers to format objects for 
 * uniform Rest responses.
 */
class RestNormalization {

	//TODO: photo versions
	public static function normalizeFoodsaver($data) {
		return [
			'id' => (int) $data['fs_id'],
			'name' => $data['fs_name'],
			//TODO:
			//'nachname' => (in_array('fs_nachname', $data) ? $data['fs_nachname'] : ''),
			'avatar' => $data['fs_photo'],
			//'avatar' => $b['fs_photo'] ? ('/images/130_q_' . $b['fs_photo']) : null,
			'sleepStatus' => $data['sleep_status'],
		];
	}
}
