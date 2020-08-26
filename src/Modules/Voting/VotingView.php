<?php

namespace Foodsharing\Modules\Voting;

use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Core\View;
use Foodsharing\Modules\Voting\DTO\Poll;

class VotingView extends View
{
	public function pollOverview(Poll $poll, array $region, bool $mayVote)
	{
		return $this->vueComponent('poll-overview', 'pollOverview', [
			'poll' => $poll,
			'regionName' => $region['name'],
			'isWorkGroup' => $region['type'] === Type::WORKING_GROUP,
			'mayVote' => $mayVote
		]);
	}

	public function newPollForm(array $region)
	{
		return $this->vueComponent('new-poll-form', 'newPollForm', [
			'region' => $region
		]);
	}
}
