<?php

namespace Foodsharing\Modules\Region;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\RegionOptionType;
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
use Foodsharing\Permissions\WorkGroupPermissions;
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
	private WorkGroupPermissions $workGroupPermission;

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
		VotingPermissions $votingPermissions,
		WorkGroupPermissions $workGroupPermissions
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
		$this->workGroupPermission = $workGroupPermissions;

		parent::__construct();
	}

	private function mayAccessApplications(int $regionId): bool
	{
		return $this->forumPermissions->mayAccessAmbassadorBoard($regionId);
	}

	private function isHomeDistrict($region)
	{
		if ((int)$region['id'] === $this->session->getCurrentRegionId()) {
			return true;
		}

		return false;
	}

	private function regionViewData(array $region, ?string $activeSubpage): array
	{
		$isWorkGroup = $this->isWorkGroup($region);
		$regionId = (int)$region['id'];
		$isHomeDistrict = $this->isHomeDistrict($region);

		$menu = [
			'forum' => ['name' => 'terminology.forum', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=forum'],
			'events' => ['name' => 'terminology.events', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=events'],
			'polls' => ['name' => 'terminology.polls', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=polls'],
			'members' => ['name' => 'group.members', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=members'],
		];

		if (!$isWorkGroup && $this->forumPermissions->mayAccessAmbassadorBoard($regionId)) {
			$menu['ambassador_forum'] = ['name' => 'terminology.ambassador_forum', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=botforum'];
		}

		if (!$isWorkGroup && $this->regionPermissions->maySetRegionOptions($regionId)) {
			$menu['options'] = ['name' => 'terminology.options', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=options'];
		}

		if (!$isWorkGroup && $this->regionPermissions->maySetRegionPin($regionId)) {
			$menu['options'] = ['name' => 'terminology.pin', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=pin'];
		}

		if ($isWorkGroup) {
			$menu['wall'] = ['name' => 'menu.entry.wall', 'href' => '?page=bezirk&bid=' . $regionId . '&sub=wall'];
			if ($region['has_children'] === 1) {
				$menu['subgroups'] = ['name' => 'terminology.subgroups', 'href' => '/?page=groups&p=' . $regionId];
			}
			if ($this->session->isAdminFor($regionId) || $this->session->may('orga')) {
				$menu['workingGroupEdit'] = ['name' => 'menu.entry.workingGroupEdit', 'href' => '/?page=groups&sub=edit&id=' . $regionId];
			}
		} else {
			$menu['fsp'] = ['name' => 'terminology.fsp', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=fairteiler'];
			$menu['groups'] = ['name' => 'terminology.groups', 'href' => '/?page=groups&p=' . $regionId];
			$menu['statistic'] = ['name' => 'terminology.statistic', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=statistic'];

			$menu['stores'] = ['name' => 'menu.entry.stores', 'href' => '?page=betrieb&bid=' . $regionId];

			if ($this->session->isAdminFor($regionId)) {
				$menu['fsList'] = ['name' => 'menu.entry.fs', 'href' => '?page=foodsaver&bid=' . $regionId];
				$menu['passports'] = ['name' => 'menu.entry.ids', 'href' => '?page=passgen&bid=' . $regionId];
			}

			if ($this->reportPermissions->mayAccessReportGroupReports($regionId)) {
				$menu['reports'] = ['name' => 'terminology.reports', 'href' => '/?page=report&bid=' . $regionId];
			}
			if ($this->reportPermissions->mayAccessArbitrationReports($regionId)) {
				$menu['arbitration'] = ['name' => 'terminology.arbitration', 'href' => '/?page=report&bid=' . $regionId];
			}
		}

		if ($this->session->isAdminFor($regionId)) {
			$regionOrGroupString = $isWorkGroup ? $this->translator->trans('group.mail_link_title.workgroup') : $this->translator->trans('group.mail_link_title.region');
			if ($regionMailInfo = $this->mailboxGateway->getMailboxesWithUnreadCount([$region['mailbox_id']])) {
				$regionOrGroupString .= ' (' . $regionMailInfo[0]['count'] . ')';
			}

			$menu['mailbox'] = ['name' => $regionOrGroupString, 'href' => '/?page=mailbox'];
		}

		if ($this->mayAccessApplications($regionId)) {
			if ($requests = $this->gateway->listRequests($regionId)) {
				$menu['applications'] = ['name' => $this->translator->trans('group.applications') . ' (' . count($requests) . ')', 'href' => '/?page=bezirk&bid=' . $regionId . '&sub=applications'];
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

		$menu = $this->sortMenuItems($menu);

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
			'parent_id' => $this->region['parent_id'],
			'name' => $this->region['name'],
			'isWorkGroup' => $isWorkGroup,
			'isHomeDistrict' => $isHomeDistrict,
			'stat' => $stat,
			'admins' => array_map($avatarListEntry, array_slice($this->region['botschafter'], 0, self::DisplayAvatarListEntries)),
			'welcomeAdmins' => array_map($avatarListEntry, array_slice($this->region['welcomeAdmins'], 0, self::DisplayAvatarListEntries)),
			'votingAdmins' => array_map($avatarListEntry, array_slice($this->region['votingAdmins'], 0, self::DisplayAvatarListEntries)),
			'fspAdmins' => array_map($avatarListEntry, array_slice($this->region['fspAdmins'], 0, self::DisplayAvatarListEntries)),
			'storesAdmins' => array_map($avatarListEntry, array_slice($this->region['storesAdmins'], 0, self::DisplayAvatarListEntries)),
			'reportAdmins' => array_map($avatarListEntry, array_slice($this->region['reportAdmins'], 0, self::DisplayAvatarListEntries)),
			'mediationAdmins' => array_map($avatarListEntry, array_slice($this->region['mediationAdmins'], 0, self::DisplayAvatarListEntries)),
			'arbitrationAdmins' => array_map($avatarListEntry, array_slice($this->region['arbitrationAdmins'], 0, self::DisplayAvatarListEntries)),
			'fsManagementAdmins' => array_map($avatarListEntry, array_slice($this->region['fsManagementAdmins'], 0, self::DisplayAvatarListEntries)),
			'prAdmins' => array_map($avatarListEntry, array_slice($this->region['prAdmins'], 0, self::DisplayAvatarListEntries)),
			'moderationAdmins' => array_map($avatarListEntry, array_slice($this->region['moderationAdmins'], 0, self::DisplayAvatarListEntries)),
		];
		$viewdata['nav'] = [
			'menu' => $menu,
			'active' => $activeSubpage ? ('=' . $activeSubpage) : null,
		];

		return $viewdata;
	}

	private function sortMenuItems(array $menu): array
	{
		$menuOrderMaster = [
			['key' => 'wall', 'position' => 0],
			['key' => 'forum', 'position' => 1],
			['key' => 'ambassador_forum', 'position' => 2],
			['key' => 'stores', 'position' => 3],
			['key' => 'groups', 'position' => 4],
			['key' => 'events', 'position' => 5],
			['key' => 'fsp', 'position' => 6],
			['key' => 'conferences', 'position' => 7],
			['key' => 'polls', 'position' => 8],
			['key' => 'members', 'position' => 9],
			['key' => 'statistic', 'position' => 10],
			['key' => 'fsList', 'position' => 11],
			['key' => 'passports', 'position' => 12],
			['key' => 'mailbox', 'position' => 13],
			['key' => 'workingGroupEdit', 'position' => 14],
			['key' => 'reports', 'position' => 15],
			['key' => 'applications', 'position' => 16],
			['key' => 'arbitration', 'position' => 17],
			['key' => 'subgroups', 'position' => 18],
			['key' => 'options', 'position' => 19],
			['key' => 'pin', 'position' => 20],
		];

		$orderedMenu = [];

		foreach ($menuOrderMaster as $value) {
			if (array_key_exists($value['key'], $menu)) {
				$orderedMenu[] = $menu[$value['key']];
			}
		}

		return $orderedMenu;
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
			case 'options':
				if (!$this->regionPermissions->maySetRegionOptions($region_id) || $this->isWorkGroup($region)) {
					$this->flashMessageHelper->info($this->translator->trans('region.restricted'));
					$this->routeHelper->go($this->forumTransactions->url($region_id, false));
				}
				$this->options($request, $response, $region);
				break;
			case 'pin':
				if (!$this->regionPermissions->maySetRegionPin($region_id) || $this->isWorkGroup($region)) {
					$this->flashMessageHelper->info($this->translator->trans('region.restricted'));
					$this->routeHelper->go($this->forumTransactions->url($region_id, false));
				}
				$this->pin($request, $response, $region);
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
				$ambassadorForum, $postActiveWithoutModeration, $postActiveWithoutModeration ? $data->sendMail : null
			);

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
		// for now, the admin mode of the members list is only available in working groups
		$viewdata['mayEditMembers'] = $region['type'] === Type::WORKING_GROUP && $this->workGroupPermission->mayEdit($region);
		$viewdata['userId'] = $this->session->id();
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
		$viewData['ageBand']['district'] = $this->gateway->AgeBandDistrict((int)$region['id']);
		$viewData['ageBand']['homeDistrict'] = $this->gateway->AgeBandHomeDistrict((int)$region['id']);

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

	private function options(Request $request, Response $response, array $region): void
	{
		$this->pageHelper->addBread($this->translator->trans('terminology.options'), '/?page=bezirk&bid=' . $region['id'] . '&sub=options');
		$this->pageHelper->addTitle($this->translator->trans('terminology.options'));
		$viewdata = $this->regionViewData($region, $request->query->get('sub'));
		$isReportButtonEnabled = intval($this->gateway->getRegionOption($region['id'], RegionOptionType::ENABLE_REPORT_BUTTON)) === 1;
		$isMediationButtonEnabled = intval($this->gateway->getRegionOption($region['id'], RegionOptionType::ENABLE_MEDIATION_BUTTON)) === 1;
		$viewdata['isReportButtonEnabled'] = empty($isReportButtonEnabled) ? false : true;
		$viewdata['isMediationButtonEnabled'] = empty($isMediationButtonEnabled) ? false : true;
		$response->setContent($this->render('pages/Region/options.twig', $viewdata));
	}

	private function pin(Request $request, Response $response, array $region): void
	{
		$this->pageHelper->addBread($this->translator->trans('terminology.pin'), '/?page=bezirk&bid=' . $region['id'] . '&sub=pin');
		$this->pageHelper->addTitle($this->translator->trans('terminology.pin'));
		$viewdata = $this->regionViewData($region, $request->query->get('sub'));
		$result = $this->gateway->getRegionPin($region['id']);
		$viewdata['lat'] = $result['lat'] ?? null;
		$viewdata['lon'] = $result['lon'] ?? null;
		$viewdata['desc'] = $result['desc'] ?? null;
		$viewdata['status'] = $result['status'] ?? null;
		$response->setContent($this->render('pages/Region/pin.twig', $viewdata));
	}
}
