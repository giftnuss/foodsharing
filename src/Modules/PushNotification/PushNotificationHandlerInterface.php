<?php

namespace Foodsharing\Modules\PushNotification;

interface PushNotificationHandlerInterface
{
	/**
	 * Returns a string that identifies subscriptions that will be handled by this handler. It will be used in the
	 * database but also in the URL of the REST api.
	 */
	static function getTypeIdentifier(): string;

	/**
	 * Returns the public key fitting to the private key the PushNotificationHandler signs its notifications with.
	 */
	function getPublicKey(): string;

	/**
	 * Gets an array with subscription data strings in the format they were saved in the database and sends the
	 * $messasge to all of these clients.
	 *
	 * @param $action - the action to be called when the message is being clicked on
	 *
	 * @var array $subscriptionData - an array with subscription data strings in the format they were saved in the database
	 */
	function sendPushNotificationsToClients(array $subscriptionData, string $title, string $message, ?string $action = null): void;
}