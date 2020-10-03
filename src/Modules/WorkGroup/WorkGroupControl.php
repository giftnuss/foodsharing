<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Permissions\WorkGroupPermissions;
use Foodsharing\Utility\ImageHelper;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkGroupControl extends Control
{
	private WorkGroupGateway $workGroupGateway;
	private WorkGroupPermissions $workGroupPermissions;
	private ImageHelper $imageService;
	private FormFactoryInterface $formFactory;

	public function __construct(
		WorkGroupView $view,
		WorkGroupGateway $workGroupGateway,
		WorkGroupPermissions $workGroupPermissions,
		ImageHelper $imageService
	) {
		$this->view = $view;
		$this->workGroupGateway = $workGroupGateway;
		$this->workGroupPermissions = $workGroupPermissions;
		$this->imageService = $imageService;

		parent::__construct();
	}

	/**
	 * @required
	 */
	public function setFormFactory(FormFactoryInterface $formFactory): void
	{
		$this->formFactory = $formFactory;
	}

	public function index(Request $request, Response $response): void
	{
		if (!$this->session->may()) {
			$this->routeHelper->goLogin();
		}

		$this->pageHelper->addBread('Arbeitsgruppen', '/?page=groups');

		if (!$request->query->has('sub')) {
			$this->list($request, $response);
		} elseif ($request->query->get('sub') == 'edit') {
			$this->edit($request, $response);
		}
	}

	private function getSideMenuData(?string $activeUrlPartial = null): array
	{
		$countries = $this->workGroupGateway->getCountryGroups();
		$bezirke = $this->session->getRegions();

		$localRegions = array_filter($bezirke, function ($region) {
			return !in_array($region['type'], [Type::COUNTRY, Type::WORKING_GROUP]);
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
			return $group['type'] == Type::WORKING_GROUP;
		});
		$menuMyGroups = array_map(
			function ($group) {
				return [
					'name' => $group['name'],
					'href' => '/?page=bezirk&bid=' . $group['id'] . '&sub=forum'
				];
			}, $myGroups
		);

		return [
			'global' => $menuGlobal,
			'local' => $menuLocalRegions,
			'countries' => $menuCountries,
			'groups' => $menuMyGroups,
			'active' => $activeUrlPartial,
		];
	}

	private function list(Request $request, Response $response): void
	{
		$this->pageHelper->addTitle($this->translator->trans('terminology.groups'));

		$sessionId = $this->session->id();
		$parent = $request->query->getInt('p', RegionIDs::GLOBAL_WORKING_GROUPS);
		$myApplications = $this->workGroupGateway->getApplications($sessionId);
		$myStats = $this->workGroupGateway->getStats($sessionId);
		$groups = $this->getGroups($parent, $myApplications, $myStats);

		$response->setContent(
			$this->render(
				'pages/WorkGroup/list.twig',
				['nav' => $this->getSideMenuData('=' . $parent), 'groups' => $groups]
			)
		);
	}

	private function getGroups(int $parent, array $applications, array $stats): array
	{
		$insertLeaderImage = function (array $leader): array {
			return array_merge($leader, ['image' => $this->imageService->img($leader['photo'])]);
		};
		$enrichGroupData = function (array $group) use ($insertLeaderImage, $applications, $stats): array {
			$leaders = array_map($insertLeaderImage, $group['leaders']);
			$satisfied = $this->workGroupPermissions->fulfillApplicationRequirements($group, $stats);

			return array_merge($group, [
				'leaders' => $leaders,
				'image' => $group['photo'] ? 'images/' . $group['photo'] : null,
				'appliedFor' => in_array($group['id'], $applications),
				'applyMinBananaCount' => $group['banana_count'],
				'applyMinFetchCount' => $group['fetch_count'],
				'applyMinFoodsaverWeeks' => $group['week_num'],
				'applicationRequirementsNotFulfilled' => !$satisfied,
				'mayEdit' => $this->workGroupPermissions->mayEdit($group),
				'mayAccess' => $this->workGroupPermissions->mayAccess($group),
				'mayApply' => $this->workGroupPermissions->mayApply($group, $applications, $stats),
				'mayJoin' => $this->workGroupPermissions->mayJoin($group),
			]);
		};

		return array_map($enrichGroupData, $this->workGroupGateway->listGroups($parent));
	}

	private function edit(Request $request, Response $response): void
	{
		$groupId = $request->query->getInt('id');
		$group = $this->workGroupGateway->getGroup($groupId);
		if (!$group) {
			$this->routeHelper->go('/?page=groups');
		} elseif ($group['type'] != Type::WORKING_GROUP || !$this->workGroupPermissions->mayEdit($group)) {
			$this->routeHelper->go('/?page=dashboard');
		}

		$this->pageHelper->addBread($group['name'] . ' bearbeiten', '/?page=groups&sub=edit&id=' . (int)$group['id']);
		$editWorkGroupRequest = EditWorkGroupData::fromGroup($group);
		$form = $this->formFactory->create(WorkGroupForm::class, $editWorkGroupRequest);
		$form->handleRequest($request);
		if ($form->isSubmitted()) {
			if ($form->isValid()) {
				$data = $editWorkGroupRequest->toGroup();
				$this->workGroupGateway->updateGroup($group['id'], $data);
				$this->workGroupGateway->updateTeam($group['id'], $data['member'], $data['leader']);
				$this->flashMessageHelper->info('Änderungen gespeichert!');
				$this->routeHelper->goSelf();
			}
		}
		$response->setContent($this->render('pages/WorkGroup/edit.twig',
			['nav' => $this->getSideMenuData(), 'group' => $group, 'form' => $form->createView()]
		));
	}
}
