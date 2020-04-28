<?php

namespace Foodsharing\Modules\Group;

use Foodsharing\Modules\Core\BaseGateway;

/* Group gateway meant to collect methods common for regions as well as working groups */
class GroupGateway extends BaseGateway
{
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
}
