<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Modules\Core\View;

class FoodsaverView extends View
{
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
			$this->v_utils->v_field(
				$this->fsAvatarList($foodsaver, array('id' => 'fslist', 'shuffle' => false)),
				$this->func->s('fs_in') . $bezirk['name'] . ($inactive ? $this->func->s('fs_list_not_logged_for_6_months') : '')
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
			$options = array(
				'values' => array(
					array('id' => 1, 'name' => 'ist im Bundesweiten Orgateam dabei')
				)
			);

			if ($g_data['orgateam'] == 1) {
				$options['checkall'] = true;
			}

			$orga = $this->v_utils->v_form_checkbox('orgateam', $options);
			$orga .= $this->v_utils->v_form_select('rolle', array(
				'values' => array(
					array('id' => 0, 'name' => 'Foodsharer/in'),
					array('id' => 1, 'name' => 'Foodsaver/in (FS)'),
					array('id' => 2, 'name' => 'Betriebsverantwortliche/r (BIEB)'),
					array('id' => 3, 'name' => 'Botschafter/in (BOT)'),
					array('id' => 4, 'name' => 'Orgamensch/in (ORG)')
				)
			));
		}

		$this->func->addJs('
			$("#rolle").change(function(){
				if(this.value == 4)
				{
					$("#orgateam-wrapper input")[0].checked = true;
				}
				else
				{
					$("#orgateam-wrapper input")[0].checked = false;
				}
			});
			$("#plz, #stadt, #anschrift").bind("blur",function(){


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

		return $this->v_utils->v_quickform($title, array(
			$bezirkchoose,
			$orga,
			$this->v_utils->v_form_text('name', array('required' => true)),
			$this->v_utils->v_form_text('nachname', array('required' => true)),

			$position,

			$this->v_utils->v_form_text('stadt', array('required' => true)),
			$this->v_utils->v_form_text('plz', array('required' => true)),
			$this->v_utils->v_form_text('anschrift', array('required' => true)),
			$this->v_utils->v_form_text('lat'),
			$this->v_utils->v_form_text('lon'),
			$this->v_utils->v_form_text('email', array('required' => true, 'disabled' => true)),
			$this->v_utils->v_form_text('telefon'),
			$this->v_utils->v_form_text('handy'),
			$this->v_utils->v_form_select('geschlecht', array('values' => array(
				array('name' => 'Frau', 'id' => 2),
				array('name' => 'Mann', 'id' => 1),
				array('name' => 'beides oder Sonstiges', 'id' => 3)
			),
				array('required' => true)
			)),

			$this->v_utils->v_form_date('geb_datum', array('required' => true, 'yearRangeFrom' => (date('Y') - 111), 'yearRangeTo' => date('Y')))
		));
	}

	public function u_delete_account()
	{
		$this->func->addJs('
		$("#delete-account-confirm").dialog({
			autoOpen: false,
			modal: true,
			title: "' . $this->func->s('delete_account_confirm_title') . '",
			buttons: {
				"' . $this->func->s('abort') . '" : function(){
					$("#delete-account-confirm").dialog("close");
				},
				"' . $this->func->s('delete_account_confirm_bt') . '" : function(){
					goTo("/?page=foodsaver&a=edit&id=' . (int)$_GET['id'] . '&deleteaccount=1");
				}
			}
		});

		$("#delete-account").button().click(function(){
			$("#delete-account-confirm").dialog("open");
		});
	');
		$content = '
	<div style="text-align:center;margin-bottom:10px;">
		<span id="delete-account">' . $this->func->s('delete_now') . '</span>
	</div>
	' . $this->v_utils->v_info($this->func->s('posible_restore_account'), $this->func->s('reference'));

		$this->func->addHidden('
		<div id="delete-account-confirm">
			' . $this->v_utils->v_info($this->func->s('delete_account_confirm_msg')) . '
		</div>
	');

		return $this->v_utils->v_field($content, $this->func->s('delete_account'), array('class' => 'ui-padding'));
	}
}
