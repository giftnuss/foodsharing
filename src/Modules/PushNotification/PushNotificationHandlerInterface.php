<?php

namespace Foodsharing\Modules\PushNotification;

use Foodsharing\Modules\PushNotification\Notification\PushNotification;

interface PushNotificationHandlerInterface
{
	/**
	 * Returns a string that identifies subscriptions that will be handled by this handler. It will be used in the
	 * database but also in the URL of the REST api. (Example: type identifier "webpush" makes the API URL
	 * /pushnotification/webpush/resource).
	 */
	public static function getTypeIdentifier(): string;

	/**
	 * Sends a PushNotification to all of the clients belonging to $subscriptionData.
	 *
	 * @param string[] $subscriptionData - an map from subscription ID to subscription data for all subscriptions
	 * the notification should be sent to
	 *
	 * @return int[] - Dead subscriptions: The returned array contains the IDs of subscriptions to whose endpoints the
	 *		delivery failed. These dead subscriptions will be removed form the database.
	 */
	public function sendPushNotificationsToClients(array $subscriptionData, PushNotification $notification): array;

	/**
	 * Returns information like the public key fitting to the private key the PushNotificationHandler signs its notifications with.
	 */
	public function getServerInformation(): array;
}
