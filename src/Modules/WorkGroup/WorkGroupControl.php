<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;
use Symfony\Component\HttpFoundation\Request;

class WorkGroupControl extends Control
{
	private $ag_id;
	private $my_applications;
	private $my_stats;

	public function __construct(WorkGroupModel $model, WorkGroupView $view)
	{
		$this->model = $model;
		$this->view = $view;

		parent::__construct();

		if (!S::may()) {
			$this->func->goLogin();
		}

		$this->setAgId(392);

		$this->func->addBread('Arbeitsgruppen', '/?page=groups');

		if (isset($_GET['p']) && (int)$_GET['p'] > 0) {
			$this->setAgId((int)$_GET['p']);
		}

		$this->my_applications = $this->model->getMyApplications();
		$this->my_stats = $this->model->getMyStats();
	}

	public function index()
	{
		$countrys = $this->model->getCountryGroups();
		$bezirke = $this->model->getBezirke();

		$this->func->addContent($this->view->leftNavi($countrys, $bezirke), CNT_LEFT);

		if (!isset($_GET['sub'])) {
			$this->func->addTitle($this->func->s('groups'));
			$this->func->addContent($this->view->topbar('foodsharing Arbeitsgruppen', 'hier findest Du Hilfe und viel zu tun...', '<img src="/img/groups.png" />'), CNT_TOP);
			if ($groups = $this->model->listGroups()) {
				$this->func->addContent($this->view->listGroups($groups, $this->my_applications, $this->my_stats));
			} else {
				$this->func->addContent($this->v_utils->v_info('Hier gibt es noch keine Arbeitsgruppen'));
			}
		}
	}

	public function edit(Request $request)
	{
		$bids = $this->model->getFsBezirkIds($this->func->fsId());

		if (!$this->func->isOrgaTeam() && !$this->func->isBotForA($bids, true, true)) {
			$this->func->go('/?page=dashboard');
		}

		if ($group = $this->model->getGroup($_GET['id'])) {
			if ($group['type'] != 7) {
				$this->func->go('/?page=dashboard');
			}
			if ($this->isSubmitted()) {
				$data = array();
				if ($name = ($this->getPostString('name'))) {
					$data['name'] = $name;
				}

				if ($teaser = ($this->getPostString('teaser'))) {
					$data['teaser'] = $teaser;
				}

				if ($desc = ($this->getPostHtml('desc'))) {
					$data['desc'] = $desc;
				}

				if ($img = ($this->getPostString('photo'))) {
					$data['photo'] = $img;
				}

				$data['apply_type'] = 1;
				$data['banana_count'] = 0;
				$data['fetch_count'] = 0;
				$data['week_num'] = 0;
				$data['report_num'] = 0;

				if ($_POST['apply_type'] == 1) {
					$data['banana_count'] = (int)$_POST['banana_count'];
					$data['fetch_count'] = (int)$_POST['fetch_count'];
					$data['week_num'] = (int)$_POST['week_num'];

					if (isset($_POST['report_num']) && is_array($_POST['report_num']) && count($_POST['report_num']) > 0) {
						$data['report_num'] = 1;
					}
				} else {
					$data['apply_type'] = (int)$_POST['apply_type'];
				}

				/*
				 * Handle Member and Group-Admin Fields
				 */
				$members = $this->func->getTagselectIds($request->request->get('member'));
				$leader = $this->func->getTagselectIds($request->request->get('leader'));

				$data = array_merge($group, $data);

				if ($this->model->updateGroup($group['id'], $data)) {
					$this->model->updateTeam($group['id'], $members, $leader);
					$this->func->info('Änderungen gespeichert!');
					$this->func->go('/?page=groups&sub=edit&id=' . (int)$group['id']);
				}
			}

			$this->func->addBread($group['name'] . ' bearbeiten', '/?page=groups&sub=edit&id=' . (int)$group['id']);
			$this->func->addContent($this->view->editGroup($group));
		}
	}

	private function setAgId($id)
	{
		$this->ag_id = $id;
		$this->model->setAgId($id);
		$this->view->setAgId($id);
	}
}
