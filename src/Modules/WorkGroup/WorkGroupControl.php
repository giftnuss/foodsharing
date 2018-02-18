<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;
use Symfony\Component\HttpFoundation\Request;

class WorkGroupControl extends Control
{
	public function __construct(WorkGroupModel $model, WorkGroupView $view)
	{
		$this->model = $model;
		$this->view = $view;

		parent::__construct();
	}

	public function index(Request $request)
	{
		if (!S::may()) {
			$this->func->goLogin();
		}

		$this->func->addBread('Arbeitsgruppen', '/?page=groups');

		if (!$request->query->has('sub')) {
			$this->list($request);
		} elseif ($request->query->get('sub') == 'edit') {
			$this->edit($request);
		}
	}

	private function list(Request $request)
	{
		$parent = $request->query->getInt('p', 392);

		$groups = $this->model->listGroups($parent);

		$this->func->addTitle($this->func->s('groups'));
		$this->func->addContent($this->view->topbar('foodsharing Arbeitsgruppen', 'hier findest Du Hilfe und viel zu tun...', '<img src="/img/groups.png" />'), CNT_TOP);
		$this->addNav();
		if ($groups) {
			$my_applications = $this->model->getApplications(S::id());
			$my_stats = $this->model->getStats(S::id());
			$this->func->addContent($this->view->listGroups($groups, $my_applications, $my_stats));
		} else {
			$this->func->addContent($this->v_utils->v_info('Hier gibt es noch keine Arbeitsgruppen'));
		}
	}

	private function edit(Request $request)
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
				$data = $this->prepareEditInput($request);
				if ($this->handleEdit($group, $data)) {
					$this->func->info('Änderungen gespeichert!');
					$this->func->go('/?page=groups&sub=edit&id=' . (int)$group['id']);
				}
			}
			$this->addNav();
			$this->func->addBread($group['name'] . ' bearbeiten', '/?page=groups&sub=edit&id=' . (int)$group['id']);
			$this->func->addContent($this->view->editGroup($group));
		}
	}

	private function addNav()
	{
		$countrys = $this->model->getCountryGroups();
		$bezirke = $this->model->getBezirke();

		$this->func->addContent($this->view->leftNavi($countrys, $bezirke), CNT_LEFT);
	}

	private function prepareEditInput(Request $request)
	{
		$fields = [
			'name' => ['filter' => 'stripTagsAndTrim'],
			'teaser' => ['filter' => 'stripTagsAndTrim'],
			'photo' => ['filter' => 'stripTagsAndTrim', 'required' => false],
			'apply_type' => ['method' => 'getInt'],
			'banana_count' => ['method' => 'getInt'],
			'fetch_count' => ['method' => 'getInt'],
			'week_num' => ['method' => 'getInt'],
			'report_num' => ['filter' => 'isNonEmptyArray', 'required' => false, 'default' => false],
			'members' => ['filter' => 'tagSelectIds', 'required' => false, 'default' => [], 'parameterName' => 'member'],
			'leader' => ['filter' => 'tagSelectIds', 'required' => false, 'default' => []]
		];

		$data = $this->sanitizeRequest($request, $fields);

		if ($data['apply_type'] != 1) {
			$data['banana_count'] = 0;
			$data['fetch_count'] = 0;
			$data['week_num'] = 0;
			$data['report_num'] = 0;
		}

		return $data;
	}

	private function handleEdit($group, $data)
	{
		if ($this->model->updateGroup($group['id'], $data)) {
			$this->model->updateTeam($group['id'], $data['members'], $data['leader']);

			return true;
		}

		return false;
	}
}
