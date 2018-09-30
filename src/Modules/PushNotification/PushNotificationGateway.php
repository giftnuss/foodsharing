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

    private $privateKey;
    private $publicKey;

    private $webpush;

    public function __construct(Database $db)
    {
        parent::__construct($db);

        $auth = [
            'VAPID' => [
                'subject' => 'www.foodsharing.de',
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
    public function addSubscription(int $foodsaverId, string $subscription)
    {
        $this->db->insert('fs_pushnotificationsubscription', [
            'foodsaver_id' => $foodsaverId,
            'subscription' => $subscription
        ]);
    }

    public function sendPushNotificationsToFoodsaver(int $foodsaverId, string $message)
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

    public function getPublicKey()
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

    private function getPrivateKey()
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

    private function generateKeys()
    {
        $keys = VAPID::createVapidKeys();
        $this->publicKey = $keys['publicKey'];
        $this->privateKey = $keys['privateKey'];

        file_put_contents(self::pathOfPrivateKeyFile, $this->privateKey);
        file_put_contents(self::pathOfPublicKeyFile, $this->publicKey);
    }
}