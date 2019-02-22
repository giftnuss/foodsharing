<?php

namespace Foodsharing\Modules\GeoClean;

use Foodsharing\Modules\Core\View;

class GeoCleanView extends View
{
	public function rightmenu()
	{
		return $this->v_utils->v_menu(array(
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
				array('cnt' => '<a href="/?page=foodsaver&a=edit&id=' . $fs['id'] . '" class="button">' . $this->func->s('edit') . '</a> <a href="#" onclick="u_getGeo(' . (int)$fs['id'] . ');return false;" class="button">Koordinaten ermitteln</a>')
			);
			$this->pageCompositionHelper->addHidden('
				' . $this->v_utils->v_form_hidden('fs' . $fs['id'] . 'anschrift', $fs['anschrift']) . '
				' . $this->v_utils->v_form_hidden('fs' . $fs['id'] . 'plz', $fs['plz']) . '	
				' . $this->v_utils->v_form_hidden('fs' . $fs['id'] . 'stadt', $fs['stadt']) . '	
			');
		}

		$this->pageCompositionHelper->addJsFunc('
			var u_fslist = [' . implode(',', $js) . '];	
		');

		return
			$this->v_utils->v_field(
				$this->v_utils->v_tablesorter(array(
					array('name' => $this->func->s('name'), 'width' => 150),
					array('name' => $this->func->s('address')),
					array('name' => $this->func->s('options'), 'width' => 240)
				), $data),
				'Foodsaver ohne Koordinaten'
			);
	}

	public function regionList($regions)
	{
		$rows = [];

		foreach ($regions as $r) {
			$rows[] = [
				['cnt' => '<a class="linkrow ui-corner-all" href="?page=bezirk&bid=' . $r['id'] . '&sub=forum">' . $r['name'] . '</a>'],
				['cnt' => $r['fscount']]
			];
		}

		$out = $this->v_utils->v_tablesorter([
			['name' => $this->func->s('name')],
			['name' => $this->func->s('fs_count'), 'width' => 120]
		], $rows, ['pager' => true]);

		return $out;
	}
}
