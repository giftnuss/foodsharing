<?php


namespace Foodsharing\Modules\PushNotification\Notification;


use Foodsharing\Helpers\TranslationHelper;

abstract class PushNotification
{
	/**
	 * If a PushNotificationHandler does not know a push notification type, it can create a simple text notification
	 * without any extra features that features this fallback string. This is could useful for outdated clients, that
	 * could still display some sort of notification even if it does not know any features specific to the notification
	 * type.
	 */
	public abstract function getFallbackString(TranslationHelper $translationHelper): string;
}
