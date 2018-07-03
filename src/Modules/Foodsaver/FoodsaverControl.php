<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Settings\SettingsModel;
use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Store\StoreModel;

class FoodsaverControl extends Control
{
	private $storeModel;
	private $settingsModel;
	private $regionGateway;

	public function __construct(FoodsaverModel $model, FoodsaverView $view, StoreModel $storeModel, SettingsModel $settingsModel, RegionGateway $regionGateway)
	{
		$this->model = $model;
		$this->view = $view;
		$this->storeModel = $storeModel;
		$this->settingsModel = $settingsModel;
		$this->regionGateway = $regionGateway;

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
			$bids = $this->regionGateway->getFsBezirkIds($id);
			if ($data && ($this->func->isOrgaTeam() || $this->func->isBotForA($bids, false, true))) {
				$this->handle_edit();
				$data = $this->model->getOne_foodsaver($id);

				$this->func->addBread($this->func->s('bread_foodsaver'), '/?page=foodsaver');
				$this->func->addBread($this->func->s('bread_edit_foodsaver'));
				$this->func->setEditData($data);
				$regionDetails = false;
				if ($data['bezirk_id'] > 0) {
					$regionDetails = $this->model->getBezirk($data['bezirk_id']);
				}
				$this->func->addContent($this->view->foodsaver_form($data['name'] . ' ' . $data['nachname'] . ' bearbeiten', $regionDetails));

				$this->func->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
					$this->func->pageLink('foodsaver', 'back_to_overview')
				)), $this->func->s('actions')), CNT_RIGHT);

				if ($this->func->isOrgaTeam()) {
					$this->func->addContent($this->view->u_delete_account(), CNT_RIGHT);
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
			$this->func->info('Foodsaver ' . $foodsaver['name'] . ' ' . $foodsaver['nachname'] . ' wurde gelöscht, für die Wiederherstellung wende Dich an ' . SUPPORT_EMAIL);
			$this->func->go('/?page=dashboard');
		}
	}

	private function handle_edit()
	{
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

			if ($oldFs = $this->settingsModel->getOne_foodsaver($_GET['id'])) {
				$logChangedFields = array('name', 'nachname', 'stadt', 'plz', 'anschrift', 'telefon', 'handy', 'geschlecht', 'geb_datum', 'rolle', 'orgateam');
				$this->settingsModel->logChangedSetting($_GET['id'], $oldFs, $g_data, $logChangedFields);
			}
			if ($this->model->update_foodsaver($_GET['id'], $g_data, $this->storeModel)) {
				$this->func->info($this->func->s('foodsaver_edit_success'));
			} else {
				$this->func->error($this->func->s('error'));
			}
		}
	}

	private function picture_box()
	{
		$photo = $this->model->getPhoto($_GET['id']);

		if (!(file_exists('images/thumb_crop_' . $photo))) {
			$p_cnt = $this->v_utils->v_photo_edit('img/portrait.png', (int)$_GET['id']);
		} else {
			$p_cnt = $this->v_utils->v_photo_edit('images/thumb_crop_' . $photo, (int)$_GET['id']);
			//$p_cnt = $this->v_utils->v_photo_edit('img/portrait.png');
		}

		return $this->v_utils->v_field($p_cnt, 'Dein Foto');
	}
}
