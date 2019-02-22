<?php

namespace Foodsharing\Modules\PushNotification\PushNotificationHandlers;

use Foodsharing\Modules\PushNotification\PushNotificationHandlerInterface;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;

class WebPushHandler implements PushNotificationHandlerInterface
{
	private const keyFileDirectory = __DIR__ . '/../../../../data/keys/webpush';
	private const privateKeyFileName = 'priv.key';
	private const publicKeyFileName = 'pub.key';

	private const typeIdentifier = 'webpush';

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

	public function __construct()
	{
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
	 * Returns a string that identifies subscriptions that will be handled by this handler. It will be used in the
	 * database but also in the URL of the REST api.
	 */
	public static function getTypeIdentifier(): string
	{
		return self::typeIdentifier;
	}

	/**
	 * Gets an array with subscription strings in the format they were saved in the database and sends the
	 * $messasge to all of these clients.
	 *
	 * @var array - an array with subscription strings in JSON format
	 */
	public function sendPushNotificationsToClients(array $subscriptionData, string $title, string $message, ?string $action = null): void
	{
		foreach ($subscriptionData as $subscriptionAsJson) {
			$subscriptionArray = json_decode($subscriptionAsJson, true);

			// Fix inconsistent definition of encoding by some clients
			$subscriptionArray['contentEncoding'] = $subscriptionArray['contentEncoding'] ?? 'aesgcm';

			$subscription = Subscription::create($subscriptionArray);

			$this->webpush->sendNotification($subscription, $message);
		}

		$reports = $this->webpush->flush();

		/**
		 * Check sent results
		 * @var MessageSentReport $report
		 */
		foreach ($reports as $report) {
			$endpoint = $report->getRequest()->getUri()->__toString();

			if (!$report->isSuccess()) {
				error_log("Message failed to sent for subscription {$endpoint}: {$report->getReason()}");
			}
		}
	}

	/**
	 * Returns the public key fitting to the private key the PushNotificationHandler signs its notifications with.
	 */
	public function getPublicKey(): string
	{
		if ($this->publicKey !== null) {
			return $this->publicKey;
		}

		if (is_file(self::keyFileDirectory . '/' . self::publicKeyFileName)) {
			return $this->publicKey = file_get_contents(self::keyFileDirectory . '/' . self::publicKeyFileName);
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
			return $this->privateKey = file_get_contents(self::keyFileDirectory . '/' . self::privateKeyFileName);
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
