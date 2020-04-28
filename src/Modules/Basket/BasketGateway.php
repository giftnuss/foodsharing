<?php

namespace Foodsharing\Modules\Basket;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\DBConstants\Basket\Status as BasketStatus;
use Foodsharing\Modules\Core\DBConstants\BasketRequests\Status as RequestStatus;

class BasketGateway extends BaseGateway
{
	public function getBasketCoordinates(): array
	{
		$stm = '
			SELECT id,lat,lon 
			FROM fs_basket 
			WHERE status = :status
			';

		return $this->db->fetchAll($stm, [':status' => BasketStatus::REQUESTED_MESSAGE_READ]);
	}

	public function addBasket(
		$desc,
		$pic,
		$tel,
		$contact_type,
		$weight,
		$location_type,
		$lat,
		$lon,
		$lifeTimeInSeconds,
		$region_id,
		$fsId
	): int {
		$appost = 1;

		if (isset($_REQUEST['appost']) && '0' === $_REQUEST['appost']) {
			$appost = 0;
		}

		return $this->db->insert(
			'fs_basket',
			[
				'foodsaver_id' => $fsId,
				'status' => BasketStatus::REQUESTED_MESSAGE_READ,
				'time' => date('Y-m-d H:i:s'),
				'description' => strip_tags($desc),
				'picture' => strip_tags($pic),
				'tel' => strip_tags($tel['tel']),
				'handy' => strip_tags($tel['handy']),
				'contact_type' => strip_tags($contact_type),
				'location_type' => (int)$location_type,
				'weight' => (float)$weight,
				'lat' => (float)$lat,
				'lon' => (float)$lon,
				'bezirk_id' => (int)$region_id,
				'appost' => $appost,
				'until' => date('Y-m-d', time() + $lifeTimeInSeconds),
			]
		);
	}

	/**
	 * Fetches a basket from the database. Returns details of the basket with
	 * the given id or false if the basket does not yet exist. If the status is
	 * set only a basket that matches this will be returned.
	 *
	 * @param int $id the basket's id
	 * @param int|bool $status a basket status or false
	 *
	 * @return array the details of the basket or an empty array
	 */
	public function getBasket($id, $status = false)
	{
		$status_sql = '';

		if ($status !== false) {
			$status_sql = 'AND `status` = ' . (int)$status;
		}

		$stm = '
			SELECT
				b.id,
				b.status,
				b.description,
				b.picture,
				b.contact_type,
				b.tel,
				b.handy,
				b.fs_id AS fsf_id,
				b.lat,
				b.lon,
				b.foodsaver_id,
				UNIX_TIMESTAMP(b.time) AS time_ts,
				UNIX_TIMESTAMP(b.update) AS update_ts,
				UNIX_TIMESTAMP(b.until) AS until_ts,
				fs.id AS fs_id,
				fs.name AS fs_name,
				fs.photo AS fs_photo,
				fs.sleep_status,
				COUNT(a.foodsaver_id) AS request_count				
			FROM
				fs_basket b
			INNER JOIN
				fs_foodsaver fs
			ON
				b.foodsaver_id = fs.id			
			AND
				b.id = :id
			LEFT OUTER JOIN
				fs_basket_anfrage a
			ON
				a.`status` IN(:status_unread,:status_read)
			AND
				a.basket_id = b.id
			' . $status_sql . '				
		';
		$basket = $this->db->fetch($stm, [
			':id' => $id,
			':status_unread' => RequestStatus::REQUESTED_MESSAGE_UNREAD,
			':status_read' => RequestStatus::REQUESTED_MESSAGE_READ
		]);

		//check if the first fetch succeeded
		if (empty($basket) || !isset($basket['foodsaver_id']) || !isset($basket['fsf_id'])) {
			return [];
		}

		$stm = '
				SELECT 
					fs.name AS fs_name,
					fs.photo AS fs_photo,
					fs.id AS fs_id
					
				FROM
					fs_foodsaver fs
					
				WHERE
					fs.id = :foodsaver_id
			';
		if ('0' === $basket['fsf_id'] && $fs = $this->db->fetch(
				$stm,
				[':foodsaver_id' => $basket['foodsaver_id']]
			)) {
			$basket = array_merge($basket, $fs);
		}

		return $basket;
	}

	public function listRequests(int $basket_id, $foodsaver_id): array
	{
		$stm = '		
				SELECT
					UNIX_TIMESTAMP(a.time) AS time_ts,
					fs.name AS fs_name,
					fs.photo AS fs_photo,
					fs.id AS fs_id,
					fs.geschlecht AS fs_gender,
					fs.sleep_status,
					b.id		
		
				FROM
					fs_basket_anfrage a,
					fs_basket b,
					fs_foodsaver fs
		
				WHERE
					a.basket_id = b.id
		
				AND
					a.`status` IN(:status_unread,:status_read)
		
				AND
					a.foodsaver_id = fs.id
		
				AND
					b.foodsaver_id = :foodsaver_id
		
				AND
					a.basket_id = :basket_id		
				';

		return $this->db->fetchAll(
			$stm,
			[
				':status_unread' => RequestStatus::REQUESTED_MESSAGE_UNREAD,
				':status_read' => RequestStatus::REQUESTED_MESSAGE_READ,
				':foodsaver_id' => $foodsaver_id,
				':basket_id' => $basket_id,
			]
		);
	}

	public function getRequest(int $basket_id, int $foodsaver_id_requester, $foodsaver_id_offerer): array
	{
		$stm = '		
				SELECT
					UNIX_TIMESTAMP(a.time) AS time_ts,
					fs.name AS fs_name,
					fs.photo AS fs_photo,
					fs.id AS fs_id,
					fs.geschlecht AS fs_gender,
					b.id		
				FROM
					fs_basket_anfrage a,
					fs_basket b,
					fs_foodsaver fs		
				WHERE
					a.basket_id = b.id		
				AND
					a.`status` IN(:status_unread,:status_read)		
				AND
					a.foodsaver_id = fs.id		
				AND
					b.foodsaver_id = :foodsaver_id_offerer				
				AND
					a.foodsaver_id = :foodsaver_id_requester				
				AND
					a.basket_id = :basket_id		
				';

		return $this->db->fetch(
			$stm,
			[
				':status_unread' => RequestStatus::REQUESTED_MESSAGE_UNREAD,
				':status_read' => RequestStatus::REQUESTED_MESSAGE_READ,
				':foodsaver_id_offerer' => $foodsaver_id_offerer,
				':foodsaver_id_requester' => $foodsaver_id_requester,
				':basket_id' => $basket_id,
			]
		);
	}

	public function getRequestStatus(int $basket_id, int $foodsaver_id_requester, int $foodsaver_id_offerer): array
	{
		$stm = '		
				SELECT
					a.`status`			
				FROM
					fs_basket_anfrage a,
					fs_basket b		
				WHERE
					a.basket_id = b.id		
				AND
					b.foodsaver_id = :foodsaver_id_offerer
				AND
					a.foodsaver_id = :foodsaver_id_requester
				AND
					a.basket_id = :basket_id		
				';

		return $this->db->fetch(
			$stm,
			[
				':foodsaver_id_offerer' => $foodsaver_id_offerer,
				':foodsaver_id_requester' => $foodsaver_id_requester,
				':basket_id' => $basket_id,
			]
		);
	}

	public function listUpdates(int $foodsaverId): array
	{
		$stm = '
			SELECT 
				UNIX_TIMESTAMP(a.time) AS time_ts,
				fs.name AS fs_name,
				fs.photo AS fs_photo,
				fs.id AS fs_id,
				fs.sleep_status,
				b.id,
				b.description				
				
			FROM 
				fs_basket_anfrage a, 
				fs_basket b,
				fs_foodsaver fs
				
			WHERE 
				a.basket_id = b.id 
				
			AND 
				a.`status` IN(:status_unread,:status_read)
				
			AND
				a.foodsaver_id = fs.id
				
			AND
				b.foodsaver_id = :foodsaver_id
				
			ORDER BY
				a.`time` DESC				
		';

		return $this->db->fetchAll(
			$stm,
			[
				':status_unread' => RequestStatus::REQUESTED_MESSAGE_UNREAD,
				':status_read' => RequestStatus::REQUESTED_MESSAGE_READ,
				':foodsaver_id' => $foodsaverId,
			]
		);
	}

	public function getUpdateCount(int $foodsaverId): int
	{
		$stm = '
				SELECT COUNT(a.basket_id)
				FROM fs_basket_anfrage a, fs_basket b
				WHERE a.basket_id = b.id
				AND a.`status` = :status
				AND b.foodsaver_id = :foodsaver_id
			';

		return (int)$this->db->fetchValue(
			$stm,
			[':status' => RequestStatus::REQUESTED_MESSAGE_UNREAD, ':foodsaver_id' => $foodsaverId]
		);
	}

	public function addTypes(int $basket_id, array $types): void
	{
		if (!empty($types)) {
			foreach ($types as $type) {
				$this->db->insert('fs_basket_has_types', ['basket_id' => $basket_id, 'types_id' => $type]);
			}
		}
	}

	public function addKind(int $basket_id, array $kinds): void
	{
		if (!empty($kinds)) {
			foreach ($kinds as $kind) {
				$this->db->insert('fs_basket_has_art', ['basket_id' => $basket_id, 'art_id' => $kind]);
			}
		}
	}

	public function removeBasket(int $basketId, int $foodsaverId): int
	{
		return $this->db->update(
			'fs_basket',
			[
				'status' => BasketStatus::DELETED_OTHER_REASON,
				'update' => date('Y-m-d H:i:s')
			],
			['id' => $basketId, 'foodsaver_id' => $foodsaverId]
		);
	}

	public function editBasket(
		int $id,
		string $desc,
		?string $pic,
		float $lat,
		float $lon,
		int $fsId
	): int {
		return $this->db->update(
			'fs_basket',
			[
				'update' => date('Y-m-d H:i:s'),
				'description' => strip_tags($desc),
				'picture' => strip_tags($pic),
				'lat' => $lat,
				'lon' => $lon
			],
			['id' => $id, 'foodsaver_id' => $fsId]
		);
	}

	public function listMyBaskets(int $foodsaverId): array
	{
		$stm = '
			SELECT 
				`id`,
				`description`,
				`picture`,
				UNIX_TIMESTAMP(`time`) AS time_ts
				
			FROM 
				fs_basket
				
			WHERE
				`foodsaver_id` = :foodsaver_id
				
			AND 
				`status` = :status
				';
		if ($baskets = $this->db->fetchAll(
			$stm,
			[':foodsaver_id' => $foodsaverId, ':status' => BasketStatus::REQUESTED_MESSAGE_READ]
		)
		) {
			foreach ($baskets as $key => $b) {
				$stm = 'SELECT COUNT(foodsaver_id) FROM fs_basket_anfrage WHERE basket_id = :basket_id AND status < :status';
				$baskets[$key]['req_count'] = $this->db->fetchValue(
					$stm,
					[':basket_id' => $b['id'], ':status' => RequestStatus::REQUESTED]
				);
			}

			return $baskets;
		}

		return [];
	}

	public function setStatus(int $basket_id, int $status, int $foodsaverId): void
	{
		$appost = 1;
		if (isset($_REQUEST['appost']) && '0' === $_REQUEST['appost']) {
			$appost = 0;
		}

		$this->db->insertOrUpdate('fs_basket_anfrage', [
			'foodsaver_id' => $foodsaverId,
			'basket_id' => $basket_id,
			'status' => $status,
			'time' => $this->db->now(),
			'appost' => $appost
		]);
	}

	public function getAmountOfFoodBaskets(int $fs_id): int
	{
		return $this->db->count('fs_basket', ['foodsaver_id' => $fs_id]);
	}

	public function listNearbyBasketsByDistance($foodsaver_id, $gps_coordinate, int $distance_km = 30): array
	{
		return $this->db->fetchAll(
			'
			SELECT
				b.id,
			    b.time,
			    UNIX_TIMESTAMP(b.`time`) AS time_ts,
				b.picture,
				b.description,
				b.lat,
				b.lon,
				(6371 * acos(
					cos(radians(:lat)) *
					cos(radians(b.lat)) *
					cos(radians(b.lon) - radians(:lon)) +
					sin(radians(:lat1)) *
					sin(radians(b.lat)))) AS distance,
				b.until,
				fs.name AS fs_name
			FROM
				fs_basket b,
				fs_foodsaver fs	
			WHERE
				b.foodsaver_id = fs.id	
			AND
				b.status = :status
			AND
				foodsaver_id != :fs_id
			AND 
			    b.until > NOW()
			HAVING
				distance <= :distance
			ORDER BY
				distance
			LIMIT 6
		',
			[
				':lat' => (float)$gps_coordinate['lat'],
				':lat1' => (float)$gps_coordinate['lat'],
				':lon' => (float)$gps_coordinate['lon'],
				':status' => BasketStatus::REQUESTED_MESSAGE_READ,
				':fs_id' => $foodsaver_id,
				':distance' => $distance_km,
			]
		);
	}

	public function listNewestBaskets(): array
	{
		return $this->db->fetchAll('	
			SELECT
				b.id,
				b.`time`,
				UNIX_TIMESTAMP(b.`time`) AS time_ts,
				b.description,
				b.picture,
				b.contact_type,
				b.tel,
				b.handy,
				b.fs_id AS fsf_id,
			    b.until,
				fs.id AS fs_id,
				fs.name AS fs_name,
				fs.photo AS fs_photo	
			FROM
				fs_basket b,
				fs_foodsaver fs	
			WHERE
				b.foodsaver_id = fs.id
			AND
				b.status = :status
			AND 
			    b.until > NOW()
			ORDER BY
				id DESC	
			LIMIT
				0, 10	
		', [':status' => BasketStatus::REQUESTED_MESSAGE_READ]);
	}
}
