<?php

class CreateCompanyCest
{
	/**
	 * @param ApiTester $I
	 * @param \Codeception\Example $example
	 *
	 * @example ["createFoodsharer", false]
	 * @example ["createFoodsaver", false]
	 * @example ["createStoreCoordinator", true]
	 * @example ["createAmbassador", true]
	 */
	public function seeCreateCompanyLink(\ApiTester $I, \Codeception\Example $example)
	{
		$pass = sq('pass');
		$user = call_user_func(array($I, $example[0]), $pass);

		$I->login($user['email'], $pass);

		$str = '~.*Neuen Betrieb eintragen.*~';
		if ($example[1]) {
			$I->seeRegExp($str);
		} else {
			$I->dontSeeRegExp($str);
		}
	}
}
