<?php

namespace Foodsharing\Modules\Profile;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;

class ProfileControl extends Control
{
	private $foodsaver;
	private $fs_id;

	public function __construct()
	{
		$this->model = new ProfileModel();
		$this->view = new ProfileView();

		parent::__construct();

		if (!S::may()) {
			$this->func->go('/');
		}

		if ($id = $this->uriInt(2)) {
			$this->model->setFsId((int)$id);
			$this->fs_id = (int)$id;
			if ($data = $this->model->getData()) {
				if (is_null($data['deleted_at']) || S::may('orga')) {
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
		$this->func->addBread($this->foodsaver['name'], '/profile/' . $this->foodsaver['id']);
		if (S::may('orga')) {
			$this->view->usernotes($this->wallposts('usernotes', $this->foodsaver['id']), $this->model->getCompanies($this->foodsaver['id']), $this->model->getCompaniesCount($this->foodsaver['id']), $this->model->getNextDates($this->foodsaver['id'], 50));
		} else {
			$this->func->go('/profile/' . $this->foodsaver['id']);
		}
	}

	public function profile()
	{
		$bids = $this->model->getFsBezirkIds($this->foodsaver['id']);

		if ($this->func->isOrgaTeam() || $this->func->isBotForA($bids, false, true)) {
			$this->view->profile($this->wallposts('foodsaver', $this->foodsaver['id']), $this->model->getCompanies($this->foodsaver['id']), $this->model->getCompaniesCount($this->foodsaver['id']), $this->model->getNextDates($this->foodsaver['id'], 50));
		} else {
			$this->view->profile($this->wallposts('foodsaver', $this->foodsaver['id']), null, null);
		}
	}
}
