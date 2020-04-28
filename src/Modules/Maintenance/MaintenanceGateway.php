<?php

namespace Foodsharing\Modules\Maintenance;

use Foodsharing\Modules\Core\BaseGateway;

class MaintenanceGateway extends BaseGateway
{
	public function deactivateOldBaskets(): int
	{
		return $this->db->update(
			'fs_basket',
			['status' => 6],
			['status' => 1, 'until <' => $this->db->now()]);
	}

	public function deleteUnconfirmedFetchDates(): int
	{
		return $this->db->delete('fs_abholer', ['confirmed' => 0, 'date <' => $this->db->now()]);
	}

	public function wakeupSleepingUsers()
	{
		return $this->db->update(
			'fs_foodsaver',
			['sleep_status' => 0],
			['sleep_status' => 1, 'sleep_until >' => 0, 'sleep_until <' => $this->db->now()]);
	}
}
