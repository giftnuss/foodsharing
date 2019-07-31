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
		$I->sendPOST('api/pushnotification/webpush/subscription', $this->testSubscription);

		$I->seeResponseCodeIs(HttpCode::OK);
	}

	public function subscriptionFailsIfNotLoggedIn(\ApiTester $I)
	{
		$I->sendPOST('api/pushnotification/webpush/subscription', $this->testSubscription);

		$I->seeResponseCodeIs(HttpCode::FORBIDDEN);
	}
}
