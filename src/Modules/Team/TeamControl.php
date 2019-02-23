<?php

namespace Foodsharing\Modules\Team;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;

class TeamControl extends Control
{
	private $gateway;
	private $contentGateway;

	public function __construct(Db $model, TeamGateway $gateway, TeamView $view, ContentGateway $contentGateway)
	{
		$this->gateway = $gateway;
		$this->model = $model;
		$this->view = $view;
		$this->contentGateway = $contentGateway;

		parent::__construct();
	}

	public function index(): void
	{
		$this->pageCompositionHelper->addBread($this->func->s('team'), '/team');
		$this->pageCompositionHelper->addTitle($this->func->s('team'));

		// Three types of pages:
		// a) /team - displays board
		// b) /team/ehemalige - displays former active members
		// c) /team/{:id} - displays specific user

		if ($id = $this->uriInt(2)) {
			// Type c, display user
			if ($user = $this->gateway->getUser($id)) {
				$this->pageCompositionHelper->addTitle($user['name']);
				$this->pageCompositionHelper->addBread($user['name']);
				$this->pageCompositionHelper->addContent($this->view->user($user));

				if ($user['contact_public']) {
					$this->pageCompositionHelper->addContent($this->view->contactForm($user));
				}

				return;
			}

			$this->func->go('/team');

			return;
		}

		if ($teamType = $this->uriStr(2)) {
			if ($teamType === 'ehemalige') {
				// Type b, display "Ehemalige"
				$this->pageCompositionHelper->addBread($this->func->s('Ehemalige'), '/team/ehemalige');
				$this->pageCompositionHelper->addTitle($this->func->s('Ehemalige'));
				$this->displayTeamContent(1564, 54);

				return;
			}

			$this->pageCompositionHelper->addContent('Page not found');

			return;
		}

		// Type a, display "Vorstand" and "Aktive"
		$this->pageCompositionHelper->addContent("<div id='vorstand'>");
		$this->displayTeamContent(1373, 39);
		$this->pageCompositionHelper->addContent("</div><div id='aktive'>");
		$this->displayTeamContent(1565, 53);
		$this->pageCompositionHelper->addContent('</div>');
	}

	private function displayTeamContent($bezirkId, $contentId): void
	{
		if ($team = $this->gateway->getTeam($bezirkId)) {
			$this->pageCompositionHelper->addContent($this->view->teamList($team, $this->contentGateway->get($contentId)));
		}
	}
}
