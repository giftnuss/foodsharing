<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Modules\Core\View;

class WorkGroupView extends View
{
	public function applyForm($group)
	{
		return $this->v_utils->v_form('apply', [
			$this->v_utils->v_form_textarea('motivation', [
				'value' => '',
				'label' => $this->translator->trans('group.apply.motivation', ['{group}' => $group['name']]),
			]),
			$this->v_utils->v_form_textarea('faehigkeit', [
				'value' => '',
				'label' => $this->translator->trans('group.apply.faehigkeit'),
			]),
			$this->v_utils->v_form_textarea('erfahrung', [
				'value' => '',
				'label' => $this->translator->trans('group.apply.erfahrung'),
			]),
			$this->v_utils->v_form_select('zeit', [
				'selected' => '',
				'label' => $this->translator->trans('group.apply.zeit'),
				'values' => [
					['id' => '1', 'name' => $this->translator->trans('group.apply.time.1')],
					['id' => '2', 'name' => $this->translator->trans('group.apply.time.2')],
					['id' => '3', 'name' => $this->translator->trans('group.apply.time.3')],
					['id' => '5', 'name' => $this->translator->trans('group.apply.time.5')],
				],
			]),
		], ['submit' => false]);
	}

	public function contactgroup($group)
	{
		$adminList = '';
		if ($group['leader']) {
			foreach ($group['leader'] as $gl) {
				$adminList .= '<a style="margin: 4px 4px 0 0;" href="/profile/' . (int)$gl['id'] . '" class="member">'
					. '<img alt="' . $gl['name'] . '" src="' . $this->imageService->img($gl['photo']) . '">'
					. '</a>';
			}
		}
		if ($adminList) {
			$head = $this->v_utils->v_input_wrapper($this->translator->trans('group.contact.admins', [
				'{count}' => count($group['leader']),
			]), $adminList);
		} else {
			$head = '';
		}

		$head .= $this->v_utils->v_field($this->translator->trans('group.contact.disclaimer'));

		return $head . $this->v_utils->v_form_textarea('message');
	}
}
