<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Core\DBConstants\Store\Milestone;
use Foodsharing\Modules\Core\DBConstants\StoreTeam\MembershipStatus;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\DTO\StoreForTopbarMenu;

class StoreGateway extends BaseGateway
{
	private RegionGateway $regionGateway;

	public function __construct(
		Database $db,
		RegionGateway $regionGateway
	) {
		parent::__construct($db);

		$this->regionGateway = $regionGateway;
	}

	public function getBetrieb($storeId): array
	{
		$result = $this->db->fetch('
            SELECT  `id`,
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

            FROM    `fs_betrieb`

            WHERE   `fs_betrieb`.`id` = :id', [':id' => $storeId]);

		$result['verantwortlicher'] = '';
		if ($bezirk = $this->regionGateway->getRegionName($result['bezirk_id'])) {
			$result['bezirk'] = $bezirk;
		}
		if ($verantwortlich = $this->getBiebsForStore($storeId)) {
			$result['verantwortlicher'] = $verantwortlich;
		}
		if ($kette = $this->getOne_kette($result['kette_id'])) {
			$result['kette'] = $kette;
		}

		$result['notizen'] = $this->getStorePosts($storeId);

		return $result;
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

			WHERE 	b.bezirk_id = :regionId
			  AND	b.betrieb_status_id <> :permanentlyClosed
			  AND	b.`lat` != ""
		', [
				':regionId' => $regionId,
				':permanentlyClosed' => CooperationStatus::PERMANENTLY_CLOSED,
		]);
	}

	public function listMyStores(int $fsId): array
	{
		return $this->db->fetchAll('
			SELECT 	b.id,
					b.name,
					b.plz,
					b.stadt,
					b.str,
					b.hsnr

			FROM	fs_betrieb b
					INNER JOIN fs_betrieb_team t
					ON b.id = t.betrieb_id

			WHERE	t.foodsaver_id = :fsId
			AND     t.active = :membershipStatus
		', [
			':fsId' => $fsId,
			':membershipStatus' => MembershipStatus::MEMBER
		]);
	}

	public function getMyStores($fsId, $addFromRegionId = null): array
	{
		$betriebe = $this->db->fetchAll('
			SELECT 	s.id,
					s.betrieb_status_id,
					s.plz,
					s.kette_id,

					s.ansprechpartner,
					s.fax,
					s.telefon,
					s.email,

					s.betrieb_kategorie_id,
					s.name,
					CONCAT(s.str," ",s.hsnr) AS anschrift,
					s.str,
					s.hsnr,
					s.`betrieb_status_id`,
					t.verantwortlich,
					t.active

			FROM 	fs_betrieb s
					INNER JOIN fs_betrieb_team t
			        ON s.id = t.betrieb_id

            WHERE 	t.foodsaver_id = :fsId

			ORDER BY t.verantwortlich DESC, s.name ASC
		', [
			':fsId' => $fsId
		]);

		$result = [];
		$result['verantwortlich'] = [];
		$result['team'] = [];
		$result['waitspringer'] = [];
		$result['requested'] = [];
		$result['sonstige'] = [];

		$already_in = [];

		foreach ($betriebe as $b) {
			$already_in[$b['id']] = true;
			if ($b['verantwortlich'] == 0) {
				if ($b['active'] == MembershipStatus::APPLIED_FOR_TEAM) {
					$result['requested'][] = $b;
				} elseif ($b['active'] == MembershipStatus::MEMBER) {
					$result['team'][] = $b;
				} elseif ($b['active'] == MembershipStatus::JUMPER) {
					$result['waitspringer'][] = $b;
				}
			} else {
				$result['verantwortlich'][] = $b;
			}
		}

		if ($addFromRegionId !== null) {
			$child_region_ids = $this->regionGateway->listIdsForDescendantsAndSelf($addFromRegionId);
			if (!empty($child_region_ids)) {
				$placeholders = $this->db->generatePlaceholders(count($child_region_ids));

				$result['sonstige'] = [];
				$betriebe = $this->db->fetchAll('
					SELECT  b.id,
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

					FROM 	fs_betrieb b
							INNER JOIN fs_bezirk bz
					        ON b.bezirk_id = bz.id

					WHERE 	bezirk_id IN(' . $placeholders . ')

					ORDER BY bz.name DESC
					', $child_region_ids);

				foreach ($betriebe as $b) {
					if (!isset($already_in[$b['id']])) {
						$result['sonstige'][] = $b;
					}
				}
			}
		}

		return $result;
	}

	public function getMyStore($fs_id, $storeId): array
	{
		$result = $this->db->fetch('
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

			FROM 	`fs_betrieb` b
        			LEFT JOIN `fs_abholer` a
        			ON a.betrieb_id = b.id
			AND		a.date < CURDATE()

			WHERE 	b.`id` = :storeId

			GROUP BY b.`id`
        ', [
			':storeId' => $storeId
		]);

		if ($result) {
			$result['lebensmittel'] = $this->getGroceries($storeId);
			$result['foodsaver'] = $this->getStoreTeam($storeId);
			$result['springer'] = $this->getBetriebSpringer($storeId);
			$result['requests'] = $this->getApplications($storeId);
			$result['verantwortlich'] = false;
			$result['team_js'] = [];
			$result['team'] = [];
			$result['jumper'] = false;

			if (!empty($result['springer'])) {
				foreach ($result['springer'] as $v) {
					if ($v['id'] == $fs_id) {
						$result['jumper'] = true;
					}
				}
			}

			if (!empty($result['foodsaver'])) {
				$result['team'] = [];
				foreach ($result['foodsaver'] as $v) {
					$result['team_js'][] = $v['id'];
					$result['team'][] = [
						'id' => $v['id'],
						'value' => $v['name']
					];
					if ($v['verantwortlich'] == 1) {
						$result['verantwortlicher'] = $v['id'];
						if ($v['id'] == $fs_id) {
							$result['verantwortlich'] = true;
						}
					}
				}
			} else {
				$result['foodsaver'] = [];
			}
			$result['team_js'] = implode(',', $result['team_js']);
		}

		return $result;
	}

	private function getGroceries(int $storeId): array
	{
		return $this->db->fetchAll('
        	SELECT  l.`id`,
        			l.name

        	FROM 	`fs_betrieb_has_lebensmittel` hl
        			INNER JOIN `fs_lebensmittel` l
        	        ON l.id = hl.lebensmittel_id

        	WHERE 	`betrieb_id` = :storeId
        ', [
			':storeId' => $storeId
		]);
	}

	private function getApplications(int $storeId): array
	{
		return $this->db->fetchAll('
			SELECT 		fs.`id`,
						fs.photo,
						CONCAT(fs.name," ",fs.nachname) AS name,
						name as vorname,
						fs.sleep_status,
			       		fs.verified

			FROM 		`fs_betrieb_team` t
						INNER JOIN `fs_foodsaver` fs
			            ON fs.id = t.foodsaver_id

			WHERE 		`betrieb_id` = :storeId
			AND 		t.active = :membershipStatus
			AND			fs.deleted_at IS NULL
		', [
			':storeId' => $storeId,
			':membershipStatus' => MembershipStatus::APPLIED_FOR_TEAM
		]);
	}

	public function getStoreName(int $storeId): string
	{
		return $this->db->fetchValueByCriteria('fs_betrieb', 'name', ['id' => $storeId]);
	}

	public function getStoreRegionId(int $storeId): int
	{
		return $this->db->fetchValueByCriteria('fs_betrieb', 'bezirk_id', ['id' => $storeId]);
	}

	public function getStoreCategories(): array
	{
		return $this->db->fetchAll('
			SELECT	`id`,
					`name`
			FROM	`fs_betrieb_kategorie`
			ORDER BY `name`
		');
	}

	public function getBasics_groceries(): array
	{
		return $this->db->fetchAll('
			SELECT 	`id`,
					`name`
			FROM 	`fs_lebensmittel`
			ORDER BY `name`
		');
	}

	public function getBasics_chain(): array
	{
		return $this->db->fetchAll('
			SELECT	`id`,
					`name`
			FROM 	`fs_kette`
			ORDER BY `name`
		');
	}

	public function getStoreTeam($storeId): array
	{
		return $this->db->fetchAll('
				SELECT  fs.`id`,
						fs.`verified`,
						fs.`active`,
						fs.`telefon`,
						fs.`handy`,
						fs.photo,
						fs.quiz_rolle,
						fs.rolle,
						CONCAT(fs.name," ",fs.nachname) AS name,
						name as vorname,
						t.`active` AS team_active,
						t.`verantwortlich`,
						t.`stat_last_update`,
						t.`stat_fetchcount`,
						t.`stat_first_fetch`,
						t.`stat_add_date`,
						UNIX_TIMESTAMP(t.`stat_last_fetch`) AS last_fetch,
						UNIX_TIMESTAMP(t.`stat_add_date`) AS add_date,
						fs.sleep_status

				FROM 	`fs_betrieb_team` t
				INNER JOIN `fs_foodsaver` fs
				     	ON fs.id = t.foodsaver_id

				WHERE	`betrieb_id` = :id
				AND 	t.active  = :membershipStatus
				AND		fs.deleted_at IS NULL

				ORDER BY fs.id
		', [
			':id' => $storeId,
			':membershipStatus' => MembershipStatus::MEMBER
		]);
	}

	public function getBetriebSpringer($storeId): array
	{
		return $this->db->fetchAll('
				SELECT  fs.`id`,
						fs.`verified`,
						fs.`active`,
						fs.`telefon`,
						fs.`handy`,
						fs.photo,
						fs.quiz_rolle,
						fs.rolle,
						CONCAT(fs.name," ",fs.nachname) AS name,
						name as vorname,
						t.`active` AS team_active,
						t.`verantwortlich`,
						t.`stat_last_update`,
						t.`stat_fetchcount`,
						t.`stat_first_fetch`,
						t.`stat_add_date`,
						UNIX_TIMESTAMP(t.`stat_last_fetch`) AS last_fetch,
						UNIX_TIMESTAMP(t.`stat_add_date`) AS add_date,
						fs.sleep_status

				FROM 	`fs_betrieb_team` t
						INNER JOIN `fs_foodsaver` fs
				        ON fs.id = t.foodsaver_id

				WHERE 	`betrieb_id` = :id
				AND 	t.active  = :membershipStatus
				AND		fs.deleted_at IS NULL

				ORDER BY fs.id
		', [
			':id' => $storeId,
			':membershipStatus' => MembershipStatus::JUMPER
		]);
	}

	public function getBiebsForStore($storeId)
	{
		return $this->db->fetchAll('
			SELECT 	`foodsaver_id` as id

			FROM fs_betrieb_team

			WHERE `betrieb_id` = :betrieb_id
			AND verantwortlich = 1
			AND `active` = :membershipStatus
        ', [
			':betrieb_id' => $storeId,
			':membershipStatus' => MembershipStatus::MEMBER
		]);
	}

	/**
	 * Returns all managers of a store.
	 */
	public function getStoreManagers(int $storeId): array
	{
		return $this->db->fetchAllValues('
			SELECT 	t.`foodsaver_id`,
					t.`verantwortlich`

			FROM 	`fs_betrieb_team` t
					INNER JOIN  `fs_foodsaver` fs
					ON fs.id = t.foodsaver_id

			WHERE 	t.`betrieb_id` = :storeId
					AND t.active = :membershipStatus
					AND t.verantwortlich = 1
					AND fs.deleted_at IS NULL
		', [
			':storeId' => $storeId,
			':membershipStatus' => MembershipStatus::MEMBER
		]);
	}

	public function getAllStoreManagers(): array
	{
		$verant = $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`email`

			FROM 	`fs_foodsaver` fs
					INNER JOIN `fs_betrieb_team` bt
			        ON bt.foodsaver_id = fs.id

			WHERE 	bt.verantwortlich = 1
			AND		fs.deleted_at IS NULL
		');

		$result = [];
		foreach ($verant as $v) {
			$result[$v['id']] = $v;
		}

		return $result;
	}

	public function getStoreCountForBieb($fs_id)
	{
		return $this->db->count('fs_betrieb_team', ['foodsaver_id' => $fs_id, 'verantwortlich' => 1]);
	}

	public function getStoreTeamStatus(int $storeId): int
	{
		return $this->db->fetchValueByCriteria('fs_betrieb', 'team_status', ['id' => $storeId]);
	}

	public function getUserTeamStatus(int $userId, int $storeId): int
	{
		$result = $this->db->fetchByCriteria('fs_betrieb_team', [
			'active',
			'verantwortlich'
		], [
			'betrieb_id' => $storeId,
			'foodsaver_id' => $userId
		]);

		if ($result) {
			if ($result['verantwortlich'] && $result['active'] == MembershipStatus::MEMBER) {
				return TeamStatus::Coordinator;
			} else {
				switch ($result['active']) {
					case MembershipStatus::JUMPER:
						return TeamStatus::WaitingList;
					case MembershipStatus::MEMBER:
						return TeamStatus::Member;
					default:
						return TeamStatus::Applied;
				}
			}
		}

		return TeamStatus::NoMember;
	}

	public function getBetriebConversation(int $storeId, bool $springerConversation = false): ?int
	{
		if ($springerConversation) {
			$chatType = 'springer_conversation_id';
		} else {
			$chatType = 'team_conversation_id';
		}

		return $this->db->fetchValueByCriteria('fs_betrieb', $chatType, ['id' => $storeId]);
	}

	// TODO clean up data handling (use a DTO)
	// TODO eventually, switch to wallpost system
	public function addStoreWallpost(array $data): int
	{
		return $this->db->insert('fs_betrieb_notiz', [
			'foodsaver_id' => $data['foodsaver_id'],
			'betrieb_id' => $data['betrieb_id'],
			'milestone' => Milestone::NONE,
			'text' => $data['text'],
			'zeit' => $data['zeit'],
			'last' => 0, // TODO remove this column entirely
		]);
	}

	// TODO rename to addStoreMilestone and clean up data handling
	public function add_betrieb_notiz(array $data): int
	{
		return $this->db->insert('fs_betrieb_notiz', [
			'foodsaver_id' => $data['foodsaver_id'],
			'betrieb_id' => $data['betrieb_id'],
			'milestone' => $data['milestone'],
			'text' => strip_tags($data['text']),
			'zeit' => $data['zeit'],
			'last' => 0, // TODO remove this column entirely
		]);
	}

	public function deleteStoreWallpost(int $storeId, int $postId): int
	{
		return $this->db->delete('fs_betrieb_notiz', ['id' => $postId, 'betrieb_id' => $storeId]);
	}

	/**
	 * retrieves all store managers for a given region (by being store manager in a store that is part of that region,
	 * which is semantically not the same we use on platform).
	 */
	public function getStoreManagersOf(int $regionId): array
	{
		return $this->db->fetchAllValues('
            SELECT DISTINCT
                    bt.foodsaver_id

            FROM    `fs_bezirk_closure` c
			        INNER JOIN `fs_betrieb` b
                    ON c.bezirk_id = b.bezirk_id
			            INNER JOIN `fs_betrieb_team` bt
                        ON bt.betrieb_id = b.id
			                INNER JOIN `fs_foodsaver` fs
                            ON fs.id = bt.foodsaver_id

			WHERE   c.ancestor_id = :regionId
            AND     bt.verantwortlich = 1
            AND     fs.deleted_at IS NULL
        ', [
			':regionId' => $regionId
		]);
	}

	/**
	 * @return StoreForTopbarMenu[]
	 */
	public function listFilteredStoresForFoodsaver($fsId): array
	{
		$rows = $this->db->fetchAll('
			SELECT 	b.`id`,
					b.name,
					bt.verantwortlich AS managing

			FROM 	`fs_betrieb_team` bt
					INNER JOIN `fs_betrieb` b
			        ON bt.betrieb_id = b.id

			WHERE   bt.`foodsaver_id` = :fsId
			AND 	bt.active = :membershipStatus
			AND 	b.betrieb_status_id NOT IN (:doesNotWantToWorkWithUs, :givesToOtherCharity)
			ORDER BY bt.verantwortlich DESC, b.name ASC
		', [
			':fsId' => $fsId,
			':membershipStatus' => MembershipStatus::MEMBER,
			':doesNotWantToWorkWithUs' => CooperationStatus::DOES_NOT_WANT_TO_WORK_WITH_US,
			':givesToOtherCharity' => CooperationStatus::GIVES_TO_OTHER_CHARITY
		]);

		$stores = [];
		foreach ($rows as $row) {
			$store = new StoreForTopbarMenu();
			$store->id = $row['id'];
			$store->name = $row['name'];
			$store->isManaging = $row['managing'];
			$stores[] = $store;
		}

		return $stores;
	}

	public function listStoreIdsForBieb($fsId)
	{
		return $this->db->fetchAllByCriteria('fs_betrieb_team', ['betrieb_id'], ['foodsaver_id' => $fsId, 'verantwortlich' => 1]);
	}

	private function getOne_kette($id): array
	{
		return $this->db->fetch('
			SELECT   `id`,
			         `name`,
			         `logo`

			FROM     `fs_kette`

			WHERE    `id` = :id
        ', [
			':id' => $id
		]);
	}

	/**
	 * Returns the store comment with the specified ID.
	 */
	public function getStoreWallpost(int $storeId, int $postId): array
	{
		return $this->db->fetchByCriteria('fs_betrieb_notiz',
			['id', 'foodsaver_id', 'betrieb_id', 'text', 'zeit'],
			['id' => $postId, 'betrieb_id' => $storeId]
		);
	}

	/**
	 * Returns all comments for a given store.
	 */
	public function getStorePosts(int $storeId, int $offset = 0, int $limit = 50): array
	{
		return $this->db->fetchAll('
			SELECT sn.`id`,
			       sn.`foodsaver_id`,
				   fs.`photo`,
				   CONCAT(fs.`name`," ",fs.`nachname`) AS name,
			       sn.`betrieb_id`,
			       sn.`text`,
			       sn.`milestone`,
			       sn.`zeit`

			FROM `fs_betrieb_notiz` sn
				INNER JOIN fs_foodsaver fs
				ON         fs.id = sn.foodsaver_id

			WHERE  sn.`betrieb_id` = :storeId
			AND    sn.`milestone` = :noMilestone

			ORDER BY sn.`zeit` DESC
			LIMIT :offset, :limit
		', [
			':storeId' => $storeId,
			':noMilestone' => Milestone::NONE,
			':offset' => $offset,
			':limit' => $limit,
		]);
	}

	public function updateStoreRegion(int $storeId, int $regionId): int
	{
		return $this->db->update('fs_betrieb', [
			'bezirk_id' => $regionId
		], [
			'id' => $storeId
		]);
	}

	public function getStoreByConversationId(int $id): ?array
	{
		$store = $this->db->fetch('
			SELECT	id,
					name

			FROM	fs_betrieb

			WHERE	team_conversation_id = :memberId
			OR      springer_conversation_id = :jumperId
		', [
			':memberId' => $id,
			':jumperId' => $id
		]);

		return $store;
	}

	public function addStoreLog(
		int $store_id,
		int $foodsaver_id,
		?int $fs_id_p,
		?\DateTimeInterface $dateReference,
		int $action,
		?string $content = null,
		?string $reason = null
	) {
		return $this->db->insert('fs_store_log', [
			'store_id' => $store_id,
			'action' => $action,
			'fs_id_a' => $foodsaver_id,
			'fs_id_p' => $fs_id_p,
			'date_reference' => $dateReference ? $dateReference->format('Y-m-d H:i:s') : null,
			'content' => strip_tags($content),
			'reason' => strip_tags($reason),
		]);
	}

	public function getStoreStateList(): array
	{
		return [
			['id' => '1', 'name' => 'Es besteht noch kein Kontakt'],
			['id' => '2', 'name' => 'Verhandlungen laufen'],
			['id' => '3', 'name' => 'Betrieb ist bereit zu spenden :-)'],
			['id' => '4', 'name' => 'Betrieb will nicht kooperieren'],
			['id' => '5', 'name' => 'Betrieb spendet bereits'],
			['id' => '6', 'name' => 'spendet an Tafel etc. & wirft nichts weg'],
			['id' => '7', 'name' => 'Betrieb existiert nicht mehr :-('],
		];
	}

	public function setStoreTeamStatus(int $storeId, int $teamStatus)
	{
		$this->db->update('fs_betrieb', ['team_status' => $teamStatus], ['id' => $storeId]);
	}

	public function getStores()
	{
		return $this->db->fetchAllByCriteria('fs_betrieb', ['id', 'name']);
	}

	/**
	 * Add store manager to a store and make her responsible for that store.
	 *
	 * @return int	Last insert ID
	 */
	public function addStoreManager(int $storeId, int $storeManagerId): int
	{
		$data = [
			'foodsaver_id' => $storeManagerId,
			'betrieb_id' => $storeId,
			'verantwortlich' => 1,
			'active' => 1,
		];

		return $this->db->insertOrUpdate('fs_betrieb_team', $data);
	}

	public function addUserToTeam(int $storeId, int $userId): void
	{
		$this->db->update('fs_betrieb_team', [
			'active' => MembershipStatus::MEMBER,
			'stat_add_date' => $this->db->now()
		], [
			'betrieb_id' => $storeId,
			'foodsaver_id' => $userId
		]);
	}

	public function removeUserFromTeam(int $storeId, int $userId): void
	{
		$this->db->delete('fs_betrieb_team', [
			'betrieb_id' => $storeId,
			'foodsaver_id' => $userId
		]);
	}

	public function listStoresInRegion(int $regionId, bool $includeSubregions = false): array
	{
		if ($includeSubregions) {
			$regionIds = $this->regionGateway->listIdsForDescendantsAndSelf($regionId);
		} else {
			$regionIds = [$regionId];
		}

		return $this->db->fetchAll('
				SELECT 	fs_betrieb.id,
						`fs_betrieb`.betrieb_status_id,
						fs_betrieb.plz,
						fs_betrieb.added,
						`stadt`,
						fs_betrieb.kette_id,
						fs_betrieb.betrieb_kategorie_id,
						fs_betrieb.name,
						CONCAT(fs_betrieb.str," ",fs_betrieb.hsnr) AS anschrift,
						fs_betrieb.str,
						fs_betrieb.hsnr,
						CONCAT(fs_betrieb.lat,", ",fs_betrieb.lon) AS geo,
						fs_betrieb.`betrieb_status_id`,
						fs_bezirk.name AS bezirk_name

				FROM 	fs_betrieb,
						fs_bezirk

				WHERE 	fs_betrieb.bezirk_id = fs_bezirk.id
				AND 	fs_betrieb.bezirk_id IN(' . implode(',', $regionIds) . ')
		');
	}
}
