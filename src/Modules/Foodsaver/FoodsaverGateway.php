<?php

namespace Foodsharing\Modules\Foodsaver;

use Carbon\Carbon;
use Exception;
use Foodsharing\Helpers\DataHelper;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Region\ForumFollowerGateway;
use Foodsharing\Modules\Store\StoreModel;

final class FoodsaverGateway extends BaseGateway
{
	private $dataHelper;
	private $forumFollowerGateway;

	public function __construct(
		Database $db,
		DataHelper $dataHelper,
		ForumFollowerGateway $forumFollowerGateway
	) {
		parent::__construct($db);

		$this->dataHelper = $dataHelper;
		$this->forumFollowerGateway = $forumFollowerGateway;
	}

	public function getFoodsaversByRegion(int $regionId, bool $hideRecentlyOnline = false): array
	{
		$onlyInactiveClause = '';
		if ($hideRecentlyOnline) {
			$oldestActiveDate = Carbon::now()->subMonths(6)->format('Y-m-d H:i:s');
			$onlyInactiveClause = '
				AND (fs.last_login < "' . $oldestActiveDate . '"
					OR fs.last_login IS NULL)
			';
		}

		return $this->db->fetchAll('
		    SELECT	fs.id,
					fs.name,
					fs.nachname,
					fs.photo,
					fs.sleep_status,
					CONCAT("#",fs.id) AS href

		    FROM	fs_foodsaver fs
					INNER JOIN fs_foodsaver_has_bezirk fsreg
					ON fs.id = fsreg.foodsaver_id

		    WHERE   fs.deleted_at IS NULL
			AND     fsreg.bezirk_id = :regionId'
					. $onlyInactiveClause . '

			ORDER BY fs.name ASC
		', [
			':regionId' => $regionId
		]);
	}

	public function listActiveFoodsaversByRegion(int $regionId): array
	{
		$res = $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`photo`,
					fs.`name`,
					fs.sleep_status

		    FROM	fs_foodsaver fs
					INNER JOIN fs_foodsaver_has_bezirk fsreg
					ON fs.id = fsreg.foodsaver_id

			WHERE   fs.deleted_at IS NULL
			AND 	fsreg.active = 1
			AND 	fsreg.bezirk_id = :regionId

			ORDER BY fs.`name`
		', [
			':regionId' => $regionId
		]);

		return array_map(function ($fs) {
			if ($fs['photo']) {
				$image = '/images/50_q_' . $fs['photo'];
			} else {
				$image = '/img/50_q_avatar.png';
			}

			return [
				'user' => [
					'id' => $fs['id'],
					'name' => $fs['name'],
					'sleep_status' => $fs['sleep_status']
				],
				'size' => 50,
				'imageUrl' => $image
			];
		}, $res);
	}

	public function listActiveWithFullNameByRegion(int $regionId): array
	{
		return $this->db->fetchAll('
			SELECT 	fs.id,
					CONCAT(fs.`name`, " ", fs.`nachname`) AS `name`,
					fs.`email`,
					fs.`geschlecht`

		    FROM	fs_foodsaver fs
					INNER JOIN fs_foodsaver_has_bezirk fsreg
					ON fs.id = fsreg.foodsaver_id

			WHERE   fs.deleted_at IS NULL
			AND 	fs.last_login >= CURDATE() - INTERVAL 6 MONTH
			AND 	fsreg.active = 1
			AND 	fsreg.bezirk_id = :regionId
		', [
			':regionId' => $regionId
		]);
	}

	public function getFoodsaverDetails(int $fsId): array
	{
		return $this->db->fetchByCriteria(
			'fs_foodsaver',
			[
				'id',
				'admin',
				'orgateam',
				'bezirk_id',
				'photo',
				'rolle',
				'type',
				'verified',
				'name',
				'nachname',
				'lat',
				'lon',
				'email',
				'token',
				'mailbox_id',
				'option',
				'geschlecht',
				'privacy_policy_accepted_date',
				'privacy_notice_accepted_date'
			], [
			'id' => $fsId
		]);
	}

	public function getFoodsaverBasics(int $fsId): array
	{
		$fs = $this->db->fetchByCriteria('fs_foodsaver', [
			'id',
			'name',
			'nachname',
			'bezirk_id',
			'rolle',
			'photo',
			'geschlecht',
			'stat_fetchweight',
			'stat_fetchcount',
			'sleep_status'
		], [
			'id' => $fsId
		]);
		if ($fs) {
			$fs['bezirk_name'] = '';
			if ($fs['bezirk_id'] > 0) {
				$fs['bezirk_name'] = $this->db->fetchValueByCriteria('fs_bezirk', 'name', [
					'id' => $fs['bezirk_id']
				]);
			}

			return $fs;
		}

		return [];
	}

	public function getFoodsaversWithoutAmbassadors(): array
	{
		$foodsavers = $this->getActiveFoodsavers();
		$ambassadors = $this->getActiveAmbassadors();

		return array_udiff($foodsavers, $ambassadors, function (array $fs, array $amb) {
			return $fs['id'] - $amb['id'];
		});
	}

	private function getActiveFoodsavers(): array
	{
		return $this->db->fetchAll('
			SELECT  fs.id,
					CONCAT(fs.`name`, " ", fs.`nachname`) AS `name`,
					fs.`anschrift`,
					fs.`email`,
					fs.`telefon`,
					fs.`handy`,
					fs.plz

			FROM 	`fs_foodsaver` fs

			WHERE	fs.deleted_at IS NULL
            AND     fs.`active` = 1
		');
	}

	public function getFoodsaver(int $fsId): array
	{
		$out = $this->db->fetchByCriteria('fs_foodsaver', [
			'id',
			'bezirk_id',
			'plz',
			'stadt',
			'lat',
			'lon',
			'email',
			'name',
			'nachname',
			'anschrift',
			'telefon',
			'handy',
			'geschlecht',
			'geb_datum',
			'anmeldedatum',
			'photo',
			'about_me_intern',
			'about_me_public',
			'orgateam',
			'data',
			'rolle',
			'position',
			'homepage'
		], [
			'id' => $fsId
		]);

		if ($bot = $this->getAmbassadorsRegions($fsId)) {
			$out['botschafter'] = $bot;
		}

		return $out;
	}

	private function getAmbassadorsRegions(int $fsId): array
	{
		return $this->db->fetchAll('
			SELECT   reg.`name`,
                     reg.`id`

			FROM     fs_bezirk reg
				     INNER JOIN fs_botschafter amb
                     ON amb.`bezirk_id` = reg.`id`

			WHERE    amb.foodsaver_id = :fsId
        ', [
			':fsId' => $fsId
		]);
	}

	public function getAdminsOrAmbassadors(int $groupId): array
	{
		return $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`name`,
					fs.`name` AS `vorname`,
					fs.`nachname`,
					fs.`photo`,
					fs.`email`,
					fs.`geschlecht`,
					fs.`sleep_status`

			FROM    `fs_foodsaver` fs
			        INNER JOIN `fs_botschafter` amb
                    ON fs.id = amb.`foodsaver_id`

			WHERE amb.`bezirk_id` = :regionId
			AND		fs.deleted_at IS NULL',
			[':regionId' => $groupId]
		);
	}

	public function getActiveAmbassadors(): array
	{
		return $this->db->fetchAll('
			SELECT  fs.`id`,
					fs.`name`,
					fs.`nachname`,
					fs.`geschlecht`,
					fs.`email`

			FROM 	`fs_foodsaver` fs
                    JOIN `fs_botschafter` amb
                    ON fs.id = amb.foodsaver_id
                        LEFT JOIN `fs_bezirk` reg
                        ON amb.bezirk_id = reg.id

			WHERE	reg.type != :excludedRegionType
			AND     fs.deleted_at IS NULL
            AND     fs.`active` = 1
        ', [
			':excludedRegionType' => Type::WORKING_GROUP
		]);
	}

	public function isAdminForAnyGroupOrRegion(int $fsId): bool
	{
		return $this->db->count('fs_botschafter', ['foodsaver_id' => $fsId]) > 0;
	}

	public function getOrgateam(): array
	{
		return $this->db->fetchAllByCriteria('fs_foodsaver', [
			'id',
			'name',
			'nachname',
			'geschlecht',
			'email'
		], [
			'orgateam' => 1
		]);
	}

	public function getFsMap(int $regionId): array
	{
		return $this->db->fetchAll('
            SELECT  `id`,
                    `lat`,
                    `lon`,
                    CONCAT(`name`," ",`nachname`) AS `name`,
                    `plz`,
                    `stadt`,
                    `anschrift`,
                    `photo`

			FROM    `fs_foodsaver`

			WHERE   `active` = 1
			AND     `bezirk_id` = :regionId
			AND     `lat` != ""
        ', [
			':regionId' => $regionId
		]);
	}

	public function xhrGetFoodsaversOfRegionsForTagSelect(array $regionIds): array
	{
		return $this->db->fetchAll('
			SELECT DISTINCT
					fs.`id`,
					CONCAT(fs.`name`," ",fs.`nachname`," (",fs.`id`,")") AS value

			FROM 	fs_foodsaver fs
					INNER JOIN fs_foodsaver_has_bezirk hb
			        ON hb.foodsaver_id = fs.id

			WHERE 	hb.bezirk_id IN(' . $this->dataHelper->commaSeparatedIds($regionIds) . ')
			AND		fs.deleted_at IS NULL
		');
	}

	public function xhrGetFoodsaver(array $data): array
	{
		if (isset($data['bid'])) {
			throw new Exception('filterung by bezirkIds is not supported anymore');
		}

		$term = $data['term'];
		$term = trim($term);
		$term = preg_replace('/[^a-zA-ZäöüÖÜß]/', '', $term);
		$term = $term . '%';

		if (strlen($term) > 2) {
			$out = $this->db->fetchAll('
				SELECT	`id`,
						CONCAT_WS(" ", `name`, `nachname`, CONCAT("(", `id`, ")")) AS value

				FROM 	fs_foodsaver

				WHERE 	(`name` LIKE :term
						OR	`nachname` LIKE :term2)
				AND     deleted_at IS NULL
			', [
				':term' => $term,
				':term2' => $term
			]);

			return $out;
		}

		return [];
	}

	public function getEmailAddress(int $fsId): string
	{
		return $this->db->fetchValueByCriteria('fs_foodsaver', 'email', ['id' => $fsId]);
	}

	public function getEmailAddressesFromMainRegions(array $regionIds): array
	{
		return $this->getEmailAddresses(Role::FOODSHARER, Role::ORGA, ['bezirk_id' => $regionIds]);
	}

	public function getNewsletterSubscribersEmailAddresses(int $minRole = Role::FOODSHARER, int $maxRole = Role::ORGA, array $criteria = []): array
	{
		return $this->getEmailAddresses($minRole, $maxRole, [
			'newsletter' => 1
		]);
	}

	public function getEmailAddresses(int $minRole = Role::FOODSHARER, int $maxRole = Role::ORGA, array $criteria = []): array
	{
		$foodsavers = $this->db->fetchAllByCriteria(
			'fs_foodsaver',
			[
				'id',
				'email'
			],
			array_merge([
				'active' => 1,
				'deleted_at' => null,
				'rolle >=' => $minRole,
				'rolle <=' => $maxRole
			], $criteria)
		);

		return $this->dataHelper->useIdAsKey($foodsavers);
	}

	public function getRegionAmbassadorsEmailAddresses(array $regionIds): array
	{
		$foodsavers = $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`email`

			FROM 	`fs_foodsaver` fs
					INNER JOIN `fs_botschafter` b
					ON b.foodsaver_id = fs.id

			WHERE 	fs.deleted_at IS NULL
            AND     b.`bezirk_id` > 0
			AND     b.`bezirk_id` IN(' . $this->dataHelper->commaSeparatedIds($regionIds) . ')
		');

		return $this->dataHelper->useIdAsKey($foodsavers);
	}

	public function getEmailAddressesFromRegions(array $regionIds): array
	{
		$foodsavers = $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`email`

			FROM 	`fs_foodsaver` fs
					INNER JOIN `fs_foodsaver_has_bezirk` b
					ON b.foodsaver_id = fs.id

			WHERE 	fs.deleted_at IS NULL
			AND     b.`bezirk_id` > 0
			AND     b.`bezirk_id` IN(' . $this->dataHelper->commaSeparatedIds($regionIds) . ')
		');

		return $this->dataHelper->useIdAsKey($foodsavers);
	}

	public function updateGroupMembers(int $regionId, array $fsIds, bool $keepAdmins): array
	{
		if ($keepAdmins) {
			if ($admins = $this->db->fetchAllValuesByCriteria('fs_botschafter', 'foodsaver_id', ['bezirk_id' => $regionId])) {
				$fsIds = array_merge($fsIds, $admins);
			}
		}

		$updateCounts = ['inserts' => 0, 'deletions' => 0];
		if ($fsIds) {
			$updateCounts['deletions'] = $this->deleteGroupMembers($regionId, $fsIds);
			$updateCounts['inserts'] = $this->insertGroupMembers($regionId, $fsIds);
		} else {
			$updateCounts['deletions'] = $this->deleteGroupMembers($regionId);
		}

		return $updateCounts;
	}

	private function deleteGroupMembers(int $regionId, array $remainingMemberIds = []): int
	{
		$this->forumFollowerGateway->deleteForumSubscriptions($regionId, $remainingMemberIds, false);

		if ($remainingMemberIds) {
			$delCount = 0;
			$preGroupMembers = $this->db->fetchAllValuesByCriteria('fs_foodsaver_has_bezirk', 'foodsaver_id', [
				'bezirk_id' => $regionId
			]);
			foreach ($preGroupMembers as $fsId) {
				if (!in_array($fsId, $remainingMemberIds)) {
					$delCount += $this->db->delete(
						'fs_foodsaver_has_bezirk',
						['bezirk_id' => $regionId, 'foodsaver_id' => $fsId]
					);
				}
			}

			return $delCount;
		}

		return $this->db->delete('fs_foodsaver_has_bezirk', ['bezirk_id' => $regionId]);
	}

	private function insertGroupMembers(int $regionId, array $fsIds): int
	{
		$before = $this->db->count('fs_foodsaver_has_bezirk', ['bezirk_id' => $regionId]);
		foreach ($fsIds as $fsId) {
			$this->db->insertIgnore(
				'fs_foodsaver_has_bezirk',
				[
					'foodsaver_id' => $fsId,
					'bezirk_id' => $regionId,
					'active' => 1,
					'added' => $this->db->now()
				]
			);
		}
		$current = $this->db->count('fs_foodsaver_has_bezirk', ['bezirk_id' => $regionId]);

		return $current - $before;
	}

	public function getAllWorkGroupAmbassadorIds(): array
	{
		return $this->getAmbassadorIds(RegionIDs::ROOT, false, true);
	}

	public function getRegionAmbassadorIds(int $regionId): array
	{
		return $this->getAmbassadorIds($regionId);
	}

	/**
	 * Retrieves the list of all ambassador for a given region or district.
	 *
	 * Because the region data model holds both, <i>regions</i> <b>and</b> <i>work groups</i>,
	 * one can decide which one to query via flag parameters.
	 *
	 * @param int $regionId The region ID
	 * @param bool $includeRegionAmbassador "Real" regions shall be queried
	 * @param bool $includeGroupAmbassador Work groups shall be queried. If <code>$includeRegionAmbassador</code> is <code>false</code>,
	 *     this is implicitely handled as <code>true</code>.
	 */
	private function getAmbassadorIds(int $regionId, bool $includeRegionAmbassador = true, bool $includeGroupAmbassador = false): array
	{
		$sql = '
			SELECT DISTINCT
					amb.foodsaver_id

			FROM	`fs_bezirk_closure` rc
					LEFT JOIN `fs_bezirk` reg
					ON rc.bezirk_id = reg.id
						INNER JOIN `fs_botschafter` amb
						ON rc.bezirk_id = amb.bezirk_id
							INNER JOIN `fs_foodsaver` fs
							ON amb.foodsaver_id = fs.id

			WHERE  (rc.ancestor_id = :ancestorId
                    OR rc.bezirk_id = :regionId)
			AND		fs.deleted_at IS NULL
		';

		if (!$includeRegionAmbassador) {
			$sql .= ' AND reg.type = ' . Type::WORKING_GROUP;
		} elseif (!$includeGroupAmbassador) {
			$sql .= ' AND reg.type != ' . Type::WORKING_GROUP;
		}

		return $this->db->fetchAllValues(
			$sql, [
			':ancestorId' => $regionId,
			':regionId' => $regionId
		]);
	}

	public function deleteFoodsaver(int $fsId): void
	{
		$this->db->update('fs_foodsaver', ['password' => null, 'deleted_at' => $this->db->now()], ['id' => $fsId]);

		$this->archiveFoodsaver($fsId);

		$this->db->delete('fs_apitoken', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_application_has_wallpost', ['application_id' => $fsId]);
		$this->db->delete('fs_basket_anfrage', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_botschafter', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_buddy', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_buddy', ['buddy_id' => $fsId]);
		$this->db->delete('fs_email_status', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_fairteiler_follower', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_foodsaver_has_bell', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_foodsaver_has_bezirk', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_foodsaver_has_contact', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_foodsaver_has_event', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_foodsaver_has_wallpost', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_mailbox_member', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_mailchange', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_pass_gen', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_pass_gen', ['bot_id' => $fsId]);
		$this->db->delete('fs_pass_request', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_quiz_session', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_rating', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_rating', ['rater_id' => $fsId]);
		$this->db->delete('fs_theme_follower', ['foodsaver_id' => $fsId]);

		$this->db->update(
			'fs_foodsaver',
			[
				'verified' => 0,
				'rolle' => 0,
				'plz' => null,
				'stadt' => null,
				'lat' => null,
				'lon' => null,
				'photo' => null,
				'email' => null,
				'password' => null,
				'name' => null,
				'nachname' => null,
				'anschrift' => null,
				'telefon' => null,
				'handy' => null,
				'geb_datum' => null,
				'deleted_at' => $this->db->now()
			], [
			'id' => $fsId
		]);
	}

	private function archiveFoodsaver(int $fsId): void
	{
		$foodsaver = $this->db->fetchByCriteria('fs_foodsaver', '*', [
			'id' => $fsId
		]);

		$this->db->insert('fs_foodsaver_archive', $foodsaver);
	}

	public function getFsAutocomplete(array $regions): array
	{
		if (is_array(end($regions))) {
			$tmp = [];
			foreach ($regions as $r) {
				$tmp[] = $r['id'];
			}
			$regions = $tmp;
		}

		return $this->db->fetchAll('
			SELECT DISTINCT
						fs.id,
						CONCAT(fs.`name`, " ", fs.`nachname`, " (",fs.`id`,")") AS value

			FROM 	`fs_foodsaver` fs
					INNER JOIN fs_foodsaver_has_bezirk fb
					ON fs.id = fb.foodsaver_id

			WHERE 	fs.deleted_at IS NULL
			AND		fb.`bezirk_id` IN(' . $this->dataHelper->commaSeparatedIds($regions) . ')'
		);
	}

	public function updateProfile(int $fsId, array $data): bool
	{
		$fields = [
			'bezirk_id',
			'plz',
			'lat',
			'lon',
			'stadt',
			'anschrift',
			'telefon',
			'handy',
			'geb_datum',
			'about_me_intern',
			'about_me_public',
			'homepage',
			'position'
		];

		$fieldsToStripTags = [
			'plz',
			'lat',
			'lon',
			'stadt',
			'anschrift',
			'telefon',
			'handy',
			'about_me_intern',
			'about_me_public',
			'homepage',
			'position'
		];

		$clean_data = [];
		foreach ($fields as $field) {
			if (array_key_exists($field, $data)) {
				$clean_data[$field] = in_array($field, $fieldsToStripTags, true) ? strip_tags($data[$field]) : $data[$field];
			}
		}

		$this->db->update('fs_foodsaver', $clean_data, [
			'id' => $fsId
		]);

		return true;
	}

	public function updatePhoto(int $fsId, string $photo): void
	{
		$this->db->update('fs_foodsaver', [
			'photo' => strip_tags($photo)
		], [
			'id' => $fsId
		]);
	}

	public function getPhotoFileName(int $fsId): string
	{
		if ($photo = $this->db->fetchValueByCriteria('fs_foodsaver', 'photo', ['id' => $fsId])) {
			return $photo;
		}

		return '';
	}

	public function emailExists(string $email): bool
	{
		return $this->db->exists('fs_foodsaver', ['email' => $email]);
	}

	/**
	 * set option is an key value store each var is available in the user session.
	 *
	 * @param $val
	 */
	public function setOption(int $fsId, string $key, $val): int
	{
		$options = [];
		if ($opt = $this->db->fetchValueByCriteria('fs_foodsaver', 'option', ['id' => $fsId])) {
			$options = unserialize($opt);
		}

		$options[$key] = $val;

		return $this->db->update('fs_foodsaver', [
			'option' => serialize($options)
		], [
			'id' => $fsId
		]);
	}

	public function deleteFromRegion(int $regionId, int $fsId): void
	{
		$this->db->delete('fs_botschafter', ['bezirk_id' => $regionId, 'foodsaver_id' => $fsId]);
		$this->db->delete('fs_foodsaver_has_bezirk', ['bezirk_id' => $regionId, 'foodsaver_id' => $fsId]);

		$this->forumFollowerGateway->deleteForumSubscription($regionId, $fsId);

		$mainRegion_id = $this->db->fetchValueByCriteria('fs_foodsaver', 'bezirk_id', ['id' => $fsId]);
		if ($mainRegion_id === $regionId) {
			$this->db->update('fs_foodsaver', [
				'bezirk_id' => 0
			], [
				'id' => $fsId
			]);
		}
	}

	public function setQuizRole(int $fsId, int $quizRole): int
	{
		return $this->db->update('fs_foodsaver', [
			'quiz_rolle' => $quizRole
		], [
			'id' => $fsId
		]);
	}

	public function riseRole(int $fsId, int $newRoleId): void
	{
		$this->db->update(
			'fs_foodsaver',
			['rolle' => $newRoleId],
			[
				'id' => $fsId,
				'rolle <' => $newRoleId
			]
		);
	}

	public function loadFoodsaver(int $foodsaverId): array
	{
		return $this->db->fetchByCriteria('fs_foodsaver', [
			'id',
			'name',
			'nachname',
			'photo',
			'rolle',
			'geschlecht',
			'last_login'
		], [
			'id' => $foodsaverId,
			'deleted_at' => null
		]);
	}

	public function updateFoodsaver(int $fsId, array $data): int
	{
		$updateData = [
			'bezirk_id' => $data['bezirk_id'],
			'plz' => strip_tags(trim($data['plz'])),
			'stadt' => strip_tags(trim($data['stadt'])),
			'lat' => strip_tags(trim($data['lat'])),
			'lon' => strip_tags(trim($data['lon'])),
			'name' => strip_tags($data['name']),
			'nachname' => strip_tags($data['nachname']),
			'anschrift' => strip_tags($data['anschrift']),
			'telefon' => strip_tags($data['telefon']),
			'handy' => strip_tags($data['handy']),
			'geschlecht' => $data['geschlecht'],
			'geb_datum' => $data['geb_datum']
		];

		if (isset($data['position'])) {
			$updateData['position'] = strip_tags($data['position']);
		}

		if (isset($data['email'])) {
			$updateData['email'] = strip_tags($data['email']);
		}

		if (isset($data['orgateam'])) {
			$updateData['orgateam'] = $data['orgateam'];
		}

		if (isset($data['rolle'])) {
			$updateData['rolle'] = $data['rolle'];
		}

		return $this->db->update('fs_foodsaver', $updateData, [
			'id' => $fsId
		]);
	}

	public function downgradePermanently(int $fsId, StoreModel $storeModel): int
	{
		$this->signOutFromStores($fsId, $storeModel);

		$this->db->delete('fs_foodsaver_has_bell', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_foodsaver_has_bezirk', ['foodsaver_id' => $fsId]);
		$this->db->delete('fs_botschafter', ['foodsaver_id' => $fsId]);

		$fsUpdateData['rolle'] = Role::FOODSHARER;
		$fsUpdateData['bezirk_id'] = 0;
		$fsUpdateData['quiz_rolle'] = Role::FOODSHARER;
		$fsUpdateData['verified'] = 0;

		return $this->db->update('fs_foodsaver', $fsUpdateData, [
			'id' => $fsId
		]);
	}

	private function signOutFromStores(int $fsId, StoreModel $storeModel): void
	{
		$storeIds = $this->db->fetchAllValuesByCriteria('fs_betrieb_team', 'betrieb_id', [
			'foodsaver_id' => $fsId
		]);

		foreach ($storeIds as $storeId) {
			$storeModel->signout($storeId, $fsId);
		}
	}

	public function getFoodsaverAddress(int $foodsaverId): array
	{
		return $this->db->fetchByCriteria(
			'fs_foodsaver',
			[
				'plz',
				'stadt',
				'lat',
				'lon',
				'anschrift',
			],
			['id' => $foodsaverId]
		);
	}

	public function getSubscriptions(int $fsId): array
	{
		return $this->db->fetchByCriteria(
			'fs_foodsaver',
			[
				'infomail_message',
				'newsletter'
			],
			['id' => $fsId]
		);
	}

	public function getProfileForUsers(array $fsIds): array
	{
		$res = $this->db->fetchAllByCriteria(
			'fs_foodsaver',
			['id', 'name', 'photo', 'sleep_status'],
			['id' => $fsIds]);

		$profiles = [];
		foreach ($res as $p) {
			$profile = new Profile();
			$profile->id = $p['id'];
			$profile->name = $p['name'];
			$profile->avatar = $p['photo'];
			$profile->sleepStatus = $p['sleep_status'];
			$profiles[$p['id']] = $profile;
		}

		return $profiles;
	}

	/**
	 * Returns the first name of the foodsaver.
	 */
	public function getFoodsaverName($foodsaverId): string
	{
		return $this->db->fetchValueByCriteria('fs_foodsaver', 'name', ['id' => $foodsaverId, 'deleted_at' => null]);
	}

	public function foodsaverExists($foodsaverId): bool
	{
		return $this->foodsaversExist([$foodsaverId]);
	}

	public function foodsaversExist(array $foodsaverIds): bool
	{
		$foodsaverIds = array_unique($foodsaverIds);
		$existing = $this->db->fetchAllValuesByCriteria('fs_foodsaver', 'id', ['id' => $foodsaverIds, 'deleted_at' => null]);

		return count($foodsaverIds) === count($existing);
	}
}
