<?php

namespace Foodsharing\Modules\Profile;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;

class ProfileControl extends Control
{
	private $foodsaver;
	private $fs_id;
	private $regionGateway;

	public function __construct(ProfileModel $model, ProfileView $view, RegionGateway $regionGateway)
	{
		$this->model = $model;
		$this->view = $view;
		$this->regionGateway = $regionGateway;

		parent::__construct();

		if (!$this->session->may()) {
			$this->func->go('/');
		}

		if ($id = $this->uriInt(2)) {
			$this->model->setFsId((int)$id);
			$this->fs_id = (int)$id;
			if ($data = $this->model->getData()) {
				if (is_null($data['deleted_at']) || $this->session->may('orga')) {
					$this->foodsaver = $data;
					$this->foodsaver['buddy'] = $this->model->buddyStatus($this->foodsaver['id']);

					$this->view->setData($this->foodsaver);

					if ($this->uriStr(3) == 'notes') {
						$this->organotes();
					} else {
						$this->profile();
					}
				} else {
					$this->func->goPage('dashboard');
				}
			} else {
				$this->func->goPage('dashboard');
			}
		} else {
			$this->func->goPage('dashboard');
		}
	}

	public function index()
	{
	}

	private function organotes()
	{
		$this->pageCompositionHelper->addBread($this->foodsaver['name'], '/profile/' . $this->foodsaver['id']);
		if ($this->session->may('orga')) {
			$this->view->usernotes($this->wallposts('usernotes', $this->foodsaver['id']), true, true, true, $this->model->getCompanies($this->foodsaver['id']), $this->model->getCompaniesCount($this->foodsaver['id']), $this->model->getNextDates($this->foodsaver['id'], 50));
		} else {
			$this->func->go('/profile/' . $this->foodsaver['id']);
		}
	}

	public function profile(): void
	{
		$bids = $this->regionGateway->getFsRegionIds($this->foodsaver['id']);
		if ($this->session->isOrgaTeam() || $this->session->isBotForA($bids, false, true)) {
			$this->view->profile($this->wallposts('foodsaver', $this->foodsaver['id']), true, true, true, true, $this->model->getCompanies($this->foodsaver['id']), $this->model->getCompaniesCount($this->foodsaver['id']), $this->model->getNextDates($this->foodsaver['id'], 50));
		} else {
			$this->view->profile($this->wallposts('foodsaver', $this->foodsaver['id']), false, false, false, false, null, null);
		}
	}
}
