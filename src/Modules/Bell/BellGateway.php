<?php

namespace Foodsharing\Modules\Bell;

use Foodsharing\Modules\Core\BaseGateway;

class BellGateway extends BaseGateway
{
	public function addBell($foodsaver_ids, $title, $body, $icon, $link_attributes, $vars, $identifier = '', $closeable = 1)
	{
		if (!is_array($foodsaver_ids)) {
			$foodsaver_ids = array($foodsaver_ids);
		}

		if ($link_attributes !== false) {
			$link_attributes = serialize($link_attributes);
		}

		if ($vars !== false) {
			$vars = serialize($vars);
		}

		$bid = $this->db->insert(
			'fs_bell',
			[
				'name' => strip_tags($title),
				'body' => strip_tags($body),
				'vars' => strip_tags($vars),
				'attr' => strip_tags($link_attributes),
				'icon' => strip_tags($icon),
				'identifier' => strip_tags($identifier),
				'time' => date('Y-m-d H:i:s'),
				'closeable' => $closeable
			]
		);

		$values = array();
		foreach ($foodsaver_ids as $id) {
			if (is_array($id)) {
				$id = $id['id'];
			}

			$values[] = '(' . (int)$id . ',' . (int)$bid . ',0)';
		}
		$this->db->execute('INSERT INTO `fs_foodsaver_has_bell`(`foodsaver_id`, `bell_id`, `seen`) VALUES ' . implode(',', $values));
	}
}
