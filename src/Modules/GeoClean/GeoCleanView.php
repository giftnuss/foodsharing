<?php

namespace Foodsharing\Modules\GeoClean;

use Foodsharing\Modules\Core\View;

class GeoCleanView extends View
{
	public function rightmenu()
	{
		return v_menu(array(
			array(
				'name' => 'Alle durchprobieren',
				'click' => 'u_goAll();return false;'
			)
		));
	}

	public function listFs($foodsaver)
	{
		$js = array();
		$data = array();

		foreach ($foodsaver as $fs) {
			$js[] = (int)$fs['id'];

			$data[] = array(
				array('cnt' => '<input class="hiddenid" type="hidden" name="fs-' . $fs['id'] . '" id="fs-' . $fs['id'] . '" value="' . $fs['id'] . '" />' . $fs['name'] . ' ' . $fs['nachname']),
				array('cnt' => $fs['anschrift'] . ', ' . $fs['plz'] . ' ' . $fs['stadt']),
				array('cnt' => '<a href="/?page=foodsaver&a=edit&id=' . $fs['id'] . '" class="button">' . s('edit') . '</a> <a href="#" onclick="u_getGeo(' . (int)$fs['id'] . ');return false;" class="button">Koordinaten ermitteln</a>')
			);
			addHidden('
				' . v_form_hidden('fs' . $fs['id'] . 'anschrift', $fs['anschrift']) . '
				' . v_form_hidden('fs' . $fs['id'] . 'plz', $fs['plz']) . '	
				' . v_form_hidden('fs' . $fs['id'] . 'stadt', $fs['stadt']) . '	
			');
		}

		addJsFunc('
			var u_fslist = [' . implode(',', $js) . '];	
		');

		return
			v_field(
				v_tablesorter(array(
					array('name' => s('name'), 'width' => 150),
					array('name' => s('address')),
					array('name' => s('options'), 'width' => 240)
				), $data),
				'Foodsaver ohne Koordinaten'
			);
	}

	public function regionlist($regions)
	{
		$rows = [];

		foreach ($regions as $r) {
			$rows = [
				['cnt' => '<a class="linkrow ui-corner-all" href="?page=bezirk&bid=' . $r['id'] . '&sub=forum">' . $r['name'] . '</a>'],
				['cnt' => $r['fscount']]
			];
		}

		$out = v_tablesorter([
			['name' => 'Name'],
			['name' => 'Anzahl Foodsaver', 'width' => 120]
		], $rows, ['pager' => true]);

		return $out;
	}
}
