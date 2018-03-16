<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Modules\Core\View;
use Foodsharing\Lib\Session\S;

class WorkGroupView extends View
{
	public function leftNavi($countrys, $bezirke)
	{
		$out = '';

		// Überregionale
		$items = array();
		$items[] = array('name' => 'Alle anzeigen', 'href' => '/?page=groups');
		$out .= $this->v_utils->v_field($this->v_utils->v_menu($items), 'Überregionale Gruppen');

		// Lokale Gruppen
		$items = array();
		if (is_array($bezirke)) {
			foreach ($bezirke as $b) {
				if ($b['type'] != 7 && $b['type'] != 6) {
					$items[] = array('name' => 'Gruppen für ' . $b['name'], 'href' => '/?page=groups&p=' . $b['id']);
				}
			}
		}

		$out .= $this->v_utils->v_field($this->v_utils->v_menu($items), 'Lokalgruppen');

		// Ländegruppen
		$items = array();
		foreach ($countrys as $c) {
			$items[] = array('name' => 'Gruppen für ' . $c['name'], 'href' => '/?page=groups&p=' . $c['id']);
		}
		$out .= $this->v_utils->v_field($this->v_utils->v_menu($items), 'Länderspezifische Gruppen');

		/*
		 * Deine Bezirke
		*/
		$orgacheck = false;
		$orga = '';
		if (isset($_SESSION['client']['bezirke'])) {
			$orga = '
		<ul class="linklist">';
			foreach ($_SESSION['client']['bezirke'] as $b) {
				if ($b['type'] == 7) {
					$orgacheck = true;
					$orga .= '
			<li><a class="ui-corner-all" href="/?page=bezirk&bid=' . $b['id'] . '&sub=forum">' . $b['name'] . '</a></li>';
				}
			}
			$orga .= '
		</ul>';
		}

		if ($orgacheck) {
			$out .= $this->v_utils->v_field($orga, 'Deine Gruppen', array('class' => 'ui-padding'));
		}

		return $out;
	}

	public function listGroups($groups, $myapps, $mystats)
	{
		$this->func->addJs('
				$(".fancybox").fancybox();
				/*
				$contents = $(".groups .field div.ui-widget.ui-widget-content");
				$contents.css({
					"overflow":"hidden"
				});
				
				$contents.animate({
					height:10,
					padding:0
				});
				*/
				
		');
		$out = '
		<div class="groups">';

		foreach ($groups as $g) {
			$group = '';
			if ($g['leader']) {
				shuffle($g['leader']);
				$member = '
			<div class="members">
				';

				$max = 16;
				foreach ($g['leader'] as $m) {
					--$max;
					$member .= '
				<a class="member" href="#" onclick="profile(' . (int)$m['id'] . ');return false;"><img src="' . $this->func->img($m['photo']) . '" alt="' . $m['name'] . '" /></a>';
					if ($max == 0) {
						break;
					}
				}

				$member .= '
				<div><strong>' . count($g['leader']) . ' Admin/s</strong></div>
				<div>' . count($g['member']) . ' Mitwirkende</div>
				
			</div>';

				$group .= $member;
			}

			$photo = '';
			if (!empty($g['photo'])) {
				$photo .= '
			<div class="photo">
				<a class="fancybox" href="' . $this->img($g['photo'], '') . '"><img src="' . $this->img($g['photo']) . '" alt="' . $g['name'] . ' Titelbild" /></a>
			</div>';
			}

			$group .= $photo;

			$btn = '<a class="button" href="#" onclick="ajreq(\'contactgroup\',{id:' . (int)$g['id'] . '});return false;">Gruppe kontaktieren</a>';

			$info = '';

			if ($this->func->isOrgaTeam() || $this->func->isBotFor($g['id']) || $this->func->isBotFor($g['parent_id'])) {
				$btn .= '<a class="button" href="/?page=groups&sub=edit&id=' . $g['id'] . '">Gruppe bearbeiten</a>';
			}

			if ($this->func->mayBezirk($g['id']) || $this->func->isBotFor($g['parent_id'])) {
				$btn .= '<a class="button" href="/?page=bezirk&bid=' . $g['id'] . '">Zur Gruppe</a>';
			}

			if (isset($myapps[$g['id']])) {
				$info = '<div class="ui-padding">' . $this->v_utils->v_info('Für diese Gruppe hast Du Dich bereits beworben') . '</div>';
			} elseif (!$this->func->hasBezirk($g['id'])) {
				if ($this->canApply($g, $mystats)) {
					$btn .= '<a class="button" href="#" onclick="ajreq(\'apply\',{id:' . $g['id'] . '});">Für diese Arbeitsgruppe bewerben</a>';
				} elseif ($g['apply_type'] == 3) {
					$btn .= '<a class="button" href="#" onclick="ajreq(\'addtogroup\',{id:' . $g['id'] . '});">Dieser Arbeitsgruppe beitreten</a>';
				} elseif ($g['apply_type'] == 1 && !S::may('orga')) {
					$info .= $this->v_utils->v_info('
						Für Diese Arbeitsgruppe kannst Du Dich mit ' . $g['banana_count'] . ' Vertrauensbananen und ' . $g['fetch_count'] . ' Abholungen bewerben sofern Du schon ' . $g['week_num'] . ' Wochen als Foodsaver dabei bist		
					') . '<div style="margin-bottom:5px;"></div>';
				}
			}

			$group .= '
			<div class="teaser">
				' . nl2br($g['teaser']) . '
				<p style="margin-top:15px;"><strong>' . $g['email'] . '</strong></p>
			</div>
				
			<div class="clear"></div>
			<div class="bottom_bar">
				' . $info . '
				<div class="float_right">
					' . $btn . '
				</div>
				<div class="clear"></div>
			</div>';

			$out .= $this->v_utils->v_field($group, $g['name'], array('class' => 'ui-padding'));
		}

		$out .= '
		</div>';

		return $out;
	}

	public function canApply($group, $mystats)
	{
		if ($group['apply_type'] == 0) {
			return false;
		}

		// apply_type

		if ($group['apply_type'] == 1) {
			if (
				$mystats['bananacount'] >= $group['banana_count'] &&
				$mystats['fetchcount'] >= $group['fetch_count'] &&
				$mystats['weeks'] >= $group['week_num']
			) {
				if ((int)$group['report_num'] == 0 && (int)$mystats['reports'] > 0) {
					return false;
				}

				return true;
			}
		} elseif ($group['apply_type'] == 2) {
			return true;
		}

		return false;
	}

	public function applyForm($group)
	{
		return $this->v_utils->v_form('apply', array(
			$this->v_utils->v_form_textarea('motivation', ['value' => '', 'label' => 'Was ist Deine Motivation, in der Gruppe ' . $group['name'] . ' mitzuwirken?']),
			$this->v_utils->v_form_textarea('faehigkeit', ['value' => '', 'label' => 'Was sind Deine Fähigkeiten, die Du in diesem Bereich hast?']),
			$this->v_utils->v_form_textarea('erfahrung', ['value' => '', 'label' => 'Kannst Du in der Gruppe auf Erfahrungen, die Du woanders gesammelt hast zurückgreifen? Wenn ja, wo bzw. was?']),
			$this->v_utils->v_form_select('zeit', array('selected' => '', 'label' => 'Wie viele Stunden hast Du pro Woche Zeit und Lust dafür aufzuwenden?', 'values' => array(
				array('id' => '1-2 Stunden', 'name' => '1-2 Stunden'),
				array('id' => '2-3 Stunden', 'name' => '2-3 Stunden'),
				array('id' => '3-4 Stunden', 'name' => '3-4 Stunden'),
				array('id' => '5 oder mehr Stunden', 'name' => '5 oder mehr Stunden')
			)))
		), array('submit' => false));
	}

	public function editGroup($group)
	{
		if ($group['apply_type'] != 1) {
			$this->func->addJs('$("#addapply").hide();');
		}

		$this->func->addJs('
			$("#apply_type").change(function(){
				if($(this).val() == 1)
				{
					$("#addapply").show();
				}
				else
				{
					$("#addapply").hide();
				}
			});		
		');

		$out = '';

		$this->func->setEditData($group);

		$basics = $this->v_utils->v_form_text('name') .
			$this->v_utils->v_form_textarea('teaser') .
			$this->v_utils->v_form_picture('photo', array('resize' => array(528, 60, 128), 'crop' => array((528 / 350), 1)));

		$apply = $this->v_utils->v_form_select('apply_type', array(
				'values' => array(
					array('id' => 0, 'name' => 'Niemand (geschlossene Gruppe)'),
					array('id' => 1, 'name' => 'Jeder, der bestimmte Vertrauenspunkte erfüllt'),
					array('id' => 2, 'name' => 'Jeder darf sich bewerben'),
					array('id' => 3, 'name' => 'Jeder kann sich ohne Bewerbung einklinken')
				)
			)) .
			'<div id="addapply">' .
			$this->v_utils->v_form_text('banana_count') .
			$this->v_utils->v_form_text('fetch_count') .
			$this->v_utils->v_form_text('week_num') .
			$this->v_utils->v_form_checkbox('report_num', array('values' => array(
				array('id' => 1, 'name' => 'Ja, auch Foodsaver mit Verstoßmeldungen können sich bewerben.')
			))) .
			'</div>';

		$out .= $this->v_utils->v_form('editgroup', array(
			$this->v_utils->v_field($basics, $group['name'] . ' bearbeiten', array('class' => 'ui-padding')),
			$this->v_utils->v_field($apply, 'Bewerbungen', array('class' => 'ui-padding')),
			$this->v_utils->v_field($this->v_utils->v_form_tagselect('member', array('xhr' => 'recip')), $this->func->s('member'), array('class' => 'ui-padding')),
			$this->v_utils->v_field($this->v_utils->v_form_tagselect('leader', array('xhr' => 'recip')), $this->func->s('leader'), array('class' => 'ui-padding'))
		), array('submit' => 'Änderungen speichern'));

		return $out;
	}

	private function img($img, $prefix = 'crop_1_128_')
	{
		return 'images/' . str_replace('/', '/' . $prefix, $img);
	}

	public function contactgroup($group)
	{
		$head = '';

		if ($group['leader']) {
			foreach ($group['leader'] as $gl) {
				$head .= '<a style="margin:4px 4px 0 0;" onclick="profile(' . (int)$gl['id'] . ');return false;" href="#" class="member"><img alt="' . $gl['name'] . '" src="' . $this->func->img($gl['photo']) . '"></a>';
			}
			$head = $this->v_utils->v_input_wrapper(count($group['leader']) . ' Ansprechpartner', $head);
		}

		$head .= $this->v_utils->v_field($this->func->s('contact-disclaimer'));

		return $head . $this->v_utils->v_form_textarea('message');
	}
}
