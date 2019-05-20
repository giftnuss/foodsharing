<?php

namespace Foodsharing\Modules\PushNotification\PushNotificationHandlers;

use Foodsharing\Modules\PushNotification\PushNotificationHandlerInterface;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;

class WebPushHandler implements PushNotificationHandlerInterface
{
	private const typeIdentifier = 'webpush';

	public function __construct()
	{
		$auth = [
			'VAPID' => [
				'subject' => $_SERVER['SERVER_NAME'] ?? '',
				'publicKey' => WEBPUSH_PUBLIC_KEY,
				'privateKey' => WEBPUSH_PRIVATE_KEY
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
	public function sendPushNotificationsToClients(array $subscriptionData, $title, array $options, array $action = null): array
	{
		$options['data']['action'] = $action;
		$payloadJson = json_encode(['title' => $title, 'options' => $options]);

		foreach ($subscriptionData as $subscriptionAsJson) {
			$subscriptionArray = json_decode($subscriptionAsJson, true);

			// Fix inconsistent definition of encoding by some clients
			$subscriptionArray['contentEncoding'] = $subscriptionArray['contentEncoding'] ?? 'aesgcm';

			$subscription = Subscription::create($subscriptionArray);

			/**
			 * @var MessageSentReport$report
			 */
			$reportGenerator = $this->webpush->sendNotification($subscription, $payloadJson, true);

			foreach ($reportGenerator as $report) {
				$endpoint = $report->getEndpoint();

				if ($report->isSubscriptionExpired()) {
					$deadSubscriptions[] = $subscriptionAsJson;
				}

				// logging
				if (!$report->isSuccess()) {
					error_log("Message failed to sent for subscription {$endpoint}: {$report->getReason()}");
				}
			}
		}

		return $deadSubscriptions;
	}

	public function getEndpointInformation(): array
	{
		return ['key' => WEBPUSH_PUBLIC_KEY];
	}
}
