<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Lib\Session\S;
use Foodsharing\Lib\Twig;
use Foodsharing\Modules\Core\Control;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkGroupControl extends Control
{
	public function __construct(WorkGroupModel $model, WorkGroupView $view)
	{
		$this->model = $model;
		$this->view = $view;

		parent::__construct();
	}

	public function index(Request $request, Response $response)
	{
		if (!S::may()) {
			$this->func->goLogin();
		}

		$this->func->addBread('Arbeitsgruppen', '/?page=groups');

		if (!$request->query->has('sub')) {
			$this->list($request, $response);
		} elseif ($request->query->get('sub') == 'edit') {
			$this->edit($request, $response);
		}
	}

	public function canApply($group, $mystats)
	{
		if ($group['apply_type'] == 0) {
			return false;
		}

		// apply_type

		if ($group['apply_type'] == 1) {
			if (
				$mystats['bananacount'] >= $group['banana_count'] &&
				$mystats['fetchcount'] >= $group['fetch_count'] &&
				$mystats['weeks'] >= $group['week_num']
			) {
				if ((int)$group['report_num'] == 0 && (int)$mystats['reports'] > 0) {
					return false;
				}

				return true;
			}
		} elseif ($group['apply_type'] == 2) {
			return true;
		}

		return false;
	}

	private function fulfillApplicationRequirements($group, $stats)
	{
		return
			($stats['reports'] == 0 || $group['report_num'] != 0)
			&& $stats['bananacount'] >= $group['banana_count']
			&& $stats['fetchcount'] >= $group['fetch_count']
			&& $stats['weeks'] >= $group['week_num'];
	}

	private function mayEdit($group)
	{
		/* this actually only implements access for bots for _direct parents_, not all hierarchical parents */
		return $this->func->isOrgaTeam() || $this->func->isBotFor($group['id']) || $this->func->isBotFor($group['parent_id']);
	}

	private function mayAccess($group)
	{
		return $this->func->mayBezirk($group['id']) || $this->func->isBotFor($group['parent_id']);
	}

	private function mayApply($group, $applications, $stats)
	{
		return
			!$this->func->mayBezirk($group['id'])
			&& !in_array($group['id'], $applications)
			&& ($group['apply_type'] == 2
			  || ($group['apply_type'] == 1 && $this->fulfillApplicationRequirements($group, $stats)));
	}

	private function mayJoin($group)
	{
		return
			!$this->func->mayBezirk($group['id'])
			&& $group['apply_type'] == 3;
	}

	private function list(Request $request, Response $response)
	{
		$parent = $request->query->getInt('p', 392);
		$myApplications = $this->model->getApplications(S::id());
		$myStats = $this->model->getStats(S::id());
		$groups = $this->model->listGroups($parent);
		$countries = $this->model->getCountryGroups();
		$bezirke = $this->model->getBezirke();

		$localRegions = array_filter($bezirke, function ($region) {
			return !in_array($region['type'], [6, 7]);
		});

		$regionToMenuItem = function ($region) {
			return [
				'name' => $region['name'],
				'href' => '/?page=groups&p=' . $region['id']
			];
		};

		$menuGlobal = [['name' => 'Alle anzeigen', 'href' => '/?page=groups']];
		$menuLocalRegions = array_map($regionToMenuItem, $localRegions);
		$menuCountries = array_map($regionToMenuItem, $countries);

		$myGroups = array_filter(isset($_SESSION['client']['bezirke']) ? $_SESSION['client']['bezirke'] : [], function ($group) {
			return $group['type'] == 7;
		});
		$menuMyGroups = array_map(
			function ($group) {
				return [
					'name' => $group['name'],
					'href' => '/?page=bezirk&bid=' . $group['id'] . '&sub=forum'
				];
			}, $myGroups
		);

		$groups = array_map(
			function ($group) use ($myApplications, $myStats) {
				return array_merge($group, [
					'leaders' => array_map(function ($leader) {return array_merge($leader, ['image' => $this->func->img($leader['photo'])]); }, $group['leaders']),
					'image' => $group['photo'] ? 'images/' . $group['photo'] : null,
					'appliedFor' => in_array($group['id'], $myApplications),
					'applyMinBananaCount' => $group['banana_count'],
					'applyMinFetchCount' => $group['fetch_count'],
					'applyMinFoodsaverWeeks' => $group['week_num'],
					'applicationRequirementsNotFulfilled' => ($group['apply_type'] == 1) && !$this->fulfillApplicationRequirements($group, $myStats),
					'mayEdit' => $this->mayEdit($group),
					'mayAccess' => $this->mayAccess($group),
					'mayApply' => $this->mayApply($group, $myApplications, $myStats),
					'mayJoin' => $this->mayJoin($group)
				]);
			}, $groups);

		$this->func->addTitle($this->func->s('groups'));
		/*
		$this->func->addContent($this->view->topbar('foodsharing Arbeitsgruppen', 'hier findest Du Hilfe und viel zu tun...', '<img src="/img/groups.png" />'), CNT_TOP);

		$this->addNav();
		if ($groups) {
			$my_applications = $this->model->getApplications(S::id());
			$my_stats = $this->model->getStats(S::id());
			$this->func->addContent($this->view->listGroups($groups, $my_applications, $my_stats));
		} else {
			$this->func->addContent($this->v_utils->v_info('Hier gibt es noch keine Arbeitsgruppen'));
		}*/
		$response->setContent($this->render('pages/WorkGroup/list.twig',
			['pagemenu' => ['global' => $menuGlobal,
					'local' => $menuLocalRegions,
					'countries' => $menuCountries,
					'groups' => $menuMyGroups],
				'groups' => $groups]
		));
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
