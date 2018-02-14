<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Modules\Core\BaseGateway;

class RegionGateway extends BaseGateway
{
	public function getAllRegions($fs_id)
	{
		$bezirk_ids = [];
		foreach ($this->getRegions($fs_id) as $bezirk) {
			foreach ($this->getChildRegions($bezirk['id']) as $child_id) {
				$bezirk_ids[$child_id] = $child_id;
			}
		}
		return $bezirk_ids;
	}

	public function getRegions($fs_id)
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
			$output[$v['id']] = [
				'id' => $v['id'],
				'name' => $v['name'],
				'type' => $v['type']
			];
		}

		return $output;
	}

	public function getChildRegions($bid)
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
