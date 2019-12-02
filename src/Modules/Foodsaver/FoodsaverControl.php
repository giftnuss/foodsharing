<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
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
		FoodsaverView $view,
		StoreModel $storeModel,
		SettingsGateway $settingsGateway,
		RegionGateway $regionGateway,
		FoodsaverGateway $foodsaverGateway,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper
	) {
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
		    $regionId = $region['id'];
            if ($foodsavers = $this->foodsaverGateway->listFoodsaver($regionId)) {
				// add breadcrumps
				$this->pageHelper->addBread('Foodsaver', '/?page=foodsaver&bid=' . $regionId);
				$this->pageHelper->addBread($region['name'], '/?page=foodsaver&bid=' . $regionId);

				// list fooodsaver ($inactive can be 1 or 0, 1 means that it shows only the inactive ones and not all)
				$this->pageHelper->addContent(
					$this->view->foodsaverList($foodsavers, $region),
					CNT_LEFT
				);

				$this->pageHelper->addContent($this->view->foodsaverForm());

				// list inactive foodsaver
				if ($foodsaverInactive = $this->foodsaverGateway->listFoodsaver($_GET['bid'], true)) {
					$this->pageHelper->addContent(
						$this->view->foodsaverList($foodsaverInactive, $region, true),
						CNT_RIGHT
					);
				}
			}
		} elseif (($fsId = $this->identificationHelper->getActionId('edit')) && ($this->session->isAmbassador() || $this->session->isOrgaTeam())) {
			$fs = $this->foodsaverGateway->getOne_foodsaver($fsId);
			$regionIds = $this->regionGateway->getFsRegionIds($fsId);
			if ($fs && ($this->session->isAmbassadorForRegion($regionIds, false, true) || $this->session->isOrgaTeam())) {
				$this->handle_edit();
				$fs = $this->foodsaverGateway->getOne_foodsaver($fsId);

				$this->pageHelper->addBread($this->translationHelper->s('bread_foodsaver'), '/?page=foodsaver');
				$this->pageHelper->addBread($this->translationHelper->s('bread_edit_foodsaver'));

				$this->dataHelper->setEditData($fs);

				$regionDetails = $fs['bezirk_id'] > 0 ? $this->regionGateway->getRegion($fs['bezirk_id']) : false;
				$this->pageHelper->addContent($this->view->foodsaver_form($fs['name'] . ' ' . $fs['nachname'] . ' bearbeiten', $regionDetails));

				$this->pageHelper->addContent($this->v_utils->v_field(
				    $this->v_utils->v_menu([
					   ['href' => '/profile/' . $fs['id'], 'name' => $this->translationHelper->s('back_to_profile')],
					   ['click' => 'fsapp.confirmDeleteUser(' . $fs['id'] . ')', 'name' => $this->translationHelper->s('delete_account')]
				    ]),
				    $this->translationHelper->s('actions')),
				    CNT_RIGHT
				);
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

			$fsId = $_GET['id'];
			if ($oldFs = $this->foodsaverGateway->getOne_foodsaver($fsId)) {
				$logChangedFields = array('name', 'nachname', 'stadt', 'plz', 'anschrift', 'telefon', 'handy', 'geschlecht', 'geb_datum', 'rolle', 'orgateam');
				$this->settingsGateway->logChangedSetting($fsId, $oldFs, $g_data, $logChangedFields, $this->session->id());
			}

			if (!isset($g_data['bezirk_id'])) {
				$g_data['bezirk_id'] = $this->session->getCurrentRegionId();
			}

			if ($this->updateFoodsaver($fsId, $g_data)) {
				$this->flashMessageHelper->info($this->translationHelper->s('foodsaver_edit_success'));
			} else {
				$this->flashMessageHelper->error($this->translationHelper->s('error'));
			}
		}
	}

	private function updateFoodsaver(int $fsId, array $data): int
	{
		$updateResult = $this->foodsaverGateway->updateFoodsaver($fsId, $data);
		if ($updateResult) {
			if (isset($data['rolle']) && $data['rolle'] == Role::FOODSHARER && $this->session->isOrgaTeam()) {
				$updateResult = $this->foodsaverGateway->downgradePermanently($fsId, $this->storeModel);
			}
		}

		return $updateResult;
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
