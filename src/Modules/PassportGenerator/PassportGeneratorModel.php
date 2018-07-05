<?php

namespace Foodsharing\Modules\PassportGenerator;

use Foodsharing\Modules\Core\Model;
use Foodsharing\Modules\Region\RegionGateway;

class PassportGeneratorModel extends Model
{
	private $regionGateway;

	public function __construct(RegionGateway $regionGateway)
	{
		$this->regionGateway = $regionGateway;

		parent::__construct();
	}

	public function updateLastGen($foodsaver)
	{
		return $this->update('
			UPDATE 	`fs_foodsaver`
			SET 	`last_pass` = NOW()
			WHERE 	`id` IN(' . implode(',', $foodsaver) . ')		
		');
	}

	public function getPassFoodsaver($bezirk_id)
	{
		$req = $this->q('
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
				AND 	fb.`bezirk_id` IN(' . implode(',', $this->regionGateway->listIdsForDescendantsAndSelf($bezirk_id)) . ')
				AND		fs.deleted_at IS NULL
				
				ORDER BY bezirk_name
		');

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
}
