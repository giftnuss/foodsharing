<?php

namespace Foodsharing\Modules\Core;

use Foodsharing\Lib\Db\ManualDb;

class Model extends ManualDb
{
	public function getContent($id)
	{
		if ($cnt = $this->qRow('
			SELECT 	`title`,`body` FROM fs_content WHERE `id` = ' . (int)$id . '
		')
		) {
			return $cnt;
		}

		return false;
	}

	/**
	 * @deprecated
	 * @see \Foodsharing\Modules\Region\RegionGateway::listForFoodsaver()
	 */
	public function getBezirke()
	{
		if (isset($_SESSION['client']['bezirke']) && is_array($_SESSION['client']['bezirke'])) {
			return $_SESSION['client']['bezirke'];
		}
	}

	public function buddyRequest($fsid)
	{
		$this->insert('
			REPLACE INTO `fs_buddy`(`foodsaver_id`, `buddy_id`, `confirmed`)
			VALUES (' . (int)$this->func->fsId() . ',' . (int)$fsid . ',0)
		');

		return true;
	}

	public function confirmBuddy($fsid)
	{
		$this->insert('
			REPLACE INTO `fs_buddy`(`foodsaver_id`, `buddy_id`, `confirmed`)
			VALUES (' . (int)$this->func->fsId() . ',' . (int)$fsid . ',1)
		');
		$this->insert('
			REPLACE INTO `fs_buddy`(`foodsaver_id`, `buddy_id`, `confirmed`)
			VALUES (' . (int)$fsid . ',' . (int)$this->func->fsId() . ',1)
		');
	}

	public function delBells($identifier)
	{
		if ($bells = $this->q('SELECT id FROM fs_bell WHERE identifier = ' . $this->strval($identifier))) {
			$ids = array();
			foreach ($bells as $b) {
				$ids[(int)$b['id']] = (int)$b['id'];
			}

			$ids = implode(',', $ids);

			$this->del('DELETE FROM fs_foodsaver_has_bell WHERE bell_id IN(' . $ids . ')');
			$this->del('DELETE FROM fs_bell WHERE id IN(' . $ids . ')');
		}
	}
}
