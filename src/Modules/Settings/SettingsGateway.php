<?php

namespace Foodsharing\Modules\Settings;

use Foodsharing\Modules\Core\BaseGateway;

class SettingsGateway extends BaseGateway
{
	public function logChangedSetting($fsId, $old, $new, $logChangedKeys, $changerId = null)
	{
		if (!$changerId) {
			$changerId = $fsId;
		}
		/* the logic is not exactly matching the update mechanism but should be close enough to get all changes... */
		foreach ($logChangedKeys as $k) {
			if (array_key_exists($k, $new) && $new[$k] != $old[$k]) {
				$this->db->insert('fs_foodsaver_change_history', [
					'date' => date(\DateTime::ISO8601),
					'fs_id' => $fsId,
					'changer_id' => $changerId,
					'object_name' => $k,
					'old_value' => $old[$k],
					'new_value' => $new[$k]
				]);
			}
		}
	}
}
