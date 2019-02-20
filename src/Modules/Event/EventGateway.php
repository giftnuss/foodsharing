<?php

namespace Foodsharing\Modules\Event;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Region\RegionGateway;

class EventGateway extends BaseGateway
{
	private $regionGateway;

	public function __construct(Database $db, RegionGateway $regionGateway)
	{
		parent::__construct($db);
		$this->regionGateway = $regionGateway;
	}

	public function listForRegion($id)
	{
		return $this->db->fetchAll('
			SELECT
				e.`id`,
				e.`name`,
				e.`start`,
				UNIX_TIMESTAMP(e.`start`) AS start_ts,
				e.`end`,
				UNIX_TIMESTAMP(e.`end`) AS end_ts

			FROM
				`fs_event` e

			WHERE
				e.bezirk_id = :id

			AND e.start > NOW()

			ORDER BY
				e.start
		', [':id' => $id]);
	}

	public function getEvent($id)
	{
		$event = $this->db->fetch('
			SELECT
				e.id,
				fs.`id` AS fs_id,
				fs.name AS fs_name,
				fs.photo AS fs_photo,
				e.`bezirk_id`,
				e.`location_id`,
				e.`name`,
				e.`start`,
				UNIX_TIMESTAMP(e.`start`) AS start_ts,
				e.`end`,
				UNIX_TIMESTAMP(e.`end`) AS end_ts,
				e.`description`,
				e.`bot`,
				e.`online`,
				e.`public`

			FROM
				`fs_event` e,
				`fs_foodsaver` fs

			WHERE
				e.foodsaver_id = fs.id

			AND
				e.id = :id
		', [':id' => $id]);

		$event['location'] = false;
		if ($event['location_id'] !== null) {
			$event['location'] = $this->getLocation($event['location_id']);
		}

		return $event;
	}

	public function getLocation($id)
	{
		if ($id === null) {
			return null;
		}

		return $this->db->fetch('
			SELECT id, name, lat, lon, zip, city, street
			FROM  fs_location
			WHERE 	id = :id
		', [':id' => $id]);
	}

	public function addLocation($location_name, $lat, $lon, $address, $zip, $city)
	{
		$lat = round($lat, 8);
		$lon = round($lon, 8);

		return $this->db->insert('fs_location', [
			'name' => strip_tags($location_name),
			'lat' => (float)$lat,
			'lon' => (float)$lon,
			'zip' => strip_tags($zip),
			'city' => strip_tags($city),
			'street' => strip_tags($address)
		]);
	}

	public function getEventWithInvites($id)
	{
		$event = $this->db->fetch('
			SELECT
				e.id,
				e.public,
				fs.`id` AS fs_id,
				fs.name AS fs_name,
				fs.photo AS fs_photo,
				e.`bezirk_id`,
				e.`location_id`,
				e.`name`,
				e.`start`,
				UNIX_TIMESTAMP(e.`start`) AS start_ts,
				e.`end`,
				UNIX_TIMESTAMP(e.`end`) AS end_ts,
				e.`description`,
				e.`bot`,
				e.`online`
	
			FROM
				`fs_event` e,
				`fs_foodsaver` fs
	
			WHERE
				e.foodsaver_id = fs.id
	
			AND
				e.id = :id
		', [':id' => $id]);

		$event['location'] = false;
		$event['invites'] = $this->getEventInvites($id);

		if ($event['location_id'] !== null) {
			$event['location'] = $this->getLocation($event['location_id']);
		}

		return $event;
	}

	private function getEventInvites($event_id)
	{
		$invites = $this->db->fetchAll('
			SELECT 	fs.id,
					fs.name,
					fs.photo,
					fe.status

			FROM 
				`fs_foodsaver_has_event` fe,
				`fs_foodsaver` fs
				
			WHERE
				fe.foodsaver_id = fs.id
				
			AND 
				fe.event_id = :event_id
		', [':event_id' => $event_id]);

		$out = array(
			'invited' => array(),
			'accepted' => array(),
			'maybe' => array(),
			'may' => array()
		);
		foreach ($invites as $i) {
			$out['may'][$i['id']] = true;
			if ($i['status'] == 0) {
				$out['invited'][] = $i;
			} elseif ($i['status'] == 1) {
				$out['accepted'][] = $i;
			} elseif ($i['status'] == 2) {
				$out['maybe'][] = $i;
			}
		}

		return $out;
	}

	public function getNextEvents($fs_id)
	{
		$next = $this->db->fetchAll('
			SELECT
				e.id,
				e.name,
				e.`description`,
				e.`start`,
				UNIX_TIMESTAMP(e.`start`) AS start_ts,
				fe.`status`

			FROM
				`fs_event` e
			LEFT JOIN
				`fs_foodsaver_has_event` fe
			ON
				e.id = fe.event_id AND fe.foodsaver_id = :fs_id

			WHERE
				e.start >= CURDATE()
			AND
				((e.public = 1 AND (fe.`status` IS NULL OR fe.`status` <> 3))
				OR
					fe.`status` IN(1,2)
				)
			ORDER BY e.`start`
		', [':fs_id' => (int)$fs_id]);

		$out = array();

		if ($next) {
			foreach ($next as $n) {
				$out[date('Y-m-d H:i', $n['start_ts']) . '-' . $n['id']] = $n;
			}
		}
		if (!empty($out)) {
			return $out;
		}
	}

	public function getInvites($fs_id)
	{
		return $this->db->fetchAll('
			SELECT
				e.id,
				e.name,
				e.`description`,
				e.`start`,
				UNIX_TIMESTAMP(e.`start`) AS start_ts,
				fe.`status`

			FROM
				`fs_event` e,
				`fs_foodsaver_has_event` fe

			WHERE
				fe.event_id = e.id

			AND
				fe.foodsaver_id = :fs_id

			AND
				fe.`status` = 0

			AND
				e.`end` > NOW()
		', [':fs_id' => (int)$fs_id]);
	}

	public function addEvent($fs_id, $event): int
	{
		$extracted_event = [
			'foodsaver_id' => $fs_id,
			'bezirk_id' => $event['bezirk_id'],
			'location_id' => $event['location_id'],
			'public' => $event['public'],
			'name' => $event['name'],
			'start' => $event['start'],
			'end' => $event['end'],
			'description' => $event['description'],
			'bot' => 0,
			'online' => $event['online']
		];

		return $this->db->insert('fs_event', $extracted_event);
	}

	public function updateEvent($id, $event): bool
	{
		$extracted_event = [
			'bezirk_id' => $event['bezirk_id'],
			'location_id' => $event['location_id'],
			'public' => $event['public'],
			'name' => $event['name'],
			'start' => $event['start'],
			'end' => $event['end'],
			'description' => $event['description'],
			'online' => $event['online']
		];

		$this->db->requireExists('fs_event', ['id' => $id]);
		$this->db->update('fs_event', $extracted_event, ['id' => $id]);

		return true;
	}

	public function deleteInvites($event_id)
	{
		return $this->db->delete('fs_foodsaver_has_event', ['event_id' => $event_id]);
	}

	public function getInviteStatus($event_id, $foodsaver_id)
	{
		try {
			$status = $this->db->fetchValueByCriteria(
				'fs_foodsaver_has_event',
				'status',
				['event_id' => $event_id, 'foodsaver_id' => $foodsaver_id]
			);
		} catch (\Exception $e) {
			$status = -1;
		}

		return (int)$status;
	}

	public function setInviteStatus($event_id, $foodsaver_id, $status)
	{
		$this->db->update(
			'fs_foodsaver_has_event',
			['status' => $status],
			['foodsaver_id' => $foodsaver_id, 'event_id' => $event_id]
		);

		return true;
	}

	public function addInviteStatus($event_id, $foodsaver_id, $status): bool
	{
		$this->db->insertOrUpdate(
			'fs_foodsaver_has_event',
			[
				'status' => $status,
				'foodsaver_id' => $foodsaver_id,
				'event_id' => $event_id
			]
		);

		return true;
	}

	public function inviteFullRegion($bezirk_id, $event_id, $invite_subs = false)
	{
		$b_sql = '= ' . (int)$bezirk_id;

		if ($invite_subs) {
			$bids = $this->regionGateway->listIdsForDescendantsAndSelf($bezirk_id);
			$b_sql = 'IN(' . implode(',', $bids) . ')';
		}

		if ($fsids = $this->db->fetchAllValues('
			SELECT 	`foodsaver_id`
			FROM	`fs_foodsaver_has_bezirk`
			WHERE 	`bezirk_id` ' . $b_sql . '
			AND 	`active` = 1
		')
		) {
			$invited = array();
			if ($inv = $this->db->fetchAllValues(
				'
				SELECT `foodsaver_id` FROM `fs_foodsaver_has_event`
				WHERE `event_id` = :event_id',
				[':event_id' => (int)$event_id]
			)
			) {
				foreach ($inv as $i) {
					$invited[$i] = true;
				}
			}

			foreach ($fsids as $id) {
				if (!isset($invited[$id])) {
					$this->addInviteStatus($event_id, $id, 0);
				}
			}
		}
	}
}
