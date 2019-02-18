<?php

class PushNotificationGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Modules\PushNotification\PushNotificationGateway
	 */
	private $gateway;

	/**
	 * @var array
	 */
	private $testUser;

	/**
	 * @var string
	 */
	private $testSubscription;

	public function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\PushNotification\PushNotificationGateway::class);
		$this->testUser = $this->tester->createFoodsaver();
		$this->testSubscription = '
		{
			"endpoint": "https://some.pushservice.com/something-unique",
			"keys": {
				"p256dh": "BIPUL12DLfytvTajnryr2PRdAgXS3HGKiLqndGcJGabyhHheJYlNGCeXl1dn18gSJ1WAkAPIxr4gK0_dQds4yiI=",
				"auth":"FPssNDTKnInHVndSTdbKFw=="
 			}
		}';
	}

	public function testAddSubscription()
	{
		$this->gateway->addSubscription($this->testUser['id'], $this->testSubscription);

		$this->tester->seeInDatabase('fs_pushnotificatonsubscription', ['foodsaver_id' => $this->testUser['id'], 'subscription' => $this->testSubscription]);
	}

	public function testUpdateSubscription()
	{
		//insert test subscription:
		$this->gateway->addSubscription($this->testUser['id'], $this->testSubscription);
		$newSubscription = '
		{
			"endpoint": "https://some.pushservice.com/something-unique",
			"keys": {
				"p256dh": "new-key",
				"auth": "new-auth"
 			}
		}';

		$this->gateway->updateSubscription($this->testUser['id'], $newSubscription);
		$this->tester->seeInDatabase('fs_pushnotificationsubscription', ['foodsaver_id' => $this->testUser['id'], 'subscription' => $newSubscription]);
	}

	public function testDeleteSubscription()
	{
		//insert test subsription:
		$this->gateway->addSubscription($this->testUser['id'], $this->testSubscription);
		$endpoint = json_encode($this->testSubscription, true)['endpoint'];

		$this->gateway->deleteSubscription($this->testUser['id'], $endpoint);

		$this->tester->dontSeeInDatabase('fs_pushnotificationsubscription', ['foodsaver_id' => $this->testUser['id']]);
	}
}
