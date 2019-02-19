<?php

namespace Foodsharing\Modules\PushNotification;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationGateway extends BaseGateway
{
	private const keyFileDirectory = __DIR__ . '/../../../data/keys/pushnotifications';
	private const privateKeyFileName = 'priv.key';
	private const publicKeyFileName = 'pub.key';

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
				'subject' => $_SERVER['SERVER_NAME'] ?? '',
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
		$subscriptionsByThisFoodsaver = $this->db->fetchAllValuesByCriteria(
			'fs_pushnotificationsubscription',
			'subscription',
			['foodsaver_id' => $foodsaverId]
		);

		$endpointToBeUpdated = json_decode($subscription, true)['endpoint'];
		$subscriptionsToBeUpdated = [];

		foreach ($subscriptionsByThisFoodsaver as $subscriptionByThisFoodsaver) {
			$endpoint = json_decode($subscriptionByThisFoodsaver, true)['endpoint'];
			if($endpoint === $endpointToBeUpdated) {
				$subscriptionsToBeUpdated[] = $subscriptionByThisFoodsaver;
			}
		}

		if (empty($subscriptionsToBeUpdated)) {
			return 0;
		}

		return $this->db->update(
			'fs_pushnotificationsubscription',
			['subscription' => $subscription],
			['subscription' => $subscriptionsToBeUpdated, 'foodsaver_id' => $foodsaverId]
		);
	}

	public function deleteSubscription(int $foodsaverId, string $subscription): int
	{
		$subscriptionsByThisFoodsaver = $this->db->fetchAllValuesByCriteria(
			'fs_pushnotificationsubscription',
			'subscription',
			['foodsaver_id' => $foodsaverId]
		);

		$subscritionsToBeDeleted = [];

		foreach ($subscriptionsByThisFoodsaver as $subscriptionByThisFoodsaver) {
			if($subscriptionByThisFoodsaver === $subscription) {
				$subscritionsToBeDeleted[] = $subscriptionByThisFoodsaver;
			}
		}

		return $this->db->delete(
			'fs_pushnotificationsubscription',
			['foodsaver_id' => $foodsaverId, 'subscription' => $subscription]
		);
	}

//  As soon as we have MySQL >= 5.7, we can replace the last 2 functions with the following:
//	/**
//	 * @param string $subscription – @see \Foodsharing\Modules\PushNotification\PushNotificationGateway::addSubscription()
//	 */
//	public function updateSubscription(int $foodsaverId, string $subscription): int
//	{
//		$stm = '
//			UPDATE fs_pushnotificationsubscription
//			SET subscription = :subscription
//			WHERE foodsaver_id = :foodsaverId
//			AND JSON_EXTRACT(subscription, $.endpoint) = JSON_EXTRACT(:subscription, $.endpoint)
//		';
//
//		return $this->db->execute($stm, [':foodsaverId' => $foodsaverId, ':subscription' => $subscription]);
//	}
//
//	/**
//	 * @param string $endpoint – the endpoint the notifications are sent to; part of the subscription JSON
//	 */
//	public function deleteSubscription(int $foodsaverId, string $endpoint): int
//	{
//		$stm = '
//			DELETE FROM fs_pushnotificationsubscription
//			WHERE foodsaver_id = :foodsaverId
//			AND JSON_EXTRACT(subscription, $.endpoint) = :endpoint
//		';
//
//		return $this->db->execute($stm, [':foodsaverId' => $foodsaverId, ':endpoint' => $endpoint]);
//	}

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

		if (is_file(self::keyFileDirectory . '/' . self::publicKeyFileName)) {
			return $this->publicKey = file_get_contents(self::publicKeyFileName);
		}

		$this->generateKeys();

		return $this->publicKey;
	}

	private function getPrivateKey(): string
	{
		if ($this->privateKey !== null) {
			return $this->privateKey;
		}

		if (is_file(self::keyFileDirectory . '/' . self::privateKeyFileName)) {
			return $this->privateKey = file_get_contents(self::privateKeyFileName);
		}

		$this->generateKeys();

		return $this->privateKey;
	}

	private function generateKeys(): void
	{
		$keys = VAPID::createVapidKeys();
		$this->publicKey = $keys['publicKey'];
		$this->privateKey = $keys['privateKey'];

		file_put_contents(self::keyFileDirectory . '/' . self::privateKeyFileName, $this->privateKey);
		file_put_contents(self::keyFileDirectory . '/' . self::publicKeyFileName, $this->publicKey);

		if (FS_ENV !== 'test' && FS_ENV !== 'dev') { // Tests don't work with restrictive file rights
			chmod(self::keyFileDirectory . '/' . self::privateKeyFileName, 0600);
		}
	}
}
