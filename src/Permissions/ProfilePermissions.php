<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Region\RegionGateway;

class ProfilePermissions
{
	private $session;
	private $regionGateway;

	public function __construct(Session $session, RegionGateway $regionGateway)
	{
		$this->session = $session;
		$this->regionGateway = $regionGateway;
	}

	public function mayAdministrateUserProfile($fsId, $regionId = 0): bool
	{
		if (!$this->session->may()) {
			return false;
		}

		if ($regionId != 0 && $this->session->isAdminFor($regionId)) {
			return  true;
		}

		if ($this->session->may('orga')) {
			return true;
		}

		if ($this->session->isAmbassador()) {
			$regionIDs = $this->regionGateway->getFsRegionIds($fsId);

			return $this->session->isAmbassadorForRegion($regionIDs, false, true);
		}

		return false;
	}

	public function maySeeHistory($fsId): bool
	{
		return $this->mayAdministrateUserProfile($fsId);
	}

	public function maySeeEmailAddress(int $foodsharerId): bool
	{
		return $this->session->id() == $foodsharerId || $this->session->isOrgaTeam();
	}
}
