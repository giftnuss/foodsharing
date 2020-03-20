<?php

use Foodsharing\Modules\PushNotification\Notification\PushNotification;
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

	public function testAddHandler()
	{
		$testHandler = $this->makeTestPushNotificationHandler('test');
		$this->gateway->addHandler($testHandler);

		$this->tester->assertTrue($this->gateway->hasHandlerFor('test'));
	}

	public function testAddSubscription()
	{
		$testHandler = $this->makeTestPushNotificationHandler('test type');
		$this->gateway->addHandler($testHandler);

		$this->gateway->addSubscription($this->testUser['id'], $this->testSubscription, 'test type');

		$this->tester->seeInDatabase(
			'fs_push_notification_subscription',
			['foodsaver_id' => $this->testUser['id'], 'data' => $this->testSubscription, 'type' => 'test type']
		);
	}

	public function testHasHandlerForReturnsFalseIfThereIsNoHandler()
	{
		$this->tester->assertFalse($this->gateway->hasHandlerFor('wrong type identifier'));
	}

	public function testGetPublicKey()
	{
		$testHandler = $this->makeTestPushNotificationHandler('test', 'testPublicKey');
		$this->gateway->addHandler($testHandler);

		$endpointInformation = $this->gateway->getServerInformation('test');

		$this->tester->assertEquals('testPublicKey', $endpointInformation['key']);
	}

	private function makeTestPushNotificationHandler(string $typeIdentifier, string $publicKey = ''): PushNotificationHandlerInterface
	{
		return new class($typeIdentifier, $publicKey) implements PushNotificationHandlerInterface {
			private static $typeIdentifier;
			private $publicKey;

			public function __construct(string $typeIdentifier, string $publicKey)
			{
				self::$typeIdentifier = $typeIdentifier;
				$this->publicKey = $publicKey;
			}

			public static function getTypeIdentifier(): string
			{
				return self::$typeIdentifier;
			}

			public function getServerInformation(): array
			{
				return $this->publicKey ? ['key' => $this->publicKey] : [];
			}

			public function sendPushNotificationsToClients(array $subscriptionData, PushNotification $notification): array
			{
				return [];
			}
		};
	}
}
