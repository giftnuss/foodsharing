<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Helpers\DataHelper;
use Foodsharing\Helpers\IdentificationHelper;
use Foodsharing\Helpers\WeightHelper;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\StorePermissions;

class StoreControl extends Control
{
	private $bellGateway;
	private $storeGateway;
	private $storePermissions;
	private $regionGateway;
	private $foodsaverGateway;
	private $identificationHelper;
	private $dataHelper;
	private $weightHelper;

	public function __construct(
		StoreModel $model,
		StorePermissions $storePermissions,
		StoreView $view,
		BellGateway $bellGateway,
		StoreGateway $storeGateway,
		FoodsaverGateway $foodsaverGateway,
		RegionGateway $regionGateway,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper,
		WeightHelper $weightHelper
	) {
		$this->model = $model;
		$this->view = $view;
		$this->bellGateway = $bellGateway;
		$this->storeGateway = $storeGateway;
		$this->storePermissions = $storePermissions;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->regionGateway = $regionGateway;
		$this->identificationHelper = $identificationHelper;
		$this->dataHelper = $dataHelper;
		$this->weightHelper = $weightHelper;

		parent::__construct();

		if (!$this->session->may()) {
			$this->routeHelper->goLogin();
		}
	}

	public function index()
	{
		/* form methods below work with $g_data */
		global $g_data;

		if (isset($_GET['bid'])) {
			$regionId = (int)$_GET['bid'];
		} else {
			$regionId = $this->session->getCurrentRegionId();
		}

		if (!$this->session->isOrgaTeam() && $regionId == 0) {
			$regionId = $this->session->getCurrentRegionId();
		}
		if ($regionId > 0) {
			$region = $this->regionGateway->getRegion($regionId);
		} else {
			$region = ['name' => 'kompletter Datenbank'];
		}
		if ($this->identificationHelper->getAction('new')) {
			if ($this->storePermissions->mayCreateStore()) {
				$this->handle_add($this->session->id());

				$this->pageHelper->addBread($this->translationHelper->s('bread_betrieb'), '/?page=betrieb');
				$this->pageHelper->addBread($this->translationHelper->s('add_new_store'));

				if (isset($_GET['id'])) {
					$g_data['foodsaver'] = $this->model->getBetriebLeader($_GET['id']);
				}

				$chosenRegion = ($regionId > 0 && $this->regionGateway->getType($regionId) <= Type::REGION) ? $region : null;
				$this->pageHelper->addContent($this->view->betrieb_form($chosenRegion, 'betrieb', $this->model->getBasics_lebensmittel(), $this->model->getBasics_kette(), $this->model->get_betrieb_kategorie(), $this->storeGateway->getStoreStateList(), $this->weightHelper->getWeightListEntries()));

				$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
					['name' => $this->translationHelper->s('back_to_overview'), 'href' => '/?page=fsbetrieb&bid=' . $regionId]
				]), $this->translationHelper->s('actions')), CNT_RIGHT);
			} else {
				$this->flashMessageHelper->info('Zum Anlegen eines Betriebes musst Du Betriebsverantwortlicher sein');
				$this->routeHelper->go('?page=settings&sub=upgrade/up_bip');
			}
		} elseif ($id = $this->identificationHelper->getActionId('delete')) {
			/*
			if($this->model->del_betrieb($id))
			{
				$this->flashMessageHelper->info($this->translationHelper->s('betrieb_deleted'));
				$this->routeHelper->goPage();
			}
			*/
		} elseif ($id = $this->identificationHelper->getActionId('edit')) {
			$this->pageHelper->addBread($this->translationHelper->s('bread_betrieb'), '/?page=betrieb');
			$this->pageHelper->addBread($this->translationHelper->s('edit_store'));
			$data = $this->model->getOne_betrieb($id);

			$this->pageHelper->addTitle($data['name']);
			$this->pageHelper->addTitle($this->translationHelper->s('edit'));

			if ($this->storePermissions->mayEditStore($id)) {
				$this->handle_edit();

				$this->dataHelper->setEditData($data);

				$region = $this->model->getValues(['id', 'name'], 'bezirk', $data['bezirk_id']);
				if (isset($_GET['id'])) {
					$g_data['foodsaver'] = $this->model->getBetriebLeader($_GET['id']);
				}

				$this->pageHelper->addContent($this->view->betrieb_form($region, '', $this->model->getBasics_lebensmittel(), $this->model->getBasics_kette(), $this->model->get_betrieb_kategorie(), $this->storeGateway->getStoreStateList(), $this->weightHelper->getWeightListEntries()));
			} else {
				$this->flashMessageHelper->info('Diesen Betrieb kannst Du nicht bearbeiten');
			}

			$this->pageHelper->addContent($this->v_utils->v_field($this->v_utils->v_menu([
				$this->routeHelper->pageLink('betrieb', 'back_to_overview')
			]), $this->translationHelper->s('actions')), CNT_RIGHT);
		} elseif (isset($_GET['id'])) {
			$this->routeHelper->go('/?page=fsbetrieb&id=' . (int)$_GET['id']);
		} else {
			$this->pageHelper->addBread($this->translationHelper->s('betrieb_bread'), '/?page=betrieb');

			$stores = $this->model->listBetriebReq($regionId);

			$storesMapped = array_map(function ($store) {
				return [
					'id' => (int)$store['id'],
					'name' => $store['name'],
					// status 3 and 5 are the same (in cooperation), always return 3
					'status' => $store['betrieb_status_id'] == 5 ? 3 : (int)$store['betrieb_status_id'],
					'added' => $store['added'],
					'region' => $store['bezirk_name'],
					'address' => $store['anschrift'],
					'city' => $store['stadt'],
					'zipcode' => $store['plz'],
					'geo' => $store['geo'],
				];
			}, $stores);

			$this->pageHelper->addContent($this->view->vueComponent('vue-storelist', 'store-list', [
				'regionName' => $region['name'],
				'regionId' => $regionId,
				'showCreateStore' => $this->storePermissions->mayCreateStore(),
				'stores' => $storesMapped
			]));
		}
	}

	private function handle_edit()
	{
		global $g_data;
		if ($this->submitted()) {
			$g_data['stadt'] = $g_data['ort'];
			$g_data['hsnr'] = '';
			$g_data['str'] = $g_data['anschrift'];

			if ($this->model->update_betrieb($_GET['id'], $g_data)) {
				$this->flashMessageHelper->info($this->translationHelper->s('betrieb_edit_success'));
				$this->routeHelper->go('/?page=fsbetrieb&id=' . (int)$_GET['id']);
			} else {
				$this->flashMessageHelper->error($this->translationHelper->s('error'));
			}
		}
	}

	private function handle_add($coordinator)
	{
		global $g_data;
		if ($this->submitted()) {
			$g_data['status_date'] = date('Y-m-d H:i:s');

			if (!isset($g_data['bezirk_id'])) {
				$g_data['bezirk_id'] = $this->session->getCurrentRegionId();
			}
			if (!in_array($g_data['bezirk_id'], $this->session->listRegionIDs())) {
				$this->flashMessageHelper->error($this->translationHelper->s('store.can_only_create_store_in_member_region'));
				$this->routeHelper->goPage();
			}

			if (isset($g_data['ort'])) {
				$g_data['stadt'] = $g_data['ort'];
			}
			$g_data['foodsaver'] = [$coordinator];
			if (isset($g_data['anschrift'])) {
				$g_data['str'] = $g_data['anschrift'];
			}
			$g_data['hsnr'] = '';

			if ($id = $this->model->add_betrieb($g_data)) {
				$this->storeGateway->add_betrieb_notiz([
					'foodsaver_id' => $this->session->id(),
					'betrieb_id' => $id,
					'text' => '{BETRIEB_ADDED}',
					'zeit' => date('Y-m-d H:i:s', (time() - 10)),
					'milestone' => 1
				]);

				if (isset($g_data['first_post']) && !empty($g_data['first_post'])) {
					$this->storeGateway->add_betrieb_notiz([
						'foodsaver_id' => $this->session->id(),
						'betrieb_id' => $id,
						'text' => $g_data['first_post'],
						'zeit' => date('Y-m-d H:i:s'),
						'milestone' => 0
					]);
				}

				$foodsaver = $this->foodsaverGateway->getFoodsaversByRegion($g_data['bezirk_id']);

				$this->bellGateway->addBell($foodsaver, 'store_new_title', 'store_new', 'img img-store brown', [
					'href' => '/?page=fsbetrieb&id=' . (int)$id
				], [
					'user' => $this->session->user('name'),
					'name' => $g_data['name']
				], 'store-new-' . (int)$id);

				$this->flashMessageHelper->info($this->translationHelper->s('betrieb_add_success'));

				$this->routeHelper->go('/?page=fsbetrieb&id=' . (int)$id);
			} else {
				$this->flashMessageHelper->error($this->translationHelper->s('error'));
			}
		}
	}
}
