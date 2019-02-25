<?php

namespace Foodsharing\Modules\PushNotification;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\PushNotification\PushNotificationHandlers\WebPushHandler;

class PushNotificationGateway extends BaseGateway
{
	/**
	 * @var PushNotificationHandlerInterface[]
	 */
	private $pushNotificationHandlers = [];

	public function __construct(Database $db, WebPushHandler $webPushHandler)
	{
		parent::__construct($db);
		$this->addHandler($webPushHandler);
	}

	public function addSubscription(int $foodsaverId, string $subscriptionData, string $type): int
	{
		return $this->db->insert('fs_push_notification_subscription', [
			'foodsaver_id' => $foodsaverId,
			'data' => $subscriptionData,
			'type' => $type
		]);
	}

	public function deleteSubscription(int $foodsaverId, string $subscriptionData): int
	{
		return $this->db->delete(
			'fs_push_notification_subscription',
			['foodsaver_id' => $foodsaverId, 'data' => $subscriptionData]
		);
	}

	public function addHandler(PushNotificationHandlerInterface $handler)
	{
		$this->pushNotificationHandlers[$handler::getTypeIdentifier()] = $handler;
	}

	/**
	 * @param string $title: the notification title
	 * @param array $options: an array of options to be sent to the endpoint - @see PushNotificationHandlerInterface::sendPushNotificationsToClients() for more information
	 * @param array $action: the action to be berformed when the user clicks on the notificaation - @see PushNotificationHandlerInterface::sendPushNotificationsToClients()
	 */
	public function sendPushNotificationsToFoodsaver(int $foodsaverId, string $title, array $options, array $action): void
	{
		$subscriptions = $this->db->fetchAllByCriteria(
			'fs_push_notification_subscription',
			['data', 'type'],
			['foodsaver_id' => $foodsaverId]
		);

		foreach ($this->pushNotificationHandlers as $handler) {
			$subscriptionDataForThisHandler = [];

			foreach ($subscriptions as $subscription) {
				if ($subscription['type'] === $handler::getTypeIdentifier()) {
					$subscriptionDataForThisHandler[] = $subscription['data'];
				}
			}

			if (!empty($subscriptionDataForThisHandler)) {
				$handler->sendPushNotificationsToClients($subscriptionDataForThisHandler, $title, $options, $action);
			}
		}
	}

	public function hasHandlerFor(string $typeIdentifier): bool
	{
		foreach ($this->pushNotificationHandlers as $handler) {
			if ($handler::getTypeIdentifier() === $typeIdentifier) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $typeIdentifier - the identifier of the PushNotificationHandler, returned by the getTypeIdentifier
	 * of the PushNotificationHandler object
	 */
	public function getPublicKey(string $typeIdentifier): string
	{
		return $this->pushNotificationHandlers[$typeIdentifier]->getPublicKey();
	}
}
