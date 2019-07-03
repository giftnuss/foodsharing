<?php

namespace Foodsharing\Modules\Buddy;

use Foodsharing\Modules\Core\BaseGateway;

class BuddyGateway extends BaseGateway
{
	public function listBuddies($fsId): array
	{
		$stm = '
			SELECT 	fs.`id`,
					fs.name,
					fs.nachname,
					fs.photo
			
			FROM 	fs_foodsaver fs,
					fs_buddy b
		
			WHERE 	b.buddy_id = fs.id
			AND 	b.foodsaver_id = :foodsaver_id
			AND 	b.confirmed = 1
		';

		return $this->db->fetchAll($stm, [':foodsaver_id' => $fsId]);
	}

	public function listBuddyIds($fsId): array
	{
		return $this->db->fetchAllValuesByCriteria('fs_buddy', 'buddy_id', ['foodsaver_id' => $fsId, 'confirmed' => 1]);
	}

	public function removeRequest($buddyId, $fsId): void
	{
		$this->db->delete('fs_buddy', ['foodsaver_id' => (int)$buddyId, 'buddy_id' => (int)$fsId]);
	}

	public function buddyRequestedMe($buddyId, $fsId): bool
	{
		if ($this->db->exists('fs_buddy', ['foodsaver_id' => (int)$buddyId, 'buddy_id' => (int)$fsId])) {
			return true;
		}

		return false;
	}

	public function buddyRequest($buddyId, $fsId): bool
	{
		$stm = '
			REPLACE INTO `fs_buddy`(`foodsaver_id`, `buddy_id`, `confirmed`)
			VALUES (:foodsaver_id, :buddy_id, 0)
		';
		$this->db->execute($stm, ['foodsaver_id' => (int)$fsId, 'buddy_id' => (int)$buddyId]);

		return true;
	}

	public function confirmBuddy($buddyId, $fsId): void
	{
		$stm = '
			REPLACE INTO `fs_buddy`(`foodsaver_id`, `buddy_id`, `confirmed`)
			VALUES (:foodsaver_id, :buddy_id, 1)
		';
		$this->db->execute($stm, ['foodsaver_id' => (int)$fsId, 'buddy_id' => (int)$buddyId]);
		$stm = '
			REPLACE INTO `fs_buddy`(`foodsaver_id`, `buddy_id`, `confirmed`)
			VALUES (:buddy_id, :foodsaver_id, 1)
		';
		$this->db->execute($stm, ['foodsaver_id' => (int)$fsId, 'buddy_id' => (int)$buddyId]);
	}
}
