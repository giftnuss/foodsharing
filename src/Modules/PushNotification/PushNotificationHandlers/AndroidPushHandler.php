<?php

namespace Foodsharing\Modules\PushNotification\PushNotificationHandlers;

use Foodsharing\Modules\PushNotification\PushNotificationHandlerInterface;
use Minishlink\WebPush\Encryption;
use Psr\Log\LoggerInterface;
use Base64Url\Base64Url;

class AndroidPushHandler implements PushNotificationHandlerInterface
{
	private const typeIdentifier = 'android';

	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		// TODO remove logger before merging
		$this->logger = $logger;
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
		$this->logger->error("AndroidPush logger");
		$this->logger->error("AndroidPush logger2");
        // Initialize Guzzle client
		try {
			$this->logger->error("client created");

			$options['data']['action'] = $action;
			$payloadJson = json_encode(['title' => $title, 'options' => $options, 'p' => '']);

			// Capillary does not support aes128gcm padding, thus we manually add padding to the payload			
			$paddingLen = 2600 - strlen($payloadJson);
			if ($paddingLen > 0) {
				$payloadJson = json_encode(['title' => $title, 'options' => $options, 'p' => str_repeat("0", $paddingLen)]);
			}

			$this->logger->error("Number of subs " . sizeof($subscriptionData));
			foreach ($subscriptionData as $subscriptionAsJson) {

				$this->logger->error("start sub handling");
				$subscriptionArray = json_decode($subscriptionAsJson, true);

				$userPublicKey = $subscriptionArray['public_key'];
				$userAuthToken = $subscriptionArray['auth_secret'];
				$userFcmToken = $subscriptionArray['fcm_token'];
				$contentEncoding = 'aes128gcm';

				$keychainUniqueId = $subscriptionArray['keychain_unique_id'] ?? '';
				$serialNumber = $subscriptionArray['serial_number'] ?? 0;

				// Capillary does not support padding
                $paddedPayload = $payloadJson . chr(2);

				$this->logger->error("before encrypt");
				$this->logger->error("payload json " . $payloadJson);
				$this->logger->error("userPublicKey " . $userPublicKey);
				$this->logger->error("userAuthToken " . $userAuthToken);
				$this->logger->error("contentEncoding " . $contentEncoding);
	            $encrypted = Encryption::encrypt($paddedPayload, $userPublicKey, $userAuthToken, $contentEncoding);
				$this->logger->error("after encrypt");

	            $cipherText = $encrypted['cipherText'];
	            $salt = $encrypted['salt'];
	            $localPublicKey = $encrypted['localPublicKey'];

				$this->logger->error("salt length " . strlen($salt));

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

				// use key 'http' even if you send the request to https://...
				$requestJson = json_encode($data);
				$this->logger->error("requestJson is: " . $requestJson);
				$this->logger->error("content length is: " . strlen($requestJson));
				// TODO move this somewhere else
				$fcm_key = "";
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
					$this->logger->error("An error in android push");
				}

				$this->logger->error($result);
			}
		} catch (Exception $e) {
			$this->logger->error("Error in android push: " . $e->getMessage());
		}

		$this->logger->error("END AndroidPush");
		return [];
	}

	public function getPublicKey(): string
	{
		// TODO: this is not necessary for this handler. Maybe this should be removed from the abstraction.
		return "";
	}

}
