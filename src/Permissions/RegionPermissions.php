<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Region\RegionGateway;

final class RegionPermissions
{
	private $regionGateway;
	private $session;

	public function __construct(RegionGateway $regionGateway, Session $session)
	{
		$this->regionGateway = $regionGateway;
		$this->session = $session;
	}

	public function mayJoinRegion(int $regionId): bool
	{
		$type = $this->regionGateway->getType($regionId);

		return $this->session->may('fs') && in_array($type, [Type::CITY, TYPE::REGION, TYPE::PART_OF_TOWN, TYPE::DISTRICT], true);
	}
}
