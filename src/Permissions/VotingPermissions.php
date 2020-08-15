<?php

namespace Foodsharing\Permissions;

use Exception;
use Foodsharing\Lib\Session;
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
				$regionId = $this->votingGateway->getPoll($pollId)->regionId;
			} catch (Exception $e) {
				// thrown if the poll does not exist
				return false;
			}
		}

		return $this->session->mayBezirk($regionId);
	}

	public function mayVote(int $pollId): bool
	{
		try {
			return $this->votingGateway->mayUserVote($pollId, $this->session->id());
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
