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
			$verificationStatus = $r['verified'] ? '<i class="fas fa-user-check" title="' . $this->translator->trans('store.request.verified') . '"></i> ' : '';
			$out .= '
		<tr class="' . $odd . ' request-' . $r['id'] . '">
			<td class="img" width="35px"><a href="/profile/' . (int)$r['id'] . '"><img src="' . $this->imageService->img($r['photo']) . '" /></a></td>
			<td style="padding-top:17px;"><span class="msg">' . $verificationStatus . '<a href="/profile/' . (int)$r['id'] . '">' . $r['name'] . '</a></span></td>
			<td style="width:92px;padding-top:17px;"><span class="msg"><ul class="toolbar"><li class="ui-state-default ui-corner-left" title="Ablehnen" onclick="denyRequest(' . (int)$r['id'] . ',' . (int)$storeData['id'] . ');"><span class="ui-icon ui-icon-closethick"></span></li><li class="ui-state-default" title="Auf die Springerliste setzen" onclick="warteRequest(' . (int)$r['id'] . ',' . (int)$storeData['id'] . ');"><span class="ui-icon ui-icon-star"></span></li><li class="ui-state-default ui-corner-right" title="Akzeptieren" onclick="acceptRequest(' . (int)$r['id'] . ',' . (int)$storeData['id'] . ');"><span class="ui-icon ui-icon-heart"></span></li></ul></span></td>
		</tr>';
		}

		$out .= '</table>';

		$this->pageHelper->hiddenDialog('requests', [$out]);
		$this->pageHelper->addJs('$("#requests").dialog("option", "title", "Anfragen für ' . $this->sanitizerService->jsSafe($storeData['name'], '"') . '");');
		$this->pageHelper->addJs('$("#requests").dialog("option", "buttons", {});');
		$this->pageHelper->addJs('$("#requests").dialog("open");');
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

	public function u_editPickups(array $allDates): string
	{
		$out = '<table class="timetable">
		<thead>
			<tr>
				<th class="ui-padding">' . $this->translator->trans('day') . '</th>
				<th class="ui-padding">' . $this->translator->trans('time') . '</th>
				<th class="ui-padding">' . $this->translator->trans('pickup.edit.slotcount') . '</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="3" class="ui-padding">
					<span id="nft-add">' . $this->translator->trans('pickup.edit.more') . '</span>
				</td>
			</tr>
		</tfoot>
		<tbody>';

		$dows = range(1, 6);
		$dows[] = 0;

		foreach ($allDates as $date) {
			$time = explode(':', $date['time']);

			$out .= '
			<tr class="odd">
				<td class="ui-padding">
					<select class="nft-dow" name="newfetchtime[]" id="nft-dow">
						' . $this->prepareOptionRange($dows, $date['dow'], true) . '
					</select>
				</td>
				<td class="ui-padding">
					<select class="nfttime-hour" name="nfttime[hour][]">
						' . $this->prepareOptionRange(range(0, 23), $time[0]) . '
					</select>
					<select class="nfttime-min" name="nfttime[min][]">
						' . $this->prepareOptionRange(range(0, 55, 5), $time[1]) . '
					</select>
				</td>
				<td class="ui-padding">
					<input class="fetchercount" type="text" name="nft-count[]" value="' . $date['fetcher'] . '"/>
					<button class="nft-remove"></button>
				</td>
			</tr>';
		}
		$out .= '</tbody></table>';

		$out .= '<table id="nft-hidden-row" style="display: none;">
		<tbody>
			<tr class="odd">
				<td class="ui-padding">
					<select class="nft-dow" name="newfetchtime[]" id="nft-dow">
						' . $this->prepareOptionRange($dows, null, true) . '
					</select>
				</td>
				<td class="ui-padding">
					<select class="nfttime-hour" name="nfttime[hour][]">
						' . $this->prepareOptionRange(range(0, 23)) . '
					</select>
					<select class="nfttime-min" name="nfttime[min][]">
						' . $this->prepareOptionRange(range(0, 55, 5)) . '
					</select></td>
				<td class="ui-padding">
					<input class="fetchercount" type="text" name="nft-count[]" value="2" />
					<button class="nft-remove"></button>
				</td>
			</tr>
		</tbody>
		</table>';

		return $out;
	}

	private function prepareOptionRange(array $range, ?string $selectedValue = null, bool $dayOfWeek = false): string
	{
		$out = '';
		foreach ($range as $item) {
			$selected = ($item == $selectedValue) ? ' selected="selected"' : '';
			$label = $dayOfWeek ? $this->timeHelper->getDow($item) : str_pad($item, 2, '0', STR_PAD_LEFT);
			$out .= '<option' . $selected . ' value="' . $item . '">' . $label . '</option>';
		}

		return $out;
	}
}
