<?php

class LoginCest
{
	/**
	 * @example ["createFoodsaver", "Hallo ", "Foodsaver für"]
	 * @example ["createFoodsharer", "Willkommen "]
	 * @example ["createStoreCoordinator", "Hallo ", "Betriebsverantwortlich"]
	 * @example ["createAmbassador", "Hallo ", "Botschafter/In für"]
	 * @example ["createOrga", "Hallo ", "Orgamensch für"]
	 */
	public function checkLogin(\ApiTester $I, \Codeception\Example $example)
	{
		$pass = sq('pass');
		$user = call_user_func(array($I, $example[0]), $pass);

		$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

		$I->sendPOST('/?page=login', [
			'email_adress' => $user['email'],
			'password' => $pass
		]);

		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeHtml();
		$I->seeRegExp('~.*' . $example[1] . $user['name'] . '.*~i');
		if (isset($example[2])) {
			$I->seeRegExp('~.*' . $example[2] . '.*~i');
		}
	}
}
