<?php

namespace Foodsharing\Modules\Dashboard;

use Foodsharing\Modules\Basket\BasketGateway;
use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Map\MapConstants;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Event\EventGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Profile\ProfileGateway;
use Foodsharing\Modules\Quiz\QuizSessionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Utility\ImageHelper;
use Foodsharing\Utility\LoginService;
use Foodsharing\Utility\Sanitizer;

class DashboardControl extends Control
{
	private ?array $user;
	private DashboardGateway $dashboardGateway;
	private ContentGateway $contentGateway;
	private BasketGateway $basketGateway;
	private StoreGateway $storeGateway;
	private FoodsaverGateway $foodsaverGateway;
	private EventGateway $eventGateway;
	private LoginService $loginService;
	private \Twig\Environment $twig;
	private ProfileGateway $profileGateway;
	private Sanitizer $sanitizerService;
	private ImageHelper $imageService;
	private QuizSessionGateway $quizSessionGateway;

	public function __construct(
		DashboardView $view,
		DashboardGateway $dashboardGateway,
		ContentGateway $contentGateway,
		BasketGateway $basketGateway,
		StoreGateway $storeGateway,
		FoodsaverGateway $foodsaverGateway,
		EventGateway $eventGateway,
		LoginService $loginService,
		ProfileGateway $profileGateway,
		\Twig\Environment $twig,
		Sanitizer $sanitizerService,
		ImageHelper $imageService,
		QuizSessionGateway $quizSessionGateway
	) {
		$this->view = $view;
		$this->dashboardGateway = $dashboardGateway;
		$this->contentGateway = $contentGateway;
		$this->basketGateway = $basketGateway;
		$this->storeGateway = $storeGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->eventGateway = $eventGateway;
		$this->loginService = $loginService;
		$this->twig = $twig;
		$this->profileGateway = $profileGateway;
		$this->sanitizerService = $sanitizerService;
		$this->imageService = $imageService;
		$this->quizSessionGateway = $quizSessionGateway;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->go('/');
		}

		$this->user = $this->dashboardGateway->getUser($this->session->id());
	}

	public function index(): void
	{
		$check = false;

		$is_bieb = $this->session->may('bieb');
		$is_bot = $this->session->may('bot');
		$is_fs = $this->session->may('fs');

		if (isset($_SESSION['client']['verantwortlich']) && is_array($_SESSION['client']['verantwortlich']) && count($_SESSION['client']['verantwortlich']) > 0) {
			$is_bieb = true;
		}

		if (isset($_SESSION['client']['botschafter']) && is_array($_SESSION['client']['botschafter']) && count($_SESSION['client']['botschafter']) > 0) {
			//this is is_bieb on purpose; prevents group administrators to be notified about the ambassador quiz
			$is_bieb = true;
		}

		$fsId = $this->session->id();
		if (!$this->session->isActivated($fsId)) {
			$this->pageHelper->addContent($this->v_utils->v_error($this->translationHelper->s('mail_activation_error_body'), $this->translationHelper->s('mail_activation_error_title')));
		}
		if (
			($is_fs && !$this->quizSessionGateway->hasPassedQuiz($fsId, Role::FOODSAVER)) ||
			($is_bieb && !$this->quizSessionGateway->hasPassedQuiz($fsId, Role::STORE_MANAGER)) ||
			($is_bot && !$this->quizSessionGateway->hasPassedQuiz($fsId, Role::AMBASSADOR))
		) {
			$check = true;

			if ($is_bot) {
				$this->pageHelper->addJs('ajreq("endpopup", {app:"quiz"});');
			}
		}

		if ($check) {
			$cnt = $this->contentGateway->get(33);

			$cnt['body'] = str_replace([
				'{NAME}',
				'{ANREDE}'
			], [
				$this->session->user('name'),
				$this->translator->trans('salutation.' . $this->session->user('gender'))
			], $cnt['body']);

			if ($this->session->getOption('quiz-infobox-seen')) {
				$cnt['body'] = '<div>' . substr(strip_tags($cnt['body']), 0, 120) . ' ...'
					. '<a href="#" onclick="$(this).parent().hide().next().show(); return false;">'
					. $this->translator->trans('dashboard.quiz.read') . '</a>'
				. '</div>'
				. '<div style="display: none;">' . $cnt['body'] . '</div>';
			} else {
				$cnt['body'] = $cnt['body'] . '<p>'
					. '<a href="#" onclick="ajreq(\'quizpopup\', {app:\'quiz\'});return false;">'
					. $this->translator->trans('dashboard.quiz.go') . '</a>'
				. '</p><p>'
					. '<a href="#" onclick="$(this).parent().parent().hide(); ajax.req(\'quiz\', \'hideinfo\'); return false;">'
					. '<i class="far fa-check-square"></i> ' . $this->translator->trans('dashboard.quiz.ack')
					. '</a>'
				. '</p>';
			}
			$this->pageHelper->addContent($this->v_utils->v_info($cnt['body'], $cnt['title']));
		}

		$this->pageHelper->addBread($this->translator->trans('dashboard.title'));
		$this->pageHelper->addTitle($this->translator->trans('dashboard.title'));

		if ($this->session->may('fs')) {
			// User is foodsaver: prompt for home region if not set
			if (!$this->session->getCurrentRegionId()) {
				$this->pageHelper->addJs('becomeBezirk();');
			}
			$this->dashFoodsaver();
		} else {
			$this->dashFoodsharer();
		}
	}

	public function activate()
	{
		$fsId = $this->session->id();

		if ($this->loginGateway->newEmailActivation($fsId)) {
			$this->flashMessageHelper->info($this->translationHelper->s('activation_mail_sent'));
		} else {
			$this->flashMessageHelper->error($this->translationHelper->s('activation_mail_failure'));
		}

		$this->routeHelper->goPage('dashboard');
	}

	/**
	 * Simple dashboard that is only rendered for foodsharers (users who haven't done the quiz yet and can only create
	 * food baskets and so on).
	 */
	private function dashFoodsharer(): void
	{
		$this->pageHelper->setContentWidth(8, 8);
		$subtitle = $this->translator->trans('dashboard.foodsharer');

		if ($this->user['stat_fetchweight'] > 0) {
			$subtitle = $this->translator->trans('dashboard.foodsharer_amount', [
				'{weight}' => $this->user['stat_fetchweight'],
			]);
		}

		$imageUrl = $this->imageService->img($this->user['photo'], 50, 'q', '/img/foodsharepoint50x50.png');

		$this->pageHelper->addContent(
			$this->twig->render('partials/topbar.twig', [
				'title' => $this->translator->trans('dashboard.greeting', ['{name}' => $this->user['name']]),
				'subtitle' => $subtitle,
				'avatar' => [
					'user' => $this->user,
					'size' => 50,
					'imageUrl' => $imageUrl,
				],
			]),
			CNT_TOP
		);

		// Advertisement for Push Notifications
		$this->pageHelper->addContent(
			$this->twig->render('partials/pushNotificationBanner.twig'),
			CNT_TOP
		);

		$this->pageHelper->addContent($this->view->foodsharerMenu(), CNT_LEFT);

		$cnt = $this->contentGateway->get(33);

		$cnt['body'] = str_replace([
			'{NAME}',
			'{ANREDE}'
		], [
			$this->session->user('name'),
			$this->translator->trans('salutation.' . $this->session->user('gender'))
		], $cnt['body']);

		$this->pageHelper->addContent($this->v_utils->v_info($cnt['body']));

		$this->pageHelper->addContent($this->view->becomeFoodsaver());

		$this->view->updates();

		if ($this->user['lat'] && ($baskets = $this->basketGateway->listNearbyBasketsByDistance($this->session->id(), $this->getUserLocationOrDefault()))) {
			$this->pageHelper->addContent($this->view->listBaskets($baskets, true), CNT_LEFT);
		} elseif ($baskets = $this->basketGateway->listNewestBaskets()) {
			$this->pageHelper->addContent($this->view->listBaskets($baskets, false), CNT_LEFT);
		}
	}

	private function getUserLocationOrDefault(): array
	{
		return $this->session->getLocation() ?? ['lat' => MapConstants::CENTER_GERMANY_LAT, 'lon' => MapConstants::CENTER_GERMANY_LON];
	}

	/**
	 * Dashboard for all users except for foodsharers (they get a simpler one –  @see DashboardControl::dashFoodsharer() ).
	 */
	private function dashFoodsaver(): void
	{
		$address = $this->foodsaverGateway->getFoodsaverAddress($this->session->id());

		if (empty($address['lat']) || empty($address['lon'])) {
			$this->flashMessageHelper->info($this->translator->trans('dashboard.checkAddress'));
			$this->routeHelper->go('/?page=settings&sub=general&');
		}

		/*
		 * check if there are stores not bound to a region
		 */
		elseif (isset($_SESSION['client']['verantwortlich']) && is_array($_SESSION['client']['verantwortlich'])) {
			$storeIds = [];
			foreach ($_SESSION['client']['verantwortlich'] as $b) {
				$storeIds[] = (int)$b['betrieb_id'];
			}
			if (!empty($storeIds)) {
				if ($this->dashboardGateway->countStoresWithoutDistrict($storeIds) > 0) {
					$this->pageHelper->addJs('ajax.req("betrieb", "setbezirkids");');
				}
			}
		}

		/* Invitations */
		if ($invites = $this->eventGateway->getEventInvitations($this->session->id())) {
			$this->pageHelper->addContent($this->view->u_invites($invites));
		}

		/* Events */
		if ($events = $this->eventGateway->getEventsInterestedIn($this->session->id())) {
			$this->pageHelper->addContent($this->view->u_events($events));
		}

		$this->pageHelper->addContent($this->view->vueComponent('activity-overview', 'activity-overview', []));

		$me = $this->foodsaverGateway->getFoodsaverBasics($this->session->id());
		if ($me['rolle'] < 0 || $me['rolle'] > 4) {
			$me['rolle'] = 0;
		}
		if ($me['geschlecht'] != 1 && $me['geschlecht'] != 2) {
			$me['geschlecht'] = 0;
		}

		$pickups = $me['stat_fetchcount'];
		$gerettet = $me['stat_fetchweight'];

		// special case: stat_fetchcount and stat_fetchweight are correlated, each pickup increases both count and weight
		$pickup_text = '';
		if ($pickups > 0) {
			$pickup_text = $this->translator->trans('dashboard.foodsaver_amount', [
				'{pickups}' => $pickups,
				'{weight}' => number_format($gerettet, 0, ',', '.'),
			]);
		}
		if ($me['bezirk_name'] == null) {
			$home_district_text = '</p><p>' .
				'<a class="button" href="javascript:becomeBezirk()">'
					. $this->translator->trans('dashboard.chooseHomeRegion') .
				'</a>';
		} else {
			$home_district_text = $this->translator->trans('dashboard.homeRegion', ['{region}' => $me['bezirk_name']]);
		}

		$this->pageHelper->addContent(
			'
		<div class="ui-padding-bottom">
		<ul class="content-top corner-all linklist">
		<li>

			<div class="ui-padding">
				<a href="profile/' . $me['id'] . '">
					<div class="img">' . $this->imageService->avatar($me, 50) . '</div>
				</a>
				<h3 class "corner-all">' . $this->translator->trans('dashboard.greeting', ['{name}' => $me['name']]) . '</h3>
				<p>'
					. $pickup_text . $home_district_text .
				'</p>
				<div style="clear:both;"></div>

            </div>

		</li>
		</ul>
		</div>',

			CNT_TOP
		);

		// Advertisement for Push Notifications
		$this->pageHelper->addContent(
			$this->twig->render('partials/pushNotificationBanner.twig'),
			CNT_TOP
		);

		// Next pickup dates
		if ($dates = $this->profileGateway->getNextDates($this->session->id(), 10)) {
			$this->pageHelper->addContent($this->view->u_nextDates($dates), CNT_RIGHT);
		}

		// Regions and workgroups
		if (isset($_SESSION['client']['bezirke'])) {
			$groups = '';
			$regions = '';
			$hasGroups = false;
			foreach ($_SESSION['client']['bezirke'] as $b) {
				if ($b['type'] == Type::WORKING_GROUP) {
					$hasGroups = true;
					$groups .= '<li><a class="ui-corner-all" href="/?page=bezirk&bid=' . $b['id'] . '&sub=forum">' . $b['name'] . '</a></li>';
				} else {
					$regions .= '<li><a class="ui-corner-all" href="/?page=bezirk&bid=' . $b['id'] . '&sub=forum">' . $b['name'] . '</a></li>';
				}
			}

			$out = $this->v_utils->v_field(
				'<ul class="linklist">' . $regions . '</ul>', $this->translator->trans('dashboard.my.regions'), [
					'class' => 'ui-padding truncate-content truncate-height-85 collapse-mobile',
			]);

			if ($hasGroups) {
				$out .= $this->v_utils->v_field(
					'<ul class="linklist">' . $groups . '</ul>', $this->translator->trans('dashboard.my.groups'), [
						'class' => 'ui-padding truncate-content truncate-height-140 collapse-mobile',
				]);
			}

			$this->pageHelper->addContent($out, CNT_RIGHT);
		}

		// Food baskets
		if ($baskets = $this->basketGateway->listNearbyBasketsByDistance($this->session->id(), $this->getUserLocationOrDefault())) {
			$out = '
			<ul class="linklist">';
			foreach ($baskets as $b) {
				$img = 'img/basket.png';
				if (!empty($b['picture'])) {
					$img = 'images/basket/thumb-' . $b['picture'];
				}

				$distance = round($b['distance'], 1);

				if ($distance == 1.0) {
					$distance = '1 km';
				} elseif ($distance < 1) {
					$distance = ($distance * 1000) . ' m';
				} else {
					$distance = number_format($distance, 1, ',', '.') . ' km';
				}

				$out .= '
				<li>
					<a class="ui-corner-all" onclick="ajreq(\'bubble\', {app:\'basket\', id:' . (int)$b['id'] . ', modal:1}); return false;" href="#">
						<span style="float: left; margin-right: 7px;"><img width="35px" src="' . $img . '" class="ui-corner-all"></span>
						<span style="height: 35px; overflow: hidden; font-size: 11px; line-height: 16px;">'
						. '<strong style="float: right; margin: 0 0 0 3px;">(' . $distance . ')</strong>'
						. $this->sanitizerService->tt($b['description'], 50)
						. '</span>
						<span style="clear: both;"></span>
					</a>
				</li>';
			}
			$out .= '
			</ul>
			<div class="all-baskets-link">
				<a class="button" href="/essenskoerbe/find">' . $this->translator->trans('basket.all') . '</a>
			</div>';

			$this->pageHelper->addContent(
				$this->v_utils->v_field($out, $this->translator->trans('basket.nearby'), [
					'class' => 'truncate-content truncate-height-150 collapse-mobile',
				]),
			CNT_LEFT);
		}

		// Stores
		if ($stores = $this->storeGateway->getMyStores($this->session->id())) {
			$this->pageHelper->addContent($this->view->u_myBetriebe($stores), CNT_LEFT);
		} else {
			$this->pageHelper->addContent($this->v_utils->v_info($this->translator->trans('dashboard.my.no-stores')), CNT_LEFT);
		}
	}
}
