<?php

namespace Foodsharing\Modules\Stats;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;

class StatsGateway extends BaseGateway
{
	private $statsService;

	public function __construct(Database $db, StatsService $statsService)
	{
		$this->statsService = $statsService;

		parent::__construct($db);
	}

	public function fetchAllStores(): array
	{
		return $this->db->fetchAll('SELECT id, name, added FROM fs_betrieb');
	}
}
