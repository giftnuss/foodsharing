<?php

namespace Foodsharing\Modules\StoreUser;

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
use Symfony\Contracts\Translation\TranslatorInterface;

class StoreUserView extends View
{
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
		TranslatorInterface $translator
	) {
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

	public function u_getVerantwortlicher($storeData)
	{
		$out = [];
		foreach ($storeData['foodsaver'] as $fs) {
			if ($fs['verantwortlich'] == 1) {
				$out[] = $fs;
			}
		}

		return $out;
	}

	public function handleRequests($storeData)
	{
		$out = '<table class="pintable">';
		$odd = 'odd';
		$this->pageHelper->addJs('$("table.pintable tr td ul li").tooltip();');

		foreach ($storeData['requests'] as $r) {
			if ($odd == 'even') {
				$odd = 'odd';
			} else {
				$odd = 'even';
			}
			$verificationStatus = $r['verified'] ? '<i class="fas fa-user-check" title="' . $this->translationHelper->s('user_is_verified') . '"></i> ' : '';
			$out .= '
		<tr class="' . $odd . ' request-' . $r['id'] . '">
			<td class="img" width="35px"><a href="/profile/' . (int)$r['id'] . '"><img src="' . $this->imageService->img($r['photo']) . '" /></a></td>
			<td style="padding-top:17px;"><span class="msg">' . $verificationStatus . '<a href="/profile/' . (int)$r['id'] . '">' . $r['name'] . '</a></span></td>
			<td style="width:92px;padding-top:17px;"><span class="msg"><ul class="toolbar"><li class="ui-state-default ui-corner-left" title="Ablehnen" onclick="denyRequest(' . (int)$r['id'] . ',' . (int)$storeData['id'] . ');"><span class="ui-icon ui-icon-closethick"></span></li><li class="ui-state-default" title="Auf die Springerliste setzen" onclick="warteRequest(' . (int)$r['id'] . ',' . (int)$storeData['id'] . ');"><span class="ui-icon ui-icon-star"></span></li><li class="ui-state-default ui-corner-right" title="Akzeptieren" onclick="acceptRequest(' . (int)$r['id'] . ',' . (int)$storeData['id'] . ');"><span class="ui-icon ui-icon-heart"></span></li></ul></span></td>
		</tr>';
		}

		$out .= '</table>';

		$this->pageHelper->hiddenDialog('requests', [$out]);
		$this->pageHelper->addJs('$("#dialog_requests").dialog("option","title","Anfragen für ' . $this->sanitizerService->jsSafe($storeData['name'], '"') . '");');
		$this->pageHelper->addJs('$("#dialog_requests").dialog("option","buttons",{});');
		$this->pageHelper->addJs('$("#dialog_requests").dialog("open");');
	}

	public function u_legacyStoreTeamStatus(array $storeData): string
	{
		$this->pageHelper->addJs('
			$("#team_status").on("change", function(){
				var val = $(this).val();
				showLoader();
				$.ajax({
					url: "/xhr.php?f=bteamstatus&bid=' . (int)$storeData['id'] . '&status=" + val,
					success: function() { hideLoader(); }
				});
			});
		');

		global $g_data;
		$g_data['team_status'] = $storeData['team_status'];

		$out = $this->v_utils->v_form_select('team_status', [
			'values' => [
				['id' => 0, 'name' => 'Team ist voll'],
				['id' => 1, 'name' => 'HelferInnen gesucht'],
				['id' => 2, 'name' => 'Es werden dringend HelferInnen gesucht!']
			]
		]);

		return $out;
	}

	public function u_storeList($storeData, $title)
	{
		if (empty($storeData)) {
			return '';
		}

		$isRegion = false;
		$storeRows = [];
		foreach ($storeData as $i => $store) {
			$status = $this->v_utils->v_getStatusAmpel($store['betrieb_status_id']);

			$storeRows[$i] = [
				['cnt' => '<a class="linkrow ui-corner-all" href="/?page=fsbetrieb&id=' . $store['id'] . '">' . $store['name'] . '</a>'],
				['cnt' => $store['str'] . ' ' . $store['hsnr']],
				['cnt' => $store['plz']],
				['cnt' => $status]
			];

			if (isset($store['bezirk_name'])) {
				$storeRows[$i][] = ['cnt' => $store['bezirk_name']];
				$isRegion = true;
			}
		}

		$head = [
			['name' => 'Name', 'width' => 180],
			['name' => 'Anschrift'],
			['name' => 'Postleitzahl', 'width' => 90],
			['name' => 'Status', 'width' => 50]];
		if ($isRegion) {
			$head[] = ['name' => 'Region'];
		}

		$table = $this->v_utils->v_tablesorter($head, $storeRows);

		return $this->v_utils->v_field($table, $title);
	}

	public function u_form_abhol_table($allDates = false, $option = [])
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
		if (is_array($allDates)) {
			foreach ($allDates as $date) {
				if ($odd == 'even') {
					$odd = 'odd';
				} else {
					$odd = 'even';
				}

				$day = '';
				foreach ($dows as $d) {
					$sel = '';
					if ($d == $date['dow']) {
						$sel = ' selected="selected"';
					}
					$day .= '<option' . $sel . ' value="' . $d . '">' . $this->translationHelper->s('dow' . $d) . '</option>';
				}

				$time = explode(':', $date['time']);

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
                        <input class="fetchercount" style="width:20px; float: left" type="text" name="nft-count[]" value="' . $date['fetcher'] . '"/>
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
