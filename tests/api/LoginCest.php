<?php

class LoginCest
{
	/**
	 * @example ["createFoodsaver", "Wähle den Bezirk aus"]
	 * @example ["createFoodsharer", "Viel Spaß beim Retten"]
	 * @example ["createStoreCoordinator", "Dein Stammbezirk ist"]
	 * @example ["createAmbassador", "Dein Stammbezirk ist"]
	 * @example ["createOrga", "Dein Stammbezirk ist"]
	 */
	public function checkLogin(\ApiTester $I, \Codeception\Example $example)
	{
		$pass = sq('pass');
		$user = $I->{$example[0]}($pass);

		$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

		$I->sendPOST('/?page=login', [
			'login_form' => [
				'email_address' => $user['email'],
				'password' => $pass
			]
		]);

		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeHtml();
		$I->seeRegExp('~.*' . $example[1] . '.*~i');

		$I->seeInDatabase('fs_foodsaver', [
			'email' => $user['email']
		]);
	}

	/**
	 * @example ["createFoodsaver", "Hallo ", "Foodsaver für"]
	 */
	public function checkInvalidLogin(\ApiTester $I, \Codeception\Example $example)
	{
		$pass = sq('pass');
		$user = call_user_func([$I, $example[0]], $pass);

		$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

		$I->sendPOST('/?page=login', [
			'login_form' => [
				'email_address' => $user['email'],
				'password' => 'WROOOOOOOOONG'
			]
		]);

		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeHtml();
		$I->seeResponseContains('E-Mail-Adresse oder Passwort sind falsch');
	}
}
