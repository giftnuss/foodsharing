<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Modules\Core\View;

class FoodsaverView extends View
{
	public function addFoodsaver($bezirk)
	{
		$cnt = $this->v_utils->v_form_tagselect('search_name', array('required' => true, 'xhr' => 'recip'));

		$cnt .= $this->v_utils->v_input_wrapper('', '<span class="button" onclick="fsapp.addFoodsaver();">' . $this->func->s('add') . '</span>');

		$cnt .= '
			<div id="appdata" style="display:none">
				<input type="hidden" name="bid" class="bid" value="' . $bezirk['id'] . '" />
			</div>';

		return $this->v_utils->v_field(
			$cnt,

			'Foodsaver hinzufügen',
			array('class' => 'ui-padding')
		);
	}

	public function foodsaverForm($foodsaver = false)
	{
		if ($foodsaver === false) {
			return '<div id="fsform"></div>';
		} else {
			$cnt = $this->v_utils->v_input_wrapper('Foto', '<a class="avatarlink corner-all" href="#" onclick="profile(' . (int)$foodsaver['id'] . ');return false;"><img style="display:none;" class="corner-all" src="' . $this->func->img($foodsaver['photo'], 'med') . '" /></a>');
			$cnt .= $this->v_utils->v_input_wrapper('Name', $foodsaver['name'] . ' ' . $foodsaver['nachname']);
			$cnt .= $this->v_utils->v_input_wrapper('Rolle', $this->func->s('rolle_' . $foodsaver['rolle'] . '_' . $foodsaver['geschlecht']));

			$cnt .= $this->v_utils->v_input_wrapper('Letzter Login', $foodsaver['last_login']);

			$cnt .= $this->v_utils->v_input_wrapper('Optionen', '
				<span class="button" onclick="fsapp.delfromBezirk(' . $foodsaver['id'] . ');">Aus Bezirk löschen</span>		
			');

			return $this->v_utils->v_field($cnt, $foodsaver['name'], array('class' => 'ui-padding'));
		}
	}

	public function foodsaverList($foodsaver, $bezirk, $inactive = false)
	{
		$name = $inactive ? 'inactive' : '';

		return
			'<div id="' . $name . 'foodsaverlist">' .
			v_field(
				$this->fsAvatarList($foodsaver, array('id' => 'fslist', 'shuffle' => false)),
				$this->func->s('fs_in') . $bezirk['name'] . ($inactive ? $this->func->s('fs_list_not_logged_for_6_months') : '')
			) . '
		</div>';
	}
}
