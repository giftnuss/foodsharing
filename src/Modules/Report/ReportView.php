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
		return $this->v_utils->v_form_select('betrieb_id', [
			'label' => $this->translator->trans('reports.in-store', ['{user}' => $this->foodsaver['name']]),
			'values' => $betriebe,
		]);
	}

	// TODO Modernisieren
	public function reportDialog(): string
	{
		return $this->v_utils->v_input_wrapper($this->translator->trans('profile.report.view.why') . ' -> ' . $this->foodsaver['name'], '

			' . $this->v_utils->v_form_select('reportreason', ['required' => true, 'nowrapper' => true, 'value' => 1, 'values' => [
				[
					'id' => 1,
					'name' => $this->translator->trans('profile.report.view.tardy')],
				[
					'id' => 2,
					'name' => $this->translator->trans('profile.report.view.noshow')],
				/*[
					'id' => 3,
					'name' => $this->translator->trans('profile.report.view.respect')],
				[
					'id' => 4,
					'name' => $this->translator->trans('profile.report.view.dirty')],
				[
					'id' => 5,
					'name' => $this->translator->trans('profile.report.view.social')],
				[
					'id' => 6,
					'name' => $this->translator->trans('profile.report.view.bossy')],
				[
					'id' => 7,
					'name' => $this->translator->trans('profile.report.view.complaints')],
				[
					'id' => 8,
					'name' => $this->translator->trans('profile.report.view.thief')],
				[
					'id' => 9,
					'name' => $this->translator->trans('profile.report.view.returned')],*/
				[
					'id' => 10,
					'name' => $this->translator->trans('profile.report.view.unreliable')], /*
				[
					'id' => 12,
					'name' => $this->translator->trans('profile.report.view.trasher')],
				[
					'id' => 13,
					'name' => $this->translator->trans('profile.report.view.picky')],
				[
					'id' => 14,
					'name' => $this->translator->trans('profile.report.view.additional')],*/
				[
					'id' => 15,
					'name' => $this->translator->trans('profile.report.view.sells')]/*,
				[
					'id' => 16,
					'name' => $this->translator->trans('profile.report.view.other')]*/]
			]) . '<br />
			<div id="reportreason_3" class="cb" style="margin:5px 0;">
			' . $this->v_utils->v_form_checkbox('reportreason_3', ['nowrapper' => true, 'value' => 1, 'values' => [
				[
					'id' => 1,
					'name' => $this->translator->trans('profile.report.view.vsfs')],
				[
					'id' => 2,
					'name' => $this->translator->trans('profile.report.view.vsstore')]
			]
			]) . '
			</div>
			' . $this->v_utils->v_form_select('reportreason_3_sub', ['nowrapper' => true, 'value' => 1, 'values' => [
				[
					'id' => 1,
					'name' => $this->translator->trans('profile.report.view.what.hurtful')],
				[
					'id' => 2,
					'name' => $this->translator->trans('profile.report.view.what.racist')],
				[
					'id' => 3,
					'name' => $this->translator->trans('profile.report.view.what.sexist')],
				[
					'id' => 4,
					'name' => $this->translator->trans('profile.report.view.what.homophobe')],
				[
					'id' => 5,
					'name' => $this->translator->trans('profile.report.view.what.brutal')],
				[
					'id' => 6,
					'name' => $this->translator->trans('profile.report.view.what.other')]]
			]) . '
			<div id="reportreason_6" class="cb">
			' . $this->v_utils->v_form_checkbox('reportreason_6', ['nowrapper' => true, 'value' => 1, 'values' => [
				[
					'id' => 1,
					'name' => $this->translator->trans('profile.report.view.vsstore')],
				[
					'id' => 2,
					'name' => $this->translator->trans('profile.report.view.vsfs')],
				[
					'id' => 3,
					'name' => $this->translator->trans('profile.report.view.vsclients')]]
			]) . '
			</div>
			<div id="reportreason_5" class="cb">
			' . $this->v_utils->v_form_checkbox('reportreason_5', ['nowrapper' => true, 'value' => 1, 'values' => [
				[
					'id' => 1,
					'name' => $this->translator->trans('profile.report.view.nearstore')],
				[
					'id' => 2,
					'name' => $this->translator->trans('profile.report.view.nearfs')],
				[
					'id' => 3,
					'name' => $this->translator->trans('profile.report.view.nearclients')]]
			]) . '
			</div>
			<div id="reportreason_7" class="cb">
			' . $this->v_utils->v_form_checkbox('reportreason_7', ['nowrapper' => true, 'value' => 1, 'values' => [
				[
					'id' => 1,
					'name' => $this->translator->trans('profile.report.view.nearstore')],
				[
					'id' => 2,
					'name' => $this->translator->trans('profile.report.view.nearfs')],
				[
					'id' => 3,
					'name' => $this->translator->trans('profile.report.view.nearclients')]]
			]) . '
			</div>
			<div id="reportreason_8" class="cb">
			' . $this->v_utils->v_form_checkbox('reportreason_8', ['nowrapper' => true, 'value' => 1, 'values' => [
				[
					'id' => 1,
					'name' => $this->translator->trans('profile.report.view.ofstore')],
				[
					'id' => 2,
					'name' => $this->translator->trans('profile.report.view.offs')],
				[
					'id' => 3,
					'name' => $this->translator->trans('profile.report.view.ofclients')]]
			]) . '
			</div>');
	}

	public function statsMenu($stats): string
	{
		$menu = [
			['name' => $this->translator->trans('profile.report.view.newreport') . ' (' . $stats['new'] . ')', 'href' => '/?page=report&sub=uncom'],
			['name' => $this->translator->trans('profile.report.view.delivered') . ' (' . $stats['com'] . ')', 'href' => '/?page=report&sub=com']
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

		return $this->v_utils->v_field($out, $this->translator->trans('reports.all_reports'));
	}

	public function listReports($reports): string
	{
		$this->pageHelper->addStyle('.tablesorter td{ cursor:pointer; }');

		$this->pageHelper->addJs('
			$(".tablesorter tr").on("click", function(){
				rid = parseInt($(this).children("td:first").children("input:first").val());
				ajreq("loadReport",{id:rid});
			});
		');

		$rows = [];
		foreach ($reports as $r) {
			$rows[] = [
				['cnt' => '<input type="hidden" class="rid" name="rid" value="' . $r['id'] . '"><span class="photo"><a title="' . $r['fs_name'] . ' ' . $r['fs_nachname'] . '" href="/profile/' . (int)$r['fs_id'] . '"><img id="miniq-' . $r['fs_id'] . '" src="' . $this->imageService->img($r['fs_photo']) . '" /></a></span>'],
				['cnt' => '<span class="photo"><a title="' . $r['rp_name'] . ' ' . $r['rp_nachname'] . '" href="/profile/' . (int)$r['rp_id'] . '"><img id="miniq-' . $r['rp_id'] . '" src="' . $this->imageService->img($r['rp_photo']) . '" /></a></span>'],
				['cnt' => htmlspecialchars($this->sanitizerService->tt($r['msg'], 50))],
				['cnt' => '<span style="display:none;">a' . $r['time_ts'] . ' </span>' . $this->timeHelper->niceDateShort($r['time_ts']) . ' Uhr'],
				['cnt' => $r['fs_stadt']],
				['cnt' => $r['b_name']],
			];
		}

		$table = $this->v_utils->v_tablesorter([
			['name' => $this->translator->trans('reports.about'), 'width' => 40],
			['name' => $this->translator->trans('reports.from'), 'width' => 40],
			['name' => $this->translator->trans('reportmessage')],
			['name' => $this->translator->trans('reports.when'), 'width' => 80],
			['name' => $this->translator->trans('profile.report.view.residence'), 'width' => 80],
			['name' => $this->translator->trans('terminology.homeregion'), 'width' => 40]
		], $rows, ['pager' => true]);

		return $table;
	}
}
