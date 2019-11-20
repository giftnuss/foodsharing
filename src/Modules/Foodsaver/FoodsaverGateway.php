<?php

namespace Foodsharing\Modules\Foodsaver;

use Carbon\Carbon;
use Exception;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Quiz\QuizSessionGateway;
use Foodsharing\Modules\Region\ForumFollowerGateway;
use Foodsharing\Modules\Store\StoreModel;

final class FoodsaverGateway extends BaseGateway
{
	private $forumFollowerGateway;
	private $quizSessionGateway;

	public function __construct(
		Database $db,
		ForumFollowerGateway $forumFollowerGateway,
		QuizSessionGateway $quizSessionGateway
	) {
		parent::__construct($db);

		$this->forumFollowerGateway = $forumFollowerGateway;
		$this->quizSessionGateway = $quizSessionGateway;
	}

	public function getFoodsaver(int $regionId): array
	{
		$and = $regionId ? ' AND fb.`bezirk_id` = ' . $regionId : '';

		return $this->db->fetchAll('
			SELECT 		fs.id,
						CONCAT(fs.`name`, " ", fs.`nachname`) AS `name`,
						fs.`name` AS vorname,
						fs.`anschrift`,
						fs.`email`,
						fs.`telefon`,
						fs.`handy`,
						fs.`plz`,
						fs.`geschlecht`

			FROM 		fs_foodsaver_has_bezirk fb,
						`fs_foodsaver` fs

			WHERE 		fb.foodsaver_id = fs.id
			AND			fs.deleted_at IS NULL' . $and
		);
	}

	public function listFoodsaver(int $regionId, bool $showOnlyInactive = false): array
	{
		$onlyInactiveClause = '';
		if ($showOnlyInactive) {
			$oldestActiveDate = Carbon::now()->subMonths(6)->format('Y-m-d H:i:s');
			$onlyInactiveClause = '
				AND (
						fs.last_login < "' . $oldestActiveDate . '"
						OR
						fs.last_login IS NULL
					)
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
					LEFT JOIN fs_foodsaver_has_bezirk hb
						ON fs.id = hb.foodsaver_id

		    WHERE	fs.deleted_at IS NULL
					AND	hb.bezirk_id = :regionId'
					. $onlyInactiveClause . '
		    
			ORDER BY fs.name ASC
		',
		[':regionId' => $regionId]);
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
			],
			['id' => $fsId]
		);
	}

	public function getFoodsaverBasics(int $fsId): array
	{
		if ($fs = $this->db->fetch('
			SELECT 	fs.`name`,
					fs.nachname,
					fs.bezirk_id,
					fs.rolle,
					fs.photo,
					fs.geschlecht,
					fs.stat_fetchweight,
					fs.stat_fetchcount,
					fs.sleep_status,
					fs.id

			FROM 	`fs_foodsaver` fs

			WHERE fs.id = :fsId
		', [':fsId' => $fsId])
		) {
			$fs['bezirk_name'] = '';
			if ($fs['bezirk_id'] > 0) {
				$fs['bezirk_name'] = $this->db->fetchValueByCriteria('fs_bezirk', 'name', ['id' => $fs['bezirk_id']]);
			}

			return $fs;
		}

		return [];
	}

	public function getOne_foodsaver(int $fsId): array
	{
		$out = $this->db->fetch('
			SELECT
				`id`,
				`bezirk_id`,
				`plz`,
				`stadt`,
				`lat`,
				`lon`,
				`email`,
				`name`,
				`nachname`,
				`anschrift`,
				`telefon`,
				`handy`,
				`geschlecht`,
				`geb_datum`,
				`anmeldedatum`,
				`photo`,
				`about_me_public`,
				`orgateam`,
				`data`,
				`rolle`,
				`position`,
				`homepage`
			FROM 		`fs_foodsaver`
			WHERE 		`id` = :id',
			[':id' => $fsId]
		);

		$bot = $this->db->fetchAll('
			SELECT `fs_bezirk`.`name`,
				   `fs_bezirk`.`id`
			FROM `fs_bezirk`,
				 fs_botschafter
			WHERE `fs_botschafter`.`bezirk_id` = `fs_bezirk`.`id`
			AND `fs_botschafter`.foodsaver_id = :id',
			[':id' => $fsId]
		);

		if ($bot) {
			$out['botschafter'] = $bot;
		}

		return $out;
	}

	public function getBotschafter(int $regionId): array
	{
		return $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`email`,
					fs.`name`,
					fs.`name` AS `vorname`,
					fs.`nachname`,
					fs.`photo`,
					fs.`geschlecht`

			FROM `fs_foodsaver` fs,
			`fs_botschafter`

			WHERE fs.id = `fs_botschafter`.`foodsaver_id`

			AND `fs_botschafter`.`bezirk_id` = :regionId
			AND		fs.deleted_at IS NULL',
			[':regionId' => $regionId]
		);
	}

	public function getBezirkCountForBotschafter(int $fsId): int
	{
		return $this->db->count('fs_botschafter', ['foodsaver_id' => $fsId]);
	}

	public function getAllBotschafter(): array
	{
		return $this->db->fetchAll('
			SELECT 		fs.`id`,
						fs.`name`,
						fs.`nachname`,
						fs.`geschlecht`,
						fs.`email`

			FROM 		`fs_foodsaver` fs
			WHERE		fs.id
			IN			(SELECT foodsaver_id
						FROM `fs_fs_botschafter` b
						LEFT JOIN `fs_bezirk` bz
						ON b.bezirk_id = bz.id
						WHERE bz.type != 7
						)
			AND		fs.deleted_at IS NULL'
		);
	}

	public function getAllFoodsaver(): array
	{
		return $this->db->fetchAll('
			SELECT 		fs.id,
						CONCAT(fs.`name`, " ", fs.`nachname`) AS `name`,
						fs.`anschrift`,
						fs.`email`,
						fs.`telefon`,
						fs.`handy`,
						fs.plz

			FROM 		`fs_foodsaver` fs
			WHERE		fs.deleted_at IS NULL AND fs.`active` = 1
		');
	}

	public function getOrgateam(): array
	{
		return $this->db->fetchAll('
			SELECT 		`id`,
						`name`,
						`nachname`,
						`geschlecht`,
						`email`

			FROM 		`fs_foodsaver`

			WHERE 		`orgateam` = 1
		');
	}

	public function getFsMap(int $regionId): array
	{
		return $this->db->fetchAll(
			'SELECT `id`,`lat`,`lon`,CONCAT(`name`," ",`nachname`)
			AS `name`,`plz`,`stadt`,`anschrift`,`photo`
			FROM `fs_foodsaver`
			WHERE `active` = 1
			AND `bezirk_id` = :regionId
			AND `lat` != "" ',
			[':regionId' => $regionId]
		);
	}

	public function xhrGetTagFsAll(array $regionIds): array
	{
		return $this->db->fetchAll('
			SELECT	DISTINCT fs.`id`,
					CONCAT(fs.`name`," ",fs.`nachname`," (",fs.`id`,")") AS value

			FROM 	fs_foodsaver fs,
					fs_foodsaver_has_bezirk hb
			WHERE 	hb.foodsaver_id = fs.id
			AND 	hb.bezirk_id IN(' . implode(',', $regionIds) . ')
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
				WHERE 	((`name` LIKE :term
							OR	`nachname` LIKE :term2))
						AND	deleted_at IS NULL
			', [':term' => $term, ':term2' => $term]);

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
		return $this->getEmailAddresses(
			$minRole,
			$maxRole,
			['newsletter' => 1]
		);
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

		return $this->useIdAsIndex($foodsavers);
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
					AND b.`bezirk_id` > 0
					AND	b.`bezirk_id` IN(:regionIds)
		', [':regionIds' => implode(',', array_map('intval', $regionIds))]);

		return $this->useIdAsIndex($foodsavers);
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
					AND b.`bezirk_id` > 0
					AND	b.`bezirk_id` IN(:regionIds)
		', [':regionIds' => implode(',', array_map('intval', $regionIds))]);

		return $this->useIdAsIndex($foodsavers);
	}
	
	private function useIdAsIndex(array $data): array
	{
		$out = [];
		foreach ($data as $d) {
			$out[$d['id']] = $d;
		}

		return $out;
	}

	public function updateGroupMembers(int $regionId, array $fsIds, bool $leaveAdmins): array
	{
		if ($leaveAdmins) {
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
			$preGroupMembers = $this->db->fetchAllValuesByCriteria(
				'fs_foodsaver_has_bezirk', 'foodsaver_id', ['bezirk_id' => $regionId]
			);
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

		return $this->db->count('fs_foodsaver_has_bezirk', ['bezirk_id' => $regionId]) - $before;
	}

	public function listFoodsaverByRegion(int $regionId): array
	{
		$res = $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`photo`,
					fs.`name`,
					fs.sleep_status

			FROM 	`fs_foodsaver` fs,
					`fs_foodsaver_has_bezirk` c

			WHERE 	c.`foodsaver_id` = fs.id
			AND     fs.deleted_at IS NULL
			AND 	c.bezirk_id = :regionId
			AND 	c.active = 1
			ORDER BY fs.`name`
		', [':regionId' => $regionId]);

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

	public function listActiveWithFullNameByRegion(int $fsId): array
	{
		return $this->db->fetchAll('

			SELECT 	fs.id,
					CONCAT(fs.`name`, " ", fs.`nachname`) AS `name`,
					fs.`name` AS vorname,
					fs.`anschrift`,
					fs.`email`,
					fs.`telefon`,
					fs.`handy`,
					fs.`plz`,
					fs.`geschlecht`

			FROM 	fs_foodsaver_has_bezirk fb,
					`fs_foodsaver` fs

			WHERE 	fb.foodsaver_id = fs.id
			AND 	fb.bezirk_id = :id
			AND 	fb.`active` = 1
			AND		fs.deleted_at IS NULL
		', ['id' => $fsId]);
	}

	public function listAmbassadorsByRegion(int $fsId): array
	{
		return $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`photo`,
					fs.`name`,
					fs.`nachname`,
					fs.sleep_status

			FROM 	`fs_foodsaver` fs,
					`fs_botschafter` c

			WHERE 	c.`foodsaver_id` = fs.id
			AND     fs.deleted_at IS NULL
			AND 	c.bezirk_id = :id
		', ['id' => $fsId]);
	}

	/* retrieves the list of all bots for given bezirk or sub bezirk */
	public function getBotIds(int $regionId, bool $includeRegionAmbassador = true, bool $includeGroupAmbassador = false): array
	{
		$where_type = '';
		if (!$includeRegionAmbassador) {
			$where_type = 'bz.type = ' . Type::WORKING_GROUP;
		} elseif (!$includeGroupAmbassador) {
			$where_type = 'bz.type <> ' . Type::WORKING_GROUP;
		}

		return $this->db->fetchAllValues('
			SELECT DISTINCT 
					bot.foodsaver_id
			FROM	`fs_bezirk_closure` c
					LEFT JOIN `fs_bezirk` bz
						ON bz.id = c.bezirk_id
						INNER JOIN `fs_botschafter` bot
							ON bot.bezirk_id = c.bezirk_id
							INNER JOIN `fs_foodsaver` fs
								ON fs.id = bot.foodsaver_id
			WHERE	c.ancestor_id = ' . $regionId . ''
				. ' AND fs.deleted_at IS NULL'
				. ' AND ' . $where_type
		);
	}

	public function del_foodsaver(int $fsId): void
	{
		$this->db->update('fs_foodsaver', ['password' => null, 'deleted_at' => $this->db->now()], ['id' => $fsId]);

		$this->db->execute('
			INSERT INTO fs_foodsaver_archive
			(
				SELECT * FROM fs_foodsaver WHERE id = ' . $fsId . '
			)
		');

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
			],
			['id' => $fsId]
		);
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
					LEFT JOIN fs_foodsaver_has_bezirk fb
						ON fs.id = fb.foodsaver_id

			WHERE 	fs.deleted_at IS NULL
					AND fb.`bezirk_id` IN(' . implode(',', $regions) . ')'
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
			'about_me_public',
			'homepage',
			'position'
		];

		$clean_data = [];
		foreach ($fields as $field) {
			if (!array_key_exists($field, $data)) {
				continue;
			}
			$clean_data[$field] = in_array($field, $fieldsToStripTags, true) ? strip_tags($data[$field]) : $data[$field];
		}

		$this->db->update(
			'fs_foodsaver',
			$clean_data,
			['id' => $fsId]
		);

		return true;
	}

	public function updatePhoto(int $fsId, string $photo): void
	{
		$this->db->update(
			'fs_foodsaver',
			['photo' => strip_tags($photo)],
			['id' => $fsId]
		);
	}

	public function getPhoto(int $fsId): string
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
	 * @param string $key
	 * @param $val
	 */
	public function setOption(int $fsId, string $key, string $val): int
	{
		$options = [];
		if ($opt = $this->db->fetchValueByCriteria('fs_foodsaver', 'option', ['id' => $fsId])) {
			$options = unserialize($opt);
		}

		$options[$key] = $val;

		return $this->db->update(
			'fs_foodsaver',
			['option' => serialize($options)],
			['id' => $fsId]
		);
	}

	public function deleteFromRegion(int $regionId, int $fsId): void
	{
		$this->db->delete('fs_botschafter', ['bezirk_id' => $regionId, 'foodsaver_id' => $fsId]);
		$this->db->delete('fs_foodsaver_has_bezirk', ['bezirk_id' => $regionId, 'foodsaver_id' => $fsId]);

		$this->forumFollowerGateway->deleteForumSubscription($regionId, $fsId);

		$mainRegion_id = $this->db->fetchValueByCriteria('fs_foodsaver', 'bezirk_id', ['id' => $fsId]);
		if ($mainRegion_id === $regionId) {
			$this->db->update('fs_foodsaver', ['bezirk_id' => 0], ['id' => $fsId]);
		}
	}

	public function setQuizRole(int $fsId, int $quizRole): int
	{
		return $this->db->update(
			'fs_foodsaver',
			['quiz_rolle' => $quizRole],
			['id' => $fsId]
		);
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
		return $this->db->fetch('
			SELECT
				id,
				name,
				nachname,
				photo,
				rolle,
				geschlecht,
				last_login
			FROM
				fs_foodsaver
			WHERE
				id = :fsId
            AND
                deleted_at IS NULL
		', [':fsId' => $foodsaverId]);
	}

	public function updateFoodsaver(int $fsId, array $data, StoreModel $storeModel): int
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
			if ($data['rolle'] == Role::FOODSHARER && $data['is_orgateam']) {
				$data['bezirk_id'] = 0;
				$updateData['quiz_rolle'] = Role::FOODSHARER;
				$updateData['verified'] = 0;

				$this->signOutFromStores($fsId, $storeModel);

				//Delete Bells for Foodsaver
				$this->db->delete(
					'fs_foodsaver_has_bell',
					['foodsaver_id' => $fsId]
				);
				// Delete from Bezirke and Working Groups
				$this->db->delete(
					'fs_foodsaver_has_bezirk',
					['foodsaver_id' => $fsId]
				);
				//Delete from Bezirke and Working Groups (when Admin)
				$this->db->delete(
					'fs_botschafter',
					['foodsaver_id' => $fsId]
				);

				$this->quizSessionGateway->blockUserForQuiz($fsId, Role::FOODSAVER);
			}
		}

		return $this->db->update(
			'fs_foodsaver',
			$updateData,
			['id' => $fsId]
		);
	}

	private function signOutFromStores(int $fsId, StoreModel $storeModel): void
	{
		$storeIds = $this->db->fetchAll('
			SELECT 	bt.betrieb_id as id
			FROM 	fs_betrieb_team bt
			WHERE 	bt.foodsaver_id = :fsId
		', [':fsId' => $fsId]);

		//Delete from Companies
		foreach ($storeIds as $storeId) {
			$storeModel->signout($storeId, $fsId);
		}
	}
	public function getFoodsaverAddress($foodsaverId)
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

	/**
	 * Returns the first name of the foodsaver.
	 */
	public function getFoodsaverName($foodsaverId): string
	{
		return $this->db->fetchValueByCriteria('fs_foodsaver', 'name', ['id' => $foodsaverId]);
	}
}
