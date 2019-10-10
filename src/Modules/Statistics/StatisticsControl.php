<?php

namespace Foodsharing\Modules\Statistics;

use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;

class StatisticsControl extends Control
{
	private $contentGateway;
	private $statisticsGateway;

	public function __construct(StatisticsGateway $statisticsGateway, StatisticsView $view, ContentGateway $contentGateway)
	{
		$this->statisticsGateway = $statisticsGateway;
		$this->view = $view;
		$this->contentGateway = $contentGateway;

		parent::__construct();
	}

	public function index()
	{
		$content = $this->contentGateway->get(11);

		$this->pageHelper->addTitle($content['title']);
		$this->pageHelper->addBread($content['title']);

		$stat_total = $this->statisticsGateway->listTotalStat();
		$stat_total['totalBaskets'] = $this->statisticsGateway->countAllBaskets();
		$stat_total['avgWeeklyBaskets'] = $this->statisticsGateway->avgWeeklyBaskets();

		$stat_cities = $this->statisticsGateway->listStatCities();
		$stat_fs = $this->statisticsGateway->listStatFoodsaver();

		$this->pageHelper->addContent($this->view->getStatTotal($stat_total, $this->statisticsGateway->countAllFoodsharers(), $this->statisticsGateway->avgDailyFetchCount()), CNT_TOP);
		$this->pageHelper->addContent($this->view->getStatCities($stat_cities), CNT_LEFT);
		$this->pageHelper->addContent($this->view->getStatFoodsaver($stat_fs), CNT_RIGHT);

		$this->setContentWidth(12, 12);
	}
}
