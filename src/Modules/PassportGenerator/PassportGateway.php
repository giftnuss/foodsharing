<?php

namespace Foodsharing\Modules\PassportGenerator;

use Foodsharing\Modules\Core\BaseGateway;

class PassportGateway extends BaseGateway
{
	public function passGen($bot_id, $fsid)
	{
		return $this->db->insert('fs_pass_gen', [
			'foodsaver_id' => $fsid,
			'date' => $this->db->now(),
			'bot_id' => $bot_id,
		]);
	}
}
