<?php

namespace Foodsharing\Modules\Store;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Foodsharing\Helpers\TimeHelper;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\BellUpdaterInterface;
use Foodsharing\Modules\Bell\BellUpdateTrigger;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\DTO\StoreForTopbarMenu;

class StoreGateway extends BaseGateway implements BellUpdaterInterface
{
	private $regionGateway;
	private $bellGateway;
	private $timeHelper;

	public function __construct(
		Database $db,
		RegionGateway $regionGateway,
		BellGateway $bellGateway,
		BellUpdateTrigger $bellUpdateTrigger,
		TimeHelper $timeHelper
	) {
		parent::__construct($db);

		$this->regionGateway = $regionGateway;
		$this->bellGateway = $bellGateway;
		$this->timeHelper = $timeHelper;

		$bellUpdateTrigger->subscribe($this);
	}

	public function getBetrieb($storeId): array
	{
		$out = $this->db->fetch('
		SELECT		`id`,
					plz,
					`fs_betrieb`.bezirk_id,
					`fs_betrieb`.kette_id,
					`fs_betrieb`.betrieb_kategorie_id,
					`fs_betrieb`.name,
					`fs_betrieb`.str,
					`fs_betrieb`.hsnr,
					`fs_betrieb`.stadt,
					`fs_betrieb`.lat,
					`fs_betrieb`.lon,
					CONCAT(`fs_betrieb`.str, " ",`fs_betrieb`.hsnr) AS anschrift,
					`fs_betrieb`.`betrieb_status_id`,
					`fs_betrieb`.status_date,
					`fs_betrieb`.ansprechpartner,
					`fs_betrieb`.telefon,
					`fs_betrieb`.email,
					`fs_betrieb`.fax,
					`fs_betrieb`.team_status,
					`kette_id`

		FROM 		`fs_betrieb`

		WHERE 		`fs_betrieb`.`id` = :id',
		[':id' => $storeId]);

		$out['verantwortlicher'] = '';
		if ($bezirk = $this->regionGateway->getRegionName($out['bezirk_id'])) {
			$out['bezirk'] = $bezirk;
		}
		if ($verantwortlich = $this->getBiebsForStore($storeId)) {
			$out['verantwortlicher'] = $verantwortlich;
		}
		if ($kette = $this->getOne_kette($out['kette_id'])) {
			$out['kette'] = $kette;
		}

		$out['notizen'] = $this->getBetriebNotiz($storeId);

		return $out;
	}

	public function getMapsStores(int $regionId): array
	{
		return $this->db->fetchAll('
			SELECT 	b.id,
					b.betrieb_status_id,
					b.plz,
					b.`lat`,
					b.`lon`,
					b.`stadt`,
					b.kette_id,
					b.betrieb_kategorie_id,
					b.name,
					CONCAT(b.str," ",b.hsnr) AS anschrift,
					b.str,
					b.hsnr,
					b.`betrieb_status_id`,
					k.logo

			FROM 	fs_betrieb b
			LEFT JOIN fs_kette k ON b.kette_id = k.id

			WHERE 	b.bezirk_id = :bezirk_id

			AND b.`lat` != ""',
			[
				':bezirk_id' => $regionId
			]
		);
	}

	public function getMyStores($fs_id, $regionId, $options = []): array
	{
		$betriebe = $this->db->fetchAll('
			SELECT 	fs_betrieb.id,
						`fs_betrieb`.betrieb_status_id,
						fs_betrieb.plz,
						fs_betrieb.kette_id,

						fs_betrieb.ansprechpartner,
						fs_betrieb.fax,
						fs_betrieb.telefon,
						fs_betrieb.email,

						fs_betrieb.betrieb_kategorie_id,
						fs_betrieb.name,
						CONCAT(fs_betrieb.str," ",fs_betrieb.hsnr) AS anschrift,
						fs_betrieb.str,
						fs_betrieb.hsnr,
						fs_betrieb.`betrieb_status_id`,
						fs_betrieb_team.verantwortlich,
						fs_betrieb_team.active

				FROM 	fs_betrieb,
						fs_betrieb_team

				WHERE 	fs_betrieb.id = fs_betrieb_team.betrieb_id

				AND 	fs_betrieb_team.foodsaver_id = :fs_id

				ORDER BY fs_betrieb_team.verantwortlich DESC, fs_betrieb.name ASC
		', [':fs_id' => $fs_id]);

		$out = [];
		$out['verantwortlich'] = [];
		$out['team'] = [];
		$out['waitspringer'] = [];
		$out['anfrage'] = [];

		$already_in = [];

		if (is_array($betriebe)) {
			foreach ($betriebe as $b) {
				$already_in[$b['id']] = true;
				if ($b['verantwortlich'] == 0) {
					if ($b['active'] == 0) {
						$out['anfrage'][] = $b;
					} elseif ($b['active'] == 1) {
						$out['team'][] = $b;
					} elseif ($b['active'] == 2) {
						$out['waitspringer'][] = $b;
					}
				} else {
					$out['verantwortlich'][] = $b;
				}
			}
		}
		unset($betriebe);

		if (!isset($options['sonstige'])) {
			$options['sonstige'] = true;
		}

		if ($options['sonstige']) {
			$child_region_ids = $this->regionGateway->listIdsForDescendantsAndSelf($regionId);
			$placeholders = $this->db->generatePlaceholders(count($child_region_ids));

			$out['sonstige'] = [];
			$betriebe = $this->db->fetchAll(
		'SELECT 		b.id,
						b.betrieb_status_id,
						b.plz,
						b.kette_id,

						b.ansprechpartner,
						b.fax,
						b.telefon,
						b.email,

						b.betrieb_kategorie_id,
						b.name,
						CONCAT(b.str," ",b.hsnr) AS anschrift,
						b.str,
						b.hsnr,
						b.`betrieb_status_id`,
						bz.name AS bezirk_name

				FROM 	fs_betrieb b,
						fs_bezirk bz

				WHERE 	b.bezirk_id = bz.id
				AND 	bezirk_id IN(' . $placeholders . ')
				ORDER BY bz.name DESC',
			$child_region_ids);

			foreach ($betriebe as $b) {
				if (!isset($already_in[$b['id']])) {
					$out['sonstige'][] = $b;
				}
			}
		}

		return $out;
	}

	public function getMyStore($fs_id, $storeId): array
	{
		$out = $this->db->fetch('
			SELECT
			b.`id`,
			b.`betrieb_status_id`,
			b.`bezirk_id`,
			b.`plz`,
			b.`stadt`,
			b.`lat`,
			b.`lon`,
			b.`kette_id`,
			b.`betrieb_kategorie_id`,
			b.`name`,
			b.`str`,
			b.`hsnr`,
			b.`status_date`,
			b.`status`,
			b.`ansprechpartner`,
			b.`telefon`,
			b.`fax`,
			b.`email`,
			b.`begin`,
			b.`besonderheiten`,
			b.`public_info`,
			b.`public_time`,
			b.`ueberzeugungsarbeit`,
			b.`presse`,
			b.`sticker`,
			b.`abholmenge`,
			b.`team_status`,
			b.`prefetchtime`,
			b.`team_conversation_id`,
			b.`springer_conversation_id`,
			count(DISTINCT(a.date)) AS pickup_count

			FROM 		`fs_betrieb` b
			LEFT JOIN   `fs_abholer` a
			ON a.betrieb_id = b.id

			WHERE 		b.`id` = :id
			GROUP BY b.`id`',
			[':id' => $storeId]
		);
		if (!$out) {
			return $out;
		}

		$out['lebensmittel'] = $this->db->fetchAll('
				SELECT 		l.`id`,
							l.name
				FROM 		`fs_betrieb_has_lebensmittel` hl,
							`fs_lebensmittel` l
				WHERE 		l.id = hl.lebensmittel_id
				AND 		`betrieb_id` = :id
		', [':id' => $storeId]);

		$out['foodsaver'] = $this->getStoreTeam($storeId);

		$out['springer'] = $this->getBetriebSpringer($storeId);

		$out['requests'] = $this->db->fetchAll('
				SELECT 		fs.`id`,
							fs.photo,
							CONCAT(fs.name," ",fs.nachname) AS name,
							name as vorname,
							fs.sleep_status,
							fs.verified

				FROM 		`fs_betrieb_team` t,
							`fs_foodsaver` fs

				WHERE 		fs.id = t.foodsaver_id
				AND 		`betrieb_id` = :id
				AND 		t.active = 0
				AND			fs.deleted_at IS NULL
		', [':id' => $storeId]);

		$out['verantwortlich'] = false;
		$foodsaver = [];
		$out['team'] = [];
		$out['jumper'] = false;

		if (!empty($out['springer'])) {
			foreach ($out['springer'] as $v) {
				if ($v['id'] == $fs_id) {
					$out['jumper'] = true;
				}
			}
		}

		if (!empty($out['foodsaver'])) {
			$out['team'] = [];
			foreach ($out['foodsaver'] as $v) {
				$foodsaver[$v['id']] = $v['name'];
				$out['team'][] = ['id' => $v['id'], 'value' => $v['name']];
				if ($v['verantwortlich'] == 1) {
					$out['verantwortlicher'] = $v['id'];
					if ($v['id'] == $fs_id) {
						$out['verantwortlich'] = true;
					}
				}
			}
		} else {
			$out['foodsaver'] = [];
		}

		return $out;
	}

	public function getStoreTeam($storeId): array
	{
		return $this->db->fetchAll('
				SELECT 		fs.`id`,
							fs.`verified`,
							fs.`active`,
							fs.`telefon`,
							fs.`handy`,
							fs.photo,
							fs.quiz_rolle,
							fs.rolle,
							CONCAT(fs.name," ",fs.nachname) AS name,
							name as vorname,
							t.`verantwortlich`,
							t.`stat_last_update`,
							t.`stat_fetchcount`,
							t.`stat_first_fetch`,
							t.`stat_add_date`,
							UNIX_TIMESTAMP(t.`stat_last_fetch`) AS last_fetch,
							UNIX_TIMESTAMP(t.`stat_add_date`) AS add_date,
							fs.sleep_status


				FROM 		`fs_betrieb_team` t,
							`fs_foodsaver` fs

				WHERE 		fs.id = t.foodsaver_id
				AND 		`betrieb_id` = :id
				AND 		t.active  = 1
				AND			fs.deleted_at IS NULL
				ORDER BY 	t.`stat_fetchcount` DESC
		', [':id' => $storeId]);
	}

	public function getBetriebSpringer($storeId): array
	{
		return $this->db->fetchAll('
				SELECT 		fs.`id`,
							fs.`active`,
							fs.`telefon`,
							fs.`handy`,
							fs.photo,
							fs.rolle,
							CONCAT(fs.name," ",fs.nachname) AS name,
							name as vorname,
							t.`verantwortlich`,
							t.`stat_last_update`,
							t.`stat_fetchcount`,
							t.`stat_first_fetch`,
							UNIX_TIMESTAMP(t.`stat_add_date`) AS add_date,
							fs.sleep_status

				FROM 		`fs_betrieb_team` t,
							`fs_foodsaver` fs

				WHERE 		fs.id = t.foodsaver_id
				AND 		`betrieb_id` = :id
				AND 		t.active  = 2
				AND			fs.deleted_at IS NULL
		', [':id' => $storeId]);
	}

	public function getBiebsForStore($storeId)
	{
		return $this->db->fetchAll(
			'
			SELECT 	`foodsaver_id` as id
			FROM fs_betrieb_team
			WHERE `betrieb_id` = :betrieb_id
			AND verantwortlich = 1
			AND `active` = 1',
			[':betrieb_id' => $storeId]);
	}

	public function getAllStoreManagers(): array
	{
		$verant = $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`email`

			FROM 	`fs_foodsaver` fs,
					`fs_betrieb_team` bt

			WHERE 	bt.foodsaver_id = fs.id

			AND 	bt.verantwortlich = 1
			AND		fs.deleted_at IS NULL
		');

		$out = [];
		foreach ($verant as $v) {
			$out[$v['id']] = $v;
		}

		return $out;
	}

	public function getStoreCountForBieb($fs_id)
	{
		return $this->db->count('fs_betrieb_team', ['foodsaver_id' => $fs_id, 'verantwortlich' => 1]);
	}

	public function getEmailBiepBez($region_ids): array
	{
		// TODO can probably be removed
		$placeholders = $this->db->generatePlaceholders(count($region_ids));

		$verant = $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`email`

			FROM 	`fs_foodsaver` fs,
					`fs_betrieb_team` bt,
					`fs_foodsaver_has_bezirk` b

			WHERE 	bt.foodsaver_id = fs.id
			AND 	bt.foodsaver_id = b.foodsaver_id
			AND 	bt.verantwortlich = 1
			AND		b.`bezirk_id` IN(' . $placeholders . ')
			AND		fs.deleted_at IS NULL
		', $region_ids);

		$out = [];
		foreach ($verant as $v) {
			$out[$v['id']] = $v;
		}

		return $out;
	}

	public function getUserTeamStatus(int $userId, int $storeId): int
	{
		$result = $this->db->fetchByCriteria('fs_betrieb_team',
			['active', 'verantwortlich'],
			[
				'betrieb_id' => $storeId,
				'foodsaver_id' => $userId,
			]);
		if (!$result) {
			return TeamStatus::NoMember;
		} else {
			if ($result['verantwortlich'] && $result['active'] == 1) {
				return TeamStatus::Coordinator;
			} else {
				switch ($result['active']) {
					case 2:
						return TeamStatus::WaitingList;
					case 1:
						return TeamStatus::Member;
					default:
						return TeamStatus::Applied;
				}
			}
		}
	}

	public function addFetcher(int $fsId, int $storeId, \DateTime $date, bool $confirmed = false): int
	{
		$queryResult = $this->db->insertIgnore('fs_abholer', [
			'foodsaver_id' => $fsId,
			'betrieb_id' => $storeId,
			'date' => $this->db->date($date),
			'confirmed' => $confirmed
		]);

		if (!$confirmed) {
			$this->updateBellNotificationForStoreManagers($storeId, true);
		}

		return $queryResult;
	}

	public function deleteAllDatesFromAFoodsaver(int $fs_id)
	{
		$storeIdsThatWillBeDeleted = $this->db->fetchAllValuesByCriteria(
			'fs_abholer',
			'betrieb_id',
			[
				'foodsaver_id' => $fs_id,
				'date >' => $this->db->now()
			]
		);

		$result = $this->db->delete('fs_abholer', [
			'foodsaver_id' => $fs_id,
			'date >' => $this->db->now()
		]);

		foreach ($storeIdsThatWillBeDeleted as $storeIdDel) {
			$this->updateBellNotificationForStoreManagers($storeIdDel);
		}

		return $result;
	}

	public function removeFetcher(int $fsId, int $storeId, \DateTime $date)
	{
		$deletedRows = $this->db->delete('fs_abholer', [
			'foodsaver_id' => $fsId,
			'betrieb_id' => $storeId,
			'date' => $this->db->date($date)
		]);
		$this->updateBellNotificationForStoreManagers($storeId);

		return $deletedRows;
	}

	/**
	 * @param bool $markNotificationAsUnread:
	 * if an older notification exists, that has already been marked as read,
	 * it can be marked as unread again while updating it
	 */
	public function updateBellNotificationForStoreManagers(int $storeId, bool $markNotificationAsUnread = false): void
	{
		$storeName = $this->db->fetchValueByCriteria('fs_betrieb', 'name', ['id' => $storeId]);
		$messageIdentifier = 'store-fetch-unconfirmed-' . $storeId;
		$messageCount = $this->getUnconfirmedFetchesCount($storeId);
		$messageVars = ['betrieb' => $storeName, 'count' => $messageCount];
		$messageTimestamp = $this->getNextUnconfirmedFetchTime($storeId);
		$messageExpiration = $messageTimestamp;

		$oldBellExists = $this->bellGateway->bellWithIdentifierExists($messageIdentifier);

		if ($messageCount === 0 && $oldBellExists) {
			$this->bellGateway->delBellsByIdentifier($messageIdentifier);
		} elseif ($messageCount > 0 && $oldBellExists) {
			$oldBellId = $this->bellGateway->getOneByIdentifier($messageIdentifier);
			$data = [
				'vars' => $messageVars,
				'time' => $messageTimestamp,
				'expiration' => $messageExpiration
			];
			$this->bellGateway->updateBell($oldBellId, $data, $markNotificationAsUnread);
		} elseif ($messageCount > 0 && !$oldBellExists) {
			$bellData = Bell::create(
				'betrieb_fetch_title',
				'betrieb_fetch',
				'img img-store brown',
				['href' => '/?page=fsbetrieb&id=' . $storeId],
				$messageVars,
				$messageIdentifier,
				0,
				$messageExpiration,
				$messageTimestamp
			);
			$this->bellGateway->addBell($this->getResponsibleFoodsavers($storeId), $bellData);
		}
	}

	public function confirmFetcher($fsid, $storeId, $date): int
	{
		$result = $this->db->update(
		'fs_abholer',
			['confirmed' => 1],
			['foodsaver_id' => $fsid, 'betrieb_id' => $storeId, 'date' => $this->db->date($date)]
		);

		$this->updateBellNotificationForStoreManagers($storeId);

		return $result;
	}

	public function listFetcher($storeId, $dates): array
	{
		if (!empty($dates)) {
			$placeholders = $this->db->generatePlaceholders(count($dates));

			$res = $this->db->fetchAll('
				SELECT 	fs.id,
						fs.name,
						fs.photo,
						a.date,
						a.confirmed

				FROM 	`fs_abholer` a,
						`fs_foodsaver` fs

				WHERE 	a.foodsaver_id = fs.id
				AND 	a.betrieb_id = ?
				AND  	a.date IN(' . $placeholders . ')
				AND		fs.deleted_at IS NULL',
				array_merge([$storeId], $dates)
			);

			return $res;
		}

		return [];
	}

	public function getAbholzeiten($storeId)
	{
		if ($res = $this->db->fetchAll('SELECT `time`,`dow`,`fetcher` FROM `fs_abholzeiten` WHERE `betrieb_id` = :id', [':id' => $storeId])) {
			$out = [];
			foreach ($res as $r) {
				$out[$r['dow'] . '-' . $r['time']] = [
					'dow' => $r['dow'],
					'time' => $r['time'],
					'fetcher' => $r['fetcher']
				];
			}

			ksort($out);

			return $out;
		}

		return false;
	}

	public function getBetriebConversation($storeId, $springerConversation = false)
	{
		if ($springerConversation) {
			$ccol = 'springer_conversation_id';
		} else {
			$ccol = 'team_conversation_id';
		}

		return $this->db->fetchValue('SELECT ' . $ccol . ' FROM `fs_betrieb` WHERE `id` = :id', [':id' => $storeId]);
	}

	public function changeBetriebStatus($fs_id, $storeId, $status): int
	{
		$last = $this->db->fetch('SELECT id, milestone FROM `fs_betrieb_notiz` WHERE `betrieb_id` = :id ORDER BY id DESC LIMIT 1', [':id' => $storeId]);

		if ($last['milestone'] == 3) {
			$this->db->delete('fs_betrieb_notiz', ['id' => $last['id']]);
		}

		$this->add_betrieb_notiz([
			'foodsaver_id' => $fs_id,
			'betrieb_id' => $storeId,
			'text' => 'status_msg_' . (int)$status,
			'zeit' => date('Y-m-d H:i:s'),
			'milestone' => 3
		]);

		return $this->db->update(
			'fs_betrieb',
			['betrieb_status_id' => $status],
			['id' => $storeId]
		);
	}

	public function add_betrieb_notiz($data): int
	{
		$last = 0;
		if (isset($data['last']) && $data['last'] == 1) {
			$this->db->update(
				'fs_betrieb_notiz',
				['last' => 0],
				['betrieb_id' => $data['betrieb_id'], 'last' => 1]
			);
			$last = 1;
		}

		return $this->db->insert('fs_betrieb_notiz', [
			'foodsaver_id' => $data['foodsaver_id'],
			'betrieb_id' => $data['betrieb_id'],
			'milestone' => $data['milestone'],
			'text' => strip_tags($data['text']),
			'zeit' => $data['zeit'],
			'last' => $last
		]);
	}

	public function deleteBPost($id): int
	{
		return $this->db->delete('fs_betrieb_notiz', ['id' => $id]);
	}

	public function getTeamleader($storeId): array
	{
		return $this->db->fetch(
		'SELECT 	fs.`id`,CONCAT(fs.name," ",nachname) AS name
				FROM fs_betrieb_team t, fs_foodsaver fs
				WHERE t.foodsaver_id = fs.id
				AND `betrieb_id` = :id
				AND t.verantwortlich = 1
				AND fs.`active` = 1
				AND	fs.deleted_at IS NULL',
			[':id' => $storeId]);
	}

	/* retrieves all store managers for a given region (by being store manager in a store that is part of that region, which is semantically not the same we use on platform) */
	public function getStoreManagersOf(int $regionId): array
	{
		return $this->db->fetchAllValues('SELECT DISTINCT bt.foodsaver_id FROM `fs_bezirk_closure` c
			INNER JOIN `fs_betrieb` b ON c.bezirk_id = b.bezirk_id
			INNER JOIN `fs_betrieb_team` bt ON bt.betrieb_id = b.id
			INNER JOIN `fs_foodsaver` fs ON fs.id = bt.foodsaver_id
			WHERE c.ancestor_id = :regionId AND bt.verantwortlich = 1 AND fs.deleted_at IS NULL',
			[':regionId' => $regionId]);
	}

	/**
	 * @return StoreForTopbarMenu[]
	 */
	public function listFilteredStoresForFoodsaver($fsId): array
	{
		$rows = $this->db->fetchAll('
			SELECT 	b.`id`,
					b.name

			FROM 	`fs_betrieb_team` bt,
					`fs_betrieb` b

			WHERE 	bt.betrieb_id = b.id
			AND 	bt.`foodsaver_id` = :fsId
			AND 	bt.active = 1
			AND 	b.betrieb_status_id NOT IN (:doesNotWantToWorkWithUs, :givesToOtherCharity)
			ORDER BY b.name',
			[
				':fsId' => $fsId,
				':doesNotWantToWorkWithUs' => CooperationStatus::DOES_NOT_WANT_TO_WORK_WITH_US,
				':givesToOtherCharity' => CooperationStatus::GIVES_TO_OTHER_CHARITY
			]
		);

		$stores = [];
		foreach ($rows as $row) {
			$store = new StoreForTopbarMenu();
			$store->id = $row['id'];
			$store->name = $row['name'];
			$stores[] = $store;
		}

		return $stores;
	}

	public function listStoreIdsForBieb($fsId)
	{
		return $this->db->fetchAllByCriteria('fs_betrieb_team', ['betrieb_id'], ['foodsaver_id' => $fsId, 'verantwortlich' => 1]);
	}

	public function getPickupSignupsForDate(int $storeId, \DateTimeInterface $date)
	{
		return $this->getPickupSignupsForDateRange($storeId, $date, $date);
	}

	public function getPickupSignupsForDateRange(int $storeId, \DateTimeInterface $from, \DateTimeInterface $to = null)
	{
		$condition = ['date >=' => $this->db->date($from), 'betrieb_id' => $storeId];
		if (!is_null($to)) {
			$condition['date <='] = $this->db->date($to);
		}
		$result = $this->db->fetchAllByCriteria(
			'fs_abholer',
			['foodsaver_id', 'date', 'confirmed'],
			$condition
		);

		return array_map(function ($e) {
			$e['date'] = $this->db->parseDate($e['date']);

			return $e;
		}, $result);
	}

	public function getRegularPickup(int $storeId, int $dow, string $time): ?int
	{
		try {
			return $this->db->fetchValueByCriteria(
				'fs_abholzeiten',
				'fetcher',
				[
					'betrieb_id' => $storeId,
					'dow' => $dow,
					'time' => $time
				]
			);
		} catch (\Exception $e) {
			return null;
		}
	}

	public function getRegularPickups(int $storeId)
	{
		return $this->db->fetchAllByCriteria(
			'fs_abholzeiten',
			['time', 'dow', 'fetcher'],
			['betrieb_id' => $storeId]);
	}

	public function getOnetimePickups(int $storeId, \DateTimeInterface $date)
	{
		return $this->getOnetimePickupsForRange($storeId, $date, $date);
	}

	public function getOnetimePickupsForRange(int $storeId, \DateTimeInterface $from, ?\DateTimeInterface $to)
	{
		$condition = [
			'betrieb_id' => $storeId,
			'time >=' => $this->db->date($from)
		];
		if ($to) {
			$condition = array_merge($condition,
				[
					'time <=' => $this->db->date($to)
				]
			);
		}
		$result = $this->db->fetchAllByCriteria(
			'fs_fetchdate',
			['time', 'fetchercount'],
			$condition
		);

		return array_map(function ($e) {
			return [
				'date' => $this->db->parseDate($e['time']),
				'fetcher' => $e['fetchercount']
			];
		}, $result);
	}

	public function addOnetimePickup(int $storeId, \DateTimeInterface $date, int $slots)
	{
		$this->db->insert(
			'fs_fetchdate',
			[
				'betrieb_id' => $storeId,
				'time' => $this->db->date($date),
				'fetchercount' => $slots
			]
		);
	}

	public function updateOnetimePickupTotalSlots(int $storeId, \DateTimeInterface $date, int $slots): bool
	{
		return $this->db->update(
			'fs_fetchdate',
			['fetchercount' => $slots],
			[
				'betrieb_id' => $storeId,
				'time' => $this->db->date($date)
			]
		) === 1;
	}

	public function getFutureRegularPickupInterval(int $storeId): CarbonInterval
	{
		$result = $this->db->fetchValueByCriteria('fs_betrieb', 'prefetchtime', ['id' => $storeId]);

		return CarbonInterval::seconds($result);
	}

	private function getNextUnconfirmedFetchTime(int $storeId): \DateTime
	{
		$date = $this->db->fetchValue(
			'SELECT MIN(`date`)
					   FROM `fs_abholer`
					   WHERE `betrieb_id` = :storeId AND `confirmed` = 0 AND `date` > NOW()',
			[':storeId' => $storeId]
		);

		return new \DateTime($date);
	}

	private function getUnconfirmedFetchesCount(int $storeId)
	{
		return $this->db->count('fs_abholer', ['betrieb_id' => $storeId, 'confirmed' => 0, 'date >' => $this->db->now()]);
	}

	/*
	 * Private methods
	 */

	private function getOne_kette($id): array
	{
		return $this->db->fetch('
			SELECT
			`id`,
			`name`,
			`logo`

			FROM 		`fs_kette`

			WHERE 		`id` = :id',
			[':id' => $id]);
	}

	/**
	 * Returns the store comment with the specified ID.
	 */
	public function getStoreComment(int $commentId): array
	{
		return $this->db->fetchByCriteria('fs_betrieb_notiz',
			['id', 'foodsaver_id', 'betrieb_id', 'text', 'zeit'],
			['id' => $commentId]
		);
	}

	/**
	 * Returns all comments for a given store.
	 *
	 * @param $storeId
	 */
	private function getBetriebNotiz($storeId): array
	{
		return $this->db->fetchAll('
			SELECT
			`id`,
			`foodsaver_id`,
			`betrieb_id`,
			`text`,
			`zeit`,
			UNIX_TIMESTAMP(`zeit`) AS zeit_ts

			FROM 		`fs_betrieb_notiz`

			WHERE `betrieb_id` = :id',
		[':id' => $storeId]);
	}

	/**
	 * @return int[]
	 */
	private function getResponsibleFoodsavers(int $storeId): array
	{
		return $this->db->fetchAllValuesByCriteria(
			'fs_betrieb_team',
			'foodsaver_id',
			[
				'betrieb_id' => $storeId,
				'verantwortlich' => 1
			]
		);
	}

	public function updateExpiredBells(): void
	{
		$expiredBells = $this->bellGateway->getExpiredByIdentifier('store-fetch-unconfirmed-%');

		foreach ($expiredBells as $bell) {
			$storeId = substr($bell->identifier, strlen('store-fetch-unconfirmed-'));
			$this->updateBellNotificationForStoreManagers($storeId);
		}
	}

	public function getStoreByConversationId(int $id): ?array
	{
		$store = $this->db->fetch('SELECT id, name FROM fs_betrieb WHERE team_conversation_id = ? OR springer_conversation_id = ?', [$id, $id]);

		return $store;
	}

	/**
	 * @param \DateTime $from DateRange start for all slots. Now if empty.
	 * @param \DateTime $to DateRange for regular slots - future pickup interval if empty
	 * @param \DateTime $oneTimeSlotTo DateRange for onetime slots to be taken into account
	 */
	public function getPickupSlots(int $storeId, ?Carbon $from = null, ?Carbon $to = null, ?Carbon $oneTimeSlotTo = null): array
	{
		$intervalFuturePickupSignup = $this->getFutureRegularPickupInterval($storeId);
		$from = $from ?? Carbon::now();
		$extendedToDate = Carbon::tomorrow()->add($intervalFuturePickupSignup);
		$to = $to ?? $extendedToDate;
		$regularSlots = $this->getRegularPickups($storeId);
		$onetimeSlots = $this->getOnetimePickupsForRange($storeId, $from, $oneTimeSlotTo);
		$signupsTo = is_null($oneTimeSlotTo) ? null : max($to, $oneTimeSlotTo);
		$signups = $this->getPickupSignupsForDateRange($storeId, $from, $signupsTo);

		$slots = [];
		foreach ($regularSlots as $slot) {
			$date = $from->copy();
			$date->addDays($this->realMod($slot['dow'] - $date->format('w'), 7));
			$date->setTimeFromTimeString($slot['time'])->shiftTimezone('Europe/Berlin');
			if ($date < $from) {
				/* setting time could shift it into past */
				$date->addDays(7);
			}
			while ($date <= $to) {
				if (empty(array_filter($onetimeSlots, function ($e) use ($date) {
					return $date == $e['date'];
				}))) {
					/* only take this regular slot into account when there is no manual slot for the same time */
					$occupiedSlots = array_map(
						function ($e) {
							return ['foodsaverId' => $e['foodsaver_id'], 'isConfirmed' => (bool)$e['confirmed']];
						},
						array_filter($signups,
							function ($e) use ($date) {
								return $date == $e['date'];
							}
						)
					);
					$isAvailable =
						$date > Carbon::now() &&
						$date <= $extendedToDate &&
						$slot['fetcher'] > count($occupiedSlots);
					$slots[] = [
						'date' => $date,
						'totalSlots' => $slot['fetcher'],
						'occupiedSlots' => array_values($occupiedSlots),
						'isAvailable' => $isAvailable
					];
				}

				$date = $date->copy()->addDays(7);
			}
		}
		foreach ($onetimeSlots as $slot) {
			$occupiedSlots = array_map(
				function ($e) {
					return ['foodsaverId' => $e['foodsaver_id'], 'isConfirmed' => (bool)$e['confirmed']];
				},
				array_filter($signups,
					function ($e) use ($slot) {
						return $slot['date'] == $e['date'];
					}
				)
			);
			if ($slot['fetcher'] === 0 && count($occupiedSlots) === 0) {
				/* Do not display empty/cancelled pickups.
				Do show them, when somebody is signed up (although this should not happen) */
				continue;
			}
			/* Onetime slots are always in the future available for signups */
			$isAvailable =
				$slot['date'] > Carbon::now() &&
				$slot['fetcher'] > count($occupiedSlots);
			$slots[] = [
				'date' => $slot['date'],
				'totalSlots' => $slot['fetcher'],
				'occupiedSlots' => array_values($occupiedSlots),
				'isAvailable' => $isAvailable];
		}

		return $slots;
	}

	private function realMod(int $a, int $b)
	{
		$res = $a % $b;
		if ($res < 0) {
			return $res += abs($b);
		}

		return $res;
	}

	public function getStoreStateList()
	{
		return $this->db->fetchAll('
				SELECT
				`id`,
				`name`
				FROM `fs_betrieb_status`
				ORDER BY `name`');
	}

	public function setStoreTeamStatus(int $storeId, int $teamStatus)
	{
		$this->db->update('fs_betrieb', ['team_status' => $teamStatus], ['id' => $storeId]);
	}

	public function getStores()
	{
		return $this->db->fetchAllByCriteria('fs_betrieb', ['id', 'name']);
	}
}
