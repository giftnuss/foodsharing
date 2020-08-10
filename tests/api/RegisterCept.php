<?php

$I = new ApiTester($scenario);

$I->wantTo('Register a new user');

$email = sq('email') . '@test.com';
$first_name = sq('first_name');
$last_name = sq('last_name');
$pass = sq('pass');
$birthdate = '1990-05-31';

$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$I->sendPOST('/api/user', [
	'firstname' => $first_name,
	'lastname' => $last_name,
	'email' => $email,
	'mobilePhone' => '39833',
	'password' => $pass,
	'gender' => 0,
	'birthdate' => $birthdate,
	'subscribeNewsletter' => 1,
//	'lat' => 51.36662,
//	'lon' => 12.74167,
//	'str' => 'Kantstraße',
//	'nr' => '5a',
//	'plz' => '12345'
]);

$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['name' => $first_name]);

$I->seeInDatabase('fs_foodsaver', [
	'email' => $email,
	'name' => $first_name,
	'nachname' => $last_name,
	'newsletter' => 1,
	'geb_datum' => $birthdate,
//	'anschrift' => 'Kantstraße 5a',
//	'plz' => '12345'
]);

// verify password
$hash = $I->grabFromDatabase('fs_foodsaver', 'password', ['email' => $email]);
$I->assertTrue(password_verify($pass, $hash));
