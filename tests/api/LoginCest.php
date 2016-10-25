<?php
class LoginCest
{
	/**
	 * @example ["createFoodsaver", "Hallo "]
	 * @example ["createFoodsharer", "Willkommen "]
	 * @example ["createStoreCoordinator", "Hallo "]
	 */
	public function checkLogin(\ApiTester $I, \Codeception\Example $example)
	{
		$pass = sq('pass');
		$user = $I->$example[0]($pass);

		$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

		$I->sendPOST('/?page=login', [
			'email_adress' => $user['email'],
			'password' => $pass
		]);

		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeRegExp('~.*'.$example[1].$user['name'].'.*~i');
		$I->seeHtml();
	}
}
