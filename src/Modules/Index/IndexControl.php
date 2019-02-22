<?php

namespace Foodsharing\Modules\Index;

use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;

class IndexControl extends Control
{
	private $contentGateway;
	private $indexGateway;

	public function __construct(IndexGateway $indexGateway, IndexView $view, ContentGateway $contentGateway)
	{
		$this->indexGateway = $indexGateway;
		parent::__construct();
		$this->contentGateway = $contentGateway;
		$this->view = $view;
	}

	public function index()
	{
		$this->pageCompositionHelper->addTitle('Rette mit!');

		$gerettet = (int)$this->indexGateway->getFetchedWeight();

		if ($gerettet == 0) {
			$gerettet = 762338;
		}

		$gerettet = round($gerettet, 0);

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

		$this->pageCompositionHelper->addContent($this->view->index(
			$page_content['body'],
			$gerettet
		), CNT_OVERTOP);
	}
}
