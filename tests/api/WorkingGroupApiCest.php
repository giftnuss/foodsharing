<?php

namespace Foodsharing\api;

class WorkingGroupApiCest
{
	private $workingGroup;
	private $user;
	private $userOrga;

	public function _before(\ApiTester $I)
	{
		$this->workingGroup = $I->createWorkingGroup('test');
		$this->user = $I->createFoodsaver();
		$this->userOrga = $I->createOrga();
	}

	public function canAddMembersToWorkingGroups(\ApiTester $I)
	{
		$I->login($this->userOrga['email']);
		$I->sendPOST('api/groups/' . $this->workingGroup['id'] . '/members/' . $this->user['id']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	public function canRemoveMembersFromWorkingGroups(\ApiTester $I)
	{
		$I->login($this->userOrga['email']);
		$I->sendDelete('api/groups/' . $this->workingGroup['id'] . '/members/' . $this->user['id']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
	}
}
