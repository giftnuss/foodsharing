<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Modules\Core\BaseGateway;

class RegionGateway extends BaseGateway
{

	public function listIdsForFoodsaverWithDescendants($fs_id)
	{
		$bezirk_ids = [];
		foreach ($this->listForFoodsaver($fs_id) as $bezirk) {
			$bezirk_ids += $this->listIdsForDescendantsAndSelf($bezirk['id']);
		}
		return $bezirk_ids;
	}

	public function listForFoodsaver($fs_id)
	{
		$values = $this->db->fetchAll(
			'							
			SELECT 	b.`id`,
					b.name,
					b.type
			
			FROM 	`fs_foodsaver_has_bezirk` hb,
					`fs_bezirk` b
			
			WHERE 	hb.bezirk_id = b.id
			AND 	`foodsaver_id` = :fs_id
			AND 	hb.active = 1
			
			ORDER BY b.name',
			[':fs_id' => $fs_id]
		);

		$output = [];
		foreach ($values as $v) {
			$output[$v['id']] = $v;
		}

		return $output;
	}

	public function listIdsForDescendantsAndSelf($bid)
	{
		if ((int)$bid == 0) {
			return [];
		}

		return $this->db->fetchAllValues(
			'SELECT bezirk_id FROM `fs_bezirk_closure` WHERE ancestor_id = :bid',
			[':bid' => $bid]
		);
	}
}
