<?php

namespace Foodsharing\Permissions;

use DateTime;
use Exception;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingScope;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Voting\DTO\Poll;
use Foodsharing\Modules\Voting\VotingGateway;

final class VotingPermissions
{
	private Session $session;
	private VotingGateway $votingGateway;
	private RegionGateway $regionGateway;
	private StoreGateway $storeGateway;

	public function __construct(
		Session $session,
		VotingGateway $votingGateway,
		RegionGateway $regionGateway,
		StoreGateway $storeGateway)
	{
		$this->session = $session;
		$this->votingGateway = $votingGateway;
		$this->regionGateway = $regionGateway;
		$this->storeGateway = $storeGateway;
	}

	public function maySeePoll(Poll $poll): bool
	{
		return $this->session->mayBezirk($poll->regionId);
	}

	public function mayListPolls(int $regionId): bool
	{
		return $this->session->mayBezirk($regionId);
	}

	public function maySeeResults(Poll $poll): bool
	{
		return $poll->endDate < new DateTime();
	}

	public function mayVote(Poll $poll): bool
	{
		// only as member of the region
		if (!$this->session->mayBezirk($poll->regionId)) {
			return false;
		}

		// only in ongoing polls
		$now = new DateTime();
		if ($poll->startDate > $now || $poll->endDate < $now) {
			return false;
		}

		// constraints by the poll's scope
		switch ($poll->scope) {
			case VotingScope::AMBASSADORS:
				if (!$this->session->isAmbassadorForRegion([$poll->regionId])) {
					return false;
				}
				break;
			case VotingScope::STORE_MANAGERS:
				if (!$this->session->may('bieb')) {
					return false;
				}
				if (!in_array($this->session->id(), $this->storeGateway->getStoreManagersOf($poll->regionId))) {
					return false;
				}
				break;
			case VotingScope::VERIFIED_FOODSAVERS:
				if (!$this->session->may('fs') || !$this->session->isVerified()) {
					return false;
				}
				break;
			case VotingScope::FOODSAVERS:
				if (!$this->session->may('fs')) {
					return false;
				}
				break;
			case VotingScope::VERIFIED_FOODSAVERS_HOME_DISTRICT:
				if (!$this->session->may('fs') || !$this->session->isVerified()
					|| $this->session->getCurrentRegionId() !== $poll->regionId) {
					return false;
				}
				break;
		}

		// only if not voted yet
		try {
			return $this->votingGateway->hasUserVoted($poll->id, $this->session->id()) === null;
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
		if ($type == Type::WORKING_GROUP) {
			return $this->session->isAdminFor($regionId);
		} else {
			$votingGroup = $this->regionGateway->getRegionVotingGroupId($regionId);

			return $this->session->isAdminFor($votingGroup);
		}
	}

	public function mayDeletePoll(int $pollId): bool
	{
		return $this->session->may('orga');
	}
}
