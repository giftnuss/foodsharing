<?php

namespace Foodsharing\Modules\StoreUser;

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Helpers\PageHelper;
use Foodsharing\Helpers\RouteHelper;
use Foodsharing\Helpers\TimeHelper;
use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Lib\Session;
use Foodsharing\Lib\View\Utils;
use Foodsharing\Modules\Core\View;
use Foodsharing\Services\ImageService;
use Foodsharing\Services\SanitizerService;

class StoreUserView extends View
{
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
		TranslationHelper $translationHelper
	) {
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

	public function u_getVerantwortlicher($betrieb)
	{
		$out = array();
		foreach ($betrieb['foodsaver'] as $fs) {
			if ($fs['verantwortlich'] == 1) {
				$out[] = $fs;
			}
		}

		return $out;
	}

	public function handleRequests($betrieb)
	{
		$out = '<table class="pintable">';
		$odd = 'odd';
		$this->pageHelper->addJs('$("table.pintable tr td ul li").tooltip();');

		foreach ($betrieb['requests'] as $r) {
			if ($odd == 'even') {
				$odd = 'odd';
			} else {
				$odd = 'even';
			}
			$out .= '
		<tr class="' . $odd . ' request-' . $r['id'] . '">
			<td class="img" width="35px"><a href="/profile/' . (int)$r['id'] . '"><img src="' . $this->imageService->img($r['photo']) . '" /></a></td>
			<td style="padding-top:17px;"><span class="msg"><a href="/profile/' . (int)$r['id'] . '">' . $r['name'] . '</a></span></td>
			<td style="width:92px;padding-top:17px;"><span class="msg"><ul class="toolbar"><li class="ui-state-default ui-corner-left" title="Ablehnen" onclick="denyRequest(' . (int)$r['id'] . ',' . (int)$betrieb['id'] . ');"><span class="ui-icon ui-icon-closethick"></span></li><li class="ui-state-default" title="Auf die Springerliste setzen" onclick="warteRequest(' . (int)$r['id'] . ',' . (int)$betrieb['id'] . ');"><span class="ui-icon ui-icon-star"></span></li><li class="ui-state-default ui-corner-right" title="Akzeptieren" onclick="acceptRequest(' . (int)$r['id'] . ',' . (int)$betrieb['id'] . ');"><span class="ui-icon ui-icon-heart"></span></li></ul></span></td>
		</tr>';
		}

		$out .= '</table>';

		$this->pageHelper->hiddenDialog('requests', array($out));
		$this->pageHelper->addJs('$("#dialog_requests").dialog("option","title","Anfragen für ' . $this->sanitizerService->jsSafe($betrieb['name'], '"') . '");');
		$this->pageHelper->addJs('$("#dialog_requests").dialog("option","buttons",{});');
		$this->pageHelper->addJs('$("#dialog_requests").dialog("open");');
	}

	public function u_innerRow($contentType, $betrieb)
	{
		$out = '';
		if ($betrieb[$contentType] != '') {
			$betrieb[$contentType] = trim($betrieb[$contentType]);
			nl2br($betrieb[$contentType]);

			if (($contentType == 'telefon' || $contentType == 'handy') && strpbrk($betrieb[$contentType], '1234567890')) {
				$phoneNumber = preg_replace('/[^0-9\+]/', '', $betrieb[$contentType]);

				$content = '<a href="tel:' . $phoneNumber . '">' . $betrieb[$contentType] . '</a>';
			} else {
				$content = $betrieb[$contentType];
			}

			$out = '<div class="innerRow"><span class="label">' . $this->translationHelper->s($contentType) . '</span>
			<span class="cnt">' . $content . '</span></div><div style="clear:both"></div>';
		}

		return $out;
	}

	public function u_team($betrieb)
	{
		$id = $this->identificationHelper->id('team');
		$out = '<ul id="' . $id . '" class="team">';
		$jssaver = array();
		$sleeper = '';

		foreach ($betrieb['foodsaver'] as $fs) {
			$jssaver[] = (int)$fs['id'];

			$class = '';
			$click = 'profile(' . (int)$fs['id'] . ');';
			if ($fs['verantwortlich'] == 1) {
				$class .= ' verantwortlich';
			} elseif ($betrieb['verantwortlich'] || $this->session->isAdminFor($betrieb['bezirk_id']) || $this->session->isOrgaTeam()) {
				$class .= ' context-team';
				$click = '';
			}

			if ($fs['verified'] != 1) {
				$class .= ' notVerified';
			}

			$tel = '';
			$number = false;
			if (!empty($fs['handy'])) {
				$number = $fs['handy'];
				$tel .= '<span class="item phone">' . ($this->session->isMob() ? '<a href="tel:' . $fs['handy'] . '"><span>' . $fs['handy'] . '</span></a>' : $fs['handy']) . '</span>';
			}
			if (!empty($fs['telefon'])) {
				$tel .= '<span class="item phone">' . ($this->session->isMob() ? '<a href="tel:' . $fs['telefon'] . '"><span>' . $fs['telefon'] . '</span></a>' : $fs['telefon']) . '</span>';
			}

			if ((int)$fs['last_fetch'] > 0) {
				$last = $this->translationHelper->sv('stat_fetchcount', array(
					'date' => date('d.m.Y', $fs['last_fetch'])
				));
			} else {
				$last = $this->translationHelper->sv('stat_fetchcount_none', array());
			}

			//date at which user was added
			$memberSince = '';
			if ($betrieb['verantwortlich']) {
				$addedAt = (!is_null($fs['add_date']) && $fs['add_date'] > 0)
						? date('d.m.Y', $fs['add_date'])
						: '(' . $this->translationHelper->s('stat_since_unknown') . ')';
				$memberSince = $this->translationHelper->sv('stat_teammember_since', array(
					'date' => $addedAt
				));
			}

			$onclick = ' onclick="' . $click . 'return false;"';
			$href = '#';
			if ($number !== false && $this->session->isMob()) {
				$onclick = '';
				$href = 'tel:' . preg_replace('/[^0-9\+]/', '', $number);
			}

			$tmp = '
				<li class="team fs-' . $fs['id'] . '">
					<a class="ui-corner-all' . $class . '" title="#tt-tt-' . $fs['id'] . '" href="' . $href . '"' . $onclick . '>
						' . $this->imageService->avatar($fs) . '
						<span class="infos">
							<span class="item"><strong>' . $fs['name'] . '</strong> <span style="float:right">(' . $fs['stat_fetchcount'] . ')</span></span>
							' . $tel . '
						</span>
					</a>
					<span style="display:none" class="tt-' . $fs['id'] . '">
						' . (!empty($memberSince) ? $memberSince . '<br>' : '') . $last . '
					</span>
				</li>';

			if ($fs['sleep_status'] == 0) {
				$out .= $tmp;
			} else {
				$sleeper .= $tmp;
			}
		}

		if ($betrieb['springer']) {
			foreach ($betrieb['springer'] as $fs) {
				$jssaver[] = (int)$fs['id'];

				$class = '';
				$click = 'profile(' . (int)$fs['id'] . ');';
				if ($betrieb['verantwortlich'] || $this->session->isAdminFor($betrieb['bezirk_id']) || $this->session->isOrgaTeam()) {
					$class .= ' context-jumper';
					$click = '';
				}

				$tel = '';
				$number = false;
				if (!empty($fs['handy'])) {
					$tel .= '<span class="item phone"><span>' . $fs['handy'] . '</span></span>';
				}
				if (!empty($fs['telefon'])) {
					$tel .= '<span class="item phone"><span>' . $fs['telefon'] . '</span></span>';
				}

				//date at which jumper was added
				$addedAt = (!is_null($fs['add_date']) && $fs['add_date'] > 0)
						? date('d.m.Y', $fs['add_date'])
						: '(' . $this->translationHelper->s('stat_since_unknown') . ')';
				$jumperSince = $this->translationHelper->sv('stat_jumper_since', array(
					'date' => $addedAt
				));

				$onclick = ' onclick="' . $click . 'return false;"';
				$href = '#';
				if ($number !== false && $this->session->isMob()) {
					$onclick = '';
					$href = 'tel:' . preg_replace('/[^0-9\+]/', '', $number);
				}

				$tmp = '
					<li class="jumper fs-' . $fs['id'] . '">
						<a class="ui-corner-all' . $class . '" title="#tt-tt-' . $fs['id'] . '" href="' . $href . '"' . $onclick . '>
							' . $this->imageService->avatar($fs) . '
							<span class="infos">
								<span class="item"><strong>' . $fs['name'] . '</strong></span>
								' . $tel . '
							</span>
						</a>
						<span style="display:none" class="tt-' . $fs['id'] . '">
							' . $jumperSince . '
						</span>
					</li>';

				if ($fs['sleep_status'] == 0) {
					$out .= $tmp;
				} else {
					$sleeper .= $tmp;
				}
			}
		}

		$out .= $sleeper . '</ul><div style="clear:both"></div>';

		if ($betrieb['verantwortlich']) {
			$this->pageHelper->addJs('
			$("#team_status").on("change", function(){
				var val = $(this).val();
				showLoader();
				$.ajax({
					url: "/xhr.php?f=bteamstatus&bid=' . (int)$betrieb['id'] . '&status=" + val,
					success: function(){
						hideLoader();
					}
				});
			});		
		');
			global $g_data;
			$g_data['team_status'] = $betrieb['team_status'];

			$out .= '
			<div class="ui-padding">' .
				$this->v_utils->v_form_select('team_status', array(
					'values' => array(
						array('id' => 0, 'name' => 'Team ist voll'),
						array('id' => 1, 'name' => 'HelferInnen gesucht'),
						array('id' => 2, 'name' => 'Es werden dringend HelferInnen gesucht!')
					)
				)) . '</div>';
		}

		return $out;
	}

	public function u_betriebList($betriebe, $title, $verantwortlich)
	{
		if (empty($betriebe)) {
			return '';
		}

		$bezirk = false;
		$betriebrows = array();
		foreach ($betriebe as $i => $b) {
			$status = $this->v_utils->v_getStatusAmpel($b['betrieb_status_id']);

			$betriebrows[$i] = array(
				array('cnt' => '<a class="linkrow ui-corner-all" href="/?page=fsbetrieb&id=' . $b['id'] . '">' . $b['name'] . '</a>'),
				array('cnt' => $b['str'] . ' ' . $b['hsnr']),
				array('cnt' => $b['plz']),
				array('cnt' => $status)
			);

			if (isset($b['bezirk_name'])) {
				$betriebrows[$i][] = array('cnt' => $b['bezirk_name']);
				$bezirk = true;
			}

			if ($verantwortlich) {
				$betriebrows[$i][] = array('cnt' => $this->v_utils->v_toolbar(array('id' => $b['id'], 'types' => array('edit'), 'confirmMsg' => 'Soll ' . $b['name'] . ' wirklich unwiderruflich gel&ouml;scht werden?')));
			}
		}

		$head = array(
			array('name' => 'Name', 'width' => 180),
			array('name' => 'Anschrift'),
			array('name' => 'Postleitzahl', 'width' => 90),
			array('name' => 'Status', 'width' => 50));
		if ($bezirk) {
			$head[] = array('name' => 'Region');
		}
		if ($verantwortlich) {
			$head[] = array('name' => 'Aktionen', 'sort' => false, 'width' => 30);
		}

		$table = $this->v_utils->v_tablesorter($head, $betriebrows);

		return $this->v_utils->v_field($table, $title);
	}

	public function format_db_date($date): string
	{
		$part = explode('-', $date);

		return (int)$part[2] . '. ' . $this->translationHelper->s('month_' . (int)$part[1]);
	}

	public function u_form_abhol_table($zeiten = false, $option = array())
	{
		$out = '
		<table class="timetable">
			
			<thead>
				<tr>
					<th class="ui-padding">' . $this->translationHelper->s('day') . '</th>
					<th class="ui-padding">' . $this->translationHelper->s('time') . '</th>
					<th class="ui-padding">' . $this->translationHelper->s('fetcher_count') . '</th>
				</tr>
			</thead>
			<tfoot>
			    <tr>
					<td colspan="3"><span id="nft-add">' . $this->translationHelper->s('add') . '</span></td>
				</tr>
			</tfoot>
			<tbody>';
		$dows = range(1, 6);
		$dows[] = 0;
		$odd = 'even';
		if (is_array($zeiten)) {
			foreach ($zeiten as $z) {
				if ($odd == 'even') {
					$odd = 'odd';
				} else {
					$odd = 'even';
				}

				$day = '';
				foreach ($dows as $d) {
					$sel = '';
					if ($d == $z['dow']) {
						$sel = ' selected="selected"';
					}
					$day .= '<option' . $sel . ' value="' . $d . '">' . $this->translationHelper->s('dow' . $d) . '</option>';
				}

				$time = explode(':', $z['time']);

				$out .= '
			    <tr class="' . $odd . '">
			        <td class="ui-padding">
					    <select class="nft-row" style="width:100px; float: left" name="newfetchtime[]" id="nft-dow">' . $day . '</select>
                    </td>
                    <td class="ui-padding">
                        <select class="nfttime-hour" style="float: left;" name="nfttime[hour][]">
                            <option selected="selected" value="' . (int)$time[0] . '">' . $time[0] . '</option>
                            <option value="0">00</option><option value="1">01</option><option value="2">02</option>
                            <option value="3">03</option><option value="4">04</option><option value="5">05</option>
                            <option value="6">06</option><option value="7">07</option><option value="8">08</option>
                            <option value="9">09</option><option value="10">10</option><option value="11">11</option>
                            <option value="12">12</option><option value="13">13</option><option value="14">14</option>
                            <option value="15">15</option><option value="16">16</option><option value="17">17</option>
                            <option value="18">18</option><option value="19">19</option><option value="20">20</option>
                            <option value="21">21</option><option value="22">22</option><option value="23">23</option>
                        </select>
                            <select class="nfttime-min" name="nfttime[min][]">
                            <option selected="selected" value="' . (int)$time[1] . '">' . $time[1] . '</option>
                            <option value="0">00</option><option value="5">05</option><option value="10">10</option>
                            <option value="15">15</option><option value="20">20</option><option value="25">25</option>
                            <option value="30">30</option><option value="35">35</option><option value="40">40</option>
                            <option value="45">45</option><option value="50">50</option><option value="55">55</option>
                        </select>
                    </td>
                    <td class="ui-padding" style="width:100px">
                        <input class="fetchercount" style="width:20px; float: left" type="text" name="nft-count[]" value="' . $z['fetcher'] . '"/>
                        <button style="float: right; height: 32px" class="nft-remove"></button>
                    </td>
			    </tr>';
			}
		}
		$out .= '</tbody></table>';

		$out .= '<table id="nft-hidden-row" style="display:none;">
			<tbody>
                <tr>
                    <td class="ui-padding">
                        <select class="nft-row" style="width:100px;" name="newfetchtime[]" id="nft-dow">
                            <option value="0">' . $this->translationHelper->s('dow0') . '</option>	
                            <option value="1">' . $this->translationHelper->s('dow1') . '</option>	
                            <option value="2">' . $this->translationHelper->s('dow2') . '</option>	
                            <option value="3">' . $this->translationHelper->s('dow3') . '</option>	
                            <option value="4">' . $this->translationHelper->s('dow4') . '</option>	
                            <option value="5">' . $this->translationHelper->s('dow5') . '</option>	
                            <option value="6">' . $this->translationHelper->s('dow6') . '</option>		
                        </select>
                    </td>
                    <td class="ui-padding">
                        <select class="nfttime-hour" name="nfttime[hour][]">
                            <option value="0">00</option><option value="1">01</option><option value="2">02</option><option value="3">03</option>
                            <option value="4">04</option><option value="5">05</option><option value="6">06</option><option value="7">07</option>
                            <option value="8">08</option><option value="9">09</option><option value="10">10</option><option value="11">11</option>
                            <option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option>
                            <option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option>
                            <option value="20" selected="selected">20</option><option value="21">21</option><option value="22">22</option>
                            <option value="23">23</option>
                        </select>
                        <select class="nfttime-min" name="nfttime[min][]">
                            <option value="0" selected="selected">00</option><option value="5">05</option><option value="10">10</option>
                            <option value="15">15</option><option value="20">20</option><option value="25">25</option><option value="30">30</option>
                            <option value="35">35</option><option value="40">40</option><option value="45">45</option><option value="50">50</option>
                            <option value="55">55</option>
                        </select></td>
                    <td class="ui-padding">
                        <input class="fetchercount" type="text" name="nft-count[]" style="width:25px" value="2"/>
                        <button style="float: right; height: 27px"class="nft-remove"></button>
                    </td>
                </tr>
			</tbody>
			</table>';

		return $out;
	}
}
