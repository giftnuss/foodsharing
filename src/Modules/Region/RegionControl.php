<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Event\EventGateway;
use Foodsharing\Modules\FairTeiler\FairTeilerGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Permissions\ForumPermissions;
use Foodsharing\Services\ForumService;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class RegionControl extends Control
{
	private $region;
	private $gateway;
	private $eventGateway;
	private $foodsaverGateway;
	private $forumGateway;
	private $fairteilerGateway;

	/* @var TranslatorInterface */
	private $translator;
	/* @var FormFactoryBuilder */
	private $formFactory;
	private $forumService;
	private $forumPermissions;
	private $forumModerated;
	private $regionHelper;

	/**
	 * @required
	 */
	public function setTranslator(TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}

	/**
	 * @required
	 */
	public function setFormFactory(FormFactoryBuilder $formFactory)
	{
		$this->formFactory = $formFactory;
	}

	public function __construct(
		EventGateway $eventGateway,
		FairTeilerGateway $fairteilerGateway,
		FoodsaverGateway $foodsaverGateway,
		ForumGateway $forumGateway,
		ForumPermissions $forumPermissions,
		ForumService $forumService,
		Db $model,
		RegionGateway $gateway,
		RegionHelper $regionHelper
	) {
		$this->model = $model;
		$this->gateway = $gateway;
		$this->eventGateway = $eventGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->forumPermissions = $forumPermissions;
		$this->forumGateway = $forumGateway;
		$this->fairteilerGateway = $fairteilerGateway;
		$this->forumService = $forumService;
		$this->regionHelper = $regionHelper;

		parent::__construct();
	}

	private function mayAccessApplications($regionId)
	{
		return $this->forumPermissions->mayAccessAmbassadorBoard($regionId);
	}

	private function regionViewData($region, $activeSubpage)
	{
		$isWorkGroup = $this->isWorkGroup($region);
		$menu = [
			['name' => 'terminology.forum', 'href' => '/?page=bezirk&bid=' . (int)$region['id'] . '&sub=forum'],
			['name' => 'terminology.events', 'href' => '/?page=bezirk&bid=' . (int)$region['id'] . '&sub=events'],
		];

		if ($this->forumPermissions->mayAccessAmbassadorBoard($region['id']) && !$isWorkGroup) {
			$menu[] = ['name' => 'terminology.ambassador_forum', 'href' => '/?page=bezirk&bid=' . (int)$region['id'] . '&sub=botforum'];
		}

		if ($isWorkGroup) {
			$menu[] = ['name' => 'terminology.wall', 'href' => '/?page=bezirk&bid=' . (int)$region['id'] . '&sub=wall'];
			if ($this->session->may('orga') || $this->session->isAdminFor($region['id'])) {
				$menu[] = ['name' => 'Gruppe verwalten', 'href' => '/?page=groups&sub=edit&id=' . (int)$region['id']];
			}
		} else {
			$menu[] = ['name' => 'terminology.fsp', 'href' => '/?page=bezirk&bid=' . (int)$region['id'] . '&sub=fairteiler'];
			$menu[] = ['name' => 'terminology.groups', 'href' => '/?page=groups&p=' . (int)$region['id']];
		}
		if ($this->mayAccessApplications($region['id'])) {
			if ($requests = $this->gateway->listRequests($region['id'])) {
				$menu[] = ['name' => $this->translator->trans('group.applications') . ' (' . count($requests) . ')', 'href' => '/?page=bezirk&bid=' . $region['id'] . '&sub=applications'];
			}
		}

		$avatarListEntry = function ($fs) {
			return [
				'user' => ['id' => $fs['id'],
					'name' => $fs['name'],
					'sleep_status' => $fs['sleep_status']
				],
				'size' => 50,
				'imageUrl' => $this->func->img($fs['photo'], 50, 'q')
			];
		};
		$viewdata['isRegion'] = !$isWorkGroup;
		$stat = [
			'num_fs' => count($this->region['foodsaver']),
			'num_sleeping' => count($this->region['sleeper']),
			'num_ambassadors' => $this->region['stat_botcount'],
			'num_stores' => $this->region['stat_betriebcount'],
			'num_cooperations' => $this->region['stat_korpcount'],
			'num_pickups' => $this->region['stat_fetchcount'],
			'pickup_weight_kg' => round($this->region['stat_fetchweight']),
		];
		$viewdata['region'] = [
			'id' => $this->region['id'],
			'name' => $this->region['name'],
			'isWorkGroup' => $isWorkGroup,
			'stat' => $stat,
			'admins' => array_map($avatarListEntry, array_slice($this->region['botschafter'], 0, 30)),
		];

		$viewdata['nav'] = ['menu' => $menu, 'active' => '=' . $activeSubpage];

		return $viewdata;
	}

	private function isWorkGroup($region)
	{
		return $region['type'] == Type::WORKING_GROUP;
	}

	public function index(Request $request, Response $response)
	{
		if (!$this->session->may()) {
			$this->func->goLogin();
		}

		$region_id = $request->query->getInt('bid', $_SESSION['client']['bezirk_id']);

		if ($this->session->mayBezirk($region_id) && ($region = $this->gateway->getRegionDetails($region_id))) {
			$big = [Type::BIG_CITY, Type::FEDERAL_STATE, Type::COUNTRY];
			$region['moderated'] = $region['moderated'] || in_array($region['type'], $big);
			$this->region = $region;
		} else {
			$this->func->go('/?page=dashboard');
		}

		$this->func->addTitle($region['name']);
		$this->func->addBread($region['name'], '/?page=bezirk&bid=' . $region_id);

		switch ($request->query->get('sub')) {
			case 'botforum':
				if (!$this->forumPermissions->mayAccessAmbassadorBoard($region_id)) {
					$this->func->go($this->forumService->url($region_id, false));
				}
				$this->forum($request, $response, $region, true);
				break;
			case 'forum':
				$this->forum($request, $response, $region, false);
				break;
			case 'wall':
				$this->wall($request, $response, $region);
				break;
			case 'fairteiler':
				$this->fairteiler($request, $response, $region);
				break;
			case 'events':
				$this->events($request, $response, $region);
				break;
			case 'applications':
				$this->applications($request, $response, $region);
				break;
			default:
				if ($this->isWorkGroup($region)) {
					$this->func->go('/?page=bezirk&bid=' . $region_id . '&sub=wall');
				} else {
					$this->func->go($this->forumService->url($region_id, false));
				}
				break;
		}
	}

	private function wall(Request $request, Response $response, $region)
	{
		$viewdata = $this->regionViewData($region, $request->query->get('sub'));
		$viewdata['wall'] = ['module' => 'bezirk', 'wallId' => $region['id']];
		$response->setContent($this->render('pages/Region/wall.twig', $viewdata));
	}

	private function fairteiler(Request $request, Response $response, $region)
	{
		$this->func->addBread($this->func->s('fairteiler'), '/?page=bezirk&bid=' . $region['id'] . '&sub=fairteiler');
		$this->func->addTitle($this->func->s('fairteiler'));
		$viewdata = $this->regionViewData($region, $request->query->get('sub'));
		$bezirk_ids = $this->gateway->listIdsForDescendantsAndSelf($region['id']);
		$viewdata['fairteiler'] = $this->fairteilerGateway->listFairteiler($bezirk_ids);
		$response->setContent($this->render('pages/Region/fairteiler.twig', $viewdata));
	}

	private function handleNewThreadForm(Request $request, $region, $ambassadorForum)
	{
		$this->func->addBread($this->translator->trans('forum.new_thread'));
		$data = CreateForumThreadData::create();
		$form = $this->formFactory->getFormFactory()->create(ForumCreateThreadForm::class, $data);
		$form->handleRequest($request);
		if ($form->isSubmitted()) {
			if ($form->isValid() && $this->forumPermissions->mayPostToRegion($region['id'], $ambassadorForum)) {
				$moderated = !$this->session->user('verified') || $this->region['moderated'];
				$threadId = $this->forumService->createThread($this->session->id(), $data->title, $data->body, $region, $ambassadorForum, $moderated);
				$this->forumGateway->followThread($this->session->id(), $threadId);
				if ($moderated) {
					$this->func->info($this->translator->trans('forum.hold_back_for_moderation'));
				}
				$this->func->go($this->forumService->url($region['id'], $ambassadorForum));
			}
		}

		return $form->createView();
	}

	private function forum(Request $request, Response $response, $region, $ambassadorForum)
	{
		$sub = $request->query->get('sub');
		$trans = $this->translator->trans(($ambassadorForum) ? 'terminology.ambassador_forum' : 'terminology.forum');
		$viewdata = $this->regionViewData($region, $sub);
		$this->func->addBread($trans, $this->forumService->url($region['id'], $ambassadorForum));
		$this->func->addTitle($trans);
		$viewdata['sub'] = $sub;

		if ($tid = $request->query->getInt('tid')) {
			/* this index triggers the rendering of the vue forum component */
			$viewdata['thread'] = ['id' => $tid];
			$viewdata['posts'] = [];
		} elseif ($request->query->has('newthread')) {
			$viewdata['newThreadForm'] = $this->handleNewThreadForm($request, $region, $ambassadorForum);
		} else {
			$viewdata['threads'] = $this->regionHelper->transformThreadViewData($this->forumGateway->listThreads($region['id'], $ambassadorForum), $region['id'], $ambassadorForum);
		}

		$response->setContent($this->render('pages/Region/forum.twig', $viewdata));
	}

	private function events(Request $request, Response $response, $region)
	{
		$this->func->addBread($this->translator->trans('events.name'), '/?page=bezirk&bid=' . $region['id'] . '&sub=events');
		$this->func->addTitle($this->translator->trans('events.name'));
		$sub = $request->query->get('sub');
		$viewdata = $this->regionViewData($region, $sub);

		$viewdata['events'] = $this->eventGateway->listForRegion($region['id']);

		$response->setContent($this->render('pages/Region/events.twig', $viewdata));
	}

	private function applications(Request $request, Response $response, $region)
	{
		$this->func->addBread($this->translator->trans('group.applications'), '/?page=bezirk&bid=' . $region['id'] . '&sub=events');
		$this->func->addTitle($this->translator->trans('group.applications_for', ['%name%' => $region['name']]));
		$sub = $request->query->get('sub');
		$viewdata = $this->regionViewData($region, $sub);
		if ($this->mayAccessApplications($region['id'])) {
			$viewdata['applications'] = $this->gateway->listRequests($region['id']);
		}
		$response->setContent($this->render('pages/Region/applications.twig', $viewdata));
	}
}
