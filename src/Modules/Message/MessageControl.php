<?php

namespace Foodsharing\Modules\Message;

use Foodsharing\Modules\Core\Control;

final class MessageControl extends Control
{
	private MessageGateway $messageGateway;
	private MessageTransactions $messageTransactions;

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
		$this->pageHelper->addBread($this->translator->trans('messages.bread'));
		$this->pageHelper->addTitle($this->translator->trans('messages.bread'));

		$this->pageHelper->addContent($this->view->compose());
		$this->pageHelper->addContent($this->view->conversation());

		if (!$this->session->isMob()) { /* for desktop only */
			$this->pageHelper->addContent($this->view->leftMenu(), CNT_RIGHT);
		}

		$data = $this->messageTransactions->listConversationsWithProfilesForUser($this->session->id());
		$this->pageHelper->addContent($this->view->conversationListWrapper(
			$this->view->conversationList($data['conversations'], $data['profiles'])
		), CNT_RIGHT);
	}
}
