<?php

namespace Foodsharing\Permissions;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use DateInterval;
use DateTime;
use Exception;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Group\GroupFunctionGateway;
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
	private GroupFunctionGateway $groupFunctionGateway;

	public function __construct(
		Session $session,
		VotingGateway $votingGateway,
		RegionGateway $regionGateway,
		StoreGateway $storeGateway,
		GroupFunctionGateway $groupFunctionGateway
	) {
		$this->session = $session;
		$this->votingGateway = $votingGateway;
		$this->regionGateway = $regionGateway;
		$this->storeGateway = $storeGateway;
		$this->groupFunctionGateway = $groupFunctionGateway;
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

		// only if not voted yet
		try {
			return $this->votingGateway->getVoteDatetime($poll->id, $this->session->id()) === null;
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
			$votingGroup = $this->groupFunctionGateway->getRegionFunctionGroupId($regionId, WorkgroupFunction::VOTING);

			return !empty($votingGroup) && $this->session->isAdminFor($votingGroup);
		}
	}

	public function mayDeletePoll(int $pollId): bool
	{
		return $this->session->may('orga');
	}

	public function mayEditPoll(Poll $poll): bool
	{
		// polls can be edited by the author during the first hour after creating the poll
		if ($this->session->id() != $poll->authorId) {
			return false;
		}

		return $poll->creationDate->add($this->editTimeAfterPollCreation()) > Carbon::now();
	}

	/**
	 * Returns the interval during which a poll can be edited after its creation. This also defined the
	 * poll's minimum start time.
	 */
	public function editTimeAfterPollCreation(): DateInterval
	{
		return CarbonInterval::hours(1);
	}
}
