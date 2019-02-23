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
	 * @param string $action [string page, array params]: An array containing information about what to happen when the user
	 * 		clicks or taps on the notification. The page string should be one of the function names defined in
	 * 		client/src/urls.js, the params array should contain th()//e parameters to be passed to the function.
	 */
	public function sendPushNotificationsToClients(array $subscriptionData, string $title, array $options, array $action = null): void;

	/**
	 * Returns the public key fitting to the private key the PushNotificationHandler signs its notifications with.
	 */
	public function getPublicKey(): string;
}
