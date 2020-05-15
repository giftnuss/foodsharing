<?php

namespace Foodsharing\Services;

use Carbon\Carbon;
use Foodsharing\Helpers\EmailHelper;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\WebSocketConnection;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Message\Message;
use Foodsharing\Modules\Message\MessageGateway;
use Foodsharing\Modules\PushNotification\Notification\MessagePushNotification;
use Foodsharing\Modules\PushNotification\PushNotificationGateway;
use Foodsharing\Modules\Store\StoreGateway;

class MessageService
{
	private $emailHelper;
	private $foodsaverGateway;
	private $mem;
	private $messageGateway;
	private $storeGateway;
	private $translationHelper;
	private $pushNotificationGateway;
	private $webSocketConnection;

	public function __construct(EmailHelper $emailHelper, FoodsaverGateway $foodsaverGateway, Mem $mem, MessageGateway $messageGateway, StoreGateway $storeGateway, TranslationHelper $translationHelper, PushNotificationGateway $pushNotificationGateway, WebSocketConnection $webSocketConnection)
	{
		$this->emailHelper = $emailHelper;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->mem = $mem;
		$this->messageGateway = $messageGateway;
		$this->storeGateway = $storeGateway;
		$this->translationHelper = $translationHelper;
		$this->pushNotificationGateway = $pushNotificationGateway;
		$this->webSocketConnection = $webSocketConnection;
	}

	private function sendNewMessageNotificationEmail(array $recipient, array $templateData): void
	{
		/* skip repeated notification emails in a short interval */
		if (!isset($_SESSION['lastMailMessage']) || !is_array($sessdata = $_SESSION['lastMailMessage'])) {
			$sessdata = [];
		}

		if (!isset($sessdata[$recipient['id']]) || (time() - $sessdata[$recipient['id']]) > 600) {
			$sessdata[$recipient['id']] = time();

			$templateData = array_merge($templateData, [
				'anrede' => $this->translationHelper->genderWord($recipient['gender'], 'Lieber', 'Liebe', 'Liebe/r'),
				'name' => $recipient['name'],
			]);

			$this->emailHelper->tplMail($templateData['emailTemplate'], $recipient['email'], $templateData);
		}
		$_SESSION['lastMailMessage'] = $sessdata;
	}

	/**
	 * There are different ways conversations can be named:
	 * 	- Each conversation can have a custom name
	 * 		- Although we don't want to allow to rename conversations with less than three people, it is not the responsibility of this method.
	 *  - For conversations not having a name, the name will be the list of all people in there except the person to whom the list is displayed
	 *  - Store team conversations will also just have a custom name, so they don't need extra handling.
	 */
	public function getProperConversationNameForFoodsaver(int $foodsaverId, ?string $conversationName, ?array $members): string
	{
		if ($conversationName) {
			return $conversationName;
		}

		return implode(', ',
			array_column(array_filter($members,
					function ($m) use ($foodsaverId) {
						return $m['id'] != $foodsaverId;
					}),
				'name'
			));
	}

	private function getNotificationTemplateData(int $conversationId, Message $message, array $members, string $notificationTemplate = null): array
	{
		$data = [];
		$data['chatName'] = $this->messageGateway->getConversationName($conversationId);
		$data['store'] = $this->storeGateway->getStoreByConversationId($conversationId);
		if ($data['store']) {
			$data['store']['LINK'] = BASE_URL . '/?page=fsbetrieb&id=' . $data['store']['id'];
		} else {
			$data['store'] = null;
		}
		if ($notificationTemplate !== null) {
			$data['emailTemplate'] = $notificationTemplate;
		} else {
			$data['emailTemplate'] = 'chat/message';
		}
		$data['sender'] = $this->foodsaverGateway->getFoodsaverDetails($message->authorId)['name'];
		$data['message'] = $message->body;
		$data['link'] = BASE_URL . '/?page=msg&cid=' . $conversationId;

		return $data;
	}

	private function sendNewMessageNotifications(int $conversationId, Message $message, string $notificationTemplate = null): void
	{
		if ($members = $this->messageGateway->listConversationMembersWithProfile($conversationId)) {
			$user_ids = array_column($members, 'id');

			$author = array_values(array_filter($members, function ($m) use ($message) {
				return $m['id'] == $message->authorId;
			}));
			if (!$author) {
				/* sender of message seem to not be part of the conversation... How to handle? */
				$author = $this->foodsaverGateway->getFoodsaver($message->authorId);
			} else {
				$author = $author[0];
			}

			$this->webSocketConnection->sendSockMulti($user_ids, 'conv', 'push', [
				'cid' => $conversationId,
				'message' => $message,
				/* Compatibility part: This ensures readability of the push message in legacy clients. Will be removed after release */
				'id' => $message->id,
				'fs_id' => $message->authorId,
				'fs_name' => $author['name'],
				'fs_photo' => $author['photo'],
				'body' => htmlentities($message->body),
				'time' => $message->sentAt->format('Y-m-d H:i:s')
			]);

			$notificationTemplateData = $this->getNotificationTemplateData($conversationId, $message, $members, $notificationTemplate);
			foreach ($members as $m) {
				if (($m['id'] != $message->authorId) && !$this->webSocketConnection->isUserOnline($m['id'])) {
					$conversationName = $this->getProperConversationNameForFoodsaver($m['id'], $notificationTemplateData['chatName'], $members);
					$pushNotification = new MessagePushNotification(
						$author['name'],
						$message->body,
						new \DateTime(),
						$conversationId,
						count($members) > 2 ? $conversationName : null
					);
					$this->pushNotificationGateway->sendPushNotificationsToFoodsaver($m['id'], $pushNotification);
					if ($m['infomail_message']) {
						$this->sendNewMessageNotificationEmail($m, array_merge($notificationTemplateData, ['chatName' => $conversationName]));
					}
				}
			}
		}
	}

	public function sendMessageToUser(int $userId, int $senderId, string $body, string $notificationTemplate = null): ?Message
	{
		$conversationId = $this->messageGateway->getOrCreateConversation([$senderId, $userId]);

		return $this->sendMessage($conversationId, $senderId, $body, $notificationTemplate);
	}

	public function sendMessage(int $conversationId, int $senderId, string $body, string $notificationTemplate = null): ?Message
	{
		$body = trim($body);
		if (!empty($body)) {
			$time = Carbon::now();
			$message = $this->messageGateway->addMessage($conversationId, $senderId, $body, $time);
			$this->sendNewMessageNotifications($conversationId, $message, $notificationTemplate);

			return $message;
		}

		return null;
	}

	public function deleteUserFromConversation(int $conversationId, int $userId): bool
	{
		/* only allow removing users from non-locked conversations (as "locked" means more something like "is part
		of a synchronized user group".
		When a user gets removed, check if the whole conversation can be removed. */
		if (!$this->messageGateway->isConversationLocked(
				$conversationId
			) && $this->messageGateway->deleteUserFromConversation($conversationId, $userId)) {
			if (!$this->messageGateway->conversationHasRealMembers(($conversationId))) {
				$this->messageGateway->deleteConversation($conversationId);
			}

			return true;
		}

		return false;
	}

	public function listConversationsWithProfilesForUser(int $userId, ?int $limit = null, int $offset = 0): array
	{
		$conversations = $this->messageGateway->listConversationsForUser(
			$userId,
			$limit,
			$offset
		);

		$profileIDs = [];
		array_walk($conversations, function ($v, $k) use (&$profileIDs) {
			$profileIDs = array_merge($v->members, $profileIDs);
			if ($v->lastMessage) {
				$profileIDs[] = $v->lastMessage->authorId;
			}
		});
		$profileIDs = array_unique($profileIDs);
		$profiles = $this->foodsaverGateway->getProfileForUsers($profileIDs);

		return [
			'conversations' => $conversations,
			'profiles' => $profiles
		];
	}
}
