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

	public function mayDeleteFoodSharePointOfRegion(int $regionId): bool
	{
		return $this->session->isAdminFor($regionId) || $this->session->isOrgaTeam();
	}

	public function mayApproveFoodSharePointCreation(int $regionId): bool
	{
		return $this->mayDeleteFoodSharePointOfRegion($regionId);
	}

	public function mayFollow(): bool
	{
		return $this->session->may();
	}

	public function mayAdd(int $regionId): bool
	{
		return $this->mayDeleteFoodSharePointOfRegion($regionId);
	}

	public function mayEdit(int $regionId, array $follower): bool
	{
		return (isset($regionId) && $this->session->isAdminFor($regionId)) ||
			$this->session->isOrgaTeam() ||
			(
				isset($follower['all'][$this->session->id()]) &&
				$follower['all'][$this->session->id()] === 'fsp_manager'
			);
	}
}
