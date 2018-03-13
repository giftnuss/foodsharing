<?php

namespace Foodsharing\Modules\NewArea;

use Foodsharing\Modules\Core\Model;

class NewAreaModel extends Model
{
	public function getWantNews()
	{
		$fs = $this->q('
				
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
		');

		foreach ($fs as $key => $f) {
			$fs[$key]['bezirke'] = $this->q('
				SELECT	b.`name`,
						b.`id`
				FROM 	`fs_foodsaver_has_bezirk` bh,
						`fs_bezirk` b
						
				WHERE 	bh.bezirk_id = b.id 
				AND bh.foodsaver_id = ' . $f['id'] . '
			');
		}

		return $fs;
	}

	public function clearWantNew($fsid)
	{
		$this->update('
			UPDATE 	`fs_foodsaver`
			SET 	`want_new` = 0,
					`new_bezirk` = ""
			WHERE 	`id` = ' . (int)$fsid . '		
		');
	}
}
