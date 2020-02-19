<?php

class NewsletterApiCest
{
	private $tester;
	private $user;
	private $userOrga;

	public function _before(\ApiTester $I)
	{
		$this->tester = $I;
		$this->user = $I->createFoodsharer();
		$this->userOrga = $I->createOrga();
	}

	/**
	 * @param ApiTester $I
	 */
	public function foodsaverMayNotTestNewsletter(\ApiTester $I): void
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/newsletter/test', [
			'address' => 'test@abcdef.com',
			'subject' => 'Subject',
			'message' => 'Message'
		]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
	}

	/**
	 * @param ApiTester $I
	 */
	public function invalidEmailAddressIsRejected(\ApiTester $I): void
	{
		$I->login($this->userOrga['email']);
		$I->sendPOST('api/newsletter/test', [
			'address' => 'test',
			'subject' => 'Subject',
			'message' => 'Message'
		]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::INTERNAL_SERVER_ERROR);
	}

	/**
	 * @param ApiTester $I
	 */
	public function validEmailAddressIsAccepted(\ApiTester $I): void
	{
		$I->login($this->userOrga['email']);
		$I->sendPOST('api/newsletter/test', [
			'address' => 'test@abcdef.com',
			'subject' => 'Subject',
			'message' => 'Message'
		]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
	}
}
