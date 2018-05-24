<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkGroupControl extends Control
{
	/**
	 * @var FormFactoryBuilder
	 */
	private $formFactory;

	public function __construct(WorkGroupModel $model, WorkGroupView $view)
	{
		$this->model = $model;
		$this->view = $view;

		parent::__construct();
	}

	/**
	 * @required
	 */
	public function setFormFactory(FormFactoryBuilder $formFactory)
	{
		$this->formFactory = $formFactory;
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

	private function fulfillApplicationRequirements($group, $stats)
	{
		return
			$stats['bananacount'] >= $group['banana_count']
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

	private function getSideMenuData()
	{
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

		return ['global' => $menuGlobal,
			'local' => $menuLocalRegions,
			'countries' => $menuCountries,
			'groups' => $menuMyGroups];
	}

	private function list(Request $request, Response $response)
	{
		$parent = $request->query->getInt('p', 392);
		$myApplications = $this->model->getApplications(S::id());
		$myStats = $this->model->getStats(S::id());
		$groups = $this->model->listGroups($parent);

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

		$response->setContent($this->render('pages/WorkGroup/list.twig',
			['nav' => $this->getSideMenuData(), 'groups' => $groups]
		));
	}

	private function edit(Request $request, Response $response)
	{
		$groupId = $request->query->getInt('id');

		$bids = $this->model->getFsBezirkIds($this->func->fsId());
		if (!$this->func->isOrgaTeam() && !$this->func->isBotForA($bids, true, true)) {
			$this->func->go('/?page=dashboard');
		}

		if ($group = $this->model->getGroup($groupId)) {
			if ($group['type'] != 7) {
				$this->func->go('/?page=dashboard');
			}
			$this->func->addBread($group['name'] . ' bearbeiten', '/?page=groups&sub=edit&id=' . (int)$group['id']);
			$editWorkGroupRequest = EditWorkGroupData::fromGroup($group);
			$form = $this->formFactory->getFormFactory()->create(WorkGroupForm::class, $editWorkGroupRequest);
			$form->handleRequest($request);
			if ($form->isSubmitted()) {
				if ($form->isValid()) {
					$data = $editWorkGroupRequest->toGroup();
					$this->model->updateGroup($group['id'], $data);
					$this->model->updateTeam($group['id'], $data['member'], $data['leader']);
					$this->func->info('Änderungen gespeichert!');
					$this->func->goSelf();
				}
			}
		}

		$response->setContent($this->render('pages/WorkGroup/edit.twig',
			['nav' => $this->getSideMenuData(), 'group' => $group, 'form' => $form->createView()]
		));
	}
}
