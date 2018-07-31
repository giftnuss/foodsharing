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
		$this->func->addBread($this->func->s('team'), '/team');
		$this->func->addTitle($this->func->s('team'));

		// Three types of pages:
		// a) /team - displays board
		// b) /team/ehemalige - displays former active members
		// c) /team/{:id} - displays specific user

		if ($id = $this->uriInt(2)) {
			// Type c, display user
			if ($user = $this->gateway->getUser($id)) {
				$this->func->addTitle($user['name']);
				$this->func->addBread($user['name']);
				$this->func->addContent($this->view->user($user));

				if ($user['contact_public']) {
					$this->func->addContent($this->view->contactForm($user));
				}

				return;
			}

			$this->func->go('/team');

			return;
		}

		if ($teamType = $this->uriStr(2)) {
			if ($teamType === 'ehemalige') {
				// Type b, display "Ehemalige"
				$this->func->addBread($this->func->s('Ehemalige'), '/team/ehemalige');
				$this->func->addTitle($this->func->s('Ehemalige'));
				$this->displayTeamContent(1564, 54);

				return;
			}

			$this->func->addContent('Page not found');

			return;
		}

		// Type a, display "Vorstand" and "Aktive"
		$this->func->addContent("<div id='vorstand'>");
		$this->displayTeamContent(1373, 39);
		$this->func->addContent("</div><div id='aktive'>");
		$this->displayTeamContent(1565, 53);
		$this->func->addContent('</div>');
	}

	private function displayTeamContent($bezirkId, $contentId): void
	{
		if ($team = $this->gateway->getTeam($bezirkId)) {
			$this->func->addContent($this->view->teamList($team, $this->contentGateway->get($contentId)));
		}
	}
}
