<?php

namespace Foodsharing\Modules\Index;

use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Content\ContentId;

class IndexControl extends Control
{
	private ContentGateway $contentGateway;

	public function __construct(IndexView $view, ContentGateway $contentGateway)
	{
		parent::__construct();
		$this->contentGateway = $contentGateway;
		$this->view = $view;
	}

	public function index()
	{
		$this->pageHelper->addTitle('Rette mit!');

		$host = $_SERVER['HTTP_HOST'] ?? BASE_URL;
		if (strpos($host, 'foodsharing.at') !== false) {
			$page_content_block1 = $this->contentGateway->get(ContentId::STARTPAGE_BLOCK1_AT);
			$page_content_block2 = $this->contentGateway->get(ContentId::STARTPAGE_BLOCK2_AT);
			$page_content_block3 = $this->contentGateway->get(ContentId::STARTPAGE_BLOCK3_AT);
		} elseif (strpos($host, 'foodsharingschweiz.ch') !== false) {
			$page_content_block1 = $this->contentGateway->get(ContentId::STARTPAGE_BLOCK1_CH);
			$page_content_block2 = $this->contentGateway->get(ContentId::STARTPAGE_BLOCK2_CH);
			$page_content_block3 = $this->contentGateway->get(ContentId::STARTPAGE_BLOCK3_CH);
		} elseif (strpos($host, 'beta.foodsharing.de') !== false) {
			$page_content_block1 = $this->contentGateway->get(ContentId::STARTPAGE_BLOCK1_BETA);
			$page_content_block2 = $this->contentGateway->get(ContentId::STARTPAGE_BLOCK2_BETA);
			$page_content_block3 = $this->contentGateway->get(ContentId::STARTPAGE_BLOCK3_BETA);
		} else {
			$page_content_block1 = $this->contentGateway->get(ContentId::STARTPAGE_BLOCK1_DE);
			$page_content_block2 = $this->contentGateway->get(ContentId::STARTPAGE_BLOCK2_DE);
			$page_content_block3 = $this->contentGateway->get(ContentId::STARTPAGE_BLOCK3_DE);
		}

		$this->pageHelper->addContent($this->view->index(
			$page_content_block1['body'],
			$page_content_block2['body'],
			$page_content_block3['body']), CNT_OVERTOP
		);
	}
}
