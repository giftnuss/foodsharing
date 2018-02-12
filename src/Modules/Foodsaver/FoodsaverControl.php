<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Settings\SettingsModel;
use Foodsharing\Lib\Session\S;

class FoodsaverControl extends Control
{
	public function __construct()
	{
		$this->model = new FoodsaverModel();
		$this->view = new FoodsaverView();

		parent::__construct();

		if (isset($_GET['deleteaccount'])) {
			$this->deleteAccount($_GET['id']);
		}
	}

	/*
	 * Default Method for ?page=foodsaver
	 */
	public function index()
	{
		// check bezirk_id and permissions
		if (isset($_GET['bid']) && ($bezirk = $this->model->getBezirk($_GET['bid'])) && (S::may('orga') || $this->func->isBotForA(array($_GET['bid']), false, true))) {
			// permission granted so we can load the foodsavers
			if ($foodsaver = $this->model->listFoodsaver($_GET['bid'])) {
				// add breadcrumps
				$this->func->addBread('Foodsaver', '/?page=foodsaver&bid=' . $bezirk['id']);
				$this->func->addBread($bezirk['name'], '/?page=foodsaver&bid=' . $bezirk['id']);

				// list fooodsaver ($inactive can be 1 or 0, 1 means that it shows only the inactive ones and not all)
				$this->func->addContent(
					$this->view->foodsaverList($foodsaver, $bezirk),
					CNT_LEFT
				);

				$this->func->addContent($this->view->foodsaverForm());

				$this->func->addContent(
					$this->view->addFoodsaver($bezirk),
					CNT_RIGHT
				);

				// list inactive foodsaver
				if ($foodsaverInactive = $this->model->listFoodsaver($_GET['bid'], true)) {
					$this->func->addContent(
						$this->view->foodsaverList($foodsaverInactive, $bezirk, true),
						CNT_RIGHT
					);
				}
			}
		} elseif (($id = $this->func->getActionId('edit')) && ($this->func->isBotschafter() || $this->func->isOrgaTeam())) {
			$data = $this->model->getOne_foodsaver($id);
			$bids = $this->model->getFsBezirkIds($id);
			if ($data && ($this->func->isOrgaTeam() || $this->func->isBotForA($bids, false, true))) {
				handle_edit();
				$data = $this->model->getOne_foodsaver($id);

				$this->func->addBread($this->func->s('bread_foodsaver'), '/?page=foodsaver');
				$this->func->addBread($this->func->s('bread_edit_foodsaver'));
				$this->func->setEditData($data);

				$this->func->addContent(foodsaver_form($data['name'] . ' ' . $data['nachname'] . ' bearbeiten'));

				$this->func->addContent(picture_box(), CNT_RIGHT);

				$this->func->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
					$this->func->pageLink('foodsaver', 'back_to_overview')
				)), $this->func->s('actions')), CNT_RIGHT);

				if ($this->func->isOrgaTeam()) {
					$this->func->addContent(u_delete_account(), CNT_RIGHT);
				}
			}
		} else {
			$this->func->addContent($this->v_utils->v_info('Du hast leider keine Berechtigung für diesen Bezirk'));
		}
	}

	private function deleteAccount($id)
	{
		if ((S::may('orga'))) {
			$foodsaver = $this->model->getValues(array('email', 'name', 'nachname', 'bezirk_id'), 'foodsaver', $id);

			$this->model->del_foodsaver($id);
			$this->func->info('Foodsaver ' . $foodsaver['name'] . ' ' . $foodsaver['nachname'] . ' wurde gelöscht, für die Wiederherstellung wende Dich an it@lebensmittelretten.de');
			$this->func->go('/?page=dashboard');
		}
	}
}

function handle_edit()
{
	global $db;
	global $g_data;

	if ($this->func->submitted()) {
		if ($this->func->isOrgaTeam()) {
			if (isset($g_data['orgateam']) && is_array($g_data['orgateam']) && $g_data['orgateam'][0] == 1) {
				$g_data['orgateam'] = 1;
			}
		} else {
			$g_data['orgateam'] = 0;
			unset($g_data['email']);
			unset($g_data['rolle']);
		}

		$settings_model = new SettingsModel();
		if ($oldFs = $settings_model->getOne_foodsaver($_GET['id'])) {
			$logChangedFields = array('name', 'nachname', 'stadt', 'plz', 'anschrift', 'telefon', 'handy', 'geschlecht', 'geb_datum', 'rolle', 'orgateam');
			$settings_model->logChangedSetting($_GET['id'], $oldFs, $g_data, $logChangedFields);
		}
		if ($db->update_foodsaver($_GET['id'], $g_data)) {
			$this->func->info($this->func->s('foodsaver_edit_success'));
		} else {
			$this->func->error($this->func->s('error'));
		}
	}
}

function foodsaver_form($title = 'Foodsaver')
{
	global $db;
	global $g_data;

	$orga = '';

	$position = '';

	if (S::may('orga')) {
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

	$bezirk = false;

	if ((int)$g_data['bezirk_id'] > 0) {
		$bezirk = $db->getBezirk($g_data['bezirk_id']);
	}

	$bezirkchoose = $this->v_utils->v_bezirkChooser('bezirk_id', $bezirk);

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

function picture_box()
{
	global $db;

	$photo = $db->getPhoto($_GET['id']);

	if (!(file_exists('images/thumb_crop_' . $photo))) {
		$p_cnt = $this->v_utils->v_photo_edit('img/portrait.png', (int)$_GET['id']);
	} else {
		$p_cnt = $this->v_utils->v_photo_edit('images/thumb_crop_' . $photo, (int)$_GET['id']);
		//$p_cnt = $this->v_utils->v_photo_edit('img/portrait.png');
	}

	return $this->v_utils->v_field($p_cnt, 'Dein Foto');
}

function u_delete_account()
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
