<?php

class LoginApiCest
{
	public function checkLogin(\FunctionalTester $I)
	{
		$pass = 'pw';
		$user = $I->createFoodsaver($pass);
		$I->sendPOST('api/user/login', [
			'email' => $user['email'],
			'password' => $pass
			]);
		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson([
			'id' => $user['id'],
			'name' => $user['name']
		]);
	}

	public function loginFailsWrongUserPassword(\FunctionalTester $I)
	{
		$user['email'] = 'thissurelydoesnotexist@example.com';
		$pass = '123';
		$I->sendPOST('api/user/login', [
			'email' => $user['email'],
			'password' => $pass
		]);
		$I->seeResponseCodeIs(401);
	}

	public function loginFailsWrongPasswordForExistingUser(\FunctionalTester $I)
	{
		$pass = 'pw';
		$user = $I->createFoodsaver($pass);
		$I->sendPOST('api/user/login', [
			'email' => $user['email'],
			'password' => 'asdf'
		]);
		$I->seeResponseCodeIs(401);
	}
}
