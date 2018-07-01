<?php

namespace Foodsharing\Modules\Dashboard;

use Foodsharing\Modules\Basket\BasketGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Model;
use Foodsharing\Modules\Profile\ProfileModel;

class DashboardControl extends Control
{
	private $user;
	private $dashboardGateway;
	private $contentGateway;
	private $basketGateway;
	private $twig;
	private $profileModel;

	public function __construct(
		DashboardView $view,
		DashboardGateway $dashboardGateway,
		ContentGateway $contentGateway,
		BasketGateway $basketGateway,
		Model $model,
		ProfileModel $profileModel,
		\Twig\Environment $twig)
	{
		$this->view = $view;
		$this->dashboardGateway = $dashboardGateway;
		$this->contentGateway = $contentGateway;
		$this->basketGateway = $basketGateway;
		$this->model = $model;
		$this->twig = $twig;
		$this->profileModel = $profileModel;

		parent::__construct();

		if (!S::may()) {
			$this->func->go('/');
		}

		$this->user = $this->dashboardGateway->getUser($this->func->fsId());
	}

	public function index()
	{
		$check = false;

		$is_bieb = S::may('bieb');
		$is_bot = S::may('bot');
		$is_fs = S::may('fs');

		if (isset($_SESSION['client']['betriebe']) && is_array($_SESSION['client']['betriebe']) && count($_SESSION['client']['betriebe']) > 0) {
			$is_fs = true;
		}

		if (isset($_SESSION['client']['verantwortlich']) && is_array($_SESSION['client']['verantwortlich']) && count($_SESSION['client']['verantwortlich']) > 0) {
			$is_bieb = true;
		}

		if (isset($_SESSION['client']['botschafter']) && is_array($_SESSION['client']['botschafter']) && count($_SESSION['client']['botschafter']) > 0) {
			$is_bieb = true;
		}

		if (
			(
				$is_fs
				&&
				(int)$this->model->qOne('SELECT COUNT(id) FROM fs_quiz_session WHERE quiz_id = 1 AND status = 1 AND foodsaver_id = ' . (int)$this->func->fsId()) == 0
			)
			||
			(
				$is_bieb
				&&
				(int)$this->model->qOne('SELECT COUNT(id) FROM fs_quiz_session WHERE quiz_id = 2 AND status = 1 AND foodsaver_id = ' . (int)$this->func->fsId()) == 0
			)
			||
			(
				$is_bot
				&&
				(int)$this->model->qOne('SELECT COUNT(id) FROM fs_quiz_session WHERE quiz_id = 3 AND status = 1 AND foodsaver_id = ' . (int)$this->func->fsId()) == 0
			)
		) {
			$check = true;

			if ($is_bot) {
				$this->func->addJs('ajreq("endpopup",{app:"quiz"});');
			}
		}

		if ($check) {
			$cnt = $this->contentGateway->get(33);

			$cnt['body'] = str_replace(array(
				'{NAME}',
				'{ANREDE}'
			), array(
				S::user('name'),
				$this->func->s('anrede_' . S::user('gender'))
			), $cnt['body']);

			if (S::option('quiz-infobox-seen')) {
				$cnt['body'] = '<div>' . substr(strip_tags($cnt['body']), 0, 120) . ' ...<a href="#" onclick="$(this).parent().hide().next().show();return false;">weiterlesen</a></div><div style="display:none;">' . $cnt['body'] . '</div>';
			} else {
				$cnt['body'] = $cnt['body'] . '<p><a href="#" onclick="ajreq(\'quizpopup\',{app:\'quiz\'});return false;">Weiter zum Quiz</a></p><p><a href="#" onclick="$(this).parent().parent().hide();ajax.req(\'quiz\',\'hideinfo\');return false;"><i class="fa fa-check-square-o"></i> Hinweis gelesen und nicht mehr anzeigen</a></p>';
			}
			$this->func->addContent($this->v_utils->v_info($cnt['body'], $cnt['title']));
		}

		$this->func->addBread('Dashboard');
		$this->func->addTitle('Dashboard');
		/*
		 * User is foodsaver
		 */

		if ($this->user['rolle'] > 0 && !$this->func->getBezirkId()) {
			$this->func->addJs('becomeBezirk();');
		}

		if (S::may('fs')) {
			$this->dashFoodsaver();
		} else {
			// foodsharer dashboard
			$this->dashFs();
		}
	}

	private function dashFs()
	{
		$this->setContentWidth(8, 8);
		$subtitle = $this->func->s('no_saved_food');

		if ($this->user['stat_fetchweight'] > 0) {
			$subtitle = $this->func->sv('saved_food', array('weight' => $this->user['stat_fetchweight']));
		}

		$this->func->addContent(
			$this->twig->render('partials/topbar.twig', [
				'title' => $this->func->sv('welcome', ['name' => $this->user['name']]),
				'subtitle' => $subtitle,
				'avatar' => [
					'user' => $this->user,
					'size' => 50,
					'imageUrl' => $this->func->img($this->user['photo'], 50, 'q', '/img/fairteiler50x50.png')
				]
			]),
			CNT_TOP
		);

		$this->func->addContent($this->view->becomeFoodsaver());

		$this->func->addContent($this->view->foodsharerMenu(), CNT_LEFT);

		$cnt = $this->contentGateway->get(33);

		$cnt['body'] = str_replace(array(
			'{NAME}',
			'{ANREDE}'
		), array(
			S::user('name'),
			$this->func->s('anrede_' . S::user('gender'))
		), $cnt['body']);

		$this->func->addContent($this->v_utils->v_info($cnt['body'], $cnt['title']));

		$this->view->updates();

		if ($this->user['lat'] && ($baskets = $this->dashboardGateway->listCloseBaskets($this->func->fsId(), S::getLocation($this->model)))) {
			$this->func->addContent($this->view->closeBaskets($baskets), CNT_LEFT);
		} else {
			if ($baskets = $this->dashboardGateway->getNewestFoodbaskets()) {
				$this->func->addContent($this->view->newBaskets($baskets), CNT_LEFT);
			}
		}
	}

	private function dashFoodsaver()
	{
		$val = $this->model->getValues(array('photo_public', 'anschrift', 'plz', 'lat', 'lon', 'stadt'), 'foodsaver', $this->func->fsId());

		if (empty($val['lat']) || empty($val['lon']) ||
			($val['lat']) == '50.05478727164819' && $val['lon'] == '10.3271484375'
		) {
			$this->func->info('Bitte überprüfe Deine Adresse, die Koordinaten konnten nicht ermittelt werden.');
			$this->func->go('/?page=settings&sub=general&');
		}

		global $g_data;
		$g_data = $val;
		$elements = array();

		if ($val['photo_public'] == 0) {
			$g_data['photo_public'] = 1;
			$elements[] = $this->v_utils->v_form_radio('photo_public', array('desc' => 'Du solltest zumindest intern den Menschen in Deiner Umgebung ermöglichen, Dich zu kontaktieren. So kannst Du von anderen Foodsavern eingeladen werden, Lebensmittel zu retten und Ihr könnt Euch einander kennen lernen.', 'values' => array(
				array('name' => 'Ja, ich bin einverstanden, dass mein Name und mein Foto veröffentlicht werden.', 'id' => 1),
				array('name' => 'Bitte nur meinen Namen veröffentlichen.', 'id' => 2),
				array('name' => 'Meine Daten nur intern anzeigen.', 'id' => 3),
				array('name' => 'Meine Daten niemandem zeigen.', 'id' => 4)
			)));
		}

		if (empty($val['lat']) || empty($val['lon'])) {
			$this->func->addJs('
		$("#plz, #stadt, #anschrift, #hsnr").bind("blur",function(){
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
			$out = $this->v_utils->v_form('grabInfo', $elements, array('submit' => 'Speichern'));

			$this->func->addJs('
		$("#grab-info-link").fancybox({
			closeClick:false,
			closeBtn:true,
		});
		$("#grab-info-link").trigger("click");
		
		$("#grabinfo-form").submit(function(e){
			e.preventDefault();
			check = true;
	
			if($("input[name=\'photo_public\']:checked").val()==4)
			{
				$("input[name=\'photo_public\']")[0].focus();
				check = false;
				if(confirm("Sicher das Du Deine Daten nicht anzeigen lassen möchstest? So kann Dich kein Foodsaver finden"))
				{
					check = true;
				}
			}
			if(check)
			{
				showLoader();
				$.ajax({
					url:"xhr.php?f=grabInfo",
					data: $("#grabinfo-form").serialize(),
					dataType: "json",
					complete:function(){hideLoader();},
					success: function(){
						pulseInfo("Danke Dir!");
						$.fancybox.close();
					}
				});
			}
		});
		
		');

			$this->func->addHidden('
			<div id="grab-info">
				<div class="popbox">
					<h3>Bitte noch ein paar Daten vervollständigen bzw. überprüfen!</h3>
					<p class="subtitle">Damit Dein Profil voll funktionsfähig ist, benötigen wir noch folgende Angaben von Dir. Herzlichen Dank!</p>
					' . $out . '
				</div>
			</div><a id="grab-info-link" href="#grab-info">&nbsp;</a>');
		}

		/*
		 * check is there are Betrieb not ordered to an bezirk
		 */
		elseif (isset($_SESSION['client']['verantwortlich']) && is_array($_SESSION['client']['verantwortlich'])) {
			$ids = array();
			foreach ($_SESSION['client']['verantwortlich'] as $b) {
				$ids[] = (int)$b['betrieb_id'];
			}
			if (!empty($ids)) {
				if ($bids = $this->model->q('SELECT id,name,bezirk_id,str,hsnr FROM fs_betrieb WHERE id IN(' . implode(',', $ids) . ') AND ( bezirk_id = 0 OR bezirk_id IS NULL)')) {
					$this->func->addJs('ajax.req("betrieb","setbezirkids");');
				}
			}
		}

		/* Einladungen */
		if ($invites = $this->model->getInvites()) {
			$this->func->addContent($this->view->u_invites($invites));
		}

		/* Events */
		if ($events = $this->model->getNextEvents()) {
			$this->func->addContent($this->view->u_events($events));
		}

		$this->func->addStyle('
			#activity ul.linklist li span.time{margin-left:58px;display:block;margin-top:10px;}
	
			#activity ul.linklist li span.qr
			{
				margin-left:58px;
				-webkit-border-radius: 3px;
				-moz-border-radius: 3px;
				border-radius: 3px;
				opacity:0.5;
			}
				
			#activity ul.linklist li span.qr:hover
			{
				opacity:1;
			}
			
			#activity ul.linklist li span.qr img
			{
				height:32px;
				width:32px;
				margin-right:-35px;
				border-right:1px solid #ffffff;
				-webkit-border-top-left-radius: 3px;
				-webkit-border-bottom-left-radius: 3px;
				-moz-border-radius-topleft: 3px;
				-moz-border-radius-bottomleft: 3px;
				border-top-left-radius: 3px;
				border-bottom-left-radius: 3px;
			}
			#activity ul.linklist li span.qr textarea, #activity ul.linklist li span.qr .loader
			{
				border: 0 none;
				height: 16px;
				margin-left: 36px;
				padding: 8px;
				width: 78.6%;
				-webkit-border-top-right-radius: 3px;
				-webkit-border-bottom-right-radius: 3px;
				-moz-border-radius-topright: 3px;
				-moz-border-radius-bottomright: 3px;
				border-top-right-radius: 3px;
				border-bottom-right-radius: 3px;
				margin-right:-30px;
				background-color:#F9F9F9;
			}
				
			#activity ul.linklist li span.qr .loader
			{
				background-color: #ffffff;
				position: relative;
				text-align: left;
				top: -10px;
			}
	
			#activity ul.linklist li span.t span.txt {
				overflow: hidden;
				text-overflow: unset;
				white-space: normal;
				padding-left:10px;
				border-left:2px solid #4A3520;
				margin-bottom:10px;
				display:block;
			}
			#activity ul.linklist li span
			{
				color:#4A3520;
			}
			#activity ul.linklist li span a
			{
				color:#46891b !important;
			}
			#activity span.n i.fa	
			{
				display:inline-block;
				width:11px;
				text-align:center;
			}
			#activity span.n small
			{
				float:right;
				opacity:0.8;
				font-size:12px;
			}
			#activity ul.linklist li span a:hover
			{
				text-decoration:underline !important;
				color:#46891b !important;
			}
			
			#activity ul.linklist li
			{
				margin-bottom:10px;
				background-color:#ffffff;
				padding:10px;
				-webkit-border-radius: 6px;
				-moz-border-radius: 6px;
				border-radius: 6px;
			}
	
			ul.linklist li span.n
			{
				font-weight:normal;
				font-size:13px;	
				margin-bottom:10px;
				text-overflow: unset;
				white-space: inherit;
			}
		
			@media (max-width: 900px) 
			{
				#activity ul.linklist li span.qr textarea, #activity ul.linklist li span.qr .loader
				{
					width:74.6%;
				}
			}
			@media (max-width: 400px) 
			{
				ul.linklist li span.n
				{
					height:55px;
				}
				#activity ul.linklist li span.qr textarea, #activity ul.linklist li span.qr .loader
				{
					width:82%;
				}
				#activity ul.linklist li span.time, #activity ul.linklist li span.qr
				{
					margin-left:0px;
				}
				#activity span.n small
				{
					float:none;
					display:block;
				}
			}
		');
		$this->func->addContent('
		<div class="head ui-widget-header ui-corner-top">
			Updates-Übersicht<span class="option"><a id="activity-option" href="#activity-listings" class="fa fa-gear"></a></span>
		</div>
		<div id="activity">
			<div class="loader" style="padding:40px;background-image:url(/img/469.gif);background-repeat:no-repeat;background-position:center;"></div>
			<div style="display:none" id="activity-info">' . $this->v_utils->v_info('Es gibt gerade nichts Neues') . '</div>
		</div>');

		/*
		 * Top
		*/
		$me = $this->model->getFoodsaverBasics($this->func->fsId());
		if ($me['rolle'] < 0 || $me['rolle'] > 4) {
			$me['rolle'] = 0;
		}
		if ($me['geschlecht'] != 1 && $me['geschlecht'] != 2) {
			$me['geschlecht'] = 0;
		}

		$gerettet = $me['stat_fetchweight'];

		if ($gerettet > 0) {
			$gerettet = '. Du hast <strong>' . number_format($gerettet, 2, ',', '.') . '<span style="white-space:nowrap">&thinsp;</span>kg</strong> gerettet.';
		} else {
			$gerettet = '';
		}

		$this->func->addContent(
			'
		<div class="top corner-all">
			<div class="img">' . $this->func->avatar($me, 50) . '</div>
				<h3>Hallo ' . $me['name'] . '</h3>
				<p>' . $this->func->s('rolle_' . $me['rolle'] . '_' . $me['geschlecht']) . ' für ' . $me['bezirk_name'] . '</a>' . $gerettet . '</p>
			<div style="clear:both;"></div>		
		</div>',

			CNT_TOP
		);

		/*
		 * Nächste Termine
		*/
		if ($dates = $this->profileModel->getNextDates($this->func->fsId(), 10)) {
			$this->func->addContent($this->view->u_nextDates($dates), CNT_RIGHT);
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
				if ($b['type'] != 7) {
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

			$out = $this->v_utils->v_field($out, 'Deine Bezirke', array('class' => 'ui-padding'));

			if ($orgacheck) {
				$out .= $this->v_utils->v_field($orga, 'Deine Gruppen', array('class' => 'ui-padding'));
			}

			$this->func->addContent($out, CNT_RIGHT);
		}

		/*
		 * Essenskörbe
		 */

		if ($baskets = $this->basketGateway->listCloseBaskets(S::id(), S::getLocation())) {
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
							<span style="height:35px;overflow:hidden;font-size:11px;line-height:16px;"><strong style="float:right;margin:0 0 0 3px;">(' . $distance . ')</strong>' . $this->func->tt($b['description'], 50) . '</span>
							
							<span style="clear:both;"></span>
						</a>
					</li>';
			}
			$out .= '
			</ul>
			<div style="text-align:center;">
				<a class="button" href="/essenskoerbe/find/">Alle Essenskörbe</a>
			</div>';

			$this->func->addContent($this->v_utils->v_field($out, 'Essenskörbe in Deiner Nähe'), CNT_LEFT);
		}

		/*
		 * Deine Betriebe
		*/
		if ($betriebe = $this->model->getMyBetriebe(array('sonstige' => false))) {
			$this->func->addContent($this->view->u_myBetriebe($betriebe), CNT_LEFT);
		} else {
			$this->func->addContent($this->v_utils->v_info('Du bist bis jetzt in keinem Filial-Team.'), CNT_LEFT);
		}
	}
}
