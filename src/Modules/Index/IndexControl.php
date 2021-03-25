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
			$contentIds = [ContentId::STARTPAGE_BLOCK1_AT, ContentId::STARTPAGE_BLOCK2_AT, ContentId::STARTPAGE_BLOCK3_AT];
			$country = 'AT';
		} elseif (strpos($host, 'foodsharingschweiz.ch') !== false) {
			$contentIds = [ContentId::STARTPAGE_BLOCK1_CH, ContentId::STARTPAGE_BLOCK2_CH, ContentId::STARTPAGE_BLOCK3_CH];
			$country = 'CH';
		} elseif (strpos($host, 'beta.foodsharing.de') !== false) {
			$contentIds = [ContentId::STARTPAGE_BLOCK1_BETA, ContentId::STARTPAGE_BLOCK2_BETA, ContentId::STARTPAGE_BLOCK3_BETA];
			$country = 'BETA';
		} else {
			$contentIds = [ContentId::STARTPAGE_BLOCK1_DE, ContentId::STARTPAGE_BLOCK2_DE, ContentId::STARTPAGE_BLOCK3_DE];
			$country = 'DE';
		}

		$page_content_blocks = $this->contentGateway->getMultiple($contentIds);
		$this->pageHelper->addContent($this->view->index(
			$page_content_blocks[0]['body'],
			$page_content_blocks[1]['body'],
			$page_content_blocks[2]['body'],
			$country), CNT_OVERTOP
		);
	}
}
