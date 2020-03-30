<?php

namespace Foodsharing\Modules\Message;

use Foodsharing\Lib\WebSocketConnection;
use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\PushNotification\Notification\MessagePushNotification;
use Foodsharing\Modules\PushNotification\PushNotificationGateway;

final class MessageXhr extends Control
{
	/**
	 * @var MessageGateway
	 */
	private $messageGateway;
	/**
	 * @var FoodsaverGateway
	 */
	private $foodsaverGateway;
	/**
	 * @var WebSocketConnection
	 */
	private $webSocketConnection;
	/**
	 * @var PushNotificationGateway
	 */
	private $pushNotificationGateway;

	public function __construct(
		MessageModel $model,
		MessageView $view,
		MessageGateway $messageGateway,
		FoodsaverGateway $foodsaverGateway,
		PushNotificationGateway $pushNotificationGateway,
		WebSocketConnection $webSocketConnection
	) {
		$this->model = $model;
		$this->view = $view;
		$this->messageGateway = $messageGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->pushNotificationGateway = $pushNotificationGateway;
		$this->webSocketConnection = $webSocketConnection;

		parent::__construct();

		if (!$this->session->may()) {
			echo '';
			exit();
		}
	}

	/**
	 * ajax call to rename an conversation.
	 */
	public function rename(): void
	{
		if ($this->mayConversation($_GET['cid']) && !$this->messageGateway->conversationLocked($_GET['cid'])) {
			$xhr = new Xhr();

			$name = htmlentities($_GET['name']);
			$name = trim($name);

			if (($name != '') && $this->messageGateway->renameConversation($_GET['cid'], $name)) {
				$xhr->addScript('$("#chat-' . (int)$_GET['cid'] . ' .chatboxtitle").html(\'<i class="fas fa-comment fa-flip-horizontal"></i> ' . $name . '\');conv.settings(' . (int)$_GET['cid'] . ');$("#convlist-' . (int)$_GET['cid'] . ' .names").html("' . $name . '")');
			}

			$xhr->send();
		}
	}

	/**
	 * ajax call to delete logged in user from an chat.
	 */
	public function leave(): void
	{
		if ($this->mayConversation($_GET['cid']) && !$this->messageGateway->conversationLocked(
				$_GET['cid']
			) && $this->model->deleteUserFromConversation($_GET['cid'], $this->session->id())) {
			$xhr = new Xhr();
			$xhr->addScript('conv.close(' . (int)$_GET['cid'] . ');$("#convlist-' . (int)$_GET['cid'] . '").remove();conv.registerPollingService();');
			$xhr->send();
		}
	}

	/**
	 * ajax call to load an existing conversation.
	 */
	public function loadconversation(): void
	{
		$id = (int)$_GET['id'];
		if ($this->mayConversation($id) && $member = $this->model->listConversationMembers($id)) {
			$xhr = new Xhr();
			// $xhr->addData('member', $member);
			$xhr->addData('member', array_map(function ($m) {
				return [
					'id' => $m['id'],
					'name' => $m['name'],
					'photo' => $m['photo']
				];
			}, $member));
			$xhr->addData('conversation', $this->model->getValues(['name'], 'conversation', $id));
			if ($msgs = $this->messageGateway->getConversationMessages($id)) {
				$xhr->addData('messages', $msgs);
			}

			$this->model->setAsRead([(int)$_GET['id']]);

			$xhr->send();
		}
	}

	/**
	 * ajax call to load more older messages from a specified conversation.
	 *
	 * GET['lmid'] = last message id
	 * GET['cid'] = conversation_id
	 */
	public function loadmore(): void
	{
		if ($this->mayConversation((int)$_GET['cid'])) {
			$xhr = new Xhr();
			if ($msgs = $this->messageGateway->loadMore((int)$_GET['cid'], (int)$_GET['lmid'])) {
				$xhr->addData('messages', $msgs);
			} else {
				$xhr->setStatus(0);
			}
			$xhr->send();
		}
	}

	/**
	 * ajax call to send a message to an conversation.
	 *
	 * GET['b'] = body text
	 * GET['c'] = conversation id
	 */
	public function sendmsg(): void
	{
		$xhr = new Xhr();
		$conversationId = $_POST['c'];

		if (!$this->mayConversation($conversationId)) {
			http_response_code(403);

			return;
		}
		$this->session->noWrite();

		$body = trim($_POST['b'] ?? '');
		$body = htmlentities($body);
		$message_id = $this->model->sendMessage($conversationId, $body);
		if (empty($body) || $message_id === false) {
			http_response_code(400);

			return;
		}
		$xhr->setStatus(1);

		/*
		 * for not so db intensive polling store updates in memcache if the recipients are online
		*/
		$members = $this->model->listConversationMembers($conversationId);
		$userIds = array_column($members, 'id');

		$this->webSocketConnection->sendSockMulti($userIds, 'conv', 'push', [
			'id' => $message_id,
			'cid' => (int)$conversationId,
			'fs_id' => $this->session->id(),
			'fs_name' => $this->session->user('name'),
			'fs_photo' => $this->session->user('photo'),
			'body' => $body,
			'time' => date('Y-m-d H:i:s')
		]);

		foreach ($members as $member) {
			if ($member['id'] == $this->session->id()) {
				continue; // don't notify the user who sent the notification
			}

			$this->mem->userAppend($member['id'], 'msg-update', (int)$conversationId);

			if ($this->webSocketConnection->isUserOnline($member['id'])) {
				continue; // don't send E-Mail or Push Notifications for users who are online
			}
			/*
			 * send Push Notification
			 *
			 */
			$pushNotification = new MessagePushNotification(
				$this->session->user('name'),
				html_entity_decode($body),
				new \DateTime(),
				$conversationId,
				count($members) > 2 ? $this->messageGateway->getProperConversationNameForFoodsaver($member['id'], $conversationId) : null
			);

			$this->pushNotificationGateway->sendPushNotificationsToFoodsaver($member['id'], $pushNotification);

			/*
			 * send an E-Mail notification
			 */
			if ($member['infomail_message']) {
				$this->convMessage($member, $conversationId, $body);
			}
		}

		$xhr->addData('msg', [
			'id' => $message_id,
			'body' => $body,
			'time' => date('Y-m-d H:i:s'),
			'fs_photo' => $this->session->user('photo'),
			'fs_name' => $this->session->user('name'),
			'fs_id' => $this->session->id()
		]);
		$xhr->send();
	}

	/**
	 * ajax call to load all active conversations.
	 */
	public function loadconvlist(): void
	{
		$this->session->noWrite();

		$limit = -1;
		if (isset($_GET['limit'])) {
			$limit = (int)$_GET['limit'];
		}

		if ($conversations = $this->model->listConversations($limit)) {
			$xhr = new Xhr();

			// because some of the messages and the titles are still stored in encoded html, theres the option to
			// decode them again for the usage in vue components
			// At some point there should always the raw input handled, which the user has entered
			// and served over a proper API endpoint

			$conversations = array_map(function ($c) {
				$c['member'] = array_map(function ($m) {
					return [
						'id' => $m['id'],
						'name' => $m['name'],
						'photo' => $m['photo']
					];
				}, $c['member']);

				return $c;
			}, $conversations);
			if (isset($_GET['raw']) && $_GET['raw']) {
				$xhr->addData('convs', array_map(function ($c) {
					$c['last'] = $c['last'] ? str_replace(' ', 'T', $c['last']) : null;
					if (isset($c['name']) && $c['name']) {
						$c['name'] = html_entity_decode($c['name']);
					}
					if (isset($c['last_message'])) {
						$c['last_message'] = html_entity_decode($c['last_message']);
					}
					$c['member'] = array_map(function ($m) {
						return [
							'id' => $m['id'],
							'name' => $m['name'],
							'photo' => $m['photo']
						];
					}, $c['member']);

					return $c;
				}, $conversations));
			} else {
				$xhr->addData('convs', $conversations);
			}
			$xhr->send();
		}
	}

	/**
	 * Method to check that the user is part of an conversation and has access, to reduce database querys we store conversation_ids in an array.
	 */
	private function mayConversation(int $conversation_id): bool
	{
		// first get the session array
		if (!($ids = $this->session->get('msg_conversations'))) {
			$ids = [];
		}

		// check if the conversation in stored in the session
		if (isset($ids[$conversation_id])) {
			return true;
		}

		if ($this->model->mayConversation($conversation_id)) {
			$ids[$conversation_id] = true;
			$this->session->set('msg_conversations', $ids);

			return true;
		}

		return false;
	}

	public function user2conv(): void
	{
		$xhr = new Xhr();

		if (isset($_GET['fsid']) && (int)$_GET['fsid'] > 0 && $cid = $this->model->user2conv($_GET['fsid'])) {
			$xhr->setStatus(1);
			$xhr->addData('cid', $cid);
			$xhr->send();
		}

		$xhr->setStatus(0);
		$xhr->send();
	}

	/**
	 * ajax call to add an new conversation to this call comes 2 important POST parameters recip => an array with user ids body => the message body text.
	 */
	public function newconversation(): void
	{
		/*
		 *  body	asd
			recip[]	56
			recip[]	58
		 */

		/*
		 * Check is there are correct post data sender?
		 */
		if (isset($_POST['recip'], $_POST['body'])) {
			/*
			 * initiate an xhr object
			 */
			$xhr = new Xhr();

			/*
			 * Make all ids to int and remove doubles check its not 0
			 */
			$recip = [];
			foreach ($_POST['recip'] as $r) {
				if ((int)$r > 0) {
					$recip[(int)$r] = (int)$r;
				}
			}

			/*
			 * quick body text preparing
			 */
			$body = htmlentities(trim($_POST['body']));

			if (!empty($recip) && $body != '') {
				/*
				 * add conversation if successful send an success message otherwise error
				 */
				if ($cid = $this->model->addConversation($recip, $body)) {
					/*
					 * add the conversation id to ajax output
					 */
					$xhr->addData('cid', $cid);
				} else {
					$xhr->addMessage($this->translationHelper->s('error'), 'error');
				}
			} else {
				$xhr->addMessage($this->translationHelper->s('wrong_recip_count'), 'error');
			}

			/*
			 * send all ajax stuff to the client
			 */
			$xhr->send();
		}
	}

	/**
	 * ajax call to check every time updates in all conversations
	 * GET[m] is the last message id and GET[cid] is the current conversation id.
	 *
	 * @param $opt
	 *
	 * @return array|bool
	 */
	public function heartbeat($opt)
	{
		$cid = false;
		$lmid = false;

		if (isset($opt['cid'], $opt['mid']) && $this->mayConversation($opt['cid'])) {
			$cid = (int)$opt['cid'];
			$lmid = (int)$opt['mid'];
		}

		if ($conversationIDs = $this->model->checkConversationUpdates()) {
			$conversationKeys = array_flip($conversationIDs);

			$this->model->setAsRead($conversationIDs);
			$return = [];
			/*
			 * check is a new message there for active conversation?
			 */

			if ($cid && isset($conversationKeys[$cid]) && $messages = $this->messageGateway->getLastMessages($cid, $lmid)) {
				$return['messages'] = $messages;
			}

			if ($conversations = $this->messageGateway->listConversationUpdates($conversationIDs)) {
				$return['convs'] = $conversations;
			}

			return [
				'data' => $return,
				'script' => 'msg.pushArrived(ajax.data);'
			];
		}

		return false;
	}

	public function people(): void
	{
		$this->session->noWrite();

		$term = trim($_GET['term']);
		if ($people = $this->model->findConnectedPeople($term)) {
			echo json_encode($people);
			exit();
		}

		echo json_encode([]);
		exit();
	}

	private function convMessage($recipient, $conversation_id, $msg)
	{
		if (!isset($_SESSION['lastMailMessage']) || !is_array($sessdata = $_SESSION['lastMailMessage'])) {
			$sessdata = [];
		}

		if (!isset($sessdata[$recipient['id']]) || (time() - $sessdata[$recipient['id']]) > 600) {
			$sessdata[$recipient['id']] = time();

			$chatname = $this->messageGateway->getProperConversationNameForFoodsaver($recipient['id'], $conversation_id);

			$this->emailHelper->tplMail('chat/message', $recipient['email'], [
				'anrede' => $this->translationHelper->genderWord($recipient['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
				'sender' => $this->session->user('name'),
				'name' => $recipient['name'],
				'chatname' => $chatname,
				'message' => $msg,
				'link' => BASE_URL . '/?page=msg&cid=' . (int)$conversation_id
			]);
		}

		$_SESSION['lastMailMessage'] = $sessdata;
	}
}
