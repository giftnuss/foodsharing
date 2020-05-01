<?php

namespace Foodsharing\Modules\FoodSharePoint;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\FoodSharePoint\FollowerType;
use Foodsharing\Modules\Core\DBConstants\Info\InfoType;
use Foodsharing\Modules\Region\RegionGateway;

class FoodSharePointGateway extends BaseGateway
{
	private $regionGateway;
	private $bellGateway;

	public function __construct(Database $db, RegionGateway $regionGateway, BellGateway $bellGateway)
	{
		parent::__construct($db);
		$this->regionGateway = $regionGateway;
		$this->bellGateway = $bellGateway;
	}

	public function getEmailFollower(int $id): array
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
			AND 	ff.infotype = :infoType
		',
			[':id' => $id, ':infoType' => InfoType::EMAIL]
		);
	}

	public function getLastFoodSharePointPost(int $foodSharePointId): array
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
			[':foodSharePointId' => $foodSharePointId]
		);
	}

	public function updateFSPManagers(int $foodSharePointId, $fspManager): void
	{
		$values = [];

		foreach ($fspManager as $fs) {
			$values[] = [
				'fairteiler_id' => $foodSharePointId,
				'foodsaver_id' => (int)$fs,
				'type' => FollowerType::FOOD_SHARE_POINT_MANAGER,
				'infotype' => InfoType::EMAIL
			];
		}

		$this->db->update(
			'fs_fairteiler_follower',
			['type' => FollowerType::FOLLOWER],
			['fairteiler_id' => $foodSharePointId]
		);

		$this->db->insertOrUpdateMultiple('fs_fairteiler_follower', $values);
	}

	public function getInfoFollowerIds(int $foodSharePointId): array
	{
		return $this->db->fetchAllValues(
			'
			SELECT 	fs.`id`

			FROM 	`fs_fairteiler_follower` ff,
					`fs_foodsaver` fs

			WHERE 	ff.foodsaver_id = fs.id
			AND 	ff.fairteiler_id = :foodSharePointId
		',
			[':foodSharePointId' => $foodSharePointId]
		);
	}

	public function listActiveFoodSharePoints(array $regionIds): array
	{
		if (!$regionIds) {
			return [];
		}
		if ($foodSharePoints = $this->db->fetchAll(
			'
			SELECT 	`id`,
					`name`,
					`picture`
			FROM 	`fs_fairteiler`
			WHERE 	`bezirk_id` IN( ' . implode(',', $regionIds) . ' )
			AND 	`status` = 1
			ORDER BY `name`
		'
		)
		) {
			foreach ($foodSharePoints as $fspKey => $fspValue) {
				$foodSharePoints[$fspKey]['pic'] = false;
				if (!empty($fspValue['picture'])) {
					$foodSharePoints[$fspKey]['pic'] = [
						'thumb' => 'images/' . str_replace('/', '/crop_1_60_', $fspValue['picture']),
						'head' => 'images/' . str_replace('/', '/crop_0_528_', $fspValue['picture']),
						'orig' => 'images/' . ($fspValue['picture']),
					];
				}
			}

			return $foodSharePoints;
		}

		return [];
	}

	public function listFoodsaversFoodSharePoints(int $fsId): array
	{
		return $this->db->fetchAll('
			SELECT
				ft.id,
				ft.name,
				ff.infotype,
				ff.`type`

			FROM
				`fs_fairteiler_follower` ff
				LEFT JOIN `fs_fairteiler` ft
				ON ff.fairteiler_id = ft.id

			WHERE
				ff.foodsaver_id = :fsId
		', [':fsId' => $fsId]);
	}

	public function listFoodSharePointsNested(array $regionIds = []): array
	{
		if (!empty($regionIds) && ($foodSharePoint = $this->db->fetchAll(
				'
			SELECT 	ft.`id`,
					ft.`name`,
					ft.`picture`,
					bz.id AS bezirk_id,
					bz.name AS bezirk_name

			FROM 	`fs_fairteiler` ft,
					`fs_bezirk` bz

			WHERE 	ft.bezirk_id = bz.id
			AND 	ft.`bezirk_id` IN(' . implode(',', $regionIds) . ')
			AND 	ft.`status` = 1
			ORDER BY ft.`name`
		'
			))
		) {
			$out = [];

			foreach ($foodSharePoint as $fsp) {
				if (!isset($out[$fsp['bezirk_id']])) {
					$out[$fsp['bezirk_id']] = [
						'id' => $fsp['bezirk_id'],
						'name' => $fsp['bezirk_name'],
						'fairteiler' => [],
					];
				}
				$pic = false;
				if (!empty($fsp['picture'])) {
					$pic = [
						'thumb' => 'images/' . str_replace('/', '/crop_1_60_', $fsp['picture']),
						'head' => 'images/' . str_replace('/', '/crop_0_528_', $fsp['picture']),
						'orig' => 'images/' . ($fsp['picture']),
					];
				}
				$out[$fsp['bezirk_id']]['fairteiler'][] = [
					'id' => $fsp['id'],
					'name' => $fsp['name'],
					'picture' => $fsp['picture'],
					'pic' => $pic,
				];
			}

			return $out;
		}

		return [];
	}

	public function listNearbyFoodSharePoints(array $location, int $distance = 30): array
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
				distance
			LIMIT 6
		',
			[
				':lat' => (float)$location['lat'],
				':lat1' => (float)$location['lat'],
				':lon' => (float)$location['lon'],
				':distance' => $distance,
			]
		);
	}

	public function follow(int $foodsaverId, int $foodSharePointId, int $infoType): void
	{
		$this->db->insertIgnore(
			'fs_fairteiler_follower',
			[
				'fairteiler_id' => $foodSharePointId,
				'foodsaver_id' => $foodsaverId,
				'type' => FollowerType::FOLLOWER,
				'infotype' => $infoType,
			]
		);
	}

	public function unfollow(int $fsId, int $foodSharePointId): int
	{
		return $this->db->delete(
			'fs_fairteiler_follower',
			[
				'fairteiler_id' => $foodSharePointId,
				'foodsaver_id' => $fsId
			]
		);
	}

	public function unfollowFoodSharePoints(int $fsId, array $foodSharePointIds): int
	{
		return $this->db->delete(
			'fs_fairteiler_follower',
			[
				'foodsaver_id' => $fsId,
				'fairteiler_id' => $foodSharePointIds
			]
		);
	}

	public function updateInfoType(int $fsId, int $foodSharePointId, int $infoType): int
	{
		return $this->db->update(
			'fs_fairteiler_follower',
			['infotype' => $infoType],
			[
				'foodsaver_id' => $fsId,
				'fairteiler_id' => $foodSharePointId
			]
		);
	}

	public function getFollower(int $foodSharePointId): array
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
			AND 	ff.fairteiler_id = :foodSharePointId

		',
			[':foodSharePointId' => $foodSharePointId]
		);
		$normal = [];
		$fspManagers = [];
		$all = [];
		foreach ($follower as $f) {
			if ($f['type'] === FollowerType::FOLLOWER) {
				$normal[] = $f;
				$all[$f['id']] = 'follow';
			} elseif ($f['type'] === FollowerType::FOOD_SHARE_POINT_MANAGER) {
				$fspManagers[] = $f;
				$all[$f['id']] = 'fsp_manager';
			}
		}

		return [
			'follow' => $normal,
			'fsp_manager' => $fspManagers,
			'all' => $all,
		];
	}

	public function acceptFoodSharePoint(int $foodSharePointId): void
	{
		$this->db->update('fs_fairteiler', ['status' => 1], ['id' => $foodSharePointId]);
		$this->removeBellNotificationForNewFoodSharePoint($foodSharePointId);
	}

	public function updateFoodSharePoint(int $foodSharePointId, array $data): bool
	{
		$this->db->requireExists('fs_fairteiler', ['id' => $foodSharePointId]);
		$this->db->update('fs_fairteiler', $data, ['id' => $foodSharePointId]);

		return true;
	}

	public function deleteFoodSharePoint(int $foodSharePointId): int
	{
		$this->db->delete('fs_fairteiler_follower', ['fairteiler_id' => $foodSharePointId]);

		$result = $this->db->delete('fs_fairteiler', ['id' => $foodSharePointId]);

		$this->removeBellNotificationForNewFoodSharePoint($foodSharePointId);

		return $result;
	}

	public function getFoodSharePoint(int $foodSharePointId): array
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
			WHERE 	ft.id = :foodSharePointId
		',
			[':foodSharePointId' => $foodSharePointId]
		)
		) {
			$foodSharePoint['pic'] = false;
			if (!empty($foodSharePoint['picture'])) {
				$foodSharePoint['pic'] = [
					'thumb' => 'images/' . str_replace('/', '/crop_1_60_', $foodSharePoint['picture']),
					'head' => 'images/' . str_replace('/', '/crop_0_528_', $foodSharePoint['picture']),
					'orig' => 'images/' . ($foodSharePoint['picture']),
				];
			}

			return $foodSharePoint;
		}

		return [];
	}

	public function addFoodSharePoint(int $foodsaverId, array $data): int
	{
		$db_data = array_merge(
			$data,
			[
				'add_date' => date('Y-m-d H:i:s'),
				'add_foodsaver' => $foodsaverId,
			]
		);
		$food_share_point_id = $this->db->insert('fs_fairteiler', $db_data);
		if ($food_share_point_id) {
			$this->db->insert(
				'fs_fairteiler_follower',
				['fairteiler_id' => $food_share_point_id, 'foodsaver_id' => $foodsaverId, 'type' => FollowerType::FOOD_SHARE_POINT_MANAGER]
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
			'img img-recycle yellow',
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
}
