<?php

use Codeception\Util\HttpCode;

class PushNotificationSubscriptionCest
{
	/**
	 * @var string
	 */
	private $testSubscription;

	public function _before(\ApiTester $I)
	{
		$this->tester = $I;
		$this->user = $I->createFoodsaver();

		$this->testSubscription = '
		{
			"endpoint": "https://some.pushservice.com/something-unique",
			"keys": {
				"p256dh": "BIPUL12DLfytvTajnryr2PRdAgXS3HGKiLqndGcJGabyhHheJYlNGCeXl1dn18gSJ1WAkAPIxr4gK0_dQds4yiI=",
				"auth":"FPssNDTKnInHVndSTdbKFw=="
 			}
		}';
	}

	public function subscriptionSucceedsIfLoggedIn(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/pushnotification/webpush/subscription', ['body' => $this->testSubscription]);

		$I->seeResponseCodeIs(HttpCode::OK);
	}

	public function subscriptionFailsIfNotLoggedIn(\ApiTester $I)
	{
		$I->sendPOST('api/pushnotification/webpush/subscription', ['body' => $this->testSubscription]);

		$I->seeResponseCodeIs(HttpCode::FORBIDDEN);
	}

	public function deletionSucceedsIfLoggedInAndSubscriptionExists(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/pushnotification/webpush/subscription', ['body' => $this->testSubscription]);

		$I->sendDELETE('api/pushnotification/webpush/subscription', ['body' => $this->testSubscription]);

		$I->seeResponseCodeIs(HttpCode::OK);
	}

	public function deletionReturns404IfSubscriptionDoesntExist(\ApiTester $I)
	{
		$I->login($this->user['email']);

		$I->sendDELETE('api/pushnotification/webpush/subscription', ['body' => $this->testSubscription]);

		$I->seeResponseCodeIs(HttpCode::NOT_FOUND);
	}

	public function deletionReturns403IfNotLoggedIn(\ApiTester $I)
	{
		$I->sendDELETE('api/pushnotification/webpush/subscription', ['body' => $this->testSubscription]);

		$I->seeResponseCodeIs(HttpCode::FORBIDDEN);
	}
}
