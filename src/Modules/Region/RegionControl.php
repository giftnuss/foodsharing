<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Event\EventGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\FoodSharePoint\FoodSharePointGateway;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Modules\Voting\VotingGateway;
use Foodsharing\Permissions\ForumPermissions;
use Foodsharing\Permissions\RegionPermissions;
use Foodsharing\Permissions\ReportPermissions;
use Foodsharing\Permissions\VotingPermissions;
use Foodsharing\Utility\ImageHelper;
use phpDocumentor\Reflection\Types\This;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RegionControl extends Control
{
	private array $region;
	private RegionGateway $gateway;
	private EventGateway $eventGateway;
	private ForumGateway $forumGateway;
	private FoodSharePointGateway $foodSharePointGateway;
	private FoodsaverGateway $foodsaverGateway;
	private ForumFollowerGateway $forumFollowerGateway;
	private FormFactoryInterface $formFactory;
	private ForumTransactions $forumTransactions;
	private ForumPermissions $forumPermissions;
	private RegionPermissions $regionPermissions;
	private ImageHelper $imageService;
	private ReportPermissions $reportPermissions;
	private MailboxGateway $mailboxGateway;
	private VotingGateway $votingGateway;
	private VotingPermissions $votingPermissions;

	private const DisplayAvatarListEntries = 30;

	/**
	 * @required
	 */
	public function setFormFactory(FormFactoryInterface $formFactory)
	{
		$this->formFactory = $formFactory;
	}

	public function __construct(
		EventGateway $eventGateway,
		FoodSharePointGateway $foodSharePointGateway,
		FoodsaverGateway $foodsaverGateway,
		ForumGateway $forumGateway,
		ForumFollowerGateway $forumFollowerGateway,
		ForumPermissions $forumPermissions,
		RegionPermissions $regionPermissions,
		ForumTransactions $forumTransactions,
		RegionGateway $gateway,
		ReportPermissions $reportPermissions,
		ImageHelper $imageService,
		MailboxGateway $mailboxGateway,
		VotingGateway $votingGateway,
		VotingPermissions $votingPermissions
	) {
		$this->gateway = $gateway;
		$this->eventGateway = $eventGateway;
		$this->forumPermissions = $forumPermissions;
		$this->regionPermissions = $regionPermissions;
		$this->forumGateway = $forumGateway;
		$this->foodSharePointGateway = $foodSharePointGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->forumFollowerGateway = $forumFollowerGateway;
		$this->forumTransactions = $forumTransactions;
		$this->reportPermissions = $reportPermissions;
		$this->imageService = $imageService;
		$this->mailboxGateway = $mailboxGateway;
		$this->votingGateway = $votingGateway;
		$this->votingPermissions = $votingPermissions;

		parent::__construct();
	}

	private function mayAccessApplications(int $regionId): bool
	{
		return $this->forumPermissions->mayAccessAmbassadorBoard($regionId);
	}

	private function regionViewData(array $region, string $activeSubpage): array
	{
		$isWorkGroup = $this->isWorkGroup($region);
		$regionId = (int)$region['id'];
		$menu = [
			['name' => 'terminology.forum', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=forum'],
			['name' => 'terminology.events', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=events'],
			['name' => 'group.members', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=members'],
			['name' => 'terminology.polls', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=polls'],
		];

		if (!$isWorkGroup && $this->forumPermissions->mayAccessAmbassadorBoard($regionId)) {
			$menu[] = ['name' => 'terminology.ambassador_forum', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=botforum'];
		}

		if ($isWorkGroup) {
			$menu[] = ['name' => 'terminology.wall', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=wall'];
			if ($region['has_children'] === 1) {
				$menu[] = ['name' => 'terminology.subgroups', 'href' => '/?page=groups&p=' . $regionId];
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

		if ($this->session->isAdminFor($regionId)) {
			$regionOrGroupString = $isWorkGroup ? $this->translator->trans('group.mail_link_title.workgroup') : $this->translator->trans('group.mail_link_title.region');
			if ($regionMailInfo = $this->mailboxGateway->getNewCount([['id' => $region['mailbox_id']]])) {
				$regionOrGroupString .= ' (' . $regionMailInfo[0]['count'] . ')';
			}

			$menu[] = ['name' => $regionOrGroupString, 'href' => '/?page=mailbox'];
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
			'admins' => array_map($avatarListEntry, array_slice($this->region['botschafter'], 0, self::DisplayAvatarListEntries)),
			'welcomeAdmins' => array_map($avatarListEntry, array_slice($this->region['welcomeAdmins'], 0, self::DisplayAvatarListEntries)),
			'votingAdmins' => array_map($avatarListEntry, array_slice($this->region['votingAdmins'], 0, self::DisplayAvatarListEntries)),
			'fspAdmins' => array_map($avatarListEntry, array_slice($this->region['fspAdmins'], 0, self::DisplayAvatarListEntries)),
		];
		$viewdata['nav'] = ['menu' => $menu, 'active' => '=' . $activeSubpage];

		return $viewdata;
	}

	private function isWorkGroup(array $region): bool
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
			$this->flashMessageHelper->error($this->translator->trans('region.not-member'));
			$this->routeHelper->go('/?page=dashboard');

			return;
		}

		$this->pageHelper->addTitle($region['name']);
		$this->pageHelper->addBread($region['name'], '/?page=bezirk&bid=' . $region_id);

		switch ($request->query->get('sub')) {
			case 'botforum':
				if (!$this->forumPermissions->mayAccessAmbassadorBoard($region_id)) {
					$this->routeHelper->go($this->forumTransactions->url($region_id, false));
				}
				$this->forum($request, $response, $region, true);
				break;
			case 'forum':
				$this->forum($request, $response, $region, false);
				break;
			case 'wall':
				if (!$this->isWorkGroup($region)) {
					$this->flashMessageHelper->info($this->translator->trans('region.forum-redirect'));
					$this->routeHelper->go('/?page=bezirk&bid=' . $region_id . '&sub=forum');
				} else {
					$this->wall($request, $response, $region);
				}
				break;
			case 'fairteiler':
				$this->foodSharePoint($request, $response, $region);
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
			case 'polls':
				$this->polls($request, $response, $region);
				break;
			default:
				if ($this->isWorkGroup($region)) {
					$this->routeHelper->go('/?page=bezirk&bid=' . $region_id . '&sub=wall');
				} else {
					$this->routeHelper->go($this->forumTransactions->url($region_id, false));
				}
				break;
		}
	}

	private function wall(Request $request, Response $response, array $region)
	{
		$viewdata = $this->regionViewData($region, $request->query->get('sub'));
		$viewdata['wall'] = ['module' => 'bezirk', 'wallId' => $region['id']];
		$response->setContent($this->render('pages/Region/wall.twig', $viewdata));
	}

	private function foodSharePoint(Request $request, Response $response, array $region)
	{
		$this->pageHelper->addBread($this->translator->trans('terminology.fsp'), '/?page=bezirk&bid=' . $region['id'] . '&sub=fairteiler');
		$this->pageHelper->addTitle($this->translator->trans('terminology.fsp'));
		$viewdata = $this->regionViewData($region, $request->query->get('sub'));
		$bezirk_ids = $this->gateway->listIdsForDescendantsAndSelf($region['id']);
		$viewdata['food_share_points'] = $this->foodSharePointGateway->listActiveFoodSharePoints($bezirk_ids);
		$response->setContent($this->render('pages/Region/foodSharePoint.twig', $viewdata));
	}

	private function handleNewThreadForm(Request $request, array $region, $ambassadorForum, bool $postActiveWithoutModeration)
	{
		$this->pageHelper->addBread($this->translator->trans('forum.new_thread'));
		$data = CreateForumThreadData::create();
		$form = $this->formFactory->create(ForumCreateThreadForm::class, $data, ['postActiveWithoutModeration' => $postActiveWithoutModeration]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()
			&& $this->forumPermissions->mayPostToRegion($region['id'], $ambassadorForum)
		) {
			$threadId = $this->forumTransactions->createThread(
				$this->session->id(), $data->title, $data->body, $region,
				$ambassadorForum, $postActiveWithoutModeration, $data->sendMail
			);

			$this->forumFollowerGateway->followThreadByEmail($this->session->id(), $threadId);
			$this->forumFollowerGateway->followThreadByBell($this->session->id(), $threadId);

			if (!$postActiveWithoutModeration) {
				$this->flashMessageHelper->info($this->translator->trans('forum.hold_back_for_moderation'));
			}
			$this->routeHelper->go($this->forumTransactions->url($region['id'], $ambassadorForum));
		}

		return $form->createView();
	}

	private function forum(Request $request, Response $response, $region, $ambassadorForum)
	{
		$sub = $request->query->get('sub');
		$trans = $this->translator->trans(($ambassadorForum) ? 'terminology.ambassador_forum' : 'terminology.forum');
		$viewdata = $this->regionViewData($region, $sub);
		$this->pageHelper->addBread($trans, $this->forumTransactions->url($region['id'], $ambassadorForum));
		$this->pageHelper->addTitle($trans);
		$viewdata['sub'] = $sub;

		if ($threadId = $request->query->getInt('tid')) {
			$viewdata['threadId'] = $threadId; // this triggers the rendering of the vue component `Thread`
		} elseif ($request->query->has('newthread')) {
			$postActiveWithoutModeration = $this->forumPermissions->mayStartUnmoderatedThread($region, $ambassadorForum);
			$viewdata['newThreadForm'] = $this->handleNewThreadForm($request, $region, $ambassadorForum, $postActiveWithoutModeration);
			$viewdata['postActiveWithoutModeration'] = $postActiveWithoutModeration;
		} else {
			$viewdata['threads'] = []; // this triggers the rendering of the vue component `ThreadList`
		}

		$response->setContent($this->render('pages/Region/forum.twig', $viewdata));
	}

	private function events(Request $request, Response $response, $region)
	{
		$this->pageHelper->addBread($this->translator->trans('events.bread'), '/?page=bezirk&bid=' . $region['id'] . '&sub=events');
		$this->pageHelper->addTitle($this->translator->trans('events.bread'));
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
		$viewdata['region']['members'] = $this->foodsaverGateway->listActiveFoodsaversByRegion($region['id']);
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

	private function polls(Request $request, Response $response, array $region): void
	{
		$this->pageHelper->addBread($this->translator->trans('terminology.polls'), '/?page=bezirk&bid=' . $region['id'] . '&sub=polls');
		$this->pageHelper->addTitle($this->translator->trans('terminology.polls'));
		$viewdata = $this->regionViewData($region, $request->query->get('sub'));
		$viewdata['polls'] = $this->votingGateway->listPolls($region['id']);
		$viewdata['regionId'] = $region['id'];
		$viewdata['mayCreatePoll'] = $this->votingPermissions->mayCreatePoll($region['id']);
		$response->setContent($this->render('pages/Region/polls.twig', $viewdata));
	}
}
