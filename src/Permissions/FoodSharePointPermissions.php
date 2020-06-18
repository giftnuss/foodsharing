<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;

class FoodSharePointPermissions
{
	private $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
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
		return $this->session->isAdminFor($regionId) || $this->session->isOrgaTeam();
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
}
