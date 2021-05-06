<?php

namespace Foodsharing\Modules\PushNotification\Notification;

use Foodsharing\Modules\Foodsaver\Profile;
use Foodsharing\Modules\Message\Message;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This push notification class is to be used for notifications about messages in a conversation. Push notification
 * handlers or clients are able to display notifications based on the information provided by instances of this class.
 */
class MessagePushNotification extends PushNotification
{
	/**
	 * The message to which this notification refers.
	 */
	private Message $message;

	/**
	 * This is the author of the message. It's a foodsaver's user name, not a conversation title or anything.
	 */
	private Profile $author;

	/**
	 * The conversation id will be needed to enable the user to reply to the message this notification resembles.
	 */
	private int $conversationId;

	/**
	 * Optional. This is the name of the conversation, if the conversation has one.
	 */
	private ?string $conversationName;

	public function __construct(Message $message, Profile $author, int $conversationId, ?string $conversationName = null)
	{
		$this->message = $message;
		$this->author = $author;
		$this->conversationId = $conversationId;
		$this->conversationName = $conversationName;
	}

	public function getMessage(): Message
	{
		return $this->message;
	}

	public function getAuthor(): Profile
	{
		return $this->author;
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
	 * This is not only the fall back body, but the actual body of the message. Because message bodies can't be
	 * translated, the Translator is not needed and defaults to null.
	 */
	public function getBody(TranslatorInterface $translator = null): string
	{
		return $this->message->body;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTitle(TranslatorInterface $translator): string
	{
		$userName = $this->author->name ?? $translator->trans('dashboard.deleted_user');
		if ($this->getConversationName() !== null) {
			return $translator->trans(
				'chat.notification_named_conversation',
				['{foodsaver}' => $userName, '{conversation}' => $this->getConversationName()]
			);
		}

		return $translator->trans(
			'chat.notification_unnamed_conversation',
			['{foodsaver}' => $userName]
		);
	}
}
