<?php

namespace Foodsharing\Modules\Event;

use Foodsharing\Modules\Core\BaseGateway;

class EventGateway extends BaseGateway
{
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
		', ['id' => $id]);
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
		', ['id' => $id]);

		$event['location'] = false;
		if ($event['location_id'] > 0) {
			$event['location'] = $this->getLocation($event['location_id']);
		}

		return $event;
	}

	public function getLocation($id)
	{
		return $this->db->fetch('
			SELECT id, name, lat, lon, zip, city, street
			FROM  fs_location
			WHERE 	id = :id
		', ['id' => $id]);
	}

	public function addLocation($location_name, $lat, $lon, $address, $zip, $city)
	{
		$lat = round($lat, 8);
		$lon = round($lon, 8);

		return $this->db->insert('fs_location', [
			'name' => strip_tags($location_name),
			'lat' => floatval($lat),
			'lon' => floatval($lon),
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
		', ['id' => $id]);

		$event['location'] = false;
		$event['invites'] = $this->getEventInvites($id);

		if ($event['location_id'] > 0) {
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
		', ['event_id' => $event_id]);

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
				e.id = fe.event_id AND fe.foodsaver_id = ' . (int)$fs_id . '

			WHERE
				e.start >= CURDATE()
			AND
				((e.public = 1 AND (fe.`status` IS NULL OR fe.`status` <> 3))
				OR
					fe.`status` IN(1,2)
				)
			ORDER BY e.`start`
		');

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
				fe.foodsaver_id = ' . (int)$fs_id . '

			AND
				fe.`status` = 0

			AND
				e.`end` > NOW()
		');
	}
}
