<?php

namespace Foodsharing\Modules\PassportGenerator;

use Foodsharing\Modules\Core\View;

final class PassportGeneratorView extends View
{
	public function passTable(array $region): string
	{
		$data = [];

		foreach ($region['foodsaver'] as $fs) {
			if ($fs['last_pass'] == '0000-00-00 00:00:00' || $fs['last_pass'] == null) {
				$ts = 0;
				$when = $this->translator->trans('pass.none');
			} else {
				$ts = date('YmdHis', $fs['last_pass_ts']);
				$when = $this->timeHelper->niceDate($fs['last_pass_ts']);
			}
			$last = '<span style="display:none">a' . $ts . '</span>'
				. ' <a href="#" class="dateclick linkrow ui-corner-all">' . $when . '</a>';

			if ($fs['verified']) {
				$sort = 'a';
				$action = 'undo';
			} else {
				$sort = 'b';
				$action = 'do';
			}
			$verified = '<span style="display:none;">' . $sort . '</span>'
				. '<a href="#" title="'
				. $this->translator->trans('pass.verify.' . $action, ['{name}' => $fs['name']])
				. '" class="verify verify-' . $action . '">'
				. /* weird icon magic: */ '<span></span>'
				. '</a>';

			if (empty($fs['photo'])) {
				$img = $this->imageService->img($fs['photo']);
			} else {
				$img = 'images/thumb_crop_' . $fs['photo'];
			}

			$checkbox = '<input class="checkbox'
					. ' bezirk' . $region['id']
					. ' date' . date('Y-m-d-H-i-s', $fs['last_pass_ts'])
				. '" type="checkbox" name="passes[]" value="' . $fs['id'] . '" />';

			$picture = '<span style="display:none">a' . $fs['photo'] . '</span>'
				. '<a href="#" class="fsname"><img src="' . $img . '" width="35" /></a>';

			$name = '<a href="/?page=foodsaver&a=edit&id=' . $fs['id'] . '" class="linkrow ui-corner-all">'
				. $fs['name'] . '</a>';

			$data[] = [
				['cnt' => $checkbox],
				['cnt' => $picture],
				['cnt' => $name],
				['cnt' => $last],
				['cnt' => $verified]
			];
		}

		$checkbox = '<input class="checker" type="checkbox" name="checker" value="' . $region['id'] . '" />';

		return $this->v_utils->v_field(
			$this->v_utils->v_tablesorter([
				['name' => $checkbox, 'sort' => false, 'width' => 20],
				['name' => $this->translator->trans('pass.photo'), 'width' => 40],
				['name' => $this->translator->trans('pass.name')],
				['name' => $this->translator->trans('pass.date'), 'width' => 200],
				['name' => $this->translator->trans('pass.verified'), 'width' => 70],
			], $data),
			$region['bezirk']
		);
	}

	public function menubar(): string
	{
		return $this->v_utils->v_menu([
			['name' => $this->translator->trans('pass.nav.select'), 'click' => 'checkAllCb(true);return false;'],
			['name' => $this->translator->trans('pass.nav.deselect'), 'click' => 'checkAllCb(false);return false;'],
		], $this->translator->trans('pass.nav.options'));
	}

	public function start(): string
	{
		return $this->v_utils->v_menu([
			['name' => $this->translator->trans('pass.nav.generate'), 'href' => '#start'],
		], $this->translator->trans('pass.nav.title'));
	}

	public function tips(): string
	{
		return $this->v_utils->v_info(
			'<p>' . $this->translator->trans('pass.hintSelect') . '</p>' .
			'<p>' . $this->translator->trans('pass.hintVerify') . '</p>',
			$this->translator->trans('pass.hint')
		);
	}
}
