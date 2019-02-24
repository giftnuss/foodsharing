<?php

namespace Foodsharing\Modules\Report;

use Foodsharing\Modules\Core\View;

class ReportView extends View
{
	private $foodsaver;

	public function setFoodsaver($foodsaver): void
	{
		$this->foodsaver = $foodsaver;
	}

	public function betriebList($betriebe): string
	{
		return $this->v_utils->v_form_select('betrieb_id', ['label' => $this->func->sv('betrieb_id', $this->foodsaver['name']), 'values' => $betriebe]);
	}

	public function reportDialog(): string
	{
		return $this->v_utils->v_input_wrapper('Warum möchtest Du ' . $this->foodsaver['name'] . ' melden?', '

			' . $this->v_utils->v_form_select('reportreason', ['required' => true, 'nowrapper' => true, 'value' => 1, 'values' => [
				[
					'id' => 1,
					'name' => 'Ist zu spät zum Abholen gekommen'],
				[
					'id' => 2,
					'name' => 'Ist gar nicht zum Abholen gekommen'],
				[
					'id' => 3,
					'name' => 'Hat sich unhöflich oder respektlos verhalten'],
				[
					'id' => 4,
					'name' => 'Hat den Abholort nicht sauber hinterlassen'],
				[
					'id' => 5,
					'name' => 'Hat sich nicht gemeinschaftlich und sozial beim Abholen verhalten'],
				[
					'id' => 6,
					'name' => 'Hat sich fordernd/übergriffig verhalten'],
				[
					'id' => 7,
					'name' => 'Hat Vorwürfe gemacht'],
				[
					'id' => 8,
					'name' => 'Hat Sachen mitgenommen die nicht für ihn/sie bestimmt waren'],
				[
					'id' => 9,
					'name' => 'Hat Pfandflaschen/-kisten etc. nicht zurückgebracht'],
				[
					'id' => 10,
					'name' => 'Häufiges kurzfristiges Absagen der Abholungen'],
				[
					'id' => 11,
					'name' => 'Ignoriert Kontaktaufnahme'],
				[
					'id' => 12,
					'name' => 'Schmeißt gerettete Lebensmittel weg'],
				[
					'id' => 13,
					'name' => 'Nimmt nicht alle zur Abholung vorgesehenen Lebensmittel mit'],
				[
					'id' => 14,
					'name' => 'Hat sich außerhalb seiner/ihrer Abholzeit beim Betrieb zu rettende Lebensmittel genommen oder nachgefragt'],
				[
					'id' => 15,
					'name' => 'Verkauft gerettete Lebensmittel'],
				[
					'id' => 16,
					'name' => 'Hat gegen andere Verhaltensregeln verstoßen (alles andere)']]
			]) . '<br />
			<div id="reportreason_3" class="cb" style="margin:5px 0;">
			' . $this->v_utils->v_form_checkbox('reportreason_3', ['nowrapper' => true, 'value' => 1, 'values' => [
				[
					'id' => 1,
					'name' => 'gegenüber Foodsavern'],
				[
					'id' => 2,
					'name' => 'gegenüber BetriebsmitarbeiterInnen']
			]
			]) . '
			</div>
			' . $this->v_utils->v_form_select('reportreason_3_sub', ['nowrapper' => true, 'value' => 1, 'values' => [
				[
					'id' => 1,
					'name' => 'beleidigende Äußerungen'],
				[
					'id' => 2,
					'name' => 'rassistische Äußerungen'],
				[
					'id' => 3,
					'name' => 'sexistische Äußerungen'],
				[
					'id' => 4,
					'name' => 'homophobe Äußerungen'],
				[
					'id' => 5,
					'name' => 'Gewalttätigkeit und Drohung'],
				[
					'id' => 6,
					'name' => 'Andere unangebrachte Äußerungen und Verhalten']]
			]) . '
			<div id="reportreason_6" class="cb">
			' . $this->v_utils->v_form_checkbox('reportreason_6', ['nowrapper' => true, 'value' => 1, 'values' => [
				[
					'id' => 1,
					'name' => 'gegenüber BetriebsmitarbeiterInnen'],
				[
					'id' => 2,
					'name' => 'gegenüber Foodsavern'],
				[
					'id' => 3,
					'name' => 'gegenüber Kunden']]
			]) . '
			</div>
			<div id="reportreason_5" class="cb">
			' . $this->v_utils->v_form_checkbox('reportreason_5', ['nowrapper' => true, 'value' => 1, 'values' => [
				[
					'id' => 1,
					'name' => 'vor BetriebsmitarbeiterInnen'],
				[
					'id' => 2,
					'name' => 'vor Foodsavern'],
				[
					'id' => 3,
					'name' => 'vor Kunden']]
			]) . '
			</div>
			<div id="reportreason_7" class="cb">
			' . $this->v_utils->v_form_checkbox('reportreason_7', ['nowrapper' => true, 'value' => 1, 'values' => [
				[
					'id' => 1,
					'name' => 'gegenüber BetriebsmitarbeiterInnen'],
				[
					'id' => 2,
					'name' => 'gegenüber Foodsavern'],
				[
					'id' => 3,
					'name' => 'gegenüber Kunden']]
			]) . '
			</div>
			<div id="reportreason_8" class="cb">
			' . $this->v_utils->v_form_checkbox('reportreason_8', ['nowrapper' => true, 'value' => 1, 'values' => [
				[
					'id' => 1,
					'name' => 'von BetriebsmitarbeiterInnen'],
				[
					'id' => 2,
					'name' => 'von Foodsavern'],
				[
					'id' => 3,
					'name' => 'von Kunden']]
			]) . '
			</div>');
	}

	public function statsMenu($stats): string
	{
		$menu = [
			['name' => 'Neue Meldungen (' . $stats['new'] . ')', 'href' => '/?page=report&sub=uncom'],
			['name' => 'Bestätigte (' . $stats['com'] . ')', 'href' => '/?page=report&sub=com']
		];

		$active = 'uncom';
		if ($_GET['sub'] === 'com') {
			$active = 'com';
		}

		return $this->menu($menu, ['active' => $active]);
	}

	public function listReportsTiny($reports): string
	{
		$out = '<ul class="linklist">';

		foreach ($reports as $r) {
			$name = '';
			if (!empty($r['rp_name'])) {
				$name = ' von ' . $r['rp_name'] . ' ' . $r['rp_nachname'] . '';
			}
			$out .= '<li><a href="#" onclick="ajreq(\'loadReport\',{id:' . (int)$r['id'] . '})">' . date('d.m.Y', $r['time_ts']) . $name . '</a></li>';
		}

		$out .= '</ul>';

		return $this->v_utils->v_field($out, 'Alle Meldungen');
	}

	public function listReports($reports): string
	{
		$this->pageCompositionHelper->addStyle('.tablesorter td{ cursor:pointer; }');

		$this->pageCompositionHelper->addJs('
			$(".tablesorter tr").on("click", function(){
				rid = parseInt($(this).children("td:first").children("input:first").val());
				ajreq("loadReport",{id:rid});
			});
		');

		$rows = array();
		foreach ($reports as $r) {
			$rows[] = [
				['cnt' => '<input type="hidden" class="rid" name="rid" value="' . $r['id'] . '"><span class="photo"><a title="' . $r['fs_name'] . ' ' . $r['fs_nachname'] . '" href="/profile/' . (int)$r['fs_id'] . '"><img id="miniq-' . $r['fs_id'] . '" src="' . $this->func->img($r['fs_photo']) . '" /></a></span>'],
				['cnt' => '<span class="photo"><a title="' . $r['rp_name'] . ' ' . $r['rp_nachname'] . '" href="/profile/' . (int)$r['rp_id'] . '"><img id="miniq-' . $r['rp_id'] . '" src="' . $this->func->img($r['rp_photo']) . '" /></a></span>'],
				['cnt' => htmlspecialchars($this->sanitizerService->tt($r['msg'], 50))],
				['cnt' => '<span style="display:none;">a' . $r['time_ts'] . ' </span>' . $this->func->niceDateShort($r['time_ts']) . ' Uhr'],
				['cnt' => $r['fs_stadt']],
				['cnt' => $r['b_name']],
			];
		}

		$table = $this->v_utils->v_tablesorter([
			['name' => 'Über', 'width' => 40],
			['name' => 'Von', 'width' => 40],
			['name' => $this->func->s('message')],
			['name' => $this->func->s('datetime'), 'width' => 80],
			['name' => 'FS Wohnort', 'width' => 80],
			['name' => 'Stammbezirk', 'width' => 40]
		], $rows, ['pager' => true]);

		return $table;
	}
}
