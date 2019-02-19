<?php

use Foodsharing\Modules\PushNotification\PushNotificationHandlerInterface;

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

		$this->tester->seeInDatabase('fs_push_notification_subscription', ['foodsaver_id' => $this->testUser['id'], 'data' => $this->testSubscription]);
	}

	public function testDeleteSubscription()
	{
		//insert test subsription:
		$this->gateway->addSubscription($this->testUser['id'], $this->testSubscription);

		$this->gateway->deleteSubscription($this->testUser['id'], $this->testSubscription);

		$this->tester->dontSeeInDatabase('fs_push_notification_subscription', ['foodsaver_id' => $this->testUser['id']]);
	}

	public function testAddHandler()
	{
		$testHandler = new class() implements PushNotificationHandlerInterface {
			public static function getTypeIdentifier(): string
			{
				return 'test';
			}

			public function getPublicKey(): string
			{
				return '';
			}

			public function sendPushNotificationsToClients(array $subscriptionData, string $title, string $message, string $action = null): void
			{
				return;
			}
		};

		$this->gateway->addHandler($testHandler);

		$this->tester->assertTrue($this->gateway->hasHandlerFor('test'));
	}

	public function testHasHandlerForReturnsFalseIfThereIsNoHandler()
	{
		$this->tester->assertFalse($this->gateway->hasHandlerFor('wrong type identifier'));
	}

	public function testGetPublicKey()
	{
		$testHandler = new class() implements PushNotificationHandlerInterface {
			public static function getTypeIdentifier(): string
			{
				return 'test';
			}

			public function getPublicKey(): string
			{
				return 'testPublicKey';
			}

			public function sendPushNotificationsToClients(array $subscriptionData, string $title, string $message, ?string $action = null): void
			{
				return;
			}
		};
		$this->gateway->addHandler($testHandler);

		$publicKey = $this->gateway->getPublicKey('test');

		$this->tester->assertEquals('testPublicKey', $publicKey);
	}
}
