<?php

namespace Foodsharing\Permissions;

use DateTime;
use Exception;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Voting\DTO\Poll;
use Foodsharing\Modules\Voting\VotingGateway;

final class VotingPermissions
{
	private $session;
	private $votingGateway;

	public function __construct(Session $session, VotingGateway $votingGateway)
	{
		$this->session = $session;
		$this->votingGateway = $votingGateway;
	}

	public function mayListPolls(int $regionId): bool
	{
		return $this->session->mayBezirk($regionId);
	}

	public function maySeePoll(int $pollId, int $regionId = null): bool
	{
		if (is_null($regionId)) {
			try {
				$regionId = $this->votingGateway->getPoll($pollId, false)->regionId;
			} catch (Exception $e) {
				// thrown if the poll does not exist
				return false;
			}
		}

		return $this->session->mayBezirk($regionId);
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
		return $this->session->mayBezirk($regionId);
	}

	public function mayDeletePoll(int $pollId): bool
	{
		return $this->session->may('orga');
	}
}
