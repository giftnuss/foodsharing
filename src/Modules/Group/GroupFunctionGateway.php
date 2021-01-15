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
	/**
	 * Finds the working group in the specified region that has a certain function.
	 *
	 * @param int $parentId ID of the region
	 * @param int $function function type, see {@see WorkgroupFunction}
	 *
	 * @return int|null the working group's ID or null, group with that function exists
	 */
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

	/**
	 * Adds a function to an existing working group. If the group does not exist or already has that
	 * function, nothing happens.
	 *
	 * @param int $regionId the working group's parent region
	 * @param int $targetId ID of the working group
	 * @param int $functionId function type to be added, see {@see WorkgroupFunction}
	 *
	 * @return bool if the function was successfully added
	 *
	 * @throws \Exception
	 */
	public function addRegionFunction(int $regionId, int $targetId, int $functionId): bool
	{
		return $this->db->insert('fs_region_function', [
			'region_id' => $regionId,
			'target_id' => $targetId,
			'function_id' => $functionId,
		]) > 0;
	}

	/**
	 * Removes a function from all working groups in a region that have the specific function. The groups
	 * themselves are not altered. If no group in the region has that function, nothing happens.
	 *
	 * @param int $regionId ID of the region
	 * @param int $functionId function type to be removed, see {@see WorkgroupFunction}
	 *
	 * @return int the number of groups that lost the function
	 *
	 * @throws \Exception
	 */
	public function deleteRegionFunction(int $regionId, int $functionId): int
	{
		return $this->db->delete('fs_region_function', [
			'region_id' => $regionId,
			'function_id' => $functionId,
		]);
	}

	/**
	 * Removes all functions from a given working group.
	 *
	 * @param int $targetId the group's ID
	 *
	 * @return int how many functions were removed from that group
	 *
	 * @throws \Exception
	 */
	public function deleteTargetFunctions(int $targetId): int
	{
		return $this->db->delete('fs_region_function', ['target_id' => $targetId]);
	}

	public function existRegionFunctionGroup(int $region_id, int $target_id, int $function_id): bool
	{
		return $this->db->exists('fs_region_function', [
			'region_id' => $region_id,
			'target_id' => $target_id,
			'function_id' => $function_id,
		]);
	}
}
