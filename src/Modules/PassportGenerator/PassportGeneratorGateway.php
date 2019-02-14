<?php

namespace Foodsharing\Modules\PassportGenerator;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Region\RegionGateway;

final class PassportGeneratorGateway extends BaseGateway
{
	private $regionGateway;

	public function __construct(Database $db, RegionGateway $regionGateway)
	{
		$this->regionGateway = $regionGateway;

		parent::__construct($db);
	}

	public function passGen(int $bot_id, int $fsid): int
	{
		return $this->db->insert('fs_pass_gen', [
			'foodsaver_id' => $fsid,
			'date' => $this->db->now(),
			'bot_id' => $bot_id,
		]);
	}

	public function updateLastGen(array $foodsaver): int
	{
		return $this->db->update('fs_foodsaver', ['last_pass' => $this->db->now()], ['id' => implode(',', $foodsaver)]);
	}

	public function getPassFoodsaver(int $regionId): array
	{
		$stm = '
				SELECT 	fs.`id`,
						CONCAT(fs.`name`," ",fs.`nachname`) AS `name`,
						fs.verified,
						fs.last_pass,
						fs.photo,
						UNIX_TIMESTAMP(fs.last_pass) AS last_pass_ts,
						b.name AS bezirk_name,
						b.id AS bezirk_id
				
				FROM 	fs_foodsaver_has_bezirk fb,
						fs_foodsaver fs,
						fs_bezirk b
				
				WHERE 	fb.foodsaver_id = fs.id
				AND 	fb.bezirk_id = b.id
				AND 	fb.`bezirk_id` IN(' . implode(',', $this->regionGateway->listIdsForDescendantsAndSelf($regionId)) . ')
				AND		fs.deleted_at IS NULL
				
				ORDER BY bezirk_name
		';
		$req = $this->db->fetchAll($stm);

		$out = array();
		foreach ($req as $r) {
			if (!isset($out[$r['bezirk_id']])) {
				$out[$r['bezirk_id']] = array(
					'id' => $r['bezirk_id'],
					'bezirk' => $r['bezirk_name'],
					'foodsaver' => array()
				);
			}
			$out[$r['bezirk_id']]['foodsaver'][] = $r;
		}

		return $out;
	}

	public function fetchFoodsaverData(int $fs_id): array
	{
		$stm = 'SELECT `photo`,`id`,`name`,`nachname`,`geschlecht`,`rolle` FROM fs_foodsaver WHERE `id` = :fsId';

		return $this->db->fetch($stm, [':fsId' => $fs_id]);
	}
}
