<?php

namespace api;

use Codeception\Util\HttpCode as Http;
use DateTime;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingType;

/**
 * Tests for the voting api.
 */
class VotingApiCest
{
	private $region;
	private $userAuthor;
	private $userVoter;
	private $userNonVoter;
	private $poll;

	private const POLLS_API = 'api/polls';
	private const GROUPS_API = 'api/groups';

	public function _before(\ApiTester $I)
	{
		$this->region = $I->createRegion();
		$this->userAuthor = $I->createFoodsaver(null, ['bezirk_id' => $this->region['id']]);
		$this->userVoter = $I->createFoodsaver(null, ['bezirk_id' => $this->region['id']]);
		$this->userNonVoter = $I->createFoodsaver(null, ['bezirk_id' => $this->region['id']]);
		$this->poll = $this->createPoll($I, ['type' => VotingType::SELECT_ONE_CHOICE]);
		$I->addPollVoter($this->poll['id'], $this->userAuthor['id']);
		$I->addPollVoter($this->poll['id'], $this->userVoter['id']);
	}

	public function canOnlyGetPollsAsRegionMember(\ApiTester $I)
	{
		$I->sendGET(self::POLLS_API . '/' . $this->poll['id']);
		$I->seeResponseCodeIs(Http::FORBIDDEN);

		$I->sendGET(self::GROUPS_API . '/' . $this->region['id'] . '/polls');
		$I->seeResponseCodeIs(Http::FORBIDDEN);

		$I->login($this->userNonVoter['email']);
		$I->sendGET(self::POLLS_API . '/' . $this->poll['id']);
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();

		$I->sendGET(self::GROUPS_API . '/' . $this->region['id'] . '/polls');
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
	}

	public function canNotGetNonExistingPoll(\ApiTester $I)
	{
		$I->login($this->userVoter['email']);
		$I->sendGET(self::POLLS_API . '/' . ($this->poll['id'] + 1));
		$I->seeResponseCodeIs(Http::NOT_FOUND);
	}

	public function canOnlyVoteOnce(\ApiTester $I)
	{
		$choice = 0;
		$upvotes = $I->grabFromDatabase('fs_poll_has_options', 'upvotes', [
			'poll_id' => $this->poll['id'],
			'option' => $choice
		]);

		$I->login($this->userVoter['email']);
		$I->sendPUT(self::POLLS_API . '/' . $this->poll['id'] . '/vote', ['options' => [$choice => 1]]);
		$I->seeResponseCodeIs(Http::OK);
		$I->sendPUT(self::POLLS_API . '/' . $this->poll['id'] . '/vote', ['options' => [$choice => 1]]);
		$I->seeResponseCodeIs(Http::FORBIDDEN);

		$I->seeInDatabase('fs_poll_has_options', [
			'poll_id' => $this->poll['id'],
			'option' => $choice,
			'upvotes' => $upvotes + 1
		]);
	}

	public function canNotVoteAsNonvoter(\ApiTester $I)
	{
		$choice = 0;
		$upvotes = $I->grabFromDatabase('fs_poll_has_options', 'upvotes', [
			'poll_id' => $this->poll['id'],
			'option' => $choice
		]);

		$I->login($this->userNonVoter['email']);
		$I->sendPUT(self::POLLS_API . '/' . $this->poll['id'] . '/vote', ['options' => [0 => 1]]);
		$I->seeResponseCodeIs(Http::FORBIDDEN);

		$I->seeInDatabase('fs_poll_has_options', [
			'poll_id' => $this->poll['id'],
			'option' => $choice,
			'upvotes' => $upvotes
		]);
	}

	public function canOnlyVoteInOngoingPoll(\ApiTester $I)
	{
		$I->login($this->userVoter['email']);

		// create outdated poll
		$poll = $this->createPoll($I, [
			'type' => VotingType::SELECT_ONE_CHOICE,
			'end' => (new DateTime('now - 1 day'))->format('Y-m-d H:i:s')
		]);
		$I->addPollVoter($poll['id'], $this->userVoter['id']);

		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 1]
		]);
		$I->seeResponseCodeIs(Http::FORBIDDEN);

		// create future poll
		$poll2 = $this->createPoll($I, [
			'type' => VotingType::SELECT_ONE_CHOICE,
			'start' => (new DateTime('now + 1 day'))->format('Y-m-d H:i:s')
		]);
		$I->addPollVoter($poll2['id'], $this->userVoter['id']);

		$I->sendPUT(self::POLLS_API . '/' . $poll2['id'] . '/vote', [
			'options' => [0 => 1]
		]);
		$I->seeResponseCodeIs(Http::FORBIDDEN);
	}

	public function canUseSingleSelection(\ApiTester $I)
	{
		// create poll with single selection
		$poll = $this->createPoll($I, ['type' => VotingType::SELECT_ONE_CHOICE]);
		$I->addPollVoter($poll['id'], $this->userVoter['id']);

		// vote with different numbers of options
		$I->login($this->userVoter['email']);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 1, 1 => 0, 2 => 1]
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => []
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [1 => 1]
		]);
		$I->seeResponseCodeIs(Http::OK);
	}

	public function canUseMultipleSelection(\ApiTester $I)
	{
		// create poll with multi-selection
		$poll = $this->createPoll($I, ['type' => VotingType::SELECT_MULTIPLE]);
		$I->addPollVoter($poll['id'], $this->userVoter['id']);

		// vote with different numbers of options
		$I->login($this->userVoter['email']);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 1, 1 => 0]
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 1, 1 => 0, 2 => 1, 3 => 0, 4 => 1, 5 => 0]
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => []
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [1 => 1, 2 => 1]
		]);
		$I->seeResponseCodeIs(Http::OK);
	}

	public function canUseScoreVoting(\ApiTester $I)
	{
		// create poll with score voting
		$poll = $this->createPoll($I, ['type' => VotingType::SCORE_VOTING]);
		$I->addPollVoter($poll['id'], $this->userVoter['id']);

		// vote with different numbers of options
		$I->login($this->userVoter['email']);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 1, 1 => 0]
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 1, 1 => 0, 2 => 1, 3 => 0, 4 => 1, 5 => 0]
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => []
		]);
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->sendPUT(self::POLLS_API . '/' . $poll['id'] . '/vote', [
			'options' => [0 => 0, 1 => 1, 2 => -1, 3 => 0]
		]);
		$I->seeResponseCodeIs(Http::OK);
	}

	public function orgaCanDeletePoll(\ApiTester $I)
	{
		$poll = $this->createPoll($I, ['type' => VotingType::SCORE_VOTING]);
		$I->login($this->userAuthor['email']);
		$I->sendDELETE(self::POLLS_API . '/' . $poll['id']);
		$I->seeResponseCodeIs(Http::FORBIDDEN);
		$I->seeInDatabase('fs_poll', ['id' => $poll['id']]);

		$userOrga = $I->createOrga();
		$I->login($userOrga['email']);
		$I->sendDELETE(self::POLLS_API . '/' . $poll['id']);
		$I->seeResponseCodeIs(Http::OK);
		$I->cantSeeInDatabase('fs_poll', ['id' => $poll['id']]);
	}

	private function createPoll(\ApiTester $I, array $params = []): array
	{
		$poll = $I->createPoll($this->region['id'], $this->userAuthor['id'], $params);
		foreach (range(0, 3) as $_) {
			$I->createPollOption($poll['id']);
		}

		return $poll;
	}
}
