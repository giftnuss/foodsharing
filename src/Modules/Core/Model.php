<?php

namespace Foodsharing\Modules\Core;

use Foodsharing\Lib\Db\ManualDb;

class Model extends ManualDb
{
	public function mayBezirk($bid)
	{
		if (isset($_SESSION['client']['bezirke'][$bid]) || $this->func->isBotschafter() || $this->func->isOrgaTeam()) {
			return true;
		}

		return false;
	}

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

	public function buddyStatus($fsid)
	{
		if (($status = $this->qOne('SELECT `confirmed` FROM fs_buddy WHERE `foodsaver_id` = ' . (int)$this->func->fsId() . ' AND `buddy_id` = ' . (int)$fsid)) !== false) {
			return $status;
		}

		return -1;
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

	public function updateSleepMode($status, $from, $to, $msg)
	{
		return $this->update('
 			UPDATE 
 				fs_foodsaver 
 				
 			SET	
 				`sleep_status` = ' . (int)$status . ',
 				`sleep_from` = ' . $this->dateval($from) . ',
 				`sleep_until` = ' . $this->dateval($to) . ',
 				`sleep_msg` = ' . $this->strval($msg) . '

 			WHERE 
 				id = ' . (int)$this->func->fsId() . '
 		');
	}

	public function getRealBezirke()
	{
		if ($bezirks = $this->getBezirke()) {
			$out = array();
			foreach ($bezirks as $b) {
				if (in_array($b['type'], array(1, 2, 3, 9))) {
					$out[] = $b;
				}
			}

			return $out;
		}

		return false;
	}
}
