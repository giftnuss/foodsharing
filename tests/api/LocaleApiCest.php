<?php

namespace Foodsharing\api;

use ApiTester;
use Codeception\Example;
use Codeception\Util\HttpCode;

class LocaleApiCest
{
	private $user;

	public function _before(ApiTester $I)
	{
		$this->user = $I->createFoodsharer();
	}

	public function onlyHaveLocaleWhenLoggedIn(ApiTester $I)
	{
		$I->sendGET('api/locale');
		$I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);

		$I->sendPOST('api/locale', ['locale' => 'de']);
		$I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
	}

	public function haveDefaultLocale(ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendGET('api/locale');
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson([
			'locale' => 'de'
		]);
	}

	/**
	 * @example["de"]
	 * @example["en"]
	 * @example["fr"]
	 * @example["it"]
	 * @example["nb_NO"]
	 */
	public function canSetExistingLocale(ApiTester $I, Example $example)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/locale', ['locale' => $example[0]]);
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson([
			'locale' => $example[0]
		]);
	}

	public function canNotSetEmptyLocale(ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/locale', ['locale' => '']);
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseIsJson();
		$I->canSeeResponseContainsJson([
			'locale' => 'de'
		]);
	}
}
