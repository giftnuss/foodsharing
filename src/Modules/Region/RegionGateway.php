<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;

class RegionGateway extends BaseGateway
{
	private $foodsaverGateway;

	public function __construct(
		Database $db,
		FoodsaverGateway $foodsaverGateway
) {
		parent::__construct($db);
		$this->foodsaverGateway = $foodsaverGateway;
	}

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
			['fs_id' => $fs_id]
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
			['bid' => $bid]
		);
	}

	public function listForFoodsaverExceptWorkingGroups($fs_id)
	{
		return $this->db->fetchAll('
			SELECT
				b.`id`,
				b.`name`,
				b.`teaser`,
				b.`photo`

			FROM
				fs_bezirk b,
				fs_foodsaver_has_bezirk hb

			WHERE
				hb.bezirk_id = b.id

			AND
				hb.`foodsaver_id` = :fs_id

			AND
				b.`type` != 7

			ORDER BY
				b.`name`
		', ['fs_id' => $fs_id]);
	}

	public function getRegionDetails($id)
	{
		$bezirk = $this->db->fetch('
			SELECT
				`id`,
				`name`,
				`email`,
				`email_name`,
				`type`,
				`stat_fetchweight`,
				`stat_fetchcount`,
				`stat_fscount`,
				`stat_botcount`,
				`stat_postcount`,
				`stat_betriebcount`,
				`stat_korpcount`,
				`moderated`

			FROM 	`fs_bezirk`

			WHERE 	`id` = :id
			LIMIT 1
		', ['id' => $id]);

		$bezirk['foodsaver'] = $this->foodsaverGateway->listActiveByRegion($id);

		$bezirk['sleeper'] = $this->foodsaverGateway->listInactiveByRegion($id);

		$bezirk['fs_count'] = count($bezirk['foodsaver']);

		$bezirk['botschafter'] = $this->foodsaverGateway->listAmbassadorsByRegion($id);

		return $bezirk;
	}

	public function getType($id)
	{
		$bezirkType = $this->db->fetchValue('
			SELECT
				`type`
			FROM 	`fs_bezirk`

			WHERE 	`id` = :id
			LIMIT 1
		', ['id' => $id]);

		return $bezirkType;
	}

	public function listRequests($id)
	{
		return $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`name`,
					fs.`nachname`,
					fs.`photo`,
					fb.application,
					fb.active,
					UNIX_TIMESTAMP(fb.added) AS `time`

			FROM 	`fs_foodsaver_has_bezirk` fb,
					`fs_foodsaver` fs

			WHERE 	fb.foodsaver_id = fs.id
			AND 	fb.bezirk_id = :id
			AND 	fb.active = 0
		', ['id' => $id]);
	}
}