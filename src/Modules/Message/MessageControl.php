<?php

namespace Foodsharing\Modules\Message;

use Foodsharing\Modules\Core\Control;

final class MessageControl extends Control
{
	private $messageGateway;
	private $messageTransactions;

	public function __construct(
		MessageGateway $messageGateway,
		MessageTransactions $messageTransactions,
		MessageView $view
	) {
		$this->view = $view;
		$this->messageGateway = $messageGateway;
		$this->messageTransactions = $messageTransactions;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->goLogin();
		}
	}

	public function index(): void
	{
		$this->setTemplate('msg');
		$this->setContentWidth(5, 8);

		$this->pageHelper->addJs('msg.fsid = ' . (int)$this->session->id() . ';');
		$this->pageHelper->addBread($this->translationHelper->s('messages'));
		$this->pageHelper->addTitle($this->translationHelper->s('messages'));

		$this->pageHelper->addContent($this->view->compose());
		$this->pageHelper->addContent($this->view->conversation());

		if (!$this->session->isMob()) { /* for desktop only */
			$this->pageHelper->addContent($this->view->leftMenu(), CNT_RIGHT);
		}

		$data = $this->messageTransactions->listConversationsWithProfilesForUser($this->session->id());
		$this->pageHelper->addContent($this->view->conversationListWrapper($this->view->conversationList($data['conversations'], $data['profiles'])), CNT_RIGHT);
	}
}
