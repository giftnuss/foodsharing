<?php

namespace Foodsharing\Modules\Dashboard;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;

class DashboardControl extends Control
{
	private $user;
	private $gateway;
	private $contentGateway;

	public function __construct(
		DashboardView $view,
		DashboardGateway $gateway,
		ContentGateway $contentGateway)
	{
		$this->view = $view;
		$this->gateway = $gateway;
		$this->contentGateway = $contentGateway;

		parent::__construct();

		if (!S::may()) {
			go('/');
		}

		$this->user = $this->gateway->getUser(fsId());
	}

	public function index()
	{
		addBread(s('dashboard'));
		addTitle(s('dashboard'));
		/*
		 * User is foodsaver
		 */

		if ($this->user['rolle'] > 0 && !getBezirkId()) {
			addJs('becomeBezirk();');
		}

		// foodsharer dashboard
		$this->dashFs();
	}

	private function dashFs()
	{
		$this->setContentWidth(8, 8);
		$subtitle = s('no_saved_food');

		if ($this->user['stat_fetchweight'] > 0) {
			$subtitle = sv('saved_food', array('weight' => $this->user['stat_fetchweight']));
		}

		addContent(
			$this->view->topbar(
				sv('welcome', array('name' => $this->user['name'])),
				$subtitle,
				avatar($this->user, 50, '/img/fairteiler50x50.png')
			),
			CNT_TOP
		);

		addContent($this->view->becomeFoodsaver());

		addContent($this->view->foodsharerMenu(), CNT_LEFT);

		$cnt = $this->contentGateway->getContent(33);

		$cnt['body'] = str_replace(array(
			'{NAME}',
			'{ANREDE}'
		), array(
			S::user('name'),
			s('anrede_' . S::user('gender'))
		), $cnt['body']);

		addContent(v_info($cnt['body'], $cnt['title']));

		$this->view->updates();

		if ($this->user['lat'] && ($baskets = $this->gateway->listCloseBaskets(fsId(), S::getLocation(), 50))) {
			addContent($this->view->closeBaskets($baskets), CNT_LEFT);
		} else {
			if ($baskets = $this->gateway->getNewestFoodbaskets()) {
				addContent($this->view->newBaskets($baskets), CNT_LEFT);
			}
		}
	}
}
