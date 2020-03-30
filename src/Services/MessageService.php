<?php

namespace Foodsharing\Services;

use Foodsharing\Helpers\EmailHelper;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Lib\Db\Db;
use Foodsharing\Lib\WebSocketConnection;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Message\MessageModel;

class MessageService
{
	private $emailHelper;
	private $foodsaverGateway;
	private $translationHelper;
	private $legacyDb;
	private $messageModel;
	private $webSocketConnection;

	public function __construct(
		EmailHelper $emailHelper,
		FoodsaverGateway $foodsaverGateway,
		TranslationHelper $translationHelper,
		Db $legacyDb,
		MessageModel $messageModel,
		WebSocketConnection $webSocketConnection
	) {
		$this->emailHelper = $emailHelper;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->translationHelper = $translationHelper;
		$this->legacyDb = $legacyDb;
		$this->messageModel = $messageModel;
		$this->webSocketConnection = $webSocketConnection;
	}

	public function sendMessageToUser(
		int $userId,
		int $senderId,
		string $body,
		string $notificationTemplate = null
	): void {
		$this->messageModel->message($userId, $body);
		$this->sendMailNotification($senderId, $userId, $body, $notificationTemplate);
	}

	private function sendMailNotification(
		int $senderId,
		int $recipientId,
		string $message,
		string $notificationTemplate = null
	): void {
		$info = $this->legacyDb->getVal('infomail_message', 'foodsaver', $recipientId);
		if ((int)$info > 0) {
			if (!isset($_SESSION['lastMailMessage'])) {
				$_SESSION['lastMailMessage'] = [];
			}

			// Only send message if the user is not currently logged in
			if (!$this->webSocketConnection->isUserOnline($recipientId)) {
				if (!isset($_SESSION['lastMailMessage'][$recipientId]) || (time(
						) - $_SESSION['lastMailMessage'][$recipientId]) > 600) {
					$_SESSION['lastMailMessage'][$recipientId] = time();
					$foodsaver = $this->foodsaverGateway->getFoodsaver($recipientId);
					$sender = $this->foodsaverGateway->getFoodsaver($senderId);

					$this->emailHelper->tplMail(
						$notificationTemplate,
						$foodsaver['email'],
						[
							'anrede' => $this->translationHelper->genderWord(
								$foodsaver['geschlecht'],
								'Lieber',
								'Liebe',
								'Liebe/r'
							),
							'sender' => $sender['name'],
							'name' => $foodsaver['name'],
							'message' => $message,
							'link' => BASE_URL . '/?page=msg&u2c=' . $senderId,
						]
					);
				}
			}
		}
	}
}
