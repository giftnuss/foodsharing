<?php

namespace Foodsharing\Modules\PushNotification\Notification;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Push Notification to be sent as a greeting or test right after a user subscribed to push notifications. Its purpose
 * is not only to simplify testing and debugging of push notification clients, but also to demonstrate
 * Push Notifications to users who might not have heard of this feature before and activated them out of curiosity.
 */
class TestPushNotification extends PushNotification
{
	public function getTitle(TranslatorInterface $translator): string
	{
		return $translator->trans('push_notifications.test_notification_title');
	}

	public function getBody(TranslatorInterface $translator): string
	{
		return $translator->trans('push_notifications.test_notification_body');
	}
}
