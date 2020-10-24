<?php

namespace Foodsharing\Modules\Group;

use Foodsharing\Modules\Core\BaseGateway;

/* Group gateway meant to collect queries common for regions as well as working groups */
class GroupGateway extends BaseGateway
{
	public function getGroupLegacy(int $groupId): array
	{
		$out = $this->db->fetchByCriteria('fs_bezirk',
			['id', 'parent_id', 'has_children', 'name', 'email', 'email_pass', 'email_name', 'type', 'master', 'mailbox_id'],
			['id' => $groupId]
		);

		if ($this->existRegionWelcomeGroup($out['id'], $out['parent_id'])) {
			$out['workgroup_function'] = WorkgroupFunction::WELCOME;
		} elseif ($this->existRegionVotingGroup($out['id'], $out['parent_id'])) {
			$out['workgroup_function'] = WorkgroupFunction::VOTING;
		} elseif ($this->existRegionFSPGroup($out['id'], $out['parent_id'])) {
			$out['workgroup_function'] = WorkgroupFunction::FSP;
		} else {
			$out['workgroup_function'] = [];
		}

		$out['botschafter'] = $this->db->fetchAll('
			SELECT  `fs_foodsaver`.`id`,
			        CONCAT(`fs_foodsaver`.`name`," ",`fs_foodsaver`.`nachname`) AS name

			FROM    `fs_botschafter`,
			        `fs_foodsaver`

			WHERE   `fs_foodsaver`.`id` = `fs_botschafter`.`foodsaver_id`
			AND     `fs_botschafter`.`bezirk_id` = ' . $groupId . '
		');

		$out['foodsaver'] = $this->db->fetchAllValuesByCriteria('fs_botschafter', 'foodsaver_id',
			['bezirk_id' => $groupId]
		);

		return $out;
	}

	public function deleteGroup($groupId)
	{
		$parent_id = $this->db->fetchValueByCriteria(
			'fs_bezirk',
			'parent_id',
			['id' => $groupId]
		);

		$this->db->update(
			'fs_foodsaver',
			['bezirk_id' => null],
			['bezirk_id' => $groupId]
		);
		$this->db->update(
			'fs_bezirk',
			['parent_id' => 0],
			['parent_id' => $groupId]
		);

		$this->db->delete('fs_bezirk', ['id' => $groupId]);

		$count = $this->db->count('fs_bezirk', ['parent_id' => $parent_id]);

		if ($count == 0) {
			$this->db->update(
				'fs_bezirk',
				['has_children' => 0],
				['id' => $parent_id]
			);
		}
	}

	public function recreateClosure()
	{
		$this->db->beginTransaction();
		$this->db->execute('DELETE FROM fs_bezirk_closure');
		$this->db->execute('INSERT INTO fs_bezirk_closure (bezirk_id, ancestor_id, depth) SELECT a.id, a.id, 0 FROM fs_bezirk AS a WHERE a.parent_id > ?', [0]);
		for ($i = 0; $i <= 5; ++$i) {
			$this->db->execute('INSERT INTO fs_bezirk_closure (bezirk_id, ancestor_id, depth) SELECT a.bezirk_id, b.parent_id, a.depth+1 FROM fs_bezirk_closure AS a JOIN fs_bezirk AS b ON b.id = a.ancestor_id WHERE b.parent_id IS NOT NULL AND a.depth = ?', [$i]);
		}
		$this->db->commit();
	}

	/**
	 * Returns whether the group contains any subregions or working groups.
	 */
	public function hasSubregions(int $groupId): bool
	{
		return $this->db->exists('fs_bezirk', [
			'parent_id' => $groupId
		]);
	}

	/**
	 * Returns whether the group contains any stores. This does not include subregions.
	 */
	public function hasStores(int $groupId): bool
	{
		return $this->db->exists('fs_betrieb', [
			'bezirk_id' => $groupId
		]);
	}

	/**
	 * Returns whether the group contains any foodsharepoints. This does not search subregions, if the group has
	 * any, but includes FSPs that have not been accepted yet.
	 */
	public function hasFoodSharePoints(int $groupId): bool
	{
		return $this->db->exists('fs_fairteiler', [
			'bezirk_id' => $groupId
		]);
	}

	private function existRegionWelcomeGroup(int $region_id, int $target_id): bool
	{
		return  $this->db->exists('fs_region_function',
			['region_id' => $region_id,
			 'function_id' => WorkgroupFunction::WELCOME,
			 'target_id' => $target_id]
		);
	}

	private function existRegionVotingGroup(int $region_id, int $target_id): bool
	{
		return  $this->db->exists('fs_region_function', ['region_id' => $region_id, 'function_id' => WorkgroupFunction::VOTING, 'target_id' => $target_id]);
	}

	private function existRegionFSPGroup(int $region_id, int $target_id): bool
	{
		return  $this->db->exists('fs_region_function', ['region_id' => $region_id, 'function_id' => WorkgroupFunction::FSP, 'target_id' => $target_id]);
	}
}
