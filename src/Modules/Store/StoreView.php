<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Modules\Core\View;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Foodsharing\Utility\ImageHelper;
use Foodsharing\Utility\PageHelper;
use Foodsharing\Utility\RouteHelper;
use Foodsharing\Utility\Sanitizer;
use Foodsharing\Utility\TimeHelper;
use Foodsharing\Utility\TranslationHelper;
use Foodsharing\Utility\WeightHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

class StoreView extends View
{
	private $weightHelper;

	public function __construct(
		\Twig\Environment $twig,
		Session $session,
		Utils $viewUtils,
		DataHelper $dataHelper,
		IdentificationHelper $identificationHelper,
		ImageHelper $imageService,
		PageHelper $pageHelper,
		RouteHelper $routeHelper,
		Sanitizer $sanitizerService,
		TimeHelper $timeHelper,
		TranslationHelper $translationHelper,
		WeightHelper $weightHelper,
		TranslatorInterface $translator
	) {
		$this->weightHelper = $weightHelper;
		parent::__construct(
			$twig,
			$session,
			$viewUtils,
			$dataHelper,
			$identificationHelper,
			$imageService,
			$pageHelper,
			$routeHelper,
			$sanitizerService,
			$timeHelper,
			$translationHelper,
			$translator
		);
	}

	public function dateForm()
	{
		return '<div id="datepicker" style="height: 220px;"></div>'
			. $this->v_utils->v_input_wrapper('time', $this->v_utils->v_form_time('time'))
			. $this->v_utils->v_form_select('fetchercount', ['selected' => 1, 'values' => [
				['id' => 1, 'name' => $this->translator->trans('pickup.edit.slotcount')],
				['id' => 2, 'name' => $this->translator->trans('pickup.edit.slotscount', ['{count}' => 2])],
				['id' => 3, 'name' => $this->translator->trans('pickup.edit.slotscount', ['{count}' => 3])],
				['id' => 4, 'name' => $this->translator->trans('pickup.edit.slotscount', ['{count}' => 4])],
				['id' => 5, 'name' => $this->translator->trans('pickup.edit.slotscount', ['{count}' => 5])],
				['id' => 6, 'name' => $this->translator->trans('pickup.edit.slotscount', ['{count}' => 6])],
				['id' => 7, 'name' => $this->translator->trans('pickup.edit.slotscount', ['{count}' => 7])],
				['id' => 8, 'name' => $this->translator->trans('pickup.edit.slotscount', ['{count}' => 8])],
			]]);
	}

	public function betrieb_form($region = false, $page = '', $lebensmittel_values, $chains, $categories, $status, $weightArray)
	{
		global $g_data;

		$regionPicker = $this->v_utils->v_regionPicker($region ?: [], $this->translator->trans('terminology.region'));

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
			$regionPicker,
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
			$this->v_utils->v_form_textarea('besonderheiten', [
				'desc' => $this->v_utils->v_info($this->translator->trans('info.md'), false, '<i class="fab fa-markdown fa-2x d-inline align-middle text-muted"></i>')
			]),
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
		$managers = '<ul class="linklist">';
		foreach ($store['foodsaver'] as $fs) {
			if ($fs['verantwortlich'] == 1) {
				$managers .= '<li>' .
					'<a style="background-color: transparent;" href="/profile/' . intval($fs['id']) . '">'
					. $this->imageService->avatar($fs, 50) .
					'</a></li>';
			}
		}
		$managers .= '</ul>';

		$count_info = '<div>' . $this->translator->trans('storeview.teamInfo', [
			'{active}' => count($store['foodsaver']),
			'{jumper}' => count($store['springer']),
		]) . '</div>';

		$pickup_count = intval($store['pickup_count']);
		if ($pickup_count > 0) {
			$count_info .= '<div>' . $this->translator->trans('storeview.pickupCount', [
				'{pickupCount}' => $this->translator->trans('storeview.counter', [
					'{suffix}' => 'x',
					'{count}' => $pickup_count,
				]),
			]) . '</div>';

			$pickupWeight = $this->translator->trans('storeview.counter', [
				'{suffix}' => 'kg',
				'{count}' => round(floatval(
					$pickup_count * $this->weightHelper->mapIdToKilos($store['abholmenge'])
				), 2),
			]);
			$count_info .= '<div>' . $this->translator->trans('storeview.pickupWeight', [
				'{pickupWeight}' => $pickupWeight,
			]) . '</div>';
		}

		$when = strtotime($store['begin']);
		if ($when > 0) {
			$startTime = $this->translator->trans('month.' . intval(date('m', $when))) . ' ' . date('Y', $when);
			$count_info .= '<div>' . $this->translator->trans('storeview.cooperation', [
				'{startTime}' => $startTime,
			]) . '</div>';
		}

		$fetchTime = intval($store['public_time']);
		if ($fetchTime != 0) {
			$count_info .= '<div>' . $this->translator->trans('storeview.frequency', [
				'{freq}' => $this->translator->trans('storeview.frequency' . $fetchTime),
			]) . '</div>';
		}

		$publicInfo = '';
		if (!empty($store['public_info'])) {
			$publicInfo = $this->v_utils->v_input_wrapper(
				$this->translator->trans('storeview.info'),
				$store['public_info'],
				'bcntspecial'
			);
		}

		$status = $this->v_utils->v_getStatusAmpel($store['betrieb_status_id']);

		// Store status
		$bstatus = $this->translator->trans('storestatus.' . intval($store['betrieb_status_id'])) . '.';
		// Team status
		$tstatus = $this->translator->trans('storeedit.fetch.teamStatus' . intval($store['team_status']));

		$html = $this->v_utils->v_input_wrapper(
			$this->translator->trans('storeedit.store.status'),
			$status . '<span class="bstatus">' . $bstatus . '</span>' . $count_info
		) . $this->v_utils->v_input_wrapper(
			$this->translator->trans('storeview.managers'), $managers, 'bcntverantwortlich'
		) . $publicInfo . '<div class="ui-padding">'
		. $this->v_utils->v_info('<strong>' . $tstatus . '</strong>') . '</div>';

		return $html;
	}
}
