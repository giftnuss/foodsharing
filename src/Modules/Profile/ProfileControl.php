<?php

namespace Foodsharing\Modules\Profile;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;

final class ProfileControl extends Control
{
	private $foodsaver;
	private $fs_id;
	private $regionGateway;
	private $profileGateway;

	public function __construct(
		ProfileView $view,
		RegionGateway $regionGateway,
		ProfileGateway $profileGateway
	) {
		$this->view = $view;
		$this->profileGateway = $profileGateway;
		$this->regionGateway = $regionGateway;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->go('/');
		}

		if ($id = $this->uriInt(2)) {
			$this->profileGateway->setFsId((int)$id);
			$this->fs_id = (int)$id;
			if ($data = $this->profileGateway->getData($this->session->id())) {
				if (is_null($data['deleted_at']) || $this->session->may('orga')) {
					$this->foodsaver = $data;
					$this->foodsaver['buddy'] = $this->profileGateway->buddyStatus($this->foodsaver['id']);

					$this->view->setData($this->foodsaver);

					if ($this->uriStr(3) == 'notes') {
						$this->organotes();
					} else {
						$this->profile();
					}
				} else {
					$this->routeHelper->goPage('dashboard');
				}
			} else {
				$this->routeHelper->goPage('dashboard');
			}
		} else {
			$this->routeHelper->goPage('dashboard');
		}
	}

	// this is required even if empty.
	public function index()
	{
	}

	private function organotes()
	{
		$this->pageHelper->addBread($this->foodsaver['name'], '/profile/' . $this->foodsaver['id']);
		if ($this->session->may('orga')) {
			$this->view->usernotes(
				$this->wallposts('usernotes', $this->foodsaver['id']),
				true,
				true,
				true,
				$this->profileGateway->getCompanies($this->foodsaver['id']),
				$this->profileGateway->getCompaniesCount($this->foodsaver['id']),
				$this->profileGateway->getNextDates($this->foodsaver['id'], 50)
			);
		} else {
			$this->routeHelper->go('/profile/' . $this->foodsaver['id']);
		}
	}

	public function profile(): void
	{
		$bids = $this->regionGateway->getFsRegionIds($this->foodsaver['id']);
		if ($this->session->isOrgaTeam() || $this->session->isBotForA($bids, false, true)) {
			$this->view->profile(
				$this->wallposts('foodsaver', $this->foodsaver['id']),
				true,
				true,
				true,
				true,
				$this->profileGateway->getCompanies($this->foodsaver['id']),
				$this->profileGateway->getCompaniesCount($this->foodsaver['id']),
				$this->profileGateway->getNextDates($this->foodsaver['id'], 50)
			);
		} else {
			$this->view->profile(
				$this->wallposts('foodsaver', $this->foodsaver['id']),
				false,
				false,
				false,
				false,
				null,
				null
			);
		}
	}
}
