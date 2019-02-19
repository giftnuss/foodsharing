<?php
/**
 * Created by IntelliJ IDEA.
 * User: matthias
 * Date: 19.02.19
 * Time: 12:11.
 */

namespace Foodsharing\api;

class GroupApiCest
{
	private $region;

	public function _before(\ApiTester $I)
	{
		$this->region = $I->createRegion();
	}

	public function deleteGroupFailsForAmbassador(\ApiTester $I)
	{
		$ambassador = $I->createAmbassador();
		$I->login($ambassador['email']);
		$I->sendDELETE("api/groups/{$this->region['id']}");
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
		$I->seeResponseIsJson();
		$I->seeInDatabase('fs_bezirk', ['id' => $this->region['id']]);
	}

	public function deleteGroupWorksForOrga(\ApiTester $I)
	{
		$orga = $I->createOrga();
		$I->login($orga['email']);
		$I->sendDELETE("api/groups/{$this->region['id']}");
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$I->dontSeeInDatabase('fs_bezirk', ['id' => $this->region['id']]);
	}
}
