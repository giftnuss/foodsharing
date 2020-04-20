<?php

namespace Foodsharing\Modules\Dashboard;

use Foodsharing\Modules\Basket\BasketGateway;
use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Event\EventGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Profile\ProfileGateway;
use Foodsharing\Modules\Quiz\QuizSessionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Services\ImageService;
use Foodsharing\Services\SanitizerService;

class DashboardControl extends Control
{
	private $user;
	private $dashboardGateway;
	private $contentGateway;
	private $basketGateway;
	private $storeGateway;
	private $foodsaverGateway;
	private $eventGateway;
	private $twig;
	private $profileGateway;
	private $sanitizerService;
	private $imageService;
	private $quizSessionGateway;

	public function __construct(
		DashboardView $view,
		DashboardGateway $dashboardGateway,
		ContentGateway $contentGateway,
		BasketGateway $basketGateway,
		StoreGateway $storeGateway,
		FoodsaverGateway $foodsaverGateway,
		EventGateway $eventGateway,
		ProfileGateway $profileGateway,
		\Twig\Environment $twig,
		SanitizerService $sanitizerService,
		ImageService $imageService,
		QuizSessionGateway $quizSessionGateway
	) {
		$this->view = $view;
		$this->dashboardGateway = $dashboardGateway;
		$this->contentGateway = $contentGateway;
		$this->basketGateway = $basketGateway;
		$this->storeGateway = $storeGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->eventGateway = $eventGateway;
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

	public function index()
	{
		$check = false;

		$is_bieb = $this->session->may('bieb');
		$is_bot = $this->session->may('bot');
		$is_fs = $this->session->may('fs');

		if (isset($_SESSION['client']['betriebe']) && is_array($_SESSION['client']['betriebe']) && count($_SESSION['client']['betriebe']) > 0) {
			$is_fs = true;
		}

		if (isset($_SESSION['client']['verantwortlich']) && is_array($_SESSION['client']['verantwortlich']) && count($_SESSION['client']['verantwortlich']) > 0) {
			$is_bieb = true;
		}

		if (isset($_SESSION['client']['botschafter']) && is_array($_SESSION['client']['botschafter']) && count($_SESSION['client']['botschafter']) > 0) {
			//this is is_bieb on purpose; prevents group administrators to be notified about the ambassador quiz
			$is_bieb = true;
		}

		$fsId = $this->session->id();
		if (
			($is_fs && !$this->quizSessionGateway->hasPassedQuiz($fsId, Role::FOODSAVER)) ||
			($is_bieb && !$this->quizSessionGateway->hasPassedQuiz($fsId, Role::STORE_MANAGER)) ||
			($is_bot && !$this->quizSessionGateway->hasPassedQuiz($fsId, Role::AMBASSADOR))
		) {
			$check = true;

			if ($is_bot) {
				$this->pageHelper->addJs('ajreq("endpopup",{app:"quiz"});');
			}
		}

		if ($check) {
			$cnt = $this->contentGateway->get(33);

			$cnt['body'] = str_replace([
				'{NAME}',
				'{ANREDE}'
			], [
				$this->session->user('name'),
				$this->translationHelper->s('anrede_' . $this->session->user('gender'))
			], $cnt['body']);

			if ($this->session->option('quiz-infobox-seen')) {
				$cnt['body'] = '<div>' . substr(strip_tags($cnt['body']), 0, 120) . ' ...<a href="#" onclick="$(this).parent().hide().next().show();return false;">weiterlesen</a></div><div style="display:none;">' . $cnt['body'] . '</div>';
			} else {
				$cnt['body'] = $cnt['body'] . '<p><a href="#" onclick="ajreq(\'quizpopup\',{app:\'quiz\'});return false;">Weiter zum Quiz</a></p><p><a href="#" onclick="$(this).parent().parent().hide();ajax.req(\'quiz\',\'hideinfo\');return false;"><i class="far fa-check-square"></i> Hinweis gelesen und nicht mehr anzeigen</a></p>';
			}
			$this->pageHelper->addContent($this->v_utils->v_info($cnt['body'], $cnt['title']));
		}

		$this->pageHelper->addBread('Dashboard');
		$this->pageHelper->addTitle('Dashboard');
		/* User is foodsaver */

		if ($this->user['rolle'] > 0 && !$this->session->getCurrentRegionId()) {
			$this->pageHelper->addJs('becomeBezirk();');
		}

		if ($this->session->may('fs')) {
			$this->dashFoodsaver();
		} else {
			// foodsharer dashboard
			$this->dashFoodsharer();
		}
	}

	/**
	 * Simple dashboard that is only rendered for foodsharers (users who haven't done the quiz yet and can only create
	 * food baskets and so on).
	 */
	private function dashFoodsharer()
	{
		$this->setContentWidth(8, 8);
		$subtitle = $this->translationHelper->s('no_saved_food');

		if ($this->user['stat_fetchweight'] > 0) {
			$subtitle = $this->translationHelper->sv('saved_food', ['weight' => $this->user['stat_fetchweight']]);
		}

		$this->pageHelper->addContent(
			$this->twig->render('partials/topbar.twig', [
				'title' => $this->translationHelper->sv('welcome', ['name' => $this->user['name']]),
				'subtitle' => $subtitle,
				'avatar' => [
					'user' => $this->user,
					'size' => 50,
					'imageUrl' => $this->imageService->img($this->user['photo'], 50, 'q', '/img/fairteiler50x50.png')
				]
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
			$this->translationHelper->s('anrede_' . $this->session->user('gender'))
		], $cnt['body']);

		$this->pageHelper->addContent($this->v_utils->v_info($cnt['body']));

		$this->pageHelper->addContent($this->view->becomeFoodsaver());

		$this->view->updates();

		if ($this->user['lat'] && ($baskets = $this->basketGateway->listNearbyBasketsByDistance($this->session->id(), $this->session->getLocation()))) {
			$this->pageHelper->addContent($this->view->nearbyBaskets($baskets), CNT_LEFT);
		} else {
			if ($baskets = $this->basketGateway->listNewestBaskets()) {
				$this->pageHelper->addContent($this->view->newBaskets($baskets), CNT_LEFT);
			}
		}
	}

	/**
	 * Dashboard for all users except for foodsharers (they get a simpler one –  @see DashboardControl::dashFoodsharer() ).
	 */
	private function dashFoodsaver()
	{
		$val = $this->foodsaverGateway->getFoodsaverAddress($this->session->id());

		if (empty($val['lat']) || empty($val['lon'])) {
			$this->flashMessageHelper->info($this->translationHelper->s('please_check_address'));
			$this->routeHelper->go('/?page=settings&sub=general&');
		}

		global $g_data;
		$g_data = $val;
		$elements = [];

		if (empty($val['lat']) || empty($val['lon'])) {
			$this->pageHelper->addJs('
                $("#plz, #stadt, #anschrift, #hsnr").on("blur",function(){
                    if($("#plz").val() != "" && $("#stadt").val() != "" && $("#anschrift").val() != "")
                    {
                        u_loadCoords({
                            plz: $("#plz").val(),
                            stadt: $("#stadt").val(),
                            anschrift: $("#anschrift").val(),
                            complete: function()
                            {
                                hideLoader();
                            }
                        },function(lat,lon){
                            $("#lat").val(lat);
                            $("#lon").val(lon);
                        });
                    }
                });

                $("#lat-wrapper").hide();
                $("#lon-wrapper").hide();
            ');
			$elements[] = $this->v_utils->v_form_text('anschrift');
			$elements[] = $this->v_utils->v_form_text('plz');
			$elements[] = $this->v_utils->v_form_text('stadt');
			$elements[] = $this->v_utils->v_form_text('lat');
			$elements[] = $this->v_utils->v_form_text('lon');
		}

		if (!empty($elements)) {
			$out = $this->v_utils->v_form('grabInfo', $elements, ['submit' => 'Speichern']);

			$this->pageHelper->addJs('
                $("#grab-info-link").fancybox({
                    closeClick:false,
                    closeBtn:true,
                });
                $("#grab-info-link").trigger("click");

                $("#grabinfo-form").on("submit", function(e){
                    e.preventDefault();

                        showLoader();
                        $.ajax({
                            url:"/xhr.php?f=grabInfo",
                            data: $("#grabinfo-form").serialize(),
                            dataType: "json",
                            complete:function(){hideLoader();},
                            success: function(){
                                pulseInfo("Danke Dir!");
                                $.fancybox.close();
                            }
                        });
                });
            ');

			$this->pageHelper->addHidden('
			<div id="grab-info">
				<div class="popbox">
					<h3>Bitte noch ein paar Daten vervollständigen bzw. überprüfen!</h3>
					<p class="subtitle">Damit Dein Profil voll funktionsfähig ist, benötigen wir noch folgende Angaben von Dir. Herzlichen Dank!</p>
					' . $out . '
				</div>
			</div><a id="grab-info-link" href="#grab-info">&nbsp;</a>');
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
					$this->pageHelper->addJs('ajax.req("betrieb","setbezirkids");');
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

		/*
		 * Top
		*/
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
			$pickup_text = $this->translationHelper->sv('you_saved_times_weight', ['pickups' => $pickups, 'weight' => number_format($gerettet, 0, ',', '.')]);
		}
		if ($me['bezirk_name'] == null) {
			$home_district_text = '</p>' .
			'<p>' . '<a  class="button" href="javascript:becomeBezirk()" >' . $this->translationHelper->s('please_choose_your_home_district') . '</a>';
		} else {
			$home_district_text = $this->translationHelper->s('your_home_district_is') . $me['bezirk_name'] . '.';
		}

		$this->pageHelper->addContent(
			'
		<div class="pure-u-1 ui-padding-bottom">
		<ul class="content-top corner-all linklist">
		<li>

			<div class="ui-padding">
				<a href="profile/' . $me['id'] . '">
					<div class="img">' . $this->imageService->avatar($me, 50) . '</div>
				</a>
				<h3 class "corner-all">' . $this->translationHelper->sv('greeting', ['name' => $me['name']]) . '</h3>
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

		/*
		 * Nächste Termine
		*/
		if ($dates = $this->profileGateway->getNextDates($this->session->id(), 10)) {
			$this->pageHelper->addContent($this->view->u_nextDates($dates), CNT_RIGHT);
		}

		/*
		 * Deine Bezirke
		*/
		if (isset($_SESSION['client']['bezirke'])) {
			$orga = '
		<ul class="linklist">';
			$out = '
		<ul class="linklist">';
			$orgacheck = false;
			foreach ($_SESSION['client']['bezirke'] as $b) {
				if ($b['type'] != Type::WORKING_GROUP) {
					$out .= '
			<li><a class="ui-corner-all" href="/?page=bezirk&bid=' . $b['id'] . '&sub=forum">' . $b['name'] . '</a></li>';
				} else {
					$orgacheck = true;
					$orga .= '
			<li><a class="ui-corner-all" href="/?page=bezirk&bid=' . $b['id'] . '&sub=forum">' . $b['name'] . '</a></li>';
				}
			}
			$out .= '
		</ul>';
			$orga .= '
		</ul>';

			$out = $this->v_utils->v_field($out, 'Deine Bezirke', ['class' => 'ui-padding']);

			if ($orgacheck) {
				$out .= $this->v_utils->v_field($orga, 'Deine Gruppen', ['class' => 'ui-padding']);
			}

			$this->pageHelper->addContent($out, CNT_RIGHT);
		}

		/*
		 * Essenskörbe
		 */

		if ($baskets = $this->basketGateway->listNearbyBasketsByDistance($this->session->id(), $this->session->getLocation())) {
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
						<a class="ui-corner-all" onclick="ajreq(\'bubble\',{app:\'basket\',id:' . (int)$b['id'] . ',modal:1});return false;" href="#">
							<span style="float:left;margin-right:7px;"><img width="35px" alt="Maike" src="' . $img . '" class="ui-corner-all"></span>
							<span style="height:35px;overflow:hidden;font-size:11px;line-height:16px;"><strong style="float:right;margin:0 0 0 3px;">(' . $distance . ')</strong>' . $this->sanitizerService->tt($b['description'], 50) . '</span>

							<span style="clear:both;"></span>
						</a>
					</li>';
			}
			$out .= '
			</ul>
			<div style="text-align:center;">
				<a class="button" href="/essenskoerbe/find/">Alle Essenskörbe</a>
			</div>';

			$this->pageHelper->addContent($this->v_utils->v_field($out, 'Essenskörbe in Deiner Nähe'), CNT_LEFT);
		}

		/*
		 * Deine Betriebe
		*/
		if ($betriebe = $this->storeGateway->getMyStores($this->session->id(), $this->session->getCurrentRegionId(), ['sonstige' => false])) {
			$this->pageHelper->addContent($this->view->u_myBetriebe($betriebe), CNT_LEFT);
		} else {
			$this->pageHelper->addContent($this->v_utils->v_info('Du bist bis jetzt in keinem Betriebsteam.'), CNT_LEFT);
		}
	}
}
