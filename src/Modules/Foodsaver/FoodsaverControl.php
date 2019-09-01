<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Settings\SettingsGateway;
use Foodsharing\Modules\Store\StoreModel;

class FoodsaverControl extends Control
{
	private $storeModel;
	private $settingsGateway;
	private $regionGateway;
	private $foodsaverGateway;
	private $identificationHelper;
	private $dataHelper;

	public function __construct(
		FoodsaverModel $model,
		FoodsaverView $view,
		StoreModel $storeModel,
		SettingsGateway $settingsGateway,
		RegionGateway $regionGateway,
		FoodsaverGateway $foodsaverGateway,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper
	) {
		$this->model = $model;
		$this->view = $view;
		$this->storeModel = $storeModel;
		$this->settingsGateway = $settingsGateway;
		$this->regionGateway = $regionGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->identificationHelper = $identificationHelper;
		$this->dataHelper = $dataHelper;

		parent::__construct();
	}

	/*
	 * Default Method for ?page=foodsaver
	 */
	public function index()
	{
		// check bezirk_id and permissions
		if (isset($_GET['bid']) && ($region = $this->regionGateway->getRegion($_GET['bid'])) && ($this->session->may('orga') || $this->session->isAmbassadorForRegion(array($_GET['bid']), false, true))) {
			// permission granted so we can load the foodsavers
			if ($foodsaver = $this->model->listFoodsaver($_GET['bid'])) {
				// add breadcrumps
				$this->pageHelper->addBread('Foodsaver', '/?page=foodsaver&bid=' . $region['id']);
				$this->pageHelper->addBread($region['name'], '/?page=foodsaver&bid=' . $region['id']);

				// list fooodsaver ($inactive can be 1 or 0, 1 means that it shows only the inactive ones and not all)
				$this->pageHelper->addContent(
					$this->view->foodsaverList($foodsaver, $region),
					CNT_LEFT
				);

				$this->pageHelper->addContent($this->view->foodsaverForm());

				// list inactive foodsaver
				if ($foodsaverInactive = $this->model->listFoodsaver($_GET['bid'], true)) {
					$this->pageHelper->addContent(
						$this->view->foodsaverList($foodsaverInactive, $region, true),
						CNT_RIGHT
					);
				}
			}
		} elseif (($id = $this->identificationHelper->getActionId('edit')) && ($this->session->isAmbassador() || $this->session->isOrgaTeam())) {
			$data = $this->foodsaverGateway->getOne_foodsaver($id);
			$regionIds = $this->regionGateway->getFsRegionIds($id);
			if ($data && ($this->session->isAmbassadorForRegion($regionIds, false, true) || $this->session->isOrgaTeam())) {
				$this->handle_edit();
				$data = $this->foodsaverGateway->getOne_foodsaver($id);

				$this->pageHelper->addBread($this->translationHelper->s('bread_foodsaver'), '/?page=foodsaver');
				$this->pageHelper->addBread($this->translationHelper->s('bread_edit_foodsaver'));
				$this->dataHelper->setEditData($data);
				$regionDetails = false;
				if ($data['bezirk_id'] > 0) {
					$regionDetails = $this->regionGateway->getRegion($data['bezirk_id']);
				}
				$this->pageHelper->addContent($this->view->foodsaver_form($data['name'] . ' ' . $data['nachname'] . ' bearbeiten', $regionDetails));

				$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu(array(
					array('href' => '/profile/' . $data['id'], 'name' => $this->translationHelper->s('back_to_profile')),
					array('click' => 'fsapp.confirmDeleteUser(' . $data['id'] . ')', 'name' => $this->translationHelper->s('delete_account'))
				)), $this->translationHelper->s('actions')), CNT_RIGHT);
			}
		} else {
			$this->pageHelper->addContent($this->v_utils->v_info('Du hast leider keine Berechtigung für diesen Bezirk'));
		}
	}

	private function handle_edit()
	{
		global $g_data;

		if ($this->submitted()) {
			if ($this->session->isOrgaTeam()) {
				if (isset($g_data['orgateam']) && is_array($g_data['orgateam']) && $g_data['orgateam'][0] == 1) {
					$g_data['orgateam'] = 1;
				}
			} else {
				$g_data['orgateam'] = 0;
				unset($g_data['email'], $g_data['rolle']);
			}

			if ($oldFs = $this->foodsaverGateway->getOne_foodsaver($_GET['id'])) {
				$logChangedFields = array('name', 'nachname', 'stadt', 'plz', 'anschrift', 'telefon', 'handy', 'geschlecht', 'geb_datum', 'rolle', 'orgateam');
				$this->settingsGateway->logChangedSetting($_GET['id'], $oldFs, $g_data, $logChangedFields, $this->session->id());
			}
			if ($this->model->update_foodsaver($_GET['id'], $g_data, $this->storeModel)) {
				$this->flashMessageHelper->info($this->translationHelper->s('foodsaver_edit_success'));
			} else {
				$this->flashMessageHelper->error($this->translationHelper->s('error'));
			}
		}
	}

	private function picture_box()
	{
		$photo = $this->foodsaverGateway->getPhoto($_GET['id']);

		if (!(file_exists('images/thumb_crop_' . $photo))) {
			$p_cnt = $this->v_utils->v_photo_edit('img/portrait.png', (int)$_GET['id']);
		} else {
			$p_cnt = $this->v_utils->v_photo_edit('images/thumb_crop_' . $photo, (int)$_GET['id']);
			//$p_cnt = $this->v_utils->v_photo_edit('img/portrait.png');
		}

		return $this->v_utils->v_field($p_cnt, 'Dein Foto');
	}
}
