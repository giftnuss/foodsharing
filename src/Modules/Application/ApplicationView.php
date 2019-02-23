<?php

namespace Foodsharing\Modules\Application;

use Foodsharing\Modules\Core\View;

class ApplicationView extends View
{
	private $bezirk;
	private $bezirk_id;

	public function setBezirk($bezirk)
	{
		$this->bezirk = $bezirk;
		$this->bezirk_id = $bezirk['id'];
	}

	public function applicationMenu($application)
	{
		return $this->v_utils->v_menu(array(
			array('click' => 'ajreq(\'accept\',{bid:' . (int)$this->bezirk_id . ',fid:' . (int)$application['id'] . '});return false;', 'name' => 'Ja'),
			array('click' => 'ajreq(\'decline\',{bid:' . (int)$this->bezirk_id . ',fid:' . (int)$application['id'] . '});return false;', 'name' => 'Nein')
		), 'Bewerbung annehmen');
	}

	public function application($application)
	{
		$out = $this->headline('Bewerbung für ' . $this->bezirk['name'] . ' von ' . $application['name'], $this->imageService->img($application['photo']), 'profile(' . $application['id'] . ');');

		$cnt = nl2br($application['application']);

		$cnt = $this->v_utils->v_input_wrapper($application['name'], $cnt);
		$cnt .= '<div class="clear"></div>';

		$out .= $this->v_utils->v_field($cnt, 'Motivations-Text', array('class' => 'ui-padding'));

		return $out;
	}
}
