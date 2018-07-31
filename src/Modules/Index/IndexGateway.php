<?php

namespace Foodsharing\Modules\Index;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;

class IndexGateway extends BaseGateway
{
	public function getFetchedWeight()
	{
		$stm = 'SELECT stat_fetchweight FROM fs_bezirk WHERE id = :region_id';
		
		return $this->db->fetchValue($stm, [':region_id' => RegionIDs::EUROPE]);
	}
}
