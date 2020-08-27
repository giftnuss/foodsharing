<?php

namespace Foodsharing\Modules\Voting;

use DateTime;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingType;
use Foodsharing\Modules\Core\View;
use Foodsharing\Modules\Voting\DTO\Poll;

class VotingView extends View
{
	public function pollOverview(Poll $poll, array $region, bool $mayVote, ?DateTime $userVoteDate)
	{
		return $this->vueComponent('poll-overview', 'pollOverview', [
			'poll' => $poll,
			'numValues' => VotingType::getNumberOfValues($poll->type),
			'regionName' => $region['name'],
			'isWorkGroup' => $region['type'] === Type::WORKING_GROUP,
			'mayVote' => $mayVote,
			'userVoteDate' => $userVoteDate
		]);
	}

	public function newPollForm(array $region)
	{
		return $this->vueComponent('new-poll-form', 'newPollForm', [
			'region' => $region
		]);
	}
}
