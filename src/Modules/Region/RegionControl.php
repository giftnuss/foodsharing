<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Event\EventGateway;
use Foodsharing\Modules\FairTeiler\FairTeilerGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Permissions\ForumPermissions;
use Foodsharing\Permissions\ReportPermissions;
use Foodsharing\Permissions\RegionPermissions;
use Foodsharing\Services\ForumService;
use Foodsharing\Services\ImageService;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

final class RegionControl extends Control
{
	private $region;
	private $gateway;
	private $eventGateway;
	private $forumGateway;
	private $fairteilerGateway;
	private $foodsaverGateway;
	private $forumFollowerGateway;
	/* @var TranslatorInterface */
	private $translator;
	/* @var FormFactoryBuilder */
	private $formFactory;
	private $forumService;
	private $forumPermissions;
	private $regionPermissions;
	private $regionHelper;
	private $imageService;
	private $reportPermissions;

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
		ForumFollowerGateway $forumFollowerGateway,
		ForumPermissions $forumPermissions,
		RegionPermissions $regionPermissions,
		ForumService $forumService,
		RegionGateway $gateway,
		RegionHelper $regionHelper,
		ReportPermissions $reportPermissions,
		ImageService $imageService
	) {
		$this->gateway = $gateway;
		$this->eventGateway = $eventGateway;
		$this->forumPermissions = $forumPermissions;
		$this->regionPermissions = $regionPermissions;
		$this->forumGateway = $forumGateway;
		$this->fairteilerGateway = $fairteilerGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->forumFollowerGateway = $forumFollowerGateway;
		$this->forumService = $forumService;
		$this->regionHelper = $regionHelper;
		$this->reportPermissions = $reportPermissions;
		$this->imageService = $imageService;

		parent::__construct();
	}

	private function mayAccessApplications($regionId)
	{
		return $this->forumPermissions->mayAccessAmbassadorBoard($regionId);
	}

	private function regionViewData($region, $activeSubpage)
	{
		$isWorkGroup = $this->isWorkGroup($region);
		$regionId = (int)$region['id'];
		$menu = [
			['name' => 'terminology.forum', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=forum'],
			['name' => 'terminology.events', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=events'],
			['name' => 'group.members', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=members'],
		];

		if (!$isWorkGroup && $this->forumPermissions->mayAccessAmbassadorBoard($regionId)) {
			$menu[] = ['name' => 'terminology.ambassador_forum', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=botforum'];
		}

		if ($isWorkGroup) {
			$menu[] = ['name' => 'terminology.wall', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=wall'];
			if ($region['has_children'] === 1) {
				$menu[] = ['name' => 'terminology.subgroup', 'href' => '/?page=groups&p=' . $regionId];
			}
			if ($this->session->isAdminFor($regionId) || $this->session->may('orga')) {
				$menu[] = ['name' => 'Gruppe verwalten', 'href' => '/?page=groups&sub=edit&id=' . $regionId];
			}
		} else {
			$menu[] = ['name' => 'terminology.fsp', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=fairteiler'];
			$menu[] = ['name' => 'terminology.groups', 'href' => '/?page=groups&p=' . $regionId];
			$menu[] = ['name' => 'terminology.statistic', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=statistic'];
			if ($this->reportPermissions->mayAccessReportsForRegion($regionId)) {
				$menu[] = ['name' => 'terminology.reports', 'href' => '/?page=report&bid=' . $regionId];
			}
		}
		if ($this->mayAccessApplications($regionId)) {
			if ($requests = $this->gateway->listRequests($regionId)) {
				$menu[] = ['name' => $this->translator->trans('group.applications') . ' (' . count($requests) . ')', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=applications'];
			}
		}

		$avatarListEntry = function ($fs) {
			return [
				'user' => [
					'id' => $fs['id'],
					'name' => $fs['name'],
					'sleep_status' => $fs['sleep_status']
				],
				'size' => 50,
				'imageUrl' => $this->imageService->img($fs['photo'], 50, 'q')
			];
		};
		$viewdata['isRegion'] = !$isWorkGroup;
		$stat = [
			'num_fs' => $this->region['fs_count'],
			'num_sleeping' => $this->region['sleeper_count'],
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
			$this->routeHelper->goLogin();
		}

		$region_id = $request->query->getInt('bid', $_SESSION['client']['bezirk_id']);

		if ($this->session->mayBezirk($region_id) && ($region = $this->gateway->getRegionDetails($region_id))) {
			$big = [Type::BIG_CITY, Type::FEDERAL_STATE, Type::COUNTRY];
			$region['moderated'] = $region['moderated'] || in_array($region['type'], $big);
			$this->region = $region;
		} else {
			$this->routeHelper->go('/?page=dashboard');
		}

		$this->pageHelper->addTitle($region['name']);
		$this->pageHelper->addBread($region['name'], '/?page=bezirk&bid=' . $region_id);

		switch ($request->query->get('sub')) {
			case 'botforum':
				if (!$this->forumPermissions->mayAccessAmbassadorBoard($region_id)) {
					$this->routeHelper->go($this->forumService->url($region_id, false));
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
			case 'members':
				$this->members($request, $response, $region);
				break;
			case 'statistic':
				$this->statistic($request, $response, $region);
				break;
			default:
				if ($this->isWorkGroup($region)) {
					$this->routeHelper->go('/?page=bezirk&bid=' . $region_id . '&sub=wall');
				} else {
					$this->routeHelper->go($this->forumService->url($region_id, false));
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
		$this->pageHelper->addBread($this->translationHelper->s('fairteiler'), '/?page=bezirk&bid=' . $region['id'] . '&sub=fairteiler');
		$this->pageHelper->addTitle($this->translationHelper->s('fairteiler'));
		$viewdata = $this->regionViewData($region, $request->query->get('sub'));
		$bezirk_ids = $this->gateway->listIdsForDescendantsAndSelf($region['id']);
		$viewdata['fairteiler'] = $this->fairteilerGateway->listFairteiler($bezirk_ids);
		$response->setContent($this->render('pages/Region/fairteiler.twig', $viewdata));
	}

	private function handleNewThreadForm(Request $request, $region, $ambassadorForum)
	{
		$this->pageHelper->addBread($this->translator->trans('forum.new_thread'));
		$data = CreateForumThreadData::create();
		$form = $this->formFactory->getFormFactory()->create(ForumCreateThreadForm::class, $data);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid() && $this->forumPermissions->mayPostToRegion(
				$region['id'],
				$ambassadorForum
			)) {
			$postActiveWithoutModeration = ($this->session->user('verified') && !$this->region['moderated']) || $this->session->isAmbassadorForRegion([$region['id']]);

			$threadId = $this->forumService->createThread($this->session->id(), $data->title, $data->body, $region, $ambassadorForum, $postActiveWithoutModeration);
			$this->forumFollowerGateway->followThread($this->session->id(), $threadId);
			if (!$postActiveWithoutModeration) {
				$this->flashMessageHelper->info($this->translator->trans('forum.hold_back_for_moderation'));
			}
			$this->routeHelper->go($this->forumService->url($region['id'], $ambassadorForum));
		}

		return $form->createView();
	}

	private function forum(Request $request, Response $response, $region, $ambassadorForum)
	{
		$sub = $request->query->get('sub');
		$trans = $this->translator->trans(($ambassadorForum) ? 'terminology.ambassador_forum' : 'terminology.forum');
		$viewdata = $this->regionViewData($region, $sub);
		$this->pageHelper->addBread($trans, $this->forumService->url($region['id'], $ambassadorForum));
		$this->pageHelper->addTitle($trans);
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
		$this->pageHelper->addBread($this->translator->trans('events.name'), '/?page=bezirk&bid=' . $region['id'] . '&sub=events');
		$this->pageHelper->addTitle($this->translator->trans('events.name'));
		$sub = $request->query->get('sub');
		$viewdata = $this->regionViewData($region, $sub);

		$viewdata['events'] = $this->eventGateway->listForRegion($region['id']);

		$response->setContent($this->render('pages/Region/events.twig', $viewdata));
	}

	private function applications(Request $request, Response $response, $region)
	{
		$this->pageHelper->addBread($this->translator->trans('group.applications'), '/?page=bezirk&bid=' . $region['id'] . '&sub=events');
		$this->pageHelper->addTitle($this->translator->trans('group.applications_for', ['%name%' => $region['name']]));
		$sub = $request->query->get('sub');
		$viewdata = $this->regionViewData($region, $sub);
		if ($this->mayAccessApplications($region['id'])) {
			$viewdata['applications'] = $this->gateway->listRequests($region['id']);
		}
		$response->setContent($this->render('pages/Region/applications.twig', $viewdata));
	}

	private function members(Request $request, Response $response, array $region): void
	{
		$this->pageHelper->addBread($this->translator->trans('group.members'), '/?page=bezirk&bid=' . $region['id'] . '&sub=members');
		$this->pageHelper->addTitle($this->translator->trans('group.members'));
		$sub = $request->query->get('sub');
		$viewdata = $this->regionViewData($region, $sub);
		$viewdata['region']['members'] = $this->foodsaverGateway->listFoodsaverByRegion($region['id']);
		$response->setContent($this->render('pages/Region/members.twig', $viewdata));
	}

	private function statistic(Request $request, Response $response, array $region): void
	{
		$this->pageHelper->addBread(
			$this->translator->trans('terminology.statistic'),
			'/?page=bezirk&bid=' . $region['id'] . '&sub=statistic'
		);
		$this->pageHelper->addTitle($this->translator->trans('terminology.statistic'));
		$sub = $request->query->get('sub');
		$viewData = $this->regionViewData($region, $sub);

		$viewData['genderData']['district'] = $this->gateway->genderCountRegion((int)$region['id']);
		$viewData['genderData']['homeDistrict'] = $this->gateway->genderCountHomeRegion((int)$region['id']);
		$viewData['pickupData']['daily'] = 0;
		$viewData['pickupData']['weekly'] = 0;
		$viewData['pickupData']['monthly'] = 0;
		$viewData['pickupData']['yearly'] = 0;

		if ($region['type'] !== Type::COUNTRY || $this->regionPermissions->mayAccessStatisticCountry()) {
			$viewData['pickupData']['daily'] = $this->gateway->listRegionPickupsByDate((int)$region['id'], '%Y-%m-%d');
			$viewData['pickupData']['weekly'] = $this->gateway->listRegionPickupsByDate((int)$region['id'], '%Y/%v');
			$viewData['pickupData']['monthly'] = $this->gateway->listRegionPickupsByDate((int)$region['id'], '%Y-%m');
			$viewData['pickupData']['yearly'] = $this->gateway->listRegionPickupsByDate((int)$region['id'], '%Y');
		}
		$response->setContent($this->render('pages/Region/statistic.twig', $viewData));
	}
}
