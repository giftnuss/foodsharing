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

		// initially old md5 password is stored
		$I->assertNotEmpty($user['passwd']);

		// password got replaced after login
		$I->seeInDatabase('fs_foodsaver', [
			'email' => $user['email'],
			'passwd' => null, // md5
			'fs_password' => null // sha1
		]);

		// new hash is valid
		$newHash = $I->grabFromDatabase('fs_foodsaver', 'password', ['email' => $user['email']]);
		$I->assertTrue(password_verify($pass, $newHash));
	}

	/**
	 * @example ["createFoodsaver", "Hallo ", "Foodsaver für"]
	 */
	public function checkInvalidLogin(\ApiTester $I, \Codeception\Example $example)
	{
		$pass = sq('pass');
		$user = call_user_func(array($I, $example[0]), $pass);

		$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

		$I->sendPOST('/?page=login', [
			'email_adress' => $user['email'],
			'password' => 'WROOOOOOOOONG'
		]);

		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeHtml();
		$I->seeResponseContains('Falsche Zugangsdaten');

		// password is not updated
		$I->seeInDatabase('fs_foodsaver', [
			'email' => $user['email'],
			'passwd' => $user['passwd'], // md5
			'password' => null, // bcrypt
			'fs_password' => null // sha1
		]);
	}
}
