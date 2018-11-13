<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Modules\Core\View;

class StoreView extends View
{
	public function dateForm()
	{
		return
			'<div id="datepicker" style="height:195px;"></div>' .
			$this->v_utils->v_input_wrapper('Uhrzeit', $this->v_utils->v_form_time('time')) .
			$this->v_utils->v_form_select('fetchercount', array('values' => array(
				array('id' => 0, 'name' => 'Termin fällt aus'),
				array('id' => 1, 'name' => '1 Abholer/in'),
				array('id' => 2, 'name' => '2 Abholer/innen'),
				array('id' => 3, 'name' => '3 Abholer/innen'),
				array('id' => 4, 'name' => '4 Abholer/innen'),
				array('id' => 5, 'name' => '5 Abholer/innen'),
				array('id' => 6, 'name' => '6 Abholer/innen'),
				array('id' => 7, 'name' => '7 Abholer/innen'),
				array('id' => 8, 'name' => '8 Abholer/innen')
			)));
	}

	public function fetchHistory()
	{
		return $this->v_utils->v_form_daterange('daterange', array(
				'content_after' => ' <a href="#" id="daterange_submit" class="button"><i class="fas fa-search"></i></a>'
			)) . '<div id="daterange_content"></div>';
	}

	public function fetchlist($history)
	{
		$out = '
			<ul class="linklist history">';
		$curdate = 0;
		foreach ($history as $h) {
			if ($curdate != $h['date']) {
				$out .= '<li class="title">' . $this->func->niceDate($h['date_ts']) . '</li>';
				$curdate = $h['date'];
			}
			$out .= '
				<li>
					<a class="corner-all" href="/profile/' . (int)$h['id'] . '">
						<span class="i"><img src="' . $this->func->img($h['photo']) . '" /></span>
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

	public function betrieb_form($bezirk = false, $page = '', $lebensmittel_values, $chains, $categories, $status)
	{
		global $g_data;

		$bc = $this->v_utils->v_bezirkChooser('bezirk_id', $bezirk);

		if (!isset($g_data['foodsaver'])) {
			$g_data['foodsaver'] = array($this->func->fsId());
		}

		$this->func->addJs('
			$("#lat-wrapper").hide();
			$("#lon-wrapper").hide();
		');

		$first_post = '';
		if ($this->func->getAction('new')) {
			$first_post = $this->v_utils->v_form_textarea('first_post', array('required' => true));
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

		$this->func->addJs('$("textarea").css("height","70px");$("textarea").autosize();');

		$latLonOptions = [];

		foreach (['anschrift', 'plz', 'ort', 'lat', 'lon'] as $i) {
			if (isset($g_data[$i])) {
				$latLonOptions[$i] = $g_data[$i];
			}
		}
		if (isset($g_data['lat'], $g_data['lon'])) {
			$latLonOptions['location'] = ['lat' => $g_data['lat'], 'lon' => $g_data['lon']];
		}

		return $this->v_utils->v_quickform('betrieb', array(
			$bc,
			$this->v_utils->v_form_hidden('page', $page),
			$this->v_utils->v_form_text('name', ['required' => true]),
			$this->latLonPicker('LatLng', $latLonOptions),

			$this->v_utils->v_form_select('kette_id', array('add' => true, 'values' => $chains, 'desc' => 'Bitte nur inhabergeführte Betriebe selbstständig ansprechen, niemals Betriebe einer Kette anfragen!')),
			$this->v_utils->v_form_select('betrieb_kategorie_id', array('add' => true, 'values' => $categories)),

			$this->v_utils->v_form_select('betrieb_status_id', array('values' => $status)),

			$this->v_utils->v_form_text('ansprechpartner'),
			$this->v_utils->v_form_text('telefon'),
			$this->v_utils->v_form_text('fax'),
			$this->v_utils->v_form_text('email'),

			$this->v_utils->v_form_checkbox('lebensmittel', array('values' => $lebensmittel_values)),
			$this->v_utils->v_form_date('begin'),
			$this->v_utils->v_form_textarea('besonderheiten'),
			$this->v_utils->v_form_textarea('public_info', array('maxlength' => 180, 'desc' => 'Hier kannst Du einige Infos für die Foodsaver angeben, die sich für das Team bewerben möchten. <br />(max. 180 Zeichen)<div>' . $this->v_utils->v_info('<strong>Wichtig</strong>: Gib hier keine genauen Abholzeiten an.<br />Es ist des Öfteren vorgekommen, dass Leute unabgesprochen zum Laden gegangen sind.') . '</div>')),
			$this->v_utils->v_form_select('public_time', ['values' => [
				['id' => 0, 'name' => 'Keine Angabe'],
				['id' => 1, 'name' => 'morgens'],
				['id' => 2, 'name' => 'mittags/nachmittags'],
				['id' => 3, 'name' => 'abends'],
				['id' => 4, 'name' => 'nachts']
			]]),
			$first_post,
			$this->v_utils->v_form_select('ueberzeugungsarbeit', array('values' => array(
				array('id' => 1, 'name' => 'Überhaupt kein Problem, er/sie war/en sofort begeistert!'),
				array('id' => 2, 'name' => 'Nach einiger Überzeugungsarbeit erklärte er/sie sich bereit mitzumachen '),
				array('id' => 3, 'name' => 'Ganz schwierig, aber am Ende hat er/sie eingewilligt'),
				array('id' => 4, 'name' => 'Zuerst sah es so aus, als ob er/sie nicht mitmachen wollte, aber dann hat sie/er sich doch bei mir gemeldet')
			))),
			$this->v_utils->v_form_select('presse', array('values' => array(
				array('id' => 1, 'name' => 'Ja'),
				array('id' => 0, 'name' => 'Nein')
			))),
			$this->v_utils->v_form_select('sticker', array('values' => array(
				array('id' => 1, 'name' => 'Ja'),
				array('id' => 0, 'name' => 'Nein')
			))),
			$this->v_utils->v_form_select('prefetchtime', array('values' => array(
				array('id' => 1209600, 'name' => '2 Wochen'),
				array('id' => 1814400, 'name' => '3 Wochen'),
				array('id' => 2419200, 'name' => '4 Wochen')
			))),
			$this->v_utils->v_form_select('abholmenge', ['values' => [
				['id' => 1, 'name' => '1-3 kg'],
				['id' => 2, 'name' => '3-5 kg'],
				['id' => 3, 'name' => '5-10 kg'],
				['id' => 4, 'name' => '10-20 kg'],
				['id' => 5, 'name' => '20-30 kg'],
				['id' => 6, 'name' => '40-50 kg'],
				['id' => 7, 'name' => 'mehr als 50 kg']
			]])
		));
	}
}
