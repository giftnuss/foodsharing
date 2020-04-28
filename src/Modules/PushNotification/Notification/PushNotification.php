<?php

namespace Foodsharing\Modules\PushNotification\Notification;

use Symfony\Contracts\Translation\TranslatorInterface;

abstract class PushNotification
{
	/**
	 * More complex PushNotifications should provide all metadata needed for the PushNotificationHandler or the client
	 * to render a beautiful message according to the specific PushNotification type using dedicated getters. For the
	 * case this is not possible or needed, e. g. because the notification type is so simple it doesn't provide any
	 * metadata, or the client or handler does not know how to render a message from the metadata of a specific
	 * PushNotification class, every PushNotification must be able to render a title and a body itself, on which the
	 * client (or the handler) then can fall back on.
	 */
	abstract public function getTitle(TranslatorInterface $translator): string;

	abstract public function getBody(TranslatorInterface $translator): string;
}
