<?php

namespace Foodsharing\Modules\PushNotification;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\PushNotification\Notification\PushNotification;
use Foodsharing\Modules\PushNotification\PushNotificationHandlers\AndroidPushHandler;
use Foodsharing\Modules\PushNotification\PushNotificationHandlers\WebPushHandler;

class PushNotificationGateway extends BaseGateway
{
	/**
	 * @var PushNotificationHandlerInterface[]
	 */
	private $pushNotificationHandlers = [];

	public function __construct(Database $db, WebPushHandler $webPushHandler, AndroidPushHandler $androidPushHandler)
	{
		parent::__construct($db);
		$this->addHandler($androidPushHandler);
		$this->addHandler($webPushHandler);
	}

	public function addSubscription(int $foodsaverId, string $subscriptionData, string $type): int
	{
		if (!$this->hasHandlerFor($type)) {
			throw new \InvalidArgumentException("There is no handler registered to handle {$type}.");
		}

		return $this->db->insert('fs_push_notification_subscription', [
			'foodsaver_id' => $foodsaverId,
			'data' => $subscriptionData,
			'type' => $type
		]);
	}

	public function deleteSubscription(int $foodsaverId, int $subscriptionId, string $type)
	{
		if (!$this->hasHandlerFor($type)) {
			throw new \InvalidArgumentException("There is no handler registered to handle {$type}.");
		}

		return $this->db->delete('fs_push_notification_subscription', [
			'foodsaver_id' => $foodsaverId,
			'id' => $subscriptionId
		]);
	}

	/**
	 * @param int[] $subscriptionIds - array of subscription IDs to be removed
	 */
	private function deleteSubscriptions(array $subscriptionIds)
	{
		return $this->db->delete('fs_push_notification_subscription', ['id' => $subscriptionIds]);
	}

	public function addHandler(PushNotificationHandlerInterface $handler)
	{
		$this->pushNotificationHandlers[$handler::getTypeIdentifier()] = $handler;
	}

	public function sendPushNotificationsToFoodsaver(int $foodsaverId, PushNotification $notification): void
	{
		$subscriptions = $this->db->fetchAllByCriteria(
			'fs_push_notification_subscription',
			['id', 'data', 'type'],
			['foodsaver_id' => $foodsaverId]
		);

		foreach ($this->pushNotificationHandlers as $handler) {
			$subscriptionDataForThisHandler = [];

			foreach ($subscriptions as $subscription) {
				if ($subscription['type'] === $handler::getTypeIdentifier()) {
					$subscriptionDataForThisHandler[$subscription['id']] = $subscription['data'];
				}
			}

			if (!empty($subscriptionDataForThisHandler)) {
				$deadSubscriptions = $handler->sendPushNotificationsToClients($subscriptionDataForThisHandler, $notification);

				// safety check: only remove dead subscriptions that were in the array for this handler
				$subscriptionsToRemove = array_intersect($deadSubscriptions, array_keys($subscriptionDataForThisHandler));
				$this->deleteSubscriptions($subscriptionsToRemove);
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
	public function getServerInformation(string $typeIdentifier): array
	{
		return $this->pushNotificationHandlers[$typeIdentifier]->getServerInformation();
	}
}
