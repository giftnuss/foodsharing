<?php

namespace Foodsharing\Modules\FairTeiler;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Region\RegionGateway;

class FairTeilerGateway extends BaseGateway
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
			AND 	ff.infotype = 1
		',
			[':id' => $id]
		);
	}

	public function getLastFairSharePointPost(int $fspId): array
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

	public function updateResponsibles(int $id, $bfoodsaver): void
	{
		$values = array();

		foreach ($bfoodsaver as $fs) {
			$values[] = '(' . $id . ',' . (int)$fs . ',2,1)';
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

	public function getInfoFollowerIds(int $id): array
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

	public function listFairteiler(array $regionIds): array
	{
		if (!$regionIds) {
			return [];
		}
		if ($fairteiler = $this->db->fetchAll(
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
			foreach ($fairteiler as $key => $ft) {
				$fairteiler[$key]['pic'] = false;
				if (!empty($ft['picture'])) {
					$fairteiler[$key]['pic'] = array(
						'thumb' => 'images/' . str_replace('/', '/crop_1_60_', $ft['picture']),
						'head' => 'images/' . str_replace('/', '/crop_0_528_', $ft['picture']),
						'orig' => 'images/' . ($ft['picture']),
					);
				}
			}

			return $fairteiler;
		}

		return [];
	}

	public function listFairteilerNested(array $regionIds = []): array
	{
		if (!empty($regionIds) && ($fairteiler = $this->db->fetchAll(
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
			$out = array();

			foreach ($fairteiler as $ft) {
				if (!isset($out[$ft['bezirk_id']])) {
					$out[$ft['bezirk_id']] = [
						'id' => $ft['bezirk_id'],
						'name' => $ft['bezirk_name'],
						'fairteiler' => [],
					];
				}
				$pic = false;
				if (!empty($ft['picture'])) {
					$pic = [
						'thumb' => 'images/' . str_replace('/', '/crop_1_60_', $ft['picture']),
						'head' => 'images/' . str_replace('/', '/crop_0_528_', $ft['picture']),
						'orig' => 'images/' . ($ft['picture']),
					];
				}
				$out[$ft['bezirk_id']]['fairteiler'][] = [
					'id' => $ft['id'],
					'name' => $ft['name'],
					'picture' => $ft['picture'],
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

	public function follow(int $foodSharePointId, int $foodsaverId, $infoType): void
	{
		$this->db->insertIgnore(
			'fs_fairteiler_follower',
			[
				'fairteiler_id' => $foodSharePointId,
				'foodsaver_id' => $foodsaverId,
				'type' => 1,
				'infotype' => $infoType,
			]
		);
	}

	public function unfollow(int $foodSharePointId, int $foodsaverId): int
	{
		return $this->db->delete('fs_fairteiler_follower', ['fairteiler_id' => $foodSharePointId, 'foodsaver_id' => $foodsaverId]);
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
			AND 	ff.fairteiler_id = :id

		',
			[':id' => $foodSharePointId]
		);
		$normal = [];
		$responsibles = [];
		$all = [];
		foreach ($follower as $f) {
			if ($f['type'] == 1) {
				$normal[] = $f;
				$all[$f['id']] = 'follow';
			} elseif ($f['type'] == 2) {
				$responsibles[] = $f;
				$all[$f['id']] = 'verantwortlich';
			}
		}

		return [
			'follow' => $normal,
			'verantwortlich' => $responsibles,
			'all' => $all,
		];
	}

	public function acceptFairteiler(int $foodSharePointId): void
	{
		$this->db->update('fs_fairteiler', ['status' => 1], ['id' => $foodSharePointId]);
		$this->removeBellNotificationForNewFairteiler($foodSharePointId);
	}

	public function updateFairteiler(int $foodSharePointId, array $data): bool
	{
		$this->db->requireExists('fs_fairteiler', ['id' => $foodSharePointId]);
		$this->db->update('fs_fairteiler', $data, ['id' => $foodSharePointId]);

		return true;
	}

	public function deleteFairteiler(int $foodSharePointId): int
	{
		$this->db->delete('fs_fairteiler_follower', ['fairteiler_id' => $foodSharePointId]);

		$result = $this->db->delete('fs_fairteiler', ['id' => $foodSharePointId]);

		$this->removeBellNotificationForNewFairteiler($foodSharePointId);

		return $result;
	}

	public function getFairteiler(int $foodSharePointId): array
	{
		if ($ft = $this->db->fetch(
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
			[':id' => $foodSharePointId]
		)
		) {
			$ft['pic'] = false;
			if (!empty($ft['picture'])) {
				$ft['pic'] = [
					'thumb' => 'images/' . str_replace('/', '/crop_1_60_', $ft['picture']),
					'head' => 'images/' . str_replace('/', '/crop_0_528_', $ft['picture']),
					'orig' => 'images/' . ($ft['picture']),
				];
			}

			return $ft;
		}

		return [];
	}

	public function addFairteiler(int $foodsaverId, array $data): int
	{
		$db_data = array_merge(
			$data,
			[
				'add_date' => date('Y-m-d H:i:s'),
				'add_foodsaver' => $foodsaverId,
			]
		);
		$ft_id = $this->db->insert('fs_fairteiler', $db_data);
		if ($ft_id) {
			$this->db->insert(
				'fs_fairteiler_follower',
				['fairteiler_id' => $ft_id, 'foodsaver_id' => $foodsaverId, 'type' => 2]
			);

			$this->sendBellNotificationForNewFairteiler($ft_id);
		}

		return $ft_id;
	}

	private function sendBellNotificationForNewFairteiler(int $foodSharePointId): void
	{
		$fairteiler = $this->getFairteiler($foodSharePointId);

		if ($fairteiler['status'] === 1) {
			return; //Fairteiler has been created by orga member or the ambassador himself
		}

		$region = $this->regionGateway->getRegion($fairteiler['bezirk_id']);

		$ambassadorIds = $this->db->fetchAllValuesByCriteria('fs_botschafter', 'foodsaver_id', ['bezirk_id' => $region['id']]);

		$this->bellGateway->addBell(
			$ambassadorIds,
			'sharepoint_activate_title',
			'sharepoint_activate',
			'img img-recycle yellow',
			['href' => '/?page=fairteiler&sub=check&id=' . $foodSharePointId],
			['bezirk' => $region['name'], 'name' => $fairteiler['name']],
			'new-fairteiler-' . $foodSharePointId,
			0
		);
	}

	private function removeBellNotificationForNewFairteiler(int $fairteilerId): void
	{
		$identifier = 'new-fairteiler-' . $fairteilerId;
		if (!$this->bellGateway->bellWithIdentifierExists($identifier)) {
			return;
		}
		$this->bellGateway->delBellsByIdentifier($identifier);
	}
}
