<?php

namespace Foodsharing\Lib\Xhr;

use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Helpers\WeightHelper;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Services\ImageService;

class ViewUtils
{
	/**
	 * @var Utils
	 */
	private $viewUtils;
	private $session;
	private $imageService;
	private $translationHelper;
	private $weightHelper;

	public function __construct(
		Utils $viewUtils,
		Session $session,
		ImageService $imageService,
		TranslationHelper $translationHelper,
		WeightHelper $weightHelper
	) {
		$this->viewUtils = $viewUtils;
		$this->weightHelper = $weightHelper;
		$this->session = $session;
		$this->imageService = $imageService;
		$this->translationHelper = $translationHelper;
	}

	public function bBubble($b)
	{
		$button = '';
		if (($b['inTeam']) || $this->session->isOrgaTeam()) {
			$button .= '<div class="buttonrow"><a class="lbutton" href="/?page=fsbetrieb&id=' . (int)$b['id'] . '">' . $this->translationHelper->s('to_team_page') . '</a></div>';
		}
		if ($b['team_status'] != 0 && (!$b['inTeam'] && (!$b['pendingRequest']))) {
			$button .= '<div class="buttonrow"><a class="lbutton" href="#" onclick="betriebRequest(' . (int)$b['id'] . ');return false;">' . $this->translationHelper->s('want_to_fetch') . '</a></div>';
		} elseif ($b['team_status'] != 0 && (!$b['inTeam'] && ($b['pendingRequest']))) {
			$button .= '<div class="buttonrow"><a class="lbutton" href="#" onclick="rejectBetriebRequest(' . (int)$this->session->id() . ',' . (int)$b['id'] . ');return false;">Anfrage zur&uuml;ckziehen </a></div>';
		}

		$verantwortlich = '<ul class="linklist">';
		foreach ($b['foodsaver'] as $fs) {
			if ($fs['verantwortlich'] == 1) {
				$verantwortlich .= '
			<li><a style="background-color:transparent !important;" href="/profile/' . (int)$fs['id'] . '">' . $this->imageService->avatar($fs, 50) . '</a></li>';
			}
		}
		$verantwortlich .= '
	</ul>';

		$besonderheiten = '';

		$count_info = '';
		$activeFoodSaver = count($b['foodsaver']);
		$jumperFoodSaver = count($b['springer']);
		$count_info .= '<div>Es sind <strong>' . $activeFoodSaver . '</strong> Aktive und <strong>' . $jumperFoodSaver . '</strong> Springer im Team</div>';
		$pickup_count = (int)$b['pickup_count'];
		if ($pickup_count > 0) {
			$count_info .= '<div>Bei diesem Betrieb wurde <strong>' . $pickup_count . '<span style="white-space:nowrap">&thinsp;</span>x</strong> abgeholt</div>';
			$fetch_weight = round(floatval(($pickup_count * $this->weightHelper->mapIdToKilos($b['abholmenge']))), 2);
			$count_info .= '<div">Es wurden <strong>' . $fetch_weight . '<span style="white-space:nowrap">&thinsp;</span>kg</strong> gerettet</div>';
		}

		$time = strtotime($b['begin']);
		if ($time > 0) {
			$count_info .= '<div>Kooperation seit ' . $this->translationHelper->s('month_' . (int)date('m', $time)) . ' ' . date('Y', $time) . '</div>';
		}

		if ((int)$b['public_time'] != 0) {
			$b['public_info'] .= '<div>Es wird in etwa ' . $this->translationHelper->s('pubbtime_' . (int)$b['public_time']) . ' abgeholt. Geh bitte niemals ohne Absprache zum Laden!</div>';
		}

		if (!empty($b['public_info'])) {
			$besonderheiten = $this->viewUtils->v_input_wrapper($this->translationHelper->s('info'), $b['public_info'], 'bcntspecial');
		}

		$status = $this->viewUtils->v_getStatusAmpel($b['betrieb_status_id']);

		return '
			' . $this->viewUtils->v_input_wrapper($this->translationHelper->s('status'), $status . '<span class="bstatus">' . $this->translationHelper->s('betrieb_status_' . $b['betrieb_status_id']) . '</span>' . $count_info) . '
			' . $this->viewUtils->v_input_wrapper('Verantwortliche Foodsaver', $verantwortlich, 'bcntverantwortlich') . '
			' . $besonderheiten . '
			<div class="ui-padding">
				' . $this->viewUtils->v_info('' . $this->translationHelper->s('team_status_' . $b['team_status']) . '') . '		
			</div>
			' . $button;
	}

	public function childBezirke($childs, $parent_id)
	{
		$out = '
	<select class="select childChanger" id="xv-childbezirk-' . (int)$parent_id . '" onchange="u_printChildBezirke(this);">
		<option value="-1:0" class="xv-childs-0">Bitte auswählen...</option>';
		foreach ($childs as $c) {
			$out .= '
		<option value="' . $c['id'] . ':' . (int)$c['type'] . '" class="xv-childs-' . $c['id'] . '">' . $c['name'] . '</option>';
		}
		$out .= '
	</select>';

		return $out;
	}

	public function set($rows, $title = '')
	{
		$out = '
	<div class="xv_set">
		<h3>' . $title . '</h3>';
		foreach ($rows as $r) {
			$out .= '
		<div class="xv_row">
			<span class="xv_label">' . $r['name'] . '</span><span class="xv_val">' . $r['val'] . '</span>
		</div>';
		}

		return $out . '
	</div>';
	}
}
