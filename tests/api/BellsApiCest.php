<?php

namespace Foodsharing\api;

use ApiTester;
use Codeception\Util\HttpCode;

class BellsApiCest
{
	private $user;
	private $bells;

	public function _before(ApiTester $I)
	{
		$this->user = $I->createFoodsharer();

		$this->bells = [];
		foreach (range(0, 10) as $_) {
			$this->bells[] = $I->addBells([$this->user]);
		}
	}

	public function canRequestBells(ApiTester $I)
	{
		$I->sendGET('api/bells');
		$I->seeResponseCodeIs(HttpCode::FORBIDDEN);

		$I->login($this->user['email']);
		$I->sendGET('api/bells');
		$I->seeResponseCodeIs(HttpCode::OK);
	}

	public function canMarkBellsAsRead(ApiTester $I)
	{
		$bellIds = array_slice($this->bells, 0, 3);

		$I->sendPATCH('api/bells', ['ids' => $bellIds]);
		$I->seeResponseCodeIs(HttpCode::FORBIDDEN);

		$I->login($this->user['email']);
		$I->sendPATCH('api/bells', ['ids' => $bellIds]);
		$I->seeResponseCodeIs(HttpCode::OK);
		foreach ($bellIds as $id) {
			$I->seeInDatabase('fs_foodsaver_has_bell', [
				'foodsaver_id' => $this->user['id'],
				'bell_id' => $id,
				'seen' => 1
			]);
		}

		foreach (array_diff($this->bells, $bellIds) as $id) {
			$I->seeInDatabase('fs_foodsaver_has_bell', [
				'foodsaver_id' => $this->user['id'],
				'bell_id' => $id,
				'seen' => 0
			]);
		}
	}

	public function canDeleteBell(ApiTester $I)
	{
		$I->sendDELETE('api/bells/' . $this->bells[0]);
		$I->seeResponseCodeIs(HttpCode::FORBIDDEN);

		$I->seeInDatabase('fs_foodsaver_has_bell', [
			'foodsaver_id' => $this->user['id'],
			'bell_id' => $this->bells[0]
		]);

		$I->login($this->user['email']);
		$I->sendDELETE('api/bells/' . $this->bells[0]);
		$I->seeResponseCodeIs(HttpCode::OK);

		$I->dontSeeInDatabase('fs_foodsaver_has_bell', [
			'foodsaver_id' => $this->user['id'],
			'bell_id' => $this->bells[0]
		]);
	}
}
