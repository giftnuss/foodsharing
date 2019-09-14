<?php

namespace Foodsharing\Services;

use Carbon\Carbon;
use Foodsharing\Helpers\EmailHelper;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\WebSocketSender;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Message\Message;
use Foodsharing\Modules\Message\MessageGateway;
use Foodsharing\Modules\Store\StoreGateway;

class MessageService
{
	private $emailHelper;
	private $foodsaverGateway;
	private $mem;
	private $messageGateway;
	private $storeGateway;
	private $translationHelper;
	private $webSocketSender;

	public function __construct(EmailHelper $emailHelper, FoodsaverGateway $foodsaverGateway, Mem $mem, MessageGateway $messageGateway, StoreGateway $storeGateway, TranslationHelper $translationHelper, WebSocketSender $webSocketSender)
	{
		$this->emailHelper = $emailHelper;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->mem = $mem;
		$this->messageGateway = $messageGateway;
		$this->storeGateway = $storeGateway;
		$this->translationHelper = $translationHelper;
		$this->webSocketSender = $webSocketSender;
	}

	private function sendNewMessageNotificationEmail(array $recipient, array $templateData): void
	{
		if (!$this->mem->userOnline($recipient['id'])) {
			/* skip repeated notification emails in a short interval */
			if (!isset($_SESSION['lastMailMessage']) || !is_array($sessdata = $_SESSION['lastMailMessage'])) {
				$sessdata = array();
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
	}

	private function getNotificationTemplateData(int $conversationId, Message $message, array $members, string $notificationTemplate = null): array
	{
		$data = [];
		$storeName = $this->storeGateway->getStoreNameByConversationId($conversationId);
		if ($notificationTemplate !== null) {
			$data['emailTemplate'] = $notificationTemplate;
		} elseif ($storeName !== null) {
			$data['emailTemplate'] = 'chat/message_store';
			$data['storeName'] = $storeName;
		} else {
			if (count($members) > 2) {
				$data['emailTemplate'] = 'chat/message_group';
				$data['chatName'] =
					implode(', ',
						array_column(
							array_filter($members,
								function ($m) use ($message) {
									return $m['id'] != $message->authorId;
								}),
							'name'
						)
					);
			} else {
				$data['emailTemplate'] = 'chat/message';
			}
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

			$this->webSocketSender->sendSockMulti($user_ids, 'conv', 'push', array(
				'cid' => $conversationId,
				'message' => $message,
			));

			$notificationTemplateData = $this->getNotificationTemplateData($conversationId, $message, $members, $notificationTemplate);
			foreach ($members as $m) {
				if (($m['id'] != $message->authorId) && $m['infomail_message']) {
					$this->sendNewMessageNotificationEmail($m, $notificationTemplateData);
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
