<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Region\RegionGateway;

class ProfilePermissions
{
	private Session $session;
	private RegionGateway $regionGateway;

	public function __construct(Session $session, RegionGateway $regionGateway)
	{
		$this->session = $session;
		$this->regionGateway = $regionGateway;
	}

	public function mayAdministrateUserProfile(int $userId, ?int $regionId = null): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}

		if (!$this->session->isAmbassador()) {
			return false;
		}

		if ($regionId !== null && $this->session->isAdminFor($regionId)) {
			return true;
		}

		$regionIds = $this->regionGateway->getFsRegionIds($userId);

		return $this->session->isAmbassadorForRegion($regionIds, false, true);
	}

	public function mayChangeUserVerification(int $userId): bool
	{
		return $this->mayAdministrateUserProfile($userId);
	}

	public function maySeeHistory(int $fsId): bool
	{
		return $this->mayAdministrateUserProfile($fsId);
	}

	public function maySeeUserNotes(int $userId): bool
	{
		return $this->session->may('orga');
	}

	public function maySeePickups(int $fsId): bool
	{
		return $this->session->id() == $fsId || $this->mayAdministrateUserProfile($fsId);
	}

	public function maySeeEmailAddress(int $fsId): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}

		return $this->session->id() == $fsId;
	}

	public function maySeePrivateEmail(int $userId): bool
	{
		return $this->session->may('orga');
	}

	public function maySeeLastLogin(int $userId): bool
	{
		return $this->session->may('orga');
	}

	public function maySeeRegistrationDate(int $userId): bool
	{
		return $this->session->may('orga');
	}

	public function maySeeFetchRate(int $fsId): bool
	{
		return false;
	}

	public function mayDeleteUser(int $userId): bool
	{
		return $this->session->id() == $userId || $this->session->may('orga');
	}

	public function maySeeBounceWarning(int $userId): bool
	{
		return $this->session->id() == $userId || $this->session->may('orga');
	}
}
