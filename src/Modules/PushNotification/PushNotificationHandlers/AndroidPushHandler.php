<?php

namespace Foodsharing\Modules\PushNotification\PushNotificationHandlers;

use Foodsharing\Modules\PushNotification\Notification\MessagePushNotification;
use Foodsharing\Modules\PushNotification\Notification\PushNotification;
use Foodsharing\Modules\PushNotification\PushNotificationHandlerInterface;
use Minishlink\WebPush\Encryption;
use Psr\Log\LoggerInterface;
use Base64Url\Base64Url;
use Minishlink\WebPush\Utils;
use Symfony\Contracts\Translation\TranslatorInterface;

class AndroidPushHandler implements PushNotificationHandlerInterface
{
	private const typeIdentifier = 'android';

	/**
	 * @var fcmKey
	 */
	private $fcmKey;

	/**
	 * @var TranslatorInterface
	 */
	private $translator;

	public function __construct(TranslatorInterface $translator)
	{
		$this->translator = $translator;

		//$this->fcmKey = FCM_SERVER_KEY;
		$this->fcmKey = "AAAAIoVAv9Y:APA91bFybj8_BXrQOI5GXzKjFAGlvxJMf_CcTD70k8i8kc4ngLsbvXW6R6sDBoCpwYMgiWWX6rpyYob6QbW2w_tmliIlrgOEijPRBVPGIxuY0yWvbFcchZLINQGFwGLi8gGykwdXvu8N";
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
        // Initialize Guzzle client
		try {
			$payloadJson = $this->makePayload($notification);

			foreach ($subscriptionData as $subscriptionAsJson) {
				$subscriptionArray = json_decode($subscriptionAsJson, true);

				$userPublicKey = $subscriptionArray['public_key'];
				$userAuthToken = $subscriptionArray['auth_secret'];
				$userFcmToken = $subscriptionArray['fcm_token'];
				$contentEncoding = 'aes128gcm';

				$keychainUniqueId = $subscriptionArray['keychain_unique_id'] ?? '';
				$serialNumber = $subscriptionArray['serial_number'] ?? 0;

				// Capillary does not support padding
                $paddedPayload = $payloadJson . chr(2);
	            $encrypted = Encryption::encrypt($paddedPayload, $userPublicKey, $userAuthToken, $contentEncoding);

	            $cipherText = $encrypted['cipherText'];
	            $salt = $encrypted['salt'];
	            $localPublicKey = $encrypted['localPublicKey'];

                $encryptionContentCodingHeader = Encryption::getContentCodingHeader($salt, $localPublicKey, $contentEncoding);
                $payload = $encryptionContentCodingHeader.$cipherText;
                // Use compatibility padding to consider encoding

	            $url = 'https://fcm.googleapis.com/fcm/send';
				$data = ['to' => $userFcmToken,
				    		'data' => [
				    			'b' => Base64Url::encode($payload),
				    			'k' => $keychainUniqueId,
				    			's' => $serialNumber
				    		]];

				$requestJson = json_encode($data);
				// TODO move this somewhere else
				$fcm_key = $this->fcmKey;
				$options = array(
				    'http' => array(
				        'header'  => "Content-Type: application/json\r\nAuthorization: key=" . $fcm_key . "\r\n" . sprintf('Content-Length: %d', strlen($requestJson)) . "\r\n",
				        'method'  => 'POST',
				        'content' => $requestJson
				    )
				);
				$context  = stream_context_create($options);
				$result = file_get_contents($url, false, $context);
				if ($result === FALSE) {
					//$this->logger->error("An error in android push");
				}

				//$this->logger->error($result);
			}
		} catch (Exception $e) {
			//$this->logger->error("Error in android push: " . $e->getMessage());
		}

		//$this->logger->error("END AndroidPush");
		return [];
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
