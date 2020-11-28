<?php

namespace Foodsharing\Modules\Voting;

use DateTime;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Core\View;
use Foodsharing\Modules\Voting\DTO\Poll;

class VotingView extends View
{
	public function pollOverview(Poll $poll, array $region, bool $mayVote, ?DateTime $userVoteDate, bool $mayEdit)
	{
		return $this->vueComponent('poll-overview', 'pollOverview', [
			'poll' => $poll,
			'regionName' => $region['name'],
			'isWorkGroup' => $region['type'] === Type::WORKING_GROUP,
			'mayVote' => $mayVote,
			'userVoteDate' => $userVoteDate,
			'mayEdit' => $mayEdit
		]);
	}

	public function newPollForm(array $region)
	{
		return $this->vueComponent('new-poll-form', 'newPollForm', [
			'region' => $region,
			'isWorkGroup' => $region['type'] == Type::WORKING_GROUP
		]);
	}
}
