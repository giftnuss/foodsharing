<?php

namespace Foodsharing\Modules\PushNotification\Notification;

use Foodsharing\Helpers\TranslationHelper;

/**
 * This push notification class is to be used for notifications about messages in a conversation. Push notification
 * handlers or clients are able to display notifications based on the information provided by instances of this class.
 */
class MessagePushNotification extends PushNotification
{
	/**
	 * @var string
	 *
	 * This is the author of the message. It's a foodsaver's user name, not a conversation title or anything.
	 */
	private $sender;

	/**
	 * @var string
	 *
	 * The actual text of the message
	 */
	private $body;

	/**
	 * @var \DateTime
	 *
	 * This is the time the message has been sent
	 */
	private $time;

	/**
	 * @var int
	 *
	 * The conversation id will be needed to enable the user to reply to the message this notification resembles
	 */
	private $conversationId;

	/**
	 * @var string|null
	 *
	 * Optional. This is the name of the conversation, if the conversation has one.
	 */
	private $conversationName;

	public function __construct(string $sender, string $body, \DateTime $time, int $conversationId, ?string $conversationName = null)
	{
		$this->sender = $sender;
		$this->body = $body;
		$this->time = $time;
		$this->conversationId = $conversationId;
		$this->conversationName = $conversationName;
	}

	public function getSender(): string
	{
		return $this->sender;
	}

	public function getBody(): string
	{
		return $this->body;
	}

	public function getTime(): \DateTime
	{
		return $this->time;
	}

	public function getConversationId(): int
	{
		return $this->conversationId;
	}

	public function getConversationName(): ?string
	{
		return $this->conversationName;
	}

	/**
	 * This will probably never be used as MessagePushNotification is the first of its kind, but you never know.
	 */
	public function getFallbackString(TranslationHelper $translationHelper): string
	{
		if ($this->getConversationName() !== null) {
			return $translationHelper->sv(
				'message_notification_named_conversation',
				['foodsaver' => $this->getSender(), 'conversation' => $this->getConversationName()]
			);
		}

		return $translationHelper->sv(
			'message_notification_unnamed_conversation',
			$this->getSender()
		);
	}
}