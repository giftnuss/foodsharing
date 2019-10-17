<?php

namespace Foodsharing\Modules\FoodSharePoint;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Region\RegionGateway;

class FoodSharePointGateway extends BaseGateway
{
	private $regionGateway;
	private $bellGateway;

	public function __construct(
		Database $db,
		RegionGateway $regionGateway,
		BellGateway $bellGateway
	) {
		parent::__construct($db);
		$this->regionGateway = $regionGateway;
		$this->bellGateway = $bellGateway;
	}

	public function getEmailFollower($id)
	{
		return $this->db->fetchAll(
			'
			SELECT 	fs.`id`,
					fs.`name`,
					fs.`nachname`,
					fs.`email`,
					fs.`geschlecht`

			FROM 	`fs_fairteiler_follower` ff,
					`fs_foodsaver` fs

			WHERE 	ff.foodsaver_id = fs.id
			AND 	ff.fairteiler_id = :id
			AND 	ff.infotype = 1
		',
			[':id' => $id]
		);
	}

	public function getLastFoodSharePointPost($fspId)
	{
		return $this->db->fetch(
			'
			SELECT 		wp.id,
						wp.time,
						UNIX_TIMESTAMP(wp.time) AS time_ts,
						wp.body,
						wp.attach,
						fs.name AS fs_name,
						fs.id AS fs_id

			FROM 		fs_fairteiler_has_wallpost hw
			LEFT JOIN 	fs_wallpost wp
			ON 			hw.wallpost_id = wp.id

			LEFT JOIN 	fs_foodsaver fs ON wp.foodsaver_id = fs.id

			WHERE 		hw.fairteiler_id = :foodSharePointId

			ORDER BY 	wp.id DESC
			LIMIT 1
		',
			[':foodSharePointId' => $fspId]
		);
	}

	public function updateVerantwortliche($id, $bfoodsaver)
	{
		$values = array();

		foreach ($bfoodsaver as $fs) {
			$values[] = '(' . (int)$id . ',' . (int)$fs . ',2,1)';
		}

		$this->db->update('fs_fairteiler_follower', ['type' => 1], ['fairteiler_id' => $id]);

		$this->db->execute(
			'
				REPLACE INTO `fs_fairteiler_follower`
				(
					`fairteiler_id`,
					`foodsaver_id`,
					`type`,
					`infotype`
				)
				VALUES
				' . implode(',', $values) . '
		'
		);
	}

	public function getInfoFollowerIds($id)
	{
		return $this->db->fetchAllValues(
			'
			SELECT 	fs.`id`

			FROM 	`fs_fairteiler_follower` ff,
					`fs_foodsaver` fs

			WHERE 	ff.foodsaver_id = fs.id
			AND 	ff.fairteiler_id = :id
		',
			[':id' => $id]
		);
	}

	public function listFoodSharePoints($region_ids)
	{
		if (!$region_ids) {
			return [];
		}
		if ($foodSharePoint = $this->db->fetchAll(
			'
			SELECT 	`id`,
					`name`,
					`picture`
			FROM 	`fs_fairteiler`
			WHERE 	`bezirk_id` IN( ' . implode(',', $region_ids) . ' )
			AND 	`status` = 1
			ORDER BY `name`
		'
		)
		) {
			foreach ($foodSharePoint as $fspKey => $fspValue) {
				$foodSharePoints[$fspKey]['pic'] = false;
				if (!empty($fspValue['picture'])) {
					$foodSharePoints[$fspKey]['pic'] = $this->getPicturePaths($fspValue['picture']);
				}
			}

			return $foodSharePoint;
		}

		return [];
	}

	public function listFoodSharePointsNested($bezirk_ids = [])
	{
		if (!empty($bezirk_ids) && ($foodSharePoint = $this->db->fetchAll(
				'
			SELECT 	ft.`id`,
					ft.`name`,
					ft.`picture`,
					bz.id AS bezirk_id,
					bz.name AS bezirk_name

			FROM 	`fs_fairteiler` ft,
					`fs_bezirk` bz

			WHERE 	ft.bezirk_id = bz.id
			AND 	ft.`bezirk_id` IN(' . implode(',', $bezirk_ids) . ')
			AND 	ft.`status` = 1
			ORDER BY ft.`name`
		'
			))
		) {
			$out = array();

			foreach ($foodSharePoint as $fsp) {
				if (!isset($out[$fsp['bezirk_id']])) {
					$out[$fsp['bezirk_id']] = array(
						'id' => $fsp['bezirk_id'],
						'name' => $fsp['bezirk_name'],
						'fairteiler' => array(),
					);
				}
				$pic = false;
				if (!empty($fsp['picture'])) {
					$foodSharePoints[$fsp]['pic'] = $this->getPicturePaths($fsp['picture']);
				}
				$out[$fsp['bezirk_id']]['fairteiler'][] = array(
					'id' => $fsp['id'],
					'name' => $fsp['name'],
					'picture' => $fsp['picture'],
					'pic' => $pic,
				);
			}

			return $out;
		}

		return [];
	}

	public function listNearbyFoodSharePoints($loc, $distance = 30)
	{
		return $this->db->fetchAll(
			'
			SELECT
				ft.`id`,
				ft.`bezirk_id`,
				ft.`name`,
				ft.`picture`,
				ft.`status`,
				ft.`desc`,
				ft.`anschrift`,
				ft.`plz`,
				ft.`ort`,
				ft.`lat`,
				ft.`lon`,
				ft.`add_date`,
				UNIX_TIMESTAMP(ft.`add_date`) AS time_ts,
				ft.`add_foodsaver`,
				(6371 * acos( cos( radians( :lat ) ) * cos( radians( ft.lat ) ) * cos( radians( ft.lon ) - radians( :lon ) ) + sin( radians( :lat1 ) ) * sin( radians( ft.lat ) ) ))
				AS distance
			FROM
				`fs_fairteiler` ft

			WHERE
				ft.`status` = 1

			HAVING
				distance <= :distance

			ORDER BY
				distance ASC

			LIMIT 6
		',
			[
				':lat' => (float)$loc['lat'],
				':lat1' => (float)$loc['lat'],
				':lon' => (float)$loc['lon'],
				':distance' => $distance,
			]
		);
	}

	public function follow($food_share_point_id, $fs_id, $infotype)
	{
		$this->db->insertIgnore(
			'fs_fairteiler_follower',
			[
				'fairteiler_id' => $food_share_point_id,
				'foodsaver_id' => $fs_id,
				'type' => 1,
				'infotype' => $infotype,
			]
		);
	}

	public function unfollow($food_share_point_id, $fs_id)
	{
		return $this->db->delete('fs_fairteiler_follower', ['fairteiler_id' => $food_share_point_id, 'foodsaver_id' => $fs_id]);
	}

	public function getFollower($id)
	{
		$follower = $this->db->fetchAll(
			'
			SELECT 	fs.`name`,
					fs.`nachname`,
					fs.`id`,
					fs.`photo`,
					ff.type,
					fs.sleep_status

			FROM 	fs_foodsaver fs,
					fs_fairteiler_follower ff
			WHERE 	ff.foodsaver_id = fs.id
			AND 	ff.fairteiler_id = :id

		',
			[':id' => $id]
		);
		$normal = [];
		$verantwortliche = [];
		$all = [];
		foreach ($follower as $f) {
			if ($f['type'] == 1) {
				$normal[] = $f;
				$all[$f['id']] = 'follow';
			} elseif ($f['type'] == 2) {
				$verantwortliche[] = $f;
				$all[$f['id']] = 'verantwortlich';
			}
		}

		return [
			'follow' => $normal,
			'verantwortlich' => $verantwortliche,
			'all' => $all,
		];
	}

	public function acceptFoodSharePoint($id)
	{
		$this->db->update('fs_fairteiler', ['status' => 1], ['id' => $id]);
		$this->removeBellNotificationForNewFoodSharePoint($id);
	}

	public function updateFoodSharePoint($id, $data)
	{
		$this->db->requireExists('fs_fairteiler', ['id' => $id]);
		$this->db->update('fs_fairteiler', $data, ['id' => $id]);

		return true;
	}

	public function deleteFoodSharePoint($id)
	{
		$this->db->delete('fs_fairteiler_follower', ['fairteiler_id' => $id]);

		$result = $this->db->delete('fs_fairteiler', ['id' => $id]);

		$this->removeBellNotificationForNewFoodSharePoint($id);

		return $result;
	}

	public function getFoodSharePoint($id)
	{
		if ($foodSharePoint = $this->db->fetch(
			'
			SELECT 	ft.id,
					ft.`bezirk_id`,
					ft.`name`,
					ft.`picture`,
					ft.`status`,
					ft.`desc`,
					ft.`anschrift`,
					ft.`plz`,
					ft.`ort`,
					ft.`lat`,
					ft.`lon`,
					ft.`add_date`,
					UNIX_TIMESTAMP(ft.`add_date`) AS time_ts,
					ft.`add_foodsaver`,
					fs.name AS fs_name,
					fs.nachname AS fs_nachname,
					fs.id AS fs_id

			FROM 	fs_fairteiler ft
			LEFT JOIN
					fs_foodsaver fs


			ON 	ft.add_foodsaver = fs.id
			WHERE 	ft.id = :id
		',
			[':id' => $id]
		)
		) {
			$foodSharePoint['pic'] = false;
			if (!empty($foodSharePoint['picture'])) {
				$foodSharePoint['pic'] = $this->getPicturePaths($foodSharePoint['picture']);
			}

			return $foodSharePoint;
		}

		return false;
	}

	public function addFoodSharePoint($fs_id, $data)
	{
		$db_data = array_merge(
			$data,
			[
				'add_date' => date('Y-m-d H:i:s'),
				'add_foodsaver' => $fs_id,
			]
		);
		$food_share_point_id = $this->db->insert('fs_fairteiler', $db_data);
		if ($food_share_point_id) {
			$this->db->insert(
				'fs_fairteiler_follower',
				['fairteiler_id' => $food_share_point_id, 'foodsaver_id' => $fs_id, 'type' => 2]
			);

			$this->sendBellNotificationForNewFoodSharePoint($food_share_point_id);
		}

		return $food_share_point_id;
	}

	private function sendBellNotificationForNewFoodSharePoint(int $foodSharePointId): void
	{
		$foodSharePoint = $this->getFoodSharePoint($foodSharePointId);

		if ($foodSharePoint['status'] === 1) {
			return; //FoodSharePoint has been created by orga member or the ambassador himself
		}

		$region = $this->regionGateway->getRegion($foodSharePoint['bezirk_id']);

		$ambassadorIds = $this->db->fetchAllValuesByCriteria('fs_botschafter', 'foodsaver_id', ['bezirk_id' => $region['id']]);

		$bellData = Bell::create(
			'sharepoint_activate_title',
			'sharepoint_activate',
			'fas fa-recycle',
			['href' => '/?page=fairteiler&sub=check&id=' . $foodSharePointId],
			['bezirk' => $region['name'], 'name' => $foodSharePoint['name']],
			'new-fairteiler-' . $foodSharePointId,
			0
		);
		$this->bellGateway->addBell($ambassadorIds, $bellData);
	}

	private function removeBellNotificationForNewFoodSharePoint(int $foodSharePointId): void
	{
		$identifier = 'new-fairteiler-' . $foodSharePointId;
		if (!$this->bellGateway->bellWithIdentifierExists($identifier)) {
			return;
		}
		$this->bellGateway->delBellsByIdentifier($identifier);
	}

	private function getPicturePaths(string $picture): array
	{
		if (strpos($picture, '/api/uploads/') === 0) {
			return array(
				'thumb' => $picture . '?h=60&w=60',
				'head' => $picture . '?h=169&w=525',
				'orig' => $picture
			);
		}

		return array(
			'thumb' => 'images/' . str_replace('/', '/crop_1_60_', $picture),
			'head' => 'images/' . str_replace('/', '/crop_0_528_', $picture),
			'orig' => 'images/' . ($picture),
		);
	}
}
