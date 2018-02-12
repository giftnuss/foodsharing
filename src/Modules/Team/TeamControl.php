<?php

namespace Foodsharing\Modules\Team;

use Foodsharing\Modules\Content\ContentModel;
use Foodsharing\Modules\Core\Control;

class TeamControl extends Control
{
	public function __construct()
	{
		$this->model = new TeamModel();
		$this->view = new TeamView();

		parent::__construct();

		$this->func->addScript('/js/jquery.qrcode.min.js');
	}

	public function index()
	{
		$this->func->addBread($this->func->s('team'), '/team');
		$this->func->addTitle($this->func->s('team'));

		// Three types of pages:
		// a) /team - displays vorstand
		// b) /team/ehemalige - displays Ehemalige
		// c) /team/{:id} - displays specific user

		if ($id = $this->uriInt(2)) {
			// Type c, display user
			if ($user = $this->model->getUser($id)) {
				$this->func->addTitle($user['name']);
				$this->func->addBread($user['name']);
				$this->func->addContent($this->view->user($user));

				if ($user['contact_public']) {
					$this->func->addContent($this->view->contactForm($user));
				}
			} else {
				$this->func->go('/team');
			}
		} else {
			if ($teamType = $this->uriStr(2)) {
				if ($teamType == 'ehemalige') {
					// Type b, display "Ehemalige"
					$this->func->addBread($this->func->s('Ehemalige'), '/team/ehemalige');
					$this->func->addTitle($this->func->s('Ehemalige'));
					$this->displayTeamContent(1564, 54);
				} else {
					$this->func->addContent('Page not found');
				}
			} else {
				// Type a, display "Vorstand" and "Aktive"
				$this->func->addContent("<div id='vorstand'>");
				$this->displayTeamContent(1373, 39);
				$this->func->addContent("</div><div id='aktive'>");
				$this->displayTeamContent(1565, 53);
				$this->func->addContent('</div>');
			}
		}
	}

	private function displayTeamContent($bezirkId, $contentId)
	{
		if ($team = $this->model->getTeam($bezirkId)) {
			$db = new ContentModel();
			$this->func->addContent($this->view->teamlist($team, $db->getContent($contentId)));
		}
	}
}
