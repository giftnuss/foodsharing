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

		$this->db->execute('LOCK TABLES `fs_bell` WRITE');
		$this->db->execute('ALTER TABLE `fs_bell` AUTO_INCREMENT = (SELECT MAX(id) FROM `fs_bell`)');
		$this->db->execute('UNLOCK TABLES');
	}
}
