<?php

namespace Foodsharing\Modules\Application;

use Foodsharing\Modules\Core\BaseGateway;

class ApplicationGateway extends BaseGateway
{
	private const STATUS_ACTIVE = 1;
	private const STATUS_TENTATIVE = 10;
	private const STATUS_DENIED = 20;

	public function getApplication($bid, $fid)
	{
		$stm = '
			SELECT 	fs.`id`,
					fs.`name`,
					fs.`nachname`,
					fs.`photo`,
					fb.application,
					fb.active

			FROM 	`fs_foodsaver_has_bezirk` fb,
					`fs_foodsaver` fs
				
			WHERE 	fb.foodsaver_id = fs.id
			AND 	fb.bezirk_id =  :region_id

			AND 	fb.foodsaver_id = :foodsaver_id
		';

		return $this->db->fetch($stm, [':region_id' => $bid, ':foodsaver_id' => $fid]);
	}

	public function acceptApplication($regionId, $foodsaverId): void
	{
		$this->updateActivityStatus($regionId, $foodsaverId, self::STATUS_ACTIVE);
	}

	public function deferApplication($regionId, $foodsaverId): void
	{
		$this->updateActivityStatus($regionId, $foodsaverId, self::STATUS_TENTATIVE);
	}

	public function denyApplication($regionId, $foodsaverId): void
	{
		$this->updateActivityStatus($regionId, $foodsaverId, self::STATUS_DENIED);
	}

	private function updateActivityStatus($regionId, $foodsaverId, $value): int
	{
		return $this->db->update(
			'fs_foodsaver_has_bezirk',
			['active' => $value],
			['bezirk_id' => (int)$regionId, 'foodsaver_id' => (int)$foodsaverId]
		);
	}

	public function getRegion($id = false)
	{
		$stm = '
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
				`stat_korpcount`
	
			FROM 	`fs_bezirk`
	
			WHERE 	`id` = :id
			LIMIT 1
		';

		$region = $this->db->fetch($stm, [':id' => $id]);

		$stm = '
			SELECT 	fs.`id`,
					fs.`photo`,
					fs.`name`,
					fs.`nachname`
	
			FROM 	`fs_foodsaver` fs,
					`fs_foodsaver_has_bezirk` c
	
			WHERE 	c.`foodsaver_id` = fs.id
			AND 	c.bezirk_id = :id
			AND 	c.active = 1
		';
		$region['foodsaver'] = $this->db->fetchAll($stm, [':id' => $id]);

		$region['fs_count'] = \count($region['foodsaver']);

		$stm = '
			SELECT 	fs.`id`,
					fs.`photo`,
					fs.`name`,
					fs.`nachname`
	
			FROM 	`fs_foodsaver` fs,
					`fs_botschafter` c
	
			WHERE 	c.`foodsaver_id` = fs.id
			AND 	c.bezirk_id = :id
		';
		$region['botschafter'] = $this->db->fetchAll($stm, [':id' => $id]);

		return $region;
	}
}
