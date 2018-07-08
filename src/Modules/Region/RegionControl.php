<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Core\Model;
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
		Model $model,
		RegionGateway $gateway,
		RegionHelper $regionHelper
	) {
		$this->forumModerated = false;
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
			if ($this->session->may('orga') || $this->func->isBotFor($region['id'])) {
				$menu[] = ['name' => 'Gruppe verwalten', 'href' => '/?page=groups&sub=edit&id=' . (int)$region['id']];
			}
		} else {
			$menu[] = ['name' => 'terminology.fsp', 'href' => '/?page=bezirk&bid=' . (int)$region['id'] . '&sub=fairteiler'];
			$menu[] = ['name' => 'terminology.groups', 'href' => '/?page=groups&p=' . (int)$region['id']];
		}
		if ($this->mayAccessApplications($region['id'])) {
			if ($requests = $this->gateway->listRequests($region['id'])) {
				$menu[] = ['name' => $this->translator->trans('group.applications') . ' <strong>(' . count($requests) . ')</strong>', 'href' => '/?page=bezirk&bid=' . $region['id'] . '&sub=applications'];
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

		if ($this->func->mayBezirk($region_id) && ($region = $this->gateway->getRegionDetails($region_id))) {
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

	private function forum_thread($threadId, $region, $ambassadorForum = false)
	{
		$viewdata = [];

		$processPosts = function ($p) use ($region) {
			$p['mayDeletePost'] = $this->forumPermissions->mayDeletePost($region, $p);
			$p['avatar'] = [
				'user' => ['id' => $p['author_id'],
					'name' => $p['author_name'],
					'sleep_status' => $p['author_sleep_status'],
				],
				'size' => '130',
				'imageUrl' => $this->func->img($p['author_photo'], '130', 'q')
			];
			$p['time'] = $this->func->niceDate($p['time_ts']);

			return $p;
		};

		if ($this->forumPermissions->mayAccessThread($threadId) && $thread = $this->forumGateway->getThread($threadId)) {
			$viewdata['thread'] = $thread;
			$this->func->addBread($thread['title']);
			if ($thread['active'] == 0 && ($this->forumPermissions->mayActivateThreads($region['id']))) {
				if (isset($_GET['activate'])) {
					$this->forumService->activateThread($threadId, $region, $ambassadorForum);
					$this->func->info('Thema wurde aktiviert!');
					$this->func->go($this->forumService->url($region['id'], $ambassadorForum, $threadId));
				} elseif (isset($_GET['delete'])) {
					$this->func->info('Thema wurde gelöscht!');
					$this->forumGateway->deleteThread($threadId);
					$this->func->go($this->forumService->url($region['id'], $ambassadorForum));
				}
				$viewdata['activate_url'] = $this->forumService->url($region['id'], $ambassadorForum, $threadId) . '&activate=1';
				$viewdata['delete_url'] = $this->forumService->url($region['id'], $ambassadorForum, $threadId) . '&delete=1';
			}

			if ($thread['active'] == 1 || $this->forumPermissions->mayActivateThreads($region['id'])) {
				$viewdata['posts'] = array_map($processPosts, $this->forumGateway->listPosts($threadId));
				$viewdata['following'] = $this->forumGateway->isFollowing($this->session->id(), $threadId);
				$viewdata['mayChangeStickyness'] = $this->forumPermissions->mayChangeStickyness($region['id']);
			} else {
				$this->func->go($this->forumService->url($region['id'], false));
			}
		} else {
			$this->func->go($this->forumService->url($region['id'], $ambassadorForum));
		}

		return $viewdata;
	}

	private function handleNewThreadForm(Request $request, $region, $ambassadorForum)
	{
		$this->func->addBread($this->translator->trans('forum.new_thread'));
		$data = CreateForumThreadData::create();
		$form = $this->formFactory->getFormFactory()->create(ForumCreateThreadForm::class, $data);
		$form->handleRequest($request);
		if ($form->isSubmitted()) {
			if ($form->isValid() && $this->forumPermissions->mayPostToRegion($region['id'], $ambassadorForum)) {
				$threadId = $this->forumService->createThread($this->session->id(), $data->title, $data->body, $region, $ambassadorForum, $this->forumModerated);
				$this->forumGateway->followThread($this->session->id(), $threadId);
				if ($this->forumModerated) {
					$this->func->info($this->translator->trans('forum.hold_back_for_moderation'));
				}
				$this->func->go($this->forumService->url($region['id'], $ambassadorForum));
			}
		}

		return $form->createView();
	}

	private function handlePostForm(Request $request, Response $response, $region, $threadId, $ambassadorForum)
	{
		$data = CreateForumPostData::create($this->forumGateway->isFollowing($this->session->id(), $threadId), $threadId);
		$form = $this->formFactory->getFormFactory()->create(ForumPostForm::class, $data);
		$form->handleRequest($request);
		if ($form->isSubmitted()) {
			if ($form->isValid() && $this->forumPermissions->mayPostToThread($threadId)) {
				$postId = $this->forumService->addPostToThread($this->session->id(), $data->thread, $data->body);
				if ($data->subscribe) {
					$this->forumGateway->followThread($this->session->id(), $data->thread);
				} else {
					$this->forumGateway->unfollowThread($this->session->id(), $data->thread);
				}
				$this->func->go($this->forumService->url($region['id'], $ambassadorForum, $data->thread, $postId));
			} else {
				$this->func->error($this->func->s('post_could_not_saved'));
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
			$viewdata['postForm'] = $this->handlePostForm($request, $response, $region, $tid, $ambassadorForum);
			$viewdata = array_merge($viewdata, $this->forum_thread($tid, $region, $ambassadorForum));
		} elseif ($request->query->has('newthread')) {
			$viewdata['newThreadForm'] = $this->handleNewThreadForm($request, $region, $ambassadorForum);
		} else {
			$viewdata['threads'] = $this->regionHelper->transformThreadViewData($this->forumGateway->listThreads($region['id'], $ambassadorForum), $region['id'], $ambassadorForum);
		}

		// map the post objects to match the format which is gonna be send out by the api
		// get removed anyway as soon as the API is in use
		if (isset($viewdata['posts'])) {
			// $viewdata['posts'] = array_map(function ($post) {
			// 	return [
			// 		'id' => (int)$post['id'],
			// 		'body' => $post['body'],
			// 		'time' => date('Y-m-d H:i:s', $post['time_ts']),
			// 		'author' => [
			// 			'id' => (int)$post['fs_id'],
			// 			'name' => $post['fs_name'],
			// 			'sleepStatus' => (int)$post['fs_sleep_status'],
			// 			'avatar' => [
			// 				'url' => $post['avatar']['imageUrl'],
			// 				'size' => (int)$post['avatar']['size'],
			// 			]
			// 		],
			// 		'reactions' => [
			// 			[
			// 				'key' => 'banana',
			// 				'count' => 5
			// 			],
			// 		],
			// 		'mayDeletePost' => (bool)$post['mayDeletePost']
			// 	];
			// }, $viewdata['posts']);
			// $viewdata['posts'] = array_slice($viewdata['posts'], 0, 2);
			$viewdata['posts'] = [];
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
