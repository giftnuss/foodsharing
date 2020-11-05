<?php

namespace Foodsharing\Modules\PushNotification\PushNotificationHandlers;

use Base64Url\Base64Url;
use Exception;
use Foodsharing\Modules\PushNotification\Notification\MessagePushNotification;
use Foodsharing\Modules\PushNotification\Notification\PushNotification;
use Foodsharing\Modules\PushNotification\PushNotificationHandlerInterface;
use Minishlink\WebPush\Encryption;
use Minishlink\WebPush\Utils;
use Symfony\Contracts\Translation\TranslatorInterface;

class AndroidPushHandler implements PushNotificationHandlerInterface
{
	private const typeIdentifier = 'android';

	private string $fcmKey;

	private TranslatorInterface $translator;

	public function __construct(TranslatorInterface $translator)
	{
		$this->translator = $translator;
		$this->fcmKey = FCM_KEY;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getTypeIdentifier(): string
	{
		return self::typeIdentifier;
	}

	/**
	 * @param string[] $subscriptionData an array with subscription strings in JSON format
	 */
	public function sendPushNotificationsToClients(array $subscriptionData, PushNotification $notification): array
	{
		$deadSubscriptions = [];

		if (empty($this->fcmKey)) {
			// FCM Key is not set - do nothing
			return $deadSubscriptions;
		}

		$payloadJson = $this->makePayload($notification);

		foreach ($subscriptionData as $subscriptionAsJson) {
			$subscriptionArray = json_decode($subscriptionAsJson, true);

			try {
				$userPublicKey = $subscriptionArray['public_key']['public_key'];
				$userAuthToken = $subscriptionArray['public_key']['auth_secret'];
				$userFcmToken = $subscriptionArray['fcm_token'];
				$contentEncoding = 'aes128gcm';

				$keychainUniqueId = $subscriptionArray['public_key']['keychain_unique_id'] ?? '';
				$serialNumber = $subscriptionArray['public_key']['serial_number'] ?? 0;
			} catch (Exception $e) {
				// Failed to read required elements from the subscription data
				$deadSubscriptions[] = $subscriptionAsJson;
				continue;
			}

			// Capillary does not support padding
			$paddedPayload = $payloadJson . chr(2);
			$encrypted = Encryption::encrypt($paddedPayload, $userPublicKey, $userAuthToken, $contentEncoding);

			$cipherText = $encrypted['cipherText'];
			$salt = $encrypted['salt'];
			$localPublicKey = $encrypted['localPublicKey'];

			$encryptionContentCodingHeader = Encryption::getContentCodingHeader($salt, $localPublicKey, $contentEncoding);
			$payload = $encryptionContentCodingHeader . $cipherText;

			$url = 'https://fcm.googleapis.com/fcm/send';
			$data = [
				'to' => $userFcmToken,
				'data' => [
					'b' => Base64Url::encode($payload),
					'k' => $keychainUniqueId,
					's' => $serialNumber
				]
			];

			$requestJson = json_encode($data);
			$options = [
				'http' => [
					'header' => "Content-Type: application/json\r\nAuthorization: key=" . $this->fcmKey . "\r\n" . sprintf('Content-Length: %d', strlen($requestJson)) . "\r\n",
					'method' => 'POST',
					'content' => $requestJson
				]
			];
			$context = stream_context_create($options);
			$result = file_get_contents($url, false, $context);

			$resultJson = json_decode($result, true);
			if ($resultJson['failure'] === 1 && $resultJson['results'][0]['error'] == 'NotRegistered') {
				$deadSubscriptions[] = $subscriptionAsJson;
			}
		}

		return $deadSubscriptions;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getServerInformation(): array
	{
		return [];
	}

	/**
	 * @return string - json formatted payload
	 */
	private function makePayload(PushNotification $notification): string
	{
		$payloadArray = [];

		if ($notification instanceof MessagePushNotification) {
			$payloadArray['t'] = 'c';
			$payloadArray['c'] = $notification->getConversationId();
			$payloadArray['m'] = $notification->getMessage();
			$payloadArray['a'] = $notification->getAuthor();
		} else {
			// Seems to be a PushNotification type we don't know, but luckily we can fall back on a simple text notification with just title and body
			$payloadArray['t'] = 'd';
			$payloadArray['c'] = $notification->getTitle($this->translator);
			$payloadArray['b'] = $notification->getBody($this->translator);
		}

		$payloadArray = $this->cropPayload($payloadArray, $notification);

		return json_encode($payloadArray);
	}

	/**
	 * Crops the payload body, so the payload doesn't exceed the safe string length for WebPush payloads.
	 *
	 * @param array $payload a payload array containing at least a 'body' key (because this is what will be cropped)
	 *
	 * @return array Payload that definitely has a sendable length
	 */
	private function cropPayload(array $payload, PushNotification $notification): array
	{
		$overlappingChars = Utils::safeStrlen(json_encode($payload)) - Encryption::MAX_COMPATIBILITY_PAYLOAD_LENGTH;

		if ($overlappingChars <= 0) {
			return $payload;
		}

		if ($notification instanceof MessagePushNotification) {
			$body = $payload['m']->body;
			$body = substr($body, 0, strlen($body) - $overlappingChars - 3);
			$body .= '...';
			$payload['m']->body = $body;
		} else {
			$payload['b'] = substr($payload['b'], 0, strlen($payload['b']) - $overlappingChars - 3);
			$payload['b'] .= '...';
		}

		return $payload;
	}
}
