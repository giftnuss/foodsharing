<?php

namespace Foodsharing\Modules\Index;

use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;

class IndexControl extends Control
{
	private $contentGateway;

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
			$page_content = $this->contentGateway->get(37);
		} elseif (strpos($host, 'foodsharingschweiz.ch') !== false) {
			$page_content = $this->contentGateway->get(47);
		} elseif (strpos($host, 'beta.foodsharing.de') !== false) {
			$page_content = $this->contentGateway->get(48);
		} else {
			$page_content = $this->contentGateway->get(38);
		}

		$this->pageHelper->addContent($this->view->index(
			$page_content['body']
		), CNT_OVERTOP);
	}
}
