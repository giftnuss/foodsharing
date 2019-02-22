<?php

namespace Foodsharing\Modules\PushNotification;

interface PushNotificationHandlerInterface
{
	/**
	 * Returns a string that identifies subscriptions that will be handled by this handler. It will be used in the
	 * database but also in the URL of the REST api.
	 */
	public static function getTypeIdentifier(): string;

	/**
	 * Returns the public key fitting to the private key the PushNotificationHandler signs its notifications with.
	 */
	public function getPublicKey(): string;

	/**
	 * Gets an array with subscription data strings in the format they were saved in the database and sends the
	 * $messasge to all of these clients.
	 *
	 * @param array $subscriptionData - an array with subscription data strings in the format they were saved in the database
	 * @param string title - the title of the notification
	 * @param array $options - an array containing the content of the notification. possible indexes:
	 *  -body: the message of the notification, usually displayed below the title
	 *  -icon: url to an icon to be displayed next to the notification
	 * All indexes are optional. Other indexes can be specific to the handler implementation or the endpoint the
	 * notification is sent to.
	 */
	public function sendPushNotificationsToClients(array $subscriptionData, string $title, array $options): void;
}
