<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Modules\Core\BaseGateway;

class FoodsaverGateway extends BaseGateway
{
	public function listActiveByRegion($id)
	{
		return $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`photo`,
					fs.`name`,
					fs.`nachname`,
					fs.sleep_status

			FROM 	`fs_foodsaver` fs,
					`fs_foodsaver_has_bezirk` c

			WHERE 	c.`foodsaver_id` = fs.id
			AND     fs.deleted_at IS NULL
			AND 	c.bezirk_id = :id
			AND 	c.active = 1
			AND 	fs.sleep_status = 0

			ORDER BY fs.`name`
		', ['id' => $id]);
	}

	public function listActiveWithFullNameByRegion($id)
	{
		return $this->db->fetchAll('

			SELECT 	fs.id,
					CONCAT(fs.`name`, " ", fs.`nachname`) AS `name`,
					fs.`name` AS vorname,
					fs.`anschrift`,
					fs.`email`,
					fs.`telefon`,
					fs.`handy`,
					fs.`plz`,
					fs.`geschlecht`

			FROM 	fs_foodsaver_has_bezirk fb,
					`fs_foodsaver` fs

			WHERE 	fb.foodsaver_id = fs.id
			AND 	fb.bezirk_id = :id
			AND 	fb.`active` = 1
			AND		fs.deleted_at IS NULL
		', ['id' => $id]);
	}

	public function listInactiveByRegion($id)
	{
		return $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`photo`,
					fs.`name`,
					fs.`nachname`,
					fs.sleep_status

			FROM 	`fs_foodsaver` fs,
					`fs_foodsaver_has_bezirk` c

			WHERE 	c.`foodsaver_id` = fs.id
			AND     fs.deleted_at IS NULL
			AND 	c.bezirk_id = :id
			AND 	c.active = 1
			AND 	fs.sleep_status > 0

			ORDER BY fs.`name`
		', ['id' => $id]);
	}

	public function listAmbassadorsByRegion($id)
	{
		return $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`photo`,
					fs.`name`,
					fs.`nachname`,
					fs.sleep_status

			FROM 	`fs_foodsaver` fs,
					`fs_botschafter` c

			WHERE 	c.`foodsaver_id` = fs.id
			AND     fs.deleted_at IS NULL
			AND 	c.bezirk_id = :id
		', ['id' => $id]);
	}
}
