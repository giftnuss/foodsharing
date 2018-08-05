<?php

namespace Foodsharing\Modules\NewArea;

use Foodsharing\Modules\Core\BaseGateway;

class NewAreaGateway extends BaseGateway
{
	public function listWantNews(): array
	{
		$stm = '				
			SELECT 	`id`,
					`name`,
					`nachname`,
					`photo`,
					`anschrift`,
					`plz`,
					`stadt`,
					`new_bezirk`,
					`want_new`
			FROM 	`fs_foodsaver`
			WHERE 	`want_new` = 1		
		';
		$fs = $this->db->fetchAll($stm);

		foreach ($fs as $key => $f) {
			$stm = '
				SELECT	b.`name`,
						b.`id`
				FROM 	`fs_foodsaver_has_bezirk` bh,
						`fs_bezirk` b
						
				WHERE 	bh.bezirk_id = b.id 
				AND bh.foodsaver_id = :fs_id
			';
			$fs[$key]['bezirke'] = $this->db->fetchAll($stm, [':fs_id' => $f['id']]);
		}

		return $fs;
	}

	public function clearWantNew($fsId): void
	{
		$this->db->update('fs_foodsaver', ['want_new' => 0, 'new_bezirk' => ''], ['id' => (int)$fsId]);
	}
}
