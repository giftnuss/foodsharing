<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Group\GroupFunctionGateway;
use Foodsharing\Modules\Region\RegionGateway;

class FoodSharePointPermissions
{
	private Session $session;
	private RegionGateway $regionGateway;
	private GroupFunctionGateway $groupFunctionGateway;

	public function __construct(
		Session $session,
		RegionGateway $regionGateway,
		GroupFunctionGateway $groupFunctionGateway
	) {
		$this->session = $session;
		$this->regionGateway = $regionGateway;
		$this->groupFunctionGateway = $groupFunctionGateway;
	}

	public function mayFollow(): bool
	{
		return $this->session->may();
	}

	public function mayUnfollow(int $fspId): bool
	{
		// TODO this should not be allowed if user is manager of the FSP
		return $this->mayFollow();
	}

	public function mayAdd(int $regionId): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}

		$fspGroup = $this->groupFunctionGateway->getRegionFunctionGroupId($regionId, WorkgroupFunction::FSP);
		if (!empty($fspGroup)) {
			return $this->session->isAdminFor($fspGroup);
		}

		return $this->session->isAdminFor($regionId);
	}

	public function mayEdit(int $regionId, array $follower): bool
	{
		if ($this->mayAdd($regionId)) {
			return true;
		}
		if (isset($follower['all'][$this->session->id()])) {
			return $follower['all'][$this->session->id()] === 'fsp_manager';
		}

		return false;
	}

	public function mayDeleteFoodSharePointOfRegion(int $regionId): bool
	{
		return $this->mayAdd($regionId);
	}

	public function mayApproveFoodSharePointCreation(int $regionId): bool
	{
		return $this->mayAdd($regionId);
	}

	public function mayDeleteFoodSharePointWallPostOfRegion(?int $regionId)
	{
		if ($this->session->may('orga')) {
			return true;
		}

		$fspGroup = $this->groupFunctionGateway->getRegionFunctionGroupId($regionId, WorkgroupFunction::FSP);
		if (!empty($fspGroup)) {
			return $this->session->isAdminFor($fspGroup);
		}

		return false;
	}
}
