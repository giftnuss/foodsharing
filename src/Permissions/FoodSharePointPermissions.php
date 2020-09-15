<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Region\RegionGateway;

class FoodSharePointPermissions
{
	private Session $session;
	private RegionGateway $regionGateway;

	public function __construct(
		Session $session,
		RegionGateway $regionGateway)
	{
		$this->session = $session;
		$this->regionGateway = $regionGateway;
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
		if ($this->session->isOrgaTeam()) {
			return true;
		}

		$fspGroup = $this->regionGateway->getRegionFunctionGroupId($regionId, WorkgroupFunction::FSP);

		if (empty($fspGroup)) {
			if ($this->session->isAdminFor($regionId)) {
				return true;
			}
		} elseif ($this->session->isAdminFor($fspGroup)) {
			return true;
		}

		return false;
	}

	public function mayEdit(int $regionId, array $follower): bool
	{
		return $this->mayAdd($regionId) || (
			isset($follower['all'][$this->session->id()]) &&
			$follower['all'][$this->session->id()] === 'fsp_manager'
		);
	}

	public function mayDeleteFoodSharePointOfRegion(int $regionId): bool
	{
		return $this->mayAdd($regionId);
	}

	public function mayApproveFoodSharePointCreation(int $regionId): bool
	{
		return $this->mayAdd($regionId);
	}

	public function mayDeleteFoodSharePointWallPostofRegion(int $regionId) {

		if ($this->session->isOrgaTeam()) {
			return true;
		}

		$fspGroup = $this->regionGateway->getRegionFunctionGroupId($regionId, WorkgroupFunction::FSP);
		if (!empty($fspGroup)) {
			if ($this->session->isAdminFor($fspGroup)) {
				return true;
			}
		}

		return false;
	}
}
