<?php

namespace Foodsharing\Modules\Message;

use Foodsharing\Lib\WebSocketSender;
use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Store\StoreGateway;

final class MessageXhr extends Control
{
	private $messageGateway;
	private $storeGateway;
	/**
	 * @var WebSocketSender
	 */
	private $webSocketSender;

	public function __construct(MessageModel $model, MessageView $view, MessageGateway $messageGateway, StoreGateway $storeGateway, WebSocketSender $webSocketSender)
	{
		$this->model = $model;
		$this->view = $view;
		$this->messageGateway = $messageGateway;
		$this->storeGateway = $storeGateway;
		$this->webSocketSender = $webSocketSender;

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
		if ($this->messageGateway->mayConversation($this->session->id(), $_GET['cid']) && !$this->messageGateway->conversationLocked($_GET['cid'])) {
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
		if ($this->messageGateway->mayConversation($this->session->id(), $_GET['cid']) && !$this->messageGateway->conversationLocked(
				$_GET['cid']
			) && $this->model->deleteUserFromConversation($_GET['cid'], $this->session->id())) {
			$xhr = new Xhr();
			$xhr->addScript('conv.close(' . (int)$_GET['cid'] . ');$("#convlist-' . (int)$_GET['cid'] . '").remove();conv.registerPollingService();');
			$xhr->send();
		}
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
			$recip = array();
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

	public function people(): void
	{
		$this->session->noWrite();

		$term = trim($_GET['term']);
		if ($people = $this->model->findConnectedPeople($term)) {
			echo json_encode($people);
			exit();
		}

		echo json_encode(array());
		exit();
	}
}
