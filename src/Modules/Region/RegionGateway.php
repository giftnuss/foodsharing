<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;

class RegionGateway extends BaseGateway
{
	private FoodsaverGateway $foodsaverGateway;

	public function __construct(
		Database $db,
		FoodsaverGateway $foodsaverGateway
	) {
		parent::__construct($db);
		$this->foodsaverGateway = $foodsaverGateway;
	}

	public function getRegion(int $regionId): ?array
	{
		if ($regionId == RegionIDs::ROOT) {
			return null;
		}

		return $this->db->fetchByCriteria('fs_bezirk',
			['name', 'id', 'email', 'email_name', 'has_children', 'parent_id', 'mailbox_id', 'type'],
			['id' => $regionId]
		);
	}

	public function getOne_bezirk(int $regionId): array
	{
		$out = $this->db->fetchByCriteria('fs_bezirk',
			['id', 'parent_id', 'has_children', 'name', 'email', 'email_pass', 'email_name', 'type', 'master', 'mailbox_id'],
			['id' => $regionId]
		);

		if ($this->existRegionWelcomeGroup($out['id'], $out['parent_id'])) {
			$out['workgroup_function'] = WorkgroupFunction::WELCOME;
		} else {
			if ($this->existRegionVotingGroup($out['id'], $out['parent_id'])) {
				$out['workgroup_function'] = WorkgroupFunction::VOTING;
			} else {
				if ($this->existRegionFSPGroup($out['id'], $out['parent_id'])) {
					$out['workgroup_function'] = WorkgroupFunction::FSP;
				} else {
					$out['workgroup_function'] = [];
				}
			}
		}

		$out['botschafter'] = $this->db->fetchAll('
				SELECT 		`fs_foodsaver`.`id`,
							CONCAT(`fs_foodsaver`.`name`," ",`fs_foodsaver`.`nachname`) AS name

				FROM 		`fs_botschafter`,
							`fs_foodsaver`

				WHERE 		`fs_foodsaver`.`id` = `fs_botschafter`.`foodsaver_id`
				AND 		`fs_botschafter`.`bezirk_id` = ' . $regionId . '
			');

		$out['foodsaver'] = $this->db->fetchAllValuesByCriteria('fs_botschafter', 'foodsaver_id',
			['bezirk_id' => $regionId]
		);

		return $out;
	}

	public function getMailBezirk(int $regionId): array
	{
		return $this->db->fetchByCriteria('fs_bezirk',
			['id', 'name', 'email', 'email_name', 'email_pass'],
			['id' => $regionId]
		);
	}

	public function listRegionsIncludingParents(array $regionId): array
	{
		$stm = 'SELECT DISTINCT ancestor_id FROM `fs_bezirk_closure` WHERE bezirk_id IN (' . implode(',', array_map('intval', $regionId)) . ')';

		return $this->db->fetchAllValues($stm);
	}

	public function getBasics_bezirk(): array
	{
		return $this->db->fetchAll('
			SELECT 	 	`id`,
						`name`

			FROM 		`fs_bezirk`
			ORDER BY `name`');
	}

	public function getBezirkByParent(int $parentId, bool $includeOrga = false): array
	{
		$sql = 'AND 		`type` != ' . Type::WORKING_GROUP;
		if ($includeOrga) {
			$sql = '';
		}

		return $this->db->fetchAll('
			SELECT
				`id`,
				`name`,
				`has_children`,
				`parent_id`,
				`type`,
				`master`
			FROM 		`fs_bezirk`
			WHERE 		`parent_id` = :id
			AND id != :rootId
			' . $sql . '
			ORDER BY 	`name`',
			[
				':rootId' => RegionIDs::ROOT,
				':id' => $parentId
			]
		);
	}

	public function listIdsForFoodsaverWithDescendants(?int $foodsaverId): array
	{
		if ($foodsaverId === null) {
			return [];
		}
		$bezirk_ids = [];
		foreach ($this->listForFoodsaver($foodsaverId) as $bezirk) {
			$bezirk_ids += $this->listIdsForDescendantsAndSelf($bezirk['id']);
		}

		return $bezirk_ids;
	}

	/**
	 * @return bool true when the given user is active (an accepted member) in the given region
	 */
	public function hasMember(int $foodsaverId, int $regionId): bool
	{
		return $this->db->exists('fs_foodsaver_has_bezirk', ['bezirk_id' => $regionId, 'foodsaver_id' => $foodsaverId, 'active' => 1]);
	}

	/**
	 * @return bool true when the given user is an admin/ambassador for the given group/region
	 */
	public function isAdmin(int $foodsaverId, int $regionId): bool
	{
		return $this->db->exists('fs_botschafter', ['bezirk_id' => $regionId, 'foodsaver_id' => $foodsaverId]);
	}

	public function listForFoodsaver(?int $foodsaverId): array
	{
		if ($foodsaverId === null) {
			return [];
		}
		$values = $this->db->fetchAll(
			'
			SELECT 	b.`id`,
					b.name,
					b.type

			FROM 	`fs_foodsaver_has_bezirk` hb,
					`fs_bezirk` b

			WHERE 	hb.bezirk_id = b.id
			AND 	`foodsaver_id` = :fs_id
			AND 	hb.active = 1

			ORDER BY b.name',
			[':fs_id' => $foodsaverId]
		);

		$output = [];
		foreach ($values as $v) {
			$output[$v['id']] = $v;
		}

		return $output;
	}

	public function getFsRegionIds(int $foodsaverId): array
	{
		return $this->db->fetchAllValuesByCriteria('fs_foodsaver_has_bezirk', 'bezirk_id',
			['foodsaver_id' => $foodsaverId]
		);
	}

	public function getFsAmbassadorIds(?int $foodsaverId): array
	{
		if ($foodsaverId === null) {
			return [];
		}

		return $this->db->fetchAllValuesByCriteria('fs_botschafter', 'bezirk_id',
			['foodsaver_id' => $foodsaverId]
		);
	}

	public function listIdsForDescendantsAndSelf(int $regionId, bool $includeSelf = true, bool $includeWorkgroups = true): array
	{
		if ($regionId == RegionIDs::ROOT) {
			return [];
		}
		if ($includeSelf) {
			$minDepth = 0;
		} else {
			$minDepth = 1;
		}

		if ($includeWorkgroups) {
			return $this->db->fetchAllValuesByCriteria('fs_bezirk_closure', 'bezirk_id',
				['ancestor_id' => $regionId, 'depth >=' => $minDepth]
			);
		} else {
			return $this->db->fetchAllValues(
				'SELECT
						fbc.bezirk_id
					FROM `fs_bezirk_closure` fbc
					left outer join `fs_bezirk` reg on fbc.bezirk_id = reg.id
					  WHERE
						fbc.ancestor_id = :regionId
					AND fbc.depth >= :min_depth
					and reg.type <> :regionTypeWorkGroup',
				['regionId' => $regionId, 'min_depth' => $minDepth, 'regionTypeWorkGroup' => Type::WORKING_GROUP]
			);
		}
	}

	public function listForFoodsaverExceptWorkingGroups(int $foodsaverId): array
	{
		return $this->db->fetchAll('
			SELECT
				b.`id`,
				b.`name`,
				b.`teaser`,
				b.`photo`

			FROM
				fs_bezirk b,
				fs_foodsaver_has_bezirk hb

			WHERE
				hb.bezirk_id = b.id

			AND
				hb.`foodsaver_id` = :foodsaverId

			AND
				b.`type` != :workGroupType

			ORDER BY
				b.`name`
		', [
			':foodsaverId' => $foodsaverId,
			':workGroupType' => Type::WORKING_GROUP
		]);
	}

	public function getRegionDetails(int $regionId): array
	{
		$region = $this->db->fetch('
			SELECT
				b.`id`,
				b.`name`,
				b.`email`,
				b.`email_name`,
				b.`mailbox_id`,
				b.`type`,
				b.`stat_fetchweight`,
				b.`stat_fetchcount`,
				b.`stat_fscount`,
				b.`stat_botcount`,
				b.`stat_postcount`,
				b.`stat_betriebcount`,
				b.`stat_korpcount`,
				b.`moderated`,
				b.`has_children`,
				(
					SELECT 	count(c.`foodsaver_id`)
					FROM 	`fs_foodsaver_has_bezirk` c
					LEFT JOIN `fs_foodsaver` fs ON c.`foodsaver_id` = fs.id
					WHERE     fs.deleted_at IS NULL
					AND 	c.bezirk_id = b.id
					AND 	c.active = 1
					AND 	fs.sleep_status = 0
				) AS fs_count,
				(
					SELECT 	count(c.`foodsaver_id`)
					FROM 	`fs_foodsaver_has_bezirk` c
					LEFT JOIN `fs_foodsaver` fs ON c.`foodsaver_id` = fs.id
					WHERE     fs.deleted_at IS NULL
					AND 	c.bezirk_id = b.id
					AND 	c.active = 1
					AND 	fs.sleep_status > 0
				) AS sleeper_count

			FROM 	`fs_bezirk` AS b

			WHERE 	b.`id` = :id
			LIMIT 1
		', ['id' => $regionId]);

		$region['botschafter'] = $this->foodsaverGateway->getAdminsOrAmbassadors($regionId);
		shuffle($region['botschafter']);

		if ($welcomeGroupId = $this->getRegionFunctionGroupId($regionId, WorkgroupFunction::WELCOME)) {
			$region['welcomeAdmins'] = $this->foodsaverGateway->getAdminsOrAmbassadors($welcomeGroupId);
			shuffle($region['welcomeAdmins']);
		} else {
			$region['welcomeAdmins'] = [];
		}

		if ($votingGroupId = $this->getRegionFunctionGroupId($regionId, WorkgroupFunction::VOTING)) {
			$region['votingAdmins'] = $this->foodsaverGateway->getAdminsOrAmbassadors($votingGroupId);
			shuffle($region['votingAdmins']);
		} else {
			$region['votingAdmins'] = [];
		}

		if ($fspGroupId = $this->getRegionFunctionGroupId($regionId, WorkgroupFunction::FSP)) {
			$region['fspAdmins'] = $this->foodsaverGateway->getAdminsOrAmbassadors($fspGroupId);
			shuffle($region['fspAdmins']);
		} else {
			$region['fspAdmins'] = [];
		}

		return $region;
	}

	public function getType(int $regionId): int
	{
		return (int)$this->db->fetchValueByCriteria('fs_bezirk', 'type', ['id' => $regionId]);
	}

	public function listRequests(int $regionId): array
	{
		return $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`name`,
					fs.`nachname`,
					fs.`photo`,
					fb.application,
					fb.active,
					UNIX_TIMESTAMP(fb.added) AS `time`

			FROM 	`fs_foodsaver_has_bezirk` fb,
					`fs_foodsaver` fs

			WHERE 	fb.foodsaver_id = fs.id
			AND 	fb.bezirk_id = :regionId
			AND 	fb.active = 0
		', ['regionId' => $regionId]);
	}

	public function linkBezirk(int $foodsaverId, int $regionId, int $active = 1)
	{
		$this->db->insertOrUpdate('fs_foodsaver_has_bezirk', [
			'bezirk_id' => $regionId,
			'foodsaver_id' => $foodsaverId,
			'added' => $this->db->now(),
			'active' => $active
		]);
	}

	public function update_bezirkNew(int $regionId, array $data)
	{
		if (isset($data['botschafter']) && is_array($data['botschafter'])) {
			$this->db->delete('fs_botschafter', ['bezirk_id' => $regionId]);
			foreach ($data['botschafter'] as $foodsaver_id) {
				$this->db->insert('fs_botschafter', [
					'bezirk_id' => $regionId,
					'foodsaver_id' => $foodsaver_id
				]);
			}
		}

		$master = 0;
		if (isset($data['master'])) {
			$master = (int)$data['master'];
		}

		$this->db->beginTransaction();

		if ((int)$data['parent_id'] > RegionIDs::ROOT) {
			$this->db->update('fs_bezirk', ['has_children' => 1], ['id' => $data['parent_id']]);
		}

		$has_children = 0;
		if ($this->db->exists('fs_bezirk', ['parent_id' => $regionId])) {
			$has_children = 1;
		}

		$this->db->update(
			'fs_bezirk',
			[
				'name' => strip_tags($data['name']),
				'email_name' => strip_tags($data['email_name']),
				'parent_id' => $data['parent_id'],
				'type' => $data['type'],
				'master' => $master,
				'has_children' => $has_children,
			],
			['id' => $regionId]
		);

		$this->db->execute('DELETE a FROM `fs_bezirk_closure` AS a JOIN `fs_bezirk_closure` AS d ON a.bezirk_id = d.bezirk_id LEFT JOIN `fs_bezirk_closure` AS x ON x.ancestor_id = d.ancestor_id AND x.bezirk_id = a.ancestor_id WHERE d.ancestor_id = ' . $regionId . ' AND x.ancestor_id IS NULL');
		$this->db->execute('INSERT INTO `fs_bezirk_closure` (ancestor_id, bezirk_id, depth) SELECT supertree.ancestor_id, subtree.bezirk_id, supertree.depth+subtree.depth+1 FROM `fs_bezirk_closure` AS supertree JOIN `fs_bezirk_closure` AS subtree WHERE subtree.ancestor_id = ' . $regionId . ' AND supertree.bezirk_id = ' . (int)(int)$data['parent_id']);
		$this->db->commit();
	}

	public function addRegion(array $data): int
	{
		$this->db->beginTransaction();

		$id = $this->db->insert('fs_bezirk', [
			'parent_id' => (int)$data['parent_id'],
			'has_children' => (int)$data['has_children'],
			'name' => strip_tags($data['name']),
			'email' => strip_tags($data['email']),
			'email_pass' => strip_tags($data['email_pass']),
			'email_name' => strip_tags($data['email_name'])
		]);

		$this->db->execute('INSERT INTO `fs_bezirk_closure` (ancestor_id, bezirk_id, depth) SELECT t.ancestor_id, ' . $id . ', t.depth+1 FROM `fs_bezirk_closure` AS t WHERE t.bezirk_id = ' . (int)$data['parent_id'] . ' UNION ALL SELECT ' . $id . ', ' . $id . ', 0');
		$this->db->commit();

		if (isset($data['foodsaver']) && is_array($data['foodsaver'])) {
			foreach ($data['foodsaver'] as $foodsaver_id) {
				$this->db->insert('fs_botschafter', [
					'bezirk_id' => (int)$id,
					'foodsaver_id' => (int)$foodsaver_id
				]);
				$this->db->insert('fs_foodsaver_has_bezirk', [
					'bezirk_id' => (int)$id,
					'foodsaver_id' => (int)$foodsaver_id
				]);
			}
		}

		return $id;
	}

	public function getRegionName(int $regionId): string
	{
		return $this->db->fetchValueByCriteria('fs_bezirk', 'name', ['id' => $regionId]);
	}

	public function addMember(int $foodsaverId, int $regionId)
	{
		$this->db->insertIgnore('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $foodsaverId,
			'bezirk_id' => $regionId,
			'active' => 1,
			'added' => $this->db->now()
		]);
	}

	public function getMasterId(int $regionId): int
	{
		return $this->db->fetchValueByCriteria('fs_bezirk', 'master', ['id' => $regionId]);
	}

	public function listRegionsForBotschafter(int $foodsaverId): array
	{
		return $this->db->fetchAll(
	'SELECT 	`fs_botschafter`.`bezirk_id`,
					`fs_bezirk`.`has_children`,
					`fs_bezirk`.`parent_id`,
					`fs_bezirk`.name,
					`fs_bezirk`.id,
					`fs_bezirk`.type

			FROM 	`fs_botschafter`,
					`fs_bezirk`

			WHERE 	`fs_bezirk`.`id` = `fs_botschafter`.`bezirk_id`

			AND 	`fs_botschafter`.`foodsaver_id` = :id',
			[':id' => $foodsaverId]
		);
	}

	public function addOrUpdateMember(int $foodsaverId, int $regionId): bool
	{
		return $this->db->insertOrUpdate('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $foodsaverId,
			'bezirk_id' => $regionId,
			'active' => 1,
			'added' => $this->db->now()
		]) > 0;
	}

	public function updateMasterRegions(array $regionIds, int $masterId): void
	{
		$this->db->update('fs_bezirk', ['master' => $masterId], ['id' => $regionIds]);
	}

	public function getRegionWelcomeGroupId(int $parentId): ?int
	{
		try {
			return $this->db->fetchValueByCriteria(
				'fs_region_function',
				'region_id',
				[
					'target_id' => $parentId,
					'function_id' => WorkgroupFunction::WELCOME
				]
			);
		} catch (\Exception $e) {
			return null;
		}
	}

	public function getRegionVotingGroupId(int $parentId): ?int
	{
		try {
			return $this->db->fetchValueByCriteria(
				'fs_region_function',
				'region_id',
				[
					'target_id' => $parentId,
					'function_id' => WorkgroupFunction::VOTING
				]
			);
		} catch (\Exception $e) {
			return null;
		}
	}

	public function getRegionFunctionGroupId(int $parentId, int $function): ?int
	{
		try {
			return $this->db->fetchValueByCriteria(
				'fs_region_function',
				'region_id',
				[
					'target_id' => $parentId,
					'function_id' => $function
				]
			);
		} catch (\Exception $e) {
			return null;
		}
	}

	public function deleteRegionFunction($regionId, $functionId)
	{
		return $this->db->delete('fs_region_function',
			['region_id' => $regionId,
			 'function_id' => $functionId]
		);
	}

	public function deleteTargetFunctions($targetId)
	{
		return $this->db->delete('fs_region_function',
			['target_id' => $targetId]
		);
	}

	public function addRegionFunction(int $regionId, int $functionId, int $targetId)
	{
		return $this->db->insert('fs_region_function',
			['region_id' => $regionId,
			'function_id' => $functionId,
			'target_id' => $targetId]
			);
	}

	public function RegionFunctionGroup(int $region_id, int $target_id): bool
	{
		return  $this->db->fetchValueByCriteria('fs_region_function', 'function_id',
			['region_id' => $region_id,
			 'target_id' => $target_id]
		);
	}

	public function existRegionWelcomeGroup(int $region_id, int $target_id): bool
	{
		return  $this->db->exists('fs_region_function',
			['region_id' => $region_id,
			 'function_id' => WorkgroupFunction::WELCOME,
			 'target_id' => $target_id]
		);
	}

	public function existRegionVotingGroup(int $region_id, int $target_id): bool
	{
		return  $this->db->exists('fs_region_function', ['region_id' => $region_id, 'function_id' => WorkgroupFunction::VOTING, 'target_id' => $target_id]);
	}

	public function existRegionFSPGroup(int $region_id, int $target_id): bool
	{
		return  $this->db->exists('fs_region_function', ['region_id' => $region_id, 'function_id' => WorkgroupFunction::FSP, 'target_id' => $target_id]);
	}

	public function genderCountRegion(int $regionId): array
	{
		return $this->db->fetchAll(
			'select  fs.geschlecht as gender,
						   count(*) as NumberOfGender
					from fs_foodsaver_has_bezirk fb
		 			left outer join fs_foodsaver fs on fb.foodsaver_id=fs.id
					where fb.bezirk_id = :regionId
					and fs.deleted_at is null
					group by geschlecht',
			[':regionId' => $regionId]
		);
	}

	public function genderCountHomeRegion(int $regionId): array
	{
		return $this->db->fetchAll(
			'select  fs.geschlecht as gender,
						   count(*) as NumberOfGender
					from fs_foodsaver fs
					where fs.bezirk_id = :regionId
					and fs.deleted_at is null
					group by geschlecht',
			[':regionId' => $regionId]
		);
	}

	public function listRegionPickupsByDate(int $regionId, string $dateFormat): array
	{
		$regionIDs = implode(',', array_map('intval', $this->listIdsForDescendantsAndSelf($regionId)));

		if (empty($regionIDs)) {
			return [];
		}

		return $this->db->fetchAll(
			'select
						date_Format(a.date,:format) as time,
						count(distinct a.betrieb_id) as NumberOfStores,
						count(distinct a.date, a.betrieb_id) as NumberOfAppointments ,
						count(*) as NumberOfSlots,
						count(distinct a.foodsaver_id) as NumberOfFoodsavers
					from fs_abholer a
					left outer join fs_betrieb b on a.betrieb_id = b.id
						where b.bezirk_id in (' . $regionIDs . ')
						and a.confirmed = 1
					group by date_Format(date,:groupFormat)
					order by date desc',
			[':format' => $dateFormat, ':groupFormat' => $dateFormat]
		);
	}

	public function ageBandHomeDistrict(int $districtId): array
	{
		return $this->db->fetchAll(
			'SELECT
				CASE
				WHEN age >=18 AND age <=25 THEN \'18-25\'
				WHEN age >=26 AND age <=33 THEN \'26-33\'
				WHEN age >=34 AND age <=41 THEN \'34-41\'
				WHEN age >=42 AND age <=49 THEN \'42-49\'
				WHEN age >=50 AND age <=57 THEN \'50-57\'
				WHEN age >=58 AND age <=65 THEN \'58-65\'
				WHEN age >=66 AND age <=73 THEN \'66-73\'
				WHEN age >=74 AND age < 100 THEN \'74+\'
				WHEN age >= 100 or age < 18 THEN \'invalid\'
				WHEN age IS NULL THEN \'unknown\'
				END AS Altersgruppe,
				COUNT(*) AS Anzahl
				FROM
				(
				 SELECT DATE_FORMAT(NOW(), \'%Y\') - DATE_FORMAT(geb_datum, \'%Y\') - (DATE_FORMAT(NOW(), \'00-%m-%d\') < DATE_FORMAT(geb_datum, \'00-%m-%d\')) AS age,
				 id FROM fs_foodsaver WHERE rolle >= :rolle AND bezirk_id = :id and deleted_at is null
				) AS tbl
				GROUP BY Altersgruppe',
			['rolle' => Role::FOODSAVER, ':id' => $districtId]
		);
	}

	public function ageBandDistrict(int $districtId): array
	{
		return $this->db->fetchAll(
			'SELECT
				CASE
				WHEN age >=18 AND age <=25 THEN \'18-25\'
				WHEN age >=26 AND age <=33 THEN \'26-33\'
				WHEN age >=34 AND age <=41 THEN \'34-41\'
				WHEN age >=42 AND age <=49 THEN \'42-49\'
				WHEN age >=50 AND age <=57 THEN \'50-57\'
				WHEN age >=58 AND age <=65 THEN \'58-65\'
				WHEN age >=66 AND age <=73 THEN \'66-73\'
				WHEN age >=74 AND age < 100 THEN \'74+\'
				WHEN age >= 100 or age < 18 THEN \'invalid\'
				WHEN age IS NULL THEN \'unknown\'
				END AS Altersgruppe,
				COUNT(*) AS Anzahl
				FROM
				(
				 SELECT DATE_FORMAT(NOW(), \'%Y\') - DATE_FORMAT(geb_datum, \'%Y\') - (DATE_FORMAT(NOW(), \'00-%m-%d\') < DATE_FORMAT(geb_datum, \'00-%m-%d\')) AS age,
				 		fs.id
				 FROM fs_foodsaver_has_bezirk fb
					 left outer join fs_foodsaver fs on fb.foodsaver_id=fs.id
					 WHERE fs.rolle >= :rolle AND fb.bezirk_id = :id and fs.deleted_at is null
				) AS tbl
				GROUP BY Altersgruppe',
			['rolle' => Role::FOODSAVER, ':id' => $districtId]
		);
	}
}
