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

	/**
	 * @param string[] $subscriptionData - array of subscription data to be removed
	 */
	private function deleteSubscriptionsByData(array $subscriptionData)
	{
		return $this->db->delete('fs_push_notification_subscription', ['data' => $subscriptionData]);
	}

	public function addHandler(PushNotificationHandlerInterface $handler)
	{
		$this->pushNotificationHandlers[$handler::getTypeIdentifier()] = $handler;
	}

	/**
	 * @param string $title: the notification title
	 * @param array $options: an array of options to be sent to the endpoint - @see PushNotificationHandlerInterface::sendPushNotificationsToClients() for more information
	 * @param array $action: the action to be performed when the user clicks on the notification - @see PushNotificationHandlerInterface::sendPushNotificationsToClients()
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
				$deadSubscriptions = $handler->sendPushNotificationsToClients($subscriptionDataForThisHandler, $title, $options, $action);
				$this->deleteSubscriptionsByData($deadSubscriptions);
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
	 * method of the PushNotificationHandler object
	 */
	public function getEndpointInformation(string $typeIdentifier): array
	{
		return $this->pushNotificationHandlers[$typeIdentifier]->getEndpointInformation();
	}
}
