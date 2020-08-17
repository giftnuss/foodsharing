<?php

namespace api;

use Codeception\Util\HttpCode as Http;

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
		$this->poll = $I->createPoll($this->region['id'], $this->userAuthor['id']);
		foreach (range(0, 3) as $_) {
			$I->createPollOption($this->poll['id']);
		}
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
}
