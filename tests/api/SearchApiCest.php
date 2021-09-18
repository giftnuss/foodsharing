<?php

namespace Foodsharing\api;

use ApiTester;
use Codeception\Util\HttpCode;

class SearchApiCest
{
	private $user;
	private $user1;
	private $user2;
	private $region1;
	private $region2;
	private $userOrga;

	public function _before(ApiTester $I)
	{
		$this->region1 = $I->createRegion();
		$this->region2 = $I->createRegion();

		$this->user = $I->createFoodsaver();
		$I->addRegionMember($this->region1['id'], $this->user['id']);
		$this->user1 = $I->createFoodsaver();
		$I->addRegionMember($this->region1['id'], $this->user1['id']);
		$this->user2 = $I->createFoodsaver();
		$I->addRegionMember($this->region2['id'], $this->user2['id']);

		$this->userOrga = $I->createOrga();
	}

	public function canOnlySearchWhenLoggedIn(ApiTester $I)
	{
		$I->sendGET('api/search/user?q=test');
		$I->seeResponseCodeIs(HttpCode::FORBIDDEN);

		$I->login($this->user['email']);
		$I->sendGET('api/search/user?q=test');
		$I->seeResponseCodeIs(HttpCode::OK);
	}

	public function canSearchInSameRegion(ApiTester $I)
	{
		$I->login($this->user['email']);
		sleep(2);
		$I->sendGET("api/search/user?q={$this->user1['name']}&regionId={$this->region1['id']}");
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson(['id' => $this->user1['id']]);
	}

	public function canNotSearchInOtherRegions(ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendGET("api/search/user?q={$this->user2['name']}&regionId={$this->region2['id']}");
		$I->seeResponseCodeIs(HttpCode::FORBIDDEN);
	}

	public function canSearchInAllRegionsAsOrga(ApiTester $I)
	{
		$I->login($this->userOrga['email']);

		$I->sendGET("api/search/user?q={$this->user1['name']}&regionId={$this->region1['id']}");
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->canSeeResponseContainsJson(['id' => $this->user1['id']]);

		$I->sendGET("api/search/user?q={$this->user2['name']}&regionId={$this->region2['id']}");
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->canSeeResponseContainsJson(['id' => $this->user2['id']]);
	}
}
