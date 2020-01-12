<?php

namespace Foodsharing\Modules\Profile;

use Foodsharing\Modules\Basket\BasketGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\ReportPermissions;

final class ProfileControl extends Control
{
	private $foodsaver;
	private $regionGateway;
	private $profileGateway;
	private $basketGateway;
	private $reportPermissions;
	
	public function __construct(
		ProfileView $view,
		RegionGateway $regionGateway,
		ProfileGateway $profileGateway,
		BasketGateway $basketGateway,
		ReportPermissions $reportPermissions
	) {
		$this->view = $view;
		$this->profileGateway = $profileGateway;
		$this->regionGateway = $regionGateway;
		$this->basketGateway = $basketGateway;
		$this->reportPermissions = $reportPermissions;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->go('/');
		}

		if ($id = $this->uriInt(2)) {
			$this->profileGateway->setFsId((int)$id);
			$data = $this->profileGateway->getData($this->session->id(), $this->reportPermissions->mayHandleReports());
			if ($data && $data['deleted_at'] === null) {
				$this->foodsaver = $data;
				$this->foodsaver['buddy'] = $this->profileGateway->buddyStatus($this->foodsaver['id'], $this->session->id());
				$this->foodsaver['basketCount'] = $this->basketGateway->getAmountOfFoodBaskets(
						$this->foodsaver['id']
					);

				$this->view->setData($this->foodsaver);

				if ($this->uriStr(3) === 'notes') {
					$this->orgaTeamNotes();
				} else {
					$this->profile();
				}
			} else {
				$this->flashMessageHelper->error($this->translationHelper->s('fs_profile_does_not_exist_anymore'));
				$this->routeHelper->goPage('dashboard');
			}
		} else {
			$this->routeHelper->goPage('dashboard');
		}
	}

	private function orgaTeamNotes(): void
	{
		$this->pageHelper->addBread($this->foodsaver['name'], '/profile/' . $this->foodsaver['id']);
		if ($this->session->may('orga')) {
			$this->view->userNotes(
				$this->wallposts('usernotes', $this->foodsaver['id']),
				true,
				$this->profileGateway->listStoresOfFoodsaver($this->foodsaver['id']),
			);
		} else {
			$this->routeHelper->go('/profile/' . $this->foodsaver['id']);
		}
	}

	public function profile(): void
	{
		$regionIDs = $this->regionGateway->getFsRegionIds($this->foodsaver['id']);
		if ($this->session->isAmbassadorForRegion($regionIDs, false, true) || $this->session->isOrgaTeam()) {
			$this->view->profile(
				$this->wallposts('foodsaver', $this->foodsaver['id']),
				true,
				$this->profileGateway->listStoresOfFoodsaver($this->foodsaver['id']),
				$this->profileGateway->getNextDates($this->foodsaver['id'], 50)
			);
		} else {
			$this->view->profile(
				$this->wallposts('foodsaver', $this->foodsaver['id']),
				false,
				[],
				$this->foodsaver['id'] === $this->session->id() ? $this->profileGateway->getNextDates($this->foodsaver['id'], 50) : []
			);
		}
	}

	// this is required even if empty.
	public function index(): void
	{
	}
}
