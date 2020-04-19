<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Helpers\PageHelper;
use Foodsharing\Helpers\RouteHelper;
use Foodsharing\Helpers\TimeHelper;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Helpers\WeightHelper;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Modules\Core\View;
use Foodsharing\Services\ImageService;
use Foodsharing\Services\SanitizerService;

class StoreView extends View
{
	private $weightHelper;

	public function __construct(
			\Twig\Environment $twig,
			Utils $viewUtils,
			Session $session,
			SanitizerService $sanitizerService,
			PageHelper $pageHelper,
			TimeHelper $timeHelper,
			ImageService $imageService,
			RouteHelper $routeHelper,
			IdentificationHelper $identificationHelper,
			DataHelper $dataHelper,
			TranslationHelper $translationHelper,
			WeightHelper $weightHelper
			) {
		$this->weightHelper = $weightHelper;
		parent::__construct(
						$twig,
						$viewUtils,
						$session,
						$sanitizerService,
						$pageHelper,
						$timeHelper,
						$imageService,
						$routeHelper,
						$identificationHelper,
						$dataHelper,
						$translationHelper
						);
	}

	public function dateForm()
	{
		return
			'<div id="datepicker" style="height:220px;"></div>' .
			$this->v_utils->v_input_wrapper('Uhrzeit', $this->v_utils->v_form_time('time')) .
			$this->v_utils->v_form_select('fetchercount', ['selected' => 1, 'values' => [
				['id' => 1, 'name' => '1 Abholer/in'],
				['id' => 2, 'name' => '2 Abholer/innen'],
				['id' => 3, 'name' => '3 Abholer/innen'],
				['id' => 4, 'name' => '4 Abholer/innen'],
				['id' => 5, 'name' => '5 Abholer/innen'],
				['id' => 6, 'name' => '6 Abholer/innen'],
				['id' => 7, 'name' => '7 Abholer/innen'],
				['id' => 8, 'name' => '8 Abholer/innen']
			]]);
	}

	public function fetchHistory()
	{
		return $this->v_utils->v_form_daterange('daterange', [
				'content_after' => ' <a href="#" id="daterange_submit" class="button"><i class="fas fa-search"></i></a>'
			]) . '<div id="daterange_content"></div>';
	}

	public function fetchlist($history)
	{
		$out = '
			<ul class="linklist history">';
		$curdate = 0;
		foreach ($history as $h) {
			if ($curdate != $h['date']) {
				$out .= '<li class="title">' . $this->timeHelper->niceDate($h['date_ts']) . '</li>';
				$curdate = $h['date'];
			}
			$out .= '
				<li>
					<a class="corner-all" href="/profile/' . (int)$h['id'] . '">
						<span class="i"><img src="' . $this->imageService->img($h['photo']) . '" /></span>
						<span class="n">' . $h['name'] . ' ' . $h['nachname'] . '</span>
						<span class="t"></span>
						<span class="c"></span>
					</a>
				</li>';
		}

		$out .= '
			</ul>';

		return $out;
	}

	public function betrieb_form($region = false, $page = '', $lebensmittel_values, $chains, $categories, $status, $weightArray)
	{
		global $g_data;

		$bc = $this->v_utils->v_bezirkChooser('bezirk_id', $region);

		if (!isset($g_data['foodsaver'])) {
			$g_data['foodsaver'] = [$this->session->id()];
		}

		$first_post = '';
		if ($this->identificationHelper->getAction('new')) {
			$first_post = $this->v_utils->v_form_textarea('first_post', ['required' => true]);
		}
		if (isset($g_data['stadt'])) {
			$g_data['ort'] = $g_data['stadt'];
		}
		if (isset($g_data['str'])) {
			$g_data['anschrift'] = $g_data['str'];
		}
		if (isset($g_data['hsnr'])) {
			$g_data['anschrift'] .= ' ' . $g_data['hsnr'];
		}

		$this->pageHelper->addJs('$("textarea").css("height","70px");$("textarea").autosize();');

		$latLonOptions = [];

		foreach (['anschrift', 'plz', 'ort', 'lat', 'lon'] as $i) {
			if (isset($g_data[$i])) {
				$latLonOptions[$i] = $g_data[$i];
			}
		}
		if (isset($g_data['lat'], $g_data['lon'])) {
			$latLonOptions['location'] = ['lat' => $g_data['lat'], 'lon' => $g_data['lon']];
		} else {
			$latLonOptions['location'] = ['lat' => 0, 'lon' => 0];
		}

		return $this->v_utils->v_quickform($this->translationHelper->s('betrieb'), [
			$bc,
			$this->v_utils->v_form_hidden('page', $page),
			$this->v_utils->v_form_text('name', ['required' => true]),
			$this->latLonPicker('LatLng', $latLonOptions),

			$this->v_utils->v_form_select('kette_id', ['add' => true, 'values' => $chains, 'desc' => 'Bitte nur inhabergeführte Betriebe bis maximal 3 Filialen ansprechen, niemals Filialen einer größeren Kette ansprechen! Betriebskettenregeln beachten!']),
			$this->v_utils->v_form_select('betrieb_kategorie_id', ['add' => true, 'values' => $categories]),

			$this->v_utils->v_form_select('betrieb_status_id', ['values' => $status, 'desc' => $this->v_utils->v_info($this->translationHelper->s('store_status_impact_explanation'))]),

			$this->v_utils->v_form_text('ansprechpartner'),
			$this->v_utils->v_form_text('telefon'),
			$this->v_utils->v_form_text('fax'),
			$this->v_utils->v_form_text('email'),

			$this->v_utils->v_form_checkbox('lebensmittel', ['values' => $lebensmittel_values]),
			$this->v_utils->v_form_date('begin'),
			$this->v_utils->v_form_textarea('besonderheiten'),
			$this->v_utils->v_form_textarea('public_info', ['maxlength' => 180, 'desc' => 'Hier kannst Du einige Infos für die Foodsaver angeben, die sich für das Team bewerben möchten. <br />(max. 180 Zeichen)<div>' . $this->v_utils->v_info('<strong>Wichtig:</strong> Gib hier keine genauen Abholzeiten an.<br />Es ist des Öfteren vorgekommen, dass Leute unabgesprochen zum Laden gegangen sind.') . '</div>']),
			$this->v_utils->v_form_select('public_time', ['values' => [
				['id' => 0, 'name' => 'Keine Angabe'],
				['id' => 1, 'name' => 'morgens'],
				['id' => 2, 'name' => 'mittags/nachmittags'],
				['id' => 3, 'name' => 'abends'],
				['id' => 4, 'name' => 'nachts']
			]]),
			$first_post,
			$this->v_utils->v_form_select('ueberzeugungsarbeit', ['values' => [
				['id' => 1, 'name' => 'Überhaupt kein Problem, er/sie war/en sofort begeistert!'],
				['id' => 2, 'name' => 'Nach Überzeugungsarbeit erklärte er/sie sich bereit mitzumachen '],
				['id' => 3, 'name' => 'Ganz schwierig, aber am Ende hat er/sie eingewilligt'],
				['id' => 4, 'name' => 'Zuerst sah es schlecht aus, dann hat er/sie sich aber doch gemeldet']
			]]),
			$this->v_utils->v_form_select('presse', ['values' => [
				['id' => 1, 'name' => 'Ja'],
				['id' => 0, 'name' => 'Nein']
			]]),
			$this->v_utils->v_form_select('sticker', ['values' => [
				['id' => 1, 'name' => 'Ja'],
				['id' => 0, 'name' => 'Nein']
			]]),
			$this->v_utils->v_form_select('prefetchtime', ['values' => [
				['id' => 604800, 'name' => '1 Woche'],
				['id' => 1209600, 'name' => '2 Wochen'],
				['id' => 1814400, 'name' => '3 Wochen'],
				['id' => 2419200, 'name' => '4 Wochen']
			]]),
			$this->v_utils->v_form_select('abholmenge', ['values' => $weightArray])
		]);
	}

	public function bubble(array $store): string
	{
		$b = $store;
		$verantwortlich = '<ul class="linklist">';
		foreach ($b['foodsaver'] as $fs) {
			if ($fs['verantwortlich'] == 1) {
				$verantwortlich .= '
			<li><a style="background-color:transparent;" href="/profile/' . (int)$fs['id'] . '">' . $this->imageService->avatar($fs, 50) . '</a></li>';
			}
		}
		$verantwortlich .= '</ul>';

		$besonderheiten = '';

		$count_info = '';
		$activeFoodSaver = count($b['foodsaver']);
		$jumperFoodSaver = count($b['springer']);
		$count_info .= '<div>' . $this->translationHelper->sv('store_info', ['active' => $activeFoodSaver, 'jumper' => $jumperFoodSaver]) . '</div>';
		$pickup_count = (int)$b['pickup_count'];
		if ($pickup_count > 0) {
			$count_info .= '<div>' . $this->translationHelper->sv('store_info_pickupcount', ['pickupCount' => $pickup_count]) . '</div>';
			$fetch_weight = round(floatval(($pickup_count * $this->weightHelper->mapIdToKilos($b['abholmenge']))), 2);
			$count_info .= '<div>' . $this->translationHelper->sv('store_info_pickupweight', ['fetch_weight' => $fetch_weight]) . '</div>';
		}

		$time = strtotime($b['begin']);
		if ($time > 0) {
			$count_info .= '<div> ' . $this->translationHelper->sv('store_info_cooperation', ['startTime' => $this->translationHelper->s('month_' . (int)date('m', $time)) . ' ' . date('Y', $time)]) . '</div>';
		}

		if ((int)$b['public_time'] != 0) {
			$b['public_info'] .= '<div>' . $this->translationHelper->sv('store_info_freq', ['freq' => $this->translationHelper->s('pubbtime_' . (int)$b['public_time'])]) . '</div>';
		}

		if (!empty($b['public_info'])) {
			$besonderheiten = $this->v_utils->v_input_wrapper($this->translationHelper->s('info'), $b['public_info'], 'bcntspecial');
		}

		$status = $this->v_utils->v_getStatusAmpel($b['betrieb_status_id']);
		$html = $this->v_utils->v_input_wrapper($this->translationHelper->s('status'), $status . '<span class="bstatus">' . $this->translationHelper->s('betrieb_status_' . $b['betrieb_status_id']) . '</span>' . $count_info) . '
			' . $this->v_utils->v_input_wrapper($this->translationHelper->s('foodsaver'), $verantwortlich, 'bcntverantwortlich') . '
			' . $besonderheiten . '
			<div class="ui-padding">
				' . $this->v_utils->v_info('' . $this->translationHelper->s('team_status_' . $b['team_status']) . '') . '
			</div>';

		return $html;
	}
}
