<?php

namespace Foodsharing\Permissions;

use DateTime;
use Exception;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Voting\DTO\Poll;
use Foodsharing\Modules\Voting\VotingGateway;

final class VotingPermissions
{
	private Session $session;
	private VotingGateway $votingGateway;
	private RegionGateway $regionGateway;

	/**
	 * In regions with one of these types all verified foodsavers are allowed to create polls. In other regions only
	 * ambassadors are allowed to do so.
	 */
	private const LOWER_REGIONS = [Type::CITY, Type::DISTRICT, Type::REGION, Type::PART_OF_TOWN, Type::BIG_CITY,
		Type::WORKING_GROUP];

	public function __construct(
		Session $session,
		VotingGateway $votingGateway,
		RegionGateway $regionGateway)
	{
		$this->session = $session;
		$this->votingGateway = $votingGateway;
		$this->regionGateway = $regionGateway;
	}

	public function maySeeResults(Poll $poll): bool
	{
		return $poll->endDate < new DateTime();
	}

	public function mayVote(Poll $poll): bool
	{
		$now = new DateTime();
		if ($poll->startDate > $now || $poll->endDate < $now) {
			return false;
		}

		try {
			return $this->votingGateway->mayUserVote($poll->id, $this->session->id());
		} catch (Exception $e) {
			return false;
		}
	}

	public function mayCreatePoll(int $regionId): bool
	{
		if (!$this->session->mayBezirk($regionId) || !$this->session->isVerified()) {
			return false;
		}

		$type = $this->regionGateway->getType($regionId);
		if (in_array($type, self::LOWER_REGIONS)) {
			return true;
		} else {
			return $this->session->isAdminFor($regionId);
		}
	}

	public function mayDeletePoll(int $pollId): bool
	{
		return $this->session->may('orga');
	}
}
