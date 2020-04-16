<?php

namespace Foodsharing\Modules\PushNotification\PushNotificationHandlers;

use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Modules\PushNotification\Notification\MessagePushNotification;
use Foodsharing\Modules\PushNotification\Notification\PushNotification;
use Foodsharing\Modules\PushNotification\PushNotificationHandlerInterface;
use Minishlink\WebPush\Encryption;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\Utils;
use Minishlink\WebPush\WebPush;

class WebPushHandler implements PushNotificationHandlerInterface
{
	private const typeIdentifier = 'webpush';

	/**
	 * @var WebPush
	 */
	private $webpush;

	/**
	 * @var TranslationHelper
	 */
	private $translationHelper;

	public function __construct(TranslationHelper $translationHelper)
	{
		$this->translationHelper = $translationHelper;

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
	 * @see PushNotificationHandlerInterface::getTypeIdentifier()
	 */
	public static function getTypeIdentifier(): string
	{
		return self::typeIdentifier;
	}

	/**
	 * @var string[] an array with subscription strings in JSON format
	 */
	public function sendPushNotificationsToClients(array $subscriptionData, PushNotification $notification): array
	{
		$payload = $this->makePayload($notification);
		$deadSubscriptions = [];

		foreach ($subscriptionData as $subscriptionAsJson) {
			$subscriptionArray = json_decode($subscriptionAsJson, true);

			// Fix inconsistent definition of encoding by some clients
			$subscriptionArray['contentEncoding'] = $subscriptionArray['contentEncoding'] ?? 'aesgcm';

			$subscription = Subscription::create($subscriptionArray);

			/**
			 * @var MessageSentReport
			 */
			$reportGenerator = $this->webpush->sendNotification($subscription, $payload, true);

			foreach ($reportGenerator as $report) {
				$endpoint = $report->getEndpoint();

				if ($report->isSubscriptionExpired()) {
					$deadSubscriptions[] = $subscriptionAsJson;
				}

				// logging
				if (!$report->isSuccess()) {
					error_log("Message failed to send for subscription {$endpoint}: {$report->getReason()}");
				}
			}
		}

		return $deadSubscriptions;
	}

	public function getServerInformation(): array
	{
		return ['key' => WEBPUSH_PUBLIC_KEY];
	}

	/**
	 * @return string - json formatted payload
	 */
	private function makePayload(PushNotification $notification): string
	{
		$payloadArray = [];

		if ($notification instanceof MessagePushNotification) {
			// set body
			$payloadArray['options']['body'] = $notification->getBody();
			// set time stamp
			$payloadArray['options']['timestamp'] = $notification->getTime()->getTimestamp() * 1000; // timestamp needs to be in milliseconds
			// set action
			$payloadArray['options']['data']['action'] = ['page' => 'conversations', 'params' => [$notification->getConversationId()]]; // this thing will be resolved to an url by urls.js on client side
			// Set title
			if ($notification->getConversationName() !== null) {
				$payloadArray['title'] = $this->translationHelper->sv(
					'message_notification_named_conversation',
					['foodsaver' => $notification->getSender(), 'conversation' => $notification->getConversationName()]
				);
			} else {
				$payloadArray['title'] = $this->translationHelper->sv(
					'message_notification_unnamed_conversation',
					$notification->getSender()
				);
			}
		} else {
			$payloadArray['title'] = $notification->getFallbackString($this->translationHelper);
		}

		$payloadArray = $this->cropPayload($payloadArray);

		return json_encode($payloadArray);
	}

	/**
	 * Crops the payload body, so the payload doesn't exceed the safe string length for WebPush payloads.
	 *
	 * @param array $payload a payload array containing at least a 'body' key (because this is what will be cropped)
	 *
	 * @return array Payload that definitely has a sendable length
	 */
	private function cropPayload(array $payload): array
	{
		$overlappingChars = Utils::safeStrlen(json_encode($payload)) - Encryption::MAX_PAYLOAD_LENGTH;

		if ($overlappingChars <= 0) {
			return $payload;
		}

		// only cut the body, I assume that the rest is not the critical factor
		$payload['options']['body'] = substr($payload['options']['body'], 0, strlen($payload['options']['body']) - $overlappingChars - 3);
		$payload['options']['body'] .= '...';

		return $payload;
	}
}
