<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Modules\Core\View;

class WorkGroupView extends View
{
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

	private function img($img, $prefix = 'crop_1_128_')
	{
		return 'images/' . str_replace('/', '/' . $prefix, $img);
	}

	public function contactgroup($group)
	{
		$head = '';

		if ($group['leader']) {
			foreach ($group['leader'] as $gl) {
				$head .= '<a style="margin:4px 4px 0 0;" href="/profile/' . (int)$gl['id'] . '" class="member"><img alt="' . $gl['name'] . '" src="' . $this->imageService->img($gl['photo']) . '"></a>';
			}
			$head = $this->v_utils->v_input_wrapper(count($group['leader']) . ' Ansprechpartner', $head);
		}

		$head .= $this->v_utils->v_field($this->func->s('contact-disclaimer'));

		return $head . $this->v_utils->v_form_textarea('message');
	}
}
