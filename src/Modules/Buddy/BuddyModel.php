<?php

namespace Foodsharing\Modules\Buddy;

use Foodsharing\Modules\Core\Model;

class BuddyModel extends Model
{
	public function listBuddies()
	{
		return $this->q('
			SELECT 	fs.`id`,
					fs.name,
					fs.nachname,
					fs.photo
			
			FROM 	' . PREFIX . 'foodsaver fs,
					' . PREFIX . 'buddy b
		
			WHERE 	b.buddy_id = fs.id
			AND 	b.foodsaver_id = ' . (int)$this->func->fsId() . '
			AND 	b.confirmed = 1
		');
	}

	public function removeRequest($fsid)
	{
		$this->del('
			DELETE FROM `' . PREFIX . 'buddy` WHERE foodsaver_id = ' . (int)$fsid . ' AND buddy_id = ' . (int)$this->func->fsId() . '	
		');
	}

	public function buddyRequestedMe($fsid)
	{
		if ($this->qOne('SELECT 1 FROM ' . PREFIX . 'buddy WHERE foodsaver_id = ' . (int)$fsid . ' AND buddy_id = ' . (int)$this->func->fsId())) {
			return true;
		}

		return false;
	}
}
