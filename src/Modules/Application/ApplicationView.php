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
		return $this->v_utils->v_menu([
			['click' => 'tryAcceptApplication(' . (int)$this->bezirk_id . ',' . (int)$application['id'] . ');return false;', 'name' => 'Ja'],
			['click' => 'tryDeclineApplication(' . (int)$this->bezirk_id . ',' . (int)$application['id'] . ');return false;', 'name' => 'Nein']
		], 'Bewerbung annehmen');
	}

	public function application($application)
	{
		$out = $this->headline(
			'Bewerbung für ' . $this->bezirk['name'] . ' von ' . $application['name'],
			$application['photo'],
			$application['id']
		);

		$cnt = nl2br($application['application']);

		$cnt = $this->v_utils->v_input_wrapper($application['name'], $cnt);
		$cnt .= '<div class="clear"></div>';

		$out .= $this->v_utils->v_field($cnt, 'Motivations-Text', ['class' => 'ui-padding']);

		return $out;
	}

	private function headline(string $title, ?string $img, int $userId): string
	{
		return '
		<div class="welcome ui-padding margin-bottom ui-corner-all">
			<div class="welcome_profile_image">
				<a href="/profile/' . $userId . '">
					<img width="50" height="50" src="' . $this->imageService->img($img) . '">
				</a>
			</div>
			<div class="welcome_profile_name">
				<div class="user_display_name">
					' . $title . '
				</div>
			</div>
		</div>';
	}
}
