<?php

namespace Foodsharing\Modules\Foodsaver;

use DateTime;
use Foodsharing\Modules\Core\Model;
use Foodsharing\Modules\Store\StoreModel;

class FoodsaverModel extends Model
{
	public function listFoodsaver($bezirk_id, $showOnlyInactive = false)
	{
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
			hb.bezirk_id = ' . (int)$bezirk_id .
			($showOnlyInactive ? '
		    AND (
			fs.last_login <  "' . (new DateTime('NOW -6 MONTH'))->format('Y-m-d H:i:s') . '"
		    OR
			fs.last_login IS NULL)' : '') . '
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

	public function update_foodsaver($id, $data, StoreModel $storeModel)
	{
		$data['anmeldedatum'] = date('Y-m-d H:i:s');

		if (!isset($data['bezirk_id'])) {
			$data['bezirk_id'] = $this->func->getBezirkId();
		}

		$orga = '';
		if (isset($data['orgateam'])) {
			$orga = '`orgateam` = ' . $this->intval($data['orgateam']) . ',';
		}

		$rolle = '';
		$quiz_rolle = '';
		$verified = '';
		if (isset($data['rolle'])) {
			$rolle = '`rolle` =  ' . $this->intval($data['rolle']) . ',';
			if ($data['rolle'] == 0 && $this->func->isOrgaTeam()) {
				$data['bezirk_id'] = 0;
				$quiz_rolle = '`quiz_rolle` = 0,';
				$verified = '`verified` = 0,';

				$bids = $this->q('
					SELECT 	bt.betrieb_id as id
					FROM 	' . PREFIX . 'betrieb_team bt
					WHERE 	bt.foodsaver_id = ' . $this->intval($id) . '
				');
				//Delete from Companies
				foreach ($bids as $b) {
					$storeModel->signout($b, $id);
				}

				//Delete Bells for Foodsaver
				$this->del('
					DELETE FROM  `' . PREFIX . 'foodsaver_has_bell`
					WHERE 		`foodsaver_id` = ' . $this->intval($id) . '
				');
				// Delete from Bezirke and Working Groups
				$this->del('
					DELETE FROM  `' . PREFIX . 'foodsaver_has_bezirk`
					WHERE 		`foodsaver_id` = ' . $this->intval($id) . '
				');
				//Delete from Bezirke and Working Groups (when Admin)
				$this->del('
					DELETE FROM  `' . PREFIX . 'botschafter`
					WHERE 		`foodsaver_id` = ' . $this->intval($id) . '
				');

				//Block Person for Quiz
				for ($i = 1; $i <= 7; ++$i) {
					$this->insert('
					INSERT INTO ' . PREFIX . 'quiz_session (
						foodsaver_id,
						quiz_id,
						`status`,
						time_start
					)
					VALUES
					(
						' . $this->intval($id) . ',
						1,
						2,
						now()
					)
				');
				}
			}
		}

		$position = '';
		if (isset($data['position'])) {
			$position = '`position` =  ' . $this->strval($data['position']) . ',';
		}

		$email = '';
		if (isset($data['email'])) {
			$email = '`email` = ' . $this->strval($data['email']) . ',';
		}

		return $this->update('

		UPDATE 	`' . PREFIX . 'foodsaver`

		SET
				`bezirk_id` =  ' . $this->intval($data['bezirk_id']) . ',
				`plz` =  ' . $this->strval(trim($data['plz'])) . ',
				`stadt` =  ' . $this->strval(trim($data['stadt'])) . ',
				`lat` =  ' . $this->strval(trim($data['lat'])) . ',
				`lon` =  ' . $this->strval(trim($data['lon'])) . ',
				`name` =  ' . $this->strval($data['name']) . ',
				`nachname` =  ' . $this->strval($data['nachname']) . ',
				`anschrift` =  ' . $this->strval($data['anschrift']) . ',
				`telefon` =  ' . $this->strval($data['telefon']) . ',
				`handy` =  ' . $this->strval($data['handy']) . ',
				`geschlecht` =  ' . $this->intval($data['geschlecht']) . ',
				' . $position . '
				' . $rolle . '
				' . $orga . '
				' . $email . '
				' . $quiz_rolle . '
				' . $verified . '
				`geb_datum` =  ' . $this->dateval($data['geb_datum']) . '

		WHERE 	`id` = ' . $this->intval($id));
	}
}
