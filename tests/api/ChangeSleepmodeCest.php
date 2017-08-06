<?php
namespace api;


class ChangeSleepmodeCest
{
	//https://foodsharing.de/?page=settings&sub=sleeping
	public function pageDisplaysWithNullValues(\ApiTester $I)
	{
		$user = $I->createFoodsaver(null, array('sleep_from' => null, 'sleep_status' => 1));
		$I->login($user['email']);
		$request = array('page' => 'settings',
			'sub' => 'sleeping');
		$I->sendGET('/', $request);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
	}

}
