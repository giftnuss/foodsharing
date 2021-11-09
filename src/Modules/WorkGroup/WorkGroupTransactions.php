<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Modules\Region\ForumFollowerGateway;

class WorkGroupTransactions
{
	private WorkGroupGateway $workGroupGateway;
	private ForumFollowerGateway $forumFollowerGateway;

	public function __construct(
		WorkGroupGateway $workGroupGateway,
		ForumFollowerGateway $forumFollowerGateway
	) {
		$this->workGroupGateway = $workGroupGateway;
		$this->forumFollowerGateway = $forumFollowerGateway;
	}

	/**
	 * Removes a user from a working group and cancels the forum subscriptions.
	 *
	 * @throws \Exception
	 */
	public function removeMemberFromGroup(int $groupId, int $memberId): void
	{
		$this->forumFollowerGateway->deleteForumSubscriptions($groupId, [$memberId], false);
		$this->workGroupGateway->removeFromGroup($groupId, $memberId);
	}
}
