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

		$stat_gesamt = $this->statisticsGateway->listTotalStat();

		$stat_cities = $this->statisticsGateway->listStatCities();

		foreach ($stat_cities as $i => $c) {
			$stat_cities[$i]['percent'] = $this->getPercent($stat_gesamt['fetchweight'], $c['fetchweight']);
		}

		$stat_fs = $this->statisticsGateway->listStatFoodsaver();

		$this->pageHelper->addContent($this->view->getStatTotal($stat_gesamt), CNT_TOP);

		$this->pageHelper->addContent($this->view->getStatCities($stat_cities), CNT_LEFT);
		$this->pageHelper->addContent($this->view->getStatFoodsaver($stat_fs), CNT_RIGHT);

		$this->setContentWidth(12, 12);
	}

	private function getPercent($gesamt, $teil)
	{
		if ($gesamt) {
			return round(($teil / ($gesamt / 100)), 0);
		}

		return 0;
	}
}
