<?php

namespace Foodsharing\Modules\Group;

use Foodsharing\Modules\Core\BaseGateway;

/* Group gateway meant to collect methods common for regions as well as working groups */
class GroupGateway extends BaseGateway
{
	public function deleteGroup($id)
	{
		$parent_id = $this->db->fetchValueByCriteria(
			'fs_bezirk',
			'parent_id',
			['id' => $id]
		);

		$this->db->update(
			'fs_foodsaver',
			['bezirk_id' => null],
			['bezirk_id' => $id]
		);
		$this->db->update(
			'fs_bezirk',
			['parent_id' => 0],
			['parent_id' => $id]
		);

		$this->db->delete('fs_bezirk', ['id' => $id]);

		$count = $this->db->fetchValue('SELECT COUNT(`id`) FROM fs_bezirk WHERE `parent_id` = :id', [':id' => $parent_id]);

		if ($count == 0) {
			$this->db->update(
				'fs_bezirk',
				['has_children' => 0],
				['id' => $parent_id]
			);
		}
	}
}
