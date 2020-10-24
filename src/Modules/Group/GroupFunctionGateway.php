<?php

namespace Foodsharing\Modules\Group;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;

/**
 * This gateway covers all functionality that is related to workgroups which are configured to have
 * some predefined function ({@see WorkgroupFunction}) for their attached group (region or workgroup).
 *
 * For legacy codebase reasons, some of the method names still use `region` which in this context can
 * stand for both actual regions and workgroups. Entities providing this function are always workgroups.
 */
class GroupFunctionGateway extends BaseGateway
{
	public function getRegionFunctionGroupId(int $parentId, int $function): ?int
	{
		try {
			return $this->db->fetchValueByCriteria(
				'fs_region_function',
				'region_id',
				[
					'target_id' => $parentId,
					'function_id' => $function,
				]
			);
		} catch (\Exception $e) {
			return null;
		}
	}

	public function addRegionFunction(int $regionId, int $targetId, int $functionId)
	{
		return $this->db->insert('fs_region_function', [
			'region_id' => $regionId,
			'target_id' => $targetId,
			'function_id' => $functionId,
		]);
	}

	public function deleteRegionFunction($regionId, $functionId)
	{
		return $this->db->delete('fs_region_function', [
			'region_id' => $regionId,
			'function_id' => $functionId,
		]);
	}

	public function deleteTargetFunctions($targetId)
	{
		return $this->db->delete('fs_region_function', ['target_id' => $targetId]);
	}

	public function existRegionWelcomeGroup(int $region_id, int $target_id): bool
	{
		return $this->existRegionFunctionGroup($region_id, $target_id, WorkgroupFunction::WELCOME);
	}

	public function existRegionVotingGroup(int $region_id, int $target_id): bool
	{
		return $this->existRegionFunctionGroup($region_id, $target_id, WorkgroupFunction::VOTING);
	}

	public function existRegionFSPGroup(int $region_id, int $target_id): bool
	{
		return $this->existRegionFunctionGroup($region_id, $target_id, WorkgroupFunction::FSP);
	}

	private function existRegionFunctionGroup(int $region_id, int $target_id, int $function_id): bool
	{
		return $this->db->exists('fs_region_function', [
			'region_id' => $region_id,
			'target_id' => $target_id,
			'function_id' => $function_id,
		]);
	}
}
