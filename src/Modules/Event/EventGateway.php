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

	public function listForRegion(int $regionId): array
	{
		return $this->db->fetchAll('
			SELECT
				e.id,
				e.name,
				e.start,
				UNIX_TIMESTAMP(e.start) AS start_ts,
				e.end,
				UNIX_TIMESTAMP(e.end) AS end_ts
			FROM
				fs_event e
			WHERE
				e.bezirk_id = :regionId
			AND e.start > NOW()
			ORDER BY
				e.start
		', [':regionId' => $regionId]);
	}

	public function getEvent(int $eventId, bool $withInvitations = false): ?array
	{
		$event = $this->db->fetch('
			SELECT
				e.id,
				fs.id AS fs_id,
				fs.name AS fs_name,
				fs.photo AS fs_photo,
				e.bezirk_id,
				e.location_id,
				e.name,
				e.`start`,
				UNIX_TIMESTAMP(e.start) AS start_ts,
				e.`end`,
				UNIX_TIMESTAMP(e.end) AS end_ts,
				e.description,
				e.bot,
				e.online,
				e.public
			FROM
				fs_event e,
				fs_foodsaver fs
			WHERE
				e.foodsaver_id = fs.id
			AND
				e.id = :eventId
		', [':eventId' => $eventId]);

		if (!$event) {
			return null;
		}

		if ($withInvitations) {
			$event['invites'] = $this->getEventInvites($eventId);
		}

		if ($event['location_id'] === null) {
			$event['location'] = false;
		} else {
			$event['location'] = $this->getLocation($event['location_id']);
		}

		return $event;
	}

	public function getLocation(int $locationId)
	{
		return $this->db->fetch('
			SELECT id, name, lat, lon, zip, city, street
			FROM   fs_location
			WHERE  id = :locationId
		', [':locationId' => $locationId]);
	}

	public function addLocation(string $locationName, float $lat, float $lon, string $address, string $zip, string $city): int
	{
		return $this->db->insert('fs_location', [
			'name' => strip_tags($locationName),
			'lat' => round($lat, 8),
			'lon' => round($lon, 8),
			'zip' => strip_tags($zip),
			'city' => strip_tags($city),
			'street' => strip_tags($address),
		]);
	}

	private function getEventInvites($eventId)
	{
		$invites = $this->db->fetchAll('
			SELECT 	fs.id,
					fs.name,
					fs.photo,
					fhe.status
			FROM
				`fs_foodsaver_has_event` fhe,
				`fs_foodsaver` fs

			WHERE
				fhe.foodsaver_id = fs.id

			AND
				fhe.event_id = :eventId
		', [':eventId' => $eventId]);

		$out = [
			'invited' => [],
			'accepted' => [],
			'maybe' => [],
			'may' => []
		];
		foreach ($invites as $i) {
			$out['may'][$i['id']] = true;
			if ($i['status'] == InvitationStatus::INVITED) {
				$out['invited'][] = $i;
			} elseif ($i['status'] == InvitationStatus::ACCEPTED) {
				$out['accepted'][] = $i;
			} elseif ($i['status'] == InvitationStatus::MAYBE) {
				$out['maybe'][] = $i;
			}
		}

		return $out;
	}

	public function getEventsInterestedIn(int $userId): array
	{
		$next = $this->db->fetchAll('
			SELECT
				e.id,
				e.name,
				e.description,
				e.start,
				UNIX_TIMESTAMP(e.start) AS start_ts,
				fhe.status
			FROM
				fs_event e
			LEFT JOIN
				fs_foodsaver_has_event fhe
			ON
				e.id = fhe.event_id AND fhe.foodsaver_id = :userId
			WHERE
				e.end >= CURDATE()
			AND
				((e.public = 1 AND (fhe.status IS NULL OR fhe.status <> 3))
				OR
					fhe.status IN(1,2)
				)
			ORDER BY e.start
		', [':userId' => $userId]);

		$out = [];

		if ($next) {
			foreach ($next as $n) {
				$out[date('Y-m-d H:i', $n['start_ts']) . '-' . $n['id']] = $n;
			}
		}

		return $out;
	}

	public function getEventInvitations(int $userId): array
	{
		return $this->db->fetchAll('
			SELECT
				e.id,
				e.name,
				e.description,
				e.start,
				UNIX_TIMESTAMP(e.start) AS start_ts,
				fhe.`status`
			FROM
				fs_event e,
				fs_foodsaver_has_event fhe
			WHERE
			    fhe.event_id = e.id
			AND
				fhe.foodsaver_id = :userId
			AND
				fhe.status = 0
			AND
				e.end > NOW()
			ORDER BY
			e.start
		', [':userId' => $userId]);
	}

	public function addEvent(int $creatorId, array $event): int
	{
		$extracted_event = [
			'foodsaver_id' => $creatorId,
			'bezirk_id' => $event['bezirk_id'],
			'location_id' => $event['location_id'],
			'public' => $event['public'],
			'name' => $event['name'],
			'start' => $event['start'],
			'end' => $event['end'],
			'description' => $event['description'],
			'bot' => 0,
			'online' => $event['online'],
		];

		return $this->db->insert('fs_event', $extracted_event);
	}

	public function updateEvent(int $eventId, array $event): bool
	{
		$extracted_event = [
			'bezirk_id' => $event['bezirk_id'],
			'location_id' => $event['location_id'],
			'public' => $event['public'],
			'name' => $event['name'],
			'start' => $event['start'],
			'end' => $event['end'],
			'description' => $event['description'],
			'online' => $event['online'],
		];

		$this->db->requireExists('fs_event', ['id' => $eventId]);
		$this->db->update('fs_event', $extracted_event, ['id' => $eventId]);

		return true;
	}

	public function deleteInvites(int $eventId): int
	{
		return $this->db->delete('fs_foodsaver_has_event', ['event_id' => $eventId]);
	}

	public function getInviteStatus(int $eventId, int $foodsaverId): int
	{
		try {
			$status = $this->db->fetchValueByCriteria(
				'fs_foodsaver_has_event',
				'status',
				['event_id' => $eventId, 'foodsaver_id' => $foodsaverId]
			);
		} catch (\Exception $e) {
			$status = -1;
		}

		return (int)$status;
	}

	/**
	 * Sets the invitation status for multiple foodsavers.
	 *
	 * @throws \Exception if the database query fails (which should usually not happen)
	 */
	public function setInviteStatus(int $eventId, array $foodsaverIds, int $status): bool
	{
		$data = [];
		$parts = array_chunk($foodsaverIds, 100);
		foreach ($parts as $part) {
			foreach ($part as $userId) {
				$data[] = [
					'status' => $status,
					'foodsaver_id' => $userId,
					'event_id' => $eventId,
				];
			}
			$this->db->insertOrUpdateMultiple(
				'fs_foodsaver_has_event',
				$data
			);
		}

		return true;
	}

	public function inviteFullRegion(int $regionId, int $eventId, bool $invite_subs = false): void
	{
		$regionIds = [$regionId];
		if ($invite_subs) {
			$regionIds = $this->regionGateway->listIdsForDescendantsAndSelf($regionId);
		}

		$foodsaverIds = $this->db->fetchAllValuesByCriteria('fs_foodsaver_has_bezirk', 'foodsaver_id',
			['bezirk_id' => $regionIds, 'active' => 1]
		);
		$invited = $this->db->fetchAllValuesByCriteria('fs_foodsaver_has_event', 'foodsaver_id',
			['event_id' => $eventId]
		);

		$this->setInviteStatus($eventId, array_diff($foodsaverIds, $invited), InvitationStatus::INVITED);
	}
}
