<?php

namespace Foodsharing\Modules\PushNotification;

use Foodsharing\Modules\PushNotification\Notification\PushNotification;

interface PushNotificationHandlerInterface
{
	/**
	 * Returns a string that identifies subscriptions that will be handled by this handler. It will be used in the
	 * database but also in the URL of the REST api.
	 */
	public static function getTypeIdentifier(): string;

	/**
	 * Gets an array with subscription data strings in the format they were saved in the database and sends the
	 * $messasge to all of these clients.
	 *
	 * @param array $subscriptionData - an array with subscription data strings in the format they were saved in the database
	 *
	 * @return string[] - Dead subscriptions: The returned array contains strings that identify endpoints to which the
	 * 		delivery failed. Subscriptions with data equaling one of the dead subscriptions will be removed form the
	 * 		database.
	 */
	public function sendPushNotificationsToClients(array $subscriptionData, PushNotification $notification): array;

	/**
	 * Returns information like the public key fitting to the private key the PushNotificationHandler signs its notifications with.
	 */
	public function getServerInformation(): array;
}
