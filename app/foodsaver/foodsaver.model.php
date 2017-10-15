<?php

class FoodsaverModel extends Model
{
	public function listFoodsaver($bezirk_id, $inactive = 0)
	{
		$date = new DateTime('NOW');
		if ($inactive == 1) {
			$date = new DateTime('NOW -6 MONTH');
		}

		return $this->q('
		    SELECT
			fs.id,
			fs.name,
			fs.nachname,
			fs.photo,
			fs.sleep_status,
			CONCAT("#",fs.id) AS href
			 
		    FROM
			' . PREFIX . 'foodsaver fs,
			' . PREFIX . 'foodsaver_has_bezirk hb
			 
		    WHERE
			fs.id = hb.foodsaver_id
			 
		    AND
			fs.deleted_at IS NULL

		    AND
			hb.bezirk_id = ' . (int)$bezirk_id . '

		    AND 
			fs.last_login <  "' . $date->format("Y-m-d H:i:s") . '"
		    OR
			fs.last_login IS NULL

		    ORDER BY
			fs.last_login DESC
		');
	}

	/**
	 * Adds a list of foodsaver to an defined bezirk.
	 *
	 * @param array $foodsaver_ids
	 * @param int $bezirk_id
	 */
	public function addFoodsaverToBezirk($foodsaver_ids, $bezirk_id)
	{
		$values = array();

		foreach ($foodsaver_ids as $id) {
			$values[] = '(' . (int)$bezirk_id . ',' . (int)$id . ',1)';
		}

		return $this->insert('
			INSERT IGNORE INTO ' . PREFIX . 'foodsaver_has_bezirk
			(
				bezirk_id,
				foodsaver_id,
				active
			)
			VALUES
			' . implode(',', $values) . '
		');
	}

	public function delfrombezirk($bezirk_id, $foodsaver_id)
	{
		$this->del('
			DELETE FROM
				' . PREFIX . 'botschafter

			WHERE
				bezirk_id = ' . (int)$bezirk_id . '

			AND
				foodsaver_id = ' . (int)$foodsaver_id . '
		');

		return $this->del('
			DELETE FROM
				' . PREFIX . 'foodsaver_has_bezirk

			WHERE
				bezirk_id = ' . (int)$bezirk_id . '

			AND
				foodsaver_id = ' . (int)$foodsaver_id . '
		');
	}

	public function loadFoodsaver($fsid)
	{
		return $this->qRow('
			SELECT
				id,
				name,
				nachname,
				photo,
				rolle,
				geschlecht,
				last_login

			FROM
				' . PREFIX . 'foodsaver

			WHERE
				id = ' . (int)$fsid . '

            AND
                deleted_at IS NULL
		');
	}
}
