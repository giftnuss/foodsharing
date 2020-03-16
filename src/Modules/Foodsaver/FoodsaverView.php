<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Modules\Core\View;

class FoodsaverView extends View
{
	public function foodsaverForm($foodsaver = false)
	{
		if ($foodsaver === false) {
			return '<div id="fsform"></div>';
		}

		$cnt = $this->v_utils->v_input_wrapper('Foto', '<a class="avatarlink corner-all" href="/profile/' . (int)$foodsaver['id'] . '"><img style="display:none;" class="corner-all" src="' . $this->imageService->img($foodsaver['photo'], 'med') . '" /></a>');
		$cnt .= $this->v_utils->v_input_wrapper('Name', $foodsaver['name'] . ' ' . $foodsaver['nachname']);
		$cnt .= $this->v_utils->v_input_wrapper('Rolle', $this->translationHelper->s('rolle_' . $foodsaver['rolle'] . '_' . $foodsaver['geschlecht']));

		$cnt .= $this->v_utils->v_input_wrapper('Letzter Login', $foodsaver['last_login']);

		$cnt .= $this->v_utils->v_input_wrapper('Optionen', '
			<span class="button" onclick="fsapp.deleteFromRegion(' . $foodsaver['id'] . ');">Aus Bezirk löschen</span>
		');

		return $this->v_utils->v_field($cnt, $foodsaver['name'], ['class' => 'ui-padding']);
	}

	public function foodsaverList($foodsaver, $bezirk, $inactive = false)
	{
		$name = $inactive ? 'inactive' : '';

		return
			'<div id="' . $name . 'foodsaverlist">' .
			$this->v_utils->v_field(
				$this->fsAvatarList($foodsaver, ['id' => 'fslist', 'noshuffle' => true, 'height' => 600]),
				count($foodsaver) . ' ' . $this->translationHelper->s('fs_in') . $bezirk['name'] . ($inactive ? $this->translationHelper->s('fs_list_not_logged_for_6_months') : '')
			) . '
		</div>';
	}

	public function foodsaver_form($title = 'Foodsaver', $regionDetails)
	{
		global $g_data;

		$orga = '';

		$position = '';

		if ($this->session->may('orga')) {
			$position = $this->v_utils->v_form_text('position');
			$options = [
				'values' => [
					['id' => 1, 'name' => 'ist im bundesweiten Orgateam dabei']
				]
			];

			if ($g_data['orgateam'] == 1) {
				$options['checkall'] = true;
			}

			$orga = $this->v_utils->v_form_checkbox('orgateam', $options);
			$orga .= $this->v_utils->v_form_select('rolle', [
				'values' => [
					['id' => 0, 'name' => 'Foodsharer/in'],
					['id' => 1, 'name' => 'Foodsaver/in (FS)'],
					['id' => 2, 'name' => 'Betriebsverantwortliche/r (BIEB)'],
					['id' => 3, 'name' => 'Botschafter/in (BOT)'],
					['id' => 4, 'name' => 'Orgamensch (ORG)']
				]
			]);
		}

		$this->pageHelper->addJs('
			$("#rolle").on("change", function(){
				if(this.value == 4)
				{
					$("#orgateam-wrapper input")[0].checked = true;
				}
				else
				{
					$("#orgateam-wrapper input")[0].checked = false;
				}
			});
			$("#plz, #stadt, #anschrift").on("blur",function(){


					if($("#plz").val() != "" && $("#stadt").val() != "" && $("#anschrift").val() != "")
					{
					u_loadCoords({
							plz: $("#plz").val(),
							stadt: $("#stadt").val(),
							anschrift: $("#anschrift").val(),
							},function(lat,lon){
							$("#lat").val(lat);
							$("#lon").val(lon);
							});
					}
					});

			$("#lat-wrapper").hide();
			$("#lon-wrapper").hide();
			');

		$bezirkchoose = $this->v_utils->v_bezirkChooser('bezirk_id', $regionDetails);

		return $this->v_utils->v_quickform($title, [
			$bezirkchoose,
			$orga,
			$this->v_utils->v_form_text('name', ['required' => true]),
			$this->v_utils->v_form_text('nachname', ['required' => true]),

			$position,

			$this->v_utils->v_info($this->translationHelper->s('warning_of_address_change')),
			$this->v_utils->v_form_text('stadt', ['required' => true]),
			$this->v_utils->v_form_text('plz', ['required' => true]),
			$this->v_utils->v_form_text('anschrift', ['required' => true]),
			$this->v_utils->v_form_text('lat'),
			$this->v_utils->v_form_text('lon'),
			$this->v_utils->v_form_text('email', ['required' => true, 'disabled' => true]),
			$this->v_utils->v_form_text('telefon'),
			$this->v_utils->v_form_text('handy'),
			$this->v_utils->v_form_select('geschlecht', ['values' => [
				['name' => 'Frau', 'id' => 2],
				['name' => 'Mann', 'id' => 1],
				['name' => 'Beides oder Sonstiges', 'id' => 3]
			],
				['required' => true]
			]),

			$this->v_utils->v_form_date('geb_datum', ['required' => true, 'yearRangeFrom' => (date('Y') - 111), 'yearRangeTo' => date('Y')])
		]);
	}

	public function u_delete_account()
	{
		$content = '
	<div style="text-align:center;margin-bottom:10px;">
		<span id="delete-account">' . $this->translationHelper->s('delete_now') . '</span>
	</div>
	';

		return $this->v_utils->v_field($content, $this->translationHelper->s('delete_account'), ['class' => 'ui-padding']);
	}
}
