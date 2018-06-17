<?php

namespace Foodsharing\Modules\Basket;

use Foodsharing\Modules\Core\Model;

class BasketModel extends Model
{
	public function addTypes($basket_id, $types)
	{
		if (!empty($types)) {
			$sql = array();

			foreach ($types as $t) {
				$sql[] = '(' . (int)$basket_id . ',' . (int)$t . ')';
			}

			$this->insert('
				INSERT INTO `fs_basket_has_types`
				(
					`basket_id`, 
					`types_id`
				) 
				VALUES 
				' . implode(',', $sql) . '
			');
		}
	}

	public function addArt($basket_id, $types)
	{
		if (!empty($types)) {
			$sql = array();

			foreach ($types as $t) {
				$sql[] = '(' . (int)$basket_id . ',' . (int)$t . ')';
			}

			$this->insert('
			INSERT INTO `fs_basket_has_art`
			(
				`basket_id`,
				`art_id`
			)
			VALUES
				' . implode(',', $sql) . '
			');
		}
	}
}
