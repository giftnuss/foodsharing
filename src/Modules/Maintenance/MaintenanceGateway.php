<?php

namespace Foodsharing\Modules\Maintenance;

use Foodsharing\Modules\Core\BaseGateway;

class MaintenanceGateway extends BaseGateway
{
	public function deleteBells(array $bell_ids): void
	{
		$bellIds = implode(',', array_map('intval', $bell_ids));
		$this->db->delete('fs_foodsaver_has_bell', ['bell_id' => $bellIds]);

		$this->db->delete('fs_bell', ['id' => $bellIds]);
	}

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
}
