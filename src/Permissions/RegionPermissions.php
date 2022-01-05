<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Group\GroupFunctionGateway;
use Foodsharing\Modules\Region\RegionGateway;

final class RegionPermissions
{
	private RegionGateway $regionGateway;
	private Session $session;
	private GroupFunctionGateway $groupFunctionGateway;

	public function __construct(RegionGateway $regionGateway, Session $session, GroupFunctionGateway $groupFunctionGateway)
	{
		$this->regionGateway = $regionGateway;
		$this->session = $session;
		$this->groupFunctionGateway = $groupFunctionGateway;
	}

	public function mayJoinRegion(int $regionId): bool
	{
		$type = $this->regionGateway->getType($regionId);

		return $this->session->may('fs') && Type::isAccessibleRegion($type);
	}

	public function mayAdministrateRegions(): bool
	{
		return $this->session->may('orga');
	}

	public function mayAdministrateWorkgroupFunction(int $wgfunction): bool
	{
		if (WorkgroupFunction::isRestrictedWorkgroupFunction($wgfunction)) {
			return $this->session->may('orga') && $this->session->isAdminFor(RegionIDs::CREATING_WORK_GROUPS_WORK_GROUP);
		}

		return true;
	}

	public function mayAccessStatisticCountry(): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}

		return false;
	}

	public function mayHandleFoodsaverRegionMenu(int $regionId): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}

		return $this->session->isAmbassadorForRegion([$regionId], false, false);
	}

	public function maySetRegionOptions(int $regionId): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}

		return $this->session->isAmbassadorForRegion([$regionId], false, false);
	}

	public function maySetRegionPin(int $regionId): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}

		if ($this->groupFunctionGateway->existRegionFunctionGroup($regionId, WorkgroupFunction::PR)) {
			if ($this->groupFunctionGateway->isRegionFunctionGroupAdmin($regionId, WorkgroupFunction::PR, $this->session->id())) {
				return true;
			}

			return false;
		}

		return $this->session->isAmbassadorForRegion([$regionId], false, false);
	}

	public function hasConference(int $regionType): bool
	{
		return in_array($regionType, [Type::COUNTRY, Type::FEDERAL_STATE, Type::CITY, TYPE::WORKING_GROUP, Type::PART_OF_TOWN, Type::DISTRICT, Type::REGION, Type::BIG_CITY]);
	}

	public function mayDeleteFoodsaverFromRegion(int $regionId): bool
	{
		return $this->mayHandleFoodsaverRegionMenu($regionId);
	}

	public function maySeeRegionMembers(int $regionId): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}

		return in_array($regionId, $this->session->listRegionIDs());
	}
}
