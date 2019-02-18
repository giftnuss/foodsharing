<?php

namespace Foodsharing\Modules\PushNotification;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationGateway extends BaseGateway
{
	private const pathOfPrivateKeyFile = ROOT_DIR . 'keys/pushnotifications/priv.key';
	private const pathOfPublicKeyFile = ROOT_DIR . 'keys/pushnotifications/pub.key';

	/**
	 * @var string
	 */
	private $privateKey;

	/**
	 * @var string
	 */
	private $publicKey;

	/**
	 * @var WebPush
	 */
	private $webpush;

	public function __construct(Database $db)
	{
		parent::__construct($db);

		$auth = [
			'VAPID' => [
				'subject' => $_SERVER['SERVER_NAME'],
				'publicKey' => $this->getPublicKey(),
				'privateKey' => $this->getPrivateKey()
			],
		];

		$this->webpush = new WebPush($auth);
	}

	/**
	 * @param string $subscription : The push notification subscription in JSON format, e. g.
	 * {
	 *  "endpoint": "https://some.pushservice.com/something-unique",
	 *  "keys": {
	 *   "p256dh":
	 *   "BIPUL12DLfytvTajnryr2PRdAgXS3HGKiLqndGcJGabyhHheJYlNGCeXl1dn18gSJ1WAkAPIxr4gK0_dQds4yiI=",
	 *   "auth":"FPssNDTKnInHVndSTdbKFw=="
	 *   }
	 * }
	 */
	public function addSubscription(int $foodsaverId, string $subscription): int
	{
		return $this->db->insert('fs_pushnotificationsubscription', [
			'foodsaver_id' => $foodsaverId,
			'subscription' => $subscription
		]);
	}

	/**
	 * @param string $subscription – @see \Foodsharing\Modules\PushNotification\PushNotificationGateway::addSubscription()
	 */
	public function updateSubscription(int $foodsaverId, string $subscription): int
	{
		$stm = '
			UPDATE fs_pushnotificationsubscription 
			SET subscription = :subscription 
			WHERE foodsaver_id = :foodsaverId
			AND JSON_EXTRACT(subscription, $.endpoint) = JSON_EXTRACT(:subscription, $.endpoint)
		';

		return $this->db->execute($stm, [':foodsaverId' => $foodsaverId, ':subscription' => $subscription]);
	}

	/**
	 * @param string $endpoint – the endpoint the notifications are sent to; part of the subscription JSON
	 */
	public function deleteSubscription(int $foodsaverId, string $endpoint): int
	{
		$stm = '
			DELETE FROM fs_pushnotificationsubscription
			WHERE foodsaver_id = :foodsaverId
			AND JSON_EXTRACT(subscription, $.endpoint) = :endpoint
		';

		return $this->db->execute($stm, [':foodsaverId' => $foodsaverId, ':endpoint' => $endpoint]);
	}

	public function sendPushNotificationsToFoodsaver(int $foodsaverId, string $message): void
	{
		$subscriptionsAsJson = $this->db->fetchAllByCriteria(
			'fs_pushnotificationsubscription',
			['subscription'],
			['foodsaver_id' => $foodsaverId]
		);

		foreach ($subscriptionsAsJson as $subscriptionAsJson) {
			$subscriptionArray = json_decode($subscriptionAsJson['subscription'], true);

			// Fix inconsistent definition of encoding by some clients
			$subscriptionArray['contentEncoding'] = $subscriptionArray['contentEncoding'] ?? 'aesgcm';

			$subscription = Subscription::create($subscriptionArray);

			$this->webpush->sendNotification($subscription, $message);
		}
		$this->webpush->flush();
	}

	public function getPublicKey(): string
	{
		if ($this->publicKey !== null) {
			return $this->publicKey;
		}

		if (is_file(self::pathOfPublicKeyFile)) {
			return $this->publicKey = file_get_contents(self::pathOfPublicKeyFile);
		}

		$this->generateKeys();

		return $this->publicKey;
	}

	private function getPrivateKey(): string
	{
		if ($this->privateKey !== null) {
			return $this->privateKey;
		}

		if (is_file(self::pathOfPrivateKeyFile)) {
			return $this->privateKey = file_get_contents(self::pathOfPrivateKeyFile);
		}

		$this->generateKeys();

		return $this->privateKey;
	}

	private function generateKeys(): void
	{
		$keys = VAPID::createVapidKeys();
		$this->publicKey = $keys['publicKey'];
		$this->privateKey = $keys['privateKey'];

		file_put_contents(self::pathOfPrivateKeyFile, $this->privateKey);
		file_put_contents(self::pathOfPublicKeyFile, $this->publicKey);
		chmod(self::pathOfPrivateKeyFile, 0600);
	}
}
